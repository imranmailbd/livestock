<?php
class End_of_Day{
	protected $db;
	private int $page, $totalRows;
	private string $sorting_type, $date_range, $eod_date, $drawer;
	
	public function __construct($db){$this->db = $db;}
	
	public function view(){}
	
	public function AJ_view_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$eod_date = $POST['eod_date']??date('Y-m-d');
		$drawer = $POST['eod_drawer']??'';
		
		$eod_date = date('Y-m-d', strtotime($eod_date));
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		
		$this->eod_date = $eod_date;
		$jsonResponse['eod_date'] = $eod_date;
		
		$multiple_cash_drawers = 0;
		$cash_drawers = '';
		$cdArray = array();
		$Common = new Common($this->db);
		$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
		if(!empty($cdData)){
			extract($cdData);
			$cdArray1 = explode('||',$cash_drawers);
			if(!empty($cdArray1)){
				$newCarr = array();
				foreach($cdArray1 as $oneCar){
					if(!empty($oneCar)){$newCarr[$oneCar] = '';}
				}
				$cdArray = array_keys($newCarr);
			}
		}
		$jsonResponse['multiple_cash_drawers'] = intval($multiple_cash_drawers);
		$jsonResponse['cash_drawers'] = $cash_drawers;
		
		$cashDrawersOptions = array();
		if(!empty($cdArray)){
			foreach($cdArray as $oneCar){
				if(!empty($oneCar)){$cashDrawersOptions[addslashes(stripslashes($oneCar))] = '';}
			}
		}
		
		$posPaymentSql = "SELECT pos_payment.drawer FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id AND pos_payment.payment_datetime BETWEEN :startdate AND :enddate AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id GROUP BY pos_payment.drawer";
		$bindData = array();
		$bindData['startdate'] = "$eod_date 00:00:00";
		$bindData['enddate'] = "$eod_date 23:59:59";
		$queryObj = $this->db->query($posPaymentSql, $bindData);
		if($queryObj){
			while($posPaymentRow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$cashDrawersOptions[addslashes(stripslashes($posPaymentRow->drawer))] = '';
			}						
		}
		$pettyCashSql = "SELECT drawer FROM petty_cash WHERE accounts_id = $accounts_id AND eod_date = :eod_date AND petty_cash_publish = 1 GROUP BY drawer ORDER BY drawer ASC";
		$pettyCashObj = $this->db->query($pettyCashSql, array('eod_date'=>$eod_date));
		if($pettyCashObj){
			while($posPaymentRow = $pettyCashObj->fetch(PDO::FETCH_OBJ)){
				$cashDrawersOptions[addslashes(stripslashes($posPaymentRow->drawer))] = '';
			}
		}

		if(!empty($cashDrawersOptions)){
			$cashDrawersOptions = array_keys($cashDrawersOptions);
			sort($cashDrawersOptions);
		}
		$jsonResponse['cashDrawersOptions'] = $cashDrawersOptions;
		
		$this->drawer = $drawer;

		$jsonResponse['loadData_EOD'] = $this->loadData_EOD();

		$showPOSMessage = 0;
		$salesman_name = '';		
		$employee_id = $_SESSION["employee_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		if($employee_id==0){ $employee_id = $user_id;}
		
		$posData = $this->db->querypagination("SELECT pos_id, employee_id, customer_id FROM pos WHERE accounts_id = $accounts_id AND user_id = $employee_id AND invoice_no = 0 AND pos_publish = 0 ORDER BY pos_id DESC LIMIT 0, 1", array());
		if($posData){
			$showPOSMessage++;
			$employee_id = $posData[0]['employee_id'];
			if($employee_id>0){
				$salesmanObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $employee_id", array());
				if($salesmanObj){
					$salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ);
					$salesman_name = trim(stripslashes("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
				}
			}
		}
		$jsonResponse['showPOSMessage'] = $showPOSMessage;
		$jsonResponse['salesman_name'] = $salesman_name;
		$jsonResponse['employee_id'] = intval($employee_id);
		$jsonResponse['user_id'] = $user_id;

		$jsonResponse['loadData_payment'] = $this->loadData_payment();
		$jsonResponse['loadData_petty_cash'] = $this->loadData_petty_cash();

		return json_encode($jsonResponse);
	}

	public function lists(){}
	
    private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sorting_type = $this->sorting_type;
		$date_range = $this->date_range;
		
		$_SESSION["current_module"] = "End_of_Day";
		$_SESSION["list_filters"] = array('sorting_type'=>$sorting_type, 'date_range'=>$date_range);
		
		$startdate = $enddate = $startdate2 = $enddate2 = '';
		if($date_range !=''){
			$activity_feeddatearray = explode(' - ', $date_range);
			if(is_array($activity_feeddatearray) && count($activity_feeddatearray)>1){
				$startdate2 = date('Y-m-d',strtotime($activity_feeddatearray[0]));
				$enddate2 = date('Y-m-d',strtotime($activity_feeddatearray[1]));
				$startdate = $startdate2.' 00:00:00';
				$enddate = $enddate2.' 23:59:59';
			}
		}
		
		$filterSql = $filterSql2 = "";
		$bindData = $bindData2 = array();
		if($startdate !='' && $enddate !=''){
			$filterSql .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
			$filterSql2 .= " AND (eod_date BETWEEN :startdate AND :enddate)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
			$bindData2['startdate'] = $startdate2;
			$bindData2['enddate'] = $enddate2;
		}
		
		$strextra ="SELECT eod_date AS dateTime FROM petty_cash WHERE accounts_id = $accounts_id $filterSql2 AND petty_cash_publish = 1 GROUP BY eod_date, drawer";
		$query = $this->db->querypagination($strextra, $bindData2);
		$endOfDayData = array();
		if($query){
			foreach($query as $oneRow){
				$endOfDayData[$oneRow['dateTime']] = '';
			}
		}

		$strextra ="SELECT SUBSTRING(pos_payment.payment_datetime, 1, 10) AS dateTime FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id $filterSql AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id GROUP BY dateTime, pos_payment.drawer";
		$query = $this->db->querypagination($strextra, $bindData);
		if($query){
			foreach($query as $oneRow){
				$endOfDayData[$oneRow['dateTime']] = '';
			}
		}

		$totalRows = 0;		
		if(!empty($endOfDayData))
			$totalRows = count($endOfDayData);
		$this->totalRows = $totalRows;
	}
	
    private function loadTableRows(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		
		$page = $this->page;
		$totalRows = $this->totalRows;
		$ssorting_type = $this->sorting_type;
		$date_range = $this->date_range;
		
		$sortingTypeData = array(0=>'dateTime ASC', 
								1=>'dateTime DESC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}

		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$startdate = $enddate = $startdate2 = $enddate2 = '';
		if($date_range !=''){
			$activity_feeddatearray = explode(' - ', $date_range);
			if(is_array($activity_feeddatearray) && count($activity_feeddatearray)>1){
				$startdate2 = date('Y-m-d',strtotime($activity_feeddatearray[0]));
				$enddate2 = date('Y-m-d',strtotime($activity_feeddatearray[1]));
				$startdate = $startdate2.' 00:00:00';
				$enddate = $enddate2.' 23:59:59';
			}
		}
		$filterSql = $filterSql2 = "";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$filterSql .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
			$filterSql2 .= " AND (eod_date BETWEEN :startdate2 AND :enddate2)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
			$bindData['startdate2'] = $startdate2;
			$bindData['enddate2'] = $enddate2;
		}
		
		$sqlquery ="SELECT eod_date AS dateTime, drawer FROM petty_cash WHERE accounts_id = $accounts_id $filterSql2 AND petty_cash_publish = 1";
		$sqlquery .= " UNION ALL ";
		$sqlquery .=" SELECT SUBSTRING(pos_payment.payment_datetime, 1, 10) AS dateTime, pos_payment.drawer AS drawer FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id $filterSql AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id";
		$sqlquery .= " GROUP BY dateTime, drawer";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		
		$returnData = array('tabledata'=>array());
		$query = $this->db->querypagination($sqlquery, $bindData);
		$endOfDayData = array();
		if($query){
			foreach($query as $oneRow){
				$endOfDayData[$oneRow['dateTime']][$oneRow['drawer']] = '';
			}
		}
		
		$tabledata = array();
		if(empty($endOfDayData)){
			$returnData = array('tabledata'=>$tabledata);
			return $returnData;
		}
				
		foreach($endOfDayData as $payment_datetime=>$drawerInfo){
			foreach($drawerInfo as $drawer=>$drawerVal){
				$tableOneRow = array();
				$drawer = addslashes(stripslashes($drawer));
				$onestartdate = $payment_datetime.' 00:00:00';
				$oneenddate = $payment_datetime.' 23:59:59';
				$totalPettyCash = 0;
				$sqlPettyCash = "SELECT SUM(add_sub*amount) AS totalPettyCash FROM petty_cash WHERE accounts_id = $accounts_id AND eod_date = '$payment_datetime' AND drawer LIKE '$drawer' GROUP BY eod_date";
				$queryPettyCashObj = $this->db->query($sqlPettyCash, array());
				if($queryPettyCashObj){
					$totalPettyCash = $queryPettyCashObj->fetch(PDO::FETCH_OBJ)->totalPettyCash;
				}

				$eodpaymentmethodvaluearray = array();
				$eod_date = $payment_datetime;
				$comments = $newcomments = '';
				$end_of_day_id = 0;
				$eodsql = "SELECT end_of_day_id, eod_date, payment_method, counted, comments FROM end_of_day WHERE accounts_id = $accounts_id AND eod_date = '$eod_date' AND drawer LIKE '$drawer' ORDER BY end_of_day_id ASC";
				$eodquery = $this->db->query($eodsql, array());
				if($eodquery){
					while($eodonerow = $eodquery->fetch(PDO::FETCH_OBJ)){
						$eod_date = $eodonerow->eod_date;
						$payment_method = $eodonerow->payment_method;
						if($payment_method=='Cash'){
							$eodpaymentmethodvaluearray[$payment_method] = round($eodonerow->counted,2);
							$end_of_day_id = $eodonerow->end_of_day_id;
						}
						else{
							$eodpaymentmethodvaluearray[$payment_method] = round($eodonerow->counted,2);
						}
						
						if($eodonerow->comments !=''){
							$newcomments = stripslashes(trim((string) $eodonerow->comments));
						}
					}
				}
				//return array('tabledata'=>$tabledata, 'eodsql'=>$eodsql, 'bindData'=>$bindData);
				$tableOneRow['comments'] = $newcomments;
				$StartingBalance = floatval($eodpaymentmethodvaluearray['Starting Balance']??0.00);
				$Cash = floatval($eodpaymentmethodvaluearray['Cash']??0.00);
				$eodpaymentmethodvaluearray['Cash'] = $Cash-$StartingBalance;
				
				$cashData = array();
				
				$possql = "SELECT pos_payment.payment_method, SUM(pos_payment.payment_amount) AS total_payment_amount 
							FROM pos, pos_payment 
							WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id 
								AND (pos_payment.payment_datetime BETWEEN :onestartdate AND :oneenddate) AND pos_payment.drawer LIKE '$drawer' 
							 GROUP BY pos_payment.payment_method";
				$posquery = $this->db->querypagination($possql, array('onestartdate'=>$onestartdate, 'oneenddate'=>$oneenddate));
				if($posquery){
					$i=0;
					$rowspan = count($posquery)+1;
					$cashfound = 0;
					foreach($posquery as $posonerow){
						if($posonerow['payment_method']=='Cash'){$cashfound++;}
					}

					foreach($posquery as $posonerow){
						$i++;
						$payment_method = $posonerow['payment_method'];

						$calculated = round($posonerow['total_payment_amount'],2);
						$counted = $eodpaymentmethodvaluearray[$payment_method]??0.00;
						$cashData[] = array($end_of_day_id, $payment_datetime, $payment_method, round($calculated,2), round($counted,2), $drawer);
					}
				}
				
				if($totalPettyCash !=0){
					$counted = 0;
					if(empty($cashData) && array_key_exists('Cash', $eodpaymentmethodvaluearray)){
						$counted = $eodpaymentmethodvaluearray['Cash']??0.00;
					}
					$cashData[] = array($end_of_day_id, $payment_datetime, 'Petty Cash', round($totalPettyCash,2), round($counted,2), $drawer);
				}
				
				$tableOneRow['cashData'] = $cashData;
				$tabledata[] = $tableOneRow;
			}
			$returnData['tabledata'] = $tabledata;
		}
		$returnData = array('tabledata'=>$tabledata);
		return $returnData;
    }
		
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sorting_type = intval($POST['sorting_type']??0);
		$date_range = $POST['date_range']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->sorting_type = $sorting_type;
		$this->date_range = $date_range;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}
	
	public function loadData_EOD(){
		$eod_date = $this->eod_date;
		$drawer = $this->drawer;
		
		$startdate = $eod_date.' 00:00:00';
		$enddate = $eod_date.' 23:59:59';
		$accounts_id = $_SESSION["accounts_id"]??0;

		$multiple_cash_drawers = 0;
		$cdArray = array();
		$Common = new Common($this->db);
		$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
		if(!empty($cdData)){
			extract($cdData);
			$cdArray = explode('||',$cash_drawers);
		}
		$multiple_cash_drawers = intval($multiple_cash_drawers);
		
		$paymentmethodarray = $paymentmethodvaluearray = array();
		$end_of_day_id = 0;
		$total_counted = 0.00;
		$comments = $changestr = '';
	
		$sql = "SELECT end_of_day_id, payment_method, counted, comments FROM end_of_day WHERE accounts_id = $accounts_id AND eod_date = :eod_date";
		$bindData = array('eod_date'=>$eod_date);
		if(!empty($cdArray) && $multiple_cash_drawers>0){
			$sql .= " AND drawer = :drawer";
			$bindData['drawer'] = $drawer;
		}
		$queryObj = $this->db->query($sql, $bindData);
		if($queryObj){
			while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$end_of_day_id = intval($onerow->end_of_day_id);
				$payment_method = $onerow->payment_method;
				$counted = round($onerow->counted,2);
				$comments = stripslashes(trim((string) $onerow->comments));

				$total_counted = $total_counted+$counted;
				$paymentmethodarray[] = $payment_method;
				$paymentmethodvaluearray[$payment_method] = $counted;
			}
		}

		$jsonResponse = array();
		$jsonResponse['end_of_day_id'] = $end_of_day_id;
		$jsonResponse['eod_date'] = $eod_date;
		$jsonResponse['comments'] = $comments;

		$paymentData = $bindData = array();
		$total_calculated = $calculatedCash = 0.00;
		$strextra = "FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id";		
		if(!empty($cdArray) && $multiple_cash_drawers>0){
			$strextra .= " AND pos_payment.drawer = :drawer";
			$bindData['drawer'] = $drawer;
		}

		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}
		$strextra .= " GROUP BY pos_payment.payment_method";
		$sqlquery = "SELECT pos_payment.payment_method, SUM(pos_payment.payment_amount) AS total_payment_amount $strextra";
		$queryObj = $this->db->query($sqlquery, $bindData);
		if($queryObj){
			while($onegrouprow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$payment_method = $onegrouprow->payment_method;
				$calculated = round($onegrouprow->total_payment_amount,2);
				$counted = 0.00;
				if(!empty($paymentmethodarray) && in_array($payment_method, $paymentmethodarray)){
					$counted = $paymentmethodvaluearray[$payment_method];
				}

				if($payment_method=='Change'){
					$counted = $calculated;
				}

				if($payment_method=='Cash'){
					$calculatedCash = $calculated;
				}
				else{
					$total_calculated += $calculated;

					$paymentData[] = array($payment_method, $calculated, $counted);
				}
			}
		}
		$jsonResponse['paymentData'] = $paymentData;

		$starting_cash = $paymentmethodvaluearray['Starting Balance']??0.00;
		$jsonResponse['starting_cash'] = $starting_cash;
		
		$petty_cash = 0;
		$sqlPettyCash = "SELECT SUM(add_sub*amount) AS totalPettyCash FROM petty_cash WHERE accounts_id = $accounts_id AND eod_date = :eod_date";
		$bindData = array('eod_date'=>$eod_date);
		if(!empty($cdArray) && $multiple_cash_drawers>0){
			$sqlPettyCash .= " AND drawer = :drawer";
			$bindData['drawer'] = $drawer;
		}
		$sqlPettyCash .= " GROUP BY eod_date";
		$queryPettyCashObj = $this->db->query($sqlPettyCash, $bindData);
		if($queryPettyCashObj){
			$petty_cash = round($queryPettyCashObj->fetch(PDO::FETCH_OBJ)->totalPettyCash,2);
		}
		
		$counted_cash = $paymentmethodvaluearray['Cash']??0.00;

		$jsonResponse['counted_cash'] = round($counted_cash,2);		
		$jsonResponse['calculatedCash'] = round($calculatedCash,2);
		$jsonResponse['petty_cash'] = round($petty_cash,2);
		
		$total_calculated += $calculatedCash;		
		
		return $jsonResponse;
	}

    public function loadData_payment(){
        $eod_date = $this->eod_date;
		$drawer = $this->drawer;

		$jsonResponse =array();
		
		$returnmsg = '';
		$total_payment = 0.00;		
		$startdate = $eod_date.' 00:00:00';
		$enddate = $eod_date.' 23:59:59';
		$accounts_id = $_SESSION["accounts_id"]??0;

		$multiple_cash_drawers = 0;
		$cdArray = array();
		$Common = new Common($this->db);
		$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
		if(!empty($cdData)){
			extract($cdData);
			$cdArray = explode('||',$cash_drawers);
		}
		$multiple_cash_drawers = intval($multiple_cash_drawers);

		$i = 1;
		$str = '';
		$strextra = "FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id";		
		
		$bindData = array();
		if(!empty($cdArray) && $multiple_cash_drawers>0){
			$strextra .= " AND pos_payment.drawer = :drawer";
			$bindData['drawer'] = $drawer;
		}
		
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}
		
		$paymentgetwayarray = array();
		$vData = $Common->variablesData('payment_options', $accounts_id);
		if(!empty($vData)){
			extract($vData);
			$paymentgetwayarray = explode('||',$payment_options);
		}

		$strextra .= "  GROUP BY pos_payment.pos_payment_id";
		$sql = "SELECT pos_payment.pos_payment_id, pos_payment.payment_datetime, pos.pos_id, pos.invoice_no, pos.pos_type, pos.order_status, pos.customer_id, pos_payment.payment_method, pos_payment.payment_amount, pos_payment.user_id $strextra";
		$queryObj = $this->db->query($sql, $bindData);
		$paymentData = array();
		if($queryObj){
			while($onegrouprow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$puser_id = $onegrouprow->user_id;
				$puser_name = '';
				if($puser_id>0){
					$userObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $puser_id", array());
					if($userObj){
						$userOneRow = $userObj->fetch(PDO::FETCH_OBJ);
						$puser_name = stripslashes(trim("$userOneRow->user_first_name $userOneRow->user_last_name"));
					}
				}

				$pos_payment_id = $onegrouprow->pos_payment_id;
				
				$invoiceViewLink = '/Invoices/view/'.$onegrouprow->invoice_no;
				$invoice_no = "s$onegrouprow->invoice_no";
				if($onegrouprow->pos_type == 'Repairs' && $onegrouprow->order_status<2){
					$repairsObj = $this->db->query("SELECT repairs_id, ticket_no FROM repairs WHERE accounts_id = $accounts_id AND pos_id = $onegrouprow->pos_id", array());
					if($repairsObj){
						$repairsRow = $repairsObj->fetch(PDO::FETCH_OBJ);
						$invoiceViewLink = '/Repairs/edit/'.$repairsRow->repairs_id;
						$invoice_no = "t$repairsRow->ticket_no";
					}
				}
				elseif($onegrouprow->pos_type == 'Order' && $onegrouprow->order_status<2){
					$invoiceViewLink = '/Orders/edit/'.$onegrouprow->invoice_no;
					$invoice_no = "o$onegrouprow->invoice_no";
				}

				$customer_id = $onegrouprow->customer_id;
				$customer_name = '';
				if($customer_id>0){
					$customersObj = $this->db->query("SELECT first_name, last_name FROM customers WHERE customers_id = $customer_id", array());
					if($customersObj){
						$customersOneRow = $customersObj->fetch(PDO::FETCH_OBJ);
						$customer_name = stripslashes(trim("$customersOneRow->first_name $customersOneRow->last_name"));
					}
				}

				$payment_method = $onegrouprow->payment_method;
				$payment_amount = round($onegrouprow->payment_amount,2);

				$total_payment = $total_payment+$payment_amount;

				$payment_typeOpt = array();
				if(!empty($paymentgetwayarray)){
					foreach($paymentgetwayarray as $paymentgetway){
						$payment_typeOpt[$paymentgetway] = '';
					}
				}
				if(strtoupper($payment_method)=='SQUAREUP' && !in_array($payment_method, $paymentgetwayarray)){
					$payment_typeOpt[$payment_method] = '';
				}

				$paymentData[] = array($pos_payment_id, $puser_name, $onegrouprow->payment_datetime, $invoiceViewLink, $invoice_no, $customer_name, $payment_method, array_keys($payment_typeOpt), round($payment_amount,2));

			}
		}		
		$jsonResponse['paymentData'] = $paymentData;
		$jsonResponse['total_payment'] = round($total_payment,2);
		
		return $jsonResponse;
    }
	
    public function saveend_of_day(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = '';
		$message = '';
		$end_of_day_idarray = array();
		$eod_date = date('Y-m-d', strtotime($POST['eod_date']??date('Y-m-d')));
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$drawer = $POST['drawer']??'';
		$drawer = $this->db->checkCharLen('end_of_day.drawer', $drawer);
		
		$comments = $POST['comments']??'';
		$petty_cash = floatval($POST['petty_cash']??0);
		$calculatedCash = round($POST['calculatedCash']??0.00,2);
		$cash_counted = round($POST['cash_counted']??0.00,2);
		$starting_cash = round($POST['starting_cash']??0.00,2);
		
		$multiple_cash_drawers = 0;
		$cdArray = array();
		$Common = new Common($this->db);
		$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
		if(!empty($cdData)){
			extract($cdData);
			$cdArray = explode('||',$cash_drawers);
		}
		$multiple_cash_drawers = intval($multiple_cash_drawers);

		$sonsql = "SELECT end_of_day_id FROM end_of_day WHERE accounts_id = $accounts_id AND eod_date = :eod_date";
		$bindData = array('eod_date'=>$eod_date);
		if(!empty($cdArray) && $multiple_cash_drawers>0){
			$sonsql .= " AND drawer = :drawer";
			$bindData['drawer'] = $drawer;
		}

		$sonQueryObj = $this->db->query($sonsql, $bindData);
		if($sonQueryObj){
			while($onesonrow = $sonQueryObj->fetch(PDO::FETCH_OBJ)){
				$end_of_day_idarray[$onesonrow->end_of_day_id] = '';
			}
		}
		//
		//=================For Cash Collection=================//
		$u = $a = $end_of_day_id = 0;
		$sonsql = "SELECT end_of_day_id FROM end_of_day WHERE accounts_id = $accounts_id AND eod_date = :eod_date AND payment_method = 'Cash'";
		$bindData = array('eod_date'=>$eod_date);
		if(!empty($cdArray) && $multiple_cash_drawers>0){
			$sonsql .= " AND drawer = :drawer";
			$bindData['drawer'] = $drawer;
		}
		
		$sonqueryObj = $this->db->query($sonsql, $bindData);
		if($sonqueryObj){
			$end_of_day_id = intval($sonqueryObj->fetch(PDO::FETCH_OBJ)->end_of_day_id);
			
			$update = $this->db->update('end_of_day', array('calculated'=>$calculatedCash, 'counted'=>$cash_counted, 'comments'=>$comments, 'drawer'=>$drawer, 'last_updated'=>date('Y-m-d H:i:s')), $end_of_day_id);
			if($update){
				$u++;
				$note_for = $this->db->checkCharLen('notes.note_for', 'end_of_day');
				$noteData=array('table_id'=> $end_of_day_id,
								'note_for'=> $note_for,
								'created_on'=> date('Y-m-d H:i:s'),
								'last_updated'=> date('Y-m-d H:i:s'),
								'accounts_id'=> $_SESSION["accounts_id"],
								'user_id'=> $_SESSION["user_id"],
								'note'=> $this->db->translate('End of Day Updated.'),
								'publics'=>0);
				$notes_id = $this->db->insert('notes', $noteData);
				
			}

			if(!empty($end_of_day_idarray) && array_key_exists($end_of_day_id, $end_of_day_idarray)){
				unset($end_of_day_idarray[$end_of_day_id]);
			}
		}
		else{
			$payment_method = $this->db->checkCharLen('end_of_day.payment_method', 'Cash');
			
			$eotddata = array('last_updated'=>date('Y-m-d H:i:s'),
							'accounts_id'=>$accounts_id,
							'eod_date'=>$eod_date,
							'payment_method'=>$payment_method,
							'calculated'=>$calculatedCash,
							'counted'=>$cash_counted,
							'comments'=>$comments,
							'drawer'=>$drawer);
			$id = $this->db->insert('end_of_day', $eotddata);
			if($id){
				$end_of_day_id = $id;
				$a++;
				$petty_cashNote = '';
				if($petty_cash !=0){					
					$currency = $_SESSION["currency"]??'৳';
					$petty_cashStr = $currency.$petty_cash;
					if($petty_cash<0){$petty_cashStr = '-'.$currency.($petty_cash*(-1));}
					$petty_cashNote = $this->db->translate('Petty Cash')." : $petty_cashStr<br>";
				}
				$note_for = $this->db->checkCharLen('notes.note_for', 'end_of_day');
				$noteData=array('table_id'=> $end_of_day_id,
								'note_for'=> $note_for,
								'created_on'=> date('Y-m-d H:i:s'),
								'last_updated'=> date('Y-m-d H:i:s'),
								'accounts_id'=> $_SESSION["accounts_id"],
								'user_id'=> $_SESSION["user_id"],
								'note'=> $petty_cashNote.$this->db->translate('End of Day Closed'),
								'publics'=>0);
				$notes_id = $this->db->insert('notes', $noteData);
			}
		}

		//=================For Starting Balance Collection=================//
		
		$sonsql = "SELECT end_of_day_id FROM end_of_day WHERE accounts_id = $accounts_id AND eod_date = :eod_date AND payment_method = 'Starting Balance'";
		$bindData = array('eod_date'=>$eod_date);
		if(!empty($cdArray) && $multiple_cash_drawers>0){
			$sonsql .= " AND drawer = :drawer";
			$bindData['drawer'] = $drawer;
		}
		$sonqueryObj = $this->db->query($sonsql, $bindData);
		if($sonqueryObj){
			$end_of_day_id = $sonqueryObj->fetch(PDO::FETCH_OBJ)->end_of_day_id;
			
			$update = $this->db->update('end_of_day', array('calculated'=>0, 'counted'=>$starting_cash, 'comments'=>$comments, 'drawer'=>$drawer, 'last_updated'=>date('Y-m-d H:i:s')), $end_of_day_id);
			if($update){
				$u++;
				$note_for = $this->db->checkCharLen('notes.note_for', 'end_of_day');
				$noteData=array('table_id'=> $end_of_day_id,
								'note_for'=> $note_for,
								'created_on'=> date('Y-m-d H:i:s'),
								'last_updated'=> date('Y-m-d H:i:s'),
								'accounts_id'=> $_SESSION["accounts_id"],
								'user_id'=> $_SESSION["user_id"],
								'note'=> $this->db->translate('End of the day Starting Balance updated successfully.'),
								'publics'=>0);
				$notes_id = $this->db->insert('notes', $noteData);
				
			}

			if(!empty($end_of_day_idarray) && array_key_exists($end_of_day_id, $end_of_day_idarray)){
				unset($end_of_day_idarray[$end_of_day_id]);
			}
		}
		else{
			$payment_method = $this->db->checkCharLen('end_of_day.payment_method', 'Starting Balance');
			$eotddata = array( 	'last_updated'=>date('Y-m-d H:i:s'),
				'accounts_id'=>$accounts_id,
				'eod_date'=>$eod_date,
				'payment_method'=>'Starting Balance',
				'calculated'=>0.00,
				'counted'=>$starting_cash,
				'comments'=>$comments,
				'drawer'=>$drawer);
			$id = $this->db->insert('end_of_day', $eotddata);
			if($id){
				$end_of_day_id = $id;
				$a++;
				$note_for = $this->db->checkCharLen('notes.note_for', 'end_of_day');
				$noteData=array('table_id'=> $end_of_day_id,
								'note_for'=> $note_for,
								'created_on'=> date('Y-m-d H:i:s'),
								'last_updated'=> date('Y-m-d H:i:s'),
								'accounts_id'=> $_SESSION["accounts_id"],
								'user_id'=> $_SESSION["user_id"],
								'note'=> $this->db->translate('End of the day Starting Balance added successfully.'),
								'publics'=>0);
				$notes_id = $this->db->insert('notes', $noteData);
			}
		}

		$payment_methodarray = $POST['payment_method[]']??array();
		$calculatedarray = $POST['calculated[]']??array();
		$countedarray = $POST['counted[]']??array();

		if(!empty($payment_methodarray)){
			$c = 0;

			foreach($payment_methodarray as $payment_method){
				$payment_method = $this->db->checkCharLen('end_of_day.payment_method', $payment_method);
			
				$calculated = round($calculatedarray[$c],2);
				$counted = round($countedarray[$c],2);

				$sonsql = "SELECT end_of_day_id FROM end_of_day WHERE accounts_id = $accounts_id AND eod_date = :eod_date AND payment_method = '$payment_method'";
				$bindData = array('eod_date'=>$eod_date);
				if(!empty($cdArray) && $multiple_cash_drawers>0){
					$sonsql .= " AND drawer = :drawer";
					$bindData['drawer'] = $drawer;
				}
				$sonqueryObj = $this->db->query($sonsql, $bindData);
				if($sonqueryObj){
					$end_of_day_id = $sonqueryObj->fetch(PDO::FETCH_OBJ)->end_of_day_id;
					
					$update = $this->db->update('end_of_day', array('calculated'=>$calculated, 'counted'=>$counted, 'comments'=>$comments, 'drawer'=>$drawer, 'last_updated'=>date('Y-m-d H:i:s')), $end_of_day_id);
					if($update){$u++;}

					if(!empty($end_of_day_idarray) && array_key_exists($end_of_day_id, $end_of_day_idarray)){
						unset($end_of_day_idarray[$end_of_day_id]);
					}
				}
				else{

					$eotddata = array( 	'last_updated'=>date('Y-m-d H:i:s'),
						'accounts_id'=>$accounts_id,
						'eod_date'=>$eod_date,
						'payment_method'=>$payment_method,
						'calculated'=>$calculated,
						'counted'=>$counted,
						'comments'=>$comments,
						'drawer'=>$drawer);
					$id = $this->db->insert('end_of_day', $eotddata);
					if($id){$end_of_day_id = $id;}
					$a++;
				}
				$c++;
			}
		}

		if(is_array($end_of_day_idarray) && count($end_of_day_idarray)>0){
			foreach($end_of_day_idarray as $end_of_day_id=>$val){
				if($end_of_day_id>0)
					$this->db->delete('end_of_day', 'end_of_day_id', $end_of_day_id);
			}
		}

		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if($a >0 && $u==0){
			$activity_feed_title = $this->db->translate('End of Day Closed');
			$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
			$activity_feed_link = "/End_of_Day/view";
			$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
			
			$afData = array('created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'accounts_id' => $_SESSION["accounts_id"],
							'user_id' => $_SESSION["user_id"],
							'activity_feed_title' => $activity_feed_title,
							'activity_feed_name' => $this->db->translate('End of Day Closed').' '.date($dateformat, strtotime($eod_date)),
							'activity_feed_link' => $activity_feed_link,
							'uri_table_name' => "end_of_day",
							'uri_table_field_name' =>"comments",
							'field_value' => 1
							);
			$this->db->insert('activity_feed', $afData);
			
			$savemsg = 'add-success';
		}
		else{
			$activity_feed_title = $this->db->translate('End of Day Updated.');
			$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
			$activity_feed_link = "/End_of_Day/view";
			$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
			
			$afData = array('created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'accounts_id' => $_SESSION["accounts_id"],
							'user_id' => $_SESSION["user_id"],
							'activity_feed_title' => $activity_feed_title,
							'activity_feed_name' => $this->db->translate('Updated End of Day for').' '.date($dateformat, strtotime($eod_date)),
							'activity_feed_link' => $activity_feed_link,
							'uri_table_name' => "end_of_day",
							'uri_table_field_name' =>"comments",
							'field_value' => 1);
			$this->db->insert('activity_feed', $afData);
			
			$savemsg = 'update-success';
		}
				
		return json_encode(array('login'=>'', 'returnStr'=>$savemsg, 'message'=>$message));
	}
	
    public function AJ_update(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnmsg = '';
		//'pos_payment', 'pos_payment_id',
		$accounts_id = $_SESSION["accounts_id"]??0;
		$tableidvalue = $POST['tableidvalue']??0;
		$payment_method = $POST['updatedfieldvalue']??'Cash';
		$payment_method = $this->db->checkCharLen('pos_payment.payment_method', $payment_method);
		$updatetableonefield = $this->db->update('pos_payment', array('payment_method'=> $payment_method), $tableidvalue);
		if($updatetableonefield){
			$returnmsg = 'Ok';
		}
		else{
			$returnmsg = 'error';
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnmsg));
    }
	
    public function loadData_petty_cash(){
		$eod_date = $this->eod_date;
		$drawer = $this->drawer;
		
		$total_petty_cash = 0.00;            
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$multiple_cash_drawers = 0;
		$cdArray = array();
		$Common = new Common($this->db);
		$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
		if(!empty($cdData)){
			extract($cdData);
			$cdArray = explode('||',$cash_drawers);
		}
		$multiple_cash_drawers = intval($multiple_cash_drawers);

		$strextra = "FROM petty_cash WHERE accounts_id = $accounts_id";
		$bindData = array();
		if($eod_date !=''){
			$strextra .= " AND eod_date = :eod_date";
			$bindData['eod_date'] = $eod_date;
		}
		if(!empty($cdArray) && $multiple_cash_drawers>0){
			$strextra .= " AND drawer = :drawer";
			$bindData['drawer'] = $drawer;
		}		
		$strextra .= " AND petty_cash_publish = 1 ORDER BY petty_cash_id ASC";
		$jsonResponse = $petty_cashData = array();

		$sql = "SELECT * $strextra";

		$queryObj = $this->db->query($sql, $bindData);
		if($queryObj){
			while($onegrouprow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$petty_cash_id = $onegrouprow->petty_cash_id;
				$add_sub = $onegrouprow->add_sub;
				$type = 'Subtraction';
				if($add_sub>0){
					$type = 'Addition';
				}
				$amount = $onegrouprow->amount*$add_sub;
				$total_petty_cash += $amount;
				$reason = stripslashes(trim((string) $onegrouprow->reason));
				$petty_cashData[] = array($petty_cash_id, $reason, $type, $amount);
			}
		}
		$jsonResponse['petty_cashData'] = $petty_cashData;
		$jsonResponse['total_petty_cash'] = $total_petty_cash;
		
		return $jsonResponse;
    }
	
	public function cashDrawerCounter(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnData = array();
		$returnData['login'] = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$idName = $POST['idName']??'';
		
		$denominationsData = array($currency.'100=100', $currency.'50=50', $currency.'20=20', $currency.'10=10', $currency.'5=5', $currency.'1=1', 'Quarters (.25)=.25', 'Dimes (.10)=.10', 'Nickels (.05)=.05', 'Pennies (.01)=.01');
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'counting_Cash_Til'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$value = $variablesData->value;					
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('denominations', $value)){
					$denominationsData = array();
					if(strlen($value['denominations'])>6){
						$denominationsData = explode('||', $value['denominations']);
					}
				}
			}
		}
		
		$returnData['denominationsData'] = $denominationsData;
	
		return json_encode($returnData);
	}
	
	public function prints(){
		$language = $_SESSION["language"]??'English';
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

		$htmlStr = '<!DOCTYPE html>
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
		return $htmlStr;		
	}
	
	public function AJ_prints_large_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$eod_date = $POST['eod_date']??'';
		if(empty($eod_date)){$eod_date = date('Y-m-d');}

		$drawer = $POST['drawer']??'';

		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->endOfDayInfo($eod_date, 'large', $drawer);
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_small_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$eod_date = $POST['eod_date']??'';
		if(empty($eod_date)){$eod_date = date('Y-m-d');}

		$drawer = $POST['drawer']??0;

		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->endOfDayInfo($eod_date, 'small', $drawer);
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_eodlist_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$eod_date = $POST['eod_date']??'';

		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->endOfDayLists($eod_date);
		
		return json_encode($jsonResponse);
	}
	
}
?>