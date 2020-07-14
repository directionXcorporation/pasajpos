<?php
class mainformControllerClass{
	protected $langDecode_constructor, $lang, $templateModel_constructor,$filePath_constructor,$login_constructor, $mainformModel_constructor,$userModel_constructor;
	public function __construct($langDecode_constructor,$lang,$templateModel_constructor,$filePath_constructor, $login_constructor, $mainformModel_constructor,$userModel_constructor) {
	    $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->templateModel= $templateModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->login = $login_constructor;
		$this->mainformModel = $mainformModel_constructor;
		$this->userModel = $userModel_constructor;
	}
	
	public function wrapErrorHandling($pageData="", $fileName="", $pageType="js"){
	    $output = $pageData;
	    $parameters = array(
	        "_DATA"=>$pageData,
	        "_FILENAME"=>$fileName
        );
        $file = $this->filePath->getPath("view/globals/ErrorHandlers/".$pageType.".tpl",1);
        $output = $this->filePath->showHTML($file,$parameters);
        if($output){
	        return $output;
        }else{
            return $pageData;
        }
	}
	public function createPage($pageCode="",$options=array()){
		/**
		* Returns the html of a page
		* $pageCode: Code of page to look for in database
		* $options{
		*	pageLang : The language to override user's language
		*	(array)templateParameters: parameters to be replaced in template file if any
		*	loadInAjax: true to load the menu ans everything in html, false to load only html of content
		*	filePath: relative path to file path and name to /view/en/ folder if does not exist in filedetails table, without beginning slashes
		*	fileParameters: array("p".page_id=>array("parameter"=>value,...),...)
		* }
		* return{
		*	html code of page
		* }
		*/
		
		$pageLang = $this->lang;
		$templateParameters = array();
		$filePath = "";
		$fileParameters = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        if(!$pageLang){
			$pageLang = $this->lang;
		}
		$options = array(
			"lang"=>$pageLang
		);
		$pageJSHeaderFiles = "";
		$pageJSFooterFiles = "";
		$pageCSSHeaderFiles = "";
		$pageCSSFooterFiles = "";
		$pageOtherHeaderFiles = "";
		$pageOtherFooterFiles = "";
		
		$fileParameterArray = array();
		$pageDetails = $this->templateModel->getTemplateDetails($pageCode,$options);
		
		if(isset($pageDetails['status'])){
				$options['pageCodeExplicit'] = 0;
				$pageFiles = $this->templateModel->getPageFiles($pageCode,$options);
				foreach($pageFiles as $pageFiles_element){
					$pageFilesPath = $this->filePath->getPath($pageFiles_element['filePath'],1);
					if($pageFiles_element['hasParameter']){
						if(isset($fileParameters["p".$pageFiles_element['id']])){
							$fileParameterArray = $fileParameters["p".$pageFiles_element['id']];
						}
					}
					$pageFilesContent = $this->filePath->showHTML($pageFilesPath, $fileParameterArray);
					$pageFilesContent = $this->wrapErrorHandling($pageFilesContent, $pageFiles_element['filePath'], $pageFiles_element['fileType']);
					if($pageFiles_element['fileType']=='js'){
					    $pageFilesContent = $this->wrapErrorHandling($pageFilesContent, 'js');
						if($pageFiles_element['addToHead']){
							$pageJSHeaderFiles .= "
							".$pageFilesContent;
						}else{
							$pageJSFooterFiles .= "
							".$pageFilesContent;
						}
					}else if($pageFiles_element['fileType']=='css'){
						if($pageFiles_element['addToHead']){
							$pageCSSHeaderFiles .= $pageFilesContent;
						}else{
							$pageCSSFooterFiles .= $pageFilesContent;
						}
					}else{
						if($pageFiles_element['addToHead']){
							$pageOtherHeaderFiles .= $pageFilesContent;
						}else{
							$pageOtherFooterFiles .= $pageFilesContent;
						}
					}
			    }
		}
		$langOptions = array(
    	        "status"=>1,
    	        "type"=>array(
    	            "rows"=>array(
    	                "text"
    	            )
    	        ),
    	        "availableLanguages"=>1
    	);
		$availableLanguageArray = $this->langDecode->searchDynaLang($langOptions);
		$availableLangsArray = array();
		if($availableLanguageArray['status']){
		    foreach($availableLanguageArray['rows'] as $lang){
        	    array_push($availableLangsArray, "'".$lang['lang']."'");
        	}
		}
		if(!in_array("'en_US'", $availableLangsArray)){
		    array_push($availableLangsArray, "'en_US'");
		}
		$availableLangs = "[";
		$availableLangs .= implode(",", $availableLangsArray);
    	$availableLangs .= "]";
    	$templateParameters['_AVAILABLE_LANGS'] = $availableLangs;
    	
		$templateParameters['PAGE_JS_HEADER_HTML'] = $pageJSHeaderFiles.$templateParameters['PAGE_JS_HEADER_HTML'];
		$templateParameters['PAGE_JS_FOOTER_HTML'] = $pageJSFooterFiles.$templateParameters['PAGE_JS_FOOTER_HTML'];
		$templateParameters['PAGE_CSS_HEADER_HTML'] = $pageCSSHeaderFiles.$templateParameters['PAGE_CSS_HEADER_HTML'];
		$templateParameters['PAGE_CSS_FOOTER_HTML'] = $pageCSSFooterFiles.$templateParameters['PAGE_CSS_FOOTER_HTML'];
		$templateParameters['PAGE_OTHER_HEADER_HTML'] = $pageOtherHeaderFiles.$templateParameters['PAGE_OTHER_HEADER_HTML'];
		$templateParameters['PAGE_OTHER_FOOTER_HTML'] = $pageOtherFooterHTML.$templateParameters['PAGE_OTHER_FOOTER_HTML'];
		
		if(isset($pageDetails['status'])){
			if($pageDetails['status']){
				$templateParameters['PAGE_TITLE'] = $pageDetails['parameters']['PAGE_TITLE'];
				$templateParameters['PAGE_LANGCODE'] = $pageDetails['parameters']['PAGE_LANGCODE'];
				$templateParameters['PAGE_DESCRIPTION'] = $pageDetails['parameters']['PAGE_DESCRIPTION'];
				$templateParameters['PAGE_KEYWORDS'] = $pageDetails['parameters']['PAGE_KEYWORDS'];
				$templateFilePath = $this->filePath->getPath("view/".$pageDetails['fileName'],1);
				$pageTemplateHTML = $this->filePath->showHTML($templateFilePath,$templateParameters);
			}else{
				$pageTemplateHTML = $pageDetails['msg'];
			}
		}else{
			if(isset($filePath)){
				$templateFilePath = $this->filePath->getPath("view/".$filePath,1);
				$pageTemplateHTML = $this->filePath->showHTML($templateFilePath,$templateParameters);
			}else{
				$pageTemplateHTML = "";
			}
		}
		return $pageTemplateHTML;
	}
	public function createMenuHTML($options = array()){
		/**
		* Adds user group to a user
		* (array)$options{
		* 	$userId: userId of the user. Default to current user
		*	$userGroups: groups of this user
		*	$allowedFunctions
		* }
		*/
		$userId = "";
        
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
		}
		
		$menuArray = array();
		$status = 1;
		$msg = "";
		$menuArray2 = array();
		try{
			if(!$userId){
					$loginCheck = $this->login->loginCheck();
					if($loginCheck['status']){
						$userId = $loginCheck['userId'];
					}
			}
			if(!$allowedFunctions){
				if(!$userGroups){
					$userGroups = $this->userModel->getUserGroups($userId);
				}
				$functionOptions = array(
					"userGroups"=>$userGroups,
					"userId"=>$userId
				);
				$allowedFunctions = $this->userModel->getUserFunctions($functionOptions);
			}
			$menuItemsOptions = array(
				"userId"=>$userId,
				"allowedFunctions"=>$allowedFunctions
			);
			$menuItems = $this->mainformModel->getShowableMenuItems($menuItemsOptions);

			$allmenuItems = $this->mainformModel->getMenuItemByCode();
			if($allmenuItems['status']){
				if($menuItems['status']){
					$menuArray1 = $this->buildTree($allmenuItems['menu'], 'parentCode', 'menuCode');
					if($menuArray1['status']){
						$temp = array();
						$menuArray2 = $menuArray1['tree'];
						$menuArray2 = $this->removeNotMatchingRoutes($menuArray2,$menuItems['menu'],'menuCode');
					}else{
						$msg = $menuArray1['msg'];
						$status = 0;
					}
				}else{
					$msg = $menuItems['msg'];
					$status = 0;
				}
			}else{
				$msg = $allmenuItems['msg'];
				$status = 0;
			}
		}
		
		catch(Exception $e) {
			$status = 0;
			$msg = $e->getMessage();
		}
		$menuArray = array(
			"status"=>$status,
			"msg"=>$msg,
			"menu"=>$menuArray2
		);
		return $menuArray;
	}
	
	public function removeNotMAtchingRoutes($fromArray,$comparedToArray,$elementKey){
		foreach($fromArray as $key2 =>$val2){
			if($this->find_key_value($val2, $elementKey, $comparedToArray)){
									
			}else{
				unset($fromArray[$key2]);
			}
			if(isset($fromArray[$key2]['nodes']) && !empty(array_filter($fromArray[$key2]['nodes']))>0){
				$fromArray[$key2]['nodes'] = $this->removeNotMAtchingRoutes($fromArray[$key2]['nodes'],$comparedToArray,$elementKey);
			}
		}
		return array_values($fromArray);
	}
	public function buildTree($flat, $pidKey, $idKey = null, $mainIndex=''){
		$grouped = array();
		$status = 1;
		$msg = "";
		$tree = array();
		
		try{
			foreach ($flat as $sub){
				$grouped[$sub[$pidKey]][] = $sub;
			}
	
			$fnBuilder = function($siblings) use (&$fnBuilder, $grouped, $idKey) {
				foreach ($siblings as $k => $sibling) {
					$id = $sibling[$idKey];
					if(isset($grouped[$id])) {
						$sibling['nodes'] = $fnBuilder($grouped[$id]);
					}
					$siblings[$k] = $sibling;
				}
	
				return $siblings;
			};
	
			$tree = $fnBuilder($grouped[$mainIndex]);
		}catch(Exception $e) {
			$status = 0;
			$msg = $e->getMessage();
		}
		return array("status"=>$status,"tree"=>$tree,"msg"=>$msg);
	}
	public function find_key_value($array, $key, $val_array){
		if (isset($array[$key]) && in_array($array[$key],$val_array)){
	        	return true;
	        }
	        if(isset($array['nodes'])){
			foreach ($array['nodes'] as $item){
			        if (is_array($item) && $this->find_key_value($item, $key, $val_array)){
			        	return true;
			        }
			
			        if (isset($item[$key]) && in_array($item[$key],$val_array)){
			        	return true;
			        }
			}
		}
	
	    return false;
	}
	
	public function getChildrenFor($ary, $id, $parentId='parent_groupId', $childrenId='nodes'){
		$results = array();

		foreach ($ary as $el){
			if ($el[$parentId] == $id){
				$copy = $el;
				unset($copy[$childrenId]); // remove child elements
				$results[] = $copy;
			}
			if (count($el[$childrenId]) > 0 && ($children = $this->getChildrenFor($el[$childrenId], $id)) !== FALSE){
				$results = array_merge($results, $children);
			}
		}
		return $results;
	}
	public function checkFunctionAccess($userId, $functionCode, $options=array()){
		/**
		* Checks if the userId has access to perform functionCode
		* (array)$options{
		*	userGroups
		*	allGroups
		*	targetUserGroups
		*	targetUSerId
		* }
		* return true, false;
		*/
		$userGroups = array();
		$allGroups = '';
		$targetUserGroups = '';
		$targerUserId = '';
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
		}
        $allowed = 0;
        
        if(!$userId){
			$loginArray = $this->login->loginCheck();
			if($loginArray['status'] && $loginArray['userId']){
				$userId = $loginArray['userId'];
			}else{
				$userId = '';
			}
		}
        if(empty(array_filter($userGroups))){
			$userGroups = $this->userModel->getUserGroups($userId);
		}
		
	}
	
	public function checkPermission($userId, $functionCodes=array(),$userGroups=array()){
		$allowed = 0;
		if(!$userId){
			$loginArray = $this->login->loginCheck();
			if($loginArray['status'] && $loginArray['userId']){
				$userId = $loginArray['userId'];
			}else{
				$userId = '';
			}
		}

		if(empty(array_filter($userGroups))){
			$userGroups = $this->userModel->getUserGroups($userId);
		}
		if(!is_array($functionCodes)){
			if($functionCodes){
				$functionCodes = array(
					"functionCode"=>$functionCodes
				);
			}else{
				$functionCodes = array();
			}
		}
		$functionOptions = array(
			"userGroups"=>$userGroups,
			"userId"=>$userId,
			"functionCodes"=>$functionCodes
		);
		$userFunctions = $this->userModel->getUserFunctions($functionOptions);

		foreach($userFunctions as $userFunctionsElement){
			$userFunctionsFlat[] = $userFunctionsElement['functionCode'];
		}
		$functionCodesFlat = array();
		foreach($functionCodes as $functionCodesElement){
			if(isset($functionCodesElement['functionCode'])){
				$functionCodesFlat[] = $functionCodesElement['functionCode'];
			}else{
				$functionCodesFlat[] = $functionCodesElement;
			}
		}

		$result = array_intersect($functionCodesFlat,$userFunctionsFlat);
		if(count($functionCodesFlat)==count($result)){
			$allowed = 1;
		}
		return array("status"=>$allowed);
	}
	
	public function addTableView($viewName, $tableCode, $gridState, $options=array()){
		/**
		* Create a new view for table
		* viewName: name of this view
		* tableCode: code of table that this view belongs to
		* gridState: state of this view
		* (array)options{
		*	insertBy_userId
		*	(array) userGroups: Groups that this user belongs to.  will be added for both groups as well as user if userID entered (array){ (array){'groupId'=>val},{...} }
		*	userId: Id of the user we are trying to save available views for
		* }
		*/
		$insertBy_userId = "";
		$status = 0;
		$msg = "";
		
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
		$viewIdOptions = array(
			'insertBy_userId'=>$insertBy_userId
		);
		$viewIdArray = $this->mainformModel->addTableViewId($viewIdOptions);
		if($viewIdArray['status']){
			$viewId = $viewIdArray['gridsViewDetail_viewId'];
			$viewDetailsOptions = array(
				'insertBy_userId'=>$insertBy_userId
			);
			$viewDetailsArray = $this->mainformModel->addTableViewDetails($viewId, $tableCode, $viewName, $gridState, $viewDetailsOptions);
			if($viewDetailsArray['status']){
				$viewUsersOptions = array(
					'userGroups'=>$userGroups,
					'userId'=>$userId,
					'insertBy_userId'=>$insertBy_userId
				);
				$viewUsersArray = $this->mainformModel->addTableViewUsers($viewId, $viewUsersOptions);
				if($viewUsersArray['status']){
					$viewStatusOptions = array(
						'insertBy_userId'=>$insertBy_userId
					);
					$viewStatusArray = $this->mainformModel->addTableViewStatus($viewId,1,$viewStatusOptions);
					if($viewStatusArray['status']){
						$status = 1;
						$msg = '';
					}else{
						$status = 0;
						$msg = $viewStatusArray['msg'];
					}
				}else{
					$status = 0;
					$msg = $viewUsersArray['msg'];
				}
			}else{
				$status = 0;
				$msg = $viewDetailsArray['msg'];
			}
			
		}else{
			$status = 0;
			$msg = $viewIdArray['msg'];
		}
		return array("status"=>$status,"msg"=>$msg,"gridsViewDetail_viewId"=>$viewId);
	}
	
	public function removeTableView($viewId, $options=array()){
		/**
		* removes a view
		* viewId: id of this view
		* (array)options{
		*	insertBy_userId
		* }
		*/
		$insertBy_userId = "";
		$status = 0;
		$msg = "";
		
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}

		$viewStatusArray = $this->mainformModel->addTableViewStatus($viewId,1,$options);
		if($viewStatusArray['status']){
			$status = 1;
			$msg = '';
		}else{
			$status = 0;
			$msg = $viewStatusArray['msg'];
		}
		return array("status"=>$status,"msg"=>$msg,"gridsViewDetail_viewId"=>$viewId);
	}
}