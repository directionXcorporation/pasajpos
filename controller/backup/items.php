<?php
class itemsControllerClass{
	protected $langDecode_constructor, $lang, $usersModel_constructor, $filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $itemsModel_constructor, $mainformController_constructor;
	public function __construct($langDecode_constructor,$lang,$usersModel_constructor,$filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $itemsModel_constructor, $mainformController_constructor) {
	        $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->usersModel = $usersModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->mainformModel = $mainformModel_constructor;
		$this->itemsModel = $itemsModel_constructor;
		$this->mainformController = $mainformController_constructor;
	}
	
	
	public function changeItemCategory($changeArray=array(),$options=array(),$accessOptions=array()){
		/**
		* Saves the details of a item category
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
	
	public function changeItem($changeArray=array(),$options=array(),$accessOptions=array()){
		/**
		* Saves the details of an item
		* (array)changeArray{
		*	changeDetails = change details if 1. Default to 0
		*	changeStatus = change status if 1.Default to 0
		*	changeBrands = change brands if 1.Default to 0
		*	changeCategory = change category if 1.Default to 0
		*	changePrice = change price if 1.Default to 0
		* }
		* (array)options{
		*	action: edit, create. default to create
		*	itemsDetail_itemId: itemId to edit details
		*	itemsDetail_itemName
		*	itemsDetail_itemCode
		*	itemsDetail_itemBarcode
		*	itemsDetail_itemExternalId
		*	itemsDetail_itemDescription
		*	itemsStatus_status
		*	(array) brands = {
		*		brandId,
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
		*	itemsDetail_itemId
		* }
		*/
		
		$action = "create";
		$insertBy_userId = "";
		$id = "";

		$itemsDetail_itemId = "";
		$itemsDetail_itemName = "";
		$itemsDetail_itemCode = '';
		$itemsStatus_status = 0;
		$itemsDetail_itemBarcode = '';
		$itemsDetail_itemExternalId = '';
		$itemsDetail_itemDescription = '';
	
		$brands = array();
		
		$msg = "";
		$returnStatus = 1;
		
		$somethingToChange = 0;
		$changeDetails = 0;
		$changeStatus = 0;
		$changeBrands = 0;
		$changeCategory = 0;
		$changePrices = 0;


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
			"manageallitems"
		);
		$permission = $this->mainformController->checkPermission($userId, $functionCode, $userGroups);

		if($permission['status']){
			if(!empty(array_filter($changeArray))>0 && $somethingToChange){
				if(!$itemsDetail_itemId && $action=="create"){
					$addItemOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result['addItem'] = $this->itemsModel->addItemId($addItemOptions);
					if($result['addItem']['status']){
						$itemsDetail_itemId = $result['addItem']['itemsId_itemId'];
					}else{
						$returnStatus = 0;
						$msg = $result['addItem']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($itemsDetail_itemId){
					if($permission['status']){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
		
						if($changeDetails){
							if($permission['status']){
								$itemsOptions = array(
									"itemCode"=>$itemsDetail_itemCode,
									"itemName"=>$itemsDetail_itemName,
									"itemExternalId"=>$itemsDetail_itemExternalId,
									"itemBarcode"=>$itemsDetail_itemBarcode,
									"itemDescription"=>$itemsDetail_itemDescription,
									"insertBy_userId"=>$insertBy_userId
								);
								$result['changeDetails'] = $this->itemsModel->addItemsDetail($itemsDetail_itemId,$itemsOptions);
							}else{
								$result['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change item details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"itemsDetail_itemId"=>$itemsDetail_itemId
								);
							}
						}
						if($changeStatus){
							if($permission['status']){
								$result['changeStatus'] = $this->itemsModel->addItemsStatus($itemsDetail_itemId, $itemsStatus_status, $insertBy_userId);
							}else{
								$result['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change item status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"itemsDetail_itemId"=>$itemsDetail_itemId
								);
							}
						}
						if($changeCategory){
							if($permission['status']){
								
							}else{
								$result['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change item Category","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"itemsDetail_itemId"=>$itemsDetail_itemId
								);
							}
						}
						if($changeBrands){
							if(!empty(array_filter($brands))>0){
							
								$addItemBrandsOptions = array(
									"brands"=>$brands,
									"insertBy_userId"=>$insertBy_userId	
								);
								$result['changeBrands'] = $this->itemsModel->addItemsBrand($itemsDetail_itemId,$addItemBrandsOptions);
							}else{
								$changeBrandStatus = 1;
						                $changeBrandMsgCode = "No Brands to change";
						                $changeBrandMsgCat = "OK_MSG";
						                
						                $changeBrandMsg = $this->langDecode->decode($changeGroupMsgCode,$changeGroupMsgCat,$this->lang);
								$result['changeBrands'] = array("status"=>$changeBrandStatus,"msg"=>$changeBrandMsg);
							}
						}
						if($changePrices){
							if(!empty(array_filter($itemPrices))>0){
								$addItemPriceOptions = array(
									"prices"=>$itemPrices,
									"insertBy_userId"=>$insertBy_userId	
								);
								
								$result['changePrices'] = $this->itemsModel->addItemsPrice($itemsDetail_itemId,$addItemPriceOptions);
							}else{
								$changePriceStatus = 1;
						                $changePriceMsgCode = "No Price to change";
						                $changePriceMsgCat = "OK_MSG";
						                $changePriceMsg = $this->langDecode->decode($changePriceMsgCode,$changePriceMsgCat,$this->lang);
								$result['changePrices'] = array("status"=>$changePriceStatus,"msg"=>$changePriceMsg);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallitems' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject itemId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}else{
				$msg = "Nothing to change";
				$returnStatus = 1;
				$msgCode = "ERR_MSG";
			}
		}else{
			$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallitems' for you";
			$returnStatus = 0;
			$msgCode = "SYS_ERR_MSG";
		}
		$result["status"] = $returnStatus;
		$result["msg"] = $this->langDecode->decode($msg, $msgCode, $this->lang);
		return $result;
	}
}
?>