<?php
class Template{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	public function headerHTML(){
		$segment2name = $GLOBALS['segment2name'];
		$segment3name = $GLOBALS['segment3name'];
		$segment4name = $GLOBALS['segment4name'];
		if(in_array($segment4name, array('duplicated_user', 'Logout', 'autologout', 'session_ended'))){
			$accounts_id = $_SESSION["accounts_id"]??0;
			$user_id = $_SESSION["user_id"]??0;
			$updated_array = array('last_updated'=> date('Y-m-d H:i:s'), 'login_ck_id'=>'');
			if($user_id>0){
				if($segment4name != 'duplicated_user'){
					$this->db->update('user', $updated_array, $user_id);
				}
				$workstations_id = 0;
				if (isset($_COOKIE["workstation"])) {
					$workstationsName = $_COOKIE["workstation"];
					$WSCountObj = $this->db->query("SELECT workstations_id FROM workstations WHERE accounts_id = $accounts_id AND name=:workstationsName", array('workstationsName'=>$workstationsName));
					if($WSCountObj){
						$workstations_id = $WSCountObj->fetch(PDO::FETCH_OBJ)->workstations_id;
					}
				}
				
				$sql = "SELECT user_login_history_id FROM user_login_history WHERE user_id = :user_id";
				if($workstations_id>0){
					$sql .= " AND workstations_id = $workstations_id";
				}
				$sql .= " ORDER BY user_login_history_id DESC";
				$ulhObj = $this->db->query($sql, array("user_id"=>$user_id),1);
				if($ulhObj){
					$user_login_history_id = $ulhObj->fetch(PDO::FETCH_OBJ)->user_login_history_id;
					$updated_array = array('logout_datetime' => date('Y-m-d H:i:s'));
					if($segment4name=='Logout'){
						$updated_array['logout_by'] = 'User Logout';
					}
					elseif($segment4name=='autologout'){
						$updated_array['logout_by'] = 'Auto Logout';
					}
					elseif($segment4name=='duplicated_user'){
						$updated_array['logout_by'] = 'Another Logout';
					}
					elseif($segment4name=='session_ended'){
						$updated_array['logout_by'] = 'Session Missing';
					}
					$this->db->update('user_login_history', $updated_array, $user_login_history_id);
				}
			}
			session_unset();session_destroy();
		}

		$user_first_name = '';
		if(isset($_SESSION["user_first_name"])){
			$user_first_name = $_SESSION["user_first_name"];
		}

		$company_name = '';
		if(isset($_SESSION["company_name"])){
			$company_name = $_SESSION["company_name"];
		}

		$minute_to_logout = 60;
		if(isset($_SESSION["minute_to_logout"])){
			$minute_to_logout = floor($_SESSION["minute_to_logout"]);
		}

		$currency = '৳';
		if(isset($_SESSION["currency"])){
			$currency = $_SESSION["currency"]??'৳';
		}

		$status = 'Trial';
		if(isset($_SESSION["status"])){
			$status = $_SESSION["status"];
		}
		$price_per_location = 0;
		if(isset($_SESSION["price_per_location"])){
			$price_per_location = $_SESSION["price_per_location"];
		}

		$admin_id = 0;
		if(isset($_SESSION["admin_id"])){
			$admin_id = $_SESSION["admin_id"];
		}
		$trial_days = 0;
		if(isset($_SESSION["trial_days"])){
			$trial_days = $_SESSION["trial_days"];
		}

		$timeformat = $_SESSION["timeformat"]??'12 hour';

		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		
		$prod_cat_man = 0;
		if(isset($_SESSION["prod_cat_man"])){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		}
		$accounts_id = 0;
		if(isset($_SESSION["accounts_id"])){
			$accounts_id = $_SESSION["accounts_id"]??0;
		}
		$is_admin = 0;
		if(isset($_SESSION["is_admin"])){
			$is_admin = $_SESSION["is_admin"];
		}
		$timeclock_enabled = 0;
		if(isset($_SESSION["timeclock_enabled"])){
			$timeclock_enabled = $_SESSION["timeclock_enabled"];
		}
		$multipleLocations = $_SESSION["multipleLocations"]??0;
		
		if(isset($_COOKIE['failcall'])){
			$failcall = $_COOKIE['failcall'];
			setcookie('failcall', '', time() - 3600, '/');
		}

		$prevuri = 'Home';
		if(isset($_SERVER['HTTP_REFERER'])){$prevuri = str_replace($_SERVER['SERVER_NAME'], '', $_SERVER['HTTP_REFERER']);}

		//===============Check Session and Unset================//
		if(isset($_SESSION["current_module"]) && isset($_SESSION["list_filters"]) && strcmp($_SESSION["current_module"],$segment2name) !==0){
			unset($_SESSION["list_filters"]);
		}
		$loadLangFile = $_SESSION["language"]??'English';
		$allowed = array();
		if(isset($_SESSION["allowed"])){$allowed = $_SESSION["allowed"];}
		$jsFileNewNames = array();

		$htmlStr = "<!DOCTYPE html>
		<html>
		<head>
			<meta charset=\"utf-8\">
			<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";
		
		if($segment2name=='Account'){
			$htmlStr .= "
			<meta name=\"description\" content=\"".COMPANYNAME." is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store.\" />
			<meta name=\"keywords\" content=\"Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale\" />";
		}

		$htmlStr .= "
		<title>$GLOBALS[title]</title>
		<link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/assets/images/favicon-32x32.png\">
		<link rel=\"stylesheet\" href=\"/assets/css-".swVersion."/style.css\">";
		if($segment2name=='Website'){
			$htmlStr .= "
			<link rel=\"stylesheet\" href=\"/assets/css-".swVersion."/instancehome.css\">";
		}
		$htmlStr .= "
		<script language=\"JavaScript\" type=\"text/javascript\">
			//=============we are using var instead of let / const because of safari version <14 issues==================// 
			var accountsInfo = [$prod_cat_man,$accounts_id,$admin_id,$is_admin,$timeclock_enabled];
			var currency = '$currency';
			var userFirstName = '$user_first_name';
			var timeformat = '$timeformat';
			var loadLangFile = '$loadLangFile';
			var calenderDate = '$calenderDate';
			var companyName = '".addslashes($company_name)."';	
			var OUR_DOMAINNAME = '".OUR_DOMAINNAME."';	
			var SUBDOMAIN = '$GLOBALS[subdomain]';
			var COMPANYNAME = '".COMPANYNAME."';
			var multipleLocations = $multipleLocations;
			var swVersion = '".swVersion."';
			var timelimit_autoLogOut = '".floor($minute_to_logout*60)."';
			var allowed = ".json_encode($allowed).";
			var jsFileNewNames = ".json_encode($jsFileNewNames).";
			var langModifiedData = {};
			var pathArray = window.location.pathname.split('/');
			var OS;
			var segment1 = 'Home';
			var segment2 = '';
			var segment3 = '';
			var segment4 =  '';
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
			function stripslashes(text) {
				text = text.replace(/\\\'/g, '\'');
				text = text.replace(/\\\\\"/g, '\"');
				text = text.replace(/\\\\0/g, '\\0');
				text = text.replace(/\\\\\\\\/g, '\\\\');
				return text;
			}
		</script>";
		
		$htmlStr .= "
		<script src=\"/assets/js-".swVersion."/languages/$loadLangFile.js\"></script>";

		if(isset($_SESSION) && array_key_exists('languageJSVar', $_SESSION)){
			$languageJSVar = $_SESSION["languageJSVar"];
			if(!empty($languageJSVar)){
				$htmlStr .= "
				<script language=\"JavaScript\" type=\"text/javascript\">
				langModifiedData = {";
				foreach($languageJSVar as $varName=>$varValue){
					$htmlStr .= '
		\''.trim((string) $varName).'\':stripslashes(\''.addslashes($varValue).'\'),';
				}
				$htmlStr .= '}
				</script>';
			}
		}
		
		$htmlStr .= "
		</head>    
		<noscript>
			<style type=\"text/css\">
				#wrapper, #footer{display:none}
			</style>
			<center style=\"font-size:25px; color:red; padding:20px 50px;\">For full functionality of this site it is necessary to enable JavaScript.
			<br />Here are the <a href=\"http://www.enable-javascript.com/\" target=\"_blank\">
			instructions how to enable JavaScript in your web browser</a>.
			</center>
		</noscript>
		<body style=\"display:flex;flex-direction:column;\">
		<div id=\"topheaderbar\"></div>
		<div style=\"flex-grow:1\">
			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"height:100%\">
			<tr id=\"pageBody\">
				<td width=\"100\" style=\"background: #2f3949;\" valign=\"top\" id=\"sideBar\">
					<div class=\"sidebar-wrapper\" id=\"sidebarWrapper\">
						<ul class=\"sidebar-nav settingslefthide\" id=\"sideNav\"></ul>
					</div>
				</td>
				<td valign=\"top\" style=\"width:100%\">
					<div id=\"page-content-wrapper\">
						<div class=\"container-fluid\">
							<div class=\"columnXS12\">";
							
		$created_on = date('Y-m-d H:i:s');
		if(isset($_SESSION["created_on"])){$created_on = $_SESSION["created_on"];}
		
		$registeredDays =  $this->twoDateDifference($created_on);
		$DaysRemaining = $trial_days-$registeredDays;
		if($DaysRemaining<0){$DaysRemaining = 0;}
		
		if($status =='Trial' && $registeredDays<=2 && $admin_id==0){
			$user_id = 0;
			if(isset($_SESSION["user_id"])){
				$user_id = $_SESSION["user_id"]??0;
			}
			if($user_id>0){
				$REQUEST_URI = '';
				if(isset($_SERVER['REQUEST_URI'])){$REQUEST_URI = $_SERVER['REQUEST_URI'];}
				
				$activity_feed_title = 'Module';
				$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
				$activity_feed_link = $REQUEST_URI;
				$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
				$afData = array('created_on' => date('Y-m-d H:i:s'),
								'last_updated' => date('Y-m-d H:i:s'),
								'accounts_id' => $accounts_id,
								'user_id' => $user_id,
								'activity_feed_title' => $activity_feed_title,
								'activity_feed_name' =>$REQUEST_URI ,
								'activity_feed_link' => $activity_feed_link,
								'uri_table_name' => "activity_feed",
								'uri_table_field_name' =>"activity_feed_publish",
								'field_value' => 1
								);
				$this->db->insert('activity_feed', $afData);
			}
		}
		if(in_array(OUR_DOMAINNAME, array('livestock.com','livestockl.com'))){//, 'machousel.com.bd'
			
			if(isset($_SESSION["accounts_id"])){
				$headerdemobg = '';
				if($status=='Trial' || $status=='CANCELED'){
					$headerdemobg = ' bgcoolblue';
					if($DaysRemaining<=3){
						$headerdemobg = ' bgred';
					}
					//====================This code for Our Billing=================//
					$alert_dialogAttached = "<script type=\"module\">
					import {alert_dialog} from '/assets/js-".swVersion."/".commonJS."';
					if(document.getElementById(\"YouAreNotAdmin\")){
						document.getElementById(\"YouAreNotAdmin\").addEventListener('click',()=>{
							alert_dialog('".$this->db->translate('You are not admin User')."', '".$this->db->translate('You are not the user that created this account. To subscribe please have the account creator log in and click this button')."', '".$this->db->translate('Ok')."');
						});
					}
					</script>";
					$onClick = "id=\"YouAreNotAdmin\"";
					if($is_admin==1){
						$alert_dialogAttached = '';
						$onClick = "onclick=\"javascript:window.location='/Account/payment_details'\"";
					}
					$h3txt = $this->db->translate('Accounts State:')." $trial_days ".$this->db->translate('Days Free Trial')." - $DaysRemaining ".$this->db->translate('Days Remaining').". <a class=\"btn subscribeButton bgyellow\" style=\"color:red\" href=\"javascript:void(0);\" $onClick title=\"".$this->db->translate('SUBSCRIBE NOW!')."\">".$this->db->translate('SUBSCRIBE NOW!')."</a>$alert_dialogAttached";
					if($status=='CANCELED'){
						$h3txt = $this->db->translate('This account has been CANCELED. To reopen click')." <a class=\"btn subscribeButton bgyellow\" style=\"color:red\" href=\"javascript:void(0);\" $onClick title=\"".$this->db->translate('SUBSCRIBE NOW!')."\">".$this->db->translate('SUBSCRIBE NOW!')."</a>$alert_dialogAttached";
					}
					elseif($DaysRemaining==0){
						$h3txt = $this->db->translate('Trial Period Ended')." &emsp;
								<a class=\"btn subscribeButton bgyellow\" style=\"color:red\" href=\"javascript:void(0);\" $onClick title=\"".$this->db->translate('SUBSCRIBE NOW!')."\">".$this->db->translate('SUBSCRIBE NOW!')."</a>$alert_dialogAttached";
						if($status=='Trial' && $trial_days<=14){ 
							$h3txt .= " &emsp; <a class=\"txtunderline txtwhite\" href=\"/Home/rat/\" title=\"".$this->db->translate('Need more time?')."\">".$this->db->translate('Need more time?')."</a>";
						}
					}
					
					$htmlStr .= "
					<div class=\"$headerdemobg\">
						<h3 style=\"font-size: 18px; font-weight: bold;\">
							$h3txt
							&emsp; 
							<a class=\"txtunderline txtwhite showHelpPopupBtn\" href=\"javascript:void(0);\" title=\"".$this->db->translate('Contact Us')."\">".$this->db->translate('Contact Us')."</a>
						</h3>
					</div>";
				}
				elseif($status=='Payment Due'){
					$status_date = date('Y-m-d');
					$accoutsObj = $db->query("SELECT status_date FROM accounts WHERE accounts_id = $accounts_id", array());
					if($accoutsObj){
						$status_date = $accoutsObj->fetch(PDO::FETCH_OBJ)->status_date;
					}
					
					$PaymentDueDaysRemaining = 3-Headerhelp::twoDateDifference($status_date);
					if($PaymentDueDaysRemaining<0){$PaymentDueDaysRemaining = 0;}
					
					$htmlStr .= "
					<div class=\"bgyellow\">
										<h3 class=\"margin0 txt16bold p4x12px\">
											".$this->db->translate('Account Status:')." ".$this->db->translate('Payment Due').", ".$this->db->translate('you have only')." $PaymentDueDaysRemaining ".$this->db->translate('days until your account will no longer operate. Please update your payment method.')."
											&emsp; 
											<a class=\"txtunderline showHelpPopupBtn\" href=\"javascript:void(0);\" title=\"".$this->db->translate('Contact Us')."\">".$this->db->translate('Contact Us')."</a>
										</h3>
									</div>";
				}
				elseif($status=='SUSPENDED'){
					$htmlStr .= "
					<div class=\"bgred\">
										<h3 style=\"font-size: 18px; font-weight: bold;\">
											".$this->db->translate('Account Status:')." ".$this->db->translate('SUSPENDED').", ".$this->db->translate('Please update your payment method')."
											&emsp; 
											<a class=\"txtunderline txtwhite showHelpPopupBtn\" href=\"javascript:void(0);\" title=\"".$this->db->translate('Contact Us')."\">".$this->db->translate('Contact Us')."</a>
										</h3>
									</div>";
				}
			}
		}
		
		$htmlStr .= "
		<div class=\"dashboard_contant\" id=\"dashboard_contant\">";

		return $htmlStr;
	}
	
	public function footerHTML(){
		$jsfileName = $GLOBALS['segment2name'];
		$type = 'text/javascript';
		
		$jsFileNewNames = array();
		
		if(!empty($jsFileNewNames) && array_key_exists($jsfileName, $jsFileNewNames)){$jsfileName = $jsFileNewNames[$jsfileName];}
		$htmlStr = "";
		if(in_array($GLOBALS['segment2name'], array('POS', 'Orders')) && in_array($GLOBALS['status'], array('SUSPENDED', 'CANCELED'))){
			$htmlStr .= "
			<script type=\"text/javascript\">
		import {alert_dialog} from '/assets/js-".swVersion."/".commonJS."';
		alert_dialog('".$this->db->translate('Accounts SUSPENDED')."', '".$this->db->translate('Your accounts has been suspended. This is most likely do to a billing issue. Please contact us by clicking the help ? at the top right of any page on this application. Thank you')."', '".$this->db->translate('Ok')."');
		</script>";
		}
		elseif(in_array($GLOBALS['segment2name'], array('POS', 'Repairs', 'Orders')) && $GLOBALS['status']=='Trial' && $GLOBALS['DaysRemaining']==0){
			$htmlStr .= "
			<script type=\"module\">
		import {alert_dialog} from '/assets/js-".swVersion."/".commonJS."';
		alert_dialog('".$this->db->translate('Your trial accounts has expired.')."', '".$this->db->translate('Please click Buy Now above to continue to use your accounts.')."', '".$this->db->translate('Ok')."')
		</script>";
		}
		else{
			$htmlStr .= "
			<div id=\"viewPageInfo\"></div>
			<script type=\"module\" src=\"/assets/js-".swVersion."/$jsfileName.js\"></script>";			
		}
		
		$htmlStr .= "
		</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
			</table>
		</div>
		</body>
		</html>";
		return $htmlStr;
	}
	
	public function twoDateDifference($date1, $date2=''){
		$date1 = new DateTime(date('Y-m-d', strtotime($date1)));
		if($date2==''){
			$date2 = new DateTime("now");
		}
		else{
			$date2 = new DateTime(date('Y-m-d', strtotime($date2)));
		}
		$interval = $date1->diff($date2);
		$returnval = 0;
		if($date1 < $date2){
			$returnval = $interval->format('%a');
		}
		
		return $returnval;
	}
	
	public function modules(){
		$returnarray = array();
		$returnarray['Cash Register'] = ['POS', 1]; 
		$returnarray['Repairs'] = ['Repairs', 2]; 
		$returnarray['Invoices'] = ['Invoices', 3]; 
		$returnarray['Customers'] = ['Customers', 4]; 
		$returnarray['Products'] = ['Products', 5]; 
		$returnarray['Purchase Orders'] = ['Purchase_orders', 6]; 
		$returnarray['Orders'] = ['Orders', 7];
		$returnarray['Devices Inventory'] = ['IMEI', 8];
		$returnarray['Stock Take'] = ['Stock_Take', 9]; 
		$returnarray['Expenses'] = ['Expenses', 10];
		$returnarray['Suppliers'] = ['Suppliers', 11]; 
		$returnarray['Inventory Transfer'] = ['Inventory_Transfer', 12]; 
		$returnarray['Dashboard'] = ['Dashboard', 13]; 
		$returnarray['End of Day'] = ['End_of_Day', 14]; 
		$returnarray['Appointment Calendar'] = ['Appointment_Calendar', 15]; 
		$returnarray['Accounts Receivables'] = ['Accounts_Receivables', 16]; 
		$returnarray['Time Clock Manager'] = ['Time_Clock', 17]; 
		$returnarray['Website'] = ['Website', 18]; 
		$returnarray['Commissions'] = ['Commissions', 19]; 
		$returnarray['Sales Reports'] = ['Sales_reports', 20]; 
		$returnarray['Repairs Reports'] = ['Repairs_reports', 21]; 
		$returnarray['Inventory Reports'] = ['Inventory_reports', 22]; 
		$returnarray['Activity Feed'] = ['Activity_Feed', 23]; 
		$returnarray['Getting Started'] = ['Getting_Started', 24]; 
		$returnarray['Manage Data'] = ['Manage_Data', 25]; 
		$returnarray['Setup'] = ['Settings', 26]; 
		$returnarray['Integrations'] = ['Integrations', 27];
		$returnarray['Accounts'] = ['Accounts', 28];
		$returnarray['Live Stocks'] = ['Livestocks', 29]; 
		
		
		return $returnarray;
	}
	
	public function modulesicon(){
		$returnarray = array();
		$returnarray['POS'] = 'shopping-cart'; 
		$returnarray['Repairs'] = 'wrench'; 
		$returnarray['Invoices'] = 'folder-open'; 
		$returnarray['Customers'] = 'address-book'; 
		$returnarray['Products'] = 'barcode'; 
		$returnarray['Purchase_orders'] = 'plus-square'; 
		$returnarray['Suppliers'] = 'address-book';
		$returnarray['Orders'] = 'pencil-square-o'; 
		$returnarray['IMEI'] = 'tablet';
		$returnarray['Stock_Take'] = 'folder-open'; 
		$returnarray['Expenses'] = 'money'; 
		$returnarray['Inventory_Transfer'] = 'truck'; 
		$returnarray['Dashboard'] = 'line-chart'; 
		$returnarray['End_of_Day'] = 'money'; 
		$returnarray['Appointment_Calendar'] = 'calendar-plus-o'; 
		$returnarray['Accounts_Receivables'] = 'credit-card'; 
		$returnarray['Time_Clock'] = 'clock-o'; 
		$returnarray['Website'] = 'globe'; 
		$returnarray['Commissions'] = 'bullhorn'; 
		$returnarray['Sales_reports'] = 'pie-chart'; 
		$returnarray['Repairs_reports'] = 'pie-chart'; 
		$returnarray['Inventory_reports'] = 'pie-chart'; 
		$returnarray['Activity_Feed'] = 'exchange'; 
		$returnarray['Getting_Started'] = 'cog'; 
		$returnarray['Manage_Data'] = 'cog'; 
		$returnarray['Settings'] = 'cog'; 
		$returnarray['Integrations'] = 'compress';	
		$returnarray['Accounts'] = 'money'; 	
		$returnarray['Livestocks'] = 'bullhorn'; 			
		
		return $returnarray;
	}
	
}
?>