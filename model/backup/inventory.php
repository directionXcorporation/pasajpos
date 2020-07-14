<?php

class inventoryModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor, $lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
		$this->lang = $lang;
	}
	
	public function addInventoryShotHeader($options=array()){
		/**
			* Creates an inventory shot Detail for items for on storeId
			* (array) options {
			*	inventoryShotHeader_inventoryShotId
			*	inventoryShotHeader_markdownId: default to 0
			*	insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	msg: message decoded in user lang
			*	shotId
			* }
		*/
		
		$inventoryShotHeader_inventoryShotId = '';
		$insertBy_userId = '';
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		if(!$inventoryShotHeader_markdownId){
			$inventoryShotHeader_markdownId = 0;
		}
		
		if(!$inventoryShotHeader_inventoryShotId){
			$inventoryShotHeader_inventoryShotId = $this->loginClass->generateSecureId("inventoryShotId");
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		$userIp = $this->filePath->getUserIp();
		
		$col = array();
		$val = array();
		$type = array();
		
		$col = array(
	       		"inventoryShotId",
	        	"markdownId",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"inventoryShotId"=>$inventoryShotHeader_inventoryShotId,
	        	"markdownId"=>$inventoryShotHeader_markdownId,
	        	"insertBy_userId"=>$insertBy_userId,
	        	"insertIp"=>$userIp
	        );
	        $type = array(
	        	"inventoryShotId"=>"s",
	        	"markdownId"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        if($inventoryShotHeader_inventoryShotId){
		        $result = $this->db->pdoInsert("inventoryShotHeader",$col,$val,$type);
		        if(isset($result['status'])){
		            if($result['status']>0){
		                $status = 1;
		                $msgCode = "Inventroy Shot Header created successfuly";
		                $msgCat = "OK_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		            }else{
		                $status = 0;
		                $msgCode = "There was a system error creating inventory shot header. Please try again. Administrator is informed of the problem.";
		                $msgCat="SYS_ERR_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		            }
		        }else{
		            $status = 0;
		            $msgCode = "There was a system error creating inventory shot header. Please try again. Administrator is informed of the problem.";
		            $msgCat="SYS_ERR_MSG";
		            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		        }
		}else{
			$status = 0;
			$msgCode = "Error occured while fetching unique Id";
		        $msgCat="OK_MSG";
		        $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
	        return array("status"=>$status,"msg"=>$msg,"inventoryShotHeader_inventoryShotId"=>$inventoryShotHeader_inventoryShotId);
	}
	public function addInventoryShotDetailPerStore($storeId, $inventoryShotId, $shot=array()){
		/**
			* Creates an inventory shot Detail for items for on storeId
			* storeId
			* inventoryShotId
			* shot array(
			* 	array(
			*		itemId
			*		inventoryShotDetail_quantityOnHand
			*	)
			* )
			* returns{
			*	status: 0:failed, 1: success
			*	msg: message decoded in user lang
			* }
		*/

		$col = array();
		$val = array();
		$type = array();

		$col = array(
			"inventoryShotId",
	       		"storeId",
	        	"itemId",
	        	"quantityOnHand"
	        );
	        $userIp = $this->filePath->getUserIp();
	        
	        foreach($shot as $element){
	        	if($element['itemId'] && $element['inventoryShotDetail_quantityOnHand']){
		        	$val[] = array(
		        		"inventoryShotId"=>$inventoryShotId,
		        		"storeId"=>$storeId,
			        	"itemId"=>$element['itemId'],
			        	"quantityOnHand"=>$element['inventoryShotDetail_quantityOnHand']
		        	);
	        	}
	        }
	        $type = array(
	        	"inventoryShotId"=>"s",
	       		"storeId"=>"s",
	        	"itemId"=>"s",
	        	"quantityOnHand"=>"i"
	        );
	        
	        if(!empty(array_filter($val))>0 && $inventoryShotId && $storeId){
		        $result = $this->db->pdoInsert("inventoryShotDetail",$col,$val,$type);
		        if(isset($result['status'])){
		            if($result['status']>0){
		                $status = 1;
		                $msgCode = "Inventroy Shot created successfuly";
		                $msgCat = "OK_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		            }else{
		                $status = 0;
		                $msgCode = "There was a system error creating inventory shot. Please try again. Administrator is informed of the problem.";
		                $msgCat="SYS_ERR_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		            }
		        }else{
		            $status = 0;
		            $msgCode = "There was a system error creating inventory shot. Please try again. Administrator is informed of the problem.";
		            $msgCat="SYS_ERR_MSG";
		            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		        }
		}else{
			$status = 1;
			$msgCode = "There was no data to add.";
		        $msgCat="OK_MSG";
		        $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
	        return array("status"=>$status,"msg"=>$msg);
	}
	
	public function addInventoryShotDetailPerItemPerStore($itemId, $inventoryShotId, $storeId, $quantityOnHand){
		/**
			* Creates an inventory shot Detail for store
			* itemId
			* inventoryShotId
			* storeId
			* quantityOnHand
			* returns{
			*	status: 0:failed, 1: success
			*	msg: message decoded in user lang
			* }
		*/

		$col = array();
		$val = array();
		$type = array();

		$col = array(
			"inventoryShotId",
	       		"storeId",
	        	"itemId",
	        	"quantityOnHand"
	        );
	        $userIp = $this->filePath->getUserIp();

	       	if($storeId){
		       	$val = array(
		       		"inventoryShotId"=>$inventoryShotId,
		       		"storeId"=>$storeId,
		        	"itemId"=>$itemId,
		        	"quantityOnHand"=>$quantityOnHand
		       	);
	       	}
	        $type = array(
	        	"inventoryShotId"=>"s",
	       		"storeId"=>"s",
	        	"itemId"=>"s",
	        	"quantityOnHand"=>"i"
	        );
	        
	        if(!empty(array_filter($val))>0 && $inventoryShotId && $storeId){
		        $result = $this->db->pdoInsert("inventoryShotDetail",$col,$val,$type);
		        if(isset($result['status'])){
		            if($result['status']>0){
		                $status = 1;
		                $msgCode = "Inventroy Shot created successfuly";
		                $msgCat = "OK_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		            }else{
		                $status = 0;
		                $msgCode = "There was a system error creating inventory shot. Please try again. Administrator is informed of the problem.";
		                $msgCat="SYS_ERR_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		            }
		        }else{
		            $status = 0;
		            $msgCode = "There was a system error creating inventory shot. Please try again. Administrator is informed of the problem.";
		            $msgCat="SYS_ERR_MSG";
		            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		        }
		}else{
			$status = 1;
			$msgCode = "There was no data to add.";
		        $msgCat="OK_MSG";
		        $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
	        return array("status"=>$status,"msg"=>$msg);
	}
	
	public function searchInventory($options=array()){
		/**
		* Search inventory
		* (array) options(
		*	itemId
		*	storeId
		*	inventoryOnDate
		* )
		*/
		$sql = "SELECT inventoryShotDetail.quantityOnHand 
			FROM inventoryShotDetail 
			";
	}

}
?>