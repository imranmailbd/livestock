<?php
class POS{
	protected $db;
	private int $page, $totalRows, $pos_id;
	private string $history_type;
	private array $actFeeTitOpt;
	
	public function __construct($db){$this->db = $db;}
	
	public function index(){}
	
	public function AJ_index_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$jsonResponse['accounts_id'] = intval($accounts_id);
		$user_id = $_SESSION['user_id']??0;
		$jsonResponse['user_id'] = intval($user_id);
		$currency = $_SESSION["currency"]??'৳';

		$POST = json_decode(file_get_contents('php://input'), true);
		$segment3name = $POST['segment3name']??'';
		$segment4name = $POST['segment4name']??'';
		$segment5name = $POST['segment5name']??'';

		$Common = new Common($this->db);
		$Carts = new Carts($this->db);
		$Payments = new Payments($this->db);
			
		$default_invoice_printer = 'Small';
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				$default_invoice_printer = array_key_exists('default_invoice_printer', $value)?$value['default_invoice_printer']:'Small';
			}
		}
		
		if(empty($default_invoice_printer) || is_null($default_invoice_printer)){$default_invoice_printer = 'Small';}
		$jsonResponse['default_invoice_printer'] = $default_invoice_printer;

		$cCustomFields = 0;
		$queryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers'", array());
		if($queryObj){
			$cCustomFields = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$jsonResponse['cCustomFields'] = intval($cCustomFields);

		$pCustomFields = 0;
		$queryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'product'", array());
		if($queryObj){
			$pCustomFields = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$jsonResponse['pCustomFields'] = intval($pCustomFields);

		$pos_id = $invoice_no = $taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
		$taxes_name1 = $taxes_name2 = '';
		$option1Val = $option2Val = 0;

		$customer_id = $_SESSION["customer_id"]??0;
		$employee_id = $_SESSION["employee_id"]??0;
		$user_id = $_SESSION["user_id"];

		if($employee_id==0){$employee_id = $user_id;}

		$posData = false;		
		if($segment4name=='edit' && !empty($segment5name) && $segment5name >0){			
			$pos_id = intval($segment5name);
			$sqlPOS = "SELECT pos_id, employee_id, customer_id FROM pos WHERE accounts_id = $accounts_id AND pos_id = $pos_id AND employee_id = $employee_id AND pos_publish = 0 ORDER BY pos_id DESC LIMIT 0, 1";
			$posData = $this->db->querypagination($sqlPOS, array());
			if($posData){				
				$_SESSION["pos_id"] = $pos_id = $posData[0]['pos_id'];
				$_SESSION["employee_id"] = $employee_id = $posData[0]['employee_id'];
				$_SESSION["customer_id"] = $customer_id = intval($posData[0]['customer_id']);
			}
		}
		elseif(isset($_SESSION["pos_id"])){
			$pos_id = $_SESSION["pos_id"];
		}
		else{
			$posData = $this->db->querypagination("SELECT pos_id, employee_id, customer_id FROM pos WHERE accounts_id = $accounts_id AND user_id = $employee_id AND invoice_no = 0 AND pos_publish = 0 ORDER BY pos_id DESC LIMIT 0, 1", array());
			if($posData){
				$_SESSION["pos_id"] = $pos_id = $posData[0]['pos_id'];
				$_SESSION["employee_id"] = $employee_id = $posData[0]['employee_id'];
				$_SESSION["customer_id"] = $customer_id = $posData[0]['customer_id'];
			}
		}

		if($pos_id>0){
			$posData = $this->db->querypagination("SELECT * FROM pos WHERE pos_id = $pos_id LIMIT 0, 1", array());
			if($posData){
				$invoice_no = $posData[0]['invoice_no'];
				if($invoice_no>0){					
					if(isset($_SESSION["employee_id"])){unset($_SESSION["employee_id"]);$employee_id = $_SESSION["user_id"];}
					if(isset($_SESSION["customer_id"])){unset($_SESSION["customer_id"]);$customer_id = 0;}
					if(isset($_SESSION["taxes_id1"])){unset($_SESSION["taxes_id1"]);}
					if(isset($_SESSION["taxes_id2"])){unset($_SESSION["taxes_id2"]);}
					if(isset($_SESSION["pos_id"])){unset($_SESSION["pos_id"]);$pos_id = 0;}
				}
				else{
					$pos_id = $posData[0]['pos_id'];
					$sales_datetime = $posData[0]['sales_datetime'];
					$employee_id = $posData[0]['employee_id'];
					$customer_id = intval($posData[0]['customer_id']);
					$taxes_name1 = $posData[0]['taxes_name1'];
					$taxes_name2 = $posData[0]['taxes_name2'];
					$taxes_percentage1 = $posData[0]['taxes_percentage1'];
					$tax_inclusive1 = $posData[0]['tax_inclusive1'];
					$taxes_percentage2 = $posData[0]['taxes_percentage2'];
					$tax_inclusive2 = $posData[0]['tax_inclusive2'];
		
					if(!in_array($sales_datetime, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
						$hours = (strtotime(date('Y-m-d H:i:s')) - strtotime($sales_datetime))/3600;
						if($hours>1){
							$this->startOverPOS();
							if(isset($_SESSION["employee_id"])){unset($_SESSION["employee_id"]);}
							if(isset($_SESSION["customer_id"])){unset($_SESSION["customer_id"]);}
							if(isset($_SESSION["taxes_id1"])){unset($_SESSION["taxes_id1"]);}
							if(isset($_SESSION["taxes_id2"])){unset($_SESSION["taxes_id2"]);}
							if(isset($_SESSION["pos_id"])){unset($_SESSION["pos_id"]);}
							$employee_id = $_SESSION["user_id"];
							$customer_id = 0;
							$pos_id = 0;
							$taxes_name1 = $taxes_name2 = '';
							$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;

						}
					}
				}
			}					
		}
		
		if(isset($_SESSION["taxes_id1"]) && $_SESSION["taxes_id1"]>0 && $taxes_name1 ==''){
			$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = :taxes_id ORDER BY taxes_id DESC LIMIT 0, 1", array('taxes_id'=>$_SESSION["taxes_id1"]),1);
			if($taxesObj){					
				$taxes_name1 = $taxesObj[0]['taxes_name'];
				$taxes_percentage1 = $taxesObj[0]['taxes_percentage'];
				$tax_inclusive1 = $taxesObj[0]['tax_inclusive'];
			}
		}
							
		if(isset($_SESSION["taxes_id2"]) && $_SESSION["taxes_id2"]>0 && $taxes_name2 ==''){
			$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = :taxes_id ORDER BY taxes_id DESC LIMIT 0, 1", array('taxes_id'=>$_SESSION["taxes_id2"]),1);
			if($taxesObj){					
				$taxes_name2 = $taxesObj[0]['taxes_name'];
				$taxes_percentage2 = $taxesObj[0]['taxes_percentage'];
				$tax_inclusive2 = $taxesObj[0]['tax_inclusive'];
			}
		}
		
		$customer_name = $email_address = '';
		$available_credit = $readonly = 0;
		
		$cash_reg_req_customer = $cash_drawer_sale = 0;
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'cash_register_options'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				$cash_reg_req_customer = intval($value['cash_reg_req_customer']??0);
				$cash_drawer_sale = intval($value['cash_drawer_sale']??0);
			}
		}
		$customerObj = $this->db->query("SELECT default_customer FROM accounts WHERE accounts_id = $accounts_id", array());
		if($customerObj){
			$default_customer = intval($customerObj->fetch(PDO::FETCH_OBJ)->default_customer);
			if($default_customer==$customer_id){//$cash_reg_req_customer==1 && 
				$customer_id = 0;
			}
		}

		if($customer_id>0){
			$customerObj = $this->db->query("SELECT customers_id, first_name, last_name, company, email, contact_no, credit_limit FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = :customer_id", array('customer_id'=>$customer_id),1);
			if($customerObj){
				$onecustomerrow = $customerObj->fetch(PDO::FETCH_OBJ);
				$readonly = 1;
								
				$customers_id = $onecustomerrow->customers_id;
				$customer_name = trim((string) stripslashes($onecustomerrow->company));
				$email_address = $email = trim((string) stripslashes($onecustomerrow->email));
				$contact_no = trim((string) stripslashes($onecustomerrow->contact_no));
				$first_name = trim((string) stripslashes($onecustomerrow->first_name));
				if($customer_name !=''){$customer_name .= ', ';}
				$customer_name .= $first_name;
				$last_name = trim((string) stripslashes($onecustomerrow->last_name));
				if($customer_name !=''){$customer_name .= ' ';}
				$customer_name .= $last_name;
				
				if($email !=''){
					$customer_name .= " ($email)";
				}
				elseif($contact_no !=''){
					$customer_name .= " ($contact_no)";
				}
				$credit_limit = $onecustomerrow->credit_limit;
				if($credit_limit>0){
					$availCreditData = $Common->calAvailCr($customers_id, $credit_limit, 1);
					if(array_key_exists('available_credit', $availCreditData)){
						$available_credit = $availCreditData['available_credit'];
					}
				}							
			}
		}		
		
		$methodOpts = array();
		$vData = $Common->variablesData('payment_options', $accounts_id);
		if(!empty($vData)){
			extract($vData);
			$methodOpts = explode('||',$payment_options);
		}
		
		$jsonResponse['pos_id'] = intval($pos_id);
		$jsonResponse['employee_id'] = intval($employee_id);
		$jsonResponse['customer_id'] = intval($customer_id);

		$jsonResponse['taxes_name1'] = $taxes_name1;
		$jsonResponse['taxes_percentage1'] = round($taxes_percentage1,3);
		$jsonResponse['tax_inclusive1'] = intval($tax_inclusive1);

		$jsonResponse['taxes_name2'] = $taxes_name2;
		$jsonResponse['taxes_percentage2'] = round($taxes_percentage2,3);
		$jsonResponse['tax_inclusive2'] = intval($tax_inclusive2);

		$jsonResponse['customer_name'] = $customer_name;
		$jsonResponse['email_address'] = $email_address;
		$jsonResponse['readonly'] = $readonly;
		$jsonResponse['available_credit'] = round($available_credit,2);
		$jsonResponse['cash_reg_req_customer'] = intval($cash_reg_req_customer);
		$jsonResponse['cash_drawer_sale'] = intval($cash_drawer_sale);
		$jsonResponse['payment_datetime'] = date('Y-m-d H:i');
		$jsonResponse['methodOpts'] = $methodOpts;
		
		$multiple_cash_drawers = 0;
		$casDraOpts = array();
		$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
		if(!empty($cdData)){
			$cash_drawers = '';
			extract($cdData);
			$casDraOpts = explode('||',$cash_drawers);
		}
		$drawer = $_COOKIE['drawer']??'';
		$drawerOpt = array();
		if($multiple_cash_drawers>0 && !empty($casDraOpts)){
			foreach($casDraOpts as $oneCDOption){
				$oneCDOption = trim((string) $oneCDOption);
				if(!empty($oneCDOption)){
					$drawerOpt[addslashes(stripslashes($oneCDOption))] = '';
				}
			}
			if(!empty($drawerOpt)){$drawerOpt = array_keys($drawerOpt);}
		}
		else{
			$multiple_cash_drawers = 0;
		}
		
		$drawer = $_COOKIE['drawer']??'';

		$jsonResponse['multiple_cash_drawers'] = intval($multiple_cash_drawers);
		$jsonResponse['casDraOpts'] = $drawerOpt;
		$jsonResponse['drawer'] = $drawer;
		
		$sqrup_currency_code = '';
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'cr_card_processing' AND value !=''", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}

		$webcallbackurl = '';
		if(OUR_DOMAINNAME=='machousel.com.bd'){
			$webcallbackurl = 'demo.';
		}
		$webcallbackurl .= OUR_DOMAINNAME;

		$avaCreRowSty = 1;
		if($available_credit==0){$avaCreRowSty = 0;}

		$petty_cash_tracking = $_SESSION["petty_cash_tracking"]??0;
		
		$jsonResponse['sqrup_currency_code'] = $sqrup_currency_code;
		$jsonResponse['webcallbackurl'] = $webcallbackurl;
		$jsonResponse['avaCreRowSty'] = intval($avaCreRowSty);
		$jsonResponse['petty_cash_tracking'] = intval($petty_cash_tracking);
		
		$empOpt = array();
		$sqlquery = "SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id AND (user_publish =1 OR (user_publish =0 AND user_id= :user_id)) ORDER BY user_first_name ASC, user_last_name ASC";
		$query = $this->db->query($sqlquery, array('user_id'=>$employee_id));
		if($query){
			while($useronerow = $query->fetch(PDO::FETCH_OBJ)){
				$optval = $useronerow->user_id;
				$optlable = trim(stripslashes("$useronerow->user_first_name $useronerow->user_last_name"));
				$empOpt[$optval] = $optlable;
			}
		}
		$jsonResponse['empOpt'] = $empOpt;
		
		$subPermission = array();
		if(!empty($_SESSION["allowed"]) && array_key_exists(1, $_SESSION["allowed"])) {
			$subPermission = $_SESSION["allowed"][1];
		}		
		$jsonResponse['subPermission'] = $subPermission;
		$pPermission = 1;
		if(in_array('cnanp', $subPermission)) {$pPermission = 0;}
		$jsonResponse['pPermission'] = intval($pPermission);
		
		$taxesRowCount = $defaultTaxCount = 0;
		$no_of_result_rows = $no_of_default_rows = 0;
		$option1 = $option2 = array();		
		$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND taxes_publish = 1 ORDER BY taxes_name ASC", array());
		if($taxesObj){
			$taxesRowCount = count($taxesObj);
			$taxesRowCount1 = 0;
			foreach($taxesObj as $taxesonerow1){
				$default_tax = $taxesonerow1['default_tax'];
				if($default_tax>0){
					$taxesRowCount1++;
				}
			}
			foreach($taxesObj as $taxesonerow){                                          
				$taxes_id = $taxesonerow['taxes_id'];
				$staxes_name = $taxesonerow['taxes_name'];
				$staxes_percentage = $taxesonerow['taxes_percentage'];
				$stax_inclusive = $taxesonerow['tax_inclusive'];
				
				$default_tax = $taxesonerow['default_tax'];
				if($default_tax>0){
					$defaultTaxCount++;
				}
				if($taxes_name1=='' && ($defaultTaxCount==1 || $taxesRowCount1==0)){
					$taxesRowCount1++;					
					$taxes_name1 = $staxes_name;
					$taxes_percentage1 = $staxes_percentage;
					$tax_inclusive1 = $stax_inclusive;				
				}
				
				if($defaultTaxCount==2 && $taxes_name2==''){					
					$taxes_name2 = $staxes_name;
					$taxes_percentage2 = $staxes_percentage;
					$tax_inclusive2 = $stax_inclusive;
				}
				
				if(strcmp($taxes_name1, $staxes_name)==0){
					$_SESSION["taxes_id1"] = $taxes_id;
					$option1Val = $taxes_id;
				}
				if(strcmp($taxes_name2, $staxes_name)==0){
					$_SESSION["taxes_id2"] = $taxes_id;
					$option2Val = $taxes_id;
				}
				
				$tiStr = '';
				if($stax_inclusive>0){$tiStr = ' Inclusive';}
				
				$option1[$taxes_id] = "$staxes_name ($staxes_percentage%$tiStr)";
				$option2[$taxes_id] = "$staxes_name ($staxes_percentage%$tiStr)";
			}
		}
		$tax1 = $tax2 = '';
		if($defaultTaxCount>1){
			$tax1 = '1';
			$tax2 = '2';
		}

		$jsonResponse['taxesRowCount'] = intval($taxesRowCount);
		$jsonResponse['no_of_result_rows'] = intval($no_of_result_rows);
		$jsonResponse['taxes_name1'] = $taxes_name1;
		$jsonResponse['taxes_percentage1'] = round($taxes_percentage1,3);
		$jsonResponse['tax_inclusive1'] = intval($tax_inclusive1);
		$jsonResponse['taxes_name2'] = $taxes_name2;
		$jsonResponse['taxes_percentage2'] = round($taxes_percentage2,3);
		$jsonResponse['tax_inclusive2'] = intval($tax_inclusive2);

		$jsonResponse['defaultTaxCount'] = intval($defaultTaxCount);
		$jsonResponse['option1'] = $option1;
		$jsonResponse['option1Val'] = $option1Val;
		$jsonResponse['option2'] = $option2;
		$jsonResponse['option2Val'] = $option2Val;
		$jsonResponse['tax1'] = $tax1;
		$jsonResponse['tax2'] = $tax2;

		$jsonResponse['cartsData'] = $Carts->loadCartData('POS', $pos_id);
		$jsonResponse['paymentData'] = $Payments->loadPOSPayment('POS', $pos_id);

		return json_encode($jsonResponse);
	}

	public function updatePOS(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$fieldName = $POST['fieldName']??'';
		$fieldValue = $POST['fieldValue']??0;
		$returnval = 0;
		$returnStr = '';
		if($fieldName=='employee_id'){
			if(empty($fieldValue)){
				$returnStr = 'Missing Sales Person. Please select Sales Person.';
				$fieldValue = 0;
				$returnval++;
			}
			elseif($fieldValue != intval($fieldValue)){
				$returnStr = 'Invalid Price/Location. Please enter valid Price/Location.';
				$fieldValue = intval($fieldValue);
				$returnval++;
			}
		}
		elseif($fieldName=='customer_id'){
			$fieldValue = intval($fieldValue);
			if($fieldValue==0){
				$customerObj = $this->db->query("SELECT default_customer FROM accounts WHERE accounts_id = $accounts_id", array());
				if($customerObj){
					$fieldValue = intval($customerObj->fetch(PDO::FETCH_OBJ)->default_customer);
				}
			}
		}
		
		if(isset($_SESSION["pos_id"]) && $_SESSION["pos_id"]>0){
			$pos_id = $_SESSION["pos_id"];
			$sqlquery = "SELECT invoice_no FROM pos WHERE pos_id = $pos_id";
			$queryObj = $this->db->query($sqlquery, array());
			if($queryObj){
				$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
				$invoice_no = $posRow->invoice_no;
				if($invoice_no>0){
					$returnval = 1000;
				}
			}
			
			if($returnval==0){
				$updateData = array();
				$updateData['last_updated'] = date('Y-m-d H:i:s');
				
				if(in_array($fieldName, array('taxes_id1', 'taxes_id2'))){
					$fieldNo = str_replace('taxes_id', '', $fieldName);
					$taxes_name = '';
					$taxes_percentage = $tax_inclusive = 0;
					$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = :taxes_id ORDER BY taxes_id DESC LIMIT 0, 1", array('taxes_id'=>$fieldValue),1);
					if($taxesObj){					
						$taxes_name = $taxesObj[0]['taxes_name'];
						$taxes_percentage = $taxesObj[0]['taxes_percentage'];
						$tax_inclusive = $taxesObj[0]['tax_inclusive'];
					}
					$taxes_name = $this->db->checkCharLen('pos.taxes_name'.$fieldNo, $taxes_name);
		
					$updateData['taxes_name'.$fieldNo] = $taxes_name;
					$updateData['taxes_percentage'.$fieldNo] = $taxes_percentage;
					$updateData['tax_inclusive'.$fieldNo] = $tax_inclusive;
				}
				else{						
					$updateData[$fieldName] = $fieldValue;
				}
				$this->db->update('pos', $updateData, $pos_id);
				$_SESSION[$fieldName] = $fieldValue;
				$returnval = 2;
			}
		}
		else{
			$_SESSION[$fieldName] = $fieldValue;
			$returnval = 1;
		}
		
		$returnData = array('login'=>'', 'returnval'=>$returnval, 'returnStr'=>$returnStr);
		return json_encode($returnData);
	}
	
	public function startOverPOS($returnval=0){
	
		if(isset($_SESSION["employee_id"])){unset($_SESSION["employee_id"]);}
		if(isset($_SESSION["customer_id"])){unset($_SESSION["customer_id"]);}
		if(isset($_SESSION["taxes_id1"])){unset($_SESSION["taxes_id1"]);}
		if(isset($_SESSION["taxes_id2"])){unset($_SESSION["taxes_id2"]);}
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$message = '';
		if(isset($_SESSION["pos_id"]) && $_SESSION["pos_id"]>0){
			$pos_id = $_SESSION["pos_id"];
		}
		else{
			$pos_id = $employee_id = 0;
			if(isset($_SESSION["employee_id"])){ $employee_id = $_SESSION["employee_id"];}
			elseif(isset($_SESSION["user_id"])){ $employee_id = $_SESSION["user_id"];}	
			$posData = $this->db->querypagination("SELECT pos_id FROM pos WHERE accounts_id = $accounts_id AND user_id = $employee_id AND invoice_no = 0 AND pos_publish = 0 ORDER BY pos_id DESC LIMIT 0, 1", array());
			if($posData){
				$pos_id = $posData[0]['pos_id'];
			}			
		}
		
		if($pos_id>0){
			$_SESSION["pos_id"] = $pos_id;
			$posData = $this->db->querypagination("SELECT pos_id FROM pos WHERE accounts_id = $accounts_id AND pos_id = $pos_id AND invoice_no = 0 AND pos_publish = 0 ORDER BY pos_id DESC LIMIT 0, 1", array());
			if($posData){
				$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_id = :pos_id ORDER BY pos_cart_id ASC", array('pos_id'=>$pos_id),1);
				if($pos_cartObj){
					while($pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
						$pos_cart_id = $pos_cartrow->pos_cart_id;
						$product_id = $pos_cartrow->item_id;
						$productObj = $this->db->query("SELECT product_type FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man", array());
						if($productObj){
							$product_type = $productObj->fetch(PDO::FETCH_OBJ)->product_type;
							
							if($pos_cartrow->item_type=='livestocks'){
								
								$pciObj = $this->db->query("SELECT pos_cart_item_id, item_id FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id", array());
								if($pciObj){
									while($pciOneRow = $pciObj->fetch(PDO::FETCH_OBJ)){
										$this->db->delete('pos_cart_item', 'pos_cart_item_id', $pciOneRow->pos_cart_item_id);
										$this->db->update('item', array('in_inventory'=>1, 'is_pos'=>0), $pciOneRow->item_id);
									}
								}
							}
							elseif($pos_cartrow->item_type=='product' && $pos_cartrow->require_serial_no>0){
								$snObj = $this->db->query("SELECT serial_number_id FROM serial_number WHERE pos_cart_id = $pos_cart_id", array());
								if($snObj){
									while($snonerow = $snObj->fetch(PDO::FETCH_OBJ)){
										$this->db->delete('serial_number', 'serial_number_id', $snonerow->serial_number_id);
									}
								}
							}
						}							
						
						$this->db->delete('pos_cart', 'pos_cart_id', $pos_cart_id);
						
					}
				}
				
				$noteObj = $this->db->query("SELECT notes_id FROM notes WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = $pos_id", array());
				if($noteObj){
					while($noteonerow = $noteObj->fetch(PDO::FETCH_OBJ)){
						$this->db->delete('notes', 'notes_id', $noteonerow->notes_id);
					}
				}
				$dsObj = $this->db->query("SELECT digital_signature_id FROM digital_signature WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = $pos_id", array());
				if($dsObj){
					while($dsonerow = $dsObj->fetch(PDO::FETCH_OBJ)){
						$this->db->delete('digital_signature', 'digital_signature_id', $dsonerow->digital_signature_id);
					}
				}
				$track_editsObj = $this->db->query("SELECT track_edits_id FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = $pos_id", array());
				if($track_editsObj){
					while($track_editsonerow = $track_editsObj->fetch(PDO::FETCH_OBJ)){
						$this->db->delete('track_edits', 'track_edits_id', $track_editsonerow->track_edits_id);
					}
				}
				$ppObj = $this->db->query("SELECT pos_payment_id FROM pos_payment WHERE pos_id = $pos_id", array());
				if($ppObj){
					while($pponerow = $ppObj->fetch(PDO::FETCH_OBJ)){
						$this->db->delete('pos_payment', 'pos_payment_id', $pponerow->pos_payment_id);
					}
				}
				
				$this->db->delete('pos', 'pos_id', $pos_id);
							
				$_SESSION["pos_id"] = 0;					
				unset($_SESSION["pos_id"]);
				$message = 'OK';
			}
			else{			
				$_SESSION["pos_id"] = 0;					
				unset($_SESSION["pos_id"]);
				$message = $this->db->translate('This invoice has been completed');
			}
		}
		else{
			$message = $this->db->translate('Sorry! system could not find any Cash Register.');
		}
		
		$returnData = array('login'=>'', 'message'=>$message);
		if($returnval>0)
			return json_encode($returnData);
	}
	
    public function completePOS(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = $pos_id = 0;
		$savemsg = $message = $email = '';    
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$Common = new Common($this->db);
		$pos_id = intval($POST['pos_id']??0);
		if($pos_id == 0 && isset($_SESSION["pos_id"])){$pos_id = $_SESSION["pos_id"];}

		$employee_id = $_SESSION['employee_id']??0;
		if($employee_id == 0 && $user_id>0){$employee_id = $user_id;}
		$customer_id = $_SESSION["customer_id"]??0;
		$changemethod = $POST['changemethod']??'Cash';

		if($pos_id==0){
			$savemsg = 'error';
			$message = 'noCartAdded';
		}
		elseif($employee_id==0){
			$savemsg = 'error';
			$message = 'salesNotFound';
		}
		else{
			$sqlquery = "SELECT invoice_no FROM pos WHERE pos_id = $pos_id";
			$queryObj = $this->db->query($sqlquery, array());
			if($queryObj){
				$invoice_no = $queryObj->fetch(PDO::FETCH_OBJ)->invoice_no;
				if($invoice_no>0){
					$savemsg = 'Completed';
				}
			}
			if(empty($savemsg)){
				$amount_due = floatval($POST['amount_due']??0);
				if($amount_due==''){$amount_due = 0;}
				$changemethod = $POST['changemethod']??'Cash';

				//=============collect user last new invoice no================//
				$invoice_no = 1;
				$poObj = $this->db->querypagination("SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id ORDER BY invoice_no DESC LIMIT 0, 1", array());
				if($poObj){
					$invoice_no = $poObj[0]['invoice_no']+1;
				}

				$conditionarray = array('invoice_no' => $invoice_no,
					'employee_id' => $employee_id,
					'last_updated' => date('Y-m-d H:i:s'),
					'user_id' => $user_id,
					'pos_publish' => 1,
					'status'=>'Invoiced');
				//if($customer_id>0){$conditionarray['customer_id'] = $customer_id;}
				$update = $this->db->update('pos', $conditionarray, $pos_id);
				if($update){
					//===============Update Inventory==============//
					$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_id = $pos_id ORDER BY pos_cart_id ASC", array());
					if($pos_cartObj){
						while($pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
							$pos_cart_id = $pos_cartrow->pos_cart_id;
							$product_id = $pos_cartrow->item_id;
							$item_type = $pos_cartrow->item_type;
								
							$qty = $ave_cost = 0;
							if($item_type=='livestocks'){
								$pciObj = $this->db->query("SELECT COUNT(pos_cart_item_id) AS counttotalrows FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id", array());
								if($pciObj){
									$qty = $pciObj->fetch(PDO::FETCH_OBJ)->counttotalrows;
								}
							}
							elseif($pos_cartrow->item_type=='product' && $pos_cartrow->require_serial_no>0){
								$snObj = $this->db->query("SELECT count(serial_number_id) as counttotalrows FROM serial_number WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
								if($snObj){$qty = $snObj->fetch(PDO::FETCH_OBJ)->counttotalrows;}
							}
							else{
								$qty = $pos_cartrow->qty;
							}
							
							if($item_type !='livestocks'){
								$inventoryObj = $this->db->query("SELECT i.ave_cost, i.ave_cost_is_percent, p.manage_inventory_count FROM product p, inventory i WHERE p.product_id = $product_id AND i.accounts_id = $accounts_id AND p.product_id = i.product_id", array());
								if($inventoryObj){
									$inventoryOneRow = $inventoryObj->fetch(PDO::FETCH_OBJ);
									$ave_cost = $inventoryOneRow->ave_cost;
									$ave_cost_is_percent = $inventoryOneRow->ave_cost_is_percent;
									$manage_inventory_count = $inventoryOneRow->manage_inventory_count;
									if($ave_cost_is_percent>0 && $manage_inventory_count==0){
									
										if($pos_cartrow->discount_is_percent>0){
											$discount_value = round($pos_cartrow->sales_price*0.01*$pos_cartrow->discount,2);
										}
										else{
											$discount_value = round($pos_cartrow->discount,2);
										}

										$ave_cost = round(($pos_cartrow->sales_price-$discount_value)*$ave_cost*0.01,2);
									}
								}
							}
							
							$pcUpdate = $this->db->update('pos_cart', array('ave_cost'=>$ave_cost, 'shipping_qty'=>$qty), $pos_cart_id);
							if($pcUpdate){
								$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
								if($inventoryObj){
									$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
									$current_inventory = $inventoryrow->current_inventory;
									if($pos_cartrow->item_type !='livestocks'){
										$newcurrent_inventory = floor($current_inventory-$qty);
										$updateproduct = $this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventoryrow->inventory_id);
									}
								}
							}
							
							if($item_type =='livestocks'){
								$Common->cartCellphoneAveCost($pos_cart_id, date('Y-m-d H:i:s'), 1);
							}
						}
					}

					$pos_payment_id = $payment_amount = 0;
					$payment_date = '';
					$sqlquery = "SELECT pos_payment_id, payment_amount, payment_datetime FROM pos_payment WHERE pos_id = $pos_id AND payment_method = 'Cash' ORDER BY pos_payment_id DESC LIMIT 0,1";
					$query = $this->db->querypagination($sqlquery, array());
					if($query){
						foreach($query as $row){
							$pos_payment_id = $row['pos_payment_id'];
							$payment_amount = $row['payment_amount'];
							$payment_date = date('Y-m-d', strtotime($row['payment_datetime']));
						}
					}
					
					$today_date = date('Y-m-d');
					$updatecash = 0;
					if($changemethod=='Cash' && $pos_payment_id>0 && strcmp($today_date, $payment_date)==0 && $amount_due<0){
						
						if((-1*$amount_due) < $payment_amount){
							$payment_amount = $payment_amount+$amount_due;
							
							$pos_paymentdata =array('payment_amount'=>$payment_amount,
													'payment_datetime'=>date('Y-m-d H:i:s'));
							$this->db->update('pos_payment', $pos_paymentdata, $pos_payment_id);
							$updatecash = 1;
						}
					}
					
					if($updatecash == 0 && $amount_due<0){
						$payment_method = $this->db->checkCharLen('pos_payment.payment_method', $changemethod);
						$drawer = $this->db->checkCharLen('pos_payment.drawer', '');
			
						$ppData = array('pos_id'=>$pos_id,
										'payment_method'=>$payment_method,
										'payment_amount'=>round($amount_due,2),
										'payment_datetime'=>date('Y-m-d H:i:s'),
										'user_id' => $user_id,
										'more_details' => '',
										'drawer' => $drawer);
						$pos_payment_id = $this->db->insert('pos_payment', $ppData);
					}
					
					//===========Final check for Grand Total and Payment Total======================//
					$sql = "SELECT * FROM pos WHERE pos_id = $pos_id";
					$query = $this->db->querypagination($sql, array());
					if($query){
						foreach($query as $onerow){
							$customer_id = intval($onerow['customer_id']);
							$is_due = $onerow['is_due'];
							$taxable_total = $nontaxable_total = 0.00;
							
							$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
							$query = $this->db->query($sqlquery, array());
							if($query){
								while($row = $query->fetch(PDO::FETCH_OBJ)){
									$sales_price = $row->sales_price;
									$shipping_qty = $row->shipping_qty;
									$total =round($sales_price * $shipping_qty,2);
									$discount_is_percent = $row->discount_is_percent;
									$discount = $row->discount;
									if($discount_is_percent>0){
										$discount_value = round($total*0.01*$discount,2);
									}
									else{ 
										$discount_value = round($discount*$shipping_qty,2);
									}
									$taxable = $row->taxable;																		
									if($taxable>0){
										$taxable_total = $taxable_total+$total-$discount_value;
									}
									else{
										$nontaxable_total = $nontaxable_total+$total-$discount_value;
									}						
								}
							}
							
							$taxes_total1 = 0;					
							$tax_inclusive1 = $onerow['tax_inclusive1'];
							if($onerow['taxes_name1'] !=''){
								$taxes_total1 = $Common->calculateTax($taxable_total, $onerow['taxes_percentage1'], $tax_inclusive1);
							}
							$taxes_total2 = 0;					
							$tax_inclusive2 = $onerow['tax_inclusive2'];
							if($onerow['taxes_name2'] !=''){
								$taxes_total2 = $Common->calculateTax($taxable_total, $onerow['taxes_percentage2'], $tax_inclusive2);
							}
							
							if($tax_inclusive1>0){$taxes_total1 = 0;}
							if($tax_inclusive2>0){$taxes_total2 = 0;}

							$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
							
							$amountPaid = 0;
							$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
							$queryObj = $this->db->query($sqlquery, array());
							if($queryObj){
								$amountPaid = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
							}
							$dueAmount = round($grand_total-$amountPaid, 2);
							
							if($dueAmount>0){
								$credit_days = $credit_limit = 0;
								$customersObj = $this->db->querypagination("SELECT credit_days, credit_limit FROM customers WHERE customers_id = $customer_id AND accounts_id = $prod_cat_man AND credit_days>0 ORDER BY accounts_id DESC LIMIT 0, 1", array());
								if($customersObj){
									$credit_days = $customersObj[0]['credit_days'];
									$credit_limit = $customersObj[0]['credit_limit'];							
								}
								if($credit_limit>0){
									$this->db->update('pos', array('credit_days'=>$credit_days, 'is_due'=>1), $pos_id);
								}
							}
							elseif($dueAmount<0){
								$lpos_payment_id = 0;
								$newpayment_amount = $dueAmount;
								$sqlquery = "SELECT pos_payment_id, payment_amount FROM pos_payment WHERE pos_id = $pos_id AND payment_method = 'Cash' ORDER BY pos_payment_id DESC LIMIT 0,1";
								$query = $this->db->querypagination($sqlquery, array());
								if($query){
									foreach($query as $row){
										$lpos_payment_id = $row['pos_payment_id'];
										$newpayment_amount = $row['payment_amount']+$dueAmount;
									}
								}
								
								if($lpos_payment_id>0){
									if($newpayment_amount==0){
										$this->db->delete('pos_payment', 'pos_payment_id', $lpos_payment_id);
									}
									else{
										$pos_paymentdata =array('payment_amount'=>$newpayment_amount,
																'payment_datetime'=>date('Y-m-d H:i:s')
																);
										$this->db->update('pos_payment', $pos_paymentdata, $lpos_payment_id);
									}
								}
								elseif($newpayment_amount !=0){
									$payment_method = $this->db->checkCharLen('pos_payment.payment_method', 'Cash');
									$drawer = $this->db->checkCharLen('pos_payment.drawer', '');
									$ppData = array('pos_id'=>$pos_id,
													'payment_method'=>$payment_method,
													'payment_amount'=>round($newpayment_amount,2),
													'payment_datetime'=>date('Y-m-d H:i:s'),
													'user_id' => $user_id,
													'more_details' => '',
													'drawer' => $drawer);
									$pos_payment_id = $this->db->insert('pos_payment', $ppData);
								}
							}
							elseif($is_due==1 && $dueAmount==0){
								$this->db->update('pos', array('credit_days'=>0, 'is_due'=>0), $pos_id);
							}

							$BulkSMS = new BulkSMS($this->db);
							$BulkSMS->sendInvoiceSMS($customer_id, $onerow['invoice_no'], $grand_total, $amountPaid, $onerow['sales_datetime']);
						
						}
					}
					
					$email_address = '';
					$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
					if($varObj){
						$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
						if(!empty($value)){
							$value = unserialize($value);
							if(array_key_exists('invoice_backup_email', $value))
								$email_address = $value['invoice_backup_email'];
						}
					}
					
					if(!empty($email_address)){
						$Carts = new Carts($this->db);
						$Carts->AJ_sendposmail($pos_id, $email_address, $amount_due, 0);
					}
					
					$savemsg = 'success';
					$id = $invoice_no;
					$_SESSION["pos_id"] = 0;
					if(isset($_SESSION["pos_id"])){unset($_SESSION["pos_id"]);}
					
					$this->startOverPOS();
				}
				else{
					$savemsg = 'error';
					$message = 'notAddPos';
				}
			}
		}
	
		$returnData = array( 'login'=>'', 'message'=>$message, 'id'=>$id, 'pos_id'=>$pos_id, 'savemsg'=>$savemsg, 'email'=>$email);
		return json_encode($returnData);
	}
	
	private function filterHAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$pos_id = $this->pos_id;
		$shistory_type = $this->history_type;
		
		$invoice_no = 0;
		$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
		if($posObj){
			$invoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
		}

		$bindData = array();
		$bindData['pos_id'] = $pos_id;            
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Sales Created')==0){
				$filterSql = "SELECT COUNT(pos_id) AS totalrows FROM pos WHERE pos_id = :pos_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Signature Created')==0){
				$filterSql = "SELECT COUNT(digital_signature_id) AS totalrows FROM digital_signature WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Invoices/view/', :pos_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
				$bindData['pos_id'] = $invoice_no;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Invoices/view/', $invoice_no)  
			UNION ALL 
			SELECT COUNT(pos_id) AS totalrows FROM pos WHERE pos_id = :pos_id AND accounts_id = $accounts_id 
			UNION ALL 
			SELECT COUNT(notes_id) AS totalrows FROM notes WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id 
			UNION ALL 
			SELECT COUNT(digital_signature_id) AS totalrows FROM digital_signature WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id 
			UNION ALL 
			SELECT COUNT(track_edits_id) AS totalrows FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id";
		}
		
		$totalRows = 0;
		$tableObj = $this->db->query($filterSql, $bindData);
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;
			}
		}		
		$this->totalRows = $totalRows;

		$actFeeTitOpt = array();
		$Sql = "SELECT activity_feed_title AS afTitle FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Invoices/view/', $invoice_no)  
		UNION ALL 
		SELECT 'Sales Created' AS afTitle FROM pos WHERE pos_id = :pos_id AND accounts_id = $accounts_id 
		UNION ALL 
		SELECT 'Notes Created' AS afTitle FROM notes WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id 
		UNION ALL 
		SELECT 'Signature Created' AS afTitle FROM digital_signature WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id 
		UNION ALL 
		SELECT 'Track Edits' AS afTitle FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id";
		$tableObj = $this->db->query($Sql, array('pos_id'=>$pos_id));
		if($tableObj){
			$actFeeTitOpts = array();
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$actFeeTitOpts[$oneRow->afTitle] = '';
			}
			ksort($actFeeTitOpts);
			$actFeeTitOpt = array_keys($actFeeTitOpts);
		}
		$this->actFeeTitOpt = $actFeeTitOpt;
	}
	
    private function loadHTableRows(){
        
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$pos_id = $this->pos_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		
		$invoice_no = 0;
		$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
		if($posObj){
			$invoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
		}
		
		$bindData = array();
		$bindData['pos_id'] = $pos_id;            
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Sales Created')==0){
				$filterSql = "SELECT 'pos' AS tablename, created_on AS tabledate, pos_id AS table_id, 'Sales Created' AS activity_feed_title FROM pos 
					WHERE pos_id = :pos_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Signature Created')==0){
				$filterSql = "SELECT 'digital_signature' AS tablename, created_on AS tabledate, digital_signature_id AS table_id, 'Signature Created' AS activity_feed_title FROM digital_signature 
							WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Invoices/view/', :pos_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
				$bindData['pos_id'] = $invoice_no;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Invoices/view/', $invoice_no)  
					UNION ALL 
					SELECT 'pos' AS tablename, created_on AS tabledate, pos_id AS table_id, 'Sales Created' AS activity_feed_title FROM pos 
						WHERE pos_id = :pos_id AND accounts_id = $accounts_id 
					UNION ALL 
					SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id 
					UNION ALL 
					SELECT 'digital_signature' AS tablename, created_on AS tabledate, digital_signature_id AS table_id, 'Signature Created' AS activity_feed_title FROM digital_signature 
						WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id 
					UNION ALL 
					SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id 
					ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		$query = $this->db->querypagination($filterSql, $bindData);

		$tabledata = array();
		if($query){
			$userIdNames = array();
			$userObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id", array());
			if($userObj){
				while($userOneRow = $userObj->fetch(PDO::FETCH_OBJ)){
					$userIdNames[$userOneRow->user_id] = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
				}
			}
			$Activity_Feed = new Activity_Feed($this->db);
			foreach($query as $onerow){
				$activity_feed_title = $onerow['activity_feed_title'];
				$tablename = $onerow['tablename'];
				$table_id = $onerow['table_id'];
				
				$getHMoreInfo = $Activity_Feed->getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title);
				if(!empty($getHMoreInfo)){
					$tabledata[] = $getHMoreInfo;
				}
			}
		}

		return $tabledata;
    }
	
	public function AJgetHPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']??0);
		if($pos_id==0 && isset($_SESSION['pos_id'])){$pos_id = $_SESSION['pos_id'];}
	
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->pos_id = $pos_id;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['actFeeTitOpt'] = $this->actFeeTitOpt;
			
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRows();
		
		return json_encode($jsonResponse);
	}
	
	public function prints(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$pos_id = intval($GLOBALS['segment5name']);
		$segment7name = $GLOBALS['segment7name'];
		$language = $_SESSION["language"]??'English';
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

		if($pos_id==0){
			if(isset($_SESSION["pos_id"])){$table_id = $pos_id = $_SESSION["pos_id"];}
			else{
				$user_id = $_SESSION["user_id"]??0;
				$customer_id = $_SESSION["customer_id"]??0;
				$employee_id = $_SESSION["employee_id"]??$user_id;
				
				if(intval($customer_id)==0){
					$customerObj = $this->db->query("SELECT default_customer FROM accounts WHERE accounts_id = $accounts_id", array());
					if($customerObj){
						$customer_id = intval($customerObj->fetch(PDO::FETCH_OBJ)->default_customer);
					}
				}
				$taxes_name1 = $taxes_name2 = '';
				$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
				$taxes_id1 = $_SESSION["taxes_id1"]??0;					
				if($taxes_id1>0){
					$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id1 ORDER BY taxes_id DESC LIMIT 0, 1", array());
					if($taxesObj){
						$taxes_name1 = $taxesObj[0]['taxes_name'];
						$taxes_percentage1 = $taxesObj[0]['taxes_percentage'];
						$tax_inclusive1 = $taxesObj[0]['tax_inclusive'];
					}				
				}
				$taxes_id2 = $_SESSION["taxes_id2"]??0;
				if($taxes_id2>0){
					$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id2 ORDER BY taxes_id DESC LIMIT 0, 1", array());
					if($taxesObj){
						$taxes_name2 = $taxesObj[0]['taxes_name'];
						$taxes_percentage2 = $taxesObj[0]['taxes_percentage'];
						$tax_inclusive2 = $taxesObj[0]['tax_inclusive'];
					}
				}
				
				if(empty($taxes_name1) || empty($taxes_name2)){
					$taxesObj = $this->db->query("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND taxes_publish = 1 ORDER BY taxes_name ASC", array());
					if($taxesObj){
						$defaultTaxCount = 0;
						while($taxesonerow = $taxesObj->fetch(PDO::FETCH_OBJ)){
							
							$staxes_name = $taxesonerow->taxes_name;
							$staxes_percentage = $taxesonerow->taxes_percentage;							
							$default_tax = $taxesonerow->default_tax;
							$tax_inclusive = $taxesonerow->tax_inclusive;
							if(empty($taxes_name1)){
								$taxes_name1 = $staxes_name;
								$taxes_percentage1 = $staxes_percentage;
								$tax_inclusive1 = $tax_inclusive;
							}
							
							if($default_tax>0){
								$defaultTaxCount++;
								if($defaultTaxCount==1 && empty($taxes_name1)){
									$taxes_name1 = $staxes_name;
									$taxes_percentage1 = $staxes_percentage;
									$tax_inclusive1 = $tax_inclusive;
								}
								
								if($defaultTaxCount==2 && empty($taxes_name2)){
									$taxes_name2 = $staxes_name;
									$taxes_percentage2 = $staxes_percentage;
									$tax_inclusive2 = $tax_inclusive;
								}
							}
						}
					}
				}
				
				$posData = array('invoice_no' => 0, 
								'sales_datetime' => date('Y-m-d H:i:s'), 
								'employee_id' => $employee_id, 
								'customer_id' => $customer_id, 
								'taxes_name1' => $taxes_name1,
								'taxes_percentage1' => $taxes_percentage1,
								'tax_inclusive1' => $tax_inclusive1,
								'taxes_name2' => $taxes_name2,
								'taxes_percentage2' => $taxes_percentage2,
								'tax_inclusive2' => $tax_inclusive2,
								'pos_type' => 'Sale', 
								'created_on' => date('Y-m-d H:i:s'),
								'last_updated' => date('Y-m-d H:i:s'),
								'user_id' => $user_id, 
								'accounts_id' => $accounts_id, 
								'pos_publish' => 0, 
								'credit_days' => 0, 
								'is_due' => 0, 
								'status' => 'New');

				$pos_id = $this->db->insert('pos', $posData);
				$_SESSION["pos_id"] = $table_id = $pos_id;
			}	
		}

		$htmlStr = "";
		$posObj = $this->db->query("SELECT pos_id, invoice_no FROM pos WHERE accounts_id = $accounts_id AND pos_id = :pos_id", array('pos_id'=>$pos_id),1);
		if($posObj){
			$posOneRow = $posObj->fetch(PDO::FETCH_OBJ);
			$pos_id = $posOneRow->pos_id;
			$invoice_no = $posOneRow->invoice_no;
			if($segment7name=='signature'){ 
				$htmlStr .= '<!DOCTYPE html>
					<html>
					<head>
						<meta charset="utf-8">
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
						<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
						<script language="JavaScript" type="text/javascript">var currency = \''.$currency.'\';var calenderDate = \''.$calenderDate.'\';var timeformat = \''.$timeformat.'\';var loadLangFile = \''.$loadLangFile.'\';</script>
						<link rel="stylesheet" href="/assets/css-'.swVersion.'/style.css">
						<script language="JavaScript" type="text/javascript">
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
			";
						if(isset($_SESSION) && array_key_exists('languageJSVar', $_SESSION)){
							$languageJSVar = $_SESSION["languageJSVar"];
							if(!empty($languageJSVar)){
								$htmlStr .= "
								langModifiedData = {";
								foreach($languageJSVar as $varName=>$varValue){
									$htmlStr .= '
						\''.trim((string) $varName).'\':stripslashes(\''.addslashes($varValue).'\'),';
								}
								$htmlStr .= '}
								';
							}
						}
						
					$htmlStr .= '</script>
					<script src="/assets/js-'.swVersion.'/languages/'.$language.'.js"></script>
					</head>
					<body>
						<div id="viewPageInfo"></div>
						<script type="module" src="/assets/js-'.swVersion.'/'.printsJS.'"></script>
					</body>
					</html>';
			}
			else{
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
		}
		return $htmlStr;
	}
	
	public function AJ_prints_small_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']);
		if($pos_id==0 && isset($_SESSION["pos_id"])){
			$pos_id = $_SESSION["pos_id"];
		}
		$amount_due = $POST['amount_due']??0;
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->invoicesInfo($pos_id, 'small', $amount_due, 'POS');
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_pick_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']);
		if($pos_id==0 && isset($_SESSION["pos_id"])){
			$pos_id = $_SESSION["pos_id"];
		}
		$amount_due = $POST['amount_due']??0;
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->invoicesInfo($pos_id, 'pick', $amount_due, 'POS');
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_large_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']);
		if($pos_id==0 && isset($_SESSION["pos_id"])){
			$pos_id = $_SESSION["pos_id"];
		}
		$amount_due = $POST['amount_due']??0;
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->invoicesInfo($pos_id, 'large', $amount_due, 'POS');
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_signature_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']);
		if($pos_id==0 && isset($_SESSION["pos_id"])){
			$pos_id = $_SESSION["pos_id"];
		}
		$amount_due = $POST['amount_due']??0;
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->invoicesInfo($pos_id, 'large', $amount_due, 'POS');
		
		return json_encode($jsonResponse);
	}
	
	public function openCashDrawer(){
		$htmlStr = "<!DOCTYPE html>
		<html>
		<head>
			<meta charset=\"utf-8\">
			<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
			<meta name=\"description\" content=\"".COMPANYNAME." is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store.\" />
			<meta name=\"keywords\" content=\"Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale\" />
			<title>".$this->db->translate('Open Cash Drawer')."</title>
			<style type=\"text/css\">
				*{font-family:Arial, sans-serif, Helvetica;font-size: 11px;}
				body{width:100%; margin:0; padding:0;background:#fff;color:#000;}
				@page {size:portrait;margin-top: 0;margin-bottom: 0;}
				table{border-collapse:collapse;}
				tr.border td, tr.border th{ border:1px solid #CCC; padding:2px; vertical-align: top;}
			</style>
		</head>
		<body>
			<script type=\"text/javascript\"> 
				var document_focus = false;
				document.onreadystatechange = function () {
				  var state = document.readyState
				  if (state == 'interactive') {
					  
				  } else if (state == 'complete') {
					  setTimeout(function(){
						document.getElementById('interactive');
						window.print();
						document_focus = true;
					  },1000);
				  }
				}				
				setInterval(function() {
					var deviceOpSy = getDeviceOperatingSystem();
					if (document_focus === true && deviceOpSy=='unknown') { window.close(); }  
				}, 300);				

				function getDeviceOperatingSystem() {
					var userAgent = navigator.userAgent || navigator.vendor || window.opera;

					  // Windows Phone must come first because its UA also contains 'Android'
					if (/windows phone/i.test(userAgent)) {
						return 'Windows Phone';
					}

					if (/android/i.test(userAgent)) {
						return 'Android';
					}

					if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
						return 'iOS';
					}

					return 'unknown';
				}
			</script>
		</body>
		</html>";
		
		return $htmlStr;
	}
	
	public function AJget_pettyCashPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$petty_cash_id = intval($POST['petty_cash_id']??0);
		$petty_cashData = array();
		$petty_cashData['login'] = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$petty_cashData['petty_cash_id'] = 0;		
		$petty_cashData['eod_date'] = date('Y-m-d');
		$petty_cashData['add_sub'] = -1;		
		$petty_cashData['amount'] = 0.00;
		$petty_cashData['reason'] = '';
		// && isset($_SESSION["accounts_id"])
		if($petty_cash_id>0 && $accounts_id>0){
			$petty_cashObj = $this->db->query("SELECT * FROM petty_cash WHERE petty_cash_id = :petty_cash_id AND accounts_id = $accounts_id AND petty_cash_publish = 1", array('petty_cash_id'=>$petty_cash_id),1);
			if($petty_cashObj){
				$petty_cashRow = $petty_cashObj->fetch(PDO::FETCH_OBJ);	
	
				$petty_cashData['petty_cash_id'] = $petty_cashRow->petty_cash_id;
				$petty_cashData['eod_date'] = $petty_cashRow->eod_date;
				$petty_cashData['add_sub'] = intval($petty_cashRow->add_sub);
				$petty_cashData['amount'] = round($petty_cashRow->amount,2);
				$petty_cashData['reason'] = trim((string) $petty_cashRow->reason);
			}
		}
		
		return json_encode($petty_cashData);
	}
	
	public function AJsave_pettyCash(){	
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$returnStr = '';		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		$petty_cash_id = intval($POST['petty_cash_id']??0);
		$eod_date = date('Y-m-d', strtotime(trim((string) $POST['eod_date']??'1000-01-01')));
		$add_sub = trim((string) $POST['add_sub']??-1);
		$drawer = $this->db->checkCharLen('petty_cash.drawer', trim((string) isset($POST['pdrawer']) ? $POST['pdrawer'] : ''));
		$amount = floatval(trim((string) $POST['amount']??0.00));
		$reason = addslashes($POST['reason']??'');
		
		if($eod_date !='' && $add_sub !='' && $amount>0){
			$conditionarray = array();
			$conditionarray['last_updated'] = date('Y-m-d H:i:s');
			$conditionarray['accounts_id'] = $accounts_id;
			$conditionarray['user_id'] = $user_id = $_SESSION["user_id"]??0;
			$conditionarray['eod_date'] = $eod_date;
			$conditionarray['add_sub'] = $add_sub;
			$conditionarray['amount'] = round($amount,2);
			$conditionarray['reason'] = $reason;
			$conditionarray['drawer'] = $drawer;
			
			if($petty_cash_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');					
				$petty_cash_id = $this->db->insert('petty_cash', $conditionarray);
				if($petty_cash_id){$savemsg = '';}
				else{
					$returnStr .= 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('petty_cash', $conditionarray, $petty_cash_id);
				if($update){
					$savemsg = '';
					$activity_feed_title = $this->db->translate('Petty cash was edited');
					$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
					$activity_feed_link = "/POS/";
					$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
					
					$afData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $_SESSION["accounts_id"],
									'user_id' => $_SESSION["user_id"],
									'activity_feed_title' => $activity_feed_title,
									'activity_feed_name' => "EOD: $eod_date",
									'activity_feed_link' => $activity_feed_link,
									'uri_table_name' => "petty_cash",
									'uri_table_field_name' =>"petty_cash_publish",
									'field_value' => 1
									);
					$this->db->insert('activity_feed', $afData);
					
				}
				else{
					$returnStr .= 'errorOnEditing';
				}
			}
		}
	
		$array = array( 'login'=>'','petty_cash_id'=>$petty_cash_id,
						'savemsg'=>$savemsg,
						'returnStr'=>$returnStr);
		return json_encode($array);					
	}	
	
	//================Joining Class==============//
	public function AJget_CustomersPopup(){
		$Customers = new Customers($this->db);
		return $Customers->AJget_CustomersPopup();
	}
	
	public function AJsave_Customers(){
		$Customers = new Customers($this->db);
		return $Customers->AJsave_Customers();
	}

	public function AJget_ProductsPopup(){
		$Products = new Products($this->db);
		return $Products->AJget_ProductsPopup();
	}
	
	public function AJsave_Products(){
		$Products = new Products($this->db);
		return $Products->AJsave_Products();
	}
	
	public function AJget_ManufOpt(){
		$Products = new Products($this->db);
		return $Products->AJget_ManufOpt();
	}
	
	public function AJget_brandOpt(){
		$Products = new Products($this->db);
		return $Products->AJget_brandOpt();
	}
}
?>