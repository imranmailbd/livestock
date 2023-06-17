<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Instancehome{
	protected $db;
	private int $page, $limit, $totalRows, $prod_cat_man, $accounts_id, $display_add_customer, $display_services, $display_products, $display_inventory, $request_a_quote, $mobile_repair_appointment, $repair_status_online;
	private string $category_id, $company_name, $business_address, $company_phone_no, $customer_service_email, $meta_keywords, $meta_description, $product_type, $manufacturer_id, $keyword_search;
	private array $manOpt, $catOpt;
	
	public function __construct($db){$this->db = $db;}
		
	public function index(){
		$subdomain = $GLOBALS['subdomain'];
		$returnHTML = '';
		$accountsObj = $this->db->query("SELECT accounts_id, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$this->accounts_id = $accounts_id;
			$this->company_name = $accountsData->company_name;
			$this->company_phone_no = $accountsData->company_phone_no;
			$this->customer_service_email = $accountsData->customer_service_email;
			
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			$this->business_address = $business_address;
			
			$meta_keywords = $meta_description = '';
			$display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
			$ihObj = $this->db->query("SELECT meta_keywords, meta_description, display_add_customer, display_services, display_products, display_inventory, request_a_quote, mobile_repair_appointment, repair_status_online FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;				
			}
			
			$this->meta_keywords = $meta_keywords;
			$this->meta_description = $meta_description;
			$this->display_add_customer = $display_add_customer;
			$this->display_services = $display_services;
			$this->display_products = $display_products;
			$this->display_inventory = $display_inventory;
			$this->request_a_quote = $request_a_quote;
			$this->mobile_repair_appointment = $mobile_repair_appointment;
			$this->repair_status_online = $repair_status_online;

			$returnHTML = $this->template($returnHTML);
		}
		else{
			$returnHTML = "<meta http-equiv = \"refresh\" content = \"0; url = http://".OUR_DOMAINNAME."\" />";
		}
		return $returnHTML;
	}
	
	public function AJ_index_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$subdomain = $GLOBALS['subdomain'];
		
		$accountsObj = $this->db->query("SELECT accounts_id, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$jsonResponse['accounts_id'] = $accounts_id;
			$jsonResponse['company_name'] = $accountsData->company_name;
			$jsonResponse['company_phone_no'] = $accountsData->company_phone_no;
			$jsonResponse['customer_service_email'] = $accountsData->customer_service_email;
			
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			
			$mst_one = '';
			$mst_two = '';
			$mst_three = '';
			$mst_four = '';
			
			$bd_one_icon = 'refresh';
			$bd_one_headline = '';
			$bd_one_subheadline = '';
			$bd_one_details = '';
			
			$bd_two_icon = 'cogs';
			$bd_two_headline = '';
			$bd_two_subheadline = '';
			$bd_two_details = '';
			
			$bd_three_icon = 'phone';
			$bd_three_headline = '';
			$bd_three_subheadline = '';
			$bd_three_details = '';
			
			$cellular_services1 = '';
			$cellular_services2 = '';
			$cellular_services3 = '';
			$cellular_services4 = '';
			
			$cellular_services5 = $cellular_services6 = $cellular_services7 = '';										
			$mon_from = $mon_to = $tue_from = $tue_to = $wed_from = $wed_to = $thu_from = $thu_to = '';
			$fri_from = $fri_to = $sat_from = $sat_to = $sun_from = $sun_to = '';
			$map_address = $business_address;

			$ihObj = $this->db->query("SELECT * FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;
				
				$mst_one = $ihData->mst_one;
				$mst_two = $ihData->mst_two;
				$mst_three = $ihData->mst_three;
				$mst_four = $ihData->mst_four;
				
				if($ihData->business_address !=''){
					$map_address = $ihData->business_address;
				}
				$bd_one_icon = $ihData->bd_one_icon;
				$bd_one_headline = $ihData->bd_one_headline;
				$bd_one_subheadline = $ihData->bd_one_subheadline;
				$bd_one_details = $ihData->bd_one_details;
				
				$bd_two_icon = $ihData->bd_two_icon;
				$bd_two_headline = $ihData->bd_two_headline;
				$bd_two_subheadline = $ihData->bd_two_subheadline;
				$bd_two_details = $ihData->bd_two_details;
				
				$bd_three_icon = $ihData->bd_three_icon;
				$bd_three_headline = $ihData->bd_three_headline;
				$bd_three_subheadline = $ihData->bd_three_subheadline;
				$bd_three_details = $ihData->bd_three_details;
				
				$cellular_services1 = $ihData->cellular_services1;
				$cellular_services2 = $ihData->cellular_services2;
				$cellular_services3 = $ihData->cellular_services3;
				$cellular_services4 = $ihData->cellular_services4;
				$cellular_services5 = $ihData->cellular_services5;
				$cellular_services6 = $ihData->cellular_services6;
				$cellular_services7 = $ihData->cellular_services7;
				
				$mon_from = $ihData->mon_from;
				$mon_to = $ihData->mon_to;
				$tue_from = $ihData->tue_from;
				$tue_to = $ihData->tue_to;
				$wed_from = $ihData->wed_from;
				$wed_to = $ihData->wed_to;
				$thu_from = $ihData->thu_from;
				$thu_to = $ihData->thu_to;
				$fri_from = $ihData->fri_from;
				$fri_to = $ihData->fri_to;
				$sat_from = $ihData->sat_from;
				$sat_to = $ihData->sat_to;
				$sun_from = $ihData->sun_from;
				$sun_to = $ihData->sun_to;				
			}
			$jsonResponse['mst_one'] = $mst_one;
			$jsonResponse['mst_two'] = $mst_two;
			$jsonResponse['mst_three'] = $mst_three;
			$jsonResponse['mst_four'] = $mst_four;
			
			$jsonResponse['cellular_services1'] = $cellular_services1;
			$jsonResponse['cellular_services2'] = $cellular_services2;
			$jsonResponse['cellular_services3'] = $cellular_services3;
			$jsonResponse['cellular_services4'] = $cellular_services4;
			$jsonResponse['cellular_services5'] = $cellular_services5;
			$jsonResponse['cellular_services6'] = $cellular_services6;
			$jsonResponse['cellular_services7'] = $cellular_services7;

			$jsonResponse['mon_from'] = $mon_from;
			$jsonResponse['mon_to'] = $mon_to;
			$jsonResponse['tue_from'] = $tue_from;
			$jsonResponse['tue_to'] = $tue_to;
			$jsonResponse['wed_from'] = $wed_from;
			$jsonResponse['wed_to'] = $wed_to;
			$jsonResponse['thu_from'] = $thu_from;
			$jsonResponse['thu_to'] = $thu_to;
			$jsonResponse['fri_from'] = $fri_from;
			$jsonResponse['fri_to'] = $fri_to;
			$jsonResponse['sat_from'] = $sat_from;
			$jsonResponse['sat_to'] = $sat_to;
			$jsonResponse['sun_from'] = $sun_from;
			$jsonResponse['sun_to'] = $sun_to;

			$jsonResponse['business_address'] = $business_address;

			$jsonResponse['bd_one_icon'] = $bd_one_icon;
			$jsonResponse['bd_one_headline'] = $bd_one_headline;
			$jsonResponse['bd_one_subheadline'] = $bd_one_subheadline;
			$jsonResponse['bd_one_details'] = $bd_one_details;

			$jsonResponse['bd_two_icon'] = $bd_two_icon;
			$jsonResponse['bd_two_headline'] = $bd_two_headline;
			$jsonResponse['bd_two_subheadline'] = $bd_two_subheadline;
			$jsonResponse['bd_two_details'] = $bd_two_details;

			$jsonResponse['bd_three_icon'] = $bd_three_icon;
			$jsonResponse['bd_three_headline'] = $bd_three_headline;
			$jsonResponse['bd_three_subheadline'] = $bd_three_subheadline;
			$jsonResponse['bd_three_details'] = $bd_three_details;
			$jsonResponse['business_address'] = $map_address;
			
			$onePicture = '/assets/images/pagebodyseg11.png';
			$alt = '';
			$filePath = "./assets/accounts/a_$accounts_id/pagebodyseg1_";
			$pics = glob($filePath."*.jpg");
			if($pics){		
				$onePicture = '';
				foreach($pics as $onePicture){
					$onePicture = str_replace('./', '/', $onePicture);
					$alt = str_replace("/assets/accounts/a_$accounts_id/", '',$onePicture);
				}											
			}
			$jsonResponse['onePicture'] = $onePicture;
			$jsonResponse['alt'] = $alt;
			
			$bg_color1 = '#8fbf4d';
			$color1 = '#FFFFFF';
			$font_family1 = 'Arial';	
			
			$bg_color2 = '#f4f4f4';
			$color2 = '#333333';
			$font_family2 = 'Arial';	
			
			$bg_color3 = '#FFFFFF';
			$color3 = '#333333';
			$font_family3 = 'Arial';	
			
			$variables_id = 0;
			$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'web_home'", array());
			if($varObj){
				$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
				$variables_id =  $variablesData->variables_id;		 
				$value = $variablesData->value;
				if(!empty($value)){
					$value = unserialize($value);
					extract($value);
				}
			}

			$jsonResponse['bg_color1'] = $bg_color1;
			$jsonResponse['color1'] = $color1;
			$jsonResponse['font_family1'] = $font_family1;

			$jsonResponse['bg_color2'] = $bg_color2;
			$jsonResponse['color2'] = $color2;
			$jsonResponse['font_family2'] = $font_family2;

			$jsonResponse['bg_color3'] = $bg_color3;
			$jsonResponse['color3'] = $color3;
			$jsonResponse['font_family3'] = $font_family3;
		}

		return json_encode($jsonResponse);
	}
		
	public function Contact_Us(){
		$subdomain = $GLOBALS['subdomain'];
		$returnHTML = '';
		$accountsObj = $this->db->query("SELECT accounts_id, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$this->accounts_id = $accounts_id;
			$this->company_name = $accountsData->company_name;
			$this->company_phone_no = $accountsData->company_phone_no;
			$this->customer_service_email = $accountsData->customer_service_email;
			
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			$this->business_address = $business_address;
			
			$meta_keywords = $meta_description = '';
			$display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
			$ihObj = $this->db->query("SELECT meta_keywords, meta_description, display_add_customer, display_services, display_products, display_inventory, request_a_quote, mobile_repair_appointment, repair_status_online FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;				
			}
			
			$this->meta_keywords = $meta_keywords;
			$this->meta_description = $meta_description;
			$this->display_add_customer = $display_add_customer;
			$this->display_services = $display_services;
			$this->display_products = $display_products;
			$this->display_inventory = $display_inventory;
			$this->request_a_quote = $request_a_quote;
			$this->mobile_repair_appointment = $mobile_repair_appointment;
			$this->repair_status_online = $repair_status_online;
			
			$returnHTML .= '<input type="hidden" id="encodeSubdomain" value="'.base64_encode($subdomain).'">';
			$returnHTML = $this->template($returnHTML);
		}
		else{
			$returnHTML = "<meta http-equiv = \"refresh\" content = \"0; url = http://".OUR_DOMAINNAME."\" />";
		}
		return $returnHTML;
	}

	public function AJ_Contact_Us_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$subdomain = $GLOBALS['subdomain'];
		$jsonResponse['encodeSubdomain'] = base64_encode($subdomain);
		$accountsObj = $this->db->query("SELECT accounts_id, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$jsonResponse['company_name'] = $accountsData->company_name;
			$jsonResponse['company_phone_no'] = $accountsData->company_phone_no;
			$jsonResponse['customer_service_email'] = $accountsData->customer_service_email;
			
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			
			$mon_from = $mon_to = $tue_from = $tue_to = $wed_from = $wed_to = $thu_from = $thu_to = '';
			$fri_from = $fri_to = $sat_from = $sat_to = $sun_from = $sun_to = '';
			$map_address = $business_address;

			$ihObj = $this->db->query("SELECT * FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				
				if($ihData->business_address !=''){
					$map_address = $ihData->business_address;
				}
				
				$mon_from = $ihData->mon_from;
				$mon_to = $ihData->mon_to;
				$tue_from = $ihData->tue_from;
				$tue_to = $ihData->tue_to;
				$wed_from = $ihData->wed_from;
				$wed_to = $ihData->wed_to;
				$thu_from = $ihData->thu_from;
				$thu_to = $ihData->thu_to;
				$fri_from = $ihData->fri_from;
				$fri_to = $ihData->fri_to;
				$sat_from = $ihData->sat_from;
				$sat_to = $ihData->sat_to;
				$sun_from = $ihData->sun_from;
				$sun_to = $ihData->sun_to;				
			}
			
			$jsonResponse['mon_from'] = $mon_from;
			$jsonResponse['mon_to'] = $mon_to;
			$jsonResponse['tue_from'] = $tue_from;
			$jsonResponse['tue_to'] = $tue_to;
			$jsonResponse['wed_from'] = $wed_from;
			$jsonResponse['wed_to'] = $wed_to;
			$jsonResponse['thu_from'] = $thu_from;
			$jsonResponse['thu_to'] = $thu_to;
			$jsonResponse['fri_from'] = $fri_from;
			$jsonResponse['fri_to'] = $fri_to;
			$jsonResponse['sat_from'] = $sat_from;
			$jsonResponse['sat_to'] = $sat_to;
			$jsonResponse['sun_from'] = $sun_from;
			$jsonResponse['sun_to'] = $sun_to;

			$jsonResponse['business_address'] = $business_address;
			$jsonResponse['map_address'] = $map_address;
			
		}

		return json_encode($jsonResponse);
	}
	
	public function sendContactUs(){
		$msg = '';
		$POST = json_decode(file_get_contents('php://input'), true);
		if (isset($POST) && array_key_exists('g-recaptcha-response', $POST)) {
			$subdomain = $GLOBALS['subdomain'];
			$name = $POST['name']??'';
			$email = $POST['email']??'';
			$subject = "[New message] From $subdomain.".OUR_DOMAINNAME." Contact Form";
			$message = nl2br(trim((string) $POST['message']??''));
				
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $this->db->supportEmail('Host');
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = $this->db->supportEmail('Username');
			$mail->Password = $this->db->supportEmail('Password');
			
			$company_name = $customer_service_email = '';
			$accountsObj = $this->db->query("SELECT company_name, customer_service_email FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
			if($accountsObj){
				$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
				$company_name = $accountsData->company_name;
				$customer_service_email = $accountsData->customer_service_email;
			}
			
			if(empty($email) || empty($customer_service_email)){
				if(empty($email)){
					$msg = 'Your email address is empty. Please try again with Email.';
				}
				else{
					$msg = 'Company (to) email address is empty. Please try again / contact with company.';
				}
				
				return "<meta http-equiv = \"refresh\" content = \"0; url = /Quote?msg=$msg\" />";
			}

			$mailBody = "<p>
Name : <strong>$name</strong><br>
Email : <a href=\"mailto:$email\" title=\"Click for reply\">$email</a><br>
<br>
Message : $message
</p>";
			$mail->addReplyTo($email, $name);
			$mail->setFrom($this->db->supportEmail('do_not_reply'), $subdomain);
			$mail->clearAddresses();
			$mail->addAddress($customer_service_email, $company_name);
			$mail->Subject = $subject;
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			$mail->Body = $mailBody;
			
			//Send the message, check for errors
			if (!$mail->send()) {
				$singleErrorMessage = $mail->ErrorInfo;
				$msg = $singleErrorMessage.'Your message could not send.<br />Please try again, thank you.';
			}
			else {
				$mail->clearAddresses();
				$mail->addReplyTo($customer_service_email, $company_name);
				$mail->setFrom($this->db->supportEmail('do_not_reply'), $subdomain);
				$mail->clearAddresses();
				$mail->addAddress($email, $name);
				$mail->Body = "<p>
Dear <i><strong>$name</strong></i>,<br />
We have received your request for contact.<br /><br />
You wrote:<br />
$message
</p>
<p>
<br />
Thank you for requesting a quote.
<br />
We will reply as soon as possible.
</p>";
			
				$mail->send();
				
				$msg = 'Your message has been successfully sent.<br />We will be in touch very soon, thank you.';
			}
		}
		return "<meta http-equiv = \"refresh\" content = \"0; url = /Contact-Us?msg=$msg\" />";
	}
	
	public function Customer(){
		$subdomain = $GLOBALS['subdomain'];
		$returnHTML = '';
		$accountsObj = $this->db->query("SELECT accounts_id, location_of, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$this->accounts_id = $accounts_id;
			$this->company_name = $accountsData->company_name;
			$this->company_phone_no = $accountsData->company_phone_no;
			$this->customer_service_email = $accountsData->customer_service_email;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			$this->business_address = $business_address;
			$meta_keywords = $meta_description = '';
			$display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = $enable_widget = 0;
			$ihObj = $this->db->query("SELECT * FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;
				$enable_widget = $ihData->enable_widget;
			}
			
			$this->meta_keywords = $meta_keywords;
			$this->meta_description = $meta_description;
			$this->display_add_customer = $display_add_customer;
			$this->display_services = $display_services;
			$this->display_products = $display_products;
			$this->display_inventory = $display_inventory;
			$this->request_a_quote = $request_a_quote;
			$this->mobile_repair_appointment = $mobile_repair_appointment;
			$this->repair_status_online = $repair_status_online;
			
			$serverName = ($_SERVER['http']?'http://':'http://').$_SERVER['SERVER_NAME'];
			$returnHTML .= "<script type=\"module\" id=\"CSAPI\" class=\"customer\" src=\"$serverName/assets/widget.js?".base64_encode($subdomain)."\"></script>";
			
		}

		$returnHTML = $this->template($returnHTML);
		return $returnHTML;
	}

	public function Services(){
		$subdomain = $GLOBALS['subdomain'];
		$returnHTML = '';
		$accountsObj = $this->db->query("SELECT accounts_id, location_of, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$this->accounts_id = $accounts_id;
			$this->company_name = $accountsData->company_name;
			$this->company_phone_no = $accountsData->company_phone_no;
			$this->customer_service_email = $accountsData->customer_service_email;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}
			$this->prod_cat_man = $prod_cat_man;
			
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			$this->business_address = $business_address;
			
			$meta_keywords = $meta_description = '';
			$display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
			$ihObj = $this->db->query("SELECT meta_keywords, meta_description, display_add_customer, display_services, display_products, display_inventory, request_a_quote, mobile_repair_appointment, repair_status_online FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;				
			}
			
			$this->meta_keywords = $meta_keywords;
			$this->meta_description = $meta_description;
			$this->display_add_customer = $display_add_customer;
			$this->display_services = $display_services;
			$this->display_products = $display_products;
			$this->display_inventory = $display_inventory;
			$this->request_a_quote = $request_a_quote;
			$this->mobile_repair_appointment = $mobile_repair_appointment;
			$this->repair_status_online = $repair_status_online;
			
			$serverName = ($_SERVER['http']?'http://':'http://').$_SERVER['SERVER_NAME'];
			$returnHTML .= "<script type=\"module\" id=\"CSAPI\" class=\"services\" src=\"$serverName/assets/widget.js?".base64_encode($subdomain)."\"></script>";

			$returnHTML = $this->template($returnHTML);
			
		}
		else{
			$returnHTML = "<meta http-equiv = \"refresh\" content = \"0; url = http://".OUR_DOMAINNAME."\" />";
		}
		return $returnHTML;
	}
	
	public function AJ_Services_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$product_id = intval($POST['product_id']??0);
		$segment3name = $POST['segment3name']??'view';

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$subdomain = $GLOBALS['subdomain'];
		
		$accountsObj = $this->db->query("SELECT accounts_id, location_of FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}
		
			$paypal_email = '';
        	$currency_code = 'USD';
			$display_prices = $enable_paypal = 0;
			$ihObj = $this->db->query("SELECT paypal_email, currency_code, display_services_prices, enable_services_paypal FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				$paypal_email = $ihData->paypal_email;
            	$currency_code = $ihData->currency_code;
				$display_prices = $ihData->display_services_prices;
				$enable_paypal = $ihData->enable_services_paypal;
			}
			$jsonResponse['display_prices'] = intval($display_prices);
			$jsonResponse['enable_paypal'] = intval($enable_paypal);
			$jsonResponse['paypal_email'] = $paypal_email;
			$jsonResponse['currency_code'] = $currency_code;

			$title = $this->db->translate('Services');
			$productObj = false;
			if($segment3name=='view' && $product_id>0){
				$sqlPM = "SELECT p.*, manufacturer.name AS manufacture FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = :product_id AND p.accounts_id = $prod_cat_man";
				$productObj = $this->db->query($sqlPM, array('product_id'=>$product_id),1);
				if($productObj){
					$productOneRow = $productObj->fetch(PDO::FETCH_OBJ);
					$product_id = $productOneRow->product_id;
					$manufacture = $productOneRow->manufacture;
					$singleproduct_name = $productOneRow->product_name;
					$title = stripslashes(trim($manufacture.' '.$singleproduct_name.' Details'));
				}
			}
			$jsonResponse['title'] = $title;
			
			if($productObj){
				$product_id = $productOneRow->product_id;
				$sku = $productOneRow->sku;
				$manufacturer_id = $productOneRow->manufacturer_id;
				$manufacture = $productOneRow->manufacture;
				$singleproduct_name = $productOneRow->product_name;
				$title = stripslashes(trim($manufacture.' '.$singleproduct_name));
				$description = nl2br(stripslashes($productOneRow->description));
				$product_name = stripslashes(trim($manufacture.' '.$singleproduct_name));

				$prodImg = $productSrc = '';
				$filePath = "./assets/accounts/a_$accounts_id/prod_$product_id".'_';
				$pics = glob($filePath."*.jpg");
				if($pics){			
					foreach($pics as $onePicture){
						$prodImg = str_replace("./assets/accounts/a_$accounts_id/", '', $onePicture);
						$productSrc = str_replace('./', '/', $onePicture);
					}
				}
				if($productSrc==''){
					$prodImg = 'no-picture';
					$productSrc = '/assets/images/no-picture.png';
				}
				
				$regular_price = 0.00;
				if($display_prices>0){
					$inventoryObj = $this->db->query("SELECT regular_price FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$regular_price = round($inventoryObj->fetch(PDO::FETCH_OBJ)->regular_price,2);
					}
				}

				$jsonResponse['product_name'] = $product_name;
				$jsonResponse['sku'] = $sku;
				$jsonResponse['description'] = $description;
				$jsonResponse['manufacturer_id'] = $manufacturer_id;
				$jsonResponse['prodImg'] = $prodImg;
				$jsonResponse['productSrc'] = $productSrc;
				$jsonResponse['regular_price'] = $regular_price;
			}
		}

		return json_encode($jsonResponse);
	}
	
	public function Product(){
		$subdomain = $GLOBALS['subdomain'];
		$returnHTML = '';
		$accountsObj = $this->db->query("SELECT accounts_id, location_of, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$this->accounts_id = $accounts_id;
			$this->company_name = $accountsData->company_name;
			$this->company_phone_no = $accountsData->company_phone_no;
			$this->customer_service_email = $accountsData->customer_service_email;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}
			$this->prod_cat_man = $prod_cat_man;
			
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			$this->business_address = $business_address;
			
			$meta_keywords = $meta_description = '';
			$display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
			$ihObj = $this->db->query("SELECT meta_keywords, meta_description, display_add_customer, display_services, display_products, display_inventory, request_a_quote, mobile_repair_appointment, repair_status_online FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;				
			}
			
			$this->meta_keywords = $meta_keywords;
			$this->meta_description = $meta_description;
			$this->display_add_customer = $display_add_customer;
			$this->display_services = $display_services;
			$this->display_products = $display_products;
			$this->display_inventory = $display_inventory;
			$this->request_a_quote = $request_a_quote;
			$this->mobile_repair_appointment = $mobile_repair_appointment;
			$this->repair_status_online = $repair_status_online;
			$serverName = ($_SERVER['http']?'http://':'http://').$_SERVER['SERVER_NAME'];
			$returnHTML .= "<script type=\"module\" id=\"CSAPI\" class=\"product\" src=\"$serverName/assets/widget.js?".base64_encode($subdomain)."\"></script>";

			$returnHTML = $this->template($returnHTML);
		}
		else{
			$returnHTML = "<meta http-equiv = \"refresh\" content = \"0; url = http://".OUR_DOMAINNAME."\" />";
		}
		return $returnHTML;
	}
	
	public function AJ_Product_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$product_id = intval($POST['product_id']??0);
		$segment3name = $POST['segment3name']??'view';

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$subdomain = $GLOBALS['subdomain'];
		
		$accountsObj = $this->db->query("SELECT accounts_id, location_of FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}       
		
			$paypal_email = '';
        	$currency_code = 'USD';
			$display_prices = $enable_paypal = 0;
			$ihObj = $this->db->query("SELECT paypal_email, currency_code, display_products_prices, enable_product_paypal FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				$paypal_email = $ihData->paypal_email;
            	$currency_code = $ihData->currency_code;
				$display_prices = $ihData->display_products_prices;
				$enable_paypal = $ihData->enable_product_paypal;
			}
			$jsonResponse['display_prices'] = intval($display_prices);
			$jsonResponse['enable_paypal'] = intval($enable_paypal);
			$jsonResponse['paypal_email'] = $paypal_email;
			$jsonResponse['currency_code'] = $currency_code;

			$title = $this->db->translate('Services');
			$productObj = false;
			if($segment3name=='view' && $product_id>0){
				$sqlPM = "SELECT p.*, manufacturer.name AS manufacture FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = :product_id AND p.accounts_id = $prod_cat_man";
				$productObj = $this->db->query($sqlPM, array('product_id'=>$product_id),1);
				if($productObj){
					$productOneRow = $productObj->fetch(PDO::FETCH_OBJ);
					$product_id = $productOneRow->product_id;
					$manufacture = $productOneRow->manufacture;
					$singleproduct_name = $productOneRow->product_name;
					$title = stripslashes(trim($manufacture.' '.$singleproduct_name.' Details'));
				}
			}
			$jsonResponse['title'] = $title;

			if($productObj){
				$product_id = $productOneRow->product_id;
				$sku = $productOneRow->sku;
				$manufacturer_id = $productOneRow->manufacturer_id;
				$manufacture = $productOneRow->manufacture;
				$singleproduct_name = $productOneRow->product_name;
				$title = stripslashes(trim($manufacture.' '.$singleproduct_name));
				$description = nl2br(stripslashes($productOneRow->description));
				$product_name = stripslashes(trim($manufacture.' '.$singleproduct_name));

				$prodImg = $productSrc = '';
				$filePath = "./assets/accounts/a_$accounts_id/prod_$product_id".'_';
				$pics = glob($filePath."*.jpg");
				if($pics){			
					foreach($pics as $onePicture){
						$prodImg = str_replace("./assets/accounts/a_$accounts_id/", '', $onePicture);
						$productSrc = str_replace('./', '/', $onePicture);
					}
				}
				if($productSrc==''){
					$prodImg = 'no-picture';
					$productSrc = '/assets/images/no-picture.png';
				}
				
				$regular_price = 0.00;
				if($display_prices>0){
					$inventoryObj = $this->db->query("SELECT regular_price FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$regular_price = round($inventoryObj->fetch(PDO::FETCH_OBJ)->regular_price,2);
					}
				}

				$jsonResponse['product_name'] = $product_name;
				$jsonResponse['sku'] = $sku;
				$jsonResponse['description'] = $description;
				$jsonResponse['manufacturer_id'] = $manufacturer_id;
				$jsonResponse['prodImg'] = $prodImg;
				$jsonResponse['productSrc'] = $productSrc;
				$jsonResponse['regular_price'] = $regular_price;
			}
		}

		return json_encode($jsonResponse);
	}
	
	public function Livestock(){
		$subdomain = $GLOBALS['subdomain'];
		$returnHTML = '';
		$accountsObj = $this->db->query("SELECT accounts_id, location_of, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$this->accounts_id = $accounts_id;
			$this->company_name = $accountsData->company_name;
			$this->company_phone_no = $accountsData->company_phone_no;
			$this->customer_service_email = $accountsData->customer_service_email;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}
			$this->prod_cat_man = $prod_cat_man;
			
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			$this->business_address = $business_address;
			
			$meta_keywords = $meta_description = '';
			$display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
			$ihObj = $this->db->query("SELECT meta_keywords, meta_description, display_add_customer, display_services, display_products, display_inventory, request_a_quote, mobile_repair_appointment, repair_status_online FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;				
			}
			
			$this->meta_keywords = $meta_keywords;
			$this->meta_description = $meta_description;
			$this->display_add_customer = $display_add_customer;
			$this->display_services = $display_services;
			$this->display_products = $display_products;
			$this->display_inventory = $display_inventory;
			$this->request_a_quote = $request_a_quote;
			$this->mobile_repair_appointment = $mobile_repair_appointment;
			$this->repair_status_online = $repair_status_online;
			$serverName = ($_SERVER['http']?'http://':'http://').$_SERVER['SERVER_NAME'];
			$returnHTML .= "<script type=\"module\" id=\"CSAPI\" class=\"cellPhones\" src=\"$serverName/assets/widget.js?".base64_encode($subdomain)."\"></script>";

			$returnHTML = $this->template($returnHTML);
		}
		else{
			$returnHTML = "<meta http-equiv = \"refresh\" content = \"0; url = http://".OUR_DOMAINNAME."\" />";
		}
		return $returnHTML;
	}
	
	public function AJ_Livestock_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$product_id = intval($POST['product_id']??0);
		$segment3name = $POST['segment3name']??'view';

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$subdomain = $GLOBALS['subdomain'];
		
		$accountsObj = $this->db->query("SELECT accounts_id, location_of FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}       
		
			$paypal_email = '';
        	$currency_code = 'USD';
			$display_prices = $enable_paypal = 0;
			$ihObj = $this->db->query("SELECT paypal_email, currency_code, display_cell_prices, enable_cell_paypal FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				$paypal_email = $ihData->paypal_email;
            	$currency_code = $ihData->currency_code;
				$display_prices = $ihData->display_cell_prices;
				$enable_paypal = $ihData->enable_cell_paypal;
			}
			$jsonResponse['display_prices'] = intval($display_prices);
			$jsonResponse['enable_paypal'] = intval($enable_paypal);
			$jsonResponse['paypal_email'] = $paypal_email;
			$jsonResponse['currency_code'] = $currency_code;

			$title = $this->db->translate('Services');
			$productObj = false;
			if($segment3name=='view' && $product_id>0){
				$sqlPM = "SELECT p.*, manufacturer.name AS manufacture FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = :product_id AND p.accounts_id = $prod_cat_man";
				$productObj = $this->db->query($sqlPM, array('product_id'=>$product_id),1);
				if($productObj){
					$productOneRow = $productObj->fetch(PDO::FETCH_OBJ);
					$product_id = $productOneRow->product_id;
					$manufacture = $productOneRow->manufacture;
					$singleproduct_name = $productOneRow->product_name;
					$title = stripslashes(trim($manufacture.' '.$singleproduct_name));
					$title .= ' '.stripslashes(trim($productOneRow->colour_name.' '.$productOneRow->storage));                
					$title .= ' Details';
				}
			}
			$jsonResponse['title'] = $title;
						
			if($productObj){
				$product_id = $productOneRow->product_id;
				$sku = $productOneRow->sku;
				$manufacturer_id = $productOneRow->manufacturer_id;
				$manufacture = $productOneRow->manufacture;
				$singleproduct_name = $productOneRow->product_name;
				$title = stripslashes(trim($manufacture.' '.$singleproduct_name));
				$description = nl2br(stripslashes($productOneRow->description));
				$product_name = stripslashes(trim($manufacture.' '.$singleproduct_name));
				$colour_name = $productOneRow->colour_name;		
				$storage = $productOneRow->storage;
				$physical_condition_name = $productOneRow->physical_condition_name;
				
				$prodImg = $productSrc = '';
				$filePath = "./assets/accounts/a_$accounts_id/prod_$product_id".'_';
				$pics = glob($filePath."*.jpg");
				if($pics){			
					foreach($pics as $onePicture){
						$prodImg = str_replace("./assets/accounts/a_$accounts_id/", '', $onePicture);
						$productSrc = str_replace('./', '/', $onePicture);
					}
				}
				if($productSrc==''){
					$prodImg = 'no-picture';
					$productSrc = '/assets/images/no-picture.png';
				}
				
				$regular_price = 0.00;
				if($display_prices>0){
					$inventoryObj = $this->db->query("SELECT regular_price FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$regular_price = round($inventoryObj->fetch(PDO::FETCH_OBJ)->regular_price,2);
					}
				}

				$jsonResponse['product_name'] = $product_name;
				$jsonResponse['sku'] = $sku;
				$jsonResponse['colour_name'] = $colour_name;
				$jsonResponse['storage'] = $storage;
				$jsonResponse['physical_condition_name'] = $physical_condition_name;
				$jsonResponse['description'] = $description;
				$jsonResponse['manufacturer_id'] = $manufacturer_id;
				$jsonResponse['prodImg'] = $prodImg;
				$jsonResponse['productSrc'] = $productSrc;
				$jsonResponse['regular_price'] = $regular_price;
			}
		}

		return json_encode($jsonResponse);
	}
	
	public function AJgetPage(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$segment4name = $GLOBALS['segment4name'];
		$sproduct_type = $POST['sproduct_type']??'Standard';
		$smanufacturer_id = $POST['smanufacturer_id']??'';
		$scategory_id = $POST['scategory_id']??'';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}

		if($sproduct_type=='Labor/Services'){$segment2name = 'Services';}
		elseif($sproduct_type=='Standard'){$segment2name = 'Product';}
		else{$segment2name = 'Livestock';}

		if(in_array($segment2name, array('Services', 'Product'))){
			$limit = intval($POST["limit"]??15);
		}
		else{
			$limit = intval($POST["limit"]??10);
		}
		$this->limit = $limit;

		$accounts_id = $prod_cat_man = 0;
		$subdomain = $GLOBALS['subdomain'];
		$accountsObj = $this->db->query("SELECT accounts_id, location_of FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}
		}
		$this->prod_cat_man = $prod_cat_man;
		$this->accounts_id = $accounts_id;
		$this->product_type = $sproduct_type;
		$this->manufacturer_id = $smanufacturer_id;
		$this->category_id = $scategory_id;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){			
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['manOpt'] = $this->manOpt;
			$jsonResponse['catOpt'] = $this->catOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions(){
		$prod_cat_man = $this->prod_cat_man;
		$accounts_id = $this->accounts_id;
		
		$sproduct_type = $this->product_type;
		$smanufacturer_id = $this->manufacturer_id;
		$scategory_id = $this->category_id;
		$keyword_search = $this->keyword_search;
		
		if($sproduct_type=='Labor/Services'){$segment2name = 'Services';}
		elseif($sproduct_type=='Standard'){$segment2name = 'Product';}
		else{$segment2name = 'Livestock';}
		
		$bindData = array();
		$filterSql = "";
		if($smanufacturer_id !=''){
			$filterSql .= " AND p.manufacturer_id = :manufacturer_id";
			$bindData['manufacturer_id'] = $smanufacturer_id;
		}
		
		if($scategory_id !=''){
			$filterSql .= " AND p.category_id = :category_id";
			$bindData['category_id'] = $scategory_id;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if (strpos($keyword_search, " ") === false) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		if(in_array($segment2name, array('Services', 'Product'))){
			$Sql = "SELECT p.product_id FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_type = '$sproduct_type' AND p.description !='' $filterSql AND p.product_publish = 1";
			$Sql2 = "SELECT p.manufacturer_id, p.category_id FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_type = '$sproduct_type' AND p.description !='' $filterSql AND p.product_publish = 1 GROUP BY p.manufacturer_id, p.category_id";
		}
		else{
			$Sql = "SELECT i.item_id FROM item i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id AND p.product_type = '$sproduct_type' AND p.description !='' AND i.in_inventory = 1 $filterSql AND p.product_publish = 1 AND p.product_id = i.product_id";
			$Sql2 = "SELECT p.manufacturer_id, p.category_id FROM item i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id AND p.product_type = '$sproduct_type' AND p.description !='' AND i.in_inventory = 1 $filterSql AND p.product_publish = 1 AND p.product_id = i.product_id GROUP BY p.manufacturer_id, p.category_id";
		}
		if(!in_array($segment2name, array('Services', 'Product'))){
			$Sql .= " GROUP BY p.product_id";
		}
		
		$totalRows = 0;
		$queryObj = $this->db->query($Sql, $bindData);
		if($queryObj){
			$totalRows = $queryObj->rowCount();						
		}

		$manOpts = $catOpts = array();
		$query = $this->db->querypagination($Sql2, $bindData);
		if($query){
			foreach($query as $oneRow){
				$catOpts[$oneRow['category_id']] = '';
				$manOpts[$oneRow['manufacturer_id']] = '';
			}
		}
		
		$manOpt = array();
		if(count($manOpts)>0){			
			if(array_key_exists('0', $manOpts)){$manOpt[0] = '';}
			$manStr = "SELECT manufacturer_id, name FROM manufacturer WHERE manufacturer_id IN (".implode(', ', array_keys($manOpts)).") AND accounts_id = $prod_cat_man ORDER BY name ASC";
			$manObj = $this->db->query($manStr, array());
			if($manObj){
				while($oneRow=$manObj->fetch(PDO::FETCH_OBJ)){
					$manOpt[$oneRow->manufacturer_id] = stripslashes(trim((string) $oneRow->name));
				}
			}
		}

		$catOpt = array();
		if(count($catOpts)>0){
			if(array_key_exists('0', $catOpts)){$catOpt[0] = '';}
			$catStr = "SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($catOpts)).") AND accounts_id = $prod_cat_man ORDER BY category_name ASC";
			$catQuery = $this->db->query($catStr, array());
			if($catQuery){
				while($oneRow=$catQuery->fetch(PDO::FETCH_OBJ)){
					$selected = '';
					if($oneRow->category_id==$scategory_id){$selected = ' selected="selected"';}
					$catOpt[$oneRow->category_id] = stripslashes(trim((string) $oneRow->category_name));
				}
			}
		}
		
		$this->totalRows = $totalRows;
		$this->manOpt = $manOpt;
		$this->catOpt = $catOpt;
	}
	
    private function loadTableRows(){
		$segment2name = $GLOBALS['segment2name'];
		$prod_cat_man = $this->prod_cat_man;
		$accounts_id = $this->accounts_id;
		
		$limit = $this->limit;		
		if($limit<=0){$limit = 10;}
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$sproduct_type = $this->product_type;
		$smanufacturer_id = $this->manufacturer_id;
		$scategory_id = $this->category_id;
		$keyword_search = $this->keyword_search;
		
		if($sproduct_type=='Labor/Services'){$segment2name = 'Services';}
		elseif($sproduct_type=='Standard'){$segment2name = 'Product';}
		else{$segment2name = 'Livestock';}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		if(in_array($sproduct_type, array('Labor/Services', 'Standard'))){
			$filterSql = "SELECT p.product_id, p.manufacturer_id, manufacturer.name AS manufacture, p.product_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_type = '$sproduct_type' AND p.description !='' AND p.product_publish = 1";
		}
		else{
			$filterSql = "SELECT p.product_id, p.manufacturer_id, manufacturer.name AS manufacture, p.product_name AS product_name, p.colour_name, p.storage, p.physical_condition_name FROM item i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id AND p.product_type = '$sproduct_type' AND p.description !='' AND p.product_id = i.product_id AND i.in_inventory = 1 AND p.product_publish = 1";
		}
		
		$bindData = array();
		if($smanufacturer_id !=''){
			$filterSql .= " AND p.manufacturer_id = :manufacturer_id";
			$bindData['manufacturer_id'] = $smanufacturer_id;
		}
		if($scategory_id !=''){
			$filterSql .= " AND p.category_id = :category_id";
			$bindData['category_id'] = $scategory_id;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if (strpos($keyword_search, " ") === false) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		if(!in_array($sproduct_type, array('Labor/Services', 'Standard'))){
			$filterSql .= " GROUP BY p.product_id";
		}

		$sqlquery = "$filterSql ORDER BY TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name)) ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){
				$product_id = $onerow['product_id'];
				$manufacturer_id = $onerow['manufacturer_id'];
				$manufacture = $onerow['manufacture'];
				$singleproduct_name = $onerow['product_name'];
				$product_name = stripslashes(trim($manufacture.' '.$singleproduct_name));

				if(strlen($product_name)>30){
					$product_name = substr($product_name, 0, 30).'...';
				}
				
				$prodImg = '';
				$filePath = "./assets/accounts/a_$accounts_id/prod_$product_id".'_';
				$pics = glob($filePath."*.jpg");
				if($pics){
					foreach($pics as $onePicture){
						$prodImg = str_replace("./assets/accounts/a_$accounts_id/", '', $onePicture);
						$productSrc = str_replace('./', '/', $onePicture);
					}
				}
				
				if($prodImg==''){
					$prodImg = 'no-picture';
					$productSrc = '/assets/images/no-picture.png';
				}
				$colour_nameArray = $storageArray = $phyConArray = array();
				$storage ='';
				if(!in_array($sproduct_type, array('Labor/Services', 'Standard'))){
					$colour_name = $onerow['colour_name'];
					if($colour_name !=''){
						if(!in_array($colour_name, $colour_nameArray)){
							$colour_nameArray[] = $colour_name;
						}
					}
					
					$storage = $onerow['storage'];
					if($storage !=''){
						if(!in_array($storage, $storageArray)){
							$storageArray[] = $storage;
						}
					}
					
					if(!empty($onerow['physical_condition_name'])){
						$phyConArray[$onerow['physical_condition_name']] = '';
					}
				}

				$tabledata[] = array($product_id, $segment2name, $page, $product_name, $sproduct_type, $prodImg, $productSrc, $storage, $colour_nameArray, $phyConArray);				
			}
		}

		return $tabledata;
    }
		
	public function Quote(){
		$subdomain = $GLOBALS['subdomain'];
		$returnHTML = '';
		$accountsObj = $this->db->query("SELECT accounts_id, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$this->accounts_id = $accounts_id;
			$this->company_name = $accountsData->company_name;
			$this->company_phone_no = $accountsData->company_phone_no;
			$this->customer_service_email = $accountsData->customer_service_email;
			
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			$this->business_address = $business_address;
			
			$meta_keywords = $meta_description = '';
			$display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
			$ihObj = $this->db->query("SELECT meta_keywords, meta_description, display_add_customer, display_services, display_products, display_inventory, request_a_quote, mobile_repair_appointment, repair_status_online FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;				
			}
			
			$this->meta_keywords = $meta_keywords;
			$this->meta_description = $meta_description;
			$this->display_add_customer = $display_add_customer;
			$this->display_services = $display_services;
			$this->display_products = $display_products;
			$this->display_inventory = $display_inventory;
			$this->request_a_quote = $request_a_quote;
			$this->mobile_repair_appointment = $mobile_repair_appointment;
			$this->repair_status_online = $repair_status_online;
			
			$serverName = ($_SERVER['http']?'http://':'http://').$_SERVER['SERVER_NAME'];
			$returnHTML .= "<script type=\"module\" id=\"CSAPI\" class=\"quote\" src=\"$serverName/assets/widget.js?".base64_encode($subdomain)."\"></script>";

			$returnHTML = $this->template($returnHTML);
		}
		else{
			$returnHTML = "<meta http-equiv = \"refresh\" content = \"0; url = http://".OUR_DOMAINNAME."\" />";
		}
		return $returnHTML;
	}

	public function AJ_Quote_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$subdomain = $GLOBALS['subdomain'];
		
		$accountsObj = $this->db->query("SELECT accounts_id FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$bg_color = '#ffffff';
			$color = '#363947';
			$font_family = 'Arial';	
			$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'Quote'", array());
			if($varObj){
				$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
				$value = $variablesData->value;
				if(!empty($value)){
					$value = unserialize($value);
					extract($value);
				}
			}
			
			$jsonResponse['bg_color'] = $bg_color;
			$jsonResponse['color'] = $color;
			$jsonResponse['font_family'] = $font_family;			
		}

		return json_encode($jsonResponse);
	}
	
	public function sendQuote(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$msg = '';
		$subdomain = $GLOBALS['subdomain']??'';
		$name = $POST['name']??'';
		$phone = $POST['phone']??'';
		$email = $POST['email']??'';
		$bamod = $POST['bamod']??'';
		$message = nl2br(trim((string) $POST['message']??''));
		$subject = '[New message] From '.$subdomain.'.'.OUR_DOMAINNAME.' Quote Form';
		
		$company_name = $customer_service_email = '';
		$accountsObj = $this->db->query("SELECT company_name, customer_service_email FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$company_name = $accountsData->company_name;
			$customer_service_email = $accountsData->customer_service_email;
		}
		if(empty($email) || empty($customer_service_email)){
			if(empty($email)){
				$msg = 'Your email address is empty. Please try again with Email.';
			}
			else{
				$msg = 'Company (to) email address is empty. Please try again / contact with company.';
			}
			
			return "<meta http-equiv = \"refresh\" content = \"0; url = /Quote?msg=$msg\" />";
		}
		
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host = $this->db->supportEmail('Host');
		$mail->Port = 587;
		$mail->SMTPAuth = true;
		$mail->Username = $this->db->supportEmail('Username');
		$mail->Password = $this->db->supportEmail('Password');
		
		$mail->addReplyTo($email, $name);
		$mail->setFrom($this->db->supportEmail('do_not_reply'), $subdomain);
		$mail->clearAddresses();
		$mail->addAddress($customer_service_email, $company_name);
		$mail->Subject = $subject;	    
		$mail->isHTML(true);
		$mail->CharSet = 'UTF-8';
		$mail->Body = "<p>
Name : <strong>$name</strong><br>
Phone Number : $phone<br>
Email : <a href=\"mailto:$email\" title=\"Click for reply\">$email</a><br>
".$this->db->translate('Brand and model of device')." : $bamod<br>
<br>
Problem : $message
</p>";
		//Send the message, check for errors
		if (!$mail->send()) {
			$msg = 'Your Quote could not send.<br />Please try again, thank you.';
		}
		else {
			$mail->clearAddresses();
			$mail->addReplyTo($customer_service_email, $company_name);
			$mail->setFrom($this->db->supportEmail('do_not_reply'), $subdomain);
			$mail->clearAddresses();
			$mail->addAddress($email, $name);
			$mail->Body = "<p>
Dear <strong><i>$name</i></strong>,<br />
We have received your request for a quote. <br /><br />
You wrote:<br />
$message
</p>
<p>
<br />
Thank you for requesting a quote.
<br />
We will reply as soon as possible.
</p>";
				
			$mail->send();
			
			$msg = 'Your Quote has been successfully sent.<br />We will be in touch very soon, thank you.';
		}

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['msg'] = $msg;

		return json_encode($jsonResponse);
	}
	
	public function Appointment(){
		$subdomain = $GLOBALS['subdomain'];
		$returnHTML = '';
		$accountsObj = $this->db->query("SELECT accounts_id, location_of, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$this->accounts_id = $accounts_id;
			$this->company_name = $accountsData->company_name;
			$this->company_phone_no = $accountsData->company_phone_no;
			$this->customer_service_email = $accountsData->customer_service_email;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			$this->business_address = $business_address;
			$meta_keywords = $meta_description = '';
			$display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
			$ihObj = $this->db->query("SELECT * FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;				
			}
			
			$this->meta_keywords = $meta_keywords;
			$this->meta_description = $meta_description;
			$this->display_add_customer = $display_add_customer;
			$this->display_services = $display_services;
			$this->display_products = $display_products;
			$this->display_inventory = $display_inventory;
			$this->request_a_quote = $request_a_quote;
			$this->mobile_repair_appointment = $mobile_repair_appointment;
			$this->repair_status_online = $repair_status_online;
			
			$serverName = ($_SERVER['http']?'http://':'http://').$_SERVER['SERVER_NAME'];
			$returnHTML .= "<script id=\"CSAPI\" class=\"appointment\" src=\"$serverName/assets/widget.js?".base64_encode($subdomain)."\"></script>";			
		}
		
		$returnHTML = $this->template($returnHTML);
		return $returnHTML;
	}
	
	public function sendAppointment(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$msg = '';
		if (isset($POST) && array_key_exists('mathCaptcha', $POST)) {
			$subdomain = $GLOBALS['subdomain']??'';
			$name = $POST['name']??'';
			$phone = $POST['phone']??'';
			$email = $POST['email']??'';
			$bamod = $POST['bamod']??'';
			$wntbr = $POST['wntbr']??'';
			$location = nl2br(trim((string) $POST['location']??''));
			$dattm = $POST['dattm']??'';
			
			$subject = '[New message] From '.$subdomain.'.'.OUR_DOMAINNAME.' Repair Appointment Form';
				
			$company_name = $customer_service_email = '';
			$accountsObj = $this->db->query("SELECT company_name, customer_service_email FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
			if($accountsObj){
				$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
				$company_name = $accountsData->company_name;
				$customer_service_email = $accountsData->customer_service_email;
			}

			if(empty($email) || empty($customer_service_email)){
				if(empty($email)){
					$msg = 'Your email address is empty. Please try again with Email.';
				}
				else{
					$msg = 'Company (to) email address is empty. Please try again / contact with company.';
				}
				
				return "<meta http-equiv = \"refresh\" content = \"0; url = /Quote?msg=$msg\" />";
			}

			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $this->db->supportEmail('Host');
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = $this->db->supportEmail('Username');
			$mail->Password = $this->db->supportEmail('Password');
			
			$mail->addReplyTo($email, $name);
			$mail->setFrom($this->db->supportEmail('do_not_reply'), $subdomain);
			$mail->clearAddresses();
			$mail->addAddress($customer_service_email, $company_name);
			$mail->Subject = $subject;	    
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			$mail->Body = "<p>
Name : <strong>$name</strong><br>
Phone : $phone<br>
Email : <a href=\"mailto:$email\" title=\"Click for reply\">$email</a><br>
".$this->db->translate('Brand and model of device')." : $bamod<br>
What needs to be Finished : $wntbr<br>
<br>
Location where to meet : $location<br>
<br>
Date and time to meet : $dattm
</p>";
			//Send the message, check for errors
			if (!$mail->send()) {
				$msg = 'Your Appointment could not send.<br />Please try again, thank you.';
			} else {
				$mail->clearAddresses();
				$mail->addReplyTo($customer_service_email, $company_name);
				$mail->setFrom($this->db->supportEmail('do_not_reply'), $subdomain);
				$mail->clearAddresses();
				$mail->addAddress($email, $name);
				$mail->Body = "<p>
Dear <i>$name</i>,<br />
We have received your request for Repair Appointment. <br /><br />
You wrote your location:<br />
$location<br />
<br />
</p>
<p>
Thank you for requesting an appointment.
<br />
We will reply as soon as possible.
</p>";
				
				$mail->send();
				
				$msg = 'Your Appointment has been successfully sent.<br />We will be in touch very soon, thank you.';
			}
		}
		return "<meta http-equiv = \"refresh\" content = \"0; url = /Appointment?msg=$msg\" />";
	}
	
	public function Check_Repair_Status(){
		$subdomain = $GLOBALS['subdomain'];
		$returnHTML = '';
		$accountsObj = $this->db->query("SELECT accounts_id, location_of, company_name, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
		if($accountsObj){
			$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsData->accounts_id;
			$this->accounts_id = $accounts_id;
			$this->company_name = $accountsData->company_name;
			$this->company_phone_no = $accountsData->company_phone_no;
			$this->customer_service_email = $accountsData->customer_service_email;
			$prod_cat_man = $accounts_id;
			if($accountsData->location_of>0){
				$prod_cat_man = $accountsData->location_of;
			}
			$business_address = $accountsData->company_street_address;
			if($accountsData->company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_city;
			}
			if($accountsData->company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_state_name;
			}
			if($accountsData->company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $accountsData->company_zip;
			}
			if($accountsData->company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $accountsData->company_country_name;
			}
			$this->business_address = $business_address;
			$meta_keywords = $meta_description = '';
			$display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
			$ihObj = $this->db->query("SELECT * FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$ihData = $ihObj->fetch(PDO::FETCH_OBJ);				
				$meta_keywords = $ihData->meta_keywords;
				$meta_description = $ihData->meta_description;
				$display_add_customer = $ihData->display_add_customer;
				$display_services = $ihData->display_services;
				$display_products = $ihData->display_products;
				$display_inventory = $ihData->display_inventory;
				$request_a_quote = $ihData->request_a_quote;
				$mobile_repair_appointment = $ihData->mobile_repair_appointment;
				$repair_status_online = $ihData->repair_status_online;				
			}
			
			$this->meta_keywords = $meta_keywords;
			$this->meta_description = $meta_description;
			$this->display_add_customer = $display_add_customer;
			$this->display_services = $display_services;
			$this->display_products = $display_products;
			$this->display_inventory = $display_inventory;
			$this->request_a_quote = $request_a_quote;
			$this->mobile_repair_appointment = $mobile_repair_appointment;
			$this->repair_status_online = $repair_status_online;
			
			$serverName = ($_SERVER['http']?'http://':'http://').$_SERVER['SERVER_NAME'];
			$returnHTML .= "<script id=\"CSAPI\" class=\"repair_status\" src=\"$serverName/assets/widget.js?".base64_encode($subdomain)."\"></script>";
		}
		$returnHTML = $this->template($returnHTML);
		return $returnHTML;
	}
	
	public function template($bodyHTML){
		$segment2 = $GLOBALS['segment2'];
		$segment2name = $GLOBALS['segment2name'];
		$accounts_id = $this->accounts_id;
		$company_name = $this->company_name;
		
		$Common = new Common($this->db);
		$web_logourl = '';
		$filePath = "./assets/accounts/a_$accounts_id/web_logo_";
		$pics = glob($filePath."*.jpg");
		if($pics){		
			foreach($pics as $onePicture){
				$web_logourl = str_replace('./', '/', $onePicture);
			}				
		}
		$language = 'English';
		$currency = '৳';
		$timezone = 'America/New_York';
		$vData = $Common->variablesData('account_setup', $accounts_id);
		if(!empty($vData)){
			extract($vData);
		}
		
		if($currency =='' || is_null($currency)){$currency = '৳';}
		if($timezone =='' || is_null($timezone)){$timezone = 'America/New_York';}
		if($language=='' || is_null($language)){$language = 'English';}
		date_default_timezone_set($timezone);

		$_SESSION["language"]= $language;
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
									if(in_array($php_js, array(2,3))){
										$languageJSVar[$varName] = addslashes(trim((string) stripslashes($selLang)));
									}
								}
							}
						}
					}
				}
			}
			if(!empty($languageVar)){
				$_SESSION["languageVar"] = $languageVar;
			}
		}
		
		$title = $GLOBALS['title'];
		if(in_array($segment2, array('', 'index'))){$title = $company_name;}
		$htmlStr = "<!DOCTYPE html>
		<html>
		<head>
			<meta charset=\"utf-8\">
			<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
			<title>$title</title>    
			<meta name=\"description\" content=\"$this->meta_description\" />
			<meta name=\"keywords\" content=\"$this->meta_keywords\" />
			<link rel=\"stylesheet\" href=\"/assets/css-".swVersion."/instancehome.css\" />
			<link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/assets/images/favicon-32x32.png\">
			<script language=\"JavaScript\" type=\"text/javascript\">
				var currency = '$currency';
				var loadLangFile = '$language';
				var langModifiedData = {};
				//var languageData = {};
				function stripslashes(str) {
					str = str.replace(/\\'/g, '\'');
					str = str.replace(/\\\"/g, '\"');
					str = str.replace(/\\0/g, '\0');
					str = str.replace(/\\\\/g, '');
					return str;
				}
			</script>";
			$htmlStr .= "<script src=\"/assets/js-".swVersion."/languages/$language.js\"></script>";
			
			$htmlStr .= "<script type=\"text/javascript\" src=\"/assets/js-".swVersion."/Instancehome.js\"></script>";

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
			
		$htmlStr .= '</head>';
		
		$web_header = "header{width: 100%;height: auto;padding: 20px 0;background: #FFF;}
		.menu .nav > li > a{font-size:14px; color:#363947; font-weight:600; line-height:20px;font-family:Arial;}
		.menu .nav > li > a:hover, .current a{color:#8fbf4d;}
		";
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'web_header'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				
				$web_header = "header {width: 100%;height: auto;padding: 20px 0;background:$value[bg_color];}
		.logo a{color:$value[color];}
		.menu .nav > li > a{font-size:14px; color:$value[color]; font-weight:600; line-height:20px;font-family:'$value[font_family]';}
		.menu .nav > li > a:hover, .current a{ color:$value[color];}
		";
			}
		}

		$web_footer = "footer{width:100%; float:left; height:auto; background:#363947; padding-bottom:25px;}
		footer .description, footer p{font-size: 15px;line-height: 17px;padding-left: 51px;padding-top: 30px;text-transform: uppercase;color:#8f9caa;font-family:'Arial';margin-bottom:0;}
		footer a, footer #name_address{color:#8f9caa;}
		";
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'web_footer'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				
				$web_footer = "footer{width:100%; float:left; height:auto; background:$value[bg_color]; padding-bottom:25px;}
		footer .description, footer p{font-size: 15px;line-height: 17px;padding-left: 51px;padding-top: 30px;text-transform: uppercase;color:$value[color];font-family:'$value[font_family]';margin-bottom:0;}
		footer a, footer #name_address{color:$value[color];}
		";
			}
		}
		$htmlStr .= "<style type=\"text/css\">
			html, body {height:100%;overflow:inherit;}
			#wrapper {min-height:100%;}
			* html #wrapper {height:100%;}
			.form_box{margin-top:40px}
			$web_header
			$web_footer
		</style>
		<body>
		<div id=\"wrapper\" class=\"pleft0\">
			<header id=\"topheaderbar\">
				<div class=\"container\">					
					<div class=\"flexStartRow\">
						<div class=\"columnXS12 columnSM3\">
							<div class=\"logo\">";
								
								if($web_logourl !=''){
									$htmlStr .= "<a href=\"/\" title=\"$company_name\">
										<img class=\"img-responsive maxhight80\" src=\"$web_logourl\" alt=\"$company_name\" />
									</a>";
								}
								else{
									$htmlStr .= "<a href=\"/\" title=\"$company_name\"><h1>$company_name</h1></a>";
								}
								
							$htmlStr .= "</div>
							<button type=\"button\" class=\"navbar-toggle collapsed\" data-toggle=\"collapse\" data-target=\"#bs-example-navbar-collapse-1\" aria-expanded=\"false\" style=\"position: absolute;top: 1.333rem;right: 1rem;\">
								<span class=\"sr-only\">Toggle navigation</span>
								<span class=\"icon-bar\"></span>
								<span class=\"icon-bar\"></span>
								<span class=\"icon-bar\"></span>
							</button>
						</div>						
						<div class=\"columnXS12 columnSM9\">
							<nav class=\"navbar navbar-default menu\">
								<div class=\"container-fluid\">		
									<!-- Collect the nav links, forms, and other content for toggling -->
									<div class=\"collapse navbar-collapse\" id=\"bs-example-navbar-collapse-1\">
										<ul class=\"flexEndRow nav navbar-nav\">
											<li>";
											$curCls1 = $curCls2 = '';
											if($segment2name==''){$curCls1 = ' class="current"';}
											if($segment2name=='Contact-Us'){$curCls2 = ' class="current"';}
											
											$htmlStr .= "</li>
											<li$curCls1><a href=\"/\" title=\"".$this->db->translate('Home')."\">".$this->db->translate('Home')."</a></li>
											<li$curCls2><a href=\"/Contact-Us\" title=\"".$this->db->translate('Contact Us')."\">".$this->db->translate('Contact Us')."</a></li>";
											
											if($this->display_add_customer>0){
												$curCls = '';
												if($segment2name=='Customer'){$curCls = ' class="current"';}
												$htmlStr .= "<li$curCls><a href=\"/Customer\" title=\"".$this->db->translate('Customer')."\">".$this->db->translate('Customer')."</a></li>";
											}
											if($this->display_services>0){
												$curCls = '';
												if($segment2=='Services'){$curCls = ' class="current"';}
												$htmlStr .= "<li$curCls><a href=\"/Services\" title=\"".$this->db->translate('Services')."\">".$this->db->translate('Services')."</a></li>";
											}
											if($this->display_products>0){
												$curCls = '';
												if($segment2name=='Product'){$curCls = ' class="current"';}
												$htmlStr .= "<li$curCls><a href=\"/Product\" title=\"".$this->db->translate('Product')."\">".$this->db->translate('Product')."</a></li>";
											}
											if($this->display_inventory>0){
												$curCls = '';
												if($segment2name=='Livestock'){$curCls = ' class="current"';}
												$htmlStr .= "<li$curCls><a href=\"/Livestock\" title=\"".$this->db->translate('Live Stocks')."\">".$this->db->translate('Live Stocks')."</a></li>";
											}
											
											if($this->request_a_quote>0 || $this->mobile_repair_appointment>0 || $this->repair_status_online>0){
												$curCls = '';
												if(in_array($segment2name, array('Quote', 'Appointment', 'Check_Repair_Status'))){$curCls = ' class="current"';}
												$htmlStr .= "<li$curCls>
													<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\" title=\"".$this->db->translate('Repairs')."\">".$this->db->translate('Repairs')." <span class=\"caret\"></span></a>
													 <ul class=\"dropdown-menu\" style=\"display:none\">";
														if($this->request_a_quote>0){
															$htmlStr .= "<li><a href=\"/Quote\" title=\"".$this->db->translate('Request a Quote')."\">".$this->db->translate('Request a Quote')."</a></li>";
														}
														if($this->mobile_repair_appointment>0){
															$htmlStr .= "<li><a href=\"/Appointment\" title=\"".$this->db->translate('Repair Appointment')."\">".$this->db->translate('Repair Appointment')."</a></li>";														   
														}
														if($this->repair_status_online>0){
															$htmlStr .= "<li><a href=\"/Check_Repair_Status\" title=\"".$this->db->translate('Check Repair Status')."\">".$this->db->translate('Check Repair Status')."</a></li>";
														}
													$htmlStr .= "</ul>
												</li>";
											}
										$htmlStr .= "</ul>
									 </div><!-- /.navbar-collapse -->
								</div><!-- /.container-fluid -->
							</nav>
						</div>
					</div>
				</div>
			</header>
			$bodyHTML
			<div id=\"viewPageInfo\" class=\"pleft0 pright0\"></div>
		</div>
		<div class=\"flexColumn footer_scroll\">
			<footer id=\"scroll_mar_bot\">
				<div class=\"container\">
					<div class=\"flexSpaBetRow\">
						<div class=\"columnMD5\">
							<div id=\"name_address\" class=\"description\" style=\"display: flex; align-items: center; line-height: 17px; text-transform: uppercase;\">
								<i class=\"fa fa-map-marker\" style=\"width: 50px; font-size: 42px; line-height: 50px;\"></i>
								<div>
									$this->company_name<br>
									$this->business_address
								</div>
							</div>
						</div>
						<div class=\"columnMD5\">
							<div class=\"description\" style=\"display: flex; align-items: center; line-height: 17px; text-transform: uppercase;\">
								<i class=\"fa fa-mobile\" style=\"width: 50px; font-size: 42px; line-height: 50px;\"></i>
								<div>
									".$this->db->translate('Telephone').": <a href=\"tel:$this->company_phone_no\" title=\"$this->company_phone_no\">$this->company_phone_no</a>
									<br />
									".$this->db->translate('E-mail').": <a href=\"mailto:$this->customer_service_email\" title=\"$this->customer_service_email\">$this->customer_service_email</a>
								</div>
							</div>
						</div>
						<div class=\"columnMD2\">
							<div class=\"description\" style=\"line-height: 17px; text-transform: uppercase; padding-left: 51px;\" id=\"copyright\"><span class=\"txt_600\">$this->company_name</span><br />© <span class=\"policy\">".date('Y')."</span></p>
						</div>
					</div>
				</div>    
			</footer>
			<footer class=\"Powered\">
				<div class=\"container\">
					<div class=\"flexStartRow\">
						<div class=\"columnSM4\" align=\"left\">
							<p><a style=\"color:white;font-weight:600\" href=\"/Account/login\">Staff Login</a></p>
						</div>
						<div class=\"columnSM8\" align=\"right\">
							<p>Powered by <a style=\"color:green;font-weight:600\" href=\"//".OUR_DOMAINNAME."\" title=\"".OUR_DOMAINNAME."\">".OUR_DOMAINNAME."</a></p>
						</div>
					</div>
				</div>
			</footer>
		</div>
		<div id=\"dialog-confirm\" title=\"Information\"  style=\"display:none;width:100%;\"></div>
		</body>
		</html>";

		return $htmlStr;
	}	
}	
?>