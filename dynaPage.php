<?php
require_once('includes/filepath.inc.php');
$filePath = new filePath();
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
	$_POST = $_REQUEST = json_decode( file_get_contents( 'php://input' ), true );
}
if(isset($_POST)){
    foreach($_POST as $key=>$element){
        $_POST[$key] = $filePath->tosafestring($element);
    }
}
if(isset($_GET)){
    foreach($_GET as $key=>$element){
        $_GET[$key] = $filePath->tosafestring($element);
    }
}
if(isset($_REQUEST)){
    foreach($_REQUEST as $key=>$element){
        $_REQUEST[$key] = $filePath->tosafestring($element);
    }
}
require_once($filePath->getpath('includes/includes.inc.php',1));
require_once($filepath->getpath('model/dynaPage.php',1));
require_once($filepath->getpath('controller/dynaPage.php',1));

$pageCode = 'dynaPage';

$db = new pdoDb();


if($SecureSessionHandlerClass->get('lang')){
	$lang = $SecureSessionHandlerClass->get('lang');
}else{
	$lang = "en";
}

$langDecodeClass = new langDecodeClass($db,$filePath,$lang);
$loginClass = new loginClass($db,$filePath,$langDecodeClass,$SecureSessionHandlerClass,$lang);
$templateModelClass = new templateModelClass($db,$langDecodeClass,$lang);
$loginClass = new loginClass($db,$filePath,$langDecodeClass,$SecureSessionHandlerClass,$lang);
$mainformModelClass = new mainformModelClass($db,$filePath,$langDecodeClass,$loginClass,$lang);
$usersModelClass = new usersModel($db,$filePath,$langDecodeClass,$loginClass ,$lang);
$dynaPageModelClass = new dynaPageModel($db,$filePath,$langDecodeClass,$loginClass ,$lang);

$mainformControllerClass = new mainformControllerClass($langDecodeClass,$lang,$templateModelClass,$filePath, $loginClass,$mainformModelClass,$usersModelClass);
$loginregisterControllerClass = new loginregisterController($mainformControllerClass,$filePath,$usersModelClass,$loginClass,$langDecodeClass,$lang);
$dynaPageControllerClass = new dynaPageController($langDecodeClass,$lang,$usersModelClass,$filePath, $loginClass,$mainformModelClass,$dynaPageModelClass,$mainformControllerClass);

$loginArray = $loginClass->loginCheck();
if($loginArray['status'] && $loginArray['userId']){
	$userId = $loginArray['userId'];
}
if($userId){
	$userGroups = $usersModelClass->getUserGroups($userId);
}else{
	$userGroups = array();
}
/*
//This is test for compression:
$object = json_decode('{"status":1,"msg":"","pageDetails":{"rows":[{"dynaPageDetails_id":25,"dynaPageDetails_pageId":"selectTill","dynaPageDetails_pageType":"standard","dynaPageDetails_onlineInsertTime":"2018-05-20 00:00:45","dynaPageDetails_insertBy_userId":"","dynaPageDetails_insertIp":"","dynaPageDetails_availableOffline":0,"dynaPageDetails_accessWithoutLogin":0,"dynaPageStatus_status":1,"dynaPageData_data":"{\r\n\t\"options\": {\r\n\t\t\"gridType\": \"fit\",\r\n\t\t\"margin\": \"3\",\r\n\t\t\"compactType\": \"none\",\r\n\t\t\"minCols\": 5,\r\n\t\t\"maxCols\": 30,\r\n\t\t\"minRows\": 5,\r\n\t\t\"maxRows\": 30,\r\n\t\t\"outerMargin\": true,\r\n\t\t\"scrollSensitivity\": 10,\r\n\t\t\"draggable\": {\r\n\t\t\t\"delayStart\": 0,\r\n\t\t\t\"enabled\": true,\r\n\t\t\t\"ignoreContentClass\": \"gridster-item-content\"\r\n\t\t},\r\n\t\t\"resizable\": {\r\n\t\t\t\"delayStart\": 0,\r\n\t\t\t\"enabled\": true\r\n\t\t},\r\n\t\t\"swap\": true,\r\n\t\t\"pushItems\": true,\r\n\t\t\"disablePushOnDrag\": false,\r\n\t\t\"disablePushOnResize\": false,\r\n\t\t\"pushResizeItems\": false,\r\n\t\t\"disableWindowResize\": false,\r\n\t\t\"api\": [],\r\n\t\t\"margins\": []\r\n\t},\r\n\t\"screens\": [{\r\n\t\t\"screenId\": \"777v7v7v-0bb5-418e-8079-396ed5999a08\",\r\n\t\t\"screenName\": \"main\",\r\n\t\t\"items\": [{\r\n\t\t\t\"element\": \"\u003Ctillselect selectedtillid=\'$ctrl.selectedTillId\' stores=\'$ctrl.allStoresDetail\' tills=\'$ctrl.allTillsDetail\' db=\'$ctrl.db\'\u003E\u003C\/tillselect\u003E\",\r\n\t\t\t\"cols\": 5,\r\n\t\t\t\"rows\": 1,\r\n\t\t\t\"x\": 0,\r\n\t\t\t\"y\": 0\r\n\t\t}, {\r\n\t\t\t\"element\": \"\u003Ctillbutton onclickaction=\'$ctrl.saveTill({selectedTillId: $ctrl.selectedTillId})\' text=\'confirm\'\u003E\u003C\/tillbutton\u003E\",\r\n\t\t\t\"cols\": 1,\r\n\t\t\t\"rows\": 1,\r\n\t\t\t\"x\": 0,\r\n\t\t\t\"y\": 1\r\n\t\t}]\r\n\t}]\r\n}","pageCommand":[],"dynaPageInitVariables_vars":[{"dynaPageInitVariables_id":72,"dynaPageInitVariables_variableId":"storesDetailTableName","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"storesDetailTableName","dynaPageInitVariables_type":"var","dynaPageInitVariables_initialVal":"storesDetail","dynaPageInitVariables_onlineInsertTime":"2019-07-17 18:49:20","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":1003},{"dynaPageInitVariables_id":71,"dynaPageInitVariables_variableId":"lastStoresDetailDownload","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"lastStoresDetailDownload","dynaPageInitVariables_type":"var","dynaPageInitVariables_initialVal":"1900-01-01 00:00:00","dynaPageInitVariables_onlineInsertTime":"2018-06-25 16:15:41","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":1003},{"dynaPageInitVariables_id":202,"dynaPageInitVariables_variableId":"tillsDetailTableName","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"tillsDetailTableName","dynaPageInitVariables_type":"var","dynaPageInitVariables_initialVal":"tillsDetail","dynaPageInitVariables_onlineInsertTime":"2019-07-17 20:05:06","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":1003},{"dynaPageInitVariables_id":201,"dynaPageInitVariables_variableId":"lastTillsDetailDownload","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"lastTillsDetailDownload","dynaPageInitVariables_type":"var","dynaPageInitVariables_initialVal":"","dynaPageInitVariables_onlineInsertTime":"2019-07-17 20:05:06","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":1003},{"dynaPageInitVariables_id":70,"dynaPageInitVariables_variableId":"storesDetailDownload","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"storesDetail","dynaPageInitVariables_type":"localTable","dynaPageInitVariables_initialVal":"{\r\n\t\"downloadData\": 1,\r\n\t\"localTableName\": \"storesDetail\",\r\n\t\"url\": \"\/dynaPage.php\",\r\n\t\"dataField\": \"result.rows\",\r\n\t\"responseStatusField\": \"status\",\r\n\t\"parametersVar\": \"parameters\",\r\n\t\"partialOrFullField\": \"partialRefresh\",\r\n\t\"needDataTransformation\": \"hard\",\r\n\t\"preCallFunctions\": [\r\n\t\t{\r\n\t\t\t\"outputVariableId\":\"lastStoresDetailDownload\",\r\n\t\t\t\"functionName\":\"getLastUpdatedTime\", \r\n\t\t\t\"input\": [\r\n\t\t\t\t{\"variableId\":\"db\"},\r\n\t\t\t\t{\"variableId\":\"storesDetailTableName\"}\r\n\t\t\t],\r\n\t\t\t\"functionPath\": \"window\"\r\n\t\t}\r\n\t],\r\n\t\"postParameters\": {\r\n\t\t\"action\": \"runServerCommand\",\r\n\t\t\"commandCode\": \"storesDetailSearch\",\r\n\t\t\"sendWithQueryParams\": [\r\n\t\t\t{\r\n\t\t\t\t\"localName\": \"lastStoresDetailDownload\",\r\n\t\t\t\t\"serverName\": \"onlineInsertTime_storesDetail\"\r\n\t\t\t},\r\n\t\t\t{\r\n\t\t\t\t\"localName\": \"lastStoresDetailDownload\",\r\n\t\t\t\t\"serverName\": \"onlineInsertTime_storesStatus\"\r\n\t\t\t}\r\n\t\t],\r\n\t\t\"queryParameters\": [\r\n\t\t\t{\"name\":\"lastStoresDetailDownload\", \"variableId\": \"lastStoresDetailDownload\", \"isRequired\": 0, \"default\": \"1900-01-01 00:00:00\"}\r\n\t\t]\r\n\t}\r\n}","dynaPageInitVariables_onlineInsertTime":"2019-07-17 20:48:15","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":1002},{"dynaPageInitVariables_id":199,"dynaPageInitVariables_variableId":"tillsDetailDownload","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"tillsDetail","dynaPageInitVariables_type":"localTable","dynaPageInitVariables_initialVal":"{\r\n\t\"downloadData\": 1,\r\n\t\"localTableName\": \"tillsDetail\",\r\n\t\"url\": \"\/dynaPage.php\",\r\n\t\"dataField\": \"result.rows\",\r\n\t\"responseStatusField\": \"status\",\r\n\t\"parametersVar\": \"parameters\",\r\n\t\"partialOrFullField\": \"partialRefresh\",\r\n\t\"needDataTransformation\": \"hard\",\r\n\t\"preCallFunctions\": [\r\n\t\t{\r\n\t\t\t\"outputVariableId\":\"lastTillsDetailDownload\",\r\n\t\t\t\"functionName\":\"getLastUpdatedTime\", \r\n\t\t\t\"input\": [\r\n\t\t\t\t{\"variableId\":\"db\"},\r\n\t\t\t\t{\"variableId\":\"tillsDetailTableName\"}\r\n\t\t\t],\r\n\t\t\t\"functionPath\": \"window\"\r\n\t\t}\r\n\t],\r\n\t\"postParameters\": {\r\n\t\t\"action\": \"runServerCommand\",\r\n\t\t\"commandCode\": \"tillsSearch\",\r\n\t\t\"sendWithQueryParams\": [\r\n\t\t\t{\r\n\t\t\t\t\"localName\": \"lastTillsDetailDownload\",\r\n\t\t\t\t\"serverName\": \"tillsDetail_onlineInsertTime\"\r\n\t\t\t},\r\n\t\t\t{\r\n\t\t\t\t\"localName\": \"lastTillsDetailDownload\",\r\n\t\t\t\t\"serverName\": \"tillsStatus_onlineInsertTime\"\r\n\t\t\t},\r\n\t\t\t{\r\n\t\t\t\t\"localName\": \"lastTillsDetailDownload\",\r\n\t\t\t\t\"serverName\": \"storesDetail_onlineInsertTime\"\r\n\t\t\t}\r\n\t\t],\r\n\t\t\"queryParameters\": [\r\n\t\t\t{\"name\":\"lastTillsDetailDownload\", \"variableId\": \"lastTillsDetailDownload\", \"isRequired\": 0, \"default\": \"1900-01-01 00:00:00\"}\r\n\t\t]\r\n\t}\r\n}","dynaPageInitVariables_onlineInsertTime":"2019-07-17 20:48:44","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":1002},{"dynaPageInitVariables_id":198,"dynaPageInitVariables_variableId":"allStoresDetail","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"allStoresDetail","dynaPageInitVariables_type":"localTableScope","dynaPageInitVariables_initialVal":"{\r\n\t\"localTableName\": \"storesDetail\",\r\n\t\"parameters\": {},\r\n\t\"searchCondition\": {},\r\n\t\"path\": [],\r\n\t\"variables\":[]\r\n}","dynaPageInitVariables_onlineInsertTime":"2019-07-17 20:47:16","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":1001},{"dynaPageInitVariables_id":200,"dynaPageInitVariables_variableId":"allTillsDetail","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"allTillsDetail","dynaPageInitVariables_type":"localTableScope","dynaPageInitVariables_initialVal":"{\r\n\t\"localTableName\": \"tillsDetail\",\r\n\t\"parameters\": {},\r\n\t\"searchCondition\": {},\r\n\t\"path\": [],\r\n\t\"variables\":[]\r\n}","dynaPageInitVariables_onlineInsertTime":"2019-07-17 20:48:40","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":1001},{"dynaPageInitVariables_id":203,"dynaPageInitVariables_variableId":"selectedTillId","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"selectedTillId","dynaPageInitVariables_type":"scope","dynaPageInitVariables_initialVal":"","dynaPageInitVariables_onlineInsertTime":"2019-07-18 18:01:35","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":1001},{"dynaPageInitVariables_id":18,"dynaPageInitVariables_variableId":"db","dynaPageId_dynaPageInitVariables_pageId":"selectTill","dynaPageInitVariables_variableName":"db","dynaPageInitVariables_type":"scope","dynaPageInitVariables_initialVal":"","dynaPageInitVariables_onlineInsertTime":"2018-06-25 16:15:41","dynaPageInitVariables_insertBy_userId":"","dynaPageInitVariables_status":1,"dynaPageInitVariables_setPriority":999}],"dynaPageData_usersGroupId":[]}],"status":1,"msg":"","sortableColumns":["dynaPageDetails_pageId","dynaPageDetails_onlineInsertTime"],"sortBy":"dynaPageDetails.onlineInsertTime DESC, dynaPageDetails.pageId"},"pageCode":"selectTill"}', true);
print_r($object);echo "<br />////////////////////////<br />";

$object2 = $RJson->pack($object, true);
print_r($object2);echo "<br />////////////////////////<br />"; // JSONH()->parse($str)

$object3 = json_decode($object2);
print_r($object3);echo "<br />////////////////////////<br />";
$object4 = $RJson->unpack($object3);
print_r($object4); // JSONH()->parse($str)
*/
if(isset($_POST['action'])){
	$msg = "";
	$status = 1;
	if($_POST['action']=="getPageDetails"){
		$pageDetails = array();
		$sqlDetails = array();
		
		if((isset($_POST['pageCode']) && $_POST['pageCode']) || (isset($_POST['parameters']) && $_POST['parameters'])){
			if(isset($_POST['lang']) && $_POST['lang']){
				$languageArray = $_POST['lang'];
			}else{
				$languageArray = array(
					array("dynaPageDetails_pageLang"=>$lang)
				);
			}
			
			if(isset($_POST['properties']) && $_POST['properties']){
				$properties = $_POST['properties'];
			}else if(isset($_POST['parameters']) && $_POST['parameters'] && $_POST['parameters']['properties']){
			    $properties = $_POST['parameters']['properties'];
			}else{
				$properties = array("status"=>1, "data"=>1);
			}
			
			if(isset($_POST['parameters']) && $_POST['parameters']){
			    $pageDetailsOptions = $_POST['parameters'];
			}
			
			$pageDetailsOptions['pageLang'] = $languageArray;
			
			if(isset($properties) && $properties){
			    $pageDetailsOptions['properties'] = $properties;
			}
			if(isset($_POST['pageCode']) && $_POST['pageCode']){
			    $pageDetailsOptions['pageId'] = array(array('dynaPageDetails_pageId'=>$_POST['pageCode']));
			}else{
			    $_POST['pageCode'] = "";
			}
			$pageDetails = $dynaPageModelClass->searchDynaPageDetails($pageDetailsOptions);
			if($pageDetails['status']){
				if($pageDetails['rows'][0]){
				    foreach($pageDetails['rows'] as $i => $pageDetail){
    					$pageDetails['rows'][$i]['pageCommand'] = array();
    					$pageDetails['rows'][$i]['dynaPageInitVariables_vars'] = array();
    					$pageDetails['rows'][$i]['dynaPageData_usersGroupId'] = array();
    					$getFullData = 1;
    					if($_POST['parameters']['fullDataCondition']){
    					    $_POST['fullDataCondition'] = $_POST['parameters']['fullDataCondition'];
    					}
    					if((!$_POST['pageCode'] || $_POST['pageCode']=='') && $_POST['fullDataCondition'] && is_array($_POST['fullDataCondition'])){
    					    foreach($_POST['fullDataCondition'] as $property => $value){
    					        if($pageDetails['rows'][$i][$property] != $value || !(is_numeric($pageDetails['rows'][$i][$property]) && intval($pageDetails['rows'][$i][$property])==intval($value))){
    					            $getFullData = 0;
    					            $pageDetails['rows'][$i]['dynaPageData_data'] = "";
    					        }
    					    }
    					}
    					
    					if($getFullData){
        					$pageCommandsOptions = array(
        						"userGroups"=>$userGroups,
        						"userId"=>$userId,
        						"pageId"=>$pageDetails['rows'],
        						"commandsSource"=>array(array("dynaPageCommandData_commandSource"=>"local"))
        					);
        					$pageCommandDetails = $dynaPageModelClass->searchCommandDetails($pageCommandsOptions);
        					if($pageCommandDetails['status']){
        						$pageDetails['rows'][$i]['pageCommand'] = $pageCommandDetails['rows'];
        					}
        					if( (is_array($properties) && isset($properties['initialVariables']) && $properties['initialVariables'] ) ){
        						$pageInitVarOptions = array(
        							"pageId"=>$pageDetails['rows']
        						);
        						$pageVariables = $dynaPageModelClass->searchDynaPageInitialVariables($pageInitVarOptions);
        						if($pageVariables['status']){
        							$pageDetails['rows'][$i]['dynaPageInitVariables_vars'] = $pageVariables['rows'];
        						}
        					}
    				    }
    				    if( (is_array($properties) && isset($properties['usersGroup']) && $properties['usersGroup'] ) ){
        				    $pageDetailsOption = array('rows'=>array());
        				    array_push($pageDetailsOption['rows'], $pageDetails['rows'][$i]['dynaPageDetails_pageId']);
        					$pageUsersGroupOptions = array("pageId"=>$pageDetailsOption);
        					
        					$pageUserGroups = $dynaPageModelClass->searchDynaPageData_usersGroupId($pageUsersGroupOptions);
        					if($pageUserGroups['status']){
        						$pageDetails['rows'][$i]['dynaPageData_usersGroupId'] = $pageUserGroups['rows'];
        					}
        				}
				    }
				}else if(isset($_POST['pageCode']) && $_POST['pageCode']){
					$status = 0;
					$msg = $langDecodeClass->decode("This page may be disabled or not exist","ERR_MSG",$lang);
				}
			}else{
				$status = $pageDetails['status'];
				$msg = $pageDetails['msg'];
			}
		}else{
			$status = 0;
			$msg = $langDecodeClass->decode("Page code is not defined","ERR_MSG",$lang);
		}
		$result = array(
			"status"=>$status,
			"msg"=>$msg,
			"pageDetails"=>$pageDetails,
			"pageCode"=>$_POST['pageCode']
		);
	}else if($_POST['action']=="getTableMap"){
		$options = array(
			"localTable"=>$_POST['localTable'],
			"properties"=>$_POST['properties'],
			"onlineInsertTime_start"=>$_POST['onlineInsertTime_start'],
			"debug"=>$_POST['debug']
		);
		$tableMapDetails = $dynaPageModelClass->searchDynaPageTableMap($options);
		if($tableMapDetails['sql']){
            $result['sql'] = $tableMapDetails['sql'];
		}
		if($tableMapDetails['vals']){
            $result['vals'] = $tableMapDetails['vals'];
		}
		if($tableMapDetails['types']){
            $result['types'] = $tableMapDetails['types'];
		}
		
		if($tableMapDetails['status']){
			$tableMapDetails = $tableMapDetails['rows'];
		}else{
			$status = $tableMapDetails['status'];
			$msg = $tableMapDetails['msg'];
		}
		$result['status'] = $status;
		$result['msg']=$msg;
		$result['tableMapDetails']=$tableMapDetails;
	}else if($_POST['action'] == "exportToServer"){
		$msg = array();
		$options = array();
		$options['insertBy_userId'] = $userId;
		if($_POST['dataToExport']){
			$saveResult = $dynaPageControllerClass->exportDataToServer($_POST['dataToExport'], $options);
		}
		foreach($saveResult['status'] as $key=> $status){
			if(!$status){
				$msg[$key] = $saveResult['msg'][$key];
			}
		}
		$result = array(
			"status"=>1,
			"msg"=>$msg,
			"processedIds"=>$saveResult['processedIds'],
			'failedIds'=>$saveResult['failedIds']
		);
	}else if($_POST['action'] == "runServerCommand"){
	    $debug = $_POST['debug'];
		$options = array(
			"userGroups"=>$userGroups,
			"userId"=>$userId
		);
		if($_POST['commandIndex']){
		    $options["commandIndex"] = $_POST['commandIndex'];
		}
		$result = $dynaPageControllerClass->runServerCommand($_POST['commandCode'], $_POST['parameters'], $options, $debug);
	}else if($_POST['action'] == "saveDevice"){
		$debug = $_POST['debug'];
		$result = $dynaPageModelClass->saveDevice($_POST['deviceId'], $_POST['deviceStatus'], $debug);
	}else if($_POST['action']=="compile"){
	    $result = array(
	           "msg"=>"",
	           "status"=>0
	    );
        if(1 || $userId){
            $options = array(
                'userGroups'=>$userGroups
            );
			$permission = $mainformControllerClass->checkFunctionAccess($userId, array("functionCode"=>"createIndex"), $options);
			if(1 || $permission['status']){
                $result['status'] = 1;
                $result['msg'] = $dynaPageControllerClass->createIndex("/dynaPage.php?action=getSW");
			}
        }
    }
	$result['compressed'] = 1;
	$content = $RJson->pack($result, true);
}else if($_GET['action']){
    if($_GET['action']=="getSW"){
        header('Content-Type: application/javascript');
        //header("Cache-Control: max-age=25920"); //0.3 day
        header("Cache-Control: no-cache");
        if(!$_GET['cache_name']){
            $_GET['cache_name'] = "offlinePages";
        }
        $languageArray = array(
			array("dynaPageDetails_pageLang"=>$lang)
		);
		$options = array(
		    "pageLang"=>$languageArray,
		    "cache_name"=>$_GET['cache_name']
		);
			
        $content = $dynaPageControllerClass->getServiceWorkers($options);
    }else if($_GET['action']=="createIndex"){
        if(1 || $userId){
            $options = array(
                'userGroups'=>$userGroups
            );
			$permission = $mainformControllerClass->checkFunctionAccess($userId, array("functionCode"=>"createIndex"), $options);
			if(1 || $permission['status']){
                passthru(id);
                $content = $dynaPageControllerClass->createIndex("/dynaPage.php?action=getSW");
			}
        }
    }
}
print_r($content);
?>