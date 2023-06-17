<?php
$serverPath = getcwd();
if($serverPath=='/home/machouse'){
	define('OUR_DOMAINNAME', 'machouse.com.bd');
	define('COMPANYNAME', 'Dazzle PVT. Ltd.');
}
else{
	define('OUR_DOMAINNAME', 'machousel.com.bd');
	define('COMPANYNAME', 'SK POS ERP');
}
require_once ("$serverPath/application/Db.php");
require_once ("$serverPath/application/Common.php");
$timezone = 'Asia/Dhaka';
date_default_timezone_set($timezone);
$cronstarttime = time();

$db = new Db();
$Common = new Common($db);
$date = date('Y-m-d');
$Str = "";
$update = "UPDATE";
//$sql = "SELECT accounts_id, company_name, company_subdomain FROM accounts WHERE accounts_id = 1961 AND status = 'Active' ORDER BY accounts_id ASC LIMIT 0,2";
$before10daysDate = date('Y-m-d', strtotime("$date -400days")).' 00:00:00';
$sql = "SELECT accounts_id, company_name, company_subdomain FROM accounts WHERE last_login>'$before10daysDate' AND check_ave_cost<'$date' ORDER BY accounts_id ASC LIMIT 0,20";
$query = $db->querypagination($sql, array());
if($query){
	foreach($query as $accountsOneRow){
		$accounts_id =  $accountsOneRow['accounts_id'];
		$db->update('accounts', array('check_ave_cost'=>$date), $accounts_id);		
	}
	
	foreach($query as $accountsOneRow){
		$accounts_id =  $accountsOneRow['accounts_id'];
		$company_name =  $accountsOneRow['company_name'];
		$company_subdomain =  $accountsOneRow['company_subdomain'];
		
		$starttime = time();					
		$accountsCost = $accountsNewCost = 0.00;
		$ProductStr = '';
		$sqlProduct ="SELECT p.product_id, p.sku, i.inventory_id, i.ave_cost, i.current_inventory FROM inventory i, product p 
					WHERE i.accounts_id = $accounts_id AND p.product_publish = 1 AND p.product_type = 'Standard' AND p.manage_inventory_count = 1 AND i.product_id = p.product_id GROUP BY p.product_id ORDER BY p.product_id ASC";
		$queryProduct = $db->query($sqlProduct, array());
		if($queryProduct){
			while($oneRow = $queryProduct->fetch(PDO::FETCH_OBJ)){
				$product_id = $oneRow->product_id;
				$sku = $oneRow->sku;
				$ave_cost = $oneRow->ave_cost;
				$prodAvgCostData = $Common->productAvgCost($accounts_id, $product_id, 1, 1);
				$newCost = $prodAvgCostData[0];
				$newInventory = $prodAvgCostData[2];
				if($ave_cost > $newCost || $ave_cost < $newCost){
					if($update=='UPDATE'){
						$db->update('inventory', array('ave_cost'=>$newCost), $oneRow->inventory_id);
					}
					$ProductStr .= "
Prod ID: $product_id, Current Inventory Cost: $ave_cost, Calculated Cost: $newCost $update";
				}
				
				$current_inventory = $oneRow->current_inventory;
				
				if($current_inventory != $newInventory){
					$ProductStr .= "
Prod ID: $product_id, Current Inventory: $current_inventory, Calculated Inventory: $newInventory $update";
						
					if($update=='UPDATE'){
						$db->update('inventory', array('current_inventory'=>$newInventory), $oneRow->inventory_id);
					}
				}
				$ProductStr .= $prodAvgCostData[1];
			}
		}
				
		$totalRunTime = time()-$starttime;
		$Str .= "
".date('Y-m-d H:i:s').", Account ID: $accounts_id, Time to Run: $totalRunTime seconds, $ProductStr";
	}
}

writeIntoLog('Update Done : '.$Str);

function writeIntoLog($message){
	if($message !=''){
		$fileName = './ave-cost-standard-'.date('D');
		if(is_array($message)){$message = implode(', ', $message);}
		$timezone = 'America/New_York';
		date_default_timezone_set($timezone);
		file_put_contents($fileName, "$message\n",FILE_APPEND);
		if(isset($_SESSION["timezone"])){
			$timezone = $_SESSION["timezone"];
			if($timezone =='' || is_null($timezone)){$timezone = 'America/New_York';}
		}
		date_default_timezone_set($timezone);
	}
}
?>