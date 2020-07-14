<?php

class dynaPageModel{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor, $lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
		$this->lang = $lang;
	}
	public function saveDevice($deviceId, $deviceStatus, $debug){
		try{
			if(!$insertBy_userId){
				$loginCheck = $this->loginClass->loginCheck();
				if($loginCheck['status']){
					$insertBy_userId = $loginCheck['userId'];
				}
			}
		
	        $col = array(
	        	"deviceId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"deviceId"=>$deviceId,
	        	"status"=>$deviceStatus,
	            "insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"deviceId"=>"s",
	        	"status"=>"i",
				"insertBy_userId"=>"s",
				"insertIp"=>"s"
	        );
			$result = $this->db->pdoInsert("deviceDetails",$col,$val,$type);
			if(isset($result['status'])){
				if($result['status']>0){
					$status = 1;
					$msgCode = "Device Id updated successfuly";
					$msgCat = "OK_MSG";
					$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
				}else{
					$status = 0;
					$msgCode = "There was a system error updating device id. Please try again. Administrator is informed of the problem.EX135";
					$msgCat="SYS_ERR_MSG";
					$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
				}
			}else{
				$status = 0;
				$msgCode = "There was a system error updating device id. Please try again. Administrator is informed of the problem.EX141";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}
		catch(Exception $e) {
		    $status = 0;
			$msgCode = $e->getMessage();
			$msgCat = "SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode, $msgCat, $this->lang);
		}
		
		return array("status"=>$status, "msg"=>$msg, "deviceId"=>$deviceId);
	}
	public function saveData($serverTableName, $data, $options=array()){
		/**
		* Save local data to server
		* serverTable: name of server table
		* (array) data{
		*	data of the page in form of array(array("columnName"=>value,...),...)
		* }
		* (array) options{
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	id: id of this record
		* }
		*/
		try{
    		$insertBy_userId = "";
    		foreach($options as $optionsKey=>$optionsElement){
    	            ${$optionsKey} = $optionsElement;
    	        }
    		if(!$insertBy_userId){
    			$loginCheck = $this->loginClass->loginCheck();
    			if($loginCheck['status']){
    				$insertBy_userId = $loginCheck['userId'];
    			}
    		}
    		$col = array();
            $val = array();
            $type = array();
            $processedIds = array();
    	        
            foreach($data as $key=> $element){
    	        	$valElement = array();
    	        	$processedIds[] = $element['dataId'];
    	        	$rowServerTableName = $element['serverGroupName'];
    	        	if($rowServerTableName == $serverTableName){
            			foreach($element['exportData'] as $colName=>$value){
            				if($key==0){
            					array_push($col, $colName);
            					$type[$colName] = "s";
            				}
            				$valElement[$colName] = $value;
            			}
            			$valElement['insertBy_userId'] = $insertBy_userId;
            			$valElement['insertIp'] = $this->filePath->getUserIp();
            			$val[] = $valElement;
    	        	}
    		}
    		if(count($val) > 0){
        		array_push($col, "insertBy_userId");
        		$type["insertBy_userId"] = "s";
        		array_push($col, "insertIp");
        		$type["insertIp"] = "s";
    
    			$result = $this->db->pdoInsert($serverTableName, $col, $val, $type);
    			$status = $result['status'];
				$msgCode = $result['msg'];
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode, $msgCat, $this->lang);
    		}else{
    		    $status = 1;
				$msgCode = "No data to upload";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode, $msgCat, $this->lang);
    		}
		}
		catch(Exception $e) {
		    $status = 0;
			$msgCode = $e->getMessage();
			$msgCat = "SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode, $msgCat, $this->lang);
		}
		
		return array("serverTableName"=>$serverTableName, "status"=>$status, "msg"=>$msg, "processedIds"=>$processedIds);
	}
	public function addDynaPageData($pageId, $options){
		/**
		* Add/Change page Details
		* pageId: Id of page to change
		* (array) options{
		*	data: data of the page
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	id: id of this record
		* }
		*/
		$data = "";
		$insertBy_userId = "";
		$id = '';
        
	        foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
	        $col = array(
	        	"pageId",
	        	"data",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"pageId"=>$pageId,
	        	"data"=>$data,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"pageId"=>"s",
	        	"data"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("dynaPageData",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Page data updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating page data details. Please try again. Administrator is informed of the problem.EX135";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating page data details. Please try again. Administrator is informed of the problem.EX141";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("pageId"=>$pageId,"status"=>$status,"id"=>$id,"msg"=>$msg);
	}
	
	public function searchDynaPageDetails($options=array()){
    		/**
			* Search among dynamic page data table
			* $options {
			*	(array)pageId: an array of page ids in form of array(array('dynaPageDetails_pageId'=>value),...)
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	availableOffline: {-1: all, 0: pages not available offine, 1: pages availabel offline}, default: -1
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status of brand. Default to 1:Active
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageDetails.onlineInsertTime DESC
			*	countOnly: ony return count if 1. default to 0
			*	(array) properties array("propertyName"=>1,...)
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageDetails_id
			*	dynaPageDetails_pageId
			*	dynaPageDetails_pageType
			*	dynaPageDetails_availableOffline
			*	dynaPageDetails_insertBy_userId
			*	dynaPageDetails_insertIp
			*	dynaPageDetails_onlineInsertTime
			*	dynaPageStatus_status
			*	dynaPageData_data
			* }
		*/
		$pageId = array();
		$insertBy_userId = array();
		$availableOffline = -1;
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$properties = array("status"=>1);
		$status = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " dynaPageDetails.onlineInsertTime DESC, dynaPageDetails.pageId  ";
		$countOnly = 0;
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        if($countOnly){
        		$sql = "SELECT COUNT(DISTINCT(dynaPageDetails.id)) AS count ";
        	}else{
		        $sql = "SELECT dynaPageDetails.id AS `dynaPageDetails_id`, dynaPageDetails.pageId AS `dynaPageDetails_pageId`,  dynaPageDetails.pageType AS `dynaPageDetails_pageType`,  
		        	dynaPageDetails.onlineInsertTime AS `dynaPageDetails_onlineInsertTime`, dynaPageDetails.insertBy_userId AS `dynaPageDetails_insertBy_userId`, dynaPageDetails.insertIp AS `dynaPageDetails_insertIp`, dynaPageDetails.availableOffline AS `dynaPageDetails_availableOffline`, 
		        	dynaPageDetails.accessWithoutLogin AS `dynaPageDetails_accessWithoutLogin` 
		        	";
		}
		
		if( ($status>-1) || (is_array($properties) && isset($properties['status']) && $properties['status'] ) ){
			$sql .= " ,dynaPageStatus.status AS `dynaPageStatus_status` ";
		}
		if( (is_array($properties) && isset($properties['data']) && $properties['data'] ) ){
			$sql .= " ,dynaPageData.data AS `dynaPageData_data` ";
		}
		
		$sql .=" FROM dynaPageDetails 
	        		LEFT JOIN dynaPageDetails AS dynaPageDetails2 ON dynaPageDetails.pageId = dynaPageDetails2.pageId AND dynaPageDetails2.onlineInsertTime GREATERTHAN dynaPageDetails.onlineInsertTime ";
	        		
	        if( ($status>-1) || (is_array($properties) && isset($properties['status']) && $properties['status'] ) ){
		        $sql .= " INNER JOIN dynaPageStatus ON dynaPageStatus.pageId = dynaPageDetails.pageId 
		        	LEFT JOIN dynaPageStatus AS dynaPageStatus2 ON dynaPageStatus2.pageId = dynaPageStatus.pageId AND dynaPageStatus2.onlineInsertTime GREATERTHAN dynaPageStatus.onlineInsertTime ";
	        }
	        
	        if( (is_array($properties) && isset($properties['data']) && $properties['data'] ) ){
		        $sql .= " INNER JOIN dynaPageData ON dynaPageData.pageId = dynaPageDetails.pageId 
		        	LEFT JOIN dynaPageData AS dynaPageData2 ON dynaPageData2.pageId = dynaPageData.pageId AND dynaPageData2.onlineInsertTime GREATERTHAN dynaPageData.onlineInsertTime ";
	        }
            
            if( (is_array($properties) && isset($properties['initialVariables']) && $properties['initialVariables'] ) ){
                $sql .= " LEFT JOIN dynaPageId_dynaPageInitVariables ON dynaPageId_dynaPageInitVariables.pageId = dynaPageDetails.pageId 
                        LEFT JOIN dynaPageId_dynaPageInitVariables AS dynaPageId_dynaPageInitVariables2 ON dynaPageId_dynaPageInitVariables.pageId = dynaPageId_dynaPageInitVariables2.pageId AND dynaPageId_dynaPageInitVariables.variableId = dynaPageId_dynaPageInitVariables2.variableId AND dynaPageId_dynaPageInitVariables2.onlineInsertTime > dynaPageId_dynaPageInitVariables.onlineInsertTime  ";
                        
                $sql .= " LEFT JOIN dynaPageInitVariables ON dynaPageInitVariables.variableId = dynaPageId_dynaPageInitVariables.variableId 
                        LEFT JOIN dynaPageInitVariables AS dynaPageInitVariables2 ON dynaPageInitVariables.variableId = dynaPageInitVariables2.variableId AND dynaPageInitVariables2.onlineInsertTime > dynaPageInitVariables.onlineInsertTime ";
            }
            
            if( (is_array($properties) && isset($properties['usersGroup']) && $properties['usersGroup'] ) ){
                $sql .= " LEFT JOIN dynaPageData_usersGroupId ON dynaPageData_usersGroupId.pageId = dynaPageDetails.pageId 
                        LEFT JOIN dynaPageData_usersGroupId AS dynaPageData_usersGroupId2 ON dynaPageData_usersGroupId.pageId = dynaPageData_usersGroupId2.pageId AND dynaPageData_usersGroupId.groupId = dynaPageData_usersGroupId2.groupId AND dynaPageData_usersGroupId2.onlineInsertTime > dynaPageData_usersGroupId.onlineInsertTime  ";
            }
            
            if( (is_array($properties) && isset($properties['injections']) && $properties['injections'] ) ){
                $sql .= " LEFT JOIN dynaPageId_dynaPageInjections ON dynaPageId_dynaPageInjections.pageId = dynaPageDetails.pageId 
                        LEFT JOIN dynaPageId_dynaPageInjections AS dynaPageId_dynaPageInjections2 ON dynaPageId_dynaPageInjections.pageId = dynaPageId_dynaPageInjections2.pageId AND dynaPageId_dynaPageInjections.injectionName = dynaPageId_dynaPageInjections2.injectionName AND dynaPageId_dynaPageInjections2.onlineInsertTime > dynaPageId_dynaPageInjections.onlineInsertTime  ";
            }
            
            if( (is_array($properties) && isset($properties['modules']) && $properties['modules'] ) ){
                $sql .= " LEFT JOIN dynaPageId_dynaPageModules ON dynaPageId_dynaPageModules.pageId = dynaPageDetails.pageId 
                        LEFT JOIN dynaPageId_dynaPageModules AS dynaPageId_dynaPageModules2 ON dynaPageId_dynaPageModules.pageId = dynaPageId_dynaPageModules2.pageId AND dynaPageId_dynaPageModules.moduleId = dynaPageId_dynaPageModules2.moduleId AND dynaPageId_dynaPageModules2.onlineInsertTime > dynaPageId_dynaPageModules.onlineInsertTime  ";
            }
            
	        $sql .= " WHERE dynaPageDetails2.id IS NULL ";
	        
	        if( (is_array($properties) && isset($properties['data']) && $properties['data'] ) ){
	        	$sql .= " AND ( (dynaPageData.id IS NULL) OR (dynaPageData.id IS NOT NULL AND dynaPageData2.id IS NULL) ) ";
	        }
            
            if( (is_array($properties) && isset($properties['initialVariables']) && $properties['initialVariables'] ) ){
                $sql .= "  AND ( (dynaPageId_dynaPageInitVariables.pageId IS NULL) OR (dynaPageId_dynaPageInitVariables.pageId IS NOT NULL AND dynaPageId_dynaPageInitVariables2.pageId IS NULL) )  ";
                
                $sql .= " AND ( (dynaPageInitVariables.variableId IS NULL) OR (dynaPageInitVariables.variableId IS NOT NULL AND dynaPageInitVariables2.variableId IS NULL) ) ";
            }
            
            if( (is_array($properties) && isset($properties['usersGroup']) && $properties['usersGroup'] ) ){
	        	$sql .= " AND ( (dynaPageData_usersGroupId.id IS NULL) OR (dynaPageData_usersGroupId.id IS NOT NULL AND dynaPageData_usersGroupId2.id IS NULL) ) ";
	        }
	        
	        if( (is_array($properties) && isset($properties['injections']) && $properties['injections'] ) ){
	        	$sql .= " AND ( (dynaPageId_dynaPageInjections.id IS NULL) OR (dynaPageId_dynaPageInjections.id IS NOT NULL AND dynaPageId_dynaPageInjections2.id IS NULL) ) ";
	        }
	        
	        if( (is_array($properties) && isset($properties['modules']) && $properties['modules'] ) ){
	        	$sql .= " AND ( (dynaPageId_dynaPageModules.id IS NULL) OR (dynaPageId_dynaPageModules.id IS NOT NULL AND dynaPageId_dynaPageModules2.id IS NULL) ) ";
	        }
            
	        //Search by ids
	        if(!empty(array_filter($pageId))>0){
	        	$sql .= " AND (dynaPageDetails.pageId = :pageId ";
	        	$vals[':pageId'] = $pageId[0]['dynaPageDetails_pageId'];
	        	$types[':pageId'] = "s";
	        	foreach($pageId as $key=>$element){
	        		$sql .= " OR dynaPageDetails.pageId = :pageId".$key;
	        		$vals[':pageId'.$key] = $element['dynaPageDetails_pageId'];
	        		$types[':pageId'.$key] = "s";
	        	}
	        	$sql .= ") ";
	        }

	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE_FORMAT(dynaPageDetails.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start, '%Y-%m-%d %H:%i:%s') ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d H:i:s');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";
	        	
	        	if( (is_array($properties) && isset($properties['initialVariables']) && $properties['initialVariables'] ) ){
    	        	$sql .= " OR DATE_FORMAT(dynaPageInitVariables.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start_dynaPageInitVariables, '%Y-%m-%d %H:%i:%s') ";
    	        	$vals[':onlineInsertTime_start_dynaPageInitVariables'] = $onlineInsertTime_start;
    	        	$types[':onlineInsertTime_start_dynaPageInitVariables'] = "s";
    	        	
    	        	$sql .= " OR DATE_FORMAT(dynaPageId_dynaPageInitVariables.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start_dynaPageId_dynaPageInitVariables, '%Y-%m-%d %H:%i:%s') ";
    	        	$vals[':onlineInsertTime_start_dynaPageId_dynaPageInitVariables'] = $onlineInsertTime_start;
    	        	$types[':onlineInsertTime_start_dynaPageId_dynaPageInitVariables'] = "s";
	        	}
	        	if( ($status>-1) || (is_array($properties) && isset($properties['status']) && $properties['status'] ) ){
    	        	$sql .= " OR DATE_FORMAT(dynaPageStatus.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start_status, '%Y-%m-%d %H:%i:%s') ";
    	        	$vals[':onlineInsertTime_start_status'] = $onlineInsertTime_start;
    	        	$types[':onlineInsertTime_start_status'] = "s";
	        	}
	        	if((is_array($properties) && isset($properties['data']) && $properties['data'] )){
	        	    $sql .= " OR DATE_FORMAT(dynaPageData.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start_data, '%Y-%m-%d %H:%i:%s') ";
	        	    $vals[':onlineInsertTime_start_data'] = $onlineInsertTime_start;
	        	    $types[':onlineInsertTime_start_data'] = "s";
	        	}
	        	
	        	if((is_array($properties) && isset($properties['usersGroup']) && $properties['usersGroup'] )){
	        	    $sql .= " OR DATE_FORMAT(dynaPageData_usersGroupId.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start_usersGroup, '%Y-%m-%d %H:%i:%s') ";
	        	    $vals[':onlineInsertTime_start_usersGroup'] = $onlineInsertTime_start;
	        	    $types[':onlineInsertTime_start_usersGroup'] = "s";
	        	}
	        	if((is_array($properties) && isset($properties['injections']) && $properties['injections'] )){
	        	    $sql .= " OR DATE_FORMAT(dynaPageId_dynaPageInjections.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start_injections, '%Y-%m-%d %H:%i:%s') ";
	        	    $vals[':onlineInsertTime_start_injections'] = $onlineInsertTime_start;
	        	    $types[':onlineInsertTime_start_injections'] = "s";
	        	}
	        	if((is_array($properties) && isset($properties['modules']) && $properties['modules'] )){
	        	    $sql .= " OR DATE_FORMAT(dynaPageId_dynaPageModules.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start_modules, '%Y-%m-%d %H:%i:%s') ";
	        	    $vals[':onlineInsertTime_start_modules'] = $onlineInsertTime_start;
	        	    $types[':onlineInsertTime_start_modules'] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(dynaPageDetails.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";
	        }
	        
	        if($status>-1){
	        	$sql .= " AND dynaPageStatus.status = :status AND dynaPageStatus2.id IS NULL ";
	        	$vals[':status'] = $status;
	        	$types[':status'] = "i";
	        }
	        
	        if($availableOffline>-1){
	        	$sql .= " AND dynaPageDetails.availableOffline = :availableOffline ";
	        	$vals[':availableOffline'] = $availableOffline;
	        	$types[':availableOffline'] = "i";
	        }
	        
	        if(!$countOnly){
			$sql .= " GROUP BY dynaPageDetails.pageId ";
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageDetails_pageId",
	        	"dynaPageDetails_onlineInsertTime"
	        );
	        $results['sortBy'] = trim($orderBy);
	        
	        return $results;
	}
	
	public function searchDynaPageData_usersGroupId($options=array()){
	    /**
			* Search table
			* Options {
			*	(array) $pageId: {fieldName: 'e.g dynaPageDetails_pageId', rows:[{dynaPageDetails_pageId: 123345},...]} The page code that user is accessing. empty for all pages
			*	(array) $groupId: {fieldName: 'e.g groupsDetail_groupId', rows:[{groupsDetail_groupId: 123345},...]}
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	msg: message decoded in user lang
			* }
		*/
		$countOnly = 0;
		$pageId = array();
		$groupId = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " dynaPageData_usersGroupId.onlineInsertTime DESC ";
		
		$vals = array();
		$types = array();
		
		$msg = "";
		$results = array();
		
		try{
			foreach($options as $optionsKey=>$optionsElement){
                ${$optionsKey} = $optionsElement;
    		}
            $sql = "SELECT 
                    MAX(dynaPageData_usersGroupId.id) AS `dynaPageData_usersGroupId_id`, 
                    MAX(dynaPageData_usersGroupId.pageId) AS `dynaPageData_usersGroupId_pageId`, 
                    MAX(dynaPageData_usersGroupId.groupId) AS `dynaPageData_usersGroupId_groupId`, 
                    MAX(dynaPageData_usersGroupId.status) AS `dynaPageData_usersGroupId_status`, 
                    MAX(dynaPageData_usersGroupId.onlineInsertTime) AS `dynaPageData_usersGroupId_onlineInsertTime`, 
                    MAX(dynaPageData_usersGroupId.insertBy_userId) AS `dynaPageData_usersGroupId_insertBy_userId`, 
                    MAX(dynaPageData_usersGroupId.insertIp) AS `dynaPageData_usersGroupId_insertIp` 
		        	";
            
            $sql .= " FROM dynaPageData_usersGroupId ";   	
            $sql .= " LEFT JOIN dynaPageData_usersGroupId AS dynaPageData_usersGroupId2 ON dynaPageData_usersGroupId.pageId = dynaPageData_usersGroupId2.pageId AND dynaPageData_usersGroupId.groupId = dynaPageData_usersGroupId2.groupId AND 
                    dynaPageData_usersGroupId2.onlineInsertTime GREATERTHAN dynaPageData_usersGroupId.onlineInsertTime ";
            
            $sql .= " WHERE dynaPageData_usersGroupId2.pageId IS NULL ";
		    
		    if(isset($pageId) && isset($pageId['rows']) && !empty(array_filter($pageId['rows']))>0){
	        	$sql .= " AND (dynaPageData_usersGroupId.pageId = :pageId ";
	        	if(isset($pageId['fieldName']) && $pageId['fieldName']){
	        	    $fieldName = $pageId['fieldName'];
	        	    $vals[':pageId'] = $pageId['rows'][0][$fieldName];
	        	}else{
	        	    $vals[':pageId'] = $pageId['rows'][0];
	        	}
	        	$types[':pageId'] = "s";
	        	foreach($pageId['rows'] as $key=>$element){
	                if(isset($pageId['fieldName']) && $pageId['fieldName']){
    	        	    $fieldName = $pageId['fieldName'];
    	        	    $vals[':pageId'.$key] = $element[$fieldName];
    	        	}else{
    	        	    $vals[':pageId'.$key] = $element;
    	        	}
	        		$sql .= " OR dynaPageData_usersGroupId.pageId = :pageId".$key;
	        		$types[':pageId'.$key] = "s";
	        	}
	        	$sql .= ") ";
	        }
	        
	        if(isset($groupId) && isset($groupId['rows']) && !empty(array_filter($groupId['rows']))>0){
	        	$sql .= " AND (dynaPageData_usersGroupId.groupId = :groupId ";
	        	if(isset($groupId['fieldName']) && $groupId['fieldName']){
	        	    $fieldName = $groupId['fieldName'];
	        	    $vals[':groupId'] = $groupId['rows'][0][$fieldName];
	        	}else{
	        	    $vals[':groupId'] = $groupId['rows'][0];
	        	}
	        	
	        	$types[':groupId'] = "s";
	        	foreach($groupId['rows'] as $key=>$element){
	        	    if(isset($groupId['fieldName']) && $groupId['fieldName']){
    	        	    $fieldName = $groupId['fieldName'];
    	        	    $vals[':groupId'.$key] = $element[$fieldName];
    	        	}else{
    	        	    $fieldName = $key;
    	        	    $vals[':groupId'.$key] = $element;
    	        	}
	        		$sql .= " OR dynaPageData_usersGroupId.groupId = :groupId".$key;
	        		
	        		$types[':groupId'.$key] = "s";
	        	}
	        	$sql .= ") ";
	        }
	        
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE_FORMAT(dynaPageData_usersGroupId.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start, '%Y-%m-%d %H:%i:%s') ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d H:i:s');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";
	
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(dynaPageData_usersGroupId.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";
	        }
	        
	        if($status>-1){
	        	$sql .= " AND dynaPageData_usersGroupId.status = :status ";
	        	$vals[':status'] = $status;
	        	$types[':status'] = "i";
	        }
	        
	        if(!$countOnly){
	            $sql .= " GROUP BY dynaPageData_usersGroupId.pageId, dynaPageData_usersGroupId.groupId ";
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }
	        
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageData_usersGroupId_pageId",
	        	"dynaPageData_usersGroupId_onlineInsertTime"
	        );
	        $results['sortBy'] = trim($orderBy);
		}
		catch(Exception $e) {
			$msg = $e->getMessage();
			$results = array("status"=>0, "msg"=>$msg, "rows"=>array(), "sortBy"=>trim($orderBy), "sortableColumns"=>array());
		}
		return $results;
	}
	public function searchDynaModuleInjections($options=array()){
		/**
			* Search among dynamic page Injections/Services
			* $options {
			*	(array)moduleId: array(array('dynaPageModules_moduleId'=>id),...)
			*	injectionStatus: status of variable. Default to 1:Active
			*	injection_module_status: status of variable-page. Default to 1:Active
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageId_dynaPageInjections.onlineInsertTime DESC
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageInjections_id
			*	dynaPageInjections_injectionName
			*	dynaModuleId_dynaPageInjections_moduleId
			*	dynaPageInjections_type
			*	dynaPageInjections_onlineInsertTime
			*	dynaPageInjections_insertBy_userId
			*	dynaPageInjections_status
			* }
		*/
		$moduleId = array();
		$injectionStatus = 1;
		$injection_module_status = 1;
		$limit = -1;
		$start = 0;
		$properties = array();
		$orderBy = " dynaModuleId_dynaPageInjections.onlineInsertTime DESC ";
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        $sql = "SELECT dynaPageInjections.id AS `dynaPageInjections_id`, dynaPageInjections.injectionName AS `dynaPageInjections_injectionName`, 
	        	dynaPageInjections.type AS `dynaPageInjections_type`, 
	        	dynaModuleId_dynaPageInjections.moduleId AS `dynaModuleId_dynaPageInjections_moduleId`, 
	        	dynaPageInjections.onlineInsertTime AS `dynaPageInjections_onlineInsertTime`, 
	        	dynaPageInjections.insertBy_userId AS `dynaPageInjections_insertBy_userId`, 
	        	dynaPageInjections.status AS `dynaPageInjections_status` ";
	        
	        if( (is_array($properties) && isset($properties['files']) && $properties['files'] ) ){
			$sql .= "
				, CONCAT(
				        '[',
				        COALESCE(
				            GROUP_CONCAT(
				                DISTINCT(CONCAT(
				                    '{',
				                    '\"dynaPageInjectionsFiles_fileName\": \"', dynaPageInjectionsFiles.fileName, '\", ',
				                    '\"dynaPageInjectionsFiles_fileType\": \"', dynaPageInjectionsFiles.fileType, '\"',
				                    '}') 
				                ) 
				                ORDER BY dynaPageInjectionsFiles.fileType ASC 
				                SEPARATOR ','),
				            ''),
				        ']'
				) AS `dynaPageInjections_files` 
			";
		}
	        	
	        $sql .= " FROM dynaPageInjections 
	        	LEFT JOIN dynaPageInjections AS dynaPageInjections2 ON dynaPageInjections2.injectionName = dynaPageInjections.injectionName AND dynaPageInjections2.onlineInsertTime GREATERTHAN dynaPageInjections.onlineInsertTime ";
	        
	        if(!empty(array_filter($moduleId))>0){
	        	$sql .= " INNER JOIN ";
	        }else{
	        	$sql .= " LEFT JOIN ";
	        }
		$sql .= " dynaModuleId_dynaPageInjections ON dynaModuleId_dynaPageInjections.injectionName = dynaPageInjections.injectionName 
			LEFT JOIN dynaModuleId_dynaPageInjections AS dynaModuleId_dynaPageInjections2 ON dynaModuleId_dynaPageInjections.injectionName = dynaModuleId_dynaPageInjections2.injectionName 
			AND dynaModuleId_dynaPageInjections.moduleId = dynaModuleId_dynaPageInjections2.moduleId AND dynaModuleId_dynaPageInjections2.onlineInsertTime GREATERTHAN dynaModuleId_dynaPageInjections.onlineInsertTime ";
		if( (is_array($properties) && isset($properties['files']) && $properties['files'] ) ){
    		$sql .= " LEFT JOIN dynaPageInjectionsFiles ON dynaPageInjectionsFiles.injectionName = dynaPageInjections.injectionName 
    			LEFT JOIN dynaPageInjectionsFiles AS dynaPageInjectionsFiles2 ON dynaPageInjectionsFiles.injectionName = dynaPageInjectionsFiles2.injectionName AND 
    			dynaPageInjectionsFiles.fileName = dynaPageInjectionsFiles2.fileName AND dynaPageInjectionsFiles2.onlineInsertTime GREATERTHAN dynaPageInjectionsFiles.onlineInsertTime ";
		}
		$sql .= " WHERE dynaPageInjections2.injectionName IS NULL AND dynaModuleId_dynaPageInjections2.injectionName IS NULL ";
		
		if( (is_array($properties) && isset($properties['files']) && $properties['files'] ) ){
    		$sql .= " AND (dynaPageInjectionsFiles.injectionName IS NULL OR (dynaPageInjectionsFiles.injectionName IS NOT NULL AND dynaPageInjectionsFiles2.injectionName IS NULL AND dynaPageInjectionsFiles.status = 1) ) ";
		}   
	        if(!empty(array_filter($moduleId))>0){
	        	$sql .= " AND (dynaModuleId_dynaPageInjections.moduleId = '' ";
	        	foreach($moduleId as $key=>$element){
	        		$sql .= " OR dynaModuleId_dynaPageInjections.moduleId = :moduleId".$key;
	        		$vals[':moduleId'.$key] = $element['dynaPageModules_moduleId'];
	        		$types[':moduleId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if($injection_module_status > -1){
	        	$sql .= " AND dynaModuleId_dynaPageInjections.status=:injection_module_status ";
	        	$vals[':injection_module_status'] = $injection_module_status;
	        	$types[':injection_module_status'] = "i";
	        }
	        if($injectionStatus > -1){
	        	$sql .= " AND dynaPageInjections.status = :injectionStatus ";
	        	$vals[':injectionStatus'] = $injectionStatus;
	        	$types[':injectionStatus'] = "i";
	        }
	        
	        $sql .= " GROUP BY dynaPageInjections.injectionName ";
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageInjections_injectionName",
	        	"dynaPageInjections_onlineInsertTime",
	        	"dynaModuleId_dynaPageInjections_moduleId"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	public function searchDynaPageInjections($options=array()){
		/**
			* Search among dynamic page Injections/Services
			* $options {
			*	(array)pageId: array(array('dynaPageDetails_pageId'=>id),...)
			*	injectionStatus: status of variable. Default to 1:Active
			*	injection_page_status: status of variable-page. Default to 1:Active
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageId_dynaPageInjections.onlineInsertTime DESC
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageInjections_id
			*	dynaPageInjections_injectionName
			*	dynaPageId_dynaPageInjections_pageId
			*	dynaPageInjections_type
			*	dynaPageInjections_onlineInsertTime
			*	dynaPageInjections_insertBy_userId
			*	dynaPageInjections_status
			* }
		*/
		$pageId = array();
		$injectionStatus = 1;
		$injection_page_status = 1;
		$limit = -1;
		$start = 0;
		$properties = array();
		$orderBy = " dynaPageId_dynaPageInjections.onlineInsertTime DESC ";
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        $sql = "SELECT dynaPageInjections.id AS `dynaPageInjections_id`, dynaPageInjections.injectionName AS `dynaPageInjections_injectionName`, 
	        	dynaPageInjections.type AS `dynaPageInjections_type`, 
	        	dynaPageId_dynaPageInjections.pageId AS `dynaPageId_dynaPageInjections_pageId`, 
	        	dynaPageInjections.onlineInsertTime AS `dynaPageInjections_onlineInsertTime`, 
	        	dynaPageInjections.insertBy_userId AS `dynaPageInjections_insertBy_userId`, 
	        	dynaPageInjections.status AS `dynaPageInjections_status` ";
	        
	        if( (is_array($properties) && isset($properties['files']) && $properties['files'] ) ){
			$sql .= "
				, CONCAT(
				        '[',
				        COALESCE(
				            GROUP_CONCAT(
				                DISTINCT(CONCAT(
				                    '{',
				                    '\"dynaPageInjectionsFiles_fileName\": \"', dynaPageInjectionsFiles.fileName, '\", ',
				                    '\"dynaPageInjectionsFiles_fileType\": \"', dynaPageInjectionsFiles.fileType, '\"',
				                    '}') 
				                ) 
				                ORDER BY dynaPageInjectionsFiles.fileType ASC 
				                SEPARATOR ','),
				            ''),
				        ']'
				) AS `dynaPageInjections_files` 
			";
		}
	        	
	        $sql .= " FROM dynaPageInjections 
	        	LEFT JOIN dynaPageInjections AS dynaPageInjections2 ON dynaPageInjections2.injectionName = dynaPageInjections.injectionName AND dynaPageInjections2.onlineInsertTime GREATERTHAN dynaPageInjections.onlineInsertTime ";
	        
	        if(!empty(array_filter($pageId))>0){
	        	$sql .= " INNER JOIN ";
	        }else{
	        	$sql .= " LEFT JOIN ";
	        }
		$sql .= " dynaPageId_dynaPageInjections ON dynaPageId_dynaPageInjections.injectionName = dynaPageInjections.injectionName 
			LEFT JOIN dynaPageId_dynaPageInjections AS dynaPageId_dynaPageInjections2 ON dynaPageId_dynaPageInjections.injectionName = dynaPageId_dynaPageInjections2.injectionName 
			AND dynaPageId_dynaPageInjections.pageId = dynaPageId_dynaPageInjections2.pageId AND dynaPageId_dynaPageInjections2.onlineInsertTime GREATERTHAN dynaPageId_dynaPageInjections.onlineInsertTime ";
		
		if( (is_array($properties) && isset($properties['files']) && $properties['files'] ) ){
    		$sql .= " LEFT JOIN dynaPageInjectionsFiles ON dynaPageInjectionsFiles.injectionName = dynaPageInjections.injectionName 
    			LEFT JOIN dynaPageInjectionsFiles AS dynaPageInjectionsFiles2 ON dynaPageInjectionsFiles.injectionName = dynaPageInjectionsFiles2.injectionName AND 
    			dynaPageInjectionsFiles.fileName = dynaPageInjectionsFiles2.fileName AND dynaPageInjectionsFiles2.onlineInsertTime GREATERTHAN dynaPageInjectionsFiles.onlineInsertTime ";
		}
		$sql .= " WHERE dynaPageInjections2.injectionName IS NULL AND dynaPageId_dynaPageInjections2.injectionName IS NULL ";
		if( (is_array($properties) && isset($properties['files']) && $properties['files'] ) ){
		    $sql .= " AND (dynaPageInjectionsFiles.injectionName IS NULL OR (dynaPageInjectionsFiles.injectionName IS NOT NULL AND dynaPageInjectionsFiles2.injectionName IS NULL AND dynaPageInjectionsFiles.status = 1) ) ";
		}
	        if(!empty(array_filter($pageId))>0){
	        	$sql .= " AND (dynaPageId_dynaPageInjections.pageId = '' ";
	        	
	        	foreach($pageId as $key=>$element){
	        		$sql .= " OR dynaPageId_dynaPageInjections.pageId = :pageId".$key;
	        		$vals[':pageId'.$key] = $element['dynaPageDetails_pageId'];
	        		$types[':pageId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if($injection_page_status > -1){
	        	$sql .= " AND dynaPageId_dynaPageInjections.status=:injection_page_status ";
	        	$vals[':injection_page_status'] = $injection_page_status;
	        	$types[':injection_page_status'] = "i";
	        }
	        if($injectionStatus > -1){
	        	$sql .= " AND dynaPageInjections.status = :injectionStatus ";
	        	$vals[':injectionStatus'] = $injectionStatus;
	        	$types[':injectionStatus'] = "i";
	        }
	        
	        $sql .= " GROUP BY dynaPageInjections.injectionName ";
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageInjections_injectionName",
	        	"dynaPageInjections_onlineInsertTime",
	        	"dynaPageId_dynaPageInjections_pageId"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	public function searchDynaPageInitialVariables($options=array()){
		/**
			* Search among dynamic page Initial Variables
			* $options {
			*	(array)pageId: array(array('dynaPageDetails_pageId'=>id),...)
			*	variableStatus: status of variable. Default to 1:Active
			*	variable_page_status: status of variable-page. Default to 1:Active
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageInitVariables.onlineInsertTime DESC
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageInitVariables_id
			*	dynaPageInitVariables_variableId
			*	dynaPageId_dynaPageInitVariables_pageId
			*	dynaPageInitVariables_variableName
			*	dynaPageInitVariables_type
			*	dynaPageInitVariables_initialVal
			*	dynaPageInitVariables_setPriority
			*	dynaPageInitVariables_onlineInsertTime
			*	dynaPageInitVariables_insertBy_userId
			*	dynaPageInitVariables_onlineInsertTime
			*	dynaPageInitVariables_status
			* }
		*/
		$pageId = array();
		$variableStatus = 1;
		$variable_page_status = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " dynaPageInitVariables.setPriority DESC ";
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        $sql = "SELECT dynaPageInitVariables.id AS `dynaPageInitVariables_id`, dynaPageInitVariables.variableId AS `dynaPageInitVariables_variableId`, 
	        	dynaPageId_dynaPageInitVariables.pageId AS `dynaPageId_dynaPageInitVariables_pageId`, dynaPageInitVariables.variableName AS `dynaPageInitVariables_variableName`, 
	        	dynaPageInitVariables.type AS `dynaPageInitVariables_type`, dynaPageInitVariables.initialVal AS `dynaPageInitVariables_initialVal`,
	        	dynaPageInitVariables.onlineInsertTime AS `dynaPageInitVariables_onlineInsertTime`, 
	        	dynaPageInitVariables.insertBy_userId AS `dynaPageInitVariables_insertBy_userId`, 
	        	dynaPageInitVariables.onlineInsertTime AS `dynaPageInitVariables_onlineInsertTime`, 
	        	dynaPageInitVariables.status AS `dynaPageInitVariables_status`, dynaPageInitVariables.setPriority AS `dynaPageInitVariables_setPriority` ";
	        
	        $sql .= " FROM dynaPageInitVariables 
	        	LEFT JOIN dynaPageInitVariables AS dynaPageInitVariables2 ON dynaPageInitVariables.variableId = dynaPageInitVariables2.variableId AND 
	        	dynaPageInitVariables2.onlineInsertTime GREATERTHAN dynaPageInitVariables.onlineInsertTime ";
	        
	        if(!empty(array_filter($pageId))>0){	
			$sql .= " INNER JOIN ";
		}else{
			$sql .= " LEFT JOIN ";
		}
		$sql .= " dynaPageId_dynaPageInitVariables ON dynaPageId_dynaPageInitVariables.variableId = dynaPageInitVariables.variableId 
			LEFT JOIN dynaPageId_dynaPageInitVariables AS dynaPageId_dynaPageInitVariables2 ON dynaPageId_dynaPageInitVariables.variableId = dynaPageId_dynaPageInitVariables2.variableId 
			AND dynaPageId_dynaPageInitVariables.pageId = dynaPageId_dynaPageInitVariables2.pageId AND dynaPageId_dynaPageInitVariables2.onlineInsertTime GREATERTHAN dynaPageId_dynaPageInitVariables.onlineInsertTime ";
		
		$sql .= " WHERE dynaPageInitVariables2.variableId IS NULL AND dynaPageId_dynaPageInitVariables2.variableId IS NULL ";
		
		if(!empty(array_filter($pageId))>0){
	        	$sql .= " AND (dynaPageId_dynaPageInitVariables.pageId = '' ";
	        	
	        	foreach($pageId as $key=>$element){
	        		$sql .= " OR dynaPageId_dynaPageInitVariables.pageId = :pageId".$key;
	        		$vals[':pageId'.$key] = $element['dynaPageDetails_pageId'];
	        		$types[':pageId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if($variable_page_status > -1){
	        	$sql .= " AND dynaPageId_dynaPageInitVariables.status=:variable_page_status ";
	        	$vals[':variable_page_status'] = $variable_page_status;
	        	$types[':variable_page_status'] = "i";
	        }
	        if($variableStatus > -1){
	        	$sql .= " AND dynaPageInitVariables.status = :variableStatus ";
	        	$vals[':variableStatus'] = $variableStatus;
	        	$types[':variableStatus'] = "i";
	        }
	        $sql .= " GROUP BY dynaPageInitVariables.variableId ";
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageInitVariables_variableName",
	        	"dynaPageInitVariables_onlineInsertTime",
	        	"dynaPageId_dynaPageInitVariables_pageId"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	
	public function searchPageModules($options=array()){
		/**
			* Search among dynamic page Modules
			* $options {
			*	(array)pageId: array(array('dynaPageDetails_pageId'=>id),...)
			*	moduleStatus: status of module. Default to 1:Active
			*	module_page_status: status of link between page and module
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageId_dynaPageModules.onlineInsertTime DESC
			*	properties: ["files"=>1]
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageModules_id
				dynaPageModules_moduleId
			*	dynaPageModules_name
			*	dynaPageModules_inputParameters
			*	dynaPageModules_inlineJsCode
			*	dynaPageModules_onlineInsertTime
			*	dynaPageModules_insertBy_userId
			*	dynaPageId_dynaPageModules_onlineInsertTime
			*	dynaPageModulesStatus_status
			*	dynaPageModulesFiles_files
			* }
		*/
		$pageId = array();
		$moduleStatus = 1;
		$module_page_status = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " dynaPageId_dynaPageModules.onlineInsertTime DESC ";
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
		$sql = "SELECT dynaPageModules.id AS `dynaPageModules_id`, dynaPageModules.moduleId AS `dynaPageModules_moduleId`, 
		dynaPageModules.code AS `dynaPageModules_code` ";
		
		if( (is_array($properties) && isset($properties['description']) && $properties['description'] ) ){
			$sql .= ", dynaPageModules.description AS `dynaPageModules_description` ";
		}
		
		$sql .= ", dynaPageModules.name AS `dynaPageModules_name`, dynaPageModules.inputParameters AS `dynaPageModules_inputParameters`, 
		dynaPageModules.onlineInsertTime AS `dynaPageModules_onlineInsertTime`, 
		dynaPageModules.insertBy_userId AS `dynaPageModules_insertBy_userId`, dynaPageModulesStatus.status AS `dynaPageModulesStatus_status`, 
		dynaPageId_dynaPageModules.pageId AS `dynaPageId_dynaPageModules_pageId` ";
		
		if( (is_array($properties) && isset($properties['files']) && $properties['files'] ) ){
			$sql .= "
				, CONCAT(
				        '[',
				        COALESCE(
				            GROUP_CONCAT(
				                DISTINCT(CONCAT(
				                    '{',
				                    '\"dynaPageModulesFiles_fileName\": \"', dynaPageModulesFiles.fileName, '\", ',
				                    '\"dynaPageModulesFiles_fileType\": \"', dynaPageModulesFiles.fileType, '\"',
				                    '}') 
				                ) 
				                ORDER BY dynaPageModulesFiles.fileType ASC 
				                SEPARATOR ','),
				            ''),
				        ']'
				) AS `dynaPageModulesFiles_files` 
			";
		}
		$sql .= " FROM dynaPageModules 
			LEFT JOIN dynaPageModules AS dynaPageModules2 ON dynaPageModules2.moduleId = dynaPageModules.moduleId AND dynaPageModules2.onlineInsertTime GREATERTHAN dynaPageModules.onlineInsertTime ";
		if( (is_array($properties) && isset($properties['files']) && $properties['files'] ) ){
		    $sql .= " LEFT JOIN dynaPageModulesFiles ON dynaPageModulesFiles.moduleId = dynaPageModules.moduleId 
			LEFT JOIN dynaPageModulesFiles AS dynaPageModulesFiles2 ON dynaPageModulesFiles.moduleId = dynaPageModulesFiles2.moduleId AND 
			dynaPageModulesFiles.fileName = dynaPageModulesFiles2.fileName AND dynaPageModulesFiles2.onlineInsertTime GREATERTHAN dynaPageModulesFiles.onlineInsertTime ";
		}
		$sql .= " INNER JOIN dynaPageModulesStatus ON dynaPageModulesStatus.moduleId = dynaPageModules.moduleId 
			LEFT JOIN dynaPageModulesStatus AS dynaPageModulesStatus2 ON dynaPageModulesStatus.moduleId = dynaPageModulesStatus2.moduleId AND
			dynaPageModulesStatus2.onlineInsertTime GREATERTHAN dynaPageModulesStatus.onlineInsertTime ";
		
		if(!empty(array_filter($pageId))>0){	
			$sql .= " INNER JOIN ";
		}else{
			$sql .= " LEFT JOIN ";
		}
		$sql .= " dynaPageId_dynaPageModules ON dynaPageId_dynaPageModules.moduleId = dynaPageModules.moduleId 
			LEFT JOIN dynaPageId_dynaPageModules AS dynaPageId_dynaPageModules2 ON dynaPageId_dynaPageModules.moduleId = dynaPageId_dynaPageModules2.moduleId 
			AND dynaPageId_dynaPageModules.pageId = dynaPageId_dynaPageModules2.pageId AND dynaPageId_dynaPageModules2.onlineInsertTime GREATERTHAN dynaPageId_dynaPageModules.onlineInsertTime ";
		
		$sql .= " WHERE dynaPageModules.moduleId IS NOT NULL AND dynaPageModules2.moduleId IS NULL 
			AND dynaPageModulesStatus.moduleId IS NOT NULL AND dynaPageModulesStatus2.moduleId IS NULL ";
		
		if( (is_array($properties) && isset($properties['files']) && $properties['files'] ) ){
		    $sql .= " AND (dynaPageModulesFiles.moduleId IS NULL OR (dynaPageModulesFiles.moduleId IS NOT NULL AND dynaPageModulesFiles2.moduleId IS NULL AND dynaPageModulesFiles.status = 1 AND dynaPageModulesFiles.loadsWith = 'module') ) ";
		}
		
		if(!empty(array_filter($pageId))>0){	
			$sql .= " AND dynaPageId_dynaPageModules.pageId IS NOT NULL AND dynaPageId_dynaPageModules2.pageId IS NULL ";
		}else{
			$sql .= " AND (dynaPageId_dynaPageModules.pageId IS NULL OR (dynaPageId_dynaPageModules.pageId IS NOT NULL AND dynaPageId_dynaPageModules2.pageId IS NULL) ) ";
		}
	        if(!empty(array_filter($pageId))>0){
	        	$sql .= " AND (dynaPageId_dynaPageModules.pageId = '' ";
	        	
	        	foreach($pageId as $key=>$element){
	        		$sql .= " OR dynaPageId_dynaPageModules.pageId = :pageId".$key;
	        		$vals[':pageId'.$key] = $element['dynaPageDetails_pageId'];
	        		$types[':pageId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if($module_page_status > -1){
	        	$sql .= " AND dynaPageId_dynaPageModules.status=:module_page_status ";
	        	$vals[':module_page_status'] = $module_page_status;
	        	$types[':module_page_status'] = "i";
	        }
	        if($moduleStatus > -1){
	        	$sql .= " AND dynaPageModulesStatus.status = :moduleStatus ";
	        	$vals[':moduleStatus'] = $moduleStatus;
	        	$types[':moduleStatus'] = "i";
	        }
	        
	        $sql .= " GROUP BY dynaPageModules.moduleId ";
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageModules_name",
	        	"dynaPageModules_onlineInsertTime",
	        	"dynaPageId_dynaPageModules_pageId"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	public function searchDynaPageModuleFunctions($options=array()){
		/**
			* Search among dynamic page Module Functions
			* $options {
			*	(array)moduleId: array(array('dynaPageModules_moduleId'=>id),...)
			*	functionStatus: status of function. Default to 1:Active
			*	function_module_status: status of function-module. Default to 1:Active
			*   userGroups: array(array("groupId"=>value,...),...)
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageInitVariables.onlineInsertTime DESC
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageFunction_id
			*	dynaPageFunction_functionId
			*	dynaPageFunction_type
			*	dynaPageFunction_inParameters
			*	dynaPageFunction_text
			*	dynaModuleId_dynaPageFunctionId_moduleId
			* 	dynaModuleId_dynaPageFunctionId_controllerAs
			*	dynaPageFunction_onlineInsertTime
			*	dynaPageFunction_insertBy_userId
			*	dynaPageFunction_onlineInsertTime
			*	dynaPageFunction_status
			* }
		*/
		$moduleId = array();
		$userGroups = array();
		$functionStatus = 1;
		$function_module_status = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " dynaPageFunction.onlineInsertTime DESC ";
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        $sql = "SELECT dynaPageFunction.id AS `dynaPageFunction_id`, dynaPageFunction.functionId AS `dynaPageFunction_functionId`, 
	        	dynaPageFunction.inParameters AS `dynaPageFunction_inParameters`, 
	        	dynaPageFunction.type AS `dynaPageFunction_type`, 
	        	dynaPageFunction.onlineInsertTime AS `dynaPageFunction_onlineInsertTime`, 
	        	dynaPageFunction.insertBy_userId AS `dynaPageFunction_insertBy_userId`, 
	        	dynaPageFunction.onlineInsertTime AS `dynaPageFunction_onlineInsertTime`, 
	        	dynaPageFunctionStatus.status AS `dynaPageFunctionStatus_status`,
	        	dynaPageFunction.text AS `dynaPageFunction_text`, 
	        	dynaModuleId_dynaPageFunctionId.moduleId AS `dynaModuleId_dynaPageFunctionId_moduleId`, 
	        	dynaModuleId_dynaPageFunctionId.controllerAs AS `dynaModuleId_dynaPageFunctionId_controllerAs` ";
	        
	        $sql .= " FROM dynaPageFunction 
	        	LEFT JOIN dynaPageFunction AS dynaPageFunction2 ON dynaPageFunction.functionId = dynaPageFunction2.functionId AND 
	        	dynaPageFunction2.onlineInsertTime GREATERTHAN dynaPageFunction.onlineInsertTime ";
	        
	        $sql .= " INNER JOIN dynaPageFunctionStatus ON dynaPageFunctionStatus.functionId = dynaPageFunction.functionId 
	        	LEFT JOIN dynaPageFunctionStatus AS dynaPageFunctionStatus2 ON dynaPageFunctionStatus.functionId = dynaPageFunctionStatus2.functionId AND 
	        	dynaPageFunctionStatus2.onlineInsertTime GREATERTHAN dynaPageFunctionStatus.onlineInsertTime ";
	        
	        if(!empty(array_filter($moduleId))>0){	
			$sql .= " INNER JOIN ";
		}else{
			$sql .= " LEFT JOIN ";
		}
		$sql .= " dynaModuleId_dynaPageFunctionId ON dynaModuleId_dynaPageFunctionId.functionId = dynaPageFunction.functionId 
			LEFT JOIN dynaModuleId_dynaPageFunctionId AS dynaModuleId_dynaPageFunctionId2 ON dynaModuleId_dynaPageFunctionId.functionId = dynaModuleId_dynaPageFunctionId2.functionId 
			AND dynaModuleId_dynaPageFunctionId.moduleId = dynaModuleId_dynaPageFunctionId2.moduleId AND dynaModuleId_dynaPageFunctionId2.onlineInsertTime GREATERTHAN dynaModuleId_dynaPageFunctionId.onlineInsertTime ";
		if(!empty(array_filter($userGroups))>0){
            $sql .= " LEFT JOIN dynaPageFunctionId_usersGroupId ON 
                            dynaPageFunctionId_usersGroupId.functionId = dynaPageFunction.functionId 
                    LEFT JOIN dynaPageFunctionId_usersGroupId AS dynaPageFunctionId_usersGroupId2 ON 
                        dynaPageFunctionId_usersGroupId2.functionId = dynaPageFunctionId_usersGroupId.functionId 
                        AND dynaPageFunctionId_usersGroupId2.groupId = dynaPageFunctionId_usersGroupId.groupId 
                        AND dynaPageFunctionId_usersGroupId2.onlineInsertTime GREATERTHAN dynaPageFunctionId_usersGroupId.onlineInsertTime";
		}
		
		$sql .= " WHERE dynaPageFunction2.functionId IS NULL AND dynaPageFunctionStatus2.functionId IS NULL AND dynaModuleId_dynaPageFunctionId2.moduleId IS NULL ";
		
		if(!empty(array_filter($moduleId))>0){
	        	$sql .= " AND (dynaModuleId_dynaPageFunctionId.moduleId = :moduleId ";
	        	$vals[':moduleId'] = $moduleId[0]['dynaPageModules_moduleId'];
	        	$types[':moduleId'] = "s";
	        	foreach($moduleId as $key=>$element){
	        		$sql .= " OR dynaModuleId_dynaPageFunctionId.moduleId = :moduleId".$key;
	        		$vals[':moduleId'.$key] = $element['dynaPageModules_moduleId'];
	        		$types[':moduleId'.$key] = "s";
	        	}
	        	$sql .= ")";
        }
        if(!empty(array_filter($userGroups))>0){
	        	$sql .= " AND (dynaPageFunctionId_usersGroupId.groupId IS NULL OR 
	        	        (dynaPageFunctionId_usersGroupId.groupId IS NOT NULL 
	        	        AND dynaPageFunctionId_usersGroupId2.groupId IS NULL 
	        	        AND dynaPageFunctionId_usersGroupId.status=1 
	        	        AND (
	        	        (dynaPageFunctionId_usersGroupId.groupId = :groupId) ";
	        	$vals[':groupId'] = $userGroups[0]['groupId'];
	        	$types[':groupId'] = "s";
	        	foreach($userGroups as $key=>$element){
	        		$sql .= " OR (dynaPageFunctionId_usersGroupId.groupId = :groupId".$key." )";
	        		$vals[':groupId'.$key] = $element['groupId'];
	        		$types[':groupId'.$key] = "s";
	        	}
	        	$sql .= " )) ";
	        	$sql .= " OR dynaPageFunction.availableOffline=1 ";
	        	$sql .= " ) ";
        }
	        if($function_module_status > -1){
	        	$sql .= " AND dynaModuleId_dynaPageFunctionId.status=:function_module_status ";
	        	$vals[':function_module_status'] = $function_module_status;
	        	$types[':function_module_status'] = "i";
	        }
	        if($functionStatus > -1){
	        	$sql .= " AND dynaPageFunctionStatus.status = :functionStatus ";
	        	$vals[':functionStatus'] = $functionStatus;
	        	$types[':functionStatus'] = "i";
	        }
	        $sql .= " GROUP BY dynaPageFunction.functionId ";
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageFunction_functionId",
	        	"dynaPageFunction_onlineInsertTime",
	        	"dynaModuleId_dynaPageFunctionId_moduleId"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	public function searchPageTemplatesDetails($options=array()){
		/**
			* Search among dynamic page Command data
			* $options {
			*	(array)pageId: array(array('dynaPageDetails_pageId'=>id),...)
			*	templateStatus: status of template. Default to 1:Active
			*	template_page_status: status of link between page and template
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageId_dynaPageCommandDataId.onlineInsertTime DESC
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageTemplatesDetail_id
			*	dynaPageTemplatesDetail_templateId
			*	dynaPageTemplatesDetail_name
			*	dynaPageTemplatesDetail_status
			*	dynaPageTemplatesDetail_insertIp
			*	dynaPageTemplatesDetail_insertBy_userId
			*	dynaPageTemplatesDetail_onlineInsertTime
			*	dynaPageTemplatesData_data
			*	dynaPageTemplatesData_parameters
			*	dynaPageId_dynaPageTemplatesId_status
			*	dynaPageId_dynaPageTemplatesId_pageId
			* }
		*/
		$pageId = array();
		$templateStatus = 1;
		$template_page_status = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " dynaPageId_dynaPageTemplatesId.onlineInsertTime DESC ";
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        $sql = "SELECT 
	        	dynaPageTemplatesDetail.id AS `dynaPageTemplatesDetail_id`,
	        	dynaPageTemplatesDetail.templateId AS `dynaPageTemplatesDetail_templateId`,
	        	dynaPageTemplatesDetail.name AS `dynaPageTemplatesDetail_name`, 
	        	dynaPageTemplatesDetail.status AS `dynaPageTemplatesDetail_status`, 
	        	dynaPageTemplatesDetail.insertIp AS `dynaPageTemplatesDetail_insertIp`, 
	        	dynaPageTemplatesDetail.insertBy_userId AS `dynaPageTemplatesDetail_insertBy_userId`, 
	        	dynaPageTemplatesDetail.onlineInsertTime AS `dynaPageTemplatesDetail_onlineInsertTime`,
	        	dynaPageTemplatesData.data AS `dynaPageTemplatesData_data`,
	        	dynaPageTemplatesData.parameters AS `dynaPageTemplatesData_parameters`,
	        	dynaPageId_dynaPageTemplatesId.status AS `dynaPageId_dynaPageTemplatesId_status`,
	        	dynaPageId_dynaPageTemplatesId.pageId AS `dynaPageId_dynaPageTemplatesId_pageId` 
	        	
	        	FROM dynaPageId_dynaPageTemplatesId
	        	LEFT JOIN dynaPageId_dynaPageTemplatesId AS dynaPageId_dynaPageTemplatesId2 ON dynaPageId_dynaPageTemplatesId.pageId = dynaPageId_dynaPageTemplatesId2.pageId AND dynaPageId_dynaPageTemplatesId.templateId = dynaPageId_dynaPageTemplatesId2.templateId AND dynaPageId_dynaPageTemplatesId2.onlineInsertTime GREATERTHAN dynaPageId_dynaPageTemplatesId.onlineInsertTime 
	        	
	        	INNER JOIN dynaPageTemplatesDetail ON dynaPageTemplatesDetail.templateId = dynaPageId_dynaPageTemplatesId.templateId 
	        	LEFT JOIN dynaPageTemplatesDetail AS dynaPageTemplatesDetail2 ON dynaPageTemplatesDetail.templateId = dynaPageTemplatesDetail2.templateId AND dynaPageTemplatesDetail2.onlineInsertTime GREATERTHAN dynaPageTemplatesDetail.onlineInsertTime
	        	
	        	INNER JOIN dynaPageTemplatesData ON dynaPageTemplatesData.templateId = dynaPageId_dynaPageTemplatesId.templateId 
	        	LEFT JOIN dynaPageTemplatesData AS dynaPageTemplatesData2 ON dynaPageTemplatesData.templateId = dynaPageTemplatesData2.templateId AND dynaPageTemplatesData2.onlineInsertTime GREATERTHAN dynaPageTemplatesData.onlineInsertTime ";
	        
	        $sql .= " WHERE dynaPageId_dynaPageTemplatesId2.templateId IS NULL AND dynaPageTemplatesDetail2.templateId IS NULL AND dynaPageTemplatesData2.templateId IS NULL ";
	        if(!empty(array_filter($pageId))>0){
	        	$sql .= " AND (dynaPageId_dynaPageTemplatesId.pageId = '' ";
	        	
	        	foreach($pageId as $key=>$element){
	        		$sql .= " OR dynaPageId_dynaPageTemplatesId.pageId = :pageId".$key;
	        		$vals[':pageId'.$key] = $element['dynaPageDetails_pageId'];
	        		$types[':pageId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if($template_page_status > -1){
	        	$sql .= " AND dynaPageId_dynaPageTemplatesId.status=:template_page_status ";
	        	$vals[':template_page_status'] = $template_page_status;
	        	$types[':template_page_status'] = "i";
	        }
	        if($templateStatus > -1){
	        	$sql .= " AND dynaPageTemplatesDetail.status = :templateStatus ";
	        	$vals[':templateStatus'] = $templateStatus;
	        	$types[':templateStatus'] = "i";
	        }
	        
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageTemplatesDetail_name",
	        	"dynaPageTemplatesDetail_onlineInsertTime",
	        	"dynaPageId_dynaPageTemplatesId_pageId"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	
	public function searchCommandDetails($options=array(), $debug = 0){
		/**
			* Search among dynamic page Command data
			* $options {
			*	pageId: array(array("dynaPageDetails_pageId"=>id),...)
			*	commandsId: array(array("dynaPageCommandData_commandId"=>id),...)
			*	commandsCode: array(array("dynaPageCommandData_commandCode"=>code),...)
			*	commandsIndex: array(array("dynaPageCommandData_commandIndex"=>index),...)
			*	commandsSource: array(array("dynaPageCommandData_commandSource"=>source),...)
			*	userGroups: id of user groups who is allowed to run this. array("userGroup"=>groupId, ...)
			*	userId: id of user who is allowed to run this
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	fullCommand: array("local","server"). Default: array("local")
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	commandStatus: status of command_page. Default to 1:Active
			*	conditionStatus: status of Command_condition. Default to 1:Active
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageId_dynaPageCommandDataId.onlineInsertTime DESC
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageId_dynaPageCommandDataId_pageId
			*	dynaPageCommandData_commandId
			*	dynaPageCommandCondition_conditionId
			*	dynaPageCommandCondition_conditionType
			*	dynaPageCommandCondition_conditionText
			*	dynaPageCommandData_commandSource
			*	dynaPageCommandData_commandText
			*	dynaPageCommandData_commandType
			*	dynaPageDetails_insertBy_userId
			*	dynaPageDetails_insertIp
			*	dynaPageDetails_onlineInsertTime
			*	dynaPageStatus_status
			*	dynaPageId_dynaPageCommandDataId_parameters
			* }
		*/
		$fullCommand = array("local");
		$insertBy_userId = array();
		$pageId = array();
		$userGroups = array();
		$commandsId = array();
		$commandsIndex = array();
		$commandsCode = array();
		$userId = "";
		$commandsSource = array();
		
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$commandStatus = 1;
		$conditionStatus = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " dynaPageCommandData.commandIndex, dynaPageCommandData.onlineInsertTime DESC ";
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        $sql = "SELECT 
	        	dynaPageCommandData.commandId AS `dynaPageCommandData_commandId`,
	        	dynaPageCommandData.commandCode AS `dynaPageCommandData_commandCode`,
	        	dynaPageCommandCondition.conditionId AS `dynaPageCommandCondition_conditionId`,
	        	dynaPageCommandCondition.conditionType AS `dynaPageCommandCondition_conditionType`, 
	        	dynaPageCommandCondition.conditionText AS `dynaPageCommandCondition_conditionText`, 
	        	dynaPageCommandData.commandSource AS `dynaPageCommandData_commandSource`, 
	        	dynaPageId_dynaPageCommandDataId.parameters AS `dynaPageId_dynaPageCommandDataId_parameters`, 
	        	CASE 
	        		WHEN dynaPageCommandData.commandSource IN ('".implode(',',$fullCommand)."') THEN dynaPageCommandData.commandText 
	        		ELSE 'dynamic' 
	        	END 
	        	AS `dynaPageCommandData_commandText`,
	        	dynaPageCommandData.commandParameters AS `dynaPageCommandData_commandParameters`, 
	        	dynaPageCommandData.commandType AS `dynaPageCommandData_commandType` 
	        	
	        	FROM dynaPageCommandData 
	        	LEFT JOIN dynaPageCommandData AS dynaPageCommandData2 ON dynaPageCommandData.commandId = dynaPageCommandData2.commandId AND dynaPageCommandData2.onlineInsertTime GREATERTHAN dynaPageCommandData.onlineInsertTime 
	        	
	        	LEFT JOIN dynaPageId_dynaPageCommandDataId ON dynaPageCommandData.commandId = dynaPageId_dynaPageCommandDataId.commandId 
	        	LEFT JOIN dynaPageId_dynaPageCommandDataId AS dynaPageId_dynaPageCommandDataId2 ON dynaPageId_dynaPageCommandDataId.pageId = dynaPageId_dynaPageCommandDataId2.pageId AND dynaPageId_dynaPageCommandDataId.commandId = dynaPageId_dynaPageCommandDataId2.commandId AND dynaPageId_dynaPageCommandDataId2.onlineInsertTime GREATERTHAN dynaPageId_dynaPageCommandDataId.onlineInsertTime 
	        	
	        	INNER JOIN dynaPageCommandDataId_dynaPageCommandConditionId ON dynaPageCommandDataId_dynaPageCommandConditionId.commandId = dynaPageCommandData.commandId
	        	LEFT JOIN dynaPageCommandDataId_dynaPageCommandConditionId AS dynaPageCommandDataId_dynaPageCommandConditionId2 ON dynaPageCommandDataId_dynaPageCommandConditionId.commandId = dynaPageCommandDataId_dynaPageCommandConditionId2.commandId AND dynaPageCommandDataId_dynaPageCommandConditionId.conditionId = dynaPageCommandDataId_dynaPageCommandConditionId2.conditionId AND dynaPageCommandDataId_dynaPageCommandConditionId2.onlineInsertTime GREATERTHAN dynaPageCommandDataId_dynaPageCommandConditionId.onlineInsertTime 
	        	
	        	INNER JOIN dynaPageCommandCondition ON dynaPageCommandCondition.conditionId = dynaPageCommandDataId_dynaPageCommandConditionId.conditionId 
	        	LEFT JOIN dynaPageCommandCondition AS dynaPageCommandCondition2 ON dynaPageCommandCondition.conditionId = dynaPageCommandCondition2.conditionId AND dynaPageCommandCondition2.onlineInsertTime GREATERTHAN dynaPageCommandCondition.onlineInsertTime ";
	        
	        $sql .= " LEFT JOIN dynaPageCommandData_usersGroupId ON dynaPageCommandData_usersGroupId.commandId = dynaPageId_dynaPageCommandDataId.commandId 
	        	LEFT JOIN dynaPageCommandData_usersGroupId AS dynaPageCommandData_usersGroupId2 ON dynaPageCommandData_usersGroupId.commandId = dynaPageCommandData_usersGroupId2.commandId AND dynaPageCommandData_usersGroupId.usersGroupId = dynaPageCommandData_usersGroupId2.usersGroupId AND dynaPageCommandData_usersGroupId.userId = dynaPageCommandData_usersGroupId2.userId AND dynaPageCommandData_usersGroupId2.onlineInsertTime GREATERTHAN dynaPageCommandData_usersGroupId.onlineInsertTime ";
	        
	        $sql .= " WHERE dynaPageId_dynaPageCommandDataId2.commandId IS NULL AND dynaPageCommandData2.commandId IS NULL AND dynaPageCommandDataId_dynaPageCommandConditionId2.commandId IS NULL AND dynaPageCommandCondition2.conditionId IS NULL ";
	        
	        if(!empty(array_filter($pageId))>0){
		        $sql .= " AND (dynaPageId_dynaPageCommandDataId.pageId= '' ";
		        
		        foreach($pageId as $key=> $element){
		        	$sql .= " OR dynaPageId_dynaPageCommandDataId.pageId= :pageId".$key;
		        	$vals[':pageId'.$key] = $element['dynaPageDetails_pageId'];
		        	$types[':pageId'.$key] = 's';
		        }
		        $sql .= " ) ";
	        }
	        if(!empty(array_filter($commandsSource))>0){
	        	$sql .= " AND (dynaPageCommandData.commandSource= :commandSource ";
		        $vals[':commandSource'] = $commandsSource[0]['dynaPageCommandData_commandSource'];
		        $types[':commandSource'] = "s";
		        foreach($commandsSource as $key=> $element){
		        	$sql .= " OR dynaPageCommandData.commandSource= :commandSource".$key;
		        	$vals[':commandSource'.$key] = $element['dynaPageCommandData_commandSource'];
		        	$types[':commandSource'.$key] = 's';
		        }
		        $sql .= " ) ";
	        }
	        
	        $sql .= " AND (dynaPageCommandData_usersGroupId.usersGroupId IS NULL OR (dynaPageCommandData_usersGroupId.usersGroupId IS NOT NULL AND dynaPageCommandData_usersGroupId2.usersGroupId IS NULL))";
	        
	        if($userId || !empty(array_filter($userGroups))>0){
	        $sql .= " AND ( (dynaPageCommandData_usersGroupId.userId = '' AND dynaPageCommandData_usersGroupId.usersGroupId = '') ";
	        if($userId){
			$sql .=" OR dynaPageCommandData_usersGroupId.userId = :userId ";
			$vals[':userId'] = $userId;
			$types[':userId'] = 's';
		}
		if(!empty(array_filter($userGroups))>0){
			$sql .= " OR dynaPageCommandData_usersGroupId.usersGroupId = :userGroups ";
			$vals[':userGroups'] = $userGroups[0]['groupId'];
			$types[':userGroups'] = 's';
		        foreach($userGroups as $key=> $userGroupsElement){
		        	$sql .= " OR dynaPageCommandData_usersGroupId.usersGroupId = :userGroups".$key;
		        	$vals[':userGroups'.$key] = $userGroupsElement['groupId'];
		        	$types[':userGroups'.$key] = 's';
		        }
		}
		$sql .= ") ";
	        }
		if(!empty(array_filter($commandsId))>0){
	        	$sql .= " AND (dynaPageCommandData.commandId = :commandId ";
	        	$vals[':commandId'] = $commandsId[0]['dynaPageCommandData_commandId'];
	        	$types[':commandId'] = "s";
	        	foreach($commandsId as $key=>$element){
	        		$sql .= " OR dynaPageCommandData.commandId = :commandId".$key;
	        		$vals[':commandId'.$key] = $element['dynaPageCommandData_commandId'];
	        		$types[':commandId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($commandsCode))>0){
	        	$sql .= " AND (dynaPageCommandData.commandCode = :commandCode ";
	        	$vals[':commandCode'] = $commandsCode[0]['dynaPageCommandData_commandCode'];
	        	$types[':commandCode'] = "s";
	        	foreach($commandsCode as $key=>$element){
	        		$sql .= " OR dynaPageCommandData.commandCode = :commandCode".$key;
	        		$vals[':commandCode'.$key] = $element['dynaPageCommandData_commandCode'];
	        		$types[':commandCode'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($commandsIndex))>0){
	        	$sql .= " AND (dynaPageCommandData.commandIndex = :commandIndex ";
	        	$vals[':commandIndex'] = $commandsIndex[0]['dynaPageCommandData_commandIndex'];
	        	$types[':commandIndex'] = "s";
	        	foreach($commandsIndex as $key=>$element){
	        		$sql .= " OR dynaPageCommandData.commandIndex = :commandIndex".$key;
	        		$vals[':commandIndex'.$key] = $element['dynaPageCommandData_commandIndex'];
	        		$types[':commandIndex'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if($sqlStatus > -1){
	        	$sql .= " AND dynaPageId_dynaPageCommandDataId.status=:commandStatus ";
	        	$vals[':commandStatus'] = $commandStatus;
	        	$types[':commandStatus'] = "i";
	        }
	        if($conditionStatus > -1){
	        	$sql .= " AND (dynaPageCommandDataId_dynaPageCommandConditionId.status=:conditionStatus ) ";
	        	$vals[':conditionStatus'] = $conditionStatus;
	        	$types[':conditionStatus'] = "i";
	        }
	        $sql .= " GROUP BY dynaPageCommandData.commandId ";
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageCommandData_sqlId",
	        	"dynaPageDetails_onlineInsertTime",
	        	"dynaPageCommandCondition_conditionId"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        if($debug){
	            $results['sql'] = $sql;
	            $results['vals'] = $vals;
	        }
	        return $results;
	}
	public function runSqlCommand($commandDetails, $commandParameters, $nullValue=NULL, $debug=0){
		$dynaResult = array();
		$msg = "";
		$status = 1;

		try{
		        $dynaCommand = $commandDetails['dynaPageCommandData_commandText'];
		       	$parameters_array = json_decode($commandDetails['dynaPageCommandData_commandParameters'], true);
		       	$dynaVals = array();
		       	$dynaTypes = array();
		       	$directReplaceParameters = [];
		       	foreach($parameters_array as $parameters_element){
		       	    if(!$commandParameters[$parameters_element['name']] && isset($parameters_element['default'])){
		       	        $commandParameters[$parameters_element['name']] = $parameters_element['default'];
		       	    }
		       	    if($parameters_element['type'] == "a"){
    		       		$commandParameters[$parameters_element['name']] = implode(",", $commandParameters[$parameters_element['name']]);
    		       		$parameters_element['type'] = "s";
                    }
    		       	if($commandParameters[$parameters_element['name']] === $nullValue){
    		       		$commandParameters[$parameters_element['name']] = "";
    		       	}
    		       	if(!$parameters_element['directReplace']){
    		       		if(isset($parameters_element['name'])){
    		        		$dynaVals[':'.$parameters_element['name']] = $commandParameters[$parameters_element['name']];
    		        		$dynaTypes[':'.$parameters_element['name']] = $parameters_element['type'];
    		       		}else{
    		       			$dynaVals[':'.$parameters_element['name']] = $nullValue;
    		        		$dynaTypes[':'.$parameters_element['name']] = $parameters_element['type'];
    		       		}
		       	    }else{
		       	        if($parameters_element['type'] == "i"){
		       	            $safeVal = $this->filePath->toSafeInt($commandParameters[$parameters_element['name']]);
		       	        }else{
		       	            $safeVal = $this->filePath->toSafeString($commandParameters[$parameters_element['name']]);
		       	        }
		       	        $directReplaceParameters[$parameters_element['name']] = $safeVal;
		       	    }
		        }
            $dynaCommand = $this->filePath->replaceParameters($dynaCommand, $directReplaceParameters);
			$dynaResult = $this->db->pdoSelect($dynaCommand,$dynaVals,$dynaTypes);
            if($debug){
                $dynaResult['dynaCommand'] = $dynaCommand;
                $dynaResult['directReplaceParameters'] = $directReplaceParameters;
                $dynaResult['commandParameters'] = $commandParameters;
                $dynaResult['dynaVals'] = $dynaVals;
            }
			if($dynaResult['status']){
			    
			}else{
			    $status = 0;
			    $msgCat="SYS_ERR_MSG";
				if(isset($dynaResult['result']['msg'])){
					$msg = $this->langDecode->decode($dynaResult['result']['msg'], $msgCat, $this->lang);
				}else{
					$msgCode = "There was a system error updating page data details for '".$dynaCommand."'. Please try again. Administrator is informed of the problem.EX1225.".serialize($dynaVals)."<br/>".serialize($dynaTypes)."<br />".serialize($commandParameters);
					$msg = $this->langDecode->decode($msgCode, $msgCat, $this->lang);
				}
			}
		}
		catch(Exception $e){
	     	$status = 0;
			$msg = $e->getMessage();
		}

	        return array("status"=>$status, "msg"=>$msg, "result"=>$dynaResult);
	}
	public function searchDynaPageTableMap($options=array()){
		/**
			* Search among dynamic page table data
			* $options {
			*	(array)localTable: array("table1","table2")
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status. Default to 1:Active
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageTableMap.onlineInsertTime DESC
			*	properties: ["columnMap"]
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageTableMap_id
			*	dynaPageTableMap_localTable
			*	dynaPageTableMap_columnMap
			*	dynaPageTableMap_insertBy_userId
			*	dynaPageTableMap_insertIp
			*	dynaPageTableMap_onlineInsertTime
			*	dynaPageTableMap_status
			* }
		*/
		$localTable = array();
		$insertBy_userId = array();
		$properties = array();
		$debug = 0;

		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = count($localTable);
		$start = 0;
		$orderBy = " dynaPageTableMap.onlineInsertTime DESC ";
		$countOnly = 0;
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        if($countOnly){
        		$sql = "SELECT COUNT(DISTINCT(dynaPageTableMap.id)) AS count ";
        	}else{
		        $sql = "SELECT dynaPageTableMap.id AS `dynaPageTableMap_id`,  
		        	dynaPageTableMap.localTable AS `dynaPageTableMap_localTable` ";
		        if(is_array($properties) && isset($properties['columnMap']) && $properties['columnMap']){
		        	$sql .= ", dynaPageTableMap.columnMap AS `dynaPageTableMap_columnMap` ";
		        }
		        $sql .= ", dynaPageTableMap.status AS `dynaPageTableMap_status`, dynaPageTableMap.onlineInsertTime AS `dynaPageTableMap_onlineInsertTime`,
		        	dynaPageTableMap.insertBy_userId AS `dynaPageTableMap_insertBy_userId`, dynaPageTableMap.insertIp AS `dynaPageTableMap_insertIp` 
		        	";
	        }
	        $sql .= " FROM dynaPageTableMap 
	        		LEFT JOIN dynaPageTableMap AS dynaPageTableMap2 ON dynaPageTableMap.localTable = dynaPageTableMap2.localTable AND dynaPageTableMap2.onlineInsertTime GREATERTHAN dynaPageTableMap.onlineInsertTime ";
	        		
	        $sql .= " WHERE dynaPageTableMap2.mapId IS NULL ";

	        if(!empty(array_filter($localTable))>0){
	        	$sql .= " AND ( (dynaPageTableMap.localTable = :localTable ";
	        	$vals[':localTable'] = $localTable[0];
	        	$types[':localTable'] = "s";
	        	if($onlineInsertTime_start && $onlineInsertTime_start[$localTable[0]]){
    	        	$sql .= " AND (DATE(dynaPageTableMap.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
    	        	$date = date_create($onlineInsertTime_start[$localTable[0]]);
    	        	$onlineInsertTime_start0 = date_format($date, 'Y-m-d H:i:s');
    	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start0;
    	        	$types[':onlineInsertTime_start'] = "s";	
    	        }
    	        $sql .= " ) ";
	        	foreach($localTable as $key=>$element){
	        		$sql .= " OR (dynaPageTableMap.localTable = :localTable".$key;
	        		$vals[':localTable'.$key] = $element;
	        		$types[':localTable'.$key] = "s";
	        		
	        		if($onlineInsertTime_start && $onlineInsertTime_start[$element]){
        	        	$sql .= " AND (DATE(dynaPageTableMap.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start".$key.")) ";
        	        	$date = date_create($onlineInsertTime_start[$element]);
        	        	$onlineInsertTime_start1 = date_format($date, 'Y-m-d H:i:s');
        	        	$vals[':onlineInsertTime_start'.$key] = $onlineInsertTime_start1;
        	        	$types[':onlineInsertTime_start'.$key] = "s";	
        	        }
        	        $sql .= " ) ";
	        	}
	        	$sql .= ") ";
	        }
	        
	        if($onlineInsertTime_start && is_string($onlineInsertTime_start)){
	        	$sql .= " AND (DATE(dynaPageTableMap.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end && is_string($onlineInsertTime_end)){
	        	$sql .= " AND (DATE(dynaPageTableMap.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";
	        }
	        
	        if($status>-1){
	        	$sql .= " AND dynaPageTableMap.status = :status ";
	        	$vals[':status'] = $status;
	        	$types[':status'] = "i";
	        }
	        
	        if(!$countOnly){
			$sql .= " GROUP BY dynaPageTableMap.mapId ";
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageTableMap_mapId",
	        	"dynaPageTableMap_onlineInsertTime",
	        	"dynaPageTableMap_localTable"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        if($debug){
    	        $results['sql'] = $sql;
    	        $results['vals'] = $vals;
    	        $results['types'] = $types;
	        }
	        return $results;
	}
	
	public function searchDynaPageFunctions($options=array()){
		/**
			* Search among dynamic page Functions
			* $options {
			*	(array)pageId: array(array('dynaPageDetails_pageId'=>id),...)
			*	pageStatus: status of function. Default to 1:Active
			*	function_page_status: status of function-page. Default to 1:Active
			*   userGroups: array(array("groupId"=>value,...),...)
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to dynaPageFunction.onlineInsertTime DESC
			* returns(array){
			*	status: 0:failed, 1: success
			*	dynaPageFunction_id
			*	dynaPageFunction_functionId
			*	dynaPageFunction_type
			*	dynaPageFunction_inParameters
			*	dynaPageFunction_text
			*	dynaPageId_dynaPageFunctionId_pageId
			*   dynaPageId_dynaPageFunctionId_controllerAs
			*	dynaPageFunction_onlineInsertTime
			*	dynaPageFunction_insertBy_userId
			*	dynaPageFunction_onlineInsertTime
			*	dynaPageFunction_status
			* }
		*/
		$pageId = array();
		$userGroups = array();
		$functionStatus = 1;
		$function_page_status = 1;
		
		$limit = -1;
		$start = 0;
		$orderBy = " dynaPageFunction.onlineInsertTime DESC ";
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        
	        $sql = "SELECT dynaPageFunction.id AS `dynaPageFunction_id`, dynaPageFunction.functionId AS `dynaPageFunction_functionId`, 
	        	dynaPageFunction.inParameters AS `dynaPageFunction_inParameters`, 
	        	dynaPageFunction.type AS `dynaPageFunction_type`, 
	        	dynaPageFunction.onlineInsertTime AS `dynaPageFunction_onlineInsertTime`, 
	        	dynaPageFunction.insertBy_userId AS `dynaPageFunction_insertBy_userId`, 
	        	dynaPageFunction.onlineInsertTime AS `dynaPageFunction_onlineInsertTime`, 
	        	dynaPageFunctionStatus.status AS `dynaPageFunctionStatus_status`,
	        	dynaPageFunction.text AS `dynaPageFunction_text`, 
	        	dynaPageId_dynaPageFunctionId.pageId AS `dynaPageId_dynaPageFunctionId_pageId`, 
	        	dynaPageId_dynaPageFunctionId.controllerAs AS `dynaPageId_dynaPageFunctionId_controllerAs` ";
	        
	        $sql .= " FROM dynaPageFunction 
	        	LEFT JOIN dynaPageFunction AS dynaPageFunction2 ON dynaPageFunction.functionId = dynaPageFunction2.functionId AND 
	        	dynaPageFunction2.onlineInsertTime GREATERTHAN dynaPageFunction.onlineInsertTime ";
	        
	        $sql .= " INNER JOIN dynaPageFunctionStatus ON dynaPageFunctionStatus.functionId = dynaPageFunction.functionId 
	        	LEFT JOIN dynaPageFunctionStatus AS dynaPageFunctionStatus2 ON dynaPageFunctionStatus.functionId = dynaPageFunctionStatus2.functionId AND 
	        	dynaPageFunctionStatus2.onlineInsertTime GREATERTHAN dynaPageFunctionStatus.onlineInsertTime ";
	   
	   if(!empty(array_filter($userGroups))>0){
            $sql .= " LEFT JOIN dynaPageFunctionId_usersGroupId ON 
                            dynaPageFunctionId_usersGroupId.functionId = dynaPageFunction.functionId 
                    LEFT JOIN dynaPageFunctionId_usersGroupId AS dynaPageFunctionId_usersGroupId2 ON 
                        dynaPageFunctionId_usersGroupId2.functionId = dynaPageFunctionId_usersGroupId.functionId 
                        AND dynaPageFunctionId_usersGroupId2.groupId = dynaPageFunctionId_usersGroupId.groupId 
                        AND dynaPageFunctionId_usersGroupId2.onlineInsertTime GREATERTHAN dynaPageFunctionId_usersGroupId.onlineInsertTime";
		}
	   	
	    if(!empty(array_filter($pageId))>0){	
			$sql .= " INNER JOIN ";
		}else{
			$sql .= " LEFT JOIN ";
		}
		
		$sql .= " dynaPageId_dynaPageFunctionId ON dynaPageId_dynaPageFunctionId.functionId = dynaPageFunction.functionId 
			LEFT JOIN dynaPageId_dynaPageFunctionId AS dynaPageId_dynaPageFunctionId2 ON dynaPageId_dynaPageFunctionId.functionId = dynaPageId_dynaPageFunctionId2.functionId 
			AND dynaPageId_dynaPageFunctionId.pageId = dynaPageId_dynaPageFunctionId2.pageId AND dynaPageId_dynaPageFunctionId2.onlineInsertTime GREATERTHAN dynaPageId_dynaPageFunctionId.onlineInsertTime ";
		
		$sql .= " WHERE dynaPageFunction2.functionId IS NULL AND dynaPageFunctionStatus2.functionId IS NULL ";
		if(!empty(array_filter($pageId))>0){
			$sql .= " AND dynaPageId_dynaPageFunctionId2.functionId IS NULL ";
		}else{
			$sql .= " AND (dynaPageId_dynaPageFunctionId.functionId IS NULL OR (dynaPageId_dynaPageFunctionId.functionId IS NOT NULL AND dynaPageId_dynaPageFunctionId2.functionId IS NULL) ) ";
		}
		if(!empty(array_filter($pageId))>0){
	        	$sql .= " AND (dynaPageId_dynaPageFunctionId.pageId = '' ";
	        	
	        	foreach($pageId as $key=>$element){
	        		$sql .= " OR dynaPageId_dynaPageFunctionId.pageId = :pageId".$key;
	        		$vals[':pageId'.$key] = $element['dynaPageDetails_pageId'];
	        		$types[':pageId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if(!empty(array_filter($userGroups))>0){
	        	$sql .= " AND (dynaPageFunctionId_usersGroupId.groupId IS NULL OR 
	        	        (dynaPageFunctionId_usersGroupId.groupId IS NOT NULL 
	        	        AND dynaPageFunctionId_usersGroupId2.groupId IS NULL 
	        	        AND dynaPageFunctionId_usersGroupId.status=1 
	        	        AND (dynaPageFunctionId_usersGroupId.groupId = '' OR 
	        	        (dynaPageFunctionId_usersGroupId.groupId = :groupId) ";
	        	$vals[':groupId'] = $userGroups[0]['groupId'];
	        	$types[':groupId'] = "s";
	        	foreach($userGroups as $key=>$element){
	        		$sql .= " OR (dynaPageFunctionId_usersGroupId.groupId = :groupId".$key." )";
	        		$vals[':groupId'.$key] = $element['groupId'];
	        		$types[':groupId'.$key] = "s";
	        	}
	        	$sql .= " )) ";
	        	$sql .= " OR dynaPageFunction.availableOffline=1 ";
	        	$sql .= " ) ";
            }
	        if($function_page_status > -1){
	        	$sql .= " AND dynaPageId_dynaPageFunctionId.status = :function_page_status ";
	        	$vals[':function_page_status'] = $function_page_status;
	        	$types[':function_page_status'] = "i";
	        }
	        if($functionStatus > -1){
	        	$sql .= " AND dynaPageFunctionStatus.status = :functionStatus ";
	        	$vals[':functionStatus'] = $functionStatus;
	        	$types[':functionStatus'] = "i";
	        }
	        $sql .= " GROUP BY dynaPageFunction.functionId ";
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageFunction_functionId",
	        	"dynaPageFunction_onlineInsertTime",
	        	"dynaPageId_dynaPageFunctionId_pageId"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
}

?>