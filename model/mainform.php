<?php
class mainformModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor,$lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
		$this->lang = $lang;
	}
	public function log($code, $text){
		$col = array();
		$val = array();
		$type = array();
		
		$col = array(
	       		"code",
			"text",
			"insertIp"
	        );
	        
	        $val = array(
			"code"=>$code,
			"text"=>serialize($text),
			"insertIp"=>$this->filePath->getUserIp()
		);
	        
	        $type = array(
	        	"code"=>"s",
	        	"text"=>"s",
	        	"insertIp"=>"s"
	        );
	        $result = $this->db->pdoInsert("log", $col, $val, $type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Log updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating log. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating log. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status);
	}
	public function isFunctionAllowed(){
	
	}
	public function searchDynaPageMenuDetails($options=array()){
	    /**
			* Get an array of lists of allowable menu items to show to user based on page and userid
			* Options {
			*	(array) $pageId: {fieldName: 'e.g dynaPageDetails_pageId', rows:[{dynaPageDetails_pageId: 123345},...]} The page code that user is accessing. empty for all pages
			*	(array) $groupId: {fieldName: 'e.g groupsDetail_groupId', rows:[{groupsDetail_groupId: 123345},...]}
			*   (array) $menuCode: {fieldName:, rows[{},{},...]} specific menu code
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	(Array)menu: menu items grouped by id and orders by order [menuCode{menuCode,pageCode,menuName,menuIcon,menuOrder,parentCode}]
			*	msg: message decoded in user lang
			* }
		*/
		$countOnly = 0;
		$pageId = array();
		$groupId = array();
		$menuDetails = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = -1;
		$start = 0;
		$properties = array();
		$orderBy = " dynaPageMenu.menuOrder, dynaPageMenu.menuCode  ";
		
		$vals = array();
		$types = array();
		
		$msg = "";
		$results = array();
		
		try{
			foreach($options as $optionsKey=>$optionsElement){
                ${$optionsKey} = $optionsElement;
    		}
            $sql = "SELECT 
                    MAX(dynaPageMenu.menuCode) AS `dynaPageMenu_menuCode`, 
                    MAX(dynaPageMenu.pageId) AS `dynaPageMenu_pageId`, 
                    MAX(dynaPageMenu.menuAction) AS `dynaPageMenu_menuAction`, 
                    MAX(dynaPageMenu.menuName) AS `dynaPageMenu_menuName`, 
                    MAX(dynaPageMenu.menuIcon) AS `dynaPageMenu_menuIcon`, 
                    MAX(dynaPageMenu.menuOrder) AS `dynaPageMenu_menuOrder`, 
                    MAX(dynaPageMenu.parentCode) AS `dynaPageMenu_parentCode`, 
                    MAX(dynaPageMenu.lang) AS `dynaPageMenu_lang`, 
                    MAX(dynaPageMenu.internal) AS `dynaPageMenu_internal`, 
                    MAX(dynaPageMenu.status) AS `dynaPageMenu_status`, 
                    MAX(dynaPageMenu.onlineInsertTime) AS `dynaPageMenu_onlineInsertTime`, 
                    MAX(dynaPageMenu.insertBy_userId) AS `dynaPageMenu_insertBy_userId`, 
                    MAX(dynaPageMenu.insertIp) AS `dynaPageMenu_insertIp`
		        	";
		        	
            if((is_array($properties) && isset($properties['usersGroup']) && $properties['usersGroup'] )){
                $sql .= ", MAX(dynaPageData_usersGroupId.groupId) AS `dynaPageData_usersGroupId_groupId` ";
            }
            
            $sql .= " FROM dynaPageMenu ";   	
            $sql .= " LEFT JOIN dynaPageMenu AS dynaPageMenu2 ON dynaPageMenu.menuCode = dynaPageMenu2.menuCode AND 
                    dynaPageMenu2.onlineInsertTime GREATERTHAN dynaPageMenu.onlineInsertTime ";
            
            if((is_array($properties) && isset($properties['usersGroup']) && $properties['usersGroup'] )){
                $sql .= " LEFT JOIN dynaPageData_usersGroupId ON dynaPageData_usersGroupId.pageId = dynaPageMenu.pageId 
                        LEFT JOIN dynaPageData_usersGroupId AS dynaPageData_usersGroupId2 ON dynaPageData_usersGroupId2.pageId = dynaPageData_usersGroupId.pageId AND dynaPageData_usersGroupId.groupId = dynaPageData_usersGroupId2.groupId ";
            }
            $sql .= " WHERE dynaPageMenu2.menuCode IS NULL ";
            
            if($status>-1){
			    $sql .= " AND dynaPageMenu.status = :status ";
		    }
		    
		    if(isset($pageId) && isset($pageId['rows']) && !empty(array_filter($pageId['rows']))>0){
	        	$sql .= " AND (dynaPageMenu.pageId = :pageId ";
	        	if(isset($pageId['fieldName']) && $pageId['fieldName']){
	        	    $fieldName = $pageId['fieldName'];
	        	}else{
	        	    $fieldName = 0;
	        	}
	        	$vals[':pageId'] = $pageId['rows'][0][$fieldName];
	        	$types[':pageId'] = "s";
	        	foreach($pageId['rows'] as $key=>$element){
	                if(isset($pageId['fieldName']) && $pageId['fieldName']){
    	        	    $fieldName = $pageId['fieldName'];
    	        	}else{
    	        	    $fieldName = $key;
    	        	}
	        		$sql .= " OR dynaPageMenu.pageId = :pageId".$key;
	        		$vals[':pageId'.$key] = $element[$fieldName];
	        		$types[':pageId'.$key] = "s";
	        	}
	        	$sql .= ") ";
	        }
	        
	        if(isset($menuCode) && isset($menuCode['rows']) && !empty(array_filter($menuCode['rows']))>0){
	        	$sql .= " AND (dynaPageMenu.menuCode = :menuCode ";
	        	if(isset($menuCode['fieldName']) && $menuCode['fieldName']){
	        	    $fieldName = $menuCode['fieldName'];
	        	}else{
	        	    $fieldName = 0;
	        	}
	        	$vals[':menuCode'] = $menuCode['rows'][0][$fieldName];
	        	$types[':menuCode'] = "s";
	        	foreach($menuCode['rows'] as $key=>$element){
	        	    if(isset($menuCode['fieldName']) && $menuCode['fieldName']){
    	        	    $fieldName = $menuCode['fieldName'];
    	        	}else{
    	        	    $fieldName = $key;
    	        	}
	        		$sql .= " OR dynaPageMenu.menuCode = :menuCode".$key;
	        		$vals[':menuCode'.$key] = $element[$fieldName];
	        		$types[':menuCode'.$key] = "s";
	        	}
	        	$sql .= ") ";
	        }
	        
	        if((is_array($properties) && isset($properties['usersGroup']) && $properties['usersGroup'] )){
    	        if(isset($groupId) && isset($groupId['rows']) && !empty(array_filter($groupId['rows']))>0){
    	            $sql .= " AND (dynaPageData_usersGroupId.pageId IS NULL OR (dynaPageData_usersGroupId.pageId IS NOT NULL AND dynaPageData_usersGroupId2.pageId IS NULL ";
    	        	$sql .= " AND (dynaPageData_usersGroupId.groupId = :groupId ";
    	        	if(isset($groupId['fieldName']) && $groupId['fieldName']){
    	        	    $fieldName = $groupId['fieldName'];
    	        	}else{
    	        	    $fieldName = 0;
    	        	}
    	        	$vals[':groupId'] = $groupId['rows'][0][$fieldName];
    	        	$types[':groupId'] = "s";
    	        	foreach($groupId['rows'] as $key=>$element){
    	                if(isset($groupId['fieldName']) && $groupId['fieldName']){
        	        	    $fieldName = $groupId['fieldName'];
        	        	}else{
        	        	    $fieldName = $key;
        	        	}
    	        		$sql .= " OR dynaPageData_usersGroupId.groupId = :groupId".$key;
    	        		$vals[':groupId'.$key] = $element[$fieldName];
    	        		$types[':groupId'.$key] = "s";
    	        	}
    	        	$sql .= "))) ";
    	        }else{
    	            $sql .= " AND (dynaPageData_usersGroupId.pageId IS NULL OR (dynaPageData_usersGroupId.pageId IS NOT NULL AND dynaPageData_usersGroupId2.pageId IS NULL) ) ";
    	        }
	        }
	        
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE_FORMAT(dynaPageMenu.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start, '%Y-%m-%d %H:%i:%s') ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d H:i:s');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";
	        	
	        	if((is_array($properties) && isset($properties['usersGroup']) && $properties['usersGroup'] )){
	        	    $sql .= " OR DATE_FORMAT(dynaPageData_usersGroupId.onlineInsertTime, '%Y-%m-%d %H:%i:%s') GREATERTHAN= DATE_FORMAT(:onlineInsertTime_start_data, '%Y-%m-%d %H:%i:%s') ";
	        	    $vals[':onlineInsertTime_start_data'] = $onlineInsertTime_start;
	        	    $types[':onlineInsertTime_start_data'] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(dynaPageMenu.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";
	        }
	        
	        if($status>-1){
	        	$sql .= " AND dynaPageMenu.status = :status ";
	        	$vals[':status'] = $status;
	        	$types[':status'] = "i";
	        }
	        
	        if(!$countOnly){
			    $sql .= " GROUP BY dynaPageMenu.pageId ";
			    if((is_array($properties) && isset($properties['usersGroup']) && $properties['usersGroup'] )){
			        $sql .= ", dynaPageData_usersGroupId.groupId ";
			    }
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }
	        
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaPageMenu_pageId",
	        	"dynaPageMenu_onlineInsertTime"
	        );
	        $results['sortBy'] = trim($orderBy);
		}
		catch(Exception $e) {
			$msg = $e->getMessage();
			$results = array("status"=>0, "msg"=>$msg, "rows"=>array(), "sortBy"=>trim($orderBy), "sortableColumns"=>array());
		}
		return $results;
	}
	public function getShowableMenuItems($options=array()){ //TO BE DELETED
	    //Should be deleted
		/**
			* Get an array of lists of allowable menu items to show to user based on page and userid
			* Options {
			*	$pageCode: The page code that user is accessing. empty for all pages
			*	$userId: userId or empty for current user
			*	(array) $allowedFunctions: allowable user functions
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	(Array)menu: menu items grouped by id and orders by order [menuCode{menuCode,pageCode,menuName,menuIcon,menuOrder,parentCode}]
			*	msg: message decoded in user lang
			* }
		*/
		$pageCode = "";
		$userId = "";
		$menu = array();
		$allowedFunctions = array();
		
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
	        if(!$userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$userId = $loginCheck['userId'];
			}
		}
		
		try{
			$vals = array(

			);
			$types = array(

			);
			//SELECT ALL MENU ITEMS THAT USER HAS ACCESS TO ITS FUNCTION
			$sql = "SELECT DISTINCT(menu.menuCode) AS menuCode 
				FROM menu 
				INNER JOIN functionMenu ON functionMenu.menuCode = menu.menuCode  
				WHERE menu.isActive=1 ";
				if(!empty(array_filter($allowedFunctions))>0){
					$sql .= " AND (  functionMenu.functionCode=:functionCode ";
					$vals[':functionCode'] = $allowedFunctions[0]['functionCode'];
					$types[':functionCode'] = 's';
					foreach($allowedFunctions as $key=>$element){
						$sql .= " OR functionMenu.functionCode = :functionCode".$key;
						$vals[':functionCode'.$key] = $element['functionCode'];
						$types[':functionCode'.$key] = 's';
					}
					$sql .= " ) ";
				}else{
					$sql .= " AND functionMenu.functionCode = '' ";
				}
				$sql .= " ORDER BY menu.menuOrder";
			$results = $this->db->pdoSelect($sql,$vals,$types);
			if($results['status']){
				foreach($results['rows'] as $element){
					$menu[$element['menuCode']] = $element['menuCode'];
				}
				$status = 1;
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$msg = $results['msg'];
				$status = 0;
			}	
		}
		catch(Exception $e) {
			$status = 0;
			$msg = $e->getMessage();
		}
		
		return array("status"=>$status,"msg"=>$msg,"menu"=>$menu);
	}
	public function getMenuItemByCode($options=array()){
		/**
			* Get the menu details by menu code
			* Options {
			*	(Array)$MenuCode: {An array of menu codes to fetch}
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	(Array)menu: menu items menu[menuCode]=array(""=>,""=>)
			*	msg: message decoded in user lang
			* }
		*/
		$MenuCode = array();
		$menu = array();
		$status = 1;
		$msg = "";
		try{
			$sql = "SELECT menu.menuCode AS menuCode,menu.pageCode AS pageCode,menu.menuName AS menuName,menu.menuIcon AS menuIcon,menu.menuOrder AS menuOrder,menu.parentCode AS parentCode, menu.availableOffline AS `availableOffline`   
				FROM menu 
				WHERE menu.isActive=1 ";
				$types = array();
				$vals = array();
			if(!empty(array_filter($MenuCode))>0){
				$sql .= " AND (menu.menuCode=:menuCode ";
				$vals[':menuCode'] = $MenuCode[0];
				$types[':menuCode'] = 's';
				foreach($MenuCode as $key => $element){
					$sql .= " OR menu.menuCode=:menuCode".$key;
					$vals[':menuCode'.$key] = $MenuCode[0];
					$types[':menuCode'.$key] = 's';
				}
				$sql .= ")";
			}
			$sql .= " ORDER BY menu.menuOrder";
			$result = $this->db->pdoSelect($sql,$vals,$types);
			if($result['status']){
				$status = 1;
				$menu = $result['rows'];
			}else{
				$status = 0;
				$msg = $result['msg'];
			}
		}
		catch(Exception $e) {
			$status = 0;
			$msg = $e->getMessage();
		}
		return array("status"=>$status,"msg"=>$msg,"menu"=>$menu);
	}
	
	public function getAvailableTableViews($tableCode,$options){
		/**
		* Get Available Views for each user to change the columns,orders and grouping when viewing a table
		* TableCode: Code of the table teh user is trying to fetch views for
		* (array) options{
		*	(array) userGroups: Groups that this user belongs to. will be ignored in userID match
		*	userId: Id of the user we are trying to fetch available views for
		* }
		* returns array(
		*	gridsViewDetail_viewId
		*	gridsViewDetail_viewName
		*	gridsViewDetail_gridState
		*	gridsViewUser_groupId
		*	gridsViewUser_userId
		* )
		*/
		$userGroups = array();
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
	        if(!$userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$userId = $loginCheck['userId'];
			}
		}
		$vals = array();
		$types = array();
		$sql = "SELECT gridsViewDetail.viewId AS 'viewId',gridsViewDetail.viewName AS 'viewName', gridsViewDetail.gridState AS 'gridState', gridsViewUser.groupId AS 'groupId', gridsViewUser.userId AS 'userId' 
			FROM gridsViewDetail 
			INNER JOIN gridsViewStatus ON gridsViewStatus.viewId = gridsViewDetail.viewId 
			LEFT JOIN gridsViewStatus AS gridsViewStatus2 ON gridsViewStatus2.viewId = gridsViewDetail.viewId AND gridsViewStatus2.onlineInsertTime GREATERTHAN gridsViewDetail.onlineInsertTime 
			INNER JOIN gridsViewUser ON gridsViewUser.viewId = gridsViewDetail.viewId 
			WHERE gridsViewStatus.status = 1 AND gridsViewStatus2.id IS NULL ";
			
		$sql .= " AND gridsViewDetail.tableCode=:tableCode ";
		$vals[':tableCode'] = $tableCode;
		$types[':tableCode'] = 's';
		
		$sql .= " AND ((gridsViewUser.groupId = '' AND gridsViewUser.userId = '') ";
		if(!empty(array_filter($userGroups))>0){
			$sql .= " OR (gridsViewUser.userId='' AND (gridsViewUser.groupId = :userGroups ";
		        $vals[':userGroups'] = $userGroups[0]['groupId'];
		        $types[':userGroups'] = 's';
		        foreach($userGroups as $key=> $userGroupsElement){
		        	$sql .= " OR gridsViewUser.groupId = :userGroups".$key;
		        	$vals[':userGroups'.$key] = $userGroupsElement['groupId'];
		        	$types[':userGroups'.$key] = 's';
		        }
		        $sql .= " ) ";
		}
		if($userId){
			$sql .= " OR (gridsViewUser.userId = :userId)";
			$vals[':userId'] = $userId;
		        $types[':userId'] = 's';
		}
		if(!empty(array_filter($userGroups))>0 && $userId){
			$sql .= " ) ";
		}
		$sql .= " ) ";
		$sql .= " ORDER BY gridsViewUser.onlineInsertTime DESC, gridsViewDetail.onlineInsertTime DESC";
		$results = $this->db->pdoSelect($sql,$vals,$types);
		if($results['status']){
			foreach($results['rows'] as $key=>$val){
				$results['rows'][$key]['gridState'] = unserialize($val['gridState']);
			}
			return $results['rows'];
		}else{
			return array();
		}
	}
	
	public function addTableViewId($options=array()){
		/**
		* Create and Add unique id for a view
		* (array) options{
		*	insertBy_userId
		* }
		* returns array(
		*	status
		*	msg
		* )
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
		
		if(!$viewId){
			$viewId = $this->loginClass->generateSecureId("viewId");
		}
		
		$col = array();
		$val = array();
		$type = array();
		
		$col = array(
	       		"viewId",
			"insertBy_userId",
			"insertIp"
	        );
	        
	        $val = array(
			"viewId"=>$viewId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
	        
	        $type = array(
	        	"viewId"=>"s",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );
	        $result = $this->db->pdoInsert("gridsViewId",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "View id updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating view id. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating view id. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"gridsViewDetail_viewId"=>$viewId);
	}
	public function addTableViewStatus($viewId,$viewStatus,$options=array()){
		/**
		* Add Status for a view
		* viewId
		* viewStatus
		* (array) options{
		*	insertBy_userId
		* }
		* returns array(
		*	status
		*	msg
		* )
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

		$col = array();
		$val = array();
		$type = array();
		
		$col = array(
	       		"viewId",
	        	"status",
			"insertBy_userId",
			"insertIp"
	        );
	        
	        $val = array(
			"viewId"=>$viewId,
		        "status"=>$viewStatus,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
	        
	        $type = array(
	        	"viewId"=>"s",
	        	"status"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );
	        $result = $this->db->pdoInsert("gridsViewStatus",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "View status updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating view status. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating view status. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"gridsViewDetail_viewId"=>$viewId);
	}
	public function addTableViewDetails($viewId, $tableCode, $viewName, $gridState, $options=array()){
		/**
		* Add details for a view
		* viewId
		* tableCode: Code of the table teh user is trying to save views for
		* gridState: View details we want to save
		* viewName: name of the view to show to users
		* (array) options{
		*	insertBy_userId
		* }
		* returns array(
		*	status
		*	msg
		* )
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

		$col = array();
		$val = array();
		$type = array();
		
		$col = array(
	       		"viewId",
	        	"tableCode",
	        	"viewName",
	        	"gridState",
			"insertBy_userId",
			"insertIp"
	        );
	        
	        $val = array(
			"viewId"=>$viewId,
		        "tableCode"=>$tableCode,
		        "viewName"=>$viewName,
		        "gridState"=>serialize($gridState),
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
	        
	        $type = array(
	        	"viewId"=>"s",
	        	"tableCode"=>"s",
	        	"viewName"=>"s",
	        	"gridState"=>"s",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );
	        $result = $this->db->pdoInsert("gridsViewDetail",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "View details updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating view details. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating view details. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"gridsViewDetail_viewId"=>$viewId);
	}
	public function addTableViewUsers($viewId, $options=array()){
		/**
		* Add allowed Users/Groups for a View for each user to change the columns,orders and grouping when viewing a table
		* viewId: Id of view to add allowed users/groups to
		* (array) options{
		*	(array) userGroups: Groups that this user belongs to.  will be added for both groups as well as user if userID entered (array){ (array){'groupId'=>val},{...} }
		*	userId: Id of the user we are trying to save available views for
		*	insertBy_userId
		* }
		* returns array(
		*	status
		*	msg
		* )
		*/
		$insertBy_userId = "";
		$userGroups = array();
		$userId = '';
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

		$col = array();
		$val = array();
		$type = array();

		$col = array(
	       		"viewId",
	        	"userId",
	        	"groupId",
			"insertBy_userId",
			"insertIp"
	        );
	        if(!empty(array_filter($userGroups))>0 || $userId){
			foreach($userGroups as $userGroups_element){
			        $val[] = array(
			        	"viewId"=>$viewId,
			        	"userId"=>'',
			        	"groupId"=>$userGroups_element['groupId'],
					"insertBy_userId"=>$insertBy_userId,
					"insertIp"=>$this->filePath->getUserIp()
			        );
			}
			if($userId){
				$val[] = array(
					"viewId"=>$viewId,
			        	"userId"=>$userId,
			        	"groupId"=>'',
					"insertBy_userId"=>$insertBy_userId,
					"insertIp"=>$this->filePath->getUserIp()
				);
			}
		}else{
			$val = array(
				"viewId"=>$viewId,
			        "userId"=>'',
			        "groupId"=>'',
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$this->filePath->getUserIp()
			);
		}
	        $type = array(
	        	"viewId"=>"s",
	        	"userId"=>"s",
	        	"groupId"=>"s",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("gridsViewUser",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "View users updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating view users. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating view users. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"gridsViewDetail_viewId"=>$viewId);
	}
}
?>