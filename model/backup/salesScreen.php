<?php

class salesScreenModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor, $lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
	        $this->lang = $lang;
	}
	public function searchSaleScreenModules(){
		$vals = array();
		$types = array();
		
		$sql = "SELECT id, name, type, parameters, description 
			FROM salesScreenModule WHERE status = 1";
		 $results = $this->db->pdoSelect($sql,$vals,$types);
		 return $results;
	}
	public function searchSalesScreen($options=array()){
		/**
			* Search among Sales Screen table
			* $options {
			*	(array) storesId array(array("storeId"=>value),...)
			*	(array) salesScreenId array(array("salesScreenId"=>value), ...)
			*	(array) salesScreenName array(array("salesScreenName"=>value),...)
			*	(array) insertBy_userId
			*	(array) properties: what properties to get e.g "data"
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status of item. Default to 1:Active
			*	limit: limit number of results default to 10; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to salesScreenDetail.onlineInsertTime DESC
			*	countOnly: ony return count if 1. default to 0
			* returns(array){
			*	status: 0:failed, 1: success
			*	insertBy_userId
			*	insertIp
			*	onlineInsertTime
			
			*	sortBy: what is this query order by
			*	sortableColumns: what are teh sortable columns that user can sort by for this query
			
			* or only count
			* }
		*/
		
		$storesId = array();
		$salesScreenId = array();
		$salesScreenName = array();
		$properties = array();
		
		$insertBy_userId = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = 10;
		$start = 0;
		$orderBy = "";
		$countOnly = 0;
		$properties = array();
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        	if(!$orderBy){
        		$orderBy = " salesScreenDetail.onlineInsertTime DESC ";
        	}
        	
        	if($countOnly){
        		$sql = "SELECT COUNT(DISTINCT(salesScreenDetail.salesScreenId)) AS count ";
        	}else{
        		$sql = "SELECT salesScreenDetail.salesScreenId AS `salesScreenDetail_salesScreenId`, salesScreenDetail.salesScreenName AS `salesScreenDetail_salesScreenName`, salesScreenDetail.salesScreenDescription AS `salesScreenDetail_salesScreenDescription`, salesScreenDetail.insertIp AS `salesScreenDetail_insertIp`, salesScreenDetail.insertBy_userId AS `salesScreenDetail_insertBy_userId`, salesScreenDetail.onlineInsertTime AS `salesScreenDetail_onlineInsertTime`, ";
        		if(in_array("data",$properties)){
        			$sql .= " salesScreenDetail.salesScreenData AS `salesScreenDetail_salesScreenData`, ";
        		}
        		$sql .= " GROUP_CONCAT(salesScreenIdstoresId.storeId) AS `salesScreenIdstoresId_storeIdGroup`, ";
        		$sql .= " salesScreenStatus.status AS `salesScreenStatus_status` ";
        	}
        	$sql .= " FROM salesScreenDetail 
        		LEFT JOIN salesScreenDetail AS salesScreenDetail2 ON salesScreenDetail.salesScreenId = salesScreenDetail2.salesScreenId AND salesScreenDetail2.onlineInsertTime GREATERTHAN salesScreenDetail.onlineInsertTime 
        		
        		INNER JOIN salesScreenStatus ON salesScreenStatus.salesScreenId = salesScreenDetail.salesScreenId 
	        	LEFT JOIN salesScreenStatus AS salesScreenStatus2 ON salesScreenStatus2.salesScreenId = salesScreenStatus.salesScreenId AND salesScreenStatus2.onlineInsertTime GREATERTHAN salesScreenStatus.onlineInsertTime 
	        	
	        	LEFT JOIN salesScreenIdstoresId ON salesScreenIdstoresId.salesScreenId = salesScreenDetail.salesScreenId 
	        	LEFT JOIN salesScreenIdstoresId AS salesScreenIdstoresId2 ON salesScreenIdstoresId.salesScreenId = salesScreenIdstoresId2.salesScreenId AND salesScreenIdstoresId.storeId = salesScreenIdstoresId2.storeId AND salesScreenIdstoresId2.onlineInsertTime GREATERTHAN salesScreenIdstoresId.onlineInsertTime ";
	        	
	        	$sql .=" WHERE salesScreenDetail2.salesScreenId IS NULL AND salesScreenStatus2.salesScreenId IS NULL AND (salesScreenIdstoresId.salesScreenId IS NULL OR (salesScreenIdstoresId.salesScreenId IS NOT NULL AND salesScreenIdstoresId2.salesScreenId IS NULL  AND salesScreenIdstoresId.status=1)) AND salesScreenIdstoresId2.salesScreenId IS NULL  ";
		
		if($status){
	        	$sql .= " AND salesScreenStatus.status=1 ";
	        }
	        
	        if(!empty(array_filter($salesScreenId))>0){
	        	$sql .= " AND (salesScreenDetail.salesScreenId = :salesScreenId ";
	        	$vals[':salesScreenId'] = $salesScreenId[0]['salesScreenId'];
	        	$types[':salesScreenId'] = "s";
	        	foreach($salesScreenId as $key=>$element){
	        		$sql .= " OR salesScreenDetail.salesScreenId = :salesScreenId".$key;
	        		$vals[':salesScreenId'.$key] = $element['salesScreenId'];
	        		$types[':salesScreenId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
		
		if(!empty(array_filter($storesId))>0){
	        	$sql .= " AND (salesScreenIdstoresId.storeId = :storeId ";
	        	$vals[':storeId'] = $storesId[0]['storeId'];
	        	$types[':storeId'] = "s";
	        	foreach($storesId as $key=>$element){
	        		$sql .= " OR salesScreenIdstoresId.storeId = :storeId".$key;
	        		$vals[':storeId'.$key] = $element['storeId'];
	        		$types[':storeId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
		
		if(!empty(array_filter($salesScreenName))>0){
	        	$sql .= " AND (salesScreenDetail.salesScreenName = :salesScreenName ";
	        	$vals[':salesScreenName'] = $storesCode[0]['salesScreenName'];
	        	$types[':salesScreenName'] = "s";
	        	foreach($itemsCode as $key=>$element){
	        		$sql .= " OR salesScreenDetail.salesScreenName = :salesScreenName".$key;
	        		$vals[':salesScreenName'.$key] = $element['salesScreenName'];
	        		$types[':salesScreenName'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
		if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (salesScreenDetail.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR salesScreenDetail.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
		if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(salesScreenDetail.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(salesScreenDetail.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
	        
		if(!$countOnly){
			$sql .= " GROUP BY salesScreenDetail.salesScreenId ";
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"salesScreenDetail_salesScreenId",
	        	"salesScreenDetail_salesScreenName",
	        	"salesScreenDetail_onlineInsertTime"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	public function addSalesScreenId($options=array()){
		/**
			* Creates a unique salesScreenId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	salesScreenId: Created salesScreenId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$salesScreenId){
			$salesScreenId = $this->loginClass->generateSecureId("salesScreenId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"salesScreenId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"salesScreenId"=>$salesScreenId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"salesScreenId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("salesScreenId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Item sales screen id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating sales screen id. Please try again. Administrator is informed of the problem.EX72";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating sales screen id. Please try again. Administrator is informed of the problem.EX78";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("salesScreenId_salesScreenId"=>$salesScreenId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addsalesScreenDetail($salesScreenId,$options){
		/**
		* Add/Change Item Details
		* $salesScreenId: Id of sales Screen to change
		* (array) options{
		*	salesScreenName: 
		*	salesScreenData: 
		*	salesScreenDescription: 
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	salesScreenDetail_salesScreenId: Id of sales screen trying to change
		*	salesScreenDetail_id: id of this record
		* }
		*/
		$salesScreenName = "";
		$salesScreenData = "";
		$salesScreenDescription = "";

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
	        	"salesScreenId",
	        	"salesScreenName",
	        	"salesScreenDescription",
	        	"salesScreenData",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"salesScreenId"=>$salesScreenId,
	        	"salesScreenName"=>$salesScreenName,
	            	"salesScreenDescription"=>$salesScreenDescription,
	            	"salesScreenData"=>base64_encode(serialize($salesScreenData)),
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"salesScreenId"=>"s",
	        	"salesScreenName"=>"s",
			"salesScreenDescription"=>"s",
			"salesScreenData"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("salesScreenDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Sales screen details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating sales screen details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating sales screen details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("salesScreenDetail_salesScreenId"=>$salesScreenId,"status"=>$status,"salesScreenDetail_id"=>$id,"msg"=>$msg);
	}
	
	public function addSalesScreenStatus($salesScreenId,$salesScreenStatus,$insertBy_userId=''){
		/**
		* Add/Change Sales Screen Status
		* salesScreenId : Id of sales screen to change
		* salesScreenStatus: Status of sales screen. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	salesScreenDetail_salesScreenId: Id of sales screen trying to change
		*	salesScreenDetail_id: id of this record
		* }
		*/
		$id = '';
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
	        $col = array(
	        	"salesScreenId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"salesScreenId"=>$salesScreenId,
	            	"status"=>$salesScreenStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"salesScreenId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("salesScreenStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Sales screen status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating sales screen status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating sales screen status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("salesScreenDetail_salesScreenId"=>$salesScreenId,"status"=>$status,"salesScreenStatus_id"=>$id,"msg"=>$msg);
	}
	
	public function addsalesScreenStore($salesScreenId,$options=array()){
		/**
		* Adds stores to sales screen
		* salesScreenId: salesScreenId 
		* (array) $options{
		*	(array) $stores{
		*		"storesDetail_storeId"=>$storeId,
		*		"status"=>$status
		*	}
		*	insertBy_userId
		* }
		*/
		$insertBy_userId = "";
		$stores = array();
        
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
	       		"storeId",
	        	"salesScreenId",
	        	"status",
			"insertBy_userId",
			"insertIp"
	        );
	        $userIp = $this->filePath->getUserIp();
	        
		foreach($stores as $stores_element){
		        $val[] = array(
		        	"storeId"=>$stores_element['storesDetail_storeId'],
		        	"salesScreenId"=>$salesScreenId,
		        	"status"=>$stores_element['status'],
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$userIp
		        );
		}
	        $type = array(
	        	"storeId"=>"s",
	        	"salesScreenId"=>"s",
	        	"status"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("salesScreenIdstoresId",$col,$val,$type);
	        
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Sales Screen stores updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating sales screen stores. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating sales screen brands. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"salesScreenDetail_salesScreenId"=>$salesScreenId);
	}
}
	
?>