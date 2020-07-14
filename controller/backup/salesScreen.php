<?php
class salesScreenControllerClass{
	protected $langDecode_constructor, $lang, $usersModel_constructor, $filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $salesScreenModel_constructor, $mainformController_constructor;
	public function __construct($langDecode_constructor,$lang,$usersModel_constructor,$filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $salesScreenModel_constructor, $mainformController_constructor) {
	        $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->usersModel = $usersModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->mainformModel = $mainformModel_constructor;
		$this->salesScreenModel = $salesScreenModel_constructor;
		$this->mainformController = $mainformController_constructor;
	}
	
	public function changeSalesScreen($changeArray=array(),$options=array(),$accessOptions=array()){
		/**
		* Saves the details of an item
		* (array)changeArray{
		*	changeDetails = change details if 1. Default to 0
		*	changeStatus = change status if 1.Default to 0
		*	changeStores = change stores if 1.Default to 0
		* }
		* (array)options{
		*	action: edit, create. default to create
		*	salesScreenDetail_salesScreenId: 
		*	salesScreenDetail_salesScreenName
		*	salesScreenDetail_salesScreenDescription
		*	salesScreenDetail_salesScreenData
		*	salesScreenStatus_status
		*	(array) stores = {
		*		storesDetail_storeId,
		*		status: 0:remove or 1:add
		*	}, undefined if not changed,
		* 
		* }
		* (array) accessOptions{
		*	(array) userGroups
		* }
		* return array{
		*	returnStatus
		*	msg
		*	salesScreenDetail_salesScreenId
		* }
		*/
		
		$action = "create";
		$insertBy_userId = "";
		$id = "";

		$salesScreenDetail_salesScreenId = "";
		$salesScreenDetail_salesScreenName = "";
		$salesScreenDetail_salesScreenDescription = '';
		$salesScreenStatus_status = 0;
		$salesScreenDetail_salesScreenData = '';
	
		$stores = array();
		
		$msg = "";
		$returnStatus = 1;
		
		$somethingToChange = 0;
		$changeDetails = 0;
		$changeStatus = 0;
		$changeStores = 0;


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
			"manageallsalesscreens"
		);
		$permission = $this->mainformController->checkPermission($userId, $functionCode, $userGroups);

		if($permission['status']){
			if(!empty(array_filter($changeArray))>0 && $somethingToChange){
				if(!$salesScreenDetail_salesScreenId && $action=="create"){
					$addSalesScreenOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result['addSalesScreen'] = $this->salesScreenModel->addSalesScreenId($addSalesScreenOptions);
					if($result['addSalesScreen']['status']){
						$salesScreenDetail_salesScreenId = $result['addSalesScreen']['salesScreenId_salesScreenId'];
					}else{
						$returnStatus = 0;
						$msg = $result['addSalesScreen']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($salesScreenDetail_salesScreenId){
					if($permission['status']){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
		
						if($changeDetails){
							if($permission['status']){
								$salesScreenOptions = array(
									"salesScreenName"=>$salesScreenDetail_salesScreenName,
									"salesScreenDescription"=>$salesScreenDetail_salesScreenDescription,
									"salesScreenData"=>$salesScreenDetail_salesScreenData,
									"insertBy_userId"=>$insertBy_userId
								);
								$result['changeDetails'] = $this->salesScreenModel->addsalesScreenDetail($salesScreenDetail_salesScreenId,$salesScreenOptions);
							}else{
								$result['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change sales screen details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"salesScreenDetail_salesScreenId"=>$salesScreenDetail_salesScreenId
								);
							}
						}
						if($changeStatus){
							if($permission['status']){
								$result['changeStatus'] = $this->salesScreenModel->addSalesScreenStatus($salesScreenDetail_salesScreenId, $salesScreenStatus_status, $insertBy_userId);
							}else{
								$result['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change sales screen status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"salesScreenDetail_salesScreenId"=>$salesScreenDetail_salesScreenId
								);
							}
						}

						if($changeStores){
							if(!empty(array_filter($stores))>0){
							
								$addSalesScreenStoresOptions = array(
									"stores"=>$stores,
									"insertBy_userId"=>$insertBy_userId	
								);
								$result['changeStores'] = $this->salesScreenModel->addsalesScreenStore($salesScreenDetail_salesScreenId,$addSalesScreenStoresOptions);
							}else{
								$changeStoreStatus = 1;
						                $changeStoreMsgCode = "No Stores to change";
						                $changeStoreMsgCat = "OK_MSG";
						                
						                $changeStoreMsg = $this->langDecode->decode($changeStoreMsgCode,$changeStoreMsgCat,$this->lang);
								$result['changeStores'] = array("status"=>$changeStoreStatus,"msg"=>$changeStoreMsg);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallsalesscreens' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject salesScreenId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}else{
				$msg = "Nothing to change";
				$returnStatus = 1;
				$msgCode = "ERR_MSG";
			}
		}else{
			$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallsalesscreens' for you";
			$returnStatus = 0;
			$msgCode = "SYS_ERR_MSG";
		}
		$result["status"] = $returnStatus;
		$result["msg"] = $this->langDecode->decode($msg, $msgCode, $this->lang);
		return $result;
	}
}
?>