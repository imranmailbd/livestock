<?php
// declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('log_errors', '1');
session_start();

$serverexp = explode('.', $_SERVER['SERVER_NAME']);

$subdomain = '';
if(count($serverexp)>2){
	$subdomain = implode(array_slice($serverexp, -3, 1));
}

$sitename =  strtolower(implode('.', array_slice($serverexp, -2, 2)));

define('OUR_DOMAINNAME', $sitename);

define('swVersion', 'v3');
define('printsJS', 'prints.js');
define('commonJS', 'common.js');

if(!in_array(OUR_DOMAINNAME, array('skitsbd.com', 'skitsbdl.com'))){
	echo 'There has been a error with your request.  If you have questions please email support@skitsbd.com';
	exit;
}

if(in_array(OUR_DOMAINNAME, array('skitsbdl.com'))){error_reporting(-1);}
else{error_reporting(0);}

$timezone = $_SESSION['timezone']??'Asia/Dhaka';
date_default_timezone_set($timezone);

define('COMPANYNAME', 'SKIT Livestock ERP');

$uri = $_SERVER['PHP_SELF'];
$segments =  array_slice(explode('/',$uri), 1);
$segment1name=$segment2name=$segment3name = $segment4name = $segment5name = $segment6name = $segment7name = $segment8name = '';
if(!empty($segments)){
	$segment1name = trim($segments[0]);	
	if(count($segments)>1){
		$segment2name = trim($segments[1]);
		if(count($segments)>2){
			$segment3name = trim($segments[2]);
			if(count($segments)>3){
				$segment4name = trim($segments[3]);				
				if(count($segments)>4){
					$segment5name = trim($segments[4]);
					if(count($segments)>5){
						$segment6name = trim($segments[5]);
						if(count($segments)>6){
							$segment7name = trim($segments[6]);
							if(count($segments)>7){
								$segment8name = trim($segments[7]);
							}
						}
					}
				}
			}
		}
	}
}
define('moduleName', $segment2name);
$loadLangFile = 'English';

if(isset($_SESSION) && array_key_exists('language', $_SESSION) && $_SESSION['language'] !='All Others'){
	$loadLangFile = $_SESSION['language'];
	if(empty($loadLangFile)){$_SESSION['language'] = $loadLangFile = 'English';}
}
spl_autoload_register(function ($class_name) {
	$class_name = str_replace('PHPMailer/PHPMailer/', 'PHPMailer/', str_replace('\\', '/', $class_name));
	$fullPath = "apps/$class_name.php";
		
	if(!file_exists($fullPath)){return false;}
	require_once ($fullPath);
});

$filename = "apps/languages/$loadLangFile.php";

if (file_exists($filename)) {
	require_once ($filename);
}
else{
	$serverPath = getcwd();
	require_once ("$serverPath/apps/languages/$loadLangFile.php");
}

if(isset($_SESSION) && array_key_exists('languageVar', $_SESSION)){
	$languageVar = $_SESSION['languageVar'];
	if(!empty($languageVar)){extract($languageVar);}
}

$db = new Db();

set_error_handler(function(int $num, string $str, string $file, string $line) {
	$accounts_id = $_SESSION['accounts_id']??0;
	$user_id = $_SESSION['user_id']??0;
	$Browser = explode(' (', $_SERVER['HTTP_USER_AGENT']);
	$message = "Encountered error $num in $file, line $line: $str, [AccID: $accounts_id, UserID: $user_id, Browser: $Browser[0]]";
	$GLOBALS['db']->writeIntoLog($message);
});

if($segment2name=='DatabaseUpdate'){
	if(!isset($_SESSION) || !array_key_exists('accounts_id', $_SESSION) || $_SESSION['accounts_id'] >6){
		header('location:/Account/login/session_ended');
		exit;
	}	
	$clsObj = new $segment2name($db);
	$clsFuncNames = get_class_methods($clsObj);
	if(in_array($segment3name, $clsFuncNames)){
		echo $clsObj->$segment3name($segment4name, $segment5name);
	}
	exit;	
}
/*
if($_SESSION['accounts_id']??0 >0 && $_SESSION['accounts_id']??0<=6){}
else{
	if(substr($_SERVER['REQUEST_URI'],0,15) !='/DatabaseUpdate'){
		include('update.html');	
		//Need to update user: login_ck_id fields value sothat user will be logout automatically
		session_unset();session_destroy();
		exit;
	}
}
*/
if(in_array($segment2name, array('run_daily_cron', 'Createbarcode', 'widget', 'unsubscribe'))){
	include "apps/$segment2name.php";
	exit;
}
if($segment2name=='showRepairStatus'){mail('support@skitsbd.com', 'OLD API CALL FROM Sub-Domain: '.$subdomain, 'This is OLD API checking mail');}
//echo $segment1name;exit;
if(!in_array($subdomain, array('', 'www')) && in_array($segment1name, array('', 'index.php')) && in_array($segment2name, array('', 'Instancehome', 'Contact-Us', 'Customer', 'Services', 'Product', 'Livestock', 'Quote', 'Appointment', 'Check_Repair_Status'))){
	$segment2 = '';
	
	if(in_array($segment2name, array('Contact-Us', 'Customer', 'Services', 'Product', 'Livestock', 'Quote', 'Appointment', 'Check_Repair_Status'))){
		$segment2 = $segment2name;
	}	
	$clsObj = new Instancehome($db);
	
	$clsFuncNames = get_class_methods($clsObj);
	$viewFunctions = array('index'=>stripslashes(COMPANYNAME.' Software'), 'Contact-Us'=>$db->translate('Contact Us'), 'Customer'=>$db->translate('Add Customer Information'), 'Services'=>$db->translate('Services'), 'Product'=>$db->translate('Products'), 'Livestock'=>$db->translate('Live Stocks'), 'Quote'=>$db->translate('Request a Quote'), 'Appointment'=>$db->translate('Repair Appointment'), 'Check_Repair_Status'=>$db->translate('Check Repair Status Online'));
	if(!empty($segment2) && array_key_exists($segment2, $viewFunctions)){
		$title = $viewFunctions[$segment2];
		$functionName = str_replace('-', '_', $segment2);
	}
	else{
		$title = $viewFunctions['index'];
		$functionName = $segment3name;
		if(empty($functionName)){$functionName = 'index';}
	}
	
	$ihSql = "SELECT instance_home.website_on FROM accounts a LEFT JOIN instance_home ON (instance_home.accounts_id = a.accounts_id) WHERE a.company_subdomain =:company_subdomain ORDER BY a.accounts_id ASC LIMIT 0,1";
	$ihObj = $db->querypagination($ihSql, array('company_subdomain'=>$subdomain));
	
	if($ihObj){
		
		$website_on = intval($ihObj[0]['website_on']);
		if($website_on==0){
			header('location:/Account/login/');
			exit;
		}
	}
	else{
		header('location:http://'.OUR_DOMAINNAME);exit;
	}
	
	echo $clsObj->$functionName();
	
	exit;
}
if($segment2name=='session_ended'){
	header('location:/Account/login/session_ended');
	exit;
}

if(in_array($segment2name, array('Login', 'Signup', 'login', 'signup'))){		
	$clsObj = new Account($db);
	$functionname = 'old'.ucfirst(strtolower($segment2name));
	echo $clsObj->$functionname();
	exit;	
}

if(in_array($segment2name, array('BulkSMS', 'Squareup'))){	
	if(in_array($segment2name, array('Squareup')) && empty($segment3name)){
		$segment3name = 'index';
	}
	
	$clsObj = new $segment2name($db);
	$clsFuncNames = get_class_methods($clsObj);
	if(in_array($segment3name, $clsFuncNames)){
		echo $clsObj->$segment3name($segment4name, $segment5name);
	}
	exit;	
}

if($segment2name=='Account'){
	
	if(empty($segment3name)){$segment3name = 'login';}
	
	$clsObj = new $segment2name($db);
	$clsFuncNames = get_class_methods($clsObj);
	$viewFunctions = array('signup'=>stripslashes(COMPANYNAME.' Software Trial Signup Page'), 'login'=>stripslashes('Login into '.COMPANYNAME), 
							'forgotpassword'=>stripslashes('Forgot Password'), 'setnewpassword'=>stripslashes('Set New Password'),
							'payment_details'=>$db->translate('Payment Details'), 'locations'=>$db->translate('Locations'));
	
	if(in_array($segment3name, $clsFuncNames) && !array_key_exists($segment3name, $viewFunctions)){
		echo $clsObj->$segment3name($segment4name, $segment5name);
		exit;
	}
	//echo 'Ok';exit;
	if(array_key_exists($segment3name, $viewFunctions)){
		$title = $viewFunctions[$segment3name];
	}
	else{
		$title = array_key_exists($viewFunctions['index'])?$viewFunctions['index']:'';
		$segment3name = 'index';
	}
	$Template = new Template($db);
	echo $Template->headerHTML();
	echo $clsObj->$segment3name($segment4name);
	echo $Template->footerHTML();
	exit;
}

if(!in_array($segment2name, array('Home', 'POS', 'Repairs', 'Invoices', 'Customers', 'Suppliers', 'Products', 'Livestocks', 
	'Purchase_orders', 'Orders', 'IMEI', 'Expenses', 'Inventory_Transfer', 'Dashboard', 'End_of_Day', 'Appointment_Calendar', 
	'Accounts_Receivables', 'Time_Clock', 'Website', 'Commissions', 'Sales_reports', 
	'Repairs_reports', 'Inventory_reports', 'Activity_Feed', 'Getting_Started', 'Manage_Data', 
	'Settings', 'Integrations', 'Search', 'Common', 'Admin', 'Carts', 'Payments', 'Stock_Take', 'Accounts'))){
		
	$POST = json_decode(file_get_contents('php://input'), true);
	if(is_array($POST) && !empty($POST)){
		echo json_encode(array('login'=>'Home/notpermitted/'));
	}
	else{
		if(isset($_SESSION['accounts_id'])){
			header('location:/Home/index');
		}
		else{			
			header('location:/Account/login/');
		}
	}
	exit;	
}

$allowSeg2Name = $segment2name;
$accounts_id = $_SESSION['accounts_id']??0;
$admin_id = $_SESSION['admin_id']??0;
$Template = new Template($db);
$allowedModule = 1;
if(!empty($_SESSION['allowed'])){
	$allowedModulesIds = $_SESSION['allowed'];
	$modules = $Template->modules();
	foreach($modules as $label=>$moduleInfo){
		$moduleSeg = $moduleInfo[0];
		$moduleId = $moduleInfo[1];
		if($allowSeg2Name==$moduleSeg && !array_key_exists($moduleId, $allowedModulesIds)){			
			$allowedModule = 0;
		}
	}
}

if(in_array($allowSeg2Name, array('Home', 'Common', 'Carts', 'Payments', 'Search', 'Squareup')) || ($allowSeg2Name=='Admin' && $accounts_id <=6) || ($allowSeg2Name =='Settings' && in_array($segment3name, array('myInfo','AJsave_myInfo', 'AJ_myInfo_MoreInfo')))) {}
elseif($allowedModule==0){
	if(isset($_POST) && !empty($_POST)){
		echo json_encode(array('login'=>'Home/notpermitted/'));
	}
	else{
		header('location:/Home/notpermitted/');
	}
	exit;
}

$POST = json_decode(file_get_contents('php://input'), true);
if((is_array($POST) || (isset($_POST) && !empty($_POST))) && isset($_SESSION['user_id']) && $admin_id==0){
	$user_id = $_SESSION['user_id']??0;
	$sql = "SELECT login_ck_id FROM user WHERE user_id = $user_id";
	$usersObj = $db->query($sql, array(), 1);
	if($usersObj){
		$login_ck_id = $usersObj->fetch(PDO::FETCH_OBJ)->login_ck_id;
		$session_id = session_id();

		if($login_ck_id !='' && $session_id != $login_ck_id){
			echo json_encode(array('login'=>'Account/login/duplicated_user'));
			exit;
		}
	}
}

if(empty($segment3name)){
	if(in_array($segment2name, array('Home', 'POS'))){$segment3name = 'index';}
	elseif($segment2name=='End_of_Day'){$segment3name = 'view';}
	elseif($segment2name=='Getting_Started'){$segment3name = 'accounts_setup';}
	elseif($segment2name=='Manage_Data'){$segment3name = 'export';}
	elseif($segment2name=='Settings'){$segment3name = 'myInfo';}
	elseif($segment2name=='Integrations'){$segment3name = 'bulkSMS';}
	elseif($segment2name=='Accounts'){$segment3name = 'dashboard';}
	else{$segment3name = 'lists';}
}

$clsObj = new $segment2name($db);
$clsFuncNames = get_class_methods($clsObj);

if($segment2name=='Manage_Data' && in_array($segment3name, array('export_data_csv', 'exportPerData'))){
	$exportdata =  $clsObj->$segment3name($segment4name, $segment5name);
	$filename = date('Y-m-d-H-i-s').'-';
	if($segment3name=='export_data_csv'){
		$filename .= str_replace(' ', '-', $_POST['export_type']??'').'.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$filename);
	}
	elseif($segment3name=='exportPerData'){
		$filename .= str_replace(' ', '-', $_POST['customerName']??'').'.txt';
		header('Content-Type: apps/force-download');
		header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
		header('Connection: close');
	}
	
	//echo '<pre>',print_r($exportdata), '</pre>';
	$content = '';
	$output = fopen('php://output', 'w');
	if(!empty($exportdata) && is_array($exportdata)){
		foreach($exportdata as $oneRow){
			if($segment3name=='exportPerData'){
				$content .= "$oneRow
";
			}
			else{fputcsv($output, $oneRow);}
		}
	}
	if($segment3name=='exportPerData'){
		fwrite($output, $content);
	}
	fclose($output);
	exit;
}

$viewFunctions = array();
if($segment2name=='Home'){$viewFunctions = array('index'=>$db->translate('Welcome to').' '.COMPANYNAME, 'help'=>$db->translate('Help'), 'notpermitted'=>$db->translate('Not Permitted'));}
elseif($segment2name=='POS'){$viewFunctions = array('index'=>$db->translate('Sales Register'));}
elseif($segment2name=='Repairs'){$viewFunctions = array('lists'=>$db->translate('Repairs'), 'add'=>$db->translate('New Repair Ticket'), 'edit'=>$db->translate('Repair Ticket'));}
elseif($segment2name=='Invoices'){$viewFunctions = array('lists'=>$db->translate('Sales Invoices'), 'view'=>$db->translate('View Invoice')." - s$segment4name", 'refund'=>$db->translate('Refund Items'));}
elseif($segment2name=='Customers'){$viewFunctions = array('lists'=>$db->translate('Manage Customers'), 'view'=>$db->translate('Customer Information'), 'crm'=>$db->translate('Manage CRM'));}
elseif($segment2name=='Suppliers'){$viewFunctions = array('lists'=>$db->translate('Manage Suppliers'), 'view'=>$db->translate('Supplier Information'));}
elseif($segment2name=='Livestocks'){$viewFunctions = array('lists'=>$db->translate('Manage Live Stocks'), 'view'=>$db->translate('Live Stocks Information'));}
elseif($segment2name=='Products'){$viewFunctions = array('lists'=>$db->translate('Manage Products'), 'view'=>$db->translate('Product Information'));}
elseif($segment2name=='Purchase_orders'){$viewFunctions = array('lists'=>$db->translate('Purchase Order'), 'add'=>$db->translate('Create Purchase Order'), 'returnPO'=>$db->translate('Return Purchase Order'), 'confirmReturn'=>$db->translate('Confirm Purchase Order'), 'edit'=>$db->translate('Purchase Order')." p$segment4name");}
elseif($segment2name=='Stock_Take'){$viewFunctions = array('lists'=>$db->translate('Stock Take Information'), 'add'=>$db->translate('Create Stock Take'), 'edit'=>$db->translate('Stock Take Information'));}
elseif($segment2name=='Orders'){$viewFunctions = array('lists'=>$db->translate('Customer Orders'), 'add'=>$db->translate('Add Order'), 'edit'=>$db->translate('Edit Order')." o$segment4name");}
elseif($segment2name=='IMEI'){$viewFunctions = array('lists'=>$db->translate('Devices Inventory'), 'view'=>$db->translate('Device Information'), 'tile_view'=>$db->translate('Devices Dashboard'));}
elseif($segment2name=='Expenses'){$viewFunctions = array('lists'=>$db->translate('Manage Expenses'), 'view'=>$db->translate('Expenses Details'), 'profit_loss'=>$db->translate('P&L Statement'));}
elseif($segment2name=='Inventory_Transfer'){$viewFunctions = array('lists'=>$db->translate('Inventory Transfer'), 'add'=>$db->translate('Create Inventory Transfer'), 'edit'=>$db->translate('Change Inventory Transfer')." p$segment4name");}
elseif($segment2name=='Dashboard'){$viewFunctions = array('lists'=>$db->translate('Dashboard'));}
elseif($segment2name=='End_of_Day'){$viewFunctions = array('lists'=>$db->translate('Manage End of Day'), 'view'=>$db->translate('End of Day Report'));}
elseif($segment2name=='Appointment_Calendar'){$viewFunctions = array('lists'=>$db->translate('Appointment Calendar'));}
elseif($segment2name=='Accounts_Receivables'){$viewFunctions = array('lists'=>$db->translate('Accounts Receivables'), 'view'=>$db->translate('Accounts Receivables Details'));}
elseif($segment2name=='Time_Clock'){$viewFunctions = array('lists'=>$db->translate('Time Clock Manager'), 'view'=>$db->translate('Time Clock Information'), 'report'=>$db->translate('Time Report'));}
elseif($segment2name=='Website'){$viewFunctions = array('lists'=>$db->translate('Manage Website'), 'all_pages_header'=>$db->translate('All pages header'), 'all_pages_footer'=>$db->translate('All pages footer'), 'home_page_body'=>$db->translate('Home Page Body'), 'ContactUs'=>$db->translate('Contact Us'), 'Customer'=>$db->translate('Display Add Customer Information'), 'services'=>$db->translate('Services'), 'products'=>$db->translate('Products'), 'livestocks'=>$db->translate('Live Stocks'), 'cell_phones'=>$db->translate('Live Stocks'), 'Quote'=>$db->translate('Request a Quote'), 'Appointment'=>$db->translate('Repair Appointment'), 'RStatus'=>$db->translate('Check Repair Status'));}
elseif($segment2name=='Commissions'){$viewFunctions = array('lists'=>$db->translate('Manage Commissions'), 'view'=>$db->translate('Commission Details'), 'report'=>$db->translate('Commission Report'));}
elseif($segment2name=='Sales_reports'){$viewFunctions = array('lists'=>$db->translate('Sales Reports'), 'sales_by_Date'=>$db->translate('Sales by Date'), 'sales_by_Employee'=>$db->translate('Sales by Sales Person'), 'sales_by_Customer'=>$db->translate('Sales by Customer'), 'sales_by_Paymenttype'=>$db->translate('Payments Received by Type'), 'sales_by_Product'=>$db->translate('Sales by Product'), 'sales_by_Category'=>$db->translate('Sales by Category'), 'sales_by_Tax'=>$db->translate('Sales by Tax'));}
elseif($segment2name=='Repairs_reports'){$viewFunctions = array('lists'=>$db->translate('Repairs Reports'), 'repairs_by_status'=>$db->translate('Repairs by status'), 'repairs_by_problem'=>$db->translate('Repairs by problems'), 'sales_by_Technician'=>$db->translate('Sales by Technician'), 'repair_Tickets_Created'=>$db->translate('Repair Tickets Created'));}
elseif($segment2name=='Inventory_reports'){$viewFunctions = array('lists'=>$db->translate('Inventory Reports'), 'inventory_Value'=>$db->translate('Inventory Value'), 'inventory_ValueN'=>$db->translate('Inventory ValueN'), 'inventory_Purchased'=>$db->translate('Inventory Purchased'), 'products_Report'=>$db->translate('Products Report'), 'livestocks_Report'=>$db->translate('Live Stocks Report'), 'purchase_Orders'=>$db->translate('Purchase Orders'));}
elseif($segment2name=='Activity_Feed'){$viewFunctions = array('lists'=>$db->translate('Activity Report'));}
elseif($segment2name=='Getting_Started'){$viewFunctions = array('accounts_setup'=>$db->translate('Accounts Setup'), 'company_info'=>$db->translate('Company Information'), 'taxes'=>$db->translate('Manage Taxes'), 'payment_options'=>$db->translate('Payment Options'), 'import_customers'=>$db->translate('Import Customers'), 'import_products'=>$db->translate('Import Products'), 'import_livestocks'=>$db->translate('Import Live Stocks'), 'small_print'=>$db->translate('Receipt Printer & Cash Drawer'), 'label_printer'=>$db->translate('Manage Label Printer'));}
elseif($segment2name=='Manage_Data'){$viewFunctions = array('export'=>$db->translate('Export Data'), 'archive_Data'=>$db->translate('Archive Data'), 'lsnipplesizescore'=>$db->translate('Manage Nipple Size Score'), 'lsbcscore'=>$db->translate('Manage Body Condition Score'), 'lsclassification'=>$db->translate('Manage Classification'), 'lssection'=>$db->translate('Manage Section'), 'lsbreed'=>$db->translate('Manage Breed'), 'lslocation'=>$db->translate('Manage Location'), 'lsgroups'=>$db->translate('Manage Groups'), 'suppliers'=>$db->translate('Manage Suppliers'), 'sview'=>$db->translate('Suppliers Information'), 'category'=>$db->translate('Manage Categories'), 'manufacturer'=>$db->translate('Manage Manufacturer'), 'repair_problems'=>$db->translate('Manage Repair Problem'), 'brand_model'=>$db->translate('Manage Brand Model'), 'vendors'=>$db->translate('Manage Vendors'), 'expense_type'=>$db->translate('Manage Expense Type'), 'customer_type'=>$db->translate('Manage Customer Type'), 'eu_gdpr'=>$db->translate('Manage EU GDPR'));}
elseif($segment2name=='Settings'){$viewFunctions = array('myInfo'=>$db->translate('My Information'), 'user'=>$db->translate('Setup Users'), 'po_setup'=>$db->translate('PO Setup'), 'barcode_labels'=>$db->translate('Barcode Labels'), 'restrict_access'=>$db->translate('Restrict Access'),'carriers'=>$db->translate('Carriers'), 'conditions'=>$db->translate('Conditions'), 'devices_custom_fields'=>$db->translate('Custom Fields'),'cash_Register_general'=>$db->translate('Cash Register').':: '.$db->translate('General'),'counting_Cash_Til'=>$db->translate('Cash Register').':: '.$db->translate('Counting Cash Til'),'multiple_Drawers'=>$db->translate('Cash Register').':: '.$db->translate('Multiple Drawers'),'invoices_general'=>$db->translate('Invoice Setup').':: '.$db->translate('General'),'customStatuses'=>$db->translate('Custom Statuses'), 'repairCustomStatuses'=>$db->translate('Custom Statuses'), 'ordersPrint'=>$db->translate('Orders Print'),'repairs_general'=>$db->translate('Repairs').':: '.$db->translate('General'), 'notifications'=>$db->translate('Repairs').':: '.$db->translate('Notifications'),'repairs_custom_fields'=>$db->translate('Repairs').':: '.$db->translate('Custom Fields'), 'forms'=>$db->translate('Repairs').':: '.$db->translate('Forms'), 'formFields'=>$db->translate('Repairs').':: '.$db->translate('Form Fields'), 'customers_custom_fields'=>$db->translate('Customers').':: '.$db->translate('Custom Fields'), 'products_custom_fields'=>$db->translate('Products').':: '.$db->translate('Custom Fields'));}
elseif($segment2name=='Integrations'){$viewFunctions = array('bulkSMS'=>$db->translate('Manage SMS Messaging'));}//'squareup'=>$db->translate('Square Credit Card Processing'), 
elseif($segment2name=='Admin'){$viewFunctions = array('lists'=>$db->translate('Manage Accounts'), 'edit'=>$db->translate('Accounts Information'), 'invoicesReport'=>$db->translate('Invoices Report'), 'importCustomers'=>$db->translate('Import Customers'), 'importProduct'=>$db->translate('Import Products'), 'popup_message'=>$db->translate('Popup Message'), 'login_message'=>$db->translate('Login Message'), 'languages'=>$db->translate('Languages'), 'our_notes'=>$db->translate('Our Notes'));}
elseif($segment2name=='Accounts'){
	$viewFunctions = array('dashboard'=>stripslashes('Accounts Dashboard'), 'groups'=>stripslashes('Manage Groups'), 'ledger'=>stripslashes('Manage Ledger'), 'ledgerView'=>'Ledger Details Information', 'receiptVoucher'=>stripslashes('Receipt Voucher'), 'paymentVoucher'=>stripslashes('Payment Voucher'), 'journalVoucher'=>stripslashes('Journal Voucher'), 'contraVoucher'=>stripslashes('Contra Voucher'), 'purchaseVoucher'=>stripslashes('Purchase Voucher'), 'salesVoucher'=>stripslashes('Sales Voucher'), 'dayBook'=>stripslashes('Day Book Report'), 'ledgerReport'=>stripslashes('Ledger Report'), 'trialBalance'=>stripslashes('Trial Balance'), 'receiptPayment'=>stripslashes('Receipt & Payment'));//, 'cOGS'=>stripslashes('COGS (Cost of Good Sold)'), 'incomeStatement'=>stripslashes('Income Statement'), 'financialPosition'=>stripslashes('Financial Position'), 'cashFlow'=>stripslashes('Cash Flow'), 'shareholderEquity'=>stripslashes('Shareholder Equity')
}

if(in_array($segment3name, $clsFuncNames) && !array_key_exists($segment3name, $viewFunctions)){

	if(!isset($_SESSION['accounts_id']) || !isset($_SESSION['user_id'])){
		echo json_encode(array('login'=>'Account/login/session_ended'));
		exit;
	}
	$user_id = $_SESSION['user_id']??0;
	if($user_id>0){
		$timezone = 'America/New_York';
		date_default_timezone_set($timezone);
		$db->update('user', array('last_request'=> date('Y-m-d H:i:s')), $user_id);
		if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
		date_default_timezone_set($timezone);
	}
	if($segment2name=='Admin' && $_SESSION['accounts_id'] >6){
		echo json_encode(array('login'=>'Home/notpermitted'));
		exit;
	}
	
	echo $clsObj->$segment3name($segment4name, $segment5name);
	exit;
}

if(!isset($_SESSION['accounts_id'])){
	//echo 'line#408';exit;
	header('location:/Account/login/session_ended');
	exit;
}

if($segment2name=='Admin' && $_SESSION['accounts_id'] >6){
	header('location:/Home/notpermitted/');
	exit;
}

if(!in_array($segment3name, $clsFuncNames)){
	header('location:/Home/notpermitted/');
	exit;
}

$title = '';
if(array_key_exists($segment3name, $viewFunctions)){$title = $viewFunctions[$segment3name];}

$status = 'Trial';
if(isset($_SESSION['status'])){$status = $_SESSION['status'];}
$created_on = date('Y-m-d H:i:s');
if(isset($_SESSION['created_on'])){$created_on = $_SESSION['created_on'];}
$trial_days = 0;
if(isset($_SESSION['trial_days'])){$trial_days = $_SESSION['trial_days'];}

$registeredDays =  $Template->twoDateDifference($created_on);
$DaysRemaining = $trial_days-$registeredDays;
if($DaysRemaining<0){$DaysRemaining = 0;}

echo $Template->headerHTML();
echo $clsObj->$segment3name($segment4name, $segment5name);
echo $Template->footerHTML();
