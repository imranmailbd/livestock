<?php
if(session_status() == PHP_SESSION_NONE){session_start();}
class PayPal{
	protected string $AUTH_TOKEN_URL, $PRODUCT_URL, $PLAN_URL, $SUBSCRIPTIONS_URL, $WEBHOOKS_URL, $CLIENT_ID, $CLIENT_SECRET;	
	
	protected $db;
	public function __construct($db){
		$this->db = $db;
		
		if(OUR_DOMAINNAME=='machouse.com.bd'){
			$this->AUTH_TOKEN_URL = "http://api.sandbox.paypal.com/v1/oauth2/token";
			$this->PRODUCT_URL = "http://api.sandbox.paypal.com/v1/catalogs/products";
			$this->PLAN_URL = "http://api.sandbox.paypal.com/v1/billing/plans";
			$this->SUBSCRIPTIONS_URL = "http://api.sandbox.paypal.com/v1/billing/subscriptions";
			$this->WEBHOOKS_URL = "http://api.sandbox.paypal.com/v1/notifications/webhooks";
			$this->CLIENT_ID = "AXekAl1sQtBPZdsA0ptH_xU0YLU5fqMXhRnAb0-pqSVeyP68sIKlkhelU3L-tjaUQP8IZ4jUrfKgQHoP";
			$this->CLIENT_SECRET = "EHEycvl86gXuDkWDFWDW3Y6XYW36BRaZcA8txRLWs51SIqQ6SNHUD02bvyyzDRgHJnxlFa9VpdS1uJT3";
		}
		else{
			$this->AUTH_TOKEN_URL = "http://api.sandbox.paypal.com/v1/oauth2/token";
			$this->PRODUCT_URL = "http://api.sandbox.paypal.com/v1/catalogs/products";
			$this->PLAN_URL = "http://api.sandbox.paypal.com/v1/billing/plans";
			$this->SUBSCRIPTIONS_URL = "http://api.sandbox.paypal.com/v1/billing/subscriptions";
			$this->WEBHOOKS_URL = "http://api.sandbox.paypal.com/v1/notifications/webhooks";
			$this->CLIENT_ID = "AXekAl1sQtBPZdsA0ptH_xU0YLU5fqMXhRnAb0-pqSVeyP68sIKlkhelU3L-tjaUQP8IZ4jUrfKgQHoP";
			$this->CLIENT_SECRET = "EHEycvl86gXuDkWDFWDW3Y6XYW36BRaZcA8txRLWs51SIqQ6SNHUD02bvyyzDRgHJnxlFa9VpdS1uJT3";
		}
	}
	
	public function getAccessToken($segment4name) {
		$accounts_id = $segment4name;
		$returnStr = 'PayPal could not response.';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->AUTH_TOKEN_URL);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSLVERSION , 6); //NEW ADDITION
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_USERPWD, $this->CLIENT_ID.":".$this->CLIENT_SECRET);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
		$result = curl_exec($ch);
		$responseObj = json_decode($result);
	    if(empty($responseObj)){}
		else{
		    date_default_timezone_set('Asia/Dhaka');
			$variables_id = 0;
			$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=6 AND name='PayPal'", array());
			if($queryObj){
				$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
			}
			$valueData = array();
			$valueData['access_token'] = $responseObj->access_token;
			$valueData['expires_in'] = time()+intval($responseObj->expires_in);

			$value = serialize($valueData);
			$data=array('accounts_id'=>6,
				'name'=>'PayPal',
				'value'=>$value,
				'last_updated'=> date('Y-m-d H:i:s'));
			if($variables_id==0){
				$variables_id = $this->db->insert('variables', $data);
				if($variables_id){
					$returnStr = 'Ok';
				}
				else{
					$returnStr = 'PayPal Info could not insert.';
				}
			}
			else{
				$update = $this->db->update('variables', $data, $variables_id);
				if($update){
					$returnStr = 'Ok';
				}
				else{
					$returnStr = 'PayPal Info could not update.';
				}
			}
			$timezone = 'Asia/Dhaka';
			if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
			date_default_timezone_set($timezone);
		}
		return $returnStr;
	}
	
	public function checkGetAccessToken($recall=0){
	    $recall++;
		$returnAccToken = '';
		$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id=6 AND name='PayPal'", array());
		if($queryObj){
			$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$paypalInfo = unserialize($value);
				if(!empty($paypalInfo)){
					$access_token = $paypalInfo['access_token'];
					$expires_in = $paypalInfo['expires_in'];
					if(!empty($access_token) && $expires_in>time()){
						$returnAccToken = $access_token;
					}
				}
			}
		}
		
		if($returnAccToken == '' && $recall==1){
		    $this->getAccessToken(6);
			$this->checkGetAccessToken($recall);			
		}
		else{
		    return $returnAccToken;
		}
	}
	
	public function loadTableRowsPayPal($access_token) {
		$returnStr = '';
		
		$allData = $this->showPayPalProducts($access_token);
		if(!empty($allData)){
			foreach($allData as $id=>$oneItemRow){
				$type = 'Products';
				$name = $oneItemRow['name'];
				$description = $oneItemRow['description'];
				
				$returnStr .= "<tr>
							<td data-title=\"Name\" align=\"center\">$name</td>
							<td data-title=\"Description\" align=\"center\">$description</td>
							<td data-title=\"Type\" align=\"center\">$type</td>
							<td data-title=\"Currency\" align=\"center\">&nbsp;</td>
							<td data-title=\"Amount\" align=\"center\">&nbsp;</td>
							<td data-title=\"Interval Unit\" align=\"center\">&nbsp;</td>
							<td data-title=\"Description\" align=\"center\">
								<a class=\"anchorfulllink\" title=\"Edit\" onClick=\"showInfoPayPal('$type', '$id', '', '$name', '$description', '', '', '');\"><i class=\"fa fa-edit\"></i></a>
							</td>
						</tr>";
					
			}
			
			$allData = $this->showPayPalPlans($access_token);
			if(!empty($allData)){
				foreach($allData as $id=>$oneItemRow){
					$type = 'plans';
					$name = $oneItemRow['name'];
					$description = $oneItemRow['description'];
					$product_id = $oneItemRow['product_id'];
					$status = $oneItemRow['status'];
					$currency_code = $oneItemRow['currency_code'];
					$price_value = $oneItemRow['price_value'];
					$interval_unit = $oneItemRow['interval_unit'];
					
					if($status=='ACTIVE'){
						$hideIcon = "<a class=\"txt18bold cursor\" title=\"Change to INACTIVE\" onClick=\"planAction('ACTIVE', '$id', '$name', '$description');\"><i class=\"fa fa-check\"></i></a>";
					}
					else{
						$hideIcon = "<a class=\"txt18bold cursor txtred\" title=\"Change to ACTIVE\" onClick=\"planAction('INACTIVE', '$id', '$name', '$description');\"><i class=\"fa fa-remove\"></i></a>";
					}
					$returnStr .= "<tr>
							<td data-title=\"Name\" align=\"center\">$name</td>
							<td data-title=\"Description\" align=\"center\">$description</td>
							<td data-title=\"Type\" align=\"center\">$type ($status)</td>
							<td data-title=\"Currency\" align=\"center\">$currency_code</td>
							<td data-title=\"Amount\" align=\"center\">$price_value</td>
							<td data-title=\"Interval Unit\" align=\"center\">$interval_unit</td>
							<td data-title=\"Description\" align=\"center\"
								<a class=\"txt18bold cursor\" title=\"Edit\" onClick=\"showInfoPayPal('$type', '$id', '$product_id', '$name', '$description', '$currency_code', '$price_value', '$interval_unit');\"><i class=\"fa fa-edit\"></i></a> $hideIcon
							</td>
						</tr>";	
				}
			}			
		}
		else{
			$returnStr .= "<tr>
							<td data-title=\"Name\" align=\"center\" colspan=\"4\">There is no plans / products found.</td>
						</tr>";
		}
		
		return $returnStr;
	}
	
	public function showPayPalProducts($access_token) {
		$allData = array();
		if($access_token !=''){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->PRODUCT_URL."?page_size=20&page=1&total_required=true");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
			$result = curl_exec($ch);
			$response = json_decode($result);
		
			if(!empty($response) && isset($response->products)){
				$products = $response->products;
				if(!empty($products)){
					foreach($products as $oneProdRow){
						//if($oneProdRow->description !='HIDDEN'){
							$allData[$oneProdRow->id] = array('name'=>$oneProdRow->name, 'description'=>$oneProdRow->description);
						//}
					}
				}
			}			
			curl_close($ch);
		}
		return $allData;
	}
	
	public function showPayPalPlans($access_token, $fromApps = 0){
		$allData = array();
		if($access_token !=''){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->PLAN_URL."?page_size=20&page=1&total_required=true");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION , 6); //NEW ADDITION
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
			$result = curl_exec($ch);
			if($result){

				$response = json_decode($result);
				if(!empty($response) && isset($response->plans)){
				
					$plans = $response->plans;
					$allPlanData = array();
					foreach($plans as $onePlanRow){
						$allPlanData[$onePlanRow->id] = array('name'=>$onePlanRow->name, 'description'=>$onePlanRow->description);
					}
					
					if(!empty($allPlanData)){
						foreach($allPlanData as $id=>$oneItemRow){
							$planInfo = $this->showPlanDetails($access_token, $id);
							if(!empty($planInfo) && is_array($planInfo)){
								$allData[$id] = array('name'=>$oneItemRow['name'], 'description'=>$oneItemRow['description'], 'product_id'=>$planInfo['product_id'], 'status'=>$planInfo['status'], 'currency_code'=>$planInfo['currency_code'], 'price_value'=>$planInfo['price_value'], 'interval_unit'=>$planInfo['interval_unit']);
							}
						}
					}
				}
			}			
			curl_close($ch);
		}
		if($fromApps==1){
			$newData = array();
			if(!empty($allData)){
				foreach($allData as $id=>$oneItemRow){
					if($oneItemRow['status']=='ACTIVE'){
						$newData[$id] = $oneItemRow;
					}
				}
			}
			if(!empty($newData)){$allData = $newData;}
		}
		return $allData;
	}
	
	public function showPayPalSubscr($access_token, $subscriptionID){
		$allData = array();
		if(!empty($access_token) && !empty($subscriptionID)){
			$url = $this->SUBSCRIPTIONS_URL."/$subscriptionID";
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION , 6); //NEW ADDITION
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
			$result = curl_exec($ch);
			if($result){
				$response = json_decode($result);
		
				if(!empty($response)){
					$allData = (array) $response;
				}
			}			
			curl_close($ch);
		}
		return $allData;
	}
	
	public function showPlanDetails($access_token, $id){
		$allData = array();
		if($access_token !='' && $id !=''){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->PLAN_URL."/$id");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
			$result = curl_exec($ch);
			$response = json_decode($result);
			
			if(!empty($response)){
				$allData['id'] = $response->id;
				$allData['product_id'] = $response->product_id;
				$allData['name'] = $response->name;
				$allData['description'] = $response->description;
				$allData['status'] = $response->status;
				$allData['currency_code'] = 'USD';
				$allData['price_value'] = '29.99';
				$allData['interval_unit'] = 'MONTH';
				$billing_cycles = (array)$response->billing_cycles;
				if(!empty($billing_cycles)){
					$billing_cycles = $billing_cycles[0];
					if(property_exists($billing_cycles, 'pricing_scheme')){
						$pricing_scheme = $billing_cycles->pricing_scheme;
						if(property_exists($pricing_scheme, 'fixed_price')){
							$fixed_price = $pricing_scheme->fixed_price;
							if(!empty($fixed_price)){
								$fixed_price = (array)$fixed_price;
								if(array_key_exists('currency_code', $fixed_price)){
									$allData['currency_code'] = $fixed_price['currency_code'];
								}
								if(array_key_exists('value', $fixed_price)){
									$allData['price_value'] = $fixed_price['value'];
								}
							}
						}
					}
					if(property_exists($billing_cycles, 'frequency')){
						$frequency = $billing_cycles->frequency;
						if(!empty($frequency)){
							$frequency = (array)$frequency;
							if(array_key_exists('interval_unit', $frequency)){
								$allData['interval_unit'] = $frequency['interval_unit'];
							}
						}
					}
				}			
			}			
			curl_close($ch);
		}
		return $allData;
	}
	
	public function updatePlanAction(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$actionType = trim((string) $POST['actionType']??'deactivate');
		$id = trim((string) $POST['id']??'');
		$access_token = trim((string) $POST['access_token']??'');
		
		$returnStr = 'error';
		if($access_token !='' && $id !=''){
			$url = $this->PLAN_URL."/$id";
			$ch = curl_init();
			if($actionType=='deleted'){
				//$url .= "/deleted";
				curl_setopt($ch, CURLOPT_POST, true);
				
				$data = $updateArray = array();
				$updateArray['op'] = 'replace';
				$updateArray['path'] = "/";
				$updateArray['value'] = array('state'=>'DELETED');
				
				$data[] = $updateArray;
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				//return "Url: $url, ".json_encode($data);
				
			}
			else{
				$url .= "/$actionType";
				curl_setopt($ch, CURLOPT_POST, true);
			}
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
			
			$result = curl_exec($ch);
			$response = json_decode($result);
			
			if(!empty($response)){
				$returnStr = json_encode($response);
			}
			else{
				$returnStr = '';
			}
			curl_close($ch);
		}
		return $returnStr;
	}
	
	public function AJsavePayPal(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$itemType = trim((string) $POST['itemType']??'plans');
		$name = trim((string) $POST['name']??'');
		$description = trim((string) $POST['description']??'');
		$currency = trim((string) $POST['currency']??'USD');
		$amount = trim((string) $POST['amount']??'29.99');
		$interval_unit = trim((string) $POST['interval_unit']??'MONTH');
		$type = trim((string) $POST['type']??'SERVICE');
		$product_id = trim((string) $POST['product_id']??'');
		$id = trim((string) $POST['id']??'');
		$op = trim((string) $POST['op']??'add');
		$access_token = trim((string) $POST['access_token']??'');
		
		$data = array();
		$data['name'] = $name;
		$data['description'] = $description;
		if($itemType=='plans'){
			$data['product_id'] = $product_id;
			$data['status'] = 'ACTIVE';
			$data['usage_type'] = 'LICENSED';
			/*
			$data['billing_cycles'][] = array('frequency'=>array('interval_unit'=>'MONTH','interval_count'=>1),
											'tenure_type'=>'TRIAL',
											'sequence'=>1,
											'total_cycles'=>0,
											'pricing_scheme'=>array('fixed_price'=>array('value'=>"0", 'currency_code'=>$currency))
											);
			*/
			//==========For for real time transaction==========//
			$data['billing_cycles'][] = array('frequency'=>array('interval_unit'=>$interval_unit,'interval_count'=>1),
											'tenure_type'=>'REGULAR',
											'sequence'=>1,
											'total_cycles'=>0,
											'pricing_scheme'=>array('fixed_price'=>array('value'=>"$amount", 'currency_code'=>$currency))
											);
					
			$data['payment_preferences'] = array('auto_bill_outstanding'=>true,
			'setup_fee'=>array('value'=>"0.00", 'currency_code'=>$currency),
			'setup_fee_failure_action'=>'CONTINUE', 'payment_failure_threshold'=>1);
			$data['taxes'] = array('percentage'=>"0", 'inclusive'=>false);
		}
		else{
			$data['type'] = $type;
			$data['category'] = 'SOFTWARE';		
		}
		
		if($itemType=='plans'){$url = $this->PLAN_URL;}
		else{$url = $this->PRODUCT_URL;}
		$returnStr = 'Add';
		if(strlen($id)>1){
			$url .="/$id";
			$returnStr = 'Update';
		}
		$headerArray = array("Content-Type: application/json", "Authorization: Bearer $access_token");
		if($op=='add'){
			$headerArray[] = "PayPal-Request-Id: $itemType-".date('Ymd-His');
		}
		
		if($op=='replace'){
			$url .= '/update-pricing-schemes';
			$oldData = $data;
			$data = $updateArray = array();
			$updateArray['op'] = $op;
			$updateArray['path'] = "/description";
			$updateArray['value'] = $oldData['description'];			
			$data[] = $updateArray;
			
			//==========Update pricing_schemes===============//
			$data = array();
			$fixedPriceVal = $oldData['billing_cycles'][0]['pricing_scheme']['fixed_price']['value'];
			$fixedPriceCurrency = $oldData['billing_cycles'][0]['pricing_scheme']['fixed_price']['currency_code'];
			$data['pricing_schemes'][] = array('billing_cycle_sequence'=>1,
				'pricing_scheme'=>array('fixed_price'=>array('value'=>$fixedPriceVal, 'currency_code'=>$fixedPriceCurrency)));
		}
		
		//return "Url: $url, ".json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
		
		if(in_array($op, array('deleted'))){//'replace', 
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
		}
		else{
			curl_setopt($ch, CURLOPT_POST, true);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		$result = curl_exec($ch);
		$response = json_decode($result);
		
		if(empty($response)){
			$returnStr = 'error';
		}
		else{
			if(isset($response->message)){
				$returnStr = $response->message;
			}
		}
		return $returnStr;
	}
	
	public function subscribePayPal(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$access_token = trim((string) $POST['access_token']??'');
		$id = $plan_id = trim((string) $POST['plan_id']??'');
		$op = $POST['op']??'add';
		$currency_code = $POST['currency_code']??'USD';
		$price_value = '29.99';//$POST['price_value']??'29.99';
		$email_address = $POST['email_address']??'';
		$this->db->writeIntoLog(json_encode($POST));
		$showPlanDetails = $this->showPlanDetails($access_token, $plan_id);
		if(!empty($showPlanDetails)){
			if(is_array($showPlanDetails) && array_key_exists('currency_code', $showPlanDetails)){
				$currency_code = $showPlanDetails['currency_code'];
			}
			if(is_array($showPlanDetails) && array_key_exists('price_value', $showPlanDetails)){
				$price_value = $showPlanDetails['price_value'];
			}
		}
		
		$given_name = $_SESSION["company_name"]??'';
		$subdomain = $GLOBALS['subdomain'];
		
		date_default_timezone_set('Asia/Dhaka');
		$next_payment_due = date('Y-m-d');
		$accounts_id = 0;
		$accSql = "SELECT accounts_id, customer_service_email, pay_frequency, next_payment_due FROM accounts WHERE company_subdomain='$subdomain'";
		$queryObj = $this->db->query($accSql, array());
		if($queryObj){
			$accountsRow = $queryObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accountsRow->accounts_id;
			$next_payment_due = $accountsRow->next_payment_due;
			if(in_array($next_payment_due, array('0000-00-00', '1000-01-01'))){$next_payment_due = date('Y-m-d');}
			
			$email_address = $accountsRow->customer_service_email;
			$pay_frequency = $accountsRow->pay_frequency;
		}	
		$data = array();
		$data['plan_id'] = $plan_id;
		if($op=='add'){
			if(strtotime($next_payment_due)>time()){
				date_default_timezone_set('UTC');
				$startTime = date('Y-m-d\TH:i:s\Z', strtotime($next_payment_due));
			}
			else{
				date_default_timezone_set('UTC');
				$startTime = date('Y-m-d\TH:i:s\Z', time()+10);
				
			}		
			$data['start_time'] = "$startTime";
		}
		date_default_timezone_set('Asia/Dhaka');
		
		$data['quantity'] = 1;
		$data['shipping_amount'] = array('currency_code'=>$currency_code, 'value'=>'0.00');
		
		$data['subscriber'] = array(
			'name'=>array('given_name'=>$given_name, 'surname'=>$subdomain), 
			'email_address'=>$email_address,
			/*'shipping_address'=>array(
				'name'=>array('full_name'=>'Abdus Shobhan'),
				'address'=>array('address_line_1'=>'2211 N First Street', 
					'address_line_2'=>'Building 17', 
					'admin_area_2'=>'San Jose', 
					'admin_area_1'=>'CA', 
					'postal_code'=>'95131', 
					'country_code'=>'US')
				)*/
			);
		$data['auto_renewal'] = true;
		$return_url = $cancel_url = 'http://'.$subdomain.'.'.OUR_DOMAINNAME;
		$return_url .= '/PayPal/returnSubscribe';
		$cancel_url .= '/PayPal/cancelSubscribe';
		$data['application_context'] = array(
			'brand_name'=>COMPANYNAME,
			'locale'=>'en-US',
			//'shipping_preference'=>'SET_PROVIDED_ADDRESS',
			'user_action'=>'SUBSCRIBE_NOW',
			'payment_method'=>array(
			  'payer_selected'=>'PAYPAL',
			  'payee_preferred'=>'IMMEDIATE_PAYMENT_REQUIRED'),
			'return_url'=>$return_url,
			'cancel_url'=>$cancel_url
			);
			
		$timezone = 'Asia/Dhaka';
		if(isset($_SESSION["timezone"])){
			$timezone = $_SESSION["timezone"];
		}
		date_default_timezone_set($timezone);
		
		$returnStr = 'Add';
		$subsId = $status = '';
		if($access_token !='' && $id !=''){
			$url = $this->SUBSCRIPTIONS_URL;
			$headerArray = array("Authorization: Bearer $access_token");
			//$headerArray[] = "Accept: application/json";
			if($op=='add'){
				$headerArray[] = "PayPal-Request-Id:SUBSCRIPTION-".date('Ymd-His');
				//$headerArray[] = "Prefer: return=representation";
				$headerArray[] = "Content-Type: application/json";
			}
			elseif($op=='replace'){
				$url .="/$id";
				$oldData = $data;
				$data = $updateArray = array();
				$updateArray['op'] = $op;
				$updateArray['path'] = "/description";
				$updateArray['value'] = $oldData['description'];
				
				$data[] = $updateArray;
			}
			//return "Url: $url, ".json_encode($headerArray).json_encode($data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
			if($op=='replace'){
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
			}
			else{
				curl_setopt($ch, CURLOPT_POST, true);
			}
		    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			$result = curl_exec($ch);
			$response = json_decode($result);
			
			if(empty($response) && $returnStr=='Add'){
				$returnStr = 'error';
			}
			else{
				if(isset($response->message)){
					$returnStr = $response->message;
				}
				elseif(isset($response->id)){
					$subsId = $response->id;
					
					if($accounts_id>0){
						/*
						//==========Update on 03/03/2023===========//
						$accountsData = array();
						$accountsData['paypal_id'] = $subsId;
						$update = $this->db->update('accounts', $accountsData, $accounts_id);
						if($update){								
							$returnStr='Ok';
						}
						*/
						$returnStr='Ok';
						
						if(isset($response->status)){$status = " Status: $response->status";}
						//=============Add Data into our_notes=======//
						date_default_timezone_set('Asia/Dhaka');
						$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
											'accounts_id'=>$accounts_id,
											'description'=>"New Subscription Added.$status, ID: $subsId");
						$this->db->insert('our_notes', $our_notesData);
						$timezone = 'Asia/Dhaka';
						if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
						date_default_timezone_set($timezone);
					}
				}
			}
		}
		return json_encode(array('login'=>'', 'status'=>$status, 'subsId'=>$subsId, 'returnStr'=>$returnStr));
	}
	
	public function subscriptionDetails($access_token, $id){
		$allData = array();
		if($access_token !='' && $id !=''){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->SUBSCRIPTIONS_URL."/$id");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
			$result = curl_exec($ch);
			$response = json_decode($result);
			
			if(!empty($response)){
				$allData = (array) $response;
			}			
			curl_close($ch);
		}
		return $allData;
	}

	public function subscribeAction(){
		if(!isset($_SESSION["prod_cat_man"])){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$returnStr = 'error';
			$access_token = trim((string) $POST['access_token']??'');
			$planId = trim((string) $POST['planId']??'');
			$id = trim((string) $POST['id']??'');
			$qty = trim((string) $POST['qty']??1);
			$action = trim((string) $POST['action']??'');
			
			if($action=='transactions'){
				$returnStr = $this->transactionsList($access_token, $id);
			}
			elseif($access_token !='' && $id !=''){
				
				$accounts_id = $_SESSION["accounts_id"]??0;
				
				$url = $this->SUBSCRIPTIONS_URL."/$id/$action";
				
				$headerArray = array("Content-Type: application/json", "Authorization: Bearer $access_token");
				if($action=='capture'){
					$headerArray[] = "PayPal-Request-Id:CAPTURE-".date('Ymd-His');
				}
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSLVERSION, 6);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
				
				curl_setopt($ch, CURLOPT_POST, true);
				$subdomain = $GLOBALS['subdomain'];
				
				if($action=='revise'){
					
					$data = array();
					$data['plan_id'] = $planId;
					$return_url = $cancel_url = 'http://'.$subdomain.'.'.OUR_DOMAINNAME;
					$return_url .= '/PayPal/returnSubscribe';
					$cancel_url .= '/PayPal/cancelSubscribe';
					$data['application_context'] = array(
						'brand_name'=>COMPANYNAME,
						'locale'=>'en-US',
						'shipping_preference'=>'SET_PROVIDED_ADDRESS',
						'payment_method'=>array(
						  'payer_selected'=>'PAYPAL',
						  'payee_preferred'=>'IMMEDIATE_PAYMENT_REQUIRED'),
						'return_url'=>$return_url,
						'cancel_url'=>$cancel_url
						);
					$reason = 'Quantity Updated to '.$qty;
				}
				elseif($action=='capture'){
					$reason = 'Charging as the balance reached the limit';
					$data = array('note'=>$reason,
								'capture_type'=>"OUTSTANDING_BALANCE",
								'amount'=>array(
									'currency_code'=>"USD",
									'value'=>"29.99"
								));
				}
				else{
					if($action=='activate'){$reason = 'Reactivating the subscription';}
					elseif($action=='cancel'){$reason = 'Subscription Cancel';}
					elseif($action=='suspend'){$reason = 'Subscription Suspend';}
					$data = array('reason'=>$reason);
					
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				
				$result = curl_exec($ch);
				$response = json_decode($result);
				
				if(isset($response->message)){
					$returnStr = $response->message;
				}
				else{
					//=============Add Data into our_notes=======//
					date_default_timezone_set('Asia/Dhaka');
					$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
										'accounts_id'=>$accounts_id,
										'description'=>"$reason, ID: $id");
					$this->db->insert('our_notes', $our_notesData);
					$timezone = 'Asia/Dhaka';
					if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
					date_default_timezone_set($timezone);
					
					if($action=='cancel' || $action=='suspend'){				
						$this->db->update('accounts', array('paypal_id'=>''), $accounts_id);
					}
					$returnStr = 'Ok';
				}
				
				curl_close($ch);
			}
			return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
		}
	}
	
	public function transactionsList($access_token, $id){
		$returnStr = '';
		if($access_token !='' && $id !=''){
			$accounts_id = $_SESSION["accounts_id"]??0;
			date_default_timezone_set('Asia/Dhaka');
			$dt = new DateTime('2019-01-01T00:00:00Z');
			$dt->setTimezone(new DateTimeZone('UTC'));
			$start_time = $dt->format('Y-m-d\TH:i:s.B\Z');
			$dt = new DateTime();
			$dt->setTimezone(new DateTimeZone('UTC'));
			$end_time = $dt->format('Y-m-d\TH:i:s.B\Z');
			
			$url = $this->SUBSCRIPTIONS_URL."/$id/transactions?start_time=$start_time&end_time=$end_time";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
			
			$result = curl_exec($ch);
			$response = json_decode($result);
			
			if(isset($response->message)){
				$returnStr = $response->message;
			}
			elseif(!empty($response)){
				$allData = (array) $response->transactions;
				$l=0;
				foreach($allData as $oneTransaction){
					$l++;
					$oneTransaction = (array) $oneTransaction;
					
					$status = $oneTransaction['status'];
					$id = $oneTransaction['id'];
					$amount_with_breakdown = (array) $oneTransaction['amount_with_breakdown'];
					$payer_name = $oneTransaction['payer_name'];
					$payer_email = $oneTransaction['payer_email'];
					$time = date('m/d/Y H:i', strtotime($oneTransaction['time']));
					$returnStr .= "<tr>
								<td data-title=\"SL#\" align=\"center\">$l</td>
								<td data-title=\"Date Time\" align=\"left\">
									$time
								</td>
								<td data-title=\"ID\" align=\"left\">
									$id
								</td>
								<td nowrap data-title=\"Amount\" align=\"center\">
									$000 USD
								</td>
								<td data-title=\"Status\" align=\"center\">
									$status
								</td>
							</tr>";
				}
			}
			
			curl_close($ch);
		}
		if($returnStr==''){
			$returnStr = "<tr>
							<td colspan=\"5\" data-title=\"SL#\" align=\"center\">There is no data found.</td>
						</tr>";
		}
		
		$timezone = 'Asia/Dhaka';
		if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
		date_default_timezone_set($timezone);
		return $returnStr;
	}
	
	private function loadTableRowsWebhooks($access_token) {
		$returnStr = '';
		
		$allData = $this->showPayPalWebhooks($access_token);
		if(!empty($allData)){
			foreach($allData as $id=>$oneItemRow){
				
				$url = $oneItemRow['url'];
				$event_types = (array) $oneItemRow['event_types'];
				if(!empty($event_types)){
					$l=0;
					$rowSpan = count($event_types);
					$nameArray = array();
					foreach($event_types as $oneRow){
						$oneRow = (array) $oneRow;						
						$nameArray[] = str_replace('PAYMENT.AUTHORIZATION.', '', $oneRow['name']);
					}
					$nameArrayStr = implode(',', $nameArray);
					foreach($event_types as $oneRow){
						$l++;
						$oneRow = (array) $oneRow;
						
						$name = $oneRow['name'];
						$description = $oneRow['description'];
						$status = $oneRow['status'];
						if($l==1){
							$returnStr .= "<tr>
									<td data-title=\"Name\" align=\"center\">$name</td>
									<td data-title=\"Description\" align=\"center\">$description</td>
									<td rowspan=\"$rowSpan\" data-title=\"URL\" align=\"center\">$url</td>
									<td rowspan=\"$rowSpan\" data-title=\"Action\" align=\"left\">
										<a class=\"txt18bold\" title=\"Edit\" onClick=\"showWebhooksInfo('$id', '$nameArrayStr');\"><i class=\"fa fa-edit\"></i></a> 
										<a class=\"txt18bold txtred\" title=\"Remove Webhook\" onClick=\"removeWebhook('$id', '$nameArrayStr');\"><i class=\"fa fa-remove\"></i></a>
									</td>
								</tr>";							
						}
						else{
							$returnStr .= "<tr>
									<td data-title=\"Name\" align=\"center\">$name</td>
									<td data-title=\"Description\" align=\"center\">$description</td>
								</tr>";
						}
					}
				}
			}		
		}
		else{
			$returnStr .= "<tr>
							<td data-title=\"Name\" align=\"center\" colspan=\"4\">There is no webhooks found.</td>
						</tr>";
		}
		
		return $returnStr;
	}
	
	public function showPayPalWebhooks($access_token) {
		$allData = array();
		if($access_token !=''){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->WEBHOOKS_URL);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
			$result = curl_exec($ch);
			$response = json_decode($result);
			
			if(!empty($response)){
				$webhooks = $response->webhooks;
				if(!empty($webhooks)){
					$webhooks = (array)$webhooks;
					foreach($webhooks as $oneWebRow){
						$oneWebRow = (array)$oneWebRow;
						$allData[$oneWebRow['id']] = array('url'=>$oneWebRow['url'], 'event_types'=>$oneWebRow['event_types']);
					}
				}
			}			
			curl_close($ch);
		}
						
		return $allData;
	}
	
	public function AJsaveWebhooks(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$name = $POST['name']??array();
		$id = trim((string) $POST['id']??'');
		$op = trim((string) $POST['op']??'add');
		$access_token = trim((string) $POST['access_token']??'');
		
		$data = array();
		$data['url'] = 'http://'.OUR_DOMAINNAME.'/PayPal/notifyWebHooks';
		$returnStr = 'You have to choose at least one name.';
		if(!empty($name)){
			foreach($name as $oneName){
				$data['event_types'][] = array('name'=>'PAYMENT.AUTHORIZATION.'.$oneName);			
			}
		
			$url = $this->WEBHOOKS_URL;
			$returnStr = 'Add';
			if(strlen($id)>1){
				$url .="/$id";
				$returnStr = 'Update';
			}
			$headerArray = array("Content-Type: application/json", "Authorization: Bearer $access_token");
			if($op=='replace'){
				$oldData = $data;
				$data = $updateArray = array();
				$updateArray['op'] = $op;
				$updateArray['path'] = "/url";
				$updateArray['value'] = $oldData['url'];			
				$data[] = $updateArray;
				$updateArray = array();
				$updateArray['op'] = $op;
				$updateArray['path'] = "/event_types";
				$updateArray['value'] = $oldData['event_types'];			
				$data[] = $updateArray;
			}
			//return "Url: $url, ".json_encode($headerArray).json_encode($data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
			if($op=='replace'){
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
			}
			else{
				curl_setopt($ch, CURLOPT_POST, true);
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			$result = curl_exec($ch);
			$response = json_decode($result);
			
			if(empty($response) && $returnStr=='Add'){
				$returnStr = 'error';
			}
			else{
				if(isset($response->message)){
					$returnStr = $response->message;
				}
			}
		}
		return $returnStr;
	}
	
	public function removeWebhook(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$access_token = trim((string) $POST['access_token']??'');
		$id = trim((string) $POST['id']??'');
	
		$returnStr = 'error';
		if($access_token !='' && $id !=''){
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			$url = $this->WEBHOOKS_URL."/$id";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			$result = curl_exec($ch);
			$response = json_decode($result);
			
			if(isset($response->message)){
				$returnStr = $response->message;
			}
			else{
				//=============Add Data into our_notes=======//
				date_default_timezone_set('Asia/Dhaka');
				$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
									'accounts_id'=>$accounts_id,
									'description'=>"Webhook has been removed. ID: $id");
				$this->db->insert('our_notes', $our_notesData);
				$this->db->update('accounts', array('paypal_id'=>''), $accounts_id);
				$timezone = 'Asia/Dhaka';
				if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
				date_default_timezone_set($timezone);
				
				$returnStr = 'Ok';
			}
			
			curl_close($ch);
		}
		return $returnStr;
	}
	
	public function AJget_Payments(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = trim((string) $POST['accounts_id']??'');
		date_default_timezone_set('Asia/Dhaka');
		$returnStr = '';
		$queryObj = $this->db->query("SELECT created_on, paypal_id FROM accounts WHERE accounts_id=$accounts_id", array());
		if($queryObj){
			$accOneRow = $queryObj->fetch(PDO::FETCH_OBJ);
			if($accOneRow){
				$created_on = $accOneRow->created_on;
				$paypal_id = $accOneRow->paypal_id;
				$access_token = $this->checkGetAccessToken();
				if($access_token !='' && $paypal_id !=''){
					$dt = new DateTime(date('Y-m-1',strtotime($created_on)-2592000));
					$dt->setTimezone(new DateTimeZone('UTC'));
					$start_time = $dt->format('Y-m-d\TH:i:s.B\Z');
					$dt = new DateTime();
					$dt->setTimezone(new DateTimeZone('UTC'));
					$end_time = $dt->format('Y-m-d\TH:i:s.B\Z');
				
					$url = $this->SUBSCRIPTIONS_URL."/$paypal_id/transactions?start_time=$start_time&end_time=$end_time";
					//return $url = $this->SUBSCRIPTIONS_URL."/$paypal_id/transactions";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					//curl_setopt($ch, CURLOPT_SSLVERSION, 6);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
					curl_setopt($ch, CURLOPT_HEADER, false);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $access_token"));
					
					$result = curl_exec($ch);
					$response = json_decode($result);
					
					if(isset($response->message)){
						$returnStr = $response->message;
					}
					elseif(!empty($response) && isset($response->transactions)){
						$allData = (array) $response->transactions;
						$l=0;
						foreach($allData as $oneTransaction){
							$l++;
							$oneTransaction = (array) $oneTransaction;
							
							$status = $oneTransaction['status'];
							$id = $oneTransaction['id'];
							$amount_with_breakdown = (array) $oneTransaction['amount_with_breakdown'];
							$currencyCode = 'USD';
							$amountValue = '';
							if(!empty($amount_with_breakdown) && array_key_exists('gross_amount', $amount_with_breakdown)){
								$gross_amount = (array) $amount_with_breakdown['gross_amount'];
								if(!empty($gross_amount) && array_key_exists('value', $gross_amount)){
									$currencyCode = $gross_amount['currency_code'];
									$amountValue = number_format($gross_amount['value'],2);
								}
							}
							$payer_name = $oneTransaction['payer_name'];
							$payer_email = $oneTransaction['payer_email'];
							$time = date('m/d/Y H:i', strtotime($oneTransaction['time']));
							$returnStr .= "<tr>
										<td data-title=\"SL#\" align=\"center\">$l</td>
										<td data-title=\"Date Time\" align=\"left\">
											$time
										</td>
										<td data-title=\"ID\" align=\"left\">
											$id
										</td>
										<td nowrap data-title=\"Amount\" align=\"center\">
											$amountValue $currencyCode
										</td>
										<td data-title=\"Status\" align=\"center\">
											$status
										</td>
									</tr>";
						}
					}
					
					curl_close($ch);	
				}
			}
		}
		if($returnStr==''){
			$returnStr = "<tr>
							<td colspan=\"5\" data-title=\"SL#\" align=\"center\">There is no data found.</td>
						</tr>";
		}
		$returnStr = '<div id="no-more-tables">
						<table class="col-md-12 table-bordered table-striped table-condensed cf listing">
							<thead class="cf">
								<tr>
									<th align="left" width="10%" nowrap>SL#</th>
									<th align="left" width="20%" nowrap>Date Time</th>
									<th align="left">ID</th>
									<th align="left" width="20%">Amount</th>
									<th align="left" width="20%">Status</th>
								</tr>
							</thead>
							<tbody>
								'.$returnStr.'
							</tbody>
						</table>
					</div>';
		
		$timezone = 'Asia/Dhaka';
		if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
		date_default_timezone_set($timezone);
		return $returnStr;
	}
	
	public function capturePayment(){
		if(!isset($_SESSION["prod_cat_man"])){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$paypal_id = trim((string) $POST['paypal_id']??'');
			$accounts_id = trim((string) $POST['accounts_id']??'');
			$amount = trim((string) $POST['amount']??29.99);
			
			$returnStr = 'error';
			$queryObj = $this->db->query("SELECT paypal_id FROM accounts WHERE accounts_id=$accounts_id", array());
			if($queryObj){
				$accOneRow = $queryObj->fetch(PDO::FETCH_OBJ);
				if($accOneRow){
					$paypal_id = $accOneRow->paypal_id;
					$access_token = $this->checkGetAccessToken();
					if($access_token !='' && $paypal_id !=''){
						
						$url = $this->SUBSCRIPTIONS_URL."/$paypal_id/capture";
						
						$headerArray = array("Content-Type: application/json", "Authorization: Bearer $access_token");//, "PayPal-Request-Id:CAPTURE-".date('Ymd-His')
						
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_SSLVERSION, 6);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
						curl_setopt($ch, CURLOPT_HEADER, false);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
						
						curl_setopt($ch, CURLOPT_POST, true);
						
						$reason = 'Charging as the balance reached the limit';
						$data = array('note'=>$reason,
										'capture_type'=>"OUTSTANDING_BALANCE",
										'amount'=>array(
											'currency_code'=>"USD",
											'value'=>"$amount"
										));
						curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
						
						$result = curl_exec($ch);
						$response = json_decode($result);
						
						if(isset($response->message)){
							$returnStr = $response->message;
						}
						else{
							//=============Add Data into our_notes=======//
							date_default_timezone_set('Asia/Dhaka');
							$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
												'accounts_id'=>$accounts_id,
												'description'=>"$reason, ID: $paypal_id");
							$this->db->insert('our_notes', $our_notesData);
							$timezone = 'Asia/Dhaka';
							if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
							date_default_timezone_set($timezone);
							
							$returnStr = 'Ok';
						}
						
						curl_close($ch);
					}
				}
			}
			return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
		}
	}
	
	public function addSubscriptionID(){
		$returnStr='';
		$subscriptionID = $GLOBALS['segment4name'];
		$subdomain = $GLOBALS['subdomain'];
			
		$queryObj = $this->db->query("SELECT accounts_id FROM accounts WHERE company_subdomain='$subdomain'", array());
		if($queryObj){
			$accounts_id = $queryObj->fetch(PDO::FETCH_OBJ)->accounts_id;
			$update = $this->db->update('accounts', array('paypal_id'=>$subscriptionID, 'status'=>'Active', 'status_date'=>date('Y-m-d H:i:s')), $accounts_id);
			if($update){
				$returnStr='Ok';
			}
			//=============Add Data into our_notes=======//
			date_default_timezone_set('Asia/Dhaka');
			$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
								'accounts_id'=>$accounts_id,
								'description'=>"New Subscription Added.");
			$this->db->insert('our_notes', $our_notesData);
			$timezone = 'Asia/Dhaka';
			if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
			date_default_timezone_set($timezone);
		}
		
		return "<meta http-equiv = \"refresh\" content = \"0; url = /Account/payment_details/$returnStr\" />";
	}
	
	public function returnSubscribe(){
		$id = isset($_GET['subscription_id']) ? trim((string) $_GET['subscription_id']):'';
		$ba_token = isset($_GET['ba_token']) ? trim((string) $_GET['ba_token']):'';
		$token = isset($_GET['token']) ? trim((string) $_GET['token']):'';
		$subdomain = $GLOBALS['subdomain'];
		
		$accounts_id = 0;
		$accSql = "SELECT accounts_id, next_payment_due FROM accounts WHERE company_subdomain='$subdomain' LIMIT 0,1";
		$queryObj = $this->db->query($accSql, array());
		if($queryObj){
			$accObj = $queryObj->fetch(PDO::FETCH_OBJ);
			$accounts_id = $accObj->accounts_id;
			$curnext_payment_due = $accObj->next_payment_due;
			//$access_token = $this->db->checkGetAccessToken();
			//$allData = $this->db->showPayPalSubscr($access_token, $id);
			//if(!empty($allData)){
				$next_payment_due = date('Y-m-d');
				if(strtotime($curnext_payment_due)>strtotime(date('Y-m-d'))){
					$next_payment_due = $curnext_payment_due;
				}
				$update = $this->db->update('accounts', array('status'=>'Active', 'next_payment_due'=>$next_payment_due, 'status_date'=>date('Y-m-d H:i:s')), $accounts_id);
				//$pstatus = $allData['status'];
				date_default_timezone_set('Asia/Dhaka');
				$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
									'accounts_id'=>$accounts_id,
									'description'=>"Subscription status changes to Active, ID: $id");
				$this->db->insert('our_notes', $our_notesData);
				$timezone = 'Asia/Dhaka';
				if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
				date_default_timezone_set($timezone);
										
			//}
		}
		
		return "<meta http-equiv = \"refresh\" content = \"0; url = /Account/payment_details\" />";
	}

	public function cancelSubscribe(){
		$id = isset($_GET['subscription_id']) ? trim((string) $_GET['subscription_id']):'';
		$ba_token = isset($_GET['ba_token']) ? trim((string) $_GET['ba_token']):'';
		$token = isset($_GET['token']) ? trim((string) $_GET['token']):'';
		$subdomain = $GLOBALS['subdomain'];
		
		$accounts_id = 0;
		$accSql = "SELECT accounts_id FROM accounts WHERE company_subdomain='$subdomain'";
		$queryObj = $this->db->query($accSql, array());
		if($queryObj){
			$accounts_id = $queryObj->fetch(PDO::FETCH_OBJ)->accounts_id;
			
			date_default_timezone_set('Asia/Dhaka');
			
			$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
								'accounts_id'=>$accounts_id,
								'description'=>"Subscription status Cancel ID: $id");
			$this->db->insert('our_notes', $our_notesData);
			$timezone = 'Asia/Dhaka';
			if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
			date_default_timezone_set($timezone);
					
		}
		
		return "<meta http-equiv = \"refresh\" content = \"0; url = /Account/payment_details\" />";
	}

	public function notifyWebHooks(){
		$message = file_get_contents('php://input');
		if($message !=''){
          $fileName = './paypal_webhook_log';
          if(is_array($message)){$message = implode(', ', $message);}
          file_put_contents($fileName, date('Y-m-d H:i:s')." $message\n",FILE_APPEND);
       	}
       	$bodyReceived = json_decode($message, true);
		
		if(is_array($bodyReceived)){
			$state = $currency = '';
			$total = 0.00;

			$id = trim((string) $bodyReceived['id']??'');
			$event_type = trim((string) $bodyReceived['event_type']??'');
			$summary = trim((string) $bodyReceived['summary']??'');
			$resource = $bodyReceived['resource']??array();
			if(!empty($resource)){
				$resourceID = trim((string) $resource['id']??'');
				if(in_array($event_type, array('PAYMENT.SALE.COMPLETED', 'PAYMENT.SALE.PENDING'))){
					$state = trim((string) $resource['state']??'');
					$amount = $resource['amount']??array();
					if(!empty($amount)){
						$total = floatval(trim((string) $amount['total']??0.00));
						$currency = trim((string) $amount['currency']??'USD');
					}
					
					$paypal_id = trim((string) $resource['billing_agreement_id']??'');
					$id = $resourceID;

					if($event_type=='PAYMENT.SALE.PENDING'){
						$clearing_time = trim((string) $resource['clearing_time']??'');
						if(!empty($clearing_time)){
							$summary .=', Clearing Time: '.sub_str($clearing_time, 0, 10);
						}
					}
				}
				else{
					$paypal_id = $resourceID;
				}
			}
			else{
				$paypal_id = $id;
			}
			
			$accounts_id = 0;
			if(!empty($paypal_id)){
				$accSql = "SELECT accounts_id FROM accounts WHERE paypal_id =:paypal_id";
				$queryObj = $this->db->query($accSql, array('paypal_id'=>$paypal_id));
				if(!$queryObj){
					$accSql = "SELECT accounts_id FROM accounts WHERE paypal_id =:paypal_id";
					$queryObj = $this->db->query($accSql, array('paypal_id'=>$paypal_id));
				}
				if(!$queryObj){
					$accSql = "SELECT accounts_id FROM our_notes WHERE description LIKE CONCAT('%', :paypal_id, '%')";
					$queryObj = $this->db->query($accSql, array('paypal_id'=>'ID: '.$paypal_id));
				}
				
				if($queryObj){
					$accounts_id = intval($queryObj->fetch(PDO::FETCH_OBJ)->accounts_id);
				}
			}

			if($accounts_id>0){
				//==============Inserting into our_notes table====================//
				$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
										'accounts_id'=>$accounts_id,
										'description'=>"$summary ($event_type), PayPal ID: $paypal_id, ID: $id");
				$this->db->insert('our_notes', $our_notesData);
				if($event_type=='BILLING.SUBSCRIPTION.CANCELLED'){
					$this->db->update('accounts', array('status'=>'Pending', 'paypal_id'=>'', 'status_date'=>date('Y-m-d H:i:s')), $accounts_id);
				}

				if($event_type=='PAYMENT.SALE.COMPLETED' && $state=='completed' && $total>0){
					$accSql = "SELECT company_subdomain, price_per_location, pay_frequency, next_payment_due, status FROM accounts WHERE accounts_id = $accounts_id";
					$queryObj = $this->db->query($accSql, array());
					if($queryObj){
						$accountsRow = $queryObj->fetch(PDO::FETCH_OBJ);
						
						$subdomain = $accountsRow->company_subdomain;
						$next_payment_due = $accountsRow->next_payment_due;
						$price_per_location = $accountsRow->price_per_location;
						$pay_frequency = $accountsRow->pay_frequency;
						$status = $accountsRow->status;
						
						date_default_timezone_set('Asia/Dhaka');
						$date = date('Y-m-d');
						if(!in_array($next_payment_due, array('0000-00-00', '1000-01-01')) && ($status=='Active' || strtotime($next_payment_due)>strtotime($date))){
							$date = $next_payment_due;
						}
						$date = new DateTime($date);
						if($pay_frequency=='Yearly'){
							$date->modify('+12 month');
						}
						else{
							$date->modify('+1 month');
						}
						$nextnext_payment_due = $date->format('Y-m-d');
	
						$timezone = 'Asia/Dhaka';
						if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
						date_default_timezone_set($timezone);
						
						//=======For Update Accounts========//
						$updateAccInfo = array('paypal_id'=>$paypal_id, 'next_payment_due'=>$nextnext_payment_due, 'status'=>'Active', 'status_date'=>date('Y-m-d H:i:s'));
						$update = $this->db->update('accounts', $updateAccInfo, $accounts_id);
							
						//=======For Insert into Invoice========//
						$description = "For account: $subdomain<br>Have QTY = 1@\$$total, Total: \$".number_format($total, 2);
						$invoice_number = 1000;
						$our_invoicesObj = $this->db->querypagination("SELECT invoice_number FROM our_invoices ORDER BY invoice_number DESC LIMIT 0, 1", array());
						if($our_invoicesObj){
							$invoice_number = $our_invoicesObj[0]['invoice_number']+1;
						}
						
						$ourInvoicesData = array();
						$ourInvoicesData['accounts_id'] = $accounts_id;
						$ourInvoicesData['invoice_number'] = $invoice_number;
						$ourInvoicesData['next_payment_due'] = $nextnext_payment_due;
						$ourInvoicesData['paid_on'] = date("Y-m-d");
						$ourInvoicesData['paid_by'] = "PP-$paypal_id";
						$ourInvoicesData['num_locations'] = 1;
						$ourInvoicesData['description'] = $description;
						$ourInvoicesData['pay_frequency'] = $pay_frequency;
						$ourInvoicesData['price_per_location'] = $total;
						$ourInvoicesData['refunded'] = 0;
						$ourInvoicesData['batch_date'] = '1000-01-01';
						$ourInvoicesData['HostBatchID'] = 0;
						$our_invoices_id = $this->db->insert('our_invoices', $ourInvoicesData);
						
						if($price_per_location != $total){
							$this->db->writeIntoLog("\nAccounts ID: $accounts_id, Payment should be $price_per_location but payment completed $total");
						}
					}
				}
			}
			else{
				$bodyReceivedStr = '';
				if(!empty($bodyReceived)){
					foreach($bodyReceived as $index=>$value){
						if(is_array($value)){
							$bodyReceivedStr .= "<p><b>$index :</b> ".json_encode($value)."</p>";
						}
						else{
							$bodyReceivedStr .= "<p><b>$index :</b> $value</p>";
						}
					}
				}
				mail($this->db->supportEmail('support'), 'PayPal Webhook could not find PayPalId.', $bodyReceivedStr);
			}
		}

		if(OUR_DOMAINNAME=='machouse.com.bd'){
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			return json_encode($jsonResponse);
		}
	}
	
	public function addDataToNote(){
		$subdomain = $GLOBALS['subdomain'];
		
		$queryObj = $this->db->query("SELECT accounts_id FROM accounts WHERE company_subdomain='$subdomain'", array());
		if($queryObj){
			$accounts_id = $queryObj->fetch(PDO::FETCH_OBJ)->accounts_id;
			//=============Add Data into our_notes=======//
			date_default_timezone_set('Asia/Dhaka');
			$our_notesData = array('created_on'=>date('Y-m-d H:i:s'),
								'accounts_id'=>$accounts_id,
								'description'=>json_encode($_REQUEST));
			$this->db->insert('our_notes', $our_notesData);
			$timezone = 'Asia/Dhaka';
			if(isset($_SESSION["timezone"])){$timezone = $_SESSION["timezone"];}
			date_default_timezone_set($timezone);
		}
		return 'Ok';
	}

}
?>