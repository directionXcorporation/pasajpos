<?php

class storesModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor,$lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
		$this->lang = $lang;
	}
	public function addBrandId($options=array()){
		/**
			* Creates a unique brandId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	brandId: Created brandId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$brandId){
			$brandId = $this->loginClass->generateSecureId("brandId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"brandId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"brandId"=>$brandId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"brandId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("brandsId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "brand id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating brand id. Please try again. Administrator is informed of the problem.EX74";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating brand id. Please try again. Administrator is informed of the problem.EX80";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("brandId"=>$brandId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addBrandsStatus($brandId,$brandStatus,$insertBy_userId=''){
		/**
		* Add/Change Brand Status
		* brandId : Id of brand to change
		* brandStatus: Status of brand. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	brandId: Id of brand trying to change
		*	id: id of this record
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
	        	"brandId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"brandId"=>$brandId,
	            	"status"=>$brandStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"brandId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("brandsStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "brand status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating brand status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating brand status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("brandId"=>$brandId,"status"=>$status,"id"=>$id,"msg"=>$msg);
	}
	
	public function addBrandsDetail($brandId,$options){
		/**
		* Add/Change Brand Details
		* brandId : Id of brand to change
		* (array) options{
		*	brandName: Name of brand
		*	brandCode: Code of brand
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	brandId: Id of brand trying to change
		*	id: id of this record
		* }
		*/
		$brandName = "";
		$brandCode = "";
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
	        	"brandId",
	        	"brandName",
	        	"brandCode",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"brandId"=>$brandId,
	        	"brandName"=>$brandName,
	            	"brandCode"=>$brandCode,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"brandId"=>"s",
	        	"brandName"=>"s",
			"brandCode"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("brandsDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "brand details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating brand details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating brand details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("brandId"=>$brandId,"status"=>$status,"id"=>$id,"msg"=>$msg);
	}
	
	public function addBrandsParent($brandId,$options=array()){
		/**
		* Adds parents to brand
		* brandId: brandId to add parents to
		* (array)$options{
		*	(array) $parents{
		*		"brandId"=>$brandId,
		*		"status"=>$status
		*	}
		*	insertBy_userId
		* }
		*/
		$insertBy_userId = "";
		$parents = array();
        
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
	       		"brandId",
	        	"parent_brandId",
	        	"status",
			"insertBy_userId",
			"insertIp"
	        );
		foreach($parents as $parents_element){
		        $val[] = array(
		        	"brandId"=>$brandId,
		        	"parent_brandId"=>$parents_element['brandId'],
		        	"status"=>$parents_element['status'],
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$this->filePath->getUserIp()
		        );
		}
	        $type = array(
	        	"brandId"=>"s",
	        	"parent_brandId"=>"s",
	        	"status"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("brandsParent",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "brand Parents updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating brand Parents. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating brand Parents. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"brandId"=>$brandId);
	}
	
	public function searchBrands($options=array()){
    		/**
			* Search among Brands table items
			* $options {
			*	(array)brandsId: an array of group ids in form of array(array('brandId'=>value),...)
			*	(array)brandName: {an array of the brand name} in form of array(array('brandName'=>value),...)
			*	(array)brandCode: {an array of the brand code} in form of array(array('brandCode'=>value),...)
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status of brand. Default to 1:Active
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to brandsDetail.onlineInsertTime DESC
			*	countOnly: ony return count if 1. default to 0
			* returns(array){
			*	status: 0:failed, 1: success
			*	id
			*	brandId
			*	brandName
			*	brandCode
			*	insertBy_userId
			*	insertIp
			*	onlineInsertTime
			* }
		*/
		$brandsId = array();
		$brandName = array();
		$brandCode = array();
		$insertBy_userId = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " brandsDetail.onlineInsertTime DESC ";
		$countOnly = 0;
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        	
        	if($countOnly){
        		$sql = "SELECT COUNT(brandsDetail.id) AS count ";
        	}else{
		        $sql = "SELECT brandsDetail.id AS id, brandsDetail.brandId AS brandId, brandsDetail.brandName AS brandName, brandsDetail.brandCode AS brandCode, 
		        	brandsDetail.onlineInsertTime AS onlineInsertTime, brandsDetail.insertBy_userId AS insertBy_userId, brandsDetail.insertIp AS insertIp, 
		        	GROUP_CONCAT(IF(brandsParent.status,brandsParent.parent_brandId,NULL)) AS parent_brandId, 
		        	brandsStatus.status AS status ";
		}
	        $sql .=" FROM brandsDetail 
	        		INNER JOIN brandsStatus ON brandsStatus.brandId = brandsDetail.brandId 
	        		LEFT JOIN brandsStatus AS brandsStatus2 ON brandsStatus2.brandId = brandsStatus.brandId AND brandsStatus2.onlineInsertTime GREATERTHAN brandsStatus.onlineInsertTime 
	        		LEFT JOIN brandsDetail AS brandsDetail2 ON brandsDetail.brandId = brandsDetail2.brandId AND brandsDetail2.onlineInsertTime GREATERTHAN brandsDetail.onlineInsertTime 
	        		LEFT JOIN brandsParent ON brandsParent.brandId = brandsDetail.brandId 
	        		LEFT JOIN brandsParent AS brandsParent2 ON brandsParent.brandId = brandsParent2.brandId AND brandsParent.parent_brandId = brandsParent2.parent_brandId AND brandsParent2.onlineInsertTime GREATERTHAN brandsParent.onlineInsertTime ";

	        $sql .=" WHERE brandsDetail2.id IS NULL AND brandsStatus2.id IS NULL AND (brandsParent.id IS NULL OR (brandsParent.id IS NOT NULL AND brandsParent2.id IS NULL  AND brandsParent.status=1)) AND brandsParent2.id IS NULL ";

	        if($status){
	        	$sql .= " AND brandsStatus.status=1 ";
	        }

	        if(!empty(array_filter($brandsId))>0){
	        	$sql .= " AND (brandsDetail.brandId = :brandId ";
	        	$vals[':brandId'] = $brandsId[0]['brandId'];
	        	$types[':brandId'] = "s";
	        	foreach($brandsId as $key=>$element){
	        		$sql .= " OR brandsDetail.brandId = :brandId".$key;
	        		$vals[':brandId'.$key] = $element['brandId'];
	        		$types[':brandId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }

	        if(!empty(array_filter($brandName))>0){
	        	$sql .= " AND (brandsDetail.brandName = :brandName ";
	        	$vals[':brandName'] = $brandName[0]['brandName'];
	        	$types[':brandName'] = "s";
	        	foreach($brandName as $key=>$element){
	        		$sql .= " OR brandsDetail.brandName = :brandName".$key;
	        		$vals[':brandName'.$key] = $element['brandName'];
	        		$types[':brandName'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($brandCode))>0){
	        	$sql .= " AND (brandsDetail.brandCode = :brandCode ";
	        	$vals[':brandCode'] = $brandCode[0]['brandCode'];
	        	$types[':brandCode'] = "s";
	        	foreach($brandCode as $key=>$element){
	        		$sql .= " OR brandsDetail.brandCode = :brandCode".$key;
	        		$vals[':brandCode'.$key] = $element['brandCode'];
	        		$types[':brandCode'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (brandsDetail.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR brandsDetail.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(brandsDetail.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(brandsDetail.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
		
		$sql .= " GROUP BY brandsDetail.brandId ";
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }
		
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        if($results['status']){
	        	$results = $results['rows'];
	        }else{
	        	$results = array();
	        }
	        return $results;
	}
	
	
	
	public function searchStores($options=array()){
    		/**
			* Search among Stores table items
			* $options {
			*	(array)storesId: an array of store ids in form of array(array('storeId'=>value),...)
			*	(array)storeName: an array of the store names in form of array(array('storeName'=>value), ...)
			*	(array)storesCode: an array of the store codes in form of array(array('storeCode'=>value), ...)
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	(array)brandsId: an array of brand ids in form of array(array('brandId'=>value),...) to search stores only in that brand
			*	(Array)storesAddress array(
			*		array(
			*		country=> where we want to limit our search to. Will be ignored if state or city is provided
			*		state=> where we want to limit our search to. Will be ignored if city is provided.  Will override country
			*		city=> where we want to limit our search to. Will override state and country
			*		),
			*		...
			*	)
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status of store. Default to 1:Active
			*	limit: limit number of results default to 10; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to storesDetail.storeCode,brandsDetail.brandCode
			*	countOnly: ony return count if 1. default to 0
			* returns(array){
			*	status: 0:failed, 1: success
			*	id
			*	storeId
			*	storeName
			*	storeCode
			*	brandId
			*	storeAddress
			*	storePhone
			*	storeEmail
			*	storeCell
			*	storeAddress
			*	storePostalCode
			*	storeCity
			*	storeState
			*	storeCountry
			*	insertBy_userId
			*	insertIp
			*	onlineInsertTime
			*	receipts
			
			*	sortBy: what is this query order by
			*	sortableColumns: what are teh sortable columns that user can sort by for this query
			
			* or only count
			* }
		*/
		$storesId = array();
		$storeName = array();
		$storesCode = array();
		$brandsId = array();
		$state = array();
		$city = array();
		$receipts = array();
		
		$insertBy_userId = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = 10;
		$start = 0;
		$orderBy = " storesDetail.storeCode,brandsDetail.brandId ";
		$countOnly = 0;
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        	if(!$orderBy){
        		$orderBy = " storesDetail.storeCode,storesIdbrandsId.brandId ";
        	}
        	if($countOnly){
        		$sql = "SELECT COUNT(storesDetail.id) AS count ";
        	}else{
		        $sql = "SELECT storesDetail.id AS `storesDetail_id`, storesDetail.storeId AS `storesDetail_storeId`, 
		        	storesDetail.storeName AS `storesDetail_storeName`, storesDetail.storeCode AS `storesDetail_storeCode`, 
		        	storesDetail.onlineInsertTime AS `storesDetail_onlineInsertTime`, storesDetail.insertBy_userId AS `storesDetail_insertBy_userId`,
		        	storesDetail.insertIp AS `storesDetail_insertIp`, 
		        	storesContact.phone AS `storesContact_phone`, storesContact.cell AS `storesContact_cell`, storesContact.email AS `storesContact_email`, 
		        	storesContact.addressLine1 AS `storesContact_addressLine1`, storesContact.addressLine2 AS `storesContact_addressLine2`, 
		        	storesContact.city AS `storesContact_city`, storesContact.state AS `storesContact_state`, storesContact.country AS `storesContact_country`,
		        	storesContact.zipcode AS `storesContact_zipcode`,
		        	GROUP_CONCAT(storesIdbrandsId.brandId) AS `storesIdbrandsId_brandIdGroup`,
		        	GROUP_CONCAT(brandsDetail.brandName) AS `brandsDetail_brandNameGroup`, GROUP_CONCAT(brandsDetail.brandCode) AS `brandsDetail_brandCodeGroup`,
		        	storesStatus.status AS `storesStatus_status` ";
		        
		        $sql .= " ,GROUP_CONCAT(CONCAT(storesIdreceiptsId.receiptType,':',storesIdreceiptsId.receiptId)) AS `storesIdreceiptsId_receiptIdTypeGroup` ";
		}
	        $sql .=" FROM storesDetail 
	        		LEFT JOIN storesDetail AS storesDetail2 ON storesDetail.storeId = storesDetail2.storeId AND storesDetail2.onlineInsertTime GREATERTHAN storesDetail.onlineInsertTime 

	        		INNER JOIN storesStatus ON storesStatus.storeId = storesDetail.storeId 
	        		LEFT JOIN storesStatus AS storesStatus2 ON storesStatus2.storeId = storesStatus.storeId AND storesStatus2.onlineInsertTime GREATERTHAN storesStatus.onlineInsertTime 

	        		LEFT JOIN storesContact ON storesContact.storeId = storesDetail.storeId 
	        		LEFT JOIN storesContact AS storesContact2 ON storesContact.storeId = storesContact2.storeId AND storesContact2.onlineInsertTime GREATERTHAN storesContact.onlineInsertTime 

	        		LEFT JOIN storesIdbrandsId ON storesIdbrandsId.storeId = storesDetail.storeId 
	        		LEFT JOIN storesIdbrandsId AS storesIdbrandsId2 ON storesIdbrandsId.storeId = storesIdbrandsId2.storeId AND storesIdbrandsId.brandId = storesIdbrandsId2.brandId AND storesIdbrandsId2.onlineInsertTime GREATERTHAN storesIdbrandsId.onlineInsertTime ";
		
		$sql .= " LEFT JOIN brandsDetail ON brandsDetail.brandId = storesIdbrandsId.brandId 
			LEFT JOIN brandsDetail AS brandsDetail2 ON brandsDetail2.brandId = brandsDetail.brandId AND brandsDetail2.onlineInsertTime GREATERTHAN brandsDetail.onlineInsertTime
			
			LEFT JOIN brandsStatus ON brandsStatus.brandId = storesIdbrandsId.brandId 
			LEFT JOIN brandsStatus AS brandsStatus2 ON brandsStatus2.brandId = brandsStatus.brandId AND brandsStatus2.onlineInsertTime GREATERTHAN brandsStatus.onlineInsertTime ";
		
		$sql .= " LEFT JOIN storesIdreceiptsId ON storesIdreceiptsId.storeId = storesDetail.storeId 
			LEFT JOIN storesIdreceiptsId AS storesIdreceiptsId2 ON storesIdreceiptsId.storeId = storesIdreceiptsId2.storeId AND storesIdreceiptsId.receiptType = storesIdreceiptsId2.receiptType AND storesIdreceiptsId2.onlineInsertTime GREATERTHAN storesIdreceiptsId.onlineInsertTime ";
			
	        $sql .=" WHERE storesDetail2.id IS NULL AND storesStatus2.id IS NULL AND (storesIdbrandsId.id IS NULL OR (storesIdbrandsId.id IS NOT NULL AND storesIdbrandsId2.id IS NULL  AND storesIdbrandsId.status=1)) AND storesIdbrandsId2.id IS NULL AND (brandsDetail.id IS NULL OR (brandsDetail.id IS NOT NULL AND brandsDetail2.id Is NULL)) AND (brandsStatus.id IS NULL OR (brandsStatus.id IS NOT NULL AND brandsStatus2.id Is NULL AND brandsStatus.status=1) AND brandsStatus2.id IS NULL) AND (storesContact.id IS NULL OR (storesContact.id IS NOT NULL AND storesContact2.id IS NULL)) ";
	        
	        $sql .= " AND storesIdreceiptsId2.storeId IS NULL ";

	        if($status){
	        	$sql .= " AND storesStatus.status=1 ";
	        }
	        
	        if(!empty(array_filter($storesId))>0){
	        	$sql .= " AND (storesDetail.storeId = :storeId ";
	        	$vals[':storeId'] = $storesId[0]['storeId'];
	        	$types[':storeId'] = "s";
	        	foreach($storesId as $key=>$element){
	        		$sql .= " OR storesDetail.storeId = :storeId".$key;
	        		$vals[':storeId'.$key] = $element['storeId'];
	        		$types[':storeId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }

	        if(!empty(array_filter($brandsId))>0){
	        	$sql .= " AND (storesIdbrandsId.brandId = :brandId ";
	        	$vals[':brandId'] = $brandsId[0]['brandId'];
	        	$types[':brandId'] = "s";
	        	foreach($brandsId as $key=>$element){
	        		$sql .= " OR storesIdbrandsId.brandId = :brandId".$key;
	        		$vals[':brandId'.$key] = $element['brandId'];
	        		$types[':brandId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }

	        if(!empty(array_filter($storesCode))>0){
	        	$sql .= " AND (storesDetail.storeCode = :storeCode ";
	        	$vals[':storeCode'] = $storesCode[0]['storeCode'];
	        	$types[':storeCode'] = "s";
	        	foreach($storeCode as $key=>$element){
	        		$sql .= " OR storesDetail.storeCode = :storeCode".$key;
	        		$vals[':storeCode'.$key] = $element['storeCode'];
	        		$types[':storeCode'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($storesAddress))>0){
	        	$sql .= " AND (";
	        	foreach($storesAddress as $key=>$element){
	        		if($element['city']){
	        			$sql .= " storesContact.city = :city".$key;
	        			$vals[':city'.$key] = $element['city'];
	        			$types[':city'.$key] = "s";
	        		}else if($element['state']){
	        			$sql .= " storesContact.state = :state".$key;
	        			$vals[':state'.$key] = $element['city'];
	        			$types[':state'.$key] = "s";
	        		}else if($element['country']){
	        			$sql .= " storesContact.country = :country".$key;
	        			$vals[':country'.$key] = $element['city'];
	        			$types[':country'.$key] = "s";
	      			}
	        		$sql .= " OR ";
	        	}
	        	$sql = rtrim($sql," OR ");
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (storesDetail.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR storesDetail.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(storesDetail.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(storesDetail.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
		
		if(!$countOnly){
			$sql .= " GROUP BY storesDetail.storeId ";
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"storesDetail_storeId",
	        	"storesDetail_onlineInsertTime",
	        	"storesIdbrandsId_brandIdGroup",
	        	"storesDetail_storeCode", 
	        	"storesContact_state",
	        	"storesContact_country",
	        	"storesContact_city",
	        	"brandsDetail_brandCodeGroup"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	
	public function addStoreId($options=array()){
		/**
			* Creates a unique storeId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	storeId: Created storeId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$storeId){
			$storeId = $this->loginClass->generateSecureId("storeId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"storeId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"storeId"=>$storeId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"storeId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("storesId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "store id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating store id. Please try again. Administrator is informed of the problem.EX695";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating store id. Please try again. Administrator is informed of the problem.EX701";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("storesId_storeId"=>$storeId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addStoresDetail($storeId,$options){
		/**
		* Add/Change Store Details
		* $storeId: Id of store to change
		* (array) options{
		*	storeName: Name of store
		*	storeCode: Code of store
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	storesDetail_storeId: Id of store trying to change
		*	storesDetail_id: id of this record
		* }
		*/
		$storeName = "";
		$storeCode = "";
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
	        	"storeId",
	        	"storeName",
	        	"storeCode",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"storeId"=>$storeId,
	        	"storeName"=>$storeName,
	            	"storeCode"=>$storeCode,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"storeId"=>"s",
	        	"storeName"=>"s",
			"storeCode"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("storesDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "store details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating store details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating store details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("storesDetail_storeId"=>$storeId,"status"=>$status,"storesDetail_id"=>$id,"msg"=>$msg);
	}
	
	public function addStoresStatus($storeId,$storeStatus,$insertBy_userId=''){
		/**
		* Add/Change Store Status
		* storeId : Id of store to change
		* storeStatus: Status of store. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	storesDetail_storeId: Id of store trying to change
		*	storesStatus_id: id of this record
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
	        	"storeId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"storeId"=>$storeId,
	            	"status"=>$storeStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"storeId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("storesStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "store status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating store status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating store status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("storesDetail_storeId"=>$storeId,"status"=>$status,"storesStatus_id"=>$id,"msg"=>$msg);
	}
	
	public function addStoresReceipt($storeId,$options=array()){
		/**
		* Adds receipts to store
		* storeId: storeId to add receipts to
		* (array)$options{
		*	(array) $receipts{
		*		"receiptId"=>$receiptId,
		*		"receiptType"=>$receiptType
		*	}
		*	insertBy_userId
		* }
		*/
		$insertBy_userId = "";
		$receipts = array();
        
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
	        	"receiptId",
	        	"receiptType",
			"insertBy_userId",
			"insertIp"
	        );
	        $userIp = $this->filePath->getUserIp();
		foreach($receipts as $receipts_element){
		        $val[] = array(
		        	"storeId"=>$storeId,
		        	"receiptId"=>$receipts_element['storesIdreceiptsId_receiptId'],
		        	"receiptType"=>$receipts_element['storesIdreceiptsId_receiptType'],
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$userIp
		        );
		}
	        $type = array(
	        	"storeId"=>"s",
	        	"receiptId"=>"s",
	        	"receiptType"=>"s",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("storesIdreceiptsId",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Store brands updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating store brands. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating store brands. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"storesDetail_storeId"=>$storeId);
	}
	
	public function addStoresBrand($storeId,$options=array()){
		/**
		* Adds brands to store
		* storeId: storeId to add brands to
		* (array)$options{
		*	(array) $parents{
		*		"brandId"=>$brandId,
		*		"status"=>$status
		*	}
		*	insertBy_userId
		* }
		*/
		$insertBy_userId = "";
		$brands = array();
        
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
	        	"brandId",
	        	"status",
			"insertBy_userId",
			"insertIp"
	        );
	        $userIp = $this->filePath->getUserIp();
		foreach($brands as $brands_element){
		        $val[] = array(
		        	"storeId"=>$storeId,
		        	"brandId"=>$brands_element['brandId'],
		        	"status"=>$brands_element['status'],
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$userIp
		        );
		}
	        $type = array(
	        	"storeId"=>"s",
	        	"brandId"=>"s",
	        	"status"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("storesIdbrandsId",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Store brands updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating store brands. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating store brands. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"storesDetail_storeId"=>$storeId);
	}
	
	public function addStoresContact($storeId,$options){
		/**
		* Add/Change Store Contact
		* $storeId: Id of store to change
		* (array) options{
		*	phone:
		*	cell: 
		*	email
		*	addressLine1
		*	addressLine2
		*	city
		*	state
		*	country
		*	zipcode
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	storesDetail_storeId: Id of store trying to change
		*	storesContact_id: id of this record
		* }
		*/
		$phone = "";
		$cell = "";
		$email = "";
		$addressLine1 = "";
		$addressLine2 = "";
		$city = "";
		$state = "";
		$country = "";
		$zipcode = "";
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
	        	"storeId",
	        	"phone",
	        	"cell",
	        	"email",
	        	"addressLine1",
	        	"addressLine2",
	        	"city",
	        	"state",
	        	"country",
	        	"zipcode",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"storeId"=>$storeId,
	        	"phone"=>$phone,
	        	"cell"=>$cell,
	        	"email"=>$email,
	        	"addressLine1"=>$addressLine1,
	        	"addressLine2"=>$addressLine2,
	        	"city"=>$city,
	        	"state"=>$state,
	        	"country"=>$country,
	        	"zipcode"=>$zipcode,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"storeId"=>"s",
	        	"phone"=>"s",
	        	"cell"=>"s",
	        	"email"=>"s",
	        	"addressLine1"=>"s",
	        	"addressLine2"=>"s",
	        	"city"=>"s",
	        	"state"=>"s",
	        	"country"=>"s",
	        	"zipcode"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("storesContact",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "store contact updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating store contact. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating store contact. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("storesDetail_storeId"=>$storeId,"status"=>$status,"storesContact_id"=>$id,"msg"=>$msg);
	}
	
	public function searchTills($options=array()){
		/**
			* Search among Stores table items
			* $options {
			*	(array)tillId: an array of till ids in form of array(array('tillId'=>value),...)
			*	(array)tillNumber: an array of the till Number in form of array(array('tillNumber'=>value), ...)
			*	(array)tillCode: an array of the till codes in form of array(array('tillCode'=>value), ...)
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	(array)storesId: an array of store ids in form of array(array('storeId'=>value),...) to search tills only in that store
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status of till. Default to 1:Active
			*	limit: limit number of results default to 10; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to tillsDetail.tillCode,storesDetail.storeCode
			*	countOnly: ony return count if 1. default to 0
			* returns(array){
			*	status: 0:failed, 1: success
			*	tillsDetail_id
			*	tillsDetail_tillId
			*	tillsDetail_tillNumber
			*	tillsDetail_tillCode
			*	tillsDetail_storeId: if the store is disabled, does not show anyhting
			*	storesDetail_storeCode
			*	storesDetail_storeName
			*	insertBy_userId
			*	insertIp
			*	onlineInsertTime
			
			*	sortBy: what is this query order by
			*	sortableColumns: what are the sortable columns that user can sort by for this query
			
			* or only count
			* }
		*/
		$tillId = array();
		$tillNumber = array();
		$tillCode = array();
		$storesId = array();
		
		$insertBy_userId = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = 10;
		$start = 0;
		$orderBy = " tillsDetail.tillCode,storesDetail.storeCode ";
		$countOnly = 0;
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        	if(!$orderBy){
        		$orderBy = " tillsDetail.tillCode,storesDetail.storeCode ";
        	}
        	if($countOnly){
        		$sql = "SELECT COUNT(tillsDetail.id) AS count ";
        	}else{
		        $sql = "SELECT tillsDetail.id AS `tillsDetail_id`, tillsDetail.tillId AS `tillsDetail_tillId`, 
		        	tillsDetail.tillNumber AS `tillsDetail_tillNumber`, tillsDetail.tillCode AS `tillsDetail_tillCode`, tillsDetail.storeId AS `tillsDetail_storeId`, 
		        	tillsDetail.onlineInsertTime AS `tillsDetail_onlineInsertTime`, tillsDetail.insertBy_userId AS `tillsDetail_insertBy_userId`,
		        	tillsDetail.insertIp AS `tillsDetail_insertIp`, 
		        	storesDetail.storeId AS `storesDetail_storeId`, storesDetail.storeCode AS `storesDetail_storeCode`, storesDetail.storeName AS `storesDetail_storeName`,
		        	tillsStatus.status AS `tillsStatus_status` ";
		}
	        $sql .=" FROM tillsDetail 
	        		LEFT JOIN tillsDetail AS tillsDetail2 ON tillsDetail.tillId = tillsDetail2.tillId AND tillsDetail2.onlineInsertTime GREATERTHAN tillsDetail.onlineInsertTime 

	        		INNER JOIN tillsStatus ON tillsStatus.tillId = tillsDetail.tillId 
	        		LEFT JOIN tillsStatus AS tillsStatus2 ON tillsStatus2.tillId = tillsStatus.tillId AND tillsStatus2.onlineInsertTime GREATERTHAN tillsStatus.onlineInsertTime 

	        		INNER JOIN storesDetail ON storesDetail.storeId = tillsDetail.storeId 
	        		LEFT JOIN storesDetail AS storesDetail2 ON storesDetail.storeId = storesDetail2.storeId AND storesDetail2.onlineInsertTime GREATERTHAN storesDetail.onlineInsertTime 

	        		INNER JOIN storesStatus ON storesStatus.storeId = tillsDetail.storeId 
			LEFT JOIN storesStatus AS storesStatus2 ON storesStatus2.storeId = storesStatus.storeId AND storesStatus2.onlineInsertTime GREATERTHAN storesStatus.onlineInsertTime ";
			
	        $sql .=" WHERE tillsDetail2.id IS NULL AND tillsStatus2.id IS NULL AND (storesDetail2.id IS NULL AND storesStatus2.id Is NULL AND storesStatus.status=1) ";

	        if($status){
	        	$sql .= " AND tillsStatus.status = 1 ";
	        }
	        
	        if(!empty(array_filter($tillId))>0){
	        	$sql .= " AND (tillsDetail.tillId = :tillId ";
	        	$vals[':tillId'] = $tillId[0]['tillId'];
	        	$types[':tillId'] = "s";
	        	foreach($tillId as $key=>$element){
	        		$sql .= " OR tillsDetail.tillId = :tillId".$key;
	        		$vals[':tillId'.$key] = $element['tillId'];
	        		$types[':tillId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }

	        if(!empty(array_filter($tillNumber))>0){
	        	$sql .= " AND (tillsDetail.tillNumber = :tillNumber ";
	        	$vals[':tillNumber'] = $tillNumber[0]['tillNumber'];
	        	$types[':tillNumber'] = "i";
	        	foreach($tillNumber as $key=>$element){
	        		$sql .= " OR tillsDetail.tillNumber = :tillNumber".$key;
	        		$vals[':tillNumber'.$key] = $element['tillNumber'];
	        		$types[':tillNumber'.$key] = "i";
	        	}
	        	$sql .= ")";
	        }

	        if(!empty(array_filter($tillCode))>0){
	        	$sql .= " AND (tillsDetail.tillCode = :tillCode ";
	        	$vals[':tillCode'] = $tillCode[0]['tillCode'];
	        	$types[':tillCode'] = "s";
	        	foreach($tillCode as $key=>$element){
	        		$sql .= " OR tillsDetail.tillCode = :tillCode".$key;
	        		$vals[':tillCode'.$key] = $element['tillCode'];
	        		$types[':tillCode'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($storesId))>0){
	        	$sql .= " AND (tillsDetail.storeId = :storeId ";
	        	$vals[':storeId'] = $storesId[0]['storeId'];
	        	$types[':storeId'] = "s";
	        	foreach($tillCode as $key=>$element){
	        		$sql .= " OR tillsDetail.storeId = :storeId".$key;
	        		$vals[':storeId'.$key] = $element['storeId'];
	        		$types[':storeId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (tillsDetail.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR tillsDetail.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(tillsDetail.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(tillsDetail.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
		
		if(!$countOnly){
			$sql .= " GROUP BY tillsDetail.tillId ";
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);

	        $results['sortableColumns'] = array(
	        	"tillsDetail_tillId",
	        	"tillsDetail_onlineInsertTime",
	        	"tillsDetail_tillNumber",
	        	"tillsDetail_tillCode", 
	        	"storesDetail_storeCode"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	
	public function addTillId($options=array()){
		/**
			* Creates a unique tillId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	tillId: Created tillId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$tillId){
			$tillId = $this->loginClass->generateSecureId("tillId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"tillId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"tillId"=>$tillId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"tillId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("tillsId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Till id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating till id. Please try again. Administrator is informed of the problem.EX1269";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating till id. Please try again. Administrator is informed of the problem.EX1275";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("tillsId_tillId"=>$tillId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addTillStatus($tillId,$tillStatus,$insertBy_userId=''){
		/**
		* Add/Change Till Status
		* tillId : Id of till to change
		* tillStatus: Status of till. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	tillsDetail_tillId: Id of till trying to change
		*	tillStatus_id: id of this record
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
	        	"tillId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"tillId"=>$tillId,
	            	"status"=>$tillStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"tillId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("tillsStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Till status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating till status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating till status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("tillsDetail_tillId"=>$storeId,"status"=>$status,"tillsStatus_id"=>$id,"msg"=>$msg);
	}
	
	public function addTillsDetail($tillId,$options){
		/**
		* Add/Change Till Details
		* $tillId: Id of till to change
		* (array) options{
		*	tillNumber: Number of this till
		*	tillCode: Code of till
		*	storeId: Id of store this till belongs to
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	tillsDetail_tillId: Id of till trying to change
		*	tillsDetail_id: id of this record
		* }
		*/
		$tillNumber = "";
		$tillCode = "";
		$storeId = "";
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
	        	"tillId",
	        	"tillNumber",
	        	"tillCode",
	        	"storeId",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"tillId"=>$tillId,
	        	"tillNumber"=>$tillNumber,
	        	"tillCode"=>$tillCode,
	            	"storeId"=>$storeId,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"tillId"=>"s",
	        	"tillNumber"=>"i",
	        	"tillCode"=>"s",
			"storeId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("tillsDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Till details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating till details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating till details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("tillsDetail_tillId"=>$tillId,"status"=>$status,"tillsDetail_id"=>$id,"msg"=>$msg);
	}
	
	public function getCountrylist(){
	
	}
}

?>