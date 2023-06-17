<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Our_billing{	
	protected $db;
	private int $page, $totalRows;
	private string $keyword_search, $limit;
	
	public function __construct($db){$this->db = $db;}

	public function payment_details(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$status = '';
		$accObj = $this->db->query("SELECT status FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accObj){
			$status = $accObj->fetch(PDO::FETCH_OBJ)->status;
		}
		if($status=='Active' && $_SESSION["status"] !='Active'){
			return "<meta http-equiv = \"refresh\" content = \"0; url = /session_ended\" />";
		}
	}
	
	public function AJ_payment_details_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$admin_id = $_SESSION["admin_id"]??0;
		$price_per_location = 0;
		$next_payment_due = '1000-01-01';
		$pay_frequency = 'Monthly';
		$transactionID = $paymentBrand = $paymentAccountID = $status = $paypal_id = $customer_service_email = '';				
		$accObj = $this->db->query("SELECT pay_frequency, next_payment_due, transactionID, paymentBrand, paymentAccountID, status, price_per_location, paypal_id, customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accObj){
			$accData = $accObj->fetch(PDO::FETCH_OBJ);
			$pay_frequency = $accData->pay_frequency;
			$transactionID = $accData->transactionID;
			$paymentBrand = $accData->paymentBrand;
			$paymentAccountID = $accData->paymentAccountID;
			$next_payment_due = $accData->next_payment_due;
			$status = $accData->status;
			$price_per_location = $accData->price_per_location;
			$paypal_id = trim($accData->paypal_id);
			$customer_service_email = trim($accData->customer_service_email);
		}

		$No_of_Location = 1;
		$accSql = "SELECT COUNT(accounts_id) as totalAccount FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man) AND status != 'SUSPENDED'";
		$noOfLocObj = $this->db->query($accSql, array());
		if($noOfLocObj){
			while($oneRow = $noOfLocObj->fetch(PDO::FETCH_OBJ)){
				$No_of_Location = $oneRow->totalAccount;
			}
		}

		$jsonResponse['paymentAccountID'] = $paymentAccountID;
		$jsonResponse['paypal_id'] = $paypal_id;
		$jsonResponse['next_payment_due'] = $next_payment_due;
		$jsonResponse['status'] = $status;
		$jsonResponse['price_per_location'] = number_format($price_per_location,2);
		$jsonResponse['pay_frequency'] = $pay_frequency;

		return json_encode($jsonResponse);
	}
	
	public function updateAccounts(){
	
		$returnStr = '';			
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$accountsObj = $this->db->query("SELECT customer_service_email, company_subdomain FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accountsObj){
			
			$accountsOneRow = $accountsObj->fetch(PDO::FETCH_OBJ);
			$sub_domain = $accountsOneRow->company_subdomain;
			$customer_service_email = $accountsOneRow->customer_service_email;
			$this->db->update('accounts', array('status'=>'Active', 'status_date'=>date('Y-m-d H:i:s'), 'next_payment_due'=>date('Y-m-d'), 'price_per_location'=>29.99), $accounts_id);
			
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
			$mail->addAddress($this->db->supportEmail('support'), COMPANYNAME);
			$mail->Subject = $this->db->translate('Clicked Subscribe');
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			//Build a simple message body
			$mail->Body = "<p>Sub-Domain : $GLOBALS[sub_domain]</p>";
								
			if($mail->send()){
				session_unset();
				session_destroy();
				$returnStr = 'Sent';
			}
			else{
				$returnStr = 'notSend';
			}
		}	
		else{
			$returnStr = 'notSend';
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJgetPage_payment_details($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$limit = $POST['limit']??'auto';
		
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_payment_details();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->limit = $limit;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_payment_details();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_payment_details(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Our_billing";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim($keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', invoice_number, paid_by, description, pay_frequency)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim($keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery ="SELECT COUNT(our_invoices_id) AS totalrows FROM our_invoices WHERE accounts_id = $accounts_id $filterSql";
		$query = $this->db->query($sqlquery, $bindData);
		$totalRows = 0;
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}

		$this->totalRows = $totalRows;
	}
	
    private function loadTableRows_payment_details(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$limit = $this->limit;
		$page = $this->page;
		$totalRows = $this->totalRows;
		$keyword_search = $this->keyword_search;
		
		if(!is_integer($limit) || $limit<=0){$limit = 1;}

		$starting_val = floor($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$filterSql = "";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim($keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', invoice_number, paid_by, description, pay_frequency)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim($keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * FROM our_invoices WHERE accounts_id = $accounts_id $filterSql ORDER BY invoice_number DESC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tableData = array();
		if($query){
			foreach($query as $rowproduct){
				
				$our_invoices_id = $rowproduct['our_invoices_id'];
				$invoice_number = $rowproduct['invoice_number'];
				$paymentAccountID = $rowproduct['paymentAccountID'];
				$description = $rowproduct['description'];
				$paid_by = $rowproduct['paid_by'];
				$total = number_format($rowproduct['num_locations']*$rowproduct['price_per_location'],2);
				$tableData[] = array($our_invoices_id, $rowproduct['paid_on'], $invoice_number, $description, $paid_by, $rowproduct['next_payment_due'], "\$$total");
			}
			
		}
		
		return $tableData;
    }
		
	public function prints($segment4name){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$our_invoices_id = intval($GLOBALS['segment4name']);
		$language = $_SESSION["language"]??'English';
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

		$htmlStr = "";
		$our_invoicesObj = $this->db->query("SELECT * FROM our_invoices WHERE our_invoices_id = :our_invoices_id AND accounts_id = $accounts_id", array('our_invoices_id'=>$our_invoices_id),1);
		if($our_invoicesObj){			
			$htmlStr .= '<!DOCTYPE html>
			<html>
			<head>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
				<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
				<script language="JavaScript" type="text/javascript">var currency = \''.$currency.'\';var calenderDate = \''.$calenderDate.'\';var timeformat = \''.$timeformat.'\';var loadLangFile = \''.$loadLangFile.'\';var langModifiedData = {};</script>
				<script src="/assets/js-'.swVersion.'/'.commonJS.'"></script>
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
				<script src="/assets/js-'.swVersion.'/'.printsJS.'"></script>
			</body>
			</html>';
		}
		return $htmlStr;
	}
	
	public function AJ_prints_MoreInfo(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['COMPANYNAME'] = COMPANYNAME;
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$our_invoices_id = intval($POST['our_invoices_id']??0);
		$jsonResponse['our_invoices_id'] = $our_invoices_id;
		$sql = "SELECT * FROM our_invoices WHERE our_invoices_id = :our_invoices_id AND accounts_id = $accounts_id";
		
		$our_invoicesObj = $this->db->query($sql, array('our_invoices_id'=>$our_invoices_id),1);
		if($our_invoicesObj){
			$our_invoices_onerow = $our_invoicesObj->fetch(PDO::FETCH_OBJ);
			$invoice_number = $our_invoices_onerow->invoice_number;
			$title = 'accounts Billing #'.$invoice_number;
			$description = $our_invoices_onerow->description;
			$num_locations = $our_invoices_onerow->num_locations;
			$price_per_location = round($our_invoices_onerow->price_per_location,2);
			$total = $num_locations*$price_per_location;
			
			$paid_by = $our_invoices_onerow->paid_by;
			if($paid_by==''){$paid_by = 'Unpaid';}
			
			$jsonResponse['title'] = $title;

			$company_info = '';
			$accountsObj = $this->db->query("SELECT company_name, company_street_address, company_city, company_state_name, company_zip, company_country_name FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accountsObj){
				$accountsOneRow = $accountsObj->fetch(PDO::FETCH_OBJ);
				$company_info = stripslashes(trim($accountsOneRow->company_name));
				$address = stripslashes(trim($accountsOneRow->company_street_address));
				$company_city = stripslashes(trim($accountsOneRow->company_city));
				if($company_city !=''){$address .= ', '.$company_city;}
				$company_state_name = stripslashes(trim($accountsOneRow->company_state_name));
				if($company_state_name !=''){$address .= ', '.$company_state_name;}
				$company_zip = stripslashes(trim($accountsOneRow->company_zip));
				if($company_zip !=''){$address .= ' '.$company_zip;}
				$company_country_name = stripslashes(trim($accountsOneRow->company_country_name));
				if($company_country_name !=''){$address .= ', '.$company_country_name;}
				
				if($company_info !=''){
					$company_info .='<br />';
				}
				$company_info .= $address;
			}
			$jsonResponse['supportEmail'] = $this->db->supportEmail('info');
			$jsonResponse['company_info'] = nl2br($company_info);
			$jsonResponse['description'] = nl2br($description);
			$jsonResponse['num_locations'] = $num_locations;
			$jsonResponse['price_per_location'] = $price_per_location;
			$jsonResponse['total'] = $total;
			$jsonResponse['paid_by'] = $paid_by;
			$jsonResponse['paid_on'] = $our_invoices_onerow->paid_on;
		}
		return json_encode($jsonResponse);
	}
	    
	public function locations(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$admin_id = $_SESSION['admin_id']??0;
		$status = $paypal_id = '';
		$accObj = $this->db->query("SELECT status, paypal_id FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accObj){
			$accObj = $accObj->fetch(PDO::FETCH_OBJ);
			$status = $accObj->status;
			$paypal_id = $accObj->paypal_id;
		}
		
		if($status=='Trial' && $prod_cat_man>10 && $admin_id==0){
			return "<input type=\"hidden\" id=\"redirectTo\" value=\"/Our_billing/payment_details\">					
					<script type=\"text/javascript\">
						setTimeout(function() {showMessAndRedi(Translate('Locations Setup'), Translate('The feature to have multiple locations and to transfer inventory from one store to another is only available after you subscribe. If you would like to know more about how this feature works send us a message and we will explain it to you'));}, 100);
					</script>";
		}
		else{
			$locationAllow = 0;
			if($admin_id>0 || $prod_cat_man<10){$locationAllow = 1;}
			return "<input type=\"hidden\" id=\"locationAllow\" value=\"$locationAllow\">
			<input type=\"hidden\" id=\"userStatus\" value=\"$status\">
			<input type=\"hidden\" id=\"paypal_id\" value=\"$paypal_id\">";
		}
	}
	
	public function AJsave_locations(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$company_subdomain = addslashes($POST['name']??'');
		$company_subdomain = $this->db->checkCharLen('accounts.company_subdomain', $company_subdomain);
		
		if(strlen($company_subdomain)>=5){
			$totalrows = 0;
			$queryObj = $this->db->query("SELECT COUNT(accounts_id) AS totalrows FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$company_subdomain, 'domain'=>OUR_DOMAINNAME));
			if($queryObj){
				$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			if($totalrows>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{

				$accountsObj2 = $this->db->querypagination("SELECT * FROM accounts WHERE accounts_id = $accounts_id ORDER BY accounts_id ASC LIMIT 0, 1", array());
				if($accountsObj2){
					$accountsData = $accountsObj2[0];

					$fieldDonotUpdate=array('accounts_id',
											'company_subdomain',
											'created_on',
											'trial_days',
											'next_payment_due',
											'paypal_id',
											'location_of');

					foreach($fieldDonotUpdate as $oneField){
						unset($accountsData[$oneField]);
					}
					
					$company_name = $company_subdomain;
					$company_country_name = $accountsData['company_country_name'];
					$customer_service_email = $accountsData['customer_service_email'];
					
					$accountsData['created_on'] = date('Y-m-d H:i:s');
					$accountsData['company_name'] = $company_subdomain;
					$accountsData['company_subdomain'] = $company_subdomain;
					$accountsData['next_payment_due'] = '1000-01-01';
					$accountsData['paypal_id'] = '';
					$accountsData['location_of'] = $prod_cat_man;
					$accountsData['trial_days'] = 1;
					$accountsData['status'] = 'Trial';
					$accountsData['status_date'] = date('Y-m-d H:i:s');

					$newaccounts_id = $this->db->insert('accounts', $accountsData);
					if($newaccounts_id){
						$savemsg = 'Add';
						$newuser_id = 0;
						$userObj2 = $this->db->query("SELECT * FROM user WHERE accounts_id = $accounts_id AND is_admin = 1 ORDER BY user_id ASC LIMIT 0, 1", array());
						if($userObj2){
							$userData = (array) $userObj2->fetch(PDO::FETCH_OBJ);

							$fieldDonotUpdate=array('user_id', 'accounts_id');

							foreach($fieldDonotUpdate as $oneField){
								unset($userData[$oneField]);
							}

							$userData['last_updated'] = date('Y-m-d');
							$userData['accounts_id'] = $newaccounts_id;
							$userData['last_request'] = '1000-01-01 00:00:00';

							$newuser_id = $this->db->insert('user', $userData);
						}
						
						$Common = new Common($this->db);
						$vData = $Common->variablesData('account_setup', $accounts_id);
						if(!empty($vData)){
							$data=array('accounts_id'=>$newaccounts_id,
										'name'=>$this->db->checkCharLen('variables.name', 'account_setup'),
										'value'=>serialize($vData),
										'last_updated'=> date('Y-m-d H:i:s'));
							$this->db->insert('variables', $data);
						}
						//============For invoice_setup =========//
						$company_info = "$company_name \r\n$company_country_name \r\n$customer_service_email";
						
						$isvalueArray = array('invoice_backup_email'=>'', //$user_email, 
											'default_invoice_printer'=> 'Large',
											'title'=> 'Sales Receipt',
											'company_info'=> $company_info,
											'customer_name'=> 1,
											'customer_address' => 0,
											'customer_phone' => 0,
											'customer_email' => 0,
											'sales_person' => 0,
											'barcode' => 0,
											'invoice_message_above'=>'',
											'print_price_zero'=> 1,
											'invoice_message'=> '',
											'notes'=>1
											);						
						$data=array('accounts_id'=>$newaccounts_id,
									'name'=>$this->db->checkCharLen('variables.name', 'invoice_setup'),
									'value'=>serialize($isvalueArray),
									'last_updated'=> date('Y-m-d H:i:s'));
						$this->db->insert('variables', $data);
						//============For repairs_setup =========//					
						$rsvalueArray=array('repair_sort'=>'customers.first_name ASC', 
											'repair_statuses'=> 'Assigned||On Hold||Waiting on Customer||Waiting for Parts',
											'title'=>'Repair Ticket',
											'company_info'=> $company_info,
											'customer_name'=> 1,
											'customer_address' => 0,
											'customer_phone' => 0,
											'customer_secondary_phone'=>0,
											'customer_email' => 0,
											'customer_type' => 0,
											'sales_person' => 0,
											'barcode' => 0,
											'status'=>0,
											'duedatetime'=>0,
											'technician'=>1,
											'short_description'=>1,
											'imei'=>1,
											'brand'=>1,
											'bin_location'=>1,
											'print_price_zero'=> 1,
											'repair_message'=> '',
											'notes'=>1
											);
						
						$data=array('accounts_id'=>$newaccounts_id,
									'name'=>$this->db->checkCharLen('variables.name', 'repairs_setup'),
									'value'=>serialize($rsvalueArray),
									'last_updated'=> date('Y-m-d H:i:s'));
						$this->db->insert('variables', $data);
						
						//================Adding new Taxes table data==============//
						$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND default_tax = 1 ORDER BY taxes_id DESC LIMIT 0, 1", array(),1);
						if($taxesObj){					
							$taxes_name = $taxesObj[0]['taxes_name'];
							$taxes_name = $this->db->checkCharLen('taxes.taxes_name', $taxes_name);
							$taxes_percentage = round($taxesObj[0]['taxes_percentage'],3);
							$tax_inclusive = $taxesObj[0]['tax_inclusive'];
							
							$taxesdata = array( 'taxes_name'=>$taxes_name,
												'taxes_percentage'=>$taxes_percentage,
												'default_tax'=>1,
												'tax_inclusive'=>$tax_inclusive,
												'created_on' => date('Y-m-d H:i:s'),	
												'accounts_id'=>$newaccounts_id,
												'user_id'=>$newuser_id
												);
							$taxes_id = $this->db->insert('taxes', $taxesdata);
						}
						
						//=============For Product Inventory=======//
						$sqlProduct = "SELECT product_id FROM product WHERE accounts_id = $prod_cat_man ORDER BY product_id ASC";
						$queryProduct = $this->db->query($sqlProduct, array());
						if($queryProduct){
							while($oneProductRow = $queryProduct->fetch(PDO::FETCH_OBJ)){
								$product_id = $oneProductRow->product_id;

								$inventoryObj = $this->db->query("SELECT * FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_id", array());
								if($inventoryObj){
									$inventoryOneRow = (array)$inventoryObj->fetch(PDO::FETCH_OBJ);
									unset($inventoryOneRow['inventory_id']);
									$inventoryOneRow['product_id'] = $product_id;
									$inventoryOneRow['current_inventory'] = 0;
									$inventoryOneRow['ave_cost'] = 0;
									$inventoryOneRow['accounts_id'] = $newaccounts_id;

									$this->db->insert('inventory', $inventoryOneRow);
								}
							}
						}
						
						$returnStr = $company_subdomain.'.'.OUR_DOMAINNAME;
					}
					else{
						$returnStr = 'errorOnAdding';
					}
				}
			}
		}
		echo json_encode(array('login'=>'', 'savemsg'=>$savemsg, 'returnStr'=>$returnStr));
	}
	
	public function AJgetPage_locations($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$this->limit = $POST['limit']??'auto';
		
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_locations();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_locations();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_locations(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Our_billing";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
		
		$filterSql = "FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man)  AND status != 'SUSPENDED'";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim($keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND company_subdomain LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim($keyword_searches[$num]);
				$num++;
			}
		}
		$totalRows = 0;
		$strextra ="SELECT COUNT(accounts_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
    private function loadTableRows_locations(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $this->limit;
		$page = $this->page;
		$totalRows = $this->totalRows;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$filterSql = "FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man)  AND status != 'SUSPENDED'";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim($keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND company_subdomain LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim($keyword_searches[$num]);
				$num++;
			}
		}		
		
		$sqlquery = "SELECT accounts_id, company_subdomain $filterSql ORDER BY company_subdomain ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tableData = array();
		if($query){
			foreach($query as $oneRow){

				$laccounts_id = $oneRow['accounts_id'];
				$company_subdomain = stripslashes($oneRow['company_subdomain']);
				$tableData[] = array('prod_cat_man'=>$prod_cat_man, 'laccounts_id'=>$laccounts_id, 'company_subdomain'=>$company_subdomain);
			}
		}
		return $tableData;
    }
	
	public function AJarchive_locations(){
		if(!isset($_SESSION["prod_cat_man"])){
			echo json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$returnStr = '';
			$user_id = $_SESSION["user_id"]??0;
			$accounts_id = intval($POST['accounts_id']??0);
			$company_subdomain = $POST['name']??'';
			$status = 'SUSPENDED';
			$oldStatus = '';
			$accOneObj = $this->db->query("SELECT status FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accOneObj){
				$oldStatus = $accOneObj->fetch(PDO::FETCH_OBJ)->status;
			}
			$updatetable = $this->db->update('accounts', array('status'=>$status, 'status_date'=>date('Y-m-d H:i:s')), $accounts_id);
			if($updatetable){
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
				
				$activity_feed_title = 'Location Archived';
				$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
				$activity_feed_link = "";
				$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
				
				$afData = array('created_on' => date('Y-m-d H:i:s'),
								'last_updated' => date('Y-m-d H:i:s'),
								'accounts_id' => $_SESSION["accounts_id"],
								'user_id' => $_SESSION["user_id"],
								'activity_feed_title' => $activity_feed_title,
								'activity_feed_name' => $company_subdomain,
								'activity_feed_link' => $activity_feed_link,
								'uri_table_name' => "accounts",
								'uri_table_field_name' =>"status",
								'field_value' => 'SUSPENDED');
				$this->db->insert('activity_feed', $afData);
				
			}
			$returnStr = 'archive-success';
			
			echo json_encode(array('login'=>'', 'returnStr'=>$returnStr));
		}
	}
	
	public function showNoteDescription($isArray = 0){
		$tableData = array();
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$fromPage = $POST['fromPage']??'';
		if(is_array($POST) && array_key_exists('fromPage', $POST) && $fromPage=='Admin'){
			$accounts_id = intval($POST['accounts_id']??0);
		}
		
		$sqlquery = "SELECT * FROM our_notes WHERE accounts_id = $accounts_id ORDER BY created_on DESC LIMIT 0, 30";
		$query = $this->db->querypagination($sqlquery, array());
		
		if($query){
			foreach($query as $oneRow){
				$description = nl2br(stripslashes($oneRow['description']));
				$tableData[] = array($oneRow['our_notes_id'], $oneRow['created_on'], $description, $fromPage);
			}
		}
		if($isArray ==1){
			return $tableData;
		}
		else{
			return json_encode(array('login'=>'', 'tableData'=>$tableData));
		}
	}

	public function closeAccounts(){
	
		$accounts_id = $_SESSION["accounts_id"]??0;
		$returnStr = 'error';
		$oldStatus = '';
		$accOneObj = $this->db->query("SELECT status FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accOneObj){
			$oldStatus = $accOneObj->fetch(PDO::FETCH_OBJ)->status;
		}
		
		$status = 'Pending';
		$accountsData=array('status'=>$status,
							'paypal_id'=>'',
							'status_date'=>date('Y-m-d H:i:s'));
		$update = $this->db->update('accounts', $accountsData, $accounts_id);
		if($update){
			date_default_timezone_set('America/New_York');
			$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
								'accounts_id'=>$accounts_id,
								'description'=>"Account canceled via ".COMPANYNAME.".");// Account status changed from $oldStatus to $status
			$this->db->insert('our_notes', $our_notesData);
			$timezone = 'America/New_York';
			if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
			date_default_timezone_set($timezone);
			
			$returnStr = 'update-success';
			$_SESSION["status"] = $status;				
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}		
}
?>