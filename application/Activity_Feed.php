<?php
class Activity_Feed{
	protected $db;
	private int $page, $totalRows, $puser_id;
	private string $activity_feed, $date_range;
	private array $pUserOpt, $actFeeTitOpt;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists($segment4name){}
	
    private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sactivity_feed = $this->activity_feed;
		$puser_id = $this->puser_id;
		$date_range = $this->date_range;
		
		$_SESSION["current_module"] = "Activity_Feed";
		$_SESSION["list_filters"] = array('sactivity_feed'=>$sactivity_feed, 'puser_id'=>$puser_id, 'date_range'=>$date_range);
		
		$startdate = $enddate = '';
		if($date_range !=''){
			$activity_feeddatearray = explode(' - ', $date_range);
			if(is_array($activity_feeddatearray) && count($activity_feeddatearray)>1){
				$startdate = date('Y-m-d',strtotime($activity_feeddatearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($activity_feeddatearray[1])).' 23:59:59';
			}
		}
		
		$userIdNames = array();
		$userObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id", array());
		if($userObj){
			while($userOneRow = $userObj->fetch(PDO::FETCH_OBJ)){
				$userIdNames[$userOneRow->user_id] = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
			}
		}
		
		$filterSql1 = $filterSql2 = $POfilterSql = $POSfilterSql = $paymentFilterSql = $loginFilterSql =  $trackFilterSql = "";
		$bindData = array();
		if($puser_id >0){
			$filterSql1 = " WHERE accounts_id = $prod_cat_man AND user_id = :user_id";
			$filterSql2 = $POfilterSql = $POSfilterSql = $loginFilterSql = $trackFilterSql = " WHERE accounts_id = $accounts_id AND user_id = :user_id";
			$paymentFilterSql = " WHERE user_id = :user_id";
			$bindData['user_id'] = $puser_id;
		}
		elseif(!empty($userIdNames)){
			$filterSql1 = " WHERE accounts_id = $prod_cat_man";
			$filterSql2 = $POfilterSql = $POSfilterSql = $loginFilterSql = $trackFilterSql = " WHERE accounts_id = $accounts_id";
			$paymentFilterSql = " WHERE user_id IN (".implode(',', array_keys($userIdNames)).")";
		}
		
		if($startdate !='' && $enddate !=''){
			$filterSql = " AND created_on BETWEEN :startdate AND :enddate";
			$filterSql1 .= $filterSql;
			$filterSql2 .= $filterSql;
			$POfilterSql .= " AND po_datetime BETWEEN :startdate AND :enddate";
			$POSfilterSql .= " AND sales_datetime BETWEEN :startdate AND :enddate";
			$paymentFilterSql .= " AND payment_datetime BETWEEN :startdate AND :enddate";
			$loginFilterSql .= " AND login_datetime BETWEEN :startdate AND :enddate";
			$trackFilterSql .= " AND created_on BETWEEN :startdate AND :enddate";
			
			$bindData['startdate']= $startdate;
			$bindData['enddate']= $enddate;
		}
		
		if($sactivity_feed !='All'){
			if(strcmp($sactivity_feed, $this->db->translate('Customer Created'))==0){
				$sqlquery = "SELECT COUNT(customers_id) AS totalrows FROM customers $filterSql1";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Customer Created'))."' as activity_feed_title FROM customers $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Product Created'))==0){
				$sqlquery = "SELECT COUNT(product_id) AS totalrows FROM product $filterSql1";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Product Created'))."' as activity_feed_title FROM product $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Supplier Created'))==0){
				$sqlquery = "SELECT COUNT(suppliers_id) AS totalrows FROM suppliers $filterSql1";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Supplier Created'))."' as activity_feed_title FROM suppliers $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Category Created'))==0){
				$sqlquery = "SELECT COUNT(category_id) AS totalrows FROM category $filterSql1";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Category Created'))."' as activity_feed_title FROM category $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('New Manufacturer Added'))==0){
				$sqlquery = "SELECT COUNTmanufacturer_id) AS totalrows FROM manufacturer $filterSql1";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('New Manufacturer Added'))."' as activity_feed_title FROM manufacturer $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('New Vendor Added'))==0){
				$sqlquery = "SELECT COUNT(vendors_id) AS totalrows FROM vendors $filterSql1";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('New Vendor Added'))."' as activity_feed_title FROM vendors $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('New Expense Type Added'))==0){
				$sqlquery = "SELECT COUNT(expense_type_id) AS totalrows FROM expense_type $filterSql1";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('New Expense Type Added'))."' as activity_feed_title FROM expense_type $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Tax Created'))==0){
				$sqlquery = "SELECT COUNT(taxes_id) AS totalrows FROM taxes $filterSql2";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Tax Created'))."' as activity_feed_title FROM taxes $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Purchase Order Created'))==0){
				$sqlquery = "SELECT COUNT(po_id) AS totalrows FROM po $POfilterSql";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Purchase Order Created'))."' as activity_feed_title FROM po $POfilterSql";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Repair Created'))==0){
				$sqlquery = "SELECT COUNT(repairs_id) AS totalrows FROM repairs $filterSql2";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Repair Created'))."' as activity_feed_title FROM repairs $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Sales Invoice Created'))==0){
				$sqlquery = "SELECT COUNT(pos_id) AS totalrows FROM pos $POSfilterSql AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2))";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Sales Invoice Created'))."' as activity_feed_title FROM pos $POSfilterSql AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2))";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Order Created'))==0){
				$sqlquery = "SELECT COUNT(pos_id) AS totalrows FROM pos $POSfilterSql AND pos_type = 'Order' AND order_status < 2";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Order Created'))."' as activity_feed_title FROM pos $POSfilterSql AND pos_type = 'Order' AND order_status < 2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Payment Receipt'))==0){
				$sqlquery = "SELECT COUNT(pos_payment_id) AS totalrows FROM pos_payment $paymentFilterSql";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Payment Receipt'))."' as activity_feed_title FROM pos_payment $paymentFilterSql";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('IMEI Created'))==0){
				$sqlquery = "SELECT COUNT(item_id) AS totalrows FROM item $filterSql2";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('IMEI Created'))."' as activity_feed_title FROM item $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('User Created'))==0){
				$sqlquery = "SELECT COUNT(user_id) AS totalrows FROM user $filterSql2";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('User Created'))."' as activity_feed_title FROM user $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Note Created'))==0){
				$sqlquery = "SELECT COUNT(notes_id) AS totalrows FROM notes $filterSql2";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Note Created'))."' as activity_feed_title FROM notes $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('User Logged In'))==0){
				$sqlquery = "SELECT COUNT(user_login_history_id) AS totalrows FROM user_login_history $loginFilterSql";
				$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('User Logged In'))."' as activity_feed_title FROM user_login_history $loginFilterSql";
			}
			elseif(strcmp($sactivity_feed, 'Track Edits')==0){
				$sqlquery = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits $trackFilterSql";
				$sqlquery2 = "SELECT user_id, 'Track Edits' as activity_feed_title FROM track_edits $trackFilterSql";
			}
			else{
				$sqlquery = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed $filterSql2 AND activity_feed_title = '$sactivity_feed'";
				$sqlquery2 = "SELECT user_id, activity_feed_title FROM activity_feed $filterSql2";
			}
			
		}
		else{
			$sqlquery = "SELECT COUNT(customers_id) AS totalrows FROM customers $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(product_id) AS totalrows FROM product $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(suppliers_id) AS totalrows FROM suppliers $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(category_id) AS totalrows FROM category $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(manufacturer_id) AS totalrows FROM manufacturer $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(vendors_id) AS totalrows FROM vendors $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(expense_type_id) AS totalrows FROM expense_type $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(taxes_id) AS totalrows FROM taxes $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(po_id) AS totalrows FROM po $POfilterSql";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(repairs_id) AS totalrows FROM repairs $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(pos_id) AS totalrows FROM pos $POSfilterSql AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2))";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(pos_id) AS totalrows FROM pos $POSfilterSql AND pos_type = 'Order' AND order_status < 2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(pos_payment_id) AS totalrows FROM pos_payment $paymentFilterSql";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(item_id) AS totalrows FROM item $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(user_id) AS totalrows FROM user $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(notes_id) AS totalrows FROM notes $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(user_login_history_id) AS totalrows FROM user_login_history $loginFilterSql";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits $trackFilterSql";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed $filterSql2";

			//===========================For Dropdown======================//
			$sqlquery2 = "SELECT user_id, '".addslashes($this->db->translate('Customer Created'))."' as activity_feed_title FROM customers $filterSql1";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Product Created'))."' as activity_feed_title FROM product $filterSql1";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Supplier Created'))."' as activity_feed_title FROM suppliers $filterSql1";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Category Created'))."' as activity_feed_title FROM category $filterSql1";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('New Manufacturer Added'))."' as activity_feed_title FROM manufacturer $filterSql1";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('New Vendor Added'))."' as activity_feed_title FROM vendors $filterSql1";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('New Expense Type Added'))."' as activity_feed_title FROM expense_type $filterSql1";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Tax Created'))."' as activity_feed_title FROM taxes $filterSql2";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Purchase Order Created'))."' as activity_feed_title FROM po $POfilterSql";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Repair Created'))."' as activity_feed_title FROM repairs $filterSql2";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Sales Invoice Created'))."' as activity_feed_title FROM pos $POSfilterSql AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2))";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Order Created'))."' as activity_feed_title FROM pos $POSfilterSql AND pos_type = 'Order' AND order_status < 2";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Payment Receipt'))."' as activity_feed_title FROM pos_payment $paymentFilterSql";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('IMEI Created'))."' as activity_feed_title FROM item $filterSql2";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('User Created'))."' as activity_feed_title FROM user $filterSql2";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('Note Created'))."' as activity_feed_title FROM notes $filterSql2";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, '".addslashes($this->db->translate('User Logged In'))."' as activity_feed_title FROM user_login_history $loginFilterSql";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, 'Track Edits' as activity_feed_title FROM track_edits $trackFilterSql";
			$sqlquery2 .= " UNION ALL ";
			$sqlquery2 .= "SELECT user_id, activity_feed_title FROM activity_feed $filterSql2";
		}
		$totalRows = 0;
		$tableObj = $this->db->query($sqlquery, $bindData);
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;
			}
		}
		$this->totalRows = $totalRows;
		
		$actFeeTitOpt = $pUserOpt = array();
		$sqlquery2 .= " GROUP BY user_id, activity_feed_title";
		$query = $this->db->query($sqlquery2, $bindData);
		if($query){
			$actFeeTitOpts = $pUserOpts = array();
			while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
				$actFeeTitOpts[$oneRow->activity_feed_title] = '';
				$pUserOpts[intval($oneRow->user_id)] = $userIdNames[$oneRow->user_id]??'';
			}
			
			ksort($actFeeTitOpts);		
			$actFeeTitOpt = array_keys($actFeeTitOpts);
			if(!empty($pUserOpts)){
				$userObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id IN (".implode(',', array_keys($pUserOpts)).") AND user_publish = 1 ORDER BY user_first_name ASC, user_last_name ASC", array());
				if($userObj){
					while($userOneRow = $userObj->fetch(PDO::FETCH_OBJ)){
						$pUserOpt[$userOneRow->user_id] = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
					}
				}
			}
		}

		$this->actFeeTitOpt = $actFeeTitOpt;
		$this->pUserOpt = $pUserOpt;
	}
	
    private function loadTableRows(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sactivity_feed = $this->activity_feed;
		$puser_id = $this->puser_id;
		$date_range = $this->date_range;

		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$startdate = $enddate = '';
		if($date_range !=''){
			$activity_feeddatearray = explode(' - ', $date_range);
			if(is_array($activity_feeddatearray) && count($activity_feeddatearray)>1){
				$startdate = date('Y-m-d',strtotime($activity_feeddatearray[0])).' 00:00:00';
				$enddate = date('Y-m-d',strtotime($activity_feeddatearray[1])).' 23:59:59';
			}
		}
		
		$userIdNames = array();
		$userObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id", array());
		if($userObj){
			while($userOneRow = $userObj->fetch(PDO::FETCH_OBJ)){
				$userIdNames[$userOneRow->user_id] = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
			}
		}
		
		$filterSql1 = $filterSql2 = $POfilterSql = $POSfilterSql = $paymentFilterSql = $loginFilterSql = $trackFilterSql = "";
		$bindData = array();
		if($puser_id >0){
			$filterSql1 = $filterSql2 = $POfilterSql = $POSfilterSql = $paymentFilterSql = $loginFilterSql = $trackFilterSql = " WHERE user_id = :user_id";
			$bindData['user_id'] = $puser_id;
		}
		elseif(!empty($userIdNames)){
			$filterSql1 = " WHERE accounts_id = $prod_cat_man";
			$filterSql2 = $POfilterSql = $POSfilterSql = $loginFilterSql = $trackFilterSql = " WHERE accounts_id = $accounts_id";
			$paymentFilterSql = " WHERE user_id IN (".implode(',', array_keys($userIdNames)).")";
		}
		
		if($startdate !='' && $enddate !=''){
			$filterSql = " AND created_on BETWEEN :startdate AND :enddate";
			$filterSql1 .= $filterSql;
			$filterSql2 .= $filterSql;
			$POfilterSql .= " AND po_datetime BETWEEN :startdate AND :enddate";
			$POSfilterSql .= " AND sales_datetime BETWEEN :startdate AND :enddate";
			$paymentFilterSql .= " AND payment_datetime BETWEEN :startdate AND :enddate";
			$loginFilterSql .= " AND login_datetime BETWEEN :startdate AND :enddate";
			$trackFilterSql .= " AND created_on BETWEEN :startdate AND :enddate";
			
			$bindData['startdate']= $startdate;
			$bindData['enddate']= $enddate;
		}			
		
		if($sactivity_feed !='All'){
			if(strcmp($sactivity_feed, $this->db->translate('Customer Created'))==0){
				$sqlquery = "SELECT 'customers' as tablename, created_on as tabledate, customers_id as table_id, '".addslashes($this->db->translate('Customer Created'))."' as activity_feed_title FROM customers $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Product Created'))==0){
				$sqlquery = "SELECT 'product' as tablename, created_on as tabledate, product_id as table_id, '".addslashes($this->db->translate('Product Created'))."' as activity_feed_title FROM product $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Supplier Created'))==0){
				$sqlquery = "SELECT 'suppliers' as tablename, created_on as tabledate, suppliers_id as table_id, '".addslashes($this->db->translate('Supplier Created'))."' as activity_feed_title FROM suppliers $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Category Created'))==0){
				$sqlquery = "SELECT 'category' as tablename, created_on as tabledate, category_id as table_id, '".addslashes($this->db->translate('Category Created'))."' as activity_feed_title FROM category $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('New Manufacturer Added'))==0){
				$sqlquery = "SELECT 'manufacturer' as tablename, created_on as tabledate, manufacturer_id as table_id, '".addslashes($this->db->translate('New Manufacturer Added'))."' as activity_feed_title FROM manufacturer $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('New Vendor Added'))==0){
				$sqlquery = "SELECT 'vendors' as tablename, created_on as tabledate, vendors_id as table_id, '".addslashes($this->db->translate('New Vendor Added'))."' as activity_feed_title FROM vendors $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('New Expense Type Added'))==0){
				$sqlquery = "SELECT 'expense_type' as tablename, created_on as tabledate, expense_type_id as table_id, '".addslashes($this->db->translate('New Expense Type Added'))."' as activity_feed_title FROM expense_type $filterSql1";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Tax Created'))==0){
				$sqlquery = "SELECT 'taxes' as tablename, created_on as tabledate, taxes_id as table_id, '".addslashes($this->db->translate('Tax Created'))."' as activity_feed_title FROM taxes $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Purchase Order Created'))==0){
				$sqlquery = "SELECT 'po' as tablename, po_datetime as tabledate, po_id as table_id, '".addslashes($this->db->translate('Purchase Order Created'))."' as activity_feed_title FROM po $POfilterSql";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Repair Created'))==0){
				$sqlquery = "SELECT 'repairs' as tablename, created_on as tabledate, repairs_id as table_id, '".addslashes($this->db->translate('Repair Created'))."' as activity_feed_title FROM repairs $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Sales Invoice Created'))==0){
				$sqlquery = "SELECT 'pos' as tablename, created_on as tabledate, pos_id as table_id, '".addslashes($this->db->translate('Sales Invoice Created'))."' as activity_feed_title FROM pos $POSfilterSql AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2))";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Order Created'))==0){
				$sqlquery = "SELECT 'pos' as tablename, created_on as tabledate, pos_id as table_id, '".addslashes($this->db->translate('Order Created'))."' as activity_feed_title FROM pos $POSfilterSql AND pos_type = 'Order' AND order_status < 2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Payment Receipt'))==0){
				$sqlquery = "SELECT 'pos_payment' as tablename, payment_datetime as tabledate, pos_payment_id as table_id, '".addslashes($this->db->translate('Payment Receipt'))."' as activity_feed_title FROM pos_payment $paymentFilterSql";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('IMEI Created'))==0){
				$sqlquery = "SELECT 'item' as tablename, created_on as tabledate, item_id as table_id, '".addslashes($this->db->translate('IMEI Created'))."' as activity_feed_title FROM item $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('User Created'))==0){
				$sqlquery = "SELECT 'user' as tablename, created_on as tabledate, user_id as table_id, '".addslashes($this->db->translate('User Created'))."' as activity_feed_title FROM user $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('Note Created'))==0){
				$sqlquery = "SELECT 'notes' as tablename, created_on as tabledate, notes_id as table_id, '".addslashes($this->db->translate('Note Created'))."' as activity_feed_title FROM notes $filterSql2";
			}
			elseif(strcmp($sactivity_feed, $this->db->translate('User Logged In'))==0){
				$sqlquery = "SELECT 'user_login_history' as tablename, login_datetime as tabledate, user_login_history_id as table_id, '".addslashes($this->db->translate('User Logged In'))."' as activity_feed_title FROM user_login_history $loginFilterSql";
			}
			elseif(strcmp($sactivity_feed, 'Track Edits')==0){
				$sqlquery = "SELECT 'track_edits' as tablename, created_on as tabledate, track_edits_id as table_id, 'Track Edits' as activity_feed_title FROM track_edits $trackFilterSql";
			}
			else{
				$sqlquery = "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed $filterSql2 AND activity_feed_title = '$sactivity_feed'";
			}
		}
		else{
			$sqlquery = "SELECT 'customers' as tablename, created_on as tabledate, customers_id as table_id, '".addslashes($this->db->translate('Customer Created'))."' as activity_feed_title FROM customers $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'product' as tablename, created_on as tabledate, product_id as table_id, '".addslashes($this->db->translate('Product Created'))."' as activity_feed_title FROM product $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'suppliers' as tablename, created_on as tabledate, suppliers_id as table_id, '".addslashes($this->db->translate('Supplier Created'))."' as activity_feed_title FROM suppliers $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'category' as tablename, created_on as tabledate, category_id as table_id, '".addslashes($this->db->translate('Category Created'))."' as activity_feed_title FROM category $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'manufacturer' as tablename, created_on as tabledate, manufacturer_id as table_id, '".addslashes($this->db->translate('New Manufacturer Added'))."' as activity_feed_title FROM manufacturer $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'vendors' as tablename, created_on as tabledate, vendors_id as table_id, '".addslashes($this->db->translate('New Vendor Added'))."' as activity_feed_title FROM vendors $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'expense_type' as tablename, created_on as tabledate, expense_type_id as table_id, '".addslashes($this->db->translate('New Expense Type Added'))."' as activity_feed_title FROM expense_type $filterSql1";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'taxes' as tablename, created_on as tabledate, taxes_id as table_id, '".addslashes($this->db->translate('Tax Created'))."' as activity_feed_title FROM taxes $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'po' as tablename, po_datetime as tabledate, po_id as table_id, '".addslashes($this->db->translate('Purchase Order Created'))."' as activity_feed_title FROM po $POfilterSql";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'repairs' as tablename, created_on as tabledate, repairs_id as table_id, '".addslashes($this->db->translate('Repair Created'))."' as activity_feed_title FROM repairs $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'pos' as tablename, created_on as tabledate, pos_id as table_id, '".addslashes($this->db->translate('Sales Invoice Created'))."' as activity_feed_title FROM pos $POSfilterSql AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2))";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'pos' as tablename, created_on as tabledate, pos_id as table_id, '".addslashes($this->db->translate('Order Created'))."' as activity_feed_title FROM pos $POSfilterSql AND pos_type = 'Order' AND order_status < 2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'pos_payment' as tablename, payment_datetime as tabledate, pos_payment_id as table_id, '".addslashes($this->db->translate('Payment Receipt'))."' as activity_feed_title FROM pos_payment $paymentFilterSql";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'item' as tablename, created_on as tabledate, item_id as table_id, '".addslashes($this->db->translate('IMEI Created'))."' as activity_feed_title FROM item $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'user' as tablename, created_on as tabledate, user_id as table_id, '".addslashes($this->db->translate('User Created'))."' as activity_feed_title FROM user $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'notes' as tablename, created_on as tabledate, notes_id as table_id, '".addslashes($this->db->translate('Note Created'))."' as activity_feed_title FROM notes $filterSql2";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'track_edits' as tablename, created_on as tabledate, track_edits_id as table_id, 'Track Edits' as activity_feed_title FROM track_edits $trackFilterSql";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'user_login_history' as tablename, login_datetime as tabledate, user_login_history_id as table_id, '".addslashes($this->db->translate('User Logged In'))."' as activity_feed_title FROM user_login_history $loginFilterSql";
			$sqlquery .= " UNION ALL ";
			$sqlquery .= "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed $filterSql2";
		}
		$sqlquery .= "  ORDER BY tabledate DESC";
		
		$tabledata = array();
		$sqlquery .= " LIMIT $starting_val, $limit";
		//$this->db->writeIntoLog(implode(' - ', $bindData).$sqlquery);
		$query = $this->db->querypagination($sqlquery, $bindData);
		if($query){
			foreach($query as $onerow){
				
				$activity_feed_title = $onerow['activity_feed_title'];
				$tablename = $onerow['tablename'];
				$table_id = $onerow['table_id'];
				$getHMoreInfo = $this->getHMoreInfo(intval($table_id), $tablename, $userIdNames, $activity_feed_title);
				if(!empty($getHMoreInfo)){
					$tabledata[] = $getHMoreInfo;
				}			
			}
		}

		return $tabledata;
    }

	public function getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title, $sitem_id=0, $fieldname='item_id'){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$currency = $_SESSION["currency"]??'৳';
		$tableMoreInfo = array();

		if(strcmp($tablename,'activity_feed')==0){
			$sql2nd = "SELECT * FROM activity_feed WHERE activity_feed_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					$activity_feed_name = stripslashes(trim((string) strip_tags($oneRow->activity_feed_name)));
					$activity_feed_link = $oneRow->activity_feed_link;					
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'customers')==0){
			$sql2nd = "SELECT * FROM customers WHERE customers_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim($oneRow->first_name.' '.$oneRow->last_name));
					$activity_feed_link = '/Customers/view/'.$oneRow->customers_id;					
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'product')==0){
			$sql2nd = "SELECT p.product_id, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.created_on, p.user_id FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim($oneRow->manufacture.' '.$oneRow->product_name.' '.$oneRow->colour_name.' '.$oneRow->storage.' '.$oneRow->physical_condition_name));
					$activity_feed_link = '/Products/view/'.$oneRow->product_id;					
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'suppliers')==0){
			$sql2nd = "SELECT * FROM suppliers WHERE suppliers_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim($oneRow->company.' '.$oneRow->first_name.' '.$oneRow->last_name));
					$activity_feed_link = '/Suppliers/view/'.$oneRow->suppliers_id;
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);					
				}
			}
		}
		elseif(strcmp($tablename,'category')==0){
			$sql2nd = "SELECT * FROM category WHERE category_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim((string) $oneRow->category_name));
					$activity_feed_link = '/Manage_Data/category/view/'.$oneRow->category_id;					
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);					
				}
			}
		}
		elseif(strcmp($tablename,'manufacturer')==0){
			$sql2nd = "SELECT * FROM manufacturer WHERE manufacturer_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim((string) $oneRow->name));
					$activity_feed_link = '/Manage_Data/manufacturer/view/'.$oneRow->manufacturer_id;
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'vendors')==0){
			$sql2nd = "SELECT * FROM vendors WHERE vendors_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim((string) $oneRow->name));
					$activity_feed_link = '/Manage_Data/vendors/view/'.$oneRow->vendors_id;
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'expense_type')==0){
			$sql2nd = "SELECT * FROM expense_type WHERE expense_type_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim((string) $oneRow->name));
					$activity_feed_link = '/Manage_Data/expense_type/view/'.$oneRow->expense_type_id;
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'taxes')==0){
			$sql2nd = "SELECT * FROM taxes WHERE taxes_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					$tiStr = '';
					if($oneRow->tax_inclusive>0){$tiStr = ' Inclusive';}
		
					$activity_feed_name = stripslashes(trim("$oneRow->taxes_name ($oneRow->taxes_percentage%$tiStr)"));
					$activity_feed_link = '/Getting_Started/taxes/view/'.$oneRow->taxes_id;
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'pos_payment')==0){
			$sql2nd = "SELECT * FROM pos_payment WHERE pos_payment_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim("$oneRow->payment_method ($currency$oneRow->payment_amount)"));					
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, '', $oneRow->payment_datetime, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'po')==0){
			$sql2nd = "SELECT * FROM po WHERE po_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = $oneRow->po_number;
					$activity_feed_link = '/Purchase_orders/edit/'.$oneRow->po_number;
					$status = $oneRow->status;
					if($oneRow->transfer>0){
						$activity_feed_link = '/Inventory_Transfer/edit/'.$oneRow->po_number;
						$supplier_id = $oneRow->supplier_id;
						$supplierssqlquery = $this->db->query("SELECT company_subdomain FROM accounts WHERE accounts_id = $supplier_id AND status != 'SUSPENDED'", array());
						if($supplierssqlquery){
							$activity_feed_title .= ' '.stripslashes(trim((string) $supplierssqlquery->fetch(PDO::FETCH_OBJ)->company_subdomain));
						}
					}
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array("[$status]", $tablename, $activity_feed_link, $oneRow->po_datetime, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'repairs')==0){
			$sql2nd = "SELECT repairs_id, customer_id, ticket_no, created_on, user_id, problem FROM repairs WHERE repairs_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$problem = stripslashes(trim((string) nl2br($oneRow->problem)));
					$customer_id = $oneRow->customer_id;
					$customerName = '';
					if($customer_id >0){
						$customersObj = $this->db->query("SELECT first_name, last_name FROM customers WHERE customers_id = $customer_id", array());
						if($customersObj){
							$customerRow = $customersObj->fetch(PDO::FETCH_OBJ);
							$problem = stripslashes(trim("$customerRow->first_name $customerRow->last_name")).', '.$problem;
						}
					}
					$activity_feed_link = '/Repairs/edit/'.$oneRow->repairs_id;					
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array($problem, $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $oneRow->ticket_no);		
				}
			}
		}
		elseif(strcmp($tablename,'pos')==0){
			$pos_id = $table_id;
			$posRow = false;
			$posObj = $this->db->query("SELECT invoice_no, sales_datetime, user_id FROM pos WHERE pos_id = $pos_id", array());
			if($posObj){
				$posRow = $posObj->fetch(PDO::FETCH_OBJ);
			}
			if($posRow){
				$activity_feed_link = "";
				$invoice_no = $posRow->invoice_no;
				if($activity_feed_title=='Order Created'){
					$activity_feed_link = "/Orders/edit/$invoice_no";
				}
				elseif($activity_feed_title=='Repair Created'){
					$repairsObj = $this->db->query("SELECT repairs_id, ticket_no FROM repairs WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
					if($repairsObj){
						$repairsRows = $repairsObj->fetch(PDO::FETCH_OBJ);
						$repairs_id = $repairsRows->repairs_id;
						$invoice_no = $repairsRows->ticket_no;
						$activity_feed_link = "/Repairs/edit/$repairs_id";
						$tablename = 'repairs';
					}
				}
				else{
					$activity_feed_link = "/Invoices/view/$invoice_no";
				}
				$userName = $userIdNames[$posRow->user_id]??'';
				
				$details = '';
				if(!empty($sitem_id) && $sitem_id !=0){
					if($fieldname=='product_id'){
						$sql2nd = "SELECT * FROM pos_cart WHERE pos_id = $pos_id AND item_id IN ($sitem_id)";
					}
					else{
						$sql2nd = "SELECT pos_cart.* FROM pos_cart, pos_cart_item WHERE pos_cart.pos_id = $pos_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'cellphones' 
						AND pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 1";
			   		}
				}
				else{
					$sql2nd = "SELECT * FROM pos_cart WHERE pos_id = $pos_id ORDER BY pos_cart_id ASC";
				}
				$query2nd = $this->db->query($sql2nd, array());
				if($query2nd){
					while($onePosRow=$query2nd->fetch(PDO::FETCH_OBJ)){
						$item_id = $onePosRow->item_id;
						$pos_cart_id = $onePosRow->pos_cart_id;
						
						$shipping_qty = $onePosRow->shipping_qty;
						$require_serial_no = $onePosRow->require_serial_no;
						
						$newimei_info = '';						
						$item_type = $onePosRow->item_type;
						if($shipping_qty<0){
							$activity_feed_title = 'Sales Invoice Returned';
						}
						if(strcmp($item_type, 'cellphones')==0){
							if(!empty($sitem_id) && $sitem_id !=0 && $fieldname=='item_id'){
								$sqlitem = "SELECT item.item_number, item.carrier_name, pos_cart_item.sale_or_refund FROM item, pos_cart_item WHERE pos_cart_item.pos_cart_id = $pos_cart_id AND pos_cart_item.item_id IN ($sitem_id) AND item.item_id = pos_cart_item.item_id";
							}
							else{
								$sqlitem = "SELECT item.item_number, item.carrier_name, pos_cart_item.sale_or_refund FROM item, pos_cart_item WHERE pos_cart_item.pos_cart_id = $pos_cart_id AND item.item_id = pos_cart_item.item_id";
							}
							$itemquery = $this->db->query($sqlitem, array());
							if($itemquery){
								while($newitem_row = $itemquery->fetch(PDO::FETCH_OBJ)){
									$imei_info = $newitem_row->item_number;
									$carrier_name = $newitem_row->carrier_name;
									if($carrier_name !=''){
										$imei_info .= ' '.$carrier_name;
									}
									
									$sale_or_refund = $newitem_row->sale_or_refund;
									if($sale_or_refund==0){
										$imei_info .= ' (Refund)';
									}
									
									if($imei_info !=''){
										if(!empty($newimei_info)){$newimei_info .= "<br>";}
										$newimei_info .= "$imei_info";
									}
								}
							}
						}
						$description = stripslashes(trim((string) $onePosRow->description));
						$details .= "$shipping_qty $description";
						if(!empty($newimei_info)){$details .= "<br>$newimei_info";}
					}								
				}
				$tableMoreInfo = array($details, $tablename, $activity_feed_link, $posRow->sales_datetime, $userName, $activity_feed_title, $invoice_no);				
			}
		}
		elseif(strcmp($tablename,'item')==0){
			$sql2nd = "SELECT * FROM item WHERE item_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneItemRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = $oneItemRow->item_number;
					$activity_feed_link = '/IMEI/view/'.$oneItemRow->item_number;
					
					$product_name = '';
					$sqlPM = "SELECT manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $oneItemRow->product_id";
					$productObj = $this->db->query($sqlPM, array());
					if($productObj){
						$productRow = $productObj->fetch(PDO::FETCH_OBJ);
						$product_name = stripslashes(trim("$productRow->manufacture $productRow->product_name $productRow->colour_name $productRow->storage $productRow->physical_condition_name"));
					}					
					$userName = $userIdNames[$oneItemRow->user_id]??'';

					$tableMoreInfo = array($product_name, $tablename, $activity_feed_link, $oneItemRow->created_on, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'user')==0){
			$sql2nd = "SELECT user_id, created_on, user_first_name, user_last_name FROM user WHERE user_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim("$oneRow->user_first_name $oneRow->user_last_name"));
					$activity_feed_link = '/Settings/setup_users/view/'.$oneRow->user_id;
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);				
				}
			}
		}
		elseif(strcmp($tablename,'notes')==0){
			$sql2nd = "SELECT * FROM notes WHERE notes_id = $table_id ORDER BY notes_id ASC";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					
					$userName = $userIdNames[$oneRow->user_id]??'';					
					$details = nl2br(stripslashes($oneRow->note));

					$tableMoreInfo = array(intval($table_id), $tablename, intval($oneRow->publics), $oneRow->created_on, $userName, $activity_feed_title, $details);		
				}
			}
		}	
		elseif(strcmp($tablename,'user_login_history')==0){
			
			$sql2nd = "SELECT * FROM user_login_history WHERE user_login_history_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ)){
					$activity_feed_names = array();
					if($oneRow->workstation>0){
						$activity_feed_names[] = 'Workstation: '.$oneRow->workstation;
					}
					if(!empty($oneRow->login_ip)){
						$activity_feed_names[] = 'IP Address: '.$oneRow->login_ip;
					}
					$activity_feed_name = implode(', ', $activity_feed_names);
					$userName = $userIdNames[$oneRow->user_id]??'';

					$tableMoreInfo = array(intval($table_id), $tablename, '', $oneRow->login_datetime, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}					
		elseif(strcmp($tablename,'track_edits')==0){
			$sql2nd = "SELECT * FROM track_edits WHERE track_edits_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ) ){
					$moredetails = array();
					$activity_feed_name = $activity_feed_link = '';
					if(!empty($oneRow->details)){
						$record_for = $oneRow->record_for;
						$record_id = $oneRow->record_id;
						$details = json_decode($oneRow->details);
						$moreInfo = (array)$details->moreInfo;
						$changed = $details->changed;
						if(array_key_exists('description', $moreInfo)){
							$moredetails[] = $moreInfo['description'];
							if($record_for=='pos'){
								$subTableObj = $this->db->query("SELECT invoice_no FROM $record_for WHERE $record_for"."_id = $record_id", array());
								if($subTableObj){
									$invoice_no = $subTableObj->fetch(PDO::FETCH_OBJ)->invoice_no;
									$activity_feed_name = 's'.$invoice_no;
									$activity_feed_link = "/Invoices/view/$invoice_no";
								}
							}
							elseif($record_for=='repairs'){
								$subTableObj = $this->db->query("SELECT ticket_no FROM $record_for WHERE $record_for"."_id = $record_id", array());
								if($subTableObj){
									$activity_feed_name = 't'.$subTableObj->fetch(PDO::FETCH_OBJ)->ticket_no;
									$activity_feed_link = "/Repairs/edit/$record_id";
								}
							}
						}
						if(!empty($changed)){
							$changed = (array)$changed;
							$changeStr = 'Edited: ';
							$c=0;
							foreach($changed as $key=>$changedData){
								$c++;
								if($c>1){$changeStr .= ', ';}

								$changeStr .= ucfirst(str_replace('_', ' ', $key));
								if(!is_array($changedData)){$changeStr .= ' '.$changedData;}
								elseif(is_array($changedData) && count($changedData)==2){		
																		
									if(in_array($key, array('custom_data', 'form_data'))){
										$custom_data = $changedData[0];
										if(!empty($custom_data)){
											$custom_data = unserialize($custom_data);
											$cd = 0;
											$changeStr .= " (";
											foreach($custom_data as $oneFieldName=>$labelVal){
												$cd++;
												if($cd >1){$changeStr .= ",";}
												$labelVal = str_replace(';', '; ', $labelVal);
												$changeStr .= " $oneFieldName : ".nl2br($labelVal);
											}
											$changeStr .= ")";
										}
										else{
											$changeStr .= '""';
										}
										$changeStr .= " to ";
										$custom_data = $changedData[1];
										if(!empty($custom_data)){
											$custom_data = unserialize($custom_data);
											$cd = 0;
											$changeStr .= " (";
											foreach($custom_data as $oneFieldName=>$labelVal){
												$cd++;
												if(!in_array(strtolower($oneFieldName), array('picture', 'sign', 'image'))){
													if($cd >1){$changeStr .= ",";}
													$changeStr .= " ".stripslashes($oneFieldName)." : ".nl2br(stripslashes($labelVal));
												}
												else{
													if($cd >1){$changeStr .= ",";}
													$labelVal = str_replace('||UploadImage', '', $labelVal);
													$labelVal = str_replace('||Signature', '', $labelVal);
													$changeStr .= " ".stripslashes($oneFieldName)." : ";
													if(!empty($labelVal)){$changeStr .= ' path not allow';}
												}
											}
											$changeStr .= ")";
										}
										else{
											$changeStr .= '""';
										}
									}
									else{	
										$changeStr .= ' "'.$changedData[0].'" to "'.$changedData[1].'"';
									}
								}										
							}
							$moredetails[] = stripslashes($changeStr);
						}
					}					
					$userName = $userIdNames[$oneRow->user_id]??'';
					$details = implode('<br>', $moredetails);

					$tableMoreInfo = array($details, $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);		
				}
			}
		}
		elseif(strcmp($tablename,'posreturn')==0){
			$subSql = "SELECT pos_cart.description, pos.invoice_no, pos.sales_datetime, pos.user_id FROM pos, pos_cart, pos_cart_item WHERE pos.pos_id = $table_id AND pos_cart_item.item_id IN ($sitem_id) AND pos_cart.item_type = 'cellphones' 
						AND pos.accounts_id = $accounts_id AND pos.pos_id = pos_cart.pos_id AND  pos_cart.pos_cart_id = pos_cart_item.pos_cart_id AND pos_cart_item.sale_or_refund = 0";
			$subQueryObj = $this->db->query($subSql, array());
			if($subQueryObj){
				while($oneRow = $subQueryObj->fetch(PDO::FETCH_OBJ)){
					
					$activity_feed_name = stripslashes(trim((string) $oneRow->description));
					$activity_feed_link = '/Invoices/view/'.$oneRow->invoice_no;
					$userName = $userIdNames[$oneRow->user_id]??'';
					$qty = '+1';
					$product_name = "($qty) $activity_feed_name";
					$tableMoreInfo = array($product_name, $tablename, $activity_feed_link, $oneRow->sales_datetime, $userName, $activity_feed_title, $oneRow->invoice_no);
				}
			}
		}	
		elseif(strcmp($tablename,'poreturn')==0){
			$subSql = "SELECT po_items.product_id, po.po_number, po_items.created_on, po.user_id, po.transfer, po.supplier_id FROM po, po_items, po_cart_item WHERE po.po_id = $table_id AND po_cart_item.item_id IN ($sitem_id) AND po.accounts_id = $accounts_id AND po.po_id = po_items.po_id AND po_cart_item.return_po_items_id = po_items.po_items_id AND po_items.received_qty<0";
			//$tableMoreInfo = array(intval($table_id), $tablename, 1, '', '', $subSql, '');
			
			$subQueryObj = $this->db->query($subSql, array());
			if($subQueryObj){
				while($oneRow = $subQueryObj->fetch(PDO::FETCH_OBJ)){

					$activity_feed_name = '';
					$sqlPM = "SELECT manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p 
					LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
					WHERE p.product_id = $oneRow->product_id";
					$productObj = $this->db->query($sqlPM, array());
					if($productObj){
						$product_row = $productObj->fetch(PDO::FETCH_OBJ);
						
						$product_name = stripslashes($product_row->product_name);
						$product_name = stripslashes(trim($product_row->manufacture.' '.$product_name));
						
						$colour_name = $product_row->colour_name;
						if($colour_name !=''){$product_name .= ' '.$colour_name;}
						
						$storage = $product_row->storage;
						if($storage !=''){$product_name .= ' '.$storage;}
						
						$physical_condition_name = $product_row->physical_condition_name;
						if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
						
						$activity_feed_name = $product_name;
					}
					$qty = '-1';
					
					$activity_feed_link = '/Purchase_orders/edit/'.$oneRow->po_number;
					if($oneRow->transfer>0){
						$activity_feed_link = '/Inventory_Transfer/edit/'.$oneRow->po_number;
						$supplier_id = $oneRow->supplier_id;
						$supplierssqlquery = $this->db->query("SELECT company_subdomain FROM accounts WHERE accounts_id = $supplier_id AND status != 'SUSPENDED'", array());
						if($supplierssqlquery){
							$activity_feed_title .= ' '.stripslashes(trim((string) $supplierssqlquery->fetch(PDO::FETCH_OBJ)->company_subdomain));
						}
					}					
					$userName = $userIdNames[$oneRow->user_id]??'';
					$product_name = "($qty) $activity_feed_name";

					$tableMoreInfo = array($product_name, $tablename, $activity_feed_link, $oneRow->created_on, $userName, $activity_feed_title, $oneRow->po_number);
				}
			}
		}	
		elseif(strcmp($tablename,'digital_signature')==0){
			$sql2nd = "SELECT note, created_on, user_id FROM digital_signature WHERE digital_signature_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ) ){
					
					$userName = $userIdNames[$oneRow->user_id]??'';
					
					$tableMoreInfo = array(intval($table_id), $tablename, 1, $oneRow->created_on, $userName, $activity_feed_title, $oneRow->note);
				}
			}
		}
		elseif(strcmp($tablename, 'commissions')==0){
			$sql2nd = "SELECT * FROM commissions WHERE commissions_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ) ){

					$rule_field = $oneRow->rule_field;
					$rule_match = $oneRow->rule_match;
					if(strcmp('Category', $rule_field)==0){
						$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $rule_match", array());
						if($categoryObj){
							$rule_match = $categoryObj->fetch(PDO::FETCH_OBJ)->category_name;
						}
					}
					$activity_feed_name = stripslashes(trim($rule_field.' '.$rule_match));
					$userName = $userIdNames[$oneRow->user_id]??'';
					
					$tableMoreInfo = array(intval($table_id), $tablename, '/Commissions/view/'.$oneRow->commissions_id, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);
				}
			}
		}
		elseif(strcmp($tablename, 'expenses')==0){
			$sql2nd = "SELECT * FROM expenses WHERE expenses_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ) ){

					$vendors_id = $oneRow->vendors_id;
					$vendors_name = '';
					if($vendors_id >0){
						$vendorsObj = $this->db->query("SELECT name FROM vendors WHERE vendors_id = $vendors_id", array());
						if($vendorsObj){
							$vendors_name = $vendorsObj->fetch(PDO::FETCH_OBJ)->name;
						}
					}
					$activity_feed_name = stripslashes(trim($oneRow->expense_type.' '.$vendors_name));
					$userName = $userIdNames[$oneRow->user_id]??'';
					
					$tableMoreInfo = array(intval($table_id), $tablename, '/Expenses/view/'.$oneRow->expenses_id, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);
				}
			}
		}
		elseif(strcmp($tablename, 'po_items')==0){
			$sql2nd = "SELECT * FROM po_items WHERE po_items_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ) ){
					$item_type = $oneRow->item_type;
					$product_id = $oneRow->product_id;
					$productName = $sku = '';
					if($product_id >0){
						$productObj = $this->db->query("SELECT manufacturer.name AS manufacture, p.product_name, p.sku FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id", array());
						if($productObj){
							$productRow = $productObj->fetch(PDO::FETCH_OBJ);
							$productName = stripslashes(trim("$productRow->manufacture $productRow->product_name"));
							$sku = $productRow->sku;
						}
					}					
					$activity_feed_name = stripslashes(trim("$productName ($sku)"));					
					$userName = $userIdNames[$oneRow->user_id]??'';
					
					$tableMoreInfo = array(intval($table_id), $tablename, '/Products/view/'.$oneRow->product_id, $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);
				
				}
			}
		}
		elseif(strcmp($tablename, 'stock_take')==0){
			$sql2nd = "SELECT ST.*, manufacturer.name AS manufacture FROM stock_take ST LEFT JOIN manufacturer ON (ST.manufacturer_id = manufacturer.manufacturer_id) WHERE ST.stock_take_id = $table_id";
			$query2nd = $this->db->query($sql2nd, array());
			if($query2nd){
				while($oneRow = $query2nd->fetch(PDO::FETCH_OBJ) ){

					$category_id = $oneRow->category_id;
					$categoryName = '';
					if($category_id >0){
						$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $category_id", array());
						if($categoryObj){
							$categoryName = "&emsp; Category: ".$categoryObj->fetch(PDO::FETCH_OBJ)->category_name;
						}
					}
					$activity_feed_name = stripslashes(trim("$oneRow->manufacture $categoryName"));
					$userName = $userIdNames[$oneRow->user_id]??'';
					
					$tableMoreInfo = array(intval($table_id), $tablename, '', $oneRow->created_on, $userName, $activity_feed_title, $activity_feed_name);
				}
			}
		}
		
		return $tableMoreInfo;		
	}
		
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sactivity_feed = $POST['sactivity_feed']??'';
		$puser_id = intval($POST['puser_id']??0);
		$date_range = $POST['date_range']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->activity_feed = $sactivity_feed;
		$this->puser_id = $puser_id;
		$this->date_range = $date_range;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){				
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['actFeeTitOpt'] = $this->actFeeTitOpt;
			$jsonResponse['pUserOpt'] = $this->pUserOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}
}
?>