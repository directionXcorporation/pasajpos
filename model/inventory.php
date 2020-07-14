<?php

class inventoryModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor, $filePath_constructor, $langDecode_constructor, $loginClass_constructor, $lang="") {
	    $this->db = $db_constructor;
	    $this->langDecode = $langDecode_constructor;
	    $this->filePath = $filePath_constructor;
	    $this->loginClass = $loginClass_constructor;
		$this->lang = $lang;
	}
	
	private function createTemporaryInventoryShot($storeId = '', $itemId = ''){
		$result = array();
		$process = false;
		$vals = array(
			'storeId' => $storeId,
			'itemId'=> $itemId,
			'storeId_nullcheck'=> $storeId,
			'itemId_nullcheck'=> $itemId
		);
		$types = array(
			'storeId' => 's',
			'itemId'=> 's',
			'storeId_nullcheck'=> 's',
			'itemId_nullcheck'=> 's'
		);
		//Empty temp table
		$sql0 = "TRUNCATE temp_inventory";
		
		//insert last inventory shots into temp table
		$sql1 = "INSERT INTO temp_inventory (itemId, storeId, shotQty, shotInsertTime)
			SELECT 
				MAX(inventoryShotDetail.itemId) AS itemId,
				MAX(inventoryShotHeader.storeId) AS storeId,
				COALESCE(MAX(inventoryShotDetail.quantityOnHand), 0) AS shotQty,
				MAX(inventoryShotDetail.onlineInsertTime) AS shotInsertTime
			FROM 
				inventoryShotDetail 
			LEFT JOIN inventoryShotDetail AS inventoryShotDetail2 
					ON inventoryShotDetail2.itemId = inventoryShotDetail.itemId AND inventoryShotDetail2.onlineInsertTime > inventoryShotDetail.onlineInsertTime
				
			INNER JOIN 
				inventoryShotHeader ON inventoryShotHeader.inventoryShotId = inventoryShotDetail.inventoryShotId
			LEFT JOIN inventoryShotHeader AS inventoryShotHeader2 
					ON inventoryShotHeader2.inventoryShotId = inventoryShotHeader.inventoryShotId AND inventoryShotHeader2.onlineInsertTime > inventoryShotHeader.onlineInsertTime 

			WHERE 
				inventoryShotHeader2.inventoryShotId IS NULL AND inventoryShotDetail2.inventoryShotId IS NULL 
				AND inventoryShotHeader.status = 1 
				AND (:itemId_nullcheck = '' OR inventoryShotDetail.itemId = :itemId) 
				AND (:storeId_nullcheck = '' OR inventoryShotHeader.storeId = :storeId) 

			GROUP BY inventoryShotDetail.itemId, inventoryShotDetail.inventoryShotId 
			ON DUPLICATE KEY UPDATE shotQty=VALUES(shotQty), moveQty=0, saleQty=0, lastshotInsertTime=onlineInsertTime";
		
		//insert movements after last shot into temp table
		$sql2 = "INSERT INTO temp_inventory (itemId, storeId, moveQty)
			SELECT 
				MAX(inventoryMovementsDetails.itemId) AS itemId,
				MAX(inventoryMovementsHeader.storeId) AS storeId, 
				COALESCE(SUM(inventoryMovementsDetails.quantity),0) AS moveQty
				
			FROM
				inventoryMovementsDetails 

			INNER JOIN inventoryMovementsHeader 
				ON inventoryMovementsHeader.movementId = inventoryMovementsDetails.movementId 
			LEFT JOIN inventoryMovementsHeader AS inventoryMovementsHeader2 
					ON inventoryMovementsHeader2.movementId = inventoryMovementsHeader.movementId AND inventoryMovementsHeader2.onlineInsertTime > inventoryMovementsHeader.onlineInsertTime 

			LEFT JOIN temp_inventory
				ON inventoryMovementsDetails.itemId = temp_inventory.itemId AND inventoryMovementsHeader.storeId= temp_inventory.storeId
				
			WHERE 
				inventoryMovementsHeader2.movementId IS NULL 
				AND (
					(temp_inventory.itemId IS NULL) OR (temp_inventory.itemId IS NOT NULL AND inventoryMovementsDetails.onlineInsertTime >= temp_inventory.shotInsertTime)
				)
				
			GROUP BY inventoryMovementsDetails.movementId, inventoryMovementsDetails.itemId 
			ON DUPLICATE KEY UPDATE moveQty=VALUES(moveQty)";
		
		//insert sales after last movement into temp table
		$sql3 = "INSERT INTO temp_inventory (itemId, storeId, saleQty)
			SELECT 
				MAX(salesItem.itemId) AS itemId,
				MAX(salesHeader.storeId) AS storeId,	
				COALESCE(-SUM(salesItem.quantity),0) AS saleQty
				
			FROM
				salesItem 
				
			INNER JOIN salesHeader 
				ON salesHeader.salesHeaderId = salesItem.salesHeaderId 
					
			LEFT JOIN temp_inventory
				ON salesItem.itemId = temp_inventory.itemId AND salesHeader.storeId= temp_inventory.storeId
			WHERE 
				(
					(temp_inventory.itemId IS NULL) OR (temp_inventory.itemId IS NOT NULL AND salesItem.onlineInsertTime >= temp_inventory.shotInsertTime)
				)
				
			GROUP BY salesItem.itemId, salesHeader.storeId 
			ON DUPLICATE KEY UPDATE saleQty=VALUES(saleQty)";
			
		try{
			$this->db->pdoSelect($sql0, $vals, $types);
			$this->db->pdoSelect($sql1, $vals, $types);
			$this->db->pdoSelect($sql2, $vals, $types);
			$this->db->pdoSelect($sql3, $vals, $types);
			$process = true;
		}
		catch(Exception $e) {
			$msg = $e->getMessage();
			$result = array("status"=>0, "msg"=>$msg);
			$process = false;
		}
		try{
			if($process){
				$result = array("status"=>1, "msg"=>"");
			}
		}
		catch(Exception $e) {
			$msg = $e->getMessage();
			$result = array("status"=>0, "msg"=>$msg);
		}
		return $result;
	}
	public function insertInventoryShot($storeId = '', $itemId = '', $debug=0){
		$result = $this->createTemporaryInventoryShot($storeId = '', $itemId = '');
		return $result;
	}
}