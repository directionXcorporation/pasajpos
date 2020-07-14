<?php

class itemsModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor, $lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
		$this->lang = $lang;
	}
	public function addItemCategoryId($options=array()){
		/**
			* Creates a unique categoryId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	itemCategoryId: Created categoryId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$itemCategoryId){
			$itemCategoryId = $this->loginClass->generateSecureId("itemCategoryId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"itemCategoryId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"itemCategoryId"=>$itemCategoryId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"itemCategoryId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("itemCategoriesId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Item category id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating item category id. Please try again. Administrator is informed of the problem.EX64";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating item Category id. Please try again. Administrator is informed of the problem.EX70";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("itemCategoryId"=>$itemCategoryId,"status"=>$status,"msg"=>$msg);
	}
	
	public function additemCategoriyStatus($itemCategoryId,$itemCategoryStatus,$insertBy_userId=''){
		/**
		* Add/Change item Category Status
		* itemCategoryId : Id of item Category to change
		* itemCategoryStatus: Status of item Category. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	itemCategoryId: Id of item Category trying to change
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
	        	"itemCategoryId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"itemCategoryId"=>$itemCategoryId,
	            	"status"=>$brandStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"itemCategoryId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("itemCategoriesStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "item Category status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating item Category status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating item Category status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("itemCategoryId"=>$itemCategoryId,"status"=>$status,"id"=>$id,"msg"=>$msg);
	}
	
	public function additemCategoriesDetail($itemCategoryId,$options){
		/**
		* Add/Change item Category Details
		* itemCategoryId : Id of item Category to change
		* (array) options{
		*	itemCategoryName: Name of item Category
		*	itemCategoryCode: Code of item Category
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	itemCategoryId: Id of item Category trying to change
		*	id: id of this record
		* }
		*/
		$itemCategoryName = "";
		$itemCategoryCode = "";
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
	        	"itemCategoryId",
	        	"itemCategoryName",
	        	"itemCategoryCode",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"itemCategoryId"=>$itemCategoryId,
	        	"itemCategoryName"=>$itemCategoryName,
	            	"itemCategoryCode"=>$itemCategoryCode,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"itemCategoryId"=>"s",
	        	"itemCategoryName"=>"s",
			"itemCategoryCode"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("itemCategoriesDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "item Category details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating item Category details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating item category details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("itemCategoryId"=>$brandId,"status"=>$status,"id"=>$id,"msg"=>$msg);
	}
	
	public function additemCategoriesParent($itemCategoryId,$options=array()){
		/**
		* Adds parents to item Category
		* brandId: itemCategoryId to add parents to
		* (array)$options{
		*	(array) $parents{
		*		"itemCategoryId"=>$itemCategoryId,
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
	       		"itemCategoryId",
	        	"parent_itemCategoryId",
	        	"status",
			"insertBy_userId",
			"insertIp"
	        );
		foreach($parents as $parents_element){
		        $val[] = array(
		        	"itemCategoryId"=>$itemCategoryId,
		        	"parent_itemCategoryId"=>$parents_element['itemCategoryId'],
		        	"status"=>$parents_element['status'],
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$this->filePath->getUserIp()
		        );
		}
	        $type = array(
	        	"itemCategoryId"=>"s",
	        	"parent_itemCategoryId"=>"s",
	        	"status"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("itemCategoriesParent",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "item Category Parents updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating item Category Parents. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating item Category Parents. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"itemCategoryId"=>$itemCategoryId);
	}
	
	public function searchItemCategories($options=array()){
    		/**
			* Search among itemCategoriesDetail table
			* $options {
			*	(array)itemCategoriesId: an array of item Category ids in form of array(array('itemCategoryId'=>value),...)
			*	(array)itemCategoriesName: {an array of the itemCategory name} in form of array(array('itemCategoryName'=>value),...)
			*	(array)itemCategoriesCode: {an array of the itemCategory code} in form of array(array('itemCategoryCode'=>value),...)
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status of brand. Default to 1:Active
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to itemCategoriesDetail.onlineInsertTime DESC
			*	countOnly: ony return count if 1. default to 0
			* returns(array){
			*	status: 0:failed, 1: success
			*	id
			*	itemCategoryId
			*	itemCategoryName
			*	itemCategoryCode
			*	insertBy_userId
			*	insertIp
			*	onlineInsertTime
			* }
		*/
		$itemCategoriesId = array();
		$itemCategoriesName = array();
		$itemCategoriesCode = array();
		$insertBy_userId = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " itemCategoriesDetail.onlineInsertTime DESC ";
		$countOnly = 0;
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        	
        	if($countOnly){
        		$sql = "SELECT COUNT(DISTINCT(itemCategoriesDetail.id)) AS count ";
        	}else{
		        $sql = "SELECT itemCategoriesDetail.id AS id, itemCategoriesDetail.itemCategoryId AS `itemCategoriesDetail_itemCategoryId`,  itemCategoriesDetail.itemCategoryName AS `itemCategoriesDetail_itemCategoryName`, itemCategoriesDetail.itemCategoryCode AS `itemCategoriesDetail_itemCategoryCode`, 
		        	itemCategoriesDetail.onlineInsertTime AS `itemCategoriesDetail_onlineInsertTime`, itemCategoriesDetail.insertBy_userId AS `itemCategoriesDetail_insertBy_userId`, itemCategoriesDetail.insertIp AS `itemCategoriesDetail_insertIp`, 
		        	GROUP_CONCAT(IF(itemCategoriesParent.status,itemCategoriesParent.parent_itemCategoryId,NULL)) AS `itemCategoriesParent_parent_itemCategoryId`, 
		        	itemCategoriesStatus.status AS `itemCategoriesStatus_status` ";
		}
	        $sql .=" FROM itemCategoriesDetail 
	        		LEFT JOIN itemCategoriesDetail AS itemCategoriesDetail2 ON itemCategoriesDetail.itemCategoryId = itemCategoriesDetail2.itemCategoryId AND itemCategoriesDetail2.onlineInsertTime GREATERTHAN itemCategoriesDetail.onlineInsertTime 
	        		
	        		INNER JOIN itemCategoriesStatus ON itemCategoriesStatus.itemCategoryId = itemCategoriesDetail.itemCategoryId 
	        		LEFT JOIN itemCategoriesStatus AS itemCategoriesStatus2 ON itemCategoriesStatus2.itemCategoryId = itemCategoriesStatus.itemCategoryId AND itemCategoriesStatus2.onlineInsertTime GREATERTHAN itemCategoriesStatus.onlineInsertTime 
	        		
	        		LEFT JOIN itemCategoriesParent ON itemCategoriesParent.itemCategoryId = itemCategoriesDetail.itemCategoryId 
	        		LEFT JOIN itemCategoriesParent AS itemCategoriesParent2 ON itemCategoriesParent.itemCategoryId = itemCategoriesParent2.itemCategoryId AND itemCategoriesParent.parent_itemCategoryId = itemCategoriesParent2.parent_itemCategoryId AND itemCategoriesParent2.onlineInsertTime GREATERTHAN itemCategoriesParent.onlineInsertTime ";

	        $sql .=" WHERE itemCategoriesDetail2.id IS NULL AND itemCategoriesStatus2.id IS NULL AND (itemCategoriesParent.id IS NULL OR (itemCategoriesParent.id IS NOT NULL AND itemCategoriesParent2.id IS NULL  AND itemCategoriesParent.status=1)) AND itemCategoriesParent2.id IS NULL ";

	        if($status){
	        	$sql .= " AND itemCategoriesStatus.status=1 ";
	        }

	        if(!empty(array_filter($itemCategoriesId))>0){
	        	$sql .= " AND (itemCategoriesDetail.itemCategoryId = :itemCategoryId ";
	        	$vals[':itemCategoryId'] = $itemCategoriesId[0]['itemCategoryId'];
	        	$types[':itemCategoryId'] = "s";
	        	foreach($itemCategoriesId as $key=>$element){
	        		$sql .= " OR itemCategoriesDetail.itemCategoryId = :brandId".$key;
	        		$vals[':itemCategoryId'.$key] = $element['itemCategoryId'];
	        		$types[':itemCategoryId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }

	        if(!empty(array_filter($itemCategoriesName))>0){
	        	$sql .= " AND (itemCategoriesDetail.itemCategoryName = :itemCategoryName ";
	        	$vals[':itemCategoryName'] = $itemCategoriesName[0]['itemCategoryName'];
	        	$types[':itemCategoryName'] = "s";
	        	foreach($itemCategoriesName as $key=>$element){
	        		$sql .= " OR itemCategoriesDetail.itemCategoryName = :itemCategoryName".$key;
	        		$vals[':itemCategoryName'.$key] = $element['itemCategoryName'];
	        		$types[':itemCategoryName'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($itemCategoriesCode))>0){
	        	$sql .= " AND (itemCategoriesDetail.itemCategoryCode = :itemCategoryCode ";
	        	$vals[':itemCategoryCode'] = $itemCategoriesCode[0]['itemCategoryCode'];
	        	$types[':itemCategoryCode'] = "s";
	        	foreach($itemCategoriesCode as $key=>$element){
	        		$sql .= " OR itemCategoriesDetail.itemCategoryCode = :itemCategoryCode".$key;
	        		$vals[':itemCategoryCode'.$key] = $element['itemCategoryCode'];
	        		$types[':itemCategoryCode'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (itemCategoriesDetail.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR itemCategoriesDetail.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(itemCategoriesDetail.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(itemCategoriesDetail.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
		
		$sql .= " GROUP BY itemCategoriesDetail.itemCategoryId ";
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
	
	
	
	public function searchItems($options=array()){
    		/**
			* Search among Items table items
			* $options {
			*	(array)itemsId: an array of item ids in form of array(array('itemId'=>value),...)
			*	(array)itemsName: an array of the item names in form of array(array('itemName'=>value), ...)
			*	(array)itemsCode: an array of the item codes in form of array(array('itemCode'=>value), ...)
			*	(array)itemsBarcode: an array of the item barcodes in form of array(array('itemBarcode'=>value), ...)
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	(array)brandsId: an array of brand ids in form of array(array('brandId'=>value),...) to search items only in that brand
			*	(array)itemsExternalId: an array of external ids like SAP id array(array('itemExternalId'=>value),...)
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status of item. Default to 1:Active
			*	limit: limit number of results default to 10; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to itemsDetail.onlineInsertTime DESC
			*	countOnly: ony return count if 1. default to 0
			*	properties: what to get. array("price")
			* returns(array){
			*	status: 0:failed, 1: success
			*	itemsDetail_itemId
			*	itemsDetail_itemName
			*	itemsDetail_itemCode
			*	itemsDetail_itembarCode
			*	itemsDetail_itemDescription
			*	itemsIdbrandsId_brandIdGroup
			*	itemsDetail_insertBy_userId
			*	itemsDetail_insertIp
			*	itemsDetail_onlineInsertTime
			
			*	sortBy: what is this query order by
			*	sortableColumns: what are teh sortable columns that user can sort by for this query
			
			* or only count
			* }
		*/
		$itemsId = array();
		$itemsName = array();
		$itemsCode = array();
		$itemsBarcode = array();
		$brandsId = array();
		$itemsExternalId = array();
		
		$insertBy_userId = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = 10;
		$start = 0;
		$orderBy = " itemsDetail.onlineInsertTime DESC ";
		$countOnly = 0;
		$properties = array();
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        	if(!$orderBy){
        		$orderBy = " itemsDetail.onlineInsertTime DESC ";
        	}
        	if($countOnly){
        		$sql = "SELECT COUNT(DISTINCT(itemsDetail.itemId)) AS count ";
        	}else{
		        $sql = "SELECT itemsDetail.itemId AS `itemsDetail_itemId`, 
		        	itemsDetail.itemName AS `itemsDetail_itemName`, itemsDetail.itemCode AS `itemsDetail_itemCode`, itemsDetail.itemBarcode AS `itemsDetail_itemBarcode`, itemsDetail.itemExternalId AS `itemsDetail_itemExternalId`, itemsDetail.itemDescription AS `itemsDetail_itemDescription`, 
		        	itemsDetail.onlineInsertTime AS `itemsDetail_onlineInsertTime`, itemsDetail.insertBy_userId AS `itemsDetail_insertBy_userId`,
		        	itemsDetail.insertIp AS `itemsDetail_insertIp`,  
		        	GROUP_CONCAT(itemsIdbrandsId.brandId) AS `itemsIdbrandsId_brandIdGroup`,
		        	GROUP_CONCAT(brandsDetail.brandName) AS `brandsDetail_brandNameGroup`, GROUP_CONCAT(brandsDetail.brandCode) AS `brandsDetail_brandCodeGroup`,
		        	itemsStatus.status AS `itemsStatus_status` ";
		}
	        $sql .=" FROM itemsDetail 
	        		LEFT JOIN itemsDetail AS itemsDetail2 ON itemsDetail.itemId = itemsDetail2.itemId AND itemsDetail2.onlineInsertTime GREATERTHAN itemsDetail.onlineInsertTime 

	        		INNER JOIN itemsStatus ON itemsStatus.itemId = itemsDetail.itemId 
	        		LEFT JOIN itemsStatus AS itemsStatus2 ON itemsStatus2.itemId = itemsStatus.itemId AND itemsStatus2.onlineInsertTime GREATERTHAN itemsStatus.onlineInsertTime 

	        		LEFT JOIN itemsIdbrandsId ON itemsIdbrandsId.itemId = itemsDetail.itemId 
	        		LEFT JOIN itemsIdbrandsId AS itemsIdbrandsId2 ON itemsIdbrandsId.itemId = itemsIdbrandsId2.itemId AND itemsIdbrandsId.brandId = itemsIdbrandsId2.brandId AND itemsIdbrandsId2.onlineInsertTime GREATERTHAN itemsIdbrandsId.onlineInsertTime ";
		
		$sql .= " LEFT JOIN brandsDetail ON brandsDetail.brandId = itemsIdbrandsId.brandId 
			LEFT JOIN brandsDetail AS brandsDetail2 ON brandsDetail2.brandId = brandsDetail.brandId AND brandsDetail2.onlineInsertTime GREATERTHAN brandsDetail.onlineInsertTime
			
			LEFT JOIN brandsStatus ON brandsStatus.brandId = itemsIdbrandsId.brandId 
			LEFT JOIN brandsStatus AS brandsStatus2 ON brandsStatus2.brandId = brandsStatus.brandId AND brandsStatus2.onlineInsertTime GREATERTHAN brandsStatus.onlineInsertTime ";
			
	        $sql .=" WHERE itemsDetail2.itemId IS NULL AND itemsStatus2.itemId IS NULL AND (itemsIdbrandsId.itemId IS NULL OR (itemsIdbrandsId.itemId IS NOT NULL AND itemsIdbrandsId2.itemId IS NULL  AND itemsIdbrandsId.status=1)) AND itemsIdbrandsId2.itemId IS NULL AND (brandsDetail.id IS NULL OR (brandsDetail.id IS NOT NULL AND brandsDetail2.id Is NULL)) AND (brandsStatus.id IS NULL OR (brandsStatus.id IS NOT NULL AND brandsStatus2.id Is NULL AND brandsStatus.status=1) AND brandsStatus2.id IS NULL)  ";

	        if($status){
	        	$sql .= " AND itemsStatus.status=1 ";
	        }
	        
	        if(!empty(array_filter($itemsId))>0){
	        	$sql .= " AND (itemsDetail.itemId = :itemId ";
	        	$vals[':itemId'] = $itemsId[0]['itemId'];
	        	$types[':itemId'] = "s";
	        	foreach($itemsId as $key=>$element){
	        		$sql .= " OR itemsDetail.itemId = :itemId".$key;
	        		$vals[':itemId'.$key] = $element['itemId'];
	        		$types[':itemId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }

	        if(!empty(array_filter($brandsId))>0){
	        	$sql .= " AND (itemsIdbrandsId.brandId = :brandId ";
	        	$vals[':brandId'] = $brandsId[0]['brandId'];
	        	$types[':brandId'] = "s";
	        	foreach($brandsId as $key=>$element){
	        		$sql .= " OR itemsIdbrandsId.brandId = :brandId".$key;
	        		$vals[':brandId'.$key] = $element['brandId'];
	        		$types[':brandId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }

	        if(!empty(array_filter($itemsCode))>0){
	        	$sql .= " AND (itemsDetail.itemCode = :itemCode ";
	        	$vals[':itemCode'] = $storesCode[0]['itemCode'];
	        	$types[':itemCode'] = "s";
	        	foreach($itemsCode as $key=>$element){
	        		$sql .= " OR itemsDetail.itemCode = :itemCode".$key;
	        		$vals[':itemCode'.$key] = $element['itemCode'];
	        		$types[':itemCode'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if(!empty(array_filter($itemsBarcode))>0){
	        	$sql .= " AND (itemsDetail.itemBarcode = :itemBarcode ";
	        	$vals[':itemBarcode'] = $storesCode[0]['itemBarcode'];
	        	$types[':itemBarcode'] = "s";
	        	foreach($itemsBarcode as $key=>$element){
	        		$sql .= " OR itemsDetail.itemBarcode = :itemBarcode".$key;
	        		$vals[':itemBarcode'.$key] = $element['itemBarcode'];
	        		$types[':itemBarcode'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if(!empty(array_filter($itemsExternalId))>0){
	        	$sql .= " AND (itemsDetail.itemExternalId = :itemExternalId ";
	        	$vals[':itemExternalId'] = $itemsExternalId[0]['itemExternalId'];
	        	$types[':itemExternalId'] = "s";
	        	foreach($itemsExternalId as $key=>$element){
	        		$sql .= " OR itemsDetail.itemExternalId = :itemExternalId".$key;
	        		$vals[':itemExternalId'.$key] = $element['itemExternalId'];
	        		$types[':itemExternalId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        
	        if(!empty(array_filter($insertBy_userId))>0){
	        	$sql .= " AND (itemsDetail.insertBy_userId = :insertBy_userId ";
	        	$vals[':insertBy_userId'] = $insertBy_userId[0];
	        	$types[':insertBy_userId'] = "s";
	        	foreach($insertBy_userId as $key=>$element){
	        		$sql .= " OR itemsDetail.insertBy_userId = :insertBy_userId".$key;
	        		$vals[':insertBy_userId'.$key] = $element;
	        		$types[':insertBy_userId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_start){
	        	$sql .= " AND (DATE(itemsDetail.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start)) ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";	
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(itemsDetail.onlineInsertTime) LESSTHAN= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";	
	        }
		
		if(!$countOnly){
			$sql .= " GROUP BY itemsDetail.itemId ";
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }

	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"itemsDetail_itemId",
	        	"itemsDetail_onlineInsertTime",
	        	"itemsIdbrandsId_brandIdGroup",
	        	"itemsDetail_itemCode", 
	        	"itemsDetail_itemBarcode",
	        	"itemsDetail_externalId",
	        	"brandsDetail_brandCodeGroup"
	        	);
	        $results['sortBy'] = trim($orderBy);
	        return $results;
	}
	
	public function searchItemPrice($options=array()){
		/**
		Get Prices for an item in all currencies
		(array)options{
			(array) itemsId: array of form Array(array("itemId"=>value),...)
			(array) properties: array(
				"itemDetails"=>array("itemName","itemBarcode","itemBarcode_currencyId"),
				"itemPrice"=>array("itemId","price","currencyId","validFrom","status")
			),
			onlineInsertTime_start
			countOnly: default to 0
			brandsId: array(array("brandId"=>value),...)
			onlyValid: only get valid prices. default to 1
			onlyActive: only get acitve prices. default to 1
		}
		return{
			msg
			status
			prices
		}
		*/
		$onlyValid = 1;
		$onlyActive = 1;
		$properties = array();

		$onlineInsertTime_start = "";
		$countOnly = 0;
		$brandsId = array();
		
		$prices = array();
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        if($countOnly){
        		$sql = "SELECT COUNT(*) AS count ";
        	}else{
        		if(count($properties)>0){
        			$sqlArray = array();
        			$sql = "SELECT ";
        			foreach($properties as $table => $columnArray){
        				if($table != "concat"){
        					foreach($columnArray as $column){
        						$sqlArray[] = $table.".".$column." AS "." `".$table."_".$column."` ";
        					}
        				}else{
        					$sqlArray[] = "CONCAT(".$columnArray.") AS `".str_replace(",","_",str_replace(".","_",$columnArray))."` ";
        				}
        			}
        			$sql .= implode(",", $sqlArray);
        		}else{
	        		$sql = "SELECT itemsPrice.itemId AS `itemsPrice_itemId`, itemsPrice.price AS `itemsPrice_price`, itemsPrice.currencyId AS `itemsPrice_currencyId`, itemsPrice.validFrom AS `itemsPrice_validFrom`, itemsPrice.status AS `itemsPrice_status` ";
	        	}
	       	}
	        $sql .= " FROM itemsPrice 
	        	LEFT JOIN itemsPrice AS itemsPrice2 ON itemsPrice.itemId = itemsPrice2.itemId AND itemsPrice.currencyId = itemsPrice2.currencyId AND itemsPrice2.onlineInsertTime GREATERTHAN itemsPrice.onlineInsertTime ";
	        if(isset($properties['itemsDetail'])){
	        	$sql .= " INNER JOIN itemsDetail ON itemsDetail.itemId=itemsPrice.itemId 
	        		LEFT JOIN itemsDetail AS itemsDetail2 ON itemsDetail2.itemId=itemsDetail.itemId AND itemsDetail2.onlineInsertTime GREATERTHAN itemsDetail.onlineInsertTime 
	        		INNER JOIN itemsStatus ON itemsStatus.itemId=itemsDetail.itemId 
	        		LEFT JOIN itemsStatus AS itemsStatus2 ON itemsStatus.itemId=itemsStatus2.itemId AND itemsStatus2.onlineInsertTime GREATERTHAN itemsStatus.onlineInsertTime ";
	        }
	        if(!empty(array_filter($brandsId))>0){
	        	$sql .= " INNER JOIN itemsIdbrandsId ON itemsIdbrandsId.itemId=itemsPrice.itemId 
	        		LEFT JOIN itemsIdbrandsId AS itemsIdbrandsId2 ON itemsIdbrandsId.itemId=itemsIdbrandsId2.itemId AND itemsIdbrandsId2.onlineInsertTime GREATERTHAN itemsIdbrandsId.onlineInsertTime ";
	        }
	        $sql .= " WHERE 
	        	itemsPrice2.itemId IS NULL ";
	        	if($onlyActive){
		        	$sql .= " AND itemsPrice.status=1 ";
		        }
		        if(isset($properties['itemsDetail'])){
		        	$sql .= " AND itemsDetail2.itemId IS NULL AND itemsStatus2.itemId IS NULL AND itemsStatus.status=1 ";
		        }
		        if(!empty(array_filter($brandsId))>0){
		        	$sql .= " AND itemsIdbrandsId2.itemId IS NULL AND itemsIdbrandsId.status=1 ";
		        	$sql .= " AND (itemsIdbrandsId.brandId = :brandId ";
		        	$vals[':brandId'] = $brandsId[0]['brandId'];
		        	$types[':brandId'] = "s";
		        	foreach($brandsId as $key=>$element){
		        		$sql .= " OR itemsIdbrandsId.brandId = :brandId".$key;
		        		$vals[':brandId'.$key] = $element['brandId'];
		        		$types[':brandId'.$key] = "s";
		        	}
		        	$sql .= " ) ";
		        }
	        	if(!empty(array_filter($itemsId))>0){
		        	$sql .= " AND (itemsPrice.itemId = :itemId ";
		        	$vals[':itemId'] = $itemsId[0]['itemId'];
		        	$types[':itemId'] = "s";
		        	foreach($itemsId as $key=>$element){
		        		$sql .= " OR itemsPrice.itemId = :itemId".$key;
		        		$vals[':itemId'.$key] = $element['itemId'];
		        		$types[':itemId'.$key] = "s";
		        	}
		        	$sql .= ")";
		        }
		        if($onlyValid){
		        	$sql .= " AND (DATE(itemsPrice.validFrom) LESSTHAN= DATE(:validFrom)) ";
		        	$date = date_create($validFrom);
		        	$validFrom = date_format($date, 'Y-m-d');
		        	$vals[':validFrom'] = $validFrom;
		        	$types[':validFrom'] = "s";	
		        }
		        
		        if($onlineInsertTime_start){
		        	$sql .= " AND (DATE(itemsPrice.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start) ";
		        	if(isset($properties['itemsDetail'])){
		        		$sql .= " OR DATE(itemsDetail.onlineInsertTime) GREATERTHAN= DATE(:onlineInsertTime_start_itemDetail) ";
		        	}
		        	$sql .= " ) ";
		        	$date = date_create($onlineInsertTime_start);
		        	$onlineInsertTime_start = date_format($date, 'Y-m-d');
		        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
		        	$types[':onlineInsertTime_start'] = "s";
		        	
		        	if(isset($properties['itemsDetail'])){
			        	$vals[':onlineInsertTime_start_itemDetail'] = $onlineInsertTime_start;
			        	$types[':onlineInsertTime_start_itemDetail'] = "s";
		        	}	
		        }
	        
		        $results = $this->db->pdoSelect($sql,$vals,$types);
		        return $results;
	}
	
	public function addItemId($options=array()){
		/**
			* Creates a unique itemId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this itemId
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
		
		if(!$itemId){
			$itemId = $this->loginClass->generateSecureId("itemId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"itemId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"itemId"=>$itemId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"itemId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("itemsId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Item id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating item id. Please try again. Administrator is informed of the problem.EX686";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating item id. Please try again. Administrator is informed of the problem.EX693";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("itemsId_itemId"=>$itemId,"status"=>$status,"msg"=>$msg);
	}
	
	public function additemsDetail($itemId,$options){
		/**
		* Add/Change Item Details
		* $storeId: Id of item to change
		* (array) options{
		*	itemName: Name of item
		*	itemCode: Code of item
		*	itemBarcode: BarCode of item
		*	itemExternalId: External Id of item, like SAP code
		*	itemDescription:
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	itemsDetail_itemId: Id of item trying to change
		*	itemsDetail_id: id of this record
		* }
		*/
		$itemName = "";
		$itemCode = "";
		$itemBarcode = "";
		$itemDescription = "";
		$itemExternalId = "";
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
	        	"itemId",
	        	"itemName",
	        	"itemCode",
	        	"itemBarcode",
	        	"itemExternalId",
	        	"itemDescription",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"itemId"=>$itemId,
	        	"itemName"=>$itemName,
	            	"itemCode"=>$itemCode,
	            	"itemBarcode"=>$itemBarcode,
	            	"itemExternalId"=>$itemExternalId,
	            	"itemDescription"=>$itemDescription,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"itemId"=>"s",
	        	"itemName"=>"s",
			"itemCode"=>"s",
			"itemBarcode"=>"s",
			"itemExternalId"=>"s",
			"itemDescription"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("itemsDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Item details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating item details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating item details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("itemsDetail_itemId"=>$itemId,"status"=>$status,"itemsDetail_id"=>$id,"msg"=>$msg);
	}
	
	public function addItemsStatus($itemId,$itemStatus,$insertBy_userId=''){
		/**
		* Add/Change Item Status
		* itemId : Id of item to change
		* itemStatus: Status of item. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	itemsDetail_itemId: Id of item trying to change
		*	itemsStatus_id: id of this record
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
	        	"itemId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"itemId"=>$itemId,
	            	"status"=>$itemStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"itemId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("itemsStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Item status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating item status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating item status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("itemsDetail_itemId"=>$itemId,"status"=>$status,"itemsStatus_id"=>$id,"msg"=>$msg);
	}
	
	public function addItemsBrand($itemsId,$options=array()){
		/**
		* Adds brands to items
		* (array-string) itemsId: array of itemId to add brand to array(array("itemId"=>value),...) or just one itemId
		* (array) $options{
		*	(array) $brands{
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
	       		"itemId",
	        	"brandId",
	        	"status",
			"insertBy_userId",
			"insertIp"
	        );
	        $userIp = $this->filePath->getUserIp();
	        if(!is_array($itemsId)){
	        	$itemsId = array(
	        		array("itemId"=>$itemsId)
	        	);
	        }
	        
		foreach($brands as $brands_element){
			foreach($itemsId as $itemsId_element){
			        $val[] = array(
			        	"itemId"=>$itemsId_element['itemId'],
			        	"brandId"=>$brands_element['brandId'],
			        	"status"=>$brands_element['status'],
					"insertBy_userId"=>$insertBy_userId,
					"insertIp"=>$userIp
			        );
		        }
		}
	        $type = array(
	        	"itemId"=>"s",
	        	"brandId"=>"s",
	        	"status"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("itemsIdbrandsId",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Item brands updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating item brands. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating item brands. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"itemsId"=>$itemsId);
	}
	
	public function addItemsPrice($itemsId,$options=array()){
		/**
		* Adds price to items
		* (array-string) itemsId: array of itemId to add price to array(array("itemId"=>value),...) or just one itemId
		* (array) $options{
		*	(array) $prices{
		*		"currencyId"=>$currencyId,
		*		"validFrom"=>$validFrom,
		*		"price"=>$price,
		*		"status"=>$status
		*	}
		*	insertBy_userId
		* }
		*/
		$insertBy_userId = "";
		$prices = array();
        
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
	       		"itemId",
	        	"price",
	        	"currencyId",
	        	"validFrom",
	        	"status",
			"insertBy_userId",
			"insertIp"
	        );
	        $userIp = $this->filePath->getUserIp();
	        if(!is_array($itemsId)){
	        	$itemsId = array(
	        		array("itemId"=>$itemsId)
	        	);
	        }
	        
		foreach($prices as $prices_element){
			$date = new DateTime($prices_element['validFrom']);
			$dateFormatted = $date->format('Y-m-d H:i:s');
			foreach($itemsId as $itemsId_element){
				if($prices_element['price'] && $prices_element['currencyId']){
				        $val[] = array(
				        	"itemId"=>$itemsId_element['itemId'],
				        	"price"=>$prices_element['price'],
				        	"currencyId"=>$prices_element['currencyId'],
				        	"validFrom"=>$dateFormatted,
				        	"status"=>$prices_element['status'],
						"insertBy_userId"=>$insertBy_userId,
						"insertIp"=>$userIp
				        );
			        }
		        }
		}
	        $type = array(
	        	"itemId"=>"s",
	        	"price"=>"s",
	        	"currencyId"=>"s",
	        	"validFrom"=>"s",
	        	"status"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );
		if(!empty(array_filter($val))>0){
		        $result = $this->db->pdoInsert("itemsPrice",$col,$val,$type);
		        if(isset($result['status'])){
		            if($result['status']>0){
		                $status = 1;
		                $msgCode = "Item prices updated successfuly";
		                $msgCat = "OK_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		            }else{
		                $status = 0;
		                $msgCode = "There was a system error updating item prices. Please try again. Administrator is informed of the problem.";
		                $msgCat="SYS_ERR_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		            }
		        }else{
		            $status = 0;
		            $msgCode = "There was a system error updating item prices. Please try again. Administrator is informed of the problem.";
		            $msgCat="SYS_ERR_MSG";
		            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		        }
		}else{
			$status = 1;
			$msgCode = "There was no data to add.";
		        $msgCat="OK_MSG";
		        $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
	        return array("status"=>$status,"msg"=>$msg,"itemsId"=>$itemsId);
	}
}
?>