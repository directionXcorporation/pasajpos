<?php

class inventoryControllerClass{
	protected $langDecode_constructor, $lang, $usersModel_constructor, $filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $inventoryModel_constructor;
	public function __construct($langDecode_constructor,$lang,$usersModel_constructor,$filePath_constructor, $loginClass_constructor, $mainformModel_constructor, $inventoryModel_constructor) {
	        $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->usersModel = $usersModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->mainformModel = $mainformModel_constructor;
		$this->inventoryModel = $inventoryModel_constructor;
	}
	public function addInventoryShotPerStore($storeId, $shot, $options=array()){
		/**
			* Creates an inventory shot for items for on storeId
			* storeId
			* shot array(
			* 	array(
			*		itemId
			*		inventoryShotDetail_quantityOnHand
			*	)
			* )
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
		$status = 0;
		$msg = '';
		
		$inventoryShotHeader = $this->inventoryModel->addInventoryShotHeader($options);
		if($inventoryShotHeader['status']){
			$inventoryShotDetail = $this->inventoryModel->addInventoryShotDetailPerStore($storeId, $inventoryShotHeader['inventoryShotHeader_inventoryShotId'], $shot);
			if($inventoryShotDetail['status']){
				$status = 1;
			}else{
				$status = 0;
				$msg = $inventoryShotDetail['msg'];
			}
		}else{
			$status = 0;
			$msg = $inventoryShotHeader['msg'];
		}
		return array("status"=>$status, "msg"=>$msg, "inventoryShotHeader_inventoryShotId"=>$inventoryShotHeader['inventoryShotHeader_inventoryShotId']);
	}
	public function addInventoryShotPerItem($itemId, $shot, $options=array()){
		/**
			* Creates an inventory shot for item
			* itemId
			* shot array(
			* 	array(
			*		inventoryShotDetail_storeId
			*		inventoryShotDetail_quantityOnHand
			*		inventoryShotHeader_markdownId: default to 0
			*	)
			* )
			* (array) options {
			*	inventoryShotHeader_inventoryShotId
			*	insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	msg: message decoded in user lang
			*	shotId
			* }
		*/
		$status = 0;
		$msg = '';

		foreach($shot as $key=> $element){
			if($element['inventoryShotHeader_markdownId']){
				$options['inventoryShotHeader_markdownId'] = $element['inventoryShotHeader_markdownId'];
			}else{
				$options['inventoryShotHeader_markdownId'] = 0;
			}
			
			$inventoryShotHeader = $this->inventoryModel->addInventoryShotHeader($options);
			if($inventoryShotHeader['status']){
				$inventoryShotDetail = $this->inventoryModel->addInventoryShotDetailPerItemPerStore($itemId, $inventoryShotHeader['inventoryShotHeader_inventoryShotId'], $element['inventoryShotDetail_storeId'], $element['inventoryShotDetail_quantityOnHand']);
				if($inventoryShotDetail['status']){
					$status = 1;
				}else{
					$status = 0;
					$msg = $inventoryShotDetail['msg'];
				}
			}else{
				$status = 0;
				$msg = $inventoryShotHeader['msg'];
			}
		}
		return array("status"=>$status, "msg"=>$msg, "inventoryShotHeader_inventoryShotId"=>$inventoryShotHeader['inventoryShotHeader_inventoryShotId']);
	}
}

?>