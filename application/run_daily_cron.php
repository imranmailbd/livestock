<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$serverPath = getcwd();
if($serverPath=='/home/machouse'){
	define('OUR_DOMAINNAME', 'machouse.com.bd');
	define('COMPANYNAME', 'Dazzle PVT. Ltd.');
}
else{
	define('OUR_DOMAINNAME', 'machousel.com.bd');
	define('COMPANYNAME', 'SK POS ERP');
}
$cronstarttime = time();

spl_autoload_register(function ($class_name) {
	$class_name = str_replace('PHPMailer/PHPMailer/', 'PHPMailer/', str_replace('\\', '/', $class_name));
	$fullPath = "application/$class_name.php";
	
	if(!file_exists($fullPath)){
		return false;
	}
	require_once ($fullPath);
});

$message = '';
$db = new Db();
$l=0;
$todaydate = date('Y-m-d');

//==========Drip===========//
$Drip = new Drip($db);
//$Drip->runDrip();
$Drip->inventoryValueInsert();exit;

	//======================Delete POS data which invoice_no = 0 before 30 days=================//
	$removemessage = '';
	$before30days = date('Y-m-d H:i:s', strtotime('-30 day', time()));
	$posSql = "SELECT pos_id, accounts_id, sales_datetime FROM pos WHERE invoice_no = 0 AND sales_datetime < '$before30days' AND pos_type = 'Sale' ORDER BY accounts_id ASC, pos_id ASC";
	$posObj = $db->query($posSql, array());
	if($posObj){
		$remove0InvoiceNoData = array();
		while($oneRow = $posObj->fetch(PDO::FETCH_OBJ)){
			$accounts_id = $oneRow->accounts_id;
			$pos_id = $oneRow->pos_id;
			$remove0InvoiceNoData[$accounts_id][] = array($pos_id, $oneRow->sales_datetime);
		}

		if(!empty($remove0InvoiceNoData)){
			
			$removemessage .= '<br /><br />//====================Remove POS data which invoice_no = 0 before 30 days ('.$before30days.')===================//';
			foreach($remove0InvoiceNoData as $accounts_id=>$posIds){
				$removemessage .= '<br />//-----------------AccountID: '.$accounts_id.'---------------------//';
				foreach($posIds as $posRow){
					$pos_id = $posRow[0];
					$sales_datetime = $posRow[1];
					$db->delete('pos', 'pos_id', $pos_id);
					$removemessage .= '<br />&emsp; POS ID: '.$pos_id.', Sales Date: '.$sales_datetime;

					$pos_cartObj = $db->query("SELECT pos_id, pos_cart_id, item_type FROM pos_cart WHERE pos_id = $pos_id ORDER BY pos_cart_id ASC", array());
					if($pos_cartObj){
						while($pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
							$pos_cart_id = $pos_cartrow->pos_cart_id;
							$item_type = $pos_cartrow->item_type;
							$db->delete('pos_cart', 'pos_cart_id', $pos_cart_id);		
							$removemessage .= '<br />&emsp; &emsp; POS Cart ID: '.$pos_cart_id;

							if($item_type=='cellphones'){
								$pciObj = $db->query("SELECT pos_cart_item_id FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id", array());
								if($pciObj){
									while($pciRow = $pciObj->fetch(PDO::FETCH_OBJ)){
										$db->delete('pos_cart_item', 'pos_cart_item_id', $pciRow->pos_cart_item_id);
										$removemessage .= '<br />&emsp; &emsp; &emsp; POSCartItem ID: '.$pciRow->pos_cart_item_id;
									}
								}
							}
						}
					}
				}	
			}				
		}
	}
	
	$message .= $removemessage;

/*
$sql = "SELECT accounts_id, company_subdomain, pay_frequency, price_per_location, next_payment_due FROM accounts WHERE next_payment_due NOT IN ('0000-00-00', '1000-01-01') AND next_payment_due <= '$todaydate' AND location_of = 0 ORDER BY accounts_id ASC";
$queryObj = $db->query($sql, array());
if($queryObj){
	while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
		$l++;
		$ourInvoicesData = array();
		$ourInvoicesData['accounts_id'] = $accounts_id = $oneRow->accounts_id;
		$company_subdomain = $oneRow->company_subdomain;
		
		$invoice_number = 1000;
		$our_invoicesObj = $db->querypagination("SELECT invoice_number FROM our_invoices ORDER BY invoice_number DESC LIMIT 0, 1", array());
		if($our_invoicesObj){
			$invoice_number = $our_invoicesObj[0]['invoice_number']+1;
		}
		$ourInvoicesData['invoice_number'] = $invoice_number;
		$ourInvoicesData['invoice_date'] = $todaydate;
		$description = "Unlimited Plan $company_subdomain";
		$num_locations = 1;
		$locationObj = $db->query("SELECT company_subdomain FROM accounts WHERE location_of = $accounts_id AND status != 'SUSPENDED'", array());
		if($locationObj){
			while($oneLocationRow = $locationObj->fetch(PDO::FETCH_OBJ)){
				$num_locations++;
				$description .= ", $oneLocationRow->company_subdomain";
			}
		}
		$ourInvoicesData['num_locations'] = $num_locations;
		$ourInvoicesData['description'] = $description;
		$ourInvoicesData['pay_frequency'] = 'One Time';
		$ourInvoicesData['price_per_location'] = $price_per_location = $oneRow->price_per_location;
		if($price_per_location==0){
			$ourInvoicesData['paid_on']=date('Y-m-d');
		}
		$db->insert('our_invoices', $ourInvoicesData);
		
		$pay_frequency = $oneRow->pay_frequency;
		
		$next_payment_due = date('Y-m-d', strtotime("+1 months", strtotime($todaydate)));
		if($pay_frequency == 'Quarterly'){
			$next_payment_due = date('Y-m-d', strtotime("+3 months", strtotime($todaydate)));
		}
		elseif($pay_frequency == 'Yearly'){
			$next_payment_due = date('Y-m-d', strtotime("+12 months", strtotime($todaydate)));
		}
		
		$db->update('accounts', array('next_payment_due'=>$next_payment_due), $accounts_id);
		
	}
}
//For testing 
echo '<br />'.$l.' invoices has been successfully created.';

$l=0;

echo '<br />'.$l.' invoices has been successfully paid / dues.';

$l=0;
$before3days = date('Y-m-d', strtotime('-3 day', time()));
//======================For Check accounts with status "Payment Due" and current date > status date + 3 days==========================//
$sql = "SELECT accounts_id FROM accounts WHERE status_date <= '$before3days' AND status ='Payment Due' ORDER BY accounts_id ASC";
$query = $db->query($sql, array());
if($query){
	
	while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
		$l++;
		$db->update('accounts', array('status'=>'SUSPENDED', 'status_date'=>date('Y-m-d H:i:s')), $oneRow->accounts_id);
	}		
}

echo '<br />'.$l.' accounts has been successfully SUSPENDED.';
*/

//====================Check if account's product has no inventory=====================// 990, 3419
//SELECT p.product_id, a.accounts_id, a.location_of FROM accounts a, product p WHERE a.location_of =0 AND a.accounts_id = p.accounts_id AND NOT EXISTS (SELECT inventory.inventory_id FROM inventory WHERE inventory.product_id = p.product_id AND inventory.accounts_id = a.accounts_id) ORDER BY p.product_id ASC, a.accounts_id ASC, a.location_of ASC 
//SELECT p.product_id, a.accounts_id, a.location_of FROM accounts a, product p WHERE a.location_of >0 AND a.location_of = p.accounts_id AND NOT EXISTS (SELECT inventory.inventory_id FROM inventory WHERE inventory.product_id = p.product_id AND inventory.accounts_id = a.accounts_id) ORDER BY p.product_id ASC, a.accounts_id ASC, a.location_of ASC 

$totalMissingInventory = 0;
$sql = "SELECT COUNT(p.product_id) AS missingInvCount FROM accounts a, product p WHERE a.location_of = 0 AND a.accounts_id = p.accounts_id AND NOT EXISTS (SELECT inventory.inventory_id FROM inventory WHERE inventory.product_id = p.product_id AND inventory.accounts_id = a.accounts_id)";
$productObj = $db->querypagination($sql, array());
if($productObj){
	foreach($productObj as $oneRow){
		$totalMissingInventory += $oneRow['missingInvCount'];
	}
}

$sql = "SELECT COUNT(p.product_id) AS missingInvCount FROM accounts a, product p WHERE a.location_of >0 AND a.location_of = p.accounts_id AND NOT EXISTS (SELECT inventory.inventory_id FROM inventory WHERE inventory.product_id = p.product_id AND inventory.accounts_id = a.accounts_id)";
//$sql = "SELECT p.product_id, a.accounts_id, a.location_of FROM accounts a, product p WHERE a.location_of >0 AND a.location_of = p.accounts_id AND NOT EXISTS (SELECT inventory.inventory_id FROM inventory WHERE inventory.product_id = p.product_id AND inventory.accounts_id = a.accounts_id)";
//SELECT p.product_id, a.accounts_id, a.location_of FROM accounts a, product p WHERE a.accounts_id = p.accounts_id AND NOT EXISTS (SELECT inventory.inventory_id FROM inventory WHERE inventory.product_id = p.product_id AND inventory.accounts_id = a.accounts_id) 
$productObj = $db->querypagination($sql, array());
if($productObj){
	foreach($productObj as $oneRow){
		$totalMissingInventory += $oneRow['missingInvCount'];
	}
}
if($totalMissingInventory>0){
	$message .= '<br />Missing Product ID into Inventory table: '.$totalMissingInventory;
}

//====================Check if account's product has more than 1 inventory=====================//
$totalDuplicateInventory = 0;
//SELECT inventory.product_id, inventory.accounts_id FROM product, inventory WHERE product.product_id = inventory.product_id GROUP BY inventory.accounts_id, inventory.product_id HAVING COUNT(inventory_id) !=1
//SELECT * FROM `inventory` WHERE `product_id` = 877398 AND `accounts_id` = 4149

$sql = "SELECT inventory.product_id FROM product, inventory WHERE product.product_id = inventory.product_id GROUP BY inventory.accounts_id, inventory.product_id HAVING COUNT(inventory_id) !=1";
$productObj = $db->querypagination($sql, array());
if($productObj){
	$totalDuplicateInventory = count($productObj);
}
if($totalDuplicateInventory>0){
	$message .= '<br />Duplicate Product AND Accounts ID into Inventory table: '.$totalDuplicateInventory;
}

//========================Check if Invnetory count and QTY PO, POS and Adjustment is not same =========================//
$created_since = '2017-01-01';
$weekDay = date('w');
$weekDaysAcc = array('1'=>"a.accounts_id LIKE '%1'", 
					'2'=>"a.accounts_id LIKE '%2'", 
					'3'=>"(a.accounts_id LIKE '%3' OR a.accounts_id LIKE '%4')", 
					'4'=>"(a.accounts_id LIKE '%5' OR a.accounts_id LIKE '%6')", 
					'5'=>"(a.accounts_id LIKE '%7' OR a.accounts_id LIKE '%8')", 
					'6'=>"(a.accounts_id LIKE '%9' OR a.accounts_id LIKE '%0')");
/*
if(array_key_exists($weekDay, $weekDaysAcc)){
	$dayStr = ' AND '.$weekDaysAcc[$weekDay];
	$sql = "SELECT i.inventory_id, i.accounts_id, i.product_id, i.current_inventory FROM inventory i, product p, accounts a WHERE p.product_type = 'Standard' AND SUBSTR(p.created_on,1,10)>='$created_since'$dayStr AND i.product_id = p.product_id AND i.accounts_id = a.accounts_id AND a.status = 'Active' ORDER BY i.accounts_id ASC, i.inventory_id DESC";	
	//$message .= "\n$sql";
	$inventoryObj = $db->query($sql, array());
	if($inventoryObj){
		while($oneInventoryRow = $inventoryObj->fetch(PDO::FETCH_OBJ)){
			$inventory_id = $oneInventoryRow->inventory_id;
			$accounts_id = $oneInventoryRow->accounts_id;
			$product_id = $oneInventoryRow->product_id;
			$current_inventory = $oneInventoryRow->current_inventory;
			
			//===============Calculate Purchase Order Stock====================//
			$poQTY = 0;
			$poSql = "SELECT SUM(CASE WHEN po.transfer=1 AND po.status='Open' THEN -po_items.received_qty ELSE po_items.received_qty END) AS poQTY FROM po, po_items WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.po_id = po_items.po_id AND (po.transfer IN ('0', '1') OR (po.transfer=2 AND po.status='Closed')) GROUP BY po.accounts_id, po_items.product_id";
			$poObj = $db->querypagination($poSql, array());
			if($poObj){
				foreach($poObj as $oneRow){
					$poQTY += $oneRow['poQTY'];
				}
			}
			
			//===============Calculate POS Stock====================//
			$posQTY = 0;
			$posSql = "SELECT SUM(pos_cart.shipping_qty) AS posQTY FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos_cart.item_id = $product_id AND pos.pos_id = pos_cart.pos_id GROUP BY pos.accounts_id, pos_cart.item_id";
			$posObj = $db->querypagination($posSql, array());
			if($posObj){
				foreach($posObj as $oneRow){
					$posQTY += $oneRow['posQTY'];
				}
			}
			
			//===============Calculate Stock Adjustment====================//
			$adjustmentQTY = 0;
			$noteSql = "SELECT note FROM notes WHERE accounts_id = $accounts_id AND table_id = $product_id AND note_for = 'product' AND note LIKE '% inventory has been adjusted %' ORDER BY notes_id ASC";
			$noteObj = $db->query($noteSql, array());
			if($noteObj){
				while($oneRow = $noteObj->fetch(PDO::FETCH_OBJ)){
					$noteArray = explode(' inventory has been adjusted ', $oneRow->note);
					$note = intval($noteArray[0]);
					$adjustmentQTY += $note;
				}
			}
			
			$compareStock = $poQTY-$posQTY+$adjustmentQTY+$activityAdjustmentQTY;
			if($current_inventory != $compareStock){
				if($segment3name=='correct'){
					$update = $db->update('inventory', array('current_inventory'=>$compareStock), $inventory_id);
					if($update){
						$message .= "<p>Account ID: $accounts_id, Product ID: $product_id, PO: $poQTY - POS: $posQTY + Note: $adjustmentQTY + Activity: $activityAdjustmentQTY = $compareStock (corrected), Inventory: $current_inventory</p>";
					}
				}
				else{					
					$iSecCkSql = "SELECT current_inventory FROM inventory WHERE inventory_id = $inventory_id";
					$iSecCkObj = $db->querypagination($iSecCkSql, array());
					if($iSecCkObj){
						foreach($iSecCkObj as $iSecCkRow){
							$newcurrent_inventory = $iSecCkRow['current_inventory'];
							if($newcurrent_inventory != $compareStock){
								$message .= "<p>Account ID: $accounts_id, Product ID: $product_id, PO: $poQTY - POS: $posQTY + Note: $adjustmentQTY + Activity: $activityAdjustmentQTY = $compareStock, Inventory: $newcurrent_inventory</p>";
							}
						}
					}
				}
			}
			
		}
	}
}
*/

if(date('w')=='0'){
	
	$daysBack14 = date('Y-m-d', strtotime('-14 day', time())).' 00:00:00';
	$sqlActivity = "DELETE FROM activity_feed WHERE activity_feed_title = 'Module' AND created_on < '$daysBack14'";
	$queryActivity = $db->query($sqlActivity, array());
	if($queryActivity){
		$message .= '<br />'.$queryActivity->rowCount().' module activity before 14 days has been removed.';
	}
	
	//======================For Check product_prices table end_date value==========================//	
	$l = 0;
	$sql = "SELECT inventory_id, product_id, accounts_id FROM inventory WHERE prices_enabled = 1 ORDER BY inventory_id ASC";
	$query = $db->query($sql, array());
	if($query){
		while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
			$inventory_id = $oneRow->inventory_id;
			$product_id = $oneRow->product_id;
			$accounts_id = $oneRow->accounts_id;
			
			$countTableData = 0;
			$product_pricesObj = $db->query("SELECT COUNT(product_prices_id) AS totalrows FROM product_prices WHERE accounts_id = $accounts_id AND product_id = $product_id AND start_date NOT IN ('0000-00-00', '1000-01-01') AND start_date <= '$todaydate' AND end_date >= '$todaydate'", array());
			if($product_pricesObj){
				$countTableData = $product_pricesObj->fetch(PDO::FETCH_OBJ)->totalrows;						
			}
			
			if($countTableData==0){
				$l++;				
				$db->update('inventory', array('prices_enabled'=>0), $inventory_id);
			}
		}		
	}
	 
	if($l>0){
		$message .= '<br />'.$l.' Product\'s prices enabled has been successfully disabled.';
	}
	
	//===============For Cancel Repair Delivery Qty data count and printing====================//
	$countData= 0;
	$posSql = "SELECT repairs.accounts_id, repairs.repairs_id, pos_cart.item_id, SUM(pos_cart.shipping_qty) AS posQTY FROM repairs, pos_cart WHERE repairs.status = 'Cancelled' AND pos_cart.shipping_qty>0 AND repairs.pos_id = pos_cart.pos_id GROUP BY repairs.accounts_id, repairs.repairs_id ORDER BY repairs.accounts_id ASC, repairs.repairs_id ASC";
	$posObj = $db->querypagination($posSql, array());
	if($posObj){
		foreach($posObj as $oneRow){
			$countData++;
			$message .= "<p>Account ID: $oneRow[accounts_id], Repair ID: $oneRow[repairs_id], Product ID: $oneRow[item_id], Ship Qty: $oneRow[posQTY]</p>";
		}
	}
	
	if($countData>0){
		//===============Update Cancel Repair => POS Cart Shipping Qty = 0====================//
		$posSql = "SELECT pos_cart.pos_cart_id FROM repairs, pos_cart WHERE repairs.status = 'Cancelled' AND pos_cart.shipping_qty>0 AND repairs.pos_id = pos_cart.pos_id ORDER BY repairs.accounts_id ASC, repairs.repairs_id ASC";
		$posObj = $db->querypagination($posSql, array());
		if($posObj){
			foreach($posObj as $oneRow){				
				$db->update('pos_cart', array('shipping_qty'=>0), $oneRow['pos_cart_id']);
			}
		}
	}
	
	$varObj = $db->query("SELECT accounts_id, value FROM variables WHERE name = 'eu_gdpr' AND value !=''", array());
	if($varObj){
		while($oneRow = $varObj->fetch(PDO::FETCH_OBJ)){
			$accounts_id = $oneRow->accounts_id;
			$value = $oneRow->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('eu_gdprMonth', $value)){					
					$eu_gdprMonth = intval($value['eu_gdprMonth']);
					$totalDays = $eu_gdprMonth*30;
					$daysback = date('Y-m-d 00:00:00', strtotime("-$totalDays day", time()));
					
					//================Customer ID List From Customer Table=======================//
					$customerIds = array();
					$customersSql = "SELECT customers_id FROM customers WHERE accounts_id = $accounts_id AND last_updated < '$daysback' AND customers_publish = 1 ORDER BY customers_id ASC";
					$customersObj = $db->query($customersSql, array());
					if($customersObj){
						while($oneCustRow = $customersObj->fetch(PDO::FETCH_OBJ)){
							$customerIds[$oneCustRow->customers_id] = '';
						}
					}
					
					if(!empty($customerIds)){
						//================Remove Customer ID From CustomerIs Which used in POS Table=======================//
						$posSql = "SELECT customer_id FROM pos WHERE accounts_id = $accounts_id AND sales_datetime >= '$daysback' AND customer_id in (".implode(', ', array_keys($customerIds)).") ORDER BY sales_datetime DESC";
						$posObj = $db->query($posSql, array());
						if($posObj){
							while($onePosRow = $posObj->fetch(PDO::FETCH_OBJ)){
								unset($customerIds[$onePosRow->customer_id]);
							}
						}			
					}
					
					if(!empty($customerIds)){
						//================Remove Customer ID From CustomerIs Which used in POS Table=======================//
						$posSql = "SELECT customer_id FROM repairs WHERE accounts_id = $accounts_id AND last_updated >= '$daysback' AND customer_id in (".implode(', ', array_keys($customerIds)).") ORDER BY repairs_id ASC";
						$posObj = $db->query($posSql, array());
						if($posObj){
							while($onePosRow = $posObj->fetch(PDO::FETCH_OBJ)){
								unset($customerIds[$onePosRow->customer_id]);
							}
						}
					}
					
					if(!empty($customerIds)){	
						$updateData = array('customers_publish'=>0,
											'first_name'=>$db->checkCharLen('customers.first_name', 'GDPR Hidden'),
											'last_name'=>'',
											'email'=>'',
											'company'=>'',
											'contact_no'=>'',
											'secondary_phone'=>'',
											'fax'=>'',
											'customer_type'=>'',
											'shipping_address_one'=>'',
											'shipping_address_two'=>'',
											'shipping_city'=>'',
											'shipping_state'=>'',
											'shipping_zip'=>'',
											'shipping_country'=>'',
											'offers_email'=>'0',
											'website'=>'',
											'credit_limit'=>'0',
											'credit_days'=>'0'
											);
						foreach($customerIds as $customers_id=>$val){
							$update = $db->update('customers', $updateData, $customers_id);
						}
						
						$message .= "<p>Account ID: $accounts_id, Customer IDs: ".count($customerIds).' Data EU GDPR, '.implode(', ', array_keys($customerIds))."</p>";
					}
				}
			}
		}
	}
	
	$last90daysback = date('Y-m-d 00:00:00', strtotime('-90 day', time()));
	$sql = "SELECT accounts_id FROM accounts WHERE last_login BETWEEN '1000-01-01 00:59:59' AND '$last90daysback' ORDER BY accounts_id ASC";
	$query = $db->query($sql, array());
	if($query){
		while($accountsOneRow = $query->fetch(PDO::FETCH_OBJ)){
			$accounts_id =  $accountsOneRow->accounts_id;
			$folderPath = '/home/machouse/public_html/assets/accounts/a_'.$accounts_id;
			if(is_dir($folderPath)){
				$files = glob($folderPath.'/*'); // get all file names
				foreach($files as $file){ // iterate files
				  if(is_file($file))
					unlink($file); // delete file
				}
				rmdir($folderPath);
				$message .= "<p>Account ID: $accounts_id Folder Removed.</p>";
			}
		}
	}	
}

$sql = "SELECT accounts_id FROM accounts WHERE status = 'Pending' AND next_payment_due < '$todaydate' ORDER BY accounts_id ASC";
$query = $db->query($sql, array());
if($query){
	while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
		$db->update('accounts', array('status'=>'CANCELED', 'paypal_id'=>'', 'status_date'=>date('Y-m-d H:i:s')), $oneRow->accounts_id);
	}
}

//========For Last day Coupon Code========//
$yesterdays = date('Y-m-d', strtotime('-1 day', time()));
//======================Check Cart payment ====================//
	
	$startDate = "$yesterdays 00:00:00";
	$endDate = "$yesterdays 23:59:59";
	$sql = "SELECT * FROM pos WHERE sales_datetime BETWEEN '$startDate' AND '$endDate' AND pos_publish = 1 AND (pos_type = 'Sale' or (pos_type in ('Order', 'Repairs') AND order_status = 2)) ORDER BY accounts_id ASC, pos_id ASC";
	$query = $db->querypagination($sql, array());
	if($query){
		foreach($query as $onerow){
			$pos_id = $onerow['pos_id'];
			$accounts_id = $onerow['accounts_id'];
			$is_due = $onerow['is_due'];
			$taxable_total = $nontaxable_total = 0.00;
			
			$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
			$query = $db->query($sqlquery, array());
			if($query){
				while($row = $query->fetch(PDO::FETCH_OBJ)){
					$sales_price = $row->sales_price;
					$shipping_qty = $row->shipping_qty;
					$total =round($sales_price * $shipping_qty,2);
					$discount_is_percent = $row->discount_is_percent;
					$discount = $row->discount;
					if($discount_is_percent>0){
						$discount_value = round($total*0.01*$discount,2);
					}
					else{ 
						$discount_value = round($discount*$shipping_qty,2);
					}
					$taxable = $row->taxable;																		
					if($taxable>0){
						$taxable_total = $taxable_total+$total-$discount_value;
					}
					else{
						$nontaxable_total = $nontaxable_total+$total-$discount_value;
					}						
				}
			}
			
			$taxes_total1 = 0;					
			$tax_inclusive1 = $onerow['tax_inclusive1'];
			if($onerow['taxes_name1'] !=''){
				if($tax_inclusive1>0){
					$taxes_total1 = $taxable_total-round($taxable_total/(($onerow['taxes_percentage1']*0.01)+1),2);
				}
				else{
					$taxes_total1 = round($taxable_total*0.01*$onerow['taxes_percentage1'],2);
				}
			}
			$taxes_total2 = 0;					
			$tax_inclusive2 = $onerow['tax_inclusive2'];
			if($onerow['taxes_name2'] !=''){
				if($tax_inclusive2>0){
					$taxes_total2 = $taxable_total-round($taxable_total/(($onerow['taxes_percentage2']*0.01)+1),2);
				}
				else{
					$taxes_total2 = round($taxable_total*0.01*$onerow['taxes_percentage2'],2);
				}
			}
			
			if($tax_inclusive1>0){$taxes_total1 = 0;}
			if($tax_inclusive2>0){$taxes_total2 = 0;}
			$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
			
			$amountPaid = 0;
			$paymentStr = '';
			$sqlquery = "SELECT * FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
			$queryObj = $db->query($sqlquery, array());
			if($queryObj){
				while($prow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$amountPaid += $prow->payment_amount;
					$paymentStr .= "$prow->payment_amount, ";
				}
			}
			if($is_due==0 && round($grand_total,2) != round($amountPaid,2)){
				$message .= "<p>Account ID: $onerow[accounts_id], Invoice No: $onerow[invoice_no], POS ID: $pos_id, is_due=$is_due & Grand Total: $grand_total < $amountPaid, ($paymentStr)</p>";
			}
			elseif($is_due==1 && round($grand_total,2) == round($amountPaid,2)){
				$message .= "<p>Account ID: $onerow[accounts_id], &emsp; $onerow[invoice_no], &emsp; POS ID: $pos_id, is_due=$is_due & Grand Total: $grand_total = $amountPaid, ($paymentStr)</p>";
			}
			
		}
	}
		
//=======================Check Cart payment End================//

$sql = "SELECT accounts_id, company_subdomain, coupon_code FROM accounts WHERE SUBSTR(created_on,1,10) BETWEEN '$yesterdays' AND '$todaydate' AND coupon_code !='' ORDER BY accounts_id ASC";
$queryObj = $db->query($sql, array());
if($queryObj){
	$message .= '<br /><p><strong>The following ('.$queryObj->rowCount().') Coupon Coded Accounts found over the last week.</strong></p>';
	while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
		$message .= "<p>Account ID: $oneRow->accounts_id, Sub-domain: $oneRow->company_subdomain, Coupon Code: $oneRow->coupon_code</p>";
	}
}

		//=======================Remove 2 Years Back Trial 20 Accounts================//
		$oneYearAgo = date('Y-m-d H:i:s', mktime(0,0,0, date('m'), date('d'), date('Y')-1));
		$sql1Year = "SELECT accounts_id, company_name, company_subdomain, status, location_of, last_login FROM accounts WHERE status = 'CANCELED' AND last_login BETWEEN '1000-01-01 00:59:59' AND '$oneYearAgo' AND (location_of>0 OR (location_of=0 AND accounts_id NOT IN (SELECT location_of FROM accounts WHERE location_of >0))) ORDER BY accounts_id ASC LIMIT 0, 20";
		$acc1YearObj = $db->query($sql1Year, array());
		
		$twoYearsBackDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')-2));
		$sql = "SELECT accounts_id, company_name, company_subdomain, last_login FROM accounts WHERE SUBSTR(last_login,1,10) BETWEEN '1000-01-10' AND '$twoYearsBackDate' AND status ='Trial' ORDER BY accounts_id ASC LIMIT 0,20";
		$queryObj = $db->query($sql, array());
		if($queryObj || $acc1YearObj){
			$Admin = new Admin($db);
			
			if($acc1YearObj){
				$message .= '<br /><p><strong>The following ('.$acc1YearObj->rowCount().') Trial Accounts Removed Successfully for 1 Years CANCELLED 20 Accounts.</strong></p>';
				while($oneAcRow = $acc1YearObj->fetch(PDO::FETCH_OBJ)){
					$returnData = $Admin->AJremove_Accounts($oneAcRow->accounts_id, 'Yes');
					$returnData = (array) json_decode($returnData);
					$dtcount = $returnData['dtcount']??0;
					$message .= "<p>Account ID: $oneAcRow->accounts_id, Company Name: $oneAcRow->company_name, Sub-domain: $oneAcRow->company_subdomain, Last Login: $oneAcRow->last_login, Removed $dtcount data</p>";
				}
			}
			
			if($queryObj){
				$message .= '<br /><p><strong>The following ('.$queryObj->rowCount().') Trial Accounts Removed Successfully for 2 Years Back Trial 20 Accounts.</strong></p>';
				while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$returnData = $Admin->AJremove_Accounts($oneRow->accounts_id, 'Yes');
					$returnData = (array) json_decode($returnData);
					$dtcount = $returnData['dtcount']??0;
					$message .= "<p>Account ID: $oneRow->accounts_id, Company Name: $oneRow->company_name, Sub-domain: $oneRow->company_subdomain, Last Login: $oneRow->last_login, Removed $dtcount data</p>";
				}
			}
		}
		//=========================End===========================//
	
$sql = "SELECT acc.accounts_id, acc.company_subdomain, acc.created_on, acc.trial_days, ins.instance_home_id, acc.status FROM accounts acc, instance_home ins WHERE acc.status IN ('Trial', 'CANCELED') AND ins.website_on = 1 AND ins.accounts_id= acc.accounts_id ORDER BY accounts_id ASC";
$accObj = $db->query($sql, array());
if($accObj){
	while($oneAcRow = $accObj->fetch(PDO::FETCH_OBJ)){
		$accounts_id = $oneAcRow->accounts_id;
		$company_subdomain = $oneAcRow->company_subdomain;
		$created_on = $oneAcRow->created_on;
		$trial_days = $oneAcRow->trial_days;
		$status = $oneAcRow->status;
		
		$date1 = new DateTime(date('Y-m-d', strtotime($created_on)));
		$date2 = new DateTime("now");
		$interval = $date1->diff($date2);
		$registeredDays = 0;
		if($date1 < $date2){
			$registeredDays = $interval->format('%a');
		}
		$DaysRemaining = $trial_days-$registeredDays;
		if(($status=='Trial' && $DaysRemaining<0) || $status=='CANCELED'){
			$message .= "<br />AccId: $accounts_id, sub-domain: $company_subdomain, DaysRemaining: $DaysRemaining = trial_days: $trial_days - registeredDays: $registeredDays";
			$db->update('instance_home', array('website_on'=>0), $oneAcRow->instance_home_id);
		}
	}
}

$totalExecuteTime = time()-$cronstarttime;
$totalMinutes = floor($totalExecuteTime/60);
$totalSeconds = floor($totalExecuteTime%60);
$message .= "<hr>Cron execution times: $totalMinutes Minutes and $totalSeconds Seconds";

if($message !=''){
	mail($db->supportEmail('support'), 'Cron Job Run Successfully.', $message);
}
?>