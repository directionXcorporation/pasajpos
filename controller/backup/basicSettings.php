<?php
class basicSettingsControllerClass{
	protected $langDecode_constructor, $lang, $filePath_constructor, $loginClass_constructor,  $basicSettingsModel_constructor, $mainformController_constructor, $usersModel_constructor;
	public function __construct($langDecode_constructor, $filePath_constructor, $loginClass_constructor, $mainformController_constructor, $usersModel_constructor, $basicSettingsModel_constructor, $lang) {
	        $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->usersModel = $usersModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->basicSettingsModel = $basicSettingsModel_constructor;
		$this->mainformController = $mainformController_constructor;
	}
	
	public function changeBasicSettings($detailName, $changeArray=array(), $options=array(), $accessOptions){
		$action = "create";
		$insertBy_userId = "";
		$id = "";
		$msg = "";
		$returnStatus = 1;
		$changeDetails = 0;
		$changeStatus = 0;
		
		$result = array();
		
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		foreach($changeArray as $changeArrayKey=>$changeArrayElement){
			${$changeArrayKey} = $changeArrayElement;
			if($changeArrayElement){
				$somethingToChange = 1;
			}
		}
		foreach($accessOptions as $accessOptionsKey=>$accessOptionsElement){
			${$accessOptionsKey} = $accessOptionsElement;
		}
		
		if(!$insertBy_userId){
			$loginArray = $this->loginClass->loginCheck();
			if($loginArray['status'] && $loginArray['userId']){
				$insertBy_userId = $loginArray['userId'];
			}
		}
		$functionCode = array(
			"editCurrencySettings",
			"editTaxSettings",
			"editMarkdownSettings",
			"editPaymentExtSettings",
			"editPaymentMethodSettings",
			"editBasicSettings"
		);
		$permission = $this->mainformController->checkPermission($userId, $functionCode, $userGroups);
		if($permission['status']){
			if($detailName=="cashDetails"){
				if(!$cashTypesDetail_cashTypeId && $action=="create"){
					$addCashTypeOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result[$detailName]['addCashType'] = $this->basicSettingsModel->addCashTypesId($addCashTypeOptions);
					if($result[$detailName]['addCashType']['status']){
						$cashTypesDetail_cashTypeId = $result[$detailName]['addCashType']['cashTypesDetail_cashTypeId'];
					}else{
						$returnStatus = 0;
						$msg = $result[$detailName]['addCashType']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($cashTypesDetail_cashTypeId){
					if($permission['status']){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
						if($changeDetails){
							if($permission['status']){
								$cashTypeOptions = array(
									"cashName"=>$cashTypesDetail_cashName,
									"cashCategoriesId"=>$cashTypesDetail_cashCategoriesId,
									"cashValue"=>$cashTypesDetail_cashValue,
									"currencyId"=>$cashTypesDetail_currencyId,
									"insertBy_userId"=>$insertBy_userId
								);
								$result[$detailName]['changeDetails'] = $this->basicSettingsModel->addCashTypesDetail($cashTypesDetail_cashTypeId, $cashTypeOptions);
							}else{
								$result[$detailName]['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change cash type details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"cashTypesDetail_cashTypeId"=>$cashTypesDetail_cashTypeId 
								);
							}
						}
						if($changeStatus){
							if($permission['status']){
								$result[$detailName]['changeStatus'] = $this->basicSettingsModel->addCashTypesStatus($cashTypesDetail_cashTypeId, $cashTypesStatus_status, $insertBy_userId);
							}else{
								$result[$detailName]['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change cash type status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"cashTypesDetail_cashTypeId"=>$cashTypesDetail_cashTypeId
								);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'editCurrencySettings' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject cashTypeId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}
			if($detailName=="taxDetails"){
				if(!$taxDetail_taxId && $action=="create"){
					$addTaxOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result[$detailName]['addTax'] = $this->basicSettingsModel->addTaxId($addTaxOptions);
					if($result[$detailName]['addTax']['status']){
						$taxDetail_taxId = $result[$detailName]['addTax']['taxDetail_taxId'];
					}else{
						$returnStatus = 0;
						$msg = $result[$detailName]['addTax']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($taxDetail_taxId){
					if($permission['status']){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
						if($changeDetails){
							if($permission['status']){
								$taxOptions = array(
									"taxName"=>$taxDetail_taxName,
									"taxPercent"=>$taxDetail_taxPercent,
									"insertBy_userId"=>$insertBy_userId
								);
								$result[$detailName]['changeDetails'] = $this->basicSettingsModel->addTaxDetail($taxDetail_taxId, $taxOptions);
							}else{
								$result[$detailName]['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change tax details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"taxDetail_taxId"=>$taxDetail_taxId
								);
							}
						}
						if($changeStatus){
							if($permission['status']){
								$result[$detailName]['changeStatus'] = $this->basicSettingsModel->addTaxStatus($taxDetail_taxId, $taxStatus_status, $insertBy_userId);
							}else{
								$result[$detailName]['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change tax status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"taxDetail_taxId"=>$taxDetail_taxId
								);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'editTaxSettings' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject taxId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}
			if($detailName=="markdownDetails"){
				if(!$markdownsDetail_markdownId && $action=="create"){
					$addMarkdownOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result[$detailName]['addMarkdown'] = $this->basicSettingsModel->addMarkdownsId($addMarkdownOptions);
					if($result[$detailName]['addMarkdown']['status']){
						$markdownsDetail_markdownId = $result[$detailName]['addMarkdown']['markdownsDetail_markdownId'];
					}else{
						$returnStatus = 0;
						$msg = $result[$detailName]['addMarkdown']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($markdownsDetail_markdownId){
					if($permission['status']){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
						if($changeDetails){
							if($permission['status']){
								$markdownOptions = array(
									"markdownType"=>$markdownsDetail_markdownType,
									"markdownDescription"=>$markdownsDetail_markdownDescription,
									"insertBy_userId"=>$insertBy_userId
								);
								$result[$detailName]['changeDetails'] = $this->basicSettingsModel->addMarkdownsDetail($markdownsDetail_markdownId, $markdownOptions);
							}else{
								$result[$detailName]['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change markdown details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"markdownsDetail_markdownId"=>$markdownsDetail_markdownId
								);
							}
						}
						if($changeStatus){
							if($permission['status']){
								$result[$detailName]['changeStatus'] = $this->basicSettingsModel->addMarkdownsStatus($markdownsDetail_markdownId, $markdownsStatus_status, $insertBy_userId);
							}else{
								$result[$detailName]['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change markdown status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"markdownsDetail_markdownId"=>$markdownsDetail_markdownId
								);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'editMarkdownSettings' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject markdownId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}
			if($detailName=="paymentExtDetails"){
				if(!$paymentExtsDetail_extId && $action=="create"){
					$addPaymentExtOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result[$detailName]['addPaymentExt'] = $this->basicSettingsModel->addPaymentExtId($addPaymentExtOptions);
					if($result[$detailName]['addPaymentExt']['status']){
						$paymentExtsDetail_extId = $result[$detailName]['addPaymentExt']['paymentExtsDetail_extId'];
					}else{
						$returnStatus = 0;
						$msg = $result[$detailName]['addPaymentExt']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($paymentExtsDetail_extId){
					if($permission['status']){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
						if($changeDetails){
							if($permission['status']){
								$extOptions = array(
									"extName"=>$paymentExtsDetail_extName,
									"baseUrl"=>$paymentExtsDetail_baseUrl,
									"method"=>$paymentExtsDetail_method,
									"parameters"=>json_encode($paymentExtsDetail_parameters),
									"silent"=>$paymentExtsDetail_silent,
									"insertBy_userId"=>$insertBy_userId
								);
								$result[$detailName]['changeDetails'] = $this->basicSettingsModel->addPaymentExtDetail($paymentExtsDetail_extId, $extOptions);
							}else{
								$result[$detailName]['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change ext details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"paymentExtsDetail_extId"=>$paymentExtsDetail_extId
								);
							}
						}
						if($changeStatus){
							if($permission['status']){
								$result[$detailName]['changeStatus'] = $this->basicSettingsModel->addPaymentExtStatus($paymentExtsDetail_extId, $paymentExtsStatus_status, $insertBy_userId);
							}else{
								$result[$detailName]['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change ext status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"paymentExtsDetail_extId"=>$paymentExtsDetail_extId
								);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'editPaymentExtSettings' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject extId. Administrator is informed of this error. Please try again shortly";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}
			if($detailName=="paymentMethodDetails"){
				if(!$paymentMethodsDetail_methodId && $action=="create"){
					$addPaymentIdOptions = array(
						"insertBy_userId"=>$insertBy_userId
					);
					$result[$detailName]['addPaymentMethod'] = $this->basicSettingsModel->addPaymentMethodId($addPaymentIdOptions);
					if($result[$detailName]['addPaymentMethod']['status']){
						$paymentMethodsDetail_methodId = $result[$detailName]['addPaymentMethod']['paymentMethodsDetail_methodId'];
					}else{
						$returnStatus = 0;
						$msg = $result[$detailName]['addPaymentMethod']['msg'];
						$msgCode = "SYS_ERR_MSG";
					}
				}
				if($paymentMethodsDetail_methodId){
					if($permission['status']){
						$returnStatus = 1;
						$msg = "Processing request";
						$msgCode = "OK_MSG";
						if($changeDetails){
							if($permission['status']){
								$methodOptions = array(
									"methodCode"=>$paymentMethodsDetail_methodCode,
									"currencyId"=>$paymentMethodsDetail_currencyId,
									"methodName"=>$paymentMethodsDetail_methodName,
									"methodDescription"=>$paymentMethodsDetail_methodDescription,
									"priceRounding"=>$paymentMethodsDetail_priceRounding,
									"sendToExtId"=>$paymentMethodsDetail_sendToExtId,
									"recieveFromExtId"=>$paymentMethodsDetail_recieveFromExtId,
									"insertBy_userId"=>$insertBy_userId
								);
								$result[$detailName]['changeDetails'] = $this->basicSettingsModel->addPaymentMethodDetail($paymentMethodsDetail_methodId, $methodOptions);
							}else{
								$result[$detailName]['changeDetails'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change method details","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"paymentMethodsDetail_methodId"=>$paymentMethodsDetail_methodId
								);
							}
						}
						if($changeStatus){
							if($permission['status']){
								$result[$detailName]['changeStatus'] = $this->basicSettingsModel->addPaymentMethodStatus($paymentMethodsDetail_methodId, $paymentMethodsStatus_status, $insertBy_userId);
							}else{
								$result[$detailName]['changeStatus'] = array(
									"status"=>0,
									"msg"=>$this->langDecode->decode("You do not have permission to change method status","SYS_ERR_MSG",$this->lang),
									"id"=>'',
									"paymentMethodsDetail_methodId"=>$paymentMethodsDetail_methodId
								);
							}
						}
					}else{
						$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'editPaymentMethodSettings' for you";
						$returnStatus = 0;
						$msgCode = "SYS_ERR_MSG";
					}
				}else{
					$msg = "Error getting the subject methodId. Administrator is informed of this error. Please try again shortly: ".$msg;
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}
			if($detailName=="basicSettings"){
				if($permission['status']){
					$returnStatus = 1;
					$msg = "Processing request";
					$msgCode = "OK_MSG";
					if($changeDetails){
						if($permission['status']){
							$result[$detailName]['changeDetails'] = $this->basicSettingsModel->addBasicSettings($options);
						}else{
							$result[$detailName]['changeDetails'] = array(
								"status"=>0,
								"msg"=>$this->langDecode->decode("You do not have permission to change basic settings","SYS_ERR_MSG",$this->lang)
							);
						}
					}
				}else{
					$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'editBasicSettings' for you";
					$returnStatus = 0;
					$msgCode = "SYS_ERR_MSG";
				}
			}
		}else{
			$msg = "You do not have permission to perform this operation. Please ask your adminsitrator to enable: 'editTaxSettings' or 'editCurrencySettings' or 'editMarkdownSettings' or 'editPaymentExtSettings' or 'editPaymentMethodSettings' or 'editBasicSettings' for you";
			$returnStatus = 0;
			$msgCode = "SYS_ERR_MSG";
		}
		$result["status"] = $returnStatus;
		$result["msg"] = $this->langDecode->decode($msg, $msgCode, $this->lang);
		return $result;
	}
}
?>