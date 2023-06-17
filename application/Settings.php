<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Settings{

   protected $db;
   private int $page, $totalRows;
	public string $pageTitle, $data_type;
	private string $keyword_search;
	
	public function __construct($db){$this->db = $db;}
	
	public function myInfo(){	
		$SetupPer = 1;
		if(!empty($_SESSION["allowed"]) && !array_key_exists(26, $_SESSION["allowed"])) {$SetupPer = 0;}
		return "<input type=\"hidden\" id=\"SetupPer\" value=\"$SetupPer\">";
	}

	public function AJ_myInfo_MoreInfo(){

		$user_id = $_SESSION['user_id']??0;		
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$userObj = $this->db->query("SELECT user_email, user_first_name, user_last_name, is_admin, minute_to_logout FROM user WHERE user_id = $user_id", array());
		if($userObj){
			$userOneRow = $userObj->fetch(PDO::FETCH_OBJ);
			$jsonResponse['user_email'] = $userOneRow->user_email;
			$jsonResponse['user_first_name'] = $userOneRow->user_first_name;
			$jsonResponse['user_last_name'] = $userOneRow->user_last_name;
			$jsonResponse['is_admin'] = $userOneRow->is_admin;
			$jsonResponse['minute_to_logout'] = $userOneRow->minute_to_logout;			
		}
		return json_encode($jsonResponse);
	}
	
	public function AJsave_myInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = '';

		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_email = $POST['user_email']??'';
		$user_email = $this->db->checkCharLen('user.user_email', $user_email);
		
		$user_first_name = $POST['user_first_name']??'';
		$user_first_name = $this->db->checkCharLen('user.user_first_name', $user_first_name);
		
		$user_last_name = $POST['user_last_name']??'';
		$user_last_name = $this->db->checkCharLen('user.user_last_name', $user_last_name);
		
		$minute_to_logout = $POST['minute_to_logout']??60;
		$totalrows = 0;
		$queryObj = $this->db->query("SELECT COUNT(user_id) AS totalrows FROM user WHERE accounts_id = $accounts_id AND user_email = :user_email AND user_id != $user_id AND user_publish = 1", array('user_email'=>$user_email));
		if($queryObj){
			$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		if($totalrows>0){
			$savemsg = 'error';
		}
		else{
			$conditionarray = array();
			$conditionarray['user_email'] = $user_email;
			$user_passwordstr = '';
			if($POST['user_password'] !=''){
				$password_hash = password_hash($POST['user_password'], PASSWORD_DEFAULT);
				$conditionarray['password_hash'] = $password_hash;
			}
			$conditionarray['user_first_name'] = $user_first_name;
			$conditionarray['user_last_name'] = $user_last_name;
			$conditionarray['last_updated'] = date('Y-m-d H:i:s');
			
			date_default_timezone_set('America/New_York');				
			$conditionarray['lastlogin_datetime'] = date('Y-m-d H:i:s');
			date_default_timezone_set($_SESSION["timezone"]);
			
			$conditionarray['minute_to_logout'] = $minute_to_logout;
			
			$updateuser = $this->db->update('user', $conditionarray, $user_id);
			if($updateuser){
				$_SESSION["user_first_name"]=$user_first_name;
				$_SESSION["minute_to_logout"]=$minute_to_logout;
			}

			$savemsg = 'update-success';
		}

		$array = array( 'login'=>'', 'id'=>$user_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	public function user(){}

	public function AJ_user_MoreInfo(){

		$user_id = $_SESSION['user_id']??0;		
		$Template = new Template($this->db);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['modules'] = $Template->modules();
		return json_encode($jsonResponse);
	}
	
	public function AJgetPage_user($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_user();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_user();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_user(){
		$accounts_id = $_SESSION["accounts_id"]??0;

		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Settings";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);

		$sqlPublish = " AND user_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND user_publish = 0";
		}		
		$filterSql = "FROM user WHERE accounts_id = $accounts_id $sqlPublish AND user_email != ''";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(stripslashes(trim((string) $keyword_search)));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', user_first_name, user_last_name, user_email, user_roll)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(user_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
	private function loadTableRows_user(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND user_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND user_publish = 0";
		}		
		$filterSql = "FROM user WHERE accounts_id = $accounts_id $sqlPublish AND user_email != ''";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(stripslashes(trim((string) $keyword_search)));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', user_first_name, user_last_name, user_email, user_roll)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY user_first_name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$user_id = $onerow['user_id'];
				$name = stripslashes(trim("$onerow[user_first_name] $onerow[user_last_name]"));
				$user_email = stripslashes($onerow['user_email']);
				if($onerow['is_admin']>0){
					$name .= " (Admin)";
				}
				$no_restrict_ip = $onerow['no_restrict_ip'];
				
				$tabledata[] = array($user_id, $name, $user_email, $no_restrict_ip);
			}
		}
		return $tabledata;
   }
	
   public function AJsave_user(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 'Ok';		
		$savemsg = '';
		
		$domainname = OUR_DOMAINNAME;

		$user_id = intval($POST['user_id']??0);
		$user_rollarray = $POST['user_roll[]']??array();
		$Common = new Common($this->db);
		
		$userRolls = array();
		if(!empty($user_rollarray) && is_array($user_rollarray)){
			foreach($user_rollarray as $oneModule){
				if(strcmp('Full-Access', $oneModule) !=0){
					$userRolls[$oneModule] = array();
				}
			}
		}

		$subRolls = array(1=>'POS', 2=>'Repairs', 3=>'Invoices', 4=>'Customers', 5=>'Products', 6=>'Purchase_orders', 7=>'Orders', 8=>'IMEI', 9=>'Stock_Take', 14=>'End_of_Day', 25=>'Manage_Data');
		foreach($subRolls as $moduleId=>$moduleName){
			$moduleValues = $POST[$moduleName.'[]']??array();
			$subRollVal = array();
			if(!empty($moduleValues) && is_array($moduleValues)){
				foreach($moduleValues as $oneModule){
					$subRollVal[] = $oneModule;
				}
			}
			if(array_key_exists($moduleId, $userRolls) && !empty($subRollVal)){
				$userRolls[$moduleId] = $subRollVal;
			}
		}
		
		$user_roll = json_encode($userRolls);

		$accounts_id = $_SESSION["accounts_id"]??0;
	   
		$conditionarray = array();
		$conditionarray['user_first_name'] = $user_first_name = $this->db->checkCharLen('user.user_first_name', $POST['user_first_name']??'');
		$conditionarray['user_last_name'] = $user_last_name = $this->db->checkCharLen('user.user_last_name', $POST['user_last_name']??'');
		$conditionarray['user_roll'] = $user_roll;
		$conditionarray['user_email'] = $user_email = $this->db->checkCharLen('user.user_email', $POST['user_email']??'');
		$conditionarray['no_restrict_ip'] = intval($POST['no_restrict_ip']??0);
		$conditionarray['last_updated'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $accounts_id;

		$company_subdomain = $GLOBALS['subdomain'];
		
		if($user_id==0){
			$totalrows = 0;
			$user_publish = 0;
			$queryObj = $this->db->query("SELECT user_id, user_publish FROM user WHERE user_email = :user_email AND accounts_id=$accounts_id ORDER BY user_publish ASC", array('user_email'=>$user_email));
			if($queryObj){
				while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$totalrows++;
					$user_id = intval($onerow->user_id);
					$user_publish = intval($onerow->user_publish);
				}
			}

			if($totalrows>0 && $user_publish==1){
				$returnStr = 'Error';
				$conditionarray['user_publish'] = 1;
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$user_password = $Common->randomPassword();
				$user_password = trim((string) $user_password);
				$password_hash = password_hash($user_password, PASSWORD_DEFAULT);
				
				$conditionarray['password_hash'] = $password_hash;
				$savemsg = 'emailExist';
			}
			else{

				$conditionarray['is_admin'] = 0;

				$user_password = $Common->randomPassword();
				$password_hash = password_hash($user_password, PASSWORD_DEFAULT);

				$conditionarray['password_hash'] = $password_hash;
				$conditionarray['changepass_link'] = '';
				$conditionarray['employee_number'] = '';
				$conditionarray['pin'] = '';
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$conditionarray['lastlogin_datetime'] = '1000-01-01 00:00:00';
				$conditionarray['popup_message'] = '';
				$conditionarray['login_message'] = '';
				$conditionarray['login_ck_id'] = '';
				$conditionarray['last_request'] = '1000-01-01 00:00:00';
				$user_id = $this->db->insert('user', $conditionarray);
				if($user_id){
					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = $this->db->supportEmail('Host');
					$mail->Port = 587;
					$mail->SMTPAuth = true;
					$mail->Username = $this->db->supportEmail('Username');
					$mail->Password = $this->db->supportEmail('Password');
					
					$mail->addReplyTo($this->db->supportEmail('support'), COMPANYNAME);
					$mail->setFrom($this->db->supportEmail('do_not_reply'), $_SESSION["company_name"]);
					$mail->clearAddresses();
					$mail->addAddress($user_email, "$user_first_name $user_last_name");
					$mail->Subject = $this->db->translate('Your access to')." ".$_SESSION["company_name"].str_replace('COMPANYNAME', COMPANYNAME, $this->db->translate('s COMPANYNAME'));
					$mail->isHTML(true);
					$mail->CharSet = 'UTF-8';
					//Build a simple message body
					$mail->Body = "<p>
									".$this->db->translate('Hi')." $user_first_name,<br />
									<br />
									<br />
									".$this->db->translate('Welcome to')." ".$_SESSION["company_name"].str_replace('COMPANYNAME', COMPANYNAME, $this->db->translate('s COMPANYNAME accounts, you have just been granted access.'))."
									<br />
									<br />
									".str_replace('COMPANYNAME', COMPANYNAME, $this->db->translate('COMPANYNAME Software is used to manage store activities like POS, Repair Ticketing, Inventory and Staff Management, and more.'))."<br />
									<br />
									<br />
									".$this->db->translate('Your login details are below').":<br />
									".$this->db->translate('Store login address').": $company_subdomain.$domainname<br />
									".$this->db->translate('Username').": $user_email<br />
									".$this->db->translate('Password').": $user_password<br />
									<br />
									<br />
									<a href=\"http://$company_subdomain.$domainname/Account/login\" target=\"_blank\" title=\"".$this->db->translate('Login')."\">".$this->db->translate('Login')."</a> ".$this->db->translate('and check things out. If you have any questions feel free to')." <a href=\"http://$domainname/contact.php\" target=\"_blank\" title=\"".$this->db->translate('Contact Us')."\">".$this->db->translate('Contact Us')."</a><br />
									<br />
									<br />
									".$this->db->translate('Thanks again').",<br />
									".str_replace('COMPANYNAME', COMPANYNAME, $this->db->translate('The COMPANYNAME Team'))."<br />
									<a href=\"http://$domainname/\" target=\"_blank\" title=\"$domainname\">$domainname</a>
								</p>";

					if($user_email =='' || is_null($user_email)){
						$savemsg = 'notSendMail';
					}
					else{
						if (!$mail->send()) {
							$savemsg = 'notSendMail';
						}
					}

					$returnStr = 'Add';
				}
				else{
					$savemsg = 'errorNewUser';
				}
			}
		}
		else{
			$totalrows = 0;
			$queryObj = $this->db->query("SELECT COUNT(user_id) AS totalrows FROM user WHERE user_email = :user_email AND user_id != :user_id AND accounts_id = $accounts_id AND user_publish = 1", array('user_email'=>$user_email, 'user_id'=>$user_id));
			if($queryObj){
				$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			if($totalrows>0){
				$savemsg = 'userExist';
			}
			else{

				$update = $this->db->update('user', $conditionarray, $user_id);
				if($update){
					$activity_feed_title = $this->db->translate('User was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Settings/users_setup/view/$user_id";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					 $afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' => $activity_feed_title,
									'activity_feed_name' => "$user_first_name $user_last_name",
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "user",
									'uri_table_field_name' =>"user_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);
					
				}

				$returnStr = 'Update';
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
   }
		
	public function po_setup(){}
	
	public function AJ_po_setup_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$accounts_id = $_SESSION['accounts_id']??0;		
		$po_message = '';
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'po_setup'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		$nextpo_number = 1;
		$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
		if($poObj){
			$nextpo_number = $poObj[0]['po_number']+1;
		}
		$jsonResponse['nextpo_number'] = $nextpo_number;
		$jsonResponse['po_message'] = stripslashes((string) $po_message);
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
   public function AJsave_po_setup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';

		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = intval($POST['variables_id']??0);
		if($variables_id==0){
			$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='po_setup'", array());
			if($queryObj){
				$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
			}
		}

		$po_message = addslashes(stripslashes($POST['po_message']??''));
		$value = serialize(array('po_message'=>$po_message));
		$data = array('accounts_id'=>$accounts_id,
						'name'=>$this->db->checkCharLen('variables.name', 'po_setup'),
						'value'=>$value,
						'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}

		$nextpo_number = floor($POST['nextpo_number']??'');
		$nextponumber = 1;
		$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
		if($poObj){
			$nextponumber = $poObj[0]['po_number']+1;
		}
		if($nextpo_number !='' && $nextpo_number>$nextponumber){
			
			$poData = array();
			$poData['po_datetime'] = date('Y-m-d H:i:s');
			$poData['last_updated'] = date('Y-m-d H:i:s');
			$poData['po_number'] = $nextpo_number-1;
			$lot_ref_no = $this->db->checkCharLen('po.lot_ref_no', '');
			$poData['lot_ref_no'] = $lot_ref_no;
			$poData['paid_by'] = '';
			$poData['supplier_id'] = 0;
			$poData['date_expected'] = date('Y-m-d');
			$poData['return_po'] = 0;
			$status = $this->db->checkCharLen('po.status', '');
			$poData['status'] = $status;
			$poData['accounts_id'] = $accounts_id;
			$poData['user_id'] = $user_id;
			$poData['tax_is_percent'] = 0;
			$poData['taxes'] = 0.000;
			$poData['shipping'] = 0.00;
			$suppliers_invoice_no = $this->db->checkCharLen('po.suppliers_invoice_no', '');
			$poData['suppliers_invoice_no'] =$suppliers_invoice_no;
			$poData['invoice_date'] = date('Y-m-d');
			$poData['date_paid'] = date('Y-m-d');
			$poData['transfer'] = 0;
			$this->db->insert('po', $poData);
			
		}
	
	
		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	public function barcode_labels(){}

	public function AJ_barcode_labels_MoreInfo(){
		$accounts_id = $_SESSION['accounts_id']??0;	
		$prod_cat_man = $_SESSION['prod_cat_man']??0;

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$Common = new Common($this->db);
		$vData = $Common->variablesData('barcode_labels', $accounts_id);
		if(!empty($vData)){
			extract($vData);
		}
		$jsonResponse['deviceLabel'] = str_replace("\r\n","\n", $deviceLabel);
		$jsonResponse['productLabel'] = str_replace("\r\n","\n", $productLabel);
		$jsonResponse['repairCustomerLabel'] = str_replace("\r\n","\n", $repairCustomerLabel);
		$jsonResponse['repairTicketLabel'] = str_replace("\r\n","\n", $repairTicketLabel);

		$vData = $Common->variablesData('label_printer', $accounts_id);
		$fontSize = 'Regular';
		$labelwidth = 57;
		$labelheight = 31;
		$units = 'mm';
		$variables_id = $top_margin = $right_margin = $bottom_margin = $left_margin = 0;
		
		$orientation = 'Portrait';
		if(!empty($vData)){
			extract($vData);
			$label_sizeWidth = floatval($label_sizeWidth);
			$label_sizeHeight = floatval($label_sizeHeight);
			if($label_size=='customSize'){
				if($label_sizeWidth>0 && $label_sizeHeight>0){
					if($units=='Inches'){
						$labelwidth = round(round($label_sizeWidth,2)*25.4);
						$labelheight = round(round($label_sizeHeight,2)*25.4);
					}
					else{
						$labelwidth = round($label_sizeWidth);
						$labelheight = round($label_sizeHeight);
					}
				}
			}
			else{
				if(!empty($label_size) && strpos($label_size, '|')  !== false){
					list($label_sizeWidth, $label_sizeHeight) = explode('|', $label_size);
				}
				
				if($label_sizeWidth>0 && $label_sizeHeight>0){
					$labelwidth = $label_sizeWidth;
					$labelheight = $label_sizeHeight;
				}
			}
		}
		
		$jsonResponse['fontSize'] = $fontSize;
		$jsonResponse['labelwidth'] = $labelwidth*3.7795275591;
		$jsonResponse['labelheight'] = $labelheight*3.7795275591;
		$jsonResponse['top_margin'] = $top_margin;
		$jsonResponse['right_margin'] = $right_margin;
		$jsonResponse['bottom_margin'] = $bottom_margin;
		$jsonResponse['left_margin'] = $left_margin;
		$jsonResponse['orientation'] = $orientation;
		

		$jsonResponse['variables_id'] = intval($variables_id);

		$Label_customfields = array();
		$cqueryObj = $this->db->query("SELECT field_for, field_type, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for IN ('product', 'repairs', 'customers', 'devices') ORDER BY order_val ASC", array());
		if($cqueryObj){
			while($oneCFRow = $cqueryObj->fetch(PDO::FETCH_OBJ)){
				$field_for = $oneCFRow->field_for;
				$field_type = $oneCFRow->field_type;
				$field_name = $oneCFRow->field_name;
				if(!in_array($field_type, array('Picture', 'PDF')) && !empty($field_name)){
					$Label_customfields[$field_for][$field_name] = '';
				}
			}
		}
		
		$productLabel_customfields = $repairsLabel_customfields = $customersLabel_customfields = $devicesLabel_customfields = array();
		if(!empty($Label_customfields) && array_key_exists('product', $Label_customfields)){
			$productLabel_customfields = array_keys($Label_customfields['product']);
		}
		if(!empty($Label_customfields) && array_key_exists('repairs', $Label_customfields)){
			$repairsLabel_customfields = array_keys($Label_customfields['repairs']);
		}
		if(!empty($Label_customfields) && array_key_exists('customers', $Label_customfields)){
			$customersLabel_customfields = array_keys($Label_customfields['customers']);
		}
		if(!empty($Label_customfields) && array_key_exists('devices', $Label_customfields)){
			$devicesLabel_customfields = array_keys($Label_customfields['devices']);
		}
		
		$jsonResponse['productLabel_customfields'] = $productLabel_customfields;
		$jsonResponse['repairsLabel_customfields'] = $repairsLabel_customfields;
		$jsonResponse['customersLabel_customfields'] = $customersLabel_customfields;
		$jsonResponse['devicesLabel_customfields'] = $devicesLabel_customfields;
		
		return json_encode($jsonResponse);
	}
	
   public function AJsave_barcode_labels(){
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';

		$accounts_id = $_SESSION["accounts_id"]??0;
		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='barcode_labels'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}

		$valueData = array();
		$valueData['fontSize'] = $POST['fontSize']??'Regular';
		$valueData['deviceLabel'] = $POST['deviceLabel']??'';
		$valueData['productLabel'] = $POST['productLabel']??'';
		$valueData['repairCustomerLabel'] = $POST['repairCustomerLabel']??'';
		$valueData['repairTicketLabel'] = $POST['repairTicketLabel']??'';
		
		$value = serialize($valueData);
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'barcode_labels'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	public function restrict_access(){}

	public function AJ_restrict_access_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$accounts_id = $_SESSION['accounts_id']??0;		
		$ip_address = '';
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'restrict_access'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		$jsonResponse['ip_address'] = $ip_address;
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
   public function AJsave_restrict_access(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';

		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = intval($POST['variables_id']??0);
		if($variables_id==0){
			$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='restrict_access'", array());
			if($queryObj){
				$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
			}
		}

		$ip_address = addslashes(stripslashes($POST['ip_address']??''));
		$value = serialize(array('ip_address'=>$ip_address));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'restrict_access'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
	}    
		
	public function AJgetPopup_custom_fields(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$custom_fields_id = intval($POST['custom_fields_id']??0);
		$custom_fieldsData = array();
		$custom_fieldsData['login'] = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		$custom_fieldsData['custom_fields_id'] = 0;				
		$custom_fieldsData['field_for'] = '';
		$custom_fieldsData['field_name'] = '';
		$custom_fieldsData['field_required'] = 0;
		$custom_fieldsData['field_type'] = $field_type = 'TextBox';
		$custom_fieldsData['parameters'] = $parameters = '';
		$custom_fieldsData['TextOnly'] = '';
		
		if($custom_fields_id>0 && $prod_cat_man>0){
			$custom_fieldsObj = $this->db->query("SELECT * FROM custom_fields WHERE custom_fields_id = :custom_fields_id AND accounts_id = $prod_cat_man", array('custom_fields_id'=>$custom_fields_id),1);
			if($custom_fieldsObj){
				$custom_fieldsRow = $custom_fieldsObj->fetch(PDO::FETCH_OBJ);	

				$custom_fieldsData['custom_fields_id'] = $custom_fields_id;				
				$custom_fieldsData['field_for'] = trim((string) $custom_fieldsRow->field_for);
				$custom_fieldsData['field_name'] = trim((string) $custom_fieldsRow->field_name);
				$custom_fieldsData['field_required'] = trim((string) $custom_fieldsRow->field_required);
				$custom_fieldsData['field_type'] = $field_type = $custom_fieldsRow->field_type;
				$parameters = $custom_fieldsRow->parameters;
			}
		}
		
		if($parameters !=''){
			if($field_type=='TextOnly'){
				$custom_fieldsData['TextOnly'] = $parameters;
			}
		}
		$custom_fieldsData['parameters'] = $parameters;
		return json_encode($custom_fieldsData);
	}
	
	public function AJsave_custom_fields($ajaxOrNot, $fieldsInfo){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';
		$custom_fields_id = 0;
		if(empty($ajaxOrNot)){
			$accounts_id = $_SESSION["accounts_id"]??0;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;			
			$custom_fields_id = intval($POST['custom_fields_id']??0);
			
			$field_for = addslashes(stripslashes($POST['field_for']??''));
			$field_name = addslashes(stripslashes($POST['field_name']??''));
			$field_required = intval($POST['field_required']??0);
			$field_type = $POST['field_type']??'';
			$DropDown = $POST['DropDown[]']??array();
			$parameters = addslashes(stripslashes($POST['TextOnly']??''));
			$user_id = $_SESSION["user_id"]??0;
		}
		else{
			$prod_cat_man = intval($fieldsInfo['prod_cat_man']??0);
			$accounts_id = intval($fieldsInfo['accounts_id']??0);
			$field_for = $fieldsInfo['field_for']??'';
			$field_name = $fieldsInfo['field_name']??'';
			$field_required = intval($fieldsInfo['field_required']??0);
			$field_type = $fieldsInfo['field_type']??'';
			$DropDown = $fieldsInfo['DropDown[]']??array();
			$parameters = '';
			$user_id = intval($fieldsInfo['user_id']??0);			
		}
		if(!empty($field_type)){
			$conditionarray = array();
			$conditionarray['field_for'] = $field_for;
			$conditionarray['field_name'] = $field_name;
			$conditionarray['field_required'] = $field_required;
			$conditionarray['field_type'] = $field_type;
			
			if($field_type=='DropDown'){				
				$DropDownArray = array();
				if(!empty($DropDown)){
					foreach($DropDown as $oneDropDown){
						if($oneDropDown !=''){
							$DropDownArray[] = $oneDropDown;
						}
					}
				}
				if(!empty($DropDownArray)){$parameters = implode('||', $DropDownArray);}
			}
						
			$conditionarray['parameters'] = $parameters;			
			$conditionarray['accounts_id'] = $prod_cat_man;			
			$conditionarray['user_id'] = $user_id;
			
			if($custom_fields_id==0){
				$customFieldsObj = $this->db->query("SELECT custom_fields_id, custom_fields_publish FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = :field_for AND field_name = :field_name", array('field_for'=>$field_for, 'field_name'=>$field_name));
				if($customFieldsObj){
					$oneCFRow = $customFieldsObj->fetch(PDO::FETCH_OBJ);
					$custom_fields_id = $oneCFRow->custom_fields_id;
					$custom_fields_publish = $oneCFRow->custom_fields_publish;
					if($custom_fields_publish==0){
						$this->db->update('custom_fields', array('custom_fields_publish'=>1), $custom_fields_id);
						$savemsg = 'Update';
					}
					else{
						$this->db->update('custom_fields', $conditionarray, $custom_fields_id);
						$returnStr = 'Name_Already_Exist';
					}
				}
				else{
					$conditionarray['last_updated'] = date('Y-m-d H:i:s');
					$order_val = 1;
					$poObj = $this->db->querypagination("SELECT order_val FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = :field_for ORDER BY order_val DESC LIMIT 0, 1", array('field_for'=>$field_for));
					if($poObj){
						$order_val = $poObj[0]['order_val']+1;
					}
					$conditionarray['order_val'] = $order_val;
					$custom_fields_id = $this->db->insert('custom_fields', $conditionarray);
					if($custom_fields_id){
						$savemsg = 'Add';
					}
					else{
						$returnStr = 'errorOnAdding';
					}
				}
			}
			else{
				$countTableData = 0;
				$customFieldsObj = $this->db->query("SELECT custom_fields_id, custom_fields_publish FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = :field_for AND field_name = :field_name AND custom_fields_id != :custom_fields_id", array('field_for'=>$field_for, 'field_name'=>$field_name, 'custom_fields_id'=>$custom_fields_id));
				if($customFieldsObj){
					$oneCFRow = $customFieldsObj->fetch(PDO::FETCH_OBJ);
					$custom_fields_id = $oneCFRow->custom_fields_id;
					$custom_fields_publish = $oneCFRow->custom_fields_publish;
					if($custom_fields_publish==0){
						$this->db->update('custom_fields', array('custom_fields_publish'=>1), $custom_fields_id);
						$savemsg = 'Update';
					}
					else{
						$returnStr = 'Name_Already_Exist';
					}
				}
				else{
					$conditionarray['last_updated'] = date('Y-m-d H:i:s');
					$update = $this->db->update('custom_fields', $conditionarray, $custom_fields_id);
					if($update){$savemsg = 'Update';}
				}
			}
		}
		
		$array = array( 'login'=>'','custom_fields_id'=>$custom_fields_id,
						'savemsg'=>$savemsg,
						'returnStr'=>$returnStr);
		
		if(empty($ajaxOrNot)){return json_encode($array);}
		else{return $array;}
	}
	
	public function AJorderup_custom_fields(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$custom_fields_id = intval($POST['custom_fields_id']??0);
		$precustom_fields_id = intval($POST['precustom_fields_id']??0);
		$order_val = intval($POST['order_val']??1);
		$this->db->update('custom_fields', array('order_val'=>$order_val-1), $custom_fields_id);
		$this->db->update('custom_fields', array('order_val'=>$order_val), $precustom_fields_id);
		$returnStr = 'Ok';
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJgetPopup_forms(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$forms_id = intval($POST['forms_id']??0);
		$form_for = trim((string) $POST['form_for']??'');
		$formsData = array();
		
		$formsData['login'] = '';
			
		$form_name = $form_for = $form_condition = $form_matches = $model = '';
		$form_public = $required = 0;
			
		if($forms_id>0 && $prod_cat_man>0){			
			$formsObj = $this->db->query("SELECT * FROM forms WHERE forms_id = :forms_id AND accounts_id = $prod_cat_man AND forms_publish = 1", array('forms_id'=>$forms_id),1);
			if($formsObj){
				$formsRow = $formsObj->fetch(PDO::FETCH_OBJ);	
				
				$form_name = trim((string) $formsRow->form_name);
				$form_for = trim((string) $formsRow->form_for);
				$form_public = trim((string) $formsRow->form_public);
				$required = trim((string) $formsRow->required);
				$form_condition = trim((string) $formsRow->form_condition);
				$form_matches = trim((string) $formsRow->form_matches);
			}			
		}
		if($form_matches !='' && strpos($form_matches, '||') !== false){
			$form_matchesExp = explode('||', $form_matches);
			$form_matches = $form_matchesExp[0];
			if(count($form_matchesExp)>0){
				$model = $form_matchesExp[1];
			}
		}
		$formsData['form_name'] =$form_name;
		$formsData['form_for'] = $form_for;
		$formsData['form_public'] = $form_public;
		$formsData['required'] = $required;
		$formsData['form_condition'] = $form_condition;
		$formsData['form_matches'] = $form_matches;
		$formsData['model'] = $model;
		
		$form_conditionOptions = array();
		$conditionArray = array('', 'All Repairs', 'Create Repair', 'Problem', 'Brand/Model');
		foreach($conditionArray as $label){
			$form_conditionOptions[] = $label;
		}
		$formsData['form_condition'] = $form_condition;
		$formsData['form_conditionOptions'] = $form_conditionOptions;
		
	
		$form_matchesOptions = array();
		if($form_condition !=''){
			
			if(strcmp('Problem', $form_condition)==0){
				$sqlproblem = "SELECT name FROM repair_problems WHERE accounts_id = $prod_cat_man AND repair_problems_publish = 1 ORDER BY name ASC";
				$problemquery = $this->db->query($sqlproblem, array());
				if($problemquery){
					while($oneproblemrow = $problemquery->fetch(PDO::FETCH_OBJ)){
						$oproblem_name = stripslashes(trim((string) $oneproblemrow->name));
						if($oproblem_name !=''){
							$form_matchesOptions[$oproblem_name] = $oproblem_name;
						}
					}
				}
			}
			elseif(strcmp('Brand/Model', $form_condition)==0){
				
				$bMSql = "SELECT brand FROM brand_model WHERE accounts_id = $prod_cat_man AND brand_model_publish = 1 GROUP BY brand ORDER BY brand ASC";
				$bMObj = $this->db->query($bMSql, array());
				if($bMObj){
					while($oneRow = $bMObj->fetch(PDO::FETCH_OBJ)){
						$brand = trim((string) stripslashes("$oneRow->brand"));
						if($brand !=''){
							$form_matchesOptions[$brand] = $brand;
						}
					}
				}
			}
		}
		
		$formsData['form_matchesOptions'] = $form_matchesOptions;
		return json_encode($formsData);
	}
	
	public function AJsave_forms(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$forms_id = 0;
		$savemsg = 'error';
		$returnStr = '';
	
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		$form_public = $required = $similar = 0;
		$form_name = $form_condition = $form_matches = $model = $form_for = '';
		if(isset($POST)){
			$forms_id = intval($POST['forms_id']??0);
			$form_name = $this->db->checkCharLen('forms.form_name', trim((string) $POST['form_name']??''));
			$form_public = intval($POST['form_public']??0);
			$required = intval($POST['required']??0);
			$form_condition = $this->db->checkCharLen('forms.form_condition', trim((string) $POST['form_condition']??''));
			$form_matches = trim((string) array_key_exists('form_matches', $POST) ? $POST['form_matches'] : '');
			$form_matches = $this->db->checkCharLen('forms.form_matches', $form_matches);
			$model = trim((string) $POST['model']??'');
			$form_for = $this->db->checkCharLen('forms.form_for', trim((string) $POST['form_for']??''));
			$similar = trim((string) $POST['similar']??0);
			if(strcmp('Brand/Model', $form_condition)==0 && $form_matches !='' && $model !=''){
				$form_matches .= "||$model";
			}
			
			$formsData = array();
			$formsData['last_updated'] = date('Y-m-d H:i:s');
			$formsData['accounts_id'] = $prod_cat_man;
			$formsData['user_id'] = $user_id = $_SESSION["user_id"]??0;
			$formsData['form_name'] = $form_name;
			$formsData['form_for'] = $form_for;
			$formsData['form_public'] = $form_public;
			$formsData['required'] = $required;
			$formsData['form_condition'] = $form_condition;				
			$formsData['form_matches'] = $form_matches;
			$oldforms_id = $forms_id;
			if($similar>0){$forms_id = 0;}
			if($forms_id==0){
				$countTableData = 0;
				$formsObj = $this->db->query("SELECT forms_id FROM forms WHERE accounts_id = $prod_cat_man AND form_for = :form_for AND form_name = :form_name", array('form_for'=>$form_for, 'form_name'=>$form_name));
				if($formsObj){
					$countTableData = $forms_id = $formsObj->fetch(PDO::FETCH_OBJ)->forms_id;
					if($forms_id>0){
						$formsData['forms_publish'] = 1;
						$update = $this->db->update('forms', $formsData, $forms_id);
						if($update){
							$savemsg = '';
							$activity_feed_title = $this->db->translate('Form was edited');
							$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
							$activity_feed_link = "/Settings/formFields/$forms_id";
							$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
											
							$afData = array('created_on' => date('Y-m-d H:i:s'),
											'last_updated' => date('Y-m-d H:i:s'),
											'accounts_id' => $_SESSION["accounts_id"],
											'user_id' => $_SESSION["user_id"],
											'activity_feed_title' => $activity_feed_title,
											'activity_feed_name' => "$form_name $form_condition",
											'activity_feed_link' => $activity_feed_link,
											'uri_table_name' => "forms",
											'uri_table_field_name' =>"forms_publish",
											'field_value' => 1
											);
							$this->db->insert('activity_feed', $afData);
						}
						else{
							$savemsg = 'error';
						}
					}
				}
				if($countTableData>0){	
					$savemsg = 'error';					
					$returnStr = 'Name_Already_Exist';
				}
				else{
					$formsData['created_on'] = date('Y-m-d H:i:s');					
					$formsData['form_definitions'] = '';
					$forms_id = $this->db->insert('forms', $formsData);
					if($forms_id){
						$savemsg = '';
						if($similar>0 && $oldforms_id>0){
							$formFieldsObj = $this->db->query("SELECT form_definitions FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = $oldforms_id", array());
							if($formFieldsObj){
								$form_definitions = $formFieldsObj->fetch(PDO::FETCH_OBJ)->form_definitions;
								$this->db->update('forms', array('form_definitions'=>$form_definitions), $forms_id);
							}
						}
					}
					else{
						$savemsg = 'error';
						$returnStr = 'errorOnAdding';
					}
				}
			}
			else{
				$countTableData = 0;
				$formsObj = $this->db->query("SELECT COUNT(forms_id) AS totalrows FROM forms WHERE accounts_id = $prod_cat_man AND form_for = :form_for AND form_name = :form_name AND form_condition = :form_condition AND forms_id != :forms_id AND forms_publish = 1", array('form_for'=>$form_for, 'form_name'=>$form_name, 'form_condition'=>$form_condition, 'forms_id'=>$forms_id));
				if($formsObj){
					$countTableData = $formsObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				if($countTableData>0){						
					$savemsg = 'error';
					$returnStr = 'Name_Already_Exist';
				}
				else{
					
					$update = $this->db->update('forms', $formsData, $forms_id);
					if($update){
						$savemsg = '';
						$activity_feed_title = $this->db->translate('Form was edited');
						$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
						$activity_feed_link = "/Settings/formFields/$forms_id";
						$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
						
						$afData = array('created_on' => date('Y-m-d H:i:s'),
										'last_updated' => date('Y-m-d H:i:s'),
										'accounts_id' => $_SESSION["accounts_id"],
										'user_id' => $_SESSION["user_id"],
										'activity_feed_title' => $activity_feed_title,
										'activity_feed_name' => "$form_name $form_condition",
										'activity_feed_link' => $activity_feed_link,
										'uri_table_name' => "forms",
										'uri_table_field_name' =>"forms_publish",
										'field_value' => 1
										);
						$this->db->insert('activity_feed', $afData);
						
					}
					else{
						$savemsg = 'error';
					}
				}
			}
		}
		else{
			$savemsg = 'error';
		}
		$array = array( 'login'=>'',
						'forms_id'=>$forms_id,
						'savemsg'=>$savemsg,
						'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	public function AJgetPopup_forms_field(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$forms_id = intval($POST['forms_id']??0);
		$sorder_val = intval($POST['order_val']??0);
		
		$formsFieldData = array();
		$formsFieldData['login'] = '';
		$formsFieldData['forms_id'] = 0;
		$formsFieldData['field_name'] = '';
		$formsFieldData['field_required'] = 0;
		$formsFieldData['field_type'] = $field_type = 'TextBox';
		
		$formsFieldData['parameters'] = $parameters = '';
		$formsFieldData['SectionBreak'] = '';
		if($forms_id>0 && $prod_cat_man>0){
			$formsObj = $this->db->query("SELECT form_definitions FROM forms WHERE forms_id = :forms_id AND accounts_id = $prod_cat_man", array('forms_id'=>$forms_id),1);
			if($formsObj){
				$formsFieldData['forms_id'] = $forms_id;
				$form_definitions = $formsObj->fetch(PDO::FETCH_OBJ)->form_definitions;
				if($form_definitions !=''){
					$form_definitions = unserialize($form_definitions);
					if(is_array($form_definitions)){
						foreach($form_definitions as $oneFieldRow){
							$order_val = $oneFieldRow['order_val'];
							if($sorder_val==$order_val){
								$formsFieldData['field_name'] = stripslashes(trim((string) $oneFieldRow['field_name']));
								$formsFieldData['field_required'] = trim((string) $oneFieldRow['field_required']);
								$formsFieldData['field_type'] = $field_type = $oneFieldRow['field_type'];
								$parameters = $oneFieldRow['parameters'];
							}
						}
					}
				}
			}
		}
		
		if($parameters !=''){
			if($field_type !='DropDown' && $field_type=='SectionBreak'){
				$formsFieldData['SectionBreak'] = $parameters;
			}
		}
		$formsFieldData['parameters'] = $parameters;

		return json_encode($formsFieldData);
	}
	
	public function AJsave_forms_field(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$message = $field_type = '';			
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;			
		$forms_id = intval($POST['forms_id']??0);
		
		$conditionarray = array();
		$conditionarray['order_val'] = $order_val = intval($POST['order_val']??1);
		$conditionarray['field_name'] = $field_name = addslashes(stripslashes($POST['field_name']??''));
		$conditionarray['field_required'] = $field_required = intval($POST['field_required']??0);
		$conditionarray['field_type'] = $field_type = $POST['field_type']??'';
		
		$parameters = '';
		if($field_type=='DropDown' && array_key_exists('DropDown[]', $POST)){
			$DropDown = $POST['DropDown[]']??array();
			$DropDownArray = array();
			if(!empty($DropDown)){
				foreach($DropDown as $oneDropDown){
					if($oneDropDown !=''){
						$DropDownArray[] = $oneDropDown;
					}
				}
			}
			if(!empty($DropDownArray)){$parameters = implode('||', $DropDownArray);}
		}
		elseif($field_type=='TextOnly' && array_key_exists('TextOnly', $POST)){
			$parameters = addslashes(stripslashes($POST['TextOnly']??''));
		}
		elseif($field_type=='SectionBreak' && array_key_exists('SectionBreak', $POST)){
			$parameters = addslashes(stripslashes($POST['SectionBreak']??''));
		}			
		
		$conditionarray['parameters'] = $parameters;
		$duplicateCount = 0;
		$fieldNameUpdates = array();
		
		if($order_val==0){
			$conditionarray['order_val'] = 1;
			$newDefinitions = array();
			$formFieldsObj = $this->db->query("SELECT form_definitions FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = :forms_id", array('forms_id'=>$forms_id),1);
			if($formFieldsObj){
				$form_definitions = $formFieldsObj->fetch(PDO::FETCH_OBJ)->form_definitions;
				$form_definitions = unserialize($form_definitions);
				if(is_array($form_definitions)){
					$conditionarray['order_val'] = count($form_definitions)+1;
					foreach($form_definitions as $oneFieldRow){
						if(strtolower($conditionarray['field_name']) == strtolower($oneFieldRow['field_name'])){
							$message = "$conditionarray[field_name]";
							$savemsg = 'error';
							$duplicateCount++;
						}
						$newDefinitions[] = $oneFieldRow;
					}						
				}
			}
			if($duplicateCount==0)
				$newDefinitions[] = $conditionarray;
			
			$signCount = 0;
			foreach($newDefinitions as $ckOneFieldRow){
				$checkFType = $ckOneFieldRow['field_type'];
				if($checkFType=='Signature'){$signCount++;}
			}
			if($signCount>1){
				$message = "signatureExists";
				$savemsg = 'error';
			}
			if($message ==''){
				$form_definitions = serialize($newDefinitions);
				$this->db->update('forms', array('form_definitions'=>$form_definitions), $forms_id);
				$savemsg = 'Update';
			}
		}
		else{
			$formFieldsObj = $this->db->query("SELECT form_definitions FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = :forms_id", array('forms_id'=>$forms_id),1);
			if($formFieldsObj){
				$form_definitions = $formFieldsObj->fetch(PDO::FETCH_OBJ)->form_definitions;
				$form_definitions = unserialize($form_definitions);
				if(is_array($form_definitions)){
					$newDefinitions = array();
					foreach($form_definitions as $oneFieldRow){
						$order_vals = $oneFieldRow['order_val'];
						if(strtolower($conditionarray['field_name']) == strtolower($oneFieldRow['field_name']) && $order_vals !=$order_val){
							$message = "$conditionarray[field_name]";
							$savemsg = 'error';
							$duplicateCount++;
						}
					}
					
					foreach($form_definitions as $oneFieldRow){
						$order_vals = $oneFieldRow['order_val'];								
						if($order_vals==$order_val && $duplicateCount==0){
							$newDefinitions[] = $conditionarray;
							if(strtolower($conditionarray['field_name']) != strtolower($oneFieldRow['field_name'])){
								$fieldNameUpdates = array($oneFieldRow['field_name'], $conditionarray['field_name']);
							}								
						}
						else{
							$newDefinitions[] = $oneFieldRow;
						}
					}
					
					$signCount = 0;
					foreach($newDefinitions as $ckOneFieldRow){
						$checkFType = $ckOneFieldRow['field_type'];
						if($checkFType=='Signature'){$signCount++;}
					}
					if($signCount>1){
						$savemsg = 'error';
						$message = "signatureExists";
					}
					
					if($message ==''){
						$form_definitions = serialize($newDefinitions);
						$this->db->update('forms', array('form_definitions'=>$form_definitions), $forms_id);
						$savemsg = 'Update';
					}
				}
			}
			else{
				$message = "noDataFound";
			}
		}			
		if($savemsg == 'Update' && !empty($fieldNameUpdates)){
			$oldFieldName = $fieldNameUpdates[0];
			$newFieldName = $fieldNameUpdates[1];
			
			$forms_dataObj = $this->db->query("SELECT * FROM forms_data WHERE forms_id = :forms_id AND accounts_id = $accounts_id", array('forms_id'=>$forms_id),1);
			if($forms_dataObj){
				while($forms_dataData = $forms_dataObj->fetch(PDO::FETCH_OBJ)){
					$form_data = $forms_dataData->form_data;
					if(!empty($form_data)){
						$form_dataArray = unserialize($form_data);
						if(array_key_exists($oldFieldName, $form_dataArray)){
							$newFieldData = array();
							foreach($form_dataArray as $fieldName=>$fieldVal){
								if($fieldName==$oldFieldName){$fieldName=$newFieldName;}
								$newFieldData[$fieldName] = $fieldVal;
							}
							$this->db->update('forms_data', array('form_data'=>serialize($newFieldData)), $forms_dataData->forms_data_id);
						}
					}
				}
			}
		}
		if($field_type=='AddImage' || $field_type=='AddFile'){
			header("location:/Settings/formFields/$forms_id/$savemsg");
			return;
		}
		else{
			$array = array( 'login'=>'',
							'forms_id'=>$forms_id,
							'savemsg'=>$savemsg,
							'message'=>$message);
			return json_encode($array);
		}
	}
	
	public function AJorderup_forms_field(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$forms_id = intval($POST['forms_id']??0);
		$preorder_val = intval($POST['preorder_val']??0);
		$order_val = intval($POST['order_val']??0);
		$formFieldsObj = $this->db->query("SELECT form_definitions FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = :forms_id", array('forms_id'=>$forms_id),1);
		if($formFieldsObj){
			$form_definitions = $formFieldsObj->fetch(PDO::FETCH_OBJ)->form_definitions;
			$form_definitions = unserialize($form_definitions);
			if(is_array($form_definitions)){
				$newDefinitions = array();
				$sameOrMissing = $prevOrder = 0;
				foreach($form_definitions as $oneFieldRow){
					$prevOrder++;
					$order_vals = $oneFieldRow['order_val'];
					if(floor($order_vals-$prevOrder) !=0){$sameOrMissing++;}
				}
				
				if($sameOrMissing>0){
					$sl=0;
					foreach($form_definitions as $oneFieldRow){
						$sl++;
						$oneFieldRow['order_val'] = $sl;
						$newDefinitions[] = $oneFieldRow;
					}
				}
				else{
					foreach($form_definitions as $oneFieldRow){
						$order_vals = $oneFieldRow['order_val'];
						if($order_vals==$preorder_val){
							$oneFieldRow['order_val'] = $order_val;
						}
						elseif($order_vals==$order_val){
							$oneFieldRow['order_val'] = $preorder_val;
						}
						$newDefinitions[] = $oneFieldRow;
					}
				}
				
				$order_valS  = array_column($newDefinitions, 'order_val');
				$field_nameS  = array_column($newDefinitions, 'field_name');
				array_multisort($order_valS, SORT_ASC, $field_nameS, SORT_ASC, $newDefinitions);
				
				$form_definitions = serialize($newDefinitions);
				$this->db->update('forms', array('form_definitions'=>$form_definitions), $forms_id);
				$savemsg = 'Update';
			}
		}
		$returnStr = 'Ok';
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function showModelMatch(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$modelOpt = array();
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;				
		$forms_id = intval($POST['forms_id']??0);
		$form_condition = trim((string) $POST['form_condition']??'');
		$form_matches = trim((string) $POST['form_matches']??'');
		$form_for = trim((string) $POST['form_for']??'');
		
		if(strcmp('Brand/Model', $form_condition)==0 && $form_matches !=''){
			$bMSql = "SELECT model FROM brand_model WHERE accounts_id = $prod_cat_man AND brand = :brand AND brand_model_publish = 1 GROUP BY model ORDER BY model ASC";
			$bMObj = $this->db->query($bMSql, array('brand'=>$form_matches));
			if($bMObj){
				while($oneRow = $bMObj->fetch(PDO::FETCH_OBJ)){
					$model = trim((string) stripslashes("$oneRow->model"));
					if($model !=''){
						$modelOpt[$model] = $model;
					}
				}
			}
		}
			
		return json_encode(array('login'=>'', 'modelOpt'=>$modelOpt));
	}
	
	public function showConditionMatch(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$form_matchesOptions = array();
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$forms_id = intval($POST['forms_id']??0);
		$form_condition = trim((string) $POST['form_condition']??'');
		$form_for = trim((string) $POST['form_for']??'');
		
		if($form_condition !=''){
			
			if(strcmp('Problem', $form_condition)==0){
				$sqlproblem = "SELECT name FROM repair_problems WHERE accounts_id = $prod_cat_man AND repair_problems_publish = 1 GROUP BY name ORDER BY name ASC";
				$problemquery = $this->db->query($sqlproblem, array());
				if($problemquery){
					while($oneproblemrow = $problemquery->fetch(PDO::FETCH_OBJ)){
						$oproblem_name = stripslashes(trim((string) $oneproblemrow->name));
						if($oproblem_name !=''){
							$form_matchesOptions[$oproblem_name] = $oproblem_name;
						}
					}
				}
			}
			elseif(strcmp('Brand/Model', $form_condition)==0){
				
				$bMSql = "SELECT brand FROM brand_model WHERE accounts_id = $prod_cat_man AND brand_model_publish = 1 GROUP BY brand ORDER BY brand ASC";
				$bMObj = $this->db->query($bMSql, array());
				if($bMObj){
					while($oneRow = $bMObj->fetch(PDO::FETCH_OBJ)){
						$brand = trim((string) stripslashes("$oneRow->brand"));
						if($brand !=''){
							$form_matchesOptions[$brand] = $brand;
						}
					}
				}
			}
		}
			
		return json_encode(array('login'=>'', 'form_matchesOptions'=>$form_matchesOptions));
	}
	
	//==================Repairs===================//	
	public function repairs_general(){}

	public function AJ_repairs_general_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$repairStatuses = array('New', 'Finished', 'Assigned', 'On Hold', 'Waiting on Customer', 'Waiting for Parts');
		$statusColors = array('#5cb85c', '#000000', '#FFFFFF', '#FFFFFF', '#FFFFFF', '#FFFFFF');
		$title = $this->db->translate('Repair Ticket');
		$logo_size = 'Small Logo';
		$logo_placement = 'Left';
		$company_info = $repair_message = $value = $repair_statuses = $status_colors = '';
		
		$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = $technician = $short_description = $imei = $brand = $bin_location = $lock_password = 1;
		$repair_sort = $customer_secondary_phone = $customer_type = $barcode = $status = $duedatetime = $print_price_zero = $notes = $variables_id = 0;
		
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
				if($repair_statuses !=''){
					$repairStatuses = explode('||',$repair_statuses);
					if(!empty($repairStatuses)){
						$p = 0;
						foreach($repairStatuses as $oneStatus){
							if(in_array($oneStatus, array('New', 'Finished'))){unset($repairStatuses[$p]);}
							$p++;
						}
					}
					array_splice( $repairStatuses, 0, 0, array('New', 'Finished'));
				}
				if(isset($status_colors) && $status_colors !=''){
					$statusColors = explode('||',$status_colors);
				}
			}
		}
		
		$jsonResponse['repair_sort'] = $repair_sort;
		$jsonResponse['repairStatuses'] = $repairStatuses;
		$jsonResponse['statusColors'] = $statusColors;
		$jsonResponse['title'] = $title;
		$jsonResponse['logo_size'] = $logo_size;
		$jsonResponse['logo_placement'] = $logo_placement;
		$jsonResponse['company_info'] = stripslashes($company_info);
		$jsonResponse['repair_message'] = stripslashes((string) $repair_message);
		$jsonResponse['value'] = $value;
		$jsonResponse['customer_name'] = $customer_name;
		$jsonResponse['customer_address'] = $customer_address;
		$jsonResponse['customer_phone'] = $customer_phone;
		$jsonResponse['customer_email'] = $customer_email;
		$jsonResponse['sales_person'] = $sales_person;
		$jsonResponse['technician'] = $technician;
		$jsonResponse['short_description'] = $short_description;
		$jsonResponse['imei'] = $imei;
		$jsonResponse['brand'] = $brand;
		$jsonResponse['bin_location'] = $bin_location;
		$jsonResponse['lock_password'] = $lock_password;
		$jsonResponse['customer_secondary_phone'] = $customer_secondary_phone;
		$jsonResponse['customer_type'] = $customer_type;
		$jsonResponse['barcode'] = $barcode;
		$jsonResponse['status'] = $status;
		$jsonResponse['duedatetime'] = $duedatetime;
		$jsonResponse['print_price_zero'] = $print_price_zero;
		$jsonResponse['notes'] = $notes;

		$nextrepairticket_no = 1;
		$repairObj = $this->db->querypagination("SELECT ticket_no FROM repairs WHERE accounts_id = $accounts_id ORDER BY ticket_no DESC LIMIT 0, 1", array());
		if($repairObj){
			$nextrepairticket_no = $repairObj[0]['ticket_no']+1;
		}
		$jsonResponse['nextrepairticket_no'] = $nextrepairticket_no;

		$onePicture = $alt = '';
		$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
		$pics = glob($filePath."*.jpg");
		if($pics){
			$totalpics = COUNT($pics);
			$l=0;
			foreach($pics as $onePictureInfo){
				$l++;
				if($l==1){
					$onePicture = str_replace('./', '/', $onePictureInfo);
					$alt = str_replace("/assets/accounts/a_$accounts_id/", '', $onePicture);
				}
				else{
					if (file_exists($onePictureInfo)){
						unlink($onePictureInfo);
					}
				}
			}				
		}
		$jsonResponse['onePicture'] = $onePicture;
		$jsonResponse['alt'] = $alt;

		$customFields = array();
		$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
		if($queryCFObj){
			while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
				$field_name = stripslashes($oneCustomFields->field_name);
				$checked = '';
				if(!empty($value) && array_key_exists("cf$oneCustomFields->custom_fields_id",$value) && $value["cf$oneCustomFields->custom_fields_id"]>0){$checked = ' checked';}
				$customFields[$oneCustomFields->custom_fields_id] = array($field_name, $checked);
			}
		}
		$jsonResponse['customFields'] = $customFields;
		$bgcolor0 = $statusColors[0]??'#5cb85c';
		$bgcolor1 = $statusColors[1]??'#000000';
		
		$jsonResponse['bgcolor0'] = $bgcolor0;
		$jsonResponse['bgcolor1'] = $bgcolor1;

		$customFieldsRepair = array();
		$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'repairs' ORDER BY order_val ASC", array());
		if($queryCFObj){
			while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
				$field_name = stripslashes($oneCustomFields->field_name);
				$checked = '';
				if(!empty($value) && array_key_exists("cf$oneCustomFields->custom_fields_id",$value) && $value["cf$oneCustomFields->custom_fields_id"]>0){$checked = ' checked';}
				$customFieldsRepair[$oneCustomFields->custom_fields_id] = array($field_name, $checked);
			}
		}
		$jsonResponse['customFieldsRepair'] = $customFieldsRepair;		
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
   	public function AJsave_repairs_general(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$variables_id = 0;
		$savemsg = 'error';
		
		if(isset($POST)){
			$variables_id = intval($POST['variables_id']??0);
			if($variables_id==0){
				$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='repairs_setup'", array());
				if($queryObj){
					$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
				}
			}
			$nextrepairticket_no = intval($POST['nextrepairticket_no']??1);
			$dataArray = array();
			$dataArray['repair_sort'] = intval($POST['repair_sort']??0);

			$repairStatuses = $POST['repair_statuses[]']??array();
			$statusColors = $POST['status_colors[]']??array();
			$repair_statuses = $status_colors = '';
			
			if(is_array($repairStatuses) && COUNT($repairStatuses)>0){
				$l = 0;
				foreach($repairStatuses as $onerepair_statuses){						
					if($onerepair_statuses !=''){
						if($onerepair_statuses =='Estimate' || $onerepair_statuses =='Cancelled'){}
						else{
							if($repair_statuses !=''){
								$repair_statuses .= '||';
								$status_colors .= '||';
							}
							$repair_statuses .= $onerepair_statuses;
							$status_colors .= $statusColors[$l];
						}
					}
					$l++;
				}
			}
			if(is_array($repairStatuses) && COUNT($repairStatuses)>0){
				//$repair_statuses = implode('||', $repairStatuses);
			}
			
			if(is_array($statusColors) && COUNT($statusColors)>0){
				//$status_colors = implode('||', $statusColors);
			}
			$dataArray['repair_statuses'] = addslashes(stripslashes($repair_statuses));
			$dataArray['status_colors'] = addslashes(stripslashes($status_colors));
			
			$dataArray['logo_size'] = $POST['logo_size']??'';
			$dataArray['logo_placement'] = $POST['logo_placement']??'';
			$dataArray['title'] = addslashes(stripslashes($POST['title']??''));
			$dataArray['company_info'] = addslashes(stripslashes($POST['company_info']??''));

			$dataArray['customer_name'] = intval($POST['customer_name']??0);
			$dataArray['customer_address'] = intval($POST['customer_address']??0);
			$dataArray['customer_phone'] = intval($POST['customer_phone']??0);
			$dataArray['customer_secondary_phone'] = intval($POST['customer_secondary_phone']??0);
			$dataArray['customer_email'] = intval($POST['customer_email']??0);
			$dataArray['customer_type'] = intval($POST['customer_type']??0);
		
			$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
			if($queryCFObj){
				while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
					$cfvalue = 0;
					if(array_key_exists("cf$oneCustomFields->custom_fields_id",$POST)){$cfvalue = 1;}
					$dataArray["cf$oneCustomFields->custom_fields_id"] = $cfvalue;
				}
			}

			$dataArray['sales_person'] = intval($POST['sales_person']??0);
			$dataArray['barcode'] = intval($POST['barcode']??0);
			$dataArray['status'] = intval($POST['status']??0);
			$dataArray['duedatetime'] = intval($POST['duedatetime']??0);
			$dataArray['technician'] = intval($POST['technician']??0);

			$dataArray['short_description'] = intval($POST['short_description']??0);

			$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'repairs' ORDER BY order_val ASC", array());
			if($queryCFObj){
				while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
					$cfvalue = 0;
					if(array_key_exists("cf$oneCustomFields->custom_fields_id",$POST)){$cfvalue = 1;}
					$dataArray["cf$oneCustomFields->custom_fields_id"] = $cfvalue;
				}
			}

			$dataArray['imei'] = intval($POST['imei']??0);
			$dataArray['brand'] = intval($POST['brand']??0);
			$dataArray['bin_location'] = intval($POST['bin_location']??0);
			$dataArray['lock_password'] = intval($POST['lock_password']??0);
			$dataArray['print_price_zero'] = intval($POST['print_price_zero']??0);
			$dataArray['repair_message'] = addslashes(stripslashes($POST['repair_message']??''));
			$dataArray['notes'] = intval($POST['notes']??0);
			
			$value = serialize($dataArray);
			$data=array('accounts_id'=>$accounts_id,
				'name'=>$this->db->checkCharLen('variables.name', 'repairs_setup'),
				'value'=>$value,
				'last_updated'=> date('Y-m-d H:i:s'));
			if($variables_id==0){
				$variables_id = $this->db->insert('variables', $data);
				if($variables_id){
					$savemsg = 'insert-success';
				}
			}
			else{
				$update = $this->db->update('variables', $data, $variables_id);
				if($update){
					$savemsg = 'update-success';
				}
			}

			$repairticketno = 1;
			$repairObj = $this->db->querypagination("SELECT ticket_no FROM repairs WHERE accounts_id = $accounts_id ORDER BY ticket_no DESC LIMIT 0, 1", array());
			if($repairObj){
				$repairticketno = $repairObj[0]['ticket_no']+1;
			}
			if($nextrepairticket_no>$repairticketno){
				$ticket_no = $nextrepairticket_no-1;
				$repairsData = array('from_repairs_id' => 0,		
									'pos_id' => 0,		
									'customer_id' => 0,		
									'ticket_no' => $ticket_no,
									'created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $accounts_id,
									'user_id' => $user_id,
									'repairs_publish' => 0,
									'problem' => '',
									'properties_id' => 0,
									'lock_password' => '',
									'due_datetime' => '1000-01-01',
									'due_time'=>'',
									'assign_to' => $_SESSION["user_id"],
									'status' => 'Invoiced',
									'bin_location' => '',
									'notify_how' => 0,
									'notify_email' => '',
									'notify_sms' => '',
									'custom_data'=>''
									);
				
				$repairs_id = $this->db->insert('repairs', $repairsData);
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
   	}

	public function repairCustomStatuses(){}

	public function AJ_repairCustomStatuses_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$accounts_id = $_SESSION['accounts_id']??0;		
		$repairStatuses = array( 'Assigned', 'On Hold', 'Waiting on Customer', 'Waiting for Parts');
		$statusColors = array('#FFFFFF', '#FFFFFF', '#FFFFFF', '#FFFFFF');
		$repair_statuses = $status_colors = '';
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
				if($repair_statuses !=''){
					$repairStatuses = explode('||',$repair_statuses);
					if(!empty($repairStatuses)){
						$p = 0;
						foreach($repairStatuses as $oneStatus){
							if(in_array($oneStatus, array('New', 'Finished'))){unset($repairStatuses[$p]);}
							$p++;
						}
					}
					//array_splice( $repairStatuses, 0, 0, array('New', 'Finished'));
				}
				if(isset($status_colors) && $status_colors !=''){
					$statusColors = explode('||',$status_colors);
				}
			}
		}
		
		$bgcolor0 = $statusColors[0]??'#5cb85c';
		$bgcolor1 = $statusColors[1]??'#000000';

		$jsonResponse['repairStatuses'] = array_values($repairStatuses);
		$jsonResponse['statusColors'] = $statusColors;
		
		$jsonResponse['bgcolor0'] = $bgcolor0;
		$jsonResponse['bgcolor1'] = $bgcolor1;
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
   public function AJsave_repairCustomStatuses(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$variables_id = 0;
		$savemsg = 'error';
		
		if(isset($POST)){
			$variables_id = intval($POST['variables_id']??0);
			$dataArray = array();
			$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
			if($varObj){
				$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
				$variables_id =  $variablesData->variables_id;
				$value = $variablesData->value;
				if(!empty($value)){
					$dataArray = unserialize($value);
				}
			}
			
			$repairStatuses = $POST['repair_statuses[]']??'';
			$statusColors = $POST['status_colors[]']??array();
			$repair_statuses = $status_colors = '';
			if(is_array($repairStatuses) && COUNT($repairStatuses)>0){
				$l = 0;
				foreach($repairStatuses as $onerepair_statuses){						
					if($onerepair_statuses !=''){
						if($onerepair_statuses =='Estimate' || $onerepair_statuses =='Cancelled'){}
						else{
							if($repair_statuses !=''){
								$repair_statuses .= '||';
								$status_colors .= '||';
							}
							$repair_statuses .= $onerepair_statuses;
							$status_colors .= $statusColors[$l];
						}
					}
					$l++;
				}
			}
			
			$dataArray['repair_statuses'] = addslashes(stripslashes($repair_statuses));
			$dataArray['status_colors'] = addslashes(stripslashes($status_colors));
			
			$value = serialize($dataArray);
			$data=array('accounts_id'=>$accounts_id,
				'name'=>$this->db->checkCharLen('variables.name', 'repairs_setup'),
				'value'=>$value,
				'last_updated'=> date('Y-m-d H:i:s'));
			if($variables_id==0){
				$variables_id = $this->db->insert('variables', $data);
				if($variables_id){
					$savemsg = 'insert-success';
				}
			}
			else{
				$update = $this->db->update('variables', $data, $variables_id);
				if($update){
					$savemsg = 'update-success';
				}
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
   }
	//====================End=======================//

	public function notifications(){}

	public function AJ_notifications_MoreInfo(){

		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION['user_id']??0;		
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$status = 'Finished';
		$repairStatuses = array('New', 'Assigned', 'On Hold', 'Waiting on Customer', 'Waiting for Parts', 'Finished');
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('repair_statuses', $value)){
					$repairStatuses = explode('||',$value['repair_statuses']);
				}
			}
		}
		if(!in_array('Finished', $repairStatuses)){
			$repairStatuses[] = 'Finished';
		}

		$jsonResponse['status'] = $status;
		$jsonResponse['repairStatuses'] = $repairStatuses;
		
		return json_encode($jsonResponse);
	}
	
	public function AJgetNotificationsData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$status = trim((string) $POST['status']??'Finished');
		$subject = $email_body = $sms_text = '';
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'notifications'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(!empty($value)){
					if(array_key_exists($status, $value)){
						$statusData = $value[$status];
						$subject = $statusData['subject']??'';
						$email_body = stripslashes($statusData['email_body']??'');
						$sms_text = stripslashes($statusData['sms_text']??'');
					}
				}
			}
		}
		return json_encode(array('login'=>'', 'status'=>$status, 'subject'=>$subject, 'email_body'=>$email_body, 'sms_text'=>$sms_text));
	}
	
	public function AJgetNotificationsLists(){
	
		$accounts_id = $_SESSION["accounts_id"]??0;
		$tabledata = array();
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'notifications'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(!empty($value)){
					foreach($value as $status=>$statusData){
						
						$subject = trim((string) stripslashes($statusData['subject']??''));
						$email_body = nl2br(stripslashes($statusData['email_body']??''));
						$sms_text = nl2br(stripslashes($statusData['sms_text']??''));
						
						$tabledata[] = array($status, $subject, $email_body, $sms_text);
					}
				}
			}
		}
		return json_encode(array('login'=>'', 'tabledata'=>$tabledata));
	}
	
   public function AJsave_notifications(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$savemsg = 'error';

		$variables_id = 0;
		$status = trim((string) isset($POST['status']) ? $POST['status'] : '');
		$subject = addslashes(stripslashes(trim((string) $POST['subject']??'')));
		$email_body = addslashes(stripslashes(trim((string) $POST['email_body']??'')));
		$sms_text = addslashes(stripslashes(trim((string) $POST['sms_text']??'')));
		
		$notifications = array();			
		$varObj = $this->db->query("SELECT variables_id, value FROM variables WHERE accounts_id = $accounts_id AND name = 'notifications'", array());
		if($varObj){
			$varOneRow = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id = $varOneRow->variables_id;
			$value = $varOneRow->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(!empty($value)){
					foreach($value as $key=>$statusData){
						$notifications[$key] = $statusData;
					}
				}
			}
		}
		$notifications[$status] = array('subject'=>$subject, 'email_body'=>$email_body, 'sms_text'=>$sms_text);
		$value = serialize($notifications);
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'notifications'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
   }
	
   public function AJremoveNotificationsData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$savemsg = 'error';

		$variables_id = 0;
		$status = trim((string) $POST['status']??'');
		
		$notifications = array();			
		$varObj = $this->db->query("SELECT variables_id, value FROM variables WHERE accounts_id = $accounts_id AND name = 'notifications'", array());
		if($varObj){
			$varOneRow = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id = $varOneRow->variables_id;
			$value = $varOneRow->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(!empty($value)){
					foreach($value as $key=>$statusData){
						if($key != $status)
							$notifications[$key] = $statusData;
					}
				}
			}
		}
		
		$value = serialize($notifications);
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'notifications'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
   	}
	
	public function repairs_custom_fields(){}

	public function AJ_repairs_custom_fields_MoreInfo(){

		$user_id = $_SESSION['user_id']??0;		
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$Common = new Common($this->db);
		$jsonResponse['tabledata'] = $Common->load_custom_fields('repairs');
		
		return json_encode($jsonResponse);
	}
    
	public function forms(){}

	public function AJ_forms_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$sqlquery = "SELECT * FROM forms WHERE accounts_id = $prod_cat_man AND form_for = 'repairs' AND forms_publish = 1 ORDER BY form_name ASC";
		$query = $this->db->querypagination($sqlquery, array());						
		$tabledata = array();
		if($query){
			$s = 0;
			$preforms_id = 0;
			foreach($query as $oneRow){
				$s++;
				$forms_id = $oneRow['forms_id'];
				$form_name = stripslashes($oneRow['form_name']);
				$FormMatch = $oneRow['form_matches'];
				if(strcmp('Brand/Model', $oneRow['form_condition'])==0 && $FormMatch !=''){
					$FormMatchArray = explode('||', $FormMatch);
					if(count($FormMatchArray)>1){
						$FormMatch = $FormMatchArray[0];
					}
				}
				$tabledata[] = array($forms_id, $form_name, intval($oneRow['form_public']), intval($oneRow['required']), $oneRow['form_condition'], $FormMatch);
			}
		}
		$jsonResponse['accounts_id'] = $accounts_id;
		$jsonResponse['prod_cat_man'] = $prod_cat_man;
		$jsonResponse['tabledata'] = $tabledata;
		return json_encode($jsonResponse);
	}
    
	public function formFields(){}

	public function AJ_formFields_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$forms_id = intval($POST['forms_id']??0);
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$formsOneRow = array();
		$sqlquery = "SELECT * FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = :forms_id AND forms_publish = 1 ORDER BY forms_id ASC LIMIT 0, 1";
		$query = $this->db->querypagination($sqlquery, array('forms_id'=>$forms_id),1);						
		if($query){
			foreach($query as $oneRow){
				$forms_id = $oneRow['forms_id'];
				$formsOneRow = $oneRow;
			}
		}
		$jsonResponse['forms_id'] = $forms_id;
		$jsonResponse['formsOneRow'] = $formsOneRow;
		
		$sqlquery = "SELECT * FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = $forms_id AND forms_publish = 1 ORDER BY forms_id ASC LIMIT 0, 1";
		$query = $this->db->querypagination($sqlquery, array());						
		$tabledata = array();
		if($query){
			foreach($query as $oneRow){
				$forms_id = $oneRow['forms_id'];
				$form_definitions = $oneRow['form_definitions'];
				$form_definitions = unserialize($form_definitions);
				if(is_array($form_definitions)){
					$s=0;
					$preorder_val = 0;
					foreach($form_definitions as $oneFieldRow){
						$s++;
						$order_val = $oneFieldRow['order_val'];						
						
						$tabledata[] = array($forms_id, $order_val, $preorder_val, stripslashes(trim((string) $oneFieldRow['field_name'])), intval($oneFieldRow['field_required']), $oneFieldRow['field_type']);
						$preorder_val = $order_val;
					}
				}
			}
		}
		$jsonResponse['tabledata'] = $tabledata;
		
		$MakePublic = 'No';
		if($formsOneRow['form_public']>0){$MakePublic = 'Yes';}
		$Required = 'No';
		if($formsOneRow['required']>0){$Required = 'Yes';}
		$FormMatch = $formsOneRow['form_matches'];
		$model = '';
		if(strcmp('Brand/Model', $formsOneRow['form_condition'])==0 && $FormMatch !=''){
			$FormMatchArray = explode('||', $FormMatch);
			if(count($FormMatchArray)>1){
				$FormMatch = $FormMatchArray[0];
				$model = $FormMatchArray[1];
			}
		}
		$jsonResponse['MakePublic'] = $MakePublic;
		$jsonResponse['Required'] = $Required;
		$jsonResponse['FormMatch'] = $FormMatch;
		$jsonResponse['model'] = $model;

		return json_encode($jsonResponse);
		
	}
    
	//==========================Products=====================///
	
	public function products_custom_fields(){}

	public function AJ_products_custom_fields_MoreInfo(){

		$user_id = $_SESSION['user_id']??0;		
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$Common = new Common($this->db);
		$jsonResponse['tabledata'] = $Common->load_custom_fields('product');
		
		return json_encode($jsonResponse);
	}
	
	//=================Orders====================//
	public function customStatuses(){}

	public function AJ_customStatuses_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$accounts_id = $_SESSION['accounts_id']??0;		
		$order_statusesarray = array('Waiting on Customer');
		$statusColors = array();
		
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'orderStatuses'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
				if($order_statuses !=''){
					$order_statusesarray = explode('||',$order_statuses);
				}
				if(isset($status_colors) && $status_colors !=''){
					$statusColors = explode('||',$status_colors);
				}
			}
		}
		
		$bgcolor0 = $statusColors[0]??'#5cb85c';
		$bgcolor1 = $statusColors[1]??'#000000';
		
		$jsonResponse['order_statusesarray'] = $order_statusesarray;
		$jsonResponse['statusColors'] = $statusColors;
		$jsonResponse['bgcolor0'] = $bgcolor0;
		$jsonResponse['bgcolor1'] = $bgcolor1;
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
   public function AJsave_customStatuses(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$variables_id = 0;
		$savemsg = 'error';
		
		if(isset($POST)){
			$variables_id = intval($POST['variables_id']??0);
			if($variables_id==0){
				$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='orderStatuses'", array());
				if($queryObj){
					$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
				}
			}

			$dataArray = array();
			
			$order_statusesarray = $POST['order_statuses[]']??'';
			$statusColors = $POST['status_colors[]']??array();
			$order_statuses = $status_colors = '';
			if(is_array($order_statusesarray) && COUNT($order_statusesarray)>0){
				foreach($order_statusesarray as $oneorder_statuses){						
					if($oneorder_statuses !=''){
						if($oneorder_statuses =='New' || $oneorder_statuses =='Quotes' || $oneorder_statuses =='Canceled'){}
						else{
							if($oneorder_statuses !=''){$order_statuses .= '||';}
							$order_statuses .= $oneorder_statuses;
						}
					}
				}
			}
			if(is_array($statusColors) && COUNT($statusColors)>0){
				$status_colors = implode('||', $statusColors);
			}
			$dataArray['order_statuses'] = addslashes(stripslashes($order_statuses));
			$dataArray['status_colors'] = addslashes(stripslashes($status_colors));
			
			$value = serialize($dataArray);
			$data=array('accounts_id'=>$accounts_id,
				'name'=>$this->db->checkCharLen('variables.name', 'orderStatuses'),
				'value'=>$value,
				'last_updated'=> date('Y-m-d H:i:s'));
			if($variables_id==0){
				$variables_id = $this->db->insert('variables', $data);
				if($variables_id){
					$savemsg = 'insert-success';
				}
			}
			else{
				$update = $this->db->update('variables', $data, $variables_id);
				if($update){
					$savemsg = 'update-success';
				}
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
   }
	
	public function ordersPrint(){}

	public function AJ_ordersPrint_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$default_invoice_printer = 'Small';
		$title = $this->db->translate('Sales Receipt');
		$logo_size = 'Small Logo';
		$logo_placement = 'Left';
		$company_info = $_SESSION["company_name"];
		$accObj = $this->db->query("SELECT company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accObj){
			$accOneRow = $accObj->fetch(PDO::FETCH_OBJ);
			if($accOneRow->company_street_address !=''){
				$company_info .= "\r\n$accOneRow->company_street_address";
			}
			if($accOneRow->company_city !='' || $accOneRow->company_state_name !='' || $accOneRow->company_zip !=''){
				$company_info .= "\r\n";
				$company_info .= "$accOneRow->company_city";
				if($accOneRow->company_city !='' && $accOneRow->company_state_name !=''){
					$company_info .= ", ";
				}
				$company_info .= "$accOneRow->company_state_name";
				if($accOneRow->company_zip !='' && $accOneRow->company_state_name !=''){
					$company_info .= " - ";
				}
				$company_info .= "$accOneRow->company_zip";
			}
			if($accOneRow->company_country_name !=''){
				$company_info .= "\r\n$accOneRow->company_country_name";
			}
		}
		
		$invoice_message_above = $invoice_message = $value = '';
		$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
		$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;
		
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'orders_print'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		
		$jsonResponse['default_invoice_printer'] = $default_invoice_printer;
		$jsonResponse['title'] = $title;
		$jsonResponse['logo_size'] = $logo_size;
		$jsonResponse['logo_placement'] = $logo_placement;
		$jsonResponse['company_info'] = stripslashes($company_info);
		$jsonResponse['invoice_message_above'] = stripslashes($invoice_message_above);
		$jsonResponse['invoice_message'] = stripslashes($invoice_message);
		$jsonResponse['value'] = $value;
		$jsonResponse['customer_name'] = $customer_name;
		$jsonResponse['customer_address'] = $customer_address;
		$jsonResponse['customer_phone'] = $customer_phone;
		$jsonResponse['customer_email'] = $customer_email;
		$jsonResponse['sales_person'] = $sales_person;
		$jsonResponse['secondary_phone'] = $secondary_phone;
		$jsonResponse['customer_type'] = $customer_type;
		$jsonResponse['barcode'] = $barcode;
		$jsonResponse['print_price_zero'] = $print_price_zero;
		$jsonResponse['notes'] = $notes;
		
		$lastOrderNo = 0;
		$posObj = $this->db->querypagination("SELECT pos_id FROM pos WHERE accounts_id = $accounts_id AND pos_type = 'Order' AND order_status=1 ORDER BY invoice_no DESC LIMIT 0, 1", array());
		if($posObj){
			$lastOrderNo = $posObj[0]['pos_id'];
		}		
		$jsonResponse['lastOrderNo'] = $lastOrderNo;

		$onePicture = $alt = '';
		$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
		$pics = glob($filePath."*.jpg");
		if($pics){
			$totalpics = COUNT($pics);
			$l=0;
			foreach($pics as $onePictureInfo){
				$l++;
				if($l==1){
					$onePicture = str_replace('./', '/', $onePictureInfo);
					$alt = str_replace("/assets/accounts/a_$accounts_id/", '', $onePicture);
				}
				else{
					if (file_exists($onePictureInfo)){
						unlink($onePictureInfo);
					}
				}
			}				
		}
		$jsonResponse['onePicture'] = $onePicture;
		$jsonResponse['alt'] = $alt;
		
		$customFields = array();
		$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
		if($queryCFObj){
			while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
				$field_name = stripslashes($oneCustomFields->field_name);
				$checked = '';
				if(!empty($value) && array_key_exists("cf$oneCustomFields->custom_fields_id",$value) && $value["cf$oneCustomFields->custom_fields_id"]>0){$checked = ' checked';}
				$customFields[$oneCustomFields->custom_fields_id] = array($field_name, $checked);
			}
		}
		
		$jsonResponse['customFields'] = $customFields;
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
	public function AJsave_ordersPrint(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$variables_id = intval($POST['variables_id']??0);
		if($variables_id==0){
			$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='orders_print'", array());
			if($queryObj){
				$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;						
			}
		}
		$conditionarray = array();
		$default_invoice_printer = $POST['default_invoice_printer']??'Small';
		$conditionarray['default_invoice_printer'] = $default_invoice_printer;
		$conditionarray['logo_size'] = $POST['logo_size']??'';
		$conditionarray['logo_placement'] = $POST['logo_placement']??'';
		$conditionarray['title'] = $POST['title']??'';
		$conditionarray['company_info'] = addslashes(stripslashes($POST['company_info']??''));
		
		$customer_name = intval($POST['customer_name']??0);
		$conditionarray['customer_name'] = $customer_name;
		
		$customer_address = intval($POST['customer_address']??0);
		$conditionarray['customer_address'] = $customer_address;
		
		$customer_phone = intval($POST['customer_phone']??0);
		$conditionarray['customer_phone'] = $customer_phone;
		
		$secondary_phone = intval($POST['secondary_phone']??0);
		$conditionarray['secondary_phone'] = $secondary_phone;
		
		$customer_email = intval($POST['customer_email']??0);
		$conditionarray['customer_email'] = $customer_email;
		
		$customer_type = intval($POST['customer_type']??0);
		$conditionarray['customer_type'] = $customer_type;
		
		$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
		if($queryCFObj){
			while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
				$cfvalue = 0;
				if(array_key_exists("cf$oneCustomFields->custom_fields_id",$POST)){$cfvalue = 1;}
				$conditionarray["cf$oneCustomFields->custom_fields_id"] = $cfvalue;
			}
		}
		
		$sales_person = intval($POST['sales_person']??0);
		$conditionarray['sales_person'] = $sales_person;
		
		$barcode = intval($POST['barcode']??0);
		$conditionarray['barcode'] = $barcode;
		
		$conditionarray['invoice_message_above'] = addslashes(stripslashes($POST['invoice_message_above']??''));
		
		$print_price_zero = intval($POST['print_price_zero']??0);
		$conditionarray['print_price_zero'] = $print_price_zero;
		
		$conditionarray['invoice_message'] = addslashes(stripslashes($POST['invoice_message']??''));
		
		$notes = intval($POST['notes']??0);
		$conditionarray['notes'] = $notes;
		
		$value = serialize($conditionarray);
		$data=array('accounts_id'=>$accounts_id,
					'name'=>$this->db->checkCharLen('variables.name', 'orders_print'),
					'value'=>$value,
					'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}
		
		$array = array( 'login'=>'', 'id'=>$variables_id,
						'savemsg'=>$savemsg);
		return json_encode($array);
	}

	//==================Invoices==================//
	
	public function invoices_general(){}

	public function AJ_invoices_general_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;		
		$accounts_id = $_SESSION['accounts_id']??0;		
		$invoice_backup_email = '';
		$default_invoice_printer = 'Small';
		$title = $this->db->translate('Sales Receipt');
		$logo_size = 'Small Logo';
		$logo_placement = 'Left';
		$company_info = $invoice_message_above = $invoice_message = $value = '';
		$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
		$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;
		
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		$jsonResponse['invoice_backup_email'] = $invoice_backup_email;
		$jsonResponse['default_invoice_printer'] = $default_invoice_printer;
		$jsonResponse['title'] = $title;
		$jsonResponse['logo_size'] = $logo_size;
		$jsonResponse['logo_placement'] = $logo_placement;
		$jsonResponse['company_info'] = stripslashes($company_info);
		$jsonResponse['invoice_message_above'] = stripslashes($invoice_message_above);
		$jsonResponse['invoice_message'] = stripslashes($invoice_message);
		$jsonResponse['value'] = $value;
		$jsonResponse['customer_name'] = $customer_name;
		$jsonResponse['customer_address'] = $customer_address;
		$jsonResponse['customer_phone'] = $customer_phone;
		$jsonResponse['customer_email'] = $customer_email;
		$jsonResponse['sales_person'] = $sales_person;
		$jsonResponse['secondary_phone'] = $secondary_phone;
		$jsonResponse['customer_type'] = $customer_type;
		$jsonResponse['barcode'] = $barcode;
		$jsonResponse['print_price_zero'] = $print_price_zero;
		$jsonResponse['notes'] = $notes;
		
		$nextinvoice_no = 1;
		$posObj = $this->db->querypagination("SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id ORDER BY invoice_no DESC LIMIT 0, 1", array());
		if($posObj){
			$nextinvoice_no = $posObj[0]['invoice_no']+1;
		}
		$jsonResponse['nextinvoice_no'] = $nextinvoice_no;

		$onePicture = $alt = '';
		$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
		$pics = glob($filePath."*.jpg");
		if($pics){
			$totalpics = COUNT($pics);
			$l=0;
			foreach($pics as $onePictureInfo){
				$l++;
				if($l==1){
					$onePicture = str_replace('./', '/', $onePictureInfo);
					$alt = str_replace("/assets/accounts/a_$accounts_id/", '', $onePicture);
				}
				else{
					if (file_exists($onePictureInfo)){
						unlink($onePictureInfo);
					}
				}
			}				
		}
		$jsonResponse['onePicture'] = $onePicture;
		$jsonResponse['alt'] = $alt;
		$customFields = array();
		$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
		if($queryCFObj){
			while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
				$field_name = stripslashes($oneCustomFields->field_name);
				$checked = '';
				if(!empty($value) && array_key_exists("cf$oneCustomFields->custom_fields_id",$value) && $value["cf$oneCustomFields->custom_fields_id"]>0){$checked = ' checked';}
				$customFields[$oneCustomFields->custom_fields_id] = array($field_name, $checked);
			}
		}
		
		$jsonResponse['customFields'] = $customFields;
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
	public function AJsave_invoices_general(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$variables_id = intval($POST['variables_id']??0);
		if($variables_id==0){
			$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='invoice_setup'", array());
			if($queryObj){
				$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;						
			}
		}

		$conditionarray = array();
		$conditionarray['invoice_backup_email'] = $POST['invoice_backup_email']??'';									
		$default_invoice_printer = $POST['default_invoice_printer']??'Small';
		$conditionarray['default_invoice_printer'] = $default_invoice_printer;
		$conditionarray['logo_size'] = $POST['logo_size']??'';
		$conditionarray['logo_placement'] = $POST['logo_placement']??'';
		$conditionarray['title'] = $POST['title']??'';
		$conditionarray['company_info'] = addslashes(stripslashes($POST['company_info']??''));
		
		$customer_name = intval($POST['customer_name']??0);
		$conditionarray['customer_name'] = $customer_name;
		
		$customer_address = intval($POST['customer_address']??0);
		$conditionarray['customer_address'] = $customer_address;
		
		$customer_phone = intval($POST['customer_phone']??0);
		$conditionarray['customer_phone'] = $customer_phone;
		
		$secondary_phone = intval($POST['secondary_phone']??0);
		$conditionarray['secondary_phone'] = $secondary_phone;
		
		$customer_email = intval($POST['customer_email']??0);
		$conditionarray['customer_email'] = $customer_email;
		
		$customer_type = intval($POST['customer_type']??0);
		$conditionarray['customer_type'] = $customer_type;
		
		$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
		if($queryCFObj){
			while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
				$cfvalue = 0;
				if(array_key_exists("cf$oneCustomFields->custom_fields_id",$POST)){$cfvalue = 1;}
				$conditionarray["cf$oneCustomFields->custom_fields_id"] = $cfvalue;
			}
		}
		
		$sales_person = intval($POST['sales_person']??0);
		$conditionarray['sales_person'] = $sales_person;
		
		$barcode = intval($POST['barcode']??0);
		$conditionarray['barcode'] = $barcode;
		
		$conditionarray['invoice_message_above'] = addslashes(stripslashes($POST['invoice_message_above']??''));
		
		$print_price_zero = intval($POST['print_price_zero']??0);
		$conditionarray['print_price_zero'] = $print_price_zero;
		
		$conditionarray['invoice_message'] = addslashes(stripslashes($POST['invoice_message']??''));
		
		$notes = intval($POST['notes']??0);
		$conditionarray['notes'] = $notes;
		
		$value = serialize($conditionarray);
		$data=array('accounts_id'=>$accounts_id,
					'name'=>$this->db->checkCharLen('variables.name', 'invoice_setup'),
					'value'=>$value,
					'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}
		
		$nextinvoiceno = floor($POST['nextinvoiceno']??1);
		$nextinvoice_no = 1;
		$posObj =$this->db->querypagination("SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id ORDER BY invoice_no DESC LIMIT 0, 1", array());
		if($posObj){
			$nextinvoice_no = $posObj[0]['invoice_no']+1;
		}
		
		if($nextinvoiceno !='' && $nextinvoiceno>$nextinvoice_no){
			$status = 'Trial';
			if(isset($_SESSION["status"])){$status = $_SESSION["status"];}
			if(in_array($status, array('SUSPENDED', 'CANCELED'))){
				return json_encode(array('login'=>'session_ended'));
			}
			
			$nextinvoiceno = $nextinvoiceno-1;
			$posData = array('invoice_no' => $nextinvoiceno, 
							'sales_datetime' => date('Y-m-d H:i:s'), 
							'employee_id' => $user_id, 
							'customer_id' => 0, 
							'taxes_name1' => '',
							'taxes_percentage1' => 0,
							'tax_inclusive1' => 0,
							'taxes_name2' => '',
							'taxes_percentage2' => 0,
							'tax_inclusive2' => 0,
							'pos_type' => 'Sale', 
							'created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'user_id' => $user_id, 
							'accounts_id' => $accounts_id, 
							'pos_publish' => 0, 
							'credit_days' => 0, 
							'is_due' => 0,
							'status'=>'New');
			$this->db->insert('pos', $posData);
		}
	
		$array = array( 'login'=>'', 'id'=>$variables_id,
						'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	//===================Devices====================//	
	public function carriers(){}

	public function AJ_carriers_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$accounts_id = $_SESSION['accounts_id']??0;		
		
		$carriersarray = $conditionsarray = array();
		$variables_id = 0;
		$Common = new Common($this->db);
		$vData = $Common->variablesData('product_setup', $accounts_id);
		if(!empty($vData)){
			if(array_key_exists('carriers', $vData)){
				$carriersarray1 = explode('||',$vData['carriers']);
				if(!empty($carriersarray1)){
					$newCarr = array();
					foreach($carriersarray1 as $oneCar){
						if(!empty($oneCar)){$newCarr[$oneCar] = '';}
					}
					$carriersarray = array_keys($newCarr);
				}
			}
			if(array_key_exists('conditions', $vData)){
				$conditionsarray1 = explode('||',$vData['conditions']);
				if(!empty($conditionsarray1)){
					$newCarr = array();
					foreach($conditionsarray1 as $oneCar){
						if(!empty($oneCar)){$newCarr[$oneCar] = '';}
					}
					$conditionsarray = array_keys($newCarr);
				}
			}
			$variables_id = $vData['variables_id']??0;
		}
		$jsonResponse['carriersarray'] = $carriersarray;
		$jsonResponse['conditionsarray'] = $conditionsarray;
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
	public function conditions(){}

	public function AJ_conditions_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$accounts_id = $_SESSION['accounts_id']??0;		
		
		$carriersarray = $conditionsarray = array();
		$variables_id = 0;
		$Common = new Common($this->db);
		$vData = $Common->variablesData('product_setup', $accounts_id);
		if(!empty($vData)){
			if(array_key_exists('carriers', $vData)){
				$carriersarray1 = explode('||',$vData['carriers']);
				if(!empty($carriersarray1)){
					$newCarr = array();
					foreach($carriersarray1 as $oneCar){
						if(!empty($oneCar)){$newCarr[$oneCar] = '';}
					}
					$carriersarray = array_keys($newCarr);
				}
			}
			if(array_key_exists('conditions', $vData)){
				$conditionsarray1 = explode('||',$vData['conditions']);
				if(!empty($conditionsarray1)){
					$newCarr = array();
					foreach($conditionsarray1 as $oneCar){
						if(!empty($oneCar)){$newCarr[$oneCar] = '';}
					}
					$conditionsarray = array_keys($newCarr);
				}
			}
			$variables_id = $vData['variables_id']??0;
		}
		$jsonResponse['carriersarray'] = $carriersarray;
		$jsonResponse['conditionsarray'] = $conditionsarray;
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
   public function AJsave_devices(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';

		$accounts_id = $_SESSION["accounts_id"]??0;
		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='product_setup'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}
		
		$carriersarray = $POST['carriers[]']??'';
		$carriers = '';
		if(is_array($carriersarray) && COUNT($carriersarray)>0){
			$i=0;
			foreach($carriersarray as $onecarriers){
				if($onecarriers !=''){
					$i++;
					if($i>1){$carriers .= '||';}
					$carriers .= $onecarriers;
				}
			}
		}

		$conditionsarray = $POST['conditions[]']??'';
		$conditions = '';
		if(is_array($conditionsarray) && COUNT($conditionsarray)>0){
			$i=0;
			foreach($conditionsarray as $oneconditions){
				if($oneconditions !=''){
					$i++;
					if($i>1){$conditions .= '||';}
					$conditions .= $oneconditions;
				}
			}
		}

		$value = serialize(array('carriers'=>$carriers, 'conditions'=> $conditions));
		$data = array('accounts_id'=>$accounts_id,
					'name'=>$this->db->checkCharLen('variables.name', 'product_setup'),
					'value'=>$value,
					'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}
		
		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
	}
    
	public function devices_custom_fields(){}

	public function AJ_devices_custom_fields_MoreInfo(){

		$user_id = $_SESSION['user_id']??0;		
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$Common = new Common($this->db);
		$jsonResponse['tabledata'] = $Common->load_custom_fields('devices');
		
		return json_encode($jsonResponse);
	}  

	//====================Customers===================//
	public function customers_custom_fields(){}

	public function AJ_customers_custom_fields_MoreInfo(){

		$user_id = $_SESSION['user_id']??0;		
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$Common = new Common($this->db);
		$jsonResponse['tabledata'] = $Common->load_custom_fields('customers');
		
		return json_encode($jsonResponse);
	}
    
	//==================Cash_Register=================//
	
	public function cash_Register_general(){}

	public function AJ_cash_Register_general_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$accounts_id = $_SESSION['accounts_id']??0;		
		$cash_reg_req_customer = $cash_drawer_sale = $petty_cash_tracking = 0;
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'cash_register_options'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		$jsonResponse['cash_reg_req_customer'] = $cash_reg_req_customer;
		$jsonResponse['cash_drawer_sale'] = $cash_drawer_sale;
		$jsonResponse['petty_cash_tracking'] = $petty_cash_tracking;
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
   public function AJsave_cash_Register_general(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = intval($POST['variables_id']??0);
		if($variables_id==0){
			$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='cash_register_options'", array());
			if($queryObj){
				$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
			}
		}

		$cash_reg_req_customer = intval($POST['cash_reg_req_customer']??0);
		$cash_drawer_sale = intval($POST['cash_drawer_sale']??0);
		$petty_cash_tracking = intval($POST['petty_cash_tracking']??0);
		
		$value = serialize(array('cash_reg_req_customer'=>$cash_reg_req_customer, 'cash_drawer_sale'=>$cash_drawer_sale, 'petty_cash_tracking'=>$petty_cash_tracking));
		$_SESSION["petty_cash_tracking"] = $petty_cash_tracking;
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'cash_register_options'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
   }
    
	public function counting_Cash_Til(){}

	public function AJ_counting_Cash_Til_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$accounts_id = $_SESSION['accounts_id']??0;		
		$currency = $_SESSION['currency']??'৳';
				
		$denominationsArray = array($currency.'100=100', $currency.'50=50', $currency.'20=20', $currency.'10=10', $currency.'5=5', $currency.'1=1', 'Quarters (.25)=.25', 'Dimes (.10)=.10', 'Nickels (.05)=.05', 'Pennies (.01)=.01');
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'counting_Cash_Til'", array());
		if($varObj){
			$denominationsArray = array();
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('denominations', $value)){
					$denominationsArray1 = explode('||',$value['denominations']);
					if(!empty($denominationsArray1)){
						$newCarr = array();
						foreach($denominationsArray1 as $oneCar){
							if(!empty($oneCar)){$newCarr[$oneCar] = '';}
						}
						$denominationsArray = array_keys($newCarr);
					}
				}
			}
		}
		$jsonResponse['denominationsArray'] = $denominationsArray;
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
	public function AJsave_counting_Cash_Til(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';

		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$varObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id = $accounts_id AND name = 'counting_Cash_Til'", array());
		if($varObj){
			$variables_id = $varObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}

		$dOptionArray = $POST['dOption[]'];
		$dValueArray = $POST['dValue[]'];
		$denominations = array();
		if(is_array($dOptionArray) && count($dOptionArray)>0){
			$i=0;
			foreach($dOptionArray as $dOption){
				$dValue = $dValueArray[$i];
				if($dOption !='' && $dValue !=''){
					$denominations[] = "$dOption=$dValue";
				}
				$i++;                        
			}
		}
		if(!empty($denominations)){$denominations = implode('||', $denominations);}
		$value = serialize(array('denominations'=>$denominations));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'counting_Cash_Til'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}

		$array = array( 'login'=>'','id'=>$variables_id,
			'savemsg'=>$savemsg);
		echo json_encode($array);
   }
	
	public function multiple_Drawers(){}

	public function AJ_multiple_Drawers_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$accounts_id = $_SESSION['accounts_id']??0;		
		$cdData = array();
		$variables_id = $multiple_cash_drawers = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'multiple_drawers'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
				if(isset($cash_drawers))
					$cdData = explode('||', stripslashes($cash_drawers));
			}
		}
		$jsonResponse['cdData'] = $cdData;
		$jsonResponse['multiple_cash_drawers'] = intval($multiple_cash_drawers);
		$jsonResponse['variables_id'] = intval($variables_id);
		return json_encode($jsonResponse);
	}
	
   public function AJsave_multiple_Drawers(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$omultiple_cash_drawers = 0;
		$ocash_drawers = 0;
		$queryObj = $this->db->query("SELECT variables_id, value FROM variables WHERE accounts_id=$accounts_id AND name='multiple_drawers'", array());
		if($queryObj){
			$variablesData = $queryObj->fetch(PDO::FETCH_OBJ);
			if($variablesData){
				$variables_id = $variablesData->variables_id;
				if(!empty($variablesData->value)){
					$valueData = unserialize($variablesData->value);
					if(array_key_exists('multiple_cash_drawers', $valueData)){
						$omultiple_cash_drawers = intval($valueData['multiple_cash_drawers']);
					}
					if(array_key_exists('cash_drawers', $valueData)){
						$ocash_drawers = $valueData['cash_drawers'];
					}
				}
			}
		}

		$multiple_cash_drawers = intval($POST['multiple_cash_drawers']??0);
		$cash_drawers = $POST['cash_drawers[]']??array();
		$cash_drawersStr = '';
		if(!empty($cash_drawers) && is_array($cash_drawers) && $multiple_cash_drawers>0){$cash_drawersStr = implode('||', $cash_drawers);}
		
		$value = serialize(array('multiple_cash_drawers'=>$multiple_cash_drawers, 'cash_drawers'=>$cash_drawersStr));
		
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'multiple_drawers'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		$checkPayment = 0;
		$todayData = date('Y-m-d');

		if($omultiple_cash_drawers != $multiple_cash_drawers){
			$paymentObj = $this->db->query("SELECT COUNT(petty_cash_id) AS countData FROM petty_cash WHERE eod_date = :todayData AND accounts_id = $accounts_id AND petty_cash_publish = 1", array('todayData'=>$todayData));
			if($paymentObj){
				$checkPayment += $paymentObj->fetch(PDO::FETCH_OBJ)->countData;
			}
			$paymentObj = $this->db->query("SELECT COUNT(pp.pos_payment_id) AS countData FROM pos, pos_payment pp WHERE substring(pp.payment_datetime,1,10) = :todayData AND pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pp.pos_id", array('todayData'=>$todayData));
			if($paymentObj){
				$checkPayment += $paymentObj->fetch(PDO::FETCH_OBJ)->countData;
			}			
		}
		elseif(!empty($ocash_drawers) && $ocash_drawers != $cash_drawersStr){
			$drawersPayment = array();
			$paymentObj = $this->db->query("SELECT drawer FROM petty_cash WHERE eod_date = :todayData AND accounts_id = $accounts_id AND petty_cash_publish = 1 GROUP BY drawer", array('todayData'=>$todayData));
			if($paymentObj){
				while($paymentOneRow = $paymentObj->fetch(PDO::FETCH_OBJ)){
					$drawersPayment[$paymentOneRow->drawer] = '';
				}
			}
			$paymentObj = $this->db->query("SELECT pp.drawer FROM pos, pos_payment pp WHERE substring(pp.payment_datetime,1,10) = :todayData AND pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pp.pos_id GROUP BY pp.drawer", array('todayData'=>$todayData));
			if($paymentObj){
				while($paymentOneRow = $paymentObj->fetch(PDO::FETCH_OBJ)){
					$drawersPayment[$paymentOneRow->drawer] = '';
				}
			}

			if(!empty($cash_drawersStr) && !empty($drawersPayment)){
				foreach(explode('||', $cash_drawersStr) as $oneDrawer){
					if(array_key_exists($oneDrawer, $drawersPayment)){
						unset($drawersPayment[$oneDrawer]);
					}
				}
			}

			if(!empty($drawersPayment)){$checkPayment++;}
		}

		if($checkPayment>0){
			$savemsg = 'beforeAnyPayments';
		}
		else{
			if($variables_id==0){
				$variables_id = $this->db->insert('variables', $data);
				if($variables_id){
					$savemsg = 'insert-success';
				}
			}
			else{
				$update = $this->db->update('variables', $data, $variables_id);
				if($update){
					$savemsg = 'update-success';
				}
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
   }
    

}
?>