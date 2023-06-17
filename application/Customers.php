<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Customers{
	protected $db;
	private int $page, $totalRows, $customers_id;
	private string $sorting_type, $data_type, $customer_type, $keyword_search, $history_type;
	private array $custTypeOpt;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){
		$allowed = $_SESSION["allowed"];
		$cncrm = 0;
		if(!empty($_SESSION["allowed"]) && array_key_exists(4, $_SESSION["allowed"])) {
			$callowedData = $_SESSION["allowed"][4];
			if(in_array('cncrm', $callowedData)){
				$cncrm = 1;
			}
		}
		return '<script type="text/javascript">var cncrm = '.$cncrm.'</script>';
	}
	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sorting_type = $POST['sorting_type']??0;
		$sdata_type = $POST['sdata_type']??'All';
		$scustomer_type = $POST['scustomer_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->sorting_type = $sorting_type;
		$this->data_type = $sdata_type;
		$this->customer_type = $scustomer_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['custTypeOpt'] = $this->custTypeOpt;
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}
	
    private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sorting_type = $this->sorting_type;
		$sdata_type = $this->data_type;
		$scustomer_type = $this->customer_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Customers";
		$_SESSION["list_filters"] = array('sorting_type'=>$sorting_type, 'scustomer_type'=>$scustomer_type, 'keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		if($scustomer_type !='All'){
			$filterSql .= " AND customer_type = :customer_type";
			$bindData['customer_type'] = $scustomer_type;
		}
		$sqlPublish = " AND customers_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND customers_publish = 0";
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
		$queryObj = $this->db->query("SELECT COUNT(customers_id) AS totalrows FROM customers WHERE accounts_id = $prod_cat_man $filterSql $sqlPublish", $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}
		$this->totalRows = $totalRows;

		$custTypeOpt = array();
		$tableObj = $this->db->query("SELECT customer_type FROM customers WHERE accounts_id = $prod_cat_man $filterSql $sqlPublish GROUP BY customer_type", $bindData);
		if($tableObj){
			$custTypeOpts = array();
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				if(!empty($oneRow->customer_type))
					$custTypeOpts[$oneRow->customer_type] = '';
			}
			if(!empty($custTypeOpts)){
				ksort($custTypeOpts);
				$custTypeOpt = array_keys($custTypeOpts);
			}
		}
		$this->custTypeOpt = $custTypeOpt;
	}
	
    private function loadTableRows(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$ssorting_type = $this->sorting_type;
		$sdata_type = $this->data_type;
		$scustomer_type = $this->customer_type;
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'TRIM(UPPER(CONCAT_WS(\' \', company, first_name, last_name))) ASC', 
								1=>'first_name ASC', 
								2=>'last_name ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND customers_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND customers_publish = 0";
		}		
		$filterSql = "FROM customers WHERE accounts_id = $prod_cat_man $sqlPublish";
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
		
		$sqlquery = "SELECT customers_id, TRIM(CONCAT_WS(' ', company, first_name, last_name)) AS name, email, contact_no $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$i = $starting_val+1;
		$tabledata = array();
		if($query){
			foreach($query as $oneRow){
				$customers_id = $oneRow['customers_id'];
				
				$name = stripslashes($oneRow['name']);
				
				$email = $oneRow['email'];
				if($email==''){$email = '&nbsp;';}
				$contact_no = $oneRow['contact_no'];
				if($contact_no==''){$contact_no = '&nbsp;';}
				
				$tabledata[] = array($customers_id, $name, $email, $contact_no);
			}
		}
		return $tabledata;
    }
	
	public function view(){}	
	
	public function AJ_view_moreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$customers_id = intval($POST['customers_id']??0);
		$canUnArchive = $default_customer = 0;
		if($prod_cat_man==$accounts_id){$canUnArchive = 1;}
		$accObj = $this->db->query("SELECT default_customer FROM accounts WHERE accounts_id = $prod_cat_man", array());
		if($accObj){
			while($oneAccRow = $accObj->fetch(PDO::FETCH_OBJ)){
				$default_customer = $oneAccRow->default_customer;
				if($default_customer==$customers_id){$canUnArchive = 0;}
			}
		}
		$jsonResponse['canUnArchive'] = $canUnArchive;
		$jsonResponse['default_customer'] = intval($default_customer);
		
		$customersObj = $this->db->query("SELECT * FROM customers WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man", array('customers_id'=>$customers_id),1);
		if($customersObj){
			$customersarray = $customersObj->fetch(PDO::FETCH_OBJ);
			
			$customers_id = $customersarray->customers_id;
			
			$company = stripslashes(trim((string) $customersarray->company));
			$jsonResponse['company'] = $company;
			$jsonResponse['customers_publish'] = $customersarray->customers_publish;
			$jsonResponse['allowed'] = $_SESSION["allowed"];
			
			$name = stripslashes(trim($customersarray->first_name.' '.$customersarray->last_name));
			$jsonResponse['name'] = $name;
			
			$email = $customersarray->email;
			$jsonResponse['email'] = $email;
			
			$contact_no = $customersarray->contact_no;
			$secondary_phone = $customersarray->secondary_phone;
			$jsonResponse['contact_no'] = $contact_no;			
			$jsonResponse['secondary_phone'] = $secondary_phone;
			
			$smsContact_no = $contact_no;
			$smsSecondary_phone = $secondary_phone;

			$bulkSMSCountryCode = '';
			if(!empty($contact_no) || !empty($secondary_phone)){
				$varObjSms = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'sms_messaging' AND value !=''", array());
				if($varObjSms){
					$value = $varObjSms->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$leadingZeros = 0;
						$value = unserialize($value);
						if(array_key_exists('bulkSMSCountryCode', $value)){
							$bulkSMSCountryCode = '+'.$value['bulkSMSCountryCode'];
						}
						if(array_key_exists('leadingZeros', $value)){
							$leadingZeros = $value['leadingZeros'];
						}
						if($leadingZeros>0){
							$smsContact_no = ltrim((string) $smsContact_no, '0');
							$smsSecondary_phone = ltrim((string) $smsSecondary_phone, '0');
						}
						if(strlen($bulkSMSCountryCode)>1){
							$withoutPlusCountryCode = ltrim((string) $bulkSMSCountryCode, '0');
							$smsContact_no = ltrim((string) $smsContact_no, $withoutPlusCountryCode);
							$smsContact_no = ltrim((string) $smsContact_no, $bulkSMSCountryCode);
							$smsSecondary_phone = ltrim((string) $smsSecondary_phone, $withoutPlusCountryCode);
							$smsSecondary_phone = ltrim((string) $smsSecondary_phone, $bulkSMSCountryCode);
						}
						$smsContact_no = $bulkSMSCountryCode.$smsContact_no;
						$smsSecondary_phone = $bulkSMSCountryCode.$smsSecondary_phone;					
					}
				}
			}
			$jsonResponse['bulkSMSCountryCode'] = $bulkSMSCountryCode;
			$jsonResponse['smsContact_no'] = $smsContact_no;				
			$jsonResponse['smsSecondary_phone'] = $smsSecondary_phone;
						
			$address = '';
			$shipping_address_one = $customersarray->shipping_address_one;
			if($shipping_address_one !=''){
				$address .= $shipping_address_one;
			}
			$shipping_address_two = $customersarray->shipping_address_two;
			if($shipping_address_two !=''){
				if($address != ''){$address .= '<br />';}
				$address .= $shipping_address_two;
			}
			$shipping_city = $customersarray->shipping_city;
			if($shipping_city !=''){
				if($address != ''){$address .= '<br />';}
				$address .= $shipping_city;
			}
			$shipping_state = $customersarray->shipping_state;
			if($shipping_state !=''){
				if($address != ''){$address .= ' ';}
				$address .= $shipping_state;
			}
			$shipping_zip = $customersarray->shipping_zip;
			if($shipping_zip !=''){
				if($address != ''){$address .= ' ';}
				$address .= $shipping_zip;
			}
			$shipping_country = $customersarray->shipping_country;
			if($shipping_country !='' || $shipping_country !='0'){
				if($address != ''){$address .= '<br />';}
				$address .= $shipping_country;
			}
			$jsonResponse['address'] = $address;
			
			$customers_publish = $customersarray->customers_publish;
			
			$Common = new Common($this->db);
			$cusDataInfo = $Common->customViewInfo('customers', $customersarray->custom_data);
			$jsonResponse['customFields'] = $cusDataInfo[0];
			$jsonResponse['viewCustomInfo'] = $cusDataInfo[1];	

			$Sql = "SELECT activity_feed_title AS afTitle FROM activity_feed 
					WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link LIKE CONCAT('/Customers/view/', :customers_id)  
				UNION ALL SELECT 'Customer Created' AS afTitle FROM customers 
					WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man 
				UNION ALL SELECT 'Repair Created' AS afTitle FROM repairs 
					WHERE accounts_id = $accounts_id AND customer_id = :customers_id 
				UNION ALL SELECT (Case When pos_type = 'Order' and order_status = 1 Then 'Order Created' 
					Else 'Sales Invoice Created' End) AS afTitle FROM pos 
					WHERE accounts_id = $accounts_id AND customer_id = :customers_id AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2) OR (pos_type = 'Order' AND order_status = 1)) 
				UNION ALL 
					SELECT 'Track Edits' AS afTitle FROM track_edits 
					WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id 
				UNION ALL SELECT 'Notes Created' AS afTitle FROM notes 
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
			$jsonResponse['login'] = 'Customers/lists/';
		}
		
		return json_encode($jsonResponse);
	}
	
	private function filterHAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$customers_id = $this->customers_id;
		$shistory_type = $this->history_type;
		
		$bindData = array();
		$bindData['customers_id'] = $customers_id;
		$totalRows = 0;
		$actFeedTitleArray = array();
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Customer Created')==0){
				$filterSql = "SELECT COUNT(customers_id) AS totalrows FROM customers 
						WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man";
			}
			elseif(strcmp($shistory_type, 'Repair Created')==0){
				$filterSql = "SELECT COUNT(repairs_id) AS totalrows FROM repairs 
						WHERE accounts_id = $accounts_id AND customer_id = :customers_id";
			}
			elseif(in_array($shistory_type, array('Order Created', 'Sales Invoice Created'))){
				$filterSql = "SELECT COUNT(pos_id) AS totalrows FROM pos 
						WHERE accounts_id = $accounts_id AND customer_id = :customers_id AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2) OR (pos_type = 'Order' AND order_status = 1))";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'customers' AND table_id = :customers_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link LIKE CONCAT('/Customers/view/', :customers_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
					WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link LIKE CONCAT('/Customers/view/', :customers_id)  
				UNION ALL SELECT COUNT(customers_id) AS totalrows FROM customers 
					WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man 
				UNION ALL SELECT COUNT(repairs_id) AS totalrows FROM repairs 
					WHERE accounts_id = $accounts_id AND customer_id = :customers_id 
				UNION ALL SELECT COUNT(pos_id) AS totalrows FROM pos 
					WHERE accounts_id = $accounts_id AND customer_id = :customers_id AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2) OR (pos_type = 'Order' AND order_status = 1)) 
				UNION ALL 
					SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
					WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id 
				UNION ALL SELECT COUNT(notes_id) AS totalrows FROM notes 
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
		$customers_id = $this->customers_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		$bindData = array();
		$bindData['customers_id'] = $customers_id;
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Customer Created')==0){
				$filterSql = "SELECT 'customers' as tablename, created_on as tabledate, customers_id as table_id, 'Customer Created' as activity_feed_title FROM customers 
							WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man";
			}
			elseif(strcmp($shistory_type, 'Repair Created')==0){
				$filterSql = "SELECT 'repairs' as tablename, created_on as tabledate, repairs_id as table_id, 'Repair Created' as activity_feed_title FROM repairs 
							WHERE accounts_id = $accounts_id and customer_id = :customers_id";
			}
			elseif(in_array($shistory_type, array('Order Created', 'Sales Invoice Created'))){
				$filterSql = "SELECT 'pos' as tablename, created_on as tabledate, pos_id as table_id, 
							(Case When pos_type = 'Order' and order_status = 1 Then 'Order Created' 
							Else 'Sales Invoice Created' End) as activity_feed_title FROM pos 
							WHERE accounts_id = $accounts_id and customer_id = :customers_id AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2) OR (pos_type = 'Order' AND order_status = 1))";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'customers' AND table_id = :customers_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link LIKE CONCAT('/Customers/view/', :customers_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link LIKE CONCAT('/Customers/view/', :customers_id)  
						UNION ALL SELECT 'customers' as tablename, created_on as tabledate, customers_id as table_id, 'Customer Created' as activity_feed_title FROM customers 
							WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man 
						UNION ALL SELECT 'repairs' as tablename, created_on as tabledate, repairs_id as table_id, 'Repair Created' as activity_feed_title FROM repairs 
							WHERE accounts_id = $accounts_id and customer_id = :customers_id 
						UNION ALL SELECT 'pos' as tablename, created_on as tabledate, pos_id as table_id, 
							(Case When pos_type = 'Order' and order_status = 1 Then 'Order Created' 
							Else 'Sales Invoice Created' End) as activity_feed_title FROM pos 
							WHERE accounts_id = $accounts_id and customer_id = :customers_id AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2) OR (pos_type = 'Order' AND order_status = 1)) 
						UNION ALL SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id 
						UNION ALL SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes 
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
			foreach($query as $grpOneRow){
				$activity_feed_title = $grpOneRow['activity_feed_title'];
				$tablename = $grpOneRow['tablename'];
				$table_id = $grpOneRow['table_id'];
				
				$getHMoreInfo = $Activity_Feed->getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title);
				if(!empty($getHMoreInfo)){
					$tabledata[] = $getHMoreInfo;
				}
			}
		}
		
		return $tabledata;
    }
	
	public function AJget_CustomersPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$customers_id = intval($POST['customers_id']??0);
		$Common = new Common($this->db);
		$customersData = array();
		$customersData['login'] = '';
		$customersData['customers_id'] = 0;				
		$customersData['first_name'] = '';
		$customersData['last_name'] = '';
		$customersData['email'] = '';
		$customersData['offers_email'] = '';
		$customersData['company'] = '';		
		$customersData['contact_no'] = '';
		$customersData['fax'] = '';	
		$customer_type = '';
		$customersData['secondary_phone'] = '';		
		$customersData['shipping_address_one'] = '';
		$customersData['shipping_address_two'] = '';
		$customersData['shipping_city'] = '';
		$customersData['shipping_state'] = '';
		$customersData['shipping_zip'] = '';
		$customersData['shipping_country'] = '';
		$customersData['created_on'] = '';
		$customersData['last_updated'] = '';
		$customersData['accounts_id'] = '';
		$customersData['website'] = '';
		$customersData['alert_message'] = '';
		$custom_data = '';
		if($customers_id>0 && $prod_cat_man>0){
			$customersObj = $this->db->query("SELECT * FROM customers WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man", array('customers_id'=>$customers_id),1);
			if($customersObj){
				$customersRow = $customersObj->fetch(PDO::FETCH_OBJ);	

				$customersData['customers_id'] = $customers_id;
				$customersData['first_name'] = stripslashes(trim((string) $customersRow->first_name));
				$customersData['last_name'] = stripslashes(trim((string) $customersRow->last_name));
				$customersData['email'] = trim((string) $customersRow->email);
				$customersData['offers_email'] = intval($customersRow->offers_email);
				$customersData['company'] = stripslashes(trim((string) $customersRow->company));
				$customersData['contact_no'] = trim((string) $customersRow->contact_no);
				$customersData['secondary_phone'] = trim((string) $customersRow->secondary_phone);
				$customersData['fax'] = trim((string) $customersRow->fax);
				$customer_type = trim((string) $customersRow->customer_type);
				
				$customersData['shipping_address_one'] = stripslashes(trim((string) $customersRow->shipping_address_one));
				$customersData['shipping_address_two'] = stripslashes(trim((string) $customersRow->shipping_address_two));
				$customersData['shipping_city'] = stripslashes(trim((string) $customersRow->shipping_city));
				$customersData['shipping_state'] = stripslashes(trim((string) $customersRow->shipping_state));
				$customersData['shipping_zip'] = trim((string) $customersRow->shipping_zip);
				$customersData['shipping_country'] = stripslashes(trim((string) $customersRow->shipping_country));
				if($customersRow->shipping_country=='0'){
					$customersData['shipping_country'] = '';
				}
				$customersData['created_on'] = $customersRow->created_on;
				$customersData['last_updated'] = $customersRow->last_updated;
				$customersData['accounts_id'] = $customersRow->accounts_id;
				$customersData['website'] = stripslashes(trim((string) $customersRow->website));
				$customersData['alert_message'] = stripslashes(trim((string) $customersRow->alert_message));
				$custom_data = trim((string) $customersRow->custom_data);
			}
		}
		
		$custTypeOpts = array();
		if($prod_cat_man>0){
			$sql = "SELECT name FROM customer_type WHERE accounts_id = $prod_cat_man AND (customer_type_publish = 1 OR (name = :name AND customer_type_publish = 0)) ORDER BY name ASC";
			$query = $this->db->query($sql, array('name'=>$customer_type));
			if($query){
				while($onerow = $query->fetch(PDO::FETCH_OBJ)){
					$label = stripslashes(trim((string) $onerow->name));
					if(!empty($label))
						$custTypeOpts[$label] = '';
				}
				if(!empty($custTypeOpts)){
					ksort($custTypeOpts);
					$custTypeOpts = array_keys($custTypeOpts);
				}
			}
			
		}
		
		$customersData['customer_type'] = $customer_type;
		$customersData['custTypeOpts'] = $custTypeOpts;
		
		$customFieldsData = $Common->customFormFields('customers', $custom_data);		
		$customersData['customFieldsData'] = $customFieldsData;
		$customersData['customFields'] = count($customFieldsData);		
		
		return json_encode($customersData);
	}
	
	public function AJsave_Customers(){
		$POST = $_POST;
		$savemsg = $returnStr = $str = '';
		$crlimit = 0;
		$Common = new Common($this->db);
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$customers_id = intval($POST['customers_id']??0);
		
		$first_name = addslashes(trim((string) array_key_exists('first_name', $POST) ? $POST['first_name'] : ''));
		$first_name = $this->db->checkCharLen('customers.first_name', $first_name);
		
		$last_name = addslashes(trim((string) array_key_exists('last_name', $POST) ? $POST['last_name'] : ''));
		$last_name = $this->db->checkCharLen('customers.last_name', $last_name);
		
		$bindData = $conditionarray = array();
		
		$conditionarray['first_name'] = $first_name;
		$conditionarray['last_name'] = $last_name;

		$email = addslashes(trim((string) array_key_exists('email', $POST) ? $POST['email'] : ''));
		$email = $this->db->checkCharLen('customers.email', $email);		
		$conditionarray['email'] = $email;

		$offers_email = intval($POST['offers_email']??0);
		$conditionarray['offers_email'] = $offers_email;

		$company = addslashes(trim((string) array_key_exists('company', $POST) ? $POST['company'] : ''));
		$conditionarray['company'] = $this->db->checkCharLen('customers.company', $company);
		
		$contact_no = addslashes(trim((string) array_key_exists('contact_no', $POST) ? $POST['contact_no'] : ''));
		$contact_no = $this->db->checkCharLen('customers.contact_no', $contact_no);
		$conditionarray['contact_no'] = $contact_no;
				
		$dupsql = "contact_no = :email";
		$bindData['email'] = $contact_no;
		if($contact_no ==''){
			$dupsql = "contact_no = :email";
			$bindData['email'] = $email;
		}
		
		$secondary_phone = addslashes(trim((string) array_key_exists('secondary_phone', $POST) ? $POST['secondary_phone'] : ''));
		$conditionarray['secondary_phone'] = $this->db->checkCharLen('customers.secondary_phone', $secondary_phone);
		
		$fax = addslashes(trim((string) array_key_exists('fax', $POST) ? $POST['fax'] : ''));
		$conditionarray['fax'] = $this->db->checkCharLen('customers.fax', $fax);
		
		$customer_type = addslashes(trim((string) array_key_exists('customer_type', $POST) ? $POST['customer_type'] : ''));
		$customer_type = $this->db->checkCharLen('customers.customer_type', $customer_type);
		
		$customer_type_name = addslashes(trim((string) array_key_exists('customer_type_name', $POST) ? $POST['customer_type_name'] : ''));
		$customer_type_name = $this->db->checkCharLen('customer_type.name', $customer_type_name);
		//===========================for customer_type==================//
		if($customer_type_name !=''){
			$customer_type_name = $this->db->checkCharLen('customer_type.name', $customer_type_name);
			$queryTypeObj = $this->db->query("SELECT name FROM customer_type WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name", array('name'=>strtoupper($customer_type_name)));
			if($queryTypeObj){
				while($oneRowCusTyp = $queryTypeObj->fetch(PDO::FETCH_OBJ)){
					$customer_type = trim((string) $oneRowCusTyp->name);
				}		
			}				
			else{
				$customer_typeData=array('name' => $customer_type_name,
										'created_on' => date('Y-m-d H:i:s'),
										'accounts_id' => $prod_cat_man,
										'user_id' => $user_id);
				$this->db->insert('customer_type', $customer_typeData);
				$customer_type = $customer_type_name;
			}
		}
		
		$conditionarray['customer_type'] = $customer_type;			
		
		$shipping_address_one = addslashes(trim((string) array_key_exists('shipping_address_one', $POST) ? $POST['shipping_address_one'] : ''));
		$conditionarray['shipping_address_one'] = $this->db->checkCharLen('customers.shipping_address_one', $shipping_address_one);
		
		$shipping_address_two = addslashes(trim((string) array_key_exists('shipping_address_two', $POST) ? $POST['shipping_address_two'] : ''));
		$conditionarray['shipping_address_two'] = $this->db->checkCharLen('customers.shipping_address_two', $shipping_address_two);
		
		$shipping_city = addslashes(trim((string) array_key_exists('shipping_city', $POST) ? $POST['shipping_city'] : ''));
		$conditionarray['shipping_city'] = $this->db->checkCharLen('customers.shipping_city', $shipping_city);
		
		$shipping_state = addslashes(trim((string) array_key_exists('shipping_state', $POST) ? $POST['shipping_state'] : ''));
		$conditionarray['shipping_state'] = $this->db->checkCharLen('customers.shipping_state', $shipping_state);
		
		$shipping_zip = addslashes(trim((string) array_key_exists('shipping_zip', $POST) ? $POST['shipping_zip'] : ''));
		$conditionarray['shipping_zip'] = $this->db->checkCharLen('customers.shipping_zip', $shipping_zip);
		
		$shipping_country = addslashes(trim((string) array_key_exists('shipping_country', $POST) ? $POST['shipping_country'] : ''));
		$conditionarray['shipping_country'] = $this->db->checkCharLen('customers.shipping_country', $shipping_country);
		
		$website = addslashes(trim((string) array_key_exists('website', $POST) ? $POST['website'] : ''));
		$conditionarray['website'] = $this->db->checkCharLen('customers.website', $website);
		
		$alert_message = addslashes(trim((string) array_key_exists('alert_message', $POST) ? $POST['alert_message'] : ''));
		$conditionarray['alert_message'] = $alert_message;
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['custom_data'] = '';
		$totalrows = $customers_publish = 0;
		if($customers_id==0){			
			$conditionarray['accounts_id'] = $prod_cat_man;
			$conditionarray['user_id'] = $user_id;
			
			$custObj = $this->db->query("SELECT customers_id, customers_publish FROM customers WHERE accounts_id = $prod_cat_man AND $dupsql", $bindData);
			if($custObj){
				while($oneCustRow = $custObj->fetch(PDO::FETCH_OBJ)){
					$totalrows = $oneCustRow->customers_id;
					$customers_publish = $oneCustRow->customers_publish;
				}
			}
			if($totalrows>0){
				$savemsg = 'error';
				if($customers_publish>0){
					$returnStr = 'Name_Already_Exist';
				}
				else{
					$returnStr = 'Name_ExistInArchive';
				}
			}
			else{										
				$conditionarray['credit_limit'] = 0;
				$conditionarray['credit_days'] = 0;
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				
				$customers_id = $this->db->insert('customers', $conditionarray);
				if($customers_id){
					$custom_data = $Common->postCustomFormFields('customers', $customers_id);
					$this->db->update('customers', array('custom_data'=>$custom_data), $customers_id);

					$str = "$first_name $last_name";
					if($email !=''){
						$str .= " ($email)";
					}
					elseif($contact_no !=''){
						$str .= " ($contact_no)";
					}						
				}
				else{
					$savemsg = 'error';
					$returnStr .= 'errorOnAdding';
				}
			}
		}
		else{
			
			$conditionarray['custom_data'] = $Common->postCustomFormFields('customers', $customers_id);
			$bindData['customers_id'] = $customers_id;
			$custObj = $this->db->query("SELECT customers_id, customers_publish FROM customers WHERE accounts_id = $prod_cat_man AND $dupsql AND customers_id != :customers_id", $bindData);
			if($custObj){
				while($oneCustRow = $custObj->fetch(PDO::FETCH_OBJ)){
					$totalrows = $oneCustRow->customers_id;
					$customers_publish = $oneCustRow->customers_publish;
				}
			}
			if($totalrows>0){
				$savemsg = 'error';
				if($customers_publish>0){
					$returnStr = 'Name_Already_Exist';
				}
				else{
					$returnStr = 'Name_ExistInArchive';
				}
			}
			else{
				$custObj = $this->db->querypagination("SELECT * FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = $customers_id", array());
			
				$update = $this->db->update('customers', $conditionarray, $customers_id);
				if($update){
					if($custObj){						
						$crlimit = $custObj[0]['credit_limit'];
						$changed = array();
						unset($conditionarray['last_updated']);
						foreach($conditionarray as $fieldName=>$fieldValue){
							$prevFieldVal = $custObj[0][$fieldName];
							if($prevFieldVal != $fieldValue){
								if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
								if($fieldValue=='1000-01-01'){$fieldValue = '';}
								elseif($fieldName=='custom_data'){
									
									$custom_data1 = $custom_data2 = array();
									if(!empty($prevFieldVal)){$custom_data1 = unserialize($prevFieldVal);}
									if(!empty($fieldValue)){$custom_data2 = unserialize($fieldValue);}
									if(!empty($custom_data1) || !empty($custom_data2)){
										
										if(!empty($custom_data1) && !empty($custom_data2)){
											$mergeData = array_merge_recursive($custom_data1, $custom_data2);
												
											foreach($mergeData as $mKey=>$mValue){
												if(array_key_exists($mKey, $custom_data1) && array_key_exists($mKey, $custom_data2)){
													$twoData = $mValue;
													
													if($mValue[0] ==$mValue[1]){
														unset($custom_data1[$mKey]);
														unset($custom_data2[$mKey]);
													}
												}
											}
										}
										elseif(!empty($custom_data1)){
											foreach($custom_data1 as $mKey=>$mValue){
												if($custom_data1[$mKey] == ''){
													unset($custom_data1[$mKey]);
												}
											}
										}
										elseif(!empty($custom_data2)){
											foreach($custom_data2 as $mKey=>$mValue){
												if($custom_data2[$mKey] == ''){
													unset($custom_data2[$mKey]);
												}
											}
										}
												
										if(!empty($custom_data1)){$prevFieldVal = serialize($custom_data1);}
										else{$prevFieldVal = '';}
										if(!empty($custom_data2)){$fieldValue = serialize($custom_data2);}
										else{$fieldValue = '';}									
									}
								}
								$changed[$fieldName] = array($prevFieldVal, $fieldValue);
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
				}
				
				$savemsg = 'update-success';					
			}
		}
		
		$array = array( 'login'=>'',
						'customers_id'=>$customers_id,
						'email'=>$email,
						'contact_no'=>$contact_no,
						'savemsg'=>$savemsg,
						'returnStr'=>$returnStr,
						'crlimit'=>$crlimit,
						'resulthtml'=>$str);
		return json_encode($array);
	}
			
	public function AJget_propertiesPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$propertiesData = array();
		$propertiesData['login'] = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$properties_id = intval($POST['properties_id']??0);
		$customers_id = intval($POST['customers_id']??0);
		
		$brand_model_id = 0;
		$imei_or_serial_no = $brand = $model = $more_details = '';
		
		if($properties_id>0 && $prod_cat_man>0){
			$propertiesObj = $this->db->query("SELECT * FROM properties WHERE properties_id = :properties_id AND accounts_id = $prod_cat_man", array('properties_id'=>$properties_id),1);
			if($propertiesObj){
				$propertiesRow = $propertiesObj->fetch(PDO::FETCH_OBJ);	
				
				$properties_id = $propertiesRow->properties_id;
				$customers_id = $propertiesRow->customers_id;
				$imei_or_serial_no = stripslashes(trim((string) $propertiesRow->imei_or_serial_no));
				$brand_model_id = trim((string) $propertiesRow->brand_model_id);
				if($brand_model_id>0){
					$brandModelObj = $this->db->query("SELECT brand, model FROM brand_model WHERE brand_model_id = $brand_model_id AND accounts_id = $prod_cat_man", array());
					if($brandModelObj){
						$brandModelRow = $brandModelObj->fetch(PDO::FETCH_OBJ);	
						$brand = trim((string) $brandModelRow->brand);
						$model = trim((string) $brandModelRow->model);
					}
				}
				$more_details = stripslashes(trim((string) $propertiesRow->more_details));
			}
			else{
				$properties_id = 0;
			}
		}
		$propertiesData['properties_id'] = $properties_id;		
		$propertiesData['customers_id'] = $customers_id;
		$propertiesData['imei_or_serial_no'] = $imei_or_serial_no;		
		$propertiesData['brand_model_id'] = $brand_model_id;
		$propertiesData['brand'] = $brand;
		$propertiesData['model'] = $model;
		
		$brandOpts = $modelOpts = array();
		if($prod_cat_man>0){
			$brandModelData = array();
			if(!empty($brand) && !empty($model)){
				$brandModelData[trim((string) $brand)][] = trim((string) $model);
			}
			$brandModelSql = "SELECT brand, model FROM brand_model WHERE accounts_id = $prod_cat_man AND brand_model_publish = 1 ORDER BY brand ASC, model ASC";
			$brandModelObj = $this->db->query($brandModelSql, array());
			if($brandModelObj){
				while($oneRow = $brandModelObj->fetch(PDO::FETCH_OBJ)){
					$brandModelData[trim((string) $oneRow->brand)][] = trim((string) $oneRow->model);
				}
			}
			
			if(!empty($brandModelData)){
				$brandData = array_keys($brandModelData);
				foreach($brandData as $oneBrand){
					if($oneBrand !=''){
						$brandOpts[strtoupper($oneBrand)] = stripslashes($oneBrand);
					}
				}
				$newBrandOpts = array();
				foreach($brandOpts as $ky=>$value){
					$newBrandOpts[] = $value;
				}
				$brandOpts = $newBrandOpts;
				if($brand !=''){
					$modelData = $brandModelData[$brand];
					sort($modelData);
					foreach($modelData as $oneModel){
						if($oneModel !=''){
							$modelOpts[stripslashes($oneModel)] = '';
						}
					}
				}
			}
		}
		if(!empty($modelOpts)){
			ksort($modelOpts);
			$modelOpts = array_keys($modelOpts);
		}
		$propertiesData['brandOpts'] = $brandOpts;
		$propertiesData['modelOpts'] = $modelOpts;
		$propertiesData['more_details'] = $more_details;
		$cnanbm = 0;
		if(!empty($_SESSION["allowed"]) && array_key_exists(2, $_SESSION["allowed"]) && in_array('cnanbm', $_SESSION["allowed"][2])) {
			$cnanbm = 1;
		}
		$propertiesData['cnanbm'] = $cnanbm;
		
		return json_encode($propertiesData);
	}
	
	public function AJsave_properties(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = $savemsg = '';
		$propertyInfo = array();
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$properties_id = intval($POST['properties_id']??0);
		$repairs_id = intval($POST['repairs_id']??0);
		$customers_id = intval($POST['customers_id']??0);
		$imei_or_serial_no = trim((string) $POST['imei_or_serial_no']??'');
		$imei_or_serial_no = $this->db->checkCharLen('properties.imei_or_serial_no', $imei_or_serial_no);
		
		$brand = trim((string) isset($POST['brand']) ? $POST['brand'] : '');
		$brand = $this->db->checkCharLen('brand_model.brand', $brand);
		$Common = new Common($this->db);
		$brand = $Common->checkAndReturnBrand($brand);
		
		$brand_name = trim((string) $POST['brand_name']??'');
		if(!empty($brand_name)){
			$brand = $this->db->checkCharLen('brand_model.brand', $brand_name);
		}
		$model = trim((string) isset($POST['model']) ? $POST['model'] : '');
		$model = $this->db->checkCharLen('brand_model.model', $model);
		$model_name = trim((string) $POST['model_name']??'');
		if(!empty($model_name)){
			$model = $this->db->checkCharLen('brand_model.model', $model_name);
		}
		
		$more_details = trim((string) $POST['more_details']??'');
		$more_details = $this->db->checkCharLen('properties.more_details', $more_details);
		$propOpts = array();
		$propertyInfo['imei_or_serial_no'] = $imei_or_serial_no;
		$propertyInfo['brand'] = $brand;
		$propertyInfo['model'] = $model;
		$propertyInfo['more_details'] = $more_details;
		$propObj = false;
		if($properties_id>0){
			$propObj = $this->db->querypagination("SELECT * FROM properties WHERE properties_id = $properties_id", array());
		}
		//===========================for Properties==================//
		if(!empty($brand) && !empty($model) && $customers_id>0){
			$brand_model_id = 0;				
			$brandModelObj = $this->db->query("SELECT brand_model_id FROM brand_model WHERE accounts_id = $prod_cat_man AND UPPER(brand) = :brand AND UPPER(model) = :model", array('brand'=>strtoupper($brand), 'model'=>strtoupper($model)));
			if($brandModelObj){
				$brand_model_id = $brandModelObj->fetch(PDO::FETCH_OBJ)->brand_model_id;
			}
			
			if($brand_model_id==0){
				$brandModelData = array();
				$brandModelData['accounts_id'] = $prod_cat_man;			
				$brandModelData['user_id'] = $_SESSION["user_id"];
				$brandModelData['created_on'] = date('Y-m-d H:i:s');
				$brandModelData['last_updated'] = date('Y-m-d H:i:s');
				$brandModelData['brand'] = $brand;
				$brandModelData['model'] = $model;
				$brand_model_id = $this->db->insert('brand_model', $brandModelData);				
			}
			
			$addiStr = "";
			$bindData = array();
			if($properties_id>0){
				$addiStr = " AND properties_id != :properties_id";
				$bindData['properties_id'] = $properties_id;
			}
			$bindData['customers_id'] = $customers_id;
			$bindData['imei_or_serial_no'] = $imei_or_serial_no;
			$bindData['more_details'] = $more_details;
			$propertiesCount = 0;
			$propertiesObj = $this->db->query("SELECT properties_id, properties_publish FROM properties WHERE accounts_id = $prod_cat_man AND customers_id = :customers_id AND imei_or_serial_no = :imei_or_serial_no AND brand_model_id = $brand_model_id AND more_details = :more_details $addiStr", $bindData);
			if($propertiesObj){
				$pOneRow = $propertiesObj->fetch(PDO::FETCH_OBJ);
				$propertiesCount = $properties_id = $pOneRow->properties_id;
				$properties_publish = $pOneRow->properties_publish;
				if($properties_publish==0){
					$update = $this->db->update('properties', array('properties_publish'=>1), $properties_id);
					if($update){
						$note_for = $this->db->checkCharLen('notes.note_for', 'customers');
						$noteData=array('table_id'=> $customers_id,
										'note_for'=> $note_for,
										'created_on'=> date('Y-m-d H:i:s'),
										'last_updated'=> date('Y-m-d H:i:s'),
										'accounts_id'=> $_SESSION["accounts_id"],
										'user_id'=> $_SESSION["user_id"],
										'note'=> $this->db->translate('Property was edited')." $brand $model $imei_or_serial_no $more_details",
										'publics'=>0);
						$notes_id = $this->db->insert('notes', $noteData);						
					}
				}
			}
			
			if($propertiesCount>0){
				$savemsg = 'error';
				$returnStr = 'Name_Already_Exist';
			}
			
			$imei_or_serial_no = $this->db->checkCharLen('properties.imei_or_serial_no', $imei_or_serial_no);
			$more_details = $this->db->checkCharLen('properties.more_details', $more_details);
			
			$conditionarray = array();
			$conditionarray['accounts_id'] = $prod_cat_man;			
			$conditionarray['user_id'] = $_SESSION["user_id"];
			$conditionarray['last_updated'] = date('Y-m-d H:i:s');
			$conditionarray['customers_id'] = $customers_id;
			$conditionarray['imei_or_serial_no'] = $imei_or_serial_no;
			$conditionarray['brand_model_id'] = $brand_model_id;
			$conditionarray['more_details'] = $more_details;					
			
			if($properties_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$properties_id = $this->db->insert('properties', $conditionarray);
				if($properties_id){}
				else{
					$savemsg = 'error';
					$returnStr .= 'errorOnAdding';
				}
			}
			else{
				
				$update = $this->db->update('properties', $conditionarray, $properties_id);
				if($update){
					if($propObj){
						$changed = array();
						unset($conditionarray['accounts_id']);
						unset($conditionarray['user_id']);
						unset($conditionarray['last_updated']);

						foreach($conditionarray as $fieldName=>$fieldValue){
							$prevFieldVal = $propObj[0][$fieldName];
							if($prevFieldVal != $fieldValue){
								if($fieldName=='brand_model_id'){
									if($prevFieldVal>0){$prevFieldVal = $Common->getOneRowFields('brand_model', array('brand_model_id'=>$prevFieldVal), array('brand', 'model'));}
									else{$prevFieldVal = '';}

									if($fieldValue>0){$fieldValue = $Common->getOneRowFields('brand_model', array('brand_model_id'=>$fieldValue), array('brand', 'model'));}
									else{$fieldValue = '';}
								}
								elseif($fieldName=='customers_id'){
									if($prevFieldVal>0){$prevFieldVal = $Common->getOneRowFields('customers', array('customers_id'=>$prevFieldVal), array('company', 'first_name', 'last_name'));}
									else{$prevFieldVal = '';}

									if($fieldValue>0){$fieldValue = $Common->getOneRowFields('customers', array('customers_id'=>$fieldValue), array('company', 'first_name', 'last_name'));}
									else{$fieldValue = '';}
								}
								$changed[$fieldName] = array($prevFieldVal, $fieldValue);
							}
						}

						if(!empty($changed)){
							$record_for='customers';
							$record_id = $customers_id;
							if($repairs_id>0){
								$record_for='repairs';
								$record_id = $repairs_id;
							}
							$moreInfo = array();
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
				}
				else{
					$savemsg = 'error';
					$returnStr .= 'errorOnEditing';
				}
			}
			
			if($repairs_id>0){
				$this->db->update('repairs', array('properties_id'=>$properties_id), $repairs_id);
			}
			
			if($savemsg==''){
				if($customers_id>0){
					$cPSql = "SELECT p.properties_id, bm.brand, bm.model, p.more_details, p.imei_or_serial_no FROM properties p, brand_model bm WHERE p.accounts_id = $prod_cat_man AND p.customers_id = :customers_id AND p.brand_model_id = bm.brand_model_id AND p.properties_publish = 1 GROUP BY p.properties_id ORDER BY bm.brand ASC, bm.model ASC, p.more_details ASC, p.imei_or_serial_no ASC";
					$cPObj = $this->db->query($cPSql, array('customers_id'=>$customers_id),1);
					if($cPObj){
						while($oneRow = $cPObj->fetch(PDO::FETCH_OBJ)){
							$optionLabel = stripslashes(trim("$oneRow->brand $oneRow->model $oneRow->more_details $oneRow->imei_or_serial_no"));
							if($optionLabel !=''){
								$propOpts[$oneRow->properties_id] = $optionLabel;
							}
						}
					}
				}
			}
		}
		else{
			$savemsg = 'error';
		}	
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg, 'propOpts'=>$propOpts, 'properties_id'=>$properties_id, 'propertyInfo'=>$propertyInfo));
	}
	
	public function AJget_brandOpt(){
	
		$returnStr = array();
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$bMSql = "SELECT brand FROM brand_model WHERE accounts_id = $prod_cat_man AND brand_model_publish = 1 GROUP BY brand ORDER BY brand ASC";
		$bMObj = $this->db->query($bMSql, array());
		if($bMObj){
			while($oneModelRow = $bMObj->fetch(PDO::FETCH_OBJ)){
				$oBrand = stripslashes(trim((string) $oneModelRow->brand));
				if($oBrand !=''){
					$returnStr[$oBrand] = '';
				}
			}
			$returnStr = array_keys($returnStr);
		}			
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJget_modelOpt(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$modelOpts = array();
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$brand = trim((string) $POST['brand']??'');
	
		if($brand !=''){
			$bMSql = "SELECT model FROM brand_model WHERE accounts_id = $prod_cat_man AND brand = :brand AND brand_model_publish = 1 GROUP BY model ORDER BY model ASC";
			$bMObj = $this->db->query($bMSql, array('brand'=>$brand));
			if($bMObj){
				while($oneModelRow = $bMObj->fetch(PDO::FETCH_OBJ)){
					$oModel = stripslashes(trim((string) $oneModelRow->model));
					if($oModel !=''){
						$modelOpts[$oModel] = '';
					}
				}
				$modelOpts = array_keys($modelOpts);
			}
		}
		
		return json_encode(array('login'=>'', 'modelOpts'=>$modelOpts));
	}
	
	public function sendEmail(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$email_address = addslashes($POST['smstophone']??'');
		if($email_address =='' || is_null($email_address)){
			$returnStr = $_Not_Send_Mail;
		}
		else{
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			$customer_service_email = '';
			$accObj = $this->db->query("SELECT customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accObj){
				$customer_service_email = $accObj->fetch(PDO::FETCH_OBJ)->customer_service_email;
			}
			if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
			
			$fromName = stripslashes($POST['smsfromname']??'');
			$subject = stripslashes($POST['subject']??'');
			if($subject ==''){$subject = "Email from $fromName";}
			$description = nl2br(stripslashes($POST['smsmessage']??''));
		
			$mail_body = "<p>$description</p>";
						
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $this->db->supportEmail('Host');
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = $this->db->supportEmail('Username');
			$mail->Password = $this->db->supportEmail('Password');
			
			$mail->addReplyTo($customer_service_email, $fromName);
			$mail->setFrom($customer_service_email, $fromName);
			$mail->clearAddresses();
			$mail->addAddress($email_address, '');
			$mail->Subject = $subject;
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			//Build a simple message body
			$mail->Body = $mail_body;
			
			if($mail->send()){
				$returnStr = 'sent';
			}
			else{
				$returnStr = "Sorry! Could not send mail. Try again later.";
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}

	public function AJmergeCustomers(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = $savemsg = '';
		$id = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$customers_id = intval($POST['fromcustomers_id']??0);
		$tocustomers_id = intval($POST['tocustomers_id']??0);
		$fromCustObj = $this->db->query("SELECT * FROM customers WHERE customers_id = :customers_id", array('customers_id'=>$customers_id), 1);
		if($fromCustObj){
			$fromCustRow = $fromCustObj->fetch(PDO::FETCH_OBJ);
			$toCustObj = $this->db->query("SELECT * FROM customers WHERE customers_id = :customers_id", array('customers_id'=>$tocustomers_id), 1);
			if($toCustObj){
				$toCustRow = $toCustObj->fetch(PDO::FETCH_OBJ);
				
				$updateData = array();
				if(!empty($fromCustRow->last_name) && empty($toCustRow->last_name)){
					$updateData['last_name'] = $fromCustRow->last_name;
				}
				if(!empty($fromCustRow->email) && empty($toCustRow->email)){
					$updateData['email'] = $fromCustRow->email;
				}
				if(!empty($fromCustRow->company) && empty($toCustRow->company)){
					$updateData['company'] = $fromCustRow->company;
				}
				if(!empty($fromCustRow->contact_no)){
					if(empty($toCustRow->contact_no)){
						$updateData['contact_no'] = $fromCustRow->contact_no;
					}
					elseif(empty($toCustRow->secondary_phone)){
						$updateData['contact_no'] = $fromCustRow->contact_no;
					}
				}
				if(!empty($fromCustRow->custom_data) && empty($toCustRow->custom_data)){
					$updateData['custom_data'] = $fromCustRow->custom_data;
				}
				if(!empty($updateData)){
					$this->db->update('customers', $updateData, $tocustomers_id);
				}
				$update = $this->db->update('customers', array('customers_publish'=>0), $customers_id);
				if($update){
					$id = $customers_id;
					$savemsg = 'Success';
					$filterSql = "SELECT activity_feed_id FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'customers' AND activity_feed_link LIKE CONCAT('/Customers/view/', :customers_id)";
					$tableObj = $this->db->query($filterSql, array('customers_id'=>$customers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$activity_feed_link = '/Customers/view/'.$tocustomers_id;
							$this->db->update('activity_feed', array('activity_feed_link'=>$activity_feed_link), $oneRow->activity_feed_id);
						}
					}
					
					$filterSql = "SELECT repairs_id FROM repairs WHERE accounts_id = $accounts_id AND customer_id = :customers_id";
					$tableObj = $this->db->query($filterSql, array('customers_id'=>$customers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('repairs', array('customer_id'=>$tocustomers_id), $oneRow->repairs_id);
						}
					}
					
					$filterSql = "SELECT pos_id FROM pos WHERE accounts_id = $accounts_id AND customer_id = :customers_id";
					$tableObj = $this->db->query($filterSql, array('customers_id'=>$customers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('pos', array('customer_id'=>$tocustomers_id), $oneRow->pos_id);
						}
					}
					
					$filterSql = "SELECT track_edits_id FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'customers' AND record_id = :customers_id ";
					$tableObj = $this->db->query($filterSql, array('customers_id'=>$customers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('track_edits', array('record_id'=>$tocustomers_id), $oneRow->track_edits_id);
						}
					}
					
					$filterSql = "SELECT notes_id FROM notes WHERE accounts_id = $accounts_id AND note_for = 'customers' AND table_id = :customers_id";
					$tableObj = $this->db->query($filterSql, array('customers_id'=>$customers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('notes', array('table_id'=>$tocustomers_id), $oneRow->notes_id);
						}
					}
					
					$filterSql = "SELECT properties_id FROM properties WHERE accounts_id = $prod_cat_man AND customers_id = :customers_id";
					$tableObj = $this->db->query($filterSql, array('customers_id'=>$customers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('properties', array('customers_id'=>$tocustomers_id), $oneRow->properties_id);
						}
					}
					
					$note_for = $this->db->checkCharLen('notes.note_for', 'customers');
					$noteData=array('table_id'=> $customers_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $_SESSION["accounts_id"],
									'user_id'=> $_SESSION["user_id"],
									'note'=> "This customer's all information has been merged to $toCustRow->first_name $toCustRow->last_name",
									'publics'=>0);
					$notes_id = $this->db->insert('notes', $noteData);
					
				}
			}			
		}
		return json_encode(array('login'=>'', 'savemsg'=>$savemsg, 'id'=>$id));
	}

    public function AJ_customers_archive(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnmsg = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$customers_id = intval($POST['customers_id']??0);
		$customer_name = $POST['customer_name']??'';

		if($customer_name !=''){
			if($customers_id>0){
				$sql = "SELECT company, first_name, last_name, email, contact_no FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = :customers_id ORDER BY customers_id ASC";
				$query = $this->db->query($sql, array('customers_id'=>$customers_id),1);
				if($query){
					$onecustomerrow = $query->fetch(PDO::FETCH_OBJ);
					$name = trim((string) stripslashes($onecustomerrow->company));
					$email = trim((string) stripslashes($onecustomerrow->email));
					$contact_no = trim((string) stripslashes($onecustomerrow->contact_no));
					$first_name = trim((string) stripslashes($onecustomerrow->first_name));
					if($name !=''){$name .= ', ';}
					$name .= $first_name;
					$last_name = trim((string) stripslashes($onecustomerrow->last_name));
					if($name !=''){$name .= ' ';}
					$name .= $last_name;

					if($onecustomerrow->email !=''){
						$name .= " ($onecustomerrow->email)";
					}
					elseif($onecustomerrow->contact_no !=''){
						$name .= " ($onecustomerrow->contact_no)";
					}

					if($customer_name !="$name"){
						$customers_id = 0;
					}
				}
			}

			if($customers_id==0 && $customer_name != ''){

				$autocustomer_name = $customer_name;
				$email = '';
				if(strpos($customer_name, ' (')!== false) {
					$scustomerexplode = explode(' (', $customer_name);
					if(count($scustomerexplode)>1){
						$autocustomer_name = trim((string) $scustomerexplode[0]);
						$email = str_replace(')', '', $scustomerexplode[1]);
					}
				}
				$bindData = array();
				$strextra = " AND TRIM(CONCAT_WS(' ', first_name, last_name)) LIKE CONCAT('%', :autocustomer_name, '%')";
				$bindData['autocustomer_name'] = $autocustomer_name;
				if($email !=''){
					$strextra .= " AND (email = :email OR contact_no = :email)";
					$bindData['email'] = $email;
				}
				$strextra .= " GROUP BY TRIM(CONCAT_WS(' ', first_name, last_name)), email";
				$sql = "SELECT customers_id FROM customers WHERE accounts_id = $prod_cat_man $strextra ORDER BY customers_id ASC";
				$query = $this->db->query($sql, $bindData);
				if($query){
					$customers_id = $query->fetch(PDO::FETCH_OBJ)->customers_id;
				}
			}
		}

		if($customers_id>0){
			$sql = "SELECT first_name, last_name, email FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = :customers_id AND customers_publish = 1 ORDER BY customers_id ASC";
			$query = $this->db->query($sql, array('customers_id'=>$customers_id),1);
			if($query){
				$onerow = $query->fetch(PDO::FETCH_OBJ);

				$autocustomer_name = stripslashes($onerow->first_name.' '.$onerow->last_name);

				if($onerow->email !='')
					$autocustomer_name .= " ($onerow->email)";

				$updatetable = $this->db->update('customers', array('customers_publish'=>0), $customers_id);
				if($updatetable){
					$note_for = $this->db->checkCharLen('notes.note_for', 'customers');
					$noteData=array('table_id'=> $customers_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $_SESSION["accounts_id"],
									'user_id'=> $_SESSION["user_id"],
									'note'=> $this->db->translate('Customers archived successfully.')." $autocustomer_name",
									'publics'=>0);
					$notes_id = $this->db->insert('notes', $noteData);
					
					$returnmsg = 'archive-success';
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnmsg));
    }

	//========================ASync========================//	
		
	public function AJgetHPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
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
	
    public function getCustomFields(){
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$customFields = 0;
		$customFieldNames = array();
		$cqueryObj = $this->db->query("SELECT field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers'", array());
		if($cqueryObj){
			while($oneCFRow = $cqueryObj->fetch(PDO::FETCH_OBJ)){
				$customFieldNames[] = $oneCFRow->field_name;
			}
			$customFields = count($customFieldNames);
		}

		$jsonResponse['customFields'] = $customFields;
		
		return json_encode($jsonResponse);
	}
	
	public function customerProperties(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$tableRows = array();
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$customers_id = intval($POST['customers_id']??0);
		if($customers_id>0){
			$cPSql = "SELECT p.properties_id, bm.brand, bm.model, p.more_details, p.imei_or_serial_no FROM properties p, brand_model bm WHERE p.accounts_id = $prod_cat_man AND p.customers_id = $customers_id AND p.brand_model_id = bm.brand_model_id AND p.properties_publish = 1 GROUP BY p.properties_id ORDER BY bm.brand ASC, bm.model ASC, p.more_details ASC, p.imei_or_serial_no ASC";
			$cPObj = $this->db->query($cPSql, array());
			if($cPObj){
				while($oneRow = $cPObj->fetch(PDO::FETCH_OBJ)){
					$brand = stripslashes(trim((string) $oneRow->brand));
					$model = stripslashes(trim((string) $oneRow->model));
					$more_details = stripslashes(trim((string) $oneRow->more_details));
					$imei_or_serial_no = stripslashes(trim((string) $oneRow->imei_or_serial_no));
					
					$tableRows[] = array($oneRow->properties_id, $brand, $model, $more_details, $imei_or_serial_no);
				}
			}
		}
		return json_encode(array('login'=>'', 'tableRows'=>$tableRows));
	}		
	
	//=========================CRM=========================//
	
	public function crm(){}
	
	public function AJ_crm_MoreInfo(){
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$cusTypeOpt = array();
		$strextra ="SELECT COALESCE(customer_type,'') AS customer_type FROM customers WHERE accounts_id = $prod_cat_man AND customers_publish = 1 GROUP BY customer_type ORDER BY customer_type ASC";
		$cusObj = $this->db->query($strextra, array());
		if($cusObj){
			while($oneRow = $cusObj->fetch(PDO::FETCH_OBJ)){
				if(!empty($oneRow->customer_type)){
					$cusTypeOpt[stripslashes(trim((string) $oneRow->customer_type))]  = '';
				}
			}
			ksort($cusTypeOpt);
			$cusTypeOpt = array_keys($cusTypeOpt);
		}
		$jsonResponse['cusTypeOpt'] = $cusTypeOpt;

		$allowed = 1;
		if(!empty($_SESSION["allowed"]) && !array_key_exists(4, $_SESSION["allowed"])) {
			$allowed = 0;
		}
		$jsonResponse['allowed'] = $allowed;		
		return json_encode($jsonResponse);
	}

	public function checkCRM(){
		
		$returnStr = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;

		$POST = json_decode(file_get_contents('php://input'), true);
		$date_range = $POST['date_range'];
		$date_invoiced_range = $POST['date_invoiced_range'];
		$customer_type = $POST['customer_type'];
		
		$startdate = $enddate = '';
		if($date_range !=''){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0]));
				$enddate = date('Y-m-d', strtotime($date_rangearray[1]));
			}
		}
		$irstartdate = $irenddate = '';
		if($date_invoiced_range !=''){
			$irdate_rangearray = explode(' - ', $date_invoiced_range);
			if(is_array($irdate_rangearray) && count($irdate_rangearray)>1){
				$irstartdate = date('Y-m-d', strtotime($irdate_rangearray[0]));
				$irenddate = date('Y-m-d', strtotime($irdate_rangearray[1]));
			}
		}
		
		$strextra = " AND c.email !='' AND c.offers_email = 1";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (SUBSTR(c.created_on,1,10) BETWEEN :startdate AND :enddate)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}
		if($customer_type !='All'){
			$strextra .= " AND c.customer_type = :customer_type";
			$bindData['customer_type'] = $customer_type;
		}
		if($irstartdate !='' && $irenddate !=''){
			$sql= "SELECT c.customers_id FROM customers c, pos WHERE pos.accounts_id = $accounts_id AND c.customers_publish = 1 AND (SUBSTR(pos.sales_datetime,1,10) BETWEEN :irstartdate AND :irenddate) $strextra AND pos.customer_id = c.customers_id";
			$bindData['irstartdate'] = $irstartdate;
			$bindData['irenddate'] = $irenddate;
		}
		else{
			$sql = "SELECT c.customers_id FROM customers c WHERE c.accounts_id = $prod_cat_man and c.customers_publish = 1 $strextra";
		}
		$sql .= " GROUP BY c.customers_id";
		$queryObj = $this->db->query($sql, $bindData);
		if($queryObj){
			$returnStr = $queryObj->rowCount();						
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}	

	public function sendCRM(){
	
		$returnStr = '';
		$count = '';
	
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$POST = json_decode(file_get_contents('php://input'), true);

		$date_range = $POST['date_range']??'';
		$date_invoiced_range = $POST['date_invoiced_range']??'';
		$customer_type = $POST['customer_type']??'All';
		$subject = $POST['subject']??'';
		$subject = stripslashes(trim((string) $subject));
		$mailbody = $POST['mailbody']??'';
		$mailbody = nl2br(stripslashes(trim((string) $mailbody)));
		
		$startdate = $enddate = $irstartdate = $irenddate = '';
		if(!empty($date_range)){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0]));
				$enddate = date('Y-m-d', strtotime($date_rangearray[1]));
			}
		}
		
		if(!empty($date_invoiced_range)){
			$irdate_rangearray = explode(' - ', $date_invoiced_range);
			if(is_array($irdate_rangearray) && count($irdate_rangearray)>1){
				$irstartdate = date('Y-m-d', strtotime($irdate_rangearray[0]));
				$irenddate = date('Y-m-d', strtotime($irdate_rangearray[1]));
			}
		}
		
		$strextra = " AND c.email !='' AND c.offers_email = 1";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND (SUBSTR(c.created_on,1,10) BETWEEN :startdate AND :enddate)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}
		if($customer_type !='All'){
			$strextra .= " AND c.customer_type = :customer_type";
			$bindData['customer_type'] = $customer_type;
		}
		if($irstartdate !='' && $irenddate !=''){
			$sql= "SELECT c.first_name, c.last_name, c.email FROM customers c, pos WHERE c.accounts_id = $prod_cat_man and c.customers_publish = 1 AND (SUBSTR(pos.sales_datetime,1,10) BETWEEN :irstartdate AND :irenddate) $strextra AND pos.customer_id = c.customers_id";
			$bindData['irstartdate'] = $irstartdate;
			$bindData['irenddate'] = $irenddate;
		}
		else{
			$sql = "SELECT c.first_name, c.last_name, c.email FROM customers c WHERE c.accounts_id = $prod_cat_man and c.customers_publish = 1 $strextra";
		}
		
		$returnStr = 'noCustomer';
		$sql .= " GROUP BY c.customers_id";
				
		$customersObj = $this->db->query($sql, $bindData);
		if($customersObj){
			$company_subdomain = $customer_service_email = '';
			$accObj = $this->db->query("SELECT company_subdomain, customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accObj){
				$accRow = $accObj->fetch(PDO::FETCH_OBJ);
				$company_subdomain = $accRow->company_subdomain;
				$customer_service_email = $accRow->customer_service_email;
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
			$mail->setFrom($this->db->supportEmail('do_not_reply'), $this->db->translate('Admin of')." ".$_SESSION["company_name"]);
			$mail->Subject = $subject;
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			//Build a simple message body
			
			$i=0;
			$sentcount = 0;
			while($oneRow = $customersObj->fetch(PDO::FETCH_OBJ)){
				$i++;
				$customer_name = $oneRow->first_name.' '.$oneRow->last_name;
				$email = $oneRow->email;
				$mail->ClearAllRecipients();
				$mail->addAddress($email, $customer_name);
			
				$mail->Body = "<p>
								".$this->db->translate('Dear')." <i><strong>$customer_name</strong></i>,<br />
								$mailbody
							</p>";
				
				if ($mail->send()) {$sentcount++;}
				
				if($i%5==0){
					sleep(1);
				}
			}
			if($sentcount>0){
				$returnStr = "messageSent";
				$count = $sentcount;
			}
			else{
				$returnStr = "messageNotSent";
				$count = $i;
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'count'=>$count));
	}
	
}
?>