<?php
class storesControllerClass{
	protected $langDecode_constructor, $lang, $usersModel_constructor, $filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $storesModel_constructor, $mainformController_constructor;
	public function __construct($langDecode_constructor,$lang,$usersModel_constructor,$filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $storesModel_constructor, $mainformController_constructor) {
	        $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->usersModel = $usersModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->mainformModel = $mainformModel_constructor;
		$this->storesModel = $storesModel_constructor;
		$this->mainformController = $mainformController_constructor;
	}
	
	
	public function changeBrand($changeArray=array(),$options=array(),$accessOptions=array()){
		/**
		* Saves the details of a brand
		* (array)changeArray{
		*	changeDetails = change details if true. Default to false
		*	changeStatus = change status if true.Default to false
		*	changeParents = change group parents if true
		* }
		* (array)options{
		*	action: edit, create
		*	insertBy_userId
		*	id = id of record fetched and shown to user
		*	brandId : brandId to edit details
		*	brandName
		*	brandCode
		*	status
		*	(array) parents = {
		*		brandId
		*	}, undefined if not changed
		* 
		* }
		* return array{
		*	returnStatus
		*	msg
		*	brandId
		* }
		*/
		
		$action = "create";
		$insertBy_userId = "";
		$id = "";
		$brandId = "";
		$brandName = "";
		$brandCode = '';
		$status = "";
		$parents = array();
		
		$msg = "";
		$returnStatus = 1;
		
		$somethingToChange = 0;
		$changeDetails = 0;
		$changeStatus = 0;
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
			"manageallbrands"
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
		if(in_array("manageallbrands",$userFunctionsFlat)){
			if(!empty(array_filter($changeArray))>0 && $somethingToChange){
				if(!$brandId && $action=="create"){
					$addbrandOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result['addBrand'] = $this->storesModel->addBrandId($addBrandOptions);
					if($result['addBrand']['status']){
						$brandId = $result['addBrand']['brandId'];
					}else{
						$returnStatus = 0;
						$msg = $result['addBrand']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($brandId){
					if(in_array("manageallbrands",$userFunctionsFlat)){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
		
						if($changeDetails){
							if(in_array("manageallbrands",$userFunctionsFlat)){
								$brandsOptions = array(
									"brandCode"=>$brandCode,
									"brandName"=>$brandName,
									"insertBy_userId"=>$insertBy_userId
								);
								$result['changeDetails'] = $this->storesModel->addBrandsDetail($brandId,$brandsOptions);
							}else{
								$result['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change brand details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"brandId"=>$brandId
								);
							}
						}
						if($changeStatus){
							if(in_array("manageallbrands",$userFunctionsFlat)){
								$result['changeStatus'] = $this->storesModel->addBrandsStatus($brandId,$status,$insertBy_userId);
							}else{
								$result['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change brand status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"brandId"=>$brandId
								);
							}
						}
						
						if($changeParents){
							if(!empty(array_filter($parents))>0){
							
								$addBrandsParentOptions = array(
									"parents"=>$parents,
									"insertBy_userId"=>$insertBy_userId	
								);
								$result['changeParents'] = $this->storesModel->addBrandsParent($brandId,$addBrandsParentOptions);
							}else{
								$changeBrandStatus = 1;
						                $changeBrandMsgCode = "No parents to change";
						                $changeBrandMsgCat = "OK_MSG";
						                $changeBrandMsg = $this->langDecode->decode($changeGroupMsgCode,$changeGroupMsgCat,$this->lang);
								$result['changeParents'] = array("status"=>$changeBrandStatus,"msg"=>$changeBrandMsg);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallbrands' for you";
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
			$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallbrands' for you";
			$returnStatus = 0;
			$msgCode = "SYS_ERR_MSG";
		}
		$result["status"] = $returnStatus;
		$result["msg"] = $this->langDecode->decode($msg, $msgCode, $this->lang);
		return $result;
	}
	
	public function changeStore($changeArray=array(),$options=array(),$accessOptions=array()){
		/**
		* Saves the details of a store
		* (array)changeArray{
		*	changeDetails = change details if 1. Default to 0
		*	changeStatus = change status if 1.Default to 0
		*	changeBrands = change brands if 1.Default to 0
		*	changeContact = change contact if 1.Default to 0
		*	changeReceipts = change receipts if 1. Default to 0
		* }
		* (array)options{
		*	action: edit, create. default to create
		*	storesDetail_storeId : storeId to edit details
		*	storesDetail_storeName
		*	storesDetail_storeCode
		*	storesStatus_status
		*	(array) brands = {
		*		brandId,
		*		status: 0:remove or 1:add
		*	}, undefined if not changed,
		*	storesContact_addressLine1
		*	storesContact_addressLine2
		*	storesContact_state
		*	storesContact_city
		*	storesContact_country
		*	storesContact_zipcode
		*	storesContact_phone
		*	storesContact_email
		*	storesContact_cell
		*	receipts{
		*		receiptId
		*		receiptType
		*	}
		* 
		* }
		* return array{
		*	returnStatus
		*	msg
		*	brandId
		* }
		*/
		
		$action = "create";
		$insertBy_userId = "";
		$id = "";
		$storesDetail_storeId = "";
		$storesDetail_storeName = "";
		$storesDetail_storeCode = '';
		$storesStatus_status = 0;
		$storesContact_addressLine1 = '';
		$storesContact_addressLine2 = '';
		$storesContact_state = '';
		$storesContact_city = '';
		$storesContact_country = '';
		$storesContact_zipcode = '';
		$storesContact_phone = '';
		$storesContact_email = '';
		$storesContact_cell = '';
	
		$brands = array();
		
		$receipt = array();
		
		$msg = "";
		$returnStatus = 1;
		
		$somethingToChange = 0;
		$changeDetails = 0;
		$changeStatus = 0;
		$changeBrands = 0;
		$changeContact = 0;
		$changeReceipts = 0;


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
			"manageallstores"
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
		if(in_array("manageallstores",$userFunctionsFlat)){
			if(!empty(array_filter($changeArray))>0 && $somethingToChange){
				if(!$storesDetail_storeId && $action=="create"){
					$addStoreOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result['addStore'] = $this->storesModel->addStoreId($addStoreOptions);
					if($result['addStore']['status']){
						$storesDetail_storeId = $result['addStore']['storesId_storeId'];
					}else{
						$returnStatus = 0;
						$msg = $result['addStore']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($storesDetail_storeId){
					if(in_array("manageallstores",$userFunctionsFlat)){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
		
						if($changeDetails){
							if(in_array("manageallstores",$userFunctionsFlat)){
								$storesOptions = array(
									"storeCode"=>$storesDetail_storeCode,
									"storeName"=>$storesDetail_storeName,
									"insertBy_userId"=>$insertBy_userId
								);
								$result['changeDetails'] = $this->storesModel->addStoresDetail($storesDetail_storeId,$storesOptions);
							}else{
								$result['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change store details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"storesDetail_storeId"=>$storesDetail_storeId
								);
							}
						}
						if($changeStatus){
							if(in_array("manageallstores",$userFunctionsFlat)){
								$result['changeStatus'] = $this->storesModel->addStoresStatus($storesDetail_storeId, $storesStatus_status, $insertBy_userId);
							}else{
								$result['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change store status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"storesDetail_storeId"=>$storesDetail_storeId
								);
							}
						}
						if($changeContact){
							if(in_array("manageallstores",$userFunctionsFlat)){
								$storesContactOptions = array(
									"phone"=>$storesContact_phone,
									"cell"=>$storesContact_cell,
									"email"=>$storesContact_email,
									"addressLine1"=>$storesContact_addressLine1,
									"addressLine2"=>$storesContact_addressLine2,
									"city"=>$storesContact_city,
									"state"=>$storesContact_state,
									"country"=>$storesContact_country,
									"zipcode"=>$storesContact_zipcode,
									"insertBy_userId"=>$insertBy_userId
								);
								$result['changeContact'] = $this->storesModel->addStoresContact($storesDetail_storeId,$storesContactOptions);
							}else{
								$result['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change store details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"storesDetail_storeId"=>$storesDetail_storeId
								);
							}
						}
						if($changeBrands){
							if(!empty(array_filter($brands))>0){
							
								$addStoreBrandsOptions = array(
									"brands"=>$brands,
									"insertBy_userId"=>$insertBy_userId	
								);
								$result['changeBrands'] = $this->storesModel->addStoresBrand($storesDetail_storeId,$addStoreBrandsOptions);
							}else{
								$changeBrandStatus = 1;
						                $changeBrandMsgCode = "No Brands to change";
						                $changeBrandMsgCat = "OK_MSG";
						                $changeBrandMsg = $this->langDecode->decode($changeGroupMsgCode,$changeGroupMsgCat,$this->lang);
								$result['changeBrands'] = array("status"=>$changeBrandStatus,"msg"=>$changeBrandMsg);
							}
						}
						if($changeReceipts){
							if(!empty(array_filter($receipts))>0){
							
								$addStoresReceiptOptions = array(
									"receipts"=>$receipts,
									"insertBy_userId"=>$insertBy_userId	
								);
								$result['changeReceipts'] = $this->storesModel->addStoresReceipt($storesDetail_storeId, $addStoresReceiptOptions);
							}else{
								$changeReceiptStatus = 1;
						                $changeReceiptMsgCode = "No Brands to change";
						                $changeReceiptMsgCat = "OK_MSG";
						                $changeReceiptMsg = $this->langDecode->decode($changeGroupMsgCode,$changeGroupMsgCat,$this->lang);
								$result['changeReceipts'] = array("status"=>$changeReceiptStatus,"msg"=>$changeReceiptMsg);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallstores' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject storeId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}else{
				$msg = "Nothing to change";
				$returnStatus = 0;
				$msgCode = "ERR_MSG";
			}
		}else{
			$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallstores' for you";
			$returnStatus = 0;
			$msgCode = "SYS_ERR_MSG";
		}
		$result["status"] = $returnStatus;
		$result["msg"] = $this->langDecode->decode($msg, $msgCode, $this->lang);
		return $result;
	}
	
	public function changeTill($changeArray=array(),$options=array(),$accessOptions=array()){
		/**
		* Saves the details of a till
		* (array)changeArray{
		*	changeDetails = change details if 1. Default to 0
		*	changeStatus = change status if 1.Default to 0
		* }
		* (array)options{
		*	action: edit, create. default to create
		*	tillsDetail_tillId : tillId to edit details
		*	tillsDetail_tillNumber
		*	tillsDetail_tillCode
		*	tillsDetail_storeId
		*	tillsStatus_status
		* }
		* return array{
		*	returnStatus
		*	msg
		*	brandId
		* }
		*/
		
		$action = "create";
		$insertBy_userId = "";
		$id = "";
		$tillsDetail_tillId = "";
		$tillsDetail_tillNumber = "";
		$tillsDetail_tillCode = '';
		$tillsStatus_status = 0;
		$tillsDetail_storeId = '';
		
		$msg = "";
		$returnStatus = 1;
		
		$somethingToChange = 0;
		$changeDetails = 0;
		$changeStatus = 0;

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
			"managealltills"
		);
		$functionOptions = array(
			"userGroups"=>$userGroups,
			"functionCode"=>$functionCode,
			"userId"=>$insertBy_userId
		);
		$permission = $this->mainformController->checkPermission($userId, $functionCode, $userGroups);
		
		if($permission['status']){
			if(!empty(array_filter($changeArray))>0 && $somethingToChange){
				if(!$tillsDetail_tillId && $action=="create"){
					$addTillOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result['addTill'] = $this->storesModel->addTillId($addTillOptions);
					if($result['addTill']['status']){
						$tillsDetail_tillId = $result['addTill']['tillsId_tillId'];
					}else{
						$returnStatus = 0;
						$msg = $result['addTill']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($tillsDetail_tillId){
					if($permission['status']){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
		
						if($changeDetails){
							if($permission['status']){
								$tillsOptions = array(
									"tillCode"=>$tillsDetail_tillCode,
									"tillNumber"=>$tillsDetail_tillNumber,
									"storeId"=>$tillsDetail_storeId,
									"insertBy_userId"=>$insertBy_userId
								);
								$result['changeDetails'] = $this->storesModel->addTillsDetail($tillsDetail_tillId,$tillsOptions);
							}else{
								$result['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change till details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"tillsDetail_tillId"=>$tillsDetail_tillId
								);
							}
						}
						if($changeStatus){
							if($permission['status']){
								$result['changeStatus'] = $this->storesModel->addTillStatus($tillsDetail_tillId, $tillsStatus_status, $insertBy_userId);
							}else{
								$result['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change till status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"storesDetail_storeId"=>$storesDetail_storeId
								);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'managealltills' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject tillId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}else{
				$msg = "Nothing to change";
				$returnStatus = 0;
				$msgCode = "ERR_MSG";
			}
		}else{
			$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'managealltills' for you";
			$returnStatus = 0;
			$msgCode = "SYS_ERR_MSG";
		}
		$result["status"] = $returnStatus;
		$result["msg"] = $this->langDecode->decode($msg, $msgCode, $this->lang);
		return $result;
	}
}
?>