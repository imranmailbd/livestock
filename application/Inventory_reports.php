<?php
class Inventory_reports{
	
	protected $db;
	public string $pageTitle;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
	public function inventory_Value(){}
	
   public function AJ_inventory_Value_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$Inventory_Date = $POST['Inventory_Date']??'';
		
		$sqlquery = "SELECT p.product_id, p.product_type, p.sku, manufacturer.name AS manufacture, p.product_name as product_name,  p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, i.current_inventory, i.ave_cost 
					FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
					WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id AND p.product_type = 'Standard' AND p.manage_inventory_count=1 
					UNION 
					SELECT p.product_id, p.product_type, p.sku, manufacturer.name AS manufacture, p.product_name as product_name,  p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, count(item.item_id) as current_inventory, i.ave_cost 
					FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) LEFT JOIN item ON (item.accounts_id = $accounts_id AND item.product_id = p.product_id and item.in_inventory = 1) 
					WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id AND p.product_type = 'Mobile Devices' 
					GROUP BY p.product_id ORDER BY manufacture ASC, product_name ASC, colour_name ASC, storage ASC, physical_condition_name ASC";

		$query = $this->db->querypagination($sqlquery, array());

		$productInfo = array();
		$Common = new Common($this->db);
		if($query){				
			foreach($query as $onegrouprow1){
				$product_id = $onegrouprow1['product_id'];
				$product_type = $onegrouprow1['product_type'];
				$qty = $onegrouprow1['current_inventory'];
				if($product_type=='Mobile Devices'){
					$product_name = stripslashes(trim("$onegrouprow1[manufacture] $onegrouprow1[product_name] $onegrouprow1[colour_name] $onegrouprow1[storage] $onegrouprow1[physical_condition_name]"));
					$ave_cost = 0.00;
					if($qty>0){
						$mobileProdAveCost = $Common->mobileProdAveCost($accounts_id, $product_id, ' AND in_inventory=1');
						$ave_cost = $mobileProdAveCost[0];
					}
				}
				else{
					$product_name = stripslashes(trim("$onegrouprow1[manufacture] $onegrouprow1[product_name]"));
					$ave_cost = $onegrouprow1['ave_cost'];
				}
				if($qty<0){$ave_cost = 0.00;}
				$productInfo[$product_id] = array($product_name, $onegrouprow1['sku'], $qty, $ave_cost, 0, 0, 0, 0, $product_type);
			}
		}    
		
		if($Inventory_Date !=''){			
			$sqlquery2 = "SELECT product.product_id, product.product_type, manufacturer.name AS manufacture, product.product_name, product.colour_name, product.storage, product.physical_condition_name, product.sku, (CASE WHEN po.transfer=1 AND po_items.received_qty>0 THEN po_items.received_qty*-1 ELSE po_items.received_qty END) AS received_qty, po_items.cost 
						FROM po, po_items, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) WHERE po.accounts_id = $accounts_id AND po_items.created_on > :startdate AND po_items.received_qty !=0 AND product.manage_inventory_count=1 AND po.po_id = po_items.po_id AND product.product_id = po_items.product_id GROUP BY po_items.po_items_id";
			$bindData2 = array('startdate'=>date('Y-m-d H:i:s', strtotime($Inventory_Date.' 23:59:59')));
			$query2 = $this->db->querypagination($sqlquery2, $bindData2);
			if($query2){
				foreach($query2 as $onegrouprow){
					$product_id = $onegrouprow['product_id'];
					$product_type = $onegrouprow['product_type'];
					if($product_type=='Mobile Devices'){
						$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name] $onegrouprow[colour_name] $onegrouprow[storage] $onegrouprow[physical_condition_name]"));
					}
					else{
						$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name]"));
					}
					$received_qty = $onegrouprow['received_qty'];
					$cost = $onegrouprow['cost'];
					if(array_key_exists($product_id, $productInfo)){
						$PIRow = $productInfo[$product_id];
						$newQty = $PIRow[4]+$received_qty;
						if($newQty>0){
							$newCost = round((($PIRow[4]*$PIRow[5])+($received_qty*$cost))/$newQty,2);
						}
						else{$newCost = 0.00;}
						$PIRow[4] = $newQty;
						$PIRow[5] = $newCost;
						$productInfo[$product_id] = $PIRow;
					}
					else{
						$productInfo[$product_id] = array($product_name, $onegrouprow['sku'], 0, 0, $received_qty, $cost, 0, 0, $product_type);
					}
				}
			}
			
			$sqlquery3 = "SELECT pc.* FROM pos, pos_cart pc, product WHERE pos.accounts_id = $accounts_id AND pos.sales_datetime > :startdate AND pc.shipping_qty !=0 AND product.product_type IN ('Standard', 'Mobile Devices') AND product.manage_inventory_count=1 AND pos.pos_id = pc.pos_id AND product.product_id = pc.item_id";
			$bindData3 = array('startdate'=>date('Y-m-d H:i:s', strtotime($Inventory_Date.' 23:59:59')));
			
			$query3 = $this->db->querypagination($sqlquery3, $bindData3);
			if($query3){
				foreach($query3 as $onegrouprow){
					$product_id = $onegrouprow['item_id'];
					$product_name = stripslashes(trim((string) "$onegrouprow[description]"));
					$explodeProd = explode(' (', $product_name);
					$sku = '';
					if(count($explodeProd)>1){
						$sku = end($explodeProd);
						$sku = str_replace(')', '', $sku);
						$product_name = str_replace(" ($sku)", '', $product_name);
					}
					
					$shipping_qty = $onegrouprow['shipping_qty'];
					$ave_cost = $onegrouprow['ave_cost'];
					if(array_key_exists($product_id, $productInfo)){
						$PIRow = $productInfo[$product_id];
						$newQty = $PIRow[6]+$shipping_qty;
						if($newQty>0){
							$newCost = (($PIRow[6]*$PIRow[7])+($shipping_qty*$ave_cost))/$newQty;
						}
						else{$newCost = $ave_cost;}
						$PIRow[6] = $newQty;
						$PIRow[7] = round($newCost,2);
						$productInfo[$product_id] = $PIRow;
					}
					else{
						$productInfo[$product_id] = array($product_name, $sku, 0, 0, 0, 0, $shipping_qty, $ave_cost, $product_type);
					}
				}
			}
		}
		
		if(!empty($productInfo)){
			foreach($productInfo as $product_id=>$productDetails){
				$product_name = $productDetails[0];
				$sku = "";
				if(!empty($productDetails[1])){
					$sku = $productDetails[1];
				}
				$curQty = $productDetails[2];
				$curAveCost = $productDetails[3];						
				$curQtyCost = round($curQty*$curAveCost,2);		
				
				$purQty = $productDetails[4];
				$purAveCost = $productDetails[5];						
				$purQtyCost = round($purQty*$purAveCost,2);
				
				$salQty = $productDetails[6];
				$salAveCost = $productDetails[7];						
				$salQtyCost = round($salQty*$salAveCost,2);	
				
				$product_type = $productDetails[8];
				$changedQty = -$purQty+$salQty;
				$changedQtyCost = -$purQtyCost+$salQtyCost;
				
				if($curQty<0){
					$dateQty = $changedQty;
				}
				else{
					$dateQty = $curQty-$purQty+$salQty;
				}
				$dateQtyCost = $curQtyCost-$purQtyCost+$salQtyCost;
				$dateAveCost = 0.00;
				if($dateQty !=0){$dateAveCost = round($dateQtyCost/$dateQty,2);}
				$search_date = date('Y-m-d', strtotime($Inventory_Date));
				
				if($dateQty !=0){
					$tableData[] = array($product_id, $product_name.$search_date, $sku, round($curQty,2), round($curAveCost,2), round($curQtyCost,2), round($changedQty,2), round($changedQtyCost,2), round($dateQty,2), round($dateAveCost,2), round($dateQtyCost,2), $product_type);
				}
			}
		}
		$jsonResponse['tableData'] = $tableData;
		
		$jsonResponse['tableData'] = $tableData;
		$jsonResponse['Inventory_Date'] = $Inventory_Date;
		
		return json_encode($jsonResponse);
   }

	public function inventory_ValueN(){}
	
	public function AJ_inventory_ValueN_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		$accountsIds = array(3907=>'budas-wireless', 4818=>'skytel', 5510=>'seminole', 6751=>'digitech', 6974=>'willywireless', 7870=>'youlin', 7922=>'almir-co');
		//$accountsIds = array(5510=>'seminole');
		$Inventory_Date = date('Y-m-d', strtotime($POST['Inventory_Date']??date('Y-m-d')));
		$tempInvRepValue = array();
		$tableSql = "SELECT * FROM temp_inventory_report WHERE accounts_id IN (".implode(', ', array_keys($accountsIds)).") AND search_date = '$Inventory_Date' ORDER BY product_id ASC";
		$tableObj = $this->db->query($tableSql, array());						
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$current_qty = floatval($oneRow->current_qty);
				$current_ave_cost = floatval($oneRow->current_ave_cost);
				$tempInvRepValue[$oneRow->product_id] = array($current_qty, $current_ave_cost);
			}
		}
		
		$sqlquery = "SELECT p.product_id, i.accounts_id, p.product_type, p.sku, manufacturer.name AS manufacture, p.product_name as product_name,  p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, i.current_inventory, i.ave_cost";
		$sqlquery .= " FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id)";
		$sqlquery .= " WHERE i.accounts_id IN (".implode(', ', array_keys($accountsIds)).") AND i.product_id = p.product_id AND p.product_type = 'Standard' AND p.manage_inventory_count=1";
		$sqlquery .= " UNION";
		$sqlquery .= " SELECT p.product_id, i.accounts_id, p.product_type, p.sku, manufacturer.name AS manufacture, p.product_name as product_name,  p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, count(item.item_id) as current_inventory, i.ave_cost";
		$sqlquery .= " FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) LEFT JOIN item ON (item.accounts_id IN (".implode(', ', array_keys($accountsIds)).") AND item.product_id = p.product_id and item.in_inventory = 1)";
		$sqlquery .= " WHERE i.accounts_id IN (".implode(', ', array_keys($accountsIds)).") AND i.product_id = p.product_id AND p.product_type = 'Mobile Devices'";
		$sqlquery .= " GROUP BY p.product_id ORDER BY manufacture ASC, product_name ASC, colour_name ASC, storage ASC, physical_condition_name ASC";
		$query = $this->db->querypagination($sqlquery, array());
		$productInfo = array();
		$Common = new Common($this->db);
		if($query){				
			foreach($query as $onegrouprow1){
				$product_id = $onegrouprow1['product_id'];
				$accounts_id = $onegrouprow1['accounts_id'];
				$product_type = $onegrouprow1['product_type'];
				$qty = $onegrouprow1['current_inventory'];
				if($product_type=='Mobile Devices'){

					$product_name = stripslashes(trim("$onegrouprow1[manufacture] $onegrouprow1[product_name] $onegrouprow1[colour_name] $onegrouprow1[storage] $onegrouprow1[physical_condition_name]"));
					$ave_cost = 0.00;
					if($qty>0){
						$mobileProdAveCost = $Common->mobileProdAveCost($accounts_id, $product_id, ' AND in_inventory=1');
						$ave_cost = $mobileProdAveCost[0];
					}
				}
				else{
					$product_name = stripslashes(trim("$onegrouprow1[manufacture] $onegrouprow1[product_name]"));
					$ave_cost = $onegrouprow1['ave_cost'];
				}
				if($qty<0){$ave_cost = 0.00;}
				$productInfo[$product_id] = array($product_name, $onegrouprow1['sku'], $qty, $ave_cost, 0, 0, 0, 0, $product_type, $accounts_id);
			}
		}  

		if($Inventory_Date !=''){	
				
			$sqlquery2 = "SELECT po_items.po_items_id, product.product_id, po.accounts_id, product.product_type, manufacturer.name AS manufacture, product.product_name, product.colour_name, product.storage, product.physical_condition_name, product.sku, (CASE WHEN po.transfer=1 AND po_items.received_qty>0 THEN po_items.received_qty*-1 ELSE po_items.received_qty END) AS received_qty, po_items.cost 
						FROM po, po_items, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) WHERE po.accounts_id IN (".implode(', ', array_keys($accountsIds)).") AND po_items.created_on > :startdate AND po_items.received_qty !=0 AND product.manage_inventory_count=1 AND po.po_id = po_items.po_id AND product.product_id = po_items.product_id GROUP BY po_items.po_items_id";
			$bindData2 = array('startdate'=>date('Y-m-d H:i:s', strtotime($Inventory_Date.' 23:59:59')));
			$query2 = $this->db->querypagination($sqlquery2, $bindData2);
			if($query2){
				foreach($query2 as $onegrouprow){
					$product_id = $onegrouprow['product_id'];
					$accounts_id = $onegrouprow['accounts_id'];
					$product_type = $onegrouprow['product_type'];
					$received_qty = $onegrouprow['received_qty'];
					$cost = $onegrouprow['cost'];
					if($product_type=='Mobile Devices'){
						$po_items_id = $onegrouprow['po_items_id'];
						$received_qty = 0;
						$itemObj = $this->db->query("SELECT COUNT(item.item_id) AS received_qty FROM item, po_cart_item WHERE po_cart_item.po_items_id = $po_items_id AND item.created_on > :startdate AND item.item_id = po_cart_item.item_id", array('startdate'=>date('Y-m-d H:i:s', strtotime($Inventory_Date.' 23:59:59'))));
						if($itemObj){
							$received_qty = $itemObj->fetch(PDO::FETCH_OBJ)->received_qty;
							$this->db->writeIntoLog('received_qty: ' .$onegrouprow['received_qty'].' => '. $received_qty);
						}
						$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name] $onegrouprow[colour_name] $onegrouprow[storage] $onegrouprow[physical_condition_name]"))." [M]";
					}
					else{
						$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name]"));
					}
					if(array_key_exists($product_id, $productInfo)){
						$PIRow = $productInfo[$product_id];
						$newQty = $PIRow[4]+$received_qty;
						if($newQty>0){
							$newCost = round((($PIRow[4]*$PIRow[5])+($received_qty*$cost))/$newQty,2);
						}
						else{$newCost = 0.00;}
						$PIRow[4] = $newQty;
						$PIRow[5] = $newCost;
						$productInfo[$product_id] = $PIRow;
					}
					else{
						$productInfo[$product_id] = array($product_name, $onegrouprow['sku'], 0, 0, $received_qty, $cost, 0, 0, $product_type, $accounts_id);
					}
				}
			}
			
			$sqlquery3 = "SELECT pc.*, pos.accounts_id FROM pos, pos_cart pc, product WHERE pos.accounts_id IN (".implode(', ', array_keys($accountsIds)).") AND pos.sales_datetime > :startdate AND pc.shipping_qty !=0 AND product.product_type IN ('Standard', 'Mobile Devices') AND product.manage_inventory_count=1 AND pos.pos_id = pc.pos_id AND product.product_id = pc.item_id";
			$bindData3 = array('startdate'=>date('Y-m-d H:i:s', strtotime($Inventory_Date.' 23:59:59')));
			
			$query3 = $this->db->querypagination($sqlquery3, $bindData3);
			if($query3){
				foreach($query3 as $onegrouprow){
					$product_id = $onegrouprow['item_id'];
					$accounts_id = $onegrouprow['accounts_id'];
					$product_name = stripslashes(trim((string) "$onegrouprow[description]"));
					$explodeProd = explode(' (', $product_name);
					$sku = '';
					if(count($explodeProd)>1){
						$sku = end($explodeProd);
						$sku = str_replace(')', '', $sku);
						$product_name = str_replace(" ($sku)", '', $product_name);
					}
					
					$shipping_qty = $onegrouprow['shipping_qty'];
					$ave_cost = $onegrouprow['ave_cost'];
					if(array_key_exists($product_id, $productInfo)){
						$PIRow = $productInfo[$product_id];
						$newQty = $PIRow[6]+$shipping_qty;
						if($newQty>0){
							$newCost = (($PIRow[6]*$PIRow[7])+($shipping_qty*$ave_cost))/$newQty;
						}
						else{$newCost = $ave_cost;}
						$PIRow[6] = $newQty;
						$PIRow[7] = round($newCost,2);
						$productInfo[$product_id] = $PIRow;
					}
					else{
						$productInfo[$product_id] = array($product_name, $sku, 0, 0, 0, 0, $shipping_qty, $ave_cost, $product_type, $accounts_id);
					}
				}
			}			
		}
		
		if(!empty($productInfo)){
			$bindData = array('startdate'=>date('Y-m-d H:i:s', strtotime($Inventory_Date.' 23:59:59')));
			foreach($productInfo as $product_id=>$productDetails){
				$product_name = $productDetails[0];
				$sku = "";
				if(!empty($productDetails[1])){
					$sku = $productDetails[1];
				}
				$curQty = $productDetails[2];
				$curAveCost = $productDetails[3];						
				$curQtyCost = round($curQty*$curAveCost,2);		
				
				$purQty = $productDetails[4];
				$purAveCost = $productDetails[5];						
				$purQtyCost = round($purQty*$purAveCost,2);
				
				$salQty = $productDetails[6];
				$salAveCost = $productDetails[7];						
				$salQtyCost = round($salQty*$salAveCost,2);	
				
				$product_type = $productDetails[8];
				$accounts_id = $productDetails[9];

				$subDomain = $accountsIds[$accounts_id];

				$changedQty = -$purQty+$salQty;
				$changedQtyCost = -$purQtyCost+$salQtyCost;
				
				$noteSql = "SELECT Replace(note, ' inventory has been adjusted $sku', '') AS qtyChanged FROM notes WHERE accounts_id = $accounts_id AND last_updated > :startdate AND table_id = $product_id AND note_for = 'product' AND note LIKE '% inventory has been adjusted %' ORDER BY last_updated ASC";
				$query = $this->db->query($noteSql, $bindData);						
				if($query){
					while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
						$qtyChanged = floatval($oneRow->qtyChanged);
						$purQty = $purQty+$qtyChanged;
					}
				}
				
				//if($curQty<0){
					//$dateQty = $changedQty;
				//}
				//else{
					$dateQty = $curQty-$purQty+$salQty;
				//}
				
				$dateQtyCost = $curQtyCost-$purQtyCost+$salQtyCost;
				$dateAveCost = 0.00;
				if($dateQty !=0){$dateAveCost = round($dateQtyCost/$dateQty,2);}
				$search_date = date('Y-m-d', strtotime($Inventory_Date));
				$tempData = $tempInvRepValue[$product_id]??array(0,0);

				if($dateQty !=$tempData[0]){
					$tableData[] = array($product_id, $product_name.$search_date, $sku, round($tempData[0],2), round($tempData[1],2), round($tempData[0]*$tempData[1],2), round($dateQty,2), round($dateAveCost,2), round($dateQtyCost,2), $product_type, $subDomain);
				}
			}
		}
		$jsonResponse['tableData'] = $tableData;
		$jsonResponse['sqlquery'] = $sqlquery;
		
		$jsonResponse['tableData'] = $tableData;
		$jsonResponse['Inventory_Date'] = $Inventory_Date;
		
		return json_encode($jsonResponse);
   }

	public function AJ_inventory_ValueN1_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = $tableTestData = array();
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$Inventory_Date = $POST['Inventory_Date']??'';
		$Current_Date = date('Y-m-d').' 23:59:59';
		if(empty($Inventory_Date)){
			$Inventory_Date = $Current_Date;
		}
		else{
			$Inventory_Date = date('Y-m-d', strtotime($Inventory_Date)).' 23:59:59';
		}

		/*
		$productInfo = array();
		//$addiCondi = " AND product.product_id = 608691";
		$addiCondi = "";
		$poSql = "SELECT product.product_id, product.product_type, manufacturer.name AS manufacture, product.product_name, product.colour_name, product.storage, product.physical_condition_name, product.sku, SUM(CASE WHEN po.transfer=1 AND po_items.received_qty>0 THEN po_items.received_qty*-1 ELSE po_items.received_qty END) AS receiveQty, SUM(CASE WHEN po.transfer=1 AND po_items.received_qty>0 THEN po_items.received_qty*po_items.cost*-1 ELSE po_items.received_qty*po_items.cost END) AS totalCost 
					FROM po, po_items, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) WHERE po.accounts_id = $accounts_id$addiCondi AND po_items.created_on <= '$Inventory_Date' AND po_items.received_qty !=0 AND product.manage_inventory_count=1 AND po.po_id = po_items.po_id AND product.product_id = po_items.product_id GROUP BY product.product_id ORDER BY product.product_id ASC";
		$poData = $this->db->querypagination($poSql, array());
		if($poData){
			foreach($poData as $onegrouprow){
				
				$product_id = $onegrouprow['product_id'];
				$product_type = $onegrouprow['product_type'];
				if($product_type=='Mobile Devices'){
					$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name] $onegrouprow[colour_name] $onegrouprow[storage] $onegrouprow[physical_condition_name]"));
				}
				else{
					$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name]"));
				}
				$productName = $product_name;
				$sku = $onegrouprow['sku'];
				
				$inventoryOnQty = floatval($onegrouprow['receiveQty']);
				$inventoryOnCost = floatval($onegrouprow['totalCost']);

				if(array_key_exists($product_id, $productInfo)){
					$oneProductRow = $productInfo[$product_id];

					$inventoryOnQtyNew = $oneProductRow[2]+$inventoryOnQty;
					$inventoryOnCostNew = $oneProductRow[3]+$inventoryOnCost;
					$inventoryOnQty = $inventoryOnQtyNew;
					$inventoryOnCost = $inventoryOnCostNew;
				}
				$productInfo[$product_id] = array($productName, $sku, $inventoryOnQty, $inventoryOnCost, $product_type);
			}
		}
		if(!empty($addiCondi)){
			$jsonResponse['poSql'] = $poSql;
		}
		$posSql = "SELECT product.product_id, product.product_type, manufacturer.name AS manufacture, product.product_name, product.colour_name, product.storage, product.physical_condition_name, product.sku, SUM(pc.shipping_qty) AS saleQty, SUM(pc.ave_cost*pc.shipping_qty) AS totalCost FROM pos, pos_cart pc, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) 
		WHERE pos.accounts_id = $accounts_id$addiCondi AND pos.sales_datetime <= '$Inventory_Date' AND pc.shipping_qty !=0 AND product.product_type IN ('Standard', 'Mobile Devices') AND product.manage_inventory_count=1 AND pos.pos_id = pc.pos_id AND product.product_id = pc.item_id GROUP BY product.product_id ORDER BY product.product_id ASC";
		$posObject = $this->db->querypagination($posSql, array());
		if($posObject){
			foreach($posObject as $onegrouprow){
				
				$product_id = $onegrouprow['product_id'];
				$product_type = $onegrouprow['product_type'];
				if($product_type=='Mobile Devices'){
					$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name] $onegrouprow[colour_name] $onegrouprow[storage] $onegrouprow[physical_condition_name]"));
				}
				else{
					$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name]"));
				}
				$productName = $product_name;
				$sku = $onegrouprow['sku'];				
				
				$inventoryOnQty = floatval($onegrouprow['saleQty'])*(-1);
				$inventoryOnCost = floatval($onegrouprow['totalCost'])*(-1);
				if(array_key_exists($product_id, $productInfo)){
					$oneProductRow = $productInfo[$product_id];

					$inventoryOnQtyNew = $oneProductRow[2]+$inventoryOnQty;
					$inventoryOnCostNew = $oneProductRow[3]+$inventoryOnCost;
					$inventoryOnQty = $inventoryOnQtyNew;
					$inventoryOnCost = $inventoryOnCostNew;
				}
				$productInfo[$product_id] = array($productName, $sku, $inventoryOnQty, $inventoryOnCost, $product_type);
			}
		}
		
		if(!empty($addiCondi)){
			$jsonResponse['posSql'] = $posSql;
		}
		
		if(!empty($productInfo)){
			foreach($productInfo as $product_id=>$oneProductRow){
				$current_inventory = 0;
				$sku = $oneProductRow[1];
				
				$inventoryOnQty = $oneProductRow[2];
				$inventoryOnCost = $oneProductRow[3];
				$product_type = $oneProductRow[4];
				$costUnitPrice = 0;
				if($inventoryOnQty !=0){
					$costUnitPrice = round($inventoryOnCost/$inventoryOnQty,2);
				}
				if($product_type=='Standard'){//('Standard', 'Mobile Devices')
					
					$inventoryObj = $this->db->query("SELECT current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$current_inventory = floatval($inventoryObj->fetch(PDO::FETCH_OBJ)->current_inventory);
					}

					$noteSql = "SELECT Replace(note, ' inventory has been adjusted $sku', '') AS qtyChanged FROM notes WHERE accounts_id = $accounts_id AND last_updated <= '$Inventory_Date' AND table_id = $product_id AND note_for = 'product' AND note LIKE '% inventory has been adjusted %' ORDER BY last_updated ASC";
					$query = $this->db->query($noteSql, array());						
					if($query){
						while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
							$qtyChanged = floatval($oneRow->qtyChanged);

							$inventoryOnQtyNew = $inventoryOnQty+$qtyChanged;
							$inventoryOnCostNew = ($qtyChanged*$costUnitPrice)+$inventoryOnCost;
							$inventoryOnQty = $inventoryOnQtyNew;
							$inventoryOnCost = $inventoryOnCostNew;
						}
					}
				}
				else{
					if($product_type=='Mobile Devices'){
						$itemObj = $this->db->query("SELECT count(item_id) as counttotalrows FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_publish = 1 AND in_inventory = 1", array());
						if($itemObj){
							$current_inventory += intval($itemObj->fetch(PDO::FETCH_OBJ)->counttotalrows);
						}
					}
					
					$noteSql = "SELECT COUNT(notes.notes_id) AS qtyChanged FROM notes, item WHERE notes.accounts_id = $accounts_id AND notes.note_for = 'item' AND item.product_id = $product_id AND notes.last_updated <= '$Inventory_Date' AND notes.note LIKE '%REMOVED FROM INVENTORY%' AND notes.table_id = item.item_id GROUP BY item.product_id";
					$query = $this->db->query($noteSql, array());						
					if($query){
						while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
							$qtyChanged = floatval($oneRow->qtyChanged);

							$inventoryOnQtyNew = $inventoryOnQty-$qtyChanged;
							$inventoryOnCostNew = $inventoryOnCost-($qtyChanged*$costUnitPrice);
							$inventoryOnQty = $inventoryOnQtyNew;
							$inventoryOnCost = $inventoryOnCostNew;
						}
					}
					
					if(!empty($addiCondi)){
						$jsonResponse['noteSql'] = $noteSql;
					}
					
				}

				if($inventoryOnQty !=0){ 
					$costUnitPrice = round($inventoryOnCost/$inventoryOnQty,2);
					$tableData[] = array($product_id, $oneProductRow[0], $sku, round($inventoryOnQty,2), round($costUnitPrice,2), round($inventoryOnCost,2), $product_type, $current_inventory);
				}
			}
		}
		*/
		$sqlquery = "SELECT p.product_id, p.product_type, p.sku, manufacturer.name AS manufacture, p.product_name as product_name,  p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, i.current_inventory, i.ave_cost 
					FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
					WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id AND p.product_type = 'Standard' AND p.manage_inventory_count=1 
					UNION SELECT p.product_id, p.product_type, p.sku, manufacturer.name AS manufacture, p.product_name as product_name,  p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, count(item.item_id) as current_inventory, i.ave_cost 
					FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) LEFT JOIN item ON (item.accounts_id = $accounts_id AND item.product_id = p.product_id and item.in_inventory = 1) 
					WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id AND p.product_type = 'Mobile Devices' 
					GROUP BY p.product_id ORDER BY manufacture ASC, product_name ASC, colour_name ASC, storage ASC, physical_condition_name ASC";
		$query = $this->db->querypagination($sqlquery, array());
		$productInfo = array();
		$Common = new Common($this->db);
		if($query){
			foreach($query as $onegrouprow1){
				$product_id = $onegrouprow1['product_id'];
				$product_type = $onegrouprow1['product_type'];
				$sku = $onegrouprow1['sku'];
				$qty = $onegrouprow1['current_inventory'];
				if($product_type=='Mobile Devices'){
					$product_name = stripslashes(trim("$onegrouprow1[manufacture] $onegrouprow1[product_name] $onegrouprow1[colour_name] $onegrouprow1[storage] $onegrouprow1[physical_condition_name]"));
					$ave_cost = 0.00;
					if($qty>0){
						$mobileProdAveCost = $Common->mobileProdAveCost($accounts_id, $product_id, ' AND in_inventory=1');
						$ave_cost = $mobileProdAveCost[0];
					}
				}
				else{
					$product_name = stripslashes(trim("$onegrouprow1[manufacture] $onegrouprow1[product_name]"));
					$ave_cost = $onegrouprow1['ave_cost'];
				}
				
				if($qty !=0){ 
					$inventoryOnCost = round($qty*$ave_cost,2);
					$tableData[] = array($product_id, $product_name, $sku, round($qty,2), round($ave_cost,2), round($inventoryOnCost,2), $product_type, $qty);
				}
			}
		}

		if(!empty($tableData)){
			$productNames = array();
			foreach ($tableData as $key => $row) {
				$productNames[$key]  = trim(strtolower($row[1]));
			}
			array_multisort($productNames, SORT_ASC, SORT_STRING, $tableData);
		}

		$jsonResponse['tableData'] = $tableData;
		
		$jsonResponse['tableTestData'] = $tableTestData;
		$jsonResponse['Inventory_Date'] = $Inventory_Date;
		
		return json_encode($jsonResponse);
   }

   public function AJ_inventory_Value2_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = $tableTestData = array();
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$Inventory_Date = $POST['Inventory_Date']??'';
		$Current_Date = date('Y-m-d').' 23:59:59';
		if(empty($Inventory_Date)){
			$Inventory_Date = $Current_Date;
		}
		else{
			$Inventory_Date = date('Y-m-d', strtotime($Inventory_Date)).' 23:59:59';
		}
		$addConSql = "";
		$addConSql1 = "";
		$addConSql2 = "";
		/*
		$addConSql = " AND p.product_id = 116613";
		$addConSql1 = " AND product.product_id = 116613";
		$addConSql2 = " AND pc.item_id = 116613";
		//*/
		$sqlquery = "SELECT p.product_id, p.product_type, p.sku, manufacturer.name AS manufacture, p.product_name as product_name,  p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, i.current_inventory, i.ave_cost 
					FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
					WHERE i.accounts_id = $accounts_id$addConSql AND i.product_id = p.product_id AND p.product_type = 'Standard' AND p.manage_inventory_count=1 
					UNION SELECT p.product_id, p.product_type, p.sku, manufacturer.name AS manufacture, p.product_name as product_name,  p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, count(item.item_id) as current_inventory, i.ave_cost 
					FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) LEFT JOIN item ON (item.accounts_id = $accounts_id AND item.product_id = p.product_id and item.in_inventory = 1) 
					WHERE i.accounts_id = $accounts_id$addConSql AND i.product_id = p.product_id AND p.product_type = 'Mobile Devices' 
					GROUP BY p.product_id ORDER BY manufacture ASC, product_name ASC, colour_name ASC, storage ASC, physical_condition_name ASC";
		$query = $this->db->querypagination($sqlquery, array());
		$productInfo = array();
		$Common = new Common($this->db);
		if($query){
			foreach($query as $onegrouprow1){
				$product_id = $onegrouprow1['product_id'];
				$product_type = $onegrouprow1['product_type'];
				$qty = $onegrouprow1['current_inventory'];
				if($product_type=='Mobile Devices'){
					$product_name = stripslashes(trim("$onegrouprow1[manufacture] $onegrouprow1[product_name] $onegrouprow1[colour_name] $onegrouprow1[storage] $onegrouprow1[physical_condition_name]"));
					$ave_cost = 0.00;
					if($qty>0){
						$mobileProdAveCost = $Common->mobileProdAveCost($accounts_id, $product_id, ' AND in_inventory=1');
						$ave_cost = $mobileProdAveCost[0];
					}
				}
				else{
					$product_name = stripslashes(trim("$onegrouprow1[manufacture] $onegrouprow1[product_name]"));
					$ave_cost = $onegrouprow1['ave_cost'];
				}
				$inventoryOnQty = $inventoryOnCost = 0.00;
				if($Inventory_Date == $Current_Date){
					$inventoryOnQty = $qty;
					$inventoryOnCost = $ave_cost;
				}
				$productInfo[$product_id] = array($product_name, $onegrouprow1['sku'], $qty, $ave_cost, 0, 0, $inventoryOnQty, $inventoryOnCost);
			}
		}
		
		if($Inventory_Date != $Current_Date){
			
			$sqlquery2 = "SELECT product.product_id, po.transfer, product.product_type, manufacturer.name AS manufacture, product.product_name, product.colour_name, product.storage, product.physical_condition_name, product.sku, po_items.created_on, po_items.received_qty, po_items.cost 
						FROM po, po_items, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) WHERE po.accounts_id = $accounts_id$addConSql1 AND po_items.received_qty !=0 AND po.po_publish = 1 AND po.po_id = po_items.po_id AND product.product_id = po_items.product_id GROUP BY po_items.po_items_id ORDER BY po_items.created_on ASC";
			$query2 = $this->db->querypagination($sqlquery2, array());
			if($query2){
				//$tableTestData[] = $query2;
				foreach($query2 as $onegrouprow){
					$poDate = $onegrouprow['created_on'];
					
					$product_id = $onegrouprow['product_id'];
					$product_type = $onegrouprow['product_type'];
					if($product_type=='Mobile Devices'){
						$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name] $onegrouprow[colour_name] $onegrouprow[storage] $onegrouprow[physical_condition_name]"));
					}
					else{
						$product_name = stripslashes(trim("$onegrouprow[manufacture] $onegrouprow[product_name]"));
					}
					$productName = $product_name;
					$sku = $onegrouprow['sku'];
					$currentQty = $currentCost = $changedQty = $changedCost = $inventoryOnQty = $inventoryOnCost = 0;
					$addOrSubtract = 1;
					if($onegrouprow['transfer']==1 && $onegrouprow['received_qty']>0){
						$addOrSubtract = -1;
					}
					if(strtotime($poDate)>strtotime($Inventory_Date)){
						$changedQty = floatval($onegrouprow['received_qty'])*$addOrSubtract;
						$changedCost = floatval($onegrouprow['cost']);
					}
					else{
						$inventoryOnQty = floatval($onegrouprow['received_qty'])*$addOrSubtract;
						$inventoryOnCost = floatval($onegrouprow['cost']);
					}
					if(array_key_exists($product_id, $productInfo)){
						$oneProductRow = $productInfo[$product_id];
						$productName = $oneProductRow[0];
						$sku = $oneProductRow[1];
						$currentQty = floatval($oneProductRow[2]);
						$currentCost = floatval($oneProductRow[3]);

						$changedQtyNew = $oneProductRow[4]+$changedQty;
						$changedCostNew = $changedCost;
						if($changedQtyNew<0){$changedCostNew = 0;}
						elseif($changedQtyNew !=0 && $changedQtyNew != $changedQty){
							$changedCostNew = round((($oneProductRow[4]*$oneProductRow[5])+($changedQty*$changedCost))/$changedQtyNew,2);
						}						
						$changedQty = $changedQtyNew;
						$changedCost = $changedCostNew;

						$inventoryOnQtyNew = $oneProductRow[6]+$inventoryOnQty;
						$inventoryOnCostNew = $inventoryOnCost;
						if($inventoryOnQtyNew<0){$inventoryOnCostNew = 0;}
						elseif($inventoryOnQtyNew !=0 && $inventoryOnQtyNew != $inventoryOnQty){
							$inventoryOnCostNew = round((($oneProductRow[6]*$oneProductRow[7])+($inventoryOnQty*$inventoryOnCost))/$inventoryOnQtyNew,2);
						}						
						$inventoryOnQty = $inventoryOnQtyNew;
						$inventoryOnCost = $inventoryOnCostNew;
					}
					$productInfo[$product_id] = array($productName, $sku, $currentQty, $currentCost, $changedQty, $changedCost, $inventoryOnQty, $inventoryOnCost);
				}
			}
			
			$sqlquery3 = "SELECT pc.*, pos.sales_datetime FROM pos, pos_cart pc, product WHERE pos.accounts_id = $accounts_id$addConSql2 AND pc.shipping_qty !=0 AND product.product_type IN ('Standard', 'Mobile Devices') AND pos.pos_id = pc.pos_id AND product.product_id = pc.item_id ORDER BY pos.sales_datetime ASC";// AND pos.pos_publish = 1
			$query3 = $this->db->querypagination($sqlquery3, array());
			if($query3){
				//$tableTestData[] = $query3;
				foreach($query3 as $onegrouprow){
					$salesDate = $onegrouprow['sales_datetime'];
					$product_id = $onegrouprow['item_id'];
					$product_name = stripslashes(trim((string) "$onegrouprow[description]"));
					$explodeProd = explode(' (', $product_name);
					$sku = '';
					if(count($explodeProd)>1){
						$sku = end($explodeProd);
						$sku = str_replace(')', '', $sku);
						$product_name = str_replace(" ($sku)", '', $product_name);
					}
					
					$productName = $product_name;
					$currentQty = $currentCost = $changedQty = $changedCost = $inventoryOnQty = $inventoryOnCost = 0;
					if(strtotime($salesDate)>strtotime($Inventory_Date)){
						$changedQty = floatval($onegrouprow['shipping_qty'])*(-1);
						$changedCost = floatval($onegrouprow['ave_cost']);
					}
					else{
						$inventoryOnQty = floatval($onegrouprow['shipping_qty'])*(-1);
						$inventoryOnCost = floatval($onegrouprow['ave_cost']);
					}
					if(array_key_exists($product_id, $productInfo)){
						$oneProductRow = $productInfo[$product_id];
						$productName = $oneProductRow[0];
						$sku = $oneProductRow[1];
						$currentQty = $oneProductRow[2];
						$currentCost = $oneProductRow[3];

						$changedQtyNew = floatval($oneProductRow[4])+floatval($changedQty);
						$changedCostNew = $changedCost;
						if($changedQtyNew<0){$changedCostNew = 0;}
						elseif($changedQtyNew !=0 && $changedQtyNew != $changedQty){
							$changedCostNew = round((($oneProductRow[4]*$oneProductRow[5])+($changedQty*$changedCost))/$changedQtyNew,2);
						}						
						$changedQty = $changedQtyNew;
						$changedCost = $changedCostNew;

						$inventoryOnQtyNew = floatval($oneProductRow[6])+floatval($inventoryOnQty);
						$inventoryOnCostNew = $inventoryOnCost;
						if($inventoryOnQtyNew<0){$inventoryOnCostNew = 0;}
						elseif($inventoryOnQtyNew !=0 && $inventoryOnQtyNew != $inventoryOnQty){
							$inventoryOnCostNew = round((($oneProductRow[6]*$oneProductRow[7])+($inventoryOnQty*$inventoryOnCost))/$inventoryOnQtyNew,2);
						}						
						$inventoryOnQty = $inventoryOnQtyNew;
						$inventoryOnCost = $inventoryOnCostNew;
					}
					$productInfo[$product_id] = array($productName, $sku, $currentQty, $currentCost, $changedQty, $changedCost, $inventoryOnQty, $inventoryOnCost);
				}
			}			
		}
		
		if(!empty($productInfo)){
			foreach($productInfo as $product_id=>$oneProductRow){
				$curQty = $oneProductRow[2];
				$curAveCost = $oneProductRow[3];
				$curQtyCost = round($curQty*$curAveCost,2);		
				
				$changedQty = $oneProductRow[4];
				$changedCost = $oneProductRow[5];
				
				$inventoryOnQty = $oneProductRow[6];
				$inventoryOnCost = $oneProductRow[7];

				$sku = $oneProductRow[1];

				if($Inventory_Date != $Current_Date){
					$sqlquery4 = "SELECT last_updated, Replace(note, ' inventory has been adjusted $sku', '') AS qtyChanged FROM notes WHERE accounts_id = $accounts_id AND table_id = $product_id AND note_for = 'product' AND note LIKE '% inventory has been adjusted %' ORDER BY last_updated ASC";
					$query = $this->db->query($sqlquery4, array());						
					if($query){
						//$tableTestData[] = $query;
						while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
							$last_updated = $oneRow->last_updated;
							$qtyChanged = floatval($oneRow->qtyChanged);

							if(strtotime($last_updated)>strtotime($Inventory_Date)){
								$changedQtyNew = $changedQty+$qtyChanged;
								$changedCostNew = $changedCost;
								if($changedQtyNew !=0){
									$changedCostNew = round((($qtyChanged*$curAveCost)+($changedQty*$changedCost))/$changedQtyNew,2);
								}
								$changedQty = $changedQtyNew;
								$changedCost = $changedCostNew;
							}
							else{
								$inventoryOnQtyNew = $inventoryOnQty+$qtyChanged;
								$inventoryOnCostNew = $inventoryOnCost;
								if($inventoryOnQtyNew !=0){
									$inventoryOnCostNew = round((($qtyChanged*$curAveCost)+($inventoryOnQty*$inventoryOnCost))/$inventoryOnQtyNew,2);
								}
								$inventoryOnQty = $inventoryOnQtyNew;
								$inventoryOnCost = $inventoryOnCostNew;
							}
						}
					}
				}
				
				$changedQtyCost = round($changedQty*$changedCost,2);
				$inventoryOnQtyCost = round($inventoryOnQty*$inventoryOnCost,2);
				
				if($curQty !=0 || $changedQty !=0 || $inventoryOnQty !=0){
					$tableData[] = array($product_id, $oneProductRow[0], $sku, round($curQty,2), round($curAveCost,2), round($curQtyCost,2), round($changedQty,2), round($changedQtyCost,2), round($inventoryOnQty,2), round($inventoryOnCost,2), round($inventoryOnQtyCost,2));
				}
			}
		}
		$jsonResponse['tableData'] = $tableData;
		
		$jsonResponse['tableTestData'] = $tableTestData;
		$jsonResponse['Inventory_Date'] = $Inventory_Date;
		
		return json_encode($jsonResponse);
   }

	public function inventory_Purchased(){}
    
    public function AJ_inventory_Purchased_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$po_datetime = $POST['po_datetime']??'';
		$supplier = trim((string) $POST['supplier']??'');
		$startdate = $enddate = '';
		if($po_datetime !=''){
			$po_datetimearray = explode(' - ', $po_datetime);
			if(is_array($po_datetimearray) && count($po_datetimearray)>1){
				$startdate = date('Y-m-d',strtotime($po_datetimearray[0])).' 00:00:00';
				$enddate = date('Y-m-d', strtotime($po_datetimearray[1])).' 23:59:59';
			}
		}

		$semail = $ssuppliername = $ssuppliernameexplode ='';
		if(!empty($supplier)){
			$ssupplierexplode = explode('(', addslashes($supplier));

			if(count($ssupplierexplode)>1){
				$semail = str_replace(')', '', $ssupplierexplode[1]);
				$ssuppliernameexplode = explode(' ', trim((string) $ssupplierexplode[0]));
			}
			else{
				$ssuppliernameexplode = explode(' ', trim((string) $ssupplierexplode[0]));
			}
		}

		$supplier_id = 0;
		$supplier_name = $supplier;
		if($supplier_name !=''){
			$autosupplier_name = $supplier_name;
			$email = '';
			if(strpos($supplier_name, ' (') !== false) {
				$ssupplierexplode = explode(' (', $supplier_name);
				if(count($ssupplierexplode)>1){
					$autosupplier_name = trim((string) $ssupplierexplode[0]);
					$email = str_replace(')', '', $ssupplierexplode[1]);
				}
			}
			$bindData = array();
			$strextra = " AND company LIKE CONCAT('%', :autosupplier_name, '%')";
			$bindData['autosupplier_name'] = $autosupplier_name;
			if($email !=''){
				$strextra .= " and (email = :email or contact_no = :email)";
				$bindData['email'] = $email;
			}
			$strextra .= " GROUP BY company, email";
			$sqlquery = "SELECT suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man $strextra limit 0, 1";
			$query = $this->db->querypagination($sqlquery, $bindData);
			if($query){
				foreach($query as $onegrouprow){
					$supplier_id = $onegrouprow['suppliers_id'];
				}
			}
		}

		//=================sql for category group =======================//

		$strextra1 = "FROM po, po_items LEFT JOIN product ON (product.accounts_id = $prod_cat_man AND product.product_id = po_items.product_id) 
						WHERE po.accounts_id = $accounts_id 
							AND po.po_id = po_items.po_id 
							AND po.po_publish = 1 
							AND po_items.received_qty >0";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra1 .= " AND (po_items.created_on between :startdate AND :enddate)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}

		if($supplier_id>0){
			$strextra1 .= " and po.supplier_id = '$supplier_id'";
		}

		$strextra1 .= " GROUP BY po_items.po_items_id";

		$sqlquery = "SELECT po_items.created_on, po.po_number, po.lot_ref_no, product.sku, 
						product.manufacturer_id, product.product_name, product.colour_name, product.storage, product.physical_condition_name, 
						po_items.item_type, po_items.product_id, po_items.received_qty, po_items.cost 
					$strextra1 ORDER BY TRIM(CONCAT_WS(' ', product.product_name, product.colour_name, product.storage, product.physical_condition_name)) ASC";
		$query = $this->db->querypagination($sqlquery, $bindData);
		if($query){
			foreach($query as $onegrouprow){

				$item_type = $onegrouprow['item_type'];
				$product_id = $onegrouprow['product_id'];
				$po_number = $onegrouprow['po_number'];
				$lot_ref_no = $onegrouprow['lot_ref_no'];
				
				$sku = $product_name = '';
				if($item_type == 'one_time'){
					$posCObj = $this->db->querypagination("SELECT description FROM pos_cart WHERE pos_cart_id = $product_id ORDER BY pos_cart_id DESC LIMIT 0, 1", array());
					if($posCObj){
						$product_name = $posCObj[0]['description'];
					}
				}
				else{
					$sku = $onegrouprow['sku'];

					$manufacturer_id = $onegrouprow['manufacturer_id'];
					$name = '';
					if($manufacturer_id>0){
						$manObj = $this->db->querypagination("SELECT name FROM manufacturer WHERE manufacturer_id = $manufacturer_id LIMIT 0, 1", array());
						if($manObj){
							$name = $manObj[0]['name'];
						}
					}
					$product_name = stripslashes(trim($name.' '.$onegrouprow['product_name']));

					$colour_name = $onegrouprow['colour_name'];
					if($colour_name !=''){$product_name .= ' '.$colour_name;}

					$storage = $onegrouprow['storage'];
					if($storage !=''){$product_name .= ' '.$storage;}

					$physical_condition_name = $onegrouprow['physical_condition_name'];
					if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
				}
				
				$received_qty = floatval($onegrouprow['received_qty']);
				$cost = round($onegrouprow['cost'],2);
				
				$tableData[] = array($onegrouprow['created_on'], $po_number, $lot_ref_no, $sku, $product_name, round($received_qty,2), round($cost,2));
			}
		}

		$jsonResponse['tableData'] = $tableData;
		
		return json_encode($jsonResponse);
    }

	public function products_Report(){}
    
	public function AJ_products_Report_MoreInfo(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;

		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$totalRows = 0;
		$manOpts = $catOpts = array();
		$sqlquery = "SELECT p.category_id, p.manufacturer_id FROM inventory i, product p WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id and p.product_publish = 1 GROUP BY p.product_id 
					ORDER BY p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC";		
		$query = $this->db->querypagination($sqlquery, array());
		if($query){
			$totalRows = count($query);
			foreach($query as $oneRow){
				$catOpts[$oneRow['category_id']] = '';
				$manOpts[$oneRow['manufacturer_id']] = '';
			}
		}

		$manOpt = array();
		if(!empty($manOpts)){			
			if(array_key_exists('0', $manOpts)){
				$manOpt[0] = '';
			}
			$manStr = "SELECT manufacturer_id, name FROM manufacturer WHERE manufacturer_id IN (".implode(', ', array_keys($manOpts)).") AND accounts_id = $prod_cat_man ORDER BY name ASC";
			$manObj = $this->db->query($manStr, array());
			if($manObj){
				while($oneRow=$manObj->fetch(PDO::FETCH_OBJ)){
					$manOpt[$oneRow->manufacturer_id] = stripslashes(trim((string) $oneRow->name));
				}
			}
		}

		$catOpt = array();
		if(count($catOpts)>0){
			if(array_key_exists('0', $catOpts)){
				$catOpt[0] = '';
			}
			$catStr = "SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($catOpts)).") AND accounts_id = $prod_cat_man ORDER BY category_name ASC";
			$catQuery = $this->db->query($catStr, array());
			if($catQuery){
				while($oneRow=$catQuery->fetch(PDO::FETCH_OBJ)){
					$catOpt[$oneRow->category_id] = stripslashes(trim((string) $oneRow->category_name));
				}
			}
		}
		
		$jsonResponse['manOpt'] = $manOpt;		
		$jsonResponse['catOpt'] = $catOpt;		
		return json_encode($jsonResponse);
	}
	
	public function products_ReportData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sortby = $POST['sortby']??'';
		$data_type = $POST['data_type']??'';
		$scategory_id = $POST['scategory_id']??0;
		$smanufacturer_id = $POST['smanufacturer_id']??'';

		//=================sql for category group =======================//

		$filtersql = '';
		$bindData = array();
		if($smanufacturer_id !=''){
			$filtersql .= " AND p.manufacturer_id = :smanufacturer_id";
			$bindData['smanufacturer_id'] = $smanufacturer_id;
		}
		if($scategory_id>0){
			$filtersql .= " AND p.category_id = :scategory_id";
			$bindData['scategory_id'] = $scategory_id;
		}

		$strextra ="SELECT p.product_id, p.product_type, p.sku AS sku, p.category_id, manufacturer.name AS manufacture, p.product_name as product_name,p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name,
					i.regular_price, p.manage_inventory_count, i.current_inventory AS current_inventory, p.allow_backorder, i.low_inventory_alert, i.ave_cost 
				FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
				WHERE i.accounts_id = $accounts_id AND p.product_type != 'Mobile Devices' $filtersql";
		if($data_type=='Available'){
			$strextra .= " AND ((p.manage_inventory_count = 0 or p.manage_inventory_count is null) or (p.manage_inventory_count=1 and i.current_inventory>0) or p.allow_backorder = 1)";
		}
		elseif($data_type=='Low Stock'){
			$strextra .= " AND p.manage_inventory_count>0";
		}
		$strextra .= " AND i.product_id = p.product_id and p.product_publish = 1 GROUP BY p.product_id";
		if($data_type=='Low Stock'){
			$strextra .= " HAVING current_inventory < i.low_inventory_alert";
		}

		$strextra .=" UNION ALL";

		$strextra .=" SELECT p.product_id, p.product_type, p.sku AS sku, p.category_id, manufacturer.name AS manufacture, p.product_name as product_name,p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name,
							i.regular_price, p.manage_inventory_count, count(item.item_id) as current_inventory, p.allow_backorder, i.low_inventory_alert, i.ave_cost";
		if($data_type=='Available'){
			$strextra .=" FROM inventory i, item, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
						WHERE item.accounts_id = $accounts_id AND item.in_inventory = 1 AND i.accounts_id = $accounts_id AND item.product_id = p.product_id AND p.product_type = 'Mobile Devices' ";
		}
		elseif($data_type=='Low Stock'){
			$strextra .=" FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) LEFT JOIN item ON (item.accounts_id = $accounts_id AND item.in_inventory = 1 AND item.product_id = p.product_id) 
						WHERE i.accounts_id = $accounts_id AND p.manage_inventory_count>0 AND p.product_type = 'Mobile Devices'";
		}
		else{
			$strextra .=" FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) LEFT JOIN item ON (item.accounts_id = $accounts_id AND item.in_inventory = 1 AND item.product_id = p.product_id) 
						WHERE i.accounts_id = $accounts_id AND p.product_type = 'Mobile Devices'";
		}

		$strextra .= "$filtersql AND i.product_id = p.product_id AND p.product_publish = 1";
		$strextra .= " GROUP BY p.product_id";
		if($data_type=='Low Stock'){
			$strextra .= " HAVING current_inventory < i.low_inventory_alert";
		}
		if($sortby=='sku'){
			$strextra .= " ORDER BY sku ASC";
		}
		else{
			$strextra .= " ORDER BY manufacture ASC, product_name ASC, colour_name ASC, storage ASC, physical_condition_name ASC";
		}
		$query = $this->db->querypagination($strextra, $bindData);
		//$tableData = array($strextra);
		if($query){
			$categoryIds = $mobileIds = array();
			$Common = new Common($this->db);
			foreach($query as $onegrouprow1){
				$categoryIds[$onegrouprow1['category_id']] = '';
				$product_id = $onegrouprow1['product_id'];
				$product_type = $onegrouprow1['product_type'];
				$qty = floatval($onegrouprow1['current_inventory']);
				
				if($product_type=='Mobile Devices' && $qty>0){
					$mobileProdAveCost = $Common->mobileProdAveCost($accounts_id, $product_id, ' AND in_inventory=1');
					$mobileIds[$product_id] = $mobileProdAveCost[0];
				}
			}
			
			if(!empty($categoryIds)){
				$catObj = $this->db->query("SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($categoryIds)).")", array());
				if($catObj){
					while($oneRow = $catObj->fetch(PDO::FETCH_OBJ)){
						$categoryIds[$oneRow->category_id] = stripslashes(trim((string) $oneRow->category_name));
					}
				}
			}
			
			foreach($query as $rowproduct){

				$product_id = $rowproduct['product_id'];
				$product_type = $rowproduct['product_type'];
				$sku = stripslashes($rowproduct['sku']);
				$category_name = $categoryIds[$rowproduct['category_id']];

				$manufacture = stripslashes(trim((string) $rowproduct['manufacture']));

				$product_name = stripslashes($rowproduct['product_name']);

				$colour_name = $rowproduct['colour_name'];
				if($colour_name !=''){$product_name .= ' '.$colour_name;}

				$storage = $rowproduct['storage'];
				if($storage !=''){$product_name .= ' '.$storage;}

				$physical_condition_name = $rowproduct['physical_condition_name'];
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$alertclass = '';
				$regular_price = round($rowproduct['regular_price'],2);
				$ave_cost = round($rowproduct['ave_cost'],2);
				
				$current_inventory = 0;
				$manage_inventory_count = intval($rowproduct['manage_inventory_count']);
				if($product_type !='Mobile Devices'){
					if($manage_inventory_count>0){
						$current_inventory = round(floatval($rowproduct['current_inventory']),2);
						$low_inventory_alert = $rowproduct['low_inventory_alert'];

						if($current_inventory<$low_inventory_alert){
							$alertclass = 'alert alert-danger';
						}
					}
					else{
						$current_inventory = '*';
					}
				}
				else{
					$current_inventory = round(floatval($rowproduct['current_inventory']),2);
					$ave_cost = 0.00;
					if($current_inventory>0 && array_key_exists($product_id, $mobileIds)){
						$ave_cost = round($mobileIds[$product_id],2);
					}
				}

				$tableData[] = array($manufacture, $product_name, $sku, $category_name, round($regular_price,2), round($ave_cost,2), $current_inventory, $alertclass);
			}
		}
		
		$jsonResponse['tableData'] = $tableData;
		
		return json_encode($jsonResponse);
   	}

	public function purchase_Orders(){}
    
    public function purchase_OrdersData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();

		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$po_datetime = $POST['po_datetime']??'';
		$startdate = $enddate = '';
		if($po_datetime !=''){
			$po_datetimearray = explode(' - ', $po_datetime);
			if(is_array($po_datetimearray) && count($po_datetimearray)>1){
				$startdate = date('Y-m-d',strtotime($po_datetimearray[0])).' 00:00:00';
				$enddate = date('Y-m-d', strtotime($po_datetimearray[1])).' 23:59:59';
			}
		}

		$supplier_id = $POST['suppliers_id']??0;
		$supplier_name = $POST['posupplier']??'';
		if($supplier_name !=''){

			$autosupplier_name = $supplier_name;
			$email = '';
			if(strpos($supplier_name, ' (') !== false) {
				$ssupplierexplode = explode(' (', $supplier_name);
				if(count($ssupplierexplode)>1){
					$autosupplier_name = trim((string) $ssupplierexplode[0]);
					$email = str_replace(')', '', $ssupplierexplode[1]);
				}
			}
			$bindData = array();
			$strextra = " and company LIKE CONCAT('%', :autosupplier_name, '%')";
			$bindData['autosupplier_name'] = $autosupplier_name;

			if($email !=''){
				$strextra .= " and (email = :email or contact_no = :email)";
				$bindData['email'] = $email;
			}
			$strextra .= " GROUP BY company, email";
			$sqlquery = "SELECT suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man $strextra limit 0, 1";
			$query = $this->db->querypagination($sqlquery, $bindData);
			if($query){
				foreach($query as $onegrouprow){
					$supplier_id = $onegrouprow['suppliers_id'];
				}
			}
		}

		//=================sql for category group =======================//

		$strextra = "FROM po, suppliers WHERE po.accounts_id = $accounts_id and po.transfer = 0 and po.supplier_id = suppliers.suppliers_id";
		$bindData = array();
		if($supplier_id>0){
			$strextra .= " and po.supplier_id = $supplier_id";
		}

		if($startdate !='' && $enddate !=''){
			$strextra .= " and (po.po_datetime between CONCAT(:startdate, '%') AND CONCAT(:enddate, '%'))";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}

		$strextra .= " ORDER BY po.po_datetime DESC, po.po_number DESC";

		$sqlquery = "SELECT * $strextra";
		$query = $this->db->query($sqlquery, $bindData);
		//$returnStr =  $sqlquery;
		if($query){
			while($rowpo = $query->fetch(PDO::FETCH_OBJ)){
				$po_id = $rowpo->po_id;
				$sqlquery = "SELECT sum(received_qty*cost) as total FROM po_items WHERE po_id = $po_id";
				$res = $this->db->query($sqlquery, array());
				$total = 0;
				if($res){
					while($row = $res->fetch(PDO::FETCH_OBJ)) {
						$total = floatval($row->total);
					}
				}
				$goods_total = $total;
				
				$po_number = $rowpo->po_number;
				$supplier_id = $rowpo->supplier_id;
				$lot_ref_no = $rowpo->lot_ref_no;
				$tax_is_percent = intval($rowpo->tax_is_percent);
				$taxes = floatval($rowpo->taxes);
				$shipping = round($rowpo->shipping,2);
									
				$status = $rowpo->status;
				$supplier_name = stripslashes($rowpo->company);
				$return_po = $rowpo->return_po;

				$tableData[] = array($rowpo->po_datetime, $po_number, $lot_ref_no, $supplier_name, round($goods_total,2), round($taxes,2), round($shipping,2), $return_po, $status, $tax_is_percent);

			}
		}
		
		$jsonResponse['tableData'] = $tableData;
		
		return json_encode($jsonResponse);
    }	
}
?>