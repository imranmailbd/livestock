<?php
class Admin{
	protected $db;
	private int $page, $totalRows, $accounts_id, $additionCond, $po_id;
	private string $sorting_type, $status, $keyword_search, $invoice_number, $order_by, $paid_by, $date_range;
	private array $staOpt;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
	private function filterAndOptions_lists(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$ssorting_type = $this->sorting_type;
		$sstatus = $this->status;
		$additionCond = $this->additionCond;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Admin";
		$_SESSION["list_filters"] = array('ssorting_type'=>$ssorting_type, 'sstatus'=>$sstatus, 'additionCond'=>$additionCond, 'keyword_search'=>$keyword_search);
				
		$bindData = array();
		$filterSql = '';
		if($additionCond>0){
			if($additionCond ==1){$filterSql .= " AND a.price_per_location = 0";}
			elseif($additionCond ==2){$filterSql .= " AND a.price_per_location >0";}
			elseif($additionCond ==3){$filterSql .= " AND a.status = 'Active' AND a.next_payment_due NOT IN ('0000-00-00', '1000-01-01') AND a.next_payment_due<'".date('Y-m-d')."'";}
			elseif($additionCond ==4){$filterSql .= " AND a.paypal_id !=''";}
			elseif($additionCond ==5){$filterSql .= " AND a.paypal_id !='' AND a.paypal_id NOT LIKE 'I-%'";}
			elseif($additionCond ==6){$filterSql .= " AND a.coupon_code !=''";}
		}
		if($sstatus !=''){
			$filterSql .= " AND a.status = :status";
			$bindData['status'] = $sstatus;
		}
		if(!empty($keyword_search)){
			$keyword_search = addslashes(trim((string) $keyword_search));
			$ONAccIds = array();
			$ONObj = $this->db->querypagination("SELECT accounts_id FROM our_notes WHERE description LIKE '%$keyword_search%' GROUP BY accounts_id", array());
			if($ONObj){
				foreach($ONObj as $ONOneRow){
					$ONAccIds[$ONOneRow['accounts_id']] = '';
				}
			}
			if(!empty($ONAccIds)){
				$filterSql .= " AND (a.accounts_id IN (".implode(', ', array_keys($ONAccIds)).") OR (";
			}
			
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				if(!empty($ONAccIds)){
					if($num>0){$filterSql .= " AND";}
				}
				else{$filterSql .= " AND";}
				$filterSql .= " TRIM(CONCAT_WS(' ', a.accounts_id, u.user_first_name, u.user_last_name, u.user_email, a.company_name, a.company_subdomain, a.company_street_address, a.company_country_name, a.company_state_name, a.company_city, a.company_zip, a.paypal_id)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
			if(!empty($ONAccIds)){
				$filterSql .= "))";
			}
		}
		$sql ="SELECT a.accounts_id FROM user u, accounts a WHERE a.domain = '".OUR_DOMAINNAME."' $filterSql AND a.accounts_id = u.accounts_id AND u.is_admin = 1 GROUP BY a.accounts_id";
		$queryObj = $this->db->query($sql, $bindData);
		$totalRows = 0;
		if($queryObj){
			$totalRows = $queryObj->rowCount();
		}
		
		$tableObj = $this->db->query("SELECT status FROM accounts WHERE domain = '".OUR_DOMAINNAME."' GROUP BY status", array());
		if($tableObj){
			$staOpts = array();
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$staOpts[$oneRow->status] = '';
			}
			ksort($staOpts);
			$staOpt = array_keys($staOpts);
		}
		
		$this->totalRows = $totalRows;
		$this->staOpt = $staOpt;
	}
	
   private function loadTableRows_lists(){
		$limit = $_SESSION["limit"]??'auto';
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$ssorting_type = $this->sorting_type;
		$sstatus = $this->status;
		$additionCond = $this->additionCond;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
	
		$bindData = array();
		$filterSql = '';
		if($additionCond>0){
			if($additionCond ==1){$filterSql .= " AND a.price_per_location = 0";}
			elseif($additionCond ==2){$filterSql .= " AND a.price_per_location >0";}
			elseif($additionCond ==3){$filterSql .= " AND a.status = 'Active' AND a.next_payment_due NOT IN ('0000-00-00', '1000-01-01') AND a.next_payment_due<'".date('Y-m-d')."'";}
			elseif($additionCond ==4){$filterSql .= " AND a.paypal_id !=''";}
			elseif($additionCond ==5){$filterSql .= " AND a.paypal_id !='' AND a.paypal_id NOT LIKE 'I-%'";}
			elseif($additionCond ==6){$filterSql .= " AND a.coupon_code !=''";}
		}
		if($sstatus !=''){
			$filterSql .= " AND a.status = :status";
			$bindData['status'] = $sstatus;
		}
		if(!empty($keyword_search)){
			$keyword_search = addslashes(trim((string) $keyword_search));
			$ONAccIds = array();
			$ONObj = $this->db->querypagination("SELECT accounts_id FROM our_notes WHERE description LIKE '%$keyword_search%' GROUP BY accounts_id", array());
			if($ONObj){
				foreach($ONObj as $ONOneRow){
					$ONAccIds[$ONOneRow['accounts_id']] = '';
				}
			}
			if(!empty($ONAccIds)){
				$filterSql .= " AND (a.accounts_id IN (".implode(', ', array_keys($ONAccIds)).") OR (";
			}
			
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				if(!empty($ONAccIds)){
					if($num>0){$filterSql .= " AND";}
				}
				else{$filterSql .= " AND";}
				$filterSql .= " TRIM(CONCAT_WS(' ', a.accounts_id, u.user_first_name, u.user_last_name, u.user_email, a.company_name, a.company_subdomain, a.company_street_address, a.company_country_name, a.company_state_name, a.company_city, a.company_zip, a.paypal_id)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
			if(!empty($ONAccIds)){
				$filterSql .= "))";
			}
		}
		
		$sortingTypeData = array(0=>'a.accounts_id DESC', 
								1=>'a.accounts_id ASC', 
								2=>'u.user_first_name ASC', 
								3=>'u.user_first_name DESC',
								4=>'a.company_name ASC',
								5=>'a.company_name DESC',
								6=>'u.user_email ASC',
								7=>'u.user_email DESC',
								8=>'a.last_login DESC',
								9=>'a.next_payment_due ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}
		
		$filterSql .= " GROUP BY a.accounts_id ORDER BY ".$sortingTypeData[$ssorting_type];
		
		$sql = "SELECT * FROM user u, accounts a WHERE a.domain = '".OUR_DOMAINNAME."' AND a.accounts_id = u.accounts_id AND u.is_admin = 1 $filterSql LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sql, $bindData);
		$availableimeitotal = $openrepairtotal = $userstotal = 0;
		$tableData = array();
		if($query){
			$i = $starting_val+1;
			
			$todayDate = date('Y-m-d').' 23:59:59';
			$last7days = date('Y-m-d', time() - 7*24*60*60).' 00:00:00';
			$last30days = date('Y-m-d', time() - 30*24*60*60).' 00:00:00';
			
			$domainname = OUR_DOMAINNAME;
			foreach($query as $row){
				
				$accounts_id1 = $row['accounts_id'];
				$name = stripslashes($row['user_first_name'].' '.$row['user_last_name']);
				$company_subdomain = $row['company_subdomain'];
				
				$company_address = $row['company_street_address'];
				$company_city = $row['company_city'];
				if($company_address !='' && $company_city !=''){$company_address .= ', ';}
				$company_address .= $company_city;
				
				$company_state_name = $row['company_state_name'];
				if($company_address !='' && $company_state_name !=''){$company_address .= ', ';}
				$company_address .= $company_state_name;
				
				$company_zip = $row['company_zip'];
				if($company_address !='' && $company_zip !=''){$company_address .= '-';}
				$company_address .= $company_zip;
				
				$company_country_name = $row['company_country_name'];
				if($company_address !='' && $company_country_name !=''){$company_address .= ', ';}
				$company_address .= $company_country_name;
				
				$last_login = $row['last_login'];
				
				$commonsql = "SELECT user_login_history_id FROM user_login_history WHERE accounts_id = $accounts_id1";
				$last7daysloginCount = 0;
				$query7Obj = $this->db->query("$commonsql AND (login_datetime BETWEEN '$last7days' AND '$todayDate') GROUP BY accounts_id, substring(login_datetime,1,10)", array());
				if($query7Obj){
					$last7daysloginCount = $query7Obj->rowCount();
				}
				$last30daysloginCount = 0;
				$query30Obj = $this->db->query("$commonsql AND (login_datetime BETWEEN '$last30days' AND '$todayDate') GROUP BY accounts_id, substring(login_datetime,1,10)", array());
				if($query30Obj){
					$last30daysloginCount = $query30Obj->rowCount();
				}
				$allloginCount = 0;						
				$queryAllObj = $this->db->query("$commonsql GROUP BY accounts_id, substring(login_datetime,1,10)", array());
				if($queryAllObj){
					$allloginCount = $queryAllObj->rowCount();
				}
				
				$commonsql = "SELECT COUNT(item_id) AS totalrows FROM item WHERE accounts_id = $accounts_id1";
				$last7daysIMEICount = 0;
				$queryObj = $this->db->query("$commonsql AND (created_on BETWEEN '$last7days' AND '$todayDate')", array());
				if($queryObj){
					$last7daysIMEICount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				$last30daysIMEICount = 0;
				$queryObj = $this->db->query("$commonsql AND (created_on BETWEEN '$last30days' AND '$todayDate')", array());
				if($queryObj){
					$last30daysIMEICount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				$allIMEICount = 0;
				$queryObj = $this->db->query("$commonsql", array());
				if($queryObj){
					$allIMEICount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				
				$commonsql = "SELECT COUNT(repairs_id) AS totalrows FROM repairs WHERE accounts_id = $accounts_id1";
				$last7daysRepairsCount = 0;
				$queryObj = $this->db->query("$commonsql AND (created_on BETWEEN '$last7days' AND '$todayDate')", array());
				if($queryObj){
					$last7daysRepairsCount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				$last30daysRepairsCount = 0;
				$queryObj = $this->db->query("$commonsql AND (created_on BETWEEN '$last30days' AND '$todayDate')", array());
				if($queryObj){
					$last30daysRepairsCount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				$allRepairsCount = 0;
				$queryObj = $this->db->query("$commonsql", array());
				if($queryObj){
					$allRepairsCount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				
				$commonsql = "SELECT count(pos_id) as totalrows FROM pos WHERE accounts_id = $accounts_id1";
				$last7daysInvoicesCount = 0;
				$queryObj = $this->db->query("$commonsql AND (sales_datetime BETWEEN '$last7days' AND '$todayDate')", array());
				if($queryObj){
					$last7daysInvoicesCount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				$last30daysInvoicesCount = 0;
				$queryObj = $this->db->query("$commonsql AND (sales_datetime BETWEEN '$last30days' AND '$todayDate')", array());
				if($queryObj){
					$last30daysInvoicesCount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				$allInvoicesCount = 0;
				$queryObj = $this->db->query("$commonsql", array());
				if($queryObj){
					$allInvoicesCount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				
				$democlass = '';
				$price_per_location = $row['price_per_location'];
				$status = $row['status'];						
				if($status=='Active' && $price_per_location==0){}
				elseif($status=='CANCELED' || $status=='SUSPENDED'){
					$democlass = ' class="bgcyan padding6"';
				}
				elseif($status=='Trial'){
					$democlass = ' class="bgyellow padding6"';
				}
				
				$paypal_id = $row['paypal_id'];
				$Payments = '';
				$next_payment_due = $row['next_payment_due'];
				if($paypal_id !=''){
					$Payments .= ' Other';
				}
				
				//=====================Days Left for Trial User==========================//
				$daysleftstr = '';
				$trial_days = $row['trial_days'];
				if($status =='Trial'){
					$created_ontime = strtotime(substr($row['created_on'], 0,10));
					$registeredDays = floor((strtotime(date('Y-m-d')) - $created_ontime) / 86400);							
					$DaysRemaining = $trial_days-$registeredDays;
					if($DaysRemaining<0){$DaysRemaining = 0;}
					$daysleftstr = " ($DaysRemaining)";
				}
								
				$paypal_id = $row['paypal_id'];
				$parentlocationname = '';
				$queryObj = $this->db->query("SELECT company_subdomain FROM accounts WHERE accounts_id = $row[location_of]", array());
				if($queryObj){
					$parentlocationname = 'Sub';						
				}
				
				$sublocationcount = 0;
				$queryObj = $this->db->query("SELECT COUNT(accounts_id) AS totalrows FROM accounts WHERE location_of = $accounts_id1 AND status = 'Active'", array());
				if($queryObj){
					$sublocationcount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
					if($sublocationcount>0){
						$parentlocationname = 'Main';
					}						
				}
				
				if($status=='Active' && $price_per_location==0){$daysleftstr = '/0';}

				$tableData[] = array($page, intval($accounts_id1), $parentlocationname, $company_subdomain, "$name / $company_country_name", "$last7daysloginCount-$last30daysloginCount-$allloginCount", "$last7daysIMEICount-$last30daysIMEICount-$allIMEICount", "$last7daysRepairsCount-$last30daysRepairsCount-$allRepairsCount", "$last7daysInvoicesCount-$last30daysInvoicesCount-$allInvoicesCount", $Payments, "<span$democlass>$status</span>$daysleftstr", $last_login, $next_payment_due);
				
				$i++;
			}
		}
		
		return $tableData;
   }
	
	public function AJgetPage_lists($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$ssorting_type = $POST['ssorting_type']??0;
		$sstatus = $POST['sstatus']??'';
		$additionCond = $POST['additionCond']??0;
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->sorting_type = $ssorting_type;
		$this->status = $sstatus;
		$this->additionCond = $additionCond;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_lists();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['staOpt'] = $this->staOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableData'] = $this->loadTableRows_lists();
		
		return json_encode($jsonResponse);
	}
	
	public function edit(){}
	
	public function showNoteDescription(){
		$tabledata = array();
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = intval($POST['accounts_id']??0);
		
		$sqlquery = "SELECT * FROM our_notes WHERE accounts_id = $accounts_id ORDER BY created_on DESC";
		$query = $this->db->querypagination($sqlquery, array());
		
		if($query){
			foreach($query as $oneRow){	
				$description = nl2br(stripslashes($oneRow['description']));
				$tabledata[] = array($oneRow['our_notes_id'], $oneRow['created_on'], $description);
			}
		}		
		return json_encode(array('login'=>'', 'tabledata'=>$tabledata));
	}
	
	public function AJ_edit_MoreInfo(){

		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = intval($POST['accounts_id']??0);
		$accountsObj = $this->db->query("SELECT a.*, u.user_email, u.popup_message FROM accounts a, user u WHERE a.accounts_id = u.accounts_id AND u.is_admin = 1 AND a.accounts_id = :accounts_id", array('accounts_id'=>$accounts_id),1);
		if($accountsObj){
			$oneRow = $accountsObj->fetch(PDO::FETCH_OBJ);
			$jsonResponse['company_subdomain'] = $oneRow->company_subdomain;
			$jsonResponse['accounts_id'] = $oneRow->accounts_id;
			$jsonResponse['company_name'] = stripslashes($oneRow->company_name);
			$address = $oneRow->company_street_address;
			if($oneRow->company_city !=''){
				$address .= ', '.$oneRow->company_city;
			}
			if($oneRow->company_state_name !=''){
				$address .= ', '.$oneRow->company_state_name;
			}
			if($oneRow->company_zip !=''){
				$address .= ' '.$oneRow->company_zip;
			}
			if($oneRow->company_country_name !=''){
				$address .= ', '.$oneRow->company_country_name;
			}
			$jsonResponse['address'] = $address;
			
			$jsonResponse['company_phone_no'] = $oneRow->company_phone_no;
			$jsonResponse['user_email'] = $oneRow->user_email;
			$jsonResponse['paypal_id'] = $oneRow->paypal_id;
			
			$locationInfo = '';
			if($oneRow->location_of>0){
				$locationquery = $this->db->query("SELECT company_subdomain FROM accounts WHERE accounts_id = $oneRow->location_of AND status != 'SUSPENDED'", array());
				if($locationquery){
					$locationInfo .= $locationquery->fetch(PDO::FETCH_OBJ)->company_subdomain;
				}
			}
			else{
				$locationquery = $this->db->query("SELECT company_subdomain FROM accounts WHERE location_of = $oneRow->accounts_id AND status != 'SUSPENDED'", array());
				if($locationquery){
					$l=0;
					while($oneUserRow = $locationquery->fetch(PDO::FETCH_OBJ)){
						$l++;
						if($l>1){$locationInfo .= ', ';}
						$locationInfo .= $oneUserRow->company_subdomain;															
					}
				}
			}
			$jsonResponse['locationInfo'] = $locationInfo;			
			$jsonResponse['last_login'] = $oneRow->last_login;
			
			$jsonResponse['created_on'] = $oneRow->created_on;			
			$jsonResponse['status'] = $oneRow->status;
			$jsonResponse['status_date'] = $oneRow->status_date;
			$jsonResponse['trial_days'] = $oneRow->trial_days;
			
			$No_of_Location = 1;
			$accSql = "SELECT COUNT(accounts_id) as totalAccount FROM accounts WHERE (accounts_id = $oneRow->accounts_id OR location_of = $oneRow->accounts_id) AND status != 'SUSPENDED'";
			$noOfLocObj = $this->db->query($accSql, array());
			if($noOfLocObj){
				while($noOfLocRow = $noOfLocObj->fetch(PDO::FETCH_OBJ)){
					$No_of_Location = $noOfLocRow->totalAccount;
				}
			}
			$jsonResponse['No_of_Location'] = $No_of_Location;
			
			$nextnext_payment_due = date('Y-m-d', strtotime(date('Y-m-d')."+1 month"));			
			if(!in_array($oneRow->next_payment_due, array('0000-00-00', '1000-01-01'))){
				$date = new DateTime($oneRow->next_payment_due);
				$date->modify('+1 month');
				$nextnext_payment_due = $date->format('Y-m-d');
			}
			$jsonResponse['next_payment_due'] = $oneRow->next_payment_due;
			$jsonResponse['nextnext_payment_due'] = $nextnext_payment_due;
			$jsonResponse['coupon_code'] = $oneRow->coupon_code;
			$jsonResponse['pay_frequency'] = $oneRow->pay_frequency;
			$jsonResponse['price_per_location'] = $oneRow->price_per_location;
			$access_token = $pstatus = '';
			if($oneRow->paypal_id !=''){
				$pstatus = 'Active';
			}
			$jsonResponse['popup_message'] = stripslashes($oneRow->popup_message);

			$jsonResponse['address'] = $address;
		}		
		
		return json_encode($jsonResponse);
	}
	
	public function AJget_AccountsPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
	
		$accounts_id = $POST['accounts_id'];
		$returnData = array('login'=>'');
		$accountsObj = $this->db->query("SELECT a.company_name, a.company_subdomain, u.user_email, a.trial_days, a.paypal_id, a.status, a.price_per_location, a.pay_frequency, a.next_payment_due, a.coupon_code, a.accounts_id FROM accounts a, user u WHERE a.accounts_id = u.accounts_id AND u.is_admin = 1 AND a.accounts_id = :accounts_id", array('accounts_id'=>$accounts_id),1);
		if($accountsObj){
			$oneRow = $accountsObj->fetch(PDO::FETCH_OBJ);
			$returnData['company_name'] = $oneRow->company_name;
			$returnData['company_subdomain'] = $oneRow->company_subdomain;
			$returnData['user_email'] = $oneRow->user_email;
			$returnData['trial_days'] = $oneRow->trial_days;
			$returnData['paypal_id'] = $oneRow->paypal_id;
			$returnData['statusVal'] = $oneRow->status;
			$returnData['price_per_location'] = $oneRow->price_per_location;
			$returnData['pay_frequency'] = $oneRow->pay_frequency;
			$nextnext_payment_due = date('Y-m-d', strtotime("+1 month"));
			if(!in_array($oneRow->next_payment_due, array('0000-00-00', '1000-01-01'))){
				$date = new DateTime($oneRow->next_payment_due);
				$date->modify('+1 month');
				$nextnext_payment_due = $date->format('Y-m-d');
			}
			$returnData['next_payment_due'] = $oneRow->next_payment_due;
			$returnData['coupon_code'] = $oneRow->coupon_code;
			$returnData['nextnext_payment_due'] = $nextnext_payment_due;
			$returnData['accounts_id'] = $oneRow->accounts_id;
		}
		
		return json_encode($returnData);		
	}
	
	public function AJsave_Accounts(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $POST['accounts_id'];
		$accountsData = array();
		$accountsData['company_subdomain'] = $company_subdomain = $this->db->checkCharLen('accounts.company_subdomain', $POST['company_subdomain']);
		$user_email = $this->db->checkCharLen('user.user_email', $POST['user_email']);
		$returnStr = '';
		
		$trial_days = $POST['trial_days'];
		if(empty($trial_days)){
			$returnStr = 'Missing Trial days. Please enter valid Trial days.';
		}
		elseif($trial_days != intval($trial_days)){
			$returnStr = 'Invalid Trial days. Please enter valid Trial days.';
		}
		$accountsData['trial_days'] = intval($trial_days);
		$accountsData['paypal_id'] = $this->db->checkCharLen('accounts.paypal_id', $POST['paypal_id']);
		$accountsData['status'] = $status = $this->db->checkCharLen('accounts.status', $POST['status']);
		$accountsData['coupon_code'] = $this->db->checkCharLen('accounts.coupon_code', $POST['coupon_code']);
		$accountsData['status_date'] = date('Y-m-d H:i:s');
		$price_per_location = $POST['price_per_location'];
		if(empty($price_per_location)){
			$returnStr = 'Missing Price/Location. Please enter valid Price/Location.';
		}
		elseif($price_per_location != floatval($price_per_location)){
			$returnStr = 'Invalid Price/Location. Please enter valid Price/Location.';
		}
		$accountsData['price_per_location'] = floatval($price_per_location);
		$accountsData['pay_frequency'] = $POST['pay_frequency'];
		$next_payment_due = '1000-01-01';
		if($POST['next_payment_due'] !=''){
			$next_payment_due = date('Y-m-d', strtotime($POST['next_payment_due']));
		}
		$accountsData['next_payment_due'] = $next_payment_due;
		
		if($accounts_id>0 && empty($returnStr)){
			$checkduplicateemailentry = 0;
			$accountsObj = $this->db->query("SELECT COUNT(accounts_id) AS totalrows FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain AND accounts_id !=:accounts_id", array('company_subdomain'=>$company_subdomain, 'domain'=>OUR_DOMAINNAME, 'accounts_id'=>$accounts_id));
			if($accountsObj){
				$checkduplicateemailentry = $accountsObj->fetch(PDO::FETCH_OBJ)->totalrows;						
			}
			if($checkduplicateemailentry>0){
				$returnStr = 'This sub-domain name is not available. Please enter a new sub-domain name.';
			}
			else{
				$checkduplicateemailentry = 0;
				$queryObj = $this->db->query("SELECT COUNT(user_id) AS totalrows FROM user WHERE accounts_id = :accounts_id AND user_email = :user_email AND user_publish = 1", array('user_email'=>$user_email, 'accounts_id'=>$accounts_id));
				if($queryObj){
					$checkduplicateemailentry = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				if($checkduplicateemailentry>1){
					$returnStr = "This email already exist! Please try again with different email.";
				}				
				else{
					$oldStatus = '';
					$accOneObj = $this->db->query("SELECT status FROM accounts WHERE accounts_id = $accounts_id", array());
					if($accOneObj){
						$oldStatus = $accOneObj->fetch(PDO::FETCH_OBJ)->status;
					}
					$this->db->update('accounts', $accountsData, $accounts_id);
					if($oldStatus != $status){
						date_default_timezone_set('America/New_York');
						$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
											'accounts_id'=>$accounts_id,
											'description'=>"Account status changed from $oldStatus to $status");
						$this->db->insert('our_notes', $our_notesData);
						$timezone = 'America/New_York';
						if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
						date_default_timezone_set($timezone);
					}
					
					$sqlquery = "SELECT user_id FROM user WHERE accounts_id = $accounts_id AND is_admin = 1";
					$query = $this->db->query($sqlquery, array());
					if($query){
						while($oneRow = $query->fetch(PDO::FETCH_OBJ)){								
							$this->db->update('user', array('user_email'=>$user_email, 'last_updated'=> date('Y-m-d H:i:s')), $oneRow->user_id);
						}
					}	
					$returnStr = "Save Successfully";
				}
			}			
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJ_importCustomers_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$company_name = $company_subdomain = '';
		$accounts_id = intval($POST['accounts_id']??0);
		if($accounts_id>0){
			$userObj = $this->db->query("SELECT accounts_id, company_name, company_subdomain FROM accounts WHERE accounts_id = $accounts_id AND status != 'SUSPENDED' ORDER BY company_subdomain ASC", array());
			if($userObj){
				while($onerow = $userObj->fetch(PDO::FETCH_OBJ)){
					$accounts_id = $onerow->accounts_id;
					$company_name = $onerow->company_name;
					$company_subdomain = $onerow->company_subdomain;
				}
			}
		}
		$jsonResponse['accounts_id'] = $accounts_id;
		$jsonResponse['company_name'] = $company_name;
		$jsonResponse['company_subdomain'] = $company_subdomain;		
		return json_encode($jsonResponse);
	}
	
	public function importCustomers(){}
	
	public function saveImportCustomers(){
		$returnStr = '';
		$redirectURL = '/Admin/';
		if(isset($_SESSION["user_id"]) && $_SESSION["user_id"] <=6){
			$POST = $_POST;
			$instance_id = $prod_cat_man = $POST['instance_id'];
			$containheading = $POST['containheading'];
			$full_path = '';
			$folderpath = "./assets/temp-text-file/";
			$uploadfile = basename($_FILES['import_file']['name']);
			$upload_filearray = explode('.', $uploadfile);
			$fileextension = $upload_filearray[floor(count($upload_filearray)-1)];
			$tempFile = $_FILES['import_file']['tmp_name'];
			if($fileextension=='csv' || $fileextension=='txt'){
				$file_name = $instance_id.'importCustomers'.time().'.'.$fileextension;
				$full_path1 = $folderpath.$file_name;
				if(move_uploaded_file($tempFile, $full_path1)){
					$full_path = $full_path1;
				}
			}

			if($instance_id>0 && $full_path !=''){
				$redirectURL .= 'edit/1/'.$instance_id;
				$sql = "SELECT location_of FROM accounts WHERE accounts_id = :instance_id AND location_of>0 ORDER BY accounts_id ASC";
				$dataObj = $this->db->query($sql, array('instance_id'=>$instance_id),1);
				if($dataObj){
					while($oneRow = $dataObj->fetch(PDO::FETCH_OBJ)){
						$prod_cat_man = $oneRow->location_of;
					}
				}
				$user_id = 0;
				$sql = "SELECT user_id FROM user WHERE accounts_id = :instance_id AND is_admin=1 ORDER BY user_id ASC";
				$dataObj = $this->db->query($sql, array('instance_id'=>$instance_id),1);
				if($dataObj){
					while($oneRow = $dataObj->fetch(PDO::FETCH_OBJ)){
						$user_id = $oneRow->user_id;
					}
				}
				
				$Common = new Common($this->db);
				$vData = $Common->variablesData('account_setup', $instance_id);
				if(!empty($vData)){
					$timezone = $vData['timezone']??($_SESSION["timezone"]??'America/New_York');
					date_default_timezone_set($timezone);
				}
				
				$insercount = $updatecount = 0;
				$ourFieldsArray = $POST['ourFields'];
				$importColumnsArray = $POST['column'];
				
				$handle = fopen($full_path, "r");
				
				if($containheading==1){fgetcsv($handle);}
				
				while($row = fgetcsv($handle)) {
					$f = 0;
					$dataArray = array( 'customers_publish'=>1,
										'created_on' => date('Y-m-d H:i:s'),
										'last_updated' => date('Y-m-d H:i:s'),
										'accounts_id'=>$prod_cat_man,
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
					$c=0;					
					foreach($importColumnsArray as $onefieldname){
						
						$oneOurfieldName = trim((string) $ourFieldsArray[$f]);
						$importfieldName = trim((string) $importColumnsArray[$f]);
						$onefieldvalue = '';
						if($importfieldName !=''){
							if($importfieldName=='Yes'){
								$onefieldvalue = 'Y';
							}
							elseif($importfieldName=='No'){
								$onefieldvalue = 'N';
							}
							elseif(array_key_exists($importfieldName, $row)){
								$onefieldvalue = trim((string) $row[$importfieldName]);
							}
						}

						if($onefieldvalue != ''){
							$dataArray[$oneOurfieldName] = $onefieldvalue;
							$c++;
						}
						$f++;
					}
					
					if($c>0){
						$full_name = addslashes($dataArray['full_name']??'');
						
						$first_name = $last_name = $email = '';
						if($full_name !=''){
							$full_nameexp = explode(' ', $full_name);
							if(count($full_nameexp)>1){
								$first_name = $full_nameexp[0];
								$last_name = implode(' ', array_slice($full_nameexp, 1));								
							}
							else{
								$first_name = $full_name;
							}
							$dataArray['first_name'] = $first_name;
							$dataArray['last_name'] = $last_name;
						}
						else{
							if (is_array($dataArray) && array_key_exists("first_name", $dataArray)){
								$first_name = addslashes($dataArray['first_name']);
							}							
							
							if (is_array($dataArray) && array_key_exists("last_name", $dataArray)){
								$last_name = addslashes($dataArray['last_name']);								
							}
						}
						
						unset($dataArray['full_name']);
						
						if (is_array($dataArray) && array_key_exists("email", $dataArray)){
							$email = addslashes($dataArray['email']);
						}
						
						if (is_array($dataArray) && array_key_exists("offers_email", $dataArray)){
							$offers_email = addslashes($dataArray['offers_email']);
							$offers_emailvalue = 0;
							if(strtolower($offers_email)=='y' || strtolower($offers_email)=='yes'){
								$offers_emailvalue = 1;
							}
							$dataArray['offers_email'] = $offers_emailvalue;
						}
						
						$customer_type = $this->db->checkCharLen('customer_type.name', addslashes($dataArray['customer_type']??''));
						
						if($first_name !=''){							
							$dataArray['first_name'] = $first_name = $this->db->checkCharLen('customers.first_name', $first_name);
							$dataArray['last_name'] = $last_name = $this->db->checkCharLen('customers.last_name', $last_name);
							$email = $this->db->checkCharLen('customers.email', $dataArray['email']);
							$dataArray['email'] = $email;							
							$dataArray['company'] = $this->db->checkCharLen('customers.company', $dataArray['company']);
							$contact_no = $this->db->checkCharLen('customers.contact_no', $dataArray['contact_no']);
							$dataArray['contact_no'] = $contact_no;
							$dataArray['secondary_phone'] = $this->db->checkCharLen('customers.secondary_phone', $dataArray['secondary_phone']);
							$dataArray['fax'] = $this->db->checkCharLen('customers.fax', $dataArray['fax']);
							$dataArray['customer_type'] = $this->db->checkCharLen('customers.customer_type', $dataArray['customer_type']);
							$dataArray['shipping_address_one'] = $this->db->checkCharLen('customers.shipping_address_one', $dataArray['shipping_address_one']);
							$dataArray['shipping_address_two'] = $this->db->checkCharLen('customers.shipping_address_two', $dataArray['shipping_address_two']);
							$dataArray['shipping_city'] = $this->db->checkCharLen('customers.shipping_city', $dataArray['shipping_city']);
							$dataArray['shipping_state'] = $this->db->checkCharLen('customers.shipping_state', $dataArray['shipping_state']);
							$dataArray['shipping_zip'] = $this->db->checkCharLen('customers.shipping_zip', $dataArray['shipping_zip']);
							$dataArray['shipping_country'] = $this->db->checkCharLen('customers.shipping_country', $dataArray['shipping_country']);
							$dataArray['website'] = $this->db->checkCharLen('customers.website', $dataArray['website']);
							
							$andcondition = " AND contact_no = '$contact_no'";
							if($contact_no=='' && $email !=''){
								$andcondition = " AND email = '$email'";
							}
							elseif($contact_no=='' && $email ==''){
								$andcondition = " AND first_name = '$first_name' AND last_name = '$last_name'";
							}
							$checkduplicate = 0;
							$sqldupl = "SELECT customers_id FROM customers WHERE accounts_id = :prod_cat_man $andcondition LIMIT 0,1";
							$queryObj = $this->db->querypagination($sqldupl, array('prod_cat_man'=>$prod_cat_man),1);
							if($queryObj){
								foreach($queryObj as $onerow){
									$checkduplicate = $onerow['customers_id'];
									$this->db->update('customers', array('customers_publish'=>1), $checkduplicate);			
								}
							}
							
							if($checkduplicate>0){
								$updatecount++;
							}
							else{
								$customers_id = $this->db->insert('customers', $dataArray);
								if($customers_id){
									if($customer_type !='' && $prod_cat_man !=''){
										$checkduplicate = 0;
										$sqldupl = "SELECT COUNT(customer_type_id) AS totalrows FROM customer_type WHERE accounts_id = :prod_cat_man AND UPPER(name) = '".strtoupper($customer_type)."' AND customer_type_publish = 1";
										$queryObj = $this->db->query($sqldupl, array('prod_cat_man'=>$prod_cat_man),1);
										if($queryObj){
											$checkduplicate = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
										}
										if($checkduplicate==0){
											$cusTypeData = array('created_on' => date('Y-m-d H:i:s'),
																'accounts_id' => $prod_cat_man,
																'user_id' => $user_id,
																'name' => $customer_type);
											$this->db->insert('customer_type', $cusTypeData);
										}
									}
									$insercount++;
								}							
							}
						}
					}
				}
				
				$returnStr = $insercount.' data has been inserted AND '.$updatecount.'  data has been duplicate.';
				
				$timezone = $_SESSION["timezone"]??'America/New_York';
				date_default_timezone_set($timezone);		
			}
		}
		
		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURL?msg=$returnStr\" />";
	}
	
	public function importProduct(){}
	
	public function saveImportProduct(){
		$returnStr = '';
		$redirectURL = '/Admin/';
		if(isset($_SESSION["user_id"]) && $_SESSION["user_id"] <=6){	
			$POST = $_POST;//json_decode(file_get_contents('php://input'), true);		
			$instance_id = $POST['instance_id'];
			$redirectURL .= 'edit/1/'.$instance_id;
			$containheading = $POST['containheading'];		
			$product_type = $POST['product_type'];
			$this->po_id = 0;
			$prod_cat_man = $instance_id;
			$queryObj = $this->db->query("SELECT location_of FROM accounts WHERE accounts_id = :accounts_id AND location_of>0", array('accounts_id'=>$instance_id),1);
			if($queryObj){
				$prod_cat_man = $queryObj->fetch(PDO::FETCH_OBJ)->location_of;						
			}
			
			$Common = new Common($this->db);
			$vData = $Common->variablesData('account_setup', $instance_id);
			if(!empty($vData)){
				$timezone = $vData['timezone']??($_SESSION["timezone"]??'America/New_York');
				date_default_timezone_set($timezone);
			}
			
			$user_id = 0;
			$sql = "SELECT user_id FROM user WHERE accounts_id = :accounts_id AND is_admin=1 ORDER BY user_id ASC";
			$dataObj = $this->db->query($sql, array('accounts_id'=>$instance_id), 1);
			if($dataObj){
				while($oneRow = $dataObj->fetch(PDO::FETCH_OBJ)){
					$user_id = $oneRow->user_id;
				}
			}
			
			$variables_id = 0;
			$newCondi = 0;
			$conditionsarray = array('A', 'B', 'C', 'D', 'New');
			$productColumns = array('category_id', 'manufacturer_id', 'product_name', 'sku', 'taxable');            
			$inventoryColumns = array('ave_cost', 'regular_price');
			
            if($product_type=='Standard'){
				array_push($productColumns, 'require_serial_no', 'manage_inventory_count', 'allow_backorder');
				
				array_push($inventoryColumns, 'current_inventory', 'low_inventory_alert');
			}
			elseif($product_type=='Live Stocks'){
				array_push($productColumns, 'colour_name', 'storage', 'physical_condition_name', 'item_number');
				
				array_push($inventoryColumns, 'low_inventory_alert');
				
				$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = :accounts_id AND name = 'product_setup'", array('accounts_id'=>$instance_id),1);
				if($varObj){
					$variablesData = $varObj->fetch(PDO::FETCH_OBJ);					
					$variables_id = $variablesData->variables_id;
					$value = $variablesData->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('conditions', $value)){
							$conditions = $value['conditions'];
							if($conditions !=''){
								$conditionsarray = explode('||',$conditions);
							}
						}
					}
				}
			}
			elseif($product_type=='Labor/Services'){
				array_push($inventoryColumns, 'ave_cost_is_percent');
			}

			$full_path = '';
			$folderpath = "./assets/temp-text-file/";
			$uploadfile = basename($_FILES['import_file']['name']);
			$upload_filearray = explode('.', $uploadfile);
			$fileextension = $upload_filearray[floor(count($upload_filearray)-1)];
			$tempFile = $_FILES['import_file']['tmp_name'];
			if($fileextension=='csv' || $fileextension=='txt'){
				$file_name = $instance_id.'importProduct'.time().'.'.$fileextension;
				$full_path1 = $folderpath.$file_name;
				if(move_uploaded_file($tempFile, $full_path1)){
					$full_path = $full_path1;
				}
			}

			if($instance_id>0 && $full_path !=''){
				$insercount = $updatecount = 0;
				$ourFieldsArray = $POST['ourFields'];
				$importColumnsArray = $POST['column'];
				
				$handle = fopen($full_path, "r");                            
				
				if($containheading==1){
					fgetcsv($handle);
				}
				$errorDebug = array();
				while($row = fgetcsv($handle)) {            
					
					$f=0;
					$pdataArray = array('created_on'=>date('Y-m-d H:i:s'),
										'last_updated'=>date('Y-m-d H:i:s'),
										'accounts_id'=>$prod_cat_man,
										'user_id'=>$user_id,
										'product_type'=> $product_type,
										'category_id'=>0,
										'manufacturer_id'=>0,
										'manufacture'=>'',
										'colour_name'=>'',
										'storage'=>'',
										'physical_condition_name'=>'',
										'product_name'=>'',
										'sku'=>'',
										'require_serial_no'=>0,
										'description'=>'',
										'manage_inventory_count'=>0,
										'allow_backorder'=>1,
										'add_description'=>'');
					
					$idataArray = array('product_id'=>0,
										'accounts_id'=>$instance_id,
										'regular_price'=>0.00,
										'ave_cost'=>0.00,
										'ave_cost_is_percent'=>0,
										'current_inventory'=>0,
										'low_inventory_alert'=>0,
										'prices_enabled'=>0);
					$item_number = '';
					$ave_cost = 0.00;
					foreach($importColumnsArray as $onefieldname){
						$oneOurfieldName = trim((string) $ourFieldsArray[$f]);
						$importfieldName = trim((string) $importColumnsArray[$f]);
						$onefieldvalue = '';
						if($importfieldName !=''){
							if(strtolower($importfieldName)=='yes'){
								$onefieldvalue = 'y';
							}
							elseif(strtolower($importfieldName)=='no'){
								$onefieldvalue = 'n';
							}
							elseif($importfieldName=='Auto Create'){
								$onefieldvalue = '';
							}							
							elseif(array_key_exists($importfieldName, $row)){
								$onefieldvalue = trim((string) $row[$importfieldName]);
							}
						}
						
						if(strtolower($onefieldvalue)=='n'){
							$onefieldvalue = 0;
						}
						elseif(strtolower($onefieldvalue)=='y'){
							$onefieldvalue = 1;
						}
						
						if($oneOurfieldName !='' && !strcmp($onefieldvalue, '')==0){
							if(in_array($oneOurfieldName,  $inventoryColumns)){
								if($oneOurfieldName =='regular_price'){
									$regular_price = preg_replace("/[^0-9.]/", "", $onefieldvalue);
									$regular_price = round($regular_price,2);
									$idataArray[$oneOurfieldName] = $regular_price;
								}
								elseif($oneOurfieldName =='ave_cost'){
									$ave_cost = floatval(preg_replace("/[^0-9.]/", "", $onefieldvalue));
									$ave_cost = round($ave_cost,2);
									$idataArray[$oneOurfieldName] = $ave_cost;
								}
								else{
									$idataArray[$oneOurfieldName] = $onefieldvalue;
								}
							}
							elseif(in_array($oneOurfieldName,  $productColumns)){
								if($oneOurfieldName =='category_id'){
									if(in_array($onefieldvalue, array('', '0'))){}
									else{
										$category_name = $this->db->checkCharLen('category.category_name', $onefieldvalue);
										$oldcategory_id = 0;
										$queryCatObj = $this->db->query("SELECT category_id, category_publish FROM category WHERE UPPER(category_name) = '".strtoupper($category_name)."' AND accounts_id = $prod_cat_man", array());
										if($queryCatObj){
											while($oneRowCat = $queryCatObj->fetch(PDO::FETCH_OBJ)){
												$oldcategory_id = $oneRowCat->category_id;
												$category_publish = $oneRowCat->category_publish;
												if($category_publish==0){
													$this->db->update('category', array('category_publish'=>1), $oldcategory_id);
												}
											}
										}
										if($oldcategory_id >0){						
											$category_id = $oldcategory_id;
										}
										else{
											$category_name = $this->db->checkCharLen('category.category_name', $category_name);
											
											$categoryData = array('created_on' => date('Y-m-d H:i:s'),			
																'last_updated' => date('Y-m-d H:i:s'),
																'accounts_id' => $prod_cat_man,
																'user_id' => $user_id,
																'category_name' => $category_name
																);
											$category_id = $this->db->insert('category', $categoryData);
										}
										$pdataArray['category_id'] = $category_id;
									}
								}
								elseif($oneOurfieldName =='manufacturer_id'){
									
									if(in_array($onefieldvalue, array('', '0'))){}
									else{
										$manufacturer_name = $this->db->checkCharLen('manufacturer.name', $onefieldvalue);
										$oldmanufacturer_id = 0;
										$queryManObj = $this->db->query("SELECT manufacturer_id, manufacturer_publish FROM manufacturer WHERE UPPER(name) = '".strtoupper($manufacturer_name)."' AND accounts_id = $prod_cat_man", array());
										if($queryManObj){
											while($oneRowMan = $queryManObj->fetch(PDO::FETCH_OBJ)){
												$oldmanufacturer_id = $oneRowMan->manufacturer_id;
												$manufacturer_publish = $oneRowMan->manufacturer_publish;
												if($manufacturer_publish==0){
													$this->db->update('manufacturer', array('manufacturer_publish'=>1), $oldmanufacturer_id);
												}
											}
										}
										if($oldmanufacturer_id >0){						
											$manufacturer_id = $oldmanufacturer_id;
										}
										else{
											$manufacturer_name = $this->db->checkCharLen('manufacturer.name', $manufacturer_name);
											
											$manufacturerData = array('created_on' => date('Y-m-d H:i:s'),			
																'last_updated' => date('Y-m-d H:i:s'),
																'accounts_id' => $prod_cat_man,
																'user_id' => $user_id,
																'name' => $manufacturer_name
																);
											$manufacturer_id = $this->db->insert('manufacturer', $manufacturerData);
										}
										$pdataArray['manufacturer_id'] = $manufacturer_id;
									}
								}
								elseif($oneOurfieldName =='physical_condition_name'){
									if(in_array($onefieldvalue, array('', '0'))){}
									else{
										$pdataArray['physical_condition_name'] = $physical_condition_name = $this->db->checkCharLen('product.physical_condition_name', $onefieldvalue);
											
										if(!in_array($physical_condition_name, $conditionsarray)){
											$newCondi++;
											array_push($conditionsarray, $physical_condition_name);
										}
									}
								}
								elseif($oneOurfieldName =='item_number'){
									$item_number = $this->db->checkCharLen('item.item_number', $onefieldvalue);
								}
								else{																
									if(in_array($oneOurfieldName, array('taxable', 'require_serial_no', 'manage_inventory_count', 'allow_backorder')) && in_array(strtolower($onefieldvalue), array('yes', 'no'))){
										if(strtolower($onefieldvalue)=='yes'){
											$onefieldvalue = 1;
										}
										elseif(strtolower($onefieldvalue)=='no'){
											$onefieldvalue = 0;
										}
									}
									
									$pdataArray[$oneOurfieldName] = $onefieldvalue;
								}
							}
						}
						
						$f++;
					}					
					
					if(count($pdataArray)>0){
						
						$product_name = addslashes($pdataArray['product_name']??'');

						if (is_array($idataArray)){
							if(array_key_exists("regular_price", $idataArray)){
								$regular_price = $idataArray['regular_price'];
								$regular_price = preg_replace("/[^0-9.]/", "", $regular_price);
								$idataArray['regular_price'] = round($regular_price,2);
							}
							if(array_key_exists("current_inventory", $idataArray)){
								$current_inventory = intval($idataArray['current_inventory']);
								//$current_inventory = preg_replace("/[^0-9.]/", "", $current_inventory);
								if($current_inventory<0){$current_inventory = 0;}
								$idataArray['current_inventory'] = $current_inventory;
							}
							
							if($product_type=='Live Stocks'){
								$pdataArray['manage_inventory_count'] = 1;
							}
							elseif($product_type=='Labor/Services'){
								$pdataArray['manage_inventory_count'] = 0;
								$idataArray['current_inventory'] = 0;								
							}
							
							if(array_key_exists("ave_cost", $idataArray)){
								$ave_cost = $idataArray['ave_cost'];
								$ave_cost = preg_replace("/[^0-9.]/", "", $ave_cost);
								if($ave_cost<0){$ave_cost = 0.00;}
								if((array_key_exists("current_inventory", $idataArray) && $idataArray['current_inventory']>0) || $product_type=='Labor/Services'){
									$idataArray['ave_cost'] = round($ave_cost,2);
								}
								elseif($pdataArray['manage_inventory_count']==0){
									$idataArray['ave_cost'] = round($ave_cost,2);
								}
							}
						}
						
						if($product_name !=''){
							$pdataArray['product_name'] = $product_name = $this->db->checkCharLen('product.product_name', $product_name);
							if(strlen($product_name)>90){
								//$this->db->writeIntoLog('Line# 1146, product_name: '.$product_name);
								$product_name = substr($product_name, 0, 90);
							}
							$product_id = 0;
							$sku ='';
							if (is_array($pdataArray) && array_key_exists("sku", $pdataArray)){
								$sku = str_replace(' ', '-', strtoupper(addslashes(trim((string) $pdataArray['sku']))));
								$pdataArray['sku'] = $sku = $this->db->checkCharLen('product.sku', $sku);
							}
							$pdataArray['last_updated'] = date('Y-m-d H:i:s');
							$pdataArray['product_type'] = $product_type = $this->db->checkCharLen('product.product_type', $product_type);
							
							$sqli = "accounts_id = $prod_cat_man AND product_publish = 1";
							if(array_key_exists("manufacturer_id", $pdataArray)){
								$manufacturer_id = $pdataArray['manufacturer_id'];
								$sqli .= " AND manufacturer_id = $manufacturer_id";
							}
							else{
								$sqli .= " AND manufacturer_id = 0";
							}
							if(array_key_exists("colour_name", $pdataArray)){
								$pdataArray['colour_name'] = $colour_name = $this->db->checkCharLen('product.colour_name', $pdataArray['colour_name']);
								$sqli .= " AND colour_name = '$colour_name'";
							}
							else{
								$sqli .= " AND colour_name = ''";
							}
							if(array_key_exists("storage", $pdataArray)){
								$pdataArray['storage'] = $storage = $this->db->checkCharLen('product.storage', $pdataArray['storage']);
								$sqli .= " AND storage = '$storage'";
							}
							else{
								$sqli .= " AND storage = ''";
							}
							if(array_key_exists("physical_condition_name", $pdataArray)){
								$pdataArray['physical_condition_name'] = $physical_condition_name = $this->db->checkCharLen('product.physical_condition_name', $pdataArray['physical_condition_name']);
								$sqli .= " AND physical_condition_name = '$physical_condition_name'";
							}
							else{
								$sqli .= " AND physical_condition_name = ''";
							}
							
							$oldproduct_id = 0;
							if ($product_type=='Live Stocks' && $item_number !='' && $sku ==''){
								$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name'", array());
								if($queryObj){
									$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
								}
							}
							else{
								$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name'", array());
								if($queryObj){
									//$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
									
									$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name"."1'", array());
									if($queryObj){
										//$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
										
										$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name"."2'", array());
										if($queryObj){
											//$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
											
											$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name"."3'", array());
											if($queryObj){
												//$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
											
												$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name"."4'", array());
												if($queryObj){
													//$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
											
													$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name"."5'", array());
													if($queryObj){
														//$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
											
														$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name"."6'", array());
														if($queryObj){
															//$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
											
															$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name"."7'", array());
															if($queryObj){
																//$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
											
																$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name"."8'", array());
																if($queryObj){
																	//$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
											
																	$queryObj = $this->db->query("SELECT product_id FROM product WHERE $sqli AND product_name = '$product_name"."9'", array());
																	if($queryObj){
																		$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
																	}
																	else{$pdataArray['product_name'] = $product_name.'9';}
																}
																else{$pdataArray['product_name'] = $product_name.'8';}
															}
															else{$pdataArray['product_name'] = $product_name.'7';}
														}
														else{$pdataArray['product_name'] = $product_name.'6';}
													}
													else{$pdataArray['product_name'] = $product_name.'5';}
												}
												else{$pdataArray['product_name'] = $product_name.'4';}
											}
											else{$pdataArray['product_name'] = $product_name.'3';}
										}
										else{$pdataArray['product_name'] = $product_name.'2';}
									}
									else{$pdataArray['product_name'] = $product_name.'1';}
								}
							}
							
							if($sku !='' && $oldproduct_id == 0){
										
								$queryObj = $this->db->query("SELECT product_id FROM product WHERE accounts_id = $prod_cat_man AND sku = '".addslashes($sku)."' AND product_publish = 1", array());
								if($queryObj){
									$oldproduct_id = $queryObj->fetch(PDO::FETCH_OBJ)->product_id;
								}
							}
							$productStr = '';
							if($oldproduct_id == 0){
								if (is_array($pdataArray) && !array_key_exists("manage_inventory_count", $pdataArray)){
									$pdataArray['manage_inventory_count'] = 1;
								}
								
								$pdataArray['created_on'] = date('Y-m-d H:i:s');
								$pdataArray['accounts_id'] = $prod_cat_man;
								$pdataArray['user_id'] = $user_id;
								$pdataArray['alert_message'] = '';								
								$pdataArray['custom_data'] = '';
								$product_id = $this->db->insert('product', $pdataArray);
								
								if($product_id){
									if(empty(trim((string) $sku)) || strlen($sku)<2){
										$this->db->update('product', array('sku'=>$product_id), $product_id);										
									}
									$insercount++;
								}
								else{
									$productStr .= 'Product Could not Insert'.json_encode($pdataArray);
								}
							}
							else{
								$product_id = $oldproduct_id;
								unset($pdataArray['product_type']);
								if(empty(trim((string) $sku)) || strlen($sku)<2){
									unset($pdataArray['sku']);
								}
								$this->db->update('product', $pdataArray, $oldproduct_id);
								$updatecount++;
							}
							//print_r($pdataArray);exit;
							
							if($product_id>0){
								$idataArray['product_id'] = $product_id;
								$idataArray['accounts_id'] = $instance_id;
								
								$oldinventory_id = 0;
								$queryObj = $this->db->query("SELECT inventory_id FROM inventory WHERE accounts_id = $instance_id AND product_id = $product_id", array());
								if($queryObj){
									$oldinventory_id = $queryObj->fetch(PDO::FETCH_OBJ)->inventory_id;
								}
								
								if($oldinventory_id === 0){
									$idataArray['minimum_price'] = 0.00;
									if(array_key_exists("current_inventory", $idataArray) && $pdataArray['manage_inventory_count']>0 && $idataArray['current_inventory']<=0){
										$idataArray['ave_cost'] = 0.00;
									}

									$inventory_id = $this->db->insert('inventory', $idataArray);
									if($inventory_id){
										$locationquery = $this->db->query("SELECT accounts_id FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man) AND status != 'SUSPENDED' AND accounts_id != $instance_id", array());						
										if($locationquery){
											while($oneUserRow = $locationquery->fetch(PDO::FETCH_OBJ)){
												$invUpdateData = $idataArray;
												$invUpdateData['current_inventory'] = 0;
												$invUpdateData['accounts_id'] = $oneUserRow->accounts_id;
												$this->db->insert('inventory', $invUpdateData);
											}
										}
									}
									else{
										$productStr .= 'Inventory: Could not Insert'.json_encode($idataArray);
									}
								}
								else{
									$inventory_id = $oldinventory_id;
									
									$invUpdateData = $idataArray;
									if(array_key_exists("current_inventory", $idataArray) && $idataArray['current_inventory'] !=0){
										unset($invUpdateData['current_inventory']);
										unset($invUpdateData['ave_cost']);
									}
									if(!empty($invUpdateData)){
										$this->db->update('inventory', $invUpdateData, $oldinventory_id);
									}
								}
								
								//echo $inventory_id; exit;
								//$this->db->writeIntoLog("Line# 1342, $product_type=='Standard' && ".is_array($idataArray).' && '.array_key_exists("current_inventory", $idataArray));
								
								if ($product_type=='Standard' && is_array($idataArray) && array_key_exists("current_inventory", $idataArray)){
									$current_inventory = $idataArray['current_inventory'];
									if($current_inventory == ''){$current_inventory = 0;}

									//$this->db->writeIntoLog("Line# 1348, current_inventory: $current_inventory, po_id: ".$this->po_id);
								
									if($current_inventory !=0){
										if($this->po_id==0){
											$oldsuppliers_id = 0;
											$queryObj = $this->db->query("SELECT suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company = 'Imported Data'", array());
											if($queryObj){
												$oldsuppliers_id = $queryObj->fetch(PDO::FETCH_OBJ)->suppliers_id;
											}
											
											if($oldsuppliers_id == 0){
												$company = $this->db->checkCharLen('suppliers.company', 'Imported Data'); 
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
											else{
												$suppliers_id = $oldsuppliers_id;
											}
											
											$po_number = 1;
											$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $instance_id ORDER BY po_number DESC LIMIT 0, 1", array());
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
											$poData['date_expected'] = date('Y-m-d');
											$poData['return_po'] = 0;
											$poData['status'] = 'Open';
											$poData['accounts_id'] = $instance_id;
											$poData['user_id'] = $user_id;
											$poData['tax_is_percent'] = 0;
											$poData['taxes'] = 0.000;
											$poData['shipping'] = 0.00;
											$poData['suppliers_invoice_no'] = '';
											$poData['invoice_date'] = date('Y-m-d');
											$poData['date_paid'] = date('Y-m-d');
											$poData['transfer'] = 0;
											$this->po_id = $this->db->insert('po', $poData);
								
											//$this->db->writeIntoLog("Line# 1414, po_id: ".$this->po_id);
										}
										
										if($this->po_id>0){
											$po_id = $this->po_id;
											$ave_cost = 0.00;
											if (is_array($idataArray) && array_key_exists("ave_cost", $idataArray)){
												$ave_cost = preg_replace("/[^0-9.]/", "", $idataArray['ave_cost']);
												$ave_cost = round($ave_cost,2);
												if($ave_cost<0){$ave_cost = 0.00;}
											}
											$item_type = 'product';
											$item_type = $this->db->checkCharLen('po_items.item_type', $item_type);
											$poiData =array('created_on'=>date('Y-m-d H:i:s'),
															'user_id'=>$user_id,
															'po_id'=>$po_id,
															'product_id'=>$product_id,
															'item_type'=>$item_type,
															'ordered_qty'=>$current_inventory,
															'received_qty'=>$current_inventory,
															'cost'=>round($ave_cost,2));											
											$sql1 = "SELECT * FROM po_items WHERE po_id = $po_id AND product_id = $product_id ORDER BY po_items_id ASC LIMIT 0,1";
											$query1 = $this->db->querypagination($sql1, array());
											if(!$query1){
												$po_items_id = $this->db->insert('po_items', $poiData);
												//$this->db->writeIntoLog("Line# 1437, po_items_id: ".$po_items_id);
											}
											else{
												foreach($query1 as $po_itemsrow){
													$po_items_id = $po_itemsrow['po_items_id'];
													$ordered_qty = $po_itemsrow['ordered_qty']+$poiData['ordered_qty'];
													$received_qty = $po_itemsrow['received_qty']+$poiData['received_qty'];
													
													$this->db->update('po_items', array('ordered_qty'=>$ordered_qty, 'received_qty'=>$received_qty), $po_items_id);
												}
											}
											//$this->db->writeIntoLog("Line# 1450, product_id: $product_id == oldproduct_id: $oldproduct_id");
											if($product_id == $oldproduct_id){
												$productObj = $this->db->query("SELECT * FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man AND product_publish = 1", array());
												if($productObj){
													$pOneRow = $productObj->fetch(PDO::FETCH_OBJ);
													
													$inventoryObj = $this->db->query("SELECT * FROM inventory WHERE product_id = $product_id AND accounts_id = $instance_id", array());
													if($inventoryObj){
														$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
														
														$manage_inventory_count = $pOneRow->manage_inventory_count;
														//==================Undo product condition===============//
														$undo_inventory = $inventoryrow->current_inventory;
														$undo_ave_cost = $inventoryrow->ave_cost;
														$undoproducttotalcost = round($undo_inventory*$undo_ave_cost,2);
														
														//=============Undo product condition =================//
														if($undo_inventory<0){$undo_inventory = 0;}
														$new_current_inventory = $undo_inventory+$current_inventory;
														$new_ave_cost = $undo_ave_cost;
														if($new_current_inventory !=0){
															$new_ave_cost = round(($undoproducttotalcost+round($ave_cost*$current_inventory,2))/$new_current_inventory,2);
															if($new_ave_cost<0){$new_ave_cost = 0.00;}
														}
														$this->db->update('inventory', array('current_inventory'=>$new_current_inventory, 'ave_cost' => round($new_ave_cost,2)), $inventoryrow->inventory_id);
														//$this->db->writeIntoLog("Line# 1475, current_inventory: $new_current_inventory");
													}
												}
											}										
										}
									}
								}
								
								if ($product_type=='Live Stocks' && $item_number !=''){
									$olditem_id = 0;
									$queryObj = $this->db->query("SELECT item_id FROM item WHERE accounts_id = $instance_id AND item_number = '$item_number'", array());
									if($queryObj){
										$olditem_id = $queryObj->fetch(PDO::FETCH_OBJ)->item_id;
									}
									
									if (!isset($ave_cost) && is_array($idataArray) && array_key_exists("ave_cost", $idataArray)){
										$ave_cost = round($idataArray['ave_cost'],2);
									}
									else{
										$ave_cost = round($ave_cost,2);
									}
									if($ave_cost<0){$ave_cost = 0.00;}
									
									if($olditem_id==0){
										$po_id = $this->po_id;
										$oldcost = $ave_cost;
										if($po_id>0){
											$queryObj = $this->db->query("SELECT cost FROM po_items WHERE po_id = $po_id AND product_id = $product_id", array());
											if($queryObj){
												$oldcost = round($queryObj->fetch(PDO::FETCH_OBJ)->cost,2);
											}
										}
										
										if($po_id==0 || $ave_cost != $oldcost){
											$oldsuppliers_id = 0;
											$queryObj = $this->db->query("SELECT suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company = 'Imported Data'", array());
											if($queryObj){
												$oldsuppliers_id = $queryObj->fetch(PDO::FETCH_OBJ)->suppliers_id;
											}
											
											if($oldsuppliers_id == 0){
												$company = $this->db->checkCharLen('suppliers.company', 'Imported Data'); 
												
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
											else{
												$suppliers_id = $oldsuppliers_id;
											}
											
											$po_number = 1;
											$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $instance_id ORDER BY po_number DESC LIMIT 0, 1", array());
											if($poObj){
												$po_number = $poObj[0]['po_number']+1;
											}
											
											$poData = array();
											$poData['po_datetime'] = date('Y-m-d H:i:s');
											$poData['last_updated'] = date('Y-m-d H:i:s');
											$poData['po_number'] = $po_number;
											$lot_ref_no = $this->db->checkCharLen('po.lot_ref_no', '');
											$poData['lot_ref_no'] = $lot_ref_no;
											$poData['paid_by'] = '';
											$poData['supplier_id'] = intval($suppliers_id);
											$poData['date_expected'] = date('Y-m-d');
											$poData['return_po'] = 0;
											$status = $this->db->checkCharLen('po.status', 'Open');
											$poData['status'] = $status;
											$poData['accounts_id'] = $instance_id;
											$poData['user_id'] = $user_id;
											$poData['tax_is_percent'] = 0;
											$poData['taxes'] = 0.000;
											$poData['shipping'] = 0.00;
											$suppliers_invoice_no = $this->db->checkCharLen('po.suppliers_invoice_no', '');
											$poData['suppliers_invoice_no'] = $suppliers_invoice_no;
											$poData['invoice_date'] = date('Y-m-d');
											$poData['date_paid'] = date('Y-m-d');
											$poData['transfer'] = 0;
											
											$po_id = $this->db->insert('po', $poData);
											$this->po_id = $po_id;
										}
											
										if($po_id>0){
											$oldpo_items_id = 0;
											$queryObj = $this->db->query("SELECT po_items_id FROM po_items WHERE po_id = $po_id AND product_id = $product_id", array());
											if($queryObj){
												$oldpo_items_id = $queryObj->fetch(PDO::FETCH_OBJ)->po_items_id;
											}
											if($oldpo_items_id==0){
												
												$item_type = 'livestocks';
												$item_type = $this->db->checkCharLen('po_items.item_type', $item_type);
												$poiData =array('created_on'=>date('Y-m-d H:i:s'),
																'user_id'=>$user_id,
																'po_id'=>$po_id,
																'product_id'=>$product_id,
																'item_type'=>$item_type,
																'ordered_qty'=>0,
																'received_qty'=>0,
																'cost'=>$ave_cost);
												$po_items_id = $this->db->insert('po_items', $poiData);											
											}
											else{
												$po_items_id = $oldpo_items_id;
											}
											
											if($po_items_id>0){
												$queryObj = $this->db->query("SELECT received_qty FROM po_items WHERE po_items_id = $po_items_id", array());
												if($queryObj){
													$pototalreceived_qty = $queryObj->fetch(PDO::FETCH_OBJ)->received_qty+1;
													$itemData = array('created_on' => date('Y-m-d H:i:s'),
																	'last_updated' => date('Y-m-d H:i:s'),
																	'accounts_id' => $instance_id,		
																	'user_id' => $user_id,
																	'product_id' => $product_id,
																	'item_number' => $item_number,
																	'carrier_name' => "",
																	'in_inventory' => 1,
																	'is_pos'=>0,
																	'custom_data'=>''
																	);
													$item_id = $this->db->insert('item', $itemData);
													if($item_id){
														$poCartItemData = array('po_items_id' => $po_items_id,
																				'item_id' => $item_id,
																				'return_po_items_id' => 0);
														$this->db->insert('po_cart_item', $poCartItemData);
														
														$insercount++;
														$updatepo_items = $this->db->update('po_items', array('ordered_qty'=>$pototalreceived_qty, 'received_qty'=>$pototalreceived_qty), $po_items_id);
														if($updatepo_items){								
															$returnstr = 1;
														}						
													}
												}
											}
										}
									}
								}
							}
						
							if($productStr !='')
							    $errorDebug[] = $productStr;
						}
					}					
				}
				
				
				if(!empty($errorDebug))
				    $this->db->writeIntoLog(implode(' || ', $errorDebug));

				if($newCondi>0){
					$variables_id = 0;
					$value = '';
					$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = :accounts_id AND name = 'product_setup'", array('accounts_id'=>$instance_id),1);
					if($varObj){
						$variablesData = $varObj->fetch(PDO::FETCH_OBJ);					
						$variables_id = $variablesData->variables_id;
						$value = $variablesData->value;
					}
					if(!empty($value)){
						$value = unserialize($value);
					}
					else{
						$Common = new Common($this->db);
						$value = $Common->variablesData('product_setup', $instance_id);
					}
					if(array_key_exists('conditions', $value)){
						$value['conditions'] = implode('||', $conditionsarray);
					}
					
					$value = serialize($value);
					
					$data = array('accounts_id'=>$instance_id,
								'name'=>$this->db->checkCharLen('variables.name', 'product_setup'),
								'value'=>$value,
								'last_updated'=> date('Y-m-d H:i:s'));
					if($variables_id==0){
						$this->db->insert('variables', $data);
					}
					else{
						$this->db->update('variables', $data, $variables_id);
					}
				}
				
				$returnStr = $insercount.' data has been inserted AND '.$updatecount.'  data has been duplicate.<br>';
			}		
		
			$timezone = $_SESSION["timezone"]??'America/New_York';
			date_default_timezone_set($timezone);

		}
		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURL?msg=$returnStr\" />";
	}
	
	public function popup_message(){}
	
	public function AJ_popup_message_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$popup_message = '';
		$popup_message_update = 0;
		$usersObj = $this->db->query("SELECT popup_message FROM user WHERE user_id = 6", array());
		if($usersObj){
			$popup_message = $usersObj->fetch(PDO::FETCH_OBJ)->popup_message;			
			$popup_message = str_replace('"../common', '"../../common',$popup_message);
		}	
		$jsonResponse['popup_message'] = trim(stripslashes($popup_message));
		return json_encode($jsonResponse);
	}
	
	public function AJsave_userPopupMessage(){
		$returnmsg = '';
		
		if(!isset($_SESSION["prod_cat_man"])){
			$returnmsg = 'session_ended';
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$popup_message = $POST['popup_message'];
			$alluserupdate = $POST['alluserupdate'];
			
			$update = false;
			$sqlquery = "SELECT user_id FROM user";
			if($alluserupdate==0){
				$sqlquery .= " WHERE user_id = 6";
			}
			$sqlquery .= " ORDER BY user_id ASC";
			$queryrows = $this->db->query($sqlquery, array());	
			if($queryrows){
				while($onerow = $queryrows->fetch(PDO::FETCH_OBJ)){
					$user_id = $onerow->user_id;
					$update = $this->db->update('user', array('popup_message'=>trim(addslashes(stripslashes($popup_message)))), $user_id);
				}
			}
				
			if($update){
				if($alluserupdate==0){
					$returnmsg = 'update-success';
				}
				else{
					$returnmsg = 'allupdate-success';
				}
			}
			else{
				$returnmsg = 'There is no changes made.';
			}			
		}
		return json_encode(array('login'=>'', 'returnmsg'=>$returnmsg));
	}
	
	public function login_message(){}
	
	public function AJ_login_message_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$login_message = '';
		$login_message_update = 0;
		$usersObj = $this->db->query("SELECT login_message FROM user WHERE user_id = 6", array());
		if($usersObj){
			$login_message = $usersObj->fetch(PDO::FETCH_OBJ)->login_message;			
			$login_message = str_replace('"../common', '"../../common',$login_message);
		}
		$jsonResponse['login_message'] = trim(stripslashes($login_message));
		return json_encode($jsonResponse);
	}

	//===========================Language Module=======================//
	public function languages(){}

	public function languagesVarWrite(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$returnmsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;

		$languageNames = array();
		$languageNames['english'] = "English";
		$languageNames['spanish'] = "Spanish";
		$languageNames['french'] = "French";
		$languageNames['greek'] = "Greek";
		$languageNames['german'] = "German";
		$languageNames['italian'] = "Italian";
		$languageNames['dutch'] = "Dutch";
		$languageNames['arabic'] = "Arabic";
		$languageNames['chinese'] = "Chinese";
		$languageNames['hindi'] = "Hindi";
		$languageNames['bengali'] = "Bengali";
		$languageNames['portuguese'] = "Portuguese";
		$languageNames['russian'] = "Russian";
		$languageNames['japanese'] = "Japanese";
		$languageNames['korean'] = "Korean";
		$languageNames['turkey'] = "Turkey";
		$languageNames['finnish'] = "Finnish";
		
		foreach($languageNames as $oneLanguage=>$langFilename){
			if($oneLanguage !=''){
				//==================For PHP file================//
				$variablesRows = '<?php
class '.$langFilename.'{
	public function index($index){
		$languageData = array(';
				if($langFilename !='English'){
					$sql1 = "SELECT english, $oneLanguage FROM languages WHERE php_js IN (1,2) ORDER BY english ASC";
					$query = $this->db->querypagination($sql1, array());						
					if($query){
						foreach($query as $onerow){
							$oneLanguageVal = trim((string) $onerow[$oneLanguage]);						
							$variablesRows .= '
			\''.trim((string) $onerow['english']).'\'=>stripslashes(\''.addslashes($oneLanguageVal).'\'),';
									
						}
					}
				}
					
				$variablesRows .= '
		);
		if(array_key_exists($index, $languageData)){
			return $languageData[$index];
		}
		return false;
	}
}
?>';
				$filename = "apps/languages/$langFilename".".php";
				if (file_exists($filename)) {}
				else{fopen($filename, 'a');}
				if(!empty($variablesRows)){						
					file_put_contents($filename, $variablesRows);
				}					
						
				//==================For js file================//
				$variablesRows = 'var languageData = {';
				if($langFilename !='English'){
					$sql1 = "SELECT english, $oneLanguage FROM languages WHERE php_js IN (2,3) ORDER BY english ASC";
					$query = $this->db->querypagination($sql1, array());						
					if($query){
						foreach($query as $onerow){
							$oneLanguageVal = addslashes(trim((string) stripslashes($onerow[$oneLanguage])));						
							$variablesRows .= '
	\''.trim((string) $onerow['english']).'\': stripslashes(\''.addslashes(preg_replace("/\r\n|\r|\n/", '<br>', $oneLanguageVal)).'\'),';
						}
					}
				}

				$variablesRows .= '
}';
				$filename = "./assets/js-".swVersion."/languages/".$langFilename.".js";
				if (file_exists($filename)) {}
				else{fopen($filename, 'a');}
				if(!empty($variablesRows)){						
					file_put_contents($filename, $variablesRows);
				}				
				$returnmsg = 'Re-written successfully.';
			}
		}

		$jsonResponse['returnmsg'] = $returnmsg;
		return json_encode($jsonResponse);	
	}
	
	public function AJgetInfoLang(){
		$returnArray = array();
		$returnArray['languages_id'] = 0;
		if(!isset($_SESSION["prod_cat_man"])){
			$returnArray['login'] = 'session_ended';			
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$returnArray['login'] = '';
			$languages_id = $POST['languages_id'];
			$sql = "SELECT * FROM languages WHERE languages_id = :languages_id ORDER BY languages_id ASC LIMIT 0, 1";			
			$query = $this->db->querypagination($sql, array('languages_id'=>$languages_id),1);
			if($query){
				foreach($query as $oneRow){
					$returnArray['languages_id'] = $oneRow['languages_id'];
					$returnArray['php_js'] = $oneRow['php_js'];
					$returnArray['english'] = stripslashes($oneRow['english']);
					$returnArray['spanish'] = stripslashes($oneRow['spanish']);
					$returnArray['french'] = stripslashes($oneRow['french']);
					$returnArray['greek'] = stripslashes($oneRow['greek']);
					$returnArray['german'] = stripslashes($oneRow['german']);
					$returnArray['italian'] = stripslashes($oneRow['italian']);
					$returnArray['dutch'] = stripslashes($oneRow['dutch']);
					$returnArray['arabic'] = stripslashes($oneRow['arabic']);
					$returnArray['chinese'] = stripslashes($oneRow['chinese']);
					$returnArray['hindi'] = stripslashes($oneRow['hindi']);
					$returnArray['bengali'] = stripslashes($oneRow['bengali']);
					$returnArray['portuguese'] = stripslashes($oneRow['portuguese']);
					$returnArray['russian'] = stripslashes($oneRow['russian']);
					$returnArray['japanese'] = stripslashes($oneRow['japanese']);
					$returnArray['korean'] = stripslashes($oneRow['korean']);
					$returnArray['turkey'] = stripslashes($oneRow['turkey']);
					$returnArray['finnish'] = stripslashes($oneRow['finnish']);
				}
			}
		}
		return json_encode($returnArray);
	}
	
	public function AJgetTranslate(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$english = addslashes(trim((string) $POST['english']));

		$translateData = array();
		$translateData['login'] = '';
		$translateData['spanish'] = '';
		$translateData['french'] = '';
		$translateData['greek'] = '';
		$translateData['german'] = '';
		$translateData['italian'] = '';
		$translateData['dutch'] = '';
		$translateData['arabic'] = '';
		$translateData['chinese'] = '';
		$translateData['hindi'] = '';
		$translateData['bengali'] = '';
		$translateData['portuguese'] = '';
		$translateData['russian'] = '';
		$translateData['japanese'] = '';
		$translateData['korean'] = '';
		$translateData['turkey'] = '';
		$translateData['finnish'] = '';

		if($english !=''){
			$translateData['spanish'] = $this->transEngToOthers('es', $english);
			$translateData['french'] = $this->transEngToOthers('fr', $english);
			$translateData['greek'] = $this->transEngToOthers('el', $english);
			$translateData['german'] = $this->transEngToOthers('de', $english);
			$translateData['italian'] = $this->transEngToOthers('it', $english);
			$translateData['dutch'] = $this->transEngToOthers('nl', $english);
			$translateData['arabic'] = $this->transEngToOthers('ar', $english);
			$translateData['chinese'] = $this->transEngToOthers('zh-CN', $english);
			$translateData['hindi'] = $this->transEngToOthers('hi', $english);
			$translateData['bengali'] = $this->transEngToOthers('bn', $english);
			$translateData['portuguese'] = $this->transEngToOthers('pt', $english);
			$translateData['russian'] = $this->transEngToOthers('ru', $english);
			$translateData['japanese'] = $this->transEngToOthers('ja', $english);
			$translateData['korean'] = $this->transEngToOthers('ko', $english);
			$translateData['turkey'] = $this->transEngToOthers('tr', $english);
			$translateData['finnish'] = $this->transEngToOthers('fi', $english);
		}
		return json_encode($translateData);
	}
	
	public function AJsaveLanguage(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$savemsg = 'error';
		$message = '';
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$languages_id = $POST['languages_id'];
		$php_js = $POST['php_js'];
		$english = addslashes(trim((string) $POST['english']));
		
		$extrastr = "";
		if($languages_id>0){$extrastr = " AND languages_id != $languages_id";}
					
		$languagescount = 0;
		$queryObj = $this->db->query("SELECT COUNT(languages_id) AS totalrows FROM languages WHERE english = :english $extrastr", array('english'=>$english));
		if($queryObj){
			$languagescount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}
		if($languagescount>0){				
			$message = 'This "'.$english.'" is already exist! Please try again with different '.$english;
		}
		else{
			
			$conditionarray = array('php_js' => $php_js,
									'english' => $english,
									'spanish' => trim((string) $POST['spanish']),
									'french' => trim((string) $POST['french']),
									'greek' => trim((string) $POST['greek']),
									'german' => trim((string) $POST['german']),
									'italian' => trim((string) $POST['italian']),
									'dutch' => trim((string) $POST['dutch']),
									'arabic' => trim((string) $POST['arabic']),
									'chinese' => trim((string) $POST['chinese']),
									'hindi' => trim((string) $POST['hindi']),
									'bengali' => trim((string) $POST['bengali']),
									'portuguese' => trim((string) $POST['portuguese']),
									'russian' => trim((string) $POST['russian']),
									'japanese' => trim((string) $POST['japanese']),
									'korean' => trim((string) $POST['korean']),
									'turkey' => trim((string) $POST['turkey']),
									'finnish' => trim((string) $POST['finnish'])
									);
			if($languages_id==0){						
				$languages_id = $this->db->insert('languages', $conditionarray);
				if($languages_id){
					$savemsg = 'Add';
					$message = 'Added Successfully';
				}
				else{
					$message = 'Error occured while adding new "'.$english.'"! Please try again.';
				}
			}
			else{
				$updatedata = $this->db->update('languages', $conditionarray, $languages_id);
				if($updatedata){									
					$savemsg = 'Update';
					$message = 'Updated Successfully';
				}
				else{
					$message = 'Error occured while changing "'.$english.'"! Please try again.';
				}
			}
		}
		$jsonResponse['savemsg'] = $savemsg;
		$jsonResponse['message'] = $message;
		
		return json_encode($jsonResponse);
	}
		 
	public function transEngToOthers($to, $text) {
		$url = "http://www.googleapis.com/language/translate/v2?key=AIzaSyAhY72zr8bTmB4yp-73lZb5L3MjDhTJCiQ&q=".rawurlencode($text)."&source=en&target=".$to;
		$ch = curl_init();
      	curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	$response = curl_exec($ch);
      	curl_close($ch);
 
		$json = (array) json_decode($response);
		if(is_array($json) && array_key_exists('data', $json)){
			$json1 = (array) $json['data'];
			if(is_array($json1) && array_key_exists('translations', $json1)){				
				$json2 = (array) $json1['translations'][0];
				if(is_array($json2) && array_key_exists('translatedText', $json2)){
					return $json2['translatedText'];
				}
			}
		}
		return '';
	}
	
	public function AJ_languages_MoreInfo(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		$languages_id = $POST['languages_id']??0;
		$php_js = 1;
		$english = $spanish = $french = $greek = $german = $italian = $dutch = $arabic = $chinese = $hindi = $bengali = $portuguese = $russian = $japanese = $korean = $turkey = $finnish = '';
		if($languages_id !='' && $languages_id>0){
			$languagesObj = $this->db->query("SELECT * FROM languages WHERE languages_id = :languages_id", array('languages_id'=>$languages_id),1);
			if($languagesObj){
				$oneLanguagesRow = $languagesObj->fetch(PDO::FETCH_OBJ);
				
				$languages_id = $oneLanguagesRow->languages_id;
				$php_js = $oneLanguagesRow->php_js;
				$english = stripslashes($oneLanguagesRow->english);
				$spanish = stripslashes($oneLanguagesRow->spanish);
				$french = stripslashes($oneLanguagesRow->french);
				$greek = stripslashes($oneLanguagesRow->greek);
				$german = stripslashes($oneLanguagesRow->german);
				$italian = stripslashes($oneLanguagesRow->italian);
				$dutch = stripslashes($oneLanguagesRow->dutch);
				$arabic = stripslashes($oneLanguagesRow->arabic);
				$chinese = stripslashes($oneLanguagesRow->chinese);
				$hindi = stripslashes($oneLanguagesRow->hindi);
				$bengali = stripslashes($oneLanguagesRow->bengali);
				$portuguese = stripslashes($oneLanguagesRow->portuguese);
				$russian = stripslashes($oneLanguagesRow->russian);
				$japanese = stripslashes($oneLanguagesRow->japanese);
				$korean = stripslashes($oneLanguagesRow->korean);
				$turkey = stripslashes($oneLanguagesRow->turkey);
				$finnish = stripslashes($oneLanguagesRow->finnish);
			}
		}
		$jsonResponse['languages_id'] = $languages_id;
		$jsonResponse['php_js'] = $php_js;
		$jsonResponse['english'] = $english;
		$jsonResponse['spanish'] = $spanish;
		$jsonResponse['french'] = $french;
		$jsonResponse['greek'] = $greek;
		$jsonResponse['german'] = $german;
		$jsonResponse['italian'] = $italian;
		$jsonResponse['dutch'] = $dutch;
		$jsonResponse['arabic'] = $arabic;
		$jsonResponse['chinese'] = $chinese;
		$jsonResponse['hindi'] = $hindi;
		$jsonResponse['bengali'] = $bengali;
		$jsonResponse['portuguese'] = $portuguese;
		$jsonResponse['russian'] = $russian;
		$jsonResponse['japanese'] = $japanese;
		$jsonResponse['korean'] = $korean;
		$jsonResponse['turkey'] = $turkey;
		$jsonResponse['finnish'] = $finnish;
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_languages(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sorder_by = $this->order_by;
		$sphp_js = $this->php_js;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "languages";
		$_SESSION["list_filters"] = array('sorder_by'=>$sorder_by, 'sphp_js'=>$sphp_js, 'keyword_search'=>$keyword_search);
		
		$totalRows = 0;
		$filterData = $bindData = array();
		if(in_array($sphp_js, array(1,2,3))){
			$filterData[] = "php_js = :php_js";
			$bindData['php_js'] = $sphp_js;
		}
		elseif($sphp_js==4){
			$filterData[] = "php_js = :php_js";
			$bindData['php_js'] = 0;
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterData[] = "english LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql = "FROM languages";
		if(!empty($filterData)){
			$filterSql .= " WHERE ".implode(' AND ', $filterData);
		}
		$strextra ="SELECT COUNT(languages_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
	
		$this->totalRows = $totalRows;
	}
	
    private function loadTableRows_languages(){
		
		$limit = $_SESSION["limit"]??'auto';
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$ssorting_type = $this->order_by;
		$sphp_js = $this->php_js;
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'english ASC', 
								1=>'english DESC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->order_by = $ssorting_type;
		}
								
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
	
		$filterData = $bindData = array();
		if(in_array($sphp_js, array(1,2,3))){
			$filterData[] = "php_js = :php_js";
			$bindData['php_js'] = $sphp_js;
		}
		elseif($sphp_js==4){
			$filterData[] = "php_js = :php_js";
			$bindData['php_js'] = 0;
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterData[] = "english LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql = "FROM languages";
		if(!empty($filterData)){
			$filterSql .= " WHERE ".implode(' AND ', $filterData);
		}
		$sqlquery = "SELECT * $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		$query = $this->db->query($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			while($onerow = $query->fetch(PDO::FETCH_OBJ)){
			
				$languages_id = $onerow->languages_id;
				$php_js = stripslashes($onerow->php_js);
				$php_jsstr = 'Not used';
				if($php_js==1){$php_jsstr = 'PHP';}
				elseif($php_js==2){$php_jsstr = 'PHP+JS';}
				elseif($php_js==3){$php_jsstr = 'JS';}
				$english = stripslashes($onerow->english);
				if($english ==''){$english = '&nbsp;';}
				
				$tabledata[] = array($languages_id, $php_jsstr, $english);
			}
		}
		return $tabledata;
    }
	
	public function AJgetPage_languages($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sorder_by = $POST['sorder_by']??0;
		$sphp_js = $POST['sphp_js']??0;
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->order_by = $sorder_by;
		$this->php_js = $sphp_js;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){		
			$this->filterAndOptions_languages();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_languages();
		
		return json_encode($jsonResponse);
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

	public function checkLanguage(){
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
		$filePath = "./apps/";
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

						$translateData['spanish'] = $this->transEngToOthers('es', $english);
						$translateData['french'] = $this->transEngToOthers('fr', $english);
						$translateData['greek'] = $this->transEngToOthers('el', $english);
						$translateData['german'] = $this->transEngToOthers('de', $english);
						$translateData['italian'] = $this->transEngToOthers('it', $english);
						$translateData['dutch'] = $this->transEngToOthers('nl', $english);
						$translateData['arabic'] = $this->transEngToOthers('ar', $english);
						$translateData['chinese'] = $this->transEngToOthers('zh-CN', $english);
						$translateData['hindi'] = $this->transEngToOthers('hi', $english);
						$translateData['bengali'] = $this->transEngToOthers('bn', $english);
						$translateData['portuguese'] = $this->transEngToOthers('pt', $english);
						$translateData['russian'] = $this->transEngToOthers('ru', $english);
						$translateData['japanese'] = $this->transEngToOthers('ja', $english);
						$translateData['korean'] = $this->transEngToOthers('ko', $english);
						$translateData['turkey'] = $this->transEngToOthers('tr', $english);
						$translateData['finnish'] = $this->transEngToOthers('fi', $english);
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
			$this->languagesVarWrite();
		//}
		if(empty($returnStr)){
			$returnStr = 'There is no data missing.';
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
		
	}
	
	public function checkLanguageOld(){
		$returnStr = '';
		$phpMissingVariable = $jsMissingVariable = array();
		//==========Check all variables:: languare data========//
		$addiCond = '';//' accounts_id = 6 AND';
		$queryObj = $this->db->query("SELECT variables_id, accounts_id, value FROM variables WHERE $addiCond name='language'", array());
		if($queryObj){
			$languageJSVar = array();
			$Common = new Common($this->db);
			while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
				$variables_id = $oneRow->variables_id;
				$accounts_id = $oneRow->accounts_id;
				$vData = $Common->variablesData('account_setup', $accounts_id);
				$language = 'English';
				if(array_key_exists('language', $vData)){
					$language = $vData['language'];
				}
				
				$value = $oneRow->value;
				$newLang = $missingLang = array();
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
									$sql = "SELECT english, php_js FROM languages WHERE english = '$varName' ORDER BY languages_id ASC";
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
									}
									else{
										if(in_array($php_js, array(1,2))){
											$phpMissingVariable[] = $varName.':: '.$selLang.", AccountID: $accounts_id, Language: $language";
										}
										if(in_array($php_js, array(2,3))){
											$jsMissingVariable[] = $varName.':: '.$selLang.", AccountID: $accounts_id, Language: $language";
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
				}
				else{
					$this->db->delete('variables', 'variables_id', $variables_id);
				}
			}
			
			if(!empty($phpMissingVariable)){
				$returnStr .= '<br>//====================PHP Variable missing List:=====================//<br>';
				$l=0;
				foreach($phpMissingVariable as $oneVariable){
					$l++;
					$returnStr .= "$l. $oneVariable<br>";
					$this->db->writeIntoLog("PHP Translate issue: Variables Table - $oneVariable");
				}
			}
	
			if(!empty($jsMissingVariable)){
				$returnStr .= '<br>//====================JS Variable missing List:=====================//<br>';
				$l=0;
				foreach($jsMissingVariable as $oneVariable){
					$l++;
					$returnStr .= "$l. $oneVariable<br>";
					$this->db->writeIntoLog("JS Translate issue: Variables Table - $oneVariable");
				}
			}
		}

		if(empty($returnStr)){
			$returnStr = 'There is no data missing.';
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	//===========================Language Module End=======================//

	public function AJremove_Accounts($accounts_id=0, $cronY=''){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnmsg = '';
		$dtcount = 0;
		if($cronY !='Yes'){
			if(!isset($_SESSION["prod_cat_man"])){
				$returnmsg = 'session_ended';
			}
			else{
				$accounts_id = intval($POST['accounts_id']??0);
				$deluserpassword = $POST['deluserpassword']??'';
				if($accounts_id==0 || $deluserpassword !='REMOVE'){
					$returnmsg .= '<p>Wrong password! Enter correct password.</p>';
				}
			}
		}
		
		if(empty($returnmsg) && $accounts_id>0){
			$prod_cat_man = $accounts_id;
			$location_of = 0;
			$queryObj = $this->db->query("SELECT location_of FROM accounts WHERE accounts_id = :accounts_id", array('accounts_id'=>$accounts_id),1);
			if($queryObj){
				$location_of = $queryObj->fetch(PDO::FETCH_OBJ)->location_of;						
			}
			
			$locationcount = 0;
			if($location_of==0){
				$queryObj = $this->db->query("SELECT COUNT(accounts_id) AS totalrows FROM accounts WHERE location_of = $accounts_id AND status != 'SUSPENDED'", array());
				if($queryObj){
					$locationcount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
			}
			else{
				$prod_cat_man = $location_of;
			}
			if($locationcount>0){
				$returnmsg .= '<p>There is '.$locationcount.' location under this instance. Please remove location first then try to remove this instance accounts.</p>';
			}
			else{
				$folderPath = './assets/accounts/a_'.$accounts_id;
				if(is_dir($folderPath)){
					$files = glob($folderPath.'/*'); // get all file names
					foreach($files as $file){ // iterate files
					  if(is_file($file))
						unlink($file); // delete file
					}
					
					rmdir($folderPath);
				}
				
				$tableNameArray = array('activity_feed', 'appointments', 'commissions', 'custom_fields', 'digital_signature', 
										'end_of_day', 'expenses', 'forms_data', 'instance_home', 'inventory', 'item', 
										'notes', 'our_invoices', 'our_notes', 'petty_cash', 'po', 'pos', 'product_prices', 'repairs', 
										'taxes', 'time_clock', 'track_edits', 'user', 'variables');
				if($accounts_id==$prod_cat_man){
					$tableNameArray[] = 'brand_model';
					$tableNameArray[] = 'category';
					$tableNameArray[] = 'customers';
					$tableNameArray[] = 'customer_type';
					$tableNameArray[] = 'expense_type';
					$tableNameArray[] = 'forms';
					$tableNameArray[] = 'manufacturer';
					$tableNameArray[] = 'product';
					$tableNameArray[] = 'properties';
					$tableNameArray[] = 'repair_problems';
					$tableNameArray[] = 'suppliers';
					$tableNameArray[] = 'vendors';
				}
				
				$posReltableArray = array('pos_cart', 'pos_payment');
				
				foreach($tableNameArray as $tablename){
					$tableidname = $tablename.'_id';
					$sqlquery = "SELECT $tableidname FROM $tablename WHERE accounts_id = $accounts_id ORDER BY $tableidname ASC";
					$query = $this->db->query($sqlquery, array());						
					if($query){
						
						while($onerow = $query->fetch(PDO::FETCH_OBJ)){
							$tableidvalue = $onerow->$tableidname;
							
							if($tablename=='pos'){
								foreach($posReltableArray as $possubtablename){
									$possubtableidname = $possubtablename.'_id';
									$possubsql="SELECT $possubtableidname FROM $possubtablename WHERE pos_id = $tableidvalue ORDER BY $possubtableidname ASC";
									$possubquery = $this->db->query($possubsql, array());						
									if($possubquery){
										while($possubonerow = $possubquery->fetch(PDO::FETCH_OBJ)){
											
											$possubtableidvalue = $possubonerow->$possubtableidname;
											if($possubtablename=='pos_cart'){
												
												$poscartsql="SELECT pos_cart_item_id FROM pos_cart_item WHERE pos_cart_id = $possubtableidvalue ORDER BY pos_cart_item_id ASC";
												$poscartquery = $this->db->query($poscartsql, array());						
												if($poscartquery){
													while($poscartonerow = $poscartquery->fetch(PDO::FETCH_OBJ)){
														$pos_cart_item_id = $poscartonerow->pos_cart_item_id;
														
														$dtrow = $this->db->delete('pos_cart_item', 'pos_cart_item_id', $pos_cart_item_id);
														if($dtrow){
															$dtcount++;
														}
													}
												}
												
												$poscartsql="SELECT serial_number_id FROM serial_number WHERE pos_cart_id = $possubtableidvalue ORDER BY serial_number_id ASC";
												$poscartquery = $this->db->query($poscartsql, array());						
												if($poscartquery){
													while($poscartonerow = $poscartquery->fetch(PDO::FETCH_OBJ)){
														$serial_number_id = $poscartonerow->serial_number_id;
														
														$dtrow = $this->db->delete('serial_number', 'serial_number_id', $serial_number_id);
														if($dtrow){
															$dtcount++;
														}
													}
												}
											}
											
											$dtrow = $this->db->delete($possubtablename, $possubtableidname, $possubtableidvalue);			
											if($dtrow){
												$dtcount++;
											}
										}
									}
								}
							}
							elseif($tablename=='po'){
				
								$po_itemssql="SELECT po_items_id FROM po_items WHERE po_id = $tableidvalue ORDER BY po_items_id ASC";
								$po_itemsquery = $this->db->query($po_itemssql, array());						
								if($po_itemsquery){
									while($po_itemsonerow = $po_itemsquery->fetch(PDO::FETCH_OBJ)){
										$po_items_id = $po_itemsonerow->po_items_id;
										
										$dtrow = $this->db->delete('po_items', 'po_items_id', $po_items_id);
										if($dtrow){
											$dtrow = $this->db->delete('po_cart_item', 'po_items_id', $po_items_id);
											if($dtrow){
												$dtcount++;
											}
											
											$dtcount++;
										}
									}
								}								
							}
							elseif($tablename=='user'){
				
								$user_login_historysql="SELECT user_login_history_id FROM user_login_history WHERE user_id = $tableidvalue ORDER BY user_login_history_id ASC";
								$user_login_historyquery = $this->db->query($user_login_historysql, array());						
								if($user_login_historyquery){
									while($user_login_historyonerow = $user_login_historyquery->fetch(PDO::FETCH_OBJ)){
										
										$user_login_history_id = $user_login_historyonerow->user_login_history_id;
										
										$dtrow = $this->db->delete('user_login_history', 'user_login_history_id', $user_login_history_id);
										if($dtrow){
											$dtcount++;
										}
									}
								}								
							}
							
							$dtrow = $this->db->delete($tablename, $tableidname, $tableidvalue);
							if($dtrow){
								$dtcount++;
							}
						}
					}
				}
				
				$dtrow = $this->db->delete('accounts', 'accounts_id', $accounts_id);
				if($dtrow){
					$dtcount++;
				}
				
				if($dtcount>0){
					$returnmsg = 'remove-success';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnmsg'=>$returnmsg, 'dtcount'=>$dtcount));
	}	
	
	public function AJreset_Accounts(){
		$returnmsg = '';
		$dtcount = 0;
		if(!isset($_SESSION["prod_cat_man"])){
			$returnmsg = 'session_ended';
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			if($POST['resetuserpassword'] !='RESET'){
				$returnmsg .= '<p>Wrong password! Enter correct password.</p>';
			}
			else{
				
				$accounts_id = $POST['accounts_id'];
				$keep_product = $POST['keep_product']??0;
				
				$prod_cat_man = $accounts_id;
				$location_of = 0;
				$default_customer = 0;
				$queryObj = $this->db->query("SELECT location_of, default_customer FROM accounts WHERE accounts_id = :accounts_id", array('accounts_id'=>$accounts_id),1);
				if($queryObj){
					$accountsRow = $queryObj->fetch(PDO::FETCH_OBJ);	
					$location_of = $accountsRow->location_of;	
					$default_customer = $accountsRow->default_customer;				
				}
				if($location_of>0){$keep_product = 1;}
				$locationcount = 0;				
				if($location_of==0){
					$queryObj = $this->db->query("SELECT COUNT(accounts_id) AS totalrows FROM accounts WHERE location_of = :location_of AND status != 'SUSPENDED'", array('location_of'=>$accounts_id),1);
					if($queryObj){
						$locationcount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
					}
					
					$inventorycount = 0;
					if($locationcount>0){
						$locationquery = $this->db->query("SELECT accounts_id FROM accounts WHERE location_of = :location_of AND status != 'SUSPENDED'", array('location_of'=>$accounts_id),1);
						if($locationquery){
							while($oneUserRow = $locationquery->fetch(PDO::FETCH_OBJ)){
								$location_accounts_id = $oneUserRow->accounts_id;
								$inventorytotal = 0;
								$invSql = "SELECT COUNT(inventory_id) AS totalrows FROM inventory WHERE accounts_id = $location_accounts_id AND current_inventory !=0";
								$inventoryObj = $this->db->query($invSql, array());
								if($inventoryObj){
									$inventorytotal = $inventoryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
								}
								$inventorycount += $inventorytotal;
							}
						}
					}
					if($inventorycount == 0){
						$locationcount = 0;
					}
				}
				else{
					$prod_cat_man = $location_of;
				}
					
				if($locationcount>0){
					$returnmsg .= '<p>There is '.$locationcount.' location under this instance. Please reset location first then try to reset this instance accounts.</p>';
				}
				else{
					
					$keep_customers = $POST['keep_customers']??0;
					
					$folderPath = './assets/accounts/a_'.$accounts_id;
					if(is_dir($folderPath)){
						$files = glob($folderPath.'/*'); // get all file names
						foreach($files as $file){ // iterate files
						  if(is_file($file))
							unlink($file); // delete file
						}
						
						rmdir($folderPath);
					}
					
					$tableNameArray = array('activity_feed', 'appointments', 'commissions', 'digital_signature', 
											'end_of_day', 'expenses', 'item', 
											'notes', 'petty_cash', 'po', 'pos','product_prices', 'repairs', 
											'time_clock', 'track_edits');
					if($keep_product==0){						
						$tableNameArray[] = 'inventory';						
					}
					if($accounts_id==$prod_cat_man){
						$tableNameArray[] = 'brand_model';
						$tableNameArray[] = 'expense_type';
						$tableNameArray[] = 'forms';
						$tableNameArray[] = 'repair_problems';
						$tableNameArray[] = 'suppliers';
						$tableNameArray[] = 'vendors';
						
						if($keep_product==0){
							$tableNameArray[] = 'product';
							$tableNameArray[] = 'category';
							$tableNameArray[] = 'manufacturer';
						}
					}					
					
					$additionalssql = '';
					if($keep_customers==0){
						$tableNameArray[] = 'customers';
						$tableNameArray[] = 'customer_type';
						if($default_customer>0){
							$additionalssql = " AND customers_id != $default_customer";
						}
						$tableNameArray[] = 'custom_fields';
						if($accounts_id==$prod_cat_man){
							$tableNameArray[] = 'properties';
						}
					}
					$posReltableArray = array('pos_cart', 'pos_payment');
					
					foreach($tableNameArray as $tablename){
						$tableidname = $tablename.'_id';
						
						$sqlquery = "SELECT $tableidname FROM $tablename WHERE accounts_id = $accounts_id";
						if($tablename=='category'){
							$sqlquery .= " AND category_name not in('Parts', 'Accessories')";
						}
						elseif($tablename=='customers'){
							$sqlquery .= $additionalssql;
						}
						elseif($tablename=='manufacturer'){
							$sqlquery .= " AND name not in('Apple', 'Samsung')";
						}
						elseif($tablename=='user'){
							$sqlquery .= " AND is_admin = 0";
						}
						elseif($tablename=='variables'){
							$sqlquery .= " AND name not in('account_setup', 'payment_options', 'product_setup', 'invoice_setup', 'repairs_setup')";
						}
						elseif($tablename=='taxes'){
							$sqlquery .= " AND default_tax = 0";
						}
						$sqlquery .= " ORDER BY $tableidname ASC";
						$query = $this->db->query($sqlquery, array());						
						if($query){							
							while($onerow = $query->fetch(PDO::FETCH_OBJ)){
								
								$tableidvalue = $onerow->$tableidname;
								
								if($tablename=='pos'){
									foreach($posReltableArray as $possubtablename){
										$possubtableidname = $possubtablename.'_id';
										$possubsql="SELECT $possubtableidname FROM $possubtablename WHERE pos_id = $tableidvalue ORDER BY $possubtableidname ASC";
										$possubquery = $this->db->query($possubsql, array());						
										if($possubquery){
											while($possubonerow = $possubquery->fetch(PDO::FETCH_OBJ)){
												$possubtableidvalue = $possubonerow->$possubtableidname;
												if($possubtablename=='pos_cart'){
													$poscartsql="SELECT pos_cart_item_id FROM pos_cart_item WHERE pos_cart_id = $possubtableidvalue ORDER BY pos_cart_item_id ASC";
													$poscartquery = $this->db->query($poscartsql, array());						
													if($poscartquery){
														while($poscartonerow = $poscartquery->fetch(PDO::FETCH_OBJ)){
															$pos_cart_item_id = $poscartonerow->pos_cart_item_id;
															
															$dtrow = $this->db->delete('pos_cart_item', 'pos_cart_item_id', $pos_cart_item_id);
															if($dtrow){
																$dtcount++;
															}
														}
													}
													
													$poscartsql="SELECT serial_number_id FROM serial_number WHERE pos_cart_id = $possubtableidvalue ORDER BY serial_number_id ASC";
													$poscartquery = $this->db->query($poscartsql, array());						
													if($poscartquery){
														while($poscartonerow = $poscartquery->fetch(PDO::FETCH_OBJ)){
															$serial_number_id = $poscartonerow->serial_number_id;
															
															$dtrow = $this->db->delete('serial_number', 'serial_number_id', $serial_number_id);
															if($dtrow){
																$dtcount++;
															}
														}
													}
												}
												
												$dtrow = $this->db->delete($possubtablename, $possubtableidname, $possubtableidvalue);			
												if($dtrow){
													$dtcount++;
												}
											}
										}
									}
								}
								elseif($tablename=='po'){
					
									$po_itemssql="SELECT po_items_id FROM po_items WHERE po_id = $tableidvalue ORDER BY po_items_id ASC";
									$po_itemsquery = $this->db->query($po_itemssql, array());						
									if($po_itemsquery){
										while($po_itemsonerow = $po_itemsquery->fetch(PDO::FETCH_OBJ)){
											$po_items_id = $po_itemsonerow->po_items_id;
											
											$dtrow = $this->db->delete('po_items', 'po_items_id', $po_items_id);
											if($dtrow){
												$dtrow = $this->db->delete('po_cart_item', 'po_items_id', $po_items_id);
												if($dtrow){
													$dtcount++;
												}
												
												$dtcount++;
											}
										}
									}						
								}
								elseif($tablename=='user'){
					
									$user_login_historysql = "SELECT user_login_history_id FROM user_login_history WHERE user_id = $tableidvalue ORDER BY user_login_history_id ASC";
									$user_login_historyquery = $this->db->query($user_login_historysql, array());						
									if($user_login_historyquery){
										while($user_login_historyonerow = $user_login_historyquery->fetch(PDO::FETCH_OBJ)){
											$user_login_history_id = $user_login_historyonerow->user_login_history_id;
											
											$dtrow = $this->db->delete('user_login_history', 'user_login_history_id', $user_login_history_id);
											if($dtrow){
												$dtcount++;
											}
										}
									}						
								}
								
								$dtrow = $this->db->delete($tablename, $tableidname, $tableidvalue);
								if($dtrow){
									$dtcount++;
								}
							}
						}
					}
					
					if($keep_product==1){
						$inventorysql = "SELECT inventory_id FROM inventory WHERE accounts_id = $accounts_id ORDER BY inventory_id ASC";
						$inventoryquery = $this->db->query($inventorysql, array());						
						if($inventoryquery){
							while($onerow = $inventoryquery->fetch(PDO::FETCH_OBJ)){
								$inventory_id = $onerow->inventory_id;
								$this->db->update('inventory', array('ave_cost'=>0.00, 'current_inventory'=>0), $inventory_id);
							}
						}
					}

					if($location_of==0 && $keep_product==0){
						$locationquery = $this->db->query("SELECT accounts_id FROM accounts WHERE location_of = :location_of AND status != 'SUSPENDED'", array('location_of'=>$accounts_id),1);
						if($locationquery){
							while($oneUserRow = $locationquery->fetch(PDO::FETCH_OBJ)){
								$location_accounts_id = $oneUserRow->accounts_id;
								$inventorysql = "SELECT inventory_id FROM inventory WHERE accounts_id = $location_accounts_id ORDER BY inventory_id ASC";
								$inventoryquery = $this->db->query($inventorysql, array());						
								if($inventoryquery){
									while($onerow = $inventoryquery->fetch(PDO::FETCH_OBJ)){
										$inventory_id = $onerow->inventory_id;
										$dtrow = $this->db->delete('inventory', 'inventory_id', $inventory_id);
										if($dtrow){
											$dtcount++;
										}
									}
								}
							}
						}
					}
					
					$returnmsg = 'reset-success';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnmsg'=>$returnmsg, 'dtcount'=>$dtcount));
	}	
	
	public function AJsave_AccountsInvoice(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $POST['accounts_id'];
		$invoice_number = 1000;
		$our_invoicesObj = $this->db->querypagination("SELECT invoice_number FROM our_invoices ORDER BY invoice_number DESC LIMIT 0, 1", array());
		if($our_invoicesObj){
			$invoice_number = $our_invoicesObj[0]['invoice_number']+1;
		}
		$price_per_location = $POST['price_per_location'];
		$subdomain = array();
		$accSql = "SELECT company_subdomain FROM accounts WHERE accounts_id = $accounts_id AND status != 'SUSPENDED'";
		$noOfLocObj = $this->db->query($accSql, array());
		if($noOfLocObj){
			while($oneRow = $noOfLocObj->fetch(PDO::FETCH_OBJ)){
				$subdomain[] = $oneRow->company_subdomain;
			}
			if(count($subdomain)>1){$price_per_location = round($price_per_location/count($subdomain),2);}
		}
		
		$next_payment_due = '1000-01-01';
		if($POST['next_payment_due'] !=''){
			$next_payment_due = date('Y-m-d', strtotime($POST['next_payment_due']));
		}
		$paid_on = '1000-01-01';
		if($POST['paid_on'] !=''){
			$paid_on = date('Y-m-d', strtotime($POST['paid_on']));
		}
		$description = "For account(s): ".implode(', ', $subdomain)."<br>Have QTY = ".count($subdomain)."@\$".number_format($price_per_location,2).", Total: \$".number_format(count($subdomain)*$price_per_location, 2);
		if($POST['description'] !=''){
			$description = trim((string) addslashes($POST['description']));
		}
		
		$paid_by = $this->db->checkCharLen('our_invoices.paid_by', trim((string) addslashes($POST['paid_by'])));
		$pay_frequency = $this->db->checkCharLen('our_invoices.pay_frequency', 'One Time');
		
		$ourInvoicesData = array();
		$ourInvoicesData['accounts_id'] = $accounts_id;
		$ourInvoicesData['invoice_number'] = $invoice_number;
		$ourInvoicesData['next_payment_due'] = $next_payment_due;
		$ourInvoicesData['paid_on'] = $paid_on;
		$ourInvoicesData['paid_by'] = $paid_by;
		$ourInvoicesData['num_locations'] = count($subdomain);
		$ourInvoicesData['description'] = $description;
		$ourInvoicesData['pay_frequency'] = $pay_frequency;
		$ourInvoicesData['price_per_location'] = $price_per_location;
		$ourInvoicesData['refunded'] = 0;
		$ourInvoicesData['batch_date'] = '1000-01-01';
		$ourInvoicesData['HostBatchID'] = 0;
		
		$our_invoices_id = $this->db->insert('our_invoices', $ourInvoicesData);
		if($our_invoices_id){
			$returnStr = 'Save Successfully';
		}
		else{
			$returnStr = 'Error occured while adding new invoice! Please try again.';
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJsave_AccountsMessage(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$popup_message = $POST['popup_message'];
		$accounts_id = $POST['accounts_id'];
		
		$update = false;
		$sqlquery = "SELECT user_id FROM user WHERE accounts_id = :accounts_id ORDER BY user_id ASC";
		$queryrows = $this->db->query($sqlquery, array('accounts_id'=>$accounts_id),1);
		if($queryrows){
			while($onerow = $queryrows->fetch(PDO::FETCH_OBJ)){
				$user_id = $onerow->user_id;
				$update = $this->db->update('user', array('popup_message'=>trim(addslashes(stripslashes($popup_message)))), $user_id);
			}
		
			$returnStr = 'Save Successfully';
		}
		else{
			$returnStr = 'Error occured while updating popup message information! Please try again.';
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJsave_PaypalPayment(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$accounts_id = $POST['accounts_id'];
		$accountsData = array();
		$accountsData['paypal_id'] = $paypal_id = $this->db->checkCharLen('accounts.paypal_id', $POST['paypal_id']);
		$accountsData['status'] = $status = 'Active';
		$accountsData['status_date'] = date('Y-m-d H:i:s');
		$next_payment_due = '1000-01-01';
		if($POST['nextnext_payment_due'] !=''){
			$next_payment_due = date('Y-m-d', strtotime($POST['nextnext_payment_due']));
		}
		$accountsData['next_payment_due'] = $next_payment_due;
		
		if($accounts_id>0){
			$oldStatus = '';
			$price_per_location = 0;
			$pay_frequency = 'Monthly';
			$accOneObj = $this->db->query("SELECT status, price_per_location, pay_frequency FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accOneObj){
				$accOneRow = $accOneObj->fetch(PDO::FETCH_OBJ);
				$oldStatus = $accOneRow->status;
				$price_per_location = $accOneRow->price_per_location;
				$pay_frequency = $accOneRow->pay_frequency;
			}
			$this->db->update('accounts', $accountsData, $accounts_id);
			
			if($oldStatus != $status){
				date_default_timezone_set('America/New_York');
				$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
									'accounts_id'=>$accounts_id,
									'description'=>"Account status changed from $oldStatus to $status");
				$this->db->insert('our_notes', $our_notesData);
				$timezone = 'America/New_York';
				if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
				date_default_timezone_set($timezone);
			}
			//=============Add Data into our invoice=======//
			$invoice_number = 1000;
			$our_invoicesObj = $this->db->querypagination("SELECT invoice_number FROM our_invoices ORDER BY invoice_number DESC LIMIT 0, 1", array());
			if($our_invoicesObj){
				$invoice_number = $our_invoicesObj[0]['invoice_number']+1;
			}
			$subdomain = array();
			$accSql = "SELECT company_subdomain FROM accounts WHERE accounts_id = $accounts_id AND status != 'SUSPENDED'";
			$noOfLocObj = $this->db->query($accSql, array());
			if($noOfLocObj){
				while($oneRow = $noOfLocObj->fetch(PDO::FETCH_OBJ)){
					$subdomain[] = $oneRow->company_subdomain;
				}
				if(count($subdomain)>1){$price_per_location = round($price_per_location/count($subdomain),2);}
			}
			$description = "For account: ".implode(', ', $subdomain)."<br>Have QTY = ".count($subdomain)."@\$".number_format($price_per_location,2).", Total: \$".number_format(count($subdomain)*$price_per_location, 2);
			
			$paid_by = $this->db->checkCharLen('our_invoices.paid_by', trim((string) addslashes("PP-$paypal_id")));
			$pay_frequency = $this->db->checkCharLen('our_invoices.pay_frequency', $pay_frequency);
					
			$oiData = array();
			$oiData['accounts_id'] = $accounts_id;
			$oiData['invoice_number'] = $invoice_number;
			$oiData['next_payment_due'] = $next_payment_due;
			$oiData['paid_on'] = date("Y-m-d");
			$oiData['paid_by'] = $paid_by;
			$oiData['num_locations'] = count($subdomain);
			$oiData['description'] = $description;
			$oiData['pay_frequency'] = $pay_frequency;
			$oiData['price_per_location'] = round($price_per_location,2);
			$oiData['refunded'] = 0;
			$oiData['batch_date'] = '1000-01-01';
			$oiData['HostBatchID'] = 0;
			
			$our_invoices_id = $this->db->insert('our_invoices', $oiData);
			
			$returnStr = "Save Successfully";
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJget_OurNotes(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$description = '';
		$our_notes_id = $POST['our_notes_id'];
		$sql = "SELECT description FROM our_notes WHERE our_notes_id = :our_notes_id ORDER BY our_notes_id ASC LIMIT 0, 1";			
		$query = $this->db->querypagination($sql, array('our_notes_id'=>$our_notes_id),1);
		if($query){
			foreach($query as $oneRow){
				$description = $oneRow['description'];
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$description));
	}
	
	public function AJsave_OurNotes(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 'Could not save data.';
	
		date_default_timezone_set('America/New_York');		
		$our_notes_id = $POST['our_notes_id'];
		$conditionarray = array();
		$conditionarray['accounts_id'] = $POST['accounts_id'];
		$conditionarray['description'] = addslashes($POST['description']);
		if($our_notes_id>0){
			$this->db->update('our_notes', $conditionarray, $our_notes_id);
			$returnStr = 'Save';
		}
		else{
			$conditionarray['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('our_notes', $conditionarray);
			$returnStr = 'Save';
		}
		$timezone = 'America/New_York';
		if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
		date_default_timezone_set($timezone);
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
				
	private function filterAndOptions_edit(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$saccounts_id = $this->accounts_id;
		$invoice_number = $this->invoice_number;
		
		$totalRows = 0;
		$filterSql = "";
		$bindData = array();
		$bindData['accounts_id'] = $saccounts_id;
		if($invoice_number !=''){
			$invoice_number = addslashes(trim((string) $invoice_number));
			if ( $invoice_number == "" ) { $invoice_number = " "; }
			$invoice_numberes = explode (" ", $invoice_number);
			if ( strpos($invoice_number, " ") === false ) {$invoice_numberes[0] = $invoice_number;}
			$num = 0;
			while ( $num < sizeof($invoice_numberes) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', invoice_number, paid_by, description, pay_frequency)) LIKE CONCAT('%', :invoice_number$num, '%')";
				$bindData['invoice_number'.$num] = trim((string) $invoice_numberes[$num]);
				$num++;
			}
		}
		
		$strextra ="SELECT COUNT(our_invoices_id) AS totalrows FROM our_invoices WHERE accounts_id = :accounts_id $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;
		
	}
	
    private function loadTableRows_edit(){
        
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$saccounts_id = $this->accounts_id;
		$invoice_number = $this->invoice_number;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		$filterSql = "";
		$bindData = array();
		if($invoice_number !=''){
			$invoice_number = addslashes(trim((string) $invoice_number));
			if ( $invoice_number == "" ) { $invoice_number = " "; }
			$invoice_numberes = explode (" ", $invoice_number);
			if ( strpos($invoice_number, " ") === false ) {$invoice_numberes[0] = $invoice_number;}
			$num = 0;
			while ( $num < sizeof($invoice_numberes) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', invoice_number, paid_by, description, pay_frequency)) LIKE CONCAT('%', :invoice_number$num, '%')";
				$bindData['invoice_number'.$num] = trim((string) $invoice_numberes[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * FROM our_invoices WHERE accounts_id = $saccounts_id $filterSql ORDER BY invoice_number DESC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tableData = array();
		if($query){
			foreach($query as $oneRow){
				
				$our_invoices_id = $oneRow['our_invoices_id'];						
				$accounts_id = $oneRow['accounts_id'];						
				$next_payment_due = $oneRow['next_payment_due'];
				$invoice_number = $oneRow['invoice_number'];
				$description = $oneRow['description'];
				$paid_by = $oneRow['paid_by'];
				$total = number_format($oneRow['num_locations']*$oneRow['price_per_location'],2);
				$paid_on = 'Unpaid';
				if( !in_array($oneRow['paid_on'], array('0000-00-00', '1000-01-01'))){
					$paid_on = $oneRow['paid_on'];
				}
				$tableData[] = array($paid_on, $invoice_number, $description, $paid_by, "\$$oneRow[price_per_location]/$oneRow[pay_frequency]", $oneRow['num_locations'], $total, $next_payment_due, $our_invoices_id);
				
			}
		}
		return $tableData;
    }
	
	public function AJgetPage_edit($segment4name){		
		$POST = json_decode(file_get_contents('php://input'), true);
		$saccounts_id = $POST['saccounts_id']??0;		
		$invoice_number = $POST['invoice_number']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->accounts_id = $saccounts_id;
		$this->invoice_number = $invoice_number;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_edit();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;				
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadTableRows_edit();
		
		return json_encode($jsonResponse);
	}
		
	public function AJsave_userloginMessage(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$returnmsg = '';
		$POST = json_decode(file_get_contents('php://input'), true);
		if(!isset($_SESSION["prod_cat_man"])){
			$returnmsg = 'session_ended';
		}
		else{
			$login_message = addslashes(trim((string) $POST['login_message']));
			$user_id = 6;
			$update = $this->db->update('user', array('login_message'=>trim(addslashes(stripslashes($login_message)))), $user_id);

			if($update){
				$returnmsg = 'update-success';
			}
			else{
				$returnmsg = 'There is no changes made. Please change message information then try again.';
			}			
		}
		$jsonResponse['returnmsg'] = $returnmsg;
		return json_encode($jsonResponse);
	}
	
	//=========================invoicesReport======================//
	
	public function invoicesReport(){}
	
	private function filterAndOptions_invoicesReport(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sorder_by = $this->order_by;
		$spaid_by = $this->paid_by;
		$date_range = $this->date_range;
		$keyword_search = $this->keyword_search;
		
		$startdate = $enddate = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0]));
				$enddate = date('Y-m-d', strtotime($date_rangearray[1]));
			}
		}
		
		$_SESSION["current_module"] = "invoicesReport";
		$_SESSION["list_filters"] = array('sorder_by'=>$sorder_by, 'spaid_by'=>$spaid_by, 'date_range'=>$date_range, 'keyword_search'=>$keyword_search);
		
		$totalRows = 0;
		$filterData = $bindData = array();
		if($startdate !='' && $enddate !=''){
			if($startdate === $enddate){
				$filterData[] = "paid_on = :startdate";
				$bindData['startdate'] = $startdate;
			}
			else{
				$filterData[] = "paid_on BETWEEN :startdate AND :enddate";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}
		}
		if($spaid_by !=''){
			$filterData[] = "paid_by = :paid_by";
			$bindData['paid_by'] = $spaid_by;
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterData[] = "TRIM(CONCAT_WS(' ', invoice_number, paid_by, description, pay_frequency)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql = '';
		if(!empty($filterData)){
			$filterSql = " WHERE ".implode(" AND ", $filterData);
		}
		
		$strextra ="SELECT COUNT(our_invoices_id) AS totalrows FROM our_invoices $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
	
		$this->totalRows = $totalRows;
	}
	
    private function loadTableRows_invoicesReport(){
		
		$limit = $_SESSION["limit"]??'auto';
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$ssorting_type = $this->order_by;
		$spaid_by = $this->paid_by;
		$date_range = $this->date_range;
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'our_invoices.invoice_number DESC', 
								1=>'our_invoices.invoice_number ASC', 
								2=>'our_invoices.paid_on DESC', 
								3=>'our_invoices.paid_on ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->order_by = $ssorting_type;
		}
		
		$startdate = $enddate = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0]));
				$enddate = date('Y-m-d', strtotime($date_rangearray[1]));
			}
		}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
	
		$filterData = $bindData = array();
		if($startdate !='' && $enddate !=''){
			if($startdate === $enddate){
				$filterData[] = "paid_on = :startdate";
				$bindData['startdate'] = $startdate;
			}
			else{
				$filterData[] = "paid_on BETWEEN :startdate AND :enddate";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}
		}
		if($spaid_by !=''){
			$filterData[] = "paid_by = :paid_by";
			$bindData['paid_by'] = $spaid_by;
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterData[] = "TRIM(CONCAT_WS(' ', invoice_number, paid_by, description, pay_frequency)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql = '';
		if(!empty($filterData)){
			$filterSql = " WHERE ".implode(" AND ", $filterData);
		}
		$sqlquery = "SELECT * FROM our_invoices $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tableData= array();
		if($query){
			foreach($query as $rowproduct){
				
				$our_invoices_id = $rowproduct['our_invoices_id'];			
				$description = nl2br(trim((string) $rowproduct['description']));
				$paid_on = 'Unpaid';
				if( !in_array($rowproduct['paid_on'], array('0000-00-00', '1000-01-01'))){
					$paid_on = $rowproduct['paid_on'];
				}
				$PricePerLocation = "\$$rowproduct[price_per_location]/$rowproduct[pay_frequency]";
				$total = '৳'.number_format($rowproduct['num_locations']*$rowproduct['price_per_location'],2);
				$next_payment_due = $rowproduct['next_payment_due'];
				
				$tableData[] = array($our_invoices_id, $paid_on, $rowproduct['invoice_number'], $description, $rowproduct['paid_by'], $PricePerLocation, $rowproduct['num_locations'], $total, $next_payment_due);

			}
		}
		return $tableData;
    }
	
	public function AJgetPage_invoicesReport($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sorder_by = $POST['sorder_by']??0;
		$spaid_by = $POST['spaid_by']??'';
		$date_range = $POST['date_range']??'';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = $POST['totalRows']??0;
		$page = $POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->order_by = $sorder_by;
		$this->paid_by = $spaid_by;
		$this->date_range = $date_range;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_invoicesReport();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_invoicesReport();
		
		return json_encode($jsonResponse);
	}

	public function AJ_printsInvoice_MoreInfo(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$POST = json_decode(file_get_contents('php://input'), true);
		$our_invoices_id = $POST['our_invoices_id'];
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['COMPANYNAME'] = COMPANYNAME;
		
		$our_invoicesObj = $this->db->query("SELECT * FROM our_invoices WHERE our_invoices_id = :our_invoices_id", array('our_invoices_id'=>$our_invoices_id),1);
		if($our_invoicesObj){
			$our_invoices_onerow = $our_invoicesObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $our_invoices_onerow->accounts_id;
			
			$invoice_number = $our_invoices_onerow->invoice_number;
			$title = 'accounts Billing #'.$invoice_number;
			$description = $our_invoices_onerow->description;
			$num_locations = $our_invoices_onerow->num_locations;
			$price_per_location = round($our_invoices_onerow->price_per_location,2);
			$total = $num_locations*$price_per_location;
			
			$paid_by = $our_invoices_onerow->paid_by;
			if($paid_by==''){$paid_by = 'Unpaid';}
			
			$company_info = '';
			$accountsObj = $this->db->query("SELECT company_name, company_street_address, company_city, company_state_name, company_zip, company_country_name FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accountsObj){
				$accountsOneRow = $accountsObj->fetch(PDO::FETCH_OBJ);
				$company_info = stripslashes(trim((string) $accountsOneRow->company_name));
				$address = stripslashes(trim((string) $accountsOneRow->company_street_address));
				$company_city = stripslashes(trim((string) $accountsOneRow->company_city));
				if($company_city !=''){$address .= ', '.$company_city;}
				$company_state_name = stripslashes(trim((string) $accountsOneRow->company_state_name));
				if($company_state_name !=''){$address .= ', '.$company_state_name;}
				$company_zip = stripslashes(trim((string) $accountsOneRow->company_zip));
				if($company_zip !=''){$address .= ' '.$company_zip;}
				$company_country_name = stripslashes(trim((string) $accountsOneRow->company_country_name));
				if($company_country_name !=''){$address .= ', '.$company_country_name;}
				
				if($company_info !=''){
					$company_info .='<br />';
				}
				$company_info .= $address;
			}
			$jsonResponse['title'] = $title;
			$jsonResponse['invoice_number'] = $invoice_number;
			$jsonResponse['company_info'] = nl2br($company_info);
			$jsonResponse['description'] = nl2br($description);
			$jsonResponse['num_locations'] = $num_locations;
			$jsonResponse['price_per_location'] = $price_per_location;
			$jsonResponse['total'] = $total;
			$jsonResponse['paid_by'] = nl2br($paid_by);
			$jsonResponse['paid_on'] = $our_invoices_onerow->paid_on;

		}		
		return json_encode($jsonResponse);
	}
	
	public function printsInvoice($our_invoices_id){
		$language = $_SESSION["language"]??'English';
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

		$htmlStr = "";
		$our_invoicesObj = $this->db->query("SELECT our_invoices_id FROM our_invoices WHERE our_invoices_id = :our_invoices_id", array('our_invoices_id'=>$our_invoices_id),1);
		if($our_invoicesObj){
			$our_invoices_id = $our_invoicesObj->fetch(PDO::FETCH_OBJ)->our_invoices_id;
			
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
					<script src="/assets/js-'.swVersion.'/'.printsJS.'"></script>
				</body>
			</html>';
		}	
		
		return $htmlStr;
	}	

	//=========================our_notes======================//
	public function our_notes(){}
	
	private function filterAndOptions_our_notes(){
		
		$sorder_by = $this->order_by;
		$date_range = $this->date_range;
		$keyword_search = $this->keyword_search;
		
		$startdate = $enddate = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0]));
				$enddate = date('Y-m-d', strtotime($date_rangearray[1]));
			}
		}
		
		$_SESSION["current_module"] = "our_notes";
		$_SESSION["list_filters"] = array('sorder_by'=>$sorder_by, 'date_range'=>$date_range, 'keyword_search'=>$keyword_search);
		
		$totalRows = 0;
		$filterData = $bindData = array();
		if($startdate !='' && $enddate !=''){
			if($startdate === $enddate){
				$filterData[] = "created_on = :startdate";
				$bindData['startdate'] = $startdate;
			}
			else{
				$filterData[] = "created_on BETWEEN :startdate AND :enddate";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterData[] = "TRIM(CONCAT_WS(' ', accounts_id, description)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql = '';
		if(!empty($filterData)){
			$filterSql = " WHERE ".implode(" AND ", $filterData);
		}
		
		$strextra ="SELECT COUNT(our_notes_id) AS totalrows FROM our_notes $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
	
		$this->totalRows = $totalRows;
	}
	
    private function loadTableRows_our_notes(){
		
		$limit = $_SESSION["limit"]??'auto';
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$ssorting_type = $this->order_by;
		$date_range = $this->date_range;
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'created_on DESC', 
								1=>'created_on ASC');

		$startdate = $enddate = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0]));
				$enddate = date('Y-m-d', strtotime($date_rangearray[1]));
			}
		}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
	
		$filterData = $bindData = array();
		if($startdate !='' && $enddate !=''){
			if($startdate === $enddate){
				$filterData[] = "created_on = :startdate";
				$bindData['startdate'] = $startdate;
			}
			else{
				$filterData[] = "created_on BETWEEN :startdate AND :enddate";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}
		}
		
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterData[] = "TRIM(CONCAT_WS(' ', accounts_id, description)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql = '';
		if(!empty($filterData)){
			$filterSql = " WHERE ".implode(" AND ", $filterData);
		}

		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->order_by = $ssorting_type;
		}

		$sqlquery = "SELECT * FROM our_notes $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tableData= array();
		if($query){
			$accountsIds = array();
			foreach($query as $rowproduct){
				$accountsIds[$rowproduct['accounts_id']] = '';
			}

			if(!empty($accountsIds)){
				$queryObj = $this->db->query("SELECT accounts_id, company_subdomain FROM accounts WHERE accounts_id IN (".implode(', ', array_keys($accountsIds)).")", array());
				if($queryObj){
					while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){
						$accountsIds[$oneRow->accounts_id] = stripslashes(trim((string) "$oneRow->company_subdomain"));
					}
				}
			}

			foreach($query as $rowproduct){
				
				$our_notes_id = $rowproduct['our_notes_id'];			
				$description = nl2br(trim((string) $rowproduct['description']));
				$created_on = '';
				if( !in_array($rowproduct['created_on'], array('0000-00-00', '1000-01-01'))){
					$created_on = $rowproduct['created_on'];
				}
				
				$tableData[] = array($our_notes_id, $rowproduct['accounts_id'], $created_on, $accountsIds[$rowproduct['accounts_id']]??'', $description);

			}
		}
		return $tableData;
    }
	
	public function AJgetPage_our_notes($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sorder_by = $POST['sorder_by']??0;
		$date_range = $POST['date_range']??'';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = $POST['totalRows']??0;
		$page = $POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->order_by = $sorder_by;
		$this->date_range = $date_range;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_our_notes();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_our_notes();
		
		return json_encode($jsonResponse);
	}

}
?>