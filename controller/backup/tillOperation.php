<?php
class tillOperationControllerClass{
	protected $langDecode_constructor, $lang, $filePath_constructor, $loginClass_constructor, $tillOperationModel_constructor, $mainformController_constructor;
	public function __construct($mainformController_constructor, $filePath_constructor, $loginClass_constructor, $langDecode_constructor, $tillOperationModelClass, $lang) {
	        $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->tillOperationModel = $tillOperationModelClass;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->mainformController = $mainformController_constructor;
	}
	public function syncCloseOpen($data){
		$operationDetails = $data;
		$status = 0;
		$tillOperationResult = array();
		$tillPaymentShotResult = array();
		$tillPaymentDiscrepancyResult = array();
		$tillPaymentMovementResult = array();
		
		$loginCheck = $this->loginClass->loginCheck();
		if($loginCheck['status']){
			$insertBy_userId = $loginCheck['userId'];
		}
		$tillOperationOptions = array(
			"insertBy_userId"=>$insertBy_userId
		);
		$tillOperationResult = $this->tillOperationModel->addTillOperation(array($operationDetails), $tillOperationOptions);
		$status = 1;
		if($tillOperationResult['status']){
			if(!empty(array_filter($operationDetails['tillPaymentShotDetails']))>0){
				$tillPaymentShotResult = $this->tillOperationModel->addTillPaymentShot($operationDetails['tillPaymentShotDetails'], $tillOperationOptions);
			}
			if(!empty(array_filter($operationDetails['tillCashShotDetails']))>0){
				$tillCashShotResult = $this->tillOperationModel->addTillCashShot($operationDetails['tillCashShotDetails'], $tillOperationOptions);
			}
			if(!empty(array_filter($operationDetails['tillPaymentDiscrepancyDetails']))>0){
				$tillPaymentDiscrepancyResult = $this->tillOperationModel->addTillPaymentDiscrepancy($operationDetails['tillPaymentDiscrepancyDetails'], $tillOperationOptions);
			}
			if(!empty(array_filter($operationDetails['tillPaymentMovementDetails']))>0){
				$tillPaymentMovementResult = $this->tillOperationModel->addPaymentMovement($operationDetails['tillPaymentMovementDetails'], $tillOperationOptions);
			}
		}
		$result = array(
			"status" => $status,
			"tillOperationResult"=>$tillOperationResult,
			"tillPaymentShotResult"=>$tillPaymentShotResult,
			"tillCashShotResult"=>$tillCashShotResult,
			"tillPaymentDiscrepancyResult"=>$tillPaymentDiscrepancyResult,
			"tillPaymentMovementResult"=>$tillPaymentMovementResult
		);
		return $result;
	}
}	
?>