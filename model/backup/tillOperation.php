<?php

class tillOperationModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor, $lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
	        $this->lang = $lang;
	}
	
	public function addTillOperation($tillOperations=array(), $options=array()){
		/**
		* Adds till operation
		* (array)$tillOperations{
		*	(array) {
		*		"tillOperation_operationId"=>offline generated id
		*		"tillOperation_offlineInsertTime"
		*		"tillOperation_offlineUserId"
		*		"tillOperation_operationType"
		*		"tillOperation_tillId"
		*	}
		*	(array) $options{
		*		insertBy_userId
		*	}
		* }
		*/
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

		$col = array(
	       		"operationId",
	        	"tillId",
	        	"operationType",
	        	"offlineUserId",
	        	"offlineInsertTime",
			"insertBy_userId",
			"insertIp"
	        );
	        $ip = $this->filePath->getUserIp();
	        
		foreach($tillOperations as $tillOperations_element){
			if($tillOperations_element['tillOperation_tillId'] && !$tillOperations_element['tillOperation_insertBy_userId']){
			        $val[] = array(
			        	"operationId"=>$tillOperations_element['tillOperation_operationId'],
			        	"tillId"=>$tillOperations_element['tillOperation_tillId'],
			        	"operationType"=>$tillOperations_element['tillOperation_operationType'],
			        	"offlineUserId"=>$tillOperations_element['tillOperation_offlineUserId'],
			        	"offlineInsertTime"=>$tillOperations_element['tillOperation_offlineInsertTime'],
					"insertBy_userId"=>$insertBy_userId,
					"insertIp"=>$ip
			        );
		        }
		}
	        $type = array(
	        	"operationId"=>"s",
	        	"tillId"=>"s",
	        	"operationType"=>"s",
	        	"offlineUserId"=>"s",
	        	"offlineInsertTime"=>"s",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("tillOperation",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Till operation updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating till operation. Please try again. Administrator is informed of the problem. EX90";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating till operation. Please try again. Administrator is informed of the problem. EX96";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"result"=>array("tillOperation_insertBy_userId"=>$insertBy_userId));
	}
	public function addTillPaymentShot($tillPaymentShot, $options=array()){
		/**
		* Adds till payment shot
		* (array)$tillPaymentShot{
		*	(array) {
		*		"tillPaymentShot_tillOperationId"=>offline generated id
		*		"tillPaymentShot_paymentMethodId"
		*		"tillPaymentShot_countedQty"
		*		"tillPaymentShot_countedValue"
		*		"tillPaymentShot_offlineInsertTime"
		*		"tillPaymentShot_offlineUserId"
		*	}
		*	(array) $options{
		*		insertBy_userId
		*	}
		* }
		*/
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

		$col = array(
	       		"tillOperationId",
	        	"paymentMethodId",
	        	"countedQty",
	        	"countedValue",
	        	"offlineUserId",
	        	"offlineInsertTime",
			"insertBy_userId",
			"insertIp"
	        );
	        $ip = $this->filePath->getUserIp();
	        
		foreach($tillPaymentShot as $tillPaymentShot_element){
			if($tillPaymentShot_element['tillPaymentShot_tillOperationId'] && $tillPaymentShot_element['tillPaymentShot_paymentMethodId'] && !$tillPaymentShot_element['tillPaymentShot_insertBy_userId']){
			        $val[] = array(
			        	"tillOperationId"=>$tillPaymentShot_element['tillPaymentShot_tillOperationId'],
			        	"paymentMethodId"=>$tillPaymentShot_element['tillPaymentShot_paymentMethodId'],
			        	"countedQty"=>$tillPaymentShot_element['tillPaymentShot_countedQty'],
			        	"countedValue"=>$tillPaymentShot_element['tillPaymentShot_countedValue'],
			        	"offlineUserId"=>$tillPaymentShot_element['tillPaymentShot_offlineUserId'],
			        	"offlineInsertTime"=>$tillPaymentShot_element['tillPaymentShot_offlineInsertTime'],
					"insertBy_userId"=>$insertBy_userId,
					"insertIp"=>$ip
			        );
		        }
		}
	        $type = array(
	        	"tillOperationId"=>"s",
	        	"paymentMethodId"=>"s",
	        	"countedQty"=>"i",
	        	"countedValue"=>"s",
	        	"offlineUserId"=>"s",
	        	"offlineInsertTime"=>"s",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("tillPaymentShot",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Till payment shot updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating till payment shot. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating till payment shot. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"result"=>array("tillPaymentShot_insertBy_userId"=>$insertBy_userId));
	}
	public function addTillCashShot($tillCashShot, $options=array()){
		/**
		* Adds till cash shot
		* (array)$tillCashShot{
		*	(array) {
		*		"tillCashShot_tillOperationId"=>offline generated id
		*		"tillCashShot_paymentMethodId"
		*		"tillCashShot_cashTypeId"
		*		"tillCashShot_countedQty"
		*		"tillCashShot_countedValue"
		*		"tillCashShot_offlineInsertTime"
		*		"tillCashShot_offlineUserId"
		*	}
		*	(array) $options{
		*		insertBy_userId
		*	}
		* }
		*/
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

		$col = array(
	       		"tillOperationId",
	        	"paymentMethodId",
	        	"cashTypeId",
	        	"countedQty",
	        	"countedValue",
	        	"offlineUserId",
	        	"offlineInsertTime",
			"insertBy_userId",
			"insertIp"
	        );
	        $ip = $this->filePath->getUserIp();
	        
		foreach($tillCashShot as $tillCashShot_element){
			if($tillCashShot_element['tillCashShot_cashTypeId'] && $tillCashShot_element['tillCashShot_paymentMethodId'] && $tillCashShot_element['tillCashShot_tillOperationId'] && !$tillCashShot_element['tillCashShot_insertBy_userId']){
			        $val[] = array(
			        	"tillOperationId"=>$tillCashShot_element['tillCashShot_tillOperationId'],
			        	"paymentMethodId"=>$tillCashShot_element['tillCashShot_paymentMethodId'],
			        	"cashTypeId"=>$tillCashShot_element['tillCashShot_cashTypeId'],
			        	"countedQty"=>$tillCashShot_element['tillCashShot_countedQty'],
			        	"countedValue"=>$tillCashShot_element['tillCashShot_countedValue'],
			        	"offlineUserId"=>$tillCashShot_element['tillCashShot_offlineUserId'],
			        	"offlineInsertTime"=>$tillCashShot_element['tillCashShot_offlineInsertTime'],
					"insertBy_userId"=>$insertBy_userId,
					"insertIp"=>$ip
			        );
		        }
		}
	        $type = array(
	        	"tillOperationId"=>"s",
	        	"paymentMethodId"=>"s",
	        	"cashTypeId"=>"s",
	        	"countedQty"=>"i",
	        	"countedValue"=>"s",
	        	"offlineUserId"=>"s",
	        	"offlineInsertTime"=>"s",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("tillCashShot",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Till cash shot updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating till cash shot. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating till cash shot. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"result"=>array("tillCashShot_insertBy_userId"=>$insertBy_userId));
	}
	public function addTillPaymentDiscrepancy($tillPaymentDiscrepancy, $options=array()){
		/**
		* Adds till payment discrepancy
		* (array)$tillPaymentDiscrepancy{
		*	(array) {
		*		"tillPaymentDiscrepancy_tillOperationId"=>offline generated id
		*		"tillPaymentDiscrepancy_paymentMethodId"
		*		"tillPaymentDiscrepancy_markdownId"
		*		"tillPaymentDiscrepancy_differenceInQty"
		*		"tillPaymentDiscrepancy_differenceInValue"
		*		"tillPaymentDiscrepancy_offlineInsertTime"
		*		"tillPaymentDiscrepancy_offlineUserId"
		*	}
		*	(array) $options{
		*		insertBy_userId
		*	}
		* }
		*/
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

		$col = array(
	       		"tillOperationId",
	        	"paymentMethodId",
	        	"markdownId",
	        	"differenceInQty",
	        	"differenceInValue",
	        	"offlineUserId",
	        	"offlineInsertTime",
			"insertBy_userId",
			"insertIp"
	        );
	        $ip = $this->filePath->getUserIp();
	        
		foreach($tillPaymentDiscrepancy as $tillPaymentDiscrepancy_element){
			if($tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_tillOperationId'] && $tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_paymentMethodId'] && !$tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_insertBy_userId']){
			        $val[] = array(
			        	"tillOperationId"=>$tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_tillOperationId'],
			        	"paymentMethodId"=>$tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_paymentMethodId'],
			        	"markdownId"=>$tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_markdownId'],
			        	"differenceInQty"=>$tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_differenceInQty'],
			        	"differenceInValue"=>$tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_differenceInValue'],
			        	"offlineUserId"=>$tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_offlineUserId'],
			        	"offlineInsertTime"=>$tillPaymentDiscrepancy_element['tillPaymentDiscrepancy_offlineInsertTime'],
					"insertBy_userId"=>$insertBy_userId,
					"insertIp"=>$ip
			        );
		        }
		}
	        $type = array(
	        	"tillOperationId"=>"s",
	        	"paymentMethodId"=>"s",
	        	"markdownId"=>"s",
	        	"differenceInQty"=>"i",
	        	"differenceInValue"=>"s",
	        	"offlineUserId"=>"s",
	        	"offlineInsertTime"=>"s",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("tillPaymentDiscrepancy",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Till payment discrepancy updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating till payment discrepancy. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating till payment discrepancy. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"result"=>array("tillPaymentDiscrepancy_insertBy_userId"=>$insertBy_userId));
	}
	public function addPaymentMovement($tillPaymentMovement, $options=array()){
		/**
		* Adds till payment movement
		* (array)$tillPaymentMovement{
		*	(array) {
		*		"tillPaymentMovement_tillOperationId"=>offline generated id
		*		"tillPaymentMovement_paymentMethodId"
		*		"tillPaymentMovement_markdownId"
		*		"tillPaymentMovement_amount"
		*		"tillPaymentMovement_note"
		*		"tillCashShot_offlineInsertTime"
		*		"tillCashShot_offlineUserId"
		*	}
		*	(array) $options{
		*		insertBy_userId
		*	}
		* }
		*/
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

		$col = array(
	       		"tillOperationId",
	        	"paymentMethodId",
	        	"amount",
	        	"markdownId",
	        	"note",
	        	"offlineUserId",
	        	"offlineInsertTime",
			"insertBy_userId",
			"insertIp"
	        );
	        $ip = $this->filePath->getUserIp();
	        
		foreach($tillPaymentMovement as $tillPaymentMovement_element){
			if($tillPaymentMovement_element['tillPaymentMovement_tillOperationId'] && $tillPaymentMovement_element['tillPaymentMovement_paymentMethodId'] && !$tillPaymentMovement_element['tillPaymentMovement_insertBy_userId']){
			        $val[] = array(
			        	"tillOperationId"=>$tillPaymentMovement_element['tillPaymentMovement_tillOperationId'],
			        	"paymentMethodId"=>$tillPaymentMovement_element['tillPaymentMovement_paymentMethodId'],
			        	"amount"=>$tillPaymentMovement_element['tillPaymentMovement_amount'],
			        	"markdownId"=>$tillPaymentMovement_element['tillPaymentMovement_markdownId'],
			        	"note"=>$tillPaymentMovement_element['tillPaymentMovement_note'],
			        	"offlineUserId"=>$tillPaymentMovement_element['tillPaymentMovement_offlineUserId'],
			        	"offlineInsertTime"=>$tillPaymentMovement_element['tillPaymentMovement_offlineInsertTime'],
					"insertBy_userId"=>$insertBy_userId,
					"insertIp"=>$ip
			        );
		        }
		}
	        $type = array(
	        	"tillOperationId"=>"s",
	        	"paymentMethodId"=>"s",
	        	"amount"=>"s",
	        	"markdownId"=>"s",
	        	"note"=>"s",
	        	"offlineUserId"=>"s",
	        	"offlineInsertTime"=>"s",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("tillPaymentMovement",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Till payment movement updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating till payment movement. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating till payment movement. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"result"=>array("tillPaymentMovement_insertBy_userId"=>$insertBy_userId));
	}
	public function searchTillsOperation($options=array()){
		/**
			* Search among till operations
			* $options {
			*	(array) operationType =>array("operationType"=>"open","operationType"=>"close")
			*	(array) tillsId => array(array("tillId"=>),...);
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to brandsDetail.onlineInsertTime DESC
			*	countOnly: ony return count if 1. default to 0
			* returns(array){
			*	operationType
			*	tillId
			*	offlineUserId
			*	offlineInsertTime
			*	operationId
			*	insertBy_userId
			*	insertIp
			*	onlineInsertTime
			* }
		*/
		$operationType = array();
		$tillsId = array();
		$insertBy_userId = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$limit = -1;
		$start = 0;
		$orderBy = " tillOperation.offlineInsertTime DESC ";
		$countOnly = 0;
		
		$result = array();
		$status = 0;
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        	
        	if($countOnly){
        		$sql = "SELECT COUNT(tillOperation.operationId) AS count ";
        	}else{
		        $sql = "SELECT tillOperation.operationId AS `tillOperation_operationId`, tillOperation.tillId AS `tillOperation_tillId`, tillOperation.operationType AS `tillOperation_operationType`, tillOperation.offlineUserId AS `tillOperation_offlineUserId`, tillOperation.offlineInsertTime AS `tillOperation_offlineInsertTime`,
		        	tillOperation.onlineInsertTime AS tillOperation_onlineInsertTime, tillOperation.insertBy_userId AS tillOperation_insertBy_userId, tillOperation.insertIp AS tillOperation_insertIp ";
		}
		$sql .= " FROM tillOperation ";
		$sql .= " WHERE tillOperation.operationId ";
		
		if(!empty(array_filter($tillsId))>0){
			$sql .= " AND (tillOperation.tillId = :tillId ";
	        	$vals[':tillId'] = $tillsId[0]['tillId'];
	        	$types[':tillId'] = "s";
	        	foreach($tillsId as $key=>$element){
	        		$sql .= " OR tillOperation.tillId = :tillId".$key;
	        		$vals[':tillId'.$key] = $element['tillId'];
	        		$types[':tillId'.$key] = "s";
	        	}
	        	$sql .= ")";
		}
		if(!empty(array_filter($operationType))>0){
			$sql .= " AND (tillOperation.operationType = :operationType ";
	        	$vals[':operationType'] = $operationType[0]['operationType'];
	        	$types[':operationType'] = "s";
	        	foreach($operationType as $key=>$element){
	        		$sql .= " OR tillOperation.operationType = :operationType".$key;
	        		$vals[':operationType'.$key] = $element['operationType'];
	        		$types[':operationType'.$key] = "s";
	        	}
	        	$sql .= ")";
		}
		if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (tillOperation.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR tillOperation.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(tillOperation.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(tillOperation.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
		
		$sql .= " GROUP BY tillOperation.operationId ";
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	
	public function searchTillPaymentShot($options=array()){
		/**
			* Search among till payment shots
			* $options {
			*	(array) operationsId => array(array("tillOperation_operationId"=>),...);
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to tillPaymentShot.paymentMethodId
			*	countOnly: ony return count if 1. default to 0
			* returns(array){
			*	tillPaymentShot_tillOperationId
			*	tillPaymentShot_paymentMethodId
			*	tillPaymentShot_countedQty
			*	tillPaymentShot_countedValue
			*	tillPaymentShot_offlineInsertTime
			*	tillPaymentShot_offlineUserId
			*	tillPaymentShot_insertBy_userId
			*	tillPaymentShot_onlineInsertTime
			*	tillPaymentShot_insertIp 
			* }
		*/
		$operationsId = array();
		$insertBy_userId = array();
		$limit = -1;
		$start = 0;
		$orderBy = " tillPaymentShot.paymentMethodId ";
		$countOnly = 0;
		
		$result = array();
		$status = 0;
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        	
        	if($countOnly){
        		$sql = "SELECT COUNT(tillPaymentShot.tillOperationId) AS count ";
        	}else{
		        $sql = "SELECT tillPaymentShot.tillOperationId AS `tillPaymentShot_tillOperationId`, tillPaymentShot.paymentMethodId AS `tillPaymentShot_paymentMethodId`, tillPaymentShot.countedQty AS `tillPaymentShot_countedQty`, tillPaymentShot.countedValue AS `tillPaymentShot_countedValue`, tillPaymentShot.offlineUserId AS `tillPaymentShot_offlineUserId`, tillPaymentShot.offlineInsertTime AS `tillPaymentShot_offlineInsertTime`,
		        	tillPaymentShot.onlineInsertTime AS tillPaymentShot_onlineInsertTime, tillPaymentShot.insertBy_userId AS tillPaymentShot_insertBy_userId, tillPaymentShot.insertIp AS tillPaymentShot_insertIp ";
		}
		$sql .= " FROM tillPaymentShot ";
		$sql .= " WHERE tillPaymentShot.tillOperationId ";
		
		if(!empty(array_filter($operationsId))>0){
			$sql .= " AND (tillPaymentShot.tillOperationId = :tillOperationId ";
	        	$vals[':tillOperationId'] = $operationsId[0]['tillOperation_operationId'];
	        	$types[':tillOperationId'] = "s";
	        	foreach($operationsId as $key=>$element){
	        		$sql .= " OR tillPaymentShot.tillOperationId = :tillOperationId".$key;
	        		$vals[':tillOperationId'.$key] = $element['tillOperation_operationId'];
	        		$types[':tillOperationId'.$key] = "s";
	        	}
	        	$sql .= ")";
		}
		if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (tillPaymentShot.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR tillPaymentShot.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(tillPaymentShot.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(tillPaymentShot.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
		
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	
	public function searchTillPaymentMovement($options=array()){
		/**
			* Search among till payment movement
			* $options {
			*	(array) operationsId => array(array("tillOperation_operationId"=>),...);
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to tillPaymentShot.paymentMethodId
			*	countOnly: ony return count if 1. default to 0
			* returns(array){
			*	tillPaymentMovement_tillOperationId
			*	tillPaymentMovement_paymentMethodId
			*	tillPaymentMovement_amount
			*	tillPaymentMovement_markdownId
			*	tillPaymentMovement_note
			*	tillPaymentMovement_offlineInsertTime
			*	tillPaymentMovement_offlineUserId
			*	tillPaymentMovement_insertBy_userId
			*	tillPaymentMovement_onlineInsertTime
			*	tillPaymentMovement_insertIp 
			* }
		*/
		$operationsId = array();
		$insertBy_userId = array();
		$limit = -1;
		$start = 0;
		$orderBy = " tillPaymentMovement.paymentMethodId ";
		$countOnly = 0;
		
		$result = array();
		$status = 0;
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        	
        	if($countOnly){
        		$sql = "SELECT COUNT(tillPaymentMovement.tillOperationId) AS count ";
        	}else{
		        $sql = "SELECT tillPaymentMovement.tillOperationId AS `tillPaymentMovement_tillOperationId`, tillPaymentMovement.paymentMethodId AS `tillPaymentMovement_paymentMethodId`, tillPaymentMovement.amount AS `tillPaymentMovement_amount`, tillPaymentMovement.markdownId AS `tillPaymentMovement_markdownId`, tillPaymentMovement.note AS `tillPaymentMovement_note`, tillPaymentMovement.offlineUserId AS `tillPaymentMovement_offlineUserId`, tillPaymentMovement.offlineInsertTime AS `tillPaymentMovement_offlineInsertTime`,
		        	tillPaymentMovement.onlineInsertTime AS tillPaymentMovement_onlineInsertTime, tillPaymentMovement.insertBy_userId AS tillPaymentMovement_insertBy_userId, tillPaymentMovement.insertIp AS tillPaymentMovement_insertIp ";
		}
		$sql .= " FROM tillPaymentMovement ";
		$sql .= " WHERE tillPaymentMovement.tillOperationId ";
		
		if(!empty(array_filter($operationsId))>0){
			$sql .= " AND (tillPaymentMovement.tillOperationId = :tillOperationId ";
	        	$vals[':tillOperationId'] = $operationsId[0]['tillOperation_operationId'];
	        	$types[':tillOperationId'] = "s";
	        	foreach($operationsId as $key=>$element){
	        		$sql .= " OR tillPaymentMovement.tillOperationId = :tillOperationId".$key;
	        		$vals[':tillOperationId'.$key] = $element['tillOperation_operationId'];
	        		$types[':tillOperationId'.$key] = "s";
	        	}
	        	$sql .= ")";
		}
		if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (tillPaymentMovement.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR tillPaymentMovement.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(tillPaymentMovement.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(tillPaymentMovement.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
		
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
}

?>