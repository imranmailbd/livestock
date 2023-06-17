<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Accounts_Receivables{
	protected $db;
	private int $page, $totalRows, $customers_id;
	private string $customer_type, $sorting_type, $keyword_search, $history_type;
	private array $custTypeOpt;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}	
	
    private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$scustomer_type = $this->customer_type;
		$sorting_type = $this->sorting_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Accounts_Receivables";
		$_SESSION["list_filters"] = array('scustomer_type'=>$scustomer_type, 'sorting_type'=>$sorting_type, 'keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		if($scustomer_type !='All'){
			$filterSql .= " AND customer_type = :customer_type";
			$bindData['customer_type'] = $scustomer_type;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$queryObj = $this->db->query("SELECT COUNT(customers_id) AS totalrows FROM customers WHERE accounts_id = $prod_cat_man AND credit_limit>0 $filterSql AND customers_publish = 1", $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}

		$custTypeOpt = array();
		$tableObj = $this->db->query("SELECT customer_type FROM customers WHERE accounts_id = $prod_cat_man AND credit_limit>0 $filterSql AND customers_publish = 1 GROUP BY customer_type", $bindData);
		if($tableObj){
			$custTypeOpts = array();
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				if(!empty($oneRow->customer_type)){
					$custTypeOpts[$oneRow->customer_type] = '';
				}
			}
			ksort($custTypeOpts);
			$custTypeOpt = array_keys($custTypeOpts);
		}
		
		$this->custTypeOpt = $custTypeOpt;
		$this->totalRows = $totalRows;
	}
	
    private function loadTableRows(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$limit = $_SESSION["limit"];
		
		$page = $this->page;
		$totalRows = $this->totalRows;
		$scustomer_type = $this->customer_type;
		$ssorting_type = $this->sorting_type;
		$sortingTypeData = array(0=>'TRIM(UPPER(CONCAT_WS(\' \', company, first_name, last_name))) ASC', 
								1=>'company ASC', 
								2=>'first_name ASC', 
								3=>'last_name ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}
		
		$keyword_search = $this->keyword_search;
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$filterSql = "FROM customers WHERE accounts_id = $prod_cat_man AND customers_publish = 1 AND credit_limit>0";
		$bindData = array();
		if($scustomer_type !='All'){
			$filterSql .= " AND customer_type = :customer_type";
			$bindData['customer_type'] = $scustomer_type;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT customers_id, company, first_name,last_name, contact_no, credit_limit, credit_days $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$i = $starting_val+1;

		$tabledata = array();
		if($query){
			foreach($query as $oneRow){
				$customers_id = $oneRow['customers_id'];
				
				$name = stripslashes(trim("$oneRow[company] $oneRow[first_name]"));
				$last_name = stripslashes($oneRow['last_name']);
				if($name !=''){$name .= ' ';}
				$name .= $last_name;
				
				$contact_no = $oneRow['contact_no'];
				if($contact_no==''){$contact_no = '&nbsp;';}
				$credit_limit = round($oneRow['credit_limit'],2);
				
				$credit_days = round($oneRow['credit_days']);
				if($credit_days==''){$credit_days = '&nbsp;';}
				
				$totalDue = $this->calTotalDue($customers_id);
				
				$tabledata[] = array($customers_id, $name, $contact_no, $credit_limit, $credit_days, $totalDue);
			}
		}
		return $tabledata;
    }

	public function AJgetPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$customers_id = intval($POST['customers_id']??0);
		
		$customersData = array();
		$customersData['login'] = '';			
		$customersData['first_name'] = '';
		$customersData['last_name'] = '';
		$customersData['contact_no'] = '';
		$customersData['credit_limit'] = '';
		$customersData['credit_days'] = '';
		if($customers_id>0 && $prod_cat_man>0){
			$customersObj = $this->db->query("SELECT company, first_name, last_name, contact_no, credit_limit, credit_days FROM customers WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man AND credit_limit>0", array('customers_id'=>$customers_id),1);
			if($customersObj){
				$customersRow = $customersObj->fetch(PDO::FETCH_OBJ);
				
				$customersData['first_name'] = stripslashes(trim("$customersRow->company $customersRow->first_name"));
				$customersData['last_name'] = trim((string) $customersRow->last_name);
				$customersData['contact_no'] = trim((string) $customersRow->contact_no);
				$customersData['credit_limit'] = round($customersRow->credit_limit,2);
				$customersData['credit_days'] = intval($customersRow->credit_days);
			}
		}
		return json_encode($customersData);
	}
	
	public function AJsaveAccountsReceivables(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = '';
		$customers_id = intval($POST['customers_id']??0);			
		$returnval = '';
		
		$credit_limit = floatval($POST['credit_limit']??0);
		$credit_days = intval($POST['credit_days']??0);
		
		$conditionarray = array();
		$conditionarray['credit_limit'] = $credit_limit;
		$conditionarray['credit_days'] = $credit_days;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		
		$oneTRowObj = $this->db->querypagination("SELECT * FROM customers WHERE customers_id = $customers_id", array());
			
		$update = $this->db->update('customers', $conditionarray, $customers_id);
		$savemsg = 'saved';
		if($update){
			$changed = array();
			if($oneTRowObj){
				$prevcredit_limit = $oneTRowObj[0]['credit_limit'];
			    if($prevcredit_limit != $credit_limit){
			        $changed['credit_limit'] = array($prevcredit_limit, $credit_limit);
			    }
			    $prevcredit_days = $oneTRowObj[0]['credit_days'];
			    if($prevcredit_days != $credit_days){
			        $changed['credit_days'] = array($prevcredit_days, $credit_days);
			    }
			}
			
			if(!empty($changed)){
				$moreInfo = array();
				$teData = array();
				$teData['created_on'] = date('Y-m-d H:i:s');
				$teData['accounts_id'] = $_SESSION["accounts_id"];
				$teData['user_id'] = $_SESSION["user_id"];
				$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'customers');
				$teData['record_id'] = $customers_id;
				$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
				$this->db->insert('track_edits', $teData);							
			}
		}
		
		$array = array( 'login'=>'', 'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	public function AJremoveAccountsReceivables(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnmsg = '';
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$customers_id = intval($POST['customers_id']??0);	
		$note = $POST['note']??'';		
		$conditionarray = array();
		$conditionarray['credit_limit'] = 0;
		$conditionarray['credit_days'] = 0;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $_SESSION["prod_cat_man"];
		
		$updatecustomers = $this->db->update('customers', $conditionarray, $customers_id);
		if($updatecustomers){
			$returnmsg = 'success';
			$note_for = $this->db->checkCharLen('notes.note_for', 'customers');
			$noteData=array('table_id'=> $customers_id,
							'note_for'=> $note_for,
							'created_on'=> date('Y-m-d H:i:s'),
							'last_updated'=> date('Y-m-d H:i:s'),
							'accounts_id'=> $_SESSION["accounts_id"],
							'user_id'=> $_SESSION["user_id"],
							'note'=> $note,
							'publics'=>0);
			$notes_id = $this->db->insert('notes', $noteData);
			
		}
	
		$returnData = array('login'=>'', 'returnmsg'=>$returnmsg);
		return json_encode($returnData);
	}
	
	public function view(){}
	
	private function filterHAndOptions(){
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$scustomers_id = $this->customers_id;
		$shistory_type = $this->history_type;
		
		$bindData = array();
		$bindData['customers_id'] = $scustomers_id;
		
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'customers' AND table_id = :customers_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link = CONCAT('/Customers/view/', :customers_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link = CONCAT('/Customers/view/', :customers_id) 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id 
					UNION ALL 
						SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'customers' AND table_id = :customers_id";
		}
		$totalRows = 0;
		$tableObj = $this->db->query($filterSql, $bindData);
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;
			}
		}		
		$this->totalRows = $totalRows;
	}
	
    private function loadHTableRows(){

		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$scustomers_id = $this->customers_id;
		$shistory_type = $this->history_type;
		$Common = new Common($this->db);
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$bindData = array();
		$bindData['customers_id'] = $scustomers_id;            
		if($shistory_type !=''){
			
			if(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'customers' AND table_id = :customers_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link = CONCAT('/Customers/view/', :customers_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link = CONCAT('/Customers/view/', :customers_id)  
					UNION ALL SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id 
					UNION ALL 
						SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'customers' AND table_id = :customers_id 
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
 
	public function calTotalDue($customers_id){
		$returnstr = 0;
		if(isset($_SESSION["prod_cat_man"])){
			$accounts_id = $_SESSION["accounts_id"]??0;
			$Common = new Common($this->db);
			
			$totalUsedCard = 0;
			$sqlquery = "SELECT SUM(CASE WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, 
						SUM(CASE WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal,
						pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, pos.pos_id 
						FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos.is_due>0 AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) 
						 AND pos.customer_id = :customers_id GROUP BY pos.pos_id ORDER BY pos.sales_datetime DESC, pos.invoice_no DESC";
			$query = $this->db->querypagination($sqlquery, array('customers_id'=>$customers_id),1);
			if($query){
				foreach($query as $oneRow){
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
						$totalUsedCard += $grand_total-$totalpayment;
					}
				}
			}
			
			$returnstr = $totalUsedCard;
		}
		return round($returnstr,2);
	}	

	//========================ASync========================//	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
	
		$scustomer_type = $POST['scustomer_type']??'All';
		$sorting_type = $POST['sorting_type']??0;
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->customer_type = $scustomer_type;
		$this->sorting_type = $sorting_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['custTypeOpt'] = $this->custTypeOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}	
	
	public function AJ_view_moreInfo(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$customers_id = intval($POST['customers_id']??0);
		$customersObj = $this->db->query("SELECT * FROM customers WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man", array('customers_id'=>$customers_id),1);
		if($customersObj){
			$customersarray = $customersObj->fetch(PDO::FETCH_OBJ);
			$customers_id = $customersarray->customers_id;
			$name = stripslashes(trim("$customersarray->company $customersarray->first_name $customersarray->last_name"));
			$Common = new Common($this->db);
			$credit_limit = round($customersarray->credit_limit,2);
			
			$availCreditData = $Common->calAvailCr($customers_id, $credit_limit, 1);
			$available_credit = $availCreditData['available_credit']??0;
			
			$jsonResponse['customers_id'] = $customers_id;
			$jsonResponse['name'] = $name;
			$jsonResponse['credit_limit'] = $credit_limit;
			$jsonResponse['available_credit'] = $available_credit;
			
			$customers_publish = $customersarray->customers_publish;
			$jsonResponse['customers_publish'] = $customers_publish;
			
			$contact_no = $customersarray->contact_no;
			$jsonResponse['contact_no'] = $contact_no;
			
			$credit_days = $customersarray->credit_days;
			$jsonResponse['credit_days'] = $credit_days;
			
			$email = $customersarray->email;
			$jsonResponse['email'] = $email;
			
			$unpaidInvoices = array();
			$filterSql = "SELECT pos.pos_id, pos.user_id, pos.invoice_no, pos.sales_datetime,  pos.credit_days, SUM(CASE WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, ";
			$filterSql .= "SUM(CASE WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal,";
			$filterSql .= "pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos.is_due>0 AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) ";
			$filterSql .= "AND pos.customer_id = $customers_id AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1 GROUP BY pos.pos_id ORDER BY pos.sales_datetime DESC, pos.invoice_no DESC";
			//$unpaidInvoices[] = $filterSql;
			$query = $this->db->querypagination($filterSql, array());
			if($query){
				foreach($query as $onePOSRow){
					$salesTime = strtotime($onePOSRow['sales_datetime']); 
					$pos_id = $onePOSRow['pos_id'];
					$invoice_no = $onePOSRow['invoice_no'];
					$credit_days = $onePOSRow['credit_days'];
					$dueDate = date('Y-m-d', strtotime("+$credit_days day", $salesTime));
					
					$taxable_total = $onePOSRow['taxableTotal'];
					$totalnontaxable = $onePOSRow['nonTaxableTotal'];
					$taxes_total1 = $Common->calculateTax($taxable_total, $onePOSRow['taxes_percentage1'], $onePOSRow['tax_inclusive1']);
					$taxes_total2 = $Common->calculateTax($taxable_total, $onePOSRow['taxes_percentage2'], $onePOSRow['tax_inclusive2']);

					$tax_inclusive1 = $onePOSRow['tax_inclusive1'];
					$tax_inclusive2 = $onePOSRow['tax_inclusive2'];
						
					$taxestotal = $taxes_total1+$taxes_total2;
					$grand_total = $taxable_total+$taxestotal+$totalnontaxable;
					if($tax_inclusive1>0){
						$grand_total -= $taxes_total1;
					}
					if($tax_inclusive2>0){
						$grand_total -= $taxes_total2;
					}				
					
					$amountPaid = 0;
					$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
					$queryObj = $this->db->query($sqlquery, array());
					if($queryObj){
						$amountPaid = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
					}
					
					$amountDue = 0;
					if($grand_total>$amountPaid){
						$amountDue = $grand_total-$amountPaid;
						
						$unpaidInvoices[] = array($invoice_no, $dueDate, $grand_total, $amountPaid, $amountDue);						
					}					
				}
			}	
			
			$jsonResponse['unpaidInvoices'] = $unpaidInvoices;
			
			$Sql = "SELECT activity_feed_title AS afTitle FROM activity_feed 
				WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link = CONCAT('/Customers/view/', :customers_id) 
			UNION ALL 
				SELECT 'Track Edits' AS afTitle FROM track_edits 
				WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id 
			UNION ALL 
				SELECT 'Notes Created' AS afTitle FROM notes 
				WHERE accounts_id = $accounts_id AND note_for = 'customers' AND table_id = :customers_id";
			$actFeeTitOpt = array();
			$tableObj = $this->db->query($Sql, array('customers_id'=>$customers_id));
			if($tableObj){
				$actFeeTitOpts = array();
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$actFeeTitOpts[$oneRow->afTitle] = '';
				}
				ksort($actFeeTitOpts);
				$actFeeTitOpt = array_keys($actFeeTitOpts);
			}
			$jsonResponse['actFeeTitOpt'] = $actFeeTitOpt;
		}
		else{
			$jsonResponse['login'] = 'Accounts_Receivables/lists/';
		}		
		
		return json_encode($jsonResponse);
	}
	
	public function AJgetHPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		//===For loading table rows===//	
		$customers_id = intval($POST['customers_id']??0);
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->customers_id = $customers_id;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRows();
		
		return json_encode($jsonResponse);
	}
	
	public function sendEmailARStatement(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		
		$customers_id = intval($POST['customers_id']??0);
		$email_address = $POST['email_address']??'';
		$Accounts_Receivables_Statement = $POST['Accounts_Receivables_Statement']??'';
		$Invoice_Date = $POST['Invoice_Date']??'';
		$Invoice_Number = $POST['Invoice_Number']??'';
		$Date_Due = $POST['Date_Due']??'';
		$Grand_Total = $POST['Grand_Total']??'';
		$Total_Paid = $POST['Total_Paid']??'';
		$Current = $POST['Current']??'';
		$_30_Past_Due = $POST['_30_Past_Due']??'';
		$_3160_Past_Due = $POST['_3160_Past_Due']??'';
		$_6190_Past_Due = $POST['_6190_Past_Due']??'';
		$_91_Past_Due = $POST['_91_Past_Due']??'';
		$No_Dues_Meet = $POST['No_Dues_Meet']??'';
		$Not_Send_Mail = $POST['Not_Send_Mail']??'';
		$No_Invoice_Meet = $POST['No_Invoice_Meet']??'';

		$Common = new Common($this->db);
		
		$customersObj = $this->db->query("SELECT * FROM customers WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man AND credit_limit>0", array('customers_id'=>$customers_id),1);
		if($customersObj){
			$customersarray = $customersObj->fetch(PDO::FETCH_OBJ);	
	
			$customers_id = $customersarray->customers_id;
			$name = $customersarray->first_name.' '.$customersarray->last_name;
			$email = $customersarray->email;
			$contact_no = $customersarray->contact_no;
			$credit_limit = $customersarray->credit_limit;
			$credit_days = $customersarray->credit_days;
			$address = $customersarray->shipping_address_one;
			if($customersarray->shipping_city !='' || $customersarray->shipping_state !='' || $customersarray->shipping_zip !=''){
				$address .= trim(', '.$customersarray->shipping_city.' '.$customersarray->shipping_state.' '.$customersarray->shipping_zip);
			}
			$title = $Accounts_Receivables_Statement.' '.$name;
			$customer_service_email = '';
			$accObj = $this->db->query("SELECT customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accObj){
				$customer_service_email = $accObj->fetch(PDO::FETCH_OBJ)->customer_service_email;
			}
			if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
			
			$mail_body = "<div style=\"width:95%;background:#fff; margin:0 auto; padding:10px 15px; overflow:hidden;border:1px solid #DDDDDD; border-radius:4px;\">
							<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
								<tr>
									<td align=\"center\">
										<h2>$title</h2>
										<p>$name<br>$address</p>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>
										<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: collapse;border-spacing:0;\">
											<tr>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"center\">$Invoice_Date</th>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"center\">$Invoice_Number</th>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"center\">$Date_Due</th>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"right\">$Grand_Total</th>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"right\">$Total_Paid</th>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"right\">$Current</th>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"right\">$_30_Past_Due</th>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"right\">$_3160_Past_Due</th>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"right\">$_6190_Past_Due</th>
												<th style=\"border:1px solid #DDDDDD; background:#F5F5F6; padding:8px 10px;\" width=\"10%\" align=\"right\">$_91_Past_Due</th>
											</tr>";
											
			$GrandCurrent = $GrandPastDue0_30 = $GrandPastDue31_60 = $GrandPastDue61_90 = $GrandPastDue91_plus = 0;
			
			$arSql ="SELECT pos.pos_id, pos.sales_datetime, pos.invoice_no, pos.credit_days, SUM(CASE WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, 
					SUM(CASE WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal,
					pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 
					FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos.is_due>0 AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) 
					 AND pos.customer_id = $customers_id GROUP BY pos.pos_id ORDER BY pos.sales_datetime DESC, pos.invoice_no DESC";
			$query = $this->db->querypagination($arSql, array());
			if($query){
				foreach($query as $oneRow){
					$pos_id = $oneRow['pos_id'];
					$salesTime = strtotime($oneRow['sales_datetime']);
					if($timeformat=='24 hour'){$invoiceDate =  date($dateformat.' H:i', $salesTime);}
					else{$invoiceDate =  date($dateformat.' g:i a', $salesTime);}
				
					$invoice_no = $oneRow['invoice_no'];
					$credit_days = $oneRow['credit_days'];
					$dueDate = date($dateformat, strtotime("+$credit_days day", $salesTime));
					
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
					$grand_totalstr = $this->addCurrency($grand_total);
					
					$amountPaid = 0;
					$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
					$queryObj = $this->db->query($sqlquery, array());
					if($queryObj){
						$amountPaid = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
					}
					$amountPaidStr = $this->addCurrency($amountPaid);
					
					$amountDue = 0;
					if($grand_total>$amountPaid){
						$amountDue = $grand_total-$amountPaid;
						$amountDueStr = $this->addCurrency($amountDue);
						
						$days = floor((time() -  strtotime("+$credit_days day", $salesTime))/86400);
						
						$CurrentStr = $PastDue0_30Str = $PastDue31_60Str = $PastDue61_90Str = $PastDue91_plusStr = '&nbsp;';
						if($days<0){
							$CurrentStr = $amountDueStr;
							$GrandCurrent +=$amountDue;
						}
						elseif($days>=0 && $days<=30 ){
							$PastDue0_30Str = $amountDueStr;
							$GrandPastDue0_30 +=$amountDue;
						}
						elseif($days>=31 && $days<=60 ){
							$PastDue31_60Str = $amountDueStr;
							$GrandPastDue31_60 +=$amountDue;
						}
						elseif($days>=61 && $days<=90 ){
							$PastDue61_90Str = $amountDueStr;
							$GrandPastDue61_90 +=$amountDue;
						}
						elseif($days>=91){
							$PastDue91_plusStr = $amountDueStr;
							$GrandPastDue91_plus +=$amountDue;
						}
						
						$mail_body .= "<tr>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"center\">$invoiceDate</td>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"center\">$invoice_no</td>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"center\">$dueDate</td>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">$grand_totalstr</td>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">$amountPaidStr</td>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">$CurrentStr</td>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">$PastDue0_30Str</td>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">$PastDue31_60Str</td>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">$PastDue61_90Str</td>
									<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">$PastDue91_plusStr</td>
								</tr>";
						
					}					
				}
			}
			else{
				$mail_body .= "<tr><td colspan=\"10\" class=\"red18bold\">$No_Dues_Meet</td></tr>";
			}
			$Notes = new Notes($this->db);
			$Notes->note_for = 'customers';
			$Notes->table_id = $customers_id;
			$allNotes = $Notes->getPublicNotes();
			
			$mail_body .= "<tr>
							<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\" colspan=\"5\">$Grand_Total :</th>
							<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">".$this->addCurrency($GrandCurrent)."</th>
							<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">".$this->addCurrency($GrandPastDue0_30)."</th>
							<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">".$this->addCurrency($GrandPastDue31_60)."</th>
							<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">".$this->addCurrency($GrandPastDue61_90)."</th>
							<td style=\"border:1px solid #DDDDDD;  padding:8px 10px;\" align=\"right\">".$this->addCurrency($GrandPastDue91_plus)."</th>
						</tr>
						</table>
						</td>
					</tr>
					<tr>
						<td>
							$allNotes
						</td>
					</tr>
				</table>
			</div>";
			
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
			$mail->Subject = $title;
			$mail->CharSet = 'UTF-8';
			$mail->isHTML(true);
			//Build a simple message body
			$mail->Body = $mail_body;
			
			if($email_address =='' || is_null($email_address)){
				$returnStr = $Not_Send_Mail;
			}
			else{
				if($mail->send()){
					$returnStr = 'Ok';
				}
				else{
					$returnStr = $Not_Send_Mail;
				}
			}							
		}
		else{
			$returnStr = "<p>$No_Invoice_Meet</p>";
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}	
	
	public function addCurrency($price){
		$currency = $_SESSION["currency"]??'৳';
		$priceWithCurrency = $currency.number_format($price,2);
		if($price <0 ){
			$priceWithCurrency = '-'.$currency.number_format($price*(-1),2);
		}
		return $priceWithCurrency;
	}

	public function AJ_prints_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$customers_id = intval($POST['customers_id']??0);

		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$Common = new Common($this->db);
		$customersObj = $this->db->query("SELECT * FROM customers WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man AND credit_limit>0", array('customers_id'=>$customers_id),1);
		if($customersObj){
			$customersarray = $customersObj->fetch(PDO::FETCH_OBJ);	
			$customers_id = $customersarray->customers_id;
			$jsonResponse['name'] = stripslashes(trim("$customersarray->first_name $customersarray->last_name"));
			$address = $customersarray->shipping_address_one;
			if($customersarray->shipping_city !='' || $customersarray->shipping_state !='' || $customersarray->shipping_zip !=''){
				$address .= trim(', '.$customersarray->shipping_city.' '.$customersarray->shipping_state.' '.$customersarray->shipping_zip);
			}
			$jsonResponse['address'] = $address;

			$tabledata = array();
			$arSql ="SELECT pos.pos_id, pos.sales_datetime, pos.invoice_no, pos.credit_days, SUM(CASE WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, 
					SUM(CASE WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal,
					pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 
					FROM pos, pos_cart 
					WHERE pos.accounts_id = $accounts_id AND pos.is_due>0 AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) 
						AND pos.customer_id = $customers_id GROUP BY pos.pos_id ORDER BY pos.sales_datetime DESC, pos.invoice_no DESC";
			$query = $this->db->querypagination($arSql, array());
			if($query){
				foreach($query as $oneRow){
					$pos_id = $oneRow['pos_id'];
					$salesTime = strtotime($oneRow['sales_datetime']);
					
					$invoice_no = $oneRow['invoice_no'];
					$credit_days = $oneRow['credit_days'];
					$dueDate = date('Y-m-d', strtotime("+$credit_days day", $salesTime));
					
					$taxable_total = $oneRow['taxableTotal'];
					$totalnontaxable = $oneRow['nonTaxableTotal'];
					$taxes_total1 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
					$taxes_total2 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);
	
					$tax_inclusive1 = $oneRow['tax_inclusive1'];
					$tax_inclusive2 = $oneRow['tax_inclusive2'];
						
					$taxestotal = $taxes_total1+$taxes_total2;
					$grand_total = round($taxable_total+$taxestotal+$totalnontaxable,2);
					if($tax_inclusive1>0){$grand_total -= $taxes_total1;}
					if($tax_inclusive2>0){$grand_total -= $taxes_total2;}
					
					$amountPaid = 0;
					$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
					$queryObj = $this->db->query($sqlquery, array());
					if($queryObj){
						$amountPaid = round($queryObj->fetch(PDO::FETCH_OBJ)->totalpayment,2);
					}
					
					$amountDue = 0;
					if($grand_total>$amountPaid){
						$amountDue = $grand_total-$amountPaid;
						
						$days = floor((time() -  strtotime("+$credit_days day", $salesTime))/86400);
						
						$Current = $PastDue0_30 = $PastDue31_60 = $PastDue61_90 = $PastDue91_plus = 0;
						if($days<0){
							$Current = $amountDue;
						}
						elseif($days>=0 && $days<=30 ){
							$PastDue0_30 = $amountDue;
						}
						elseif($days>=31 && $days<=60 ){
							$PastDue31_60 = $amountDue;
						}
						elseif($days>=61 && $days<=90 ){
							$PastDue61_90 = $amountDue;
						}
						elseif($days>=91){
							$PastDue91_plus = $amountDue;
						}

						$tabledata[] = array('invoiceDate'=>$oneRow['sales_datetime'], 'invoice_no'=>$invoice_no, 'dueDate'=>$dueDate, 'amountPaid'=>round($amountPaid,2), 
							'Current'=>round($Current,2), 'PastDue0_30'=>round($PastDue0_30,2), 'PastDue31_60'=>round($PastDue31_60,2), 'PastDue61_90'=>round($PastDue61_90,2), 'PastDue91_plus'=>round($PastDue91_plus,2));
					}
				}
			} 

			$Notes = new Notes($this->db);
			$Notes->note_for = 'customers';
			$Notes->table_id = $customers_id;
			$allNotes = $Notes->getPublicNotes(1);

			$jsonResponse['tabledata'] = $tabledata;
			$jsonResponse['allNotes'] = $allNotes;

		}
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_arlists_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$scustomer_type = $POST['scustomer_type']??'All';
		$sorting_type = $POST['sorting_type']??0;
		$keyword_search = $POST['keyword_search']??'';
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$limit = $_SESSION["limit"];
		
		$sortingTypeData = array(0=>'TRIM(UPPER(CONCAT_WS(\' \', company, first_name, last_name))) ASC', 
								1=>'company ASC', 
								2=>'first_name ASC', 
								3=>'last_name ASC');
		if(empty($sorting_type) || !array_key_exists($sorting_type, $sortingTypeData)){
			$sorting_type = 0;
		}
		
		$starting_val = ($page-1)*$limit;
		
		$filterSql = "FROM customers WHERE accounts_id = $prod_cat_man AND customers_publish = 1 AND credit_limit>0";
		$bindData = array();
		if($scustomer_type !='All'){
			$filterSql .= " AND customer_type = :customer_type";
			$bindData['customer_type'] = $scustomer_type;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT customers_id, company, first_name,last_name, contact_no, email, credit_limit, credit_days $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$sorting_type];
		$customersData = $this->db->querypagination($sqlquery, $bindData);
		$i = 1;
		$tabledata = array();
			
		$Common = new Common($this->db);
		if($customersData){
			foreach($customersData as $oneCustomerRow){
				$customers_id = $oneCustomerRow['customers_id'];
				
				$name = stripslashes(trim("$oneCustomerRow[company] $oneCustomerRow[first_name]"));
				$last_name = stripslashes($oneCustomerRow['last_name']);
				if($name !=''){$name .= ' ';}
				$name .= $last_name;
				
				$contact_no = $oneCustomerRow['contact_no'];
				if($contact_no !=''){$name .= ', '.$contact_no;}
				
				$email = $oneCustomerRow['email'];
				if($email !=''){$name .= ', '.$email;}
				$credit_limit = round($oneCustomerRow['credit_limit'],2);
				
				$credit_days = round($oneCustomerRow['credit_days']);
				if($credit_days==''){$credit_days = '&nbsp;';}
				
				$totalDue = $this->calTotalDue($customers_id);
				
				$tableOneCustomer = array('name'=>$name, 'credit_limit'=>$credit_limit, 'credit_days'=>$credit_days, 'totalDue'=>$totalDue);
				$customerCartData = array();
				$arSql ="SELECT pos.pos_id, pos.sales_datetime, pos.invoice_no, pos.credit_days, SUM(CASE WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, 
						SUM(CASE WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.sales_price*pos_cart.shipping_qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.shipping_qty)-(pos_cart.shipping_qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal,
						pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 
						FROM pos, pos_cart 
						WHERE pos.accounts_id = $accounts_id AND pos.is_due>0 AND pos.customer_id = $customers_id AND (pos.pos_type = 'Sale' or (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2))  AND pos.pos_id = pos_cart.pos_id AND pos.pos_publish = 1
							GROUP BY pos.pos_id ORDER BY pos.sales_datetime DESC, pos.invoice_no DESC";
				$query = $this->db->querypagination($arSql, array());
				if($query){
					foreach($query as $oneRow){
						$pos_id = $oneRow['pos_id'];
						$salesTime = strtotime($oneRow['sales_datetime']);
						
						$invoice_no = $oneRow['invoice_no'];
						$credit_days = $oneRow['credit_days'];
						$dueDate = date('Y-m-d', strtotime("+$credit_days day", $salesTime));
						
						$taxable_total = $oneRow['taxableTotal'];
						$totalnontaxable = $oneRow['nonTaxableTotal'];
						$taxes_total1 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
						$taxes_total2 = $Common->calculateTax($taxable_total, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);
		
						$tax_inclusive1 = $oneRow['tax_inclusive1'];
						$tax_inclusive2 = $oneRow['tax_inclusive2'];
							
						$taxestotal = $taxes_total1+$taxes_total2;
						$grand_total = round($taxable_total+$taxestotal+$totalnontaxable,2);
						if($tax_inclusive1>0){$grand_total -= $taxes_total1;}
						if($tax_inclusive2>0){$grand_total -= $taxes_total2;}
						
						$amountPaid = 0;
						$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
						$queryObj = $this->db->query($sqlquery, array());
						if($queryObj){
							$amountPaid = round($queryObj->fetch(PDO::FETCH_OBJ)->totalpayment,2);
						}
						
						$amountDue = 0;
						if($grand_total>$amountPaid){
							$amountDue = $grand_total-$amountPaid;
							
							$days = floor((time() -  strtotime("+$credit_days day", $salesTime))/86400);
							
							$Current = $PastDue0_30 = $PastDue31_60 = $PastDue61_90 = $PastDue91_plus = 0;
							if($days<0){
								$Current = $amountDue;
							}
							elseif($days>=0 && $days<=30 ){
								$PastDue0_30 = $amountDue;
							}
							elseif($days>=31 && $days<=60 ){
								$PastDue31_60 = $amountDue;
							}
							elseif($days>=61 && $days<=90 ){
								$PastDue61_90 = $amountDue;
							}
							elseif($days>=91){
								$PastDue91_plus = $amountDue;
							}

							$customerCartData[] = array('invoiceDate'=>$oneRow['sales_datetime'], 'invoice_no'=>$invoice_no, 'dueDate'=>$dueDate, 'amountPaid'=>round($amountPaid,2), 
								'Current'=>round($Current,2), 'PastDue0_30'=>round($PastDue0_30,2), 'PastDue31_60'=>round($PastDue31_60,2), 'PastDue61_90'=>round($PastDue61_90,2), 'PastDue91_plus'=>round($PastDue91_plus,2));
						}
					}
					$tableOneCustomer['customerCartData'] = $customerCartData;
	
					$tabledata[] = $tableOneCustomer;
				} 
			}
		}
		$jsonResponse['tabledata'] = $tabledata;
		return json_encode($jsonResponse);
	}
	
	public function prints($segment4name){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$language = $_SESSION["language"]??'English';
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

		$htmlStr = '';
		if($segment4name=='arlists'){
			$sortingTypeData = array(0=>'TRIM(UPPER(CONCAT_WS(\' \', company, first_name, last_name))) ASC', 
									1=>'company ASC', 
									2=>'first_name ASC', 
									3=>'last_name ASC');

			$scustomer_type = $GLOBALS['segment5name']??'';
			$sorting_type = $GLOBALS['segment6name']??0;
			if(empty($sorting_type) || !array_key_exists($sorting_type, $sortingTypeData)){
				$sorting_type = 0;
			}
			$keyword_search = $GLOBALS['segment7name']??'';
			$page = intval($GLOBALS['segment8name']??1);
			if($page<=0){$page = 1;}
			$limit = $_SESSION["limit"];

			$htmlStr .= '<!DOCTYPE html>
			<html>
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
					<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
					<script language="JavaScript" type="text/javascript">
						var currency = \''.$currency.'\';
						var calenderDate = \''.$calenderDate.'\';
						var timeformat = \''.$timeformat.'\';
						var loadLangFile = \''.$loadLangFile.'\';
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
			".'
					</script>
					<script src="/assets/js-'.swVersion.'/languages/'.$language.'.js"></script>';

					if(isset($_SESSION) && array_key_exists('languageJSVar', $_SESSION)){
						$languageJSVar = $_SESSION["languageJSVar"];
						if(!empty($languageJSVar)){
							$htmlStr .= "<script language=\"JavaScript\" type=\"text/javascript\">
							langModifiedData = {";
							foreach($languageJSVar as $varName=>$varValue){
								$htmlStr .= '
					\''.trim($varName).'\':stripslashes(\''.addslashes($varValue).'\'),';
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
		else{
			$customersObj = $this->db->query("SELECT customers_id FROM customers WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man AND credit_limit>0", array('customers_id'=>$segment4name),1);
			if($customersObj){
				$htmlStr .= '<!DOCTYPE html>
				<html>
					<head>
						<meta charset="utf-8">
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
						<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
						<script language="JavaScript" type="text/javascript">
							var currency = \''.$currency.'\';
							var calenderDate = \''.$calenderDate.'\';
							var timeformat = \''.$timeformat.'\';
							var loadLangFile = \''.$loadLangFile.'\';
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
			".'
						</script>
						<script src="/assets/js-'.swVersion.'/languages/'.$language.'.js"></script>';

						if(isset($_SESSION) && array_key_exists('languageJSVar', $_SESSION)){
							$languageJSVar = $_SESSION["languageJSVar"];
							if(!empty($languageJSVar)){
								$htmlStr .= "<script language=\"JavaScript\" type=\"text/javascript\">
								langModifiedData = {";
								foreach($languageJSVar as $varName=>$varValue){
									$htmlStr .= '
						\''.trim($varName).'\':stripslashes(\''.addslashes($varValue).'\'),';
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
		}
		return $htmlStr;
	}
	

}
?>