<?php
class Website{
	
	protected $db;
	private int $page, $totalRows, $product_id;
	private string $pageTitle, $physical_condition_name, $carrier_name, $colour_name, $keyword_search, $proNamOpt, $carNamOpt, $colNamOpt, $phyConNamOpt;
	
	public function __construct($db){$this->db = $db;}
	
	//=======================lists=========================//
	public function lists(){}

	public function AJ_lists_MoreInfo(){
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$instance_home_id = 0;
		$website_on = $display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
		$enable_widget = 0;
		$ihSql = "SELECT instance_home_id, website_on, enable_widget, display_add_customer, display_services, display_products, display_inventory, request_a_quote, mobile_repair_appointment, repair_status_online FROM instance_home WHERE accounts_id = $accounts_id";
		$ihObj = $this->db->query($ihSql, array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);
			$instance_home_id = $ihrow->instance_home_id;
			$website_on = $ihrow->website_on;
			$enable_widget = $ihrow->enable_widget;
			$display_add_customer = $ihrow->display_add_customer;
			$display_services = $ihrow->display_services;
			$display_products = $ihrow->display_products;
			$display_inventory = $ihrow->display_inventory;
			$request_a_quote = $ihrow->request_a_quote;
			$mobile_repair_appointment = $ihrow->mobile_repair_appointment;
			$repair_status_online = $ihrow->repair_status_online;
		}
		$subdomain = $GLOBALS['subdomain'];
		$embedSubDomain = base64_encode($subdomain);
		$jsonResponse['embedSubDomain'] = $embedSubDomain;
		$jsonResponse['subdomain'] = $subdomain;
		$jsonResponse['OUR_DOMAINNAME'] = OUR_DOMAINNAME;
		$jsonResponse['website_on'] = intval($website_on);
		$jsonResponse['enable_widget'] = intval($enable_widget);
		$jsonResponse['display_add_customer'] = intval($display_add_customer);
		$jsonResponse['display_services'] = intval($display_services);
		$jsonResponse['display_products'] = intval($display_products);
		$jsonResponse['display_inventory'] = intval($display_inventory);
		$jsonResponse['request_a_quote'] = intval($request_a_quote);
		$jsonResponse['mobile_repair_appointment'] = intval($mobile_repair_appointment);
		$jsonResponse['repair_status_online'] = intval($repair_status_online);
		if($instance_home_id==0){
			$iHData = array('accounts_id'=>$accounts_id,
						'created_on'=>date('Y-m-d H:i:s'),
						'last_updated'=>date('Y-m-d H:i:s'),
						'website_on'=>$website_on,
						'enable_widget'=>0,
						'display_add_customer'=>0,
						'display_inventory'=>0,
						'display_cell_prices'=>0,
						'enable_cell_paypal'=>0,
						'display_products'=>0,
						'display_products_prices'=>0,
						'enable_product_paypal'=>0,
						'display_services'=>0,
						'display_services_prices'=>0,
						'enable_services_paypal'=>0,
						'paypal_email'=>'',
						'currency_code'=>'',
						'request_a_quote'=>0,
						'mobile_repair_appointment'=>0,
						'repair_status_online'=>0,
						'mst_one'=>$this->db->checkCharLen('instance_home.mst_one', $this->db->translate('Smart Phone')),
						'mst_two'=>$this->db->checkCharLen('instance_home.mst_two', $this->db->translate('Sales Ampar')),
						'mst_three'=>$this->db->checkCharLen('instance_home.mst_three', $this->db->translate('Repairs')),
						'mst_four'=>$this->db->checkCharLen('instance_home.mst_four', $this->db->translate('AT COMPETITIVE PRICES')),
						'business_address'=>'',
						'bd_one_icon'=>'',
						'bd_one_headline'=>$this->db->checkCharLen('instance_home.bd_one_headline', $this->db->translate('REPAIR SERVICES')),
						'bd_one_subheadline'=>$this->db->checkCharLen('instance_home.bd_one_subheadline', $this->db->translate('CELL PHONE REPAIR')),
						'bd_one_details'=>$this->db->checkCharLen('instance_home.bd_one_details', $this->db->translate('Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!')),
						'bd_two_icon'=>'',
						'bd_two_headline'=>$this->db->checkCharLen('instance_home.bd_two_headline', $this->db->translate('OUR SERVICES')),
						'bd_two_subheadline'=>$this->db->checkCharLen('instance_home.bd_two_subheadline', $this->db->translate('FULL SERVICE')),
						'bd_two_details'=>$this->db->checkCharLen('instance_home.bd_two_details', $this->db->translate('Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.')),
						'bd_three_icon'=>'',
						'bd_three_headline'=>$this->db->checkCharLen('instance_home.bd_three_headline', $this->db->translate('OUR SUPPORT')),
						'bd_three_subheadline'=>$this->db->checkCharLen('instance_home.bd_three_subheadline', $this->db->translate('HERE FOR YOU')),
						'bd_three_details'=>$this->db->checkCharLen('instance_home.bd_three_details', $this->db->translate('We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.')),
						'cellular_services1'=>'',
						'cellular_services2'=>'',
						'cellular_services3'=>'',
						'cellular_services4'=>'',
						'cellular_services5'=>'',
						'cellular_services6'=>'',
						'cellular_services7'=>'',
						'mon_from'=>'',
						'mon_to'=>'',
						'tue_from'=>'',
						'tue_to'=>'',
						'wed_from'=>'',
						'wed_to'=>'',
						'thu_from'=>'',
						'thu_to'=>'',
						'fri_from'=>'',
						'fri_to'=>'',
						'sat_from'=>'',
						'sat_to'=>'',
						'sun_from'=>'',
						'sun_to'=>'',
						'meta_keywords'=>'',
						'meta_description'=>''
						);
			
			$instance_home_id = $this->db->insert('instance_home', $iHData);
			
		}
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		
		return json_encode($jsonResponse);
	}	
	
   public function update_instance_home(){
		$accounts_id = $_SESSION['accounts_id']??0;	
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 0;
		$instance_home_id = intval($POST['instance_home_id']??0);
		$ihObj = $this->db->query("SELECT instance_home_id FROM instance_home WHERE accounts_id = $accounts_id", array());
		if($ihObj){
			$instance_home_id = $ihObj->fetch(PDO::FETCH_OBJ)->instance_home_id;
		}
		$fieldname = $POST['fieldname']??'';
		$fieldval = $POST['fieldval']??'';
		if( $instance_home_id>0 && $fieldname !=''){
			$fieldsArray = array('paypal_email','currency_code','mst_one','mst_two','mst_three','mst_four',
							'business_address','bd_one_icon','bd_one_headline','bd_one_subheadline',
							'bd_two_icon','bd_two_headline','bd_two_subheadline','bd_three_icon','bd_three_headline',
							'bd_three_subheadline','cellular_services1','cellular_services2','cellular_services3',
							'cellular_services4','cellular_services5','cellular_services6','cellular_services7',
							'mon_from','mon_to','tue_from','tue_to','wed_from','wed_to','thu_from','thu_to',
							'fri_from','fri_to','sat_from','sat_to','sun_from','sun_to','meta_keywords','meta_description', 'enable_widget');
			if(in_array($fieldname, $fieldsArray)){
				$fieldval = $this->db->checkCharLen("instance_home.$fieldname", $fieldval);
			}
			$update = $this->db->update('instance_home', array($fieldname=>$fieldval, 'last_updated'=>date('Y-m-d H:i:s')), $instance_home_id);
			if($update){$returnStr = 1;}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
  	}
	
	//=======================all_pages_header=========================//
	public function all_pages_header(){}

   public function AJ_all_pages_header_MoreInfo(){
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$instance_home_id = $display_add_customer = $display_services = $display_products = $display_inventory = $request_a_quote = $mobile_repair_appointment = $repair_status_online = 0;
		
		$meta_keywords = $meta_description = '';
		$ihObj = $this->db->query("SELECT instance_home_id, meta_keywords, meta_description, display_add_customer, display_services, display_products, display_inventory, request_a_quote, mobile_repair_appointment, repair_status_online FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);
			$instance_home_id = $ihrow->instance_home_id;
			$meta_keywords = $ihrow->meta_keywords;
			$meta_description = $ihrow->meta_description;
			$display_add_customer = $ihrow->display_add_customer;
			$display_services = $ihrow->display_services;
			$display_products = $ihrow->display_products;
			$display_inventory = $ihrow->display_inventory;
			$request_a_quote = $ihrow->request_a_quote;
			$mobile_repair_appointment = $ihrow->mobile_repair_appointment;
			$repair_status_online = $ihrow->repair_status_online;
		}

		$web_logo = '';
		$bg_color = '#ffffff';
		$color = '#363947';
		$font_family = 'Arial';	
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'web_header'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;		 
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}

		$onePicture = $alt = '';
		$filePath = "./assets/accounts/a_$accounts_id/web_logo_";
		$pics = glob($filePath."*.jpg");
		if($pics){
			foreach($pics as $onePicture){
				$onePicture = str_replace('./', '/', $onePicture);
				$alt = str_replace("/assets/accounts/a_$accounts_id/", '',$onePicture);
			}			
		}
		else{
			$web_logo = '';
		}
		
		$jsonResponse['variables_id'] = intval($variables_id);
		$jsonResponse['meta_keywords'] = $meta_keywords;
		$jsonResponse['meta_description'] = $meta_description;
		$jsonResponse['web_logo'] = $web_logo;
		$jsonResponse['bg_color'] = $bg_color;
		$jsonResponse['color'] = $color;
		$jsonResponse['font_family'] = $font_family;
		$jsonResponse['onePicture'] = $onePicture;
		$jsonResponse['alt'] = $alt;
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		$jsonResponse['display_add_customer'] = intval($display_add_customer);
		$jsonResponse['display_services'] = intval($display_services);
		$jsonResponse['display_products'] = intval($display_products);
		$jsonResponse['display_inventory'] = intval($display_inventory);
		$jsonResponse['request_a_quote'] = intval($request_a_quote);
		$jsonResponse['mobile_repair_appointment'] = intval($mobile_repair_appointment);
		$jsonResponse['repair_status_online'] = intval($repair_status_online);
		$jsonResponse['company_name'] = $_SESSION["company_name"]??'';

		return json_encode($jsonResponse);
	}
    
   public function AJsave_all_pages_header(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		$returnStr = '';
   
		$accounts_id = $_SESSION["accounts_id"]??0;
		$meta_keywords = $this->db->checkCharLen('instance_home.meta_keywords', $POST['meta_keywords']??'');
		$meta_description = $this->db->checkCharLen('instance_home.meta_description',  $POST['meta_description']??'');
		$variables_id = 0;
		$web_logo = $POST['web_logo']??'';
		$bg_color = $POST['bg_color']??'#ffffff';
		$color = $POST['color']??'#363947';
		$font_family = $POST['font_family']??'Arial';
		
		$ihObj = $this->db->query("SELECT instance_home_id FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$instance_home_id = $ihObj->fetch(PDO::FETCH_OBJ)->instance_home_id;
			$this->db->update('instance_home', array('meta_keywords'=>$meta_keywords, 'meta_description'=>$meta_description), $instance_home_id);
		}

		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='web_header'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}

		$value = serialize(array('web_logo'=>$web_logo, 'bg_color'=>$bg_color, 'color'=>$color, 'font_family'=>$font_family));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'web_header'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'Add';
			}
			else{
				$returnStr = 'errorOnAdding';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'Update';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
	}

	//=======================home_page_body=========================//
	public function home_page_body(){}
	
	public function AJ_home_page_body_MoreInfo(){
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$instance_home_id = 0;
		$mst_one = '';
		$mst_two = '';
		$mst_three = '';
		$mst_four = '';
		$company_name = $_SESSION["company_name"]??'';
		
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

		$cellular_services1 = '';
		$cellular_services2 = '';
		$cellular_services3 = '';
		$cellular_services4 = '';
		$cellular_services5 = $cellular_services6 = $cellular_services7 = '';

		$mon_from = $mon_to = $tue_from = $tue_to = $wed_from = $wed_to = $thu_from = $thu_to = $fri_from = $fri_to = $sat_from = $sat_to = $sun_from = $sun_to = $business_address = '';

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

		$ihObj = $this->db->query("SELECT * FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);			
			$instance_home_id = $ihrow->instance_home_id;
			$mst_one = $ihrow->mst_one;
			$mst_two = $ihrow->mst_two;
			$mst_three = $ihrow->mst_three;
			$mst_four = $ihrow->mst_four;

			$cellular_services1 = $ihrow->cellular_services1;
			$cellular_services2 = $ihrow->cellular_services2;
			$cellular_services3 = $ihrow->cellular_services3;
			$cellular_services4 = $ihrow->cellular_services4;
			$cellular_services5 = $ihrow->cellular_services5;
			$cellular_services6 = $ihrow->cellular_services6;
			$cellular_services7 = $ihrow->cellular_services7;

			$mon_from = $ihrow->mon_from;
			$mon_to = $ihrow->mon_to;
			$tue_from = $ihrow->tue_from;
			$tue_to = $ihrow->tue_to;
			$wed_from = $ihrow->wed_from;
			$wed_to = $ihrow->wed_to;
			$thu_from = $ihrow->thu_from;
			$thu_to = $ihrow->thu_to;
			$fri_from = $ihrow->fri_from;
			$fri_to = $ihrow->fri_to;
			$sat_from = $ihrow->sat_from;
			$sat_to = $ihrow->sat_to;
			$sun_from = $ihrow->sun_from;
			$sun_to = $ihrow->sun_to;

			$business_address = $ihrow->business_address;

			if(!empty($ihrow->bd_one_icon)){$bd_one_icon = $ihrow->bd_one_icon;}
			$bd_one_headline = $ihrow->bd_one_headline;
			$bd_one_subheadline = $ihrow->bd_one_subheadline;
			$bd_one_details = $ihrow->bd_one_details;

			if(!empty($ihrow->bd_two_icon)){$bd_two_icon = $ihrow->bd_two_icon;}
			$bd_two_headline = $ihrow->bd_two_headline;
			$bd_two_subheadline = $ihrow->bd_two_subheadline;
			$bd_two_details = $ihrow->bd_two_details;

			if(!empty($ihrow->bd_three_icon)){$bd_three_icon = $ihrow->bd_three_icon;}
			$bd_three_headline = $ihrow->bd_three_headline;
			$bd_three_subheadline = $ihrow->bd_three_subheadline;
			$bd_three_details = $ihrow->bd_three_details;
		}
		
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		$jsonResponse['mst_one'] = $mst_one;
		$jsonResponse['mst_two'] = $mst_two;
		$jsonResponse['mst_three'] = $mst_three;
		$jsonResponse['mst_four'] = $mst_four;
		$jsonResponse['onePicture'] = $onePicture;
		
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
		$jsonResponse['variables_id'] = intval($variables_id);
		$jsonResponse['bg_color1'] = $bg_color1;
		$jsonResponse['color1'] = $color1;
		$jsonResponse['font_family1'] = $font_family1;

		$jsonResponse['bg_color2'] = $bg_color2;
		$jsonResponse['color2'] = $color2;
		$jsonResponse['font_family2'] = $font_family2;

		$jsonResponse['bg_color3'] = $bg_color3;
		$jsonResponse['color3'] = $color3;
		$jsonResponse['font_family3'] = $font_family3;
		
		return json_encode($jsonResponse);
	}	
    
   public function showInstanceHomeForm(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$timeformat = $_SESSION["timeformat"]??'24 hour';
		$fromSegment = $POST['fromSegment']??'';
		
		$mst_one = '';
		$mst_two = '';
		$mst_three = '';
		$mst_four = '';

		$business_address = $cellular_services1 = $cellular_services2 = $cellular_services3 = $cellular_services4 = $cellular_services5 = $cellular_services6 = $cellular_services7 = '';
		$mon_from = $mon_to = $tue_from = $tue_to = $wed_from = $wed_to = $thu_from = $thu_to = $fri_from = $fri_to = $sat_from = $sat_to = $sun_from = $sun_to = '';
		$idData = array();
		$idData['instance_home_id'] = $instance_home_id = 0;
		if($fromSegment=='homePreview311'){
			$idData['bd_one_icon'] = 'refresh';
		}
		elseif($fromSegment=='homePreview312'){
			$idData['bd_one_headline'] = '';
			$idData['bd_one_subheadline'] = '';
			$idData['bd_one_details'] = '';
		}
		elseif($fromSegment=='homePreview321'){
			$idData['bd_two_icon'] = 'cogs';
		}
		elseif($fromSegment=='homePreview322'){
			$idData['bd_two_headline'] = '';
			$idData['bd_two_subheadline'] = '';
			$idData['bd_two_details'] = '';
		}
		elseif($fromSegment=='homePreview331'){
			$idData['bd_three_icon'] = 'phone';
		}
		elseif($fromSegment=='homePreview332'){
			$idData['bd_three_headline'] = '';
			$idData['bd_three_subheadline'] = '';
			$idData['bd_three_details'] = '';
		}

		$ihObj = $this->db->query("SELECT * FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);

			foreach($idData as $index=>$value){
				$idData[$index] = $ihrow->$index;
			}

			$instance_home_id = $ihrow->instance_home_id;
			$mst_one = $ihrow->mst_one;
			$mst_two = $ihrow->mst_two;
			$mst_three = $ihrow->mst_three;
			$mst_four = $ihrow->mst_four;

			$business_address = $ihrow->business_address;

			$cellular_services1 = $ihrow->cellular_services1;
			$cellular_services2 = $ihrow->cellular_services2;
			$cellular_services3 = $ihrow->cellular_services3;
			$cellular_services4 = $ihrow->cellular_services4;
			$cellular_services5 = $ihrow->cellular_services5;
			$cellular_services6 = $ihrow->cellular_services6;
			$cellular_services7 = $ihrow->cellular_services7;

			$mon_from = $ihrow->mon_from;
			$mon_to = $ihrow->mon_to;
			$tue_from = $ihrow->tue_from;
			$tue_to = $ihrow->tue_to;
			$wed_from = $ihrow->wed_from;
			$wed_to = $ihrow->wed_to;
			$thu_from = $ihrow->thu_from;
			$thu_to = $ihrow->thu_to;
			$fri_from = $ihrow->fri_from;
			$fri_to = $ihrow->fri_to;
			$sat_from = $ihrow->sat_from;
			$sat_to = $ihrow->sat_to;
			$sun_from = $ihrow->sun_from;
			$sun_to = $ihrow->sun_to;
			$meta_keywords = $ihrow->meta_keywords;
			$meta_description = $ihrow->meta_description;
		}

		if(in_array($fromSegment, array('homePreview311', 'homePreview321', 'homePreview331'))){
			$faIcons = array('refresh', 'cogs', 'phone', 'bell-o', 'bullhorn', 'calendar-plus-o', 'diamond', 'cubes', 'credit-card', 'envelope', 'check-square', 'group', 'hand-peace-o');
		}
		$formData = array();
		$formData['instance_home_id'] = $instance_home_id;

		if($fromSegment=='homePreview1'){
			$formData['mst_one'] = $mst_one;
			$formData['mst_two'] = $mst_two;
			$formData['mst_three'] = $mst_three;
			$formData['mst_four'] = $mst_four;
			
		}
		elseif($fromSegment=='homePreview21'){
			$formData['cellular_services1'] = $cellular_services1;
			$formData['cellular_services2'] = $cellular_services2;
			$formData['cellular_services3'] = $cellular_services3;
			$formData['cellular_services4'] = $cellular_services4;
			$formData['cellular_services5'] = $cellular_services5;
			$formData['cellular_services6'] = $cellular_services6;
			$formData['cellular_services7'] = $cellular_services7;
		}
		elseif($fromSegment=='homePreview22'){
			$hours_array[] = '';
			for($l=0; $l<24; $l++){
				if($timeformat=='24 hour'){
					$hours_array[] = date("H:i", strtotime("$l:00"));
					$hours_array[] = date("H:i", strtotime("$l:30"));
				}
				else{
					$hours_array[] = date("g a", strtotime("$l:00"));
					$hours_array[] = date("g:i a", strtotime("$l:30"));
				}
			}
			$hours_array[] = 'Closed';
			
			$formData['hoursOpt'] = $hours_array;
			$formData['mon_from'] = $mon_from;
			$formData['mon_to'] = $mon_to;
			$formData['tue_from'] = $tue_from;
			$formData['tue_to'] = $tue_to;
			$formData['wed_from'] = $wed_from;
			$formData['wed_to'] = $wed_to;
			$formData['thu_from'] = $thu_from;
			$formData['thu_to'] = $thu_to;
			$formData['fri_from'] = $fri_from;
			$formData['fri_to'] = $fri_to;
			$formData['sat_from'] = $sat_from;
			$formData['sat_to'] = $sat_to;
			$formData['sun_from'] = $sun_from;
			$formData['sun_to'] = $sun_to;
		}
		elseif($fromSegment=='homePreview23'){
			$formData['business_address'] = $business_address;
		}
		elseif(in_array($fromSegment, array('homePreview311', 'homePreview321', 'homePreview331'))){
			if(strcmp($fromSegment,'homePreview311')==0){$fieldName = 'bd_one_icon';}
			elseif(strcmp($fromSegment,'homePreview321')==0){$fieldName = 'bd_two_icon';}
			else{$fieldName = 'bd_three_icon';}

			$formData[$fieldName] = $idData[$fieldName];
			$formData['faIcons'] = $faIcons;
		}
		elseif($fromSegment=='homePreview312'){
			$formData['bd_one_headline'] = $idData['bd_one_headline'];
			$formData['bd_one_subheadline'] = $idData['bd_one_subheadline'];
			$formData['bd_one_details'] = $idData['bd_one_details'];
		}
		elseif($fromSegment=='homePreview322'){
			$formData['bd_two_headline'] = $idData['bd_two_headline'];
			$formData['bd_two_subheadline'] = $idData['bd_two_subheadline'];
			$formData['bd_two_details'] = $idData['bd_two_details'];
		}
		elseif($fromSegment=='homePreview332'){
			$formData['bd_three_headline'] = $idData['bd_three_headline'];
			$formData['bd_three_subheadline'] = $idData['bd_three_subheadline'];
			$formData['bd_three_details'] = $idData['bd_three_details'];
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'formData'=>$formData));
    }

    public function updateInstanceHome(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$fromSegment = $POST['fromSegment']??'';
		$instance_home_id = intval($POST['instance_home_id']??0);
		if($instance_home_id==0){
			$ihObj = $this->db->query("SELECT instance_home_id FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$instance_home_id = $ihObj->fetch(PDO::FETCH_OBJ)->instance_home_id;
			}
		}
		if($instance_home_id>0){
			$iHData = array();
		}
		else{
			$iHData = array('accounts_id'=>$accounts_id,
						'created_on'=>date('Y-m-d H:i:s'),
						'last_updated'=>date('Y-m-d H:i:s'),
						'website_on'=>$website_on,
						'enable_widget'=>0,
						'display_add_customer'=>0,
						'display_inventory'=>0,
						'display_cell_prices'=>0,
						'enable_cell_paypal'=>0,
						'display_products'=>0,
						'display_products_prices'=>0,
						'enable_product_paypal'=>0,
						'display_services'=>0,
						'display_services_prices'=>0,
						'enable_services_paypal'=>0,
						'paypal_email'=>'',
						'currency_code'=>'',
						'request_a_quote'=>0,
						'mobile_repair_appointment'=>0,
						'repair_status_online'=>0,
						'mst_one'=>$_Smart_Phone,
						'mst_two'=>$_Sales_Ampar,
						'mst_three'=>$_Repairs,
						'mst_four'=>$_At_Competitive_Prices,
						'business_address'=>'',
						'bd_one_icon'=>'',
						'bd_one_headline'=>$_Repair_Services,
						'bd_one_subheadline'=>$_Cell_Phone_Repair,
						'bd_one_details'=>$_Repair_services_trust,
						'bd_two_icon'=>'',
						'bd_two_headline'=>$_Our_Services,
						'bd_two_subheadline'=>$_Full_Service,
						'bd_two_details'=>$_Count_reliable_quality,
						'bd_three_icon'=>'',
						'bd_three_headline'=>$_Our_Support,
						'bd_three_subheadline'=>$_Here_For_You,
						'bd_three_details'=>$_We_assist_you,
						'cellular_services1'=>'',
						'cellular_services2'=>'',
						'cellular_services3'=>'',
						'cellular_services4'=>'',
						'cellular_services5'=>'',
						'cellular_services6'=>'',
						'cellular_services7'=>'',
						'mon_from'=>'',
						'mon_to'=>'',
						'tue_from'=>'',
						'tue_to'=>'',
						'wed_from'=>'',
						'wed_to'=>'',
						'thu_from'=>'',
						'thu_to'=>'',
						'fri_from'=>'',
						'fri_to'=>'',
						'sat_from'=>'',
						'sat_to'=>'',
						'sun_from'=>'',
						'sun_to'=>'',
						'meta_keywords'=>'',
						'meta_description'=>''
						);
		}		
		
		if($fromSegment=='homePreview1'){
			$iHData['mst_one'] = $this->db->checkCharLen('instance_home.mst_one', $POST['mst_one']??'');
			$iHData['mst_two'] = $this->db->checkCharLen('instance_home.mst_two', $POST['mst_two']??'');
			$iHData['mst_three'] = $this->db->checkCharLen('instance_home.mst_three', $POST['mst_three']??'');
			$iHData['mst_four'] = $this->db->checkCharLen('instance_home.mst_four', $POST['mst_four']??'');
		}
		elseif($fromSegment=='homePreview21'){
			$iHData['cellular_services1'] = $this->db->checkCharLen('instance_home.cellular_services1', $POST['cellular_services1']??'');
			$iHData['cellular_services2'] = $this->db->checkCharLen('instance_home.cellular_services2', $POST['cellular_services2']??'');
			$iHData['cellular_services3'] = $this->db->checkCharLen('instance_home.cellular_services3', $POST['cellular_services3']??'');
			$iHData['cellular_services4'] = $this->db->checkCharLen('instance_home.cellular_services4', $POST['cellular_services4']??'');
			$iHData['cellular_services5'] = $this->db->checkCharLen('instance_home.cellular_services5', $POST['cellular_services5']??'');
			$iHData['cellular_services6'] = $this->db->checkCharLen('instance_home.cellular_services6', $POST['cellular_services6']??'');
			$iHData['cellular_services7'] = $this->db->checkCharLen('instance_home.cellular_services7', $POST['cellular_services7']??'');
		}
		elseif($fromSegment=='homePreview22'){

			$iHData['mon_from'] = $this->db->checkCharLen('instance_home.mon_from', $POST['mon_from']??'');
			$iHData['mon_to'] = $this->db->checkCharLen('instance_home.mon_to', $POST['mon_to']??'');
			$iHData['tue_from'] = $this->db->checkCharLen('instance_home.tue_from', $POST['tue_from']??'');
			$iHData['tue_to'] = $this->db->checkCharLen('instance_home.tue_to', $POST['tue_to']??'');
			$iHData['wed_from'] = $this->db->checkCharLen('instance_home.wed_from', $POST['wed_from']??'');
			$iHData['wed_to'] = $this->db->checkCharLen('instance_home.wed_to', $POST['wed_to']??'');
			$iHData['thu_from'] = $this->db->checkCharLen('instance_home.thu_from', $POST['thu_from']??'');
			$iHData['thu_to'] = $this->db->checkCharLen('instance_home.thu_to', $POST['thu_to']??'');
			$iHData['fri_from'] = $this->db->checkCharLen('instance_home.fri_from', $POST['fri_from']??'');
			$iHData['fri_to'] = $this->db->checkCharLen('instance_home.fri_to', $POST['fri_to']??'');
			$iHData['sat_from'] = $this->db->checkCharLen('instance_home.sat_from', $POST['sat_from']??'');
			$iHData['sat_to'] = $this->db->checkCharLen('instance_home.sat_to', $POST['sat_to']??'');
			$iHData['sun_from'] = $this->db->checkCharLen('instance_home.sun_from', $POST['sun_from']??'');
			$iHData['sun_to'] = $this->db->checkCharLen('instance_home.sun_to', $POST['sun_to']??'');
		}
		elseif($fromSegment=='homePreview23'){
			$iHData['business_address'] = $business_address = $POST['business_address']??'';
			if($business_address ==''){
				$iHData['website_on'] = 0;
			}
			else{
				$iHData['website_on'] = 1;
			}
		}
		elseif($fromSegment=='homePreview311'){
			$iHData['bd_one_icon'] = $POST['bd_one_icon']??'';
		}
		elseif($fromSegment=='homePreview312'){
			$iHData['bd_one_headline'] = $this->db->checkCharLen('instance_home.bd_one_headline', $POST['bd_one_headline']??'');
			$iHData['bd_one_subheadline'] = $this->db->checkCharLen('instance_home.bd_one_subheadline', $POST['bd_one_subheadline']??'');
			$iHData['bd_one_details'] = $this->db->checkCharLen('instance_home.bd_one_details', $POST['bd_one_details']??'');
		}
		elseif($fromSegment=='homePreview321'){
			$iHData['bd_two_icon'] = $this->db->checkCharLen('instance_home.bd_two_icon', $POST['bd_two_icon']??'');
		}
		elseif($fromSegment=='homePreview322'){
			$iHData['bd_two_headline'] = $this->db->checkCharLen('instance_home.bd_two_headline', $POST['bd_two_headline']??'');
			$iHData['bd_two_subheadline'] = $this->db->checkCharLen('instance_home.bd_two_subheadline', $POST['bd_two_subheadline']??'');
			$iHData['bd_two_details'] = $this->db->checkCharLen('instance_home.bd_two_details', $POST['bd_two_details']??'');
		}
		elseif($fromSegment=='homePreview331'){
			$iHData['bd_three_icon'] = $this->db->checkCharLen('instance_home.bd_three_icon', $POST['bd_three_icon']??'');
		}
		elseif($fromSegment=='homePreview332'){
			$iHData['bd_three_headline'] = $this->db->checkCharLen('instance_home.bd_three_headline', $POST['bd_three_headline']??'');
			$iHData['bd_three_subheadline'] = $this->db->checkCharLen('instance_home.bd_three_subheadline', $POST['bd_three_subheadline']??'');
			$iHData['bd_three_details'] = $this->db->checkCharLen('instance_home.bd_three_details', $POST['bd_three_details']??'');
		}

		if($instance_home_id>0){
			$iHData['last_updated'] = date('Y-m-d H:i:s');
			$update = $this->db->update('instance_home', $iHData, $instance_home_id);
			if($update){
				$returnStr = 'Updated';
			}
		}
		else{
			$instance_home_id = $this->db->insert('instance_home', $iHData);
			if($instance_home_id){
				$returnStr = 'Inserted';
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
    }

    public function AJsave_home_page_body(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';

		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$bg_color1 = $POST['bg_color1']??'#8fbf4d';
		$color1 = $POST['color1']??'#FFF';
		$font_family1 = $POST['font_family1']??'Arial';
		$bg_color2 = $POST['bg_color2']??'#f4f4f4';
		$color2 = $POST['color2']??'#333333';
		$font_family2 = $POST['font_family2']??'Arial';
		$bg_color3 = $POST['bg_color3']??'#FFFFFF';
		$color3 = $POST['color3']??'#333333';
		$font_family3 = $POST['font_family3']??'Arial';

		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id = $accounts_id AND name='web_home'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}
		
		$value = serialize(array('bg_color1'=>$bg_color1, 'color1'=>$color1, 'font_family1'=>$font_family1, 'bg_color2'=>$bg_color2, 'color2'=>$color2, 'font_family2'=>$font_family2, 'bg_color3'=>$bg_color3, 'color3'=>$color3, 'font_family3'=>$font_family3));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>'web_home',
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'Add';
			}
			else{
				$returnStr = 'errorOnAdding';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'Update';
			}
			else{
				$returnStr = 'errorOnUpdating';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	//=======================all_pages_footer=========================//
	public function all_pages_footer(){}

	public function AJ_all_pages_footer_MoreInfo(){
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$instance_home_id = 0;
		$ihObj = $this->db->query("SELECT instance_home_id FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);			
			$instance_home_id = $ihrow->instance_home_id;
		}
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		
		$company_name = $_SESSION["company_name"]??'';
		$company_phone_no = $business_address = $customer_service_email = '';
		$usersObj = $this->db->query("SELECT * FROM accounts WHERE accounts_id = $accounts_id", array());
		if($usersObj){
			$companyrowarray = $usersObj->fetch(PDO::FETCH_OBJ);
			$company_phone_no = $companyrowarray->company_phone_no;
			$customer_service_email = $companyrowarray->customer_service_email;
			$company_street_address = $companyrowarray->company_street_address;
			$company_country_name = $companyrowarray->company_country_name;
			$company_state_name = $companyrowarray->company_state_name;
			$company_city = $companyrowarray->company_city;
			$company_zip = $companyrowarray->company_zip;
			$business_address = "$company_street_address";
			if($company_city !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $company_city;
			}
			if($company_state_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $company_state_name;
			}
			if($company_zip !=''){
				if($business_address !=''){$business_address .= ' - ';}
				$business_address .= $company_zip;
			}
			if($company_country_name !=''){
				if($business_address !=''){$business_address .= ', ';}
				$business_address .= $company_country_name;
			}
		}
		$jsonResponse['company_name'] = $company_name;
		$jsonResponse['business_address'] = $business_address;
		$jsonResponse['company_phone_no'] = $company_phone_no;
		$jsonResponse['customer_service_email'] = $customer_service_email;

		$bg_color = '#363947';
		$color = '#8f9caa';
		$font_family = 'Arial';	
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'web_footer'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;		 
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}

		$jsonResponse['variables_id'] = intval($variables_id);
		$jsonResponse['bg_color'] = $bg_color;
		$jsonResponse['color'] = $color;
		$jsonResponse['font_family'] = $font_family;
		
		return json_encode($jsonResponse);
	}	
    
   public function AJsave_all_pages_footer(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';

		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$bg_color = $POST['bg_color']??'#363947';
		$color = $POST['color']??'#8f9caa';
		$font_family = $POST['font_family']??'Arial';

		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='web_footer'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}

		$value = serialize(array('bg_color'=>$bg_color, 'color'=>$color, 'font_family'=>$font_family));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'web_footer'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'Add';
			}
			else{
				$returnStr = 'errorOnAdding';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'Update';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	//=======================ContactUs=========================//
	public function ContactUs(){}
	
	public function AJ_ContactUs_MoreInfo(){
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$instance_home_id = 0;
		$ihObj = $this->db->query("SELECT instance_home_id FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);			
			$instance_home_id = $ihrow->instance_home_id;
		}
		$jsonResponse['instance_home_id'] = intval($instance_home_id);

		$bg_color = '#ffffff';
		$color = '#363947';
		$font_family = 'Arial';	
		$but_bg_color = '#ef7f1b';
		$but_color = '#FFFFFF';
		$but_font_family = 'Arial';
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'ContactUs'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		
		$jsonResponse['variables_id'] = $variables_id;
		$jsonResponse['bg_color'] = $bg_color;
		$jsonResponse['color'] = $color;
		$jsonResponse['font_family'] = $font_family;
		
		$jsonResponse['but_bg_color'] = $but_bg_color;
		$jsonResponse['but_color'] = $but_color;
		$jsonResponse['but_font_family'] = $but_font_family;
		
		return json_encode($jsonResponse);
	}	
    
   public function AJsave_ContactUs(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$bg_color = $POST['bg_color']??'#363947';
		$color = $POST['color']??'#8f9caa';
		$font_family = $POST['font_family']??'Arial';
		$but_bg_color = $POST['but_bg_color']??'#ef7f1b';
		$but_color = $POST['but_color']??'#FFFFFF';
		$but_font_family = $POST['but_font_family']??'Arial';
		
		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='ContactUs'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}

		$value = serialize(array('bg_color'=>$bg_color, 'color'=>$color, 'font_family'=>$font_family, 'but_bg_color'=>$but_bg_color, 'but_color'=>$but_color, 'but_font_family'=>$but_font_family));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'ContactUs'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'Add';
			}
			else{
				$returnStr = 'errorOnAdding';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'Update';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	//=======================Customer=========================//
	public function Customer(){}

	public function AJ_Customer_MoreInfo(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;	
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$instance_home_id = $display_add_customer = 0;
		$ihObj = $this->db->query("SELECT instance_home_id, display_add_customer, website_on FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);
			
			$instance_home_id = $ihrow->instance_home_id;
			$display_add_customer = $ihrow->display_add_customer;
		}
		
		$bg_color = '#ffffff';
		$color = '#363947';
		$font_family = 'Arial';	
		$but_bg_color = '#ef7f1b';
		$but_color = '#FFFFFF';
		$but_font_family = 'Arial';
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'Customer'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		$jsonResponse['variables_id'] = intval($variables_id);
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		$jsonResponse['display_add_customer'] = intval($display_add_customer);

		$jsonResponse['bg_color'] = $bg_color;
		$jsonResponse['color'] = $color;
		$jsonResponse['font_family'] = $font_family;
		
		$jsonResponse['but_bg_color'] = $but_bg_color;
		$jsonResponse['but_color'] = $but_color;
		$jsonResponse['but_font_family'] = $but_font_family;

		$fieldNames = array('email'=>1, 'offers_email'=>1, 'company'=>1, 'contact_no'=>1, 'secondary_phone'=>1, 'fax'=>1, 'shipping_address_one'=>1, 'shipping_address_two'=>1, 'shipping_city'=>1, 'shipping_state'=>1, 'shipping_zip'=>1, 'shipping_country'=>1, 'website'=>1);
		$customFields = array();
		$queryCFObj = $this->db->query("SELECT * FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
		if($queryCFObj){
			$l=0;
			while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
				$oneCustomFields = (array) $oneCustomFields;
				$oneCustomFields['custom_fields_id'] = intval($oneCustomFields['custom_fields_id']);
				$oneCustomFields['accounts_id'] = intval($oneCustomFields['accounts_id']);
				$oneCustomFields['custom_fields_publish'] = intval($oneCustomFields['custom_fields_publish']);
				$oneCustomFields['field_required'] = intval($oneCustomFields['field_required']);
				$oneCustomFields['order_val'] = intval($oneCustomFields['order_val']);
				$oneCustomFields['user_id'] = intval($oneCustomFields['user_id']);
				$customFields[] = $oneCustomFields;
				$fieldNames['cf'.$oneCustomFields['custom_fields_id']] = 0;
				$l++;
			}
		}
		
		$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id=$accounts_id AND name='Customer'", array());
		if($queryObj){
			$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('fieldNames', $value)){
					$fieldNames = $value['fieldNames'];
				}
			}
		}
		$jsonResponse['fieldNames'] = $fieldNames;
		$jsonResponse['customFields'] = $customFields;

		return json_encode($jsonResponse);		
	}	
    
    public function AJsave_Customer(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$bg_color = $POST['bg_color']??'#363947';
		$color = $POST['color']??'#8f9caa';
		$font_family = $POST['font_family']??'Arial';
		$but_bg_color = $POST['but_bg_color']??'#ef7f1b';
		$but_color = $POST['but_color']??'#FFFFFF';
		$but_font_family = $POST['but_font_family']??'Arial';
		$fieldNames = array();
		$fieldArray = array('email', 'offers_email', 'company', 'contact_no', 'secondary_phone', 'fax', 'shipping_address_one', 'shipping_address_two', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country', 'website');
		foreach($fieldArray as $oneField){
			$oneFieldVal = $POST[$oneField]??0;
			$fieldNames[$oneField] = $oneFieldVal;
		}
		
		$queryCFObj = $this->db->query("SELECT custom_fields_id FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
		if($queryCFObj){
			while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
				$oneField = 'cf'.$oneCustomFields->custom_fields_id;
				if(isset($POST[$oneField])){
					$oneFieldVal = $POST[$oneField]??0;
					$fieldNames[$oneField] = $oneFieldVal;
				}
			}
		}
		
		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='Customer'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}

		$value = serialize(array('bg_color'=>$bg_color, 'color'=>$color, 'font_family'=>$font_family, 'but_bg_color'=>$but_bg_color, 'but_color'=>$but_color, 'but_font_family'=>$but_font_family, 'fieldNames'=>$fieldNames));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'Customer'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'Add';
			}
			else{
				$returnStr = 'errorOnAdding';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'Update';
			}
		}
		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	//=======================services=========================//
	public function services(){}

	public function AJ_services_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$instance_home_id = $display_services = $display_services_prices = $enable_services_paypal = 0;
		$paypal_email = $currency_code = '';
		$sql = "SELECT instance_home_id, display_services, display_services_prices, enable_services_paypal, paypal_email, currency_code, website_on FROM instance_home WHERE accounts_id = $accounts_id";
		$ihObj = $this->db->query($sql, array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);
			
			$instance_home_id = $ihrow->instance_home_id;
			$display_services = $ihrow->display_services;
			$display_services_prices = $ihrow->display_services_prices;
			$enable_services_paypal = $ihrow->enable_services_paypal;
			$paypal_email = $ihrow->paypal_email;
			$currency_code = $ihrow->currency_code;
		}
		else{		
			$iHData = array('accounts_id'=>$accounts_id,
						'created_on'=>date('Y-m-d H:i:s'),
						'last_updated'=>date('Y-m-d H:i:s'),
						'website_on'=>$website_on,
						'enable_widget'=>0,
						'display_add_customer'=>0,
						'display_inventory'=>0,
						'display_cell_prices'=>0,
						'enable_cell_paypal'=>0,
						'display_products'=>0,
						'display_products_prices'=>0,
						'enable_product_paypal'=>0,
						'display_services'=>0,
						'display_services_prices'=>0,
						'enable_services_paypal'=>0,
						'paypal_email'=>'',
						'currency_code'=>'',
						'request_a_quote'=>0,
						'mobile_repair_appointment'=>0,
						'repair_status_online'=>0,
						'mst_one'=>$this->db->checkCharLen('instance_home.mst_one', $this->db->translate('Smart Phone')),
						'mst_two'=>$this->db->checkCharLen('instance_home.mst_two', $this->db->translate('Sales Ampar')),
						'mst_three'=>$this->db->checkCharLen('instance_home.mst_three', $this->db->translate('Repairs')),
						'mst_four'=>$this->db->checkCharLen('instance_home.mst_four', $this->db->translate('AT COMPETITIVE PRICES')),
						'business_address'=>'',
						'bd_one_icon'=>'',
						'bd_one_headline'=>$this->db->checkCharLen('instance_home.bd_one_headline', $this->db->translate('REPAIR SERVICES')),
						'bd_one_subheadline'=>$this->db->checkCharLen('instance_home.bd_one_subheadline', $this->db->translate('CELL PHONE REPAIR')),
						'bd_one_details'=>$this->db->checkCharLen('instance_home.bd_one_details', $this->db->translate('Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!')),
						'bd_two_icon'=>'',
						'bd_two_headline'=>$this->db->checkCharLen('instance_home.bd_two_headline', $this->db->translate('OUR SERVICES')),
						'bd_two_subheadline'=>$this->db->checkCharLen('instance_home.bd_two_subheadline', $this->db->translate('FULL SERVICE')),
						'bd_two_details'=>$this->db->checkCharLen('instance_home.bd_two_details', $this->db->translate('Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.')),
						'bd_three_icon'=>'',
						'bd_three_headline'=>$this->db->checkCharLen('instance_home.bd_three_headline', $this->db->translate('OUR SUPPORT')),
						'bd_three_subheadline'=>$this->db->checkCharLen('instance_home.bd_three_subheadline', $this->db->translate('HERE FOR YOU')),
						'bd_three_details'=>$this->db->checkCharLen('instance_home.bd_three_details', $this->db->translate('We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.')),
						'cellular_services1'=>'',
						'cellular_services2'=>'',
						'cellular_services3'=>'',
						'cellular_services4'=>'',
						'cellular_services5'=>'',
						'cellular_services6'=>'',
						'cellular_services7'=>'',
						'mon_from'=>'',
						'mon_to'=>'',
						'tue_from'=>'',
						'tue_to'=>'',
						'wed_from'=>'',
						'wed_to'=>'',
						'thu_from'=>'',
						'thu_to'=>'',
						'fri_from'=>'',
						'fri_to'=>'',
						'sat_from'=>'',
						'sat_to'=>'',
						'sun_from'=>'',
						'sun_to'=>'',
						'meta_keywords'=>'',
						'meta_description'=>''
						);
			
			$instance_home_id = $this->db->insert('instance_home', $iHData);
			
		}
		
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		$jsonResponse['display_services'] = intval($display_services);
		$jsonResponse['display_services_prices'] = intval($display_services_prices);
		$jsonResponse['enable_services_paypal'] = intval($enable_services_paypal);
		$jsonResponse['paypal_email'] = $paypal_email;
		$jsonResponse['currency_code'] = $currency_code;

		return json_encode($jsonResponse);
	}	
    
	//=======================products=========================//
	public function products(){}

	public function AJ_products_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$instance_home_id = $display_products = $display_products_prices = $enable_product_paypal = 0;
		$paypal_email = $currency_code = '';
		$sql = "SELECT instance_home_id, display_products, display_products_prices, enable_product_paypal, paypal_email, currency_code, website_on FROM instance_home WHERE accounts_id = $accounts_id";
		$ihObj = $this->db->query($sql, array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);
			
			$instance_home_id = $ihrow->instance_home_id;
			$display_products = $ihrow->display_products;
			$display_products_prices = $ihrow->display_products_prices;
			$enable_product_paypal = $ihrow->enable_product_paypal;
			$paypal_email = $ihrow->paypal_email;
			$currency_code = $ihrow->currency_code;
		}
		else{
			
			$iHData = array('accounts_id'=>$accounts_id,
						'created_on'=>date('Y-m-d H:i:s'),
						'last_updated'=>date('Y-m-d H:i:s'),
						'website_on'=>$website_on,
						'enable_widget'=>0,
						'display_add_customer'=>0,
						'display_inventory'=>0,
						'display_cell_prices'=>0,
						'enable_cell_paypal'=>0,
						'display_products'=>0,
						'display_products_prices'=>0,
						'enable_product_paypal'=>0,
						'display_services'=>0,
						'display_services_prices'=>0,
						'enable_services_paypal'=>0,
						'paypal_email'=>'',
						'currency_code'=>'',
						'request_a_quote'=>0,
						'mobile_repair_appointment'=>0,
						'repair_status_online'=>0,
						'mst_one'=>$this->db->checkCharLen('instance_home.mst_one', $this->db->translate('Smart Phone')),
						'mst_two'=>$this->db->checkCharLen('instance_home.mst_two', $this->db->translate('Sales Ampar')),
						'mst_three'=>$this->db->checkCharLen('instance_home.mst_three', $this->db->translate('Repairs')),
						'mst_four'=>$this->db->checkCharLen('instance_home.mst_four', $this->db->translate('AT COMPETITIVE PRICES')),
						'business_address'=>'',
						'bd_one_icon'=>'',
						'bd_one_headline'=>$this->db->checkCharLen('instance_home.bd_one_headline', $this->db->translate('REPAIR SERVICES')),
						'bd_one_subheadline'=>$this->db->checkCharLen('instance_home.bd_one_subheadline', $this->db->translate('CELL PHONE REPAIR')),
						'bd_one_details'=>$this->db->checkCharLen('instance_home.bd_one_details', $this->db->translate('Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!')),
						'bd_two_icon'=>'',
						'bd_two_headline'=>$this->db->checkCharLen('instance_home.bd_two_headline', $this->db->translate('OUR SERVICES')),
						'bd_two_subheadline'=>$this->db->checkCharLen('instance_home.bd_two_subheadline', $this->db->translate('FULL SERVICE')),
						'bd_two_details'=>$this->db->checkCharLen('instance_home.bd_two_details', $this->db->translate('Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.')),
						'bd_three_icon'=>'',
						'bd_three_headline'=>$this->db->checkCharLen('instance_home.bd_three_headline', $this->db->translate('OUR SUPPORT')),
						'bd_three_subheadline'=>$this->db->checkCharLen('instance_home.bd_three_subheadline', $this->db->translate('HERE FOR YOU')),
						'bd_three_details'=>$this->db->checkCharLen('instance_home.bd_three_details', $this->db->translate('We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.')),
						'cellular_services1'=>'',
						'cellular_services2'=>'',
						'cellular_services3'=>'',
						'cellular_services4'=>'',
						'cellular_services5'=>'',
						'cellular_services6'=>'',
						'cellular_services7'=>'',
						'mon_from'=>'',
						'mon_to'=>'',
						'tue_from'=>'',
						'tue_to'=>'',
						'wed_from'=>'',
						'wed_to'=>'',
						'thu_from'=>'',
						'thu_to'=>'',
						'fri_from'=>'',
						'fri_to'=>'',
						'sat_from'=>'',
						'sat_to'=>'',
						'sun_from'=>'',
						'sun_to'=>'',
						'meta_keywords'=>'',
						'meta_description'=>''
						);
			
			$instance_home_id = $this->db->insert('instance_home', $iHData);
			
		}
		
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		$jsonResponse['display_products'] = intval($display_products);
		$jsonResponse['display_products_prices'] = intval($display_products_prices);
		$jsonResponse['enable_product_paypal'] = intval($enable_product_paypal);
		$jsonResponse['paypal_email'] = $paypal_email;
		$jsonResponse['currency_code'] = $currency_code;

		return json_encode($jsonResponse);
	}	
    
    public function AJsave_Product(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$instance_home_id = intval($POST['instance_home_id']??0);
		$enable_cell_paypal = $POST['enable_cell_paypal']??0;
		$enable_product_paypal = intval($POST['enable_product_paypal']??0);
		$enable_services_paypal = $POST['enable_services_paypal']??0;
		//return json_encode(array('login'=>'', 'returnStr'=>$POST));
		if($instance_home_id==0){
			$ihObj = $this->db->query("SELECT instance_home_id FROM instance_home WHERE accounts_id = $accounts_id", array());
			if($ihObj){
				$instance_home_id = $ihObj->fetch(PDO::FETCH_OBJ)->instance_home_id;
			}
		}
		
		if($enable_cell_paypal>0 || $enable_product_paypal>0 || $enable_services_paypal>0){
			if($instance_home_id>0){
				$iHData = array();
			}
			else{
				$iHData = array('accounts_id'=>$accounts_id,
							'created_on'=>date('Y-m-d H:i:s'),
							'last_updated'=>date('Y-m-d H:i:s'),
							'website_on'=>$website_on,
							'enable_widget'=>0,
							'display_add_customer'=>0,
							'display_inventory'=>0,
							'display_cell_prices'=>0,
							'enable_cell_paypal'=>0,
							'display_products'=>0,
							'display_products_prices'=>0,
							'enable_product_paypal'=>0,
							'display_services'=>0,
							'display_services_prices'=>0,
							'enable_services_paypal'=>0,
							'paypal_email'=>'',
							'currency_code'=>'',
							'request_a_quote'=>0,
							'mobile_repair_appointment'=>0,
							'repair_status_online'=>0,
							'mst_one'=>$this->db->checkCharLen('instance_home.mst_one', $this->db->translate('Smart Phone')),
							'mst_two'=>$this->db->checkCharLen('instance_home.mst_two', $this->db->translate('Sales Ampar')),
							'mst_three'=>$this->db->checkCharLen('instance_home.mst_three', $this->db->translate('Repairs')),
							'mst_four'=>$this->db->checkCharLen('instance_home.mst_four', $this->db->translate('AT COMPETITIVE PRICES')),
							'business_address'=>'',
							'bd_one_icon'=>'',
							'bd_one_headline'=>$this->db->checkCharLen('instance_home.bd_one_headline', $this->db->translate('REPAIR SERVICES')),
							'bd_one_subheadline'=>$this->db->checkCharLen('instance_home.bd_one_subheadline', $this->db->translate('CELL PHONE REPAIR')),
							'bd_one_details'=>$this->db->checkCharLen('instance_home.bd_one_details', $this->db->translate('Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!')),
							'bd_two_icon'=>'',
							'bd_two_headline'=>$this->db->checkCharLen('instance_home.bd_two_headline', $this->db->translate('OUR SERVICES')),
							'bd_two_subheadline'=>$this->db->checkCharLen('instance_home.bd_two_subheadline', $this->db->translate('FULL SERVICE')),
							'bd_two_details'=>$this->db->checkCharLen('instance_home.bd_two_details', $this->db->translate('Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.')),
							'bd_three_icon'=>'',
							'bd_three_headline'=>$this->db->checkCharLen('instance_home.bd_three_headline', $this->db->translate('OUR SUPPORT')),
							'bd_three_subheadline'=>$this->db->checkCharLen('instance_home.bd_three_subheadline', $this->db->translate('HERE FOR YOU')),
							'bd_three_details'=>$this->db->checkCharLen('instance_home.bd_three_details', $this->db->translate('We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.')),
							'cellular_services1'=>'',
							'cellular_services2'=>'',
							'cellular_services3'=>'',
							'cellular_services4'=>'',
							'cellular_services5'=>'',
							'cellular_services6'=>'',
							'cellular_services7'=>'',
							'mon_from'=>'',
							'mon_to'=>'',
							'tue_from'=>'',
							'tue_to'=>'',
							'wed_from'=>'',
							'wed_to'=>'',
							'thu_from'=>'',
							'thu_to'=>'',
							'fri_from'=>'',
							'fri_to'=>'',
							'sat_from'=>'',
							'sat_to'=>'',
							'sun_from'=>'',
							'sun_to'=>'',
							'meta_keywords'=>'',
							'meta_description'=>''
							);
			}		
			
			$iHData['paypal_email'] = $this->db->checkCharLen('instance_home.paypal_email', $POST['paypal_email']??'');
			$iHData['currency_code'] = $this->db->checkCharLen('instance_home.currency_code', $POST['currency_code']??'');

			if($instance_home_id>0){
				$iHData['last_updated'] = date('Y-m-d H:i:s');
				$update = $this->db->update('instance_home', $iHData, $instance_home_id);
				if($update){
					$savemsg = 'Update';
				}
			}
			else{
				$instance_home_id = $this->db->insert('instance_home', $iHData);
				if($instance_home_id){
					$savemsg = 'Add';
				}
			}
		}
		return json_encode(array('login'=>'', 'savemsg'=>$savemsg));
    }
	
	//=======================cell_phones=========================//
	public function cell_phones(){}

	public function AJ_cell_phones_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$instance_home_id = $display_inventory = $display_cell_prices = $enable_cell_paypal = 0;
		$paypal_email = $currency_code = '';
		$ihObj = $this->db->query("SELECT instance_home_id, display_inventory, display_cell_prices, enable_cell_paypal, paypal_email, currency_code, website_on FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);
			
			$instance_home_id = $ihrow->instance_home_id;
			$display_inventory = $ihrow->display_inventory;
			$display_cell_prices = $ihrow->display_cell_prices;
			$enable_cell_paypal = $ihrow->enable_cell_paypal;
			$paypal_email = $ihrow->paypal_email;
			$currency_code = $ihrow->currency_code;
		}
		else{	
			
			$iHData = array('accounts_id'=>$accounts_id,
						'created_on'=>date('Y-m-d H:i:s'),
						'last_updated'=>date('Y-m-d H:i:s'),
						'website_on'=>$website_on,
						'enable_widget'=>0,
						'display_add_customer'=>0,
						'display_inventory'=>0,
						'display_cell_prices'=>0,
						'enable_cell_paypal'=>0,
						'display_products'=>0,
						'display_products_prices'=>0,
						'enable_product_paypal'=>0,
						'display_services'=>0,
						'display_services_prices'=>0,
						'enable_services_paypal'=>0,
						'paypal_email'=>'',
						'currency_code'=>'',
						'request_a_quote'=>0,
						'mobile_repair_appointment'=>0,
						'repair_status_online'=>0,
						'mst_one'=>$this->db->checkCharLen('instance_home.mst_one', $this->db->translate('Smart Phone')),
						'mst_two'=>$this->db->checkCharLen('instance_home.mst_two', $this->db->translate('Sales Ampar')),
						'mst_three'=>$this->db->checkCharLen('instance_home.mst_three', $this->db->translate('Repairs')),
						'mst_four'=>$this->db->checkCharLen('instance_home.mst_four', $this->db->translate('AT COMPETITIVE PRICES')),
						'business_address'=>'',
						'bd_one_icon'=>'',
						'bd_one_headline'=>$this->db->checkCharLen('instance_home.bd_one_headline', $this->db->translate('REPAIR SERVICES')),
						'bd_one_subheadline'=>$this->db->checkCharLen('instance_home.bd_one_subheadline', $this->db->translate('CELL PHONE REPAIR')),
						'bd_one_details'=>$this->db->checkCharLen('instance_home.bd_one_details', $this->db->translate('Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!')),
						'bd_two_icon'=>'',
						'bd_two_headline'=>$this->db->checkCharLen('instance_home.bd_two_headline', $this->db->translate('OUR SERVICES')),
						'bd_two_subheadline'=>$this->db->checkCharLen('instance_home.bd_two_subheadline', $this->db->translate('FULL SERVICE')),
						'bd_two_details'=>$this->db->checkCharLen('instance_home.bd_two_details', $this->db->translate('Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.')),
						'bd_three_icon'=>'',
						'bd_three_headline'=>$this->db->checkCharLen('instance_home.bd_three_headline', $this->db->translate('OUR SUPPORT')),
						'bd_three_subheadline'=>$this->db->checkCharLen('instance_home.bd_three_subheadline', $this->db->translate('HERE FOR YOU')),
						'bd_three_details'=>$this->db->checkCharLen('instance_home.bd_three_details', $this->db->translate('We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.')),
						'cellular_services1'=>'',
						'cellular_services2'=>'',
						'cellular_services3'=>'',
						'cellular_services4'=>'',
						'cellular_services5'=>'',
						'cellular_services6'=>'',
						'cellular_services7'=>'',
						'mon_from'=>'',
						'mon_to'=>'',
						'tue_from'=>'',
						'tue_to'=>'',
						'wed_from'=>'',
						'wed_to'=>'',
						'thu_from'=>'',
						'thu_to'=>'',
						'fri_from'=>'',
						'fri_to'=>'',
						'sat_from'=>'',
						'sat_to'=>'',
						'sun_from'=>'',
						'sun_to'=>'',
						'meta_keywords'=>'',
						'meta_description'=>''
						);
			
			$instance_home_id = $this->db->insert('instance_home', $iHData);
			
		}
		
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		$jsonResponse['display_inventory'] = intval($display_inventory);
		$jsonResponse['display_cell_prices'] = intval($display_cell_prices);
		$jsonResponse['enable_cell_paypal'] = intval($enable_cell_paypal);
		$jsonResponse['paypal_email'] = $paypal_email;
		$jsonResponse['currency_code'] = $currency_code;

		return json_encode($jsonResponse);
	}
	
    public function pictureupload(){
		$POST = json_decode(file_get_contents('php://input'), true);
        if (!is_uploaded_file($_FILES['imei_picture']['tmp_name'])){
            return $this->db->translate('Possible file upload attack. Filename:')." " . $_FILES['imei_picture']['tmp_name'];
        }

        if(!isset($_SESSION["prod_cat_man"])){
            return 'session_ended';
        }
        else{

            $accounts_id = $_SESSION["accounts_id"]??0;
            $item_id = intval($POST['mpitem_id']??0);
            $mppicturecount = intval($POST['mppicturecount']??0)+1;

            $folderpath = "./assets/accounts/a_".$accounts_id;
            if(!is_dir($folderpath)){mkdir($folderpath, 0777);}

            $imagename = 'imei_'.$item_id.'_'.$mppicturecount.'-'.substr(time(),7,3).'.png';
            $width = $height = 400;
            $image_info = getimagesize($_FILES['imei_picture']['tmp_name']);
            $imageType = $image_info[2];//1=gif, 2=jpg/jpeg, 3=png,
            if ($imageType > 3 ) {
                return "NOT A JPG/JPEG/PNG/GIF FILE!!!! TRY AGAIN";
            }
            $orig_width = $image_info[0];
            $orig_height = $image_info[1];
            //======Update Image Size=========//
            $source_aspect_ratio = $orig_width / $orig_height;
            $thumbnail_aspect_ratio = $width / $height;
            if ($orig_width <= $width && $orig_height <= $height) {
                $thumbnail_image_width = $orig_width;
                $thumbnail_image_height = $orig_height;
            } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                $thumbnail_image_width = (int) ($height * $source_aspect_ratio);
                $thumbnail_image_height = $height;
            } else {
                $thumbnail_image_width = $width;
                $thumbnail_image_height = (int) ($width / $source_aspect_ratio);
            }

            $width = $thumbnail_image_width;
            $height = $thumbnail_image_height;

            //=========Create Image==============//
            $new_image = imagecreatetruecolor( $width, $height );
            if ($imageType == '1' ) {
                $image = imagecreatefromgif($_FILES['imei_picture']['tmp_name']);
            }
            elseif ($imageType == '2' ) {
                $image = imagecreatefromjpeg($_FILES['imei_picture']['tmp_name']);
            }
            else {
                $image = imagecreatefrompng($_FILES['imei_picture']['tmp_name']);
            }

            imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
            imagepng($new_image, $folderpath.'/'.$imagename, 0);
            imagedestroy($new_image);
            imagedestroy($image);

            $picturesrc = str_replace('./', '/', $folderpath.'/'.$imagename);
			
			if (extension_loaded('imagick')){	
				//==============Image Compression and replace=================//
				$im = new Imagick($_SERVER['DOCUMENT_ROOT'] . $picturesrc);
				$im->optimizeImageLayers(); // Optimize the image layers			
				$im->setImageCompression(Imagick::COMPRESSION_JPEG);// Compression and quality
				$im->setImageCompressionQuality(0);
				$imagename = str_replace('.png', '.jpg', $imagename);
				$picturesrc1 = str_replace('.png', '.jpg', $picturesrc);
				$im->writeImages($_SERVER['DOCUMENT_ROOT'] . $picturesrc1, true);// Write the image back
				unlink('.'.$picturesrc); // delete file		
				$picturesrc = $picturesrc1;
			}
			
            return "<img src=\"$picturesrc\" alt=\"$picturesrc\" class=\"img-responsive imeipic\">";
        }
    }
	
	//=======================Quote=========================//
	public function Quote(){}

	public function AJ_Quote_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$instance_home_id = $request_a_quote = 0;
		$ihObj = $this->db->query("SELECT instance_home_id, request_a_quote, website_on FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);
			
			$instance_home_id = $ihrow->instance_home_id;
			$request_a_quote = $ihrow->request_a_quote;
		}
		else{	
			
			$iHData = array('accounts_id'=>$accounts_id,
						'created_on'=>date('Y-m-d H:i:s'),
						'last_updated'=>date('Y-m-d H:i:s'),
						'website_on'=>$website_on,
						'enable_widget'=>0,
						'display_add_customer'=>0,
						'display_inventory'=>0,
						'display_cell_prices'=>0,
						'enable_cell_paypal'=>0,
						'display_products'=>0,
						'display_products_prices'=>0,
						'enable_product_paypal'=>0,
						'display_services'=>0,
						'display_services_prices'=>0,
						'enable_services_paypal'=>0,
						'paypal_email'=>'',
						'currency_code'=>'',
						'request_a_quote'=>0,
						'mobile_repair_appointment'=>0,
						'repair_status_online'=>0,
						'mst_one'=>$this->db->checkCharLen('instance_home.mst_one', $this->db->translate('Smart Phone')),
						'mst_two'=>$this->db->checkCharLen('instance_home.mst_two', $this->db->translate('Sales Ampar')),
						'mst_three'=>$this->db->checkCharLen('instance_home.mst_three', $this->db->translate('Repairs')),
						'mst_four'=>$this->db->checkCharLen('instance_home.mst_four', $this->db->translate('AT COMPETITIVE PRICES')),
						'business_address'=>'',
						'bd_one_icon'=>'',
						'bd_one_headline'=>$this->db->checkCharLen('instance_home.bd_one_headline', $this->db->translate('REPAIR SERVICES')),
						'bd_one_subheadline'=>$this->db->checkCharLen('instance_home.bd_one_subheadline', $this->db->translate('CELL PHONE REPAIR')),
						'bd_one_details'=>$this->db->checkCharLen('instance_home.bd_one_details', $this->db->translate('Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!')),
						'bd_two_icon'=>'',
						'bd_two_headline'=>$this->db->checkCharLen('instance_home.bd_two_headline', $this->db->translate('OUR SERVICES')),
						'bd_two_subheadline'=>$this->db->checkCharLen('instance_home.bd_two_subheadline', $this->db->translate('FULL SERVICE')),
						'bd_two_details'=>$this->db->checkCharLen('instance_home.bd_two_details', $this->db->translate('Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.')),
						'bd_three_icon'=>'',
						'bd_three_headline'=>$this->db->checkCharLen('instance_home.bd_three_headline', $this->db->translate('OUR SUPPORT')),
						'bd_three_subheadline'=>$this->db->checkCharLen('instance_home.bd_three_subheadline', $this->db->translate('HERE FOR YOU')),
						'bd_three_details'=>$this->db->checkCharLen('instance_home.bd_three_details', $this->db->translate('We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.')),
						'cellular_services1'=>'',
						'cellular_services2'=>'',
						'cellular_services3'=>'',
						'cellular_services4'=>'',
						'cellular_services5'=>'',
						'cellular_services6'=>'',
						'cellular_services7'=>'',
						'mon_from'=>'',
						'mon_to'=>'',
						'tue_from'=>'',
						'tue_to'=>'',
						'wed_from'=>'',
						'wed_to'=>'',
						'thu_from'=>'',
						'thu_to'=>'',
						'fri_from'=>'',
						'fri_to'=>'',
						'sat_from'=>'',
						'sat_to'=>'',
						'sun_from'=>'',
						'sun_to'=>'',
						'meta_keywords'=>'',
						'meta_description'=>''
						);
			
			$instance_home_id = $this->db->insert('instance_home', $iHData);
			
		}
		
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		$jsonResponse['request_a_quote'] = intval($request_a_quote);

		$bg_color = '#ffffff';
		$color = '#363947';
		$font_family = 'Arial';	
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'Quote'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;		 
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		
		$jsonResponse['variables_id'] = intval($variables_id);
		$jsonResponse['bg_color'] = $bg_color;
		$jsonResponse['color'] = $color;
		$jsonResponse['font_family'] = $font_family;

		return json_encode($jsonResponse);
	}
	
    public function AJsave_Quote(){
		$POST = json_decode(file_get_contents('php://input'), true);

		$savemsg = 'error';
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$bg_color = $POST['bg_color']??'#363947';
		$color = $POST['color']??'#8f9caa';
		$font_family = $POST['font_family']??'Arial';

		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='Quote'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}

		$value = serialize(array('bg_color'=>$bg_color, 'color'=>$color, 'font_family'=>$font_family));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'Quote'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'Add';
			}
			else{
				$returnStr = 'errorOnAdding';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'Update';
			}
		}

		$array = array('login'=>'',  'id'=>$variables_id,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
    }
	
	//=======================Appointment=========================//
	public function Appointment(){}

	public function AJ_Appointment_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$instance_home_id = 0;
		$mobile_repair_appointment = 0;
		$ihObj = $this->db->query("SELECT instance_home_id, mobile_repair_appointment, website_on FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);
			
			$instance_home_id = $ihrow->instance_home_id;
			$mobile_repair_appointment = $ihrow->mobile_repair_appointment;
		}
		else{
			$iHData = array('accounts_id'=>$accounts_id,
						'created_on'=>date('Y-m-d H:i:s'),
						'last_updated'=>date('Y-m-d H:i:s'),
						'website_on'=>$website_on,
						'enable_widget'=>0,
						'display_add_customer'=>0,
						'display_inventory'=>0,
						'display_cell_prices'=>0,
						'enable_cell_paypal'=>0,
						'display_products'=>0,
						'display_products_prices'=>0,
						'enable_product_paypal'=>0,
						'display_services'=>0,
						'display_services_prices'=>0,
						'enable_services_paypal'=>0,
						'paypal_email'=>'',
						'currency_code'=>'',
						'request_a_quote'=>0,
						'mobile_repair_appointment'=>0,
						'repair_status_online'=>0,
						'mst_one'=>$this->db->checkCharLen('instance_home.mst_one', $this->db->translate('Smart Phone')),
						'mst_two'=>$this->db->checkCharLen('instance_home.mst_two', $this->db->translate('Sales Ampar')),
						'mst_three'=>$this->db->checkCharLen('instance_home.mst_three', $this->db->translate('Repairs')),
						'mst_four'=>$this->db->checkCharLen('instance_home.mst_four', $this->db->translate('AT COMPETITIVE PRICES')),
						'business_address'=>'',
						'bd_one_icon'=>'',
						'bd_one_headline'=>$this->db->checkCharLen('instance_home.bd_one_headline', $this->db->translate('REPAIR SERVICES')),
						'bd_one_subheadline'=>$this->db->checkCharLen('instance_home.bd_one_subheadline', $this->db->translate('CELL PHONE REPAIR')),
						'bd_one_details'=>$this->db->checkCharLen('instance_home.bd_one_details', $this->db->translate('Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!')),
						'bd_two_icon'=>'',
						'bd_two_headline'=>$this->db->checkCharLen('instance_home.bd_two_headline', $this->db->translate('OUR SERVICES')),
						'bd_two_subheadline'=>$this->db->checkCharLen('instance_home.bd_two_subheadline', $this->db->translate('FULL SERVICE')),
						'bd_two_details'=>$this->db->checkCharLen('instance_home.bd_two_details', $this->db->translate('Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.')),
						'bd_three_icon'=>'',
						'bd_three_headline'=>$this->db->checkCharLen('instance_home.bd_three_headline', $this->db->translate('OUR SUPPORT')),
						'bd_three_subheadline'=>$this->db->checkCharLen('instance_home.bd_three_subheadline', $this->db->translate('HERE FOR YOU')),
						'bd_three_details'=>$this->db->checkCharLen('instance_home.bd_three_details', $this->db->translate('We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.')),
						'cellular_services1'=>'',
						'cellular_services2'=>'',
						'cellular_services3'=>'',
						'cellular_services4'=>'',
						'cellular_services5'=>'',
						'cellular_services6'=>'',
						'cellular_services7'=>'',
						'mon_from'=>'',
						'mon_to'=>'',
						'tue_from'=>'',
						'tue_to'=>'',
						'wed_from'=>'',
						'wed_to'=>'',
						'thu_from'=>'',
						'thu_to'=>'',
						'fri_from'=>'',
						'fri_to'=>'',
						'sat_from'=>'',
						'sat_to'=>'',
						'sun_from'=>'',
						'sun_to'=>'',
						'meta_keywords'=>'',
						'meta_description'=>''
						);
			
			$instance_home_id = $this->db->insert('instance_home', $iHData);
			
		}
		
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		$jsonResponse['mobile_repair_appointment'] = intval($mobile_repair_appointment);

		$bg_color = '#FFFFFF';
		$color = '#363947';
		$font_family = 'Arial';
		$but_bg_color = '#ef7f1b';
		$but_color = '#FFFFFF';
		$but_font_family = 'Arial';
		
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'Appointment'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;		 
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		
		$jsonResponse['variables_id'] = intval($variables_id);
		$jsonResponse['bg_color'] = $bg_color;
		$jsonResponse['color'] = $color;
		$jsonResponse['font_family'] = $font_family;
		$jsonResponse['but_bg_color'] = $but_bg_color;
		$jsonResponse['but_color'] = $but_color;
		$jsonResponse['but_font_family'] = $but_font_family;

		$schedules = array();
		$fieldNames = array('Name', 'Phone no.', 'Email', 'Brand and model of device', 'What needs to be fixed');
		$requiredFields = array_slice($fieldNames, 0, 3);
		$blockoutDates = array();
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'Appointment'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;		 
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('schedules', $value)){$schedules = $value['schedules'];}
				if(array_key_exists('fieldNames', $value)){$fieldNames = explode('||', $value['fieldNames']);}
				if(array_key_exists('blockoutDates', $value)){$blockoutDates = $value['blockoutDates'];}
			}
		}
		$jsonResponse['fieldNames'] = $fieldNames;
		$jsonResponse['requiredFields'] = $requiredFields;
		$jsonResponse['schedules'] = $schedules;
		$jsonResponse['blockoutDates'] = $blockoutDates;

		return json_encode($jsonResponse);
	}	
    
    public function AJsave_Appointment(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$bg_color = $POST['bg_color']??'#363947';
		$color = $POST['color']??'#8f9caa';
		$font_family = $POST['font_family']??'Arial';
		$but_bg_color = $POST['but_bg_color']??'#ef7f1b';
		$but_color = $POST['but_color']??'#FFFFFF';
		$but_font_family = $POST['but_font_family']??'Arial';
		$fieldNameArray = $POST['fieldNames[]']??array();
		$blockoutDates = $POST['blockoutDates[]']??array();		
		
		$fieldNames = '';
		if(!empty($fieldNameArray)){
			foreach($fieldNameArray as $key=>$oneField){
				if(empty($oneField)){unset($fieldNameArray[$key]);}
			}
			$fieldNames = implode('||', $fieldNameArray);
		}
		
		$schedules = array();
		$weekDays = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
		foreach($weekDays as $key=>$weekDay){
			$weekDaysCks = $POST['weekDays_'.($key+1)]??0;
			if($weekDaysCks>0){
				$schedules[$weekDay][0] = 1;
				$hourMinutes = array();
				for($h=0; $h<=23; $h++){
					$hours = sprintf("%02d", $h);
					$m=0;
					while($m<=55){
						$minutes = sprintf("%02d", $m);
						$name = ($key+1).'_'.$hours.'_'.$minutes;
						$hourMinutesCks = $POST[$name]??0;
						if($hourMinutesCks>0){
							$hourMinutes[] = $name;
						}
						$m=$m+5;
					}
				}
			
				if(!empty($hourMinutes)){
					$schedules[$weekDay][1] = $hourMinutes;
				}
			}
		}
		
		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='Appointment'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}
		
		$value = serialize(array('bg_color'=>$bg_color, 'color'=>$color, 'font_family'=>$font_family, 'but_bg_color'=>$but_bg_color, 'but_color'=>$but_color, 'but_font_family'=>$but_font_family, 'fieldNames'=>$fieldNames, 'schedules'=>$schedules, 'blockoutDates'=>$blockoutDates));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'Appointment'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'Add';
			}
			else{
				$returnStr = 'errorOnAdding';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'Update';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	//=======================Appointment=========================//
	public function RStatus(){}

	public function AJ_RStatus_MoreInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;	
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$instance_home_id = $repair_status_online = 0;
		$ihObj = $this->db->query("SELECT instance_home_id, repair_status_online, website_on FROM instance_home WHERE accounts_id = $accounts_id ", array());
		if($ihObj){
			$ihrow = $ihObj->fetch(PDO::FETCH_OBJ);
			
			$instance_home_id = $ihrow->instance_home_id;
			$repair_status_online = $ihrow->repair_status_online;
		}
		else{
			
			$iHData = array('accounts_id'=>$accounts_id,
						'created_on'=>date('Y-m-d H:i:s'),
						'last_updated'=>date('Y-m-d H:i:s'),
						'website_on'=>$website_on,
						'enable_widget'=>0,
						'display_add_customer'=>0,
						'display_inventory'=>0,
						'display_cell_prices'=>0,
						'enable_cell_paypal'=>0,
						'display_products'=>0,
						'display_products_prices'=>0,
						'enable_product_paypal'=>0,
						'display_services'=>0,
						'display_services_prices'=>0,
						'enable_services_paypal'=>0,
						'paypal_email'=>'',
						'currency_code'=>'',
						'request_a_quote'=>0,
						'mobile_repair_appointment'=>0,
						'repair_status_online'=>0,
						'mst_one'=>$this->db->checkCharLen('instance_home.mst_one', $this->db->translate('Smart Phone')),
						'mst_two'=>$this->db->checkCharLen('instance_home.mst_two', $this->db->translate('Sales Ampar')),
						'mst_three'=>$this->db->checkCharLen('instance_home.mst_three', $this->db->translate('Repairs')),
						'mst_four'=>$this->db->checkCharLen('instance_home.mst_four', $this->db->translate('AT COMPETITIVE PRICES')),
						'business_address'=>'',
						'bd_one_icon'=>'',
						'bd_one_headline'=>$this->db->checkCharLen('instance_home.bd_one_headline', $this->db->translate('REPAIR SERVICES')),
						'bd_one_subheadline'=>$this->db->checkCharLen('instance_home.bd_one_subheadline', $this->db->translate('CELL PHONE REPAIR')),
						'bd_one_details'=>$this->db->checkCharLen('instance_home.bd_one_details', $this->db->translate('Repair services you can trust.  From small issues to major repairs our trained technicians are ready to assist.  We are looking forward to serving you!')),
						'bd_two_icon'=>'',
						'bd_two_headline'=>$this->db->checkCharLen('instance_home.bd_two_headline', $this->db->translate('OUR SERVICES')),
						'bd_two_subheadline'=>$this->db->checkCharLen('instance_home.bd_two_subheadline', $this->db->translate('FULL SERVICE')),
						'bd_two_details'=>$this->db->checkCharLen('instance_home.bd_two_details', $this->db->translate('Count on us for your cell phone purchase and service needs.  Reliable phones, quality repair services, unlocking, accessories, and more.')),
						'bd_three_icon'=>'',
						'bd_three_headline'=>$this->db->checkCharLen('instance_home.bd_three_headline', $this->db->translate('OUR SUPPORT')),
						'bd_three_subheadline'=>$this->db->checkCharLen('instance_home.bd_three_subheadline', $this->db->translate('HERE FOR YOU')),
						'bd_three_details'=>$this->db->checkCharLen('instance_home.bd_three_details', $this->db->translate('We are here to assist you. Our confident team is available and ready to answer your questions and exceed your expectations. Contact us today.')),
						'cellular_services1'=>'',
						'cellular_services2'=>'',
						'cellular_services3'=>'',
						'cellular_services4'=>'',
						'cellular_services5'=>'',
						'cellular_services6'=>'',
						'cellular_services7'=>'',
						'mon_from'=>'',
						'mon_to'=>'',
						'tue_from'=>'',
						'tue_to'=>'',
						'wed_from'=>'',
						'wed_to'=>'',
						'thu_from'=>'',
						'thu_to'=>'',
						'fri_from'=>'',
						'fri_to'=>'',
						'sat_from'=>'',
						'sat_to'=>'',
						'sun_from'=>'',
						'sun_to'=>'',
						'meta_keywords'=>'',
						'meta_description'=>''
						);
			
			$instance_home_id = $this->db->insert('instance_home', $iHData);
			
		}
		
		$jsonResponse['instance_home_id'] = intval($instance_home_id);
		$jsonResponse['repair_status_online'] = intval($repair_status_online);

		$bg_color = '#ffffff';
		$color = '#363947';
		$font_family = 'Arial';	
		$but_bg_color = '#ef7f1b';
		$but_color = '#FFFFFF';
		$but_font_family = 'Arial';	
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'RStatus'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;		 
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		
		$jsonResponse['variables_id'] = intval($variables_id);
		$jsonResponse['bg_color'] = $bg_color;
		$jsonResponse['color'] = $color;
		$jsonResponse['font_family'] = $font_family;
		$jsonResponse['but_bg_color'] = $but_bg_color;
		$jsonResponse['but_color'] = $but_color;
		$jsonResponse['but_font_family'] = $but_font_family;

		return json_encode($jsonResponse);		
	}	
    
    public function AJsave_RStatus(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';

		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$bg_color = $POST['bg_color']??'#363947';
		$color = $POST['color']??'#8f9caa';
		$font_family = $POST['font_family']??'Arial';
		$but_bg_color = $POST['but_bg_color']??'#ef7f1b';
		$but_color = $POST['but_color']??'#FFFFFF';
		$but_font_family = $POST['but_font_family']??'Arial';	
		
		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='RStatus'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}

		$value = serialize(array('bg_color'=>$bg_color, 'color'=>$color, 'font_family'=>$font_family, 'but_bg_color'=>$but_bg_color, 'but_color'=>$but_color, 'but_font_family'=>$but_font_family));
		$data=array('accounts_id'=>$accounts_id,
					'name'=>$this->db->checkCharLen('variables.name', 'RStatus'),
					'value'=>$value,
					'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'Add';
			}
			else{
				$returnStr = 'errorOnAdding';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'Update';
			}
		}

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
}
?>