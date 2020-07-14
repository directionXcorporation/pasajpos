<?php
class dynaPageController{
	protected $langDecode_constructor, $lang, $usersModel_constructor, $filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $dynamicPageModel_constructor, $mainformController_constructor;
	public function __construct($langDecode_constructor,$lang,$usersModel_constructor,$filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $dynamicPageModel_constructor, $mainformController_constructor) {
	    $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->usersModel = $usersModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->mainformModel = $mainformModel_constructor;
		$this->dynamicPageModel = $dynamicPageModel_constructor;
		$this->mainformController = $mainformController_constructor;
	}
	
	public function creatContent($options){
	    $dynaPageOptions = $options;
        $dynaPageDetails = $this->createDynaPage($options);
        
        $templateParameters = array(
            "PAGE_JS_FOOTER_HTML"=>$dynaPageDetails['PAGE_JS_FOOTER_HTML'],
            "PAGE_OTHER_FOOTER_HTML"=>$dynaPageDetails['PAGE_OTHER_FOOTER_HTML']
        );
        $options = array(
        	"pageLang"=>$this->lang,
        	"templateParameters"=>$templateParameters
        );
        $content = $this->mainformController->createPage('',$options);
        return $content;
	}
	private function creatSWLink($baseURL="/dynaPage.php?action=getSW"){
	    $content = 'importScripts(["'.$baseURL.'&v='.time().'"]);';
	    return $content;
	}
	
	public function createLangFile($langBasePath='i18n/', $filePrefix = '', $fileSuffix='.json'){
	    try{
    	    $langArray = array();
    	    $options = array(
    	        "status"=>1,
    	        "type"=>array(
    	            "rows"=>array(
    	                "text"
    	            )
    	        )
    	    );
    	    $translateDetails = $this->langDecode->searchDynaLang($options);

    	    $langWrite = '';
    	    $langContent = '[]';
    	    if($translateDetails['status']){
    	        foreach($translateDetails['rows'] as $translateData){
    	            if(!$langArray[$translateData['dynaTranslate_langISO2'].'_'.$translateData['dynaTranslate_countryISO2']]){
    	                $langArray[$translateData['dynaTranslate_langISO2'].'_'.$translateData['dynaTranslate_countryISO2']] = array();
    	            }
    
    	            $langArray[$translateData['dynaTranslate_langISO2'].'_'.$translateData['dynaTranslate_countryISO2']][$translateData['dynaTranslate_keyword']] = $translateData['dynaTranslate_translation'];
    	        }
    	        
    	        foreach($langArray as $key=>$content){
    	            $langContent = json_encode($content);
    	            $langFilePathName = $langBasePath.$filePrefix.$key.$fileSuffix;
    	            $langFilePath = $this->filePath->getPath($langFilePathName, 1);
    	            $langWrite .= ' '.$key.':'.file_put_contents($langFilePath, $langContent, LOCK_EX);
    	        }
    	    }
    	    return array("result"=>$langWrite, "languages"=>$langArray);
	    }
	    catch(Exception $e) {
	        return $e->getMessage();
	    }
	}
	public function createIndex($baseSWURL="/dynaPage.php?action=getSW", $indexFileName='index.html', $langBasePath='i18n/', $langFilePrefix = '', $langFileSuffix='.json', $createSW=1, $createIndex=1, $createLang=1){
	    try {
	        $langWrite = '';
	        $indexWrite = '';
	        $swWrite = '';
	        if($createLang){
    	        $langResult = $this->createLangFile($langBasePath, $langFilePrefix, $langFileSuffix);
    	        $langWrite = $langResult['result'];
	        }
	        if($createIndex){
        	    $indexContent = $this->creatContent(array());
        	    $indexFilePath = $this->filePath->getPath($indexFileName,1);
        	    $indexWrite = file_put_contents($indexFilePath, $indexContent, LOCK_EX);
	        }
	        if($createSW){
        	    $swContent = $this->creatSWLink($baseSWURL);
        	    $swFilePath = $this->filePath->getPath("sw.js",1);
        	    $swWrite = file_put_contents($swFilePath, $swContent, LOCK_EX);
	        }
	        
    	    return "SW (".$swFilePath." ".$swWrite." chars)- INDEX (".$indexFilePath." ".$indexWrite. " chars)- LANG (".$langWrite." chars)";
	    }
	    catch(Exception $e) {
	        return $e->getMessage();
	    }
	}
	public function getServiceWorkers($options=array()){
	    $output = "";
	    $offlinePageOptions = array();
	    
	    $offlinePageOptions["availableOffline"] = 1;
	    
	    if($options && !$options['cache_name']){
	        $options["cache_name"] = "offlinePages";
	    }
	    if($options && $options['pageLang']){
	        $options["pageLang"] = $offlinePageOptions["pageLang"];
	    }
	    $file = $this->filePath->getPath("javascripts/sw/dynaSW.js",1);
	    $langOptions = array(
    	    "status"=>1,
    	    "type"=>array(
    	        "rows"=>array(
    	            "text"
    	        )
    	    ),
    	    "availableLanguages"=>1
    	);
	    $translateDetails = $this->langDecode->searchDynaLang($langOptions);
	    if($translateDetails['status']){
	        $urls_to_cache = array();
	        $langs = array();
			foreach($translateDetails['rows'] as $langDetail){
			    array_push($urls_to_cache, "'/i18n/".$langDetail['lang'].".json'");
			}
			if(count($urls_to_cache)){
			    $urls_to_cache = implode(",", $urls_to_cache).",";
			}else{
			    $urls_to_cache = "";
			}
			$template_parameters = array(
    			"_URLS_TO_CACHE"=>$urls_to_cache,
    			"_CACHE_NAME"=>$options["cache_name"]
    		);
    		
    		$output = $this->filePath->showHTML($file, $template_parameters);
	    }
	    return $output;
	}
	
	public function createPageTemplates($pageTemplateDetails, $serializeOutput = 1){
		if($serializeOutput == 1){
			$output = "";
		}
		foreach($pageTemplateDetails as $templateDetails){
			$parametersArray = json_decode($templateDetails['dynaPageTemplatesData_parameters'], true);
			$parameters = array();
			foreach($parametersArray as $parametersElement){
				if($parametersElement['parameter'] == "_LANG"){
					$parameters[$parametersElement['parameter']] = $this->lang;
				}else{
					$parameters[$parametersElement['parameter']] = $this->langDecode->decode($parametersElement['value'],"TXT_MSG",$this->lang);
				}
			}
			$templateDetails['dynaPageTemplatesData_data'] = $this->filePath->replaceParameters($templateDetails['dynaPageTemplatesData_data'], $parameters);
			$file = $this->filePath->getPath("view/globals/dynaPageTemplates.tpl",1);
			if(!$templateDetails['dynaPageTemplatesData_css']){
				$templateDetails['dynaPageTemplatesData_css'] = "";
			}
			if($templateDetails['dynaPageTemplatesDetail_name']){
    			$template_parameters = array(
    				"_CSS"=>$templateDetails['dynaPageTemplatesData_css'],
    				"_HTML"=>$templateDetails['dynaPageTemplatesData_data'],
    				"_NAME"=>$templateDetails['dynaPageTemplatesDetail_name']
    			);
    			
    			$templateContent = $this->filePath->showHTML($file,$template_parameters);
			}else{
			    $templateContent = $templateDetails['dynaPageTemplatesData_data'];
			}
			if($serializeOutput == 1){
				$output .= $templateContent;
			}
		}
		return $output;
	}
	
	public function exportDataToServer($data, $options=array()){
		$insertBy_userId = "";
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
	        
		$processedIds = array();
		$failedIds = array();
		$msg = array();
		$status = array();
		
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		if(!$insertBy_userId){
			$loginArray = $this->loginClass->loginCheck();
			if($loginArray['status'] && $loginArray['userId']){
				$insertBy_userId = $loginArray['userId'];
			}
		}
		$savaDataOptions = array(
			"insertBy_userId"=>$insertBy_userId
		);
		foreach($_POST['dataToExport'] as $serverGroup => $dataToExport){
			$saveResult = $this->dynamicPageModel->saveData($serverGroup, $dataToExport, $savaDataOptions);
			if($saveResult){
			    $status[$serverGroup] = $saveResult['status'];
				$msg[$serverGroup] = $saveResult['msg'];
				if($saveResult['status']){
    			    if($saveResult['processedIds']){
    				    $processedIds = array_merge($processedIds, $saveResult['processedIds']);
    			    }
    			}else{
    			    if($saveResult['processedIds']){
    			        $failedIds = array_merge($failedIds, $saveResult['processedIds']);
    			    }
    			}
			}
		}
		return array("processedIds"=>$processedIds, "failedIds"=>$failedIds, "msg"=>$msg, "status"=>$status);
	}
	public function createPageModules($moduleDetails, $injectionNames=array(), $userGroups=array()){
		$msg = "";
		$status = 1;
		try{
			$moduleJsArray = array();
			$moduleHtmlArray = array();
			$moduleHtmlTemplateFilePath = $this->filePath->getPath("dynaModules/template.tpl", 1);
			$moduleJsTemplateFilePath = $this->filePath->getPath("dynaModules/js.tpl", 1);
			
			foreach($moduleDetails as $module){
				$cssText = "";
				$parametersArray = array();
				$javascriptText = "";
				$htmlText = "";
				
				$moduleFiles = json_decode($module['dynaPageModulesFiles_files'], true);
				$moduleInputParameters = json_decode($module['dynaPageModules_inputParameters'], true);
	
				foreach($moduleFiles as $file){
					$filePath = $this->filePath->getPath("dynaModules/".$module['dynaPageModules_code']."/".$file['dynaPageModulesFiles_fileName'], 1);
					if($file['dynaPageModulesFiles_fileType'] == "css"){
						$cssText .= $this->filePath->showHTML($filePath, array());
					}else if($file['dynaPageModulesFiles_fileType'] == "js"){
						$javascriptText .= $this->filePath->showHTML($filePath, array());
					}else if($file['dynaPageModulesFiles_fileType'] == "html"){
						$htmlText .= $this->filePath->showHTML($filePath, array());
					}
				}
				$cssText = str_replace ( "'", '"', $cssText );
				$cssText = str_replace ( PHP_EOL, '', $cssText );
				
				foreach($moduleInputParameters as $parameter){
					if($parameter['parameterType'] == "="){
						$parametersArray[] = $parameter['parameterName'].": '='";
					}else if($parameter['parameterType'] == '&'){
						$parametersArray[] = $parameter['parameterName'].": '&'";
					}else if($parameter['parameterType'] == "@"){
						$parametersArray[] = $parameter['parameterName'].": '@'";
					}else if($parameter['parameterType'] == "<"){
						$parametersArray[] = $parameter['parameterName'].": '<'";
					}else if($parameter['parameterType'] == "=*"){
						$parametersArray[] = $parameter['parameterName'].": '=*'";
					}else if($parameter['parameterType'] == "<*"){
						$parametersArray[] = $parameter['parameterName'].": '<*'";
					}
				}
				$parametersText = implode(",", $parametersArray);
				
				$moduleFunctionsArray = array();
				$moduleFunctionsOptions = array(
					"moduleId"=>array($module),
					"userGroups"=>$userGroups
				);
				
				$pageModuleFunctions = $this->dynamicPageModel->searchDynaPageModuleFunctions($moduleFunctionsOptions);

				if($pageModuleFunctions['status']){
						$moduleFunctionsArray[] = $this->createPageFunctions($pageModuleFunctions['rows']);
				}
				$moduleFunction = implode(PHP_EOL, $moduleFunctionsArray);

				$injectionsText = "";
				$injectionsText = implode(",", $injectionNames);
				if($injectionsText){
					$injectionsText = ", ".$injectionsText;
				}
				
				$moduleJsTemplateOptions = array(
					"_ID"=>$module['dynaPageModules_moduleId'],
					"_CODE"=>$module['dynaPageModules_code'],
					"_PARAMETERS"=>$parametersText,
					"_CSS"=>$cssText,
					"_JAVASCRIPT"=>$javascriptText,
					"_FUNCTIONS"=>$moduleFunction,
					"_INJECTIONS"=>$injectionsText
				);
				$moduleJsArray[] = $this->filePath->showHTML($moduleJsTemplateFilePath, $moduleJsTemplateOptions);
				
				$moduleHtmlWithLazyLoadOptions = array(
					"_HTML"=>$htmlText
				);
				$moduleHtmlWithLazyLoad = $this->filePath->showHTML($moduleHtmlTemplateFilePath, $moduleHtmlWithLazyLoadOptions);
				$moduleHtmlArray[] = array(
					"dynaPageTemplatesDetail_name"=>"moduleTemplate_".$module['dynaPageModules_moduleId'],
					"dynaPageTemplatesData_data"=>$moduleHtmlWithLazyLoad,
					"dynaPageTemplatesData_css"=>$cssText
				);
			}
			$moduleJs = implode(PHP_EOL, $moduleJsArray);
			$moduleHtml = $this->createPageTemplates($moduleHtmlArray);
			
			$result = array("js"=>$moduleJs, "html"=>$moduleHtml);
		}
		catch(Exception $e){
			$msg = $e->getMessage();
			$status = 0;
			$result = array("js"=>"", "html"=>"");
		}
		return array("result"=>$result, "status"=>$status, "msg"=>$msg);
	}
	public function createInjections($injectionDetails){
		$msg = "";
		$status = 1;
		try{
			$injectionJsArray = array();
			$injectionHtmlArray = array();
			$namesArray = array();

			foreach($injectionDetails as $injection){
				$cssText = "";
				$parametersArray = array();
				$javascriptText = "";
				$htmlText = "";
				
				$namesArray[] = $injection['dynaPageInjections_injectionName'];
				$injectionFiles = json_decode($injection['dynaPageInjections_files'], true);
	
				foreach($injectionFiles as $file){
					$filePath = $this->filePath->getPath("dynaInjections/".$injection['dynaPageInjections_injectionName']."/".$file['dynaPageInjectionsFiles_fileName'], 1);
					if($file['dynaPageInjectionsFiles_fileType'] == "css"){
						$cssText .= $this->filePath->showHTML($filePath, array());
					}else if($file['dynaPageInjectionsFiles_fileType'] == "js"){
						$javascriptText .= $this->filePath->showHTML($filePath, array());
					}else if($file['dynaPageInjectionsFiles_fileType'] == "html"){
						$htmlText .= $this->filePath->showHTML($filePath, array());
					}
				}
				$cssText = str_replace ( "'", '"', $cssText ) ;
				
				if($javascriptText){
					$injectionJsTemplateFilePath = $this->filePath->getPath("dynaInjections/js.tpl", 1);
					$injectionJsTemplateOptions = array(
						"_NAME"=>$injection['dynaPageInjections_injectionName'],
						"_JAVASCRIPT"=>$javascriptText
					);
					$injectionJsArray[] = $this->filePath->showHTML($injectionJsTemplateFilePath, $injectionJsTemplateOptions);
				}
				
				if($htmlText){
					$injectionHtmlArray[] = array(
						"dynaPageTemplatesData_data"=>$htmlText,
						"dynaPageTemplatesData_css"=>$cssText
					);
				}
			}
			
			$result = array("jsArray"=>$injectionJsArray, "htmlArray"=>$injectionHtmlArray, "namesArray"=>$namesArray);
		}
		catch(Exception $e){
			$msg = $e->getMessage();
			$status = 0;
			$result = array("js"=>"", "html"=>"");
		}
		
		return array("result"=>$result, "status"=>$status, "msg"=>$msg);
	}
	
	public function createPageFunctions($functions){
		/**
		*	Creates template of page functions
		*	(array)$functions:{
		*		dynaPageFunction_type
		*		dynaPageFunction_functionId
		*		dynaPageFunction_inParameters
		*		dynaPageFunction_text
		*       dynaModuleId_dynaPageFunctionId_controllerAs
		*	}
		*/
		$functionsTextArray = array();
		foreach($functions as $function){
		    $inputsArray = array();
			$inParametersArray = array();
			
			$function['dynaPageFunction_inParameters'] = json_decode($function['dynaPageFunction_inParameters'], true);
			foreach($function['dynaPageFunction_inParameters'] as $key => $inParameter){
				$inParametersArray[$key] = $inParameter['name'];
				$inputsArray[$key] = $inParameter['name'];
				if(isset($inParameter['default']) && $inParameter['default']){
					$inParametersArray[$key] .= " = ".$inParameter['default'];
				}else if(isset($inParameter['default']) && $inParameter['default'] == ""){
					$inParametersArray[$key] .= ' = ""';
				}
			}
			array_push($inParametersArray, "checkPermissionGlobalParameter=0");
			$inParameters = implode(", ", $inParametersArray);
			$inputs = implode(", ", $inputsArray);
			$functionTemplateFilePath = $this->filePath->getPath("view/globals/dynaPage".$function['dynaPageFunction_type']."Functions.tpl", 1);
			if(!$function['dynaModuleId_dynaPageFunctionId_controllerAs'] && !$function['dynaPageId_dynaPageFunctionId_controllerAs']){
			    $functionScope = '$scope';
			}else{
			    $functionScope = $function['dynaModuleId_dynaPageFunctionId_controllerAs'] ?? $function['dynaPageId_dynaPageFunctionId_controllerAs'];
			}
			$functionTextWithLog = $this->createFunctionDebugLog("logService.setlog", $function['dynaPageFunction_functionId'], $inputs, "start").$function['dynaPageFunction_text'].$this->createFunctionDebugLog("logService.setlog", $function['dynaPageFunction_functionId'], "", "end");
			$functionTemplateOptions = array(
			    "_CONTROLLER_AS"=>$functionScope,
				"_FUNCTION_ID"=>$function['dynaPageFunction_functionId'],
				"_FUNCTION_TEXT"=>$functionTextWithLog,
				"_PARAMETERS_IN"=>$inParameters,
				"_PARAMETERS_IN_NOVALUE"=>$inputs
			);
			$functionsTextArray[] = $this->filePath->showHTML($functionTemplateFilePath, $functionTemplateOptions);
		}
		$functionText = implode(PHP_EOL, $functionsTextArray);
		return $functionText;
	}
	private function createFunctionDebugLog($logService="console.log", $functionName="", $inputs="", $type="start"){
	    $functionId = $functionName;
	    if($type=="start"){
	        $text = ";".$logService."(['Function Name: ".$functionId."::Start', {inputs: [".$inputs."]}], 'debug');";
	    }else{
	        $text = ";".$logService."(['Function Name: ".$functionId."::End'], 'debug');";
	    }

	    return $text;
	}
	public function runServerCommand($commandCode, $parameters, $options, $debug=0){
		$result = array();
		$commandOptions = array(
			"commandsCode"=>array(array("dynaPageCommandData_commandCode"=>$commandCode)),
			"fullCommand"=> array("server")
		);
		if(isset($options['commandIndex'])){
		    $commandOptions["commandsIndex"] = array();
		    foreach($options['commandIndex'] as $commandIndex){
		        array_push($commandOptions["commandsIndex"], array("dynaPageCommandData_commandIndex"=>$commandIndex));
		    }
		}
		if(isset($options['userGroups'])){
			$commandOptions["userGroups"] = $options['userGroups'];
		}
		if(isset($options['userId'])){
			$commandOptions["userId"] = $options['userId'];
		}
		if(!isset($options['pageId'])){
			$options['pageId'] = '';
		}
		if(isset($options['pageId'])){
			$commandOptions["pageId"] = array(array("dynaPageDetails_pageId"=>$options['pageId']));
		}
		$commandDetails = $this->dynamicPageModel->searchCommandDetails($commandOptions, $debug);
		if($commandDetails['status']){
			$status = 1;
			foreach($commandDetails['rows'] as $i => $commandDetail){
				if($commandDetail['dynaPageId_dynaPageCommandDataId_parameters']){
					$commandDetail['dynaPageId_dynaPageCommandDataId_parameters'] = json_decode($commandDetail['dynaPageId_dynaPageCommandDataId_parameters'], true);
					$key = $commandDetail['dynaPageId_dynaPageCommandDataId_parameters'][0]['parameterOut'];
				}else{
					$key = $i;
				}
				$output = $this->dynamicPageModel->runSqlCommand($commandDetail, $parameters, NULL, $debug);
				if($key || count($commandDetails['rows']) > 1){
					$result[$key] = $output;
				}else{
					return $output;
				}
				if(!$result[$key]['status']){
				    $msgCat="SYS_ERR_MSG";
					$msgCode = "One of the commands did not run successfully: ".$key.". More detail: ";
					$msg = $this->langDecode->decode($msgCode, $msgCat, $this->lang);
					$result['msg'] = $msg.$result[$key]['status'];
					$status = 0;
				}
			}
			if(!count($result)){
			    $status = 0;
			    $msgCat="SYS_ERR_MSG";
				$msgCode = "Command list is empty";
				$msg = $this->langDecode->decode($msgCode, $msgCat, $this->lang);
				$result['msg'] = $msg;
			}
		}else{
		    $status = 0;
		    $msgCat="SYS_ERR_MSG";
			$msgCode = "Error while getting command details";
			$msg = $this->langDecode->decode($msgCode, $msgCat, $this->lang);
			$result['msg'] = $msg;
		}
		$result['status'] = $status;
		if($debug){
		    $result['commandDetails'] = $commandDetails;
		    $result['commandOptions'] = $commandOptions;
		    $result['options'] = $options;
		    $result['parameters'] = $parameters;
		}
		if($result['']){
			$result = $result[''];
		}
		return $result;
	}
	public function createDynaPage($options=array()){
	    $response = "";
	    $dynaTemplateOptions = array();
    	$pageTemplateDetails = $this->dynamicPageModel->searchPageTemplatesDetails($dynaTemplateOptions);
    	
    	$pageModules = array("html"=>"", "js"=>"");
    	if($pageTemplateDetails['status']){
    		$pageTempaltes = $this->createPageTemplates($pageTemplateDetails['rows'],1);
    		
    		//Create Modules
    		$pageModulesOption = array(
    			"properties"=>array("files"=>1)
    		);
    		$pageModuleArray = $this->dynamicPageModel->searchPageModules($pageModulesOption);
    		
    		//Create Module Injections
    		$moduleInjectionsOptions = array(
    			"moduleId"=>$pageModuleArray['rows'],
    			"properties"=>array("files"=>1)
    		);
    		$moduleInjectionsArray = $this->dynamicPageModel->searchDynaModuleInjections($moduleInjectionsOptions);
    		$moduleInjectionsArray = $this->createInjections($moduleInjectionsArray["rows"]);
    		$moduleInjections = $moduleInjectionsArray['result'];
    		
    		if($pageModuleArray['status']){
    			$pageModulesArray = $this->createPageModules($pageModuleArray['rows'], $moduleInjections["namesArray"], $userGroups);
    			$pageModules = $pageModulesArray['result'];
    		}
    		//Create Page Injections
    		$pageInjectionsOptions = array(
    			"properties"=>array("files"=>1)
    		);
    		$pageInjectionsArray = $this->dynamicPageModel->searchDynaPageInjections($pageInjectionsOptions);
    		if(!$pageInjectionsArray["status"]){
    			$pageInjectionsArray["rows"] = array();
    		}
    		$pageInjectionsArray = $this->createInjections($pageInjectionsArray["rows"]);
    		$pageInjections = $pageInjectionsArray['result'];
    	}
    	
    	$pageFunctionsOption = array();
    	$pageFunctionsArray = $this->dynamicPageModel->searchDynaPageFunctions($pageFunctionsOption);
    	if($pageFunctionsArray['status']){
    		$pageFunctions = $this->createPageFunctions($pageFunctionsArray['rows']);
    	}
    	
    	$allInjctions = array();
    	$allInjctions['htmlArray'] = array_unique(array_merge($pageInjections['htmlArray'],$moduleInjections['htmlArray']), SORT_REGULAR);
    	$allInjctions['jsArray'] = array_unique(array_merge($pageInjections['jsArray'],$moduleInjections['jsArray']), SORT_REGULAR);
    	
    	$allInjctions['js'] = implode(PHP_EOL, $allInjctions['jsArray']);
    	$allInjctions['html'] = $this->createPageTemplates($allInjctions['htmlArray']);
    	
    	$DynaTemplateParameters = array(
    		"_PAGE_TEMPLATES"=>$pageTempaltes,
    		"_PAGE_MODULE_TEMPLATES"=>$pageModules['html'],
    		"_PAGE_INJECTIONS_TEMPLATES"=>$allInjctions['html']
    	);
    	$dynaTemplateFile = $this->filePath->getPath("view/dynaPage.tpl",1);
    	$pageOtherFooterHtml = $this->filePath->showHTML($dynaTemplateFile, $DynaTemplateParameters);
    	
    	$pageInjectionNames = "";
    	$pageInjectionNames = implode(" ,", $pageInjections['namesArray']);
    	if($pageInjectionNames){
    		$pageInjectionNames = ", ".$pageInjectionNames;
    	}
    
    	$DynaPageJsParameters = array(
    		"_PAGE_MODULES_JS"=>$pageModules['js'],
    		"_PAGE_INJECTIONS_JS"=>$allInjctions['js'],
    		"_PAGE_FUNCTIONS"=>$pageFunctions,
    		"_INJECTIONS"=>$pageInjectionNames
    	);
    	$dynaJsFile = $this->filePath->getPath("javascripts/pages/angular-dynaPage.js",1);
    	$pageJsFooterHtml = $this->filePath->showHTML($dynaJsFile, $DynaPageJsParameters);
    	
    	$templateParameters = array(
    		"PAGE_OTHER_FOOTER_HTML"=>$pageOtherFooterHtml,
    		"PAGE_JS_FOOTER_HTML"=>$pageJsFooterHtml
    	);
        
    	return $templateParameters;
	}
	/*
	//the function below is not needed currently unless they decide to use our database
	public function decodeServerTableCode($options=array()){
		/**
			* Decode a serverTableCode
			* (array) $options{
			*	(array) dynaPageTableMap_serverTables
			* }
		*
		$dynaPageTableMap_serverTables = array();

		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        $tableNames = array();
		foreach($dynaPageTableMap_serverTables as $serverTables){
			if(isset($serverTables['serverTableCode']) && isset($serverTables['serverTable'])){
				if($serverTables['serverTableCode']){
					$tableNames[$serverTables['serverTableCode']] = $serverTables['serverTable'];
				}else{
					$tableNames[$serverTables['serverTable']] = $serverTables['serverTable'];
				}
			}
		}
		return $tableNames;
	}
	
	//TODO: The whole function below should be changed 
	//we do not know hte server structure and how it fetches data-here we assume we have the server data already
	//we just do the mapping
	//We should add another function(connector module) to fetch or insert server data into database in case they want to use our database
	public function decodeDynaPageTableMap_serverToLocal($options=array()){
		/**
			* Decode a pageTable map from local to server
			* (array) $options{
			*	dynaPageTableMap_localTable
			*	(array) dynaPageTableMap_columnMap
			*	(array) dynaPageTableMap_serverTables
			* }
		*
		$dynaPageTableMap_localTable = "";
		$dynaPageTableMap_columnMap = array();
		$dynaPageTableMap_serverTables = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        $tableDecodeOptions = array(
	        	"dynaPageTableMap_serverTables" => $dynaPageTableMap_serverTables
	        );
	        $tableNames = $this->decodeServerTableCode($tableDecodeOptions);
	        
	        $columnList = array(); //Contains list of column to fetched+their nickname
	        $joinOn = array(); //contains list of tables to LEFT join and column names
	        $mainTable = ""; //contain main table to do select from
	        
	        foreach($dynaPageTableMap_columnMap as $map){
	        	if(isset($map['serverTableCode']) && $map['serverTableCode']){
	        		if(is_array($map['columnsMap'])){
		        		foreach($map['columnsMap'] as $columnsMap){
		        			$columnMap = array(
		        				"columnName"=>$tableNames[$map['serverTableCode']].".".$columnsMap['serverColumn'],
		        				"columnNickname"=>$map['serverTableCode'].$columnsMap['localColumn']
		        			);
		        			array_push($columnList, $columnMap);
		        			if(is_array($map['collectionsMap']) && count($map['collectionsMap'])){
		        				foreach($map['collectionsMap'] as $collectionMap){
		        					$collectionMapId = $collectionMap['mapId'];
		        					$joinOn[$collectionMapId] = array("joinTable1"=>$tableNames[$map['serverTableCode']], "joinColumn1"=>$collectionMap['parentColumn'], "joinColumn2"=>$collectionMap['childColumn']);
		        				}
		        			}
		        			
		        			if($map['isMain']){
		        				//set it as main table if 
		        				 $mainTable = $tableNames[$map['serverTableCode']];
		        			}
		        			
		        			//add current table as child table name if a join exists on the map
		        			if(isset($joinOn[$map['mapId']]) && is_array($joinOn[$map['mapId']])){
		        			 	foreach($joinOn[$map['mapId']] as $key=>$element){
		        			 		$joinOn[$map['mapId']][$key]["joinTable2"] = $tableNames[$map['serverTableCode']];
		        			 	}
		        			 }
		        		}
	        		}
	        	}
	        }
	}
	*/
	
}
	
?>