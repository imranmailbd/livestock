<?php
class Db{
	protected $db;
	public function __construct(){
		try {	
			$username = 'skitsbd_imran';  //livestock
			$password = 'imran123!@#';  //livestock123!@#
			$database = 'skitsbd_livestock';  //livestock
			if(strcmp(OUR_DOMAINNAME, 'livestock.skitsbd.com') != 0) {  //livestock.com
				$username = 'root';
				$password = '';
				$database = 'livestock';
			}	
			$this->db = new PDO("mysql:dbname=$database;host=localhost;charset=utf8", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		}
		catch (PDOException $e) {
			$this->writeIntoLog('Connection failed: ' . $e->getMessage());
			sleep(5);
			call_user_func('__construct');
		}
	}
	
	public function writeIntoLog($message){
		if($message !=''){
			$fileName = './error_log';
			if(is_array($message)){$message = implode(', ', $message);}
			$timezone = 'America/New_York';
			date_default_timezone_set($timezone);
			file_put_contents($fileName, date('Y-m-d H:i:s')." $message\n",FILE_APPEND);
			if(isset($_SESSION["timezone"])){
				$timezone = $_SESSION["timezone"];
				if($timezone =='' || is_null($timezone)){$timezone = 'America/New_York';}
			}
			date_default_timezone_set($timezone);
		}
	}
	
	public function query($statement, $bindData, $paramType=0){
		
		$sql = $this->db->prepare($statement);
		if(!empty($bindData)){
			foreach($bindData as $fieldname=>$fieldvalue){
				if($paramType>0){
					$sql->bindValue(":$fieldname",$fieldvalue, PDO::PARAM_INT);
				}
				else{
					$sql->bindValue(":$fieldname",$fieldvalue, PDO::PARAM_STR);
				}
			}
		}
		
		/*$result = $sql->execute();
		if($result){
			return $sql;
		}
		else{
			$errors = $sql->errorInfo();
			$this->writeIntoLog('Query failed: ' . $errors[2]);
			return false;
		}*/
		
		$sql->execute();		
		$errors = $sql->errorInfo();
		if($errors[2]==''){
			if($sql->rowCount()>0){
				return $sql;
			}
			else{
				return false;
			}
		}
		else{
			$this->writeIntoLog('Query failed: ' . $errors[2]." near $statement");
			return false;
		}
	}
	
	public function querypagination($statement, $bindData, $paramType=0){
		
		$sql = $this->db->prepare($statement);
		if(count($bindData)>0){
			foreach($bindData as $fieldname=>$fieldvalue){
				if($paramType>0){
					$sql->bindValue(":$fieldname",$fieldvalue, PDO::PARAM_INT);
				}
				else{
					$sql->bindValue(":$fieldname",$fieldvalue, PDO::PARAM_STR);
				}
			}
		}
		$result = $sql->execute();
		if($result){
			$returnData = $sql->fetchAll(PDO::FETCH_ASSOC);
		}
		else{
			$errors = $sql->errorInfo();
			$this->writeIntoLog('Query Pagination failed: ' . $errors[2]." near $statement");
			$returnData = false;
		}
		
		return $returnData;
	}
	
	public function insert($tablename, $fieldsData){
		$fieldsArray = array_keys($fieldsData);
		$str = "INSERT INTO $tablename (`".implode('`, `', $fieldsArray)."`) values(:".implode(', :', $fieldsArray).")";
		$sql = $this->db->prepare($str);
		foreach($fieldsData as $fieldname=>$fieldvalue){
			$sql->bindValue(":$fieldname",$fieldvalue, PDO::PARAM_STR);
		}
		
		$result = $sql->execute();
		if($result){
			return $this->db->lastInsertId();
		}
		else{
			foreach($fieldsData as $field=>$value){
				$str = str_replace(":$field", $value, $str);
			}
			$errors = $sql->errorInfo();
			$this->writeIntoLog('Insert failed: ' . $errors[2]." near $str");
			return false;
		}
	}
	
	public function update($tablename, $fieldsData, $id){
		$idName = $tablename.'_id';
		$fieldsArray = array_keys($fieldsData);
		
		$str = "UPDATE $tablename SET";
		$l=0;
		foreach($fieldsArray as $oneField){
			if($l>0){$str .= ", ";}
			$str .= " $oneField = :$oneField";
			$l++;
		}
		$str .= " WHERE $idName = :$idName";
		
		$sql = $this->db->prepare($str);
		foreach($fieldsData as $field=>$value){
			$sql->bindValue(":$field", $value, PDO::PARAM_STR);
		}
		$sql->bindValue(":$idName",$id, PDO::PARAM_INT);
		$result = $sql->execute();
		if($result){
			return $sql->rowCount();
		}
		else{
			foreach($fieldsData as $field=>$value){
				$str = str_replace(":$field", $value, $str);
			}
			$errors = $sql->errorInfo();
			$this->writeIntoLog('Update failed: ' . $errors[2]." near $str");
			return false;
		}
	}
	
	public function delete($tableName, $fieldName, $fieldValue){
		$str = "DELETE FROM $tableName WHERE $fieldName = :$fieldName";
		$sql = $this->db->prepare($str);
		$sql->bindValue(":$fieldName",$fieldValue, PDO::PARAM_INT);
		$result = $sql->execute();
		if($result){
			return $sql->rowCount();
		}
		else{
			$errors = $sql->errorInfo();
			$this->writeIntoLog('Delete failed: ' . $errors[2]." near $str");
			return false;
		}
	}

	public function checkCharLen($tableField, $charStr){
		$fieldNameLenth = array('accounts.domain'=>25,
		'accounts.pay_frequency'=>10,
		'accounts.company_name'=>40,
		'accounts.company_subdomain'=>30,
		'accounts.company_phone_no'=>20,
		'accounts.customer_service_email'=>100,
		'accounts.company_street_address'=>255,
		'accounts.company_country_name'=>100,
		'accounts.company_state_name'=>100,
		'accounts.company_city'=>100,
		'accounts.company_zip'=>20,
		'accounts.status'=>20,
		'accounts.coupon_code'=>20,
		'accounts.paypal_id'=>20,
		'activity_feed.activity_feed_title'=>255,
		'activity_feed.activity_feed_link'=>255,
		'activity_feed.uri_table_name'=>255,
		'activity_feed.uri_table_field_name'=>255,
		'activity_feed.field_value'=>255,
		'brand_model.brand'=>15,
		'brand_model.model'=>25,
		'category.category_name'=>35,
		'commissions.rule_field'=>15,
		'commissions.rule_match'=>20,
		'customers.first_name'=>17,
		'customers.last_name'=>17,
		'customers.email'=>50,
		'customers.company'=>35,
		'customers.contact_no'=>20,
		'customers.secondary_phone'=>20,
		'customers.fax'=>20,
		'customers.customer_type'=>20,
		'customers.shipping_address_one'=>35,
		'customers.shipping_address_two'=>35,
		'customers.shipping_city'=>30,
		'customers.shipping_state'=>20,
		'customers.shipping_zip'=>9,
		'customers.shipping_country'=>35,
		'customers.website'=>80,
		'customer_type.name'=>20,
		'custom_fields.field_for'=>15,
		'custom_fields.field_name'=>35,
		'custom_fields.field_type'=>15,
		'digital_signature.for_table'=>20,
		'end_of_day.payment_method'=>50,
		'end_of_day.drawer'=>20,
		'expenses.expense_type'=>35,
		'expenses.bill_number'=>20,
		'expenses.ref'=>30,
		'expense_type.name'=>35,
		'forms.form_name'=>15,
		'forms.form_for'=>10,
		'forms.form_condition'=>30,
		'forms.form_matches'=>60,
		'forms_data.form_name'=>15,
		'help.title'=>100,
		'help.video_url'=>120,
		'instance_home.paypal_email'=>50,
		'instance_home.currency_code'=>3,
		'instance_home.mst_one'=>100,
		'instance_home.mst_two'=>100,
		'instance_home.mst_three'=>100,
		'instance_home.mst_four'=>100,
		'instance_home.business_address'=>200,
		'instance_home.bd_one_icon'=>50,
		'instance_home.bd_one_headline'=>200,
		'instance_home.bd_one_subheadline'=>200,
		'instance_home.bd_two_icon'=>50,
		'instance_home.bd_two_headline'=>200,
		'instance_home.bd_two_subheadline'=>200,
		'instance_home.bd_three_icon'=>50,
		'instance_home.bd_three_headline'=>200,
		'instance_home.bd_three_subheadline'=>200,
		'instance_home.cellular_services1'=>40,
		'instance_home.cellular_services2'=>40,
		'instance_home.cellular_services3'=>40,
		'instance_home.cellular_services4'=>40,
		'instance_home.cellular_services5'=>40,
		'instance_home.cellular_services6'=>40,
		'instance_home.cellular_services7'=>40,
		'instance_home.mon_from'=>10,
		'instance_home.mon_to'=>10,
		'instance_home.tue_from'=>10,
		'instance_home.tue_to'=>10,
		'instance_home.wed_from'=>10,
		'instance_home.wed_to'=>10,
		'instance_home.thu_from'=>10,
		'instance_home.thu_to'=>10,
		'instance_home.fri_from'=>10,
		'instance_home.fri_to'=>10,
		'instance_home.sat_from'=>10,
		'instance_home.sat_to'=>10,
		'instance_home.sun_from'=>10,
		'instance_home.sun_to'=>10,
		'instance_home.meta_keywords'=>60,
		'instance_home.meta_description'=>120,
		'item.item_number'=>20,
		'item.carrier_name'=>22,
		'manufacturer.name'=>30,
		'notes.note_for'=>20,
		'our_invoices.paid_by'=>50,
		'our_invoices.pay_frequency'=>10,
		'petty_cash.drawer'=>20,
		'po.lot_ref_no'=>60,
		'po.paid_by'=>50,
		'po.status'=>20,
		'po.suppliers_invoice_no'=>20,
		'pos.taxes_name1'=>20,
		'pos.taxes_name2'=>20,
		'pos.pos_type'=>10,
		'pos.status'=>20,
		'pos_cart.item_type'=>16,
		'pos_cart.description'=>200,
		'pos_payment.payment_method'=>50,
		'pos_payment.drawer'=>20,
		'po_items.item_type'=>16,
		'product.product_type'=>20,
		'product.manufacture'=>30,
		'product.colour_name'=>15,
		'product.storage'=>20,
		'product.physical_condition_name'=>3,
		'product.product_name'=>100,
		'product.sku'=>20,
		'product_prices.price_type'=>15,
		'product_prices.type_match'=>20,
		'properties.imei_or_serial_no'=>20,
		'properties.more_details'=>45,
		'repairs.problem'=>50,
		'repairs.lock_password'=>20,
		'repairs.due_time'=>10,
		'repairs.status'=>20,
		'repairs.bin_location'=>20,
		'repairs.notify_email'=>50,
		'repairs.notify_sms'=>20,
		'repair_problems.name'=>50,
		'serial_number.serial_number'=>20,
		'stock_take.status'=>10,
		'stock_take.manufacturer'=>30,
		'stock_take.reference'=>20,
		'suppliers.first_name'=>12,
		'suppliers.last_name'=>17,
		'suppliers.email'=>50,
		'suppliers.company'=>35,
		'suppliers.contact_no'=>15,
		'suppliers.secondary_phone'=>15,
		'suppliers.fax'=>15,
		'suppliers.shipping_address_one'=>35,
		'suppliers.shipping_address_two'=>35,
		'suppliers.shipping_city'=>30,
		'suppliers.shipping_state'=>20,
		'suppliers.shipping_zip'=>9,
		'suppliers.shipping_country'=>35,
		'suppliers.website'=>80,
		'taxes.taxes_name'=>20,
		'track_edits.record_for'=>20,
		'user.password_hash'=>64,
		'user.changepass_link'=>32,
		'user.employee_number'=>20,
		'user.pin'=>10,
		'user.user_first_name'=>12,
		'user.user_last_name'=>17,
		'user.user_email'=>100,
		'user.login_ck_id'=>32,
		'user_login_history.login_ip'=>20,
		'variables.name'=>30,
		'vendors.name'=>35);
		
		if(!empty($tableField) && array_key_exists($tableField, $fieldNameLenth)){
			$lenth = $fieldNameLenth[$tableField];
		}
		else{
			$lenth = 0;
		}
		
		$charStr = addslashes(stripslashes(str_replace('"', '&quot;', $charStr)));
		if($lenth>0 && mb_strlen($charStr, 'UTF-8')>$lenth){
			$charStr = mb_substr($charStr, 0, $lenth);
		}
		return $charStr;
	}
	
	public function supportEmail($emailId = 'info'){
		$emailAddress = array('info'=>"info@skitsbd.com",
							'support'=>"support@skitsbd.com",
							'do_not_reply'=>"do_not_reply@skitsbd.com",
							'Host'=>"mail.machouse.com.bd",
							'Username'=>"info@machouse.com.bd",
							'Password'=>"info123!@#"
							);
		if(!array_key_exists($emailId, $emailAddress)){
			return 'support@skitsbd.com';
		}
		else{
			return $emailAddress[$emailId];
		}
	}

	function translate($index){
		$loadLangFile = $_SESSION["language"]??'English';
		if($loadLangFile != 'English'){
			$clsObj = new $loadLangFile();
			if($clsObj->index($index)){
				return $clsObj->index($index);
			}
			else{
				$fileInfos = debug_backtrace();
				$fileLocation = str_replace('/home/celltesting/', '', $fileInfos[0]['file']);
				$lineNumber = $fileInfos[0]['line'];
				$accounts_id = $_SESSION["accounts_id"]??0;

				$this->writeIntoLog("PHP Translate issue: $index - File Location: $fileLocation, Line# $lineNumber, Accounts ID: $accounts_id, Language: $loadLangFile");
			}
		}
		return $index;
	}
	
}
?>