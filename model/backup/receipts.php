<?php

class receiptsModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor, $lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
	        $this->lang = $lang;
	}
	public function searchReceiptModules(){
		$vals = array();
		$types = array();
		
		$sql = "SELECT id, name, type, parameters, description 
			FROM receiptsModule WHERE status = 1";
		 $results = $this->db->pdoSelect($sql,$vals,$types);
		 return $results;
	}
	public function searchReceipts($options=array()){
		/**
			* Search among Receipts table
			* $options {
			*	(array) receiptsId array(array("receiptId"=>value), ...)
			*	(array) receiptsName array(array("receiptName"=>value),...)
			*	(array) insertBy_userId
			*	(array) properties: what properties to get e.g "data"
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status of item. Default to 1:Active
			*	limit: limit number of results default to 10; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to receiptsDetail.onlineInsertTime DESC
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
		$receiptsId = array();
		$receiptsName = array();
		$properties = array();
		$concat = array();
		
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
        		$orderBy = " receiptsDetail.onlineInsertTime DESC ";
        	}
        	
        	if($countOnly){
        		$sql = "SELECT COUNT(DISTINCT(receiptsDetail.receiptId)) AS count ";
        	}else{
        		$sql = "SELECT receiptsDetail.receiptId AS `receiptsDetail_receiptId`, receiptsDetail.receiptName AS `receiptsDetail_receiptName`, receiptsDetail.receiptDescription AS `receiptsDetail_receiptDescription`, receiptsDetail.insertIp AS `receiptsDetail_insertIp`, receiptsDetail.insertBy_userId AS `receiptsDetail_insertBy_userId`, receiptsDetail.onlineInsertTime AS `receiptsDetail_onlineInsertTime` ";
        		if(in_array("receiptsDetail_receiptData",$properties)){
        			$sql .= " ,receiptsDetail.receiptData AS `receiptsDetail_receiptData` ";
        		}
        		if(in_array("storesIdreceiptsId_storeId",$properties)){
        			$sql .= " ,storesIdreceiptsId.storeId AS `storesIdreceiptsId_storeId` ";
        		}
        		if(in_array("storesIdreceiptsId_receiptType",$properties)){
        			$sql .= " ,storesIdreceiptsId.receiptType AS `storesIdreceiptsId_receiptType` ";
        		}
        		if(count($concat)){
        			foreach($concat as $concat_element){
        				$sql .= " ,CONCAT(";
        				foreach($concat_element['columns'] as $concat_items){
        					$sql .= $concat_items.",";
        				}
        				$sql = rtrim($sql, ',');
        				$sql .= ") ";
        				if(isset($concat_element['name'])){
        					$sql .= " AS ".$concat_element['name'];
        				}
        			}
        		}
        		$sql .= " ,receiptsStatus.status AS `receiptsStatus_status` ";
        	}
        	$sql .= " FROM receiptsDetail 
        		LEFT JOIN receiptsDetail AS receiptsDetail2 ON receiptsDetail.receiptId = receiptsDetail2.receiptId AND receiptsDetail2.onlineInsertTime GREATERTHAN receiptsDetail.onlineInsertTime 
        		
        		INNER JOIN receiptsStatus ON receiptsStatus.receiptId = receiptsDetail.receiptId 
	        	LEFT JOIN receiptsStatus AS receiptsStatus2 ON receiptsStatus2.receiptId = receiptsStatus.receiptId AND receiptsStatus2.onlineInsertTime GREATERTHAN receiptsStatus.onlineInsertTime ";
	        	
	        	if(!empty(array_filter($storesId))>0 || in_array("storesIdreceiptsId_storeId",$properties) || in_array("storesIdreceiptsId_receiptType",$properties)){
	        		$sql .= " LEFT JOIN storesIdreceiptsId ON storesIdreceiptsId.receiptId = receiptsDetail.receiptId 
	        			LEFT JOIN storesIdreceiptsId AS storesIdreceiptsId2 ON storesIdreceiptsId2.receiptId = storesIdreceiptsId.receiptId AND storesIdreceiptsId2.storeId = storesIdreceiptsId.storeId AND storesIdreceiptsId2.onlineInsertTime GREATERTHAN storesIdreceiptsId.onlineInsertTime ";
	        	}
	        	
	        	$sql .=" WHERE receiptsDetail2.receiptId IS NULL AND receiptsStatus2.receiptId IS NULL ";
			if(!empty(array_filter($storesId))>0 || in_array("storesIdreceiptsId_storeId",$properties) || in_array("storesIdreceiptsId_receiptType",$properties)){
				$sql .= " AND storesIdreceiptsId2.storeId IS NULL ";
			}
		if($status > -1){
	        	$sql .= " AND receiptsStatus.status= :status ";
	        	$vals[':status'] = $status;
	        	$types[':status'] = "i";
	        }
	        
	        if(!empty(array_filter($receiptsId))>0){
	        	$sql .= " AND (receiptsDetail.receiptId = :receiptId ";
	        	$vals[':receiptId'] = $receiptsId[0]['receiptId'];
	        	$types[':receiptId'] = "s";
	        	foreach($receiptsId as $key=>$element){
	        		$sql .= " OR receiptsDetail.receiptId = :receiptId".$key;
	        		$vals[':receiptId'.$key] = $element['receiptId'];
	        		$types[':receiptId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if(!empty(array_filter($storesId))>0){
	        	$sql .= " AND (storesIdreceiptsId.storeId = :storeId ";
	        	$vals[':storeId'] = $storesId[0]['storesDetail_storeId'];
	        	$types[':storeId'] = "s";
	        	foreach($storesId as $key=>$element){
	        		$sql .= " OR storesIdreceiptsId.storeId = :storeId".$key;
	        		$vals[':storeId'.$key] = $element['storesDetail_storeId'];
	        		$types[':storeId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
		
		if(!empty(array_filter($receiptsName))>0){
	        	$sql .= " AND (receiptsDetail.receiptName = :receiptName ";
	        	$vals[':receiptName'] = $receiptsName[0]['receiptName'];
	        	$types[':receiptName'] = "s";
	        	foreach($receiptsName as $key=>$element){
	        		$sql .= " OR receiptsDetail.receiptName = :receiptName".$key;
	        		$vals[':receiptName'.$key] = $element['receiptName'];
	        		$types[':receiptName'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
		if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (receiptsDetail.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR receiptsDetail.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
		if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(receiptsDetail.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        	
	        	
	        	$sql .= " OR DATE(receiptsStatus.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start_status) ";
	        	$vals[':onlineInsertTime_start_status'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start_status'] = "s";
	        	
	        	if(!empty(array_filter($storesId))>0 || in_array("storesIdreceiptsId_storeId",$properties) || in_array("storesIdreceiptsId_receiptType",$properties)){
	        		$sql .= " OR DATE(storesIdreceiptsId.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start_stores) ";
	        		$vals[':onlineInsertTime_start_stores'] = $onlineInsertTime_start;
	        		$types[':onlineInsertTime_start_stores'] = "s";
	        	}
	        	$sql .= " )";
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(receiptsDetail.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
	        
		if(!$countOnly){
			$sql .= " GROUP BY receiptsDetail.receiptId ";
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"receiptsDetail_receiptId",
	        	"receiptsDetail_receiptName",
	        	"receiptsDetail_onlineInsertTime"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	public function addReceiptId($options=array()){
		/**
			* Creates a unique receiptId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	receiptId: Created receiptId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$receiptId){
			$receiptId = $this->loginClass->generateSecureId("receiptId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"receiptId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"receiptId"=>$receiptId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"receiptId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("receiptsId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Item receipt id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating receipt id. Please try again. Administrator is informed of the problem.EX72";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating receipt id. Please try again. Administrator is informed of the problem.EX78";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("receiptsId_receiptId"=>$receiptId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addReceiptDetail($receiptId, $options){
		/**
		* Add/Change Receipt Details
		* $receiptId: Id of receipt to change
		* (array) options{
		*	receiptName: 
		*	receiptData: 
		*	receiptDescription: 
		*	receiptType
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	receiptsDetail_receiptId: Id of receipt trying to change
		*	receiptsDetail_id: id of this record
		* }
		*/
		$receiptName = "";
		$receiptData = "";
		$receiptDescription = "";

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
	        	"receiptId",
	        	"receiptName",
	        	"receiptDescription",
	        	"receiptData",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"receiptId"=>$receiptId,
	        	"receiptName"=>$receiptName,
	            	"receiptDescription"=>$receiptDescription,
	            	//"receiptData"=>base64_encode(serialize($receiptData)),
	            	"receiptData"=>json_encode($receiptData, JSON_HEX_QUOT | JSON_HEX_TAG),
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"receiptId"=>"s",
	        	"receiptName"=>"s",
			"receiptDescription"=>"s",
			"receiptData"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("receiptsDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Receipt details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating receipt details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating receipt details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("receiptsDetail_receiptId"=>$receiptId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addReceiptStatus($receiptId,$receiptStatus,$insertBy_userId=''){
		/**
		* Add/Change Receipt Status
		* receiptId : Id of sales screen to change
		* receiptStatus: Status of receipt. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	receiptsDetail_receiptId: Id of receipt trying to change
		*	receiptsDetail_id: id of this record
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
	        	"receiptId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"receiptId"=>$receiptId,
	            	"status"=>$receiptStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"receiptId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("receiptsStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Receipt status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating receipt status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating receipt status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("receiptsDetail_receiptId"=>$receiptId,"status"=>$status,"msg"=>$msg);
	}
}
	
?>