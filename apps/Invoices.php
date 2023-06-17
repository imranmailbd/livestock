<?php
class Invoices {
	protected $db;
	private int $page, $totalRows, $employee_id, $pos_id;
	private string $sorting_type, $invoice_type, $keyword_search, $history_type;
	private array $empIdOpt, $actFeeTitOpt;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}	

	private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$ssorting_type = $this->sorting_type;
		$sinvoice_type = $this->invoice_type;
		$semployee_id = $this->employee_id;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Invoices";
		$_SESSION["list_filters"] = array('ssorting_type'=>$ssorting_type, 'sinvoice_type'=>$sinvoice_type, 'semployee_id'=>$semployee_id, 'keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		if($semployee_id >0){
			$filterSql .= " AND employee_id = :employee_id";
			$bindData['employee_id'] = $semployee_id;
		}

		if(!empty($sinvoice_type)){
			if($sinvoice_type=='Refund'){
				$filterSql .= " AND pos_id IN (SELECT pos.pos_id FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos_cart.shipping_qty<0 AND pos.pos_id = pos_cart.pos_id GROUP BY pos.pos_id)";
			}
			else{
				$filterSql .= " AND is_due=1";
			}
		}
		
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			$filterSql .= " AND (invoice_no LIKE CONCAT('%', :invoice_no, '%') OR customer_id IN ( SELECT customers_id FROM customers WHERE accounts_id = $prod_cat_man";
			$bindData['invoice_no'] = str_replace('s', '', strtolower($keyword_search));
					
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
			$filterSql .= "))";
		}
		$totalRows = 0;
		$queryObj = $this->db->query("SELECT COUNT(pos_id) AS totalrows, employee_id FROM pos WHERE accounts_id = $accounts_id $filterSql AND (pos_type = 'Sale' or (pos_type in ('Order', 'Repairs') AND order_status = 2)) AND pos_publish = 1 GROUP BY employee_id", $bindData);
		if($queryObj){
			while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;	
				$empIds[$oneRow->employee_id] = '';
			}
		}

		$empIdOpt = array();
		if(!empty($empIds)){
			$salesmanObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id IN (".implode(', ', array_keys($empIds)).")", array());
			if($salesmanObj){
				while($salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ)){
					$empIdOpt[$salesmanRow->user_id] = trim(stripslashes("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
				}
			}
		}
		
		$this->totalRows = $totalRows;
		$this->empIdOpt = $empIdOpt;
	}
	
    private function loadTableRows(){
		
		if(!in_array($GLOBALS['segment3name'],  array('lists', 'AJgetPage', 'view', 'AJgetHPage', 'updateCartMobileAveCost'))){
			//$this->db->writeIntoLog("Call from: $GLOBALS[segment2name]/$GLOBALS[segment3name]");
		}
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"]??'auto';
		$Common = new Common($this->db);
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$ssorting_type = $this->sorting_type;
		$sinvoice_type = $this->invoice_type;
		$semployee_id = $this->employee_id;
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'pos.sales_datetime DESC, pos.invoice_no DESC', 
								1=>'pos.sales_datetime DESC', 
								2=>'pos.invoice_no DESC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$filterSql = "";
		$bindData = array();
		if($semployee_id >0){
			$filterSql .= " AND pos.employee_id = :employee_id";
			$bindData['employee_id'] = $semployee_id;
		}
		if(!empty($sinvoice_type)){
			if($sinvoice_type=='Refund'){
				$filterSql .= " AND pos_cart.shipping_qty<0";
			}
			else{
				$filterSql .= " AND pos.is_due=1";
			}
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			$filterSql .= " AND (pos.invoice_no LIKE CONCAT('%', :invoice_no, '%') OR pos.customer_id in ( SELECT customers_id FROM customers WHERE accounts_id = $prod_cat_man";
			$bindData['invoice_no'] = str_replace('s', '', strtolower($keyword_search));
					
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
			$filterSql .= "))";
		}
		
		$sqlquery = "SELECT pos.*, SUM(CASE WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, 
		SUM(CASE WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal 
		FROM pos LEFT JOIN pos_cart ON (pos.pos_id = pos_cart.pos_id) WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) 
		$filterSql GROUP BY pos.pos_id";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			$customersId = $salesmanId = array();
			foreach($query as $oneRow){
				$customersId[$oneRow['customer_id']] = '';
				$salesmanId[$oneRow['employee_id']] = '';
			}					
			
			if(!empty($customersId)){
				$customersObj = $this->db->query("SELECT customers_id, first_name, last_name FROM customers WHERE customers_id IN (".implode(', ', array_keys($customersId)).")", array());
				if($customersObj){
					while($customersrow = $customersObj->fetch(PDO::FETCH_OBJ)){							
						$customersId[$customersrow->customers_id] = trim(stripslashes("$customersrow->first_name $customersrow->last_name"));
					}
				}
			}
			
			if(!empty($salesmanId)){
				$salesmanObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id IN (".implode(', ', array_keys($salesmanId)).")", array());
				if($salesmanObj){
					while($salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ)){							
						$salesmanId[$salesmanRow->user_id] = trim(stripslashes("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
					}
				}
			}
			
			foreach($query as $oneRow){
			
				$pos_id = intval($oneRow['pos_id']);
				$invoice_no = intval($oneRow['invoice_no']);
				if($invoice_no ==0){
					$invoice_no = $oneRow['pos_id'];
				}
				$customer_id = $oneRow['customer_id'];
				$customername = $customersId[$customer_id]??'&nbsp;';
				
				$employee_id = $oneRow['employee_id'];
				$salesname = $salesmanId[$employee_id]??'&nbsp;';
				
				$taxable_total = round($oneRow['taxableTotal'],2);
				$nonTaxableTotal = round($oneRow['nonTaxableTotal'],2);
								
				$taxes_total1 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
				$taxes_total2 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);

				$tax_inclusive1 = $oneRow['tax_inclusive1'];
				$tax_inclusive2 = $oneRow['tax_inclusive2'];
					
				$taxestotal = round($taxes_total1+$taxes_total2,2);
				$grand_total = $taxable_total+$taxestotal+$nonTaxableTotal;
				if($tax_inclusive1>0){$grand_total -= $taxes_total1;}
				if($tax_inclusive2>0){$grand_total -= $taxes_total2;}
				
				$tabledata[] = array($invoice_no, $oneRow['sales_datetime'], "s$invoice_no", $customername, $salesname, $taxable_total, $taxestotal, $nonTaxableTotal, round($grand_total,2));
			}
		}
		
		return $tabledata;
    }
	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$ssorting_type = intval($POST['ssorting_type']??0);
		$sinvoice_type = $POST['sinvoice_type']??'';
		$semployee_id = $POST['semployee_id']??0;
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);		
		
		$this->sorting_type = $ssorting_type;
		$this->invoice_type = $sinvoice_type;
		$this->employee_id = $semployee_id;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){				
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['empIdOpt'] = $this->empIdOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}
		
	public function showCartAvgCost($pos_id, $sales_datetime){
		$cartAvgCostData = array();
		if(isset($_SESSION["prod_cat_man"])){			
			$sqlquery = "SELECT pos_cart_id, item_id, item_type, description, shipping_qty, ave_cost FROM pos_cart WHERE pos_id = $pos_id ORDER BY pos_cart_id  ASC";
			$query = $this->db->query($sqlquery, array());
			if($query){
				$Common = new Common($this->db);
				while($row = $query->fetch(PDO::FETCH_OBJ)){
					$pos_cart_id = $row->pos_cart_id;
					$item_type = $row->item_type;
					$cost = round($row->ave_cost,2);
					$newCost = 0.00;
					$shipping_qty = $row->shipping_qty;
					$description = stripslashes(trim((string) $row->description));
					$addDesc = '';
					if($item_type == 'livestocks'){
						$returnData = $Common->cartCellphoneAveCost($pos_cart_id, $sales_datetime, 0);
						if(!empty($returnData)){						
							$cost = round($returnData[0],2);
							$newCost = round($returnData[1],2);
							$addDesc = $returnData[2];
						}
					}
					elseif($item_type == 'product'){
						$returnData = $Common->getProductAvgCost($row->item_id, $sales_datetime);
						if(!empty($returnData)){
							$newCost = round($returnData[0],2);
							$addDesc = $returnData[1];
						}
					}
					elseif($item_type == 'one_time'){
						$returnData = $Common->getOTProductAvgCost($row->pos_cart_id);
						if(!empty($returnData)){						
							$newCost = round($returnData[0],2);
							$addDesc = $returnData[1];
						}
					}
					else{
						$addDesc = " - ($item_type)";
					}
					$cls = '';
					if($cost !=$newCost && !in_array($addDesc, array(' - (Labor/Services)', ' - (Count Inventory : No)'))){
						$cls = ' class="bgyellow"';
					}
					$cartAvgCostData[] = array('cls'=>$cls, 'pos_cart_id'=>$pos_cart_id, 'item_type'=>$item_type, 'description'=>$description, 'addDesc'=>$addDesc, 'cost'=>$cost, 'shipping_qty'=>$shipping_qty, 'newCost'=>$newCost);
				}
			}
		}
		return $cartAvgCostData;
	}	
	
	public function view($segment4name){
		$accounts_id = $_SESSION['accounts_id']??0;		
		$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE invoice_no = :invoice_no AND accounts_id = $accounts_id", array('invoice_no'=>$segment4name),1);
		if(!$posObj){
			return "<meta http-equiv = \"refresh\" content = \"0; url = /Orders/\" />";
		}
	}
	
	public function AJ_view_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$invoice_no = $POST['invoice_no'];
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$Common = new Common($this->db);
		$Carts = new Carts($this->db);
		$jsonResponse['invoice_no'] = intval($invoice_no);
		
		$posObj = $this->db->query("SELECT * FROM pos WHERE invoice_no = :invoice_no AND accounts_id = $accounts_id", array('invoice_no'=>$invoice_no),1);
		if($posObj){
			$pos_onerow = $posObj->fetch(PDO::FETCH_OBJ);
			$list_filters = array();
			if(isset($_SESSION["list_filters"])){
				$list_filters = $_SESSION["list_filters"];
			}
			$shistory_type = $list_filters['shistory_type']??'';
		
			$pos_id = $pos_onerow->pos_id;
			$jsonResponse['pos_id'] = intval($pos_id);

			$invoice_no = $pos_onerow->invoice_no;
			$jsonResponse['invoice_no'] = intval($invoice_no);

			$pos_publish = $pos_onerow->pos_publish;
			$jsonResponse['pos_publish'] = intval($pos_publish);

			$pos_type = $pos_onerow->pos_type;
			$jsonResponse['pos_type'] = $pos_type;
			
			$customer_id = $pos_onerow->customer_id;
			$customername = $customeremail = $offers_email = $customerphone = $customeraddress = $editcustomers = '';
			$customerObj = $this->db->query("SELECT * FROM customers WHERE customers_id = $customer_id", array());
			if($customerObj){
				$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);	
				$first_name = $customerrow->first_name;
				$last_name = $customerrow->last_name;
				$company = $customerrow->company;			
				$customername = $company;
				if($customername !=''){$customername .= ', ';}
				$customername .= $first_name;
				if($customername !=''){$customername .= ' ';}
				$customername .= $last_name;
				  
				$customeremail = $customerrow->email;
				$customerphone = $customerrow->contact_no;
			}
			$jsonResponse['customer_id'] = intval($customer_id);
			$jsonResponse['customername'] = $customername;
			$jsonResponse['customeremail'] = $customeremail;
			$jsonResponse['customerphone'] = $customerphone;
			$ticket_no = 0;
			$repairs_id = 0;
			if($pos_type=='Repairs'){
				$repairsObj = $this->db->query("SELECT repairs_id, ticket_no FROM repairs WHERE pos_id = $pos_id", array());
				if($repairsObj){
					$repairRow = $repairsObj->fetch(PDO::FETCH_OBJ);
					$repairs_id = $repairRow->repairs_id;
					$ticket_no = $repairRow->ticket_no;
				}
			}
			$jsonResponse['repairs_id'] = intval($repairs_id);
			$jsonResponse['ticket_no'] = intval($ticket_no);

			$salesPersonName = '';
			$userObj2 = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id", array());
			if($userObj2){
				$userOneRow = $userObj2->fetch(PDO::FETCH_OBJ);
				$salesPersonName = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
			}
			$jsonResponse['salesPersonName'] = $salesPersonName;

			$salesTime = strtotime($pos_onerow->sales_datetime);
			$jsonResponse['sales_datetime'] = $pos_onerow->sales_datetime;

			$canrefund = 0;
			$taxable_total = $nontaxable_total = 0.00;
			$cartData = array();
			$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
			$query = $this->db->query($sqlquery, array());
			if($query){
				while($row = $query->fetch(PDO::FETCH_OBJ)){
					$pos_cart_id = $row->pos_cart_id;
					$item_id = $row->item_id;
					$item_type = $row->item_type;
					$description = stripslashes(trim((string) $row->description));
					
					$sku = '';
					$productObj = $this->db->query("SELECT sku FROM product WHERE product_id = $item_id", array());
					if($productObj){
						$sku = $productObj->fetch(PDO::FETCH_OBJ)->sku;
					}
					if($item_type =='one_time'){
						$description .= " [1]";
					}
					
					$add_description = stripslashes(trim((string) $row->add_description));
					if($add_description !=''){
						$add_description = nl2br($add_description);
					}
					$require_serial_no = $row->require_serial_no;
					$newimei_info = array();										
					if($item_type=='livestocks'){
						$sqlitem = "SELECT item.item_id, item.item_number, item.carrier_name, pos_cart_item.sale_or_refund, pos_cart_item.return_pos_cart_id FROM item, pos_cart_item WHERE item.accounts_id = $accounts_id AND item.item_id = pos_cart_item.item_id AND pos_cart_item.pos_cart_id = $pos_cart_id";
						$itemquery = $this->db->query($sqlitem, array());
						if($itemquery){
							while($newitem_row = $itemquery->fetch(PDO::FETCH_OBJ)){								
								$item_number = $newitem_row->item_number;
								$newimei_info[] = array('item_id'=>intval($newitem_row->item_id), 'item_number'=>$newitem_row->item_number, 'carrier_name'=>$newitem_row->carrier_name, 'sale_or_refund'=>intval($newitem_row->sale_or_refund), 'return_pos_cart_id'=>intval($newitem_row->return_pos_cart_id));
							}
						}					
						
					}
					elseif($item_type=='product' && $require_serial_no>0){
						$newimei_info[] = $Carts->getSerialInfo($pos_cart_id);									
					}
					
					$sales_price = round($row->sales_price,2);
					
					$qty = floatval($row->qty);
					$shipping_qty = floatval($row->shipping_qty);
					
					$return_qty = $row->return_qty;
					if($canrefund==0 && $shipping_qty>$return_qty){
						$canrefund = 1;
					}
					$jsonResponse['canrefund'] = $canrefund;

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
					
					$cartData[] = array('pos_cart_id'=>intval($pos_cart_id), 'product_id'=>intval($item_id), 'description'=>$description, 'add_description'=>$add_description, 'sku'=>$sku, 'item_type'=>$item_type, 'newimei_info'=>$newimei_info, 'shipping_qty'=>round($shipping_qty,2), 'return_qty'=>round($return_qty,2), 'sales_price'=>round($sales_price,2), 'total'=>round($total,2), 'discount_value'=>round($discount_value,2));
				}
			}
			$jsonResponse['cartData'] = $cartData;
		
			//------------------------------//
			$jsonResponse['taxable_total'] = round($taxable_total,2);
			$jsonResponse['taxes_name1'] = $pos_onerow->taxes_name1;			
			$jsonResponse['tax_inclusive1'] = $tax_inclusive1 = intval($pos_onerow->tax_inclusive1);
			$jsonResponse['taxes_percentage1'] = $taxes_percentage1 = floatval($pos_onerow->taxes_percentage1);
			$taxes_total1 = 0;
			if($pos_onerow->taxes_name1 !=''){
				$taxes_total1 = $Common->calculateTax($taxable_total, $taxes_percentage1, $tax_inclusive1);
			}
			$jsonResponse['taxes_total1'] = round($taxes_total1,2);
			if($tax_inclusive1==1){$taxes_total1 = 0;}

			$jsonResponse['taxes_name2'] = $pos_onerow->taxes_name2;			
			$jsonResponse['tax_inclusive2'] = $tax_inclusive2 = intval($pos_onerow->tax_inclusive2);
			$jsonResponse['taxes_percentage2'] = $taxes_percentage2 = floatval($pos_onerow->taxes_percentage2);
			$taxes_total2 = 0;
			if($pos_onerow->taxes_name2 !=''){
				$taxes_total2 = $Common->calculateTax($taxable_total, $taxes_percentage2, $tax_inclusive2);
			}
			$jsonResponse['taxes_total2'] = round($taxes_total2,2);
			if($tax_inclusive2==1){$taxes_total2 = 0;}
			
			$jsonResponse['nontaxable_total'] = round($nontaxable_total,2);
			//------------------------------//

			$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;	

			$jsonResponse['is_due'] = $is_due = intval($pos_onerow->is_due);
			if($is_due==1){
				$multiple_cash_drawers = 0;
				$casDraOpts = array();
				$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
				if(!empty($cdData)){
					$cash_drawers = '';
					extract($cdData);
					$casDraOpts = explode('||',$cash_drawers);
				}
				 
				$drawer = $_COOKIE['drawer']??'';
				$drawerOpt = array();
				if($multiple_cash_drawers>0 && !empty($casDraOpts)){
					foreach($casDraOpts as $oneCDOption){
						$oneCDOption = trim((string) $oneCDOption);
						if(!empty($oneCDOption)){
							$drawerOpt[$oneCDOption] = '';
						}
					}
					if(!empty($drawerOpt)){$drawerOpt = array_keys($drawerOpt);}
				}
				else{
					$multiple_cash_drawers = 0;
				}
				
				$drawer = $_COOKIE['drawer']??'';

				$jsonResponse['multiple_cash_drawers'] = intval($multiple_cash_drawers);
				$jsonResponse['cashDrawerOptions'] = $drawerOpt;
				$jsonResponse['drawer'] = $drawer;
				
			}
			
			$totalpayment = 0;
			$paymentData = array();
			$ppSql = "SELECT payment_method, payment_amount, payment_datetime FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
			$ppQueryObj = $this->db->query($ppSql, array());
			if($ppQueryObj){
				$rowspan = $ppQueryObj->rowCount();
				while($onerow = $ppQueryObj->fetch(PDO::FETCH_OBJ)){
					$payment_amount = $onerow->payment_amount;
					
					$totalpayment += $payment_amount;
					$paymentData[] = array($onerow->payment_datetime, $onerow->payment_method, round($payment_amount,2));
				}
			}
			$jsonResponse['paymentData'] = $paymentData;
			
			$jsonResponse['amountPaid'] = round($totalpayment,2);
			$amountDue = $grand_total-$totalpayment;
			$jsonResponse['amountDue'] = round($amountDue,2);

			$paymentgetwayarray = array();
			$vData = $Common->variablesData('payment_options', $accounts_id);
			if(!empty($vData)){
				extract($vData);
				$paymentgetwayarray = explode('||',$payment_options);
			}
			$jsonResponse['paymentgetwayarray'] = $paymentgetwayarray;

			$credit_days = $pos_onerow->credit_days;
			$jsonResponse['credit_days'] = intval($credit_days);

			$jsonResponse['amountDueDate'] = date('Y-m-d', strtotime("+$credit_days day", $salesTime));
			
			$refundPer = 1;
			if($canrefund>0 && $pos_publish>0){
				if(!empty($_SESSION["allowed"]) && array_key_exists(3, $_SESSION["allowed"]) && in_array('cnr', $_SESSION["allowed"][3])) {
					$refundPer = 0;
				}
			}
			$jsonResponse['refundPer'] = $refundPer;

			$CartAverageCostIssue = array();
			if(($_SESSION["admin_id"] >0) || $_SESSION["accounts_id"]<=6){
				$calculateCartDate = $pos_onerow->sales_datetime;
				$teSql = "SELECT details FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = $pos_id AND details LIKE '%Refund from invoice #%' ORDER BY track_edits_id DESC";
				$teObj = $this->db->query($teSql, array());
				if($teObj){
					$details = $teObj->fetch(PDO::FETCH_OBJ)->details;
					if(!empty($details)){
						$details = json_decode($details);
						$moreInfo = (array)$details->moreInfo;
						if(!empty($moreInfo) && array_key_exists('description', $moreInfo)){
							$descriptionStr = $moreInfo['description'];
							if(!empty($descriptionStr)){
								$prevInvExp = explode('/Invoices/view/', $descriptionStr);
								if(count($prevInvExp)==2){
									$invoiceNoPart = $prevInvExp[1];
									$prevInvExp2 = explode('">', $invoiceNoPart);
									if(count($prevInvExp2)>=2){
										$RefundInvoiceNo = intval($prevInvExp2[0]);
										
										$OriPosSql = "SELECT sales_datetime FROM pos WHERE accounts_id = $accounts_id AND invoice_no = $RefundInvoiceNo";
										$OriPosObj = $this->db->query($OriPosSql, array());
										if($OriPosObj){
											$calculateCartDate = $OriPosObj->fetch(PDO::FETCH_OBJ)->sales_datetime;
										}									
									}
								}
							}
						}
					}
				}
				
				$CartAverageCostIssue = $this->showCartAvgCost($pos_id, $calculateCartDate);
			}

			$jsonResponse['CartAverageCostIssue'] = $CartAverageCostIssue;
		}
		else{
			$jsonResponse['login'] = 'Invoices/lists/';
		}

		return json_encode($jsonResponse);
	}
	
	public function AJgetHPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$spos_id = $POST['spos_id']??0;
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->pos_id = $spos_id;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['actFeeTitOpt'] = $this->actFeeTitOpt;
			
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRows();
		
		return json_encode($jsonResponse);
	}
	
	private function filterHAndOptions(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$spos_id = $this->pos_id;
		$shistory_type = $this->history_type;
		$sinvoice_no = 0;
		$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE pos_id = $spos_id", array());
		if($posObj){
			$sinvoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
		}
		
		$bindData = array();
		$bindData['pos_id'] = $spos_id;            
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Invoice Created')==0){
				$filterSql = "SELECT COUNT(pos_id) AS totalrows FROM pos 
					WHERE pos_id = :pos_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Signature Created')==0){
				$filterSql = "SELECT COUNT(digital_signature_id) AS totalrows FROM digital_signature 
							WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id";
			}
			else{
				$shistory_type = $this->db->translate('Sales invoice archived');
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = '/Invoices/view/$sinvoice_no'";
				$filterSql .= " AND activity_feed_title = '$shistory_type'";
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
				WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = '/Invoices/view/$sinvoice_no' 
			UNION ALL 
			SELECT COUNT(pos_id) AS totalrows FROM pos 
				WHERE pos_id = :pos_id AND accounts_id = $accounts_id 
			UNION ALL 
			SELECT COUNT(notes_id) AS totalrows FROM notes 
				WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id 
			UNION ALL 
			SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
				WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id 
			UNION ALL 
			SELECT COUNT(digital_signature_id) AS totalrows FROM digital_signature 
				WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
		}

		$totalRows = 0;
		$tableObj = $this->db->query($filterSql, $bindData);
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;
			}
		}
		$this->totalRows = $totalRows;

		
		$actFeeTitOpt = array();
		$Sql = "SELECT activity_feed_title AS afTitle FROM activity_feed 
				WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = '/Invoices/view/$sinvoice_no' 
			UNION ALL 
			SELECT 'Invoice Created' AS afTitle FROM pos 
				WHERE pos_id = :pos_id AND accounts_id = $accounts_id 
			UNION ALL 
			SELECT 'Notes Created' AS afTitle FROM notes 
				WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id 
			UNION ALL 
			SELECT 'Track Edits' AS afTitle FROM track_edits 
				WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id 
			UNION ALL 
			SELECT 'Signature Created' AS afTitle FROM digital_signature 
				WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
		$tableObj = $this->db->query($Sql, array('pos_id'=>$spos_id));
		if($tableObj){
			$actFeeTitOpts = array();
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$actFeeTitOpts[$oneRow->afTitle] = '';
			}
			ksort($actFeeTitOpts);
			$actFeeTitOpt = array_keys($actFeeTitOpts);
		}
		$this->actFeeTitOpt = $actFeeTitOpt;
	}
	
    private function loadHTableRows(){
		
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$spos_id = $this->pos_id;
		$shistory_type = $this->history_type;
		$sinvoice_no = 0;
		$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE pos_id = $spos_id", array());
		if($posObj){
			$sinvoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
		}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		$bindData = array();
		$bindData['pos_id'] = $spos_id;            
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Invoice Created')==0){
				$filterSql = "SELECT 'pos' AS tablename, created_on AS tabledate, pos_id AS table_id, 'Invoice Created' AS activity_feed_title FROM pos 
					WHERE pos_id = :pos_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Signature Created')==0){
				$filterSql = "SELECT 'digital_signature' AS tablename, created_on AS tabledate, digital_signature_id AS table_id, 'Signature Created' AS activity_feed_title FROM digital_signature 
							WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id";
			}
			else{
				$shistory_type = $this->db->translate('Sales invoice archived');
				$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = '/Invoices/view/$sinvoice_no'";
				$filterSql .= " AND activity_feed_title = '$shistory_type'";
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = '/Invoices/view/$sinvoice_no' 
					UNION ALL 
					SELECT 'pos' AS tablename, created_on AS tabledate, pos_id AS table_id, 'Invoice Created' AS activity_feed_title FROM pos 
						WHERE pos_id = :pos_id AND accounts_id = $accounts_id 
					UNION ALL 
					SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id 
					UNION ALL 
					SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id 
					UNION ALL 
					SELECT 'digital_signature' AS tablename, created_on AS tabledate, digital_signature_id AS table_id, 'Signature Created' AS activity_feed_title FROM digital_signature 
						WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id 
					ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		$query = $this->db->querypagination($filterSql, $bindData);

		$tabledata = array();
		if($query){
			$userIdNames = array();
			$userObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id", array());
			if($userObj){
				while($userOneRow = $userObj->fetch(PDO::FETCH_OBJ)){
					$userIdNames[$userOneRow->user_id] = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
				}
			}
			$Activity_Feed = new Activity_Feed($this->db);
			foreach($query as $onerow){
				$activity_feed_title = $onerow['activity_feed_title'];
				$tablename = $onerow['tablename'];
				$table_id = $onerow['table_id'];
				
				$getHMoreInfo = $Activity_Feed->getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title);
				if(!empty($getHMoreInfo)){
					$tabledata[] = $getHMoreInfo;
				}
			}
		}

		return $tabledata;

    }
		
	//=====================Payment option=================//
	public function showpaymentlist(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']??0);
		$todaytime = strtotime(date('Y-m-d'));
		$paymentData = array();	
		$sqlquery = "SELECT * FROM pos_payment WHERE pos_id = :pos_id AND payment_method != 'Change'";
		$query = $this->db->query($sqlquery, array('pos_id'=>$pos_id),1);
		if($query){
			while($row = $query->fetch(PDO::FETCH_OBJ)){
				$datadatetime = strtotime(date('Y-m-d', strtotime($row->payment_datetime)));
				$trustYN = 0;
				if($datadatetime==$todaytime){$trustYN++;}
				$paymentData[] = array(intval($row->pos_payment_id), $row->payment_datetime, $row->payment_method, round($row->payment_amount,2), $trustYN);				
			}
		}
		
		return json_encode(array('login'=>'', 'paymentData'=>$paymentData));
	}
	
	public function addInvoicesPayment(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$pos_id = $POST['pos_id']??0;
		$payment_method = $POST['payment_method']??'';
		$drawer = $POST['drawer']??'';
		$payment_amount = $POST['payment_amount']??0.00;
		
		$amountDue = $POST['amount_due']??0.00;
		$amount_due = $amountDue-$payment_amount;
		
		$returnstr = 0;
		$posObj = $this->db->query("SELECT pos_id FROM pos WHERE pos_id = :pos_id AND accounts_id = $accounts_id", array('pos_id'=>$pos_id),1);
		if($posObj){
			$pos_id = $posObj->fetch(PDO::FETCH_OBJ)->pos_id;
			$payment_method = $this->db->checkCharLen('pos_payment.payment_method', $payment_method);
			$drawer = $this->db->checkCharLen('pos_payment.drawer', $drawer);
			$ppData = array('pos_id' => $pos_id,
							'payment_method' => $payment_method,
							'payment_amount' => round($payment_amount,2),	
							'payment_datetime' => date('Y-m-d H:i:s'),
							'user_id' => $user_id,
							'more_details' => '',
							'drawer' => $drawer
							);
			$pos_payment_id = $this->db->insert('pos_payment', $ppData);
			if($pos_payment_id){
				
				if($amount_due>0){
					$this->db->update('pos', array('is_due'=>1), $pos_id);
				}
				else{
					$this->db->update('pos', array('is_due'=>0), $pos_id);
				}
				$returnstr = 1;
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnstr));
	}
	
	public function removeInvoicePayment(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$pos_id = $POST['pos_id']??0;
		$pos_payment_id = $POST['pos_payment_id']??0;			
		
		$payment_amount = 0;
		$paymentObj = $this->db->query("SELECT payment_amount FROM pos_payment WHERE pos_payment_id = $pos_payment_id", array());
		if($paymentObj){
			$payment_amount = $paymentObj->fetch(PDO::FETCH_OBJ)->payment_amount;
		}
		
		$deletepos_payment = $this->db->delete('pos_payment', 'pos_payment_id', $pos_payment_id);			
		if($deletepos_payment){
			$description = "DELETE a PAYMENT ($currency$payment_amount)";
			$moreInfo = array('table'=>'pos_payment', 'id'=>$pos_payment_id, 'description'=>$description);
			$teData = array();
			$teData['created_on'] = date('Y-m-d H:i:s');
			$teData['accounts_id'] = $_SESSION["accounts_id"];
			$teData['user_id'] = $_SESSION["user_id"];
			$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
			$teData['record_id'] = $pos_id;
			$teData['details'] = json_encode(array('changed'=>array(), 'moreInfo'=>$moreInfo));
			$this->db->insert('track_edits', $teData);
		}
		
		$sql = "SELECT * FROM pos WHERE pos_id = $pos_id";
		$query = $this->db->querypagination($sql, array());
		if($query){
			$Common = new Common($this->db);
			foreach($query as $onerow){
				$customer_id = $onerow['customer_id'];
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
				$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
				$queryObj = $this->db->query($sqlquery, array());
				if($queryObj){
					$amountPaid = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
				}
				$dueAmount = round($grand_total-$amountPaid,2);
				
				if($dueAmount>0){
					$credit_days = $credit_limit = 0;
					$customersObj = $this->db->querypagination("SELECT credit_days, credit_limit FROM customers WHERE customers_id = $customer_id AND accounts_id = $prod_cat_man AND credit_days>0 ORDER BY accounts_id DESC LIMIT 0, 1", array());
					if($customersObj){
						$credit_days = $customersObj[0]['credit_days'];
						$credit_limit = $customersObj[0]['credit_limit'];
					}
					if($credit_limit>0){
						$this->db->update('pos', array('credit_days'=>$credit_days, 'is_due'=>1), $pos_id);
					}
				}
				elseif($dueAmount<0){
					$lpos_payment_id = 0;
					$newpayment_amount = $dueAmount;
					$sqlquery = "SELECT pos_payment_id, payment_amount FROM pos_payment WHERE pos_id = $pos_id AND payment_method = 'Cash' ORDER BY pos_payment_id DESC LIMIT 0,1";
					$query = $this->db->querypagination($sqlquery, array());
					if($query){
						foreach($query as $row){
							$lpos_payment_id = $row['pos_payment_id'];
							$newpayment_amount = $row['payment_amount']+$dueAmount;
						}
					}
					
					if($lpos_payment_id>0){
						if($newpayment_amount==0){
							$this->db->delete('pos_payment', 'pos_payment_id', $lpos_payment_id);
						}
						else{
							$pos_paymentdata =array('payment_amount'=>$newpayment_amount,
													'payment_datetime'=>date('Y-m-d H:i:s')
													);
							$this->db->update('pos_payment', $pos_paymentdata, $lpos_payment_id);
						}
					}
					elseif($newpayment_amount !=0){
						$payment_method = $this->db->checkCharLen('pos_payment.payment_method', 'Cash');
						$drawer = $this->db->checkCharLen('pos_payment.drawer', '');
	
						$ppData = array('pos_id'=>$pos_id,
										'payment_method'=>$payment_method,
										'payment_amount'=>round($newpayment_amount,2),
										'payment_datetime'=>date('Y-m-d H:i:s'),
										'user_id' => $user_id,
										'more_details' => '',
										'drawer' => $drawer);
						$pos_payment_id = $this->db->insert('pos_payment', $ppData);
					}
				}
				elseif($is_due==1 && $dueAmount==0){
					$this->db->update('pos', array('credit_days'=>0, 'is_due'=>0), $pos_id);
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>1));
	}	
	
	public function updateCartMobileAveCost(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = $POST['pos_id']??0;
		$sales_datetime = $POST['sales_datetime']??'1000-01-01';
		if($pos_id>0){
			$Common = new Common($this->db);
			$sqlquery = "SELECT pos_cart_id, item_id, item_type, ave_cost FROM pos_cart WHERE pos_id = $pos_id ORDER BY pos_cart_id  ASC";
			$query = $this->db->query($sqlquery, array());
			if($query){
				while($row = $query->fetch(PDO::FETCH_OBJ)){
					if($row->item_type == 'livestocks'){
						$returnData = $Common->cartCellphoneAveCost($row->pos_cart_id, $sales_datetime, 1);
					}
					else{
						$returnData = $Common->getProductAvgCost($row->item_id, $sales_datetime);
						if(is_array($returnData) && count($returnData)==2){
							$ave_cost = $returnData[0];
							if($row->ave_cost != $ave_cost){
								$this->db->update('pos_cart',array('ave_cost'=>$ave_cost), $row->pos_cart_id);
							}
						}
					}
				}
			}
		}
		return json_encode(array('login'=>''));
	}

	//===================Refund==================//
	public function refund(){}
	
	public function AJ_Refund_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$user_id = $_SESSION['user_id']??0;
		$segment3name = $GLOBALS['segment3name'];
		$Common = new Common($this->db);
		$db = new Db();
		$pos_id = $_SESSION["pos_id"]??0;
		
		$jsonResponse['pos_id'] = intval($pos_id);
		$employee_id = $_SESSION["employee_id"]??0;
		if($employee_id==0 && $user_id>0){ $employee_id = $user_id;}
		$jsonResponse['employee_id'] = intval($employee_id);
		
		$invoice_no = 0;
		$posObj = false;
		if($pos_id>0){
			$posObj = $this->db->query("SELECT * FROM pos WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
			if($posObj){
				$pos_row = $posObj->fetch(PDO::FETCH_OBJ);		
				$invoice_no = $pos_row->invoice_no;
			}
		}
		$jsonResponse['invoice_no'] = intval($invoice_no);
		
		$empOpt = array();
		$sqlquery = "SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id AND (user_publish =1 OR (user_publish =0 AND user_id= :user_id)) ORDER BY user_first_name ASC, user_last_name ASC";
		$query = $this->db->query($sqlquery, array('user_id'=>$employee_id));
		if($query){
			while($useronerow = $query->fetch(PDO::FETCH_OBJ)){
				$optval = $useronerow->user_id;
				$optlable = trim(stripslashes("$useronerow->user_first_name $useronerow->user_last_name"));
				$empOpt[$optval] = $optlable;
			}
		}
		$jsonResponse['empOpt'] = $empOpt;

		$customer_id = $_SESSION["customer_id"]??0;
		$customer_name = '';
		if($customer_id>0){
			$customerObj = $this->db->query("SELECT first_name, last_name FROM customers WHERE customers_id = $customer_id", array());
			if($customerObj){
				$customerRow = $customerObj->fetch(PDO::FETCH_OBJ);
				$customer_name = trim(stripslashes("$customerRow->first_name $customerRow->last_name"));
			}
		}
		$jsonResponse['customer_id'] = intval($customer_id);
		$jsonResponse['customer_name'] = $customer_name;

		$taxes_name1 = $taxes_name2 = '';
		$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
		if($posObj){
			$taxes_name1 = $pos_row->taxes_name1;
			$taxes_percentage1 = $pos_row->taxes_percentage1;
			$tax_inclusive1 = $pos_row->tax_inclusive1;
			$taxes_name2 = $pos_row->taxes_name2;
			$taxes_percentage2 = $pos_row->taxes_percentage2;
			$tax_inclusive2 = $pos_row->tax_inclusive2;
		}
		$jsonResponse['taxes_name1'] = $taxes_name1;
		$jsonResponse['taxes_percentage1'] = round($taxes_percentage1,3);
		$jsonResponse['tax_inclusive1'] = intval($tax_inclusive1);

		$jsonResponse['taxes_name2'] = $taxes_name2;
		$jsonResponse['taxes_percentage2'] = round($taxes_percentage2,3);
		$jsonResponse['tax_inclusive2'] = intval($tax_inclusive2);

		$totalPayment = 0;
		$payment_method = '';
		$ppSql = "SELECT SUM(payment_amount) AS totalPayment, payment_method FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY payment_method ORDER BY pos_payment_id ASC";
		$paymentObj = $this->db->query($ppSql, array(),1);
		if($paymentObj){
			while($row = $paymentObj->fetch(PDO::FETCH_OBJ)){
				$totalPayment += $row->totalPayment;
				$payment_method = $row->payment_method;
			}
		}
		$jsonResponse['payment_method'] = $payment_method;
		$jsonResponse['totalPayment'] = round($totalPayment,2);

		$multiple_cash_drawers = 0;
		$casDraOpts = array();
		$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
		if(!empty($cdData)){
			$cash_drawers = '';
			extract($cdData);
			$casDraOpts = explode('||',$cash_drawers);
		}
		
		$drawer = $_COOKIE['drawer']??'';
		$jsonResponse['drawer'] = $drawer;
		
		$drawerOpt = array();
		if($multiple_cash_drawers>0 && !empty($casDraOpts)){
			foreach($casDraOpts as $oneCDOption){
				$oneCDOption = trim((string) $oneCDOption);
				if(!empty($oneCDOption)){
					$drawerOpt[addslashes(stripslashes($oneCDOption))] = '';
				}
			}
			if(!empty($drawerOpt)){$drawerOpt = array_keys($drawerOpt);}
		}
		else{
			$multiple_cash_drawers = 0;
		}
		$jsonResponse['multiple_cash_drawers'] = intval($multiple_cash_drawers);
		$jsonResponse['drawerOpt'] = $drawerOpt;
		
		$default_invoice_printer = 'Small';
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('default_invoice_printer', $value)){
					$default_invoice_printer = $value['default_invoice_printer'];
					if($default_invoice_printer=='' || is_null($default_invoice_printer)){$default_invoice_printer = 'Small';}
				}
			}
		}
		$jsonResponse['default_invoice_printer'] = $default_invoice_printer;

		$methodOpts = array();
		$vData = $Common->variablesData('payment_options', $accounts_id);
		if(!empty($vData)){
			extract($vData);
			$methodOpts = explode('||',$payment_options);
		}
		$methodOpt = array();
		if(!empty($methodOpts)){
			foreach($methodOpts as $onePayOption){
				$onePayOption = trim((string) $onePayOption);
				if(!empty($onePayOption)){
					$methodOpt[$onePayOption] = '';
				}
			}
			if(!empty($methodOpt)){$methodOpt = array_keys($methodOpt);}
		}
		$jsonResponse['methodOpt'] = $methodOpt;

		$SquareupCount = 0;
		$pos_paymentObj = $this->db->query("SELECT count(pos_payment_id) AS SquareupCount FROM pos_payment WHERE pos_id = $pos_id AND payment_method = 'Squareup'", array());
		if($pos_paymentObj){
			$SquareupCount = $pos_paymentObj->fetch(PDO::FETCH_OBJ)->SquareupCount;
		}
		$jsonResponse['SquareupCount'] = intval($SquareupCount);
		
		return json_encode($jsonResponse);
	}
	
    public function AJrefund_Invoices(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$invoice_no = intval($POST['invoice_no']??0);

		$returnstr = '';
		$newpos=0;
		$returnsesarray = array();
		$accounts_id = $_SESSION["accounts_id"]??0;

		$posObj = $this->db->query("SELECT * FROM pos WHERE invoice_no = :invoice_no AND accounts_id = $accounts_id", array('invoice_no'=>$invoice_no),1);
		if($posObj){
			$pos_row = $posObj->fetch(PDO::FETCH_OBJ);

			$pos_id = $pos_row->pos_id;
			$employee_id = $pos_row->employee_id;
			$customer_id = $pos_row->customer_id;

			$query = $this->db->query("SELECT * FROM pos_cart WHERE pos_id = $pos_id and shipping_qty > return_qty ORDER BY pos_cart_id asc", array());
			if($query){
				while($pos_cart_row = $query->fetch(PDO::FETCH_OBJ)){

					$newpos_cart_id = $pos_cart_row->pos_cart_id;
					$newitem_id = $pos_cart_row->item_id;
					$newitem_type = $pos_cart_row->item_type;
					$newdescription = stripslashes(trim((string) $pos_cart_row->description));
					$newadd_description = stripslashes(trim((string) $pos_cart_row->add_description));
					$newrequire_serial_no = $pos_cart_row->require_serial_no;
					$newsales_price = $pos_cart_row->sales_price;

					$newdiscount_is_percent = $pos_cart_row->discount_is_percent;
					$newdiscount = $pos_cart_row->discount;
					$shipping_qty = $pos_cart_row->shipping_qty;
					$return_qty = $pos_cart_row->return_qty;
					$max_qty = $newqty = $shipping_qty-$return_qty;

					$newtaxable = $pos_cart_row->taxable;

					$newimei_id = '';
					if($newitem_type=='livestocks'){

						$sqlitem = "SELECT item.item_id FROM item, pos_cart_item WHERE item.accounts_id = $accounts_id AND item.item_id = pos_cart_item.item_id AND pos_cart_item.pos_cart_id = $newpos_cart_id AND item.in_inventory = 0 AND pos_cart_item.return_pos_cart_id= 0";
						$itemquery = $this->db->query($sqlitem, array());
						if($itemquery){
							while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
								$imei_id = $itemrow->item_id;
								if($newimei_id !=''){$newimei_id .= ', ';}
								$newimei_id .= $imei_id;
							}
						}
					}
					elseif($newitem_type=='product' && $newrequire_serial_no>0){
						$serialsql = "select serial_number from serial_number where pos_cart_id = $newpos_cart_id and returned_pos_cart_id = 0";
						$serial_numberObj = $this->db->query($serialsql, array());
						if($serial_numberObj){
							while($sonerow = $serial_numberObj->fetch(PDO::FETCH_OBJ)){
								$imei_id = $sonerow->serial_number;
								if($newimei_id !=''){
									$newimei_id .= ', ';
								}
								$newimei_id .= $imei_id;
							}
						}
					}

					$newpos++;
					$returnsesarray[]=array('pos'=>$newpos,
						'pos_cart_id'=>$newpos_cart_id,
						'item_id'=>$newitem_id,
						'item_type'=>$newitem_type,
						'description'=>$newdescription,
						'add_description'=>$newadd_description,
						'require_serial_no'=>$newrequire_serial_no,
						'imei_id'=>$newimei_id,
						'uncheckedItemIds'=>array(),
						'sales_price'=>$newsales_price,
						'max_sales_price'=>$newsales_price,
						'discount_is_percent'=>$newdiscount_is_percent,
						'discount'=>$newdiscount,
						'qty'=>$newqty,
						'ave_cost'=>$pos_cart_row->ave_cost,
						'return_qty'=>$return_qty,
						'max_qty'=>$max_qty,
						'taxable'=>$newtaxable
					);
				}
			}

			$_SESSION["pos_id"]=$pos_id;
			$_SESSION["employee_id"]=$employee_id;
			$_SESSION["customer_id"]=$customer_id;
			$_SESSION["returnsesarray"]=$returnsesarray;

		}
		else{
			$returnstr = 'noRefundMeet';
		}
		if($newpos==0){
			$returnstr = 'noProductMeet';
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnstr));
    }

    public function AJload_RefundCart(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$cartData = array();
		$pos_id = intval($POST['pos_id']??0);
		$accounts_id = $_SESSION["accounts_id"]??0;

		if(isset($_SESSION["returnsesarray"])){
			$returnsesarray = $_SESSION["returnsesarray"];

			if($returnsesarray !=''){

				foreach($returnsesarray as $row){
					$pos = $row['pos'];
					$item_id = $row['item_id'];
					$item_type = $row['item_type'];
					$description = $row['description'];
					$imei_id = $row['imei_id'];

					$sku = $product_type = '';
					$productObj = $this->db->query("SELECT sku, product_type FROM product WHERE product_id = $item_id", array());
					if($productObj){
						$productOneRow = $productObj->fetch(PDO::FETCH_OBJ);
						$sku = $productOneRow->sku;
						$product_type = $productOneRow->product_type;
					}
					$newimei_info = array();
					if($item_type=='livestocks' && $row['max_qty'] >0){
                        $newimei_info = $this->getimei_info($pos, $imei_id);
					}
					
					$cartData[] = array('pos'=>$pos, 'description'=>$description, 'imei_id'=>$imei_id, 'newimei_info'=>$newimei_info, 'uncheckedItemIds'=>$row['uncheckedItemIds'], 'sku'=>$sku, 'pos_cart_id'=>$row['pos_cart_id'], 'item_id'=>$row['item_id'], 'item_type'=>$row['item_type'], 
					'product_type'=>$product_type, 'add_description'=>$row['add_description'], 'require_serial_no'=>intval($row['require_serial_no']), 'sales_price'=>round($row['sales_price'],2), 
					'max_sales_price'=>round($row['max_sales_price'],2), 'discount_is_percent'=>intval($row['discount_is_percent']), 'discount'=>round($row['discount'],2), 'qty'=>floatval($row['qty']), 
					'return_qty'=>floatval($row['return_qty']), 'max_qty'=>floatval($row['max_qty']), 'taxable'=>intval($row['taxable']));
				}
			}
		}
		return json_encode(array('login'=>'', 'cartData'=>$cartData));
    }
	
	public function autoItemNumber(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$fpos = intval($POST['pos']??0);		
		$pos_cart_id = intval($POST['pos_cart_id']??0);		
		$keyword_search = addslashes($POST['item_number']??'');
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$returnitem_numberarray = $imeiIds = array();
		if(isset($_SESSION["returnsesarray"])){
			$returnsesarray = $_SESSION["returnsesarray"];				
			if($returnsesarray !=''){						
				foreach($returnsesarray as $row){
					$pos = $row['pos'];
					$item_type = $row['item_type'];
					$max_qty = $row['max_qty'];	
															
					if($item_type=='livestocks' && $max_qty >0 && $fpos==$pos){
						$imei_id = $row['imei_id'];
						if (strpos($imei_id, ', ') !== false) {
							$imeiIds = explode(', ', $imei_id);			
						}
						else{
							$imeiIds[] = $imei_id;
						}
					}						
				}
			}
		}
		if(!empty($imeiIds)){
			$newImeiIds = array();
			foreach($imeiIds as $imeiId){
				if(!empty($imeiId) && intval($imeiId)>0){
					$newImeiIds[] = $imeiId;
				}
			}
			$imeiIds = $newImeiIds;
		}
		$sqlitem = "SELECT item.item_id, item.item_number FROM item, pos_cart_item WHERE item.accounts_id = $accounts_id AND pos_cart_item.pos_cart_id = $pos_cart_id AND item.item_number LIKE CONCAT('%', :keyword_search, '%') AND item.item_id = pos_cart_item.item_id AND item.in_inventory = 0 GROUP BY item.item_id ORDER BY item.item_number ASC";
		$itemquery = $this->db->query($sqlitem, array('keyword_search'=>$keyword_search));
		if($itemquery){
			while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
				if(!empty($imeiIds) && in_array($itemrow->item_id, $imeiIds)){}
				else{
					$returnitem_numberarray[] = array('label'=>$itemrow->item_number, 'item_id'=>$itemrow->item_id);
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnitem_numberarray));
	}
	
	public function findItemIdByItemNumber(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$pos = intval($POST['pos']??0);
		$pos_cart_id = intval($POST['pos_cart_id']??0);
		$item_number = addslashes($POST['item_number']??'');
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
				
		$imeiIds = array();
		if(isset($_SESSION["returnsesarray"])){
			$returnsesarray = $_SESSION["returnsesarray"];				
			if($returnsesarray !=''){						
				foreach($returnsesarray as $row){
					$item_type = $row['item_type'];
					$max_qty = $row['max_qty'];	
															
					if($item_type=='livestocks' && $max_qty >0){
						$imei_id = $row['imei_id'];
						if (strpos($imei_id, ', ') !== false) {
							$imeiIds = explode(', ', $imei_id);			
						}
						else{
							$imeiIds[] = $imei_id;
						}
					}						
				}
			}
		}
		
		$sqlitem = "SELECT item.item_id, item.item_number FROM item, pos_cart_item WHERE item.accounts_id = $accounts_id AND pos_cart_item.pos_cart_id = $pos_cart_id AND item.item_number LIKE CONCAT('%', :item_number, '%') AND item.item_id = pos_cart_item.item_id AND item.in_inventory = 0 ORDER BY item.item_number ASC LIMIT 0,1";
		$itemquery = $this->db->query($sqlitem, array('item_number'=>$item_number));
		if($itemquery){
			while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
				if(!empty($imeiIds) && !in_array($itemrow->item_id, $imeiIds)){
					$returnStr = $itemrow->item_id;
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}

   public function checkingIMEI(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$pos = intval($POST['pos']??0);
		$newpos_cart_id = intval($POST['pos_cart_id']??0);
		$newitem_id = intval($POST['imei_id']??0);
		$checked = intval($POST['checked']??0);

		if(isset($_SESSION["returnsesarray"])){
			
			$returnsesarray = $_SESSION["returnsesarray"];
			$newReturnSesArray = array();
			if($returnsesarray !=''){
				foreach($returnsesarray as $row){
					$pos_cart_id = $row['pos_cart_id'];

					if($pos_cart_id==$newpos_cart_id){
						//$this->db->writeIntoLog("$pos_cart_id==$newpos_cart_id");
						$imei_id = $row['imei_id'];
						$uncheckedItemIds = $row['uncheckedItemIds'];
						if(!empty($uncheckedItemIds)){
							$uncheckedItemIds = array_flip($uncheckedItemIds);
						}

						$imeiIds = array();
						if (strpos($imei_id, ', ') !== false) {
							$imeiIds = explode(', ', $imei_id);
						}
						elseif($imei_id !=''){
							$imeiIds[] = $imei_id;
						}
						//$this->db->writeIntoLog("$imei_id : $newitem_id");
						if(!empty($imeiIds) && in_array($newitem_id, $imeiIds)){
							//$this->db->writeIntoLog("$checked>0");
							if($checked==0){
								$uncheckedItemIds[$newitem_id] = '';							
								$returnStr = 'Add';
							}
							else{
								if(!empty($uncheckedItemIds) && array_key_exists($newitem_id, $uncheckedItemIds)){
									unset($uncheckedItemIds[$newitem_id]);							
									$returnStr = 'Remove';
								}
							}
							if(!empty($returnStr)){
								$row['qty'] = count($imeiIds) - count($uncheckedItemIds);
								$row['uncheckedItemIds'] = array();
								if(!empty($uncheckedItemIds)){
									$row['uncheckedItemIds'] = array_keys($uncheckedItemIds);
								}
							}
						}
					}
					$newReturnSesArray[] = $row;
				}
			}
			$_SESSION["returnsesarray"] = $newReturnSesArray;
			
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
   }

   public function addItemNumberIntoRefund(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$pos = intval($POST['pos']??0);
		$newpos_cart_id = intval($POST['pos_cart_id']??0);
		$newitem_id = intval($POST['item_id']??0);

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;

		if(isset($_SESSION["returnsesarray"])){
			$returnsesarray = $_SESSION["returnsesarray"];
			$newReturnSesArray = array();
			if($returnsesarray !=''){
				foreach($returnsesarray as $row){
					$item_type = $row['item_type'];
					$pos_cart_id = $row['pos_cart_id'];
					$max_qty = $row['max_qty'];

					if($pos_cart_id==$newpos_cart_id){
						$imei_id = $row['imei_id'];
						$imeiIds = array();
						if (strpos($imei_id, ', ') !== false) {
							$imeiIds = explode(', ', $imei_id);
						}
						elseif($imei_id !=''){
							$imeiIds[] = $imei_id;
						}
						if(empty($imeiIds) || (!empty($imeiIds) && !in_array($newitem_id, $imeiIds))){
							$imeiIds[] = $newitem_id;
							$returnStr = 'Add';
						}
						if(!empty($imeiIds)){
							$row['imei_id'] = implode(', ', $imeiIds);
							$row['qty'] = count($imeiIds);
						}
					}
					$newReturnSesArray[] = $row;
				}
			}
			$_SESSION["returnsesarray"] = $newReturnSesArray;
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
   }
	
	public function AJremove_RefundCart(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$newpos = intval($POST['pos']??0);
		
		$accounts_id = $_SESSION["accounts_id"]??0;
			
		if(isset($_SESSION["returnsesarray"])){
			$returnsesarray = $_SESSION["returnsesarray"];
						
			if(!empty($returnsesarray)){
				$newreturnsesarray = array();
				$i = 0;
				foreach($returnsesarray as $row){					
					$pos =$row['pos'];
					if($pos == $newpos){}
					else{
						$i++;
						$row['pos'] = $i;
						$newreturnsesarray[]= $row;
					}
				}
				$_SESSION["returnsesarray"]=$newreturnsesarray;
			}
		}		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}

   public function AJupdate_RefundCart(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$newpos = intval($POST['pos']??0);
		$maxqty = floatval($POST['max_qty']??0);
		$newqty = floatval($POST['qty']??0);
		$message = $editlink = $returnstr = '';

		if(isset($_SESSION["returnsesarray"])){
			$returnsesarray = $_SESSION["returnsesarray"];

			if(!empty($returnsesarray)){
				$newreturnsesarray = array();
				foreach($returnsesarray as $row){
					$pos = $row['pos'];
					if($pos == $newpos){
						$row['add_description'] = $POST['add_description']??'';
						$row['sales_price'] = floatval($POST['sales_price']??0.00);
						$row['discount_is_percent'] = intval($POST['discount_is_percent']??1);
						$row['discount'] = floatval($POST['discount']??0);
						$row['qty'] = $newqty;
						$newreturnsesarray[] = $row;
					}
					else{
						$newreturnsesarray[] = $row;
					}
				}

				$_SESSION["returnsesarray"]=$newreturnsesarray;
			}
		}

		return json_encode(array('login'=>'', 'maxqty'=>$maxqty, 'message'=>$message, 'editlink'=>$editlink, 'returnstr'=>$returnstr));
   }

   public function AJremove_RefundCartIMEI(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$newpos = intval($POST['pos']??0);
		$newitem_id = $POST['singleimei_id']??0;

		if(isset($_SESSION["returnsesarray"])){
			$returnsesarray = $_SESSION["returnsesarray"];
			$newReturnSesArray = array();
			if(!empty($returnsesarray)){
				foreach($returnsesarray as $row){
					$pos = $row['pos'];
					$item_type = $row['item_type'];
					$pos_cart_id = $row['pos_cart_id'];
					$max_qty = $row['max_qty'];
					if($pos==$newpos && $row['imei_id'] !=''){
						$imeiIds = explode(', ', $row['imei_id']);                            
						
						if((!empty($imeiIds) && in_array($newitem_id, $imeiIds))){
							$key = array_search($newitem_id, $imeiIds);
							if ($key !== false) {
								unset($imeiIds[$key]);
								$returnStr = 'Ok';
							}
						}
						
						$row['qty'] = count($imeiIds);
						if(!empty($imeiIds)){
							$row['imei_id'] = implode(', ', $imeiIds);
						}
						else{
							$row['imei_id'] = '';
						}
					}
					$newReturnSesArray[] = $row;
				}
			}
			$_SESSION["returnsesarray"] = $newReturnSesArray;
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
   }
	
	public function clearReturnCart(){
		
		$returnStr = '';
		
		unset($_SESSION["pos_id"]);
		unset($_SESSION["employee_id"]);
		unset($_SESSION["customer_id"]);
		unset($_SESSION["returnsesarray"]);
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
   public function AJsave_Refund(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = $invoice_no = 0;
		$returnStr = '';
		$savemsg = 'error';
		//$this->db->writeIntoLog(json_encode($POST));
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sales_datetime = $created_on = $last_updated = $payment_datetime = date('Y-m-d H:i:s');
		$pos_id = $_SESSION["pos_id"]??0;
		$employee_id = $_SESSION["employee_id"]??0;
		$customer_id = $_SESSION["customer_id"]??0;

		$taxes_name1 = $POST['taxes_name1']??'';
		$taxes_percentage1 = floatval($POST['taxes_percentage1']??1);
		$tax_inclusive1 = intval($POST['tax_inclusive1']??0);
		$taxes_name2 = $POST['taxes_name2']??'';
		$taxes_percentage2 = floatval($POST['taxes_percentage2']??1);
		$tax_inclusive2 = intval($POST['tax_inclusive2']??0);
		$grand_total = floatval($POST['grand_total']??0);
		$receipt_total = floatval($POST['receipt_total']??0);
		$changemethod = $POST['changemethod']??'';
		$drawer = $POST['drawer']??'';

		$Common = new Common($this->db);
		$Carts = new Carts($this->db);
		
		$payment_amount = $grand_total;

		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;

		if($pos_id>0 && isset($_SESSION["returnsesarray"])){
			
			//=============collect user last new invoice no================//
			$invoice_no = 1;
			$poObj = $this->db->querypagination("SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id ORDER BY invoice_no DESC LIMIT 0, 1", array());
			if($poObj){
				$invoice_no = $poObj[0]['invoice_no']+1;
			}
			$fromPOSId = $pos_id;
			$posData = array('invoice_no' => $invoice_no,
							'sales_datetime' => $sales_datetime,
							'employee_id' => $employee_id,
							'customer_id' => $customer_id,
							'taxes_name1' => $taxes_name1,
							'taxes_percentage1' => $taxes_percentage1,
							'tax_inclusive1' => $tax_inclusive1,
							'taxes_name2' => $taxes_name2,
							'taxes_percentage2' => $taxes_percentage2,
							'tax_inclusive2' => $tax_inclusive2,
							'pos_type' => 'Sale',
							'created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'user_id' => $user_id,
							'accounts_id' => $accounts_id,
							'credit_days' => 0, 
							'is_due' => 0, 
							'status' => 'New');
			$pos_id = $this->db->insert('pos', $posData);
			if($pos_id){
				if($payment_amount !=0){$payment_amount = $payment_amount*(-1);}
				$payment_method = $this->db->checkCharLen('pos_payment.payment_method', $changemethod);
				$drawer = $this->db->checkCharLen('pos_payment.drawer', $drawer);
		
				$ppData = array('pos_id'=>$pos_id,
								'payment_method'=>$payment_method,
								'payment_amount'=>round($payment_amount,2),
								'payment_datetime'=>$payment_datetime,
								'user_id' => $user_id,
								'more_details' => '',
								'drawer' => $drawer);
				$pos_payment_id = $this->db->insert('pos_payment', $ppData);
				//$this->db->writeIntoLog(json_encode($ppData));
				if(isset($_SESSION["returnsesarray"])){
					
					$returnsesarray = $_SESSION["returnsesarray"];
					//print_r($returnsesarray);
					
					if(!empty($returnsesarray)){
						foreach($returnsesarray as $row){
							$rpos_cart_id = $row['pos_cart_id'];
							$item_id = $row['item_id'];
							$item_type = $row['item_type'];
							$description = $row['description'];
							$add_description = $row['add_description'];
							$require_serial_no = $row['require_serial_no'];
							$imei_id = $row['imei_id'];
							$uncheckedItemIds = $row['uncheckedItemIds'];
							$sales_price = floatval($row['sales_price']);
							$qty = -$row['qty'];
							$discount_is_percent = $row['discount_is_percent'];
							$discount = $row['discount'];

							$ave_cost = $row['ave_cost'];
							
							$taxable = $row['taxable'];
							$item_type = $this->db->checkCharLen('pos_cart.item_type', $item_type);
							$description = $this->db->checkCharLen('pos_cart.description', $description);
									
							$insertdata = array('pos_id'=>$pos_id,
								'item_id'=>$item_id,
								'item_type'=>$item_type,
								'description'=>$description,
								'add_description'=>$add_description,
								'require_serial_no'=>$require_serial_no,
								'sales_price'=>$sales_price,
								'ave_cost'=>$ave_cost,
								'qty'=>$qty,
								'shipping_qty'=>$qty,
								'return_qty'=>0,
								'discount_is_percent'=>$discount_is_percent,
								'discount'=>$discount,
								'taxable'=>$taxable
							);

							$pos_cart_id = $this->db->insert('pos_cart', $insertdata);
							if($pos_cart_id){

								$oldreturn_qty = 0;
								$pcObj = $this->db->query("SELECT return_qty FROM pos_cart WHERE pos_cart_id = $rpos_cart_id", array());
								if($pcObj){
									$oldreturn_qty = $pcObj->fetch(PDO::FETCH_OBJ)->return_qty;
								}

								$newreturn_qty = $oldreturn_qty+($qty*(-1));
								$updatepc = $this->db->update('pos_cart', array('return_qty'=>$newreturn_qty), $rpos_cart_id);

								$activity_feed_id = 0;
								if($item_type=='livestocks' && $imei_id !=''){											
									$imei_idarray = explode(', ', $imei_id);
									
									if(is_array($imei_idarray) && count($imei_idarray)>0){

										foreach($imei_idarray as $singleimei_id){
											if($singleimei_id !='' && (empty($uncheckedItemIds) || !in_array($singleimei_id, $uncheckedItemIds))){

												$itemObj = $this->db->query("SELECT item_id FROM item WHERE item_id = $singleimei_id AND accounts_id = $accounts_id AND item_publish = 1", array());
												if($itemObj){

													$imeiitem_id = $itemObj->fetch(PDO::FETCH_OBJ)->item_id;

													$in_inventory=1;
													$pciData =array('pos_cart_id'=>$pos_cart_id, 
																	'item_id'=>$imeiitem_id, 
																	'sale_or_refund'=>0, 
																	'return_pos_cart_id'=>0);
													$pos_cart_item_id = $this->db->insert('pos_cart_item', $pciData);
													
													$pciObj = $this->db->query("SELECT pos_cart_item_id FROM pos_cart_item WHERE pos_cart_id = $rpos_cart_id AND item_id = $imeiitem_id", array());
													if($pciObj){
														$pos_cart_item_id = $pciObj->fetch(PDO::FETCH_OBJ)->pos_cart_item_id;
														$this->db->update('pos_cart_item', array('return_pos_cart_id'=>$pos_cart_id), $pos_cart_item_id);
													}
													$itemarray = array();
													$itemarray['in_inventory'] = $in_inventory;
													$itemarray['is_pos'] = 1;
													$itemarray['last_updated'] = $last_updated;
													$itemarray['accounts_id'] = $accounts_id;
													$updateitem = $this->db->update('item', $itemarray, $imeiitem_id);
												}
											}
										}
									}
								
									$Common->cartCellphoneAveCost($pos_cart_id, date('Y-m-d H:i:s'), 1);
									
								}
								elseif($item_type=='product' && $imei_id !=''){
									$imei_idarray = array();
									if (strpos($imei_id, ', ') !== false) {
										$imei_idarray = explode(', ', $imei_id);
									}
									else{
										$imei_idarray[] = $imei_id;
									}

									if(is_array($imei_idarray) && count($imei_idarray)>0){
										foreach($imei_idarray as $singleimei_id){
											if($singleimei_id !=''){

												$serObj = $this->db->query("SELECT serial_number_id FROM serial_number WHERE serial_number = '$singleimei_id' AND pos_cart_id = $rpos_cart_id", array());
												if($serObj){
													$serial_number_id = $serObj->fetch(PDO::FETCH_OBJ)->serial_number_id;

													$this->db->update('serial_number', array('returned_pos_cart_id'=>$pos_cart_id), $serial_number_id);
												}
												$singleimei_id = $this->db->checkCharLen('serial_number.serial_number', $singleimei_id);
	
												$serialdata = array('pos_cart_id' => $pos_cart_id,
													'serial_number' => $singleimei_id,	
													'returned_pos_cart_id' => 0);
												$serial_number_id = $this->db->insert('serial_number',$serialdata);
											}
										}
									}
								}

								$inventoryObj = $this->db->query("SELECT p.product_type, p.manage_inventory_count, i.inventory_id, i.current_inventory, i.ave_cost FROM product p, inventory i WHERE p.product_id = $item_id AND i.accounts_id = $accounts_id AND p.product_id = i.product_id", array());
								if($inventoryObj){
									$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
									$product_type = $inventoryrow->product_type;
									$manage_inventory_count = $inventoryrow->manage_inventory_count;
									$current_inventory = $inventoryrow->current_inventory;
									if($product_type =='Standard'){
										$newcurrent_inventory = $current_inventory+($qty*(-1));
										$updateData = array('current_inventory'=>$newcurrent_inventory);
										if($manage_inventory_count>0){
											$ave_cost = $Common->productAvgCost($accounts_id, $item_id, 1);
											$updateData['ave_cost'] = $ave_cost;
										}								
										$this->db->update('inventory', $updateData, $inventoryrow->inventory_id);
									}
									
								}
							}
						}

						unset($_SESSION["pos_id"]);
						unset($_SESSION["returnsesarray"]);
						unset($_SESSION["customer_id"]);
						unset($_SESSION["employee_id"]);
					}
				}
				
				$sql = "SELECT * FROM pos WHERE pos_id = $pos_id";
				$query = $this->db->querypagination($sql, array());
				if($query){
					foreach($query as $onerow){
						$customer_id = intval($onerow['customer_id']);
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
						$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
						$queryObj = $this->db->query($sqlquery, array());
						if($queryObj){
							$amountPaid = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
						}

						$BulkSMS = new BulkSMS($this->db);
						$BulkSMS->sendInvoiceSMS($customer_id, $onerow['invoice_no'], $grand_total, $amountPaid, $onerow['sales_datetime']);
					}
				}
						
				$email_address = '';
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('invoice_backup_email', $value))
							$email_address = $value['invoice_backup_email'];
					}
				}
				if($email_address !=''){
					$Carts->AJ_sendposmail($pos_id, $email_address, 0, 0);							
				}
				
				//=========Origian POS Track Edit Insert========//
				$description = "Refund to invoice #<a href=\"/Invoices/view/$invoice_no\">s$invoice_no</a>";
				$moreInfo = array('table'=>'pos', 'id'=>$pos_id, 'description'=>$description);
				$teData = array();
				$teData['created_on'] = date('Y-m-d H:i:s');
				$teData['accounts_id'] = $_SESSION["accounts_id"];
				$teData['user_id'] = $_SESSION["user_id"];
				$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
				$teData['record_id'] = $fromPOSId;
				$teData['details'] = json_encode(array('changed'=>array(), 'moreInfo'=>$moreInfo));
				$this->db->insert('track_edits', $teData);
				
				//=========Refund New POS Track Edit Insert========//
				$sqlquery = "SELECT invoice_no FROM pos WHERE pos_id = $fromPOSId";
				$queryObj = $this->db->query($sqlquery, array());
				if($queryObj){
					$invoice_no2 = $queryObj->fetch(PDO::FETCH_OBJ)->invoice_no;
					
					$description = "Refund from invoice #<a href=\"/Invoices/view/$invoice_no2\">s$invoice_no2</a>";
					$moreInfo = array('table'=>'pos', 'id'=>$fromPOSId, 'description'=>$description);
					$teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
					$teData['record_id'] = $pos_id;
					$teData['details'] = json_encode(array('changed'=>array(), 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);
					
				}
				$savemsg = 'success';
				$id = $pos_id;
			}
			else{
				$savemsg = 'error';
				$returnStr = 'errorOnAdding';
			}
		}
		else{
			$returnStr = 'errorOnEditing';
		}

		$array = array( 'login'=>'', 'id'=>$id,
			'invoice_no'=>$invoice_no,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
   }
	
	public function setSessEmpId($payment_amount=0){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 1;
		$_SESSION["employee_id"] = intval($POST['employee_id']??0);
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}

   public function getimei_info($pos, $imei_id){
      $accounts_id = $_SESSION["accounts_id"]??0;
      $newimei_info = $imei_idarray = array();
		if (strpos($imei_id, ', ') !== false) {
         $imei_idarray = explode(', ', $imei_id);
      }
      else{
         $imei_idarray[] = $imei_id;
      }

      if(is_array($imei_idarray) && count($imei_idarray)>0){

            foreach($imei_idarray as $singleimei_id){
                if($singleimei_id !=''){

                    $itemObj = $this->db->query("SELECT * FROM item WHERE accounts_id = $accounts_id AND item_id = $singleimei_id", array());
                    if($itemObj){
                        $newitem_row = $itemObj->fetch(PDO::FETCH_OBJ);

                        $item_id = $newitem_row->item_id;
                        $item_number = $newitem_row->item_number;

                        $carrier_name = $newitem_row->carrier_name;
                        if($item_number !=''){
                            $newimei_info[] = array($item_id, $item_number, $carrier_name, 1);
						}
                    }
                }
            }
        }

        return $newimei_info;
   }

   public function getserial_info($pos, $imei_id){
        $newimei_info = '';
        $i=0;
		$imei_idarray = array();
        if (strpos($imei_id, ', ') !== false) {
            $imei_idarray = explode(', ', $imei_id);
        }
        else{
            $imei_idarray[] = $imei_id;
        }

        if(is_array($imei_idarray) && count($imei_idarray)>0){

            foreach($imei_idarray as $singleimei_id){
                if($singleimei_id !=''){
                    $i++;
                    $newimei_info .= "<p id=\"$pos$i\">$singleimei_id &emsp;<i style=\"cursor:pointer;\" data-toggle=\"tooltip\" data-original-title=\"".$this->db->translate('Remove Serial/IMEI Number')."\" onclick=\"removeIMEIFromRefundCart($pos, '$singleimei_id');\" class=\"fa fa-trash-o\"></i></p>";
                }
            }
        }

        return $newimei_info;
   }
	
}
?>