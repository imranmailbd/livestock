<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Request-Method: GET, POST");
header('Access-Control-Allow-Headers: accept, origin, content-type');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$db = new Db();
$apiResponse = array('event'=>'ping', 'error'=>'Error occured while request for API', 'responseStr'=>'');
if (isset($_REQUEST) && array_key_exists('sd', $_REQUEST) && array_key_exists('module', $_REQUEST)) {
	$subdomain = base64_decode($_REQUEST['sd']);
	$language = 'English';
	
	$accountsObj = $db->query("SELECT accounts_id, location_of FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
	if(!$accountsObj){
		$apiResponse['error'] = $db->translate('Your account is invalid.');
	}
	else{
		$accountsRow = $accountsObj->fetch(PDO::FETCH_OBJ);
		$accounts_id = $accountsRow->accounts_id;
		$prod_cat_man = $accountsRow->accounts_id;
		if($accountsRow->location_of>0){
			$prod_cat_man = $accountsRow->location_of;
		}
		$currency = '৳';
		$dateformat = 'm/d/y';
		$timeformat = '12 hour';
		$timezone = '';		
		$Common = new Common($db);
		$vData = $Common->variablesData('account_setup', $accounts_id);
		if(!empty($vData)){
			if(array_key_exists('currency', $vData)){
				$currency =$vData['currency'];
			}
			if(array_key_exists('dateformat', $vData)){
				$dateformat =$vData['dateformat'];
			}
			if(array_key_exists('timeformat', $vData)){
				$timeformat =$vData['timeformat'];
			}
			if(array_key_exists('timezone', $vData)){
				$timezone =$vData['timezone'];
			}
			if(array_key_exists('language', $vData)){
				$language =$vData['language'];
			}
			if($dateformat=='' || is_null($dateformat)){$dateformat ='m/d/Y';}
			if($timeformat=='' || is_null($timeformat)){$timeformat ='12 hour';}			
		}
		if($timezone =='' || is_null($timezone)){$timezone = 'America/New_York';}
		$apiResponse['language'] = $language;

		$module = $_REQUEST['module'];
		
		if ($module=='oldAPI'){
			$apiResponse['error'] = $db->translate('Please check your account. Go to website module and copy new API code and update current API code.');
			$email = 'support@skitsbd.com';
			mail ($email, "Existing API Information", "Account ID: $accounts_id, Sub-Domain: $subdomain");
		}
		elseif (in_array($module, array('product', 'cellPhones', 'services'))){

			$POST = json_decode(file_get_contents('php://input'), true);	
			//===Check Repair Status===//
			if (is_array($POST) && array_key_exists('sproduct_type', $POST)) {
				$sproduct_type = $POST['sproduct_type']??'Standard';
				$smanufacturer_id = $POST['smanufacturer_id']??'';
				$scategory_id = $POST['scategory_id']??'';
				$keyword_search = $POST['keyword_search']??'';
				$totalRows = intval($POST['totalRows']??0);
				$filter = intval($POST['filter']??1);
				$page = intval($POST['page']??1);
				if($page<=0){$page = 1;}

				if(!in_array($sproduct_type, array('Labor/Services', 'Mobile Devices'))){$sproduct_type = 'Standard';}

				if(in_array($sproduct_type, array('Labor/Services', 'Standard'))){
					$limit = intval($POST["limit"]??15);
				}
				else{
					$limit = intval($POST["limit"]??10);
				}

				if($sproduct_type=='Labor/Services'){$segment2name = 'Services';}
				elseif($sproduct_type=='Standard'){$segment2name = 'Product';}
				else{$segment2name = 'CellPhones';}
				
				$accountsObj = $db->query("SELECT accounts_id, location_of FROM accounts WHERE company_subdomain = :company_subdomain AND domain = :domain", array('company_subdomain'=>$subdomain, 'domain'=>OUR_DOMAINNAME));
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
					$ihObj = $db->query("SELECT paypal_email, currency_code, display_services_prices, enable_services_paypal FROM instance_home WHERE accounts_id = $accounts_id", array());
					if($ihObj){
						$ihData = $ihObj->fetch(PDO::FETCH_OBJ);
						$paypal_email = $ihData->paypal_email;
						$currency_code = $ihData->currency_code;
						$display_prices = $ihData->display_services_prices;
						$enable_paypal = $ihData->enable_services_paypal;
					}
					
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
					
					if($filter==1){
						if(in_array($sproduct_type, array('Labor/Services', 'Standard'))){
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
						$queryObj = $db->query($Sql, $bindData);
						if($queryObj){
							$totalRows = $queryObj->rowCount();						
						}

						$manOpts = $catOpts = array();
						$query = $db->querypagination($Sql2, $bindData);
						if($query){
							foreach($query as $oneRow){
								if($oneRow['category_id']>0)
									$catOpts[$oneRow['category_id']] = '';
								if($oneRow['manufacturer_id']>0)
									$manOpts[$oneRow['manufacturer_id']] = '';
							}
						}
						
						$manOpt = array();
						if(count($manOpts)>0){			
							$manStr = "SELECT manufacturer_id, name FROM manufacturer WHERE manufacturer_id IN (".implode(', ', array_keys($manOpts)).") AND accounts_id = $prod_cat_man ORDER BY name ASC";
							$manObj = $db->query($manStr, array());
							if($manObj){
								while($oneRow=$manObj->fetch(PDO::FETCH_OBJ)){
									$manOpt[$oneRow->manufacturer_id] = stripslashes(trim((string) $oneRow->name));
								}
							}
						}

						$catOpt = array();
						if(count($catOpts)>0){
							$catStr = "SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($catOpts)).") AND accounts_id = $prod_cat_man ORDER BY category_name ASC";
							$catQuery = $db->query($catStr, array());
							if($catQuery){
								while($oneRow=$catQuery->fetch(PDO::FETCH_OBJ)){
									$selected = '';
									if($oneRow->category_id==$scategory_id){$selected = ' selected="selected"';}
									$catOpt[$oneRow->category_id] = stripslashes(trim((string) $oneRow->category_name));
								}
							}
						}
						$apiResponse['totalRows'] = intval($totalRows);
						$apiResponse['manOpt'] = $manOpt;
						$apiResponse['catOpt'] = $catOpt;
					}

					if(in_array($sproduct_type, array('Labor/Services', 'Standard'))){
						$filterSql = "SELECT p.product_id, p.manufacturer_id, manufacturer.name AS manufacture, p.product_name, p.sku, p.description, i.regular_price FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id AND p.product_type = '$sproduct_type' AND p.description !='' $filterSql AND p.product_publish = 1 AND i.product_id = p.product_id";
					}
					else{
						$filterSql = "SELECT p.product_id, p.manufacturer_id, manufacturer.name AS manufacture, p.product_name AS product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku, p.description, i.regular_price FROM inventory i, item, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id AND p.product_type = '$sproduct_type' AND p.description !='' $filterSql AND item.in_inventory = 1 AND p.product_publish = 1 AND i.product_id = p.product_id AND p.product_id = item.product_id GROUP BY p.product_id";
					}

					$starting_val = ($page-1)*$limit;
					if($starting_val>$totalRows){$starting_val = 0;}

					$sqlquery = "$filterSql ORDER BY TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name)) ASC LIMIT $starting_val, $limit";
					$query = $db->querypagination($sqlquery, $bindData);
					$tabledata = array();
					if($query){
						foreach($query as $onerow){
							$product_id = $onerow['product_id'];
							$manufacturer_id = $onerow['manufacturer_id'];
							$manufacture = $onerow['manufacture'];
							$singleproduct_name = $onerow['product_name'];
							$regular_price = $onerow['regular_price'];
							$product_name = stripslashes(trim($manufacture.' '.$singleproduct_name));
							$sku = stripslashes(trim($onerow['sku']));
							$description = stripslashes(trim($onerow['description']));

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

							$tabledata[] = array($product_id, $segment2name, $page, $product_name, $sproduct_type, $prodImg, $productSrc, $storage, $colour_nameArray, $phyConArray, $sku, $description, $regular_price);				
						}
					}
					$apiResponse['tabledata'] = $tabledata;
					
					$apiResponse['display_prices'] = intval($display_prices);
					$apiResponse['enable_paypal'] = intval($enable_paypal);
					$apiResponse['paypal_email'] = $paypal_email;
					$apiResponse['currency_code'] = $currency_code;
					$apiResponse['currency'] = $currency;
					
					$apiResponse['error'] = '';
					$apiResponse['responseStr'] = '';

					$title = $db->translate(ucfirst($module));
					$apiResponse['title'] = $title;
				}				
			}
		}
		elseif ($module=='invoice'){
			
			$Common = new Common($db);
			$Carts = new Carts($db);

			$responseStr = '';
			$invoice_no = base64_decode($_REQUEST['invNo']);
			$responseStr = "<p>Invoice No: s$invoice_no</p>";
			$pos_id = 0;
			
			$posObj = $db->query("SELECT * FROM pos WHERE accounts_id = $accounts_id AND invoice_no = :invoice_no", array('invoice_no'=>$invoice_no),1);
			if($posObj){
				$pos_onerow = $posObj->fetch(PDO::FETCH_OBJ);
				$pos_id = $pos_onerow->pos_id;
				if($accounts_id==0){
					$accounts_id = $pos_onerow->accounts_id;
				}
				$fromPage = 'POS';
				if($pos_onerow->pos_type=='Order'){$fromPage = 'Orders';}
				
				$invoice_no = $pos_onerow->invoice_no;
				$customer_id = $pos_onerow->customer_id;
				$order_status = $pos_onerow->order_status;
				$pos_publish = $pos_onerow->pos_publish;
				
				$customername = $customeremail = $offers_email = $customerphone = $customeraddress = $editcustomers = '';
				$customerObj = $db->query("SELECT * FROM customers WHERE customers_id = $customer_id", array());
				if($customerObj){
					$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);	
				}
				
				$jsonResponse = array();
				$jsonResponse['login'] = '';
				$printerName = '';
				$orientation = 'portrait';
				$top_margin = $bottom_margin = 0;
				$left_margin = $right_margin = 15;

				$varObj = $db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'small_print'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('left_margin', $value)){
							$left_margin = intval($value['left_margin']);
						}
						if(array_key_exists('right_margin', $value)){
							$right_margin = intval($value['right_margin']);
						}
					}
				}
				
				$jsonResponse['printerName'] = $printerName;
				$jsonResponse['orientation'] = $orientation;
				$jsonResponse['top_margin'] = $top_margin;
				$jsonResponse['bottom_margin'] = $bottom_margin;
				$jsonResponse['right_margin'] = $right_margin;
				$jsonResponse['left_margin'] = $left_margin;

				$logo_size = 'Small Logo';
				$logo_placement = 'Left';				
				$title = $db->translate('Sales Receipt');
				$company_info = $invoice_message_above = $invoice_message = $value = '';
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
				$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;

				$varNameVal = 'invoice_setup';

				$varObj = $db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = '$varNameVal'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				if($pos_onerow->pos_type=='Order'){
					$title .= ', Order #: o'.$invoice_no;			
				}
				else{
					$title .= ', Invoice #: s'.$invoice_no;
				}
				$jsonResponse['title'] = $title;
				$jsonResponse['company_info'] = nl2br(stripslashes($company_info));

				$companylogo = "";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$pics = glob($filePath."*.jpg");
				if($pics){
					foreach($pics as $onePicture){
						$onePicture = "//".OUR_DOMAINNAME.str_replace('./', '/', $onePicture);
						$companylogo = $onePicture;
					}				
				}

				$jsonResponse['companylogo'] = $companylogo;
				$jsonResponse['sales_datetime'] = $pos_onerow->sales_datetime;

				$salesPerson = '';					   
				if($sales_person==1 && $pos_onerow->employee_id>0){
					$userObj = $db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id", array());
					if($userObj){
						$userOneRow = $userObj->fetch(PDO::FETCH_OBJ);
						$salesPerson = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
					}
				}
				$jsonResponse['salesPerson'] = $salesPerson;
				
				$customerName = array();
				if($customer_name == 1 && $customerObj){
					if($customerrow->company !=''){
						$customerName[] = stripslashes(trim((string) $customerrow->company));
					}
					$customerName[] = stripslashes(trim("$customerrow->first_name $customerrow->last_name"));
				}
				$jsonResponse['customerName'] = $customerName;

				$contactNo = array();
				if($customer_phone==1 && $customerObj && $customerrow->contact_no !=''){
					$contactNo[] = $customerrow->contact_no;
				}			
				if($secondary_phone==1 && $customerObj && $customerrow->secondary_phone !=''){
					$contactNo[] = $customerrow->secondary_phone;
				}
				$jsonResponse['contactNo'] = $contactNo;

				$customerEmail = array();
				if($customer_email==1 && $customerObj && $customerrow->email !=''){
					$customerEmail[] = $customerrow->email;
				}
				if($customer_type==1 && $customerObj){
					if($customerrow->customer_type !='')
						$customerEmail[] .= $customerrow->customer_type;
				}
				$jsonResponse['customerEmail'] = $customerEmail;
				 
				$customData = array();
				if($customerObj && $customerrow->custom_data !=''){
					$queryCFObj = $db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
					if($queryCFObj){
						$custom_data = unserialize($customerrow->custom_data);
						while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
							$field_name = stripslashes($oneCustomFields->field_name);
							$checked = '';
							if(!empty($value) && array_key_exists("cf$oneCustomFields->custom_fields_id",$value) && $value["cf$oneCustomFields->custom_fields_id"]>0 && !empty($custom_data)){
								if(array_key_exists($field_name, $custom_data) && $custom_data[$field_name] !=''){
									$customData[$field_name] = $custom_data[$field_name];
								}
							}
						}
					}
				}
				$jsonResponse['customData'] = $customData;	

				$customerAddress = array();
				if($customer_address==1 && $customerObj){
					if($customerrow->shipping_address_one !=''){										
						$customerAddress[] = stripslashes(trim((string) "$customerrow->shipping_address_one"));
					}
					if($customerrow->shipping_city !='' || $customerrow->shipping_state !='' || $customerrow->shipping_zip !=''){
						$customerAddress[] = stripslashes(trim("$customerrow->shipping_city $customerrow->shipping_state $customerrow->shipping_zip"));
					}
				}
				$jsonResponse['customerAddress'] = $customerAddress;
				$jsonResponse['logo_placement'] = $logo_placement;

				$invoice_no = $pos_onerow->invoice_no;
				if($invoice_no ==0){
					$invoice_no = $pos_onerow->pos_id;
				}
				$jsonResponse['invoice_no'] = $invoice_no;
				$jsonResponse['fromPage'] = $fromPage;
				$jsonResponse['barcode'] = $barcode;
				$jsonResponse['invoice_message_above'] = $invoice_message_above;
				
				$cartData = array();
				$taxable_total = $nontaxable_total = 0.00;                    
				$pos_id = $pos_onerow->pos_id;
				$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
				$query = $db->query($sqlquery, array());
				if($query){
					$i=0;
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						$i++;
						$pos_cart_id = $row->pos_cart_id;
						$item_id = $row->item_id;
						$item_type = $row->item_type;
						$qty = $row->qty;
						$shipping_qty = $row->shipping_qty;
						if($qty>$shipping_qty){
							$shipping_qty = $qty;
						}
						
						$description = stripslashes(trim((string) $row->description));
						if($item_type =='one_time'){$description .= " [1]";}
						$add_description = stripslashes(trim((string) $row->add_description));
						if($add_description !=''){
							$add_description = nl2br($add_description);
						}
						
						$require_serial_no = $row->require_serial_no;
						$newimei_info = array();
						if($item_type=='cellphones'){
							$sqlitem = "SELECT item.item_number, item.carrier_name, pos_cart_item.sale_or_refund FROM item, pos_cart_item WHERE item.accounts_id = $accounts_id AND item.item_id = pos_cart_item.item_id AND pos_cart_item.pos_cart_id = $pos_cart_id";
							$itemquery = $db->query($sqlitem, array());
							if($itemquery){
								while($newitem_row = $itemquery->fetch(PDO::FETCH_OBJ)){
									$imei_info = $newitem_row->item_number;
									$description = str_replace("$newitem_row->item_number", '',$description);
									
									$carrier_name = $newitem_row->carrier_name;
									if($carrier_name !=''){
										$imei_info .= ' '.$carrier_name;
									}
									
									$sale_or_refund = $newitem_row->sale_or_refund;
									if($sale_or_refund==0){
										$imei_info .= ' (Refund)';
									}
									
									if($imei_info !=''){
										$newimei_info[] = $imei_info;
									}
								}
							}
						}
						elseif($item_type=='product' && $require_serial_no>0){								
							$newimei_info = $Carts->getSerialInfo($pos_cart_id, 'Yes');										
						}
						
						$sales_price = $row->sales_price;
						$discount_is_percent = $row->discount_is_percent;
						$discount = $row->discount;
						
						if($shipping_qty>1 || $shipping_qty<-1){
							$description = "<strong>[$shipping_qty@$currency$sales_price]</strong> $description";
						}
						$total = round($sales_price*$shipping_qty,2);
						if($discount_is_percent>0){
							$discount_value = round($total*0.01*$discount, 2);
						}
						else{ 
							$discount_value = round($discount*$shipping_qty, 2);
						}
						
						$taxable = $row->taxable;																		
						if($taxable>0){
							$taxable_total = $taxable_total+$total-$discount_value;
						}
						else{
							$nontaxable_total = $nontaxable_total+$total-$discount_value;
						}
						
						if($sales_price !=0 || $print_price_zero>0){
							$cartData[] = array('description'=>$description, 'add_description'=>$add_description, 'newimei_info'=>$newimei_info, 'total'=>round($total,2), 'discount_value'=>round($discount_value,2));
						}
					}
				}			
				$jsonResponse['cartData'] = $cartData;

				//----------------------------//				
				$jsonResponse['taxable_total'] = round($taxable_total,2);
				$jsonResponse['taxes_name1'] = $pos_onerow->taxes_name1;			
				$jsonResponse['tax_inclusive1'] = $tax_inclusive1 = intval($pos_onerow->tax_inclusive1);
				$jsonResponse['taxes_percentage1'] = $taxes_percentage1 = floatval($pos_onerow->taxes_percentage1);
				$taxes_total1 = 0;
				if($pos_onerow->taxes_name1 !=''){
					$taxes_total1 = $Common->calculateTax($taxable_total, $taxes_percentage1, $tax_inclusive1);
				}
				$jsonResponse['taxes_total1'] = round($taxes_total1,2);

				$jsonResponse['taxes_name2'] = $pos_onerow->taxes_name2;			
				$jsonResponse['tax_inclusive2'] = $tax_inclusive2 = intval($pos_onerow->tax_inclusive2);
				$jsonResponse['taxes_percentage2'] = $taxes_percentage2 = floatval($pos_onerow->taxes_percentage2);
				$taxes_total2 = 0;
				if($pos_onerow->taxes_name2 !=''){
					$taxes_total2 = $Common->calculateTax($taxable_total, $taxes_percentage2, $tax_inclusive2);
				}
				$jsonResponse['taxes_total2'] = round($taxes_total2,2);
				$jsonResponse['nontaxable_total'] = round($nontaxable_total,2);
				//-------------------------------//
				
				if($tax_inclusive1>0){$taxes_total1 = 0;}
				if($tax_inclusive2>0){$taxes_total2 = 0;}
				$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
				
				$totalpayment = 0;
				$paymentData = array();
				$ppSql = "SELECT payment_method, payment_amount, payment_datetime FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
				$ppQueryObj = $db->query($ppSql, array());
				if($ppQueryObj){
					while($onerow = $ppQueryObj->fetch(PDO::FETCH_OBJ)){
						
						$payment_amount = round($onerow->payment_amount,2);
						
						$totalpayment = $totalpayment+$payment_amount;
						$paymentData[] = array('payment_datetime'=>$onerow->payment_datetime, 'payment_method'=>$onerow->payment_method, 'payment_amount'=>$payment_amount);
					}
				}
				$jsonResponse['paymentData'] = $paymentData;
				$jsonResponse['totalpayment'] = round($totalpayment,2);
				$amountDue = 0;
				if($grand_total>$totalpayment){
					$amountDue = $grand_total-$totalpayment;
					$credit_days = $pos_onerow->credit_days;
					$salesTime = strtotime($pos_onerow->sales_datetime);
					$jsonResponse['amountDueDate'] = date('Y-m-d', strtotime("+$credit_days day", $salesTime));
				}
				$jsonResponse['amountDue'] = round($amountDue,2);
				
				$SmallNotes = array();
				if($notes==1){
					//$SmallNotes = $this->getPublicSmallNotes('pos', $pos_onerow->pos_id);
					$SmallNotes = array();
					$sqlquery = "SELECT n.note, n.created_on AS created_on, n.user_id, 'notes' AS fromTable FROM notes n WHERE n.accounts_id = $accounts_id AND n.table_id = $pos_onerow->pos_id AND n.note_for = 'pos' AND n.publics>0 UNION ALL SELECT ds.note, ds.created_on AS created_on, ds.user_id, 'digital_signature' AS fromTable FROM digital_signature ds WHERE ds.accounts_id = $accounts_id AND ds.table_id = $pos_onerow->pos_id AND ds.for_table = 'pos' ORDER BY created_on DESC";
					$query = $db->query($sqlquery, array());
					$i = 1;								
					if($query){
						$accounts_idname = '';
						$userObj = $db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $user_id", array());
						if($userObj){
							$userOneRow = $userObj->fetch(PDO::FETCH_OBJ);
							$accounts_idname = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
						}
										
						while($onerow = $query->fetch(PDO::FETCH_OBJ)){
							if($onerow->fromTable=='digital_signature') {
								$note = $onerow->note;
							}
							else{
								$note = nl2br(stripslashes($onerow->note));
							}
							$createduser_id = $onerow->user_id;
							$user_name = '';
							if($createduser_id>0){
								if($user_id !=$createduser_id){
									$userObj2 = $db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $createduser_id", array());
									if($userObj2){
										$userOneRow = $userObj2->fetch(PDO::FETCH_OBJ);
										$user_name .= trim("$userOneRow->user_first_name $userOneRow->user_last_name");
									}
								}
								else{
									$user_name .= $accounts_idname;
								}
							}
							else{
								$user_name .= $db->translate('System');
							}

							$SmallNotes[] = array('fromTable'=>$onerow->fromTable, 'note'=>$note, 'created_on'=>$onerow->created_on, 'user_name'=>$user_name);
						}
					}
				}
				$jsonResponse['pos_id'] = intval($pos_onerow->pos_id);
				$jsonResponse['SmallNotes'] = $SmallNotes;
				
				$jsonResponse['invoice_message'] = nl2br(stripslashes($invoice_message));
				$marketing_data = '';
				if($customerObj){
					$varObj = $db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'marketing_data'", array());
					if($varObj){
						$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
						if(!empty($value)){
							$value = unserialize($value);
							extract($value);
							$marketing_data = nl2br(stripslashes(trim((string) $marketing_data)));
						}
					}
				}
				$jsonResponse['marketing_data'] = $marketing_data;
				//=============Json data end================//

				$language = $_SESSION["language"]??'English';
				$currency = $_SESSION["currency"]??'৳';
				$dateformat = $_SESSION["dateformat"]??'m/d/Y';
				if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
				else{$calenderDate = 'MM/DD/YYYY';}
				$timeformat = $_SESSION["timeformat"]??'12 hour';
				$loadLangFile = $_SESSION["language"]??'English';
				
				$htmlStr = '<!DOCTYPE html>
				<html>
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
					<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
					<script language="JavaScript" type="text/javascript">var currency = \''.$currency.'\';var calenderDate = \''.$calenderDate.'\';var timeformat = \''.$timeformat.'\';var loadLangFile = \''.$loadLangFile.'\';
					var langModifiedData = {};
					var OS;
					var segment1 = \'Carts\';
					var segment2 = \'cprints\';
					var segment3 = \'small\';
					var segment4 =  \''.$invoice_no.'\';
					var pathArray = \'/Carts/cprints/small/'.$invoice_no.'/0\'.split(\'/\');
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
					const barcodeCss = `
						@font-face {
							font-family: \'Libre Barcode\';
							src: url(\'/assets/fonts/LibreBarcodeText.woff2\') format(\'woff2\')
						}
						.barcode{
							font-family: \'Libre Barcode\';
							font-size: 40px;
							line-height: 40px;
							white-space:nowrap;
							overflow-wrap: normal;
							display:inline-block;
						}
					`
					'."
					function stripslashes(text) {
						text = text.replace(/\\\'/g, '\'');
						text = text.replace(/\\\\\"/g, '\"');
						text = text.replace(/\\\\0/g, '\\0');
						text = text.replace(/\\\\\\\\/g, '\\\\');
						return text;
					}					
					function Translate(text){						
						return stripslashes(text);
					}
					
					function cTag(tagName, attributes){
						let node = document.createElement(tagName);
						if(attributes){
							 for(const [key, value] of Object.entries(attributes)) {
								  if(typeof value === 'function') node.addEventListener(key,value);
							  else node.setAttribute(key, value);
							 }
						}
						return node;
				  	}

					function DBDateToViewDate(datetime, arrayYN=0, shortYear=0){
						let dateValue, timeValue;
						dateValue = timeValue = '';
						if(['0000-00-00', '1000-01-01', '0000-00-00 00:00:00', '1000-01-01 00:00:00'].includes(datetime)){datetime = '';}
					
						if(datetime.length >= 10 && ['0000-00-00', '1000-01-01'].includes(datetime)===false){
							let [yyyy, mm, dd] = datetime.substring(0, 10).split('-');
							if(shortYear==1){yyyy = yyyy.substring(2, 4)}
							if(calenderDate.toLowerCase()==='dd-mm-yyyy'){dateValue = dd+'-'+mm+'-'+yyyy;}
							else{dateValue = dd+'/'+mm+'/'+yyyy;}
					
							if(datetime.length>10){
								let [hh,ii] = datetime.substring(11, 16).split(':');
								hh = parseInt(hh);
								if(timeformat.toLowerCase()==='24 hour'){timeValue = hh+':'+ii;}
								else{
									let ampm = 'am';
									if(hh>11){ampm = 'pm';}
									if(hh>12){hh = hh-12;}
									timeValue = hh+':'+ii+' '+ampm;
								}
							}
						}
					
						if(arrayYN===1){
							return [dateValue, timeValue];
						}
						if(timeValue !==''){
							return dateValue+' '+timeValue;
						}
						return dateValue;
					}

					function generateImeiInfo (parentNode, newimei_info) {
						if(Array.isArray(newimei_info)){
							 newimei_info.forEach(imeiItem=>{
								  let pTag = cTag ('p',{style: 'margin: 0; padding-left: 10px;'});
								  pTag.innerHTML = imeiItem;
								  parentNode.appendChild(pTag);
							 })
						}
				  	}

					function addCurrency(amount){
						if(amount>=0){
							return currency+number_format(amount);
						}	
						return '-'+currency+number_format(amount*(-1));
					}

					function number_format(number){
						const roundNumber = round(number,2).toFixed(2);
						const parts = roundNumber.toString().split(".");
						return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, \",\") + (parts[1] ? \".\" + parts[1] : \"\");
				  	}
					
					function round(number,scale){
						if(scale===undefined) throw new Error(`Invalid Round Scale: \${scale}`)
					
						let [integer,fraction] = number.toString().split('.');
						const numberType = integer.search('-')===0?'-':'+';
						integer = Math.abs(integer);
						if(fraction && fraction.length>scale){
						  fraction = fraction.slice(0,scale+1);
						  let fraction_controllDigit = Number(fraction[scale]);
						  fraction = fraction.slice(0,scale);
						  if(fraction_controllDigit>=5){
							fraction = roundUp(fraction);
							if(fraction>=Math.pow(10,scale)){
							  integer++;
							  fraction = 0;
							}
						  }
						}
						integer = numberType==='+'?`+\${integer}`:`-\${integer}`;
						fraction = fraction?`.\${fraction}`:``;
						return Number(`\${integer}\${fraction}`);
					
						function roundUp(fraction){
						  let digitsInfraction = fraction.split('').map(digit=>Number(digit));
						  const LastDigitIndex = digitsInfraction.length-1;
						  digitsInfraction[LastDigitIndex] += 1;//rounding up
						  if(digitsInfraction[LastDigitIndex]>9){
							const slicedDecimalPart = digitsInfraction.slice(0,LastDigitIndex).join('');
							if(LastDigitIndex===0) return '10';
							else return roundUp(slicedDecimalPart)+'0';
						  }
						  else return digitsInfraction.join('');
						}
					}

					function calculate(operation,Number1,Number2,roundScale){
						//throwing error passing invalid params
						if(!['add','sub','mul','div'].includes(operation)) throw new Error(`Invalid operator keyword: \${operation}`)
						 else if(Number1===undefined || Number2===undefined) throw new Error(`Missing operand (First-Operand:\${Number1} Second-Operand:\${Number2})`)
						 else if(roundScale===undefined) throw new Error(`Invalid Round Scale: \${roundScale}`)
					
					
						 Number1 = RNumber(Number1);
						 Number2 = RNumber(Number2);
						 let largestDenominator = Math.max(Number1.Denominator,Number2.Denominator);
						 let results;
						 if(operation==='mul'){
							  results = (Number1.Numerator * Number2.Numerator)/(Number1.Denominator * Number2.Denominator);
						 }
						 else if(operation==='div'){
							  results = (Number1.Numerator * (largestDenominator/Number1.Denominator)) / (Number2.Numerator * (largestDenominator/Number2.Denominator));
						 }
						 else{
							  Number1 = Number1.Numerator*(largestDenominator/Number1.Denominator);
							  Number2 = Number2.Numerator*(largestDenominator/Number2.Denominator);
							  if(operation==='add') results = (Number1 + Number2)/largestDenominator;
							  if(operation==='sub') results = (Number1 - Number2)/largestDenominator;
						 }
					
						if(roundScale===false) return results;
						 return round(results,roundScale);
						
						 function RNumber(number){      
							  let [integer, fraction=''] = number.toString().split('.');
							  return {
									Numerator:Number(integer+fraction),
									Denominator: Math.pow(10,fraction.length)
							  }    
						 }
					}

					function encodeToCode128(textToEncode){
						let textChars = textToEncode.split('');
						let startChar = String.fromCharCode(204);
						let endChar = String.fromCharCode(206);
						let checkSum = 104;
						textChars.forEach((char,indx)=>{
						  let charValue = char.charCodeAt()-32; //according to code128 character-set-table code128-value is 32 lesser then ASCII-value
						  checkSum += (charValue*(indx+1));
						});
						checkSum = (checkSum%103)+32;
						if(checkSum>126) checkSum += 68;
						let checkChar = String.fromCharCode(checkSum);
						return startChar+textToEncode+checkChar+endChar;
				  	}
					
					function creatCompanylogo(imgSource,logo_size,wrappedByTr){
						let logo;
						imgSource = imgSource.replace('///', '/')
						//alert(imgSource);
						if(wrappedByTr){
							logo = cTag('tr');
								const tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
								tdCol.appendChild(cTag('img',{ 'style':`max-height:100px;max-width:100%;`,'src':imgSource,'title':Translate('Logo') }));
							logo.appendChild(tdCol);
						}
						else{
							let style;
							if(logo_size==='Large Logo'){
								style = 'max-height:150px;max-width:350px;';
							}
							else{
								style = 'max-height:100px;max-width:150px;';
							}
							
							logo = cTag('img',{'style':style,'src':imgSource,'title':Translate('Logo')});
						}
						return logo;
					}

					".'
					</script>
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
					
				$htmlStr .= "</head>
				<body>
					<div id=\"viewPageInfo\"></div>
					<script>					
						const data = ".json_encode($jsonResponse).";
						let amount_due = 0;
						let tableHeadRow, tdCol, thCol;
						const head = document.head;
						const title = cTag('title');
						if(data.title !=''){
								title.innerHTML = data.title;
						}
						else{
								title.innerHTML = 's'+data.invoice_no;
						}
						title.append(' ', data.printerName);
						head.appendChild(title);
							const style = cTag('style');
							style.setAttribute('type','text/css');
							let addCss = 'size:'+data.orientation+';margin-top:'+data.top_margin+'px;margin-bottom:'+data.bottom_margin+'px;';
							if(data.right_margin!==0) addCss+= 'margin-right:'+data.right_margin+'px;';
							if(data.left_margin!==0) addCss+= 'margin-left:'+data.left_margin+'px;';
							style.append(
									'*{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica;font-size: 11px;}'+
									'body{width:100%; margin:0; padding:0;background:#fff;color:#000;}'+
									'@page {'+addCss+'}}'+
									'table{border-collapse:collapse;}'+
									'tr.border td, tr.border th{ border:1px solid #CCC; padding:2px; vertical-align: top;}'+
									barcodeCss
							);
						head.appendChild(style);

						const Dashboard = document.querySelector('#viewPageInfo');
						Dashboard.innerHTML = '';

						const smallOrderTable = cTag('table',{ 'align':'center','width':'99.75%','cellpadding':'0','cellspacing':'0' });
						if(data.companylogo !=='') smallOrderTable.appendChild(creatCompanylogo(data.companylogo,data.logo_size,true));
							tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'align':'center','colspan':'2' });
										const headerTitle = cTag('h2');
										headerTitle.innerHTML = data.title;
									tdCol.appendChild(headerTitle);
							tableHeadRow.appendChild(tdCol);
						smallOrderTable.appendChild(tableHeadRow);
							tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'align':'center','colspan':'2' });
										const address = cTag('address');
										address.innerHTML = data.company_info;
									tdCol.appendChild(address);
							tableHeadRow.appendChild(tdCol);
						smallOrderTable.appendChild(tableHeadRow);
							tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'align':'left' });
									tdCol.innerHTML = DBDateToViewDate(data.sales_datetime, 1)[0];
							tableHeadRow.appendChild(tdCol);
									tdCol = cTag('td',{ 'align':'right','nowrap':'' });
										let dateTimeSpan = cTag('span');
										dateTimeSpan.innerHTML = DBDateToViewDate(data.sales_datetime, 1)[1];
									tdCol.appendChild(dateTimeSpan);
							tableHeadRow.appendChild(tdCol);
						smallOrderTable.appendChild(tableHeadRow);
						if(data.salesPerson !== ''){
							tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'align':'left','colspan':'2' });
										const strong = cTag('strong');
										strong.innerHTML = Translate('Sales Person')+' : ';
									tdCol.appendChild(strong);
									tdCol.append(data.salesPerson);
							tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
						}
							tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'align':'left','colspan':'2' });
									if(data.customerName.length){
										data.customerName.forEach((name,indx)=>indx>0?tdCol.append(cTag('br'),name):tdCol.append(name));
									} 
									if(data.customerAddress.length){
										data.customerAddress.forEach(info=>{
											tdCol.append(cTag('br'),info);
										})
									} 
									if(data.contactNo.length){
										data.contactNo.forEach(info=>{
											tdCol.append(cTag('br'),info);
										})
									} 
									if(data.customerEmail.length){
										data.customerEmail.forEach(info=>{
											tdCol.append(cTag('br'),info);
										})
									} 
									if(data.customData){
										for (const key in data.customData) {
											tdCol.append(cTag('br'), key+':'+data.customData[key]);
										}
									}
							tableHeadRow.appendChild(tdCol);
						smallOrderTable.appendChild(tableHeadRow);
								tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'align':'left','colspan':'2' });
									let barcodeStr = document.createDocumentFragment();
									let invoice_noStr = document.createDocumentFragment();
									let invoicePreStr;
									if(data.fromPage ==='Orders'){
										const strong = cTag('strong');
										strong.innerHTML = Translate('Order No.')+':o'+data.invoice_no;
										invoice_noStr.appendChild(strong);
										invoicePreStr = 'o';
									}
									else{
										const strong = cTag('strong');
										strong.innerHTML = Translate('Sale Invoice #: s')+data.invoice_no;
										invoice_noStr.appendChild(strong);
										invoicePreStr = 's';
									}
									if(data.barcode===1){
											let barcode = cTag('span',{class:'barcode','style':'display:block'});
											barcode.innerHTML = encodeToCode128(String(invoicePreStr+data.invoice_no));
										barcodeStr.appendChild(barcode);
									}
									tdCol.append(invoice_noStr,barcodeStr);
							tableHeadRow.appendChild(tdCol);
						smallOrderTable.appendChild(tableHeadRow);
								tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'colspan':'2','align':'left','style':'padding-bottom:10px;' });
									tdCol.innerHTML = data.invoice_message_above;
							tableHeadRow.appendChild(tdCol);
						smallOrderTable.appendChild(tableHeadRow);
								tableHeadRow = cTag('tr',{ 'class':'border' });
									thCol = cTag('th',{ 'align':'left' });
									thCol.innerHTML = Translate('Description');
							tableHeadRow.appendChild(thCol);
									thCol = cTag('th',{ 'width':'10%' });
									thCol.innerHTML = Translate('Total');
							tableHeadRow.appendChild(thCol);
						smallOrderTable.appendChild(tableHeadRow);

						if(data.cartData){
							if(data.cartData.length>0){
									data.cartData.forEach(item=>{
											tableHeadRow = cTag('tr',{ 'class':'border' });
													tdCol = cTag('td',{ 'align':'left','valign':'top' });
													tdCol.innerHTML = item.description;                                              
													if(item.add_description !=''){
														let addDesHTML = cTag('div', {class:'flex'});
														addDesHTML.innerHTML = item.add_description;
														tdCol.appendChild(addDesHTML);
													}
													generateImeiInfo(tdCol, item.newimei_info);
											tableHeadRow.appendChild(tdCol);
													tdCol = cTag('td',{ 'align':'right','nowrap':'','valign':'top' });
													tdCol.innerHTML = addCurrency(item.total);
													if(item.discount_value>0){
														tdCol.append(cTag('br'),'-'+addCurrency(item.discount_value));
													}
													else if(item.discount_value<0){
														tdCol.append(cTag('br'),addCurrency(item.discount_value*(-1)));
													}
											tableHeadRow.appendChild(tdCol);
										smallOrderTable.appendChild(tableHeadRow);
									})
							}
							else{
										tableHeadRow = cTag('tr',{ 'class':'border' });
											tdCol = cTag('td',{ 'colspan':'2' });
											tdCol.innerHTML = Translate('There is no data found');
										tableHeadRow.appendChild(tdCol);
									smallOrderTable.appendChild(tableHeadRow);
							}
						}
						else{
									tableHeadRow = cTag('tr',{ 'class':'border' });
										tdCol = cTag('td',{ 'colspan':'2' });
										tdCol.innerHTML = Translate('There is no data found');
									tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
						}

						let ti1Str = '';
						let taxes_total1 = data.taxes_total1;
						if(data.tax_inclusive1>0) {
							ti1Str = ' Inclusive';
							taxes_total1 = 0;
						}
						if(data.taxes_name1 !== ''){
									tableHeadRow = cTag('tr',{ 'class':'border' });
										tdCol = cTag('td',{ 'align':'right' });
										tdCol.innerHTML = Translate('Taxable Total');
									tableHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ 'align':'right' });
										tdCol.innerHTML = addCurrency(data.taxable_total);
									tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
									tableHeadRow = cTag('tr',{ 'class':'border' });
										tdCol = cTag('td',{ 'align':'right' });
										tdCol.innerHTML = data.taxes_name1+' ('+data.taxes_percentage1+'%'+ti1Str+') :';
									tableHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ 'align':'right' });
										tdCol.innerHTML = addCurrency(data.taxes_total1);
									tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
						}

						let ti2Str = '';
						let taxes_total2 = data.taxes_total2;
						if(data.tax_inclusive2>0) {
							ti2Str = ' Inclusive';
							taxes_total2 = 0;
						}
						if(data.taxes_name2 !==''){
									tableHeadRow = cTag('tr',{ 'class':'border' });
										tdCol = cTag('td',{ 'align':'right' });
										tdCol.innerHTML = data.taxes_name2+' ('+data.taxes_percentage2+'%'+ti2Str+') :';
									tableHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ 'align':'right' });
										tdCol.innerHTML = addCurrency(data.taxes_total2);
									tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
						}
						if(data.nontaxable_total>0 || data.nontaxable_total<0){
									tableHeadRow = cTag('tr',{ 'class':'border' });
										tdCol = cTag('td',{ 'align':'right' });
										tdCol.innerHTML = Translate('Non Taxable Total');
									tableHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ 'align':'right' });
										tdCol.innerHTML = addCurrency(data.nontaxable_total);
									tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
						}
						const grand_total = calculate('add',calculate('add',data.taxable_total,taxes_total1,2),calculate('add',taxes_total2,data.nontaxable_total,2),2);
							tableHeadRow = cTag('tr',{ 'class':'border' });
									tdCol = cTag('td',{ 'align':'right' });
									tdCol.innerHTML = Translate('Grand Total');
							tableHeadRow.appendChild(tdCol);
									tdCol = cTag('td',{ 'align':'right' });
									tdCol.innerHTML = addCurrency(grand_total);
							tableHeadRow.appendChild(tdCol);
						smallOrderTable.appendChild(tableHeadRow);

						if(data.paymentData){
							data.paymentData.forEach(item=>{
										tableHeadRow = cTag('tr',{ 'class':'border' });
											tdCol = cTag('td',{ 'align':'right' });
											tdCol.innerHTML = DBDateToViewDate(item.payment_datetime)+' '+item.payment_method+' '+Translate('Payment');
										tableHeadRow.appendChild(tdCol);
											tdCol = cTag('td',{ 'align':'right' });
											tdCol.innerHTML = addCurrency(item.payment_amount);
										tableHeadRow.appendChild(tdCol);
									smallOrderTable.appendChild(tableHeadRow);
							})
						}
						if(data.amountDue !==0){
									tableHeadRow = cTag('tr',{ 'class':'border' });
										tdCol = cTag('td',{ 'align':'right','nowrap':'' });
										if(segment1 === 'Orders') tdCol.innerHTML = Translate('Amount Due');
										else tdCol.innerHTML = Translate('Total amount due by')+' '+DBDateToViewDate(data.amountDueDate, 0, 1);
									tableHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ 'align':'right' });
										tdCol.innerHTML = addCurrency(data.amountDue);
									tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
						}
						if(amount_due<0){
									tableHeadRow = cTag('tr',{ 'class':'border' });
										tdCol = cTag('td',{ 'colspan':'2','align':'center' });
										tdCol.innerHTML = Translate('Please give change amount of')+' '+addCurrency(amount_due*(-1));
									tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
						}
						if(data.SmallNotes.length>0){
									tableHeadRow = cTag('tr');
										tdCol = cTag('td',{ 'align':'center','colspan':'2' });
										getPublicSmallNotes(tdCol,data.SmallNotes);
									tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
						}

							tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'align':'center','colspan':'2' });
									tdCol.appendChild(cTag('br'));
										const pTag = cTag('p');
										pTag.innerHTML = data.invoice_message;
									tdCol.appendChild(pTag);
							tableHeadRow.appendChild(tdCol);
						smallOrderTable.appendChild(tableHeadRow);

						if(data.marketing_data !== ''){
									tableHeadRow = cTag('tr');
										tdCol = cTag('td',{ 'align':'center','colspan':'2' });
										tdCol.innerHTML = data.marketing_data;
									tableHeadRow.appendChild(tdCol);
							smallOrderTable.appendChild(tableHeadRow);
						}
						Dashboard.appendChild(smallOrderTable);
						setTimeout(()=>{
							window.print();
							setTimeout(()=>{if(OS==='unknown') window.close()}, 100);
					  },1000)
					</script>
				</body>
				</html>";
				echo $htmlStr;
				exit;
			}
			$apiResponse['responseStr'] = $responseStr;
		}
		elseif ($module=='po'){
			$responseStr = '';
			$po_number = base64_decode($_REQUEST['poNo']);
			$responseStr = "<p>PO No: p$po_number</p>";
			$po_id = 0;
			$poObj = $db->query("SELECT po_id FROM po WHERE accounts_id = $accounts_id AND po_number = :po_number", array('po_number'=>$po_number),1);
			if($poObj){
				$po_id = $poObj->fetch(PDO::FETCH_OBJ)->po_id;
			}
			if($po_id>0){
				$Printing = new Printing($db);
				echo $Printing->poInvoicesInfo($po_id, 'Widget', 1, $accounts_id);
				exit;
			}
			$apiResponse['responseStr'] = $responseStr;
		}
		elseif ($module=='repair_status'){

			$POST = json_decode(file_get_contents('php://input'), true);	
			//===Check Repair Status===//
			if (is_array($POST) && array_key_exists('firstName', $POST)) {
				//===Check Repair Status Form Submit===//
				$first_name = trim((string) $POST['firstName']);		
				$ticket_no = str_replace('t', '', trim((string) strtolower($POST['ticketNo'])));
				$phpValidMsg = '';
				if(empty($first_name)){
					$phpValidMsg .= '<p style="color:red;">'.$db->translate('First Name is required.').'</p>';
				}
				if(empty($ticket_no)){
					$phpValidMsg .= '<p style="color:red;">'.$db->translate('Ticket Number is required.').'</p>';
				}
				if(!empty($phpValidMsg)){
					$apiResponse['error'] = $phpValidMsg;
				}
				else{
					$apiResponse['error'] = '';
					$responseStr = '';
					date_default_timezone_set($timezone);

					$strextra = "FROM repairs r, customers c WHERE r.accounts_id = $accounts_id and r.customer_id = c.customers_id";
					$bindData = array();				
					$strextra .= " and c.first_name = :first_name";
					$bindData['first_name'] = $first_name;
				
					$strextra .= " and r.ticket_no = :ticket_no";
					$bindData['ticket_no'] = $ticket_no;
					
					$strextra .= " ORDER BY c.first_name ASC, c.last_name ASC";
					$sqlquery = "SELECT r.repairs_id, r.status, r.due_datetime, r.due_time $strextra limit 0, 1";
					
					$queryObj = $db->query($sqlquery, $bindData);
					if($queryObj){
						$rowrepairs = $queryObj->fetch(PDO::FETCH_OBJ);
						$responseStr .= '<p style="color:green;">';
						$responseStr .= '<br />'.$db->translate('Status').': <button style="color: #fff;background-color: #5cb85c;border-color: #4cae4c;display: inline-block;padding: 6px 12px;margin-bottom: 0;font-size: 14px;font-weight: 400;line-height: 1.42857143;text-align: center;white-space: nowrap;vertical-align: middle;border: 1px solid transparent;border-top-color: transparent;border-right-color: transparent;border-bottom-color: transparent;border-left-color: transparent;border-radius: 4px;">'.$rowrepairs->status.'</button>';
						if(!in_array($rowrepairs->due_datetime, array('0000-00-00', '1000-01-01'))){
							$responseStr .= '<br />'.$db->translate('Due Date').': '.date($dateformat, strtotime($rowrepairs->due_datetime)).' '.$rowrepairs->due_time;
						}
						
						$sqlquery = "SELECT n.note, n.created_on AS created_on, n.user_id, 'notes' AS fromTable  FROM notes n WHERE n.accounts_id = $accounts_id AND n.table_id = $rowrepairs->repairs_id AND n.note_for = 'repairs' AND n.publics>0 UNION ALL SELECT ds.note, ds.created_on AS created_on, ds.user_id, 'digital_signature' AS fromTable FROM digital_signature ds WHERE ds.accounts_id = $accounts_id AND ds.table_id = $rowrepairs->repairs_id AND ds.for_table = 'repairs' ORDER BY created_on DESC";		
						$noteObj = $db->query($sqlquery, array());
						if($noteObj){
							$i = 1;
							$responseStr .= '<br /><br /><strong>'.$db->translate('Note information').':</strong>';
							while($onerow = $noteObj->fetch(PDO::FETCH_OBJ)){
								
								if($onerow->fromTable=='digital_signature') {
									$note = '<div style="width:100%"></div><img style="max-width:100%;" alt="'.$db->translate('Signature').'" src="'.$onerow->note.'">';
								}
								else{						
									$note = nl2br(stripslashes($onerow->note));
								}
								
								if($timeformat=='24 hour'){$created_on =  date($dateformat.' H:i', strtotime($onerow->created_on));}
								else{$created_on =  date($dateformat.' g:i a', strtotime($onerow->created_on));}
													
								$user_id = $onerow->user_id;
								
								$user_name = '<strong>'.$created_on.' '.$db->translate('By').' ';
								
								if($user_id>0){
									$userObj = $db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = :user_id", array('user_id'=>$user_id),1);
									if($userObj){
										$userOneRow = $userObj->fetch(PDO::FETCH_OBJ);
										$user_name .= "$userOneRow->user_first_name $userOneRow->user_last_name";
									}
								}
								else{
									$user_name .= $db->translate('System').'</strong>';
								}
								$user_name .= '<br>';
								
								$border = '';
								if($i>1){$border = '<hr/>';}
								
								$responseStr .= "<article style=\"margin-top: 20px;margin: 0;width:100%; float:left;\">
											<div style=\"padding-top: 5px;word-wrap: break-word;font-size: 14px;line-height: 21px;width: 100%;\">
												$border
												$user_name
												$note
											</div>
										</article>";
										
								$i++;
							}
						}
					
						$responseStr .= '</p>';
					}
					else{
						$responseStr .= '<p style="color:red;">'.$db->translate('There is no ticket found.').'</p>';
					}
					$apiResponse['responseStr'] = $responseStr;					
				}
			}
			else{
				//===Load Repair Status Form===//
				$apiResponse['error'] = '';
				$bg_color = '#ffffff';
				$color = '#363947';
				$font_family = 'Arial';
				$but_bg_color = '#ef7f1b';
				$but_color = '#FFFFFF';
				$but_font_family = 'Arial';
			
				$varObj = $db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'RStatus'", array());
				if($varObj){
					$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
					$value = $variablesData->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				$apiResponse['bg_color'] = $bg_color;
				$apiResponse['color'] = $color;
				$apiResponse['font_family'] = $font_family;
				$apiResponse['but_bg_color'] = $but_bg_color;
				$apiResponse['but_color'] = $but_color;
				$apiResponse['but_font_family'] = $but_font_family;
			}
		}
		elseif ($module=='customer'){
			$prod_cat_man = $accounts_id;
			$accountsObj2 = $db->query("SELECT location_of FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accountsObj2){
				$location_of = $accountsObj2->fetch(PDO::FETCH_OBJ)->location_of;
				if($location_of>0){$prod_cat_man = $location_of;}
			}
			$fieldNames = array('email'=>1, 'offers_email'=>1, 'company'=>1, 'contact_no'=>1, 'secondary_phone'=>1, 'fax'=>1, 'shipping_address_one'=>1, 'shipping_address_two'=>1, 'shipping_city'=>1, 'shipping_state'=>1, 'shipping_zip'=>1, 'shipping_country'=>1, 'website'=>1);
			$customFields = array();
			$queryCFObj = $db->query("SELECT * FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
			if($queryCFObj){
				$l=0;
				while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
					$customFields[] = $oneCustomFields;
					$fieldNames['cf'.$oneCustomFields->custom_fields_id] = 0;
					$l++;
				}
			}

			
			$apiResponse['dateformat'] = $dateformat;
			
			$queryObj = $db->query("SELECT value FROM variables WHERE accounts_id=$accounts_id AND name='Customer'", array());
			if($queryObj){
				$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$value = unserialize($value);
					if(array_key_exists('fieldNames', $value)){
						$fieldNames = $value['fieldNames'];
					}
				}
			}
			
			$activeFieldNames = array();
			if(!empty($fieldNames)){
				foreach($fieldNames as $key=>$val){
					if($fieldNames[$key]>0){
						$activeFieldNames[$key] = $val;
					}
				}
			}
			$apiResponse['activeFieldNames'] = $activeFieldNames;
			//===Check Submit Data===//
			$POST = json_decode(file_get_contents('php://input'), true);	
			if (is_array($POST) && array_key_exists('first_name', $POST)) {
				//===Check Repair Status Form Submit===//
				
				$first_name = trim((string) $POST['first_name']??'');		
				$last_name = trim((string) $POST['last_name']??'');		
				$phpValidMsg = '';
				if(empty($first_name)){
					$phpValidMsg .= '<p style="color:red;">'.$db->translate('First Name is required.').'</p>';
				}
								
				if(!empty($phpValidMsg)){
					$apiResponse['error'] = $phpValidMsg;
				}
				else{
					$user_id = 0;
					$accountsObj = $db->query("SELECT user_id FROM user WHERE accounts_id = $accounts_id AND is_admin = 1", array());
					if($accountsObj){
						$user_id = $accountsObj->fetch(PDO::FETCH_OBJ)->user_id;
					}
					$email = trim((string) $POST['email']??'');
					$email = $db->checkCharLen('customers.email', $email);
					
					$contact_no = trim((string) $POST['contact_no']??'');
					$contact_no = $db->checkCharLen('customers.contact_no', $contact_no);
					
					$offers_email = intval($POST['offers_email']??0);
											
					$apiResponse['error'] = '';
					$responseStr = '';
					
					$bindData = array();
					$bindData['first_name'] = $first_name;
					$bindData['last_name'] = $last_name;		
					$dupsql = "email = :email";
					$bindData['email'] = $email;
					if($email==''){
						$dupsql = "contact_no = :email";
						$bindData['email'] = $contact_no;
					}
					
					$totalrows = 0;
					$queryObj = $db->query("SELECT COUNT(customers_id) AS totalrows FROM customers WHERE accounts_id = $prod_cat_man AND first_name = :first_name AND last_name = :last_name AND $dupsql", $bindData);
					if($queryObj){
						$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
					}
					
					if($totalrows>0){
						$responseStr = $db->translate('This name and email already exist. Try again with a different name/email.').'';
					}
					else{
						$custom_data = array();
						if(!empty($customFields)){
							foreach($customFields AS $oneCustomFields){								
								$customFieldName = 'cf'.$oneCustomFields->custom_fields_id;
								if(array_key_exists($customFieldName, $activeFieldNames)){
									$custom_data[$oneCustomFields->field_name] = trim((string) $POST[$customFieldName]);
								}
							}
						}
						$company = addslashes(trim((string) array_key_exists('company', $POST) ? $POST['company'] : ''));
						$secondary_phone = addslashes(trim((string) array_key_exists('secondary_phone', $POST) ? $POST['secondary_phone'] : ''));
						$fax = addslashes(trim((string) array_key_exists('fax', $POST) ? $POST['fax'] : ''));
						$shipping_address_one = addslashes(trim((string) array_key_exists('shipping_address_one', $POST) ? $POST['shipping_address_one'] : ''));
						$shipping_address_two = addslashes(trim((string) array_key_exists('shipping_address_two', $POST) ? $POST['shipping_address_two'] : ''));
						$shipping_city = addslashes(trim((string) array_key_exists('shipping_city', $POST) ? $POST['shipping_city'] : ''));
						$shipping_state = addslashes(trim((string) array_key_exists('shipping_state', $POST) ? $POST['shipping_state'] : ''));
						$shipping_zip = addslashes(trim((string) array_key_exists('shipping_zip', $POST) ? $POST['shipping_zip'] : ''));
						$shipping_country = addslashes(trim((string) array_key_exists('shipping_country', $POST) ? $POST['shipping_country'] : ''));
						$website = addslashes(trim((string) array_key_exists('website', $POST) ? $POST['website'] : ''));						
						
						$customersdata = array( 'created_on' => date('Y-m-d H:i:s'),
												'last_updated' => date('Y-m-d H:i:s'),
												'accounts_id'=>$prod_cat_man,
												'user_id'=>$user_id,
												'first_name'=>$first_name,
												'last_name'=>$last_name,
												'email'=>$email,
												'company'=>$db->checkCharLen('customers.company', $company),
												'contact_no'=>$contact_no,
												'secondary_phone'=>$db->checkCharLen('customers.secondary_phone', $secondary_phone),
												'fax'=>$db->checkCharLen('customers.fax', $fax),
												'customer_type'=>'',
												'shipping_address_one'=>$db->checkCharLen('customers.shipping_address_one',$shipping_address_one),
												'shipping_address_two'=>$db->checkCharLen('customers.shipping_address_two', $shipping_address_two),
												'shipping_city'=>$db->checkCharLen('customers.shipping_city', $shipping_city),
												'shipping_state'=>$db->checkCharLen('customers.shipping_state', $shipping_state),
												'shipping_zip'=>$db->checkCharLen('customers.shipping_zip', $shipping_zip),
												'shipping_country'=>$db->checkCharLen('customers.shipping_country', $shipping_country),
												'offers_email'=>$offers_email,
												'website'=>$db->checkCharLen('customers.website', $website),
												'credit_limit'=>0,
												'credit_days'=>0,
												'custom_data'=>serialize($custom_data),
												'alert_message'=>''
												);
						
						$customers_id = $db->insert('customers', $customersdata);
					
						if($customers_id){
							$str = "<p style=\"color:green;\">$first_name $last_name";
							if($email !=''){
								$str .= " ($email)";
							}
							elseif($contact_no !=''){
								$str .= " ($contact_no)";
							}						
							$responseStr = $str.' '.$db->translate(' has been successfully saved.').'</p>';
						}
						else{
							$responseStr = '<p style=\"color:red;\">'.$db->translate('error occurred while adding new customer! please try again.').'</p>';
						}
					}
					
					$apiResponse['responseStr'] = $responseStr;					
				}
			}
			else{
				//===Load Repair Status Form===//
				$apiResponse['error'] = '';
				$bg_color = '#ffffff';
				$color = '#363947';
				$font_family = 'Arial';
				$but_bg_color = '#ef7f1b';
				$but_color = '#FFFFFF';
				$but_font_family = 'Arial';
				
				$varObj = $db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'Customer'", array());
				if($varObj){
					$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
					$value = $variablesData->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				
				$apiResponse['bg_color'] = $bg_color;
				$apiResponse['color'] = $color;
				$apiResponse['font_family'] = $font_family;
				$apiResponse['but_bg_color'] = $but_bg_color;
				$apiResponse['but_color'] = $but_color;
				$apiResponse['but_font_family'] = $but_font_family;
				
				$fieldLabels = array('email'=>$db->translate('Email Address'), 'offers_email'=>$db->translate('Offers Email'), 'company'=>$db->translate('Company'), 'contact_no'=>$db->translate('Phone No'), 'secondary_phone'=>$db->translate('Secondary Phone'), 'fax'=>$db->translate('Fax'), 'shipping_address_one'=>$db->translate('Address Line 1'), 'shipping_address_two'=>$db->translate('Address Line 2'), 'shipping_city'=>$db->translate('City / Town'), 'shipping_state'=>$db->translate('State / Province'), 'shipping_zip'=>$db->translate('Zip/Postal Code'), 'shipping_country'=>$db->translate('Country'), 'website'=>$db->translate('Website'));
				$CFNames = $CFDetails = array();
				$CFNames[] = 'first_name';
				$CFDetails['first_name'] = array($db->translate('First Name'), 'TextBox', '', '*');
				$CFNames[] = 'last_name';
				$CFDetails['last_name'] = array($db->translate('Last Name'), 'TextBox', '', '*');
				if(!empty($activeFieldNames)){
					foreach($activeFieldNames as $key=>$val){
						if(array_key_exists($key, $fieldLabels)){
							$CFNames[] = $key;
							$field_type = 'TextBox';
							if($key=='offers_email'){$field_type = 'Checkbox';}
							
							$CFDetails[$key] = array($fieldLabels[$key], $field_type, '', '');
						}
					}
				}
				
				$CFCount = 0;
				if(!empty($customFields)){
					foreach($customFields AS $oneCustomFields){
						$custom_fields_id = $oneCustomFields->custom_fields_id;
						$field_name = stripslashes($oneCustomFields->field_name);
						$field_type = stripslashes($oneCustomFields->field_type);
						$parameters = stripslashes($oneCustomFields->parameters);
						$field_required = stripslashes($oneCustomFields->field_required);
						$required = '';
						if($field_required>0){$required = '*';}
						
						$customFieldName = 'cf'.$oneCustomFields->custom_fields_id;
						if(array_key_exists($customFieldName, $activeFieldNames)){
							$CFNames[] = $customFieldName;
							$CFDetails[$customFieldName] = array($field_name, $field_type, $parameters, $required);
							$CFCount++;
						}
					}
				}
				
				$apiResponse['CFCount'] = $CFCount;
				$apiResponse['CFNames'] = $CFNames;
				$apiResponse['CFDetails'] = $CFDetails;
			}
		}
		elseif ($module=='appointment'){
			//===Check Repair Status===//
			$POST = json_decode(file_get_contents('php://input'), true);	
			if (is_array($POST) && array_key_exists('fieldCount', $POST)) {
				//===Check Repair Status Form Submit===//
				$phpValidMsg = '';
				$fieldCount = intval($POST['fieldCount']??0);
				if(empty($fieldCount)){$phpValidMsg .= '<p>'.$db->translate('Field is required.').'</p>';}
				$fieldNames = $db->translate('Name').'||'.$db->translate('Phone No').'||'.$db->translate('Email').'||'.$db->translate('Brand and model of device').'||'.$db->translate('What needs to be fixed');
				$varObj = $db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'Appointment'", array());
				if($varObj){
					$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
					$value = $variablesData->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('fieldNames', $value)){
							$fieldNames = $value['fieldNames'];
						}
					}
				}
				$fieldsName = explode('||', $fieldNames);
				$serInfo = array('1st', '2nd', '3rd', '4th');
				$description = '';
				$name = $email = '';
				if($fieldCount>0){
					for($l=0; $l<$fieldCount; $l++){
						$fieldVal = $POST['field'.$l.'Val']??'';
						$LabelName = $fieldsName[$l]??$l;
						if($l<=2 && empty($fieldVal)){							
							$phpValidMsg .= '<p style="color:red;">'.$LabelName.' '.$db->translate('Field is required.').'</p>';
						}
						if(strtoupper($LabelName)=='NAME'){$name = $fieldVal;}
						if(strtoupper($LabelName)=='EMAIL'){$email = $fieldVal;}
						$description .= '<b>'.$LabelName.'</b>: '.$fieldVal.'
';
					}
				}
				
				$appdate = addslashes(trim((string) $POST['appdate']??''));
				if(empty($appdate)){$phpValidMsg .= '<p style="color:red;">'.$db->translate('Date field is required.').'</p>';}
				
				$hourMinute = addslashes(trim((string) $POST['hourMinute']??''));
				if(empty($hourMinute)){$phpValidMsg .= '<p style="color:red;">'.$db->translate('Hour field is required.').'</p>';}
				
				if(!empty($phpValidMsg)){
					$apiResponse['error'] = $phpValidMsg;
				}
				else{
					$apiResponse['error'] = '';
					$responseStr = '';
					$description .= $db->translate('Date time to meet').': '.$appdate.' '.$hourMinute.'
';
					$appdatetime = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($appdate)).' '.$hourMinute));
					$user_id = 0;
					$accountsObj = $db->query("SELECT user_id FROM user WHERE accounts_id = $accounts_id AND is_admin = 1", array());
					if($accountsObj){
						$user_id = $accountsObj->fetch(PDO::FETCH_OBJ)->user_id;
					}
					
					$bindData = $acData = array();
					$acData['accounts_id'] = $accounts_id;
					$acData['created_on'] = date('Y-m-d H:i:s');
					$acData['appdatetime'] = $appdatetime;
					$acData['user_id'] = $user_id;
					$acData['description'] = $description;			
								
					$totalrows = 0;
					$queryObj = $db->query("SELECT COUNT(appointments_id) AS totalrows FROM appointments WHERE accounts_id = $accounts_id AND appdatetime = :appdatetime", array('appdatetime'=>$appdatetime));
					if($queryObj){
						$totalrows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
					}
					
					if($totalrows>0){
						$responseStr = '<p style="color:red;">'.$db->translate('this date and time already booked. try again with different date and time.').'</p>';
					}
					else{										
						$appointments_id = $db->insert('appointments', $acData);
					
						if($appointments_id){
							$responseStr = $description.' '.$db->translate(' information booked successfully.').'';
							
							$subject = "[New message] From $subdomain.".OUR_DOMAINNAME." Appointment Form";
							$message = nl2br(trim((string) $description));
								
							$mail = new PHPMailer;
							$mail->isSMTP();
							$mail->Host = $db->supportEmail('Host');
							$mail->Port = 587;
							$mail->SMTPAuth = true;
							$mail->Username = $db->supportEmail('Username');
							$mail->Password = $db->supportEmail('Password');
							
							$company_name = $customer_service_email = '';
							$accountsObj = $db->query("SELECT company_name, customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
							if($accountsObj){
								$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
								$company_name = $accountsData->company_name;
								$customer_service_email = $accountsData->customer_service_email;
							}
							if(empty($email)){$email = $db->supportEmail('info');}
							if(!empty($customer_service_email)){
							
								$mailBody = "<p>$message</p>";
								$mail->addReplyTo($email, $name);
								$mail->setFrom($db->supportEmail('do_not_reply'), $subdomain);
								$mail->clearAddresses();
								$mail->addAddress($customer_service_email, $company_name);
								$mail->Subject = $subject;
								$mail->isHTML(true);
								$mail->CharSet = 'UTF-8';
								$mail->Body = $mailBody;
								
								//Send the message, check for errors
								if (!$mail->send()) {
									$singleErrorMessage = $mail->ErrorInfo;
									$responseStr .= '<p style="color:red;">'.$singleErrorMessage.' '.$db->translate('Your message could not send.').'<br />'.$db->translate('Please try again, thank you.').'</p>';
								}
								else {
									$mail->clearAddresses();
									$mail->addReplyTo($customer_service_email, $company_name);
									$mail->setFrom($db->supportEmail('do_not_reply'), $subdomain);
									$mail->clearAddresses();
									$mail->addAddress($email, $name);
									$mail->Body = "<p>
Dear <i><strong>$name</strong></i>,<br />
".$db->translate('we have received your request for an appointment.').'<br /><br />
'.$db->translate('You wrote:')."<br />
$message
</p>
<p>
<br />
".$db->translate('Thank you for requesting an appointment.').'
<br />
'.$db->translate('We will reply as soon as possible.').'
</p>';
			
									$mail->send();
									
									$responseStr .= '<p style="color:green;">'.$db->translate('Your message has been successfully sent.').'<br />'.$db->translate('We will be in touch very soon, thank you.').'</p>';
								}
							}
							else{
								$responseStr .= '<p style="color:green;">'.$db->translate('Company customer service email address could not found.').'</p>';
							}
						
						}
						else{
							$responseStr = '<p style="color:red;">'.$db->translate('error occurred while booking new appointment! please try again.').'</p>';
						}
					}
					
					$apiResponse['responseStr'] = $responseStr;					
				}
			}
			else{
				$yyyy = $POST['yyyy']??date('Y');
				$mm = $POST['mm']??date('m');
				//===Load Repair Status Form===//
				$apiResponse['error'] = '';
				$bg_color = '#ffffff';
				$color = '#363947';
				$font_family = 'Arial';
				$but_bg_color = '#ef7f1b';
				$but_color = '#FFFFFF';
				$but_font_family = 'Arial';
				$fieldNames = $db->translate('Name').'||'.$db->translate('Phone No').'||'.$db->translate('Email').'||'.$db->translate('Brand and model of device').'||'.$db->translate('What needs to be fixed');
				$schedules = $blockoutDates = array();

				$varObj = $db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'Appointment'", array());
				if($varObj){
					$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
					$value = $variablesData->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				
				$fieldsName = explode('||', $fieldNames);
				
				$apiResponse['bg_color'] = $bg_color;
				$apiResponse['color'] = $color;
				$apiResponse['font_family'] = $font_family;
				$apiResponse['but_bg_color'] = $but_bg_color;
				$apiResponse['but_color'] = $but_color;
				$apiResponse['but_font_family'] = $but_font_family;
				$apiResponse['schedules'] = $schedules;
				$apiResponse['blockoutDates'] = $blockoutDates;
				$apiResponse['timeformat'] = $timeformat;
				$apiResponse['fieldNames'] = $fieldsName;
				
				$appointments = array();
				$startDate = mktime(0, 0, 0, $mm, 1, $yyyy);
				$endSDate = mktime(0, 0, 0, $mm+1, 1, $yyyy);
				$endDay = date('t', $endSDate);
				$endDate = mktime(23, 59, 59, $mm+1, $endDay, $yyyy);
				
				$appObj = $db->query("SELECT appdatetime FROM appointments WHERE accounts_id = $accounts_id AND appdatetime BETWEEN '".date('Y-m-d H:i:s', $startDate)."' AND '".date('Y-m-d H:i:s', $endDate)."'", array());
				if($appObj){
					while($oneRow = $appObj->fetch(PDO::FETCH_OBJ)){
						$appointments[] = date('Y-m-d H:i', strtotime($oneRow->appdatetime));
					}
				}
				$apiResponse['appointments'] = $appointments;
			}
		}
		elseif ($module=='contact_us'){
			//===Check Repair Status===//
			$POST = json_decode(file_get_contents('php://input'), true);	
			if (is_array($POST) && array_key_exists('name', $POST)) {
				//===Check Repair Status Form Submit===//
				$name = $POST['name']??'';
				$email = $POST['email']??'';
				$message = nl2br(trim((string) $POST['message']??''));
				$phpValidMsg = '';
				if(empty($name)){
					$phpValidMsg .= '<p style="color:red;">'.$db->translate('Name is required.').'</p>';
				}
				if(empty($email)){
					$phpValidMsg .= '<p style="color:red;">'.$db->translate('Email is required.').'</p>';
				}
				if(empty($message)){
					$phpValidMsg .= '<p style="color:red;">'.$db->translate('Message is required.').'</p>';
				}
				if(!empty($phpValidMsg)){
					$apiResponse['error'] = $phpValidMsg;
				}
				else{
					$subject = "[New message] From $subdomain.".OUR_DOMAINNAME." Contact Form";
					$apiResponse['error'] = '';
					$responseStr = '';

					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = $db->supportEmail('Host');
					$mail->Port = 587;
					$mail->SMTPAuth = true;
					$mail->Username = $db->supportEmail('Username');
					$mail->Password = $db->supportEmail('Password');
					
					$company_name = $customer_service_email = '';
					$accountsObj = $db->query("SELECT company_name, customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
					if($accountsObj){
						$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
						$company_name = $accountsData->company_name;
						$customer_service_email = $accountsData->customer_service_email;
					}
					if(empty($email)){$email = $db->supportEmail('info');}
					if(!empty($customer_service_email)){
					
						$mailBody = "<p>$message</p>";
						$mail->addReplyTo($email, $name);
						$mail->setFrom($db->supportEmail('do_not_reply'), $subdomain);
						$mail->clearAddresses();
						$mail->addAddress($customer_service_email, $company_name);
						$mail->Subject = $subject;
						$mail->isHTML(true);
						$mail->CharSet = 'UTF-8';
						$mail->Body = $mailBody;
						
						//Send the message, check for errors
						if (!$mail->send()) {
							$singleErrorMessage = $mail->ErrorInfo;
							$responseStr .= '<p style="color:red;">'.$singleErrorMessage.' '.$db->translate('Your message could not send.').'<br /> '.$db->translate('Please try again, thank you.').'</p>';
						}
						else {
							$mail->addReplyTo($customer_service_email, $company_name);
							$mail->setFrom($db->supportEmail('do_not_reply'), $subdomain);
							$mail->clearAddresses();
							$mail->addAddress($email, $name);
							$mail->Body = "<p>
Dear <i><strong>$name</strong></i>,<br />
".$db->translate('we have received your request for an appointment.')."<br /><br />
".$db->translate('You wrote:')."<br />
$message
</p>
<p>
<br />
".$db->translate('Thank you for requesting a quote.')."
<br />
".$db->translate('We will reply as soon as possible.')."
</p>";
	
							$mail->send();
							
							$responseStr .= '<p style="color:green;">'.$db->translate('Your message has been successfully sent.').'<br />'.$db->translate('We will be in touch very soon, thank you.').'</p>';
						}
					}
					else{
						$phpValidMsg .= '<p style="color:red;">'.$db->translate('Company customer service email address could not found.').'</p>';
					}
				
					$apiResponse['responseStr'] = $responseStr;					
				}
			}
			else{
				//===Load Repair Status Form===//
				$apiResponse['error'] = '';
				$bg_color = '#ffffff';
				$color = '#363947';
				$font_family = 'Arial';
				$but_bg_color = '#ef7f1b';
				$but_color = '#FFFFFF';
				$but_font_family = 'Arial';
			
				$varObj = $db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'ContactUs'", array());
				if($varObj){
					$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
					$value = $variablesData->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				$apiResponse['bg_color'] = $bg_color;
				$apiResponse['color'] = $color;
				$apiResponse['font_family'] = $font_family;
				$apiResponse['but_bg_color'] = $but_bg_color;
				$apiResponse['but_color'] = $but_color;
				$apiResponse['but_font_family'] = $but_font_family;
			}
		}
		elseif ($module=='quote'){
			//===Check Repair Status===//
			$POST = json_decode(file_get_contents('php://input'), true);	
			if (is_array($POST) && array_key_exists('name', $POST)) {
				//===Check Repair Status Form Submit===//
				$name = $POST['name']??'';
				$email = $POST['email']??'';
				$phone = $POST['phone']??'';
				$bamod = $POST['bamod']??'';
				$message = nl2br(trim((string) $POST['message']??''));
				$phpValidMsg = '';
				if(empty($name)){
					$phpValidMsg .= '<p style="color:red;">'.$db->translate('Name is required.').'</p>';
				}
				if(empty($email)){
					$phpValidMsg .= '<p style="color:red;">'.$db->translate('Email is required.').'</p>';
				}
				if(empty($message)){
					$phpValidMsg .= '<p style="color:red;">'.$db->translate('Message is required.').'</p>';
				}
				if(!empty($phpValidMsg)){
					$apiResponse['error'] = $phpValidMsg;
				}
				else{
					$subject = "[New message] From $subdomain.".OUR_DOMAINNAME." Quote Form";
					$apiResponse['error'] = '';
					$responseStr = '';
					
					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = $db->supportEmail('Host');
					$mail->Port = 587;
					$mail->SMTPAuth = true;
					$mail->Username = $db->supportEmail('Username');
					$mail->Password = $db->supportEmail('Password');
					
					$company_name = $customer_service_email = '';
					$accountsObj = $db->query("SELECT company_name, customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
					if($accountsObj){
						$accountsData = $accountsObj->fetch(PDO::FETCH_OBJ);
						$company_name = $accountsData->company_name;
						$customer_service_email = $accountsData->customer_service_email;
					}
					if(empty($email)){$email = $db->supportEmail('info');}
					if(!empty($customer_service_email)){
					
						$mailBody = "<p>
Name : <strong>$name</strong><br>
Phone Number : $phone<br>
Email : <a href=\"mailto:$email\" title=\"Click for reply\">$email</a><br>
".$db->translate('Brand and model of device')." : $bamod<br>
<br>
Problem : $message
</p>";

						$mail->addReplyTo($email, $name);
						$mail->setFrom($db->supportEmail('do_not_reply'), $subdomain);
						$mail->clearAddresses();
						$mail->addAddress($customer_service_email, $company_name);
						$mail->Subject = $subject;
						$mail->isHTML(true);
						$mail->CharSet = 'UTF-8';
						$mail->Body = $mailBody;
						
						//Send the message, check for errors
						if (!$mail->send()) {
							$singleErrorMessage = $mail->ErrorInfo;
							$responseStr .= '<p style="color:red;">'.$singleErrorMessage.' '.$db->translate('Your message could not send.').'<br /> '.$db->translate('Please try again, thank you.').'</p>';
						}
						else {
							$mail->addReplyTo($customer_service_email, $company_name);
							$mail->setFrom($db->supportEmail('do_not_reply'), $subdomain);
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
							
							$responseStr .= '<p style="color:green;">'.$db->translate('Your Quote has been successfully sent.').'<br />'.$db->translate('We will be in touch very soon, thank you.').'</p>';
						}
					}
					else{
						$phpValidMsg .= '<p style="color:red;">'.$db->translate('Company customer service email address could not found.').'</p>';
					}
				
					$apiResponse['responseStr'] = $responseStr;					
				}
			}
			else{
				//===Load Repair Status Form===//
				$apiResponse['error'] = '';
				$bg_color = '#ffffff';
				$color = '#363947';
				$font_family = 'Arial';
			
				$varObj = $db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'Quote'", array());
				if($varObj){
					$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
					$value = $variablesData->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				$apiResponse['bg_color'] = $bg_color;
				$apiResponse['color'] = $color;
				$apiResponse['font_family'] = $font_family;
			}
		}
		else{
			$apiResponse['error'] = '<p style="color:red;">'.$db->translate('Module name is invalid.').'</p>';
		}
	}
}

echo json_encode($apiResponse);
exit;