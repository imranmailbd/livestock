<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Carts{
	protected $db;
	public function __construct($db){$this->db = $db;}	
	
	public function NeedHaveOnPO($product_id, $product_type, $returnArray = 0, $accounts_id = 0){
		if($accounts_id==0){
			$accounts_id = $_SESSION["accounts_id"]??0;
		}
		$returnStr = '&nbsp;';
		
		$needOrders = 0;
		$ordersObj = $this->db->query("SELECT SUM(pos_cart.qty-pos_cart.shipping_qty) AS needOrders FROM pos, pos_cart WHERE pos_cart.item_id = $product_id AND pos.accounts_id = $accounts_id AND pos.pos_type = 'Order' AND pos.order_status=1 AND pos_cart.qty>pos_cart.shipping_qty AND pos.pos_id = pos_cart.pos_id", array());
		if($ordersObj){
			$needOrders = floatval($ordersObj->fetch(PDO::FETCH_OBJ)->needOrders);
			if(!$needOrders || $needOrders=='NULL'){$needOrders = 0;}
		}

		$posObj = $this->db->query("SELECT SUM(pos_cart.qty-pos_cart.shipping_qty) AS needOrders FROM pos, pos_cart WHERE pos_cart.item_id = $product_id AND pos.accounts_id = $accounts_id AND pos.pos_type = 'Sale' AND pos.order_status=1 AND pos_cart.qty>pos_cart.shipping_qty AND pos.pos_id = pos_cart.pos_id", array());
		if($posObj){
			$needOrders1 = floatval($posObj->fetch(PDO::FETCH_OBJ)->needOrders);
			if(!$needOrders1 || $needOrders1=='NULL'){$needOrders1 = 0;}
			$needOrders += $needOrders1;
		}
		
		$needRepairs = 0;						
		$repairsObj = $this->db->query("SELECT SUM(pos_cart.qty) AS needRepairs FROM pos, pos_cart, repairs WHERE pos_cart.item_id = $product_id AND repairs.status NOT IN ('Finished', 'Invoiced', 'Cancelled') AND pos.accounts_id = $accounts_id AND pos.pos_type = 'Repairs' AND pos.order_status=1 AND pos.pos_id = pos_cart.pos_id AND pos.pos_id = repairs.pos_id", array());
		if($repairsObj){
			$needRepairs = floatval($repairsObj->fetch(PDO::FETCH_OBJ)->needRepairs);
			if(!$needRepairs || $needRepairs=='NULL'){$needRepairs = 0;}
		}
		$need = $needOrders+$needRepairs;
		$have = 0;
		if($product_type=='Standard'){
			$inventoryObj = $this->db->query("SELECT current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
			if($inventoryObj){
				$have += floatval($inventoryObj->fetch(PDO::FETCH_OBJ)->current_inventory);
			}
		}
		else{
			$itemObj = $this->db->query("SELECT count(item_id) as counttotalrows FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_publish = 1 AND in_inventory = 1", array());
			if($itemObj){
				$have += intval($itemObj->fetch(PDO::FETCH_OBJ)->counttotalrows);
			}
		}
		
		$onPO = 0;						
		$poObj = $this->db->query("SELECT SUM(po_items.ordered_qty-po_items.received_qty) AS totalOnPO FROM po, po_items WHERE po_items.product_id = $product_id AND po.accounts_id = $accounts_id AND po.status = 'Open' AND po_items.ordered_qty>po_items.received_qty AND po.po_id = po_items.po_id", array());
		if($poObj){
			$onPO = $poObj->fetch(PDO::FETCH_OBJ)->totalOnPO;
			if(!$onPO || $onPO=='NULL'){$onPO = 0;}
		}
		
		if($returnArray==1){
			return array(round($need,2), round($have,2), round($onPO,2));
		}
		else{
			return "<a href=\"javascript:void(0)\" onClick=\"showOnPOInfo($product_id, '$product_type');\" class=\"txtunderline txtblue\">$need &nbsp;/&nbsp; $have &nbsp;/&nbsp; $onPO <i class=\"fa fa-link\"></i></a>";
		}
	}
	
	public function loadCartData($frompage, $pos_id){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnData = array();
		$pos_id = intval($POST['pos_id']??$pos_id);
		if($pos_id==0 && isset($_SESSION["pos_id"]) && $_SESSION["pos_id"]>0){$pos_id = $_SESSION["pos_id"];}
		$status = $POST['status']??'';
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$posObj = $this->db->query("SELECT pos_id, status FROM pos WHERE pos_id = :pos_id AND accounts_id = $accounts_id", array('pos_id'=>$pos_id),1);
		if($posObj){
			$posOneRow = $posObj->fetch(PDO::FETCH_OBJ);
			$pos_id = $posOneRow->pos_id;
			$orderStatus = $posOneRow->status;
			$edit = 1;
			if($frompage=='Orders' && $orderStatus=='Quotes'){$edit = 0;}
			if($frompage=='Repairs' && $status==''){
				$repairsObj = $this->db->query("SELECT status FROM repairs WHERE pos_id = :pos_id AND accounts_id = $accounts_id", array('pos_id'=>$pos_id),1);
				if($repairsObj){
					$status = $repairsObj->fetch(PDO::FETCH_OBJ)->status;
				}
			}
	
			$cartPer = 1;
			$posPer = array();
			if(!empty($_SESSION["allowed"])) {
				if($frompage=='POS'){
					$posPer = $_SESSION["allowed"][1]??array();
				}
				elseif($frompage=='Repairs'){
					$posPer = $_SESSION["allowed"][2]??array();
				}
				elseif($frompage=='Orders'){
					$posPer = $_SESSION["allowed"][7]??array();
				}
			}
			if(!empty($posPer)){
				foreach($posPer as $onePer){
					$onePer = trim((string) $onePer);
					if(strpos('cnccpp', $onePer) !== false){$cartPer = 0;}
				}						
			}
			$returnData['posPer'] = $posPer;
			$returnData['pos_id'] = $pos_id;
			$returnData['edit'] = $edit;
			$returnData['status'] = $status;
			$returnData['cartPer'] = $cartPer;
	
			$cartData = array();
			$query = $this->db->query("SELECT * FROM pos_cart WHERE pos_id = $pos_id", array());
			if($query){
				$i=0;
				while($row = $query->fetch(PDO::FETCH_OBJ)){
					$i++;
					$pos_cart_id = $row->pos_cart_id;
					$product_id = $item_id = intval($row->item_id);
					$item_type = $row->item_type;
					$description = stripslashes(trim((string) $row->description));
					$sku = $product_type = '';
					$manage_inventory_count = 0;
					$minimum_price = 0.00;
					$allow_backorder = 1;
					if($item_id>0){
						$productObj = $this->db->query("SELECT sku, product_type, manage_inventory_count, allow_backorder FROM product WHERE product_id = $item_id", array());
						if($productObj){
							$productOneRow = $productObj->fetch(PDO::FETCH_OBJ);

							$sku = $productOneRow->sku;
							$product_type = $productOneRow->product_type;
							$manage_inventory_count = intval($productOneRow->manage_inventory_count);
							$allow_backorder = intval($productOneRow->allow_backorder);

							$inventoryObj = $this->db->query("SELECT minimum_price FROM inventory WHERE product_id = $item_id AND accounts_id = $accounts_id", array());
							if($inventoryObj){
								$minimum_price = $inventoryObj->fetch(PDO::FETCH_OBJ)->minimum_price;
							}
						}
					}
					if($item_type =='one_time'){
						$description .= " [1]";
					}
					
					$add_description = stripslashes(trim((string) $row->add_description));
					if($add_description !=''){
						$add_description = nl2br($add_description);
					}
					
					$require_serial_no = $row->require_serial_no;
					$qty = floatval($row->qty);
					$shipping_qty = floatval($row->shipping_qty);
					
					$livestocksInfo = $serialInfo = array();
					if(in_array($item_type, array('livestocks')) || ($item_type=='product' && $require_serial_no>0)){					
						if($item_type=='livestocks'){
							$sqlitem = "SELECT item.item_id, item.item_number, item.carrier_name, pos_cart_item.pos_cart_item_id FROM item, pos_cart_item WHERE pos_cart_item.pos_cart_id = $pos_cart_id AND item.accounts_id = $accounts_id AND item.item_id = pos_cart_item.item_id ORDER BY pos_cart_item.pos_cart_item_id ASC";
							$itemquery = $this->db->query($sqlitem, array());
							if($itemquery){
								while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
									$livestocksInfo[] = array('pos_cart_item_id'=>$itemrow->pos_cart_item_id, 'item_id'=>$itemrow->item_id, 'item_number'=>$itemrow->item_number, 'carrier_name'=>$itemrow->carrier_name);
								}
							}
						}
						elseif($item_type=='product' && $require_serial_no>0){	
							$serialsql = "SELECT serial_number_id, serial_number FROM serial_number WHERE pos_cart_id = $pos_cart_id";
							$serialquery = $this->db->query($serialsql, array());
							if($serialquery){
								while($sonerow = $serialquery->fetch(PDO::FETCH_OBJ)){
									$serialInfo[] = array('serial_number_id'=>$sonerow->serial_number_id, 'serial_number'=>$sonerow->serial_number);
								}
							}
						}
					}
					
					$sales_price = round($row->sales_price,2);
					$discount_is_percent = floatval($row->discount_is_percent);
					$discount = floatval($row->discount);
					$taxable = $row->taxable;

					$NeedHaveOnPOInfo = array();
					$NeedHaveOnPOInfo['product_type'] = $product_type;
					$NeedHaveOnPOInfo['manage_inventory_count'] = intval($manage_inventory_count);
					$NeedHaveOnPOInfo['need'] = 0;
					$NeedHaveOnPOInfo['have'] = 0;
					$NeedHaveOnPOInfo['onPO'] = 0;
					if(in_array($product_type, array('Standard', 'Live Stocks')) && $manage_inventory_count>0){
						$NHPInfo = $this->NeedHaveOnPO($product_id, $product_type, 1);
						$NeedHaveOnPOInfo['need'] = $NHPInfo[0];
						$NeedHaveOnPOInfo['have'] = $NHPInfo[1];
						$NeedHaveOnPOInfo['onPO'] = $NHPInfo[2];
					}
								   
					$cartData[] = array('pos_cart_id'=>$pos_cart_id, 'item_id'=>$item_id, 'item_type'=>$item_type, 'description'=>$description, 'sku'=>$sku, 'product_type'=>$product_type, 
						'manage_inventory_count'=>$manage_inventory_count, 'add_description'=>$add_description, 'require_serial_no'=>$require_serial_no, 'qty'=>$qty, 'shipping_qty'=>$shipping_qty,
						'livestocksInfo'=>$livestocksInfo, 'serialInfo'=>$serialInfo, 'sales_price'=>$sales_price, 'minimum_price'=>$minimum_price, 'allow_backorder'=>$allow_backorder, 'discount_is_percent'=>$discount_is_percent,
						'discount'=>$discount, 'taxable'=>$taxable, 'HaveOnPO'=>$NeedHaveOnPOInfo);
				}
			}
			$returnData['cartData'] = $cartData;
		}
		
		return $returnData;
	}
	
	public function categoryProductPicker(){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$frompage = $GLOBALS['segment4name'];
		$accounts_id = $_SESSION["accounts_id"]??0;
		$returnvalue = $POST['returnvalue']??'';
		$category_id = intval($POST['category_id']??0);
		$categoryName = addslashes(trim((string) $POST['name']??''));
		
		//===========For only IMEI in Inventory===========//
		$sql = "";
		$bindData = array();
		$filtersql = " AND p.category_id = :category_id";
		$bindData['category_id'] = $category_id;
		if($categoryName !=' '){
			$categoryNameArray = explode (" ", $categoryName);
			if(!empty($categoryNameArray)){
				$num = 0;
				foreach($categoryNameArray as $oneCat) {
					if(trim((string) $oneCat) !=''){
						$filtersql .= " AND TRIM(CONCAT_WS(' ', p.sku, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name)) LIKE CONCAT('%', :categoryName$num, '%')";
						$bindData['categoryName'.$num] = trim((string) $oneCat);
						$num++;
					}
				}
			}
		}
		
		//===========For Livestocks Product / SKU===============//
		$sql .= "SELECT p.product_id AS product_id, p.product_type AS product_type, manufacturer.name AS manufacture, p.product_name AS product_name, p.colour_name AS colour_name, p.storage AS storage, p.physical_condition_name AS physical_condition_name, p.sku AS sku, i.regular_price, COUNT(item.item_id) AS current_inventory FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id)";
		if(in_array($frompage, array('POS', 'Inventory_Transfer'))){$sql .= " INNER";}
		else{$sql .= " LEFT";}
		$sql .= " JOIN item ON (item.accounts_id = $accounts_id AND p.product_id = item.product_id AND item.in_inventory = 1 AND item.item_publish = 1) WHERE i.accounts_id = $accounts_id$filtersql AND p.product_type = 'Live Stocks' AND i.product_id = p.product_id AND p.product_publish = 1 GROUP BY product_id 
		UNION SELECT p.product_id AS product_id, p.product_type AS product_type, manufacturer.name AS manufacture, p.product_name AS product_name, p.colour_name AS colour_name, p.storage AS storage, p.physical_condition_name AS physical_condition_name, p.sku AS sku, i.regular_price, i.current_inventory AS current_inventory FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id$filtersql";
		if(in_array($frompage, array('Purchase_orders', 'Inventory_Transfer'))){
			$sql .= " AND p.product_type = 'Standard'";
		}
		else{
			$sql .= " AND p.product_type != 'Live Stocks'";
		}
		if($frompage == 'POS'){
			$sql .= " AND ((p.manage_inventory_count = 0 OR p.manage_inventory_count is null) OR (p.manage_inventory_count=1 AND i.current_inventory>0) OR p.allow_backorder = 1)"; 
		}
		$sql .= " AND i.product_id = p.product_id AND p.product_publish = 1 GROUP BY product_id";				
					
		if($returnvalue=='datacount'){
			$returnStr = 0;
			$queryObj = $this->db->query($sql, $bindData);
			if($queryObj){
				$returnStr = $queryObj->rowCount();
			}
		}
		else{
			$returnStr = array();
			$starting_val = intval($POST['starting_val']);
			$sql .= "  ORDER BY TRIM(CONCAT_WS(' ', manufacture, product_name, colour_name, storage, physical_condition_name)) ASC, sku ASC LIMIT $starting_val, 12";
			$query = $this->db->querypagination($sql, $bindData);
			if($query){
				foreach($query as $rowproduct){
					
					$product_id = $rowproduct['product_id'];
					$product_name = stripslashes(trim($rowproduct['manufacture'].' '.$rowproduct['product_name']));

					if($rowproduct['colour_name'] !=''){$product_name .= ' '.$rowproduct['colour_name'];}
					if($rowproduct['storage'] !=''){$product_name .= ' '.$rowproduct['storage'];}
					if($rowproduct['physical_condition_name'] !=''){$product_name .= ' '.$rowproduct['physical_condition_name'];}

					$shortproduct_name = $product_name;
					if(strlen($product_name)>55){$shortproduct_name = substr($product_name, 0, 55).'..';}
					$sku = stripslashes($rowproduct['sku']);

					$regular_price = round($rowproduct['regular_price'],2);
					$current_inventory = $rowproduct['current_inventory'];
					$returnStr[] = array('product_id'=>$product_id, 'product_type'=>$rowproduct['product_type'], 'product_name'=>$product_name, 'shortproduct_name'=>$shortproduct_name, 'sku'=>$sku, 'regular_price'=>$regular_price, 'current_inventory'=>$current_inventory);
				}
			}
		}
		return json_encode(array('login'=>'', 'frompage'=>$frompage, 'returnStr'=>$returnStr));
	}

	public function productPicker(){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$frompage = addslashes($POST['frompage']??'POS');
	
		$returnvalue = $POST['returnvalue'];
		//===========For only IMEI in Inventory===========//
		$sql = "";
		//===========For Livestocks Product / SKU===============//
		$sql .= "SELECT p.category_id AS category_id, category.category_name AS category_name FROM inventory i, product p LEFT JOIN category ON (category.category_id = p.category_id)";
		if(in_array($frompage, array('POS', 'Inventory_Transfer'))){$sql .= " INNER";}
		else{$sql .= " LEFT";}
		$sql .= " JOIN item ON (p.product_id = item.product_id AND item.in_inventory = 1 AND item.item_publish = 1) WHERE i.accounts_id = $accounts_id AND p.product_type = 'Live Stocks' AND i.product_id = p.product_id AND p.product_publish = 1 GROUP BY category_id 
		UNION SELECT p.category_id AS category_id, category.category_name AS category_name FROM inventory i, product p LEFT JOIN category ON (category.category_id = p.category_id) WHERE i.accounts_id = $accounts_id";
		if(in_array($frompage, array('Purchase_orders', 'Inventory_Transfer'))){
			$sql .= " AND p.product_type = 'Standard'";
		}
		else{
			$sql .= " AND p.product_type != 'Live Stocks'";
		}
		if($frompage == 'POS'){
			$sql .= " AND ((p.manage_inventory_count = 0 OR p.manage_inventory_count is null) OR (p.manage_inventory_count=1 AND i.current_inventory>0) OR p.allow_backorder = 1)"; 
		}
		$sql .= " AND i.product_id = p.product_id AND p.product_publish = 1 GROUP BY category_id";				
					
		if($returnvalue=='datacount'){
			$returnStr = 0;
			$queryObj = $this->db->query($sql, array());
			if($queryObj){
				$returnStr = $queryObj->rowCount();
			}
		}
		else{
			$returnStr = array();
		
			$starting_val = intval($POST['starting_val']);
			$sql .= " ORDER BY category_name ASC LIMIT $starting_val, 12";
			$query = $this->db->querypagination($sql, array());
			if($query){
				foreach($query as $rowproduct){
	
					$category_id = $rowproduct['category_id'];
	
					$category_name = stripslashes(trim((string) $rowproduct['category_name']));
					if($category_id==0){$category_name = 'noCategory';}
					if(strlen($category_name)>80){
						$category_name = substr($category_name, 0, 80).'...';
					}
					$returnStr[] = array('category_id'=>$category_id, 'category_name'=>$category_name);
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function productPickerIMEI(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$returnvalue = $POST['returnvalue'];
		$product_id = intval($POST['product_id']??0);
		$name = $POST['name']??'';
		$bindData = array();
		$strextra = "FROM item WHERE accounts_id = $accounts_id AND product_id = :product_id AND in_inventory = 1 AND item_publish = 1";
		$bindData['product_id'] = $product_id;
		if($name !=''){
			$strextra .= " AND item_number LIKE CONCAT('%', :name, '%')";
			$bindData['name'] = $name;
		}
		
		if($returnvalue=='datacount'){
			$returnData = 0;
			$sqlquery = "SELECT count(item_id) as counttotalrows $strextra";		
			$queryObj = $this->db->query($sqlquery, $bindData);
			if($queryObj){
				$returnData = intval($queryObj->fetch(PDO::FETCH_OBJ)->counttotalrows);
			}
		}
		else{
			$sqlquery = "SELECT item_id, item_number, product_id $strextra ORDER BY item_number ASC";		
			$starting_val = intval($POST['starting_val']??0);				
			$sqlquery .= " LIMIT $starting_val, 12";
			$query = $this->db->querypagination($sqlquery, $bindData);						
			$returnData = array();
			if($query){
				foreach($query as $rowitem){
					
					$item_number = $rowitem['item_number'];
					$item_id = $rowitem['item_id'];
					$product_id = $rowitem['product_id'];
					$sqlPM = "SELECT manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id";
					$productObj = $this->db->query($sqlPM, array());
					if($productObj){
						$product_row = $productObj->fetch(PDO::FETCH_OBJ);
						
						$product_name = str_replace("'", '&lsquo;', stripslashes($product_row->product_name));
						$manufacturer_name = str_replace("'", '&lsquo;', stripslashes(trim((string) $product_row->manufacture)));
						if($manufacturer_name !=''){$product_name = $manufacturer_name.' '.$product_name;}
						
						$colour_name = $product_row->colour_name;
						if($colour_name !=''){$product_name .= ' '.$colour_name;}
						
						$storage = $product_row->storage;
						if($storage !=''){$product_name .= ' '.$storage;}
						
						$physical_condition_name = $product_row->physical_condition_name;
						if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
							
						$shortproduct_name = $product_name;
						
						if(strlen($product_name)>55){$shortproduct_name = substr($product_name, 0, 55).'..';}
						$sku = stripslashes($product_row->sku);
						
						$current_inventory = 1;
						
						$regular_price = 0.00;
						$inventoryObj = $this->db->query("SELECT regular_price FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
						if($inventoryObj){
							$regular_price = round($inventoryObj->fetch(PDO::FETCH_OBJ)->regular_price,2);
						}						
						
						$returnData[] = array('item_id'=>$item_id, 'item_number'=>$item_number, 'product_name'=>$product_name, 'shortproduct_name'=>$shortproduct_name, 'regular_price'=>$regular_price, 'current_inventory'=>$current_inventory);
					}
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnData'=>$returnData));
	}
	
    public function addCartsProduct($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $alert_message = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $employee_id = $_SESSION["user_id"];
		$Common = new Common($this->db);
		
		$customer_id = 0;
		$pos_id = intval($POST['pos_id']??0);
		$clickYesNo = intval($POST['clickYesNo']??0);
		$repairs_status = $POST['repairs_status']??'';
		$orderStatus = $POST['orderStatus']??'';
		
		if($frompage=='POS'){
			if($pos_id == 0 && isset($_SESSION["pos_id"])){
				$pos_id = $_SESSION["pos_id"];
			}
			if(isset($_SESSION["customer_id"])){
				$customer_id = $_SESSION["customer_id"];
			}
			if(isset($_SESSION["employee_id"])){
				$employee_id = $_SESSION["employee_id"];
			}
		}

		$posObj = false;
		if($pos_id>0){
			$posObj = $this->db->query("SELECT customer_id FROM pos WHERE pos_id = :pos_id AND accounts_id = $accounts_id", array('pos_id'=>$pos_id),1);
			if($posObj){
				$customer_id = $posObj->fetch(PDO::FETCH_OBJ)->customer_id;
			}
		}
			
		if(intval($customer_id)==0){
			$customerObj = $this->db->query("SELECT default_customer FROM accounts WHERE accounts_id = $accounts_id", array());
			if($customerObj){
				$customer_id = intval($customerObj->fetch(PDO::FETCH_OBJ)->default_customer);
			}
		}

		if($frompage=='POS' && (!$posObj || $pos_id==0)){
			
			$taxes_name1 = $taxes_name2 = '';
			$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
			if(isset($_SESSION["taxes_id1"])){
				$taxes_id1 = $_SESSION["taxes_id1"];
				if($taxes_id1>0){
					$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id1 ORDER BY taxes_id DESC LIMIT 0, 1", array());
					if($taxesObj){
						$taxes_name1 = $taxesObj[0]['taxes_name'];
						$taxes_percentage1 = $taxesObj[0]['taxes_percentage'];
						$tax_inclusive1 = $taxesObj[0]['tax_inclusive'];
					}
				}
			}
			
			if(isset($_SESSION["taxes_id2"])){
				$taxes_id2 = $_SESSION["taxes_id2"];
				if($taxes_id2>0){
					$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id2 ORDER BY taxes_id DESC LIMIT 0, 1", array());
					if($taxesObj){
						$taxes_name2 = $taxesObj[0]['taxes_name'];
						$taxes_percentage2 = $taxesObj[0]['taxes_percentage'];
						$tax_inclusive2 = $taxesObj[0]['tax_inclusive'];
					}
				}
			}

			if($taxes_name1 =='' || $taxes_name2 == ''){
				$taxesObj = $this->db->query("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND taxes_publish = 1 ORDER BY taxes_name ASC", array());
				if($taxesObj){
					$no_of_default_rows = 0;
					while($taxesonerow = $taxesObj->fetch(PDO::FETCH_OBJ)){

						$staxes_name = $taxesonerow->taxes_name;
						$staxes_percentage = $taxesonerow->taxes_percentage;
						$default_tax = $taxesonerow->default_tax;
						$tax_inclusive = $taxesonerow->tax_inclusive;
						
						if($default_tax>0){
							$no_of_default_rows++;
							if($no_of_default_rows==1 && $taxes_name1==''){
								$taxes_name1 = $staxes_name;
								$taxes_percentage1 = $staxes_percentage;
								$tax_inclusive1 = $tax_inclusive;
							}

							if($no_of_default_rows==2 && $taxes_name2==''){
								$taxes_name2 = $staxes_name;
								$taxes_percentage2 = $staxes_percentage;
								$tax_inclusive2 = $tax_inclusive;
							}
						}
					}
				}
			}
			
			$pos_type = $this->db->checkCharLen('pos.pos_type', 'Sale');
			
			$posData = array('invoice_no' => 0,
							'sales_datetime' => date('Y-m-d H:i:s'),
							'employee_id' => $employee_id,
							'customer_id' => $customer_id,
							'pos_type' => $pos_type,
							'taxes_name1' => $taxes_name1,
							'taxes_percentage1' => $taxes_percentage1,
							'tax_inclusive1' => $tax_inclusive1,
							'taxes_name2' => $taxes_name2,
							'taxes_percentage2' => $taxes_percentage2,
							'tax_inclusive2' => $tax_inclusive2,
							'created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'user_id' => $user_id,
							'accounts_id' => $accounts_id,
							'pos_publish' => 0, 
							'credit_days' => 0, 
							'is_due' => 0, 
							'status' => 'New');

			$pos_id = $this->db->insert('pos', $posData);
			$_SESSION["pos_id"] = $pos_id;
		}

		$fieldname = trim((string) $POST['fieldname']);
		if($fieldname=='item_number'){$fieldname = 'sku';}
		$fieldvalue = trim((string) addslashes($POST['fieldvalue']));
		
		$pos_cart_id = $discount = $taxable = $isIMEI = 0;
		$discount_is_percent = 1;
		
		$created_on = $last_updated = date('Y-m-d H:i:s');

		if($fieldvalue !='' && ($posObj || $pos_id>0)){
			$sqlquery = "SELECT invoice_no, pos_type, order_status FROM pos WHERE pos_id = $pos_id";
			$queryObj = $this->db->query($sqlquery, array());
			if($queryObj){
				$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
				$invoice_no = $posRow->invoice_no;
				$pos_type = $posRow->pos_type;
				$order_status = $posRow->order_status;
				if(($invoice_no>0 && $frompage=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
					$action = 'reload';
				}
			}
			
			if(empty($action)){
				$search_sku = $fieldvalue;
				$results = array();
				//===========For only IMEI in Inventory===========//
				$sql = "SELECT item.item_id, product.product_type FROM item, product WHERE item.item_number = :search_sku AND item.accounts_id = $accounts_id AND product.product_id = item.product_id AND item.in_inventory = 1 AND item.item_publish = 1 AND product.product_publish = 1";
				$sql .= " GROUP BY item.item_id ORDER BY product.product_name ASC, product.colour_name ASC, product.storage ASC, product.physical_condition_name ASC, item_number ASC LIMIT 0,1";
				$query = $this->db->querypagination($sql, array('search_sku'=>trim((string) $search_sku)));
				if($query){
					foreach($query as $onerow){						
						$results[$onerow['item_id']] = $onerow['product_type'];
						$isIMEI++;
					}
				}
				
				if(empty($results)){
					$bindData = array();
					$sql = "SELECT p.product_id, p.product_type, COUNT(item.item_id) AS stockQty FROM inventory i, product p";
					if($frompage == 'POS'){$sql .= " INNER";}
					else{$sql .= " LEFT";}
					$sql .= " JOIN item ON (p.product_id = item.product_id AND item.in_inventory = 1 AND item.item_publish = 1) 
					WHERE i.accounts_id = $accounts_id AND p.product_type = 'Live Stocks' AND p.sku = :search_sku AND i.product_id = p.product_id AND p.product_publish = 1 GROUP BY p.sku";				
					if($frompage == 'POS'){
						$sql .= " HAVING stockQty>0";
					}
					$sql .= " LIMIT 0, 1";
					$bindData['search_sku'] = trim((string) $search_sku);
					
					$query = $this->db->querypagination($sql, $bindData);					
					if($query){
						foreach($query as $onerow){						
							$results[$onerow['product_id']] = $onerow['product_type'];
						}
					}
				}
				
				if(empty($results)){
					//============For Not Live Stocks===========//
					$bindData = array();
					$sql = "SELECT p.product_id, p.product_type FROM product p, inventory i WHERE i.accounts_id = $accounts_id AND p.product_type != 'Live Stocks' AND p.product_publish = 1";
					if($frompage == 'POS'){
						$sql .= " AND ((p.manage_inventory_count = 0 OR p.manage_inventory_count is null) OR (p.manage_inventory_count=1 AND i.current_inventory>0) OR p.allow_backorder = 1)"; 
					}
					$sql .= " AND p.sku = :search_sku AND i.product_id = p.product_id";
					$bindData['search_sku'] = trim((string) $search_sku);
					$sql .= " GROUP BY p.sku LIMIT 0,1";
					$query1 = $this->db->querypagination($sql, $bindData);
					if($query1){
						foreach($query1 as $onerow){
							$results[$onerow['product_id']] = $onerow['product_type'];
						}
						//$this->db->writeIntoLog(json_encode($results));
					}
				}
				
				if(empty($results) && $clickYesNo==0){
					
					$newresults = array();
					$bindData = array();
					$sql = "SELECT item.item_id, item.item_number, manufacturer.name AS manufacture, product.product_name, product.colour_name, product.storage, product.physical_condition_name 
							FROM item, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) 
							WHERE item.accounts_id = $accounts_id AND product.product_id = item.product_id 
								AND item.in_inventory = 1 AND item.item_publish = 1 AND product.product_publish = 1";
					$search_sku = addslashes(trim((string) $search_sku));
					if ( $search_sku == "" ) { $search_sku = " "; }
					$search_skues = explode (" ", $search_sku);
					if ( strpos($search_sku, " ") === false ) {$search_skues[0] = $search_sku;}
					$num = 0;
					while ( $num < sizeof($search_skues) ) {
						$sql .= " AND TRIM(CONCAT_WS(' ', item.item_number, product.sku, manufacturer.name, product.product_name, product.colour_name, product.storage, product.physical_condition_name)) LIKE CONCAT('%', :search_sku$num, '%')";
						$bindData['search_sku'.$num] = trim((string) $search_skues[$num]);
						$num++;
					}
					$sql .= " GROUP BY item.item_id ORDER BY manufacturer.name ASC, product.product_name ASC, product.colour_name ASC, product.storage ASC, product.physical_condition_name ASC, item.item_number ASC";
					$query = $this->db->querypagination($sql, $bindData);
					if($query){	
						foreach($query as $onerow){
							$item_id = stripslashes($onerow['item_id']);
							$name = trim(stripslashes((string) $onerow['manufacture']));
							$product_name = stripslashes($name.' '.$onerow['product_name']);
							
							$colour_name = $onerow['colour_name'];
							if($colour_name !=''){$product_name .= ' '.$colour_name;}
							
							$storage = $onerow['storage'];
							if($storage !=''){$product_name .= ' '.$storage;}
							
							$physical_condition_name = $onerow['physical_condition_name'];
							if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
		
							$item_number = $onerow['item_number'];
							$str1 = trim((string) "$product_name - IMEI $item_number");
							
							$newresults[] = array('label'=>$str1, 'item_id'=>$item_id, 'product_type'=>'Live Stocks');
						}
					}
					
					//===========For Livestocks Product / SKU===============//
					$sql = "SELECT p.product_id, p.sku, p.product_type, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name 
							FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id)";
					if($frompage == 'POS'){$sql .= " INNER JOIN item ON (p.product_id = item.product_id AND item.in_inventory = 1 AND item.item_publish = 1)";}
					$sql .= " WHERE i.accounts_id = $accounts_id AND p.product_type = 'Live Stocks'";
					
					$search_skues = explode (" ", $search_sku);
					if ( strpos($search_sku, " ") === false ) {$search_skues[0] = $search_sku;}
					$bindData = array();
					$num = 0;
					while ( $num < sizeof($search_skues) ) {
						$sql .= " AND TRIM(CONCAT_WS(' ', p.sku, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name)) LIKE CONCAT('%', :search_sku$num, '%')";
						$bindData['search_sku'.$num] = trim((string) $search_skues[$num]);
						$num++;
					}
					$sql .= " AND i.product_id = p.product_id AND p.product_publish = 1 GROUP BY p.sku";				
					$query1 = $this->db->querypagination($sql, $bindData);
					if($query1){
						foreach($query1 as $onerow){
							$name = stripslashes((string) $onerow['manufacture']);
							$product_name = stripslashes($name.' '.$onerow['product_name']);
							
							$colour_name = $onerow['colour_name'];
							if($colour_name !=''){$product_name .= ' '.$colour_name;}
							
							$storage = $onerow['storage'];
							if($storage !=''){$product_name .= ' '.$storage;}
							
							$physical_condition_name = $onerow['physical_condition_name'];
							if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

							$sku = $onerow['sku'];
							$product_type = $onerow['product_type'];
							$label = trim((string) "$product_name - SKU $sku");
							
							$newresults[] = array('label'=>$label,'item_id'=>$onerow['product_id'], 'product_type'=>'Live Stocks');
						}
					}
					
					$sql = "SELECT p.product_id, p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name 
							FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id AND p.product_type != 'Live Stocks'";
					if($frompage == 'POS'){
						$sql .= " AND ((p.manage_inventory_count = 0 OR p.manage_inventory_count is null) OR (p.manage_inventory_count=1 AND i.current_inventory>0) OR p.allow_backorder = 1)"; 
					}
					
					$search_sku = addslashes(trim((string) $search_sku));
					if ( $search_sku == "" ) { $search_sku = " "; }
					$search_skues = explode (" ", $search_sku);
					if ( strpos($search_sku, " ") === false ) {$search_skues[0] = $search_sku;}
					$bindData = array();
					$num = 0;
					while ( $num < sizeof($search_skues) ) {
						$sql .= " AND TRIM(CONCAT_WS(' ', p.sku, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name)) LIKE CONCAT('%', :search_sku$num, '%')";
						$bindData['search_sku'.$num] = trim((string) $search_skues[$num]);
						$num++;
					}
					$sql .= " AND i.product_id = p.product_id AND p.product_publish = 1 GROUP BY p.sku";				
					$query1 = $this->db->querypagination($sql, $bindData);
					if($query1){
						foreach($query1 as $onerow){
							$name = trim(stripslashes((string) $onerow['manufacture']));
							$product_name = stripslashes($name.' '.$onerow['product_name']);
							
							if($onerow['colour_name'] !=''){$product_name .= ' '.$onerow['colour_name'];}
							if($onerow['storage'] !=''){$product_name .= ' '.$onerow['storage'];}
							if($onerow['physical_condition_name'] !=''){$product_name .= ' '.$onerow['physical_condition_name'];}
		
							$sku = $onerow['sku'];
							$label = trim("$product_name - SKU $sku");
							
							$newresults[] = array('label'=>$label,'item_id'=>$onerow['product_id'], 'product_type'=>'Product');
						}
					}
					
					if(!empty($newresults)){
						usort($newresults, $Common->build_sorter('label'));
						$l=0;
						$oneRow = $newresults[0];
						if(strpos($oneRow['label'], ' - IMEI ') !== false){
							$isIMEI++;
						}
						$results[$oneRow['item_id']] = $oneRow['product_type'];
					}
				}
				
				if(!empty($results)){
					foreach($results as $item_id=>$product_type){
						if($product_type=='Live Stocks'){
							if($isIMEI>0){
								$sql1 = "SELECT p.product_id, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku, p.add_description, i.regular_price, i.prices_enabled, p.taxable, p.alert_message  
									FROM item, inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE item.accounts_id = $accounts_id AND i.accounts_id = $accounts_id AND item.item_id = $item_id AND p.product_id = item.product_id AND item.in_inventory = 1 AND item.item_publish = 1";
							}
							else{
								$sql1 = "SELECT p.product_id, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku, p.add_description, i.regular_price, i.prices_enabled, p.taxable, p.alert_message  
									FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id AND p.product_id = $item_id";
							}
							$sql1 .= " AND i.product_id = p.product_id AND p.product_publish = 1";
							if($isIMEI>0){
								$sql1 .= " GROUP BY item.item_id ORDER BY manufacturer.name ASC, p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC, item.item_number ASC limit 0,1";
							}
							else{
								$sql1 .= " GROUP BY p.product_id ORDER BY manufacturer.name ASC, p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC limit 0,1";
							}
							//return $sql1;
							$query1 = $this->db->querypagination($sql1, array());
							if($query1){
								foreach($query1 as $onerow){
									$product_id = $onerow['product_id'];
									$product_type = 'livestocks';
									$description = stripslashes($onerow['product_name']);
									$manufacturer_name = $onerow['manufacture'];
									if($manufacturer_name !=''){$description = stripslashes(trim($manufacturer_name.' '.$description));}
		
									$colour_name = $onerow['colour_name'];
									if($colour_name !=''){$description .= ' '.$colour_name;}
		
									$storage = $onerow['storage'];
									if($storage !=''){$description .= ' '.$storage;}
		
									$physical_condition_name = $onerow['physical_condition_name'];
									if($physical_condition_name !=''){$description .= ' '.$physical_condition_name;}
		
									if($onerow['sku'] !=''){$description = $description." ($onerow[sku])";}
		
									$add_description = stripslashes(trim((string) $onerow['add_description']));
		
									$regular_price = round($onerow['regular_price'],2);
									$prices_enabled = $onerow['prices_enabled'];
									$alert_message = trim((string) stripslashes($onerow['alert_message']));
		
									$qty = 1;
									$taxable = $onerow['taxable'];
									$pos_cart_id=0;
									$sql2 = "SELECT * FROM pos_cart WHERE pos_id = $pos_id AND item_type ='livestocks' AND item_id = $product_id ORDER BY pos_id ASC LIMIT 0,1";
									$query2 = $this->db->querypagination($sql2, array());
									if($query2){
										foreach($query2 as $pos_cartrow){
											$pos_cart_id = $pos_cartrow['pos_cart_id'];
											
											if($isIMEI>0 && $orderStatus != 'Quotes'){
												$pos_cart_item_id = 0;
												$pos_cart_itemObj = $this->db->query("SELECT pos_cart_item_id FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id AND item_id = $item_id AND return_pos_cart_id = 0", array());
												if($pos_cart_itemObj){
													$pos_cart_item_id = $pos_cart_itemObj->fetch(PDO::FETCH_OBJ)->pos_cart_item_id;
												}
												if($pos_cart_item_id>0){
													$updateitem = $this->db->update('item', array('in_inventory'=>0, 'is_pos'=>1), $item_id);
												}
												else{
													$pciData =array('pos_cart_id'=>$pos_cart_id, 
																	'item_id'=>$item_id, 
																	'sale_or_refund'=>1, 
																	'return_pos_cart_id'=>0);
													$pos_cart_item_id = $this->db->insert('pos_cart_item', $pciData);
													if($pos_cart_item_id){
														$updateitem = $this->db->update('item', array('in_inventory'=>0, 'is_pos'=>1), $item_id);
													}
												}
											}
											else{
												$qty = 0;
											}											
											
											$qty = $pos_cartrow['qty']+$qty;
											$shipping_qty = 0;
											$pos_cart_itemObj2 = $this->db->query("SELECT count(pos_cart_item_id) AS totalShipQty FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id AND return_pos_cart_id = 0", array());
											if($pos_cart_itemObj2 && $orderStatus != 'Quotes'){
												$shipping_qty = $pos_cart_itemObj2->fetch(PDO::FETCH_OBJ)->totalShipQty;
											}
											$newsales_price = $pos_cartrow['sales_price'];
											if($prices_enabled==1){
												$old_qty = $pos_cartrow['qty'];
												$new_qty = $qty;
												$getproduct_prices = $this->getproduct_prices($pos_cartrow['item_id'], $regular_price, $newsales_price, $old_qty, $new_qty, $customer_id);
												if($getproduct_prices){
													$newsales_price = $getproduct_prices;
												}
											}
		
											$updatepc = $this->db->update('pos_cart', array('sales_price'=>$newsales_price, 'qty'=>$qty, 'shipping_qty'=>$shipping_qty), $pos_cart_id);
											if($updatepc){
												$action = 'Update';
											}
										}
									}
		
									if($pos_cart_id==0){
										if($isIMEI==0){
											$qty = 0;
											if($frompage == 'Orders'){$qty = 1;}
										}
										$newsales_price = floatval($regular_price);
										if($prices_enabled==1){
											$old_qty = $new_qty = $qty;
											$getproduct_prices = $this->getproduct_prices($product_id, $regular_price, $newsales_price, $old_qty, $new_qty, $customer_id);
											if($getproduct_prices){
												$newsales_price = $getproduct_prices;
											}
										}
										$product_type = $this->db->checkCharLen('pos_cart.item_type', $product_type);
										$description = $this->db->checkCharLen('pos_cart.description', $description);
										
										$insertdata = array('pos_id'=>$pos_id,
											'item_id'=>$product_id,
											'item_type'=>$product_type,
											'description'=>$description,
											'add_description'=>$add_description,
											'require_serial_no'=>0,
											'sales_price'=>$newsales_price,
											'qty'=>$qty,
											'shipping_qty'=>0,
											'return_qty'=>0,
											'ave_cost'=>0,
											'discount_is_percent'=>$discount_is_percent,
											'discount'=>$discount,
											'taxable'=>$taxable
										);
										$pos_cart_id = $this->db->insert('pos_cart', $insertdata);
										if($pos_cart_id){
											if($isIMEI>0 && $orderStatus != 'Quotes'){
												$pciData =array('pos_cart_id'=>$pos_cart_id, 
																'item_id'=>$item_id, 
																'sale_or_refund'=>1, 
																'return_pos_cart_id'=>0);
												$pos_cart_item_id = $this->db->insert('pos_cart_item', $pciData);
												if($pos_cart_item_id){
													$this->db->update('item', array('in_inventory'=>0, 'is_pos'=>1), $item_id);
													$this->db->update('pos_cart', array('qty'=>1, 'shipping_qty'=>1), $pos_cart_id);
												}
											}
											$action = 'Add';
										}
										else{
											$action = 'notProductOrder';
										}
									}
								}
							}							
							else{
								$action = 'noDataFound';
							}
						}
						else{
							$sql = "SELECT p.*, i.*, manufacturer.name AS manufacture FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = '$item_id' AND i.accounts_id = $accounts_id AND i.product_id = p.product_id AND p.product_publish = 1 ORDER BY p.product_id ASC LIMIT 0,1";
							$query = $this->db->querypagination($sql, array());
							if($query){
								foreach($query as $oneproductrow){
		
									$item_id = $product_id = $oneproductrow['product_id'];
									$inventory_id = $oneproductrow['inventory_id'];
									$product_type = $oneproductrow['product_type'];
									$category_id = $oneproductrow['category_id'];
									$sku = $oneproductrow['sku'];
									$taxable = $oneproductrow['taxable'];
									$manage_inventory_count = $oneproductrow['manage_inventory_count'];
									$current_inventory = $oneproductrow['current_inventory'];
									$allow_backorder = $oneproductrow['allow_backorder'];
									$add_description = stripslashes(trim((string) $oneproductrow['add_description']));
									$require_serial_no = $oneproductrow['require_serial_no'];
									$item_type = 'product';
		
									$regular_price = round($oneproductrow['regular_price'],2);
									$prices_enabled = $oneproductrow['prices_enabled'];
									$alert_message = trim((string) stripslashes($oneproductrow['alert_message']));
									
									$qty = $shipping_qty = 1;
									if($frompage=='POS' || $frompage=='Orders' || (empty($repairs_status) || !in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled'))) || (strcmp($item_type,'product')==0 && $require_serial_no>0)  || $orderStatus == 'Quotes'){
										$shipping_qty = 0;
									}
									
									if($require_serial_no>0 && $frompage=='POS'){$qty = 0;}
									$allowsale = 1;
									if($frompage=='POS'){
										$allowsale = 0;
										if($manage_inventory_count==0 || ($manage_inventory_count==1 && ($current_inventory >= $shipping_qty || $allow_backorder > 0))){
											$allowsale = 1;
										}
										else{
											$action = 'noStock';
										}
									}
		
									if($allowsale>0){
										//$this->db->writeIntoLog('Step1');
										$pos_cart_id = 0;
										$sql3 = "SELECT * FROM pos_cart WHERE pos_id = $pos_id AND item_type in ('product', 'livestocks') AND item_id = $item_id ORDER BY pos_id ASC LIMIT 0,1";
										$query3 = $this->db->querypagination($sql3, array());
										if($query3){
											foreach($query3 as $pos_cartrow){
												
												$pos_cart_id = $pos_cartrow['pos_cart_id'];
												if($product_type == 'Standard' && $require_serial_no>0){
													$action = 'productExist';
												}
												else{
													$qty = $pos_cartrow['qty']+$qty;
													$shipping_qty = $pos_cartrow['shipping_qty']+$shipping_qty;
													$newsales_price = $pos_cartrow['sales_price'];
													if($prices_enabled==1){
														$old_qty = $pos_cartrow['qty'];
														$new_qty = $qty;
														if($frompage=='Repairs' && !empty($repairs_status) && in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled'))){
															$old_qty = $pos_cartrow['shipping_qty'];
															$new_qty = $shipping_qty;
														}
														$getproduct_prices = $this->getproduct_prices($pos_cartrow['item_id'], $regular_price, $newsales_price, $old_qty, $new_qty, $customer_id);
														if($getproduct_prices){
															$newsales_price = $getproduct_prices;
														}
													}
													//$this->db->writeIntoLog('Step2');
													$updatepc = $this->db->update('pos_cart', array('sales_price'=>$newsales_price, 'qty'=>$qty, 'shipping_qty'=>$shipping_qty), $pos_cart_id);
													if($updatepc){
														if($pos_cartrow['shipping_qty'] !=$shipping_qty){
															$newcurrent_inventory = $current_inventory+$pos_cartrow['shipping_qty']-$shipping_qty;
															$updateproduct = $this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventory_id);
														}
														$action = 'Update';
													}
												}
											}
										}
		
										if($pos_cart_id==0){
		
											$description = stripslashes($oneproductrow['product_name']);
											$manufacturer_name = $oneproductrow['manufacture'];
											if($manufacturer_name !=''){$description = stripslashes(trim($manufacturer_name.' '.$description));}
		
											$colour_name = $oneproductrow['colour_name'];
											if($colour_name !=''){$description .= ' '.$colour_name;}
		
											$storage = $oneproductrow['storage'];
											if($storage !=''){$description .= ' '.$storage;}
		
											$physical_condition_name = $oneproductrow['physical_condition_name'];
											if($physical_condition_name !=''){$description .= ' '.$physical_condition_name;}
		
											if($oneproductrow['sku'] !=''){$description .= " ($oneproductrow[sku])";}
		
											$newsales_price = floatval($regular_price);
											if($prices_enabled==1){
												$old_qty = $new_qty = $qty;
												if($frompage=='Repairs' && !empty($repairs_status) && in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled'))){
													$old_qty = $new_qty = $shipping_qty;
												}
												$getproduct_prices = $this->getproduct_prices($item_id, $regular_price, $newsales_price, $old_qty, $new_qty, $customer_id);
												if($getproduct_prices){
													$newsales_price = $getproduct_prices;
												}
											}
											$item_type = $this->db->checkCharLen('pos_cart.item_type', $item_type);
											$description = $this->db->checkCharLen('pos_cart.description', $description);
										
											$insertdata = array('pos_id'=>$pos_id,
												'item_id'=>$item_id,
												'item_type'=>$item_type,
												'description'=>$description,
												'add_description'=>$add_description,
												'require_serial_no'=>$require_serial_no,
												'sales_price'=>$newsales_price,
												'qty'=>$qty,
												'shipping_qty'=>$shipping_qty,
												'return_qty'=>0,
												'ave_cost'=>0,
												'discount_is_percent'=>$discount_is_percent,
												'discount'=>$discount,
												'taxable'=>$taxable
											);
											$pos_cart_id = $this->db->insert('pos_cart', $insertdata);
											if($pos_cart_id){
												if($shipping_qty !=0){
													$newcurrent_inventory = floor($current_inventory-$shipping_qty);
													$updateproduct = $this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventory_id);
												}
												$action = 'Add';
											}
											else{
												$action = 'notProductOrder';
											}
										}
		
										if($frompage =='Repairs'){
											$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
											if($repairsObj){
												$repairs_id = $repairsObj->fetch(PDO::FETCH_OBJ)->repairs_id;
												$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
											}
										}
									}
								}
							}
							else{
								$action = 'noDataFound';
							}
						}
					}
				}
				else{
					$action = 'noInventory';
				}
			}
		}
		else{
			$action = 'noOrder';
		}
		$cartsData = array();
		if($action =='Add' || $action == 'Update'){
			$cartsData = $this->loadCartData($frompage, $pos_id);
		}		
		return json_encode(array('login'=>'', 'action'=>$action, 'pos_id'=>$pos_id, 'cartsData'=>$cartsData, 'alertMessage'=>$alert_message));
    }
	
	public function addCartsIMEI($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$pos_cart_id = intval($POST['pos_cart_id']??0);
		$item_number = $POST['item_number']??'';
		$pos_id = $po_id = 0;
		if($pos_cart_id>0 && $item_number !=''){
			if($frompage=='Inventory_Transfer'){
				$pos_cartObj = $this->db->query("SELECT * FROM po_items WHERE po_items_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
				if($pos_cartObj){
					$onepos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ);
					$po_items_id = $onepos_cartrow->po_items_id;
					$po_id = $onepos_cartrow->po_id;
					
					$product_id = $onepos_cartrow->product_id;
					
					$sqlitem = "SELECT item_id FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_number = :item_number AND in_inventory = 1 AND item_publish = 1 ORDER BY item_number ASC LIMIT 0,1";
					$itemquery = $this->db->querypagination($sqlitem, array('item_number'=>$item_number));
					if($itemquery){
						foreach($itemquery as $itemrow){
							
							$item_id = $itemrow['item_id'];
							$po_cart_item_id = 0;
							$po_cart_itemObj = $this->db->query("SELECT po_cart_item_id FROM po_cart_item WHERE po_items_id = $po_items_id AND item_id = $item_id AND return_po_items_id = 0", array());
							if($po_cart_itemObj){
								$po_cart_item_id = $po_cart_itemObj->fetch(PDO::FETCH_OBJ)->po_cart_item_id;
							}
							
							if($po_cart_item_id>0){
								$updateitem = $this->db->update('item', array('in_inventory'=>0, 'is_pos'=>1), $item_id);
								if($updateitem){$action = 'Add';}
							}
							else{
								$pciData =array('po_items_id'=>$po_items_id, 
												'item_id'=>$item_id, 
												'po_or_return'=>1, 
												'return_po_items_id'=>0);
								$po_cart_item_id = $this->db->insert('po_cart_item', $pciData);
								if($po_cart_item_id){
									$updateitem = $this->db->update('item', array('in_inventory'=>0, 'is_pos'=>1), $item_id);
									if($updateitem){$action = 'Add';}
								}
							}							
						}
						
						$ordered_qty = $onepos_cartrow->ordered_qty;
						$shipping_qty = 0;
						$po_cart_itemObj2 = $this->db->query("SELECT count(po_cart_item_id) AS totalShipQty FROM po_cart_item WHERE po_items_id = $po_items_id AND return_po_items_id = 0", array());
						if($po_cart_itemObj2){
							$shipping_qty = $po_cart_itemObj2->fetch(PDO::FETCH_OBJ)->totalShipQty;
						}
						if($shipping_qty>$ordered_qty){
							$ordered_qty = $shipping_qty;
						}
						$this->db->update('po_items', array('ordered_qty'=>$ordered_qty, 'received_qty'=>$shipping_qty), $po_items_id);
					}
				}
			}
			else{
				$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
				if($pos_cartObj){
					$onepos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ);
					
					$pos_id = $onepos_cartrow->pos_id;
					
					$sqlquery = "SELECT invoice_no, pos_type, order_status FROM pos WHERE pos_id = $pos_id";
					$queryObj = $this->db->query($sqlquery, array());
					if($queryObj){
						$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
						$invoice_no = $posRow->invoice_no;
						$pos_type = $posRow->pos_type;
						$order_status = $posRow->order_status;
						if(($invoice_no>0 && $frompage=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
							$action = 'reload';
						}
					}
					
					if(empty($action)){
						$product_id = $onepos_cartrow->item_id;
						
						$sqlitem = "SELECT item_id FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_number = :item_number AND in_inventory = 1 AND item_publish = 1 ORDER BY item_number ASC LIMIT 0,1";
						$itemquery = $this->db->querypagination($sqlitem, array('item_number'=>$item_number));
						if($itemquery){
							foreach($itemquery as $itemrow){
								
								$item_id = $itemrow['item_id'];
								$pos_cart_item_id = 0;
								$pos_cart_itemObj = $this->db->query("SELECT pos_cart_item_id FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id AND item_id = $item_id AND return_pos_cart_id = 0", array());
								if($pos_cart_itemObj){
									$pos_cart_item_id = $pos_cart_itemObj->fetch(PDO::FETCH_OBJ)->pos_cart_item_id;
								}
								
								if($pos_cart_item_id>0){
									$updateitem = $this->db->update('item', array('in_inventory'=>0, 'is_pos'=>1), $item_id);
									if($updateitem){$action = 'Add';}
								}
								else{
									$pciData =array('pos_cart_id'=>$pos_cart_id, 
													'item_id'=>$item_id, 
													'sale_or_refund'=>1, 
													'return_pos_cart_id'=>0);
									$pos_cart_item_id = $this->db->insert('pos_cart_item', $pciData);
									if($pos_cart_item_id){
										$updateitem = $this->db->update('item', array('in_inventory'=>0, 'is_pos'=>1), $item_id);
										if($updateitem){$action = 'Add';}
									}
								}							
							}
						}
						
						$shipping_qty = 0;
						$pos_cart_itemObj2 = $this->db->query("SELECT count(pos_cart_item_id) AS totalShipQty FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id AND return_pos_cart_id = 0", array());
						if($pos_cart_itemObj2){
							$shipping_qty = $pos_cart_itemObj2->fetch(PDO::FETCH_OBJ)->totalShipQty;
						}
						
						$oldshipping_qty = $onepos_cartrow->shipping_qty;
						
						if($shipping_qty != $oldshipping_qty){
							$qty = $onepos_cartrow->qty;
							if($shipping_qty>$qty){$qty = $shipping_qty;}
								
							if(in_array($frompage, array('POS', 'Repairs'))){
								$this->db->update('pos_cart', array('qty'=>$shipping_qty, 'shipping_qty'=>$shipping_qty), $pos_cart_id);
							}
							else{
								$this->db->update('pos_cart', array('qty'=>$qty, 'shipping_qty'=>$shipping_qty), $pos_cart_id);
							}
						}
					}
				}
			}
		}
		$cartsData = array();
		if($action =='Add'){
			if($frompage=='Inventory_Transfer' && $po_id>0){
				$Inventory_Transfer = new Inventory_Transfer($this->db);
				$status = 'Open';
				$poObj =  $this->db->query("SELECT status FROM po WHERE po_id = :po_id AND accounts_id = $accounts_id", array('po_id'=>$po_id),1);
				if($poObj){
					$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
				}
				$cartsData = $Inventory_Transfer->loadITCartData($po_id, $status);
			}
			elseif( $pos_id>0){
				$cartsData = $this->loadCartData($frompage, $pos_id);
			}			
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'pos_id'=>$pos_id, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
    
	}
	
	public function addCartsSerial($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = '';
		$pos_cart_id = intval($POST['pos_cart_id']??0);		
		$ser_imei = $POST['serial_number']??'';		
		$repairs_status = $POST['repairs_status']??'';			
		$accounts_id = $_SESSION["accounts_id"]??0;
		$pos_id = 0;
		
		if($pos_cart_id>0 && $ser_imei !=''){
			
			$sqlquery = "SELECT pos_id FROM pos_cart WHERE pos_cart_id = $pos_cart_id";
			$queryObj = $this->db->query($sqlquery, array());
			if($queryObj){
				$pos_id = $queryObj->fetch(PDO::FETCH_OBJ)->pos_id;
			}
			$customer_id = 0;
			$sqlquery = "SELECT invoice_no, pos_type, order_status, customer_id FROM pos WHERE pos_id = $pos_id";
			$queryObj = $this->db->query($sqlquery, array());
			if($queryObj){
				$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
				$invoice_no = $posRow->invoice_no;
				$customer_id = intval($posRow->customer_id);
				$pos_type = $posRow->pos_type;
				$order_status = $posRow->order_status;
				if(($invoice_no>0 && $frompage=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
					$action = 'reload';
				}

				if($pos_type == 'Repairs'){
					$sqlRepairs = "SELECT status FROM repairs WHERE pos_id = $pos_id";
					$repairsObj = $this->db->query($sqlRepairs, array());
					if($repairsObj){
						$repairs_status = $repairsObj->fetch(PDO::FETCH_OBJ)->status;
					}
				}
			}
			
			if(empty($action)){
				
				$datacount = 0;
				$snObj = $this->db->query("SELECT count(serial_number_id) as counttotalrows FROM serial_number WHERE pos_cart_id = :pos_cart_id and serial_number = :serial_number and returned_pos_cart_id = 0", array('pos_cart_id'=>$pos_cart_id, 'serial_number'=>$ser_imei));
				if($snObj){$datacount = $snObj->fetch(PDO::FETCH_OBJ)->counttotalrows;}
				
				if($datacount>0){
					$action = 'Duplicate';
				}
				else{
					$ser_imei = $this->db->checkCharLen('serial_number.serial_number', $ser_imei);
		
					$serialdata = array('pos_cart_id' => $pos_cart_id,	
										'serial_number' => $ser_imei,	
										'returned_pos_cart_id' => 0);
					$serial_number_id = $this->db->insert('serial_number',$serialdata);
					if($serial_number_id>0){
						
						$shipping_qty = 0;
						
						$snObj = $this->db->query("SELECT count(serial_number_id) as counttotalrows FROM serial_number WHERE pos_cart_id = :pos_cart_id and returned_pos_cart_id = 0", array('pos_cart_id'=>$pos_cart_id),1);
						if($snObj){$shipping_qty = $snObj->fetch(PDO::FETCH_OBJ)->counttotalrows;}
						
						$pcObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
						if($pcObj){
							$pos_cartrow = $pcObj->fetch(PDO::FETCH_OBJ);

							$qty = $pos_cartrow->qty;
							if($frompage =='POS' || ($frompage =='Repairs' && !empty($repairs_status) && !in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled')))){
								$qty = $shipping_qty;
								$shipping_qty = 0;
							}
							if($qty<$shipping_qty){
								$qty = $shipping_qty;
							}
							
							$inventoryObj = $this->db->query("SELECT i.inventory_id, i.current_inventory, i.regular_price, i.prices_enabled, p.product_id, p.manage_inventory_count FROM product p, inventory i WHERE p.product_id = $pos_cartrow->item_id AND i.accounts_id = $accounts_id AND i.product_id = p.product_id", array());
							if($inventoryObj){
								$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
								
								
								$newsales_price = $pos_cartrow->sales_price;										
								if($inventoryrow->prices_enabled==1){									
									$old_qty = $pos_cartrow->qty;
									$new_qty = $qty;
									if($frompage=='Repairs' && !empty($repairs_status) && in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled'))){
										$old_qty = $pos_cartrow->shipping_qty;
									}
									
									$getproduct_prices = $this->getproduct_prices($inventoryrow->product_id, $inventoryrow->regular_price, $newsales_price, $old_qty, $new_qty, $customer_id);
									if($getproduct_prices){
										$newsales_price = $getproduct_prices;
									}
								}
								
								if($frompage =='POS'){
									$this->db->update('pos_cart', array('sales_price'=>$newsales_price, 'qty'=>$qty), $pos_cart_id);
								}
								elseif($frompage =='Repairs'){
									if(!empty($repairs_status) && in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled'))){
										$this->db->update('pos_cart', array('sales_price'=>$newsales_price, 'qty'=>$shipping_qty, 'shipping_qty'=>$shipping_qty), $pos_cart_id);
									}
									else{
										$this->db->update('pos_cart', array('sales_price'=>$newsales_price, 'qty'=>$qty, 'shipping_qty'=>0), $pos_cart_id);
									}
								}
								else{
									$this->db->update('pos_cart', array('sales_price'=>$newsales_price, 'qty'=>$qty, 'shipping_qty'=>$shipping_qty), $pos_cart_id);
								}
								if($frompage =='POS' || (!empty($repairs_status) && !in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled')))){}
								else{
									$newcurrent_inventory = floor($inventoryrow->current_inventory-1);
									$this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventoryrow->inventory_id);
								}
								if($frompage =='Repairs'){
									$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE pos_id = $pos_cartrow->pos_id AND accounts_id = $accounts_id", array());
									if($repairsObj){
										$repairs_id = $repairsObj->fetch(PDO::FETCH_OBJ)->repairs_id;
										$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
									}
								}
								$action = 'Add';
							}
						}
					}
				}
			}
		}
		$cartsData = array();
		if($action =='Add' && $pos_id>0){
			$cartsData = $this->loadCartData($frompage, $pos_id);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'pos_id'=>$pos_id, 'cartsData'=>$cartsData));
    
	}
	
	public function getSerialInfo($pos_cart_id=0, $print = ''){
		$returnstr = '';
		if($pos_cart_id>0){
			$serialsql = "SELECT serial_number, returned_pos_cart_id FROM serial_number WHERE pos_cart_id = :pos_cart_id";
			$serialquery = $this->db->query($serialsql, array('pos_cart_id'=>$pos_cart_id),1);
			if($serialquery){
				$serialArray = array();
				while($wonerow= $serialquery->fetch(PDO::FETCH_OBJ)){
					$sale_returnstr = '';
					if($wonerow->returned_pos_cart_id>0){
						$sale_returnstr = ' <span class="padding6 mleft15 bgblack">-1</span>';
					}
					$serialArray[] = "$wonerow->serial_number$sale_returnstr";
				}
				
				if($print==''){
					$returnstr .= implode('<br />', $serialArray);
				}
				else{
					$returnstr = $serialArray;
				}
			}
		}		
		return $returnstr;
	}
	
	public function removeCartsProduct($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = '';
		$pos_id = 0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;			
		$pos_cart_id = intval($POST['pos_cart_id']??0);
		
		if($pos_cart_id>0){
			
			$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
			if($pos_cartObj){
				$pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ);
				
				$pos_id = $pos_cartrow->pos_id;
				$sqlquery = "SELECT invoice_no, pos_type, order_status FROM pos WHERE pos_id = $pos_id";
				$queryObj = $this->db->query($sqlquery, array());
				if($queryObj){
					$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
					$invoice_no = $posRow->invoice_no;
					$pos_type = $posRow->pos_type;
					$order_status = $posRow->order_status;
					if(($invoice_no>0 && $frompage=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
						$action = 'reload';
					}
				}
				
				if(empty($action)){
					$item_id = $pos_cartrow->item_id;
					$shipping_qty = $pos_cartrow->shipping_qty;
					$item_type = $pos_cartrow->item_type;
					
					$changed = array();
					if($shipping_qty >0 || $shipping_qty <0){
						if(in_array($frompage, array('Repairs', 'Orders'))){
							$changed = array($this->db->translate('Remove product')=>$pos_cartrow->description);
						}
						if($item_type=='product' || $item_type=='repairs'){
							$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE product_id = $item_id AND accounts_id = $accounts_id", array());
							if($inventoryObj){
								$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
								
								$current_inventory = $inventoryrow->current_inventory;
								$newcurrent_inventory = floor($current_inventory+$shipping_qty);
								$updateproduct = $this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventoryrow->inventory_id);			
							}
						}
					}
					
					$deletepos_cart = $this->db->delete('pos_cart', 'pos_cart_id', $pos_cart_id);
					if($deletepos_cart){
						if(!empty($changed)){
							$moreInfo = array('table'=>'pos_cart', 'pos_cart_id'=>$pos_cart_id, 'product_id'=>$item_id);
							$teData = array();
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $_SESSION["accounts_id"];
							$teData['user_id'] = $_SESSION["user_id"];
							$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
							$teData['record_id'] = $pos_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);
							
						}
						
						if($item_type=='one_time'){
							$poSql = "SELECT po.po_id, po_items.po_items_id FROM po, po_items WHERE po.accounts_id = $accounts_id AND po_items.item_type = 'one_time' AND po_items.product_id = $pos_cart_id AND po.po_id = po_items.po_id GROUP BY po_items.po_items_id ORDER BY po_items.po_items_id ASC LIMIT 0,1";
							$poData = $this->db->querypagination($poSql, array());
							if($poData !=''){
								foreach($poData as $poRow){
									$po_id = $poRow['po_id'];
									$this->db->delete('po', 'po_id', $po_id);
									
									$po_items_id = $poRow['po_items_id'];
									$this->db->delete('po_items', 'po_items_id', $po_items_id);
								}
							}
						}
						
						if($frompage =='Repairs'){									
							$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
							if($repairsObj){
								$repairs_id = $repairsObj->fetch(PDO::FETCH_OBJ)->repairs_id;
								$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
							}
						}
						
						$action = 'Removed';
					}	
				}
			}
		}
		$cartsData = array();
		if($action =='Removed' && $pos_id>0){
			$cartsData = $this->loadCartData($frompage, $pos_id);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'pos_id'=>$pos_id, 'cartsData'=>$cartsData));
    
	}
	
	public function removeCartsIMEI($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = '';
		$pos_id = 0;		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$pos_cart_id = intval($POST['pos_cart_id']??0);
		$item_id = intval($POST['item_id']??0);
		$pos_cart_item_id = intval($POST['pos_cart_item_id']??0);
		
		if($pos_cart_id>0){
			$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
			if($pos_cartObj){
				$pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ);
				$pos_id = $pos_cartrow->pos_id;
				$customer_id = 0;
				$sqlquery = "SELECT invoice_no, pos_type, order_status, customer_id FROM pos WHERE pos_id = $pos_id";
				$queryObj = $this->db->query($sqlquery, array());
				if($queryObj){
					$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
					$invoice_no = $posRow->invoice_no;
					$customer_id = intval($posRow->customer_id);
					$pos_type = $posRow->pos_type;
					$order_status = $posRow->order_status;
					if(($invoice_no>0 && $frompage=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
						$action = 'reload';
					}
				}
				
				if(empty($action)){
					$qty = $pos_cartrow->qty;
					$shipping_qty = $pos_cartrow->shipping_qty;
					$item_type = $pos_cartrow->item_type;
					$product_id = $pos_cartrow->item_id;
					$deletepos_cart_item = $this->db->delete('pos_cart_item', 'pos_cart_item_id', $pos_cart_item_id);

					$newshipping_qty = floor($shipping_qty-1);
					$pos_cart_itemObj2 = $this->db->query("SELECT count(pos_cart_item_id) AS totalShipQty FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id AND return_pos_cart_id = 0", array());
					if($pos_cart_itemObj2){
						$newshipping_qty = $pos_cart_itemObj2->fetch(PDO::FETCH_OBJ)->totalShipQty;
					}
					if($newshipping_qty <0){$newshipping_qty = 0;}
					
					$inventoryObj = $this->db->query("SELECT * FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
						
						$newsales_price = $pos_cartrow->sales_price;										
						if($inventoryrow->prices_enabled==1){
							$getproduct_prices = $this->getproduct_prices($inventoryrow->product_id, $inventoryrow->regular_price, $newsales_price, $pos_cartrow->shipping_qty, $newshipping_qty, $customer_id);
							if($getproduct_prices){
								$newsales_price = $getproduct_prices;
							}
						}
						if(in_array($frompage, array('POS', 'Repairs'))){
							$pcUpdateData = array('sales_price'=>$newsales_price, 'qty'=>$newshipping_qty, 'shipping_qty'=>$newshipping_qty);
						}
						else{
							$pcUpdateData = array('sales_price'=>$newsales_price, 'shipping_qty'=>$newshipping_qty);
						}
						
						$oneTRowObj = $this->db->querypagination("SELECT * FROM pos_cart WHERE pos_cart_id = $pos_cart_id", array());
						$oneTRowObj1 = $this->db->querypagination("SELECT item_id FROM pos_cart_item WHERE pos_cart_item_id = $pos_cart_item_id", array());
						
						$this->db->update('pos_cart', $pcUpdateData, $pos_cart_id);
						$this->db->update('item', array('in_inventory'=>1, 'is_pos'=>0), $item_id);
						
						
						$item_number = '';
						if($oneTRowObj1){
							$item_id = $oneTRowObj1[0]['item_id'];
							$oneTRowObj2 = $this->db->querypagination("SELECT item_number FROM item WHERE item_id = $item_id", array());
							if($oneTRowObj2){
								$item_number = $oneTRowObj2[0]['item_number'];
							}
						}
						$description =  $this->db->translate('from')." ".$oneTRowObj[0]['description'];
						
						if(in_array($frompage, array('Orders', 'Repairs'))){
							$notes = $this->db->translate('Remove IMEI Number')." $description";
							$notes .= "<br />on $frompage module";
							$note_for = $this->db->checkCharLen('notes.note_for', 'item');
							$noteData=array('table_id'=> $item_id,
											'note_for'=> $note_for,
											'created_on'=> date('Y-m-d H:i:s'),
											'last_updated'=> date('Y-m-d H:i:s'),
											'accounts_id'=> $_SESSION["accounts_id"],
											'user_id'=> $_SESSION["user_id"],
											'note'=> $notes,
											'publics'=>0);
							$notes_id = $this->db->insert('notes', $noteData);
						}

						$changed = array();
						$changed[$this->db->translate('Remove IMEI Number')] = array($item_number, "");
						$changed[$description] = array();
						
						foreach($pcUpdateData as $fieldName=>$fieldValue){
							$prevFieldVal = $oneTRowObj[0][$fieldName];
							if($prevFieldVal != $fieldValue){
								$changed[$fieldName] = array($prevFieldVal, $fieldValue);
							}
						}
						
						if(!empty($changed)){
							$moreInfo = array('table'=>'pos_cart', 'pos_cart_id'=>$pos_cartrow->pos_cart_id, 'product_id'=>$pos_cartrow->item_id);
							$teData = array();
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $_SESSION["accounts_id"];
							$teData['user_id'] = $_SESSION["user_id"];
							$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
							$teData['record_id'] = $pos_cartrow->pos_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);
						}
						$action = 'Removed';
					}
				}
			}
		}
		$cartsData = array();
		if($action =='Removed' && $pos_id>0){
			$cartsData = $this->loadCartData($frompage, $pos_id);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'pos_id'=>$pos_id, 'cartsData'=>$cartsData));
    
	}
	
	public function removeCartsSerial($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $repairs_status = '';
		$pos_id = 0;
		$pos_cart_id = intval($POST['pos_cart_id']??0);		
		$serial_number_id = intval($POST['serial_number_id']??0);
		
		if($pos_cart_id>0 && $serial_number_id>0){
			$sqlquery = "SELECT pos_id FROM pos_cart WHERE pos_cart_id = $pos_cart_id";
			$queryObj = $this->db->query($sqlquery, array());
			if($queryObj){
				$pos_id = $queryObj->fetch(PDO::FETCH_OBJ)->pos_id;
			}
			$customer_id = 0;
			$sqlquery = "SELECT invoice_no, pos_type, order_status, customer_id FROM pos WHERE pos_id = $pos_id";
			$queryObj = $this->db->query($sqlquery, array());
			if($queryObj){
				$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
				$invoice_no = $posRow->invoice_no;
				$customer_id = intval($posRow->customer_id);
				$pos_type = $posRow->pos_type;
				$order_status = $posRow->order_status;
				if(($invoice_no>0 && $frompage=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
					$action = 'reload';
				}

				if($pos_type == 'Repairs'){
					$sqlRepairs = "SELECT status FROM repairs WHERE pos_id = $pos_id";
					$repairsObj = $this->db->query($sqlRepairs, array());
					if($repairsObj){
						$repairs_status = $repairsObj->fetch(PDO::FETCH_OBJ)->status;
					}
				}
			}
			
			if(empty($action)){
				
				$accounts_id = $_SESSION["accounts_id"]??0;
				$serial_number = '';
				$deleteserial = false;
				$oneTRowObj1 = $this->db->querypagination("SELECT serial_number FROM serial_number WHERE serial_number_id = $serial_number_id", array());
				if($oneTRowObj1){
					$serial_number = $oneTRowObj1[0]['serial_number'];
					$deleteserial = $this->db->delete('serial_number', 'serial_number_id', $serial_number_id);
				}
				if($deleteserial){
					$shipping_qty = 0;
					$serialNObj = $this->db->query("SELECT COUNT(serial_number_id) AS counttotalrows FROM serial_number WHERE pos_cart_id = :pos_cart_id and returned_pos_cart_id = 0", array('pos_cart_id'=>$pos_cart_id),1);
					if($serialNObj){
						$shipping_qty = $serialNObj->fetch(PDO::FETCH_OBJ)->counttotalrows;
					}					
					$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
					if($pos_cartObj){
						$pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ);
						
						$inventoryObj = $this->db->query("SELECT i.inventory_id, i.current_inventory, i.regular_price, i.prices_enabled, p.product_id, p.manage_inventory_count FROM product p, inventory i WHERE p.product_id = $pos_cartrow->item_id AND i.accounts_id = $accounts_id AND i.product_id = p.product_id", array());
						if($inventoryObj){
							$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
						
							$newsales_price = $pos_cartrow->sales_price;										
							if($inventoryrow->prices_enabled==1){								
								$getproduct_prices = $this->getproduct_prices($inventoryrow->product_id, $inventoryrow->regular_price, $newsales_price, $pos_cartrow->shipping_qty, $shipping_qty, $customer_id);
								if($getproduct_prices){
									$newsales_price = $getproduct_prices;
								}
							}
							
							$oldQty = $pos_cartrow->qty;
							if($oldQty>$shipping_qty && ($frompage =='POS' || (!empty($repairs_status) && !in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled'))))){$oldQty = $shipping_qty;}
							
							$oneTRowObj = $this->db->querypagination("SELECT * FROM pos_cart WHERE pos_cart_id = $pos_cart_id", array());
						
							if($frompage =='POS' || (!empty($repairs_status) && !in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled')))){
								$pcUpdateData = array('sales_price'=>$newsales_price, 'qty'=>$oldQty);
							}
							else{
								$pcUpdateData = array('sales_price'=>$newsales_price, 'qty'=>$oldQty, 'shipping_qty'=>$shipping_qty);
								
								$newcurrent_inventory = floor($inventoryrow->current_inventory+1);
								$this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventoryrow->inventory_id);
							}
							$this->db->update('pos_cart', $pcUpdateData, $pos_cart_id);
								
							$changed = array();
							$description =  $this->db->translate('from')." ".$oneTRowObj[0]['description'];
							$changed[$this->db->translate('Remove Serial Number')] = array($serial_number, "");
							$changed[$description] = array();
							foreach($pcUpdateData as $fieldName=>$fieldValue){
								$prevFieldVal = $oneTRowObj[0][$fieldName];
								if($prevFieldVal != $fieldValue){
									$changed[$fieldName] = array($prevFieldVal, $fieldValue);
								}
							}
							
							if(!empty($changed)){
								$moreInfo = array('table'=>'pos_cart', 'pos_cart_id'=>$pos_cartrow->pos_cart_id, 'product_id'=>$pos_cartrow->item_id);
								$teData = array();
								$teData['created_on'] = date('Y-m-d H:i:s');
								$teData['accounts_id'] = $_SESSION["accounts_id"];
								$teData['user_id'] = $_SESSION["user_id"];
								$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
								$teData['record_id'] = $pos_cartrow->pos_id;
								$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
								$this->db->insert('track_edits', $teData);
							}
							if($frompage =='Repairs'){
								$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE pos_id = $pos_cartrow->pos_id AND accounts_id = $accounts_id", array());
								if($repairsObj){
									$repairs_id = $repairsObj->fetch(PDO::FETCH_OBJ)->repairs_id;
									$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
								}
							}							
							$action = 'Removed';
						}
					}
				}
			}
		}
		$cartsData = array();
		if($action =='Removed' && $pos_id>0){
			$cartsData = $this->loadCartData($frompage, $pos_id);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'pos_id'=>$pos_id, 'cartsData'=>$cartsData));
    
	}
	
	public function updateCartData($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $message = $editlink = '';
		$pos_id = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$qty = floatval($POST['qty']??0);
		$maxqty = $shipping_qty = floatval($POST['shipping_qty']??0);
		$pos_cart_id = intval($POST['pos_cart_id']??0);
		
		$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
		if($pos_cartObj){
			$pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ);
			$pos_id = $pos_cartrow->pos_id;
			if($pos_cartrow->item_type =='livestocks'){$maxqty = $shipping_qty = $pos_cartrow->shipping_qty;}
			$customer_id = $order_status = 0;
			$sqlquery = "SELECT invoice_no, pos_type, order_status, customer_id FROM pos WHERE pos_id = $pos_id";
			$queryObj = $this->db->query($sqlquery, array());
			if($queryObj){
				$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
				$invoice_no = $posRow->invoice_no;
				$customer_id = intval($posRow->customer_id);
				$pos_type = $posRow->pos_type;
				$order_status = $posRow->order_status;
				if(($invoice_no>0 && $frompage=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
					$action = 'reload';
				}
			}
			
			if(empty($action)){

				$repairs_status = '';
				if($frompage=='Repairs'){
					$oneRRowObj = $this->db->query("SELECT status FROM repairs WHERE pos_id = $pos_id", array());
					if($oneRRowObj){
						$repairs_status = $oneRRowObj->fetch(PDO::FETCH_OBJ)->status;
					}
				}
				
				$productObj = $this->db->query("SELECT * FROM product WHERE product_id = $pos_cartrow->item_id AND accounts_id = $prod_cat_man AND product_publish = 1", array());
				if($productObj){
					$product_row = $productObj->fetch(PDO::FETCH_OBJ);

					$inventoryObj = $this->db->query("SELECT * FROM inventory WHERE product_id = $pos_cartrow->item_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
						
						$product_id = $pos_cartrow->item_id;
						$oldsales_price = $pos_cartrow->sales_price;
						$oldshipping_qty = $pos_cartrow->shipping_qty;
						$additionalshipping_qty = $shipping_qty-$oldshipping_qty;
						
						$description = stripslashes(trim((string) $pos_cartrow->description));
						$allowsale = $taxable = 0;

						$current_inventory = $inventoryrow->current_inventory;
						$manage_inventory_count = $product_row->manage_inventory_count;
						$taxable = $product_row->taxable;
						$allow_backorder = $product_row->allow_backorder;
						
						$overselling = $additionalshipping_qty;
						if($frompage=='POS' && $pos_cartrow->item_type !='livestocks'){
							$overselling = $qty;
						}
						
						if( ($oldshipping_qty==$shipping_qty) || $manage_inventory_count==0 || ($manage_inventory_count==1 && ($current_inventory >= $overselling || $allow_backorder > 0))){
							$allowsale = 1;
						}
						else{
							$editlink = "/Products/view/$product_id";
							if($current_inventory>0){
								$maxqty = floor($current_inventory+$oldshipping_qty);
								$message = "available";
							}
							else{
								$maxqty = 0;
								$message = 'notAvailable';
							}
						}
						
						if($frompage =='Repairs' && $order_status == 1 && $pos_cartrow->item_type !='livestocks'){
							$allowsale = 1;
						}

						if($allowsale==1){

							$add_description = addslashes(trim((string) $POST['add_description']));
							$newsales_price = $sales_price = $POST['sales_price'];
							$discount_is_percent = $POST['discount_is_percent'];
							$discount = round($POST['discount'],2);

							if($inventoryrow->prices_enabled==1){
								$regular_price = round($inventoryrow->regular_price,2);

								if($frompage =='Repairs' && $regular_price == $newsales_price){
									
									if(!empty($repairs_status) && in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled'))){
										$old_qty = $pos_cartrow->shipping_qty;
										$new_qty = $shipping_qty;
									}
									else{
										$old_qty = $pos_cartrow->qty;
										$new_qty = $qty;
									}
									
									$getproduct_prices = $this->getproduct_prices($pos_cartrow->item_id, $regular_price, $newsales_price, $old_qty, $new_qty, $customer_id);
									if($getproduct_prices){
										$newsales_price = $getproduct_prices;
									}
								}
								elseif($regular_price == $newsales_price || $pos_cartrow->qty != $qty){
									$getproduct_prices = $this->getproduct_prices($pos_cartrow->item_id, $regular_price, $newsales_price, $pos_cartrow->qty, $qty, $customer_id);
									if($getproduct_prices){
										$newsales_price = $getproduct_prices;
									}
								}
							}
							
							$pcUpdateData = array('add_description'=>$add_description,
								'sales_price'=>$newsales_price,
								'discount_is_percent'=>$discount_is_percent,
								'discount'=>$discount,
								'qty'=>$qty,
								'shipping_qty'=>$shipping_qty,
								'taxable'=>$taxable
							);

							if(!empty($repairs_status) && !in_array($repairs_status, array('Finished', 'Invoiced', 'Cancelled'))){
								unset($pcUpdateData['shipping_qty']);
								$additionalshipping_qty = 0;
							}
							
							$oneTRowObj = $this->db->querypagination("SELECT * FROM pos_cart WHERE pos_cart_id = $pos_cart_id", array());
							
							$tableUpdate = $this->db->update('pos_cart', $pcUpdateData, $pos_cart_id);
							if($tableUpdate){
								$changed = array();
								foreach($pcUpdateData as $fieldName=>$fieldValue){
									$prevFieldVal = $oneTRowObj[0][$fieldName];
									if($prevFieldVal != $fieldValue){
										if($fieldName=='discount_is_percent'){
											if($prevFieldVal==1){$prevFieldVal = '%';}
											else{$prevFieldVal = $_SESSION["currency"];}
											if($fieldValue==1){$fieldValue = '%';}
											else{$fieldValue = $_SESSION["currency"];}
										}
										$changed[$fieldName] = array($prevFieldVal, $fieldValue);
									}
								}
								
								if(!empty($changed)){
									$moreInfo = array('table'=>'pos_cart', 'id'=>$pos_cart_id, 'product_id'=>$product_id, 'description'=>$description);
									$record_for = 'pos';
									$record_id = $pos_id;
									if($frompage =='Repairs'){
										$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
										if($repairsObj){
											$record_for = 'repairs';
											$record_id = $repairsObj->fetch(PDO::FETCH_OBJ)->repairs_id;
											$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $record_id);
										}
									}
									$teData = array();
									$teData['created_on'] = date('Y-m-d H:i:s');
									$teData['accounts_id'] = $_SESSION["accounts_id"];
									$teData['user_id'] = $_SESSION["user_id"];
									$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', $record_for);
									$teData['record_id'] = $record_id;
									$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
									$this->db->insert('track_edits', $teData);
								}
							}	
							
							if($additionalshipping_qty >0 || $additionalshipping_qty <0){

								$sku = '';
								if($product_row){
									$current_inventory = $inventoryrow->current_inventory;
									$sku = $product_row->sku;
									$newcurrent_inventory = floor($current_inventory-$additionalshipping_qty);
									$updateproduct = $this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventoryrow->inventory_id);
								}
							}

							$action = 'Updated';
						}
					}
				}
			}
		}
		$cartsData = array();
		if($action =='Updated' && $pos_id>0){
			$cartsData = $this->loadCartData($frompage, $pos_id);
		}
		
		return json_encode(array('login'=>'', 'maxqty'=>$maxqty, 'message'=>$message, 'editlink'=>$editlink,'action'=>$action, 'pos_id'=>$pos_id, 'cartsData'=>$cartsData));
    
	}
	
	public function getproduct_prices($product_id, $regular_price, $sales_price, $oldqty=1, $qty=1, $customers_id=0){
		$returnval = false;
		if($product_id>0){
			$todaydate = date('Y-m-d');
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			$strextra = "SELECT * FROM product_prices WHERE accounts_id = $accounts_id AND product_id = $product_id AND ((start_date <= '$todaydate' AND end_date >= '$todaydate') OR start_date IN ('0000-00-00', '1000-01-01'))";
			if($customers_id>0){
				$customer_type = '';
				$customerObj = $this->db->query("SELECT customer_type FROM customers WHERE customers_id = $customers_id", array());
				if($customerObj){
					$customer_type = $customerObj->fetch(PDO::FETCH_OBJ)->customer_type;
				}
				
				if($customer_type !=''){
					$strextra .= " AND ((price_type = 'Customer Type' AND type_match = '$customer_type') OR price_type = 'Sale' OR (price_type = 'Quantity' AND type_match <= $qty))";
				}
				else{
					$strextra .= " AND (price_type = 'Sale' OR (price_type = 'Quantity' AND type_match <= $qty))";
				}
			}
			else{
				$strextra .= " AND (price_type = 'Sale' OR (price_type = 'Quantity' AND type_match <= $qty))";
			}
			$lowestprice = $regular_price;
			$query = $this->db->query($strextra, array());
			if($query){
				while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
					$price_type = $oneRow->price_type;
					$type_match = $oneRow->type_match;
					$is_percent = $oneRow->is_percent;
					$price = $oneRow->price;
					
					if($price_type == 'Quantity' && $oldqty===$qty && $sales_price !=$regular_price){
						$lowestprice = $sales_price;
					}
					else{
						if($is_percent>0 && $price>0){
							$discountprice = round($regular_price*$price*0.01,2);
							$newsaleprice = $regular_price-$discountprice;
						}
						else{
							$newsaleprice = $price;
						}
						if($lowestprice==0 || $lowestprice==false){
							$lowestprice = $newsaleprice;
						}
						elseif($lowestprice>$newsaleprice){
							$lowestprice = $newsaleprice;
						}						
					}
				}
			}
			$returnval = $lowestprice;
			//$returnval = $strextra;//
		}
		return $returnval;
	}
	
	public function cprints(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$language = $_SESSION["language"]??'English';
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

		$htmlStr = "";
		$posSql = "SELECT pos_id FROM pos WHERE ";
		if($accounts_id>0){
			$posSql .= "accounts_id = $accounts_id AND ";
		}
		$posSql .= "invoice_no = :invoice_no";
		$posObj = $this->db->query($posSql, array('invoice_no'=>$GLOBALS['segment5name']),1);
		if($posObj){
			$htmlStr .= '<!DOCTYPE html>
			<html>
			<head>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
				<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
				<script language="JavaScript" type="text/javascript">var currency = \''.$currency.'\';var calenderDate = \''.$calenderDate.'\';var timeformat = \''.$timeformat.'\';var loadLangFile = \''.$loadLangFile.'\';
				var langModifiedData = {};
				var OS;
			var segment1 = \'Home\';
			var segment2 = \'\';
			var segment3 = \'\';
			var segment4 =  \'\';
			var pathArray = window.location.pathname.split(\'/\');
			if(pathArray.length>1){
				segment1 = pathArray[1];
				if(pathArray.length>2){
					segment2 = pathArray[2];
					if(pathArray.length>3){
						segment3 = pathArray[3];
						if(pathArray.length>4){segment4 = pathArray[4];}
					}
				}
			}
			'."
			function stripslashes(text) {
				text = text.replace(/\\\'/g, '\'');
				text = text.replace(/\\\\\"/g, '\"');
				text = text.replace(/\\\\0/g, '\\0');
				text = text.replace(/\\\\\\\\/g, '\\\\');
				return text;
			}
			".'</script>
				<script src="/assets/js-'.swVersion.'/languages/'.$language.'.js"></script>';

				if(isset($_SESSION) && array_key_exists('languageJSVar', $_SESSION)){
					$languageJSVar = $_SESSION["languageJSVar"];
					if(!empty($languageJSVar)){
						$htmlStr .= "<script language=\"JavaScript\" type=\"text/javascript\">
						langModifiedData = {";
						foreach($languageJSVar as $varName=>$varValue){
							$htmlStr .= '
				\''.trim((string) $varName).'\':stripslashes(\''.addslashes($varValue).'\'),';
						}
						$htmlStr .= '}
						</script>';
					}
				}
				
			$htmlStr .= '</head>
			<body>
				<div id="viewPageInfo"></div>
				<script type="module" src="/assets/js-'.swVersion.'/'.printsJS.'"></script>
			</body>
			</html>';
		}
		return $htmlStr;
	}
	
	public function AJ_cprints_small_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$invoice_no = intval($POST['invoice_no']??0);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$posObj = $this->db->query("SELECT pos_id FROM pos WHERE accounts_id = $accounts_id AND invoice_no = :invoice_no", array('invoice_no'=>$invoice_no), 1);
		if($posObj){
			$pos_id = $posObj->fetch(PDO::FETCH_OBJ)->pos_id;
			$Printing = new Printing($this->db);
			$jsonResponse = $Printing->invoicesInfo($pos_id, 'small', 'Invoices');
		
			return json_encode($jsonResponse);
		}
	}
	
	public function AJ_cprints_large_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$invoice_no = intval($POST['invoice_no']??0);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$posObj = $this->db->query("SELECT pos_id FROM pos WHERE accounts_id = $accounts_id AND invoice_no = :invoice_no", array('invoice_no'=>$invoice_no), 1);
		if($posObj){
			$pos_id = $posObj->fetch(PDO::FETCH_OBJ)->pos_id;
			$Printing = new Printing($this->db);
			$jsonResponse = $Printing->invoicesInfo($pos_id, 'large', 'Invoices');
		
			return json_encode($jsonResponse);
		}
	}
	
	public function AJ_sendposmail(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		if(func_num_args()>=3){
			$arg_list = func_get_args();
			$pos_id = $arg_list[0];
			$email_address = $arg_list[1];
			$amount_due = $arg_list[2];
			$noteCrOrNot = $arg_list[3]??0;
		}
		else{
			$pos_id = intval($POST['pos_id']??0);
			$email_address = $POST['email_address']??'';
			$amount_due = floatval($POST['amount_due']??0);
			$noteCrOrNot = intval($POST['noteCrOrNot']??0);
		}
		
		if(empty($email_address) || $pos_id==0){
			$returnStr = 'notSendMail';
		}
		else{
			
			$accounts_id = $_SESSION["accounts_id"]??0;
					
			$Printing = new Printing($this->db);
			$mail_body = $Printing->invoicesInfo($pos_id, 'large', $amount_due, 'Invoices', 1);
			$invoice_no = 0;
			$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id AND pos_id = :pos_id", array('pos_id'=>$pos_id),1);
			if($posObj){
				$invoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
			}
			
			$customer_service_email = '';
			$accObj = $this->db->query("SELECT customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accObj){
				$customer_service_email = $accObj->fetch(PDO::FETCH_OBJ)->customer_service_email;
			}
			if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
					
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $this->db->supportEmail('Host');
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = $this->db->supportEmail('Username');
			$mail->Password = $this->db->supportEmail('Password');
			
			$mail->addReplyTo($customer_service_email, $_SESSION["company_name"]);
			$subdomain = $GLOBALS['subdomain'];
			$mail->setFrom($this->db->supportEmail('do_not_reply'), $_SESSION["company_name"]);
			$mail->clearAddresses();
			$mail->addAddress($email_address, "");
			$mail->Subject = $this->db->translate('Sales Invoice # s').$invoice_no;
			$mail->CharSet = 'UTF-8';
			$mail->isHTML(true);
			$mail->Body = $mail_body;
		
			if($mail->send()){
				if($noteCrOrNot==1){
					$note_for = $this->db->checkCharLen('notes.note_for', 'pos');
					$noteData = array();
					$noteData['table_id'] = $pos_id;
					$noteData['note_for'] = $note_for;
					$noteData['last_updated'] = date('Y-m-d H:i:s');
					$noteData['accounts_id'] = $accounts_id;			
					$noteData['user_id'] = $_SESSION["user_id"];
					$noteData['note'] = $email_address;
					$noteData['publics'] = 0;
					$noteData['created_on'] = date('Y-m-d H:i:s');
					$notes_id = $this->db->insert('notes', $noteData);
				}
				$returnStr = 'Ok';
			}
			else{
				$returnStr = 'notSendMail';
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	//============One Time Product===========//
	public function AJget_oneTimePopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$oneTPData = array();
		$oneTPData['login'] = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$pos_cart_id = intval($POST['pos_cart_id']??0);
		
		$oneTPData['pos_cart_id'] = $pos_cart_id;
		$oneTPData['item_type'] = 'one_time';
		$oneTPData['description'] = '';
		$oneTPData['add_description'] = '';
		$oneTPData['sales_price'] = 0.00;
		$oneTPData['ave_cost'] = 0.00;
		$oneTPData['qty'] = 0;
		$oneTPData['shipping_qty'] = 0;
		$oneTPData['discount_is_percent'] = 1;
		$oneTPData['discount'] = 0.00;
		$oneTPData['received'] = 0;
		$oneTPData['taxable'] = 1;
		$suppliers_id = 0;
		if($pos_cart_id>0){
			$onePCObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
			if($onePCObj){
				$onePCData = $onePCObj->fetch(PDO::FETCH_OBJ);
				
				$oneTPData['description'] = $onePCData->description;
				$oneTPData['add_description'] = $onePCData->add_description;
				$oneTPData['sales_price'] = round($onePCData->sales_price,2);
				$oneTPData['ave_cost'] = round($onePCData->ave_cost,2);
				$oneTPData['qty'] = round($onePCData->qty,2);
				
				if($onePCData->shipping_qty>0){$oneTPData['received'] = 1;}
				
				$oneTPData['discount_is_percent'] = intval($onePCData->discount_is_percent);
				$oneTPData['discount'] = round($onePCData->discount,3);
				$oneTPData['taxable'] = intval($onePCData->taxable);
				
				$poSql = "SELECT po.supplier_id FROM po, po_items WHERE po.accounts_id = $accounts_id AND po_items.product_id = :pos_cart_id AND po_items.item_type = 'one_time' AND po.po_id = po_items.po_id GROUP BY po_items.po_items_id ORDER BY po_items.po_items_id ASC";
				$poData = $this->db->querypagination($poSql, array('pos_cart_id'=>$pos_cart_id),1);
				if($poData){
					foreach($poData as $poRow){
						$suppliers_id = $poRow['supplier_id'];
					}
				}
			}
		}
		
		$supplierOptions = array();
		$supplierssql = "SELECT company, email, suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company !='' AND (suppliers_publish = 1 OR (suppliers_id = :suppliers_id AND suppliers_publish = 0)) ORDER BY company ASC, email ASC";
		$suppliersquery = $this->db->query($supplierssql, array('suppliers_id'=>$suppliers_id));
		if($suppliersquery){
			while($onerow = $suppliersquery->fetch(PDO::FETCH_OBJ)){
				$opsuppliers_id = $onerow->suppliers_id;
				$optLabel = stripslashes($onerow->company);				
				if($onerow->email !='')
					$optLabel .= " ($onerow->email)";
				$supplierOptions[$opsuppliers_id] = $optLabel;
			}
		}
		$oneTPData['suppliers_id'] = intval($suppliers_id);
		$oneTPData['supplierOptions'] = $supplierOptions;
		return json_encode($oneTPData);
	}
	
	public function AJsave_oneTime($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$action = '';
		$pos_id = intval($POST['pos_id']??0);
		$pos_cart_id = intval($POST['pos_cart_idvalue']??0);
		$repairs_status = $POST['repairs_status']??'';
		$description = $POST['description']??'';
		$add_description = $POST['add_description']??'';
		$sales_price = floatval($POST['sales_price']??0);
		$qty = floatval($POST['qty']??0);
		$discount = floatval($POST['discount']??0);
		$discount_is_percent = intval($POST['discount_is_percent']??0);
		$ave_cost = floatval($POST['cost']??0);
		$suppliers_id = intval($POST['suppliers_id']??0);
		$taxable = intval($POST['taxable']??0);
		$received = floatval($POST['received']??0);
		$shipping_qty = 0;
		if($received>0){$shipping_qty = $qty;}
		$item_type = $this->db->checkCharLen('pos_cart.item_type', 'one_time');
		$description = $this->db->checkCharLen('pos_cart.description', $description);
										
		$pcInfo = array('pos_id'=>$pos_id,
						'item_id'=>0,
						'item_type'=>$item_type,
						'description'=>$description,
						'add_description'=>$add_description,
						'require_serial_no'=>0,
						'sales_price'=>round($sales_price,2),
						'ave_cost'=>round($ave_cost,2),
						'qty'=>$qty,
						'shipping_qty'=>$shipping_qty,
						'return_qty'=>0,
						'discount_is_percent'=>$discount_is_percent,
						'discount'=>round($discount,2),
						'taxable'=>$taxable);
		
		if($pos_id>0 && $description !=''){
			
			if($pos_cart_id == 0){
				$checkSql = "SELECT pos_cart_id FROM pos_cart WHERE pos_id = $pos_id AND item_type ='one_time' AND item_id = 0 AND description = '".addslashes($description)."' ORDER BY pos_id ASC LIMIT 0,1";
				$checkPCData = $this->db->querypagination($checkSql, array());
				if(!$checkPCData){
					$pos_cart_id = $this->db->insert('pos_cart', $pcInfo);
					if($pos_cart_id){						
						$action = 'Add';
					}
				}
			}
			else{
				$checkSql = "SELECT pos_cart_id FROM pos_cart WHERE pos_id = $pos_id AND item_type ='one_time' AND item_id = 0 AND description = '".addslashes($description)."' AND pos_cart_id != :pos_cart_id ORDER BY pos_id ASC LIMIT 0,1";
				$checkPCData = $this->db->querypagination($checkSql, array('pos_cart_id'=>$pos_cart_id),1);
				if(!$checkPCData){
					$this->db->update('pos_cart', $pcInfo, $pos_cart_id);
					$action = 'Update';
				}
			}
			
			if(in_array($action, array('Add', 'Update')) && $pos_cart_id >0){
				$po_id = $po_items_id = 0;
				$poSql = "SELECT po.po_id, po_items.po_items_id FROM po, po_items WHERE po.accounts_id = $accounts_id AND po_items.item_type = 'one_time' AND po_items.product_id = $pos_cart_id AND po.po_id = po_items.po_id GROUP BY po_items.po_items_id ORDER BY po_items.po_items_id ASC LIMIT 0,1";
				$poData = $this->db->querypagination($poSql, array());
				if($poData !=''){
					foreach($poData as $poRow){
						$po_id = $poRow['po_id'];
						$po_items_id = $poRow['po_items_id'];
					}
				}
				
				if($suppliers_id>0){
					$poData = array();
					$poData['supplier_id'] = intval($suppliers_id);
					$poData['user_id'] = $_SESSION["user_id"];
					
					if($po_id==0){
						
						$poData['po_datetime'] = date('Y-m-d H:i:s');
						$poData['last_updated'] = date('Y-m-d H:i:s');
						$po_number = 1;
						$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
						if($poObj){
							$po_number = $poObj[0]['po_number']+1;
						}
						$poData['po_number'] = $po_number;
						$lot_ref_no = $this->db->checkCharLen('po.lot_ref_no', '');
						$poData['lot_ref_no'] = $lot_ref_no;
						$poData['paid_by'] = '';
						$poData['date_expected'] = date('Y-m-d');
						$poData['return_po'] = 0;
						$status = $this->db->checkCharLen('po.status', 'Closed');
						$poData['status'] = $status;
						$poData['accounts_id'] = $accounts_id;
						$poData['tax_is_percent'] = 0;
						$poData['taxes'] = 0.000;
						$poData['shipping'] = 0.00;
						$suppliers_invoice_no = $this->db->checkCharLen('po.suppliers_invoice_no', '');
						$poData['suppliers_invoice_no'] = $suppliers_invoice_no;
						$poData['invoice_date'] = date('Y-m-d');
						$poData['date_paid'] = date('Y-m-d');
						$poData['transfer'] = 0;
						$po_id = $this->db->insert('po', $poData); 
					}
					else{
						$this->db->update('po', $poData, $po_id);
					}
					$item_type = $this->db->checkCharLen('po_items.item_type', 'one_time');
					$poiData = array('created_on'=>date('Y-m-d H:i:s'),
										'user_id'=>$_SESSION["user_id"],
										'po_id'=>$po_id,
										'product_id'=>$pos_cart_id,
										'item_type'=>$item_type,
										'cost'=>round($ave_cost,2),
										'ordered_qty'=>$qty,
										'received_qty'=>$shipping_qty);
					if($po_items_id==0){
						$po_items_id = $this->db->insert('po_items', $poiData); 
					}
					else{
						$this->db->update('po_items', $poiData, $po_items_id);							
					}
				}
				else{
					if($po_id>0){
						$this->db->delete('po', 'po_id', $po_id);
					}
					if($po_items_id>0){
						$this->db->delete('po_items', 'po_items_id', $po_items_id);
					}
				}
			}
		}
		
		$cartsData = array();
		if(($action =='Add' || $action =='Update') && $pos_id>0){
			$cartsData = $this->loadCartData($frompage, $pos_id);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'pos_id'=>$pos_id, 'cartsData'=>$cartsData));
	}
	
}
?>