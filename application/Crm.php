<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Crm{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
	public function AJ_lists_MoreInfo(){
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$cusTypeOpt = array();
		$strextra ="SELECT COALESCE(customer_type,'') AS customer_type FROM customers WHERE accounts_id = $prod_cat_man AND customers_publish = 1 GROUP BY customer_type ORDER BY customer_type ASC";
		$cusObj = $this->db->query($strextra, array());
		if($cusObj){
			while($oneRow = $cusObj->fetch(PDO::FETCH_OBJ)){
				$cusTypeOpt[stripslashes(trim((string) $oneRow->customer_type))]  = '';
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
	
	//================Joining Class==============//
	public function AJget_CustomersPopup(){
		$Customers = new Customers($this->db);
		return $Customers->AJget_CustomersPopup();
	}
	
	public function AJsave_Customers(){
		$Customers = new Customers($this->db);
		return $Customers->AJsave_Customers();
	}
}
?>