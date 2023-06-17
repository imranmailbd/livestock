<?php
class Getting_Started{
	
	protected $db;
	private $pageTitle;
	private int $page, $totalRows;
	private string $data_type, $language, $keyword_search;
	
	public function __construct($db){$this->db = $db;}
	
	public function accounts_setup(){		
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		return "<input type=\"hidden\" name=\"prod_cat_man\" id=\"prod_cat_man\" value=\"$prod_cat_man\">
		<input type=\"hidden\" name=\"accounts_id\" id=\"accounts_id\" value=\"$accounts_id\">";
	}
	
	public function AJ_accounts_setup_MoreInfo(){
		
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$Common = new Common($this->db);
		$vData = $Common->variablesData('account_setup', $accounts_id);
		if(!empty($vData)){
			extract($vData);

			$timezonedata = array();
			$timestamp = time();
			$timezone_identifiers = DateTimeZone::listIdentifiers();
			foreach($timezone_identifiers as $key => $zone){
				date_default_timezone_set($zone);
				$diff_from_GMT = 'UTC/GMT ' . date('P', $timestamp);
				$timezonedata[$zone] = "$diff_from_GMT - $zone";
			}
			if($timezone==''){$timezone = 'America/New_York';}
			date_default_timezone_set($timezone);

			$jsonResponse['timezone'] = $timezone;
			$jsonResponse['timezonedata'] = $timezonedata;

			$jsonResponse['dateformat'] = $dateformat;
			$jsonResponse['timeformat'] = $timeformat;
			
			$jsonResponse['currency'] = $currency;
			$jsonResponse['currencyData'] = $this->currencyData();

			$jsonResponse['language'] = $language;
		}
		
		
		return json_encode($jsonResponse);
	}
    
	private function filterAndOptionsLang(){
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$language = strtolower($this->language);
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Getting_Started";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
			
		$filterSql = $bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql[] = "TRIM(CONCAT_WS(' ', english, $language)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(languages_id) AS totalrows FROM languages";
		if(!empty($filterSql)){
			$strextra .= " WHERE ".implode(' OR ', $filterSql);
		}
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;
	}
	
    private function loadTableRowsLang(){
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$language = $this->language;
		$keyword_search = $this->keyword_search;
		$page = $this->page;
		$totalRows = $this->totalRows;
		$limit = $_SESSION["limit"];
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}					
		$language = strtolower($language);
		$filterSql = $bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql[] = "TRIM(CONCAT_WS(' ', english, $language)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * FROM languages";
		if(!empty($filterSql)){
			$sqlquery .= " WHERE ".implode(' OR ', $filterSql);
		}
		$sqlquery .= " ORDER BY english ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			$modifiedLang = array();
			$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name='language'", array());
			if($queryObj){
				$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$modifiedLang = unserialize($value);
				}
			}
			
			foreach($query as $onerow){
				
				$languages_id = $onerow['languages_id'];
				$english = nl2br(strip_tags(stripslashes($onerow['english'])));
				$selLang = nl2br(strip_tags(stripslashes($onerow[$language])));
				$action = 0;
				if(!empty($modifiedLang) && array_key_exists($english, $modifiedLang)){
					$selLang = $modifiedLang[$english];
					if(!empty($selLang)){
						$expSelLang = explode('||', $selLang);
						if(count($expSelLang)>1){
							$selLang = $expSelLang[1];
						}
					}
					$action = 1;
				}

				$tabledata[] = array($languages_id, $english, $selLang, $action);
			}
		}
		return $tabledata;
    }
	
	public function AJgetLangPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$language = $POST['language']??'English';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->language = $language;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptionsLang();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRowsLang();
		
		return json_encode($jsonResponse);
	}
	
    public function saveAS(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$varObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id = $accounts_id AND name = 'account_setup'", array());
		if($varObj){
			$variables_id = $varObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}
		$currency = $POST['currency']??'৳';
		$timezone = $POST['timezone']??'America/New_York';
		$dateformat = $POST['dateformat']??'m/d/y';
		$timeformat = $POST['timeformat']??'12 hour';
		$language = $POST['language']??'English';
		
		//==============Check if it has any sub location================//
		$subLocations = array();
		if($_SESSION["currency"] != $currency){
			$queryObj = $this->db->query("SELECT accounts_id FROM accounts WHERE location_of = $accounts_id", array());
			if($queryObj){
				while($row = $queryObj->fetch(PDO::FETCH_OBJ)){
					$subLocations[] = $row->accounts_id;						
				}
			}
		}

		$value = serialize(array('currency'=>$currency, 'timezone'=>$timezone, 'dateformat'=>$dateformat, 'timeformat'=>$timeformat, 'language'=>$language));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'account_setup'),
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
		
		if(!empty($subLocations)){
			
			foreach($subLocations as $subAccId){
				$queryObj = $this->db->query("SELECT variables_id, value FROM variables WHERE accounts_id=$subAccId AND name='account_setup'", array());
				if($queryObj){
					$subLocData = $queryObj->fetch(PDO::FETCH_OBJ);
					$subLocValueSer = $subLocData->value;
					if(!empty($subLocValueSer)){
						$subLocValue = unserialize($subLocValueSer);
						if(!empty($subLocValue) && is_array($subLocValue) && array_key_exists('currency', $subLocValue)){
							$subLocCurrency = $subLocValue['currency'];
							if($subLocCurrency != $currency){
								$subLocValue['currency'] = $currency;								
								$data2=array('value'=>serialize($subLocValue),
											'last_updated'=> date('Y-m-d H:i:s'));
								$this->db->update('variables', $data2, $subLocData->variables_id);
							}
						}
					}
				}
				else{
					$data3 = $data;
					$data3['accounts_id'] = $subAccId;
					$this->db->insert('variables', $data3);
				}
			}
		}

		date_default_timezone_set($timezone);
		$_SESSION["timezone"] = $timezone;
		$_SESSION["currency"] = $currency;
		$_SESSION["dateformat"] = $dateformat;
		$_SESSION["timeformat"] = $timeformat;
		$_SESSION["language"] = $language;
		
		$languageVar = array();
		if($language !='English'){
			$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id=$accounts_id AND name='language'", array());
			if($queryObj){
				$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$modifiedLang = unserialize($value);
					if(!empty($modifiedLang) && is_array($modifiedLang)){
						foreach($modifiedLang as $varName=>$varValue){
							$languageVar['_'.$varName] = addslashes(trim((string) stripslashes($varValue)));
						}
					}
				}
			}
		}
		$_SESSION["languageVar"] = $languageVar;

		$array = array( 'login'=>'', 'id'=>$variables_id,
			'savemsg'=>$savemsg);
		return json_encode($array);
    }
	
	public function AJgetLangPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$languagesData = array();
		$languagesData['login'] = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$languages_id = intval($POST['languages_id']??0);
		
		$php_js = 1;
		$english = $selLang = '';
		$selectedLang = $POST['selectedLang']??'English';
		$language = strtolower($selectedLang);
		
		$english = '';
		if($languages_id>0 && $selectedLang != ''){
			$languagesObj = $this->db->querypagination("SELECT * FROM languages WHERE languages_id = :languages_id LIMIT 0,1", array('languages_id'=>$languages_id),1);
			if($languagesObj){
				$modifiedLang = array();
				$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id=$accounts_id AND name='language'", array());
				if($queryObj){
					$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$modifiedLang = unserialize($value);
					}
				}
				
				foreach($languagesObj as $languagesRow){
						
					$languages_id = $languagesRow['languages_id'];
					$php_js = stripslashes($languagesRow['php_js']);
					$english = stripslashes($languagesRow['english']);
					$selLang = stripslashes($languagesRow[$language]);
						
					if(!empty($modifiedLang) && array_key_exists($english, $modifiedLang)){
						$selLang = stripslashes($modifiedLang[$english]);
						if(!empty($selLang)){
							$expSelLang = explode('||', $selLang);
							if(count($expSelLang)>1){
								$selLang = $expSelLang[1];
							}
						}
					}
				}
			}
			else{
				$languages_id = 0;
			}
		}
		$languagesData['languages_id'] = $languages_id;		
		$languagesData['php_js'] = $php_js;
		$languagesData['english'] = $english;			
		$languagesData['Language'] = $selectedLang;
		$languagesData['popuplanguage'] = $selLang;
		
		return json_encode($languagesData);
	}
	
	public function AJsaveLang(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$languages_id = intval($POST['languages_id']??0);
		$php_js = $POST['php_js']??1;
		$english = $POST['english']??'';
		$english = addslashes(trim((string) $english));
		$popuplanguage = $POST['popuplanguage']??'';
		$popuplanguage = "$php_js||".addslashes(trim((string) $popuplanguage));
		
		if($languages_id>0){
			$variables_id = $update = 0;
			
			$modifiedLang = array();
			$queryObj = $this->db->query("SELECT variables_id, value FROM variables WHERE accounts_id=$accounts_id AND name='language'", array());
			if($queryObj){
				$variablesData = $queryObj->fetch(PDO::FETCH_OBJ);
				if($variablesData){
					$variables_id = $variablesData->variables_id;
					$value = $variablesData->value;
					if(!empty($value)){
						$modifiedLang = unserialize($value);
					}
				}
			}
			$modifiedLang[$english] = $popuplanguage;
			$languageVar = $languageJSVar = array();
			if(!empty($modifiedLang) && is_array($modifiedLang)){
				foreach($modifiedLang as $varName=>$varValue){
					if(!empty($modifiedLang) && is_array($modifiedLang)){
						foreach($modifiedLang as $varName=>$varValue){
							if(!empty($varValue)){
								$expvarValue = explode('||', $varValue);
								if(count($expvarValue)>1){
									$php_js = $expvarValue[0];
									$selLang = $expvarValue[1];
									
									$languageVar[$varName] = addslashes(trim((string) stripslashes($selLang)));
									$languageJSVar[$varName] = addslashes(trim((string) stripslashes($selLang)));
								}
							}
						}
					}
				}
			}
			$_SESSION["languageVar"] = $languageVar;
			$_SESSION["languageJSVar"] = $languageJSVar;
			
			$value = serialize($modifiedLang);
			$data=array('accounts_id'=>$accounts_id,
				'name'=>$this->db->checkCharLen('variables.name', 'language'),
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
	
	public function AJremoveLang(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$english = $POST['english']??'';
		
		$variables_id = 0;
		$modifiedLang = array();
		$queryObj = $this->db->query("SELECT variables_id, value FROM variables WHERE accounts_id=$accounts_id AND name='language'", array());
		if($queryObj){
			$variablesData = $queryObj->fetch(PDO::FETCH_OBJ);
			if($variablesData){
				$variables_id = $variablesData->variables_id;
				$value = $variablesData->value;
				if(!empty($value)){
					$modifiedLang = unserialize($value);
				}
			}
		}
		
		if(!empty($modifiedLang) && array_key_exists($english, $modifiedLang)){
			unset($modifiedLang[$english]);
		}
		
		$value = serialize($modifiedLang);
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'language'),
			'value'=>$value,
			'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id>0){
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}
		$languageVar = $languageJSVar = array();
		if(!empty($modifiedLang) && is_array($modifiedLang)){
			foreach($modifiedLang as $varName=>$varValue){
				if(!empty($modifiedLang) && is_array($modifiedLang)){
					foreach($modifiedLang as $varName=>$varValue){
						if(!empty($varValue)){
							$expvarValue = explode('||', $varValue);
							if(count($expvarValue)>1){
								$php_js = $expvarValue[0];
								$selLang = $expvarValue[1];
								
								$languageVar[$varName] = addslashes(trim((string) stripslashes($selLang)));
								$languageJSVar[$varName] = addslashes(trim((string) stripslashes($selLang)));
							}
						}
					}
				}
			}
		}
		$_SESSION["languageVar"] = $languageVar;
		$_SESSION["languageJSVar"] = $languageJSVar;
		
		if($savemsg !=''){				
			$savemsg = 'Done';
			$activity_feed_title = $this->db->translate('Language information removed successfully.');
			$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
			$activity_feed_link = "";
			$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
			
			$afData = array('created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'accounts_id' => $_SESSION["accounts_id"],
							'user_id' => $_SESSION["user_id"],
							'activity_feed_title' => $activity_feed_title,
							'activity_feed_name' => $english,
							'activity_feed_link' => $activity_feed_link,
							'uri_table_name' => "variables",
							'uri_table_field_name' =>"",
							'field_value' => 0);
			$this->db->insert('activity_feed', $afData);
		}
		else{
			$savemsg = 'error';
		}
		
		$array = array( 'login'=>'','savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	public function company_info(){	}
	
	public function AJ_company_info_MoreInfo(){		
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$userObj = $this->db->query("SELECT company_name, company_subdomain, company_phone_no, customer_service_email, company_street_address, company_country_name, company_state_name, company_city, company_zip FROM accounts WHERE accounts_id = $accounts_id", array());
		if($userObj){
			$userarray = $userObj->fetch(PDO::FETCH_OBJ);
			$jsonResponse['company_name'] = $userarray->company_name;
			$jsonResponse['company_subdomain'] = $userarray->company_subdomain;
			$jsonResponse['company_phone_no'] = $userarray->company_phone_no;
			$jsonResponse['customer_service_email'] = $userarray->customer_service_email;
			$jsonResponse['company_street_address'] = $userarray->company_street_address;
			$jsonResponse['company_country_name'] = $userarray->company_country_name;
			$jsonResponse['company_state_name'] = $userarray->company_state_name;
			$jsonResponse['company_city'] = $userarray->company_city;
			$jsonResponse['company_zip'] = $userarray->company_zip;

			$jsonResponse['countryData'] = $this->countryData();
		}		
		
		return json_encode($jsonResponse);
	}
    
    public function save_company_info(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = 0;
		$savemsg = $message = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;

		$company_name = $this->db->checkCharLen('accounts.company_name', $POST['company_name']??'');
		$company_subdomain = $this->db->checkCharLen('accounts.company_subdomain', $POST['company_subdomain']??'');
		$company_phone_no = $this->db->checkCharLen('accounts.company_phone_no', $POST['company_phone_no']??'');
		$customer_service_email = $this->db->checkCharLen('accounts.customer_service_email', $POST['customer_service_email']??'');
		$company_street_address = $this->db->checkCharLen('accounts.company_street_address', $POST['company_street_address']??'');
		$company_country_name = $this->db->checkCharLen('accounts.company_country_name', $POST['company_country_name']??'');
		$company_state_name = $this->db->checkCharLen('accounts.company_state_name', $POST['company_state_name']??'');
		$company_city = $this->db->checkCharLen('accounts.company_city', $POST['company_city']??'');
		$company_zip = $this->db->checkCharLen('accounts.company_zip', $POST['company_zip']??'');

		$countTableData = 0;
		$userObj = $this->db->query("SELECT COUNT(accounts_id) AS totalrows FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain AND accounts_id != $accounts_id", array('company_subdomain'=>$company_subdomain, 'domain'=>OUR_DOMAINNAME));
		if($userObj){
			$countTableData = $userObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		if($countTableData>0){
			$savemsg = 'error';
			$message .= 'Name_Already_Exist';
		}
		else{
			$conditionarray = array();
			$conditionarray['company_name'] = $company_name;
			$conditionarray['company_subdomain'] = $company_subdomain;
			$conditionarray['company_phone_no'] = $company_phone_no;
			if($customer_service_email !=''){
				$conditionarray['customer_service_email'] = $customer_service_email;
			}
			$conditionarray['company_street_address'] = $company_street_address;
			$conditionarray['company_country_name'] = $company_country_name;
			$conditionarray['company_state_name'] = $company_state_name;
			$conditionarray['company_city'] = $company_city;
			$conditionarray['company_zip'] = $company_zip;

			$this->db->update('accounts', $conditionarray, $accounts_id);

			$_SESSION["company_name"] = $company_name;
			$id = $accounts_id;
			$savemsg = 'update-success';
		}
	
		$array = array( 'login'=>'','id'=>$id,
			'savemsg'=>$savemsg,
			'message'=>$message);
		return json_encode($array);
	}
	
	public function taxes(){}
	
	public function AJsaveTaxes(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$taxes_id = intval($POST['taxes_id']??0);
		$conditionarray = array();
		$taxes_name = addslashes($POST['taxes_name']??'');
		$taxes_name = $this->db->checkCharLen('taxes.taxes_name', $taxes_name);
		
		$taxes_percentage = round($POST['taxes_percentage']??0,3);
		if($taxes_percentage>=100){$taxes_percentage = 99.999;}
		
		$default_tax = $POST['default_tax']??0;
		$tax_inclusive = $POST['tax_inclusive']??0;
		
		$conditionarray['taxes_name'] = $taxes_name;
		$conditionarray['taxes_percentage'] = $taxes_percentage;
		$conditionarray['default_tax'] = $default_tax;
		$conditionarray['tax_inclusive'] = $tax_inclusive;
		$conditionarray['accounts_id'] = $accounts_id;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT taxes_id, taxes_publish FROM taxes WHERE accounts_id = $accounts_id AND taxes_name = :taxes_name AND taxes_percentage = :taxes_percentage";
		$bindData = array('taxes_name'=>$taxes_name, 'taxes_percentage'=>$taxes_percentage);
		if($taxes_id>0){
			$duplSql .= " AND taxes_id != :taxes_id";
			$bindData['taxes_id'] = $taxes_id;
		}
		$duplRows = $taxes_publish = 0;
		$taxesObj = $this->db->query($duplSql, $bindData);
		if($taxesObj){
			while($oneRowTax = $taxesObj->fetch(PDO::FETCH_OBJ)){
				$duplRows++;
				$taxes_publish = $oneRowTax->taxes_publish;
			}
		}
		if($duplRows>0){
			$savemsg = 'error';
			if($taxes_publish>0){
				$returnStr = 'Name_Already_Exist';
			}
			else{
				$returnStr = 'Name_ExistInArchive';
			}
		}
		else{
			if($taxes_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$taxes_id = $this->db->insert('taxes', $conditionarray);
				if($taxes_id){
					if($default_tax>0){
						$countTableData = 0;
						$taxesObj2 = $this->db->query("SELECT COUNT(taxes_id) AS totalrows FROM taxes WHERE accounts_id = $accounts_id AND default_tax = 1 AND taxes_publish = 1 AND taxes_id != $taxes_id", array());
						if($taxesObj2){
							$countTableData = $taxesObj2->fetch(PDO::FETCH_OBJ)->totalrows;
						}
						if($countTableData==2){
							$taxesObj3 = $this->db->querypagination("SELECT taxes_id FROM taxes WHERE accounts_id = $accounts_id AND default_tax = 1 AND taxes_publish = 1 AND taxes_id != $taxes_id ORDER BY taxes_id ASC LIMIT 0,1", array());
							if($taxesObj3){
								foreach($taxesObj3 as $onerow){
									$dt_taxes_id = $onerow['taxes_id'];
									$this->db->update('taxes', array('default_tax'=>0), $dt_taxes_id);
								}
							}
						}
					}
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('taxes', $conditionarray, $taxes_id);
				if($update){
					$activity_feed_title = $this->db->translate('Tax has been changed.');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/Getting_Started/taxes/";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' => $activity_feed_title,
									'activity_feed_name' => "$taxes_name",
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "taxes",
									'uri_table_field_name' =>"taxes_publish",
									'field_value' => 1);
					$this->db->insert('activity_feed', $afData);

					if($default_tax>0){
						$countTableData = 0;
						$taxesObj2 = $this->db->query("SELECT COUNT(taxes_id) AS totalrows FROM taxes WHERE accounts_id = $accounts_id AND default_tax = 1 AND taxes_publish = 1 AND taxes_id != $taxes_id", array());
						if($taxesObj2){
							$countTableData = $taxesObj2->fetch(PDO::FETCH_OBJ)->totalrows;
						}
						if($countTableData==2){
							$taxesObj3 = $this->db->querypagination("SELECT taxes_id FROM taxes WHERE accounts_id = $accounts_id AND default_tax = 1 AND taxes_publish = 1 AND taxes_id != $taxes_id ORDER BY taxes_id ASC LIMIT 0,1", array());
							if($taxesObj3){
								foreach($taxesObj3 as $onerow){
									$dt_taxes_id = $onerow['taxes_id'];
									$this->db->update('taxes', array('default_tax'=>0), $dt_taxes_id);
								}
							}
						}
					}
					$savemsg = 'Update';
				}
				else{
					$returnStr = 'errorOnEditing';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPageTaxes($segment4name){
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
			$this->filterAndOptionsTaxes();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRowsTaxes();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptionsTaxes(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Getting_Started";
		$_SESSION["list_filters"] = array('sdata_type'=>$sdata_type, 'keyword_search'=>$keyword_search);
		
		$sqlPublish = " AND taxes_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND taxes_publish = 0";
		}
		$filterSql = "FROM taxes WHERE accounts_id = $accounts_id $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', taxes_name, taxes_percentage)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(taxes_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
    private function loadTableRowsTaxes(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND taxes_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND taxes_publish = 0";
		}
		$filterSql = "FROM taxes WHERE accounts_id = $accounts_id $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', taxes_name, taxes_percentage)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY taxes_name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){

				$taxes_id = $onerow['taxes_id'];
				$taxes_name = stripslashes($onerow['taxes_name']);
				$taxes_percentage = round($onerow['taxes_percentage'],3);
				$default_tax = $onerow['default_tax'];
				$default_taxtxt = '';
				if($default_tax>0){
					$default_taxtxt = '<i class="fa fa-check default_tax"></i>';
				}
				$tax_inclusive = $onerow['tax_inclusive'];
				$tax_inclusivetxt = '';
				if($tax_inclusive>0){
					$tax_inclusivetxt = '<i class="fa fa-check"></i>';
				}
				$tabledata[] = array($taxes_id, $taxes_name, $taxes_percentage, $default_taxtxt, $tax_inclusivetxt);
			}
		}
		
		return $tabledata;
    }
	
	public function AJgetData_Taxes(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$taxes_id = intval($POST['taxes_id']??0);
		
		$taxesData = array();
		$taxesData['login'] = '';
		$taxesData['taxes_id'] = $taxes_id;
		$taxesData['taxes_name'] = '';		
		$taxesData['taxes_percentage'] = 0.00;
		$taxesData['default_tax'] = 0;		
		$taxesData['tax_inclusive'] = 0;	
		$taxesData['taxes_publish'] = 1;
		
		if($taxes_id>0 && $accounts_id>0){
			$taxesObj = $this->db->query("SELECT * FROM taxes WHERE taxes_id = :taxes_id AND accounts_id = $accounts_id", array('taxes_id'=>$taxes_id),1);
			if($taxesObj){
				$taxesRow = $taxesObj->fetch(PDO::FETCH_OBJ);
				$taxesData['taxes_id'] = $taxesRow->taxes_id;
				$taxesData['taxes_name'] = stripslashes($taxesRow->taxes_name);
				$taxesData['taxes_percentage'] = round($taxesRow->taxes_percentage, 3);
				$taxesData['default_tax'] = $taxesRow->default_tax;
				$taxesData['tax_inclusive'] = $taxesRow->tax_inclusive;
				$taxesData['taxes_publish'] = intval($taxesRow->taxes_publish);
			}
		}
		return json_encode($taxesData);
	}
	
	public function AJremoveTaxes(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = '';
		$taxes_id = intval($POST['taxes_id']??0);
		$taxes_name = $POST['taxes_name']??'';
		$user_id = $_SESSION["user_id"]??0;
		$updatetable = $this->db->update('taxes', array('taxes_publish'=>0), $taxes_id);
		if($updatetable){
			$activity_feed_title = $this->db->translate('Tax archived');
			$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
			$activity_feed_link = "/Getting_Started/taxes/";
			$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
			
			$afData = array('created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'accounts_id' => $_SESSION["accounts_id"],
							'user_id' => $_SESSION["user_id"],
							'activity_feed_title' => $activity_feed_title,
							'activity_feed_name' => $taxes_name,
							'activity_feed_link' =>  $activity_feed_link,
							'uri_table_name' => "taxes",
							'uri_table_field_name' =>"taxes_publish",
							'field_value' => 0);
			$this->db->insert('activity_feed', $afData);
			
			$savemsg = 'archive-success';
		}
		return json_encode(array('login'=>'', 'returnStr'=>$savemsg));
	}
	
	public function payment_options(){}
	
	public function AJ_payment_options_MoreInfo(){
		
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$Common = new Common($this->db);
		$vData = $Common->variablesData('payment_options', $accounts_id);
		if(!empty($vData)){
			extract($vData);
			$poData = explode('||', $payment_options);
			$jsonResponse['poData'] = $poData;
			
			$sqrup_currency_code = '';
			$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'cr_card_processing' AND value !=''", array());
			if($varObj){
				$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$value = unserialize($value);
					if(array_key_exists('sqrup_currency_code', $value)){
						$sqrup_currency_code = $value['sqrup_currency_code'];
					}
				}
			}
			$jsonResponse['sqrup_currency_code'] = $sqrup_currency_code;
			
		}		
		
		return json_encode($jsonResponse);
	}
    
    public function savePO(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = 0;
		$varObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id = $accounts_id AND name = 'payment_options'", array());
		if($varObj){
			$variables_id = $varObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}

		$poData = $POST['payment_options[]']??array();
		$newPO = array();
		if(!empty($poData)){
			$i=0;
			foreach($poData as $poOneValue){
				if($poOneValue !=''){
					$newPO[] = $poOneValue;
				}
			}
		}
		$value = serialize(array('payment_options'=>implode('||', $newPO)));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'payment_options'),
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
	
	public function import_customers(){
		$supportEmail = $this->db->supportEmail('support');
		return "<input type=\"hidden\" name=\"supportEmail\" id=\"supportEmail\" value=\"$supportEmail\">";
	}
		
	public function import_products(){}
	
	public function small_print(){}
	
	public function AJ_small_print_MoreInfo(){
		
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$left_margin = $right_margin = 15;
		$variables_id = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'small_print'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		$jsonResponse['left_margin'] = $left_margin;
		$jsonResponse['right_margin'] = $right_margin;
		$jsonResponse['variables_id'] = $variables_id;
		
		return json_encode($jsonResponse);
	}
    
    public function save_small_print(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$variables_id = intval($POST['variables_id']??0);
		if($variables_id==0){
			$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='small_print'", array());
			if($queryObj){
				$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
			}
		}
		$left_margin = $POST['left_margin']??0;
		$right_margin = $POST['right_margin']??0;
		$value = serialize(array('left_margin'=>$left_margin, 'right_margin'=>$right_margin));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'small_print'),
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
	
	public function label_printer(){}
	
	public function AJ_label_printer_MoreInfo(){
		
		$accounts_id = $_SESSION['accounts_id']??0;
		$variables_id = $barcodeLength = 0;
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$label_size = '';
		$top_margin = $bottom_margin = 5;
		$left_margin = $right_margin = 3;
		$orientation = 'Portrait';
		$fontSize = 'Regular';
		$fontFamily = 'Arial';
		$units = $label_sizeWidth = $label_sizeHeight = '';
		$Common = new Common($this->db);
		$vData = $Common->variablesData('label_printer', $accounts_id);
		if($vData){
			extract($vData);
		}
		$jsonResponse['barcodeLength'] = $barcodeLength;
		$jsonResponse['fontSize'] = $fontSize;
		if(empty($fontFamily)){$fontFamily = 'Arial';}
		$jsonResponse['fontFamily'] = $fontFamily;
		$jsonResponse['label_size'] = $label_size;
		$jsonResponse['top_margin'] = $top_margin;
		$jsonResponse['bottom_margin'] = $bottom_margin;
		$jsonResponse['left_margin'] = $left_margin;
		$jsonResponse['right_margin'] = $right_margin;
		$jsonResponse['orientation'] = $orientation;
		$jsonResponse['units'] = $units;
		$jsonResponse['label_sizeWidth'] = $label_sizeWidth;
		$jsonResponse['label_sizeHeight'] = $label_sizeHeight;
		$jsonResponse['variables_id'] = $variables_id;
		
		return json_encode($jsonResponse);
	}
    	
    public function saveLP(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = 0;
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='label_printer'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}
		
		$label_size = $POST['label_size']??'57|31';
		$units = $POST['units']??'mm';
		$label_sizeWidth = $label_sizeHeight = '';
		if($label_size=='customSize'){
			$label_sizeWidth = floatval($POST['label_sizeWidth']??57);
			$label_sizeHeight = floatval($POST['label_sizeHeight']??31);
		}

		$barcodeLength = floatval($POST['barcodeLength']??0);
		$top_margin = floatval($POST['top_margin']??0);
		$right_margin = floatval($POST['right_margin']??0);
		$bottom_margin = floatval($POST['bottom_margin']??0);
		$left_margin = floatval($POST['left_margin']??0);
		$orientation = $POST['orientation']??'Portrait';
		$fontSize = $POST['fontSize']??'Regular';
		$fontFamily = $POST['fontFamily']??'';

		$value = serialize(array('barcodeLength'=>$barcodeLength, 'fontSize'=>$fontSize, 'fontFamily'=>$fontFamily, 'label_size'=>$label_size, 'units'=>$units, 'label_sizeWidth'=>$label_sizeWidth, 'label_sizeHeight'=>$label_sizeHeight, 'top_margin'=>$top_margin, 'right_margin'=>$right_margin, 'bottom_margin'=>$bottom_margin, 'left_margin'=>$left_margin, 'orientation'=>$orientation));
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'label_printer'),
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
	
	public function lpPreview(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnHTML = 'error';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;

		$label_size = $POST['label_size']??'57|31';
		$units = $POST['units']??'mm';
		if($label_size=='customSize'){
			if($units=='Inches'){
				$labelwidth = round(round(trim((string) $POST['label_sizeWidth']??2.25),2)*25.4);
				$labelheight = round(round(trim((string) $POST['label_sizeHeight']??1.25),2)*25.4);
			}
			else{
				$labelwidth = round(trim((string) $POST['label_sizeWidth']??57));
				$labelheight = round(trim((string) $POST['label_sizeHeight']??31));
			}
		}
		else{
			list($labelwidth, $labelheight) = explode('|', $label_size);
		}
		$fixedwidth = $labelwidth;
		$fixedheight = $labelheight;
		$labelwidth = $labelwidth*3.7795275591;
		$labelheight = $labelheight*3.7795275591;

		$top_margin = intval($POST['top_margin']??0);
		$right_margin = intval($POST['right_margin']??0);
		$bottom_margin = intval($POST['bottom_margin']??0);
		$left_margin = intval($POST['left_margin']??0);

		if($top_margin !=0){$labelheight = $labelheight-$top_margin;}
		if($bottom_margin !=0){$labelheight = $labelheight-$bottom_margin;}
		if($right_margin !=0){$labelwidth = $labelwidth-$right_margin;}
		if($left_margin !=0){$labelwidth = $labelwidth-$left_margin;}

		$font_size = $POST['font_size']??'Regular';
		$font_sizeOptions = array('Small'=>'11', 'Regular'=>'12', 'Large'=>'13');
		$fontsize = $font_sizeOptions[$font_size]??'Regular';

		$lineheight = 14;
		$marginCSS = '';
		if($top_margin !=0 || $right_margin !=0 || $bottom_margin !=0 || $left_margin !=0)
			$marginCSS = 'margin:'.$top_margin.'px '.$right_margin.'px '.$bottom_margin.'px '.$left_margin.'px;';

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['fixedwidth'] = $fixedwidth;
		$jsonResponse['fixedheight'] = $fixedheight;
		$jsonResponse['labelwidth'] = $labelwidth;
		$jsonResponse['labelheight'] = $labelheight;
		$jsonResponse['fontsize'] = $fontsize;
		$jsonResponse['lineheight'] = $lineheight;
		$jsonResponse['marginCSS'] = $marginCSS;		

		return json_encode($jsonResponse);
    }
	
	public function countryData(){		
		$returnData =array('Canada',
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
				
		return $returnData;
	}
	
	public function currencyData(){
		$returnData = array('৳'=>'Dollar',
							'£'=>'Pound',
							'€'=>'Euro',
							'৳'=>'Bengali Taka',
							'Lek'=>'Albanian Lek',
							'P'=>'Botswana PULA',
							'R$'=>'Brazilian Real',
							'FC'=>'Congolese',
							'KR'=>'Danish Kroner',
							'د.إ'=>'Dirham',
							'GH¢'=>'Ghana Cedi',
							'GNF'=>'Guinean Franc',
							'Ft'=>'Hungarian Forint',
							'﷼'=>'Iranian Rial',
							'JOD'=>'Jordan',
							'KSh'=>'Kenyan',
							'₩'=>'Korean Won',
							'LBP'=>'Lebanese Pound',
							'MXN'=>'Mexican Peso',
							'₦'=>'Nigerian Naira',
							'OMR'=>'Omani Rial',
							'PHP'=>'Philippine Piso',
							'R'=>'Rand',
							'RM'=>'Ringgit',
							'Rp'=>'Rupee Letters',
							'₹'=>'Rupee Symbol',
							'₽'=>'Russia Ruble',
							'SR'=>'Saudi Riyal',
							'₪'=>'Shekel',
							'LKR'=>'Sri Lanka',
							'E'=>'Swazi Emalangeni',
							'kr'=>'Swedish Krona',
							'Fr'=>'Swiss Franc',
							'฿'=>'Thai Baht',
							'₺'=>'Turkish Lira',
							'AED'=>'UAE',
							'فلس'=>'UAE DIRHAM',
							'UGX'=>'Ugandan',
							'¥'=>'Yen, Yuan',
							'KZ'=>'Kwanzas',
							'DZD'=>'Algerian DINAR',
							'T$'=>'Tongan Paanga',
							'Rs'=>'Mauritian Rupee',
							'Tsh'=>'Tanzanian Shilling',
							'BHD'=>'Bahraini Dinar',
							'ع.د'=>'Iraqi dinar',
							'N$'=>'Namibian Dollar',
							'HKD'=>'Hong Kong Dollar', 
							'RD$'=>'Dominican Republic',
							'SRD'=>'Surinamese',
							'ZMW'=>'Zambia');
		return $returnData;
	}
}
?>