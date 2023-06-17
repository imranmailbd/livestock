<?php
class Dashboard{
	protected $db;
	private string $date_range;
	public function __construct($db){$this->db = $db;}	
	
	public function lists(){}
	
	public function AJ_lists_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$POST = json_decode(file_get_contents('php://input'), true);
		$date_range = $POST['date_range']??'';
		$this->date_range = $date_range;
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$totalcount = 0;
		$psql = "SELECT p.product_id FROM inventory i, product p WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id AND p.product_publish = 1 AND p.product_type != 'Live Stocks' AND p.manage_inventory_count>0 AND i.current_inventory < i.low_inventory_alert GROUP BY p.product_id"; 
		$productObj = $this->db->query($psql, array());
		if($productObj){
			$totalcount = $productObj->rowCount();
		}
		
		$isql = "SELECT p.product_id, i.low_inventory_alert AS low_inventory_alert FROM inventory i, product p LEFT JOIN item ON (item.product_id = p.product_id AND item.in_inventory = 1) WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id AND p.product_publish = 1 AND p.product_type = 'Live Stocks' AND p.manage_inventory_count>0 GROUP BY p.product_id HAVING COUNT(item.item_id) <= low_inventory_alert"; 
		$itemObj = $this->db->query($isql, array());
		if($itemObj){
			$totalcount += $itemObj->rowCount();
		}
		$jsonResponse['totalcount'] = $totalcount;
		$lowInvData = array();
		$allpsql = "SELECT manufacturer.name AS manufacture, p.product_name as product_name,p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, i.current_inventory as current_inventory, i.low_inventory_alert FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id and p.product_publish = 1 AND p.product_type != 'Live Stocks' AND p.manage_inventory_count>0 AND i.current_inventory < i.low_inventory_alert GROUP BY p.product_id 
					UNION ALL 
					SELECT manufacturer.name AS manufacture, p.product_name as product_name,p.colour_name as colour_name, p.storage as storage, p.physical_condition_name as physical_condition_name, COUNT(item.item_id) as current_inventory, i.low_inventory_alert AS low_inventory_alert FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) LEFT JOIN item ON (item.product_id = p.product_id AND item.in_inventory = 1) WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id and p.product_publish = 1 AND p.manage_inventory_count>0 AND p.product_type = 'Live Stocks' GROUP BY p.product_id HAVING current_inventory < low_inventory_alert 
					ORDER BY manufacture ASC, product_name ASC, colour_name ASC, storage ASC, physical_condition_name ASC LIMIT 0, 10";
		$allpObj = $this->db->query($allpsql, array());
		$lowstr = '';
		if($allpObj){
			while($oneProductRow = $allpObj->fetch(PDO::FETCH_OBJ)){
										
				$product_name = stripslashes($oneProductRow->product_name);
				
				$manufacturer_name = $oneProductRow->manufacture;						
				if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}
				
				$colour_name = $oneProductRow->colour_name;
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $oneProductRow->storage;
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $oneProductRow->physical_condition_name;
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
				
				$current_inventory = floatval($oneProductRow->current_inventory);
				$low_inventory_alert = floatval($oneProductRow->low_inventory_alert);
				
				$lowInvData[] = array($product_name, $current_inventory, $low_inventory_alert);
			}
		}
		$jsonResponse['lowInvData'] = $lowInvData;
		$jsonResponse['loadData'] = $this->loadData();
		
		return json_encode($jsonResponse);
	}
	
    public function loadData(){
       
		$returnData = array();
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$date_range = $this->date_range;
		$Common = new Common($this->db);
		
		$startdate = $enddate = '';
		if($date_range !='' && $date_range !='null'){
			$sales_datearray = explode(' - ', $date_range);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d', strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d', strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$total = $gGrandTotal = $currentpos_id = 0;

		$strextra = "FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) AND pos.pos_id = pos_cart.pos_id";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}
		
		$sumsql = "SELECT pos.pos_id, pos_cart.sales_price, pos_cart.shipping_qty, pos_cart.taxable, pos_cart.discount_is_percent, pos_cart.discount, pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 $strextra ORDER BY pos.pos_id ASC";
		$sumquery = $this->db->querypagination($sumsql, $bindData);

		if($sumquery){
			$num_rows = count($sumquery);
			if($num_rows>0){

				$prevpos_id = 0;

				for($r=0; $r<$num_rows; $r++){

					$pos_cartrow = $sumquery[$r];

					$pos_id = $pos_cartrow['pos_id'];
					$nextpos_id = 0;
					if(($r+1)<$num_rows){
						$nextrow = $sumquery[$r+1];
						$nextpos_id = $nextrow['pos_id'];
					}

					if($pos_id != $prevpos_id){
						$total++;
						$taxable_total = $Nontaxable_total = 0.00;
					}
					$prevpos_id = $pos_id;

					$sales_price = $pos_cartrow['sales_price'];
					$shipping_qty = $pos_cartrow['shipping_qty'];
					$qtyvalue = round($sales_price*$shipping_qty,2);

					$discount = $pos_cartrow['discount'];
					if($pos_cartrow['discount_is_percent']>0){
						$discount_value = round($qtyvalue*0.01*$discount,2);
					}
					else{
						$discount_value = round($discount*$shipping_qty,2);
					}

					if($pos_cartrow['taxable']>0){
						$taxable_total += $qtyvalue-$discount_value;
					}
					else{
						$Nontaxable_total += $qtyvalue-$discount_value;
					}

					if($pos_id != $nextpos_id){
						$taxes_total1 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage1'], $pos_cartrow['tax_inclusive1']);
						$taxes_total2 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage2'], $pos_cartrow['tax_inclusive2']);

						$grandTotal = $taxable_total+$Nontaxable_total+$taxes_total1+$taxes_total2;
						if($pos_cartrow['tax_inclusive1'] >0){$grandTotal -=$taxes_total1;}
						if($pos_cartrow['tax_inclusive2'] >0){$grandTotal -=$taxes_total2;}
						$gGrandTotal += $grandTotal;
					}
				}
			}
		}
		$returnData['total'] = $total;
		$returnData['totalSales'] = round($gGrandTotal,2);

		//==================Repairs====================//
		$repairsql = "SELECT status FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1";
		if($startdate !='' && $enddate !=''){
			$repairsql .= " AND (created_on BETWEEN :startdate AND :enddate)";
		}
		$repairObj = $this->db->query($repairsql, $bindData);
		$open = $added = $invoiced = 0;

		if($repairObj){
			while($roneRow = $repairObj->fetch(PDO::FETCH_OBJ)){
				$status = $roneRow->status;
				if(!in_array($status, array('Cancelled', 'Invoiced'))){$open++;}
				if(strcmp($status, 'Cancelled') !=0){$added++;}
				if(strcmp($status, 'Invoiced') ==0){$invoiced++;}
			}
		}

		$returnData['ropen'] = $open;
		$returnData['radded'] = $added;
		$returnData['rinvoiced'] = $invoiced;
		
		$customersSql = "SELECT COUNT(customers_id) AS totalrows FROM customers WHERE accounts_id = $prod_cat_man AND customers_publish = 1";
		if($startdate !='' && $enddate !=''){
			$customersSql .= " AND (created_on BETWEEN :startdate AND :enddate)";
		}
		$added = 0;
		$customerObj = $this->db->query($customersSql, $bindData);
		if($customerObj){
			$added = floor($customerObj->fetch(PDO::FETCH_OBJ)->totalrows);
		}

		$posSql = "SELECT pos_id FROM pos WHERE accounts_id = $accounts_id AND pos_publish = 1 AND (pos_type = 'Sale' OR (pos_type IN ('Order', 'Repairs') AND order_status = 2))";
		if($startdate !='' && $enddate !=''){
			$posSql .= " AND (sales_datetime BETWEEN :startdate AND :enddate)";
		}
		$posSql .= " GROUP BY customer_id";
		$purchased = 0;
		$posObj = $this->db->query($posSql, $bindData);
		if($posObj){
			$purchased = $posObj->rowCount();
		}

		$returnData['cadded'] = $added;
		$returnData['cpurchased'] = $purchased;
		
		$paymentData = array();
		$ppSql = "SELECT pos_payment.payment_method, SUM(pos_payment.payment_amount) AS total_payment_amount FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1";
		if($startdate !='' && $enddate !=''){
			$ppSql .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
		}
		$ppSql .= " AND pos.pos_id = pos_payment.pos_id GROUP BY pos_payment.payment_method ORDER BY pos_payment.payment_method ASC";
		$paymentObj = $this->db->query($ppSql, $bindData);
		$pplist = '';
		if($paymentObj){
			while($pponerow = $paymentObj->fetch(PDO::FETCH_OBJ)){
				$paymentData[$pponerow->payment_method] = round($pponerow->total_payment_amount,2);
			}
		}
		$returnData['paymentData'] = $paymentData;

		$catArray = $poProductArray = array();
		//=======================FOR Purchase Order===================
		$catSql = "SELECT p.category_id, p.product_id, SUM(pi.received_qty) AS tatalrecQty, SUM(pi.cost*pi.received_qty) AS totalcost FROM po, po_items pi, product p WHERE po.accounts_id = $accounts_id AND po.po_publish = 1 AND po.return_po = 0";
		if($startdate !='' && $enddate !=''){
			$catSql .= " AND (pi.created_on between :startdate AND :enddate)";
		}
		$catSql .= " AND po.po_id = pi.po_id AND pi.product_id = p.product_id GROUP BY p.product_id";
		$categoryObj = $this->db->query($catSql, $bindData);
		$pplist = '';
		if($categoryObj){
			$pocount = $categoryObj->rowCount();
			while($onegrouprow = $categoryObj->fetch(PDO::FETCH_OBJ)){

				if(!empty($catArray) && array_key_exists($onegrouprow->category_id, $catArray)){}
				else{
					$category_name = '';
					if($onegrouprow->category_id>0){
						$catObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $onegrouprow->category_id", array());
						if($catObj){
							$category_name = $catObj->fetch(PDO::FETCH_OBJ)->category_name;
						}
					}
					$catArray[$onegrouprow->category_id] = $category_name;
				}
				$poProductArray[$onegrouprow->product_id] = array($onegrouprow->tatalrecQty, $onegrouprow->totalcost);
			}
		}

		//=======================FOR Sales===================
		$posProductArray = array();
		$catSql = "SELECT p.category_id, p.product_id, pc.sales_price, pc.discount_is_percent, pc.discount, pc.shipping_qty, pc.return_qty, pc.taxable, pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 
					FROM pos, pos_cart pc, product p WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2))";
		if($startdate !='' && $enddate !=''){
			$catSql .= " AND (pos.sales_datetime between :startdate and :enddate)";
		}
		$catSql .= " AND pos.pos_id = pc.pos_id AND p.product_id = pc.item_id ORDER BY p.product_id ASC, pc.pos_cart_id ASC";
		$sumquery = $this->db->querypagination($catSql, $bindData);
		if($sumquery){
			$num_rows = count($sumquery);
			if($num_rows>0){

				$prevproduct_id = 0;

				for($r=0; $r<$num_rows; $r++){

					$pos_cartrow = $sumquery[$r];

					$product_id = $pos_cartrow['product_id'];
					$nextproduct_id = 0;
					if(($r+1)<$num_rows){
						$nextrow = $sumquery[$r+1];
						$nextproduct_id = $nextrow['product_id'];
					}

					if($product_id != $prevproduct_id){
						$totalShipQty = 0;
						$totalPrice = 0;
					}

					$prevproduct_id = $product_id;

					$shipping_qty = $pos_cartrow['shipping_qty'];
					$return_qty = $pos_cartrow['return_qty'];
					$totalShipQty += $shipping_qty-$return_qty;

					$sales_price = $pos_cartrow['sales_price'];
					$total = round($sales_price*($shipping_qty-$return_qty),2);

					$discount_is_percent = $pos_cartrow['discount_is_percent'];
					$discount = $pos_cartrow['discount'];
					if($discount_is_percent>0){
						$discount_value = round($total*0.01*$discount,2);
					}
					else{
						$discount_value = round($discount*($shipping_qty-$return_qty),2);
					}

					$totalPrice += $total-$discount_value;

					if($product_id != $nextproduct_id){
						$category_id = $pos_cartrow['category_id'];
						if(is_array($catArray) && is_array($catArray) && array_key_exists($category_id, $catArray)){}
						else{
							$category_name = '';
							if($category_id>0){
								$catObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $category_id", array());
								if($catObj){
									$category_name = $catObj->fetch(PDO::FETCH_OBJ)->category_name;
								}
							}
							$catArray[$category_id] = $category_name;
						}
						$posProductArray[$product_id] = array($totalShipQty, $totalPrice);
					}
				}
			}
		}
		
		$categoriesData = array();
		if(is_array($catArray) && count($catArray)>0){
			//====================Categories=========================//			
			asort($catArray);
			foreach($catArray as $category_id=>$category_name){

				$QTYIninventory = $TotalPurchased = $QTYInsales = 0;
				$CostIninventory = $TotalCost = $TotalSales = 0.00;
				
				$sqlquery21 = "SELECT product.product_id, product.product_type, product.manage_inventory_count, inventory.current_inventory, inventory.ave_cost 
								FROM product, inventory 
								WHERE inventory.accounts_id = $accounts_id AND product.category_id = $category_id AND product.product_publish = 1 AND inventory.product_id = product.product_id";
				$query21 = $this->db->query($sqlquery21, array());
				if($query21){
					while($oneProductRow = $query21->fetch(PDO::FETCH_OBJ)){

						$product_id = $oneProductRow->product_id;
						$product_type = $oneProductRow->product_type;
						$manage_inventory_count = floor($oneProductRow->manage_inventory_count);
						$current_inventory = floatval($oneProductRow->current_inventory);
						$ave_cost = round($oneProductRow->ave_cost,2);

						if($product_type == 'Live Stocks'){
							$current_inventory = 0;
							$itemObj = $this->db->query("SELECT COUNT(item_id) AS totalitemrows FROM item WHERE accounts_id = $accounts_id AND product_id = $product_id AND in_inventory = 1", array());
							if($itemObj){
								$current_inventory = floor($itemObj->fetch(PDO::FETCH_OBJ)->totalitemrows);
							}
						}
						
						if($current_inventory>0){
							$QTYIninventory += $current_inventory;
							$CostIninventory += $ave_cost*$current_inventory;
						}
						
						if(is_array($poProductArray) && is_array($poProductArray) && array_key_exists($product_id, $poProductArray)){
							$poOneRow = $poProductArray[$product_id];
							$TotalPurchased += $poOneRow[0];
							$TotalCost += $poOneRow[1];
						}

						if(is_array($posProductArray) && is_array($posProductArray) && array_key_exists($product_id, $posProductArray)){
							$posOneRow = $posProductArray[$product_id];
							$QTYInsales += $posOneRow[0];
							$TotalSales += $posOneRow[1];
						}
					}
				}

				$categoriesData[] = array($category_name, $QTYIninventory, $CostIninventory, $TotalPurchased, $TotalCost, $QTYInsales, $TotalSales);
			}
		}

		$returnData['categoriesData'] = $categoriesData;
		return $returnData;
    }
	
	public function AJgetPage(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$date_range = $POST['date_range']??'';
		$this->date_range = $date_range;
		
		$loadData = $this->loadData();
		return json_encode(array('login'=>'', 'loadData'=>$loadData));
	}
	
}
?>