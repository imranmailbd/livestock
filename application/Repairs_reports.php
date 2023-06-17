<?php
class Repairs_reports{
	
	protected $db;
	public string $pageTitle;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
    public function AJ_lists_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$statusOpt = array();
		$statusSql="SELECT status FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1 GROUP BY status	ORDER BY status ASC";
		$statusObj = $this->db->query($statusSql, array());
		if($statusObj){
			while($oneStatus = $statusObj->fetch(PDO::FETCH_OBJ)){
				$status = trim((string) $oneStatus->status);
				if($status !='')
					$statusOpt[$status] = '';
			}
			$statusOpt = array_keys($statusOpt);
		}
		$jsonResponse['statusOpt'] = $statusOpt;

		$problemOpt = array();
		$problemSql="SELECT problem FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1 GROUP BY problem ORDER BY problem ASC";
		$problemObj = $this->db->query($problemSql, array());
		if($problemObj){
			while($oneProblem = $problemObj->fetch(PDO::FETCH_OBJ)){
				$problem = trim((string) $oneProblem->problem);
				if($problem !='')
					$problemOpt[$problem] = '';
			}
			$problemOpt = array_keys($problemOpt);
		}
		$jsonResponse['problemOpt'] = $problemOpt;

		return json_encode($jsonResponse);
	}
	
    public function AJ_repairs_by_status_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$statusOpt = array();
		$statusSql="SELECT status FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1 GROUP BY status	ORDER BY status ASC";
		$statusObj = $this->db->query($statusSql, array());
		if($statusObj){
			while($oneStatus = $statusObj->fetch(PDO::FETCH_OBJ)){
				$status = trim((string) $oneStatus->status);
				if($status !='')
					$statusOpt[$status] = '';
			}
			$statusOpt = array_keys($statusOpt);
		}
		$jsonResponse['statusOpt'] = $statusOpt;

		$problemOpt = array();
		$problemSql="SELECT problem FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1 GROUP BY problem ORDER BY problem ASC";
		$problemObj = $this->db->query($problemSql, array());
		if($problemObj){
			while($oneProblem = $problemObj->fetch(PDO::FETCH_OBJ)){
				$problem = trim((string) $oneProblem->problem);
				if($problem !='')
					$problemOpt[$problem] = '';
			}
			$problemOpt = array_keys($problemOpt);
		}
		$jsonResponse['problemOpt'] = $problemOpt;

		return json_encode($jsonResponse);
	}
	
	public function repairs_by_status(){}	
    
    public function repairs_by_statusData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$timeformat = $_SESSION["timeformat"]??'';
		
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$sstatus = $POST['status']??'';
		$Common = new Common($this->db);
		
		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d',strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$strextra = "FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (created_on BETWEEN :startdate AND :enddate)";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		if($sstatus !=''){
			$strextra .= " AND status = :status";
			$bindData["status"] = $sstatus;
		}

		$statusArray = array();
		$detailsfields = "";
		if($showing_type=='Details'){
			$detailsfields = " repairs_id, ticket_no, customer_id,";
			$posIdsData = $customerIds = array();
		}
		$reparsPosIds = array();
		$sqlquery = "SELECT pos_id,$detailsfields status $strextra";
		$sqlquery .= " ORDER BY status ASC";
		$query = $this->db->query($sqlquery, $bindData);
		if($query){
			while($onegrouprow = $query->fetch(PDO::FETCH_OBJ)){
				if($onegrouprow->pos_id>0){$reparsPosIds[$onegrouprow->pos_id] = '';}
				$statusArray[$onegrouprow->status][] = $onegrouprow->pos_id;
				if($showing_type=='Details'){
					$posIdsData[$onegrouprow->pos_id] = array('repairs_id'=>$onegrouprow->repairs_id, 'ticket_no'=>$onegrouprow->ticket_no, 'customer_id'=>$onegrouprow->customer_id);
					if($onegrouprow->customer_id>0 && !in_array($onegrouprow->customer_id, $customerIds)){array_push($customerIds, $onegrouprow->customer_id);}
				}
			}
		}

		if(!empty($statusArray)){
			$posIdsDues = array();
			//$rtaxable_total = $rtaxable_totalexcl = $rNontaxable_total = $rCost = $rowtotalprice = 0.00;
				
			$sumsql = "SELECT pos.pos_id, pos_cart.sales_price, pos_cart.shipping_qty, pos_cart.discount_is_percent, pos_cart.discount, pos_cart.taxable, pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 FROM pos, pos_cart WHERE pos.pos_id IN (".implode(', ', array_keys($reparsPosIds)).") AND pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_type = 'Repairs' AND pos.pos_id = pos_cart.pos_id ORDER BY pos.pos_id ASC, pos_cart.pos_cart_id ASC";
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
							$taxable_total = $Nontaxable_total = $qtytotalalue = 0.00;
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
							$Nontaxable_total = $Nontaxable_total+$qtyvalue-$discount_value;
						}

						if($pos_id != $nextpos_id){
							$taxes_total1 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage1'], $pos_cartrow['tax_inclusive1']);
							$taxes_total2 = $Common->calculateTax($taxable_total, $pos_cartrow['taxes_percentage2'], $pos_cartrow['tax_inclusive2']);

							$tax_inclusive1 = $pos_cartrow['tax_inclusive1'];
							$tax_inclusive2 = $pos_cartrow['tax_inclusive2'];

							$Taxes = $taxes_total1+$taxes_total2;
							
							$GrandTotal = $taxable_total+$taxes_total1+$taxes_total2+$Nontaxable_total;
							if($tax_inclusive1>0){
								$GrandTotal -= $taxes_total1;
							}
							if($tax_inclusive2>0){
								$GrandTotal -= $taxes_total2;
							}
							
							$totalpayment = 0;
							$paymentSql = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
							$paymentObj = $this->db->query($paymentSql, array());
							if($paymentObj){
								$totalpayment = $paymentObj->fetch(PDO::FETCH_OBJ)->totalpayment;
							}
							
							$posIdsDues[$pos_id] = array($GrandTotal, $totalpayment);
						}
					}
				}
			}
			/*
			$posSql = "SELECT SUM(CASE WHEN pc.taxable>0 AND pc.discount_is_percent>0 THEN (pc.sales_price*pc.shipping_qty)-(pc.sales_price*pc.shipping_qty*pc.discount/100) WHEN pc.taxable>0 AND pc.discount_is_percent=0 THEN (pc.sales_price*pc.shipping_qty)-(pc.shipping_qty*pc.discount) ELSE 0 END) AS taxableTotal, 
						SUM(CASE WHEN pc.taxable=0 AND pc.discount_is_percent>0 THEN (pc.sales_price*pc.shipping_qty)-(pc.sales_price*pc.shipping_qty*pc.discount/100) WHEN pc.taxable=0 AND pc.discount_is_percent=0 THEN (pc.sales_price*pc.shipping_qty)-(pc.shipping_qty*pc.discount) ELSE 0 END) AS nonTaxableTotal,
						pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, pos.pos_id 
						FROM pos, pos_cart pc WHERE pos.pos_id IN (".implode(', ', array_keys($reparsPosIds)).") AND pos.accounts_id = $accounts_id AND ((pos.is_due>0 AND pos.order_status = 2) OR pos.order_status = 1) AND pos.pos_type = 'Repairs' AND pos.pos_id = pc.pos_id AND pos.pos_publish = 1 
						 GROUP BY pos.pos_id ORDER BY pos.pos_id ASC";
			$posDueData = $this->db->querypagination($posSql, array());
			if($posDueData){
				foreach($posDueData as $oneRow){
					$pos_id = $oneRow['pos_id'];
					$taxable_total = $oneRow['taxableTotal'];
					$totalnontaxable = $oneRow['nonTaxableTotal'];
					$taxes_total1 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
					$taxes_total2 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);

					$tax_inclusive1 = $oneRow['tax_inclusive1'];
					$tax_inclusive2 = $oneRow['tax_inclusive2'];

					$taxestotal = $taxes_total1+$taxes_total2;
					$grand_total = $taxable_total+$taxestotal+$totalnontaxable;
					if($tax_inclusive1>0){
						$grand_total -= $taxes_total1;
					}
					if($tax_inclusive2>0){
						$grand_total -= $taxes_total2;
					}

					$totalpayment = 0;
					$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
					$queryObj = $this->db->query($sqlquery, array());
					if($queryObj){
						$totalpayment = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
					}

					if($totalpayment<$grand_total){
						$posIdsDues[$pos_id] = array($grand_total, $totalpayment);
					}
				}
			}
			*/
			if($showing_type=='Details'){
				$customerNames = array();
				if(!empty($customerIds)){
					$customersSql = "SELECT customers_id, first_name, last_name FROM customers WHERE customers_id IN (".implode(', ', $customerIds).") AND accounts_id = $prod_cat_man";
					$customersObj = $this->db->query($customersSql, array());
					if($customersObj){
						while($oneRow = $customersObj->fetch(PDO::FETCH_OBJ)){
							$customerNames[$oneRow->customers_id] = stripslashes(trim("$oneRow->first_name $oneRow->last_name"));
						}
					}
				}
			}

			foreach($statusArray as $status=> $posIds){
				$statusDuesvalues = array(0, 0);
				$statusDetails = array();
				foreach($posIds as $onePosId){
					$duesValues = $posIdsDues[$onePosId]??array(0, 0);
					$statusDuesvalues = array($statusDuesvalues[0]+$duesValues[0], $statusDuesvalues[1]+$duesValues[1]);
					
					if($showing_type=='Details'){
						if(array_key_exists($onePosId, $posIdsData)){
							$repairsData = $posIdsData[$onePosId];
							$repairs_id = $repairsData['repairs_id']??0;							
							$ticket_no = $repairsData['ticket_no']??0;							
							$customer_id = $repairsData['customer_id']??0;
							$customerName = $customerNames[$customer_id]??'';
							$statusDetails[] = array($ticket_no, $repairs_id, $customerName, $customer_id, round($duesValues[0]-$duesValues[1],2), $duesValues);
						}
					}
				}
				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
				}

				$tableData[] = array('status'=>$status, 'statusDuesvalues'=>$statusDuesvalues, 'statusDetails'=>$statusDetails, 'boldclass'=>$boldclass);
			}

			$jsonResponse['posIdsDues'] = $posIdsDues;
		}

		$jsonResponse['tableData'] = $tableData;
		
		return json_encode($jsonResponse);
    }
	
    public function AJ_repairs_by_problem_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();		
		$accounts_id = $_SESSION["accounts_id"]??0;

		$problemOpt = array();
		$problemSql="SELECT problem FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1 GROUP BY problem ORDER BY problem ASC";
		$problemObj = $this->db->query($problemSql, array());
		if($problemObj){
			while($oneProblem = $problemObj->fetch(PDO::FETCH_OBJ)){
				$problem = trim((string) $oneProblem->problem);
				if($problem !='')
					$problemOpt[$problem] = '';
			}
			$problemOpt = array_keys($problemOpt);
		}
		$jsonResponse['problemOpt'] = $problemOpt;

		return json_encode($jsonResponse);
	}
	
	public function repairs_by_problem(){}	
    
    public function repairs_by_problemData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$timeformat = $_SESSION["timeformat"]??'';
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$sproblem = $POST['problem']??'';
		$Common = new Common($this->db);
		
		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d',strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$strextra = "FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (created_on BETWEEN :startdate AND :enddate)";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		if($sproblem !=''){
			$strextra .= " AND problem = :problem";
			$bindData["problem"] = $sproblem;
		}

		$problemArray = array();
		$detailsfields = "";
		if($showing_type=='Details'){
			$detailsfields = " repairs_id, ticket_no, customer_id,";
			$posIdsData = $customerIds = array();
		}
		$reparsPosIds = array();
		$sqlquery = "SELECT pos_id,$detailsfields problem $strextra";
		$sqlquery .= " ORDER BY problem ASC";
		$query = $this->db->query($sqlquery, $bindData);
		if($query){
			while($onegrouprow = $query->fetch(PDO::FETCH_OBJ)){
				if($onegrouprow->pos_id>0 && !in_array($onegrouprow->pos_id, $reparsPosIds)){array_push($reparsPosIds, $onegrouprow->pos_id);}
				$problemArray[$onegrouprow->problem][] = $onegrouprow->pos_id;
				if($showing_type=='Details'){
					$posIdsData[$onegrouprow->pos_id] = array('repairs_id'=>$onegrouprow->repairs_id, 'ticket_no'=>$onegrouprow->ticket_no, 'customer_id'=>$onegrouprow->customer_id);
					if($onegrouprow->customer_id>0 && !in_array($onegrouprow->customer_id, $customerIds)){array_push($customerIds, $onegrouprow->customer_id);}
				}
			}
		}

		if(!empty($problemArray)){
			$posIdsDues = array();
			$posSql = "SELECT SUM(CASE WHEN pc.taxable>0 AND pc.discount_is_percent>0 THEN (pc.sales_price*pc.shipping_qty)-(pc.sales_price*pc.shipping_qty*pc.discount/100) WHEN pc.taxable>0 AND pc.discount_is_percent=0 THEN (pc.sales_price*pc.shipping_qty)-(pc.shipping_qty*pc.discount) ELSE 0 END) AS taxableTotal, 
						SUM(CASE WHEN pc.taxable=0 AND pc.discount_is_percent>0 THEN (pc.sales_price*pc.shipping_qty)-(pc.sales_price*pc.shipping_qty*pc.discount/100) WHEN pc.taxable=0 AND pc.discount_is_percent=0 THEN (pc.sales_price*pc.shipping_qty)-(pc.shipping_qty*pc.discount) ELSE 0 END) AS nonTaxableTotal,
						pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, pos.pos_id 
						FROM pos, pos_cart pc WHERE pos.pos_id IN (".implode(', ', $reparsPosIds).") AND pos.accounts_id = $accounts_id AND ((pos.is_due>0 AND pos.order_status = 2) OR pos.order_status = 1) AND pos.pos_type = 'Repairs' AND pos.pos_id = pc.pos_id AND pos.pos_publish = 1 
						 GROUP BY pos.pos_id ORDER BY pos.pos_id ASC";
			$posDueData = $this->db->querypagination($posSql, array());
			if($posDueData){
				foreach($posDueData as $oneRow){
					$pos_id = $oneRow['pos_id'];
					$taxable_total = $oneRow['taxableTotal'];
					$totalnontaxable = $oneRow['nonTaxableTotal'];
					$taxes_total1 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
					$taxes_total2 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);

					$tax_inclusive1 = $oneRow['tax_inclusive1'];
					$tax_inclusive2 = $oneRow['tax_inclusive2'];

					$taxestotal = $taxes_total1+$taxes_total2;
					$grand_total = $taxable_total+$taxestotal+$totalnontaxable;
					if($tax_inclusive1>0){
						$grand_total -= $taxes_total1;
					}
					if($tax_inclusive2>0){
						$grand_total -= $taxes_total2;
					}

					$totalpayment = 0;
					$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
					$queryObj = $this->db->query($sqlquery, array());
					if($queryObj){
						$totalpayment = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
					}

					if($totalpayment<$grand_total){
						$posIdsDues[$pos_id] = $grand_total-$totalpayment;
					}
				}
			}

			if($showing_type=='Details'){
				$customerNames = array();
				if(!empty($customerIds)){
					$customersSql = "SELECT customers_id, first_name, last_name FROM customers WHERE customers_id IN (".implode(', ', $customerIds).") AND accounts_id = $prod_cat_man";
					$customersObj = $this->db->query($customersSql, array());
					if($customersObj){
						while($oneRow = $customersObj->fetch(PDO::FETCH_OBJ)){
							$customerNames[$oneRow->customers_id] = stripslashes(trim("$oneRow->first_name $oneRow->last_name"));
						}
					}
				}
			}

			foreach($problemArray as $problem=> $posIds){
				$problemDuesvalue = 0;
				$statusDetails = array();
				foreach($posIds as $onePosId){
					$duesValue = $posIdsDues[$onePosId]??0;
					$problemDuesvalue += $duesValue;

					if($showing_type=='Details'){
						if(array_key_exists($onePosId, $posIdsData)){
							$repairsData = $posIdsData[$onePosId];
							$repairs_id = $repairsData['repairs_id']??0;
							$ticket_no = $repairsData['ticket_no']??0;							
							$customer_id = $repairsData['customer_id']??0;
							$customerName = $customerNames[$customer_id]??'';
							
							$statusDetails[] = array($ticket_no, $repairs_id, $customerName, $customer_id, round($duesValue,2));
						}
					}
				}
				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
				}
				$tableData[] = array('problem'=>$problem, 'problemDuesvalue'=>round($problemDuesvalue,2), 'statusDetails'=>$statusDetails, 'boldclass'=>$boldclass);
			}
		}
		$jsonResponse['tableData'] = $tableData;
		
		return json_encode($jsonResponse);
    }
	
	public function sales_by_Technician(){}	
    
    public function sales_by_TechnicianData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$timeformat = $_SESSION["timeformat"]??'';
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$sassign_to = $POST['assign_to']??'';

		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d',strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$sassign_toArray = $bindData = array();
		$sqlquery = "SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id";
		
		if($sassign_to !=''){
			$seleced_search = addslashes(trim((string) $sassign_to));
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
				$sassign_toArray[$oneRow->user_id] = trim("$oneRow->user_first_name $oneRow->user_last_name");
			}
		}

		$strextra = "WHERE pos.accounts_id = $accounts_id 
						AND pos.pos_publish = 1 							
						AND pos.pos_type ='Repairs' 
						AND pos.order_status = 2";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		if($sassign_to !='' && !empty($sassign_toArray)){
			$strextra .= " AND repairs.assign_to IN (".implode(', ', array_keys($sassign_toArray)).")";
		}

		$sqlquery = "SELECT repairs.assign_to 
					FROM pos, repairs 
					$strextra 
					AND pos.pos_id = repairs.pos_id 
					GROUP BY repairs.assign_to ORDER BY repairs.assign_to ASC";

		$query = $this->db->querypagination($sqlquery, $bindData);
		$assign_toIds = $scustomer_idArray = array();
		if($query){
			foreach($query as $onegrouprow){
				$assign_to = $onegrouprow['assign_to'];
				$assign_toname = stripslashes($sassign_toArray[$assign_to]??'');

				$assign_toIds[$assign_to] = $assign_toname;
			}
		}
		
		if($showing_type=='Details'){
			$sqlquery = "SELECT customers_id, first_name, last_name FROM customers WHERE accounts_id = $prod_cat_man";
			$query = $this->db->query($sqlquery, array());
			if($query){
				while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
					$scustomer_idArray[$oneRow->customers_id] = trim("$oneRow->first_name $oneRow->last_name");
				}
			}
		}

		$totalprice = $gtaxable_totalexcl = $totalcost = $totaldiscount = 0;

		if($assign_toIds !=''){

			asort($assign_toIds);
			foreach($assign_toIds as $assign_to=>$technician){

				$detailsfields = '';
				$substrextra = array();
				$subrowtotalprice = $subrtaxable_totalexcl = $subrowtotalqty = $subrowgrandtotal = 0;
				if($showing_type=='Details'){
					$detailsfields = "pos.invoice_no, pos.sales_datetime, pos.customer_id,";
				}

				$sumsql = "SELECT $detailsfields pos_cart.item_id, pos_cart.description, pos_cart.sales_price, pos_cart.shipping_qty, pos_cart.discount_is_percent, pos_cart.discount  
							FROM pos, repairs, pos_cart 
							WHERE pos.accounts_id = $accounts_id 
								AND pos.pos_publish = 1 							
								AND pos.pos_type ='Repairs' 
								AND pos.order_status = 2 
								AND pos_cart.item_type IN ('product', 'one_time') 
								AND repairs.assign_to = $assign_to";
				$bindData = array();
				if($startdate !='' && $enddate !=''){
					$sumsql .= " AND (pos.sales_datetime BETWEEN :startdate AND :enddate)";
					$bindData["startdate"] = $startdate;
					$bindData["enddate"] = $enddate;
				}
				$sumsql .= " AND pos.pos_id = pos_cart.pos_id 									
							AND pos.pos_id = repairs.pos_id 
							ORDER BY pos_cart.description ASC, pos_cart.item_id ASC, pos_cart.pos_cart_id ASC";
				$sumquery = $this->db->querypagination($sumsql, $bindData);
				if($sumquery){
					$num_rows = count($sumquery);
					if($num_rows>0){

						$previtem_id = 'x';

						for($r=0; $r<$num_rows; $r++){
							$pos_cartrow = $sumquery[$r];
							
							$item_id = $pos_cartrow['item_id'];
							$nextitem_id = 'x';
							if(($r+1)<$num_rows){
								$nextrow = $sumquery[$r+1];
								$nextitem_id = $nextrow['item_id'];
							}

							if($item_id != $previtem_id){
								$rowtotalprice = $rtaxable_totalexcl = $rowtotalqty = $rowtotalcost = $rowtotaldiscount = $rowgrandtotal = 0;
								$subsubstrextra = array();
							}

							$previtem_id = $item_id;

							$description = stripslashes(trim((string) $pos_cartrow['description']));

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

							$rowtotalprice = $rowtotalprice+$qtyvalue;
							$rowtotalqty = $rowtotalqty+$shipping_qty;
							$unitgrandtotal = $qtyvalue-$discount_value;
							$rowgrandtotal = $rowgrandtotal+$unitgrandtotal;

							if($showing_type=='Details'){
								$invoice_no = $pos_cartrow['invoice_no'];
								$customer_id = $pos_cartrow['customer_id'];
								$customername = $scustomer_idArray[$customer_id]??'';
								
								$subsubstrextra[] = array($pos_cartrow['sales_datetime'], $invoice_no, $customer_id, $customername, round($shipping_qty,2), round($sales_price,2), round($unitgrandtotal,2));
													
							}
							
							if($item_id != $nextitem_id){
								$unitprice = $rowtotalprice;
								if($rowtotalqty>0){
									$unitprice = round($rowtotalprice/$rowtotalqty,2);
								}

								$subrowtotalprice = $subrowtotalprice+$rowtotalprice;
								$subrowtotalqty = $subrowtotalqty+$rowtotalqty;
								$subrowgrandtotal = $subrowgrandtotal+$rowgrandtotal;

								if($showing_type=='Details'){
									$substrextra[] = array('description'=>$description, 'rowtotalqty'=>round($rowtotalqty,2), 'unitprice'=>round($unitprice,2), 'rowgrandtotal'=>round($rowgrandtotal,2), 'subsubstrextra'=>$subsubstrextra);
								}
							}
						}
					}
				}

				$unitprice = $subrowtotalprice;
				if($subrowtotalqty>0){
					$unitprice = round($subrowtotalprice/$subrowtotalqty,2);
				}

				$totalprice = $totalprice+$subrowtotalprice;

				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
				}
				$tableData[] = array('technician'=>$technician, 'subrowtotalqty'=>round($subrowtotalqty,2), 'unitprice'=>round($unitprice,2), 'subrowgrandtotal'=>round($subrowgrandtotal,2), 'boldclass'=>$boldclass, 'substrextra'=>$substrextra);
			}
		}

		$jsonResponse['tableData'] = $tableData;
		
		return json_encode($jsonResponse);
    }
	
    public function AJ_repair_Tickets_Created_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$statusOpt = array();
		$statusSql="SELECT status FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1 GROUP BY status	ORDER BY status ASC";
		$statusObj = $this->db->query($statusSql, array());
		if($statusObj){
			while($oneStatus = $statusObj->fetch(PDO::FETCH_OBJ)){
				$status = trim((string) $oneStatus->status);
				if($status !='')
					$statusOpt[$status] = '';
			}
			$statusOpt = array_keys($statusOpt);
		}
		$jsonResponse['statusOpt'] = $statusOpt;

		return json_encode($jsonResponse);
	}
	
	public function repair_Tickets_Created(){}	
    
    public function repair_Tickets_CreatedData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$timeformat = $_SESSION["timeformat"]??'';
		$sales_date = $POST['sales_date']??'';
		$showing_type = $POST['showing_type']??'';
		$sstatus = $POST['status']??'';
		$Common = new Common($this->db);
		
		$startdate = $enddate = '';
		if($sales_date !='' && $sales_date !='null'){
			$sales_datearray = explode(' - ', $sales_date);
			if(is_array($sales_datearray) && count($sales_datearray)>1){
				$startdate = date('Y-m-d',strtotime($sales_datearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($sales_datearray[1])).' 23:59:59';
			}
		}

		$strextra = "FROM repairs WHERE accounts_id = $accounts_id AND repairs_publish = 1";
		$bindData = $dateArray = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (created_on BETWEEN :startdate AND :enddate)";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		if($sstatus !=''){
			$strextra .= " AND status = :status";
			$bindData["status"] = $sstatus;
		}
		
		$detailsfields = "";
		if($showing_type=='Details'){
			$detailsfields = " repairs_id, ticket_no, customer_id, status,";
			$posIdsData = array();
			$customerIds = array();
		}
		$reparsPosIds = array();
		$sqlquery = "SELECT pos_id,$detailsfields created_on $strextra";
		$sqlquery .= " ORDER BY created_on ASC";
		$query = $this->db->query($sqlquery, $bindData);
		if($query){
			while($onegrouprow = $query->fetch(PDO::FETCH_OBJ)){
				if($onegrouprow->pos_id>0 && !in_array($onegrouprow->pos_id, $reparsPosIds)){array_push($reparsPosIds, $onegrouprow->pos_id);}
				$dateArray[substr($onegrouprow->created_on,0,10)][] = $onegrouprow->pos_id;
				if($showing_type=='Details'){
					$posIdsData[$onegrouprow->pos_id] = array('repairs_id'=>$onegrouprow->repairs_id, 'ticket_no'=>$onegrouprow->ticket_no, 'customer_id'=>$onegrouprow->customer_id);
					if($onegrouprow->customer_id>0 && !in_array($onegrouprow->customer_id, $customerIds)){array_push($customerIds, $onegrouprow->customer_id);}
				}
			}
		}

		if(!empty($dateArray)){
			$posIdsGrandTotal = array();
			$posSql = "SELECT SUM(CASE WHEN pc.taxable>0 AND pc.discount_is_percent>0 THEN (pc.sales_price*pc.qty)-(pc.sales_price*pc.qty*pc.discount/100) WHEN pc.taxable>0 AND pc.discount_is_percent=0 THEN (pc.sales_price*pc.qty)-(pc.qty*pc.discount) ELSE 0 END) AS taxableTotal, 
						SUM(CASE WHEN pc.taxable=0 AND pc.discount_is_percent>0 THEN (pc.sales_price*pc.qty)-(pc.sales_price*pc.qty*pc.discount/100) WHEN pc.taxable=0 AND pc.discount_is_percent=0 THEN (pc.sales_price*pc.qty)-(pc.qty*pc.discount) ELSE 0 END) AS nonTaxableTotal,
						pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, pos.pos_id 
						FROM pos, pos_cart pc WHERE pos.pos_id IN (".implode(', ', $reparsPosIds).") AND pos.accounts_id = $accounts_id AND pos.pos_type = 'Repairs' AND pos.pos_id = pc.pos_id AND pos.pos_publish = 1 
						 GROUP BY pos.pos_id ORDER BY pos.pos_id ASC";
			$posDueData = $this->db->querypagination($posSql, array());
			if($posDueData){
				foreach($posDueData as $oneRow){
					$pos_id = $oneRow['pos_id'];
					$taxable_total = $oneRow['taxableTotal'];
					$totalnontaxable = $oneRow['nonTaxableTotal'];
					$taxes_total1 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
					$taxes_total2 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);

					$tax_inclusive1 = $oneRow['tax_inclusive1'];
					$tax_inclusive2 = $oneRow['tax_inclusive2'];

					$taxestotal = $taxes_total1+$taxes_total2;
					$grand_total = $taxable_total+$taxestotal+$totalnontaxable;
					if($tax_inclusive1>0){
						$grand_total -= $taxes_total1;
					}
					if($tax_inclusive2>0){
						$grand_total -= $taxes_total2;
					}
					$posIdsGrandTotal[$pos_id] = $grand_total;
				}
			}

			if($showing_type=='Details'){
				$customerNames = array();
				if(!empty($customerIds)){
					$customersSql = "SELECT customers_id, first_name, last_name FROM customers WHERE customers_id IN (".implode(', ', $customerIds).") AND accounts_id = $prod_cat_man";
					$customersObj = $this->db->query($customersSql, array());
					if($customersObj){
						while($oneRow = $customersObj->fetch(PDO::FETCH_OBJ)){
							$customerNames[$oneRow->customers_id] = stripslashes(trim("$oneRow->first_name $oneRow->last_name"));
						}
					}
				}
			}

			foreach($dateArray as $createdOn=> $posIds){
				$createdOnGrandTotal = 0;
				$createdOnDetails = array();
				$qtyCreated = count($posIds);
				foreach($posIds as $onePosId){
					$posGrandTotal = $posGrandTotal = $posIdsGrandTotal[$onePosId]??0;
					$createdOnGrandTotal += $posGrandTotal;

					if($showing_type=='Details'){
						if(array_key_exists($onePosId, $posIdsData)){
							$repairsData = $posIdsData[$onePosId];
							$repairs_id = $repairsData['repairs_id']??0;
							$ticket_no = $repairsData['ticket_no']??0;
							$customer_id = $repairsData['customer_id']??0;
							$customerName = $customerNames[$customer_id]??'';
							
							$createdOnDetails[] = array($ticket_no, $repairs_id, $customerName, $customer_id, round($posGrandTotal,2));
						}
					}
				}
				$boldclass = '';
				if($showing_type=='Details'){
					$boldclass = 'txt14bold';
				}

				$tableData[] = array('createdOn'=>$createdOn, 'qtyCreated'=>$qtyCreated, 'createdOnGrandTotal'=>round($createdOnGrandTotal,2), 'createdOnDetails'=>$createdOnDetails, 'boldclass'=>$boldclass);
			}
		}
		
		$jsonResponse['tableData'] = $tableData;
		
		return json_encode($jsonResponse);
    }
	
}
?>