<?php

class basicSettingsModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor, $lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
	        $this->lang = $lang;
	}
	
	public function searchCashCategories($options=array()){
		
		$status = 1;
		$orderBy = " cashCategories.onlineInsertTime DESC ";
		$vals = array();
		$types = array();
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		$sql = "SELECT cashCategories.id AS `cashCategories_id`, cashCategories.categoryCode AS `cashCategories_categoryCode`, cashCategories.status AS `cashCategories_status`, cashCategories.onlineInsertTime AS `cashCategories_onlineInsertTime` ";
		$sql .= " FROM cashCategories ";
		$sql .= " WHERE cashCategories.id GREATERTHAN 0 ";
		if($status){
	        	$sql .= " AND cashCategories.status = 1 ";
	        }
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	
	public function searchCashDetails($options=array()){
		
		$status = 1;
		$orderBy = " cashTypesDetail.onlineInsertTime DESC ";
		$vals = array();
		$types = array();
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		$sql = "SELECT cashTypesDetail.id AS `cashTypesDetail_id`, cashTypesDetail.cashName AS `cashTypesDetail_cashName`, cashTypesDetail.cashTypeId AS `cashTypesDetail_cashTypeId`, cashTypesDetail.cashValue AS `cashTypesDetail_cashValue`, cashTypesDetail.currencyId AS `cashTypesDetail_currencyId`, cashTypesDetail.insertIp AS `cashTypesDetail_insertIp`, cashTypesStatus.status AS `cashTypesStatus_status`, cashTypesDetail.onlineInsertTime AS `cashTypesDetail_onlineInsertTime`, cashTypesDetail.cashCategoriesId AS `cashTypesDetail_cashCategoriesId` ";
		$sql .= " FROM cashTypesDetail 
			LEFT JOIN cashTypesDetail AS cashTypesDetail2 ON cashTypesDetail.cashTypeId = cashTypesDetail2.cashTypeId AND cashTypesDetail2.onlineInsertTime GREATERTHAN cashTypesDetail.onlineInsertTime ";
		if($status){
			$sql .= " INNER JOIN cashTypesStatus ON cashTypesStatus.cashTypeId = cashTypesDetail.cashTypeId 
				LEFT JOIN cashTypesStatus AS cashTypesStatus2 ON cashTypesStatus.cashTypeId = cashTypesStatus2.cashTypeId AND cashTypesStatus2.onlineInsertTime GREATERTHAN cashTypesStatus.onlineInsertTime ";
		}
		$sql .= " WHERE cashTypesDetail2.cashTypeId IS NULL ";
		if($status){
	        	$sql .= " AND cashTypesStatus.status = 1 AND cashTypesStatus2.status IS NULL ";
	        }
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	
	public function addCashTypesId($options=array()){
		/**
			* Creates a unique cashTypeId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	cashTypesId_cashTypeId: Created cashTypeId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$cashTypeId){
			$cashTypeId = $this->loginClass->generateSecureId("cashTypeId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"cashTypeId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"cashTypeId"=>$cashTypeId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"cashTypeId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("cashTypesId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "CashType id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating cashtype id. Please try again. Administrator is informed of the problem.EX72";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating cashtype id. Please try again. Administrator is informed of the problem.EX78";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("cashTypesDetail_cashTypeId"=>$cashTypeId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addCashTypesDetail($cashTypeId,$options=array()){
		/**
		* Add/Change Cash Type Details
		* $cashTypeId: Id of cashtype to change
		* (array) options{
		*	cashName: 
		*	cashCategoriesId: 
		*	cashValue: 
		*	currencyId:
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	cashTypesDetail_cashTypeId: Id of cash type trying to change
		*	cashTypesDetail_id: id of this record
		* }
		*/
		$cashName = "";
		$cashCategoriesId = 0;
		$cashValue = "0.00";
		$currencyId = "";
		
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
	        	"cashTypeId",
	        	"cashName",
	        	"cashCategoriesId",
	        	"cashValue",
	        	"currencyId",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"cashTypeId"=>$cashTypeId,
	        	"cashName"=>$cashName,
	            	"cashCategoriesId"=>$cashCategoriesId,
	            	"cashValue"=>$cashValue,
	            	"currencyId"=>$currencyId,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"cashTypeId"=>"s",
	        	"cashName"=>"s",
			"cashCategoriesId"=>"i",
			"cashValue"=>"s",
			"currencyId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
	        
		$result = $this->db->pdoInsert("cashTypesDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Cash type details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating Cash type details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating Cash type details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("cashTypesDetail_cashTypeId"=>$cashTypeId,"status"=>$status,"cashTypesDetail_id"=>$id,"msg"=>$msg);
	}
	
	public function addCashTypesStatus($cashTypeId,$cashTypeStatus,$insertBy_userId=''){
		/**
		* Add/Change Cash Type Status
		* cashTypeId: Id of cashtype to change
		* cashTypeStatus: Status of cash type. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	cashTypesDetail_cashTypeId: Id of cash type trying to change
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
	        	"cashTypeId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"cashTypeId"=>$cashTypeId,
	            	"status"=>$cashTypeStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"cashTypeId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );

		$result = $this->db->pdoInsert("cashTypesStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Cash type status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating cash type status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating cash type status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("cashTypesDetail_cashTypeId"=>$cashTypeId,"status"=>$status,"msg"=>$msg);
	}
	
	/**
	*****************************
	********Tax Details**********
	*****************************
	*/
	public function searchTaxDetails($options=array()){
		
		$status = 1;
		$orderBy = " taxDetail.onlineInsertTime DESC ";
		$vals = array();
		$types = array();
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		$sql = "SELECT taxDetail.id AS `taxDetail_id`, taxDetail.taxName AS `taxDetail_taxName`, taxDetail.taxId AS `taxDetail_taxId`, taxDetail.taxPercent AS `taxDetail_taxPercent`, taxDetail.insertIp AS `taxDetail_insertIp`, taxStatus.status AS `taxStatus_status`, taxDetail.onlineInsertTime AS `taxDetail_onlineInsertTime` ";
		$sql .= " FROM taxDetail 
			LEFT JOIN taxDetail AS taxDetail2 ON taxDetail.taxId = taxDetail2.taxId AND taxDetail2.onlineInsertTime GREATERTHAN taxDetail.onlineInsertTime";
		if($status){
			$sql .= " INNER JOIN taxStatus ON taxStatus.taxId = taxDetail.taxId 
				LEFT JOIN taxStatus AS taxStatus2 ON taxStatus.taxId = taxStatus2.taxId AND taxStatus2.onlineInsertTime GREATERTHAN taxStatus.onlineInsertTime ";
		}
		$sql .= " WHERE taxDetail2.taxId IS NULL ";
		if($status){
	        	$sql .= " AND taxStatus.status = 1 AND taxStatus2.status IS NULL ";
	        }
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	public function addTaxId($options=array()){
		/**
			* Creates a unique taxId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	taxId_cashTypeId: Created taxId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$taxId){
			$taxId = $this->loginClass->generateSecureId("taxId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"taxId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"taxId"=>$taxId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"taxId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("taxId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Tax id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating tax id. Please try again. Administrator is informed of the problem.EX344";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating tax id. Please try again. Administrator is informed of the problem.EX350";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("taxDetail_taxId"=>$taxId,"status"=>$status,"msg"=>$msg);
	}
	public function addTaxDetail($taxId,$options=array()){
		/**
		* Add/Change Tax Details
		* $taxId: Id of tax to change
		* (array) options{
		*	taxName: 
		*	taxPercent: 
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	taxDetail_taxId: Id of tax type trying to change
		*	taxDetail_id: id of this record
		* }
		*/
		$taxName = "";
		$taxPercent = "0.00";
		
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
	        	"taxId",
	        	"taxName",
	        	"taxPercent",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"taxId"=>$taxId,
	        	"taxName"=>$taxName,
	            	"taxPercent"=>$taxPercent,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"taxId"=>"s",
	        	"taxName"=>"s",
			"taxPercent"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
	        
		$result = $this->db->pdoInsert("taxDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Tax details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating tax details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating tax details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("taxDetail_taxId"=>$taxId,"status"=>$status,"taxDetail_id"=>$id,"msg"=>$msg);
	}
	
	public function addTaxStatus($taxId,$taxStatus,$insertBy_userId=''){
		/**
		* Add/Change Tax Status
		* taxId: Id of tax to change
		* taxStatus: Status of cash type. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	taxDetail_taxId: Id of tax trying to change
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
	        	"taxId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"taxId"=>$taxId,
	            	"status"=>$taxStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"taxId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );

		$result = $this->db->pdoInsert("taxStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Tax status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating tax status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating tax status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("taxDetail_taxId"=>$taxId,"status"=>$status,"msg"=>$msg);
	}
	
	/**
	************************************
	********Markdown functions**********
	************************************
	*/
	public function searchMarkdownTypes($options=array()){
		
		$status = 1;
		$orderBy = " markdownsType.onlineInsertTime DESC ";
		$vals = array();
		$types = array();
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		$sql = "SELECT markdownsType.markdownType AS `markdownsType_markdownType`, markdownsType.markdownName AS `markdownsType_markdownName`, markdownsType.status AS `markdownsType_status`, markdownsType.onlineInsertTime AS `markdownsType_onlineInsertTime` ";
		$sql .= " FROM markdownsType ";
		$sql .= " WHERE markdownsType.markdownType != '' ";
		if($status){
	        	$sql .= " AND markdownsType.status = 1 ";
	        }
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	
	public function searchMarkdownDetails($options=array()){
		
		$status = 1;
		$orderBy = " markdownsDetail.markdownType DESC ";
		$vals = array();
		$types = array();
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		$sql = "SELECT markdownsDetail.id AS `markdownsDetail_id`, markdownsDetail.markdownId AS `markdownsDetail_markdownId`, markdownsDetail.markdownType AS `markdownsDetail_markdownType`, markdownsDetail.markdownDescription AS `markdownsDetail_markdownDescription`, markdownsDetail.insertIp AS `markdownsDetail_insertIp`, markdownsStatus.status AS `markdownsStatus_status`, markdownsDetail.onlineInsertTime AS `markdownsDetail_onlineInsertTime` ";
		$sql .= " FROM markdownsDetail 
			LEFT JOIN markdownsDetail AS markdownsDetail2 ON markdownsDetail2.markdownId = markdownsDetail.markdownId AND markdownsDetail2.onlineInsertTime GREATERTHAN markdownsDetail.onlineInsertTime";
		if($status){
			$sql .= " INNER JOIN markdownsStatus ON markdownsStatus.markdownId = markdownsDetail.markdownId 
				LEFT JOIN markdownsStatus AS markdownsStatus2 ON markdownsStatus.markdownId = markdownsStatus2.markdownId AND markdownsStatus2.onlineInsertTime GREATERTHAN markdownsStatus.onlineInsertTime ";
		}
		$sql .= " WHERE markdownsDetail2.markdownId IS NULL ";
		if($status){
	        	$sql .= " AND markdownsStatus.status = 1 AND markdownsStatus2.status IS NULL ";
	        }
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	
	public function addMarkdownsId($options=array()){
		/**
			* Creates a unique markdownId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	markdownsId_markdownId: Created markdownId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$cashTypeId){
			$markdownId = $this->loginClass->generateSecureId("markdownId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"markdownId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"markdownId"=>$markdownId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"markdownId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("markdownsId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "markdown Id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating markdown Id. Please try again. Administrator is informed of the problem.EX600";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating cashtype id. Please try again. Administrator is informed of the problem.EX606";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("markdownsDetail_markdownId"=>$markdownId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addMarkdownsDetail($markdownId, $options=array()){
		/**
		* Add/Change Markdown Details
		* $markdownId: Id of markdown to change
		* (array) options{
		*	markdownType: 
		*	markdownDescription: 
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	markdownsDetail_markdownId: Id of markdown trying to change
		*	markdownsDetail_id: id of this record
		* }
		*/
		$markdownType = "";
		$markdownDescription = "";
		
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
	        	"markdownId",
	        	"markdownType",
	        	"markdownDescription",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"markdownId"=>$markdownId,
	        	"markdownType"=>$markdownType,
	            	"markdownDescription"=>$markdownDescription,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"markdownId"=>"s",
	        	"markdownType"=>"s",
			"markdownDescription"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
	        
		$result = $this->db->pdoInsert("markdownsDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Markdown details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating Markdown details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating Markdown details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("markdownsDetail_markdownId"=>$markdownId,"status"=>$status,"markdownsDetail_id"=>$id,"msg"=>$msg);
	}
	
	public function addMarkdownsStatus($markdownId,$markdownStatus,$insertBy_userId=''){
		/**
		* Add/Change Markdown Status
		* markdownId: Id of markdown to change
		* markdownStatus: Status of markdown. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	markdownsDetail_markdownId: Id of markdown trying to change
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
	        	"markdownId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"markdownId"=>$markdownId,
	            	"status"=>$markdownStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"markdownId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );

		$result = $this->db->pdoInsert("markdownsStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Markdown status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating markdown status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating markdown status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("markdownsDetail_markdownId"=>$markdownId,"status"=>$status,"msg"=>$msg);
	}
	
	/**
	***************************
	*****PAYMENT FUNCTIONS*****
	***************************
	*/
	public function searchPaymentMethodExtDetails($options=array()){
		
		$status = 1;
		$orderBy = " paymentExtsDetail.extName ";
		$vals = array();
		$types = array();
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		$sql = "SELECT paymentExtsDetail.extId AS `paymentExtsDetail_extId`, paymentExtsDetail.extName AS `paymentExtsDetail_extName`, paymentExtsDetail.parameters AS `paymentExtsDetail_parameters`, paymentExtsDetail.baseUrl AS `paymentExtsDetail_baseUrl`, paymentExtsDetail.method AS `paymentExtsDetail_method`, paymentExtsDetail.silent AS `paymentExtsDetail_silent`, paymentExtsDetail.onlineInsertTime AS `paymentExtsDetail_onlineInsertTime`, paymentExtsStatus.status AS `paymentExtsStatus_status` ";
		$sql .= " FROM paymentExtsDetail 
			LEFT JOIN paymentExtsDetail AS paymentExtsDetail2 ON paymentExtsDetail2.extId = paymentExtsDetail.extId AND paymentExtsDetail2.onlineInsertTime GREATERTHAN paymentExtsDetail.onlineInsertTime";
		
		$sql .= " INNER JOIN paymentExtsStatus ON paymentExtsStatus.extId = paymentExtsDetail.extId 
			LEFT JOIN paymentExtsStatus AS paymentExtsStatus2 ON paymentExtsStatus.extId = paymentExtsStatus2.extId AND paymentExtsStatus2.onlineInsertTime GREATERTHAN paymentExtsStatus.onlineInsertTime ";
		$sql .= " WHERE paymentExtsStatus2.extId IS NULL AND paymentExtsDetail2.extId IS NULL ";
		if($status){
	        	$sql .= " AND paymentExtsStatus.status = 1 ";
	        }
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	
	public function searchPaymentMethodDetails($options=array()){
		/**
		* Search Payment Methods
		* (array) options: {
		* 	(array) tillIds: array(array('tillsDetail_tillId'=>value),...)
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	rows: 
		* }
		*/
		$status = 1;
		$orderBy = " paymentMethodsDetail.methodCode ";
		$vals = array();
		$types = array();
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		$sql = "SELECT paymentMethodsDetail.methodId AS `paymentMethodsDetail_methodId`, paymentMethodsDetail.methodCode AS `paymentMethodsDetail_methodCode`, paymentMethodsDetail.currencyId AS `paymentMethodsDetail_currencyId`, paymentMethodsDetail.methodName AS `paymentMethodsDetail_methodName`, paymentMethodsDetail.methodDescription AS `paymentMethodsDetail_methodDescription`, paymentMethodsDetail.priceRounding AS `paymentMethodsDetail_priceRounding`, paymentMethodsDetail.sendToExtId AS `paymentMethodsDetail_sendToExtId`, paymentMethodsDetail.recieveFromExtId AS `paymentMethodsDetail_recieveFromExtId`, paymentMethodsDetail.includeInOpeningShot AS `paymentMethodsDetail_includeInOpeningShot`, paymentMethodsDetail.includeInClosingShot AS `paymentMethodsDetail_includeInClosingShot`, paymentMethodsDetail.includeInMovement AS `paymentMethodsDetail_includeInMovement`, paymentMethodsDetail.onlineInsertTime AS `paymentMethodsDetail_onlineInsertTime`, paymentMethodsStatus.status AS `paymentMethodsStatus_status` ";
		$sql .= " FROM paymentMethodsDetail 
			LEFT JOIN paymentMethodsDetail AS paymentMethodsDetail2 ON paymentMethodsDetail2.methodId = paymentMethodsDetail.methodId AND paymentMethodsDetail2.onlineInsertTime GREATERTHAN paymentMethodsDetail.onlineInsertTime ";
		
		$sql .= " INNER JOIN paymentMethodsStatus ON paymentMethodsStatus.methodId = paymentMethodsDetail.methodId 
			LEFT JOIN paymentMethodsStatus AS paymentMethodsStatus2 ON paymentMethodsStatus.methodId = paymentMethodsStatus2.methodId AND paymentMethodsStatus2.onlineInsertTime GREATERTHAN paymentMethodsStatus.onlineInsertTime ";
		if(!empty(array_filter($tillIds))>0){
			$sql .= " INNER JOIN tillIdpaymentMethodId ON tillIdpaymentMethodId.paymentMethodId = paymentMethodsDetail.methodId 
				LEFT JOIN tillIdpaymentMethodId AS tillIdpaymentMethodId2 ON tillIdpaymentMethodId2.paymentMethodId = tillIdpaymentMethodId.paymentMethodId AND tillIdpaymentMethodId2.tillId = tillIdpaymentMethodId.tillId AND tillIdpaymentMethodId2.onlineInsertTime GREATERTHAN tillIdpaymentMethodId.onlineInsertTime ";
		}
		$sql .= " WHERE paymentMethodsStatus2.methodId IS NULL AND paymentMethodsDetail2.methodId IS NULL ";
		if($status){
	        	$sql .= " AND paymentMethodsStatus.status = 1 ";
	        }
	        if(!empty(array_filter($tillIds))>0){
	        	$sql .= " AND tillIdpaymentMethodId2.tillId IS NULL AND tillIdpaymentMethodId.status = 1 AND (tillIdpaymentMethodId.tillId = :tillId ";
	        	$vals[':tillId'] = $tillIds[0]['tillsDetail_tillId'];
	        	$types[':tillId'] = "s";
	        	foreach($tillIds as $key=>$element){
	        		$sql .= " OR tillIdpaymentMethodId.tillId = :tillId".$key;
	        		$vals[':tillId'.$key] = $element['tillsDetail_tillId'];
	        		$types[':tillId'.$key] = "s";
	        	}
	        	$sql .= ")";
	        }
	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	public function addPaymentExtId($options=array()){
		/**
			* Creates a unique extId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	paymentExtsDetail_extId: Created markdownId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$extId){
			$extId = $this->loginClass->generateSecureId("extId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"extId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"extId"=>$extId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"extId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("paymentExtsId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "ext Id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating ext Id. Please try again. Administrator is informed of the problem.EX802";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating ext id. Please try again. Administrator is informed of the problem.EX808";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("paymentExtsDetail_extId"=>$extId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addPaymentExtDetail($extId, $options=array()){
		/**
		* Add/Change Payment Ext Details
		* $extId: Id of ext to change
		* (array) options{
		*	extName: 
		*	parameters: //JSON encoded array
		*	baseUrl: 
		*	method: 
		*	silent: 
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	paymentExtsDetail_extId: Id of markdown trying to change
		*	paymentExtsDetail_id: id of this record
		* }
		*/
		$extName = "";
		$parameters = "";
		$baseUrl = "";
		$method = "";
		$silent = "";
		
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
			"extId",
	        	"extName",
	        	"parameters",
	        	"baseUrl",
	        	"method",
	        	"silent",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"extId"=>$extId,
	        	"extName"=>$extName,
	        	"parameters"=>$parameters,
	            	"baseUrl"=>$baseUrl,
	            	"method"=>$method,
	            	"silent"=>$silent,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"extId"=>"s",
	        	"extName"=>"s",
	        	"parameters"=>"s",
			"baseUrl"=>"s",
			"method"=>"s",
			"silent"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
	        
		$result = $this->db->pdoInsert("paymentExtsDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "ext details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating ext details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating ext details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("paymentExtsDetail_extId"=>$extId,"status"=>$status,"paymentExtsDetail_id"=>$id,"msg"=>$msg);
	}
	public function addPaymentExtStatus($extId,$extStatus,$insertBy_userId=''){
		/**
		* Add/Change Ext Status
		* extId: Id of markdown to change
		* extStatus: Status of ext. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	paymentExtsDetail_extId: Id of ext trying to change
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
	        	"extId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"extId"=>$extId,
	            	"status"=>$extStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"extId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );

		$result = $this->db->pdoInsert("paymentExtsStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Ext status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating ext status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating ext status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("paymentExtsDetail_extId"=>$extId,"status"=>$status,"msg"=>$msg);
	}
	public function addPaymentMethodId($options=array()){
		/**
			* Creates a unique methodId and add it to database
			* Options {
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	paymentMethodsDetail_methodId: Created methodId
			*	msg: message decoded in user lang
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$methodId){
			$methodId = $this->loginClass->generateSecureId("methodId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$col = array(
			"methodId",
			"insertBy_userId",
			"insertIp"
		);
		$val = array(
			"methodId"=>$methodId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
		);
		$type = array(
			"methodId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
		);
		$result = $this->db->pdoInsert("paymentMethodsId",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Method Id updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating method Id. Please try again. Administrator is informed of the problem.EX1074";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating method id. Please try again. Administrator is informed of the problem.EX1080";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
        
		return array("paymentMethodsDetail_methodId"=>$methodId,"status"=>$status,"msg"=>$msg);
	}
	public function addPaymentMethodDetail($methodId, $options=array()){
		/**
		* Add/Change Payment Method Details
		* $methodId: Id of method to change
		* (array) options{
		*	methodCode: 
		*	methodName
		*	currencyId:
		*	methodDescription: 
		*	priceRounding: 
		*	sendToExtId: 
		*	recieveFromExtId
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	paymentMethodsDetail_methodId: Id of method trying to change
		* }
		*/
		$methodCode = "";
		$methodName = "";
		$currencyId = "";
		$methodDescription = "";
		$priceRounding = 1;
		$sendToExtId = "";
		$recieveFromExtId = "";
		
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
			"methodId",
	        	"methodCode",
	        	"currencyId",
	        	"methodName",
	        	"methodDescription",
	        	"priceRounding",
	        	"sendToExtId",
	        	"recieveFromExtId",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"methodId"=>$methodId,
	        	"methodCode"=>$methodCode,
	        	"currencyId"=>$currencyId,
	            	"methodName"=>$methodName,
	            	"methodDescription"=>$methodDescription,
	            	"priceRounding"=>$priceRounding,
	            	"sendToExtId"=>$sendToExtId,
	            	"recieveFromExtId"=>$recieveFromExtId,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"methodId"=>"s",
	        	"methodCode"=>"s",
	        	"currencyId"=>"s",
			"methodName"=>"s",
			"methodDescription"=>"s",
			"priceRounding"=>"i",
			"sendToExtId"=>"s",
			"recieveFromExtId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
	        
		$result = $this->db->pdoInsert("paymentMethodsDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "method details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating method details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating method details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("paymentMethodsDetail_methodId"=>$methodId, "status"=>$status, "msg"=>$msg);
	}
	public function addPaymentMethodStatus($methodId,$methodStatus,$insertBy_userId=''){
		/**
		* Add/Change Method Status
		* methodId: Id of method to change
		* methodStatus: Status of method. 0:inActive, 1:active
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	paymentMethodsDetail_methodId: Id of method trying to change
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
	        	"methodId",
	        	"status",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"methodId"=>$methodId,
	            	"status"=>$methodStatus,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"methodId"=>"s",
			"status"=>"i",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );

		$result = $this->db->pdoInsert("paymentMethodsStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Method status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating method status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating method status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("paymentMethodsDetail_methodId"=>$methodId,"status"=>$status,"msg"=>$msg);
	}
	
	/**
	****************************
	*******BASIC SETTINGS*******
	****************************
	*/
	public function searchBasicSettings($options=array()){
		
		$status = 1;
		$orderBy = " basicSettings.onlineInsertTime DESC ";
		$vals = array();
		$types = array();
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		$sql = "SELECT basicSettings.settingsCode AS `basicSettings_settingsCode`, basicSettings.settingsValue AS `basicSettings_settingsValue`, basicSettings.onlineInsertTime AS `basicSettings_onlineInsertTime`, basicSettings.insertBy_userId AS `basicSettings_insertBy_userIde` ";
		$sql .= " FROM basicSettings 
			LEFT JOIN basicSettings AS basicSettings2 ON basicSettings.settingsCode = basicSettings2.settingsCode AND basicSettings2.onlineInsertTime GREATERTHAN basicSettings.onlineInsertTime ";

		$sql .= " WHERE basicSettings2.settingsCode IS NULL ";

	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        return $results;
	}
	public function addBasicSettings($settingsValueArray){
		/**
		* Add/Change Basic Settings
		* (array) $settingsValueArray: array of array('settingsCode' => settingsValue,...)
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	basicSettingsCode: 
		* }
		*/
		
		$insertBy_userId = "";
		$id = '';
		$val = array();
		if($settingsValueArray['insertBy_userId']){
			$insertBy_userId = $settingsValueArray['insertBy_userId'];
			unset($settingsValueArray['insertBy_userId']);
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
		$col = array(
	        	"settingsCode",
	        	"settingsValue",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        foreach($settingsValueArray as $settingsCode => $settingsValue){
		        $val[] = array(
		        	"settingsCode"=>$settingsCode,
		        	"settingsValue"=>$settingsValue,
		            	"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$this->filePath->getUserIp()
		        );
	        }
	        $type = array(
	        	"settingsCode"=>"s",
	        	"settingsValue"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
	        
		$result = $this->db->pdoInsert("basicSettings",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "Basic Settings updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating basic settings. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating basic settings. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("status"=>$status, "msg"=>$msg);
	}
	
}
?>