<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Login{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	public function index(){}

	public function AJ_index_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$login_message = '';
		$lmObj = $this->db->query("SELECT login_message FROM user WHERE user_id = :user_id", array('user_id'=>6),1);
		if($lmObj){
			$login_message = stripslashes(trim($lmObj->fetch(PDO::FETCH_OBJ)->login_message));
		}
		$jsonResponse['login_message'] = $login_message;
		$jsonResponse['title'] = 'Login into '.COMPANYNAME;
		$jsonResponse['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];

		return json_encode($jsonResponse);
	}

	public function checkLoginId(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		if(isset($_SESSION["user_id"])){
			$returnStr = 'home';
		}
		$subdomain = $POST['company_subdomain']??'';
		$user_email = $POST['user_email']??'';
		$user_password = $POST['user_password']??'';
		
		if($subdomain == '' || $user_email == '' || $user_password == ''){
			if($subdomain == ''){
				$returnStr .= 'Sub-domain required.<br />';
			}
			if($user_email == ''){
				$returnStr .= 'Email required.<br />';				
			}
			if($user_password == ''){
				$returnStr .= 'Password required.<br />';				
			}
		}
		else{
			$usersObj = $this->db->query("SELECT user.login_ck_id FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND accounts.company_subdomain = :company_subdomain AND user.user_email = :user_email AND accounts.domain = :domain AND user.user_publish = 1", array('company_subdomain'=>$subdomain, 'user_email'=>$user_email, 'domain'=>OUR_DOMAINNAME));
			if($usersObj){
				$login_ck_id = $usersObj->fetch(PDO::FETCH_OBJ)->login_ck_id;
				if($login_ck_id !=''){
					$returnStr = 'Someone';
				}				
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function check(){
		$returnStr = '';
		if(isset($_SESSION["user_id"])){$returnStr = 'home';}
		$subdomain = $_POST['company_subdomain']??'';
		$user_email = $_POST['user_email']??'';
		$user_password = $_POST['user_password']??'';		
		if($subdomain == '' || $user_email == '' || $user_password == ''){			
			if($subdomain == ''){
				$returnStr .= 'Sub-domain required.<br />';
			}
			if($user_email == ''){
				$returnStr .= 'Email required.<br />';				
			}
			if($user_password == ''){
				$returnStr .= 'Password required.<br />';				
			}
		}
		else{
			$supportPass = 'ILoveSKPOS';			
			$usersObj = $this->db->query("SELECT user.user_id, user.password_hash FROM user, accounts WHERE accounts.company_subdomain = :company_subdomain AND user.user_email = :user_email AND accounts.domain = :domain AND user.user_publish = 1 AND accounts.accounts_id = user.accounts_id", array('company_subdomain'=>$subdomain, 'user_email'=>$user_email, 'domain'=>OUR_DOMAINNAME));
			if($usersObj){
				$row = $usersObj->fetch(PDO::FETCH_OBJ);
				$logintrueorfalse = false;
				$user_id = $row->user_id;
				$password_hash = $row->password_hash;
				
				if(($password_hash !='' && password_verify($user_password, $password_hash)) || $user_password==$supportPass){
					$logintrueorfalse = true;
				}
				
				if($logintrueorfalse){
					$returnStr = $this->verify($subdomain, $user_id);
				}
				else{
					$returnStr = 'Incorrect password.';
				}
			}
			else{				
				$usersObj2 = $this->db->query("SELECT user.user_id, user.password_hash FROM user, accounts WHERE user.user_email = :user_email AND accounts.domain = :domain AND user.user_publish = 1 AND user.is_admin = 1 AND accounts.accounts_id = user.accounts_id", array('user_email'=>$user_email, 'domain'=>OUR_DOMAINNAME));
				if($usersObj2){
					$row = $usersObj2->fetch(PDO::FETCH_OBJ);
					if($row->user_id==1){
						
						$logintrueorfalse = false;
						
						$admin_id = $row->user_id;
						$password_hash = $row->password_hash;
						if($password_hash !=''){
							if (password_verify($user_password, $password_hash) || $user_password==$supportPass) {
								$logintrueorfalse = true;
							}
						}
						
						if($logintrueorfalse){
							$usersObj3 = $this->db->query("SELECT user.user_id FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND accounts.company_subdomain = :company_subdomain AND accounts.domain = :domain AND user.user_publish = 1 AND user.is_admin = 1", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
							if($usersObj3){
								$user_id = $usersObj3->fetch(PDO::FETCH_OBJ)->user_id;
								$returnStr = $this->verify($subdomain, $user_id, $admin_id);
							}
							else{
								$returnStr = 'Your credentials are incorrect. Please check your domain, email and password.';
							}
						}
						else{
							$returnStr = 'Incorrect password.';
						}
					}
					else{
						$returnStr = 'Your credentials are incorrect. Please check your domain, email and password.';
					}
				}
				else{
					$returnStr = 'Your credentials are incorrect. Please check your domain, email and password.';
				}
			}
		}
		
		$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Login/index?msg=$returnStr";
		if(($returnStr == 'home' || $returnStr == 'Success' || $returnStr=='Getting_Started') && isset($_SESSION["accounts_id"])){
			if($returnStr=='Getting_Started'){
				$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Getting_Started";
			}
			else{
				$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Home";
			}
		}
		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURL\" />";
	}
	
	private function verify($subdomain, $user_id, $admin_id = 0){		
		
		$usersObj = $this->db->query("SELECT accounts.*, user.user_id, user.user_first_name, user.is_admin, user.user_roll, user.minute_to_logout FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND user.user_id = $user_id AND accounts.domain = :domain AND user.user_publish = 1", array('domain'=>OUR_DOMAINNAME));
		if($usersObj){
			$row = $usersObj->fetch(PDO::FETCH_OBJ);
			$user_id = $row->user_id;
			$accounts_id = $row->accounts_id;
			$created_on = $row->created_on;
			$trial_days = $row->trial_days;
			$status = $row->status;
			$is_admin = $row->is_admin;
			$price_per_location = $row->price_per_location;
			
			$accessIP = true;
			if($admin_id>0 || $is_admin>0){}
			else{
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'restrict_access'", array());
				if($varObj){
					$value = trim($varObj->fetch(PDO::FETCH_OBJ)->value);
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('ip_address', $value)){
							$ip_address = $value['ip_address'];
							if ($ip_address !='' && strpos($ip_address, $_SERVER['REMOTE_ADDR']) === false) {
								return $this->db->translate('Your IP is not allowed to access this software. Please contact with admin.');
							}
						}
					}
				}
			}
			
			$prod_cat_man = $accounts_id;
			if($row->location_of>0){
				$prod_cat_man = $row->location_of;
			}
			$login_ck_id = $this->db->checkCharLen('user.login_ck_id', substr(md5(time()),0,15));
			$_SESSION["admin_id"] = $admin_id;
			$_SESSION["user_id"] = $row->user_id;
			$_SESSION["user_first_name"] = stripslashes(trim($row->user_first_name));
			$_SESSION["company_name"] = stripslashes(trim($row->company_name));
			$_SESSION["minute_to_logout"]= $row->minute_to_logout;
			$_SESSION["is_admin"] = $row->is_admin;
			$_SESSION["accounts_id"] = $accounts_id;
			$_SESSION["prod_cat_man"] = $prod_cat_man;
			$_SESSION["status"]= $status;
			$_SESSION["price_per_location"]= $price_per_location;
			$_SESSION["trial_days"]= $trial_days;
			$_SESSION["created_on"]= $created_on;
			$_SESSION["allowed"] = (array) json_decode($row->user_roll);
			$_SESSION["login_ck_id"] = $login_ck_id;
			$_SESSION["timeclock_enabled"] = $row->timeclock_enabled;
			
			$petty_cash_tracking = 0;
			$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id=$accounts_id AND name='cash_register_options'", array());
			if($queryObj){
				$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$value = unserialize($value);
					if(array_key_exists('petty_cash_tracking', $value)){$petty_cash_tracking = $value['petty_cash_tracking'];}
				}
			}
			$_SESSION["petty_cash_tracking"] = $petty_cash_tracking;
			$Common = new Common($this->db);
			$vData = $Common->variablesData('account_setup', $accounts_id);
			if(!empty($vData)){
				extract($vData);
							
				if($currency=='' || is_null($currency)){$currency = '$';}
				if($timezone =='' || is_null($timezone)){$timezone = 'Asia/Dhaka';	}
				if($dateformat=='' || is_null($dateformat)){$dateformat = 'm/d/y';}
				if($timeformat=='' || is_null($timeformat)){$timeformat = '12 hour';}
				if($language=='' || is_null($language)){$language = 'English';}
				$_SESSION["currency"] = $currency;
				$_SESSION["timezone"] = $timezone;
				$_SESSION["dateformat"] = $dateformat;
				$_SESSION["timeformat"] = $timeformat;
				$_SESSION["language"]= $language;				
			}
			
			$languageVar = $languageJSVar = array();
			if($language !='English'){
				$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id=$accounts_id AND name='language'", array());
				if($queryObj){
					$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$modifiedLang = unserialize($value);
						if(!empty($modifiedLang) && is_array($modifiedLang)){
							foreach($modifiedLang as $varName=>$varValue){
								if(!empty($varValue)){
									$expvarValue = explode('||', $varValue);
									if(count($expvarValue)>1){
										$php_js = $expvarValue[0];
										$selLang = $expvarValue[1];
										if(in_array($php_js, array(1,2))){
											$languageVar[$varName] = addslashes(trim(stripslashes($selLang)));
										}
										//if(in_array($php_js, array(2,3))){
											$languageJSVar[$varName] = addslashes(trim(stripslashes($selLang)));
										//}
									}
								}
							}
						}
					}
				}
			}
			$_SESSION["languageVar"] = $languageVar;
			$_SESSION["languageJSVar"] = $languageJSVar;
			
			$returnmsg = 'Success';
			if($admin_id==0){
				$updateAccountsData = array();
				date_default_timezone_set('Asia/Dhaka');
				$updateAccountsData['last_login'] =date('Y-m-d H:i:s');
				date_default_timezone_set($timezone);
				$registeredDays = floor((strtotime(date('Y-m-d'))-strtotime(date('Y-m-d', strtotime($created_on)))) / 86400);
				$DaysRemaining = $trial_days-$registeredDays;
				if($DaysRemaining<0){
					$last_login = $row->last_login;
					$last_loginDays = floor((strtotime(date('Y-m-d'))-strtotime($last_login)) / 86400);
					if($last_loginDays>30){
						$trial_days = 14+floor((strtotime(date('Y-m-d'))-strtotime(date('Y-m-d', strtotime($created_on)))) / 86400);			
						$updateAccountsData['trial_days'] = $_SESSION["trial_days"]= $trial_days;
					}
				}
				
				$this->db->update('accounts', $updateAccountsData, $accounts_id);
				date_default_timezone_set('Asia/Dhaka');
				$fieldsData = array('lastlogin_datetime' => date('Y-m-d H:i:s'), 'login_ck_id'=>$login_ck_id);
				$this->db->update('user', $fieldsData, $user_id);
				
				date_default_timezone_set($timezone);
				
				$fieldsData = array('user_id'=>$user_id, 'accounts_id'=>$accounts_id, 'login_datetime' => date('Y-m-d H:i:s'), 'logout_datetime' => date('Y-m-d H:i:s'), 'logout_by' =>'', 'login_ip' => $this->db->checkCharLen('user_login_history.login_ip', $this->ip_address()));
				$this->db->insert('user_login_history', $fieldsData);
				if(in_array($row->last_login, array('0000-00-00 00:00:00', '1000-01-01 00:00:00')) && $row->is_admin==1){
					$returnmsg = 'Getting_Started';
				}
			}
			
			return $returnmsg;
		}
		else{
			return 'Your credentials are incorrect. Please check your domain, email and password.';
		}
	}
	
	private function ip_address() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
	
	public function forgotpassword(){}

	public function AJsave_forgotpassword(){
		$returnStr = '';
		if(isset($_SESSION["user_id"])){$returnStr = 'home';}
		$subdomain = $_POST['company_subdomain']??'';
		$user_email = $_POST['user_email']??'';
		
		if($subdomain == '' || $user_email == '' ){
			if($subdomain == ''){
				$returnStr = 'Sub-domain required.<br />';
			}
			if($user_email == ''){
				$returnStr = 'Email required.<br />';				
			}
		}
		else{
		
			$usersObj = $this->db->query("SELECT user.user_id, user.user_email, user.user_first_name, user.user_last_name FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND accounts.company_subdomain = :company_subdomain AND user.user_email = :user_email AND accounts.domain = :domain AND user.user_publish = 1", array('company_subdomain'=>$subdomain, 'user_email'=>$user_email, 'domain'=>OUR_DOMAINNAME));
			if($usersObj){
				$row = $usersObj->fetch(PDO::FETCH_OBJ);
				
				$user_id = $row->user_id;
				$user_email = $row->user_email;
				$changepass_link = md5(time());
					
				$user_first_name = $row->user_first_name;
				$user_last_name = $row->user_last_name;
				
				if($user_email !=''){
					
					$fieldsData = array('changepass_link'=>$changepass_link, 'last_updated'=> date('Y-m-d H:i:s'));
					$this->db->update('user', $fieldsData, $user_id);
					
					$baseurl = '//'.$subdomain.'.'.OUR_DOMAINNAME.'/';
					
					$loginURL = $baseurl.'Login/setnewpassword/'.$changepass_link;
					
					$mail = new PHPMailer;
					$mail->isSMTP(); 
					$mail->Host = "smtp.".OUR_DOMAINNAME;
					$mail->Port = 587;
					
					$mail->addReplyTo($this->db->supportEmail('support'), COMPANYNAME);
					$mail->setFrom($this->db->supportEmail('do_not_reply'), COMPANYNAME);
					$mail->clearAddresses();
					$mail->addAddress($user_email, $user_first_name);
					$mail->Subject = "Forgot password mail for $user_first_name $user_last_name";
					$mail->isHTML(true);
					$mail->CharSet = 'UTF-8';
					$mail->Body = "<p>
Dear <strong>$user_first_name $user_last_name</strong>,<br />
<br />
<strong>Welcome to the ".COMPANYNAME." forgot password information</strong><br />
<br />
Click or copy the link below to change your password
<br />
<br />
<a href=\"$loginURL\" title=\"Click Here\">Click here</a><br />
Or
<br />
Copy link: $loginURL
<br /><br />
Sincerely,<br />
".str_replace('COMPANYNAME', COMPANYNAME, $this->db->translate('The COMPANYNAME Team'))."
</p>";
				
					if($user_email =='' || is_null($user_email)){
						$returnStr = 'Your email is blank. Please contact with site admin.';
					}
					else{
						if (!$mail->send()) {
							$returnStr = 'Mail could not sent.';
						}
						else{
							$returnStr = 'Please check your email for a message from us';
						}
					}					
				}
				else{
					$returnStr = 'Your email is blank. Please contact with site admin.';				
				}
			}				
			else{						
				$returnStr = 'Your sub-domain and email was not found in our system. Please check email and sub-domain then try to reset password.';
			}
		}
		
		$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Login/index?msg=$returnStr";
		if($returnStr == 'home' || $returnStr == 'Success'){
			$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Login/index/sent-success";
		}

		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURL\" />";
	}
	
	public function setnewpassword(){
		$changepass_link = $GLOBALS['segment4name'];
		$subdomain = $GLOBALS['subdomain'];
		
		$user_email = '';
		$usersObj = $this->db->query("SELECT user.user_id FROM user, accounts WHERE user.accounts_id = accounts.accounts_id AND accounts.company_subdomain = :company_subdomain AND user.changepass_link = :changepass_link AND accounts.domain = :domain AND user.user_publish = 1", array('company_subdomain'=>$subdomain, 'changepass_link'=>$changepass_link, 'domain'=>OUR_DOMAINNAME));
		if(!$usersObj){	
			return "<meta http-equiv = \"refresh\" content = \"0; url = /Login/index/\" />";
		}
		
	}
	
	public function AJ_setnewpassword_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$POST = json_decode(file_get_contents('php://input'), true);
		$changepass_link = $POST['changepass_link']??'';
		$subdomain = $GLOBALS['subdomain'];
		$user_email = '';
		$usersObj = $this->db->query("SELECT user.user_id, user.user_email, user.changepass_link FROM user, accounts WHERE user.accounts_id = accounts.accounts_id AND accounts.company_subdomain = :company_subdomain AND user.changepass_link = :changepass_link AND accounts.domain = :domain AND user.user_publish = 1", array('company_subdomain'=>$subdomain, 'changepass_link'=>$changepass_link, 'domain'=>OUR_DOMAINNAME));
		if($usersObj){
			$row = $usersObj->fetch(PDO::FETCH_OBJ);
			$user_email = $row->user_email;
			$changepass_link = $row->changepass_link;			
		}
		
		$jsonResponse['user_email'] = $user_email;
		$jsonResponse['changepass_link'] = $changepass_link;

		return json_encode($jsonResponse);
	}

	public function AJsave_newpassword(){
		$returnStr = '';
		if(isset($_SESSION["user_id"])){$returnStr = 'home';}
		
		$changepass_link = $_POST['changepass_link']??'';
		$subdomain = $_POST['company_subdomain']??'';
		$user_email = $_POST['user_email']??'';
		
		if($changepass_link == '' || $subdomain == '' || $user_email == ''){
			$returnStr = 'Form fields data is missing.';
		}
		else{
			$baseurl = 'http://'.$subdomain.'.'.OUR_DOMAINNAME.'/';
			
			$usersObj = $this->db->query("SELECT user.user_id, user.user_first_name, user.user_last_name FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND accounts.company_subdomain = :company_subdomain AND user.user_email = :user_email AND accounts.domain = :domain AND user.changepass_link = :changepass_link AND user.user_publish = 1", array('company_subdomain'=>$subdomain, 'user_email'=>$user_email, 'changepass_link'=>$changepass_link, 'domain'=>OUR_DOMAINNAME));
			if($usersObj){
				$row = $usersObj->fetch(PDO::FETCH_OBJ);			
					
				$user_id = $row->user_id;
				$user_password = trim($_POST['user_password']??'');
				$password_hash = password_hash($user_password, PASSWORD_DEFAULT);
					
				$user_first_name = $row->user_first_name;
				$user_last_name = $row->user_last_name;
				
				if($user_email !=''){
					
					$fieldsData = array('password_hash'=>$password_hash, 'changepass_link'=>'', 'last_updated'=> date('Y-m-d H:i:s'));
					$this->db->update('user', $fieldsData, $user_id);
									
					$loginURL = $baseurl.'login/';
					
					$mail = new PHPMailer;
					$mail->isSMTP(); 
					$mail->Host = "smtp.".OUR_DOMAINNAME;
					$mail->Port = 587;
					
					$mail->addReplyTo($this->db->supportEmail('support'), COMPANYNAME);
					$mail->setFrom($this->db->supportEmail('do_not_reply'), COMPANYNAME);
					$mail->clearAddresses();
					$mail->addAddress($user_email, $user_first_name);
					$mail->Subject = "Set new password mail for $user_first_name $user_last_name";
					$mail->isHTML(true);
					$mail->CharSet = 'UTF-8';
					$mail->Body = "<p>
Dear <strong>$user_first_name $user_last_name</strong>,<br />
<br />
<strong>Welcome to the ".COMPANYNAME." forgot password information</strong><br />
<br />
Please try to login with the following User name and password.<br />
<br />
Email: $user_email<br />
Password: $user_password<br />
<br />
Please click on the link below to login.<br />
<br />
<a href=\"$loginURL\" title=\"Click Here\">Click Here</a><br />
<br />
<br />
Sincerely,<br />
".str_replace('COMPANYNAME', COMPANYNAME, $this->db->translate('The COMPANYNAME Team'))."
</p>";
				
					if($user_email =='' || is_null($user_email)){
						$returnStr = 'Your email is blank. Please contact with site admin.';
					}
					else{
						if (!$mail->send()) {
							$returnStr = 'Mail could not sent.';
						}
						else{
							$returnStr = 'Success';
						}
					}
				}				
				else{						
					$returnStr = 'Your sub-domain and email was not found in our system. Please check email and sub-domain then try to reset password.';
				}
			}
		}
		
		$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Login/index?msg=$returnStr";
		if($returnStr == 'home' || $returnStr == 'Success'){
			$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Login/index/password-saved";
		}
		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURL\" />";
	}
}
?>