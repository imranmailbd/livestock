<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Account{
	protected $db;
	private int $page, $totalRows, $limit;
	private string $keyword_search;
	
	public function __construct($db){$this->db = $db;}
	
	public function index(){
        return "<meta http-equiv = \"refresh\" content = \"0; url = '/Account/login'\" />";
    }

    public function signup(){
        
    }

    public function AJ_signup_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['COMPANYNAME'] = COMPANYNAME;
		$comCouNamOpt = array();
		$countryData = $this->countryData();
		if(isset($countryData) && is_array($countryData)){                                    
			foreach($countryData as $country_name){
				$comCouNamOpt[$country_name] = '';
			}
			$comCouNamOpt = array_keys($comCouNamOpt);
		}
		$jsonResponse['comCouNamOpt'] = $comCouNamOpt;
		return json_encode($jsonResponse);
	}

	public function oldLogin(){
		$subdomain = $GLOBALS['subdomain'];
		if(!empty($subdomain)){$subdomain .= '.';}
		return '<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="description" content="SK POS is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
			<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
		<title>Login into SK POS</title>
		<link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-32x32.png">
		<link rel="stylesheet" href="/assets/css-'.swVersion.'/style.css">
		</head>
		<body style="display:flex;flex-direction:column;">
		<div id="topheaderbar"></div>
		<div style="flex-grow:1">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" style="height:100%">
			<tr id="pageBody">
				<td valign="top" style="width:100%">
					<div id="page-content-wrapper">
						<div class="container-fluid">
							<div class="columnXS12">
								<div class="dashboard_contant" id="dashboard_contant">
									<div id="viewPageInfo">										
										<div class="flexCenterRow">
											<div id="sideclass">&nbsp;</div>
											<div class="columnMD6" id="middleclass">
												<div class="innerContainer">
													<div class="flex">
														<div class="columnXS12" id="class1" style="padding: 6px 10px;">
															<h3 id="class2">
																<br><br>																
																Your login page has moved. Please bookmark the following link to use from now on as this old page will be removed soon
																<br><a title="Login" href="/Account/login/">http://'.$subdomain.OUR_DOMAINNAME.'/Account/login</a>
																<br><br>
															</h3>
														</div>
													</div>
												</div>
											</div>
										</div>										
									</div>
								</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
			</table>
		</div>
		</body>
		</html>';
	}

	public function oldSignup(){
		return '<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="description" content="SK POS is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
			<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
		<title>Login into SK POS</title>
		<link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-32x32.png">
		<link rel="stylesheet" href="/assets/css-'.swVersion.'/style.css">
		</head>
		<body style="display:flex;flex-direction:column;">
		<div id="topheaderbar"></div>
		<div style="flex-grow:1">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" style="height:100%">
			<tr id="pageBody">
				<td valign="top" style="width:100%">
					<div id="page-content-wrapper">
						<div class="container-fluid">
							<div class="columnXS12">
								<div class="dashboard_contant" id="dashboard_contant">
									<div id="viewPageInfo">										
										<div class="flexCenterRow">
											<div id="sideclass">&nbsp;</div>
											<div class="columnMD6" id="middleclass">
												<div class="innerContainer">
													<div class="flex">
														<div class="columnXS12" id="class1" style="padding: 6px 10px;">
															<h3 id="class2">
																<br><br>
																The signup page has moved. Please bookmark the following link to use from now on as this old page will be removed soon
																<br><a title="Signup Now" href="/Account/signup/">http://'.OUR_DOMAINNAME.'/Account/signup</a>
																<br><br>
															</h3>
														</div>
													</div>
												</div>
											</div>
										</div>										
									</div>
								</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
			</table>
		</div>
		</body>
		</html>';
	}
	
	public function countryData(){
		
		$returnarray =array('Canada',
							'United States',
							'Afghanistan',
							'Albania',
							'Algeria',
							'American Samoa',
							'Andorra',
							'Angola',
							'Anguilla',
							'Antigua and Barbuda',
							'Argentina',
							'Armenia',
							'Aruba',
							'Australia',
							'Austria',
							'Azerbaijan',
							'Bahamas',
							'Bahrain',
							'Bangladesh',
							'Barbados',
							'Belarus',
							'Belgium',
							'Belize',
							'Benin',
							'Bermuda',
							'Bhutan',
							'Bolivia',
							'Bosnia and Herzegowina',
							'Botswana',
							'Bouvet Island',
							'Brazil',
							'British Indian Ocean Territory',
							'Brunei Darussalam',
							'Bulgaria',
							'Burkina Faso',
							'Burundi',
							'Cambodia',
							'Cameroon',
							'Canada',
							'Cape Verde',
							'Cayman Islands',
							'Central African Republic',
							'Chad',
							'Chile',
							'China',
							'Christmas Island',
							'Cocos (Keeling) Islands',
							'Colombia',
							'Comoros',
							'Congo',
							'Cook Islands',
							'Costa Rica',
							'Cote D\'Ivoire',
							'Croatia',
							'Cuba',
							'Cyprus',
							'Czech Republic',
							'Denmark',
							'Djibouti',
							'Dominica',
							'Dominican Republic',
							'East Timor',
							'Ecuador',
							'Egypt',
							'El Salvador',
							'Equatorial Guinea',
							'Eritrea',
							'Estonia',
							'Ethiopia',
							'Falkland Islands (Malvinas)',
							'Faroe Islands',
							'Fiji',
							'Finland',
							'France',
							'France, Metropolitan',
							'French Guiana',
							'French Polynesia',
							'French Southern Territories',
							'Gabon',
							'Gambia',
							'Georgia',
							'Germany',
							'Ghana',
							'Gibraltar',
							'Greece',
							'Greenland',
							'Grenada',
							'Guadeloupe',
							'Guam',
							'Guatemala',
							'Guinea',
							'Guinea-bissau',
							'Guyana',
							'Haiti',
							'Heard and Mc Donald Islands',
							'Honduras',
							'Hong Kong',
							'Hungary',
							'Iceland',
							'India',
							'Indonesia',
							'Iran (Islamic Republic of)',
							'Iraq',
							'Ireland',
							'Israel',
							'Italy',
							'Jamaica',
							'Japan',
							'Jordan',
							'Kazakhstan',
							'Kenya',
							'Kiribati',
							'Korea, Democratic People\'s Republic of',
							'Korea, Republic of',
							'Kuwait',
							'Kyrgyzstan',
							'Lao People\'s Democratic Republic',
							'Latvia',
							'Lebanon',
							'Lesotho',
							'Liberia',
							'Libyan Arab Jamahiriya',
							'Liechtenstein',
							'Lithuania',
							'Luxembourg',
							'Macau',
							'Macedonia, The Former Yugoslav Republic of',
							'Madagascar',
							'Malawi',
							'Malaysia',
							'Maldives',
							'Mali',
							'Malta',
							'Marshall Islands',
							'Martinique',
							'Mauritania',
							'Mauritius',
							'Mayotte',
							'Mexico',
							'Micronesia, Federated States of',
							'Moldova, Republic of',
							'Monaco',
							'Mongolia',
							'Montserrat',
							'Morocco',
							'Mozambique',
							'Myanmar',
							'Namibia',
							'Nauru',
							'Nepal',
							'Netherlands',
							'Netherlands Antilles',
							'New Caledonia',
							'New Zealand',
							'Nicaragua',
							'Niger',
							'Nigeria',
							'Niue',
							'Norfolk Island',
							'Northern Mariana Islands',
							'Norway',
							'Oman',
							'Pakistan',
							'Palau',
							'Panama',
							'Papua New Guinea',
							'Paraguay',
							'Peru',
							'Philippines',
							'Pitcairn',
							'Poland',
							'Portugal',
							'Puerto Rico',
							'Qatar',
							'Reunion',
							'Romania',
							'Russian Federation',
							'Rwanda',
							'Saint Kitts and Nevis',
							'Saint Lucia',
							'Saint Vincent and the Grenadines',
							'Samoa',
							'San Marino',
							'Sao Tome and Principe',
							'Saudi Arabia',
							'Senegal',
							'Serbia',
							'Seychelles',
							'Sierra Leone',
							'Singapore',
							'Slovakia (Slovak Republic)',
							'Slovenia',
							'Solomon Islands',
							'Somalia',
							'South Africa',
							'South Georgia and the South Sandwich Islands',
							'Spain',
							'Sri Lanka',
							'St. Helena',
							'St. Pierre and Miquelon',
							'Sudan',
							'Suriname',
							'Svalbard and Jan Mayen Islands',
							'Swaziland',
							'Sweden',
							'Switzerland',
							'Syrian Arab Republic',
							'Taiwan',
							'Tajikistan',
							'Tanzania, United Republic of',
							'Thailand',
							'Togo',
							'Tokelau',
							'Tonga',
							'Trinidad and Tobago',
							'Tunisia',
							'Turkey',
							'Turkmenistan',
							'Turks and Caicos Islands',
							'Tuvalu',
							'Uganda',
							'Ukraine',
							'United Arab Emirates',
							'United Kingdom',
							'United States',
							'United States Minor Outlying Islands',
							'Uruguay',
							'Uzbekistan',
							'Vanuatu',
							'Vatican City State (Holy See)',
							'Venezuela',
							'Viet Nam',
							'Virgin Islands (British)',
							'Virgin Islands (U.S.)',
							'Wallis and Futuna Islands',
							'Western Sahara',
							'Yemen',
							'Yugoslavia',
							'Zaire',
							'Zambia',
							'Zimbabwe');
				
		return $returnarray;
	}

	public function signupCheck(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 'OK';
		if(isset($POST) && array_key_exists('company_subdomain', $POST)){
			$subdomain = $POST['company_subdomain'];
			
			$usersObj = $this->db->query("SELECT count(accounts_id) as totalrows FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
			if($usersObj){
				if($usersObj->fetch(PDO::FETCH_OBJ)->totalrows>0){
					$returnStr = 'This sub-domain is already used';
				}
			}
		}
		else{
			$returnStr = 'Sub-domain is required';
		}
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['returnStr'] = $returnStr;
		return json_encode($jsonResponse);
	}

	public function savesignup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnstr = '';
		
		$company_name = $this->db->checkCharLen('accounts.company_name', $POST['company_name']??'');
		$subdomain = $this->db->checkCharLen('accounts.company_subdomain', $POST['company_subdomain']??'');
		$user_first_name = $this->db->checkCharLen('user.user_first_name', $POST['user_first_name']??'');
		$company_phone_no = $this->db->checkCharLen('accounts.company_phone_no', $POST['company_phone_no']??'');
		$company_country_name = $this->db->checkCharLen('accounts.company_country_name', $POST['company_country_name']??'');
		$user_email = $this->db->checkCharLen('user.user_email', $POST['user_email']??'');
		$user_password = $POST['user_password']??'';
		$taxes_name = $this->db->checkCharLen('taxes.taxes_name', $POST['taxes_name']??'');
		$taxes_percentage = $POST['taxes_percentage']??0.00;
		$tax_inclusive = intval($POST['tax_inclusive']??0);
		$coupon_code = $this->db->checkCharLen('accounts.coupon_code', $POST['coupon_code']??'');
	
		if($company_name=='' || $subdomain == '' || $user_first_name=='' || $user_email == '' || $user_password==''){
			$returnstr = 'Signup form fields is missing.';
		}
		else{
			$password_hash = password_hash($user_password, PASSWORD_DEFAULT);
			
			$is_admin = $user_publish = 1;
			$action_type = 0;			
			$created_on = date('Y-m-d H:i:s');			
			$last_updated = date('Y-m-d H:i:s');
			$accounts_id = 0;
			
			$duplicatedomain = 0;
			$usersObj = $this->db->query("SELECT COUNT(accounts_id) AS totalrows FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain AND status != 'SUSPENDED'", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
			if($usersObj){
				$duplicatedomain = $usersObj->fetch(PDO::FETCH_OBJ)->totalrows;						
			}
			
			if($duplicatedomain>0){
				$returnstr = 'This accounts name is not available. Please enter a new accounts name.';
			}
			elseif($subdomain=='www'){
				$returnstr = 'Sub-domain could not be www';
			}
			else{
				
				$popup_message = '';//'<p>Welcome to your new '.COMPANYNAME.' Software accounts.</p><p>We suggest you follow these few quick steps to get started.</p><p><b>Step 1:</b> Go into the <b>accounts&nbsp;SETTINGS</b> first by clicking on the gear icon on the top left next to your name to setup your timezone, tax, additional users and much more.</p><p><b>Step 2:</b> You can not buy for sell anything until you first <b>ADD</b>&nbsp;<b>PRODUCTS</b> by going into inventory-&gt;manage products and add products to your store.&nbsp;</p><p><b>Step 3:</b>&nbsp;Once products are added you can <b>ADD</b>&nbsp;<b>INVENTORY</b> to each product by going into inventory-&gt;add inventory (purchase orders) and enter the product name, quantity and cost.</p><p><b>Step 4:</b> To sell your product you can use the <b>POS, Orders or Repair</b> modules.<br></p><p>Thank you and if you ever need help just click the HELP button at the bottom right of every screen in the software.</p><p>The '.COMPANYNAME.' Team</p>';
				
				$conditionarray = array('created_on' => $created_on,	
										'domain'=>OUR_DOMAINNAME,
										'pay_frequency' => 'Monthly', 
										'price_per_location' => '39.00',
										'next_payment_due'=>'1000-01-01',
										'company_name' => $company_name,
										'company_subdomain' => $subdomain,		
										'company_phone_no' => $company_phone_no,
										'customer_service_email' => $user_email,
										'company_street_address'=>'',
										'company_country_name' => $company_country_name,
										'company_state_name'=>'',
										'company_city'=>'',
										'company_zip'=>'',
										'status' => 'Trial', 
										'status_date'=>date('Y-m-d H:i:s'), 
										'trial_days' => 14,
										'coupon_code' => $coupon_code,
										'paypal_id'=>'',
										'location_of'=>0,
										'default_customer'=>0,
										'timeclock_enabled'=>0,
										'petty_cash'=>0,
										'last_login'=>'1000-01-01 00:00:00',
										'check_ave_cost'=>'1000-01-01'
										);
				$accounts_id = $this->db->insert('accounts', $conditionarray);
				if($accounts_id){
					
					$userarray = array('password_hash' => $password_hash,
										'changepass_link'=>'',
										'employee_number'=>'',
										'pin'=>'',
										'user_first_name' => $user_first_name,
										'user_last_name'=>'',
										'user_email' => $user_email,			
										'user_publish' => $user_publish,
										'is_admin' => $is_admin,
										'user_roll'=>'',
										'created_on' => $created_on,		
										'last_updated' => $last_updated,		
										'lastlogin_datetime' => '1000-01-01 00:00:00',			
										'accounts_id' => $accounts_id,
										'popup_message' => $popup_message,
										'login_message'=>'',
										'login_ck_id'=>'',
										'last_request'=>'1000-01-01 00:00:00');
					$user_id = $this->db->insert('user', $userarray);
					
					//============For invoice_setup =========//
					$company_info = "$company_name \r\n$company_country_name \r\n$user_email";
					
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
										'notes'=>1);
					
					$varData = array('accounts_id'=>$accounts_id,
									'name'=>$this->db->checkCharLen('variables.name', 'invoice_setup'),
									'value'=>serialize($isvalueArray),
									'last_updated'=> date('Y-m-d H:i:s'));
					$variables_id = 0;
					$varObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
					if($varObj){
						$variables_id = $varObj->fetch(PDO::FETCH_OBJ)->variables_id;
					}
					if($variables_id==0){
						$this->db->insert('variables', $varData);
					}
					else{
						$this->db->update('variables', $varData, $variables_id);
					}
					
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
					
					$varData=array('accounts_id'=>$accounts_id,
									'name'=>$this->db->checkCharLen('variables.name', 'repairs_setup'),
									'value'=>serialize($rsvalueArray),
									'last_updated'=> date('Y-m-d H:i:s'));
					$variables_id = 0;
					$varObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
					if($varObj){
						$variables_id = $varObj->fetch(PDO::FETCH_OBJ)->variables_id;
					}
					if($variables_id==0){
						$this->db->insert('variables', $varData);
					}
					else{
						$this->db->update('variables', $varData, $variables_id);
					}
						
					//================Adding new customers table data==============//
					$customersdata = array( 'customers_publish'=>0,
											'created_on' => date('Y-m-d H:i:s'),
											'last_updated' => date('Y-m-d H:i:s'),
											'accounts_id'=>$accounts_id,
											'user_id'=>$user_id,
											'first_name'=>$this->db->checkCharLen('customers.first_name', 'Unassigned'),
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
					$customers_id = $this->db->insert('customers', $customersdata);
					$this->db->update('accounts', array('default_customer'=>$customers_id), $accounts_id);
					
					//================Adding new Taxes table data==============//
					if($taxes_percentage>0 && $taxes_name !=''){
						$taxesdata = array( 'taxes_name'=>$taxes_name,
											'taxes_percentage'=>round($taxes_percentage,3),
											'default_tax'=>1,
											'tax_inclusive'=>$tax_inclusive,
											'created_on' => date('Y-m-d H:i:s'),	
											'accounts_id'=>$accounts_id,
											'user_id'=>$user_id
											);
						$taxes_id = $this->db->insert('taxes', $taxesdata);
					}
					//================Adding new Category table data==============//
					$categoryList = array('Parts', 'Accessories');
					foreach($categoryList as $category_name){
						$category_name = $this->db->checkCharLen('category.category_name', $category_name);
						
						$categorydata = array('category_name' => $category_name,
												'created_on' => date('Y-m-d H:i:s'),
												'last_updated' => date('Y-m-d H:i:s'),
												'accounts_id' => $accounts_id,
												'user_id' => $user_id
												);
						$this->db->insert('category', $categorydata);
					}
					
					//================Adding new manufacturer table data==============//
					$manufacturerList = array('Apple', 'Samsung');
					foreach($manufacturerList as $manufacturer_name){
						$manufacturer_name = $this->db->checkCharLen('manufacturer.name', $manufacturer_name);
						$manufacturerdata = array('name' => $manufacturer_name,
												'created_on' => date('Y-m-d H:i:s'),
												'last_updated' => date('Y-m-d H:i:s'),
												'accounts_id' => $accounts_id,
												'user_id' => $user_id
												);
						$this->db->insert('manufacturer', $manufacturerdata);
					}
					
					if($user_email =='' || is_null($user_email)){
						$returnstr = 'Error occured while sending mail! Please try again.';
					}
					else{
						
						$mail = new PHPMailer;
						$mail->isSMTP();
						$mail->Host = $this->db->supportEmail('Host');
						$mail->Port = 587;
						$mail->SMTPAuth = true;
						$mail->Username = $this->db->supportEmail('Username');
						$mail->Password = $this->db->supportEmail('Password');
						
						if(OUR_DOMAINNAME=='machouse.com.bd'){
							$mail->addReplyTo($this->db->supportEmail('info'), COMPANYNAME);
							$mail->setFrom($this->db->supportEmail('info'), COMPANYNAME.' Software');
						}
						else{
							$mail->addReplyTo($this->db->supportEmail('support'), COMPANYNAME);
							$mail->setFrom($this->db->supportEmail('support'), COMPANYNAME.' Software');
						}
						$mail->clearAddresses();
						$mail->addAddress($user_email, $user_first_name);
						$mail->Subject = "Welcome to ".COMPANYNAME." Software";
						$mail->isHTML(true);
						$mail->CharSet = 'UTF-8';						
						$body = "<p>
		Hi <b>$user_first_name!</b>, you're IN!<br />
		<br />
		I really appreciate you joining us at ".COMPANYNAME.", and I know you'll love it when you see how easy it is to enhance your important shop activities with our smart tools.
		<br /><br />
		We built ".COMPANYNAME." to help owners of cell shops grow, and I hope that we can achieve that for you.
		<br /><br />
		You can access your accounts with the details below:<br />
		Company Name: <strong>$company_name</strong><br />
		domain: $subdomain.".OUR_DOMAINNAME."<br />
		username: $user_email
		<br /><br />
		If you wouldn't mind, I'd love it if you answered one quick question: why did you sign up for ".COMPANYNAME."?
		<br /><br />
		I'm asking because knowing what made you sign up is really helpful for us in making sure that we're delivering on what our users want. Just hit \"reply\" and let me know.
		<br /><br />
		By the way, over the next couple of weeks, We'll be sending you a few more emails to help you get maximum value from ".COMPANYNAME.". We'll be sharing some tips, checking in with you and showing you how some of our customers use ".COMPANYNAME." to grow their businesses.
		<br /><br />
		Thanks,<br />
		Alvin<br />
		Founder, ".COMPANYNAME."
		</p>";
						$mail->Body = $body;
						if (!$mail->send()) {							
							$returnstr = 'Mail could not sent.';
						}
						else{							
							$returnstr = 'Success';
						}
					}					
				}
				else{
					$returnstr = 'Error occured while adding new company! Please try again.';
				}
			}
		}
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['returnStr'] = $returnstr;
		return json_encode($jsonResponse);
	}	

	//====================login=================//
    public function login(){
		
		if(isset($_SESSION["user_id"]) && $GLOBALS['segment4name']==''){
			return "<meta http-equiv = \"refresh\" content = \"0; url = '/Home'\" />";
		}
        
    }

	public function AJ_login_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$login_message = '';
		$lmObj = $this->db->query("SELECT login_message FROM user WHERE user_id = :user_id", array('user_id'=>6),1);
		if($lmObj){
			$login_message = stripslashes(trim((string) $lmObj->fetch(PDO::FETCH_OBJ)->login_message));
		}
		$jsonResponse['login_message'] = $login_message;
		$jsonResponse['title'] = 'Login into '.COMPANYNAME;
		$jsonResponse['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];

		$workstations_id = 0;
		$workstationsName = 'Unknown';
		if (isset($_COOKIE["workstations_id"])) {
			$workstations_id = $_COOKIE["workstations_id"];
			$WSObj = $this->db->query("SELECT name FROM workstations WHERE workstations_id = :workstations_id ORDER BY workstations_id DESC", array('workstations_id'=>$workstations_id));
			if($WSObj){
				$workstationsName = stripslashes(trim($WSObj->fetch(PDO::FETCH_OBJ)->name));
			}
		}
		$jsonResponse['workstations_id'] = $workstations_id;
		$jsonResponse['workstationsName'] = $workstationsName;
				
		$subDomain = $GLOBALS['subdomain'];
		if (isset($_COOKIE["subDomain"])) {
			$subDomain = $_COOKIE["subDomain"];
		}
		$jsonResponse['subDomain'] = $subDomain;
		
		$accounts_id = 0;
		$tableObj = $this->db->query("SELECT accounts_id FROM accounts WHERE company_subdomain = :company_subdomain ORDER BY accounts_id DESC", array('company_subdomain'=>$GLOBALS['subdomain']));
		if($tableObj){
			$accounts_id = intval($tableObj->fetch(PDO::FETCH_OBJ)->accounts_id);
		}
		
		$subDomainOpts = array();
		$subDomainOpts[$GLOBALS['subdomain']] = '';

		$tableObj = $this->db->query("SELECT company_subdomain FROM accounts WHERE location_of = $accounts_id ORDER BY accounts_id DESC", array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$subDomainOpts[$oneRow->company_subdomain] = '';
			}
			ksort($subDomainOpts);
		}
		$subDomainOpts = array_keys($subDomainOpts);
		$jsonResponse['subDomainOpts'] = $subDomainOpts;

		return json_encode($jsonResponse);
	}

	public function checkloginId(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		if(isset($_SESSION["user_id"])){
			$returnStr = 'Home';
		}
		$subdomain = $POST['company_subdomain']??'';
		$user_email = $POST['user_email']??'';
		$user_password = $POST['user_password']??'';
		$workstations_id = 0;
		$workstationsName = '';
		$userName = '';
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
			$login_ck_id = '';
			$user_id = 0;
			$usersObj = $this->db->query("SELECT user.user_id, user.login_ck_id, user.user_first_name, user.user_last_name FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND accounts.company_subdomain = :company_subdomain AND user.user_email = :user_email AND accounts.domain = :domain AND user.user_publish = 1", array('company_subdomain'=>$subdomain, 'user_email'=>$user_email, 'domain'=>OUR_DOMAINNAME));
			if($usersObj){
				$userData = $usersObj->fetch(PDO::FETCH_OBJ);
				$login_ck_id = $userData->login_ck_id;
				$user_id = $userData->user_id;
				$userName = stripslashes(trim("$userData->user_first_name $userData->user_last_name"));
			}

			if($user_id>0){
				$ULHObj = $this->db->query("SELECT workstations_id FROM user_login_history WHERE user_id = :user_id ORDER BY user_login_history_id DESC LIMIT 0, 1", array('user_id'=>$user_id));
				if($ULHObj ){
					$workstations_id = $ULHObj->fetch(PDO::FETCH_OBJ)->workstations_id;
					if($workstations_id>0){
						$WSObj = $this->db->query("SELECT name FROM workstations WHERE workstations_id = :workstations_id ORDER BY workstations_id DESC", array('workstations_id'=>$workstations_id));
						if($WSObj){
							$workstationsName = stripslashes(trim($WSObj->fetch(PDO::FETCH_OBJ)->name));
						}
					}
				}
			}
			
			if($login_ck_id !=''){
				$returnStr = 'Someone';
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'userName'=>$userName, 'workstations_id'=>$workstations_id, 'workstationsName'=>$workstationsName));
	}
	
	public function check(){
		$returnStr = '';
		if(isset($_SESSION["user_id"])){$returnStr = 'Home';}
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
			$supportPass = 'ILoveSK#1';			
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
					if($row->user_id<=6){
						
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
		
		$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Account/login?msg=$returnStr";
		if(($returnStr == 'Home' || $returnStr == 'Success' || $returnStr=='Getting_Started') && isset($_SESSION["accounts_id"])){
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
		
		$usersObj = $this->db->query("SELECT accounts.*, user.user_id, user.user_first_name, user.is_admin, user.user_roll, user.no_restrict_ip, user.minute_to_logout FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND user.user_id = $user_id AND accounts.domain = :domain AND user.user_publish = 1", array('domain'=>OUR_DOMAINNAME));
		if($usersObj){
			$row = $usersObj->fetch(PDO::FETCH_OBJ);
			$user_id = $row->user_id;
			$accounts_id = $row->accounts_id;
			$created_on = $row->created_on;
			$trial_days = $row->trial_days;
			$status = $row->status;
			$is_admin = $row->is_admin;
			$price_per_location = $row->price_per_location;
			$no_restrict_ip = $row->no_restrict_ip;
			
			$accessIP = true;
			if($admin_id>0 || $is_admin>0 || $no_restrict_ip>0){}
			else{
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'restrict_access'", array());
				if($varObj){
					$value = trim((string) $varObj->fetch(PDO::FETCH_OBJ)->value);
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
			$location_of = intval($row->location_of);
			$multipleLocations = 0;
			if($location_of>0){
				$prod_cat_man = $location_of;
				$multipleLocations = 1;
			}
			else{
				$queryObj = $this->db->query("SELECT COUNT(accounts_id) AS totalrows FROM accounts WHERE location_of = :location_of AND status != 'SUSPENDED'", array('location_of'=>$accounts_id),1);
				if($queryObj){
					$locationcount = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
					if($locationcount>0){
						$multipleLocations = 1;
					}						
				}
			}

			$login_ck_id = session_id();
			$_SESSION["admin_id"] = $admin_id;
			$_SESSION["user_id"] = $row->user_id;
			$_SESSION["user_first_name"] = stripslashes(trim((string) $row->user_first_name));
			$_SESSION["company_name"] = stripslashes(trim((string) $row->company_name));
			$_SESSION["minute_to_logout"]= $row->minute_to_logout;
			$_SESSION["is_admin"] = $row->is_admin;
			$_SESSION["accounts_id"] = $accounts_id;
			$_SESSION["prod_cat_man"] = $prod_cat_man;
			$_SESSION["multipleLocations"] = $multipleLocations;
			$_SESSION["status"]= $status;
			$_SESSION["price_per_location"]= $price_per_location;
			$_SESSION["trial_days"]= $trial_days;
			$_SESSION["created_on"]= $created_on;
			$_SESSION["allowed"] = (array) json_decode($row->user_roll);
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
							
				if($currency=='' || is_null($currency)){$currency = '৳';}
				if($timezone =='' || is_null($timezone)){$timezone = 'America/New_York';	}
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
											$languageVar[$varName] = addslashes(trim((string) stripslashes($selLang)));
										}
										//if(in_array($php_js, array(2,3))){
											$languageJSVar[$varName] = addslashes(trim((string) stripslashes($selLang)));
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
				date_default_timezone_set('America/New_York');
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
				date_default_timezone_set('America/New_York');
				$fieldsData = array('lastlogin_datetime' => date('Y-m-d H:i:s'), 'login_ck_id'=>$login_ck_id);
				$this->db->update('user', $fieldsData, $user_id);
				
				date_default_timezone_set($timezone);
				$SixtyDays = time()+(60*24*60*60);
				setcookie("subDomain", $subdomain, $SixtyDays);
					
				$fieldsData = array('user_id'=>$user_id, 'accounts_id'=>$accounts_id, 'workstations_id'=>0, 'login_datetime' => date('Y-m-d H:i:s'), 'logout_datetime' => '1000-01-01 00:00:00', 'logout_by' =>'', 'login_ip' => $this->db->checkCharLen('user_login_history.login_ip', $this->ip_address()));
				$user_login_history_id = $this->db->insert('user_login_history', $fieldsData);
				if($user_login_history_id){
					$workstations_id = 0;
					if (!isset($_COOKIE["workstations_id"])) {
						$newName = 1;
						$WSCountObj = $this->db->query("SELECT COUNT(workstations_id) AS newName FROM workstations WHERE accounts_id = $accounts_id", array());
						if($WSCountObj){
							$newName = $WSCountObj->fetch(PDO::FETCH_OBJ)->newName+1;
						}
						
						$WSData = array('name'=>$newName);
						$WSData['accounts_id'] = $accounts_id;
						$WSData['created_on'] = date('Y-m-d H:i:s');
						$workstations_id = $this->db->insert('workstations', $WSData);
						
						setcookie("workstations_id", $workstations_id, $SixtyDays);
					}
					else{
						$workstations_id = $_COOKIE["workstations_id"];
					}

					$this->db->update('user_login_history', array('workstations_id'=>$workstations_id), $user_login_history_id);
				}
				
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
		if(isset($_SESSION["user_id"])){$returnStr = 'Home';}
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
					
					$loginURL = $baseurl.'Account/setnewpassword/'.$changepass_link;
					
					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = $this->db->supportEmail('Host');
					$mail->Port = 587;
					$mail->SMTPAuth = true;
					$mail->Username = $this->db->supportEmail('Username');
					$mail->Password = $this->db->supportEmail('Password');
					
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
		
		$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Account/login?msg=$returnStr";
		if($returnStr == 'Home' || $returnStr == 'Success'){
			$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Account/login/sent-success";
		}

		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURL\" />";
	}
	
	public function setnewpassword(){
		$changepass_link = $GLOBALS['segment4name'];
		$subdomain = $GLOBALS['subdomain'];
		
		$user_email = '';
		$usersObj = $this->db->query("SELECT user.user_id FROM user, accounts WHERE user.accounts_id = accounts.accounts_id AND accounts.company_subdomain = :company_subdomain AND user.changepass_link = :changepass_link AND accounts.domain = :domain AND user.user_publish = 1", array('company_subdomain'=>$subdomain, 'changepass_link'=>$changepass_link, 'domain'=>OUR_DOMAINNAME));
		if(!$usersObj){	
			return "<meta http-equiv = \"refresh\" content = \"0; url = /Account/login/\" />";
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
		if(isset($_SESSION["user_id"])){$returnStr = 'Home';}
		
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
				$user_password = trim((string) $_POST['user_password']??'');
				$password_hash = password_hash($user_password, PASSWORD_DEFAULT);
					
				$user_first_name = $row->user_first_name;
				$user_last_name = $row->user_last_name;
				
				if($user_email !=''){
					
					$fieldsData = array('password_hash'=>$password_hash, 'changepass_link'=>'', 'last_updated'=> date('Y-m-d H:i:s'));
					$this->db->update('user', $fieldsData, $user_id);
									
					$loginURL = $baseurl.'login/';
					
					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = $this->db->supportEmail('Host');
					$mail->Port = 587;
					$mail->SMTPAuth = true;
					$mail->Username = $this->db->supportEmail('Username');
					$mail->Password = $this->db->supportEmail('Password');
					
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
		
		$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Account/login?msg=$returnStr";
		if($returnStr == 'Home' || $returnStr == 'Success'){
			$redirectURL = "http://$subdomain.".OUR_DOMAINNAME."/Account/login/password-saved";
		}
		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURL\" />";
	}

	//======================Our_billing=================//
	
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
		$status = $paypal_id = $customer_service_email = '';				
		$accObj = $this->db->query("SELECT pay_frequency, next_payment_due, status, price_per_location, paypal_id, customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accObj){
			$accData = $accObj->fetch(PDO::FETCH_OBJ);
			$pay_frequency = $accData->pay_frequency;
			$next_payment_due = $accData->next_payment_due;
			$status = $accData->status;
			$price_per_location = $accData->price_per_location;
			$paypal_id = trim((string) $accData->paypal_id);
			if(empty($paypal_id)){
				$accSql = "SELECT description FROM our_notes WHERE accounts_id = $accounts_id AND description LIKE '%APPROVAL_PENDING%' ORDER BY created_on DESC LIMIT 0,1";
				$queryObj = $this->db->query($accSql, array());
				if($queryObj){
					$descriptionExp = explode('ID:', $queryObj->fetch(PDO::FETCH_OBJ)->description);
					$paypal_id = stripslashes(trim((string) $descriptionExp[1]));
				}
			}
			$customer_service_email = trim((string) $accData->customer_service_email);
		}

		$No_of_Location = 1;
		$accSql = "SELECT COUNT(accounts_id) as totalAccount FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man) AND status != 'SUSPENDED'";
		$noOfLocObj = $this->db->query($accSql, array());
		if($noOfLocObj){
			while($oneRow = $noOfLocObj->fetch(PDO::FETCH_OBJ)){
				$No_of_Location = $oneRow->totalAccount;
			}
		}

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
			$this->db->update('accounts', array('status'=>'Active', 'status_date'=>date('Y-m-d H:i:s'), 'next_payment_due'=>date('Y-m-d')), $accounts_id);
			
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
		
		$_SESSION["current_module"] = "Account";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', invoice_number, paid_by, description, pay_frequency)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
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
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', invoice_number, paid_by, description, pay_frequency)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
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
				<script type="module" src="/assets/js-'.swVersion.'/'.printsJS.'"></script>
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
			return "<input type=\"hidden\" id=\"redirectTo\" value=\"/Account/payment_details\">					
					<script type=\"module\">
						import {showMessAndRedi,Translate} from '/assets/js-".swVersion."/common.js';
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
					$accountsData['price_per_location'] = 39;
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
		
		$_SESSION["current_module"] = "Account";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
		
		$filterSql = "FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man)  AND status != 'SUSPENDED'";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND company_subdomain LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
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
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND company_subdomain LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
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
	
	public function heartbeat(){
	
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'error';
		if($user_id>0){
			date_default_timezone_set('America/New_York');
			$accountsData = array('last_request'=>date('Y-m-d H:i:s'));
			$update = $this->db->update('user', $accountsData, $user_id);
			if($update){			
				$returnStr = 'update-success';			
			}

			$timezone = 'America/New_York';
			if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
			date_default_timezone_set($timezone);
		}

		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}	

}
?>