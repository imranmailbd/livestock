<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$segments =  array_slice(explode('/', $_SERVER['PHP_SELF']), 1);
$segment3name = '';
$segment4name = 0;
if(count($segments)>2){
	$segment3name = trim((string) $segments[2]);
	if(count($segments)>3){
		$segment4name = intval($segments[3]);
	}
}

if(in_array($segment3name, array('checkJSRoundWithPHP'))){
	$db = new Db();
	//$Template = new Template($db);
	//echo $Template->headerHTML();

	if($segment3name=='checkJSRoundWithPHP'){
	?>
	<?php
	$start = intval($segment4name)*2000;
	//$sql = "SELECT pos.* FROM pos, accounts a, pos_cart pc WHERE pos.sales_datetime >= '2018-01-01 00:00:00' AND a.status = 'Active'";
	//$sql .= " AND pos.invoice_no>0 AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) AND pc.shipping_qty<0 AND a.accounts_id = pos.accounts_id AND pos.pos_id = pc.pos_id GROUP BY pos.pos_id ORDER BY pos.accounts_id ASC, pos.invoice_no ASC LIMIT $segment4name, 5000";
	
	$sql = "SELECT pos.*, a.company_subdomain FROM pos, accounts a WHERE pos.sales_datetime >= '2022-07-29 00:00:00'";// AND a.status = 'Active'
	$sql .= " AND pos.invoice_no>0 AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) AND a.accounts_id = pos.accounts_id ORDER BY pos.accounts_id ASC, pos.invoice_no ASC LIMIT $start, 2000";
	echo $sql;
	$posObj = $db->query($sql, array(), 1);
	$sl = 0;
	if($posObj){
		echo "<div class=\"row\">
			<link rel=\"stylesheet\" href=\"/assets/css-".swVersion."/style.css\">
			<script src=\"/assets/js-".swVersion."/roundnumJS.js\"></script>
			<div class=\"col-sm-12\" style=\"stock_takesition:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
						<thead class=\"cf\">
							<tr>
								<th rowspan=\"2\" align=\"center\">AccountsID</th>
								<th rowspan=\"2\" align=\"center\">POS ID</th>
								<th rowspan=\"2\" align=\"center\">Invoice No.</th>
								<th rowspan=\"2\" align=\"center\">Invoice Date</th>
								<th rowspan=\"2\" align=\"center\">Taxable Total</th>
								<th rowspan=\"2\" align=\"center\">Tax%</th>
								<th colspan=\"4\" align=\"center\" width=\"40%\">Tax 1 Total</th>
							</tr>
							<tr>
								<th align=\"center\">PHP</th>
								<th align=\"center\" width=\"15%\">JS Taxes Variable</th>
								<th align=\"center\">JS RoundNum</th>
								<th align=\"center\">JS Common</th>
							</tr>
						</thead>
						<tbody id=\"tableRows\">";
						
		while($pos_onerow = $posObj->fetch(PDO::FETCH_OBJ)){
			$sl++;
			$accounts_id = $pos_onerow->accounts_id;
			$company_subdomain = $pos_onerow->company_subdomain;
			$pos_id = $pos_onerow->pos_id;
			$invoice_no = $pos_onerow->invoice_no;
			$sales_datetime = $pos_onerow->sales_datetime;
			$order_status = $pos_onerow->order_status;
			$pos_type = $pos_onerow->pos_type;
		
			$returnstr = '';
			$taxable_total = $nontaxable_total = 0.00;
			echo '<form name="frm'.$pos_id.'">';
			
			$query = $db->query("SELECT * FROM pos_cart WHERE pos_id = $pos_id", array());
			if($query){
				while($row = $query->fetch(PDO::FETCH_OBJ)){
					$pos_cart_id = $row->pos_cart_id;
					$qty = $row->qty;
					$shipping_qty = $row->shipping_qty;					
					$sales_price = $row->sales_price;					
					$discount_is_percent = $row->discount_is_percent;
					$discount = $row->discount;
					$taxable = $row->taxable;
					$total =round($sales_price * $shipping_qty,2);
					$discount_is_percent = $row->discount_is_percent;
					$discount = $row->discount;
					if($discount_is_percent>0){
						$discount_value = round($total*0.01*$discount,2);
					}
					else{ 
						$discount_value = round($discount*$shipping_qty,2);
					}
					if($taxable>0){
						$taxable_total = $taxable_total+$total-$discount_value;
					}
					else{
						$nontaxable_total = $nontaxable_total+$total-$discount_value;
					}
				}
			}
            
			$taxes_total1 = 0;					
			$tax_inclusive1 = $pos_onerow->tax_inclusive1;
			if($pos_onerow->taxes_name1 !=''){
				if($tax_inclusive1>0){
					$taxes_total1 = round($taxable_total-$taxable_total/(($pos_onerow->taxes_percentage1*0.01)+1),2);
				}
				else{
					$taxes_total1 = round($taxable_total*0.01*$pos_onerow->taxes_percentage1,2);
				}
			}
			echo '<input type="hidden" id="taxes_percentage1'.$pos_id.'" value="'.$pos_onerow->taxes_percentage1.'" />
			<input type="hidden" id="tax_inclusive1'.$pos_id.'" value="'.$pos_onerow->tax_inclusive1.'" />';
			
			$taxes_total2 = 0;					
			$tax_inclusive2 = $pos_onerow->tax_inclusive2;
			if($pos_onerow->taxes_name2 !=''){
				if($tax_inclusive2>0){
					$taxes_total2 = $taxable_total-round($taxable_total/(($pos_onerow->taxes_percentage2*0.01)+1),2);
				}
				else{
					$taxes_total2 = round($taxable_total*0.01*$pos_onerow->taxes_percentage2,2);
				}
			}

			echo '<input type="hidden" id="taxes_percentage2'.$pos_id.'" value="'.$pos_onerow->taxes_percentage2.'" />
			<input type="hidden" id="tax_inclusive2'.$pos_id.'" value="'.$pos_onerow->tax_inclusive2.'" />';
			$phptaxes_total1 = $taxes_total1;
			if($tax_inclusive1>0){$taxes_total1 = 0;}
			if($tax_inclusive2>0){$taxes_total2 = 0;}
			$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
			$grand_total = round($grand_total,2);
			echo '<input type="hidden" id="taxable_total'.$pos_id.'" value="'.$taxable_total.'">
			<input type="hidden" id="nontaxable_total'.$pos_id.'" value="'.$nontaxable_total.'">';
			
			$totalpayment = 0;
			$ppSql = "SELECT payment_method, payment_amount, payment_datetime FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
			$ppQueryObj = $db->query($ppSql, array());
			if($ppQueryObj){
				$p=0;
				$rowspan = $ppQueryObj->rowCount();
				while($onerow = $ppQueryObj->fetch(PDO::FETCH_OBJ)){
					$payment_amount = $onerow->payment_amount;
					$totalpayment = $totalpayment+$payment_amount;
					echo '<input type="hidden" name="payment_amount'.$pos_id.'[]" value="'.$payment_amount.'" />';
				}
			}
			$totalpayment = round($totalpayment,2);
			echo '</form>';
			?>
			<script type="module">
				var phptaxes_total1 = <?php echo $phptaxes_total1;?>;
				var phptaxes_total2 = <?php echo $taxes_total2;?>;
				var phpGrand_total = <?php echo $grand_total;?>;
				var phptotalpayment = <?php echo $totalpayment;?>;
				var taxable_total = parseFloat(document.getElementById("taxable_total<?php echo $pos_id;?>").value);
				var nontaxable_total = parseFloat(document.getElementById("nontaxable_total<?php echo $pos_id;?>").value);
				
				//================Calculate taxes total value=============//
				var taxes_percentage1 = parseFloat(document.getElementById("taxes_percentage1<?php echo $pos_id;?>").value);
				if(taxes_percentage1=='' || isNaN(taxes_percentage1)){taxes_percentage1 = 0;}			
				var tax_inclusive1 = parseInt(document.getElementById("tax_inclusive1<?php echo $pos_id;?>").value);
				if(tax_inclusive1=='' || isNaN(tax_inclusive1)){tax_inclusive1 = 0;}			
				var taxes_total1olds = calculateTaxNew(taxable_total, taxes_percentage1, tax_inclusive1);
				var taxes_total1old = taxes_total1olds[1];
				var taxes_total1s = calculateTax(taxable_total, taxes_percentage1, tax_inclusive1);
				var taxes_total1 = taxes_total1s[1];

				var taxes_percentage2 = parseFloat(document.getElementById("taxes_percentage2<?php echo $pos_id;?>").value);
				if(taxes_percentage2=='' || isNaN(taxes_percentage2)){taxes_percentage2 = 0;}			
				var tax_inclusive2 = parseInt(document.getElementById("tax_inclusive2<?php echo $pos_id;?>").value);
				if(tax_inclusive2=='' || isNaN(tax_inclusive2)){tax_inclusive2 = 0;}			
				var taxes_total2olds = calculateTaxNew(taxable_total, taxes_percentage2, tax_inclusive2);
				var taxes_total2old = taxes_total2olds[1];
				var taxes_total2s = calculateTax(taxable_total, taxes_percentage2, tax_inclusive2);
				var taxes_total2 = taxes_total2s[1];

				if(tax_inclusive1>0){
					//taxes_total1old = 0;
					//taxes_total1 = 0;
				}
				if(tax_inclusive2>0){
					//taxes_total2old = 0;
					//taxes_total2 = 0;
				}

				var grand_totalold = roundnumNew(taxable_total+taxes_total1old+taxes_total2old+nontaxable_total);
				var grand_total = commonroundnum(taxable_total+taxes_total1+taxes_total2+nontaxable_total);
				var paymenttotalamountold = 0;
				var paymenttotalamount = 0;
				var payment_amountarray = document.getElementsByName("payment_amount<?php echo $pos_id;?>[]");
				for(var m = 0; m < payment_amountarray.length; m++) {
					var payment_amount = parseFloat(payment_amountarray[m].value);
					if(payment_amount=='' || isNaN(payment_amount)){payment_amount =0.00;}
					paymenttotalamountold = roundnumNew(paymenttotalamountold+payment_amount);
					paymenttotalamount = commonroundnum(paymenttotalamount+payment_amount);
				}
				if(phptaxes_total1 !=taxes_total1 || phptaxes_total1 !=taxes_total1old){
				//if(phpGrand_total !=grand_total || phptotalpayment != paymenttotalamount || phptaxes_total1 !=taxes_total1 || phptaxes_total2 !=taxes_total2 || phpGrand_total !=grand_totalold || phptotalpayment != paymenttotalamountold || phptaxes_total1 !=taxes_total1old || phptaxes_total2 !=taxes_total2old){					
					
					var tr = cTag('tr');
						var td = cTag('td');
						td.innerHTML = '<?php echo "$accounts_id ($company_subdomain)";?>';
					tr.appendChild(td);
						var td = cTag('td');
						td.innerHTML = '<?php echo $pos_id;?>';
					tr.appendChild(td);
						var td = cTag('td');
							var aTag = cTag('a', {target:'_blank', href:'<?php echo "//$company_subdomain.".OUR_DOMAINNAME."/Invoices/view/$invoice_no";?>'});
							aTag.innerHTML = <?php echo $invoice_no;?>;
						td.appendChild(aTag);
					tr.appendChild(td);
						var td = cTag('td', {nowrap:'nowrap'});
						td.innerHTML = '<?php echo $sales_datetime;?>';
					tr.appendChild(td);
						var td = cTag('td', {align:'right'});
						td.innerHTML = '<?php echo $taxable_total;?>';
					tr.appendChild(td);
						var td = cTag('td', {align:'right'});
						td.innerHTML = '<?php echo $pos_onerow->taxes_percentage1;?>';
					tr.appendChild(td);
						var td = cTag('td', {align:'right'});
						td.innerHTML = '<?php echo $phptaxes_total1;?>';
					tr.appendChild(td);
						var td = cTag('td', {align:'right'});
						td.innerHTML = taxes_total1s[0];
					tr.appendChild(td);
						var td = cTag('td', {align:'right'});
						if(phptaxes_total1 !=taxes_total1old){
							td.innerHTML =taxes_total1old;
						}
					tr.appendChild(td);
						var td = cTag('td', {align:'right'});
						if(phptaxes_total1 !=taxes_total1){
							td.innerHTML =taxes_total1;
						}
					tr.appendChild(td);
						
					document.getElementById("tableRows").appendChild(tr);
				}				
			</script>
			<?php
		}
		echo "</tbody>
					</table>
				</div>				
			</div>    
		</div>";
		if($segment4name>0){
			$prevList = $segment4name-1;
			echo "<a href=\"//demo.".OUR_DOMAINNAME."/DatabaseUpdate/checkJSRoundWithPHP/$prevList\">Prev $segment4name</a>";
		}
		echo " &nbsp; ".intval($segment4name+1)." &nbsp; ";
		
		$nextList = $segment4name+1;
		echo "<a href=\"//demo.".OUR_DOMAINNAME."/DatabaseUpdate/checkJSRoundWithPHP/$nextList\">Next ".intval($nextList+1)."</a> Total $sl data checked";
	}	
	}
	//echo $Template->footerHTML();
}

class DatabaseUpdate{
	/*
	1. Unzip website_assets.zip
		Automatically server will be down
	2. Unzip assets.zip
	3. Unzip application.zip
	4. Import languages.sql
	5. Wait Dennis sir work 
	6. Run Database update Program
	7. Change Index 
	*/
	protected $db;
	public function __construct($db){
		$this->db = $db;
	}
	
	public function databaseBackup(){
		
		$bkusername = $username = 'machouse_skposerp';
		$bkpassword = $password = 'skposerp123!@#';
		$bkdatabase = 'machouse_backup';
		$database = 'machouse_skposerp';
		if(strcmp(OUR_DOMAINNAME, 'machouse.com.bd') != 0) {
			$username = 'root';
			$password = '';
			$bkdatabase = 'skposerp_backup';
			$database = 'skposerp';
		}
		
		$bkPDO = new PDO("mysql:dbname=$bkdatabase;host=localhost;charset=utf8", $bkusername, $bkpassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$sql = "SHOW TABLES";
		$statement = $bkPDO->prepare($sql);
		$statement->execute();
		$tables = $statement->fetchAll(PDO::FETCH_NUM);
		if($tables){
			foreach($tables as $table){
				$tableName = $table[0];
				if(in_array($tableName, array('languages', 'languages1', 'mailing_list')) && $bkdatabase=='celltesting_easyimei9'){}
				else{
					
					$sql2 = "DROP TABLE $tableName";
					$statement2 = $bkPDO->prepare($sql2);
					$statement2->execute();
					echo '<br />'.$tableName.' Table droped';
				}		
			}
		}
		
		echo '<br />====================All table from '.$bkdatabase.' data has been removed successfully==============================<br />';
		
		$mainPDO = new PDO("mysql:dbname=$database;host=localhost;charset=utf8", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		
		$sql = "SHOW TABLES";
		$statement = $mainPDO->prepare($sql);
		$statement->execute();
		$tables = $statement->fetchAll(PDO::FETCH_NUM);
		if($tables){
			foreach($tables as $table){
				$tableName = $table[0];
				if(in_array($tableName, array('languages', 'mailing_list')) && $bkdatabase=='celltesting_easyimei9'){}
				else{
					$sql3 = "CREATE TABLE $tableName LIKE $database.$tableName";
					$statement3 = $bkPDO->prepare($sql3);
					$statement3->execute();
					
					$sql2 = "INSERT INTO $tableName SELECT * FROM ".$database.".".$tableName;
					$statement2 = $bkPDO->prepare($sql2);
					$statement2->execute();
					echo '<br />'.$tableName.' Table Inserted';
				}
			}
		}
	}
	
	public function sqlRun(){
		$this->db->query("ALTER TABLE `user` ADD `no_restrict_ip` TINYINT(1) NOT NULL AFTER `is_admin`", array());
		//$this->db->query("", array());
		//$this->db->query("", array());
		//$this->db->query("", array());
		//$this->db->query("", array());
		//s45886
		echo 'Sql run successfully';
		
	}

	public function checkITCartMobileAveCost(){
		$str = '';
		$sqlquery = "SELECT po.accounts_id, po.po_number, po.supplier_id, po.lot_ref_no, poi.po_items_id, poi.product_id, poi.cost FROM po, po_items poi WHERE po.transfer =1 AND po.po_id = poi.po_id ORDER BY po.po_id ASC";
		$query = $this->db->querypagination($sqlquery, array());
		if($query){
			foreach($query as $oneRow){
				
				$accounts_id = $oneRow['accounts_id'];				
				$po_number = $oneRow['po_number'];				
				$po_items_id = $oneRow['po_items_id'];				
				$product_id = $oneRow['product_id'];
				$poCost = $oneRow['cost'];
                
				$str .= "<br>$accounts_id:: $oneRow[po_number]: $poCost = ";
				
				$accounts_id = intval($oneRow['supplier_id']);
				$po_number = intval($oneRow['lot_ref_no']);
				
				if($po_number>0){
    				//echo "<br>$accounts_id:: $oneRow[po_number]: $poCost = (po.accounts_id = $accounts_id AND po.po_number = $po_number AND  po.transfer = 2 AND poi.product_id = $product_id)";
    				$sqlquery2 = "SELECT po.accounts_id, po.po_number, poi.po_items_id, poi.cost FROM po, po_items poi WHERE po.accounts_id = $accounts_id AND po.po_number = $po_number AND  po.transfer = 2 AND poi.product_id = $product_id AND po.po_id = poi.po_id ORDER BY po.po_id ASC";
    				$query2 = $this->db->querypagination($sqlquery2, array());
    				if($query2){
    					foreach($query2 as $oneRow2){
    
    						$updateData = array('check_ave_cost'=>date('Y-m-d'));
    						$updateData['cost'] = $poCost;
    						$cost =  $oneRow2['cost'];
    
    						$updateData = $this->db->update('po_items', $updateData, $oneRow2['po_items_id']);
    						$str .= "$oneRow2[accounts_id]:: $oneRow2[po_number]: $cost<br>";
    					}
    				}
				}
			}
		}
		echo $str;
	}
	
	function missingInventory(){
		$tableRowsStr = "";
		$dataAdd = $dataUpdate = 0;
				
		$sql = "SELECT * FROM inventory WHERE accounts_id = 8037 ORDER BY inventory_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
			    
				$product_id = $oneRow->product_id;
				
				$idataArray = array('product_id'=>$oneRow->product_id,
									'accounts_id'=>$oneRow->accounts_id,
									'regular_price'=>$oneRow->regular_price,
									'minimum_price'=>$oneRow->minimum_price,
									'ave_cost'=>$oneRow->ave_cost,
									'ave_cost_is_percent'=>$oneRow->ave_cost_is_percent,
									'current_inventory'=>0,
									'low_inventory_alert'=>$oneRow->low_inventory_alert,
									'prices_enabled'=>$oneRow->prices_enabled);
				
				$locationObj = $this->db->query("SELECT accounts_id FROM accounts WHERE location_of = 8037 AND status != 'SUSPENDED'", array(),1);
				if($locationObj){
					while($oneRow2 = $locationObj->fetch(PDO::FETCH_OBJ)){
					    $accounts_id = $oneRow2->accounts_id;
					    $idataArray['accounts_id'] = $accounts_id;
					    $inventory_id = 0;
					    $idataObj2 = $this->db->query("SELECT inventory_id FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_id ORDER BY product_id ASC LIMIT 0, 1", array());
        				if($idataObj2){
        					$inventory_id = $idataObj2->fetch(PDO::FETCH_OBJ)->inventory_id;
        				}
        				
        				if($inventory_id>0){
        				    $this->db->update('inventory', $idataArray, $inventory_id);
        				    $dataUpdate++;
        				}
        				else{
        				    $inventory_id = $this->db->insert('inventory', $idataArray);
        				    $dataAdd++;
        				}
        				
					}						
				}
			}
		}
		$tableRowsStr .= "<p>Total $dataAdd Data Inserted and $dataUpdate Data Updated for Sub-accounts.</p>";
		return $tableRowsStr;
	}
	
	function missingProductonPOorPOS(){
		$tableRowsStr = "";
		$dataCount = 0;
				
		$sql = "SELECT accounts_id, company_subdomain, location_of FROM accounts ORDER BY accounts_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				
				$accounts_id = $oneRow->accounts_id;
				$prod_cat_man = $oneRow->location_of;
				if($prod_cat_man==0){$prod_cat_man = $accounts_id;}
				
				$subDomain = $oneRow->company_subdomain;
				$accountIMEIStr = '';
				$POSCartProdIdMissing = $POItemProdIdMissing = array();
				
				$user_id = 0;
				$userSql = "SELECT user_id FROM user WHERE accounts_id = :accounts_id AND is_admin=1 ORDER BY user_id ASC";
				$userObj = $this->db->query($userSql, array('accounts_id'=>$accounts_id), 1);
				if($userObj){
					while($oneUserRow = $userObj->fetch(PDO::FETCH_OBJ)){
						$user_id = $oneUserRow->user_id;
					}
				}

				$productName = 'Deleted Product';
				$pdataArray = array('created_on'=>date('Y-m-d H:i:s'),
									'last_updated'=>date('Y-m-d H:i:s'),
									'product_publish'=>0,
									'accounts_id'=>$prod_cat_man,
									'user_id'=>$user_id,
									'product_type'=>'Mobile Devices',
									'category_id'=>0,
									'manufacturer_id'=>0,
									'manufacture'=>'',
									'colour_name'=>'',
									'storage'=>'',
									'physical_condition_name'=>'',
									'product_name'=>$productName,
									'sku'=>'',
									'taxable'=>1,
									'require_serial_no'=>0,
									'description'=>'',
									'manage_inventory_count'=>1,
									'allow_backorder'=>1,
									'add_description'=>'',
									'alert_message'=>'',
									'custom_data'=>'');
				
				$idataArray = array('product_id'=>0,
									'accounts_id'=>$accounts_id,
									'regular_price'=>0.00,
									'minimum_price'=>0.00,
									'ave_cost'=>0.00,
									'ave_cost_is_percent'=>0,
									'current_inventory'=>0,
									'low_inventory_alert'=>0,
									'prices_enabled'=>0);
				$missingProductIds = array();					
				$missingOnPOS = $this->db->query("SELECT pos.user_id, pos.invoice_no, pos.sales_datetime, pc.item_id, pc.item_type, pc.description, pc.sales_price, pc.ave_cost, pc.shipping_qty FROM pos, pos_cart pc WHERE pos.accounts_id = $accounts_id AND pc.item_id NOT IN (SELECT product.product_id FROM product WHERE product.accounts_id = $prod_cat_man) AND pc.item_type !='one_time' AND pc.item_id>0 AND pos.pos_id = pc.pos_id GROUP BY pc.item_id ORDER BY pos.accounts_id ASC, pos.sales_datetime ASC", array());
				if($missingOnPOS){
					$dataCount++;
					while($oneRow2 = $missingOnPOS->fetch(PDO::FETCH_OBJ)){
						$created_on = $last_updated = date('Y-m-d H:i:s', strtotime($oneRow2->sales_datetime)-60);
						$product_name = stripslashes(trim((string) $oneRow2->description));
						$explodeProd = explode('(', $product_name);
						$sku = '';
						if(count($explodeProd)>1){
							$product_name = trim($explodeProd[0]);
							$explodeProd = explode(')', $explodeProd[1]);
							$sku = $explodeProd[0];
						}
						if(empty($sku)){$sku = $oneRow2->item_id;}
						//================Product================//
						
						$pdataArray['product_id'] = $oneRow2->item_id;
						$pdataArray['created_on'] = $created_on;
						$pdataArray['last_updated'] = $last_updated;
						$pdataArray['product_name'] = $product_name;
						$pdataArray['sku'] = $sku;

						if($oneRow2->item_type !='cellphones'){
							$pdataArray['product_type'] = 'Standard';
						}

						//================Inventory================//
						$idataArray['product_id'] = $oneRow2->item_id;
						$idataArray['regular_price'] = $oneRow2->sales_price;
						$idataArray['ave_cost'] = $oneRow2->ave_cost;
						$idataArray['current_inventory'] = -$oneRow2->shipping_qty;

						if(array_key_exists($oneRow2->item_id, $missingProductIds)){
							$oldQty = $missingProductIds[$oneRow2->item_id]['idataArray']['current_inventory'];
							$idataArray['current_inventory'] = $idataArray['current_inventory']+$oldQty;
						}
						$missingProductIds[$oneRow2->item_id] = array('pdataArray' => $pdataArray, 'idataArray' => $idataArray);

						$POSCartProdIdMissing[$oneRow2->item_id] = array('user_id' => $oneRow2->user_id, 'invoice_no' => $oneRow2->invoice_no, 'sales_datetime' => $oneRow2->sales_datetime, 'item_type' => $oneRow2->item_type, 'description' => $oneRow2->description, 'sales_price' => $oneRow2->sales_price, 'ave_cost' => $oneRow2->ave_cost, 'shipping_qty' => $oneRow2->shipping_qty);
					}
				}

				$missingOnPO = $this->db->query("SELECT po.accounts_id, po.user_id, po.po_number, poi.created_on, poi.product_id, poi.item_type, poi.cost, poi.received_qty FROM po, po_items poi WHERE po.accounts_id = $accounts_id AND poi.product_id NOT IN (SELECT product.product_id FROM product WHERE product.accounts_id = $prod_cat_man) AND poi.item_type !='one_time' AND poi.product_id>0 AND po.po_id = poi.po_id GROUP BY poi.product_id ORDER BY po.accounts_id ASC, poi.created_on ASC", array());
				if($missingOnPO){
					$dataCount++;
					while($oneRow2 = $missingOnPO->fetch(PDO::FETCH_OBJ)){
						$created_on = $last_updated = date('Y-m-d H:i:s', strtotime($oneRow2->created_on)-60);
						
						//================Product================//
						$pdataArray['product_id'] = $oneRow2->product_id;
						$pdataArray['created_on'] = $created_on;
						$pdataArray['last_updated'] = $last_updated;
						
						if(!array_key_exists($oneRow2->product_id, $missingProductIds)){
							$pdataArray['product_name'] = $productName;
							$pdataArray['sku'] = $oneRow2->product_id;
						}
						if($oneRow2->item_type !='cellphones'){
							$pdataArray['product_type'] = 'Standard';
						}

						//================Inventory================//
						$idataArray['product_id'] = $oneRow2->product_id;
						$idataArray['regular_price'] = $oneRow2->cost;
						$idataArray['ave_cost'] = $oneRow2->cost;
						$idataArray['current_inventory'] = $oneRow2->received_qty;

						if(array_key_exists($oneRow2->product_id, $missingProductIds)){
							$oldQty = $missingProductIds[$oneRow2->product_id]['idataArray']['current_inventory'];
							$idataArray['current_inventory'] = $idataArray['current_inventory']+$oldQty;
						}
						$missingProductIds[$oneRow2->product_id] = array('pdataArray' => $pdataArray, 'idataArray' => $idataArray);

						$POItemProdIdMissing[$oneRow2->product_id][] = array('user_id' => $oneRow2->user_id, 'po_number' => $oneRow2->po_number, 'created_on' => $oneRow2->created_on, 'item_type' => $oneRow2->item_type, 'cost' => $oneRow2->cost, 'received_qty' => $oneRow2->received_qty);
					}
				}

				if(!empty($POSCartProdIdMissing) || !empty($POItemProdIdMissing)){
					$this->db->writeIntoLog("============================ $dataCount. AccId: $accounts_id, Sub-Domain: $subDomain ============================");
				}
				
				if(!empty($POSCartProdIdMissing)){						
					foreach($POSCartProdIdMissing as $product_id=>$posCartInfo){
						$this->db->writeIntoLog('product_id: '.$product_id.', POS More Info: '.json_encode($posCartInfo));							
					}
				}
				
				if(!empty($POItemProdIdMissing)){						
					foreach($POItemProdIdMissing as $product_id=>$poCartInfo){
						$this->db->writeIntoLog('product_id: '.$product_id.', PO More Info: '.json_encode($poCartInfo));							
					}
				}
				
				if(!empty($missingProductIds)){
					foreach($missingProductIds as $product_id=>$productInfo){
						$pdataArray = $productInfo['pdataArray'];
						$productId = $pdataArray['product_id'];
						if($productId>876762){
							$productId = 0;
						}

						$productName = trim("$pdataArray[product_name] $productId");
						$productName = $this->db->checkCharLen('product.product_name', $productName);
						$pdataArray['product_name'] = $productName;
						$SKU = $pdataArray['sku'];
						$SKU = $this->db->checkCharLen('product.sku', $SKU);
						$pdataArray['sku'] = $SKU;

						$idataArray = $productInfo['idataArray'];
						if($productId>0){
							$mproduct_id = 0;
							$productObj = $this->db->querypagination("SELECT product_id FROM product WHERE product_id = $productId ORDER BY product_id DESC LIMIT 0, 1", array());
							if($productObj){
								$mproduct_id = $productObj[0]['product_id'];
							}
							
							$mproduct_id2 = 0;
							$productObj = $this->db->querypagination("SELECT product_id FROM product WHERE product_name = '$productName' ORDER BY product_id DESC LIMIT 0, 1", array());
							if($productObj){
								$mproduct_id2 = $productObj[0]['product_id'];
							}
							if($mproduct_id2>0){
								$productName = trim("$productName $productId");
								$productName = $this->db->checkCharLen('product.product_name', $productName);
								$pdataArray['product_name'] = $productName;
							}
							
							if($mproduct_id==0){								
								$mproduct_id = $this->db->insert('product', $pdataArray);
								if($mproduct_id){
									if($productId==0){$productId = $mproduct_id;}

									$this->db->writeIntoLog("//--------------------------Inserted OLD ProductID: $productId, New product_id : $mproduct_id ----------------------");
									$this->db->writeIntoLog("pdataArray: ".json_encode($pdataArray));
									if(empty($SKU)){
										$this->db->update('product', array('sku'=>$productId), $mproduct_id);
									}

									$idataArray['product_id'] = $productId;
									$inventory_id = $this->db->insert('inventory', $idataArray);	

									$this->db->writeIntoLog("inventory_id: $inventory_id, idataArray: ".json_encode($idataArray));
								}
							}
						}
					}
				}
			}
		}		
		return $tableRowsStr;
	}
	
	public function updateLabelPrinter(){
		//Showing rows 0 - 24 (10626 total, Query took 0.0004 seconds.)
		//Total 460 Data found. Total 0 Data found for 0 < Label Print >3
		// Showing rows 0 - 24 (10166 total, Query took 0.0006 seconds.)
		$returnHTML = "";
		$repairsObj = $this->db->query("SELECT variables.variables_id, accounts.label_prints_count, accounts.accounts_id, accounts.company_subdomain FROM variables, accounts WHERE accounts.label_prints_count<3 AND variables.name = 'barcode_labels' AND variables.accounts_id = accounts.accounts_id", array());
		if($repairsObj){
			$i = $j = 0;
			$returnHTML .= "<table width=\"1000\" border=\"1\">
			<thead>
				<tr style=\"position: sticky;top:0;order-bottom: 1px solid #e1e4e9;background:#d1f0f5;\">
					<th align=\"center\">SL#</th>
					<th align=\"center\">AccountID</th>
					<th align=\"center\">Sub-Domain</th>
					<th align=\"center\">Label Print Could</th>
				</tr>
			</thead>
			<tbody>";
			while($oneRow = $repairsObj->fetch(PDO::FETCH_OBJ)){
				$i++;
				$accounts_id = $oneRow->accounts_id;
				$company_subdomain = $oneRow->company_subdomain;
				$variables_id = $oneRow->variables_id;
				$label_prints_count = $oneRow->label_prints_count;
				if($label_prints_count>0){$j++;}

				$this->db->delete('variables', 'variables_id', $variables_id);

				$company_subdomain = "<a target=\"_blank\" href=\"http://$company_subdomain.".OUR_DOMAINNAME."/Account/login\" title=\"View Details\">$company_subdomain</a>";
				
				$returnHTML .= "<tr>
				<td align=\"center\">$i.</td>
				<td align=\"center\">$accounts_id</td>
				<td align=\"center\">$company_subdomain</td>
				<td align=\"center\">$label_prints_count</td>
				</tr>";
			}
			$returnHTML .= "<tr style=\"background-color:black\">
				<th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"4\">Total $i Data found. Total $j Data found for 0 < Label Print >3.</th></tr></tbody>
			</table>";
			return $returnHTML;
		}
	}

	public function updateRepairShippingQty(){
		$repairsObj = $this->db->query("SELECT pos.accounts_id, pos.pos_id, pos_cart.pos_cart_id, pos_cart.item_id, pos_cart.shipping_qty FROM pos, pos_cart, repairs WHERE pos.pos_type = 'Repairs' AND repairs.status NOT IN ('Finished', 'Invoiced', 'Cancelled') AND pos_cart.shipping_qty>0 AND pos.order_status=1 AND pos_cart.item_type !='cellphones' AND pos.pos_id = repairs.pos_id AND pos.pos_id = pos_cart.pos_id ORDER BY pos.pos_id ASC, pos_cart.pos_cart_id ASC LIMIT 0, 10000", array());
		if($repairsObj){
			while($oneRow = $repairsObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneRow->accounts_id;
				$pos_cart_id = $oneRow->pos_cart_id;
				$product_id = $oneRow->item_id;
				$shipping_qty = $oneRow->shipping_qty;

				$pcUpdate = $this->db->update('pos_cart', array('shipping_qty'=>0), $pos_cart_id);
				if($pcUpdate){
					$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
						$current_inventory = $inventoryrow->current_inventory;
						$newcurrent_inventory = floor($current_inventory+$shipping_qty);
						$this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventoryrow->inventory_id);
						echo '. ';
						$this->db->writeIntoLog("accounts_id: $accounts_id, pos_id: $oneRow->pos_id, pos_cart_id: $pos_cart_id, shipping_qty: $shipping_qty => 0, inventory_id: $inventoryrow->inventory_id, current_inventory: $current_inventory => $newcurrent_inventory");
					}
				}
			}
		}
	}

	function checkDrip(){
		$Drip = new Drip($this->db);
		echo $Drip->runDrip();
		echo '<br>Done';
	}
	//=====================Already implemented on PS=======//	
	
	public function updateLabelSettings(){
		/*
		a:5:{s:8:"fontSize";s:7:"Regular";s:11:"deviceLabel";s:27:"{{ProductName}}
		{{Barcode}}";s:12:"productLabel";s:54:"{{CompanyName}} {{ProductName}}
		{{Price}} {{Barcode}} ";s:19:"repairCustomerLabel";s:59:"{{FirstName}}{{LastName}}{{PhoneNo}}{{TicketNo}}{{DueDate}}";s:17:"repairTicketLabel";s:95:"fname: {{FirstName}} lname: {{LastName}}
		{{DueDate}}
		{{BrandModel}}
		{{ImeiSerial}}
		{{Barcode}} ";}
		*/
		$addSql = '';//"accounts_id = 6 AND ";
		$varObj = $this->db->query("SELECT variables_id, value FROM variables WHERE $addSql name = 'barcode_labels'", array());
		if($varObj){
			while($varRow = $varObj->fetch(PDO::FETCH_OBJ)){
				$variables_id = $varRow->variables_id;
				$value = $varRow->value;
				if(!empty($value)){
					$returnData = array(
					'pl_company_name'=>0, 'pl_product_name'=>1, 'pl_price'=>0, 'pl_barcode'=>1, 
					'dl_company_name'=>0, 'dl_product_name'=>1, 'dl_price'=>0, 'dl_barcode'=>1, 
					'rtl_customer_duedate'=>1, 'rtl_phone_no'=>0, 'rtl_brand_model'=>1, 'rtl_imei_serial'=>1, 'rtl_mode_deails'=>1, 'rtl_problem'=>1, 'rtl_password'=>0, 'rtl_barcode'=>1,
					'rcl_first_name'=>1, 'rcl_last_name'=>1, 'rcl_phone_no'=>1, 'rcl_ticket_no'=>1, 'rcl_due_date'=>1);
					
					$value = preg_replace_callback(
						'/(?<=^|\{|;)s:(\d+):\"(.*?)\";(?=[asbdiO]\:\d|N;|\}|$)/s',
						function($m){
							return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
						},
						$value
					);
					$varData = unserialize($value);
					if(!empty($varData)){
						foreach($varData as $index=>$ivalue){
							$returnData[$index] = $ivalue;
						}
					}
					extract($returnData);
					
					$productLabel = $deviceLabel = $repairTicketLabel = $repairCustomerLabel = '';
					//================productLabel==================//
					if(intval($pl_company_name)>0){
						$productLabel .= "{{CompanyName}}";
					}
					if(intval($pl_product_name)>0){
						if(!empty($productLabel)){
							$productLabel .= "
";
						}
						$productLabel .= "{{ProductName}}";
					}
					if(intval($pl_price)>0){
						if(!empty($productLabel)){
							$productLabel .= "
";
						}
						$productLabel .= "{{Price}}";
					}
					if(intval($pl_barcode)>0){
						if(!empty($productLabel)){
							$productLabel .= "
";
						}
						$productLabel .= "{{Barcode}}";
					}

					//================deviceLabel==================//
					if(intval($dl_company_name)>0){
						$deviceLabel .= "{{CompanyName}}";
					}
					if(intval($dl_product_name)>0){
						if(!empty($deviceLabel)){
							$deviceLabel .= "
";
						}
						$deviceLabel .= "{{ProductName}}";
					}
					if(intval($dl_price)>0){
						if(!empty($deviceLabel)){
							$deviceLabel .= "
";
						}
						$deviceLabel .= "{{Price}}";
					}
					if(intval($dl_barcode)>0){
						if(!empty($deviceLabel)){
							$deviceLabel .= "
";
						}
						$deviceLabel .= "{{Barcode}}";
					}

					//================repairTicketLabel==================//
					if(intval($rtl_customer_duedate)>0){
						$repairTicketLabel .= "{{FirstName}} {{LastName}}";
					}
					if(intval($rtl_phone_no)>0){
						if(!empty($repairTicketLabel)){
							$repairTicketLabel .= "
";
						}
						$repairTicketLabel .= "{{PhoneNo}}";
					}
					if(intval($rtl_customer_duedate)>0){
						if(!empty($repairTicketLabel)){
							$repairTicketLabel .= "
";
						}
						$repairTicketLabel .= "{{DueDate}}";
					}
					if(intval($rtl_brand_model)>0){
						if(!empty($repairTicketLabel)){
							$repairTicketLabel .= "
";
						}
						$repairTicketLabel .= "{{BrandModel}}";
					}
					if(intval($rtl_mode_deails)>0){
						if(!empty($repairTicketLabel)){
							$repairTicketLabel .= "
";
						}
						$repairTicketLabel .= "{{MoreDeails}}";
					}
					if(intval($rtl_imei_serial)>0){
						if(!empty($repairTicketLabel)){
							$repairTicketLabel .= "
";
						}
						$repairTicketLabel .= "{{ImeiSerial}}";
					}
					if(intval($rtl_problem)>0){
						if(!empty($repairTicketLabel)){
							$repairTicketLabel .= "
";
						}
						$repairTicketLabel .= "{{Problem}}";
					}
					if(intval($rtl_password)>0){
						if(!empty($repairTicketLabel)){
							$repairTicketLabel .= "
";
						}
						$repairTicketLabel .= "{{Password}}";
					}
					if(intval($rtl_barcode)>0){
						if(!empty($repairTicketLabel)){
							$repairTicketLabel .= "
";
						}
						$repairTicketLabel .= "{{Barcode}}";
					}

					//================repairCustomerLabel==================//
					if(intval($rcl_first_name)>0){
						$repairCustomerLabel .= "{{FirstName}}";
					}
					if(intval($rcl_last_name)>0){
						if(!empty($repairCustomerLabel)){
							$repairCustomerLabel .= " ";
						}
						$repairCustomerLabel .= "{{LastName}}";
					}
					if(intval($rcl_phone_no)>0){
						if(!empty($repairCustomerLabel)){
							$repairCustomerLabel .= "
";
						}
						$repairCustomerLabel .= "{{PhoneNo}}";
					}
					if(intval($rcl_ticket_no)>0){
						if(!empty($repairCustomerLabel)){
							$repairCustomerLabel .= "
";
						}
						$repairCustomerLabel .= "{{TicketNo}}";
					}
					if(intval($rcl_due_date)>0){
						if(!empty($repairCustomerLabel)){
							$repairCustomerLabel .= "
";
						}
						$repairCustomerLabel .= "{{DueDate}}";
					}
					
					$valueData = array();
					$valueData['fontSize'] = 'Regular';
					$valueData['productLabel'] = $productLabel;
					$valueData['deviceLabel'] = $deviceLabel;
					$valueData['repairTicketLabel'] = $repairTicketLabel;
					$valueData['repairCustomerLabel'] = $repairCustomerLabel;
					
					$value = serialize($valueData);
					$data=array('value'=>$value,
						'last_updated'=> date('Y-m-d H:i:s'));
					$update = $this->db->update('variables', $data, $variables_id);
				}
			}
		}
	}
	
	public function checkAndUpdateRepairSort(){
		$a = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE name = 'repairs_setup'", array());
		if($varObj){
			$sortingTypeData = array(0=>'customers.first_name ASC', 
								1=>'customers.last_name ASC', 
								2=>'repairs.due_datetime ASC, repairs.due_time ASC', 
								3=>'repairs.last_updated DESC',
								4=>'repairs.ticket_no ASC',
								5=>'repairs.ticket_no DESC',
								6=>'repairs.status ASC',
								7=>'repairs.problem ASC',
								8=>'user.user_first_name ASC, user.user_last_name ASC');

			while($oneRow = $varObj->fetch(PDO::FETCH_OBJ)){
				$value = $oneRow->value;					
				if(!empty($value)){
					$i = $ssorting_type = 0;
					$value = unserialize($value);
					if(array_key_exists('repair_sort', $value)){
						$ssorting_type = $value['repair_sort'];
						if(empty($ssorting_type) || !in_array($ssorting_type, $sortingTypeData)){
							$ssorting_type = 0;
						}
						else{
							$ssorting_type = array_search ($ssorting_type, $sortingTypeData);
						}
						$i++;
					}
					if($i>0){
						$addiStr = $value['repair_sort'].' = '.$ssorting_type;
						$value['repair_sort'] = $ssorting_type;
						$value = serialize($value);
						$data=array('value'=>$value,
							'last_updated'=> date('Y-m-d H:i:s'));
						$update = $this->db->update('variables', $data, $oneRow->variables_id);

						$a++;
						echo "<br> $oneRow->accounts_id ($addiStr)";
					}
				}
			}			
		}
		echo "==============================<br><strong>Total $a Accounts Data Found.</strong>";
	}
	
	public function checkRemoveMobileIMEI($start=0){
		$start = intval($start);
		$limit = 1000;
		
		$returnHTML = "<table width=\"1000\" border=\"1\">
		<thead>
			<tr style=\"position: sticky;top:0;order-bottom: 1px solid #e1e4e9;background:#d1f0f5;\">
				<th align=\"center\">IMEI ID</th>
				<th align=\"center\">IMEI Number</th>
				<th align=\"center\">Remove Note Has</th>
				<th align=\"center\">Notes</th>
			</tr>
		</thead>
		<tbody>";
		$i = 0;
		$sql = "SELECT item_id, accounts_id, user_id, item_number FROM item WHERE in_inventory = 0 AND item_id NOT IN (SELECT item_id FROM pos_cart_item GROUP BY item_id) ORDER BY item_id ASC LIMIT $start, $limit";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			$stock = 0;
			$removeStrLang = array(
				stripslashes('RIMOSSO DALL\'INVENTARIO'),
				stripslashes('RETIRÉ DE LINVENTAIRE'),
				stripslashes('ΑΦΑΙΡΕΣΕΤΕ ΑΠΟ ΤΟ ΑΠΟΘΕΜΑΤΙΚΟ'),
				stripslashes('REMOVIDO DO INVENTÁRIO'),
				stripslashes('RETIRADO DEL INVENTARIO'),
				stripslashes('ENTFERNT VON INVENTAR'),
				stripslashes('인벤토리에서 제거함'),
				stripslashes('POISTETTU VARASTA'),
				stripslashes('在庫から取り除かれました'),
				stripslashes('تمت إزالته من المخزون'),
				'REMOVED FROM INVENTORY',
				stripslashes('सूची से हटा दिया गया'),
				stripslashes('STOK SONRASI'),
				stripslashes('বিনিয়োগ থেকে সরিয়ে ফেলা'),
				stripslashes('VERWIJDERD VAN INVENTARIS'),
				stripslashes('УДАЛЕНЫ ИЗ ИНВЕНТАРИЗАЦИИ')
			);

			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				
				$item_id = $oneRow->item_id;
				$accounts_id = $oneRow->accounts_id;
				$item_number = $oneRow->item_number;
				$subDomain = '';
				$accSql = "SELECT company_subdomain FROM accounts WHERE accounts_id = $accounts_id ORDER BY accounts_id ASC";
				$accObj = $this->db->query($accSql, array());
				if($accObj){
					$subDomain = $accObj->fetch(PDO::FETCH_OBJ)->company_subdomain;
				}
				$notes = '';
				$hasRemoveNote = 0;
				$notes_id = 0;
				$notesObj = $this->db->query("SELECT notes_id, note FROM notes WHERE accounts_id = $accounts_id AND note_for = 'item' AND table_id = $item_id ORDER BY notes_id ASC", array());
				if($notesObj){
					while($oneNotesRow = $notesObj->fetch(PDO::FETCH_OBJ)){
						$notes_id = $oneNotesRow->notes_id;
						$notes = $oneNotesRow->note;
						foreach($removeStrLang as $oneLagStr){
							//$hasRemoveNote .= strpos($notes, $oneLagStr).' ||';
							if(strpos($notes, $oneLagStr) !=''){
								$hasRemoveNote++;
								if($oneLagStr !='REMOVED FROM INVENTORY'){
									$newNote = str_replace($oneLagStr, 'REMOVED FROM INVENTORY', $notes);
									$this->db->update('notes', array('note'=>$newNote), $notes_id);
									$notes = $newNote;
								}
							}
						}
					}
				}

				if($hasRemoveNote==0){
					$note = "REMOVED FROM INVENTORY";
					if($notes_id>0){
						if(!empty($notes)){$note .= "<br>$notes";}
						$this->db->update('notes', array('note'=>$note), $notes_id);
					}
					else{
						$noteData=array('table_id'=> $item_id,
										'note_for'=> 'item',
										'created_on'=> date('Y-m-d H:i:s'),
										'last_updated'=> date('Y-m-d H:i:s'),
										'accounts_id'=> $accounts_id,
										'user_id'=> $oneRow->user_id,
										'note'=> $note,
										'publics'=>0);
						$notes_id = $this->db->insert('notes', $noteData);
					}
					$notes = $note;
				}
				$i++;
				$returnHTML .= "<tr><td align=\"center\">$item_id</td>
					<td><a target=\"_blank\" href=\"http://$subDomain.".OUR_DOMAINNAME."/IMEI/view/$item_number\" title=\"Edit\">$item_number</a></td>
					<td align=\"center\">$hasRemoveNote</td>
					<td align=\"center\">$notes</td>
					</tr>";					
			}
		} 
				
		$returnHTML .= "<tr style=\"background-color:black\">
			<th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"5\">Total $i Data found.</th></tr></tbody></table>
			<a href=\"/DatabaseUpdate/checkRemoveMobileIMEI/".intval($limit+$start)."\">Reload Next from ".intval($limit+$start)." to ".intval($limit+$start+$limit)."</a>
			</th>
		</tr>
		</table>";
		
		$returnHTML .= "<meta http-equiv = \"refresh\" content = \"5; url = '/DatabaseUpdate/checkRemoveMobileIMEI/".intval($limit+$start)."\" />";
		return $returnHTML;

	}
	
	public function checkIMEINoSKU($start=0){
		$start = intval($start);
		$limit = 500;
		$i = 0;
		$returnHTML = "";
		$accountsIds = $IMEINoSKUs = array();
		$sql = "SELECT item_id, accounts_id, LENGTH(item_number) AS LengthOfString, item_number FROM item WHERE LENGTH(item_number)>16 ORDER BY accounts_id ASC, item_number ASC LIMIT $start, $limit";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$item_id = $oneRow->item_id;
				$accounts_id = $oneRow->accounts_id;
				$LengthOfString = $oneRow->LengthOfString;
				$item_number = $oneRow->item_number;
				$accountsIds[$accounts_id] = '';
				$IMEINoSKUs[$accounts_id]['item'][$item_id] = array($item_number, $LengthOfString);
			}
		}
		$returnHTML .= $sql;
		$sql = "SELECT product_id, accounts_id, LENGTH(sku) AS LengthOfString, sku FROM product WHERE LENGTH(sku)>16 ORDER BY accounts_id ASC, sku ASC LIMIT $start, $limit";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$product_id = $oneRow->product_id;
				$accounts_id = $oneRow->accounts_id;
				$LengthOfString = $oneRow->LengthOfString;
				$sku = $oneRow->sku;
				$accountsIds[$accounts_id] = '';
				$IMEINoSKUs[$accounts_id]['product'][$product_id] = array($sku, $LengthOfString);
			}
		}
		$returnHTML .= '<br>'.$sql.'<br>'.'<br>';
		$returnHTML .= "<table width=\"1000\" border=\"1\">
		<thead>
			<tr style=\"position: sticky;top:0;order-bottom: 1px solid #e1e4e9;background:#d1f0f5;\">
				<th align=\"center\">SL#</th>
				<th align=\"center\">AccountID</th>
				<th align=\"center\">SKU</th>
				<th align=\"center\">IMEI Number</th>
				<th align=\"center\">Lenth</th>
			</tr>
		</thead>
		<tbody>";
		
		if(!empty($accountsIds)){
			$accSql = "SELECT accounts_id, company_subdomain FROM accounts WHERE accounts_id IN (".implode(', ', array_keys($accountsIds)).") ORDER BY accounts_id ASC";
			$accObj = $this->db->query($accSql, array());
			if($accObj){
				while($oneRow = $accObj->fetch(PDO::FETCH_OBJ)){
					$accountsIds[$oneRow->accounts_id] = $oneRow->company_subdomain;
				}
			}
			
			foreach($accountsIds as $accounts_id=>$subdomain){
				$oneAccIMEINoSKUs = $IMEINoSKUs[$accounts_id];
				foreach($oneAccIMEINoSKUs as $tableName=>$tableData){
					foreach($tableData as $tableId=>$oneRow){
						$i++;
						$sku = $imei = '';
						if($tableName=='item'){
							$imei = "<a target=\"_blank\" href=\"http://$subdomain.".OUR_DOMAINNAME."/IMEI/view/$oneRow[0]\" title=\"View Details\">$oneRow[0]</a>";
						}
						else{
							$sku = "<a target=\"_blank\" href=\"http://$subdomain.".OUR_DOMAINNAME."/Products/view/$tableId\" title=\"View Details\">$oneRow[0]</a>";
						}
						$strLenth = $oneRow[1];
						
						$returnHTML .= "<tr>
						<td align=\"center\">$i.</td>
						<td align=\"center\">$accounts_id</td>
						<td align=\"center\">$sku</td>
						<td align=\"center\">$imei</td>
						<td align=\"center\">$strLenth</td>
						</tr>";
					}
				}					
			}
		} 
				
		$returnHTML .= "<tr style=\"background-color:black\">
			<th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"5\">Total $i Data found.</th></tr></tbody></table>
			<a href=\"/DatabaseUpdate/checkIMEINoSKU/".intval($limit+$start)."\">Reload Next from ".intval($limit+$start)." to ".intval($limit+$start+$limit)."</a>";
		return $returnHTML;

	}
	
	//=====================Already implemented on PS=======//	
	public function checkNegativeITQty(){		
		$returnHTML = "<table width=\"1000\" border=\"1\">
		<thead>
			<tr style=\"position: sticky;top:0;order-bottom: 1px solid #e1e4e9;background:#d1f0f5;\">
				<th align=\"center\">SL</th>
				<th align=\"center\">Transfer From Acc ID</th>
				<th align=\"center\">Transfer To Acc ID</th>
				<th align=\"center\">Inv Tran Number</th>
				<th align=\"center\">Invoice Date</th>
				<th align=\"center\">Qty</th>
				<th align=\"center\">transfer</th>
			</tr>
		</thead>
		<tbody>";
		$i = 0;
		$sql = "SELECT po.accounts_id, po.supplier_id, po.invoice_date, po.po_number, SUM(po_items.received_qty) AS totalQty, po.transfer FROM po, po_items WHERE po.status = 'Closed' AND po.po_id = po_items.po_id AND ((po_items.received_qty>0 AND po.transfer=1) OR (po_items.received_qty<0 AND po.transfer=2)) GROUP BY po.po_id ORDER BY po.po_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				
				$fromAccId = $oneRow->accounts_id;
				$toAccId = $oneRow->supplier_id;
				if($transfer==2){
					$fromAccId = $oneRow->supplier_id;
					$toAccId = $oneRow->accounts_id;
				}
				$fromSubDomain = '';
				$accSql = "SELECT company_subdomain FROM accounts WHERE accounts_id = $fromAccId ORDER BY accounts_id ASC";
				$accObj = $this->db->query($accSql, array());
				if($accObj){
					$fromSubDomain = $accObj->fetch(PDO::FETCH_OBJ)->company_subdomain;
				}
				
				$toSubDomain = '';
				$accSql = "SELECT company_subdomain FROM accounts WHERE accounts_id = $toAccId ORDER BY accounts_id ASC";
				$accObj = $this->db->query($accSql, array());
				if($accObj){
					$toSubDomain = $accObj->fetch(PDO::FETCH_OBJ)->company_subdomain;
				}

				$subDomain = $fromSubDomain;
				if($transfer==2){
					$subDomain = $toSubDomain;
				}
				
				$po_number = $oneRow->po_number;
				$invoice_date = $oneRow->invoice_date;
				$received_qty = $oneRow->totalQty;
				$transfer = $oneRow->transfer;
				$i++;
				$returnHTML .= "<tr><td align=\"center\">$i</td>
					<td align=\"center\">$fromAccId ($fromSubDomain)</td>
					<td align=\"center\">$toAccId ($toSubDomain)</td>
					<td><a target=\"_blank\" href=\"http://$subDomain.".OUR_DOMAINNAME."/Inventory_Transfer/edit/$po_number\" title=\"Edit\">$po_number</a></td>
					<td align=\"center\">$invoice_date</td>
					<td align=\"center\">$received_qty</td>
					<td align=\"center\">$transfer</td>
					</tr>";					
			}
		} 
				
		$returnHTML .= "<tr style=\"background-color:black\">
			<th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"7\">Total $i Data found.</th></tr></tbody></table>";
		return $returnHTML;

	}
	
	function missingIMEIonPO(){
		$tableRowsStr = "";
		$dataCount = 0;
		$sql = "SELECT accounts_id, company_subdomain, location_of FROM accounts WHERE accounts_id IN (358, 339, 5076) ORDER BY accounts_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				
				$accounts_id = $oneRow->accounts_id;
				$prod_cat_man = $oneRow->location_of;
				if($prod_cat_man==0){$prod_cat_man = $accounts_id;}
				
				$subDomain = $oneRow->company_subdomain;
				$accountIMEIStr = '';
				$ItemIdsMissingPOCartItem = $ProductIdsMissingItem = $PosCartItemIdsMissingItem = array();
				if($dataCount<=10){
					
					$user_id = 0;
					$userSql = "SELECT user_id FROM user WHERE accounts_id = :accounts_id AND is_admin=1 ORDER BY user_id ASC";
					$userObj = $this->db->query($userSql, array('accounts_id'=>$accounts_id), 1);
					if($userObj){
						while($oneUserRow = $userObj->fetch(PDO::FETCH_OBJ)){
							$user_id = $oneUserRow->user_id;
						}
					}
					
					$tableObj2 = $this->db->query("SELECT item.* FROM item WHERE item.accounts_id = $accounts_id AND item.item_id NOT IN (SELECT po_cart_item.item_id FROM po_cart_item, po_items WHERE po_cart_item.po_items_id = po_items.po_items_id) GROUP BY item.item_id ORDER BY item.accounts_id ASC, item.product_id ASC, item.item_id ASC", array());
					if($tableObj2){
						$dataCount++;
						while($oneRow2 = $tableObj2->fetch(PDO::FETCH_OBJ)){
							$ItemIdsMissingPOCartItem[$oneRow2->product_id][$oneRow2->item_id] = array('user_id' => $oneRow2->user_id, 'item_number' => $oneRow2->item_number);
							$tableRowsStr .= "* Acc:$oneRow2->accounts_id, UserId: $oneRow2->user_id, Prod ID: $oneRow2->product_id, item_id:$oneRow2->item_id, item_number: $oneRow2->item_number<br>";
						}
					}
					
					$tableObj2 = $this->db->query("SELECT item.* FROM item WHERE item.accounts_id = $accounts_id AND item.product_id NOT IN (SELECT product.product_id FROM product WHERE product.accounts_id = $prod_cat_man) GROUP BY item.item_id ORDER BY item.accounts_id ASC, item.product_id ASC, item.item_id ASC", array());
					if($tableObj2){
						$dataCount++;
						while($oneRow2 = $tableObj2->fetch(PDO::FETCH_OBJ)){
							$ProductIdsMissingItem[$oneRow2->product_id][$oneRow2->item_id] = array('user_id' => $oneRow2->user_id, 'item_number' => $oneRow2->item_number);
							$tableRowsStr .= "** Acc:$oneRow2->accounts_id, UserId: $oneRow2->user_id, Prod ID: $oneRow2->product_id, item_id:$oneRow2->item_id, item_number: $oneRow2->item_number<br>";
						}
					}
					
					$tableObj2 = $this->db->query("SELECT pos.user_id, pc.item_id AS product_id, pci.item_id, pci.pos_cart_item_id, pos.sales_datetime FROM pos, pos_cart pc, pos_cart_item pci WHERE pos.accounts_id = $accounts_id AND pci.item_id NOT IN (SELECT item.item_id FROM item WHERE item.accounts_id = $accounts_id) AND pos.pos_id = pc.pos_id AND pc.pos_cart_id = pci.pos_cart_id GROUP BY pci.item_id ORDER BY pos.accounts_id ASC, pos.sales_datetime ASC", array());
					if($tableObj2){
						$dataCount++;
						while($oneRow2 = $tableObj2->fetch(PDO::FETCH_OBJ)){
							$PosCartItemIdsMissingItem[$oneRow2->product_id][$oneRow2->item_id] = array('user_id' => $oneRow2->user_id, 'pos_cart_item_id' => $oneRow2->pos_cart_item_id, 'sales_datetime'=>$oneRow2->sales_datetime);
							$tableRowsStr .= "*** Acc:$accounts_id, UserId: $oneRow2->user_id, Prod ID: $oneRow2->product_id, item_id:$oneRow2->item_id, sales_datetime: $oneRow2->sales_datetime<br>";
						}
					}
					
					if(!empty($PosCartItemIdsMissingItem)){						
						foreach($PosCartItemIdsMissingItem as $product_id=>$productItems){
							foreach($productItems as $item_id=>$itemInfo){

								$item_number = 'DELETEDIMEI-'.$item_id;
								$mitem_id = 0;
								$itemObj = $this->db->querypagination("SELECT item_id FROM item WHERE accounts_id = $accounts_id AND item_number = '$item_number' ORDER BY item_number DESC LIMIT 0, 1", array());
								if($itemObj){
									$mitem_id = $itemObj[0]['item_id'];
								}
								if($mitem_id == 0){
									$itemData = array('created_on' => $itemInfo['sales_datetime'],
													'last_updated' => $itemInfo['sales_datetime'],
													'accounts_id' => $accounts_id,		
													'user_id' => $itemInfo['user_id'],
													'product_id' => $product_id,
													'item_number' => $item_number,
													'carrier_name' => "",
													'in_inventory' => 0,
													'is_pos'=>0,
													'custom_data'=>''
													);
									$mitem_id = $this->db->insert('item', $itemData);
									if($mitem_id){
										$ItemIdsMissingPOCartItem[$product_id][$mitem_id] = array('user_id' => $itemInfo['user_id'], 'item_number' => $item_number);
										$this->db->writeIntoLog('item_id : '.$mitem_id);
									}
								}

								if($mitem_id>0){
									$pos_cart_item_id = $itemInfo['pos_cart_item_id'];
									$this->db->update('pos_cart_item', array('item_id'=>$mitem_id), $pos_cart_item_id);
								}
							}
						}
						$this->db->writeIntoLog('PosCartItemIdsMissingItem : '.json_encode($PosCartItemIdsMissingItem));
					}

					if(!empty($ItemIdsMissingPOCartItem)){

						$suppliers_id = 0;
						$company = 'Unknown'; 
						$invoice_date = date('Y-m-d');
						$supplierObj = $this->db->query("SELECT suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company = '$company'", array());
						if($supplierObj){
							$suppliers_id = $supplierObj->fetch(PDO::FETCH_OBJ)->suppliers_id;
						}
						
						if($suppliers_id == 0){
							$suppliersdata = array(	'suppliers_publish'=>0,
													'created_on'=>date('Y-m-d H:i:s'),
													'last_updated'=>date('Y-m-d H:i:s'),
													'accounts_id'=>$prod_cat_man,
													'user_id'=>$user_id,
													'first_name'=>'',
													'last_name'=>'',
													'email'=>'',
													'company'=>$company,
													'contact_no'=>'',
													'secondary_phone'=>'',
													'fax'=>'',
													'shipping_address_one'=>'',
													'shipping_address_two'=>'',
													'shipping_city'=>'',
													'shipping_state'=>'',
													'shipping_zip'=>'',
													'shipping_country'=>'',
													'offers_email'=>0,
													'website'=>'');
							$suppliers_id = $this->db->insert('suppliers', $suppliersdata);												
						}
						$po_id = 0;
						$poObj = $this->db->querypagination("SELECT po_id FROM po WHERE accounts_id = $accounts_id AND invoice_date = '$invoice_date' AND supplier_id = $suppliers_id ORDER BY po_number DESC LIMIT 0, 1", array());
						if($poObj){
							$po_id = $poObj[0]['po_id'];
						}
						if($po_id==0){
							$po_number = 1;
							$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
							if($poObj){
								$po_number = $poObj[0]['po_number']+1;
							}
							$poData = array();
							$poData['po_datetime'] = date('Y-m-d H:i:s');
							$poData['last_updated'] = date('Y-m-d H:i:s');											
							$poData['po_number'] = $po_number;
							$poData['lot_ref_no'] = '';
							$poData['paid_by'] = '';
							$poData['supplier_id'] = intval($suppliers_id);
							$poData['date_expected'] = $invoice_date;
							$poData['return_po'] = 0;
							$poData['status'] = 'Closed';
							$poData['accounts_id'] = $accounts_id;
							$poData['user_id'] = $user_id;
							$poData['tax_is_percent'] = 0;
							$poData['taxes'] = 0.000;
							$poData['shipping'] = 0.00;
							$poData['suppliers_invoice_no'] = '';
							$poData['invoice_date'] = $invoice_date;
							$poData['date_paid'] = $invoice_date;
							$poData['transfer'] = 0;
							$poData['transfer'] = 0;
							$po_id = $this->db->insert('po', $poData);
						}

						if($po_id>0){
							foreach($ItemIdsMissingPOCartItem as $product_id=>$productItems){
								$qty = count($productItems);
								$poiData =array('created_on'=>date('Y-m-d H:i:s'),
												'user_id'=>$user_id,
												'po_id'=>$po_id,
												'product_id'=>$product_id,
												'item_type'=>'cellphones',
												'cost'=>0.00,
												'ordered_qty'=>$qty,
												'received_qty'=>$qty);
								$po_items_id = $this->db->insert('po_items', $poiData);
								if($po_items_id){									
									foreach($productItems as $item_id=>$itemInfo){
										$user_id = $itemInfo['user_id'];
										$item_number = $itemInfo['item_number'];
										
										$poCartItemData = array('po_items_id' => $po_items_id,
																'item_id' => $item_id,
																'return_po_items_id' => 0);
										$this->db->insert('po_cart_item', $poCartItemData);
									}
								}
							}
							$this->db->writeIntoLog('po_id : '.$po_id);
						}
						$this->db->writeIntoLog('ItemIdsMissingPOCartItem : '.json_encode($ItemIdsMissingPOCartItem));
					}

					if(!empty($ProductIdsMissingItem)){
						
						$productName = 'Deleted Product';
						$pdataArray = array('created_on'=>date('Y-m-d H:i:s'),
											'last_updated'=>date('Y-m-d H:i:s'),
											'accounts_id'=>$prod_cat_man,
											'user_id'=>$user_id,
											'product_type'=>'Mobile Devices',
											'category_id'=>0,
											'manufacturer_id'=>0,
											'manufacture'=>'',
											'colour_name'=>'',
											'storage'=>'',
											'physical_condition_name'=>'',
											'product_name'=>$productName,
											'sku'=>'',
											'taxable'=>1,
											'require_serial_no'=>0,
											'description'=>'',
											'manage_inventory_count'=>1,
											'allow_backorder'=>1,
											'add_description'=>'',
											'alert_message'=>'',
											'custom_data'=>'');
						
						$idataArray = array('product_id'=>0,
											'accounts_id'=>$accounts_id,
											'regular_price'=>0.00,
											'minimum_price'=>0.00,
											'ave_cost'=>0.00,
											'ave_cost_is_percent'=>0,
											'current_inventory'=>0,
											'low_inventory_alert'=>0,
											'prices_enabled'=>0);

						foreach($ProductIdsMissingItem as $product_id=>$productItems){
							$mproduct_id = 0;
							$productObj = $this->db->querypagination("SELECT product_id FROM product WHERE accounts_id = $prod_cat_man AND product_name = '$productName' AND product_type='Mobile Devices' ORDER BY product_id DESC LIMIT 0, 1", array());
							if($productObj){
								$mproduct_id = $productObj[0]['product_id'];
							}
							if($mproduct_id==0){
								
								$mproduct_id = $this->db->insert('product', $pdataArray);			
								if($mproduct_id){
									$this->db->writeIntoLog('product_id : '.$mproduct_id);
									$this->db->update('product', array('sku'=>$mproduct_id), $mproduct_id);

									$idataArray['product_id'] = $mproduct_id;
									$mproduct_id = $this->db->insert('inventory', $idataArray);	

									foreach($productItems as $item_id=>$itemInfo){
										$this->db->update('item', array('product_id'=>$mproduct_id), $item_id);
									}
								}
							}
						}
						$this->db->writeIntoLog('ProductIdsMissingItem : '.json_encode($ProductIdsMissingItem));
					}
					
				}
			}
		}
		
		return $tableRowsStr;
	}
	
	public function checkUnassignedCustomer(){
		$returnHTML = "<table width=\"1000\" border=\"1\">
		<thead>
			<tr style=\"position: sticky;top:0;order-bottom: 1px solid #e1e4e9;background:#d1f0f5;\">
				<th align=\"center\">Account ID</th>
				<th align=\"center\">Customer ID</th>
				<th align=\"center\">Name</th>
				<th align=\"center\">Present (Y/N)</th>
			</tr>
		</thead>
		<tbody>";
		$i = 0;
		$sql = "SELECT accounts_id, default_customer, company_subdomain FROM accounts WHERE location_of = 0 ORDER BY accounts_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				
				$accounts_id = $oneRow->accounts_id;
				$default_customer = $oneRow->default_customer;
				$subDomain = $oneRow->company_subdomain;

				$presentYN = $customers_publish = 0;
				$first_name = '';
				$accSql = "SELECT first_name, customers_publish FROM customers WHERE customers_id = $default_customer ORDER BY accounts_id ASC";
				$accObj = $this->db->query($accSql, array());
				if($accObj){
					$presentYN = 1;
					$customerRow = $accObj->fetch(PDO::FETCH_OBJ);
					$first_name = $customerRow->first_name;
					$customers_publish = $customerRow->customers_publish;
				}

				if($presentYN==0 || $first_name != 'Unassigned'){
					
					$background = '';
					if($presentYN==0){
						$background = ' style="background:pink;color:white"';
					}
					$i++;
					$returnHTML .= "<tr$background><td align=\"center\">$accounts_id</td>
						<td><a target=\"_blank\" href=\"http://$subDomain.".OUR_DOMAINNAME."/Customers/view/$default_customer\" title=\"Edit\">$default_customer</a></td>
						<td align=\"center\">$first_name</td>
						<td align=\"center\">$presentYN</td>
						</tr>";
					
					if($presentYN>0){
						$this->db->update('customers', array('first_name'=>'Unassigned', 'last_name'=>'', 'customers_publish'=>0), $default_customer);
					}
					else{
						$user_id = 0;
						$userObj = $this->db->query("SELECT user_id FROM user WHERE accounts_id = $accounts_id AND is_admin = 1", array());
						if($userObj){
							$user_id = $userObj->fetch(PDO::FETCH_OBJ)->user_id;
						}
						$customersdata = array( 'customers_publish'=>0,
												'created_on' => date('Y-m-d H:i:s'),
												'last_updated' => date('Y-m-d H:i:s'),
												'accounts_id'=>$accounts_id,
												'user_id'=>$user_id,
												'first_name'=>'Unassigned',
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
												'offers_email'=>0,
												'website'=>'',
												'credit_limit'=>0,
												'credit_days'=>0,
												'custom_data'=>'',
												'alert_message'=>''
												);
						$customers_id = $this->db->insert('customers', $customersdata);
						if($customers_id){
							$this->db->update('accounts', array('default_customer'=>$customers_id), $accounts_id);
						}
					}
					
				}
				else if($first_name == 'Unassigned' && $customers_publish==1){
					$this->db->update('customers', array('last_name'=>'', 'customers_publish'=>0), $default_customer);
				}
			}
		} 
				
		$returnHTML .= "<tr style=\"background-color:black\">
			<th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"5\">Total $i Data found.</th></tr></tbody></table>";
		return $returnHTML;

	}
	
	public function checkDupPOS(){
		$returnHTML = "<table width=\"1000\" border=\"1\">
		<thead>
			<tr style=\"position: sticky;top:0;order-bottom: 1px solid #e1e4e9;background:#d1f0f5;\">
				<th align=\"center\">Account ID</th>
				<th align=\"center\">Invoice Number</th>
				<th align=\"center\">POS Ids</th>
			</tr>
		</thead>
		<tbody>";
		$i = 0;
		$addiSql = "accounts_id IN (606, 1930, 2128, 3854, 3907) AND ";
		//$addiSql = "accounts_id IN (4013, 4044, 5510, 6321) AND ";
		//$addiSql = "accounts_id IN (6355, 7245, 8037, 8962) AND ";
		$sql = "SELECT COUNT(pos_id) AS invoiceCount, accounts_id, invoice_no FROM pos WHERE $addiSql invoice_no >0 GROUP BY accounts_id, invoice_no HAVING invoiceCount>1";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$i++;
				$invoiceCount = $oneRow->invoiceCount;
				$accounts_id = $oneRow->accounts_id;
				$invoice_no = $oneRow->invoice_no;
				$subDomain = '';
				$accSql = "SELECT company_subdomain FROM accounts WHERE accounts_id = $accounts_id ORDER BY accounts_id ASC";
				$accObj = $this->db->query($accSql, array());
				if($accObj){
					$subDomain = $accObj->fetch(PDO::FETCH_OBJ)->company_subdomain;
				}
				
				$posIds = '';
				$posObj = $this->db->query("SELECT pos.pos_id, COUNT(pos_cart.pos_cart_id) AS posCartCount FROM pos LEFT JOIN pos_cart ON (pos.pos_id = pos_cart.pos_id) WHERE pos.accounts_id = $accounts_id AND pos.invoice_no = '$invoice_no' GROUP BY pos.pos_id ORDER BY pos.pos_id ASC", array());
				if($posObj){
					while($onePOSRow = $posObj->fetch(PDO::FETCH_OBJ)){
						$posIds .= " $onePOSRow->pos_id ($onePOSRow->posCartCount) ";
					}
				}

				$nextInvoiceNo = $invoice_no+1;
				$missingInvoiceNo = '';
				$tableObj2 = $this->db->query("SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id AND invoice_no>$invoice_no ORDER BY invoice_no ASC", array());
				if($tableObj2){
					while($onePOSRow = $tableObj2->fetch(PDO::FETCH_OBJ)){
						if($onePOSRow->invoice_no==$nextInvoiceNo){
							$nextInvoiceNo = $onePOSRow->invoice_no+1;
						}
						elseif(in_array($onePOSRow->invoice_no, array('32542', '9168', '51720', '51829', '52172', '52713', '1946', '34169'))){
							//$missingInvoiceNo = $nextInvoiceNo++;
						}
						elseif(empty($missingInvoiceNo)){
							$missingInvoiceNo = $nextInvoiceNo++;
						}
					}
				}
				if(empty($missingInvoiceNo)){
					$missingInvoiceNo = $nextInvoiceNo++;
				}
				$posIds .= " Missing: $missingInvoiceNo";

				$returnHTML .= "<tr><td align=\"center\">$accounts_id</td>
						<td><a target=\"_blank\" href=\"http://$subDomain.".OUR_DOMAINNAME."/Invoices/view/$invoice_no\" title=\"Edit\">$invoice_no</a></td>
						<td align=\"center\">$posIds</td>
						</tr>";					
			}
		}
				
		$returnHTML .= "<tr style=\"background-color:black\">
			<th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"5\">Total $i Data found.</th></tr></tbody></table>";
		return $returnHTML;
	}
	
	public function checkDupRepairs(){
		$returnHTML = "<table width=\"1000\" border=\"1\">
		<thead>
			<tr style=\"position: sticky;top:0;order-bottom: 1px solid #e1e4e9;background:#d1f0f5;\">
				<th align=\"center\">Account ID</th>
				<th align=\"center\">Repairs Number</th>
				<th align=\"center\">Repairs Ids</th>
			</tr>
		</thead>
		<tbody>";
		$i = 0;
		$addiSql = "accounts_id IN (3907, 4013, 4389, 4878) AND ";
		$sql = "SELECT COUNT(repairs_id) AS invoiceCount, accounts_id, ticket_no FROM repairs WHERE $addiSql ticket_no >0 GROUP BY accounts_id, ticket_no HAVING invoiceCount>1";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$i++;
				$invoiceCount = $oneRow->invoiceCount;
				$accounts_id = $oneRow->accounts_id;
				$ticket_no = $oneRow->ticket_no;
				$subDomain = '';
				$accSql = "SELECT company_subdomain FROM accounts WHERE accounts_id = $accounts_id ORDER BY accounts_id ASC";
				$accObj = $this->db->query($accSql, array());
				if($accObj){
					$subDomain = $accObj->fetch(PDO::FETCH_OBJ)->company_subdomain;
				}
				
				$repairsIds = '';
				$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE accounts_id = $accounts_id AND ticket_no = '$ticket_no' ORDER BY repairs_id ASC", array());
				if($repairsObj){
					while($onePOSRow = $repairsObj->fetch(PDO::FETCH_OBJ)){
						$repairsIds .= " <a target=\"_blank\" href=\"http://$subDomain.".OUR_DOMAINNAME."/Repairs/edit/$onePOSRow->repairs_id\" title=\"Edit\">$onePOSRow->repairs_id</a> ";
					}
				}

				$nextTicketNo = $ticket_no+1;
				$missingTickets = '';
				$repairsObj = $this->db->query("SELECT ticket_no FROM repairs WHERE accounts_id = $accounts_id AND ticket_no>$ticket_no ORDER BY ticket_no ASC", array());
				if($repairsObj){
					while($onePOSRow = $repairsObj->fetch(PDO::FETCH_OBJ)){
						if($onePOSRow->ticket_no==$nextTicketNo){
							$nextTicketNo = $onePOSRow->ticket_no+1;
						}
						elseif(in_array($onePOSRow->ticket_no, array('19256', '22765', '26448', '41555', '16338', '18005'))){
							//$missingTickets = $nextTicketNo++;
						}
						elseif(empty($missingTickets)){
							$missingTickets = $nextTicketNo++;
						}
					}
				}
				if(empty($missingTickets)){
					$missingTickets = $nextTicketNo++;
				}
				$repairsIds .= " Missing: $missingTickets";
				$returnHTML .= "<tr><td align=\"center\">$accounts_id</td>
						<td>$ticket_no</td>
						<td align=\"center\">$repairsIds</td>
						</tr>";					
			}
		}
				
		$returnHTML .= "<tr style=\"background-color:black\">
			<th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"5\">Total $i Data found.</th></tr></tbody></table>";
		return $returnHTML;
	}
	
	public function checkMobileProduct(){
		$returnHTML = "<table width=\"1000\" border=\"1\">
		<thead>
			<tr style=\"position: sticky;top:0;order-bottom: 1px solid #e1e4e9;background:#d1f0f5;\">
				<th align=\"center\">IMEI ID</th>
				<th align=\"center\">IMEI Number</th>
				<th align=\"center\">PO Qty</th>
				<th align=\"center\">Sales Qty</th>
				<th align=\"center\">Balance</th>
				<th align=\"center\">In Inventory</th>
				<th align=\"center\">Stock Calculate</th>
			</tr>
		</thead>
		<tbody>";
		$i = 0;
		$sql = "SELECT * FROM item WHERE product_id = 673662 ORDER BY item_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			$stock = 0;
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$i++;
				$item_id = $oneRow->item_id;
				$item_number = $oneRow->item_number;
				$product_id = $oneRow->product_id;

				$poQty = $saleQty = 0;

				//==============PO===============//
				$poObj = $this->db->query("SELECT po_cart_item.return_po_items_id FROM po_items, po_cart_item WHERE po_items.product_id = $product_id AND po_cart_item.item_id = $item_id AND po_items.po_items_id = po_cart_item.po_items_id", array());
				if($poObj){
					while($onePORow = $poObj->fetch(PDO::FETCH_OBJ)){
						$poQty++;
						if($onePORow->return_po_items_id>0){$poQty--;}
					}
				}

				//==============Sales===============//
				$salesObj = $this->db->query("SELECT pos_cart_item.return_pos_cart_id FROM pos_cart, pos_cart_item WHERE pos_cart.item_id = $product_id AND pos_cart_item.item_id = $item_id AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id", array());
				if($salesObj){
					while($oneSalesRow = $salesObj->fetch(PDO::FETCH_OBJ)){
						$saleQty++;
						if($oneSalesRow->return_pos_cart_id>0){$saleQty--;}
					}
					if($saleQty>1){
						$saleQty = 1;
					}
				}

				$balance = $poQty-$saleQty;				
				$in_inventory = $oneRow->in_inventory;
				$stock += $balance;
				$background = '';
				if($balance<0){
					$background = ' style="background:pink;color:white"';
				}
				$returnHTML .= "<tr><td align=\"center\">$item_id</td>
					<td><a target=\"_blank\" href=\"http://celbrokers-centromagno.".OUR_DOMAINNAME."/IMEI/view/$item_number\" title=\"Edit\">$item_number</a></td>
					<td align=\"center\">$poQty</td>
					<td align=\"center\">$saleQty</td>
					<td$background align=\"center\">$balance</td>
					<td align=\"center\">$in_inventory</td>
					<td align=\"center\">$stock</td>
					</tr>";
					
			}
		}
				
		$returnHTML .= "<tr style=\"background-color:black\">
			<th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"5\">Total $i Data found.</th></tr></tbody></table>";
		return $returnHTML;
	}
	
	public function checkArchivedProduct(){
		$returnHTML = "<table>
		<thead>
			<tr>
				<th align=\"center\">Product ID</th>
				<th align=\"center\">Product Name</th>
				<th align=\"center\">Product SKU</th>
				<th align=\"center\">Last Updated</th>
				<th align=\"center\">Need</th>
				<th align=\"center\">Have</th>
				<th align=\"center\">OnPO</th>
				<th align=\"center\">Product Type</th>
			</tr>
		</thead>
		<tbody>";
		$addiSql = " accounts_id = 8037 AND";
		$sql = "SELECT product_id, accounts_id, product_type, product_name, sku, last_updated, user_id FROM product WHERE$addiSql product_publish = 0 ORDER BY accounts_id ASC, product_id ASC";
		$tableObj = $this->db->query($sql, array());
		$accountsIds = $productIds = array();
		if($tableObj){	
			$Carts = new Carts($this->db);
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneRow->accounts_id;
				$product_id = $oneRow->product_id;

				$need = $have = $onPO = 0;
				if(in_array($oneRow->product_type, array('Standard', 'Mobile Devices'))){
					$NHPInfo = $Carts->NeedHaveOnPO($product_id, $oneRow->product_type, 1, $accounts_id);
					$need = $NHPInfo[0];
					$have = $NHPInfo[1];
					$onPO = $NHPInfo[2];
				}
				$product_name = stripslashes(trim($oneRow->product_name));
				$sku = $oneRow->sku;
				$last_updated = $oneRow->last_updated;
				if($need>0 || $have>0 || $onPO>0){					
					$productIds[$accounts_id][$product_id] = array($product_name, $sku, $last_updated, $need, $have, $onPO, $oneRow->product_type, $oneRow->user_id);
				}				
			}
		}
		
		$i = 0;
		if(!empty($productIds)){
			$sql = "SELECT accounts_id, company_name, company_subdomain FROM accounts WHERE accounts_id IN (".implode(',', array_keys($productIds)).") ORDER BY accounts_id ASC";
			$tableObj = $this->db->query($sql, array());
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneRow->accounts_id;
				$company_name = $oneRow->company_name;
				$company_subdomain = $oneRow->company_subdomain;
				$accountsIds[$accounts_id] = array($company_name, $company_subdomain);
			}

			foreach($productIds as $accId=>$prodInfo){
				$companyName = $accountsIds[$accId][0];
				$sub_domain = $accountsIds[$accId][1];
				$returnHTML .= "<tr><th style=\"background-color:#dfdfdf\" align=\"center\" colspan=\"7\">AccID: $accId, Company: <a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Account/login\">$companyName</a></th></tr>";
				foreach($prodInfo as $prodId=>$oneRow){
					$sku = $oneRow[1];
					$need = $oneRow[3];
					$have = $oneRow[4];
					$onPO = $oneRow[5];
					$product_type = $oneRow[6];
					$user_id = $oneRow[7];
					
					$inventory_id = $current_inventory = 0;
					$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE product_id = $prodId AND accounts_id = $accId LIMIT 0,1", array());
					if($inventoryObj){
						while($oneInvRow = $inventoryObj->fetch(PDO::FETCH_OBJ)){
							$inventory_id = intval($oneInvRow->inventory_id);
							$current_inventory = floatval($oneInvRow->current_inventory);
						}
					}
					
					if($product_type=='Standard'){
						if($need !=0 || $have !=0){
							// $repairsObj = $this->db->query("SELECT pos_cart.pos_cart_id FROM pos, pos_cart, repairs WHERE pos_cart.item_id = $prodId AND pos.accounts_id = $accId AND repairs.status NOT IN ('Finished', 'Estimate', 'Invoiced', 'Cancelled') AND pos.pos_type = 'Repairs' AND pos.order_status=1 AND pos_cart.shipping_qty>0 AND pos.pos_id = pos_cart.pos_id AND pos.pos_id = repairs.pos_id", array());
							// if($repairsObj){
							// 	while($oneRepairsRow = $repairsObj->fetch(PDO::FETCH_OBJ)){
							// 		$this->db->update('pos_cart', array('qty'=>0, 'shipping_qty'=>0), $oneRepairsRow->pos_cart_id);
							// 	}
							// }
						}
						if($need !=0){
							$ordersObj = $this->db->query("SELECT pos_cart.pos_cart_id, pos_cart.shipping_qty FROM pos, pos_cart WHERE pos_cart.item_id = $prodId AND pos.accounts_id = $accId AND pos_cart.qty>pos_cart.shipping_qty AND pos.pos_type = 'Order' AND pos.order_status=1 AND pos.pos_id = pos_cart.pos_id", array());
							if($ordersObj){
								while($oneOrdersRow = $ordersObj->fetch(PDO::FETCH_OBJ)){
									$this->db->update('pos_cart', array('qty'=>$oneOrdersRow->shipping_qty), $oneOrdersRow->pos_cart_id);
								}
							}
						}

						if($current_inventory !=0){
							$negetiveHave = $current_inventory*(-1);

							//=============Create Note for Adjust Inventory==============//
							$noteData=array('table_id'=> $prodId,
											'note_for'=> 'product',
											'created_on'=> date('Y-m-d H:i:s'),
											'last_updated'=> date('Y-m-d H:i:s'),
											'accounts_id'=> $accId,
											'user_id'=> $user_id,
											'note'=> "$negetiveHave inventory has been adjusted $sku",
											'publics'=>0);
							$notes_id = $this->db->insert('notes', $noteData);

							if($inventory_id>0){
								$this->db->update('inventory', array('current_inventory'=>0), $inventory_id);
							}
						}

						if($onPO !=0){
							$poObj = $this->db->query("SELECT po_items.po_items_id, po_items.received_qty FROM po, po_items WHERE po_items.product_id = $prodId AND po.accounts_id = $accId AND po.status = 'Open' AND po_items.ordered_qty>po_items.received_qty AND po.po_id = po_items.po_id", array());
							if($poObj){
								while($onePORow = $poObj->fetch(PDO::FETCH_OBJ)){
									$this->db->update('po_items', array('ordered_qty'=>$onePORow->received_qty), $onePORow->po_items_id);
								}
							}
						}
					}
					elseif($product_type=='Mobile Devices'){
						if($need !=0){
							$ordersObj = $this->db->query("SELECT pos_cart.pos_cart_id, pos_cart.shipping_qty FROM pos, pos_cart WHERE pos_cart.item_id = $prodId AND pos.accounts_id = $accId AND pos_cart.qty>pos_cart.shipping_qty AND pos.pos_type = 'Order' AND pos.order_status=1 AND pos.pos_id = pos_cart.pos_id", array());
							if($ordersObj){
								while($oneOrdersRow = $ordersObj->fetch(PDO::FETCH_OBJ)){
									$this->db->update('pos_cart', array('qty'=>$oneOrdersRow->shipping_qty), $oneOrdersRow->pos_cart_id);
								}
							}
						}

						if($have !=0){
							$itemObj = $this->db->query("SELECT item_id FROM item WHERE product_id = $prodId AND accounts_id = $accId AND item_publish = 1 AND in_inventory = 1", array());
							if($itemObj){
								while($oneItemRow = $itemObj->fetch(PDO::FETCH_OBJ)){
									$this->db->update('item', array('in_inventory'=>0), $oneItemRow->item_id);
									//=============Create Note for Adjust Inventory==============//
									$noteData=array('table_id'=> $oneItemRow->item_id,
									'note_for'=> 'item',
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $accId,
									'user_id'=> $user_id,
									'note'=> "REMOVED FROM INVENTORY<br />This IMEI Removed because of Archiving Product.",
									'publics'=>0);
									$notes_id = $this->db->insert('notes', $noteData);
								}
							}

							if($inventory_id>0){
								$this->db->update('inventory', array('current_inventory'=>0), $inventory_id);
							}
						}
						if($onPO !=0){
							$poObj = $this->db->query("SELECT po_items.po_items_id, po_items.received_qty FROM po, po_items WHERE po_items.product_id = $prodId AND po.accounts_id = $accId AND po.status = 'Open' AND po_items.ordered_qty>po_items.received_qty AND po.po_id = po_items.po_id", array());
							if($poObj){
								while($onePORow = $poObj->fetch(PDO::FETCH_OBJ)){
									$this->db->update('po_items', array('ordered_qty'=>$onePORow->received_qty), $onePORow->po_items_id);
								}
							}
						}
					}
					
					$returnHTML .= "<tr><td align=\"center\">$prodId</td>
					<td>$oneRow[0]</td>
					<td align=\"center\">$oneRow[1]</td>
					<td align=\"center\">$oneRow[2]</td>
					<td align=\"center\">$oneRow[3]</td><td align=\"center\">$oneRow[4]</td><td align=\"center\">$oneRow[5]</td><td align=\"center\">$product_type</td></tr>";
					$i++;
				}
			}
		}
				
		$returnHTML .= "</tbody><tfooter><tr><th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"7\">Total $i Data found.</th></tr></tfooter></table>";
		return $returnHTML;
	}
	
	//===================Last Update===================//
	
	public function convertWarrantiesToSerial(){
		
		$returnHTML = "<table>
		<thead><tr><th align=\"center\">Product ID</th><th align=\"center\">Product Name</th><th align=\"center\">Product SKU</th><th align=\"center\">Last Invoice Date</th><th align=\"center\">POS Cart Data</th><th align=\"center\">POS Cart Updates</th></thead><tbody>";
		
		$sql = "SELECT product.*, inventory.accounts_id AS accountsId, inventory.low_inventory_alert, inventory.inventory_id FROM product, inventory WHERE product.product_type = 'Extended Warranties' AND inventory.product_id = product.product_id ORDER BY product.product_id ASC";
		$tableObj = $this->db->query($sql, array());
		$warrentiesData = array();
		if($tableObj){	
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$oldAccId = $oneRow->accountsId;
				$product_id = $oneRow->product_id;
				$product_name = $oneRow->product_name;
				$low_inventory_alert = $oneRow->low_inventory_alert;
				$storage = $oneRow->storage;
				$s = '';
				if($low_inventory_alert>1){$s = 's';}
				$product_name = "$product_name $low_inventory_alert $storage$s";
				$sku = $oneRow->sku;

				$totalCart = 0;
				$sales_datetime = '';
				$pos_cartObj = $this->db->query("SELECT COUNT(pos_cart.pos_cart_id) AS totalCart, pos.sales_datetime FROM pos, pos_cart WHERE pos.accounts_id = $oldAccId AND pos_cart.item_id = $product_id AND pos_cart.item_type = 'warranty' AND pos.pos_id = pos_cart.pos_id GROUP BY pos.pos_id ORDER BY pos.invoice_no ASC", array());
				if($pos_cartObj){
					while($pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
						$totalCart += $pos_cartrow->totalCart;
						$sales_datetime = $pos_cartrow->sales_datetime;
					}
				}
				$warrentiesData[$oldAccId][$product_id] = array($product_name, $sku, $sales_datetime, $totalCart, $oneRow->inventory_id);				
			}
		}

		//================Insert All Warranties to Serials================//
		$sqlWarranty = "SELECT warranties_id, pos_cart_id, ser_imei, warranty_number FROM warranties ORDER BY warranties_id ASC";
		$WarrantyObj = $this->db->query($sqlWarranty, array());
		if($WarrantyObj){		
			while($oneWarRow = $WarrantyObj->fetch(PDO::FETCH_OBJ)){
				$ser_imei = $oneWarRow->ser_imei;
				if(empty($ser_imei)){$ser_imei = $oneWarRow->warranty_number;}

				$ser_imei = $this->db->checkCharLen('serial_number.serial_number', $ser_imei);

				$serialdata = array('pos_cart_id' => $oneWarRow->pos_cart_id,	
									'serial_number' => $ser_imei,	
									'returned_pos_cart_id' => 0);
				$serial_number_id = $this->db->insert('serial_number',$serialdata);
				if($serial_number_id){
					$this->db->update('warranties', array('customer_id'=> $serial_number_id), $oneWarRow->warranties_id);
				}
			}
		}

		$i = 0;
		if(!empty($warrentiesData)){
			foreach($warrentiesData as $accId=>$prodInfo){
				$returnHTML .= "<tr><th style=\"background-color:#dfdfdf\" align=\"center\" colspan=\"5\">AccID: $accId</th></tr>";
				foreach($prodInfo as $prodId=>$oneRow){

					$this->db->update('product', array('product_name'=> $this->db->checkCharLen('product.product_name', $oneRow[0]), 'product_type'=>'Standard', 'storage'=>'', 'require_serial_no'=>1, 'manage_inventory_count'=>1, 'allow_backorder'=>1), $prodId);
					$this->db->update('inventory', array('low_inventory_alert'=>0), $oneRow[4]);
					//===============Update 1. pos_cart::item_type = 'product', require_serial_no=1================//
					$cartUpdate = 0;
					if($oneRow[3]>0){
						$sqlPOSCart = "SELECT pos_cart.pos_cart_id FROM pos, pos_cart WHERE pos.accounts_id = $accId AND pos_cart.item_id = $prodId AND pos_cart.item_type = 'warranty' AND pos.pos_id = pos_cart.pos_id ORDER BY pos_cart.pos_cart_id ASC";
						$POSCartObj = $this->db->query($sqlPOSCart, array());
						if($POSCartObj){		
							while($oneCartRow = $POSCartObj->fetch(PDO::FETCH_OBJ)){
								$cartUpdate++;
								$this->db->update('pos_cart', array('item_type'=> 'product', 'require_serial_no'=>1), $oneCartRow->pos_cart_id);

								$sqlWarranty = "SELECT pos_cart_id, customer_id, accounts_id, product_id, ser_imei FROM warranties WHERE pos_cart_id = $oneCartRow->pos_cart_id AND sale_return=1 ORDER BY warranties_id ASC";
								$WarrantyObj = $this->db->query($sqlWarranty, array());
								if($WarrantyObj){		
									while($oneWarRow = $WarrantyObj->fetch(PDO::FETCH_OBJ)){
										$warranties_id = $oneWarRow->warranties_id;
										$serial_number_id = $oneWarRow->customer_id;
										$returned_pos_cart_id = 0;
										$sqlWarranty2 = "SELECT pos_cart_id FROM warranties WHERE accounts_id = $oneWarRow->accounts_id AND product_id = $oneWarRow->product_id AND ser_imei = '$oneWarRow->ser_imei' AND pos_cart_id > $oneWarRow->pos_cart_id ORDER BY warranties_id ASC LIMIT 0, 1";
										$WarrantyObj2 = $this->db->query($sqlWarranty2, array());
										if($WarrantyObj2){		
											while($oneWarRow2 = $WarrantyObj2->fetch(PDO::FETCH_OBJ)){
												$returned_pos_cart_id = $oneWarRow2->pos_cart_id;
											}
										}
										if($returned_pos_cart_id>0){
											$this->db->update('serial_number', array('returned_pos_cart_id'=> $returned_pos_cart_id), $serial_number_id);
										}
									}
								}
							}
						}
					}

					$returnHTML .= "<tr><td align=\"center\">$prodId</td><td>$oneRow[0]</td><td align=\"center\">$oneRow[1]</td><td align=\"center\">$oneRow[2]</td><td align=\"center\">$oneRow[3]</td><td align=\"center\">$cartUpdate</td></tr>";
					$i++;
				}
			}
		}
				
		$returnHTML .= "</tbody><tfooter><tr><th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"4\">Total $i Data found.</th></tr></tfooter></table>";
		return $returnHTML;
	}
	
	public function checkWarrantiesProduct(){
		$returnHTML = "<table>
		<thead><tr><th align=\"center\">Product ID</th><th align=\"center\">Product Name</th><th align=\"center\">Product SKU</th><th align=\"center\">Last Invoice Date</th><th align=\"center\">POS Cart Data found</th></thead><tbody>";
		
		$sql = "SELECT product.* FROM accounts, product WHERE product.product_type = 'Extended Warranties' AND accounts.status = 'Active' AND accounts.accounts_id = product.accounts_id ORDER BY accounts.accounts_id ASC, product.product_id ASC";
		$tableObj = $this->db->query($sql, array());
		$warrentiesData = array();
		if($tableObj){	
			$oldAccId = 0;
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$oldAccId = $oneRow->accounts_id;
				$product_id = $oneRow->product_id;
				$product_name = $oneRow->product_name;
				$sku = $oneRow->sku;

				$totalCart = 0;
				$sales_datetime = '';
				$pos_cartObj = $this->db->query("SELECT COUNT(pos_cart.pos_cart_id) AS totalCart, pos.sales_datetime FROM pos, pos_cart WHERE pos_cart.item_id = $product_id AND pos.pos_id = pos_cart.pos_id GROUP BY pos.pos_id ORDER BY pos.invoice_no ASC", array());
				if($pos_cartObj){
					while($pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
						$totalCart += $pos_cartrow->totalCart;
						$sales_datetime = $pos_cartrow->sales_datetime;
					}
				}
				if($totalCart>0){
					$warrentiesData[$oneRow->accounts_id][$product_id] = array($product_name, $sku, $sales_datetime, $totalCart);
				}
				
			}
		}

		$i = 0;
		if(!empty($warrentiesData)){
			foreach($warrentiesData as $accId=>$prodInfo){
				$returnHTML .= "<tr><th style=\"background-color:#dfdfdf\" align=\"center\" colspan=\"5\">AccID: $accId</th></tr>";
				foreach($prodInfo as $prodId=>$oneRow){
					$returnHTML .= "<tr><td align=\"center\">$prodId</td><td>$oneRow[0]</td><td align=\"center\">$oneRow[1]</td><td align=\"center\">$oneRow[2]</td><td align=\"center\">$oneRow[3]</td></tr>";
					$i++;
				}
			}
		}
				
		$returnHTML .= "</tbody><tfooter><tr><th style=\"background-color:#4141244;color:white\" align=\"center\" colspan=\"4\">Total $i Data found.</th></tr></tfooter></table>";
		return $returnHTML;
	}

	public function brandModelCheck(){
		$returnMsg = array();
		$error = $updated = 0;
		$sql = "SELECT accounts_id, company_subdomain FROM accounts WHERE location_of=0 ORDER BY accounts_id ASC";
		$accObj = $this->db->query($sql, array());
		if($accObj){
			while($oneAcRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneAcRow->accounts_id;
				
				$company_subdomain = $oneAcRow->company_subdomain;
				$brandInfo = $brandModelInfo = '';
				
				$sql = "SELECT brand_model_id, brand, model FROM brand_model WHERE accounts_id = $accounts_id ORDER BY UPPER(TRIM(brand)) ASC, UPPER(TRIM(model)) ASC";
				$brandModelObj = $this->db->query($sql, array());
				if($brandModelObj){
					
					$brandModels = array();
					$prevBrand = $prevModel = '';
					$prevBrandModelId = 0;
					while($oneRow = $brandModelObj->fetch(PDO::FETCH_OBJ)){
						$brand_model_id = trim((string) $oneRow->brand_model_id);
						$brand = $oneRow->brand;
						$model = $oneRow->model;
						if(strtoupper(trim((string) $prevBrand))==strtoupper(trim((string) $brand)) && $prevBrand != $brand){
							$error++;
							
							$updatedBrand = trim((string) $prevBrand);
							if (ctype_upper(substr($brand, 0, 1))) {
								$updatedBrand = trim((string) $brand);
								if($prevBrandModelId>0){
									$updated++;
									$this->db->update('brand_model', array('brand'=>$updatedBrand), $prevBrandModelId);
								}
							}
							else{
								$brand = $updatedBrand;
								$updated++;
								$this->db->update('brand_model', array('brand'=>$updatedBrand), $brand_model_id);
							}
							$brandInfo .= "<br>&emsp;&emsp;Brand: |$prevBrand| &emsp;&emsp;&emsp;&emsp; |$brand| &emsp;&emsp;&emsp; New Brand Value: |$updatedBrand| &emsp;&emsp;&emsp; BM ID: $brand_model_id";
							
						}					
						$prevBrand = $brand;
						$prevModel = $model;
						$prevBrandModelId = $brand_model_id;
						
					}
					if(!empty($brandInfo)){
						$brandInfo = '//=======================================Brand===============================//'.$brandInfo;
					}
				}
				

				$sql = "SELECT COUNT(brand_model_id) AS totalRows, brand, model FROM brand_model WHERE accounts_id = $accounts_id GROUP BY UPPER(TRIM(brand)), UPPER(TRIM(model)) HAVING totalRows>1 ORDER BY UPPER(TRIM(brand)) ASC, UPPER(TRIM(model)) ASC";
				$brandModelObj = $this->db->query($sql, array());
				if($brandModelObj){
					
					$brandModels = array();
					$prevBrand = $prevModel = '';
					$prevBrandModelId = 0;
					while($oneRow = $brandModelObj->fetch(PDO::FETCH_OBJ)){
						$totalRows = trim((string) $oneRow->totalRows);
						$brand = $oneRow->brand;
						$model = $oneRow->model;

						$updatedBMId = 0;								
						$bmObj = $this->db->query("SELECT brand_model_id, brand, model, brand_model_publish FROM brand_model WHERE accounts_id = $accounts_id AND UPPER(TRIM(brand)) = :brand AND UPPER(TRIM(model)) = :model", array('brand'=>strtoupper(trim((string) $brand)), 'model'=>strtoupper(trim((string) $model))));
						if($bmObj){
							$l=0;
							while($oneBMRow = $bmObj->fetch(PDO::FETCH_OBJ)){

								$l++;
								$brand_model_id = $oneBMRow->brand_model_id;
								$brand_model_publish = $oneBMRow->brand_model_publish;
								$propCount = 0;
								if($l==1){
									$updatedBMId = $brand_model_id;
									$this->db->update('brand_model', array('brand_model_publish'=>1), $brand_model_id);
								}
								else{									
									$propertiesObj = $this->db->query("SELECT properties_id FROM properties WHERE brand_model_id = $brand_model_id", array());
									if($propertiesObj){									
										while($onePropRow = $propertiesObj->fetch(PDO::FETCH_OBJ)){
											if($updatedBMId>0){
												$this->db->update('properties', array('brand_model_id'=>$updatedBMId), $onePropRow->properties_id);
											}
											$propCount++;
										}
									}

									$this->db->delete('brand_model', 'brand_model_id', $brand_model_id);
									$brandModelInfo .= "<br>&emsp;&emsp;&emsp;&emsp;Brand: |$oneBMRow->brand| &emsp;&emsp;&emsp;Model: |$oneBMRow->model| &emsp;&emsp;&emsp; BM ID: $brand_model_id &emsp;&emsp;&emsp; [$propCount]";
								}
							}
						}						
					}
				}	

				if(!empty($brandInfo) || !empty($brandModelInfo)){
					$returnMsg[] = "AccId: $accounts_id".$brandInfo.$brandModelInfo;
				}				
			}
		}
		$returnMsg[] = "<br>=====================================<br><strong>Total ".count($returnMsg)." Accounts Data found AND $error Error found. Updated DATA: $updated</strong>";
		return implode('<br>', $returnMsg).
		'<script>setTimeout(function() { location.reload(); }, 5000);</script>';
	}
	
	public function checkMultiDrawer(){
		$a = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE name = 'multiple_drawers'", array());
		if($varObj){
			while($oneRow = $varObj->fetch(PDO::FETCH_OBJ)){
				$value = $oneRow->value;					
				if(!empty($value)){
					$i = 0;
					$addiStr = '';
					$value = unserialize($value);
					if(array_key_exists('multiple_cash_drawers', $value)){
						$multiple_cash_drawers = intval($value['multiple_cash_drawers']);
						if($multiple_cash_drawers>0){
							if(array_key_exists('cash_drawers', $value)){
								$cash_drawers = $value['cash_drawers'];
								$cdArray1 = explode('||',$cash_drawers);
								if(!empty($cdArray1)){
									$newCarr = array();
									foreach($cdArray1 as $oneCar){
										if(!empty($oneCar)){
											$newCarr[$oneCar] = '';
											if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $oneCar)){
												$addiStr .= "$oneCar, ";
												$i++;
											}
										}
									}
									$cdArray = array_keys($newCarr);
								}
							}
						}
					}
					if($i>0){
						$a++;
						echo "<br> $oneRow->accounts_id ($addiStr)";
					}
				}
			}			
		}
		echo "==============================<br><strong>Total $a Accounts Data Found.</strong>";
	}

	public function checkPOSDecimalShipQty(){

		$posData = array();
		$sql = "SELECT pos.accounts_id, pos.pos_id, pos.invoice_no, pos_cart.pos_cart_id, pos_cart.item_id, pos_cart.description, FLOOR(pos_cart.shipping_qty) AS intShipQty, pos_cart.shipping_qty AS decimalShipQty FROM pos, pos_cart, product WHERE product.product_type = 'Standard' AND pos.pos_id = pos_cart.pos_id AND pos_cart.item_id = product.product_id GROUP BY pos.accounts_id, pos.pos_id, pos_cart.item_id HAVING decimalShipQty>intShipQty ORDER BY pos.accounts_id ASC, pos.pos_id ASC, pos_cart.item_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$posData[$oneRow->accounts_id][$oneRow->pos_id][$oneRow->item_id] = array($oneRow->invoice_no, $oneRow->description, $oneRow->decimalShipQty, $oneRow->pos_cart_id);
			}
		}

		$returnHTML = "<table>
		<thead><tr><th align=\"center\">POS ID</th><th align=\"center\">Invoice No</th><th align=\"center\">Product Id</th><th align=\"center\">Product Name</th><th align=\"center\">Shiping Qty</th><th align=\"center\">POS Cart ID</th></thead><tbody>";
		
		if(!empty($posData)){
			foreach($posData as $accId=>$posInfo){
				$returnHTML .= "<tr><th style=\"background-color:#dfdfdf\" align=\"center\" colspan=\"5\">AccID: $accId</th></tr>";
				foreach($posInfo as $posId=>$prodInfo){
					foreach($prodInfo as $prodId=>$oneRow){
						$returnHTML .= "<tr><td align=\"center\">$posId</td><td align=\"center\">$oneRow[0]</td><td align=\"center\">$prodId</td><td>$oneRow[1]</td><td align=\"center\">$oneRow[2]</td><td align=\"center\">$oneRow[3]</td></tr>";
						$i++;
					}
				}
			}
		}
		return $returnHTML;
	}
	
	public function checkDoubleRepairsNo(){
		$sql1 = "SELECT accounts_id, COUNT(repairs_id) AS repairsCount, ticket_no FROM repairs WHERE repairs_publish = 1 GROUP BY accounts_id, ticket_no HAVING repairsCount>1 ORDER BY repairs_id ASC";
		$query1 = $this->db->querypagination($sql1, array());
		if($query1){
			foreach($query1 as $oneRow){
				echo "<br>AccId: $oneRow[accounts_id], Ticket No: $oneRow[ticket_no], COUNT: $oneRow[repairsCount]";
			}
		}
	}

	public function checkDoubleInvoiceNo(){
		$sql1 = "SELECT accounts_id, COUNT(pos_id) AS posCount, invoice_no FROM pos WHERE invoice_no>0 AND pos_publish = 1 GROUP BY accounts_id, invoice_no HAVING posCount>1 ORDER BY pos_id ASC";
		$query1 = $this->db->querypagination($sql1, array());
		if($query1){
			foreach($query1 as $oneRow){
				echo "<br>AccId: $oneRow[accounts_id], Invoice No: $oneRow[invoice_no], COUNT: $oneRow[posCount]";
			}
		}
	}

	public function checkDoublePONo(){
		$sql1 = "SELECT accounts_id, COUNT(po_id) AS poCount, po_number FROM po WHERE po_publish = 1 GROUP BY accounts_id, po_number HAVING poCount>1 ORDER BY po_id ASC";
		$query1 = $this->db->querypagination($sql1, array());
		if($query1){
			foreach($query1 as $oneRow){
				echo "<br>AccId: $oneRow[accounts_id], PO No: $oneRow[po_number], COUNT: $oneRow[poCount]";
			}
		}
	}

	public function userRollUpdate(){
		$i = 0;
		$sql = "SELECT user_id, user_roll FROM user WHERE user_roll LIKE '%\"4\":[%' ORDER BY user_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			$Template = new Template($this->db);
			$modules = $Template->modules();
			
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$user_roll = $oneRow->user_roll;
				$CRMYN = 0;
				if(strpos($user_roll, '"11":[]') !== false){
					$user_roll = str_replace('"11":[]', '', $user_roll);
					$user_roll = str_replace(',,', ',', $user_roll);
					$CRMYN++;
				}

				if($CRMYN==0 && strpos($user_roll, '"4":') !== false){
					$user_roll = str_replace('"4":[]', '"4":["cncrm"]', $user_roll);
				}
				$this->db->update('user', array('user_roll'=>$user_roll), $oneRow->user_id);
				echo ". [$oneRow->user_id || $CRMYN] ";
				$i++;
			}
		}
		echo "<br><strong>Total $i Data Updated.</strong>";
	}

	public function checkLanguage(){
		$returnStr = '';
		$phpMissingVariable = $jsMissingVariable = array();
		//==========Check all variables:: languare data========//
		$addiCond = '';//' accounts_id = 6 AND';
		$queryObj = $this->db->query("SELECT variables_id, accounts_id, value FROM variables WHERE $addiCond name='language'", array());
		if($queryObj){
			$languageJSVar = array();
			while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$variables_id = $oneRow->variables_id;
				$accounts_id = $oneRow->accounts_id;
				$value = $oneRow->value;
				$newLang = array();
				if(!empty($value)){
					$modifiedLang = unserialize($value);
					if(!empty($modifiedLang) && is_array($modifiedLang)){
						foreach($modifiedLang as $varName=>$varValue){
							if(!empty($varValue)){
								$expvarValue = explode('||', $varValue);
								if(count($expvarValue)>1){
									$php_js = $expvarValue[0];
									$selLang = $expvarValue[1];
									$exists = 0;
									$sql = "SELECT english, php_js FROM languages1 WHERE variable_name = '$varName' ORDER BY languages_id ASC";
									$tableObj = $this->db->query($sql, array());
									if($tableObj){
										while($oneRow2 = $tableObj->fetch(PDO::FETCH_OBJ)){
											$php_js = $oneRow2->php_js;
											$varName = $oneRow2->english;
											$exists++;
										}
									}
									if($exists > 0){
										$newLang[$varName] = "$php_js||".addslashes(trim((string) stripslashes($selLang)));

										if(in_array($php_js, array(1,2))){
											$phpMissingVariable[] = $varName.':: '.$selLang;
										}
										if(in_array($php_js, array(2,3))){
											$jsMissingVariable[] = $varName.':: '.$selLang;
										}
									}
								}
							}
						}
					}
				}
				if(!empty($newLang)){
					$value = serialize($newLang);
					$data=array('accounts_id'=>$accounts_id,
						'name'=>$this->db->checkCharLen('variables.name', 'language'),
						'value'=>$value,
						'last_updated'=> date('Y-m-d H:i:s'));
					$update = $this->db->update('variables', $data, $variables_id);
					$returnStr .="1. ";
				}
				else{
					$this->db->delete('variables', 'variables_id', $variables_id);
					$returnStr .="0. ";
				}
			}
			
			if(!empty($phpMissingVariable)){
				$returnStr .= '<br>//====================PHP Variable missing List:=====================//<br>';
				$l=0;
				foreach($phpMissingVariable as $oneVariable){
					$l++;
					$returnStr .= "$l. $oneVariable<br>";
				}
			}
	
			if(!empty($jsMissingVariable)){
				$returnStr .= '<br>//====================JS Variable missing List:=====================//<br>';
				$l=0;
				foreach($jsMissingVariable as $oneVariable){
					$l++;
					$returnStr .= "$l. $oneVariable<br>";
				}
			}
		}
		
		return $returnStr;
	}
	
	public function DeletePOS_0_Invoice180DaysPastData(){
		$removemessage = '';
		//======================Delete POS data which invoice_no = 0 before 30 days=================//
		$before30days = date('Y-m-d H:i:s', strtotime('-180 day', time()));
		$posSql = "SELECT pos_id, accounts_id, sales_datetime FROM pos WHERE invoice_no = 0 AND sales_datetime < '$before30days' AND pos_type = 'Sale' ORDER BY accounts_id ASC, pos_id ASC";
		$posObj = $this->db->query($posSql, array());
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
						$this->db->delete('pos', 'pos_id', $pos_id);
						$removemessage .= '<br />&emsp; POS ID: '.$pos_id.', Sales Date: '.$sales_datetime;

						$pos_cartObj = $this->db->query("SELECT pos_id, pos_cart_id, item_type FROM pos_cart WHERE pos_id = $pos_id ORDER BY pos_cart_id ASC", array());
						if($pos_cartObj){
							while($pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
								$pos_cart_id = $pos_cartrow->pos_cart_id;
								$item_type = $pos_cartrow->item_type;
								$this->db->delete('pos_cart', 'pos_cart_id', $pos_cart_id);		
								$removemessage .= '<br />&emsp; &emsp; POS Cart ID: '.$pos_cart_id;

								if($item_type=='cellphones'){
									$pciObj = $this->db->query("SELECT pos_cart_item_id FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id", array());
									if($pciObj){
										while($pciRow = $pciObj->fetch(PDO::FETCH_OBJ)){
											$this->db->delete('pos_cart_item', 'pos_cart_item_id', $pciRow->pos_cart_item_id);
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

		return $removemessage;
	}
	
	public function checkDuePayment($year='', $month=''){
		if(empty($year)){$year=date('Y');}
		if(empty($month)){$month=date('m');}
		$year = intval($year);
		$month = intval($month);

		$Common = new Common($this->db);
		ini_set('memory_limit', '-1');
		$message = "<link rel=\"stylesheet\" href=\"//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\">
					<link rel=\"stylesheet\" href=\"/assets/css-".swVersion."/style.css\">
					<script src=\"//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js\"></script>
					<script>
						var j = jQuery.noConflict();
		
						function changePayment(pos_id, pos_payment_id, payment_amount, is_due){
							var formhtml = '<form action=\"#\" name=\"frmPOSPayment\" id=\"frmPOSPayment\" onSubmit=\"return false;\" enctype=\"multipart/form-data\" method=\"post\" accept-charset=\"utf-8\">'+
												'<div class=\"form-group\" style=\"width:400px\">'+
												'<div class=\"col-sm-6 pright0\">'+
													'<label><strong>Updated Payment</strong></label>'+
												'</div>'+
												'<div class=\"col-sm-4\">'+
													'<input name=\"payment_amount\" id=\"payment_amount\" class=\"form-control\" value=\"'+payment_amount+'\" type=\"text\" size=\"20\" maxlength=\"20\">'+
												'</div>'+
												'<div class=\"col-sm-2 pright0\">'+
													'<button class=\"btn btn-primary\" onClick=\"savePayment()\">Update</button>'+
												'</div>'+
											'</div>'+
												'<input type=\"hidden\" name=\"pos_id\" id=\"pos_id\" value=\"'+pos_id+'\" />'+
												'<input type=\"hidden\" name=\"pos_payment_id\" id=\"pos_payment_id\" value=\"'+pos_payment_id+'\" />'+
												'<input type=\"hidden\" name=\"is_due\" id=\"is_due\" value=\"'+is_due+'\" />'+
											'</form>';
									
							j('.pa'+pos_payment_id).parent('td').append(formhtml);
							
						}
						
						function savePayment(){
							var pos_id = document.getElementById('pos_id');
							var pos_payment_id = document.getElementById('pos_payment_id');
							if(pos_id.value >0){
								var payment_amount = document.getElementById('payment_amount');
								var is_due = document.getElementById('is_due');
								j.ajax({
									method: 'POST',
									url: '/DatabaseUpdate/savePayment',
									data: {pos_id:pos_id.value, pos_payment_id:pos_payment_id.value, payment_amount:payment_amount.value, is_due:is_due.value, action:'adjustPayment'},
								}).done(function( data ) {
									if(data=='session_ended'){
										window.location = '/session_ended';
									}
									if(data=='Saved'){
										var style = {backgroundColor:'#90EE90', float:'left'};
									}
									else{
										var style = {backgroundColor:'#FFCCCB', float:'left'};
									}
									j('.pa'+pos_payment_id.value).css(style);
									j('#frmPOSPayment').remove();									
								})
								.fail(function() {
									connection_dialog(savePayment);
								});
							}
						}
						
						function changeIsDue(pos_id){
							if(pos_id >0){
								j.ajax({
									method: 'POST',
									url: '/DatabaseUpdate/savePayment',
									data: {pos_id:pos_id, action:'changeIsDue'},
								}).done(function( data ) {
									if(data=='session_ended'){
										window.location = '/session_ended';
									}
									if(data=='Saved'){
										var style = {backgroundColor:'#90EE90', float:'left'};
									}
									else{
										var style = {backgroundColor:'#FFCCCB', float:'left'};
									}
									j('.pos'+pos_id).css(style);
									j('#frmPOSPayment').remove();									
								})
								.fail(function() {});
							}
						}
						
						function addPayment(pos_id, grand_total){
							if(pos_id >0 && grand_total !=0){
								j.ajax({
									method: 'POST',
									url: '/DatabaseUpdate/savePayment',
									data: {pos_id:pos_id, payment_amount:grand_total, action:'addPayment'},
								}).done(function( data ) {
									if(data=='session_ended'){
										window.location = '/session_ended';
									}
									if(data=='Saved'){
										var style = {backgroundColor:'#90EE90', float:'left'};
									}
									else{
										var style = {backgroundColor:'#FFCCCB', float:'left'};
									}
									j('.pos'+pos_id).css(style);
									j('#frmPOSPayment').remove();									
								})
								.fail(function() {});
							}
						}
					</script>
		<div id=\"no-more-tables\">
		<table class=\"col-md-12 bgnone table-bordered table-striped table-condensed cf listing\">
		<thead class=\"cf\">
			<tr>
				<th class=\"txtcenter\" width=\"10%\">Account ID</th>
				<th class=\"txtcenter\">Invoice No.</th>
				<th class=\"txtcenter\" width=\"10%\">Payment Amount</th>
				<th class=\"txtcenter\" width=\"10%\">Track Edit</th>
				<th class=\"txtcenter\" nowrap width=\"5%\">Taxable Total</th>
				<th class=\"txtcenter\" nowrap width=\"5%\">Tax1 (%)</th>
				<th class=\"txtcenter\" nowrap width=\"5%\">Taxes1 Total</th>
				<th class=\"txtcenter\" nowrap width=\"5%\">Tax2 (%)</th>
				<th class=\"txtcenter\" nowrap width=\"5%\">Taxes2 Total</th>
			</tr>
		</thead>
		<tbody>";
		
		$sql = "SELECT pos.*, accounts.company_subdomain, customers.credit_limit FROM pos, accounts, customers WHERE";
		if(strlen($year)==4 && $month>0){
			$startDate = date('Y-m-d H:i:s', strtotime("$year-$month-01 00:00:00"));
			$lastDate = date('t', strtotime($startDate));
			$endDate = date('Y-m-d H:i:s', strtotime("$year-$month-$lastDate 23:59:59"));
			$sql .= " pos.sales_datetime BETWEEN '$startDate' AND '$endDate' AND";
		}
		$sql .= " pos.pos_publish = 1 AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) AND pos.accounts_id = accounts.accounts_id AND pos.customer_id = customers.customers_id ORDER BY accounts.accounts_id ASC, pos.pos_id ASC";
        //$message .= $sql;
		$query = $this->db->querypagination($sql, array());
		if($query){
			foreach($query as $onerow){
				$customers_id = $onerow['customer_id'];
				$is_due = $onerow['is_due'];
				$credit_limit = $onerow['credit_limit'];
				$sub_domain = $onerow['company_subdomain'];
				$pos_id = $onerow['pos_id'];
				$accounts_id = $onerow['accounts_id'];
				$is_due = $onerow['is_due'];
				$taxable_total = $nontaxable_total = 0.00;
				$trackEditCount = 0;
				$teObj = $this->db->query("SELECT COUNT(track_edits_id) AS totalrows FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = $customers_id AND details LIKE '%credit_limit%'", array());
				if($teObj){
					$trackEditCount = $teObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
				$query = $this->db->query($sqlquery, array());
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
				
				$taxes_total1 = $taxes_total11 = 0;					
				$tax_inclusive1 = $onerow['tax_inclusive1'];
				$taxes_percentage1 = '';
				if($onerow['taxes_name1'] !=''){
					$taxes_percentage1 = $onerow['taxes_percentage1'];
					$taxes_total1 = $taxes_total11 = $Common->calculateTax($taxable_total, $onerow['taxes_percentage1'], $tax_inclusive1);
				}
				$taxes_total2 = $taxes_total12 = 0;					
				$tax_inclusive2 = $onerow['tax_inclusive2'];
				$taxes_percentage2 = '';
				if($onerow['taxes_name2'] !=''){
					$taxes_percentage2 = $onerow['taxes_percentage2'];
					$taxes_total2 = $taxes_total12 = $Common->calculateTax($taxable_total, $onerow['taxes_percentage2'], $tax_inclusive2);
				}
				
				if($tax_inclusive1>0){$taxes_total1 = 0;}
				if($tax_inclusive2>0){$taxes_total2 = 0;}
				$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
				
				$amountPaid = 0;
				$paymentStr = '';
				$sqlquery = "SELECT * FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
				$queryObj = $this->db->query($sqlquery, array());
				if($queryObj){
					while($prow = $queryObj->fetch(PDO::FETCH_OBJ)){
						$amountPaid += $prow->payment_amount;
						$paymentStr .= "<a href=\"javascript:void(0);\" onClick=\"changePayment($pos_id, $prow->pos_payment_id, $prow->payment_amount, $is_due);\" class=\"pa$prow->pos_payment_id\">$prow->payment_amount</a>, ";
					}
				}
				$grand_total = round($grand_total,2);
				$amountPaid = round($amountPaid,2);
				
				$messageStr = '';
				$issues = 0;
				if($grand_total !=0 && $amountPaid==0 && $credit_limit ==0){
				    $issues++;
					$paymentStr = "<a href=\"javascript:void(0);\" onClick=\"addPayment($pos_id, $grand_total);\" class=\"pos$pos_id\">Add Payment</a>";
					$messageStr .= "<td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Account/login\">$onerow[accounts_id]</a></td>
						<td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Invoices/view/$onerow[invoice_no]/\">$onerow[invoice_no]</a>, pos_id: $pos_id, is_due=$is_due, credit_limit = $credit_limit & grand_total: $grand_total = $amountPaid</td>
						<td>$paymentStr</td>";
				}
				elseif($grand_total != $amountPaid & ($credit_limit ==0 || $is_due==0)){
				    $issues++;
					$messageStr .= "<td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Account/login\">$onerow[accounts_id]</a></td><td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Invoices/view/$onerow[invoice_no]/\">$onerow[invoice_no]</a>, pos_id: $pos_id, is_due=$is_due, credit_limit = $credit_limit & grand_total: $grand_total < $amountPaid</td><td>$paymentStr</td>";
				}
				elseif($grand_total == $amountPaid && $is_due==1){
				    $issues++;
					$messageStr .= "<td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Account/login\">$onerow[accounts_id]</a></td><td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Invoices/view/$onerow[invoice_no]/\">$onerow[invoice_no]</a>, pos_id: $pos_id, <a href=\"javascript:void(0);\" onClick=\"changeIsDue($pos_id);\" class=\"pos$pos_id\">is_due=$is_due</a> && grand_total: $grand_total = $amountPaid</td><td>$paymentStr</td>";
				}
				$messageStr .= "<td>$trackEditCount</td>";
				$messageStr .= "<td>$taxable_total</td>";
				$messageStr .= "<td>$taxes_percentage1</td>";
				$messageStr .= "<td>$taxes_total11</td>";
				$messageStr .= "<td>$taxes_percentage2</td>";
				$messageStr .= "<td>$taxes_total12</td>";
				$dueAmount = $grand_total - $amountPaid;
				if($issues>0){
					if($dueAmount>0 && $dueAmount<0.1){
						$message .= "<tr style=\"background-color: #e7c3c3;\">$messageStr</tr>";
					}
					else{
						$message .= "<tr>$messageStr</tr>";
					}
				}
			}
		}
		$message .= "</tbody></table></div>";
		echo $message;
	}
	
	public function checkCustomerDueHasNoAccRec(){
		//A/C ID: 2093, 
		$returnStr = '';
		$queryObj = $this->db->query("SELECT pos.pos_id, pos.accounts_id, pos.customer_id, pos.invoice_no, pos.sales_datetime FROM pos, customers WHERE pos.is_due=1 AND customers.credit_limit=0 AND pos.customer_id = customers.customers_id ORDER BY pos.accounts_id ASC, pos.invoice_no ASC", array());
		if($queryObj){
			$returnStr .= '<br>//====================Dues Invoice list which customer is not Account Receivable:=====================//<br>';
			$sl=0;
			while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$sl++;
				$returnStr .= "<br>$sl. A/C ID: $oneRow->accounts_id, Invoice #: $oneRow->invoice_no, POS ID: $oneRow->pos_id, Customer ID: $oneRow->customer_id, Sales Date: $oneRow->sales_datetime";
			}
		}
		return $returnStr;
	}
	
	public function languageFileCheckVar(){
		$returnStr = '';
		
		//==========Check All PHP files Variables==============//
		$phpVariablesData = $jsVariablesData = $commonVariables = array();
		$delimiter1 = 'translate(\'';
		$delimiter2 = '\')';
		$phpFiles = glob("./index.php");
		if($phpFiles){
			foreach($phpFiles as $oneFileName){
				$handle = fopen($oneFileName, "r");
				if ($handle) {
					$ln=0;
					while (($line = fgets($handle)) !== false) {
						$ln++;
						if(!empty(trim((string) $line))){
							$oneVariables = array();
							if(substr_count($line, $delimiter1)>0){
								$oneVariables = $this->explodeLaguage($line, $delimiter1, $delimiter2);
							}

							if(!empty($oneVariables)){
								foreach($oneVariables as $oneVariable){
									$phpVariablesData[$oneVariable] = 'PHP file name: index.php, Line # '.$ln;
									$commonVariables[$oneVariable] = '';
								}
							}
						}
					}
					fclose($handle);
				}
			}
		}
		$filePath = "./../application/";
		$phpFiles = glob($filePath."*.php");
		if($phpFiles){
			$firstSearch = 'translate(\'';
			$secondSearch = '\')';
			foreach($phpFiles as $oneFileName){
				$handle = fopen($oneFileName, "r");
				if ($handle) {
					$ln=0;
					while (($line = fgets($handle)) !== false) {
						$ln++;
						if(!empty(trim((string) $line))){
							$oneVariables = array();
							if(substr_count($line, $delimiter1)>0){
								$oneVariables = $this->explodeLaguage($line, $delimiter1, $delimiter2);
							}

							if(!empty($oneVariables)){
								foreach($oneVariables as $oneVariable){
									$phpVariablesData[$oneVariable] = 'PHP file name: '.str_replace($filePath, '', $oneFileName).', Line # '.$ln;
									$commonVariables[$oneVariable] = '';
								}
							}
						}
					}
					fclose($handle);
				}
			}
		}

		//==========Check All JS files Variables==============//
		$delimiter1 = 'Translate(\'';

		$jsFiles = glob("./assets/widget.js");
		if($jsFiles){			
			$firstSearch = 'Translate(\'';
			$secondSearch = '\')';
			$l = 0;
			foreach($jsFiles as $oneFileName){
				$l++;
				$handle = fopen($oneFileName, "r");
				if ($handle) {
					$ln=0;
					while (($line = fgets($handle)) !== false) {
						$ln++;
						if(!empty(trim((string) $line))){
							$oneVariables = array();
							if(substr_count($line, $delimiter1)>0){
								$oneVariables = $this->explodeLaguage($line, $delimiter1, $delimiter2);
							}

							if(!empty($oneVariables)){
								foreach($oneVariables as $oneVariable){
									$oneVariable = addslashes(stripslashes($oneVariable));
									$jsVariablesData[$oneVariable] = 'JS file name: '.str_replace($filePath, '', $oneFileName).', Line # '.$ln;
									$commonVariables[$oneVariable] = '';
								}
							}
						}
					}
					fclose($handle);
				}
			}
		}

		$filePath = "./assets/js-".swVersion."/";
		$jsFiles = glob($filePath."*.js");
		if($jsFiles){			
			$firstSearch = 'Translate(\'';
			$secondSearch = '\')';
			$l = 0;
			foreach($jsFiles as $oneFileName){
				$l++;
				$handle = fopen($oneFileName, "r");
				if ($handle) {
					$ln=0;
					while (($line = fgets($handle)) !== false) {
						$ln++;
						if(!empty(trim((string) $line))){
							$oneVariables = array();
							if(substr_count($line, $delimiter1)>0){
								$oneVariables = $this->explodeLaguage($line, $delimiter1, $delimiter2);
							}

							if(!empty($oneVariables)){
								foreach($oneVariables as $oneVariable){
									$oneVariable = addslashes(stripslashes($oneVariable));
									$jsVariablesData[$oneVariable] = 'JS file name: '.str_replace($filePath, '', $oneFileName).', Line # '.$ln;
									$commonVariables[$oneVariable] = '';
								}
							}
						}
					}
					fclose($handle);
				}
			}
		}
		$insertedCount = 0;
		$Admin = new Admin($this->db);

		if(!empty($commonVariables)){
			$sql = "SELECT languages_id FROM languages ORDER BY languages_id ASC";
			$tableObj = $this->db->query($sql, array());
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$this->db->update('languages', array('php_js'=>0), $oneRow->languages_id);
				}
			}

			ksort($commonVariables);
			foreach($commonVariables as $oneVariable=>$value){
				$oneVariable = addslashes(trim((string) stripslashes($oneVariable)));
				$exists = 0;
				$sql = "SELECT languages_id, php_js FROM languages WHERE english = '$oneVariable' ORDER BY languages_id ASC";
				$tableObj = $this->db->query($sql, array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$exists++;
						$php_js = 0;
						if(array_key_exists($oneVariable, $phpVariablesData) && array_key_exists($oneVariable, $jsVariablesData)){
							$php_js = 2;
						}
						elseif(array_key_exists($oneVariable, $phpVariablesData)){
							$php_js = 1;
						}
						elseif(array_key_exists($oneVariable, $jsVariablesData)){
							$php_js = 3;
						}
						$this->db->update('languages', array('php_js'=>$php_js), $oneRow->languages_id);
					}
				}
				if($exists==0){
					$english = $oneVariable;
					$php_js = 0;
					if(array_key_exists($oneVariable, $phpVariablesData)){
						$php_js = 1;
						$phpMissingVariable[] = $oneVariable.':: '.$phpVariablesData[$oneVariable];
					}
					if(array_key_exists($oneVariable, $jsVariablesData)){
						if($php_js>0){
							$php_js = 2;
						}
						else{
							$php_js = 3;
						}
						$jsMissingVariable[] = $oneVariable.':: '.$jsVariablesData[$oneVariable];
					}					
					$inserted = '';
					if($php_js>0){
						$translateData = array('php_js' => $php_js,'english' => $english);

						$translateData['spanish'] = $Admin->transEngToOthers('es', $english);
						$translateData['french'] = $Admin->transEngToOthers('fr', $english);
						$translateData['greek'] = $Admin->transEngToOthers('el', $english);
						$translateData['german'] = $Admin->transEngToOthers('de', $english);
						$translateData['italian'] = $Admin->transEngToOthers('it', $english);
						$translateData['dutch'] = $Admin->transEngToOthers('nl', $english);
						$translateData['arabic'] = $Admin->transEngToOthers('ar', $english);
						$translateData['chinese'] = $Admin->transEngToOthers('zh-CN', $english);
						$translateData['hindi'] = $Admin->transEngToOthers('hi', $english);
						$translateData['bengali'] = $Admin->transEngToOthers('bn', $english);
						$translateData['portuguese'] = $Admin->transEngToOthers('pt', $english);
						$translateData['russian'] = $Admin->transEngToOthers('ru', $english);
						$translateData['japanese'] = $Admin->transEngToOthers('ja', $english);
						$translateData['korean'] = $Admin->transEngToOthers('ko', $english);
						$translateData['turkey'] = $Admin->transEngToOthers('tr', $english);
						$translateData['finnish'] = $Admin->transEngToOthers('fi', $english);
						$languages_id = $this->db->insert('languages', $translateData);
						if($languages_id){
							$inserted = ' (Inserted)';
							$insertedCount++;
							$phpMissingVariable[] = $oneVariable.':: Inserted: Type='.$php_js;
						}
					}
				}
			}
		}

		if(!empty($phpMissingVariable)){
			$returnStr .= '<br>//====================PHP Variable missing List:=====================//<br>';
			$l=0;
			foreach($phpMissingVariable as $oneVariable){
				$l++;
				$returnStr .= "$l. $oneVariable<br>";
			}
		}

		if(!empty($jsMissingVariable)){
			$returnStr .= '<br>//====================JS Variable missing List:=====================//<br>';
			$l=0;
			foreach($jsMissingVariable as $oneVariable){
				$l++;
				$returnStr .= "$l. $oneVariable<br>";
			}
		}

		//if($insertedCount>0){
			$Admin->languagesVarWrite();
		//}

		return $returnStr;
	}
	
	function explodeLaguage($str, $delimiter1, $delimiter2, $returnArray=array()){
		$firstExplode = explode($delimiter1, $str, 2);
		$secondExplode = explode($delimiter2,  $firstExplode[1], 2);	
		array_push($returnArray, $secondExplode[0]);
		if(substr_count($secondExplode[1], $delimiter1)>0){
			return $this->explodeLaguage($secondExplode[1], $delimiter1, $delimiter2, $returnArray);
		}

		return $returnArray;
	}

	public function checkConditionalPrice($start=0){
		
		ini_set('memory_limit', '-1');
		$todaydate = date('Y-m-d');
		
		$sql = "SELECT i.inventory_id, i.product_id, i.accounts_id, i.prices_enabled, COUNT(pp.product_prices_id) AS totalrows FROM inventory i, product_prices pp WHERE (pp.start_date IN ('0000-00-00', '1000-01-01') OR (pp.start_date <= '$todaydate' AND pp.end_date >= '$todaydate')) AND i.product_id = pp.product_id AND i.accounts_id = pp.accounts_id GROUP BY i.inventory_id ORDER BY i.accounts_id ASC, i.inventory_id ASC";
		$returnStr = $sql;
		$query = $this->db->query($sql, array());
		if($query){		
			while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
				$inventory_id = $oneRow->inventory_id;
				$product_id = $oneRow->product_id;
				$accounts_id = $oneRow->accounts_id;
				$prices_enabled = $oneRow->prices_enabled;
				$totalrows = $oneRow->totalrows;
				if($totalrows>0){$totalrows = 1;}
				
				if($prices_enabled != $totalrows){
					$sl++;
					$this->db->update('inventory', array('prices_enabled'=>$totalrows), $inventory_id);
					$returnStr .= "<br>$sl. A/C ID: $accounts_id, Product ID: $product_id, Inventory ID: $inventory_id, Prices Enabled: $prices_enabled, Conditional Price: $totalrows";
				}
			}		
		}
		return $returnStr;
	}
	
	//=====================2022-04-22====================//

	public function checkOthersLangAcc(){
		$i = 0;
		$sql = "SELECT accounts_id FROM variables WHERE name = 'account_setup' AND value LIKE '%All Others%' ORDER BY name ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				echo "$oneRow->accounts_id<br>";
				$i++;
			}
		}
		echo "==============================<br><strong>Total $i Data Found.</strong>";
	}

	public function posCustomerUpdate(){
		$i = 0;
		$sql = "SELECT p.pos_id, a.default_customer as customer_id FROM pos p, accounts a WHERE p.customer_id = 0 AND p.accounts_id = a.accounts_id ORDER BY pos_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$this->db->update('pos', array('customer_id'=>$oneRow->customer_id), $oneRow->pos_id);
				$i++;
			}
		}
		echo "<br><strong>Total $i Data updated.</strong>";
	}
	
	//=====================2022-01-21====================//

	public function duplicateTableName(){
		//DROP TABLE `manufacturer0419`, `product0419`;
		//copy table category, customers, customer_type, expenses, expense_type, manufacturer, product, repairs, repair_problems, vendors

		$returnMsg = array();
		$totalDeleteCount = 0;
		$sql = "SELECT accounts_id, company_subdomain FROM accounts WHERE location_of=0 ORDER BY accounts_id ASC";
		$accObj = $this->db->query($sql, array());
		if($accObj){
			while($oneAcRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneAcRow->accounts_id;
				$accountsIds = array();
				$accountsIds[] = $accounts_id;
				$accIdSql = " = $accounts_id";
				$subsql = "SELECT accounts_id FROM accounts WHERE location_of = $accounts_id ORDER BY accounts_id ASC";
				$subaccObj = $this->db->query($subsql, array());
				if($subaccObj){
					while($suboneAcRow = $subaccObj->fetch(PDO::FETCH_OBJ)){
						$accountsIds[] = $suboneAcRow->accounts_id;
					}
					$accIdSql = " IN (".implode(', ', $accountsIds).")";
				}
				$company_subdomain = $oneAcRow->company_subdomain;
				//$tableName = 'category';
				//Showing rows 0 - 24 (19172 total, Query took 0.0005 seconds.) After Remove Showing rows 0 - 24 (18803 total, Query took 0.0005 seconds.)
				//$tableName = 'customer_type';
				//Showing rows 0 - 24 (2252 total, Query took 0.0003 seconds.)  After Remove Showing rows 0 - 24 (2252 total, Query took 0.0003 seconds.)
				$tableName = 'expense_type';
				//Showing rows 0 - 24 (1668 total, Query took 0.0004 seconds.) After Remove Showing rows 0 - 24 (1661 total, Query took 0.0003 seconds.)
				$tableName = 'manufacturer';
				//Showing rows 0 - 24 (24776 total, Query took 0.0003 seconds.) After Remove Showing rows 0 - 24 (24776 total, Query took 0.0004 seconds.)
				$tableName = 'repair_problems';
				//Showing rows 0 - 24 (50181 total, Query took 0.0006 seconds.) After Remove Showing rows 0 - 24 (49373 total, Query took 0.0005 seconds.)
				$tableName = 'vendors';
				//Showing rows 0 - 24 (3975 total, Query took 0.0003 seconds.) After Remove Showing rows 0 - 24 (3971 total, Query took 0.0004 seconds.)
				
				$tableIDName = $tableName.'_id';
				$tableNameField = 'name';
				if($tableName=='category'){$tableNameField = 'category_name';}
				$tablePubName = $tableName.'_publish';
				$tableSql = "SELECT COUNT($tableIDName) AS DupName, $tableNameField AS name FROM $tableName WHERE accounts_id = $accounts_id GROUP BY UPPER(TRIM($tableNameField)) HAVING DupName>1 ORDER BY UPPER(TRIM($tableNameField)) ASC";
				$tableObj = $this->db->query($tableSql, array());
				if($tableObj){
					$accountsInfo = "AccId: $accounts_id ($company_subdomain)";
					$prevName = '';
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$DupName = $oneRow->DupName;
						$gname = addslashes(stripslashes($oneRow->name));
						$accountsInfo .= "<br>&emsp;&emsp;-------------------------------------------------------------------------------<br>
						&emsp;&emsp;Table Name: $tableName, &emsp;&emsp; Name: |$gname|&emsp;&emsp;&emsp;&emsp;Duplicate Count: $DupName";
						
						$tableGroupObj = $this->db->query("SELECT $tableIDName, $tableNameField AS name, $tablePubName FROM $tableName WHERE accounts_id = $accounts_id AND UPPER(TRIM($tableNameField)) = :name", array('name'=>strtoupper($gname)));
						if($tableGroupObj){
							$l = $firstTableIdVal = $propCount = 0;
							while($oneGroupRow = $tableGroupObj->fetch(PDO::FETCH_OBJ)){
								$l++;
								$idVal = $oneGroupRow->$tableIDName;
								$name = $oneGroupRow->name;
								$tablePubVal = $oneGroupRow->$tablePubName;
								
								if($l==1){
									$firstTableIdVal = $idVal;
									$accountsInfo .= "<br>
									&emsp;&emsp;&emsp;&emsp; First Row = ID: $idVal, Name: $name, Publish: $tablePubVal";
									if($tablePubVal==0){
										//$this->db->update($tableName, array($tablePubName=>1, $tableNameField=>$gname), $idVal);
									}
								}
								else{
									$accountsInfo .= "<br>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; Deleted Row = ID: $idVal, Name: $name, Publish: $tablePubVal";
									
									//$this->db->delete($tableName, $tableIDName, $idVal);
									
									$totalDeleteCount++;
									if(in_array($tableName, array('manufacturer', 'category')) && $firstTableIdVal>0){
										$prodObj = $this->db->query("SELECT product_id FROM product WHERE accounts_id = $accounts_id AND $tableIDName = $idVal", array());
										if($prodObj){
											while($oneProdRow = $prodObj->fetch(PDO::FETCH_OBJ)){
												//$this->db->update('product', array($tableIDName=>$firstTableIdVal), $oneProdRow->product_id);
												$propCount++;
											}
										}
									}									
									elseif($tableName=='repair_problems' && $firstTableIdVal>0){
										$repSql = "SELECT repairs_id FROM repairs WHERE accounts_id $accIdSql AND UPPER(problem) = :problem";										
										$prodObj = $this->db->query($repSql, array('problem'=>strtoupper($name)));
										if($prodObj){
											while($oneProdRow = $prodObj->fetch(PDO::FETCH_OBJ)){
												$this->db->update('repairs', array('problem'=>$gname), $oneProdRow->repairs_id);
												$propCount++;
											}
										}
									}									
									elseif(in_array($tableName, array('vendors', 'expense_type')) && $firstTableIdVal>0){
										$sqlUpdate = "SELECT expenses_id FROM expenses WHERE accounts_id $accIdSql AND ";
										$bindData = array();
										if($tableName=='vendors'){
											$sqlUpdate .= "$tableIDName = :idVal";
											$bindData['idVal'] = $idVal;
											$updatedData = array($tableIDName=>$firstTableIdVal);
										}
										else{
											$sqlUpdate .= "UPPER(expense_type) = :name";
											$bindData['name'] = strtoupper($name);
											$updatedData = array('expense_type'=>$gname);
										}
										$prodObj = $this->db->query($sqlUpdate, $bindData);
										if($prodObj){
											while($oneProdRow = $prodObj->fetch(PDO::FETCH_OBJ)){
												//$this->db->update('expenses', $updatedData, $oneProdRow->expenses_id);
												$propCount++;
											}
										}
									}
									elseif($tableName=='customer_type' && $firstTableIdVal>0){
										$prodObj = $this->db->query("SELECT customers_id FROM customers WHERE accounts_id = $accounts_id AND UPPER(customer_type) = '".strtoupper($name)."'", array());
										if($prodObj){
											while($oneProdRow = $prodObj->fetch(PDO::FETCH_OBJ)){
												$this->db->update('customers', array('customer_type'=>$gname), $oneProdRow->customers_id);
												$propCount++;
											}
										}
									}
								}								
							}
							
							$accountsInfo .= "<br>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; Update Count [$propCount]";
						}
					}
					$returnMsg[] = $accountsInfo;
				}
			}
		}
		$returnMsg[] = "<br>=====================================<br><strong>Total $totalDeleteCount rows deleted & ".count($returnMsg)." Accounts Data found.</strong>";
		echo implode('<br>', $returnMsg);
	}
	
	//=========================End=======================//
	function createDataOnPo(){
		$prod_cat_man = 8753;
		$accounts_id = 8756;
		$queryObj = $this->db->query("SELECT location_of FROM accounts WHERE accounts_id = $accounts_id AND location_of>0", array());
		if($queryObj){
			$prod_cat_man = $queryObj->fetch(PDO::FETCH_OBJ)->location_of;
		}
		$po_id = 509734;
		$sql = "SELECT p.*, i.ave_cost, i.current_inventory FROM product p, inventory i WHERE p.accounts_id = $prod_cat_man AND i.accounts_id = $accounts_id AND p.product_publish = 1 AND p.product_id = i.product_id AND i.current_inventory>0";
		$sql .= " AND p.product_type LIKE 'Standard'";
		$sql .= " GROUP BY p.product_id ORDER BY p.product_id ASC";
		$query = $this->db->querypagination($sql, array());
		if($query){
			$l = 0;
			foreach($query as $productonetow){
				$product_id = $productonetow['product_id'];
				$product_type = $productonetow['product_type'];
				$product_type = $productonetow['product_type'];
				$created_on = $productonetow['created_on'];
				$user_id = $productonetow['user_id'];
				$cost = $productonetow['ave_cost'];
				$current_inventory = $productonetow['current_inventory'];

				$item_type = 'product';
				if($product_type=='Mobile Devices'){
					$item_type = 'cellphones';
				}

				$po_items_id = 0;
				$sql1 = "SELECT poi.po_items_id FROM po, po_items poi WHERE po.accounts_id = $accounts_id AND poi.product_id = $product_id AND poi.po_id = po.po_id ORDER BY poi.po_items_id ASC LIMIT 0,1";
				$query1 = $this->db->querypagination($sql1, array());
				if($query1){
					foreach($query1 as $po_itemsrow){
						$po_items_id = $po_itemsrow['po_items_id'];
					}
				}

				if($po_items_id==0){
					$l++;

					$item_type = $this->db->checkCharLen('po_items.item_type', $item_type);
					$qty = $current_inventory;
					$poiData = array('created_on'=>$created_on,
									'user_id'=>$user_id,
									'po_id'=>$po_id,
									'product_id'=>$product_id,
									'item_type'=>$item_type,
									'cost'=>round($cost,2),
									'ordered_qty'=>$qty,
									'received_qty'=>$qty);
					/*
					$po_items_id = $this->db->insert('po_items', $poiData);
					if($po_items_id){
						$action = 'Add';
					}
					*/
					echo "<p>$l. ".json_encode($poiData)."</p>";
				}
			}
		}
	}

	public function checkOneTimeProd(){
		$i = $j = $a = $b = 0;
		$onePCObj = $this->db->query("SELECT pos.accounts_id, pos.user_id, pos.sales_datetime, pc.pos_cart_id, pc.qty, pc.shipping_qty, pc.ave_cost FROM pos, pos_cart pc WHERE pc.item_type = 'one_time' AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) AND pos.pos_id = pc.pos_id ORDER BY pos.pos_id ASC LIMIT 0, 10000", array());
		if($onePCObj){
			$missingOneTimePOs = $accountsUserId = array();
			while($oneRow = $onePCObj->fetch(PDO::FETCH_OBJ)){
			
				$accounts_id = $oneRow->accounts_id;
				$pos_cart_id = $oneRow->pos_cart_id;
				$po_items_id = 0;
				
				$poSql = "SELECT po_items.po_items_id FROM po, po_items WHERE po.accounts_id = $accounts_id AND po_items.product_id = $pos_cart_id AND po_items.item_type = 'one_time' AND po.po_id = po_items.po_id GROUP BY po_items.po_items_id ORDER BY po_items.po_items_id ASC";
				$poData = $this->db->querypagination($poSql, array());
				if($poData){
					foreach($poData as $poRow){
						$po_items_id = $poRow['po_items_id'];
						$j++;
					}
				}
				if($po_items_id==0){
					$accountsUserId[$accounts_id] = $oneRow->user_id;
					$missingOneTimePOs[$accounts_id][$pos_cart_id] = array($oneRow->qty, $oneRow->shipping_qty, $oneRow->ave_cost, $oneRow->sales_datetime);
				}
				$i++;
			}

			if(!empty($missingOneTimePOs)){
				foreach($missingOneTimePOs as $accounts_id=>$posCartIDInfo){
					$user_id = $accountsUserId[$accounts_id]??0;
					$prod_cat_man = $accounts_id;
					//===================Check prod_cat_man================//
					$queryObj = $this->db->query("SELECT location_of FROM accounts WHERE accounts_id = $accounts_id AND location_of>0", array());
					if($queryObj){
						$prod_cat_man = $queryObj->fetch(PDO::FETCH_OBJ)->location_of;
					}

					//===================Check & create supplier================//
					$supplier_id = 0;
					$queryObj = $this->db->query("SELECT suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company LIKE 'Unknown'", array());
					if($queryObj){
						$supplier_id = $queryObj->fetch(PDO::FETCH_OBJ)->suppliers_id;
					}
					
					if($supplier_id == 0){
						$company = $this->db->checkCharLen('suppliers.company', 'Unknown'); 
						$suppliersdata = array(	'suppliers_publish'=>0,
												'created_on'=>date('Y-m-d H:i:s'),
												'last_updated'=>date('Y-m-d H:i:s'),
												'accounts_id'=>$prod_cat_man,
												'user_id'=>$user_id,
												'first_name'=>'',
												'last_name'=>'',
												'email'=>'',
												'company'=>$company,
												'contact_no'=>'',
												'secondary_phone'=>'',
												'fax'=>'',
												'shipping_address_one'=>'',
												'shipping_address_two'=>'',
												'shipping_city'=>'',
												'shipping_state'=>'',
												'shipping_zip'=>'',
												'shipping_country'=>'',
												'offers_email'=>0,
												'website'=>'');
						$supplier_id = $this->db->insert('suppliers', $suppliersdata);												
					}
					
					//===================Create New PO================//
					foreach($posCartIDInfo as $pos_cart_id=>$poCartInfo){
						$qty = $poCartInfo[0];
						$shipping_qty = $poCartInfo[1];
						$ave_cost = $poCartInfo[2];
						$sales_datetime = $poCartInfo[3];

						//===================create PO================//
						$po_number = 1;
						$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
						if($poObj){
							$po_number = $poObj[0]['po_number']+1;
						}
						$poData = array();
						$poData['supplier_id'] = intval($supplier_id);
						$poData['user_id'] = $user_id;
						$poData['po_datetime'] = $sales_datetime;
						$poData['last_updated'] = $sales_datetime;
						$poData['po_number'] = $po_number;
						$poData['lot_ref_no'] = '';
						$poData['paid_by'] = '';
						$poData['date_expected'] = date('Y-m-d', strtotime($sales_datetime));
						$poData['return_po'] = 0;
						$poData['status'] = 'Closed';
						$poData['accounts_id'] = $accounts_id;
						$poData['tax_is_percent'] = 0;
						$poData['taxes'] = 0.000;
						$poData['shipping'] = 0.00;
						$poData['suppliers_invoice_no'] = '';
						$poData['invoice_date'] = date('Y-m-d');
						$poData['date_paid'] = date('Y-m-d');
						$poData['transfer'] = 0;
						$po_id = $this->db->insert('po', $poData); 
						if($po_id){
							//===================create PO Item================//
							$poiData = array('created_on'=>$sales_datetime,
												'user_id'=>$user_id,
												'po_id'=>$po_id,
												'product_id'=>$pos_cart_id,
												'item_type'=>'one_time',
												'cost'=>round($ave_cost,2),
												'ordered_qty'=>$qty,
												'received_qty'=>$qty);
							$po_items_id = $this->db->insert('po_items', $poiData);
							if($po_items_id){
								$b++;
								if($qty !=$shipping_qty){
									$this->db->update('pos_cart', array('shipping_qty'=>$qty), $pos_cart_id);
									$a++;
								}
							}
						}
					}
				}
			}
		}
		echo "<br><strong>Total $j/$i Data found in PO Items. So missing ".($i-$j)." PO items.   $b PO created.  $a POS Cart Updated </strong>";
	}
	
	function checkSubLocCurrency(){
		$Common = new Common($this->db);
		$sql = "SELECT accounts_id FROM accounts WHERE location_of = 0 ORDER BY accounts_id ASC";
		$accObj = $this->db->query($sql, array());
		if($accObj){
			while($oneAcRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$location_of = $oneAcRow->accounts_id;
				$vData = $Common->variablesData('account_setup', $location_of);
				if(!empty($vData) && array_key_exists('currency', $vData)){
					$pCurrency = $vData['currency'];
					if(!empty($pCurrency)){
						//=====================Sub Location Search===================//
						$subsql = "SELECT accounts_id FROM accounts WHERE location_of = $location_of ORDER BY accounts_id ASC";
						$subaccObj = $this->db->query($subsql, array());
						if($subaccObj){
							while($suboneAcRow = $subaccObj->fetch(PDO::FETCH_OBJ)){
								$accounts_id = $suboneAcRow->accounts_id;
								$vData = $Common->variablesData('account_setup', $accounts_id);
								if(!empty($vData) && array_key_exists('currency', $vData)){
									$currency = $vData['currency'];
									if(!empty($currency) && $pCurrency != $currency){										
										echo $location_of.'|'.$pCurrency.' != '.$accounts_id.'|'.$currency.'|<br>';
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function posStatusUpdate(){
		$i = 0;
		$sql = "SELECT pos_id, order_status FROM pos WHERE pos_type = 'Order' AND status = '' ORDER BY accounts_id ASC, pos_id ASC LIMIT 0, 100000";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$status = 'New';
				if($oneRow->order_status==2){
					$status = 'Invoiced';
				}
				$this->db->update('pos', array('pos_publish'=>1, 'status'=>$status), $oneRow->pos_id);
				$i++;
			}
		}
		echo "<br><strong>Total $i Data updated.</strong>";
	}
	
	public function canceledOrderUpdate(){
		$i = 0;
		$sql = "SELECT pos_id, accounts_id, user_id, invoice_no FROM pos WHERE pos_type = 'Order' AND order_status = 0 ORDER BY accounts_id ASC, pos_id ASC";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$this->db->update('pos', array('pos_publish'=>1, 'order_status'=>2, 'status'=>'Canceled'), $oneRow->pos_id);
				echo "<p>ACCID: $oneRow->accounts_id, invoiceNo: $oneRow->invoice_no</p>";
				
				$changed = array($this->db->translate('Order has cancelled.')=>'');
				$moreInfo = $teData = array();
				$teData['created_on'] = date('Y-m-d H:i:s');
				$teData['accounts_id'] = $oneRow->accounts_id;
				$teData['user_id'] = $oneRow->user_id;
				$teData['record_for'] = 'pos';
				$teData['record_id'] = $oneRow->pos_id;
				$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
				$this->db->insert('track_edits', $teData);
				
				$i++;
			}
		}
		echo "<br><strong>Total $i Data found.</strong>";
	}
	
	public function prodManUpdate(){
		$i = 0;
		$lastAccId = 0;
		$sql = "SELECT p.product_id, m.manufacturer_id, p.accounts_id FROM product p, manufacturer m WHERE p.manufacture = m.name AND p.manufacturer_id = 0 AND p.accounts_id = m.accounts_id ORDER BY p.accounts_id ASC, p.product_id ASC LIMIT 0, 20000";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$this->db->update('product', array('manufacturer_id'=>$oneRow->manufacturer_id), $oneRow->product_id);
				$i++;
				$lastAccId = $oneRow->accounts_id;
			}
		}
		echo "<br>==============Last Account Id: $lastAccId=======================<br><strong>Total $i Data found.</strong>";
	}
	
	public function commiManUpdate(){
		$i = 0;
		$lastAccId = 0;

		$sql = "SELECT accounts_id, location_of FROM accounts ORDER BY accounts_id ASC";
		$accObj = $this->db->query($sql, array());
		if($accObj){
			while($oneAcRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneAcRow->accounts_id;				
				$location_of = $oneAcRow->location_of;				
				if($location_of==0){$location_of = $accounts_id;}
				
				$sql = "SELECT c.commissions_id, m.manufacturer_id, c.accounts_id FROM commissions c, manufacturer m WHERE m.accounts_id = $location_of AND c.accounts_id = $accounts_id AND c.rule_field = 'Manufacturer' AND c.rule_match = m.name ORDER BY c.accounts_id ASC, c.commissions_id ASC LIMIT 0, 50000";
				$tableObj = $this->db->query($sql, array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$this->db->update('commissions', array('rule_match'=>$oneRow->manufacturer_id), $oneRow->commissions_id);
						$i++;
						$lastAccId = $oneRow->accounts_id;
					}
				}
			}
		}
		
		echo "<br>==============Last Account Id: $lastAccId=======================<br><strong>Total $i Data found.</strong>";
	}
	
	public function stManUpdate(){
		$i = 0;
		$lastAccId = 0;
		
		$sql = "SELECT accounts_id, location_of FROM accounts ORDER BY accounts_id ASC";
		$accObj = $this->db->query($sql, array());
		if($accObj){
			while($oneAcRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneAcRow->accounts_id;				
				$location_of = $oneAcRow->location_of;				
				if($location_of==0){$location_of = $accounts_id;}
				
				$sql = "SELECT ST.stock_take_id, m.manufacturer_id FROM stock_take ST, manufacturer m WHERE ST.accounts_id = $accounts_id AND m.accounts_id = $location_of AND ST.manufacture = m.name AND ST.manufacturer_id = 0 ORDER BY ST.stock_take_id ASC";
				$tableObj = $this->db->query($sql, array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$this->db->update('stock_take', array('manufacturer_id'=>$oneRow->manufacturer_id), $oneRow->stock_take_id);
						$i++;
					}
				}
				$lastAccId = $accounts_id;
			}
		}	
		echo "<br>==============Last Account Id: $lastAccId=======================<br><strong>Total $i Data found.</strong>";
	}
	//========New Function End Information=======//
	
	public function duplManuCheck(){
		$returnMsg = array();
		$sql = "SELECT accounts_id, company_subdomain FROM accounts WHERE location_of=0 ORDER BY accounts_id ASC";
		$accObj = $this->db->query($sql, array());
		if($accObj){
			while($oneAcRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneAcRow->accounts_id;				
				$company_subdomain = $oneAcRow->company_subdomain;
				
				$sql = "SELECT COUNT(manufacturer_id) AS DupMan, name FROM manufacturer WHERE accounts_id = $accounts_id GROUP BY UPPER(TRIM(name)) HAVING DupMan>1 ORDER BY UPPER(TRIM(name)) ASC";
				$manObj = $this->db->query($sql, array());
				if($manObj){
					$accountsInfo = "AccId: $accounts_id ($company_subdomain)";
					$prevName = '';
					while($oneRow = $manObj->fetch(PDO::FETCH_OBJ)){
						$DupMan = $oneRow->DupMan;
						$gname = $oneRow->name;
						$accountsInfo .= "<br>&emsp;&emsp;Name: |$gname|&emsp;&emsp;&emsp;&emsp;Duplicate Count: $DupMan";
						
						$mObj = $this->db->query("SELECT manufacturer_id, name, manufacturer_publish FROM manufacturer WHERE accounts_id = $accounts_id AND UPPER(TRIM(name)) = :name", array('name'=>strtoupper($gname)));
						if($mObj){
							$l=0;
							
							while($oneMRow = $mObj->fetch(PDO::FETCH_OBJ)){
								$l++;
								$manufacturer_id = $oneMRow->manufacturer_id;
								$name = $oneMRow->name;
								$manufacturer_publish = $oneMRow->manufacturer_publish;
								$propCount = 0;
								if($l==1){
									$this->db->update('manufacturer', array('manufacturer_publish'=>1, 'name'=>$gname), $manufacturer_id);
								}
								else{
									$this->db->delete('manufacturer', 'manufacturer_id', $manufacturer_id);
								}								
							}
													
							$prodObj = $this->db->query("SELECT product_id FROM product WHERE accounts_id = $accounts_id AND UPPER(TRIM(manufacture)) = :name", array('name'=>strtoupper($gname)));
							if($prodObj){
								while($oneProdRow = $prodObj->fetch(PDO::FETCH_OBJ)){
									$this->db->update('product', array('manufacture'=>$gname), $oneProdRow->product_id);
									$propCount++;
								}
							}								
							$accountsInfo .= "<br>&emsp;&emsp;&emsp; Update Man: $gname &emsp;&emsp;&emsp; [$propCount]";
						}
					}
					$returnMsg[] = $accountsInfo;
				}
			}
		}
		$returnMsg[] = "<br>=====================================<br><strong>Total ".count($returnMsg)." Accounts Data found.</strong>";
		echo implode('<br>', $returnMsg);
	}
	
	public function archProdInvAdj(){
		$returnMsg = array();
		$sql = "SELECT i.accounts_id, a.company_subdomain, i.product_id, p.manage_inventory_count, p.product_name, p.sku, p.product_type, p.user_id, i.inventory_id, i.current_inventory FROM accounts a, product p, inventory i WHERE p.product_publish = 0 AND product_type = 'Standard' AND i.current_inventory<0 AND a.accounts_id = i.accounts_id AND p.product_id = i.product_id ORDER BY a.accounts_id ASC, p.product_id ASC LIMIT 0, 2000";
		//$returnMsg[] = $sql;
		$accObj = $this->db->query($sql, array());
		if($accObj){
			$Common = new Common($this->db);
			while($oneAcRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneAcRow->accounts_id;
				$company_subdomain = $oneAcRow->company_subdomain;
				$product_id = $oneAcRow->product_id;
				$product_name = $oneAcRow->product_name;
				$sku = $oneAcRow->sku;
				$manage_inventory_count = $oneAcRow->manage_inventory_count;
				$product_type = $oneAcRow->product_type;
				$user_id = $oneAcRow->user_id;
				if($manage_inventory_count==1){
					$prodAvgCostData = $Common->productAvgCost($accounts_id, $product_id, 1, 1);
					$current_inventory = $prodAvgCostData[2];
				}
				else{
					$current_inventory = $oneAcRow->current_inventory;
				}
				$inventory_id = $oneAcRow->inventory_id;
				
				if($current_inventory<0){
					
					$returnMsg[] = "AccId: $accounts_id, sub-domain: $company_subdomain, ProdId: $product_id, MIC: $manage_inventory_count, product_name: $product_name, sku: $sku, current_inventory: $current_inventory";
					
					$current_inventory = $current_inventory*-1;
					
					$note_for = $this->db->checkCharLen('notes.note_for', 'product');
					$noteData=array('table_id'=> $product_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $accounts_id,
									'user_id'=> $user_id,
									'note'=> "$current_inventory inventory has been adjusted $sku",
									'publics'=>0);
					$this->db->insert('notes', $noteData);
					$this->db->update('inventory', array('current_inventory'=>0), $inventory_id);
					
				}
			}
		}
		$returnMsg[] = "<br>=====================================<br><strong>Total ".count($returnMsg)." Accounts Data found.</strong>";
		echo implode('<br>', $returnMsg);
	}
	
	function transNoteToEditTrack($updateOrNot){
		$NC = 0;
		$accounts_id = 0;
		$notesObj = $this->db->query("SELECT * FROM notes WHERE note_for = 'repairs' AND note LIKE CONCAT('%', :oneString, '%') ORDER BY accounts_id ASC LIMIT 0,10000", array('oneString'=>'Changed Status to'));
		if($notesObj){
			while($noteData = $notesObj->fetch(PDO::FETCH_OBJ)){
				$NC++;
				$notes_id = $noteData->notes_id;
				$changed = array();
				$changed[$noteData->note] = '';
				$moreInfo = $teData = array();
				$teData['created_on'] = $noteData->created_on;
				$teData['accounts_id'] = $accounts_id = $noteData->accounts_id;
				$teData['user_id'] = $noteData->user_id;
				$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'repairs');
				$teData['record_id'] = $noteData->table_id;
				$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
				$this->db->insert('track_edits', $teData);
				
				$this->db->delete('notes','notes_id', $notes_id);
			}				
		}
		$dataStr = '';//json_encode($noteIds);
		
		return "Total Accounts found: $NC< Last AccId $accounts_id<br><br>==============================================
		<br>$dataStr";
	}
		
	public function checkRemoveAcc(){
		$Admin = new Admin($this->db);
		$returnMsg = array();
		$oneYearAgo = date('Y-m-d H:i:s', mktime(0,0,0, date('m'), date('d'), date('Y')-1));
		$sql1Year = "SELECT accounts_id, company_name, company_subdomain, status, location_of, last_login FROM accounts WHERE status = 'CANCELED' AND last_login < '$oneYearAgo' AND (location_of>0 OR (location_of=0 AND accounts_id NOT IN (SELECT location_of FROM accounts WHERE location_of >0))) ORDER BY accounts_id ASC LIMIT 0, 50";
		$returnMsg[] = $sql1Year;
		$accObj = $this->db->query($sql1Year, array());
		if($accObj){
			while($oneAcRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$returnData = $Admin->AJremove_Accounts($oneAcRow->accounts_id, 'Yes');
				$returnData = (array) json_decode($returnData);
				$dtcount = $returnData['dtcount']??0;
				$returnMsg[] = "Account ID: $oneAcRow->accounts_id, Company Name: $oneAcRow->company_name, Sub-domain: $oneAcRow->company_subdomain, Last Login: $oneAcRow->last_login, Removed $dtcount data";
			}
		}
		$returnMsg[] = "<br>=====================================<br><strong>Total ".count($returnMsg)." Accounts Data found.</strong>";
		echo implode('<br>', $returnMsg);
	}
	
	public function canOrExpAccInstCheck(){
		$returnMsg = array();
		//(ins.website_on = 1 OR ins.enable_widget = 1)
		$sql = "SELECT acc.accounts_id, acc.company_subdomain, acc.created_on, acc.trial_days, ins.instance_home_id, acc.status FROM accounts acc, instance_home ins WHERE acc.status IN ('Trial', 'CANCELED') AND ins.website_on = 1 AND ins.accounts_id= acc.accounts_id ORDER BY accounts_id ASC";
		//$returnMsg[] = $sql;
		$accObj = $this->db->query($sql, array());
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
					$returnMsg[] = "AccId: $accounts_id, sub-domain: $company_subdomain, $DaysRemaining = $trial_days-$registeredDays";
					$this->db->update('instance_home', array('website_on'=>0), $oneAcRow->instance_home_id);
				}
			}
		}
		$returnMsg[] = "<br>=====================================<br><strong>Total ".count($returnMsg)." Accounts Data found.</strong>";
		echo implode('<br>', $returnMsg);
	}
	
	public function itemPOReturn(){
		$sql = "SELECT a.accounts_id, a.company_subdomain, po.po_number, poi.product_id, poi.cost, poci.item_id, poi.po_items_id, poci.po_items_id AS oriPOItemId FROM accounts a, po, po_items poi, po_cart_item poci WHERE poi.received_qty <0 AND poi.cost = 0 AND a.accounts_id = po.accounts_id AND po.po_id = poi.po_id AND poi.po_items_id = poci.return_po_items_id ORDER BY a.accounts_id ASC, po.po_id ASC, poi.po_items_id ASC";
		$accObj = $this->db->query($sql, array());
		if($accObj){
			$returnMsg = array();
			$totalAccCount = 0;
			while($oneRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$oriPOItemId = $oneRow->oriPOItemId;
				$OriCost = 0;
				$OriCostObj = $this->db->query("SELECT cost FROM po_items WHERE po_items_id = $oriPOItemId", array(),1);
				if($OriCostObj){
					$OriCost = $OriCostObj->fetch(PDO::FETCH_OBJ)->cost;
				}
				if($OriCost>0){
					$po_number = "<a target=\"_blank\" href=\"//$oneRow->company_subdomain.".OUR_DOMAINNAME."/Purchase_orders/edit/$oneRow->po_number\">po$oneRow->po_number</a>";
					$totalAccCount++;
					$returnMsg[] = "Accounts ID: $oneRow->accounts_id, PO No.: $po_number, ProdID: $oneRow->product_id, IMEIID: $oneRow->item_id, Refund::PO ItemID: $oneRow->po_items_id, Cost: $oneRow->cost, Original::PO ItemID: $oriPOItemId, Cost: $OriCost";
					if($totalAccCount<3)
						$this->db->update('po_items', array('cost'=>$OriCost), $oneRow->po_items_id);
				}
			}
			$returnMsg[] = "<br>=====================================<br><strong>Total $totalAccCount Accounts found.</strong>";
			echo implode('<br>', $returnMsg);
		}
	}
	
	function checkDripEmail(){
		$message = '';
		$helpSql = "SELECT * FROM help WHERE video_or_faq=2 ORDER BY showing_order ASC";
		$helpObj = $this->db->query($helpSql, array());
		if($helpObj){
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $this->db->supportEmail('Host');
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = $this->db->supportEmail('Username');
			$mail->Password = $this->db->supportEmail('Password');
			
			while($oneRow = $helpObj->fetch(PDO::FETCH_OBJ)){
				$subject = $oneRow->title;
				$mailbody = nl2br(stripslashes(trim((string) $oneRow->sub_text)));
				$registrationDays = $oneRow->showing_order;
				
				$registrationDate = date('Y-m-d', strtotime("-$registrationDays day"));
				$sql = "SELECT a.accounts_id, u.user_first_name, u.user_last_name, u.user_email, a.company_name, a.company_subdomain FROM accounts a, user u WHERE a.status = 'Trial' AND substring(a.created_on,1,10) = '$registrationDate' AND a.accounts_id = u.accounts_id AND u.is_admin = 1 AND a.location_of = 0 ORDER BY a.accounts_id ASC";
				$queryObj = $this->db->query($sql, array());
				if($queryObj){
					while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
						$accounts_id = $oneRow->accounts_id;
						$name = stripslashes(trim("$oneRow->user_first_name $oneRow->user_last_name"));
						$email = stripslashes(trim((string) $oneRow->user_email));
						$company = stripslashes(trim((string) $oneRow->company_name));
						$subdomain = stripslashes(trim((string) $oneRow->company_subdomain));
						
						$updateSubject = str_replace('{{name}}', $name, $subject);
						$updateSubject = str_replace('{{email}}', $email, $updateSubject);
						$updateSubject = str_replace('{{company}}', $company, $updateSubject);
						$updateSubject = str_replace('{{sub-domain}}', $subdomain, $updateSubject);
						
						$updateMailbody = str_replace('{{name}}', $name, $mailbody);
						$updateMailbody = str_replace('{{email}}', $email, $updateMailbody);
						$updateMailbody = str_replace('{{company}}', $company, $updateMailbody);
						$updateMailbody = str_replace('{{sub-domain}}', $subdomain, $updateMailbody);
						
						//=========Mailing Start============//
						$mail->addReplyTo($this->db->supportEmail('support'), COMPANYNAME);
						$mail->setFrom($this->db->supportEmail('info'), COMPANYNAME);				
						$mail->ClearAllRecipients();
						$mail->addAddress('mdshobhancse@gmail.com', $email.' :: '.$name);
						$mail->addBCC('den.romano@gmail.com');
						$mail->Subject = $updateSubject;
						$mail->isHTML(true);
						$mail->CharSet = 'UTF-8';
						
						//Build a simple message body
						$mail->Body = '<!DOCTYPE html>
		<html>
		<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>'.$subject.'</title>
		</head>
		<body>
		<p>'.$updateMailbody.'</p>
		</body>
		</html>';
						$mail->send();
						
					}
				}
			}
		}
		return $message;
	}
	
	public function checkNegPeniPay(){
		$sql = "SELECT a.accounts_id, a.company_subdomain, p.invoice_no, pp.payment_amount FROM accounts a, pos p, pos_payment pp WHERE pp.payment_amount <0 AND pp.payment_method = 'Cash' AND a.accounts_id = p.accounts_id AND p.pos_id = pp.pos_id ORDER BY a.accounts_id ASC, p.pos_id ASC, pp.pos_payment_id ASC";
		$accObj = $this->db->query($sql, array());
		if($accObj){
			$returnMsg = array();
			$totalAccCount = 0;
			while($oneRow = $accObj->fetch(PDO::FETCH_OBJ)){
				
				$po_number = "<a target=\"_blank\" href=\"//$oneRow->company_subdomain.".OUR_DOMAINNAME."/Invoices/view/$oneRow->invoice_no\">$oneRow->invoice_no</a>";
				if(round($oneRow->payment_amount)==0){
					$totalAccCount++;
					$returnMsg[] = "Accounts ID: $oneRow->accounts_id, Invoice No.: $po_number, Amount: $oneRow->payment_amount";
				}
			}
			$returnMsg[] = "<br>=====================================<br><strong>Total $totalAccCount Accounts found.</strong>";
			echo implode('<br>', $returnMsg);
		}
	}
	
	public function missingIMEIsPO(){
		$tableRowsStr = "";
		$dataCount = 0;
		$missingIMEIs = array();
		$itemObj = $this->db->query("SELECT i.accounts_id, i.user_id, i.product_id, i.item_id, pos.sales_datetime FROM pos, pos_cart pc, pos_cart_item pci, item i WHERE i.item_id NOT IN (SELECT item_id FROM po_cart_item) AND pos.pos_id = pc.pos_id AND pc.pos_cart_id = pci.pos_cart_id AND i.item_id = pci.item_id GROUP BY i.item_id ORDER BY i.accounts_id ASC, pos.sales_datetime ASC LIMIT 0, 10", array());
		if($itemObj){
			while($oneRow = $itemObj->fetch(PDO::FETCH_OBJ)){
				$dataCount++;
				$missingIMEIs[$oneRow->accounts_id][$oneRow->sales_datetime][$oneRow->product_id][$oneRow->item_id] = $oneRow->user_id;
			}
		}
		
		if(!empty($missingIMEIs)){
			foreach($missingIMEIs as $accounts_id=>$itemDateData){
				
				$tableRowsStr .= "//===========AccountsID: $accounts_id===========//<br>";
				
				$prod_cat_man = $accounts_id;
				$accountsObj = $this->db->query("SELECT location_of FROM accounts WHERE accounts_id = $accounts_id", array(),1);
				if($accountsObj){
					$location_of = $accountsObj->fetch(PDO::FETCH_OBJ)->location_of;
					if($location_of>0){
						$prod_cat_man = $location_of;
					}
				}				
				
				$user_id = 0;
				$userObj = $this->db->query("SELECT user_id FROM user WHERE accounts_id = $accounts_id AND is_admin=1 ORDER BY user_id ASC", array(),1);
				if($userObj){
					$user_id = $userObj->fetch(PDO::FETCH_OBJ)->user_id;
				}
				
				ksort($itemDateData);
				$po_datetime = date('Y-m-d H:i:s', strtotime(key($itemDateData))-172800);
				
				$po_id = 0;
				$lot_ref_no = 'UNKNOWN-PO for missing IMEIs';
				$poObj = $this->db->query("SELECT po_id, po_datetime FROM po WHERE accounts_id = $accounts_id AND lot_ref_no = '$lot_ref_no'", array());
				if($poObj){
					$poOneRow = $poObj->fetch(PDO::FETCH_OBJ);
					$po_id = $poOneRow->po_id;
					if($po_datetime>$poOneRow->po_datetime){
						$po_datetime = $poOneRow->po_datetime;
						$this->db->update('po', array('po_datetime'=>$po_datetime), $po_id);
					}
				}
				$created_on = date('Y-m-d H:i:s', strtotime($po_datetime)+86400);
				
				if($po_id==0){
					$suppliers_id = 0;
					$company = $this->db->checkCharLen('suppliers.company', 'UNKNOWN');
					$supplierObj = $this->db->query("SELECT suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company = '$company'", array());
					if($supplierObj){
						$suppliers_id = $supplierObj->fetch(PDO::FETCH_OBJ)->suppliers_id;
					}
					
					if($suppliers_id == 0){						
						$suppliersdata = array(	'suppliers_publish'=>0,
												'created_on'=>date('Y-m-d H:i:s'),
												'last_updated'=>date('Y-m-d H:i:s'),
												'accounts_id'=>$prod_cat_man,
												'user_id'=>$user_id,
												'first_name'=>'',
												'last_name'=>'',
												'email'=>'',
												'company'=>$company,
												'contact_no'=>'',
												'secondary_phone'=>'',
												'fax'=>'',
												'shipping_address_one'=>'',
												'shipping_address_two'=>'',
												'shipping_city'=>'',
												'shipping_state'=>'',
												'shipping_zip'=>'',
												'shipping_country'=>'',
												'offers_email'=>0,
												'website'=>'');
						$suppliers_id = $this->db->insert('suppliers', $suppliersdata);
					}
					
					$po_number = 1;
					$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
					if($poObj){
						$po_number = $poObj[0]['po_number']+1;
					}
					
					$poData = array();
					$poData['po_datetime'] = $po_datetime;
					$poData['last_updated'] = date('Y-m-d H:i:s');
					$poData['po_number'] = $po_number;
					$poData['lot_ref_no'] = $lot_ref_no;
					$poData['paid_by'] = '';
					$poData['supplier_id'] = intval($suppliers_id);
					$poData['date_expected'] = date('Y-m-d', strtotime($created_on));
					$poData['return_po'] = 0;
					$poData['status'] = 'Closed';
					$poData['accounts_id'] = $accounts_id;
					$poData['user_id'] = $user_id;
					$poData['tax_is_percent'] = 0;
					$poData['taxes'] = 0.000;
					$poData['shipping'] = 0.00;
					$poData['suppliers_invoice_no'] = '';
					$poData['invoice_date'] = date('Y-m-d', strtotime($po_datetime));
					$poData['date_paid'] = date('Y-m-d', strtotime($created_on));
					$poData['transfer'] = 0;					
					$po_id = $this->db->insert('po', $poData);
					if($po_id){
						$tableRowsStr .= "&emsp; PO #:$po_number,  Created Date: $po_datetime<br>";
					}
				}
				
				if($po_id>0){
					foreach($itemDateData as $date=>$productData){
						foreach($productData as $product_id=>$itemData){
							$po_items_id = 0;
							$poItemObj = $this->db->query("SELECT po_items_id FROM po_items WHERE po_id = $po_id AND product_id = $product_id", array());
							if($poItemObj){
								$po_items_id = $poItemObj->fetch(PDO::FETCH_OBJ)->po_items_id;
							}
							if($po_items_id==0){
								$poiData =array('created_on'=>$po_datetime,
												'user_id'=>$user_id,
												'po_id'=>$po_id,
												'product_id'=>$product_id,
												'item_type'=>'cellphones',
												'ordered_qty'=>0,
												'received_qty'=>0,
												'cost'=>0.00);
								$po_items_id = $this->db->insert('po_items', $poiData);								
								
								$tableRowsStr .= "&emsp; &emsp; PO Item ID: $po_items_id<br>";
							}
							
							if($po_items_id>0){
								foreach($itemData as $item_id=>$user_id){
									$po_cart_item_id = 0;
									$POCIObj = $this->db->query("SELECT po_cart_item_id FROM po_cart_item WHERE po_items_id = $po_items_id AND item_id = $item_id", array());
									if($POCIObj){
										$po_cart_item_id = $POCIObj->fetch(PDO::FETCH_OBJ)->po_cart_item_id;
									}
									if($po_cart_item_id==0){
										$POCIData = array('po_items_id' => $po_items_id,
														'item_id' => $item_id,
														'return_po_items_id' => 0);
										$po_cart_item_id = $this->db->insert('po_cart_item', $POCIData);
										
										$tableRowsStr .= "&emsp; &emsp; &emsp; PO Cart Item ID: $po_cart_item_id<br>";
									}
									$this->db->update('item', array('created_on'=>$created_on), $item_id);
								}
								
								$imeiCount = 0;
								$POCIObj = $this->db->query("SELECT COUNT(po_cart_item_id) AS imeiCount FROM po_cart_item WHERE po_items_id = $po_items_id AND return_po_items_id = 0", array());
								if($POCIObj){
									$imeiCount = $POCIObj->fetch(PDO::FETCH_OBJ)->imeiCount;
								}
								$this->db->update('po_items', array('ordered_qty'=>$imeiCount, 'received_qty'=>$imeiCount), $po_items_id);
							}
						}
					}					
				}
			}
		}
		
		return $tableRowsStr;
	}
	
	public function checkPicUploadTime(){
		
		$accObj = $this->db->query("SELECT accounts_id FROM accounts ORDER BY accounts_id ASC", array());
		if($accObj){
			$returnMsg = array();
			$totalPicCount = 0;
			while($oneRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneRow->accounts_id;
				$filePath = "./assets/accounts/a_$accounts_id/imei_";
				$pics = glob($filePath."*.png");				
				if($pics){
					$l = 0;
					$picInfo = '';
					$lastYearTime = mktime(0,0,0,date('m'), date('d'), date('Y')-1);
					$returnMsg[] = "Check 1 year back date: ".date('Y-m-d', $lastYearTime);					
					foreach($pics as $onePicture){
						if (file_exists($onePicture)){
							$l++;
							$updateTime = filemtime($onePicture);
							$redTxt = '';
							if($updateTime<$lastYearTime){$redTxt = ' style="color:red"';}
							$picInfo .= "<br>&emsp;<span$redTxt>$onePicture was last modified: " . date ("F d Y H:i:s.", $updateTime).' (REMOVED)</span>';
							unlink($onePicture);
						}
					}					
					$totalPicCount += $l;
					$returnMsg[] = "Accounts ID: $accounts_id, Total $l PNG Pictures$picInfo";
				}
			
				$pics = glob($filePath."*.jpg");
				if($pics){
					$l = 0;
					$picInfo = '';
					$lastYearTime = mktime(0,0,0,date('m'), date('d'), date('Y')-1);
					$returnMsg[] = "Check 1 year back date: ".date('Y-m-d', $lastYearTime);					
					foreach($pics as $onePicture){
						if (file_exists($onePicture)){
							$l++;
							$updateTime = filemtime($onePicture);
							$redTxt = '';
							if($updateTime<$lastYearTime){$redTxt = ' style="color:red"';}
							$picInfo .= "<br>&emsp;<span$redTxt>$onePicture was last modified: " . date ("F d Y H:i:s.", $updateTime).' (REMOVED)</span>';
							unlink($onePicture);
						}
					}					
					$totalPicCount += $l;
					$returnMsg[] = "Accounts ID: $accounts_id, Total $l PNG Pictures$picInfo";
				}
			}
			$returnMsg[] = "<br>=====================================<br><strong>Total $totalPicCount pictures found.</strong>";
			echo implode('<br>', $returnMsg);
		}
	}
	
	public function removePNGPictures(){
		
		$accObj = $this->db->query("SELECT accounts_id FROM accounts ORDER BY accounts_id ASC", array());
		if($accObj){
			$returnMsg = array();
			$totalPicCount = $totalJPGfound = 0;
			while($oneRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneRow->accounts_id;
				$filePath = "./assets/accounts/a_$accounts_id/repairs_";
				//$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				//$filePath = "./assets/accounts/a_$accounts_id/web_logo_";
				//$filePath = "./assets/accounts/a_$accounts_id/pagebodyseg1_";
				//$filePath = "./assets/accounts/a_$accounts_id/imei_";
				//$filePath = "./assets/accounts/a_$accounts_id/prod_";
				$pics = glob($filePath."*.png");				
				if($pics){
					$l = $j = $c = 0;
					foreach($pics as $onePicture){
						$l++;
						if(file_exists(str_replace('.png', '.jpg', $onePicture))){
							$j++;
							if (file_exists($onePicture)){
								unlink($onePicture);
							}
						}
						elseif($c<1000){
							
							$c++;
							$picturesrc = str_replace('./', '/', $onePicture);
							$im = new Imagick($_SERVER['DOCUMENT_ROOT'] . $picturesrc);
							$im->optimizeImageLayers(); // Optimize the image layers			
							$im->setImageCompression(Imagick::COMPRESSION_JPEG);// Compression and quality
							$im->setImageCompressionQuality(0);
							$picturesrc = str_replace('.png', '.jpg', $picturesrc);
							$im->writeImages($_SERVER['DOCUMENT_ROOT'] . $picturesrc, true);// Write the image back
						}
					}
					$totalPicCount += $l;
					$totalJPGfound += $j;
					$returnMsg[] = "Accounts ID: $accounts_id, Total $l PNG Pictures & $j JPG Pictures";
				}			
			}
			$returnMsg[] = "<br>=====================================<br><strong>Total $totalPicCount pictures found.</strong>";
			echo implode('<br>', $returnMsg);
		}
	}
		
	function missingIMEIinItem(){
		$tableRowsStr = "";
		$dataCount = 0;
		$varObj = $this->db->query("SELECT pos.accounts_id, pos.user_id, pc.item_id AS product_id, pci.item_id FROM pos, pos_cart pc, pos_cart_item pci WHERE pci.item_id NOT IN (SELECT item_id FROM item) AND pos.pos_id = pc.pos_id AND pc.pos_cart_id = pci.pos_cart_id GROUP BY pci.item_id ORDER BY pos.accounts_id ASC, product_id ASC, pci.item_id ASC", array());
		if($varObj){
			while($oneRow = $varObj->fetch(PDO::FETCH_OBJ)){
				$dataCount++;
				//if($dataCount<=10){
					$itemData = array('item_id' => $oneRow->item_id,
									'created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $oneRow->accounts_id,		
									'user_id' => $oneRow->user_id,
									'product_id' => $oneRow->product_id,
									'item_number' => "UNKNOWN-$oneRow->item_id",
									'carrier_name' => "",
									'in_inventory' => 0,
									'is_pos'=>0,
									'custom_data'=>''
									);
					$item_id = $this->db->insert('item', $itemData);
				//}
				
				$tableRowsStr .= "$dataCount. accounts_id:$oneRow->accounts_id, user_id:$oneRow->user_id, product_id:$oneRow->product_id, item_id:$oneRow->item_id<br>";
			}
		}
		return $tableRowsStr;
	}
	
	function updateUserRoll(){
		$sql = "SELECT user_id, user_roll FROM user";
		//$sql .= " WHERE user_id IN (3239)";
		$sql .= " ORDER BY user_id ASC";
		
		$accObj = $this->db->query($sql, array());
		if($accObj){
			while($oneRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$user_id = $oneRow->user_id;
				$user_roll = $oneRow->user_roll;
				$newRoll = array();
				if(!empty($user_roll)){
					$user_roll = explode(', ', $oneRow->user_roll);
					foreach($user_roll as $oneRoll){
						$newRoll[$oneRoll] = array();
					}
				}
				$user_roll = json_encode($newRoll);
				$this->db->update('user', array('user_roll'=>$user_roll), $user_id);
			}
		}
	}
	
	function removeUserData($updateOrNot){
		$accountsIds = array();
		$tableNameArray = array('activity_feed', 'appointments', 'brand_model', 'category', 'commissions', 
			'custom_fields', 'customers', 'customer_type', 'digital_signature', 'end_of_day', 'expenses', 'expense_type', 'forms', 'forms_data', 'instance_home', 
			'inventory', 'item', 'manufacturer', 'notes', 'our_invoices', 'our_notes', 'petty_cash', 'po', 'pos', 'product', 'product_prices',
			'properties', 'repair_problems', 'repairs', 'suppliers', 'taxes', 'time_clock', 'track_edits', 'user', 'variables', 'vendors', 'warranties');
		foreach($tableNameArray as $tablename){
			$tableIdName = $tablename.'_id';
			
			$varObj = $this->db->query("SELECT $tableIdName, accounts_id FROM $tablename WHERE accounts_id NOT IN (SELECT accounts_id FROM accounts) ORDER BY accounts_id ASC, $tableIdName ASC", array());
			if($varObj){
				while($oneRow = $varObj->fetch(PDO::FETCH_OBJ)){
					$tableIdVal = $oneRow->$tableIdName;
					$accounts_id = $oneRow->accounts_id;
					$accountsIds[$accounts_id][$tablename][$tableIdVal] = '';
				}
			}
		}
		
		$dataStr = '';
		$totalAccounts = 0;
		if(!empty($accountsIds)){
			ksort($accountsIds);
			foreach($accountsIds as $accId=>$tableInfo){
				$totalAccounts++;
				$tableRows = 0;
				$tableRowsStr = '';
				foreach($tableInfo as $tableName=>$rowsInfo){
					$tableRows++;
					$tableRowsStr .= "&emsp;&emsp;tableName: $tableName, Rows found: ".count($rowsInfo)."<br>";
					foreach($rowsInfo as $tableIdVal=>$blankVal){
						$tableRowsStr .= "&emsp;&emsp;&emsp;ID:$tableIdVal<br>";
						$this->db->delete($tableName, $tableName.'_id', $tableIdVal);
					}
				}
				$dataStr .= "&emsp;AccId: $accId, data found: $tableRows<br>$tableRowsStr";
			}
		}
		
		if(strcmp($updateOrNot, 'correct')==0){
			//$this->db->delete($tablename, $tableIdName, $tableIdVal);
		}
		return "Total Accounts found: $totalAccounts<br><br>==============================================
		<br>$dataStr";
	}
	
	public function checkJSRoundWithPHP(){		
		return '';
	}
	
	public function updatePicturePngToJpg(){
		
		$accObj = $this->db->query("SELECT accounts_id FROM accounts ORDER BY accounts_id ASC", array());
		if($accObj){
			$returnMsg = array();
			$totalPicCount = $totalJPGfound = 0;
			while($oneRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$accounts_id = $oneRow->accounts_id;
				$filePath = "./assets/accounts/a_$accounts_id/repairs_";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$filePath = "./assets/accounts/a_$accounts_id/web_logo_";
				$filePath = "./assets/accounts/a_$accounts_id/pagebodyseg1_";
				$filePath = "./assets/accounts/a_$accounts_id/imei_";
				$filePath = "./assets/accounts/a_$accounts_id/prod_";
				$pics = glob($filePath."*.png");
				
				if($pics){
					$l = $j = $c = 0;
					foreach($pics as $onePicture){
						$l++;
						if(file_exists(str_replace('.png', '.jpg', $onePicture))){
							$j++;
							if (file_exists($onePicture)){
								//unlink($onePicture);
							}
						}
						elseif($c<1000){
							
							$c++;
							$picturesrc = str_replace('./', '/', $onePicture);
							$im = new Imagick($_SERVER['DOCUMENT_ROOT'] . $picturesrc);
							$im->optimizeImageLayers(); // Optimize the image layers			
							$im->setImageCompression(Imagick::COMPRESSION_JPEG);// Compression and quality
							$im->setImageCompressionQuality(0);
							$picturesrc = str_replace('.png', '.jpg', $picturesrc);
							$im->writeImages($_SERVER['DOCUMENT_ROOT'] . $picturesrc, true);// Write the image back
						}
					}
					$totalPicCount += $l;
					$totalJPGfound += $j;
					$returnMsg[] = "Accounts ID: $accounts_id, Total $l PNG Pictures & $j JPG Pictures";
				}			
			}
			$returnMsg[] = "<br>=====================================<br><strong>Total $totalPicCount pictures found.</strong>";
			echo implode('<br>', $returnMsg);
		}
	}
	
	function twoYearsBackTriAccRem(){
		$db = $this->db;
		$message = '';
		//=======================Remove 2 Years Back Trial 20 Accounts================//
		$twoYearsBackDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')-2));
		$sql = "SELECT accounts_id, company_name, company_subdomain, last_login FROM accounts WHERE SUBSTR(last_login,1,10) < '$twoYearsBackDate' AND status ='Trial' ORDER BY accounts_id ASC LIMIT 0,20";
		$queryObj = $this->db->query($sql, array());
		if($queryObj){
			$Admin = new Admin($db);
			$message .= '<br /><p><strong>The following ('.$queryObj->rowCount().') Trial Accounts Removed Successfully.</strong></p>';
			while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$returnData = $Admin->AJremove_Accounts($oneRow->accounts_id, 'Yes');
				$returnData = (array) json_decode($returnData);
				$dtcount = $returnData['dtcount']??0;
				$message .= "<p>Account ID: $oneRow->accounts_id, Company Name: $oneRow->company_name, Sub-domain: $oneRow->company_subdomain, Last Login: $oneRow->last_login, Removed $dtcount data</p>";
			}
		}
		//=========================End===========================//
		return $message;
	}
	
	public function checkInvtranQty(){
		
		$moreStr = '<br><br>============== IT wrong cart data ==========//';
		$sqlquery = "SELECT COUNT(po_id) AS totalCount FROM po WHERE transfer>0";
		$query = $this->db->querypagination($sqlquery, array());
		if($query){
			$moreStr = '<br><br>Total IT data: '.$query[0]['totalCount'];		
		}
		$sqlquery = "SELECT a.company_subdomain, po.accounts_id, po.po_number, po.po_datetime, poi.po_items_id, poi.received_qty AS received_qty, poi.received_qty*-1 AS realQty FROM accounts a, po, po_items poi WHERE po.status = 'Closed' AND ((po.transfer=1 AND poi.received_qty>0) OR (po.transfer=2 AND poi.received_qty<0)) AND a.accounts_id = po.accounts_id AND po.po_id = poi.po_id GROUP BY poi.po_items_id ORDER BY poi.po_items_id ASC";
		$query = $this->db->querypagination($sqlquery, array());
		if($query){
			$sl = 0;
			$prevIT = 0;
			foreach($query as $oneRow){
				$slStr = '&emsp;';
				if($prevIT != $oneRow['po_number']){
					$sl++;
					$slStr = "<br>$sl.";
				}
				$prevIT = $oneRow['po_number'];
				$po_number = "<a target=\"_blank\" href=\"http://$oneRow[company_subdomain].".OUR_DOMAINNAME."/Inventory_Transfer/edit/$oneRow[po_number]/\">$oneRow[po_number]</a>";
				$moreStr .= "<br>$slStr Acc ID: $oneRow[accounts_id]: $oneRow[company_subdomain].".OUR_DOMAINNAME.", it#: $po_number, PO ItemID: $oneRow[po_items_id], received_qty: $oneRow[received_qty], Original Count: $oneRow[realQty]";
			}
		}
		
		echo $moreStr;
	}
		
	public function checkIMEIQtyOnPO(){
		
		$moreStr = '<br><br>============== PO Item Table is missing==========//';
		$sqlquery = "SELECT po.accounts_id, po.po_number, po.po_datetime, poi.po_items_id, poi.received_qty AS received_qty, 0 AS realQty FROM po, po_items poi WHERE po.status = 'Closed' AND poi.item_type = 'cellphones' AND po.po_id = poi.po_id AND NOT EXISTS (SELECT po_cart_item.po_items_id FROM po_cart_item WHERE poi.po_items_id = po_cart_item.po_items_id) GROUP BY poi.po_items_id ORDER BY poi.po_items_id ASC";
		$query = $this->db->querypagination($sqlquery, array());
		if($query){
			$sl = 0;
			foreach($query as $oneRow){
				$sl++;
				$moreStr .= "<br>$sl. Acc ID: $oneRow[accounts_id], po#: $oneRow[po_number], PO ItemID: $oneRow[po_items_id], received_qty: $oneRow[received_qty], Original Count: $oneRow[realQty] (REMOVED)";
				//$this->db->delete('po_items', 'po_items_id', $oneRow['po_items_id']);
			}
		}
		echo $moreStr;
	}
	
	public function checkCartPayment($year=0, $month=0, $getPosId = ''){
		$Common = new Common($this->db);
		ini_set('memory_limit', '-1');
		$message = "<link rel=\"stylesheet\" href=\"//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\">
					<link rel=\"stylesheet\" href=\"/assets/css-".swVersion."/style.css\">
					<script src=\"//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js\"></script>
					<script>
						var j = jQuery.noConflict();
		
						function changePayment(pos_id, pos_payment_id, payment_amount, is_due){
							var formhtml = '<form action=\"#\" name=\"frmPOSPayment\" id=\"frmPOSPayment\" onSubmit=\"return false;\" enctype=\"multipart/form-data\" method=\"post\" accept-charset=\"utf-8\">'+
												'<div class=\"form-group\">'+
													'<div class=\"col-sm-4 pright0\">'+
														'<label><strong>Updated Payment</strong></label>'+
													'</div>'+
													'<div class=\"col-sm-6\">'+
														'<div class=\"input-group\">'+
															'<input name=\"payment_amount\" id=\"payment_amount\" class=\"form-control\" value=\"'+payment_amount+'\" type=\"text\" size=\"20\" maxlength=\"20\">'+
														'</div>'+
													'</div>'+
													'<div class=\"col-sm-2 pright0\">'+
														'<button class=\"btn btn-primary\" onClick=\"savePayment()\">Update</button>'+
													'</div>'+
												'</div>'+
												'<input type=\"hidden\" name=\"pos_id\" id=\"pos_id\" value=\"'+pos_id+'\" />'+
												'<input type=\"hidden\" name=\"pos_payment_id\" id=\"pos_payment_id\" value=\"'+pos_payment_id+'\" />'+
												'<input type=\"hidden\" name=\"is_due\" id=\"is_due\" value=\"'+is_due+'\" />'+
											'</form>';
									
							j('.pa'+pos_payment_id).parent('td').append(formhtml);
							
						}
						
						function savePayment(){
							var pos_id = document.getElementById('pos_id');
							var pos_payment_id = document.getElementById('pos_payment_id');
							if(pos_id.value >0){
								var payment_amount = document.getElementById('payment_amount');
								var is_due = document.getElementById('is_due');
								j.ajax({
									method: 'POST',
									url: '/DatabaseUpdate/savePayment',
									data: {pos_id:pos_id.value, pos_payment_id:pos_payment_id.value, payment_amount:payment_amount.value, is_due:is_due.value, action:'adjustPayment'},
								}).done(function( data ) {
									if(data=='session_ended'){
										window.location = '/session_ended';
									}
									if(data=='Saved'){
										var style = {backgroundColor:'#90EE90', float:'left'};
									}
									else{
										var style = {backgroundColor:'#FFCCCB', float:'left'};
									}
									j('.pa'+pos_payment_id.value).css(style);
									j('#frmPOSPayment').remove();									
								})
								.fail(function() {
									connection_dialog(savePayment);
								});
							}
						}
						
						function changeIsDue(pos_id){
							if(pos_id >0){
								j.ajax({
									method: 'POST',
									url: '/DatabaseUpdate/savePayment',
									data: {pos_id:pos_id, action:'changeIsDue'},
								}).done(function( data ) {
									if(data=='session_ended'){
										window.location = '/session_ended';
									}
									if(data=='Saved'){
										var style = {backgroundColor:'#90EE90', float:'left'};
									}
									else{
										var style = {backgroundColor:'#FFCCCB', float:'left'};
									}
									j('.pos'+pos_id).css(style);
									j('#frmPOSPayment').remove();									
								})
								.fail(function() {});
							}
						}
						
						function addPayment(pos_id, grand_total){
							if(pos_id >0 && grand_total !=0){
								j.ajax({
									method: 'POST',
									url: '/DatabaseUpdate/savePayment',
									data: {pos_id:pos_id, payment_amount:grand_total, action:'addPayment'},
								}).done(function( data ) {
									if(data=='session_ended'){
										window.location = '/session_ended';
									}
									if(data=='Saved'){
										var style = {backgroundColor:'#90EE90', float:'left'};
									}
									else{
										var style = {backgroundColor:'#FFCCCB', float:'left'};
									}
									j('.pos'+pos_id).css(style);
									j('#frmPOSPayment').remove();									
								})
								.fail(function() {});
							}
						}
					</script>
		<div id=\"no-more-tables\">
		<table class=\"col-md-12 bgnone table-bordered table-striped table-condensed cf listing\">
		<thead class=\"cf\">
		<tr>
		<th class=\"txtcenter\" width=\"10%\">Account ID</th>
		<th class=\"txtcenter\">Invoice No.</th>
		<th class=\"txtcenter\" width=\"40%\">Payment Amount</th>
		</tr>
		</thead>
		<tbody>";
		
		if($year==0){$year = date('Y');}
		if($month==0){$month = date('m');}
		
		$startDate = "$year-$month-01 00:00:00";
		$lastDate = date('t', strtotime($startDate));
		$endDate = "$year-$month-$lastDate 23:59:59";
		$sql = "SELECT pos.*, accounts.company_subdomain FROM pos, accounts WHERE";
		if(!empty($getPosId)){
			$sql .= " pos.pos_id = $getPosId AND";
		}
		$sql .= " pos.sales_datetime BETWEEN '$startDate' AND '$endDate' AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) AND pos.accounts_id = accounts.accounts_id ORDER BY accounts.accounts_id ASC, pos.pos_id ASC";
        $query = $this->db->querypagination($sql, array());
		if($query){
			foreach($query as $onerow){
				$sub_domain = $onerow['company_subdomain'];
				$pos_id = $onerow['pos_id'];
				$accounts_id = $onerow['accounts_id'];
				$is_due = $onerow['is_due'];
				$taxable_total = $nontaxable_total = 0.00;
				
				$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
				$query = $this->db->query($sqlquery, array());
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
					$taxes_total1 = $Common->calculateTax($taxable_total, $onerow['taxes_percentage1'], $tax_inclusive1);
				}
				$taxes_total2 = 0;					
				$tax_inclusive2 = $onerow['tax_inclusive2'];
				if($onerow['taxes_name2'] !=''){
					$taxes_total2 = $Common->calculateTax($taxable_total, $onerow['taxes_percentage2'], $tax_inclusive2);
				}
				
				if($tax_inclusive1>0){$taxes_total1 = 0;}
				if($tax_inclusive2>0){$taxes_total2 = 0;}
				$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
				
				$amountPaid = 0;
				$paymentStr = '';
				$sqlquery = "SELECT * FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
				$queryObj = $this->db->query($sqlquery, array());
				if($queryObj){
					while($prow = $queryObj->fetch(PDO::FETCH_OBJ)){
						$amountPaid += $prow->payment_amount;
						$paymentStr .= "<a href=\"javascript:void(0);\" onClick=\"changePayment($pos_id, $prow->pos_payment_id, $prow->payment_amount, $is_due);\" class=\"pa$prow->pos_payment_id\">$prow->payment_amount</a>, ";
					}
				}
				$grand_total = round($grand_total,2);
				$amountPaid = round($amountPaid,2);
				if(!empty($getPosId)){
					echo "$grand_total : $amountPaid";
				}
				if($grand_total !=0 && $paymentStr =='' && $is_due==0){
					$paymentStr = "<a href=\"javascript:void(0);\" onClick=\"addPayment($pos_id, $grand_total);\" class=\"pos$pos_id\">Add Payment</a>";
					$message .= "<tr>
						<td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Account/login\">$onerow[accounts_id]</a></td>
						<td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Invoices/view/$onerow[invoice_no]/\">$onerow[invoice_no]</a>, pos_id: $pos_id, is_due=$is_due && grand_total: $grand_total = $amountPaid</td>
						<td>$paymentStr</td>
					</tr>";
				}
				elseif($is_due==0 && $grand_total != $amountPaid){
					$message .= "<tr><td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Account/login\">$onerow[accounts_id]</a></td><td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Invoices/view/$onerow[invoice_no]/\">$onerow[invoice_no]</a>, pos_id: $pos_id, is_due=$is_due && grand_total: $grand_total < $amountPaid</td><td>$paymentStr</td></tr>";
				}
				elseif($is_due==1 && $grand_total == $amountPaid){
					$message .= "<tr><td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Account/login\">$onerow[accounts_id]</a></td><td><a target=\"_blank\" href=\"http://$sub_domain.".OUR_DOMAINNAME."/Invoices/view/$onerow[invoice_no]/\">$onerow[invoice_no]</a>, pos_id: $pos_id, <a href=\"javascript:void(0);\" onClick=\"changeIsDue($pos_id);\" class=\"pos$pos_id\">is_due=$is_due</a> && grand_total: $grand_total = $amountPaid</td><td>$paymentStr</td></tr>";
				}
			}
		}
		$message .= "</tbody></table></div>";
		echo $message;
	}
	
	public function savePayment(){
		$pos_id = isset($_POST['pos_id']) ? $_POST['pos_id']:0;
		$pos_payment_id = isset($_POST['pos_payment_id']) ? $_POST['pos_payment_id']:0;
		$payment_amount = isset($_POST['payment_amount']) ? $_POST['payment_amount']:0;
		$action = isset($_POST['action']) ? $_POST['action']:'';
		if($pos_id>0){
			if($payment_amount==0 && $pos_payment_id>0){$action = 'deletePayment';}
			
			if($action=='changeIsDue'){
				$updateData = array('is_due'=>0);
				$this->db->update('pos', $updateData, $pos_id);
			}
			elseif($action=='deletePayment' && $pos_payment_id>0){
				$this->db->delete('pos_payment', 'pos_payment_id', $pos_payment_id);
			}
			elseif($action=='addPayment'){
				$user_id = 0;
				$payment_datetime = date('Y-m-d H:i:s');
				$posObj = $this->db->query("SELECT user_id, sales_datetime FROM pos WHERE pos_id = $pos_id", array());
				if($posObj){
					while($posRow = $posObj->fetch(PDO::FETCH_OBJ)){
						$user_id = $posRow->user_id;
						$payment_datetime = $posRow->sales_datetime;
					}
				}
				$payment_method = $this->db->checkCharLen('pos_payment.payment_method', 'Cash');
				$drawer = $this->db->checkCharLen('pos_payment.drawer', '');
				$ppData = array('pos_id' => $pos_id,
								'payment_method' => $payment_method,
								'payment_amount' => round($payment_amount,2),
								'payment_datetime' => $payment_datetime,	
								'user_id' => $user_id,
								'more_details' => '',
								'drawer' => $drawer);
				$pos_payment_id = $this->db->insert('pos_payment', $ppData);
			}
			elseif($action=='adjustPayment' && $pos_payment_id>0){
				$updateData = array('payment_amount'=>$payment_amount);
				$this->db->update('pos_payment', $updateData, $pos_payment_id);
			}
			echo 'Saved';
		}
	}
	
	//===============Source code for Permission Module
	/*
	
	function test(){
		
		$user_roll = $_POST['user_roll']??array();
		$views = $_POST['view']??array();
		$adds = $_POST['add']??array();
		$edits = $_POST['edit']??array();
		$postData = array();
		if(!empty($user_roll)){
			$l=0;
			foreach($user_roll as $oneModule){
				$view = $views[$l]??0;
				$add = $adds[$l]??0;
				$edit = $edits[$l]??0;
				$postData[$oneModule] = array($view, $add, $edit);				
				$l++;				
			}
		}
		
		$postData = array();
		$postData['POS'] = array();
		$postData['Repairs'] = array('Add1',0,1);		
		$encode =  json_encode($postData);
		$returnStr = $encode.'<br>';
		$decode =  (array) json_decode($encode);
		if(array_key_exists('Repairs', $decode)){
			$returnStr .= '<br>Find Repairs';
		}
		return $returnStr;
		//print_r($decode);
	}
	
	//if($_SESSION["admin_id"]>0){
			$returnHTML .= "<div class=\"row\">
				<div class=\"col-sm-6\">
					<form action=\"/Home/test\" name=\"frmForm\" id=\"frmForm\" enctype=\"multipart/form-data\" method=\"post\" accept-charset=\"utf-8\">
					<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\" align=\"left\">
						<tr>";
				$Template = new Template($this->db);
				$modules = $Template->modules();
				$modules = array_merge($modules, array('Refund'=>'Refund'));
				if($modules){
					$d = 0;
					foreach($modules as $label=>$value){
						if($d>0 && $d%2==0){
							$returnHTML .= "</tr><tr>";
						}
						$d++;
						$returnHTML .= "<td valign=\"top\" width=\"200\" class=\"black12normal\" align=\"left\">
											<label style=\"font-weight:normal\"><input type=\"checkbox\" class=\"user_roll\" name=\"user_roll[]\" value=\"$value\"/> $label</label>
										</td>
										<td valign=\"top\" width=\"50\" class=\"black12normal\" align=\"left\">
											<label style=\"font-weight:normal\"><input type=\"checkbox\" class=\"view\" name=\"view[]\" value=\"1\"/> View</label>
										</td>
										<td valign=\"top\" width=\"50\" class=\"black12normal\" align=\"left\">
											<label style=\"font-weight:normal\"><input type=\"checkbox\" class=\"add\" name=\"add[]\" value=\"1\"/> Add</label>
										</td>
										<td valign=\"top\" width=\"150\" class=\"black12normal\" align=\"left\">
											<label style=\"font-weight:normal\"><input type=\"checkbox\" class=\"edit\" name=\"edit[]\" value=\"1\"/> Edit</label>
										</td>";
					}	
				}
				else{
					$returnHTML .= "<td class=\"black12normal\">".$this->db->translate('No users meet the criteria given')."</td>";
				}
					
				$returnHTML .= "</tr>
				<tr>
					<td colspan=\"4\"><input type=\"submit\" class=\"btn hilightbutton\" value=\"Save\">
				</tr>
				</table>
				</form>
				</div>
				<div class=\"col-sm-6\">
				
				</div>
			</div>";
		//}
	*/
	
}
?>