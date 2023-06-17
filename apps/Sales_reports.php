<?php
class Sales_reports{
	
	protected $db;
	public string $pageTitle;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
	public function AJ_lists_MoreInfo(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		$poData = array();
		$PMsql="SELECT pos_payment.payment_method 
				FROM pos, pos_payment 
				WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id 
				GROUP BY pos_payment.payment_method 
				ORDER BY pos_payment.payment_method ASC";
		$PMquery = $this->db->querypagination($PMsql, array());
		if($PMquery){
			foreach($PMquery as $onerow){
				$payment_method = trim((string) $onerow['payment_method']);
				if($payment_method !='')
					$poData[$payment_method] = '';
			}
			$poData = array_keys($poData);
		}

		$jsonResponse['poData'] = $poData;
		
		return json_encode($jsonResponse);
	}
	
	public function sales_by_Date(){}
	
    public function sales_by_DateData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$report_type = $POST['report_type']??'';
		$Common = new Common($this->db);
		
		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d', strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d', strtotime($sales_datearray[1])).' 23:59:59';
				if($report_type=='Weekly'){
					$startdate = date('Y-m-d', strtotime('last monday', strtotime($startdate))).' 00:00:00';
					$enddate = date('Y-m-d', strtotime('next sunday', strtotime($enddate))).' 23:59:59';
				}
				elseif($report_type=='Monthly'){
					$startdate = date('Y-m-1', strtotime($startdate)).' 00:00:00';
					$enddate = date('Y-m-t', strtotime($enddate)).' 23:59:59';
				}
			}
		}

		$strextra = "FROM pos WHERE accounts_id = $accounts_id AND pos_publish = 1 AND (pos_type = 'Sale' OR (pos_type IN ('Order', 'Repairs') AND order_status = 2))";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (sales_datetime BETWEEN :startdate AND :enddate)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}

		if($report_type=='Weekly'){
			$strextra .= " GROUP BY YEARWEEK( sales_datetime, 5)";
		}
		elseif($report_type=='Monthly'){
			$strextra .= " GROUP BY substring(sales_datetime,1,7)";
		}
		else{
			$strextra .= " GROUP BY substring(sales_datetime,1,10)";
		}

		$sqlquery = "SELECT sales_datetime $strextra ORDER BY sales_datetime ASC";
		$query = $this->db->querypagination($sqlquery, $bindData);

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		if($query){
			foreach($query as $onegrouprow){
				$sales_datetime = $onegrouprow['sales_datetime'];
				$sumcondition = "substring(pos.sales_datetime,1,10) = '".date('Y-m-d', strtotime($sales_datetime))."'";
				$sales_date = date('Y-m-d', strtotime($sales_datetime));
				if($report_type=='Weekly'){

					$dto = new DateTime();
					$week=date("W",strtotime($sales_datetime));
					$month=date("m",strtotime($sales_datetime));
					if($month=='12' && $week=='01'){$week = 53;}
					$year=date("Y",strtotime($sales_datetime));
					$startdate = $dto->setISODate($year, $week, 1)->format('Y-m-d').' 00:00:00';
					
					$enddate = date('Y-m-d', strtotime('next sunday', strtotime($startdate))).' 23:59:59';
					$sales_date = date('Y-m-d', strtotime($startdate)).' - '.date('Y-m-d', strtotime($enddate));
					$sumcondition = "(pos.sales_datetime BETWEEN '$startdate' AND '$enddate')";
				}
				elseif($report_type=='Monthly'){
					$startdate = date('Y-m-1', strtotime($sales_datetime)).' 00:00:00';
					$enddate = date('Y-m-t', strtotime($sales_datetime)).' 23:59:59';
					$sales_date = date('Y-m-d', strtotime($startdate)).' - '.date('Y-m-d', strtotime($enddate));
					$sumcondition = "(pos.sales_datetime BETWEEN '$startdate' AND '$enddate')";
				}
				$substrextra = array();
				$detailsfields = '';				
				$rtaxable_total = $rtaxable_totalexcl = $rNontaxable_total = $rTaxes = $rCost = $rowtotalprice = 0.00;
				if($showing_type=='Details'){
					$detailsfields = "pos.invoice_no, pos.customer_id, pos.sales_datetime,";
				}
				$sumsql = "SELECT pos_cart.*, $detailsfields pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) AND $sumcondition ORDER BY pos.sales_datetime, pos.pos_id ASC, pos_cart.pos_cart_id ASC";
				$sumquery = $this->db->querypagination($sumsql, array());
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
								$taxable_total = $Nontaxable_total = $Cost = 0.00;
							}
							$prevpos_id = $pos_id;

							$pos_cart_id = $pos_cartrow['pos_cart_id'];
							$item_type = $pos_cartrow['item_type'];

							$sales_price = round($pos_cartrow['sales_price'],2);
							$shipping_qty = floatval($pos_cartrow['shipping_qty']);

							$plusOrMinus = 1;
							if($shipping_qty<0){
								$plusOrMinus = -1;
							}
							$shipping_qty = $shipping_qty*$plusOrMinus;

							$qtyvalue = round($sales_price*$shipping_qty,2);
							$ave_cost = round($pos_cartrow['ave_cost'],2);
							$qtycost = round($ave_cost*$shipping_qty,2);
							$item_id = $pos_cartrow['item_id'];													

							$discount_is_percent = $pos_cartrow['discount_is_percent'];
							$discount = $pos_cartrow['discount'];
							if($discount_is_percent>0){
								$discount_value = round($qtyvalue*0.01*$discount,2);
							}
							else{
								$discount_value = round($discount*$shipping_qty,2);
							}
							$netProductPrice = ($qtyvalue-$discount_value)*$plusOrMinus;

							$taxable = $pos_cartrow['taxable'];
							if($taxable>0){
								$taxable_total += $netProductPrice;
							}
							else{
								$Nontaxable_total += $netProductPrice;
							}
							
							$Cost += $qtycost*$plusOrMinus;

							if($pos_id != $nextpos_id){
								$rCost = $rCost+$Cost;
								$taxes_total1 = $Common->calculateTax($taxable_total*$plusOrMinus, $pos_cartrow['taxes_percentage1'], $pos_cartrow['tax_inclusive1'])*$plusOrMinus;
								$taxes_total2 = $Common->calculateTax($taxable_total*$plusOrMinus, $pos_cartrow['taxes_percentage2'], $pos_cartrow['tax_inclusive2'])*$plusOrMinus;

								$tax_inclusive1 = $pos_cartrow['tax_inclusive1'];
								$tax_inclusive2 = $pos_cartrow['tax_inclusive2'];

								$rtaxable_total += $taxable_total;
								$Taxes = $taxes_total1+$taxes_total2;
								$rTaxes = $rTaxes+$taxes_total1+$taxes_total2;
								$rNontaxable_total += $Nontaxable_total;

								$Profit = ($taxable_total*$plusOrMinus+$Nontaxable_total*$plusOrMinus-$Cost*$plusOrMinus)*$plusOrMinus;
								

								$GrandTotal = $taxable_total+$taxes_total1+$taxes_total2+$Nontaxable_total;
								$taxable_totalexcl = $taxable_total;
								if($tax_inclusive1>0){
									$Profit -= $taxes_total1*$plusOrMinus;
									$GrandTotal -= $taxes_total1*$plusOrMinus;
									$taxable_totalexcl -= $taxes_total1*$plusOrMinus;
								}
								if($tax_inclusive2>0){
									$Profit -= $taxes_total2*$plusOrMinus;
									$GrandTotal -= $taxes_total2*$plusOrMinus;
									$taxable_totalexcl -= $taxes_total2*$plusOrMinus;
								}
								$rtaxable_totalexcl += $taxable_totalexcl;
								$qtyprofit = 0;
								if(($taxable_totalexcl+$Nontaxable_total) !=0){
									$qtyprofit = round(($Profit*$plusOrMinus*100)/($taxable_totalexcl*$plusOrMinus+$Nontaxable_total*$plusOrMinus),2);
								}

								if($showing_type=='Details'){
									$invoice_no = $pos_cartrow['invoice_no'];
									$customers_id = $pos_cartrow['customer_id'];
									$customername = '';
									$customersObj = $this->db->query("SELECT first_name, last_name FROM customers WHERE customers_id = $customers_id", array());
									if($customersObj){
										$customersOneRow = $customersObj->fetch(PDO::FETCH_OBJ);
										$customername = stripslashes(trim("$customersOneRow->first_name $customersOneRow->last_name"));
									}
									
									$substrextra[] = array($pos_cartrow['sales_datetime'], $invoice_no, $customers_id, $customername, round($taxable_total,2), round($Taxes,2), round($Nontaxable_total,2), round($GrandTotal,2), round($Cost,2), round($Profit,2), round($qtyprofit,2));
								}
							}
						}
					}
				}

				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
				}

				$tableData[] = array('sales_date'=>$sales_date, 'rtaxable_total'=>round($rtaxable_total,2), 
				'rTaxes'=>round($rTaxes,2), 'rNontaxable_total'=>round($rNontaxable_total,2), 
				'rtaxable_totalexcl'=>round($rtaxable_totalexcl,2), 'rCost'=>round($rCost,2), 
				'boldclass'=>$boldclass, 'substrextra'=>$substrextra);
			}
		}

		$jsonResponse['tableData'] = $tableData;

		return json_encode($jsonResponse);
    }
	
	public function sales_by_Employee(){}
	
    public function sales_by_EmployeeData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$semployee = $POST['employee']??'';
		$Common = new Common($this->db);
		
		$semployee_idArray = $bindData = array();
		$sqlquery = "SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id";
		if($semployee !=''){
			$seleced_search = addslashes(trim((string) $semployee));
			if ( $seleced_search == "" ) { $seleced_search = " "; }
			$seleced_searches = explode (" ", $seleced_search);
			if ( strpos($seleced_search, " ") === false ) {$seleced_searches[0] = $seleced_search;}
			$num = 0;
			while ( $num < sizeof($seleced_searches) ) {
				$sqlquery .= " AND TRIM(CONCAT_WS(' ', user_first_name, user_last_name)) LIKE CONCAT('%', :seleced_search$num, '%')";
				$bindData['seleced_search'.$num] = trim((string) $seleced_searches[$num]);
				$num++;
			}
		}
		$query = $this->db->query($sqlquery, $bindData);
		if($query){
			while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
				$semployee_idArray[$oneRow->user_id] = trim("$oneRow->user_first_name $oneRow->user_last_name");
			}
		}

		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d', strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d', strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$bindData = array();
		$sqlquery = "SELECT employee_id FROM pos 
					WHERE accounts_id = $accounts_id AND pos_publish = 1 
						AND (pos_type = 'Sale' OR (pos_type IN ('Order', 'Repairs') AND order_status = 2))";
		if($startdate !='' && $enddate !=''){
			$sqlquery .= " AND sales_datetime BETWEEN :startdate AND :enddate";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		if(!empty($semployee_idArray)){
			$sqlquery .= " AND employee_id IN (".implode(', ', array_keys($semployee_idArray)).")";
		}
		$sqlquery .= " GROUP BY employee_id";
		$query = $this->db->querypagination($sqlquery, $bindData);

		$employeeIds = array();
		if($query){
			foreach($query as $onegrouprow){
				$employee_id = $onegrouprow['employee_id'];
				$employeeIds[$employee_id] = stripslashes($semployee_idArray[$employee_id]??'');
			}
		}

        $jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();

		if($employeeIds !=''){
			asort($employeeIds);
			foreach($employeeIds as $employee_id=>$employeename){

				$bindData = array();
				$sumcondition = "pos.employee_id = $employee_id";
				if($startdate !='' && $enddate !=''){
					$sumcondition .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
					$bindData["startdate"] = $startdate;
					$bindData["enddate"] = $enddate;
				}

				$detailsfields = '';
				$rtaxable_total = $rtaxable_totalexcl = $rNontaxable_total = $rTaxes = $rCost = $rowtotalprice = 0.00;
				
				if($showing_type=='Details'){
					$detailsfields = "pos.invoice_no, pos.customer_id, pos.sales_datetime,";
				}
				$sumsql = "SELECT pos_cart.*, $detailsfields pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 
							FROM pos, pos_cart 
							WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) AND $sumcondition 
								AND pos.pos_id = pos_cart.pos_id
							ORDER BY pos.pos_id ASC, pos_cart.pos_cart_id ASC";
				$sumquery = $this->db->querypagination($sumsql, $bindData);
                $substrextra = array();
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
								$taxable_total = $Nontaxable_total = $Cost = 0.00;
							}
							$prevpos_id = $pos_id;

							$pos_cart_id = $pos_cartrow['pos_cart_id'];
							$item_type = $pos_cartrow['item_type'];

							$sales_price = round($pos_cartrow['sales_price'],2);
							$shipping_qty = floatval($pos_cartrow['shipping_qty']);
							
							$qtyvalue = round($sales_price*$shipping_qty,2);
							$ave_cost = round($pos_cartrow['ave_cost'],2);
							$qtycost = round($ave_cost*$shipping_qty,2);
							$item_id = $pos_cartrow['item_id'];
							
							$Cost = $Cost+$qtycost;

							$discount_is_percent = $pos_cartrow['discount_is_percent'];
							$discount = $pos_cartrow['discount'];
							if($discount_is_percent>0){
								$discount_value = round($qtyvalue*0.01*$discount,2);
							}
							else{
								$discount_value = round($discount*$shipping_qty,2);
							}
							$taxable = $pos_cartrow['taxable'];
							if($taxable>0){
								$taxable_total = $taxable_total+$qtyvalue-$discount_value;
							}
							else{
								$Nontaxable_total = $Nontaxable_total+$qtyvalue-$discount_value;
							}
							
							if($pos_id != $nextpos_id){
								$rCost = $rCost+$Cost;
								$taxes_total1 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage1'], $pos_cartrow['tax_inclusive1']);
								$taxes_total2 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage2'], $pos_cartrow['tax_inclusive2']);

								$tax_inclusive1 = $pos_cartrow['tax_inclusive1'];
								$tax_inclusive2 = $pos_cartrow['tax_inclusive2'];

								$rtaxable_total += $taxable_total;
								$Taxes = $taxes_total1+$taxes_total2;
								$rTaxes = $rTaxes+$taxes_total1+$taxes_total2;
								$rNontaxable_total += $Nontaxable_total;

								$Profit = $taxable_total+$Nontaxable_total-$Cost;
								$GrandTotal = $taxable_total+$taxes_total1+$taxes_total2+$Nontaxable_total;
								$taxable_totalexcl = $taxable_total;

								if($tax_inclusive1>0){
									$Profit -= $taxes_total1;
									$GrandTotal -= $taxes_total1;
									$taxable_totalexcl -= $taxes_total1;
								}

								if($tax_inclusive2>0){
									$Profit -= $taxes_total2;
									$GrandTotal -= $taxes_total2;
									$taxable_totalexcl -= $taxes_total2;
								}
								
								$rtaxable_totalexcl += $taxable_totalexcl;
								$qtyprofit = 0;
								if(($taxable_totalexcl+$Nontaxable_total) !=0){
									$qtyprofit = round(($Profit*100)/($taxable_totalexcl+$Nontaxable_total),2);
								}
								if($showing_type=='Details'){
									$invoice_no = $pos_cartrow['invoice_no'];
									$customers_id = $pos_cartrow['customer_id'];
									$customername = '';
									$customersObj = $this->db->query("SELECT first_name, last_name FROM customers WHERE customers_id = $customers_id", array());
									if($customersObj){
										$customersOneRow = $customersObj->fetch(PDO::FETCH_OBJ);
										$customername = stripslashes(trim("$customersOneRow->first_name $customersOneRow->last_name"));
									}
									
									$substrextra[] = array($pos_cartrow['sales_datetime'], $invoice_no, $customers_id, $customername, round($taxable_total,2), round($Taxes,2), round($Nontaxable_total,2), round($GrandTotal,2), round($Cost,2), round($Profit,2), round($qtyprofit,2));
								}
							}
						}
					}
				}

				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
				}

				$tableData[] = array('employeename'=>$employeename, 'rtaxable_total'=>round($rtaxable_total,2), 
				'rTaxes'=>round($rTaxes,2), 'rNontaxable_total'=>round($rNontaxable_total,2), 
				'rtaxable_totalexcl'=>round($rtaxable_totalexcl,2), 'rCost'=>round($rCost,2), 
				'boldclass'=>$boldclass, 'substrextra'=>$substrextra);
			}
		}

		$jsonResponse['tableData'] = $tableData;

		return json_encode($jsonResponse);
    }

	public function sales_by_Customer(){}
	
	public function AJ_sales_by_Customer_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;

		$customers_id = intval($POST['customers_id']??0);
		
		$cusTypeOpt = array();
		$extraStr = '';
		if($customers_id>0){$extraStr = " AND customers_id = $customers_id";}
		$customerTypeData = $this->db->querypagination("SELECT COALESCE(customer_type,'') AS customer_type FROM customers WHERE accounts_id = $prod_cat_man $extraStr AND customers_publish = 1 GROUP BY customer_type ORDER BY customer_type ASC", array());
		if($customerTypeData){
			foreach($customerTypeData as $oneRow){
				$customer_type = stripslashes($oneRow['customer_type']);
				$cusTypeOpt[$customer_type] = '';
			}
			$cusTypeOpt = array_keys($cusTypeOpt);
		}
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['cusTypeOpt'] = $cusTypeOpt;
		
		return json_encode($jsonResponse);
	}
    
    public function sales_by_CustomerData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sales_date = $POST['sales_date']??'';
		$sorder_by = $POST['sorder_by']??'total';
		$showing_type = $POST['showing_type']??'';
		$customer_type = $POST['customer_type']??'';
		$scustomers_id = intval($POST['customers_id']??0);
		$scustomer = trim((string) addslashes($POST['customer_name']??''));
		$Common = new Common($this->db);
		
		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d',strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$scustomer_idArray = $bindData = $singlebind = array();
		$sqlquery = "SELECT customers_id, first_name, last_name FROM customers WHERE accounts_id = $prod_cat_man";
		if($customer_type !='All'){
			$sqlquery .= " AND customer_type = :customer_type";
			$bindData["customer_type"] = $customer_type;
			$singlebind["customer_type"] = $customer_type;
		}
		if($scustomer !=''){
			if($scustomers_id>0){
				$singlecustomer = $sqlquery." AND customers_id = :scustomers_id";
				$singlebind["scustomers_id"] = $scustomers_id;

				$cquery1 = $this->db->query($singlecustomer, $singlebind);
				if($cquery1){
					while($oneRow = $cquery1->fetch(PDO::FETCH_OBJ)){
						$scustomer_idArray[$oneRow->customers_id] = trim("$oneRow->first_name $oneRow->last_name");
					}
				}
			}

			if(empty($scustomer_idArray)){
				$seleced_search = addslashes(trim((string) $scustomer));
				if ( $seleced_search == "" ) { $seleced_search = " "; }
				$seleced_searches = explode (" ", $seleced_search);
				if ( strpos($seleced_search, " ") === false ) {$seleced_searches[0] = $seleced_search;}
				$num = 0;
				while ( $num < sizeof($seleced_searches) ) {
					$sqlquery .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no)) LIKE CONCAT('%', :seleced_search$num, '%')";
					$bindData['seleced_search'.$num] = trim((string) $seleced_searches[$num]);
					$num++;
				}
			}
		}
		if(empty($scustomer_idArray)){
			$cquery2 = $this->db->query($sqlquery, $bindData);
			if($cquery2){
				while($oneRow = $cquery2->fetch(PDO::FETCH_OBJ)){
					$scustomer_idArray[$oneRow->customers_id] = trim("$oneRow->first_name $oneRow->last_name");
				}
			}
		}

		$sqlquery = "SELECT customer_id FROM pos WHERE accounts_id = $accounts_id AND pos_publish = 1 AND (pos_type = 'Sale' OR (pos_type IN ('Order', 'Repairs') AND order_status = 2))";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$sqlquery .= " AND (sales_datetime BETWEEN :startdate AND :enddate)";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		if(($scustomer !='' || $customer_type !='All' || $scustomers_id>0) && !empty($scustomer_idArray)){
			$sqlquery .= " AND customer_id IN (".implode(', ', array_keys($scustomer_idArray)).")";
		}
		$sqlquery .= " GROUP BY customer_id";
		$pquery1 = $this->db->querypagination($sqlquery, $bindData);
		$customerIds = array();
		if($pquery1){
			foreach($pquery1 as $onegrouprow){
				$customer_id = $onegrouprow['customer_id'];
				$customerIds[$customer_id] = stripslashes($scustomer_idArray[$customer_id]??'');
			}
		}
		
        $jsonResponse = array();
		$jsonResponse['sqlquery'] = $sqlquery;
		$jsonResponse['login'] = '';
		$tableData = array();

		if(!empty($customerIds)){
			asort($customerIds);
			$allData = array();
			foreach($customerIds as $customer_id=>$customername){
				$bindData = array();
				$sumcondition = "pos.customer_id = $customer_id";
				if($startdate !='' && $enddate !=''){
					$sumcondition .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
					$bindData["startdate"] = $startdate;
					$bindData["enddate"] = $enddate;
				}
				 
				$detailsfields = '';
				$rtaxable_total = $rtaxable_totalexcl = $rowtotaltaxes =  $rowtotalnontaxable = 0.00;

				if($showing_type=='Details'){
					$detailsfields = "pos.invoice_no, pos.customer_id, pos.sales_datetime,";
				}
				$sumsql = "SELECT pos.pos_id, $detailsfields pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, pos_cart.sales_price, qty, pos_cart.shipping_qty, pos_cart.taxable, pos_cart.discount_is_percent, pos_cart.discount FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) AND $sumcondition AND pos.pos_id = pos_cart.pos_id ORDER BY pos.pos_id ASC";
				$sumquery = $this->db->querypagination($sumsql, $bindData);
				$substrextra = array();
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
								$taxable_total = $totalnontaxable = 0.00;
							}

							$prevpos_id = $pos_id;


							$sales_price = round($pos_cartrow['sales_price'],2);
							$shipping_qty = floatval($pos_cartrow['shipping_qty']);

							$qtyvalue = round($sales_price*$shipping_qty,2);

							$discount_is_percent = $pos_cartrow['discount_is_percent'];
							$discount = $pos_cartrow['discount'];
							if($discount_is_percent>0){
								$discount_value = round($qtyvalue*0.01*$discount,2);
							}
							else{
								$discount_value = round($discount*$shipping_qty,2);
							}

							$taxable = $pos_cartrow['taxable'];
							if($taxable>0){
								$taxable_total = $taxable_total+$qtyvalue-$discount_value;
							}
							else{
								$totalnontaxable = $totalnontaxable+$qtyvalue-$discount_value;
							}

							if($pos_id != $nextpos_id){

								$taxes_total1 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage1'], $pos_cartrow['tax_inclusive1']);
								$taxes_total2 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage2'], $pos_cartrow['tax_inclusive2']);

								$tax_inclusive1 = $pos_cartrow['tax_inclusive1'];
								$tax_inclusive2 = $pos_cartrow['tax_inclusive2'];

								$rtaxable_total = $rtaxable_total+$taxable_total;
								$rowtotaltaxes = $rowtotaltaxes+$taxes_total1+$taxes_total2;
								$rowtotalnontaxable = $rowtotalnontaxable+$totalnontaxable;
								$GrandTotal = $taxable_total+$taxes_total1+$taxes_total2+$totalnontaxable;
								$taxable_totalexcl = $taxable_total;
								if($tax_inclusive1>0){
									$GrandTotal -= $taxes_total1;
									$taxable_totalexcl -= $taxes_total1;
								}
								if($tax_inclusive2>0){
									$GrandTotal -= $taxes_total2;
									$taxable_totalexcl -= $taxes_total2;
								}
								$rtaxable_totalexcl += $taxable_totalexcl;

								if($showing_type=='Details'){
									$invoice_no = $pos_cartrow['invoice_no'];
									$totaltaxes = $taxes_total1+$taxes_total2;
									
									$substrextra[] = array($pos_cartrow['sales_datetime'], $invoice_no, round($taxable_total,2), round($totaltaxes,2), round($totalnontaxable,2), round($GrandTotal,2));
								}
							}
						}
					}
				}
				
				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
				}
				//====================New Function Data================//
                $tableOneRow = array('customer_id'=>$customer_id, 'customername'=>$customername, 'rtaxable_total'=>round($rtaxable_total,2), 
				'rowtotaltaxes'=>round($rowtotaltaxes,2), 'rowtotalnontaxable'=>round($rowtotalnontaxable,2), 
				'rtaxable_totalexcl'=>round($rtaxable_totalexcl,2), 'boldclass'=>$boldclass, 'substrextra'=>$substrextra);
						
				if($sorder_by=='total'){
					$rowgrand_total = (string)round($rtaxable_totalexcl+$rowtotaltaxes+$rowtotalnontaxable,2);
					$allData[$rowgrand_total][] = $tableOneRow;
				}
				else{
					$allData[$customername][] = $tableOneRow;
				}
			}
			if($sorder_by=='total'){krsort($allData);}
			else{ksort($allData);}
			foreach($allData as $index=>$dataInfo){
				foreach($dataInfo as $oneRowInfo){
					$tableData[] = $oneRowInfo;
				}
			}
		}

		$jsonResponse['tableData'] = $tableData;

		return json_encode($jsonResponse);
	}
	
	public function sales_by_Paymenttype(){}
	
	public function AJ_Sales_by_Paymenttype_MoreInfo(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$paymenttype = $_REQUEST['paymenttype']??'';
		
		$useNamOpt = array();
		$userSql = "SELECT user.user_id, user.user_first_name, user.user_last_name FROM user, pos_payment WHERE user.accounts_id = $accounts_id AND pos_payment.user_id = user.user_id GROUP BY user.user_id ORDER BY user.user_first_name ASC, user.user_last_name ASC";
		$userObj = $this->db->querypagination($userSql, array());
		if($userObj){
			foreach($userObj as $oneRow){
				$userName = trim(stripslashes("$oneRow[user_first_name] $oneRow[user_last_name]"));
				$useNamOpt[$oneRow['user_id']] = $userName;
			}
		}
		$poData = array();
		$PMsql="SELECT pos_payment.payment_method FROM pos, pos_payment 
				WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id 
				GROUP BY pos_payment.payment_method ORDER BY pos_payment.payment_method ASC";
		$PMquery = $this->db->querypagination($PMsql, array());
		if($PMquery){
			foreach($PMquery as $onerow){
				$payment_method = trim((string) $onerow['payment_method']);
				if($payment_method !='')
					$poData[$payment_method] = '';
			}
			$poData = array_keys($poData);
		}

		$jsonResponse['paymenttype'] = $paymenttype;
		$jsonResponse['useNamOpt'] = $useNamOpt;
		$jsonResponse['poData'] = $poData;
		
		return json_encode($jsonResponse);
	}
    
    public function Sales_by_PaymenttypeData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$spaymenttype = $POST['paymenttype']??'';
		$puser_id = intval($POST['puser_id']??0);
		
		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d',strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$strextra = "FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		if($spaymenttype !=''){
			$strextra .= " AND pos_payment.payment_method = :paymenttype";
			$bindData["paymenttype"] = $spaymenttype;
		}
		if($puser_id >0){
			$strextra .= " AND pos_payment.user_id = :user_id";
			$bindData["user_id"] = $puser_id;
		}
		$strextra .= " GROUP BY pos_payment.payment_method";

		$sqlquery = "SELECT pos_payment.payment_method, sum(pos_payment.payment_amount) AS total_payment_amount $strextra";
		$sqlquery .= " ORDER BY pos_payment.payment_method";
		$query = $this->db->querypagination($sqlquery, $bindData);
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();

		if($query){
			foreach($query as $onegrouprow){
				$payment_method = $onegrouprow['payment_method'];
				$total_payment_amount = round($onegrouprow['total_payment_amount'],2);

				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
				}
				$substrextra = array();
				if($showing_type=='Details'){
					$substrSql = ' AND pos.pos_id = pos_payment.pos_id';
					$bindData = array();
					if($payment_method !=''){
						$substrSql .= " AND pos_payment.payment_method = '$payment_method'";
					}

					if($startdate !='' && $enddate !=''){
						$substrSql .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
						$bindData["startdate"] = $startdate;
						$bindData["enddate"] = $enddate;
					}

					$subsqlquery = "SELECT *, pos_payment.payment_amount AS totalpayment_amount FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 $substrSql ORDER BY pos.pos_id DESC";
					$subquery = $this->db->query($subsqlquery, $bindData);
					if($subquery){

						while($onerow = $subquery->fetch(PDO::FETCH_OBJ)){

							$pos_id = $onerow->pos_id;
							$invoice_no = $onerow->invoice_no;
							
							$totalpayment_amount = $onerow->totalpayment_amount;
							$linkUrl = '';
							if($invoice_no==0){								
								$pos_type = $onerow->pos_type;
								if($pos_type=='Repairs'){
									$repairs_id = $ticket_no = 0;
									$repairsObj = $this->db->query("SELECT repairs_id, ticket_no FROM repairs WHERE pos_id = $pos_id LIMIT 0, 1", array());
									if($repairsObj){
										$repairsRow = $repairsObj->fetch(PDO::FETCH_OBJ);
										$repairs_id = $repairsRow->repairs_id;
										$ticket_no = $repairsRow->ticket_no;
									}
									if($repairs_id >0){
										$linkUrl = "/Repairs/edit/$repairs_id";
										$invoice_no = 't'.$ticket_no;										
									}
								}
							}
							else{
								$linkUrl = "/Invoices/view/$invoice_no";
								$invoice_no = 's'.$invoice_no;
							}
							$substrextra[] = array($onerow->payment_datetime, $invoice_no, $linkUrl, round($totalpayment_amount,2));
						}
					}
				}
				
				$tableData[] = array('payment_method'=>$payment_method, 'total_payment_amount'=>round($total_payment_amount,2), 'boldclass'=>$boldclass, 'substrextra'=>$substrextra);
			}
		}

		$jsonResponse['tableData'] = $tableData;

		return json_encode($jsonResponse);
    }
	
	public function sales_by_Product(){}
	
	public function sales_by_ProductData(){
		set_time_limit(600);
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$sku = $POST['sku']??'';
		$product = trim((string) addslashes($POST['product']??''));
		
		$productIds = $bindData = array();
		if(!empty($sku)){
			$sql = "SELECT product_id FROM product  WHERE accounts_id = $prod_cat_man AND sku=:sku ORDER BY TRIM(CONCAT_WS(' ', product_name, colour_name, storage, physical_condition_name)) ASC";
			$bindData['sku'] = $sku;
			$queryObj = $this->db->query($sql, $bindData);
			if($queryObj){
				while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
					if(!in_array($onerow->product_id, $productIds))
						$productIds[] = $onerow->product_id;
				}
			}
		}
		elseif(!empty($product)){
			$sql = "SELECT p.product_id FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man";

			$seleced_search = addslashes(trim((string) $product));
			if ( $seleced_search == "" ) { $seleced_search = " "; }
			$seleced_searches = explode (" ", $seleced_search);
			if ( strpos($seleced_search, " ") === false ) {$seleced_searches[0] = $seleced_search;}
			$num = 0;
			while ( $num < sizeof($seleced_searches) ) {
				$sql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku)) LIKE CONCAT('%', :seleced_search$num, '%')";
				$bindData['seleced_search'.$num] = trim((string) $seleced_searches[$num]);
				$num++;
			}

			$sql .= " ORDER BY TRIM(CONCAT_WS(' ', p.product_name, p.colour_name, p.storage, p.physical_condition_name)) ASC";
			$queryObj = $this->db->query($sql, $bindData);
			if($queryObj){
				while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
					if(!in_array($onerow->product_id, $productIds))
						$productIds[] = $onerow->product_id;
				}
			}
		}
		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d',strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$strextra1 = "FROM pos_cart, pos WHERE pos.accounts_id = $accounts_id";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra1 .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		$strextra2 = "";
		if(!empty($productIds)){
			$strextra2 = " AND pos_cart.item_id IN (".implode(', ', $productIds).")";
		}

		$sqlquery = "SELECT pos_cart.item_id, pos_cart.description $strextra1 $strextra2 AND (pos.pos_type = 'Sale' OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1 GROUP BY pos_cart.description ORDER BY pos_cart.description ASC";
		$query = $this->db->querypagination($sqlquery, $bindData);
		
        $jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		if($query){
			$itemIds = array();
			foreach($query as $onegrouprow){
				$item_id = $onegrouprow['item_id'];
				$description = addslashes(stripslashes(trim((string) $onegrouprow['description'])));
				$itemIds[$item_id] = $description;
			}
			
			$productInfo = array();
			if(!empty($itemIds)){                    
				$strextra2 = " AND pos_cart.item_id IN (".implode(', ', array_keys($itemIds)).")";
				$detailsfields = '';
				if($showing_type=='Details'){
					$detailsfields = "pos.invoice_no, pos.sales_datetime,";
				}

				$substr="SELECT $detailsfields pos_cart.pos_cart_id, pos_cart.item_type, pos_cart.item_id, pos_cart.description, pos_cart.sales_price, pos_cart.shipping_qty, pos_cart.discount_is_percent, pos_cart.discount, pos_cart.ave_cost 
						$strextra1 $strextra2 AND (pos.pos_type = 'Sale' OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1 GROUP BY pos_cart.pos_cart_id ORDER BY pos_cart.pos_cart_id ASC";
				$pos_cartquery = $this->db->querypagination($substr, $bindData);
				if($pos_cartquery){
					
					foreach($pos_cartquery as $pos_cartrow){

						$pos_cart_id = $pos_cartrow['pos_cart_id'];
						$description = addslashes(trim((string) $pos_cartrow['description']));
						$item_id = $pos_cartrow['item_id'];
						$item_type = $pos_cartrow['item_type'];
						$sales_price = round($pos_cartrow['sales_price'],2);
						$qty = $pos_cartrow['shipping_qty'];
						$qtyvalue = round($sales_price*$qty,2);
						$ave_cost = round($pos_cartrow['ave_cost'],2);

						$qtycost = round($ave_cost*$qty,2);
						
						$discount_is_percent = $pos_cartrow['discount_is_percent'];
						$discount = $pos_cartrow['discount'];
						if($discount_is_percent>0){
							$discount_value = round($qtyvalue*0.01*$discount,2);
						}
						else{
							$discount_value = round($discount*$qty,2);
						}
													
						$unitgrandtotal = $qtyvalue-$discount_value;
						
						$qtyprofitval = $unitgrandtotal-$qtycost;
						$qtyprofit = 0;
						if($unitgrandtotal !=0){
							$qtyprofit = round(($qtyprofitval*100)/$unitgrandtotal,2);
						}
						
						$substrextra = array();
						if($showing_type=='Details'){
							$invoice_no = $pos_cartrow['invoice_no'];
							
							$substrextra = array($pos_cartrow['sales_datetime'], $invoice_no, $sales_price, $qty, $discount_value, $unitgrandtotal, $qtycost, $qtyprofitval, $qtyprofit);

						}
						
						$productInfo[$item_id][] = array(round($qtyvalue,2), round($qty,2), round($qtycost,2), round($discount_value,2), round($unitgrandtotal,2), $substrextra, $description);
					}
				}
			}
			
			if(!empty($productInfo)){
				//sort($itemIds);
				foreach($itemIds as $item_id=>$description){
					$substrextra = $itemInfor = array();
					if(array_key_exists($item_id, $productInfo)){$itemInfor = $productInfo[$item_id];}
					
					$rowtotalprice = $rowtotalqty = $rowtotalcost = $rowtotaldiscount = $rowgrandtotal = 0.00;
					if(!empty($itemInfor)){
						foreach($itemInfor as $oneRow){
							$rowtotalprice += $oneRow[0];
							$rowtotalqty += $oneRow[1];
							$rowtotalcost += $oneRow[2];
							$rowtotaldiscount += $oneRow[3];
							$rowgrandtotal += $oneRow[4];
							if($showing_type=='Details'){
								$substrextra[] = $oneRow[5];
							}
						}
					}
				
					$unitprice = $rowtotalprice;
					if($rowtotalqty>0){
						$unitprice = round($rowtotalprice/$rowtotalqty,2);
					}
					
					$boldclass = '';
					if($showing_type=='Details'){$boldclass = 'txt14bold';}

					$tableData[] = array('description'=>$description, 'unitprice'=>round($unitprice,2), 'rowtotalqty'=>round($rowtotalqty,2), 
					'rowtotaldiscount'=>round($rowtotaldiscount,2), 'rowgrandtotal'=>round($rowgrandtotal,2), 
					'rowtotalcost'=>round($rowtotalcost,2), 'rowtotalprice'=>round($rowtotalprice,2), 'boldclass'=>$boldclass, 'substrextra'=>$substrextra);
				}
			}
		}

		$jsonResponse['tableData'] = $tableData;

		return json_encode($jsonResponse);
    }
	
	public function sales_by_Category(){}
	
	public function AJ_sales_by_Category_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;	

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		$useNamOpt = array();
		$userSql = "SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id AND user_publish =1 ORDER BY user_first_name ASC, user_last_name ASC";
		$userObj = $this->db->querypagination($userSql, array());
		if($userObj){
			foreach($userObj as $oneRow){
				$userName = trim(stripslashes("$oneRow[user_first_name] $oneRow[user_last_name]"));
				$useNamOpt[$oneRow['user_id']] = $userName;
			}
		}
		$catNamOpt = array();
		$catSql = "SELECT category_id, category_name FROM category WHERE accounts_id = $prod_cat_man AND category_publish =1 ORDER BY category_name ASC";
		$catObj = $this->db->query($catSql, array());
		if($catObj){
			while($oneRow = $catObj->fetch(PDO::FETCH_OBJ)){
				$catName = stripslashes(trim((string) $oneRow->category_name));
				$catNamOpt[$oneRow->category_id] = $catName;
			}
		}

		$jsonResponse['useNamOpt'] = $useNamOpt;
		$jsonResponse['catNamOpt'] = $catNamOpt;
		
		return json_encode($jsonResponse);
	}
    
    public function sales_by_CategoryData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$semployee_id = $POST['employee_id']??'';
		$sscategory_id = $POST['category_id']??'';
		$tableData = array();
		$jsonResponse = array();
		
		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d',strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		//=================sql for category group =======================//
		$strextra1="FROM pos_cart, pos WHERE pos.accounts_id = $accounts_id";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra1 .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}
		if($semployee_id !=''){
			$strextra1 .= " AND pos.employee_id = :employee_id";
			$bindData["employee_id"] = $semployee_id;
		}
		$strextra1 .= " AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2))";
		$strextra2 = "";
		$scategory_id = '';
		if($sscategory_id !=''){
			$productIds = array();
			$prodSql = "SELECT product_id FROM product WHERE accounts_id = $prod_cat_man AND category_id = $sscategory_id";
			$prodObj = $this->db->query($prodSql, array());
			if($prodObj){
				while($onerow = $prodObj->fetch(PDO::FETCH_OBJ)){
					$productIds[$onerow->product_id] = '';
				}
			}
			if(empty($productIds)){
				$productIds = array('0'=>'');
			}
			$strextra2 = " AND pos_cart.item_id IN (".implode(', ', array_keys($productIds)).")";
		}

		$sqlquery = "SELECT pos_cart.item_id, pos.employee_id $strextra1 $strextra2 GROUP BY pos_cart.item_id ORDER BY pos_cart.item_id ASC";
		$jsonResponse['sqlquery'] = $sqlquery;		
		$jsonResponse['bindData'] = $bindData;
		$query = $this->db->querypagination($sqlquery, $bindData);
		$categoryIds = $employeeIds = $productInfo = array();
		if($query){
			$productIds = array();
			foreach($query as $onegrouprow){
				$productIds[$onegrouprow['item_id']] = '';
				$employeeIds[$onegrouprow['employee_id']] = '';
			}
			if(array_key_exists('0', $productIds)){
				$productInfo[0][0] = '';
			}
			if(!empty($productIds)){
				$prodSql = "SELECT product.category_id, product.product_id, category.category_name FROM product LEFT JOIN category ON (product.category_id = category.category_id) WHERE product.accounts_id = $prod_cat_man AND product.product_id IN (".implode(', ', array_keys($productIds)).")";
				$prodObj = $this->db->query($prodSql, array());
				if($prodObj){
					while($onerow = $prodObj->fetch(PDO::FETCH_OBJ)){
						$categoryIds[$onerow->category_id] = $onerow->category_name;
						$productInfo[$onerow->category_id][$onerow->product_id] = '';
					}
				}
			}

			$empSql = "SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id";
			if(!empty($employeeIds)){
				$empSql .= " AND user_id IN (".implode(', ', array_keys($employeeIds)).")";
			}
			$empObj = $this->db->query($empSql, array());
			if($empObj){
				while($empOneRow = $empObj->fetch(PDO::FETCH_OBJ)){
					$employeeIds[$empOneRow->user_id] = trim("$empOneRow->user_first_name $empOneRow->user_last_name");
				}
			}
			if(array_key_exists('0', $productIds) && !in_array('0', $categoryIds)){
				$categoryIds[0] = '&nbsp;';
			}
		}
		
        $jsonResponse['login'] = '';
		$jsonResponse['categoryIds'] = $categoryIds;
		$jsonResponse['employeeIds'] = $employeeIds;
		
		$totalqty = 0;
		if(!empty($categoryIds)){
			asort($categoryIds);
			
			foreach($categoryIds as $category_id=>$category_name){
				$productIds = $productInfo[$category_id];
				$substrextra = array();
				$subrowtotalprice = $subrowtotalqty = $subrowtotalcost = $subrowtotaldiscount = $subrowgrandtotal = 0;

				$sumsql = "SELECT pos_cart.item_id, pos_cart.description, pos_cart.pos_cart_id, pos_cart.item_type, pos_cart.sales_price, pos_cart.shipping_qty, pos_cart.discount_is_percent, pos_cart.discount, pos_cart.ave_cost $strextra1 AND pos_cart.item_id IN (".implode(', ', array_keys($productIds)).") ORDER BY pos_cart.description ASC, pos_cart.item_id ASC";
				$sumquery = $this->db->querypagination($sumsql, $bindData);
				if($sumquery){
					$num_rows = count($sumquery);
					if($num_rows>0){

						$prevDescription = '';

						for($r=0; $r<$num_rows; $r++){
							
							$pos_cartrow = $sumquery[$r];

							$description = stripslashes(trim((string) $pos_cartrow['description']));
							$item_id = $pos_cartrow['item_id'];
							$nextDescription = '';
							if(($r+1)<$num_rows){
								$nextrow = $sumquery[$r+1];
								$nextDescription = stripslashes(trim((string) $nextrow['description']));
							}

							if($description != $prevDescription){
								$rowtotalprice = $rowtotalqty = $rowtotalcost = $rowtotaldiscount = $rowgrandtotal = 0;
							}

							$prevDescription = $description;
							
							$pos_cart_id = $pos_cartrow['pos_cart_id'];
							$item_type = $pos_cartrow['item_type'];
							
							$sales_price = round($pos_cartrow['sales_price'],2);
							$qty = $shipping_qty = floatval($pos_cartrow['shipping_qty']);
							$qtyvalue = round($sales_price*$shipping_qty,2);
							$ave_cost = round($pos_cartrow['ave_cost'],2);
							$qtycost = round($ave_cost*$shipping_qty,2);
							
							$discount_is_percent = $pos_cartrow['discount_is_percent'];
							$discount = $pos_cartrow['discount'];
							if($discount_is_percent>0){
								$discount_value = round($qtyvalue*0.01*$discount,2);
							}
							else{
								$discount_value = round($discount*$qty,2);
							}

							$rowtotalprice += $qtyvalue;
							$rowtotalqty += $qty;
							$rowtotalcost += $qtycost;
							$rowtotaldiscount += $discount_value;
							$unitgrandtotal = $qtyvalue-$discount_value;
							$rowgrandtotal += $unitgrandtotal;
							
							if($description != $nextDescription){
								$subrowtotalprice += $rowtotalprice;
								$subrowtotalqty += $rowtotalqty;
								$subrowtotalcost += $rowtotalcost;
								$subrowtotaldiscount += $rowtotaldiscount;
								$subrowgrandtotal += $rowgrandtotal;
								
								$unitprice = $rowtotalprice;
								if($rowtotalqty>0){
									$unitprice = round($rowtotalprice/$rowtotalqty,2);
								}

								$rowqtyprofitval = $rowtotalprice-$rowtotaldiscount-$rowtotalcost;
								$rowqtyprofit = 0;
								if(($rowtotalprice-$rowtotaldiscount) !=0){
									$rowqtyprofit = round(($rowqtyprofitval*100)/($rowtotalprice-$rowtotaldiscount),2);
								}

								
								if($showing_type=='Details'){
									$substrextra[] = array($description, round($rowtotalqty,2), round($rowtotaldiscount,2), round($rowgrandtotal,2), round($rowtotalcost,2), round($rowqtyprofitval,2), round($rowqtyprofit,2));
								}
							}
						}
					}
				}
				
				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
				}
				
				$tableData[] = array('category_name'=>$category_name, 'subrowtotalqty'=>round($subrowtotalqty,2), 'subrowtotaldiscount'=>round($subrowtotaldiscount,2), 
				'subrowgrandtotal'=>round($subrowgrandtotal,2), 'subrowtotalcost'=>round($subrowtotalcost,2), 
				'subrowtotalprice'=>round($subrowtotalprice,2), 'boldclass'=>$boldclass, 'substrextra'=>$substrextra);
			}
		}

		$jsonResponse['tableData'] = $tableData;

		return json_encode($jsonResponse);
    }

	public function sales_by_Tax(){}
	
    public function sales_by_TaxData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$Common = new Common($this->db);
		
		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d',strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$strextra = " FROM pos WHERE accounts_id = $accounts_id AND pos_publish = 1 AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2))";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}
		$strextra .= " GROUP BY taxes_name1, taxes_percentage1, taxes_name2, taxes_percentage2 ORDER BY taxes_name1, taxes_percentage1, taxes_name2, taxes_percentage2";

		$sqlquery = "SELECT pos_id, taxes_name1, taxes_percentage1, taxes_name2, taxes_percentage2, sales_datetime $strextra";
		$query = $this->db->querypagination($sqlquery, $bindData);
		
        $jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		if($query){
			foreach($query as $onegrouprow){

				$taxes_name1 = $onegrouprow['taxes_name1'];
				$taxes_percentage1 = $onegrouprow['taxes_percentage1'];
				$taxes_name2 = $onegrouprow['taxes_name2'];
				$taxes_percentage2 = $onegrouprow['taxes_percentage2'];

				$sumcondition = "pos.taxes_name1 = '$taxes_name1' AND pos.taxes_percentage1 = $taxes_percentage1 AND pos.taxes_name2 = '$taxes_name2' AND pos.taxes_percentage2 = $taxes_percentage2";

				if($startdate !='' && $enddate !=''){
					$sumcondition .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
				}

				$detailsfields = '';
				$rtaxable_total = $rowtotaltaxes1 = $rowtotaltaxes2 = $rowtotalnontaxable = $rgrandtotal = 0.00;
				if($showing_type=='Details'){
					$detailsfields = "pos.taxes_name1, pos.taxes_name2, pos.invoice_no, pos.customer_id, pos.sales_datetime,";
				}

				$sumsql = "SELECT pos.pos_id, pos_cart.sales_price, pos_cart.qty, pos_cart.shipping_qty, pos_cart.taxable, pos_cart.discount_is_percent, pos_cart.discount, $detailsfields pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2)) AND $sumcondition AND pos.pos_id = pos_cart.pos_id ORDER BY pos.pos_id ASC";
				$sumquery = $this->db->querypagination($sumsql, $bindData);
				$substrextra = array();
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
								$taxable_total = $totalnontaxable = 0.00;
							}

							$prevpos_id = $pos_id;

							$sales_price = round($pos_cartrow['sales_price'],2);
							$qty = $pos_cartrow['qty'];
							$shipping_qty = floatval($pos_cartrow['shipping_qty']);

							$qtyvalue = round($sales_price*$shipping_qty,2);

							$discount_is_percent = $pos_cartrow['discount_is_percent'];
							$discount = $pos_cartrow['discount'];
							if($discount_is_percent>0){
								$discount_value = round($qtyvalue*0.01*$discount,2);
							}
							else{
								$discount_value = round($discount*$shipping_qty,2);
							}

							$taxable = $pos_cartrow['taxable'];
							if($taxable>0){
								$taxable_total = $taxable_total+$qtyvalue-$discount_value;
							}
							else{
								$totalnontaxable = $totalnontaxable+$qtyvalue-$discount_value;
							}

							if($pos_id != $nextpos_id){
								$taxes_total1 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage1'], $pos_cartrow['tax_inclusive1']);
								$taxes_total2 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage2'], $pos_cartrow['tax_inclusive2']);

								$tax_inclusive1 = $pos_cartrow['tax_inclusive1'];
								$tax_inclusive2 = $pos_cartrow['tax_inclusive2'];

								$rtaxable_total = $rtaxable_total+$taxable_total;
								$rowtotaltaxes1 = $rowtotaltaxes1+$taxes_total1;
								$rowtotaltaxes2 = $rowtotaltaxes2+$taxes_total2;
								$rowtotalnontaxable = $rowtotalnontaxable+$totalnontaxable;

								$grandtotal = $taxable_total+$taxes_total1+$taxes_total2+$totalnontaxable;
								if($tax_inclusive1>0){
									$grandtotal -= $taxes_total1;
								}
								if($tax_inclusive2>0){
									$grandtotal -= $taxes_total2;
								}
								$rgrandtotal += $grandtotal;

								if($showing_type=='Details'){
									$invoice_no = $pos_cartrow['invoice_no'];
									$customer_id = $pos_cartrow['customer_id'];
									$customersObj = $this->db->query("SELECT first_name, last_name FROM customers WHERE customers_id = $customer_id", array());
									if($customersObj){
										$customersOneRow = $customersObj->fetch(PDO::FETCH_OBJ);
										$customername = stripslashes(trim("$customersOneRow->first_name $customersOneRow->last_name"));
									}
									
									$dtaxes_name1 = $pos_cartrow['taxes_name1'];
									$dtaxes_percentage1 = $pos_cartrow['taxes_percentage1'];

									$dtaxes_name2 = $pos_cartrow['taxes_name2'];
									$dtaxes_percentage2 = $pos_cartrow['taxes_percentage2'];

									$substrextra[] = array($dtaxes_name1, $invoice_no, round($taxable_total,2), round($dtaxes_percentage1,3), round($taxes_total1,2), $dtaxes_name2, round($dtaxes_percentage2,3), round($taxes_total2,2), round($totalnontaxable,2), round($grandtotal,2));
								
								}
							}
						}
					}
				}
				
				$taxes2class = ' class="taxes2"';
				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
					$taxes2class = ' class="taxes2 txt14bold"';
				}
				
				$tableData[] = array('taxes_name1'=>$taxes_name1, 'rtaxable_total'=>round($rtaxable_total,2), 'taxes_percentage1'=>round($taxes_percentage1,3), 
				'rowtotaltaxes1'=>round($rowtotaltaxes1,2), 'taxes_name2'=>$taxes_name2, 'taxes_percentage2'=>round($taxes_percentage2,3), 
				'rowtotaltaxes2'=>round($rowtotaltaxes2,2), 'rowtotalnontaxable'=>round($rowtotalnontaxable,2), 'rgrandtotal'=>round($rgrandtotal,2),
				 'boldclass'=>$boldclass, 'taxes2class'=>$taxes2class, 'substrextra'=>$substrextra);
			}
		}
		$jsonResponse['tableData'] = $tableData;

		return json_encode($jsonResponse);
    }	
}
?>