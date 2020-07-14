<?php
class usersControllerClass{
	protected $langDecode_constructor, $lang, $usersModel_constructor,$filePath_constructor,$loginClass_constructor, $mainformModel_constructor;
	public function __construct($langDecode_constructor,$lang,$usersModel_constructor,$filePath_constructor, $loginClass_constructor, $mainformModel_constructor) {
	        $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->usersModel = $usersModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->mainformModel = $mainformModel_constructor;
	}
	public function changeUser($changeArray=array(),$options=array(),$accessOptions=array()){
		/**
		* Saves the details of a user
		* (array)$changeArray{
		*	$changeDetails = change details if true. Default to false
		*	$changeStatus = change status if true.Default to false
		*	$changeLogin = change username or password
		*	$changePinCode = change pinCode
		*	$changeGroups = change groups
		* }
		* (array)$options{
		*	$userId = userId of the person being changed
		*	$firstName = first name
		*	$lastName = last name
		*	$gender = gender, 0:unidentified, 1:male, 2:female,
		*	$insertBy_userId = user id who inserted this
		*	$username = new password
		*	$password = new username
		*	userStatus 
		*	passwordLoginStatus 
		*	fingerprintLoginStatus 
		*	pinLoginStatus 
		*   hashedPinCode
		*	statusStartDate
		*	statusEndDate
		*	(array) $groups = {
		*		groupId
		*		accessStart
		*		accessEnd
		*	}
		* }
		* (array)$accessOptions{
		*	userGroups
		* }
		* return html of template
		*/

		$insertBy_userId = "";
		$userId = "";
		$firstName = "";
		$lastName = "";
		$gender = 0;
		$username = "";
		$password = "";
		$userStatus = "";
		$hashedPinCode = "";
		$passwordLoginStatus = "";
		$fingerprintLoginStatus = "";
		$pinLoginStatus = "";
		$statusStartDate = "0000-00-00";
		$statusEndDate = "0000-00-00";
		$groups = array();
		
		$somethingToChange = false;
		$changeDetails = false;
		$changeStatus = false;
		$changeLogin = false;
		$changeGroups = false;
		$changePinCode = false;
		
		$userGroups = false;

		$result = array();
		
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		foreach($accessOptions as $accessOptionsKey=>$accessOptionsElement){
			${$accessOptionsKey} = $accessOptionsElement;
		}
		foreach($changeArray as $changeArrayKey=>$changeArrayElement){
			${$changeArrayKey} = $changeArrayElement;
			if($changeArrayElement){
				$somethingToChange = true;
			}
		}
		
		if(!$userId){
			$loginArray = $this->loginClass->loginCheck();
			if($loginArray['status'] && $loginArray['userId']){
				$options = array(
					"userId"=>array($loginArray['userId'])
				);
			}
		}
		
		if(!$insertBy_userId){
			$loginArray = $this->loginClass->loginCheck();
			if($loginArray['status'] && $loginArray['userId']){
				$insertBy_userId = $loginArray['userId'];
			}
		}
		
		if(!$userGroups){
			$userGroups = $this->usersModel->getUserGroups($insertBy_userId);
		}	
		
		if($userId){
			$functionCode = array(
				"changeownpassword",
				"changeownpincode",
				"changeownbasicinfo",
				"changeownusername",
				"addallgroupstoown",
				"addchildrengroupstoown",
				"removeowngroup",
				"changeownstatus"
			);
			$functionOptions = array(
				"userGroups"=>$userGroups,
				"functionCode"=>$functionCode,
				"userId"=>$insertBy_userId
			);
			$userFunctions = $this->usersModel->getUserFunctions($functionOptions);
			
			foreach($userFunctions as $userFunctionsElement){
				$userFunctionsFlat[] = $userFunctionsElement['functionCode'];
			}

			if(!empty(array_filter($changeArray))>0 && $somethingToChange){
				$status = 1;
				$msg = "Processing request";
				$msgCode = "OK_MSG";

				if($changeDetails){
					if(in_array("changeownbasicinfo",$userFunctionsFlat)){
						$usersOptions = array(
							"firstName"=>$firstName,
							"lastName"=>$lastName,
							"gender"=>$gender,
							"insertBy_userId"=>$insertBy_userId
						);
						$result['changeDetails'] = $this->usersModel->addUsersDetail($userId,$usersOptions);
					}else{
						$result['changeDetails'] = array(
							"status"=>0,
							"msg"=>$this->langDecode->decode("You do not have permission to change user details","SYS_ERR_MSG",$this->lang),
							"id"=>'',
							"userId"=>$userId
						);
					}
				}
				if($changeLogin){
					if(in_array("changeownpassword",$userFunctionsFlat) || in_array("changeownusername",$userFunctionsFlat)){
						$loginOptions = array(
							"userId"=>$userId,
							"insertBy_userId"=>$insertBy_userId
						);
						if(in_array("changeownpassword",$userFunctionsFlat)){
							$loginOptions["username"] = $username;
						}
						if(in_array("changeownusername",$userFunctionsFlat)){
							$loginOptions["password"] = $password;
						}
						
						$result['changeLogin'] = $this->loginClass->setUsernamePassword($loginOptions);
					}else{
						$result['changeLogin'] = array(
							"status"=>0,
							"msg"=>$this->langDecode->decode("You do not have permission to change user login information","SYS_ERR_MSG",$this->lang),
							"id"=>'',
							"userId"=>$userId
						);
					}
				}
				if($changeStatus){
					if(in_array("changeownstatus",$userFunctionsFlat)){
						$result['changeStatus'] = $this->usersModel->addUserStatus($userId,$userStatus,$passwordLoginStatus,$fingerprintLoginStatus,$pinLoginStatus,$statusStartDate,$statusEndDate);
					}else{
						$result['changeStatus'] = array(
							"status"=>0,
							"msg"=>$this->langDecode->decode("You do not have permission to change user status","SYS_ERR_MSG",$this->lang),
							"id"=>'',
							"userId"=>$userId
						);
					}
				}
				if($changePinCode){
					if(in_array("changeownpassword",$userFunctionsFlat)){
					    $hashedPinCode = '*'.strtoupper(hash('sha512', md5(hash('sha512', $hashedPinCode.$userId))));
						$result['changePinCode'] = $this->loginClass->addUsersPinCode($userId,array("pinCode"=>$hashedPinCode,"insertBy_userId"=>$insertBy_userId));
					}else{
						$result['changePinCode'] = array(
							"status"=>0,
							"msg"=>$this->langDecode->decode("You do not have permission to change user pinCode","SYS_ERR_MSG",$this->lang),
							"id"=>'',
							"userId"=>$userId
						);
					}
				}
				if($changeGroups){
					$NewGroupArray = array();
					$toRemoveGroupIds = array();
					$toAddGroupIds = array();
					$oldUserGroupArray = array();
					$oldUserGroupIds = array();
					$groupsIds = array();
					$processed = array();
					$userGroups = $this->usersModel->getUserGroups($userId);
					$oldUserGroupArray = array();
					foreach($userGroups as $key => $userGroupsElement){
						$oldUserGroupArray[$key] = array(
							"groupId"=>$userGroupsElement['groupId'],
							"accessStart"=>$userGroupsElement['accessStart'],
							"accessEnd"=>$userGroupsElement['accessEnd']
						);
						$oldUserGroupIds[$key] = $userGroupsElement['groupId'];
					}
					foreach($groups as $key => $groupsElement){
						$groupsIds[$key] = $groupsElement['groupId'];
					}
					foreach($oldUserGroupIds as $key => $oldUserGroupIdsElement){
						$processed[$oldUserGroupIdsElement] = 1;
						if(!in_array($oldUserGroupIdsElement,$groupsIds)){
							//REMOVE FROM USER GROUP AS THE GROUP ID IS NOT IN NEW GROUP
							$NewGroupArray[] = array_merge($oldUserGroupArray[$key],array("isActive"=>0));
						}else{
							if($oldUserGroupArray[$key]["accessStart"]!=$groups[$key]["accessStart"] || $oldUserGroupArray[$key]["accessEnd"]!=$groups[$key]["accessEnd"]){
								$NewGroupArray[] = array_merge($groups[$key],array("isActive"=>1));
							}
						}
					}
					foreach($groups as $groupsElement){
						if(!isset($processed[$groupsElement['groupId']])){
							$NewGroupArray[] = array_merge($groupsElement,array("isActive"=>1));
						}
					}

					if(!empty(array_filter($NewGroupArray))>0){
					
						$addGroupOptions = array(
							"userId"=>$userId,
							"groups"=>$NewGroupArray,
							"insertBy_userId"=>$insertBy_userId	
						);
						$result['changeGroups'] = $this->usersModel->addUsersGroup($addGroupOptions);
					}else{
						$changeGroupStatus = 1;
				                $changeGroupMsgCode = "No groups to change";
				                $changeGroupMsgCat = "OK_MSG";
				                $changeGroupMsg = $this->langDecode->decode($changeGroupMsgCode,$changeGroupMsgCat,$this->lang);
						$result['changeGroups'] = array("status"=>$changeGroupStatus,"msg"=>$changeGroupMsg);
					}
				}
			}else{
				$msg = "Nothing to change";
				$status = 0;
				$msgCode = "ERR_MSG";
			}
		}else{
			$msg = "Error getting the subject userId. Administrator is infromed of this error. Please try again shortly";
			$status = 0;
			$msgCode = "SYS_ERR_MSG";
		}
		$result["status"] = $status;
		$result["msg"] = $this->langDecode->decode($msg, $msgCode, $this->lang);
		return $result;
	}
	
	
	public function changeGroup($changeArray=array(),$options=array(),$accessOptions=array()){
		/**
		* Saves the details of a group
		* (array)changeArray{
		*	changeDetails = change details if true. Default to false
		*	changeStatus = change status if true.Default to false
		*	changeParents = change group parents if true
		*	changeFunctions = change Allowed Functions if true
		*	changeStores = change stores if 1.Default to 0
		* }
		* (array)options{
		*	action: edit, create
		*	insertBy_userId
		*	id = id of record fetched and shown to user
		*	groupId : groupId to edit details
		*	groupName
		*	groupDescription: null if description was not changed to save on amount of data transferred
		*	status
		*	startDate 
		*	endDate
		*	stores
		*	(array) parents = {
		*		groupId
		*		GroupName
		*	}, undefined if not changed
		* 
		* 	(array)functions{
		*		functionCode
		* 	}, undefined if not changed
		* }
		* return array{
		*	returnStatus
		*	msg
		*	groupId
		* }
		*/
		
		$action = "create";
		$insertBy_userId = "";
		$id = "";
		$groupId = "";
		$groupName = "";
		$groupDescription = '';
		$status = "";
		$startDate = "";
		$endDate = "";
		$parents = array();
		$functions = array();
		$stores = array();
		
		$msg = "";
		$returnStatus = 1;
		
		$somethingToChange = 0;
		$changeDetails = 0;
		$changeStatus = 0;
		$changeFunctions = 0;
		$changeParents = 0;


		$result = array();
		
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		foreach($accessOptions as $accessOptionsKey=>$accessOptionsElement){
			${$accessOptionsKey} = $accessOptionsElement;
		}
		foreach($changeArray as $changeArrayKey=>$changeArrayElement){
			${$changeArrayKey} = $changeArrayElement;
			if($changeArrayElement){
				$somethingToChange = 1;
			}
		}
		
		if(!$insertBy_userId){
			$loginArray = $this->loginClass->loginCheck();
			if($loginArray['status'] && $loginArray['userId']){
				$insertBy_userId = $loginArray['userId'];
			}
		}
		
		$functionCode = array(
			"manageallgroups"
		);
		$functionOptions = array(
			"userGroups"=>$userGroups,
			"functionCode"=>$functionCode,
			"userId"=>$insertBy_userId
		);
		$userFunctions = $this->usersModel->getUserFunctions($functionOptions);
			
		foreach($userFunctions as $userFunctionsElement){
			$userFunctionsFlat[] = $userFunctionsElement['functionCode'];
		}
		if(in_array("manageallgroups",$userFunctionsFlat)){
			if(!empty(array_filter($changeArray))>0 && $somethingToChange){
				if(!$groupId && $action=="create"){
					$addGroupOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result['addGroup'] = $this->usersModel->addGroupId($addGroupOptions);
					if($result['addGroup']['status']){
						$groupId = $result['addGroup']['groupId'];
					}else{
						$returnStatus = 0;
						$msg = $result['addGroup']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($groupId){
					if(in_array("manageallgroups",$userFunctionsFlat)){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
		
						if($changeDetails){
							if(in_array("manageallgroups",$userFunctionsFlat)){
								$groupsOptions = array(
									"groupName"=>$groupName,
									"groupDescription"=>$groupDescription,
									"insertBy_userId"=>$insertBy_userId
								);
								$result['changeDetails'] = $this->usersModel->addGroupsDetail($groupId,$groupsOptions);
							}else{
								$result['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change group details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"groupId"=>$groupId
								);
							}
						}
						if($changeStatus){
							if(in_array("manageallgroups",$userFunctionsFlat)){
								$result['changeStatus'] = $this->usersModel->addGroupStatus($groupId,$status,$startDate,$endDate,$insertBy_userId);
							}else{
								$result['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change group status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"groupId"=>$groupId
								);
							}
						}
						if($changeFunctions){
							if(!empty(array_filter($functions))>0){
							
								$addGroupsFunctionOptions = array(
									"functions"=>$functions,
									"insertBy_userId"=>$insertBy_userId	
								);
								$result['changeFunctions'] = $this->usersModel->addGroupsFunction($groupId,$addGroupsFunctionOptions);
							}else{
								$changeGroupStatus = 1;
						                $changeGroupMsgCode = "No functions to change";
						                $changeGroupMsgCat = "OK_MSG";
						                $changeGroupMsg = $this->langDecode->decode($changeGroupMsgCode,$changeGroupMsgCat,$this->lang);
								$result['changeFunctions'] = array("status"=>$changeGroupStatus,"msg"=>$changeGroupMsg);
							}
						}
						
						if($changeParents){
							if(!empty(array_filter($parents))>0){
							
								$addGroupsParentOptions = array(
									"parents"=>$parents,
									"insertBy_userId"=>$insertBy_userId	
								);
								$result['changeParents'] = $this->usersModel->addGroupsParent($groupId,$addGroupsParentOptions);
							}else{
								$changeGroupStatus = 1;
						                $changeGroupMsgCode = "No functions to change";
						                $changeGroupMsgCat = "OK_MSG";
						                $changeGroupMsg = $this->langDecode->decode($changeGroupMsgCode,$changeGroupMsgCat,$this->lang);
								$result['changeParents'] = array("status"=>$changeGroupStatus,"msg"=>$changeGroupMsg);
							}
						}
						if($changeStores){
							if(!empty(array_filter($stores))>0){
							
								$addGroupStoresOptions = array(
									"stores"=>$stores,
									"insertBy_userId"=>$insertBy_userId	
								);
								$result['changeStores'] = $this->usersModel->addGroupsStore($groupId,$addGroupStoresOptions);
							}else{
								$changeStoreStatus = 1;
						                $changeStoreMsgCode = "No Stores to change";
						                $changeStoreMsgCat = "OK_MSG";
						                
						                $changeStoreMsg = $this->langDecode->decode($changeStoreMsgCode,$changeStoreMsgCat,$this->lang);
								$result['changeStores'] = array("status"=>$changeStoreStatus,"msg"=>$changeStoreMsg);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallfunction' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject groupId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}else{
				$msg = "Nothing to change";
				$returnStatus = 0;
				$msgCode = "ERR_MSG";
			}
		}else{
			$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallfunction' for you";
			$returnStatus = 0;
			$msgCode = "SYS_ERR_MSG";
		}
		$result["status"] = $returnStatus;
		$result["msg"] = $this->langDecode->decode($msg, $msgCode, $this->lang);
		return $result;
	}

}