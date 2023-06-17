<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Signup{
	
	protected $db;
	public function __construct($db){$this->db = $db;}
		
	public function index(){
		$comCouNamOpt = "<option value=\"\">Country</option>";
		$countryData = $this->countryData();
		if(isset($countryData) && is_array($countryData)){                                    
			foreach($countryData as $country_name){
				$comCouNamOpt .= "<option value=\"$country_name\">$country_name</option>";
			}
		}
		$htmlStr = "";
		if(!in_array(OUR_DOMAINNAME, array('skitsbd.com', 'machouse.com.bd'))){
			$htmlStr .= '<html lang="en">
<head>
<meta charset="utf-8">
<title>'. $GLOBALS['title'].'</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="You own a cell phone repair or retail store.  You\'re looking for an affordable POS and repair tracking software to help you grow. CellStore Software can help.">
</head>
<body>';
		}
		$htmlStr .= "<link href=\"/assets/css-".swVersion."/signup.css\" rel=\"stylesheet\" type=\"text/css\">
		<div id=\"viewPageInfo\"></div><script type=\"text/javascript\" src=\"/assets/js-".swVersion."/Signup.js\"></script>";
		
		if(!in_array(OUR_DOMAINNAME, array('skitsbd.com', 'machouse.com.bd'))){
			$htmlStr .= '<!--    footer end here-->
			<!--    script start here-->
			<script src="/website_assets/js/jquery.min.js"></script>
			<script src="/website_assets/js/bootstrap.min.js"></script>
			<script>
				$(document).ready(function(){
					// Add minus icon for collapse element which is open by default
					$(".collapse.show").each(function(){
						$(this).prev(".card-header").find(".fa").addClass("fa-minus").removeClass("fa-plus");
					});
					
					// Toggle plus minus icon on show hide of collapse element
					$(".collapse").on("show.bs.collapse", function(){
						$(this).prev(".card-header").find(".fa").removeClass("fa-plus").addClass("fa-minus");
					}).on("hide.bs.collapse", function(){
						$(this).prev(".card-header").find(".fa").removeClass("fa-minus").addClass("fa-plus");
					});
				});
			</script>
			<!--    script start here-->
			</body>
			</html>';
		}
		
		return $htmlStr;
	}

	public function AJ_index_MoreInfo(){
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

	public function check(){
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
		$tax_inclusive = $POST['tax_inclusive']??0;
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
										'price_per_location' => '29.99',
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
										'status' => 'Active', 
										'status_date'=>date('Y-m-d H:i:s'),
										'trial_days' => 14,
										'coupon_code' => $coupon_code,
										'paypal_id'=>'',
										'location_of'=>0,
										'default_customer'=>0,
										'timeclock_enabled'=>0,
										'petty_cash'=>0,
										'last_login'=>'1000-01-01 00:00:00',
										'notes'=>'',
										'check_ave_cost'=>'1000-01-01'
										);
				//print_r($conditionarray);
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
						$mail->Host = "smtp.".OUR_DOMAINNAME;
						$mail->Port = 587;
						
						$mail->addReplyTo($this->db->supportEmail('support'), COMPANYNAME);
						$mail->setFrom($this->db->supportEmail('support'), COMPANYNAME.' Software');
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

}
?>