<?php
class receiptsControllerClass{
	protected $langDecode_constructor, $lang, $usersModel_constructor, $filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $receiptsModel_constructor, $mainformController_constructor;
	public function __construct($langDecode_constructor,$lang,$usersModel_constructor,$filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $receiptsModel_constructor, $mainformController_constructor) {
	        $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->usersModel = $usersModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->mainformModel = $mainformModel_constructor;
		$this->receiptsModel = $receiptsModel_constructor;
		$this->mainformController = $mainformController_constructor;
	}
	
	public function changeReceipt($changeArray=array(),$options=array(),$accessOptions=array()){
		/**
		* Saves the details of a receipt
		* (array)changeArray{
		*	changeDetails = change details if 1. Default to 0
		*	changeStatus = change status if 1.Default to 0
		* }
		* (array)options{
		*	action: edit, create. default to create
		*	receiptsDetail_receiptId: 
		*	receiptsDetail_receiptName
		*	receiptsDetail_receiptDescription
		*	receiptsDetail_receiptData
		*	receiptsStatus_status
		* 
		* }
		* (array) accessOptions{
		*	(array) userGroups
		* }
		* return array{
		*	returnStatus
		*	msg
		*	receiptsDetail_receiptId
		* }
		*/
		
		$action = "create";
		$insertBy_userId = "";
		$id = "";

		$receiptsDetail_receiptId = "";
		$receiptsDetail_receiptName = "";
		$receiptsDetail_receiptDescription = '';
		$receiptsStatus_status = 0;
		$receiptsDetail_receiptData = '';
		
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
			"manageallreceipts"
		);
		$permission = $this->mainformController->checkPermission($userId, $functionCode, $userGroups);

		if($permission['status']){
			if(!empty(array_filter($changeArray))>0 && $somethingToChange){
				if(!$receiptsDetail_receiptId && $action=="create"){
					$addReceiptOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result['addReceipt'] = $this->receiptsModel->addReceiptId($addReceiptOptions);
					if($result['addReceipt']['status']){
						$receiptsDetail_receiptId = $result['addReceipt']['receiptsId_receiptId'];
					}else{
						$returnStatus = 0;
						$msg = $result['addReceipt']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($receiptsDetail_receiptId){
					if($permission['status']){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
		
						if($changeDetails){
							if($permission['status']){
								$receiptOptions = array(
									"receiptName"=>$receiptsDetail_receiptName,
									"receiptDescription"=>$receiptsDetail_receiptDescription,
									"receiptData"=>$receiptsDetail_receiptData,
									"insertBy_userId"=>$insertBy_userId
								);
								$result['changeDetails'] = $this->receiptsModel->addReceiptDetail($receiptsDetail_receiptId,$receiptOptions);
							}else{
								$result['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change receipt details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"receiptsDetail_receiptId"=>$receiptsDetail_receiptId
								);
							}
						}
						if($changeStatus){
							if($permission['status']){
								$result['changeStatus'] = $this->receiptsModel->addReceiptStatus($receiptsDetail_receiptId, $receiptsStatus_status, $insertBy_userId);
							}else{
								$result['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change receipt status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"receiptsDetail_receiptId"=>$receiptsDetail_receiptId
								);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallreceipts' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject receiptId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}else{
				$msg = "Nothing to change";
				$returnStatus = 1;
				$msgCode = "ERR_MSG";
			}
		}else{
			$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'manageallreceipts' for you";
			$returnStatus = 0;
			$msgCode = "SYS_ERR_MSG";
		}
		$result["status"] = $returnStatus;
		$result["msg"] = $this->langDecode->decode($msg, $msgCode, $this->lang);
		return $result;
	}
}
?>