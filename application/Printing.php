<?php
class Printing{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	public function invoicesInfo($pos_id, $printType, $amount_due=0, $fromPage = 'Invoices', $emailYN=0, $printerName = ''){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$domainName = OUR_DOMAINNAME;
		$Common = new Common($this->db);
		$Carts = new Carts($this->db);
		$repairStatus = '';
		$posObj = $this->db->query("SELECT * FROM pos WHERE pos_id = :pos_id", array('pos_id'=>$pos_id),1);
		if($posObj){
			$pos_onerow = $posObj->fetch(PDO::FETCH_OBJ);	
			$pos_id = $pos_onerow->pos_id;
			if($accounts_id==0){
				$accounts_id = $pos_onerow->accounts_id;
			}

			$invoice_no = $pos_onerow->invoice_no;
			$customer_id = $pos_onerow->customer_id;
			$order_status = $pos_onerow->order_status;
			$pos_publish = $pos_onerow->pos_publish;
			if($fromPage =='Orders'){
				$title = $this->db->translate('Print Order')." #: o$invoice_no";
			}
			else{
				$title = $this->db->translate('Sales Invoices Print-Invoice #: s') . $invoice_no;			
			}
			if($fromPage=='Repairs' && $repairStatus==''){
				$repairsObj = $this->db->query("SELECT status FROM repairs WHERE pos_id = :pos_id", array('pos_id'=>$pos_id),1);
				if($repairsObj){
					$repairStatus = $repairsObj->fetch(PDO::FETCH_OBJ)->status;
				}
			}
			$customername = $customeremail = $offers_email = $customerphone = $customeraddress = $editcustomers = '';
			$customerObj = $this->db->query("SELECT * FROM customers WHERE customers_id = $customer_id", array());
			if($customerObj){
				$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);	
			}
			
			if($printType == 'small'){
				$jsonResponse = array();
				$jsonResponse['login'] = '';

				$orientation = 'portrait';
				$top_margin = $bottom_margin = 0;
				$left_margin = $right_margin = 15;

				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'small_print'", array());
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
				$title = $this->db->translate('Sales Receipt');
				$company_info = $invoice_message_above = $invoice_message = $value = '';
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
				$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;

				$varNameVal = 'invoice_setup';
				if($fromPage == 'Orders'){$varNameVal = 'orders_print';}

				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = '$varNameVal'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				$jsonResponse['title'] = $title;
				$jsonResponse['company_info'] = nl2br(stripslashes($company_info));

				$companylogo = "";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$pics = glob($filePath."*.jpg");
				if($pics){
					foreach($pics as $onePicture){
						$onePicture = "//$domainName".str_replace('./', '/', $onePicture);
						$companylogo =$onePicture;
					}				
				}

				$jsonResponse['companylogo'] = $companylogo;
				$jsonResponse['sales_datetime'] = $pos_onerow->sales_datetime;

				$salesPerson = '';					   
				if($sales_person==1 && $pos_onerow->employee_id>0){
					$userObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id", array());
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
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
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
				$query = $this->db->query($sqlquery, array());
				if($query){
					$i=0;
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						$i++;
						$pos_cart_id = $row->pos_cart_id;
						$item_id = $row->item_id;
						$item_type = $row->item_type;
						$qty = $row->qty;
						$shipping_qty = $row->shipping_qty;
						
						if(in_array($fromPage, array('POS', 'Orders')) || $item_type =='one_time' || ($fromPage=='Repairs' && (empty($repairStatus) || !in_array($repairStatus, array('Finished', 'Invoiced', 'Cancelled'))))){
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
							$itemquery = $this->db->query($sqlitem, array());
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
				$ppQueryObj = $this->db->query($ppSql, array());
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
					$SmallNotes = $this->getPublicSmallNotes('pos', $pos_onerow->pos_id);
				}
				$jsonResponse['pos_id'] = intval($pos_onerow->pos_id);
				$jsonResponse['SmallNotes'] = $SmallNotes;
				
				$jsonResponse['invoice_message'] = nl2br(stripslashes($invoice_message));
				$marketing_data = '';
				if($customerObj){
					$marketing_data = $this->showCustMarkData($customerrow->offers_email);
				}
				$jsonResponse['marketing_data'] = $marketing_data;
				return $jsonResponse;
				
			}
			elseif($printType == 'pick'){
				$jsonResponse = array();
				$jsonResponse['login'] = '';
				
				$logo_size = 'Small Logo';
				$logo_placement = 'Left';
				
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
				$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;
				$company_info = $invoice_message_above = $invoice_message = $value = '';
				
				$varNameVal = 'invoice_setup';
				if($fromPage == 'Orders'){$varNameVal = 'orders_print';}
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = '$varNameVal'", array());
                if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
								
				$companylogo = "";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$pics = glob($filePath."*.jpg");
				if($pics){
					foreach($pics as $onePicture){
						$onePicture = "//$domainName".str_replace('./', '/', $onePicture);
						$companylogo = $onePicture;
					}				
				}				
				$jsonResponse['title'] = $title;
				$jsonResponse['logo_size'] = $logo_size;
				$jsonResponse['companylogo'] = $companylogo;
				$jsonResponse['company_info'] = nl2br(stripslashes($company_info));
				$jsonResponse['logo_placement'] = $logo_placement;
							
				$customerName = array();
				if($customer_name == 1 && $customerObj){
					if($customerrow->company !=''){
						$customerName[] = stripslashes(trim((string) $customerrow->company));
					}
					$customerName[] = stripslashes(trim("$customerrow->first_name $customerrow->last_name"));
				}
				$jsonResponse['customerName'] = $customerName;
								
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
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
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
                
				$invoice_no = $pos_onerow->invoice_no;
				if($invoice_no ==0){
					$invoice_no = $pos_onerow->pos_id;
				}
				$jsonResponse['invoice_no'] = intval($invoice_no);
				
				$salesPerson = '';
				if($sales_person==1 && $pos_onerow->employee_id>0){
					$userObj2 = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id", array());
					if($userObj2){
						$userOneRow = $userObj2->fetch(PDO::FETCH_OBJ);
						$salesPerson = $this->db->translate('Sales Person')." : ".stripslashes(trim("$userOneRow->user_first_name $userOneRow->user_last_name"));
					}
				}
				$jsonResponse['salesPerson'] = $salesPerson;
				$jsonResponse['barcode'] = $barcode;

				$jsonResponse['invoiceDate'] = $pos_onerow->sales_datetime;

				$colSpan = 2;
				if($logo_placement=='Left' && $companylogo ==''){$colSpan = 1;}
				$jsonResponse['colSpan'] = $colSpan;
				$jsonResponse['invoice_message_above'] = $invoice_message_above;
				
				$cartData = array();
				$taxable_total = $nontaxable_total = 0.00;							
				$pos_id = $pos_onerow->pos_id;
				$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
				$query = $this->db->query($sqlquery, array());
				if($query){
					$i=0;
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						$i++;
						$pos_cart_id = $row->pos_cart_id;
						$item_id = $row->item_id;
						$item_type = $row->item_type;
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
							$itemquery = $this->db->query($sqlitem, array());
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
						
						$discount_is_percent = $row->discount_is_percent;
						$discount = $row->discount;
						
						$qty = $row->qty;
						$shipping_qty = $row->shipping_qty;
						$cartData[] = array('description'=>$description, 'add_description'=>$add_description, 'newimei_info'=>$newimei_info, 'qty'=>floatval($qty), 'shipping_qty'=>floatval($shipping_qty));
					}
				}
				$jsonResponse['cartData'] = $cartData;

				$getNotes = array();
				if($notes==1){
					$getNotes = $this->getPublicNotes('pos', $pos_id, 1);
				}
				$jsonResponse['getNotes'] = $getNotes;
				$jsonResponse['invoice_message'] = nl2br(stripslashes($invoice_message));
				return $jsonResponse;
			}
			elseif($emailYN==1){

				$dateformat = $_SESSION["dateformat"]??'m/d/Y';
				$timeformat = $_SESSION["timeformat"]??'12 hour';
				$printingStr = '';
				$orientation = 'portrait';
				$top_margin = $bottom_margin = 0;
				$left_margin = $right_margin = 15;
				
				$addCss = array();
				$addCss[] = "size:$orientation";
				$addCss[] = "margin-top:$top_margin".'px';
				$addCss[] = "margin-bottom:$bottom_margin".'px';				
				//if($right_margin !=0){$addCss[] = 'margin-right:'.$right_margin.'px';}
				//if($left_margin !=0){$addCss[] = 'margin-left:'.$left_margin.'px';}
				
				$printingStr .= '<!DOCTYPE html>
				<html>
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
					<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />					
					<title>'.$title.' | '.$printerName.'</title>
					<style type="text/css">
						@page {'.implode(';', $addCss).';}
						body{ font-family:Arial, sans-serif, Helvetica; min-width:99%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
						h2{font-size:22px; height:20px; margin-bottom:0; padding-bottom:0; font-weight:500;}
						.h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
						address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
						.pright15{padding-right:15px;}
						.ptop10{padding-top:10px;}
						.pbottom10{padding-bottom:10px;}
						.mbottom0{ margin-bottom:0px;}
						table{border-collapse:collapse;}
						.border th{background:#F5F5F6;}
						.border td, .border th{ border:1px solid #DDDDDD; padding:4px 10px; vertical-align: top;}
					</style>
				</head>
				<body>';
				
				if($fromPage =='Orders'){
					$title = $this->db->translate('Print Order')." #: o$invoice_no";
				}
				else{
					$title = $this->db->translate('Sales Receipt');			
				}
				$logo_size = 'Small Logo';
				$logo_placement = 'Left';
				
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
				$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;
				$company_info = $invoice_message_above = $invoice_message = $value = '';
				
				$varNameVal = 'invoice_setup';
				if($fromPage == 'Orders'){$varNameVal = 'orders_print';}
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = '$varNameVal'", array());
                if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
								
				$companylogo = "";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$pics = glob($filePath."*.jpg");
				if($pics){
					foreach($pics as $onePicture){
						$onePicture = "http://$domainName".str_replace('./', '/', $onePicture);
						if($logo_size=='Large Logo'){
							$style = 'max-height:150px;max-width:350px;';
						}
						else{
							$style = 'max-height:100px;max-width:150px;';
						}
						$companylogo = "<img style=\"$style\" src=\"$onePicture\" title=\"".$this->db->translate('Logo')."\" />";
					}				
				}				
				
				$printingStr .= '<table cellpadding="0" cellspacing="1" width="100%">
						<tr>
							<td align="center">
								<h2>'.$title.'</h2>
							</td>
						</tr>
						<tr>
							<td>
								<table width="100%" cellpadding="0" cellspacing="0">';
									
				$customerName = '';
				if($customer_name == 1 && $customerObj){
					if($customerrow->company !=''){
						$customerName .= stripslashes(trim((string) $customerrow->company)).'<br />';
					}
					$customerName .= stripslashes(trim("$customerrow->first_name $customerrow->last_name"));
				}
				$customerAddress = '';
				if($customer_address==1 && $customerObj){
					if($customerrow->shipping_address_one !=''){										
						$customerAddress .= '<br>'.stripslashes(trim((string) "$customerrow->shipping_address_one"));
					}
					if($customerrow->shipping_city !='' || $customerrow->shipping_state !='' || $customerrow->shipping_zip !=''){
						if($logo_placement !='Center'){$customerAddress .= '<br>';}
						$customerAddress .= stripslashes(trim("$customerrow->shipping_city $customerrow->shipping_state $customerrow->shipping_zip"));
					}
				}
				$contactNo = '';
				if($customer_phone==1 && $customerObj && $customerrow->contact_no !=''){
					$contactNo = '<br>'.$customerrow->contact_no;
				}
				if($secondary_phone==1 && $customerObj && $customerrow->secondary_phone !=''){
					$contactNo .= '<br>'.$customerrow->secondary_phone;
				}
				$customerEmail = '';
				if($customer_email==1 && $customerObj && $customerrow->email !=''){
					$customerEmail = '<br>'.$customerrow->email;
				}
				if($customer_type==1 && $customerObj){
					if($customerrow->customer_type !='')
						$customerEmail .= '<br>'.$customerrow->customer_type;
				}
				
				$customFieldsData = "";																	
				if($customerObj && $customerrow->custom_data !=''){
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
					if($queryCFObj){
						$custom_data = unserialize($customerrow->custom_data);
						while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
							$field_name = stripslashes($oneCustomFields->field_name);
							$checked = '';
							if(!empty($value) && array_key_exists("cf$oneCustomFields->custom_fields_id",$value) && $value["cf$oneCustomFields->custom_fields_id"]>0 && !empty($custom_data)){
								if(array_key_exists($field_name, $custom_data) && $custom_data[$field_name] !=''){
									$customFieldsData .= '<br>'.$field_name.": ".$custom_data[$field_name];
								}
							}
						}
					}
				}
				$invoice_no = $pos_onerow->invoice_no;
				if($invoice_no ==0){
					$invoice_no = $pos_onerow->pos_id;
				}
				$salesPerson = '';
				if($sales_person==1 && $pos_onerow->employee_id>0){
					$userObj2 = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id", array());
					if($userObj2){
						$userOneRow = $userObj2->fetch(PDO::FETCH_OBJ);
						$salesPerson = "<p>".$this->db->translate('Sales Person')." : ".stripslashes(trim("$userOneRow->user_first_name $userOneRow->user_last_name"))."</p>";
					}
				}
				$barCodeStr = '';
				if($barcode==1){
					$barCodeStr = "<div style=\"clear:both\"></div>
									<img style=\"max-width:96%;\" src=\"http://$domainName/Createbarcode/s$invoice_no\" alt=\"s$invoice_no\"></img>
									<div style=\"clear:both\"></div>";
				}
				
				if($timeformat=='24 hour'){$invoiceDate =  date($dateformat.' H:i', strtotime($pos_onerow->sales_datetime));}
				else{$invoiceDate =  date($dateformat.' g:i a', strtotime($pos_onerow->sales_datetime));}
				if($fromPage =='Orders'){
					$invoiceNoStr = "<h4>".$this->db->translate('Order No.').": o$invoice_no</h4>";
				}
				else{
					$invoiceNoStr = "<h4>".$this->db->translate('Invoice #: s')."$invoice_no</h4>";
				}
				
				$colSpan = 2;
				if($logo_placement=='Center'){
					if($companylogo !=''){
						$printingStr .= "<tr><td colspan=\"2\" align=\"center\" class=\"ptop10 pbottom10\">$companylogo</td></tr>";
					}
					$printingStr .= "<tr>
							<td colspan=\"2\" align=\"center\">".nl2br($company_info)."</td>
						</tr>
						<tr>
							<td align=\"left\" width=\"50%\">
								<address class=\"mbottom0\">
									<span>".$this->db->translate('Bill To').":</span> <strong>$customerName</strong>
									$customerAddress
									$contactNo
									$customerEmail
									$customFieldsData
								</address>
							</td>
							<td align=\"right\">
								$invoiceNoStr
								$salesPerson
								$barCodeStr
								<p>$invoiceDate</p>
							</td>
						</tr>";
				}
				else{
					$printingStr .= "<tr>";
						if($companylogo !=''){
							$printingStr .= "<td width=\"150\" valign=\"top\" class=\"pright15\">$companylogo</td>";
						}
						else{$colSpan = 1;}
						
						$printingStr .= "<td align=\"left\" valign=\"top\"> 
								".nl2br($company_info)."
						</td>
						<td width=\"35%\" align=\"right\" rowspan=\"2\">
							<address class=\"mbottom0\">
								<span>".$this->db->translate('Bill To').":</span> <strong>$customerName</strong>
								$customerAddress
								$contactNo
								$customerEmail
								$customFieldsData
							</address>
							$invoiceNoStr
							$salesPerson
							$barCodeStr
							<p>$invoiceDate</p>
						</td>
					</tr>";
				}
				
				$printingStr .= "<tr>
										<td colspan=\"$colSpan\" align=\"left\" class=\"pbottom10\">$invoice_message_above</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>						
								<table class=\"border\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
									<tr>
										<th width=\"3%\" align=\"right\">#</th>
										<th align=\"left\">".$this->db->translate('Description')."</th>
										<th width=\"8%\">".$this->db->translate('QTY')."</th>
										<th width=\"12%\">".$this->db->translate('Unit Price')."</th>
										<th width=\"15%\">".$this->db->translate('Total')."</th>
									</tr>";
									
				$taxable_total = $nontaxable_total = 0.00;							
				$pos_id = $pos_onerow->pos_id;
				$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
				$query = $this->db->query($sqlquery, array());
				if($query){
					$i=0;
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						$i++;
						$pos_cart_id = $row->pos_cart_id;
						$item_id = $row->item_id;
						$item_type = $row->item_type;
						$qty = $row->qty;
						$shipping_qty = $row->shipping_qty;
						if(in_array($fromPage, array('POS', 'Orders')) || $item_type =='one_time' || ($fromPage=='Repairs' && (empty($repairStatus) || !in_array($repairStatus, array('Finished', 'Invoiced', 'Cancelled'))))){
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
							$itemquery = $this->db->query($sqlitem, array());
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
						$sales_pricestr = $currency.number_format($sales_price,2);
						if($sales_price<0){
							$sales_pricestr = "-$currency".number_format($sales_price*(-1),2);
						}
						
						$discount_is_percent = $row->discount_is_percent;
						$discount = $row->discount;
						
						$total = round($sales_price*$shipping_qty,2);
						$totalstr = $currency.number_format($total,2);
						if($total <0 ){
							$totalstr = "-$currency".number_format(($total*(-1)),2);
						}
						if($discount_is_percent>0){
							$discount_value = round($total*0.01*$discount,2);
						}
						else{
							$discount_value = round($discount*$shipping_qty,2);
						}
						
						if($discount_value>0){
							$totalstr .= "<br />-$currency".number_format($discount_value,2);
						}
						elseif($discount_value<0){
							$totalstr .= "<br />$currency".number_format(($discount_value*(-1)),2);
						}
						
						$taxable = $row->taxable;																		
						if($taxable>0){
							$taxable_total = $taxable_total+$total-$discount_value;
						}
						else{
							$nontaxable_total = $nontaxable_total+$total-$discount_value;
						}
						$newimei_info = implode('<br>', $newimei_info);
						if($sales_price !=0 || $print_price_zero>0 || $emailYN>0){
							if(!empty($add_description)){$add_description = "<br>$add_description";}
							if(!empty($newimei_info)){$newimei_info = "<br>$newimei_info";}
							$printingStr .= "<tr>
											<td align=\"right\">$i</td>
											<td align=\"left\">
												$description$add_description
												<span>$newimei_info</span>
											</td>
											<td align=\"right\">$shipping_qty</td>
											<td align=\"right\">$sales_pricestr</td>
											<td align=\"right\">$totalstr</td>
										</tr>";
						}
					}
				}
				else{
					$printingStr .= "<tr><td colspan=\"5\">".$this->db->translate('There is no data found')."</td></tr>";
				}
				
				if($pos_onerow->taxes_name1 !=''){
					$taxable_totalstr = $currency.number_format($taxable_total,2);
					if($taxable_total<0){
						$taxable_totalstr = "-$currency".number_format($taxable_total*(-1),2);
					}
					
					$printingStr .= "<tr>
						<td align=\"right\" colspan=\"4\"><strong>".$this->db->translate('Taxable Total')." :</strong></td>
						<td align=\"right\"><strong>$taxable_totalstr</strong></td>
					</tr>";
				}
				
				$taxes_total1 = 0;					
				$tax_inclusive1 = $pos_onerow->tax_inclusive1;
				if($pos_onerow->taxes_name1 !=''){
					$tiStr = '';
					if($tax_inclusive1>0){$tiStr = ' Inclusive';}
					
					$taxes_total1 = $Common->calculateTax($taxable_total, $pos_onerow->taxes_percentage1, $pos_onerow->tax_inclusive1);
					$taxes_totalstr = $currency.number_format($taxes_total1,2);
					if($taxes_total1<0){
						$taxes_totalstr = "-$currency".number_format($taxes_total1*(-1),2);
					}					
					
					$printingStr .= "<tr>
						<td align=\"right\" colspan=\"4\"><strong>$pos_onerow->taxes_name1 ($pos_onerow->taxes_percentage1%$tiStr) :</strong></td>
						<td align=\"right\"><strong>$taxes_totalstr</strong></td>
					</tr>";
				}
				
				$taxes_total2 = 0;					
				$tax_inclusive2 = $pos_onerow->tax_inclusive2;
				if($pos_onerow->taxes_name2 !=''){
					$tiStr = '';
					if($tax_inclusive2>0){$tiStr = ' Inclusive';}
					
					$taxes_total2 = $Common->calculateTax($taxable_total, $pos_onerow->taxes_percentage2, $pos_onerow->tax_inclusive2);
					$taxes_totalstr = $currency.number_format($taxes_total2,2);
					if($taxes_total2<0){
						$taxes_totalstr = "-$currency".number_format($taxes_total2*(-1),2);
					}
					
					$printingStr .= "<tr>
						<td align=\"right\" colspan=\"4\"><strong>$pos_onerow->taxes_name2 ($pos_onerow->taxes_percentage2%$tiStr) :</strong></td>
						<td align=\"right\"><strong>$taxes_totalstr</strong></td>
					</tr>";
				}
				
				if($nontaxable_total>0 || $nontaxable_total<0){
					$taxable_totalstr = $currency.number_format($nontaxable_total,2);
					if($nontaxable_total<0){
						$taxable_totalstr = "-$currency".number_format($nontaxable_total*(-1),2);
					}
					
					$printingStr .= "<tr>
						<td align=\"right\" colspan=\"4\"><strong>".$this->db->translate('Non Taxable Total')." :</strong></td>
						<td align=\"right\"><strong>$taxable_totalstr</strong></td>
					</tr>";
				}
				
				if($tax_inclusive1>0){$taxes_total1 = 0;}
				if($tax_inclusive2>0){$taxes_total2 = 0;}
				$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
				$grand_totalstr = $currency.number_format($grand_total,2);
				if($grand_total<0){
					$grand_totalstr = "-$currency".number_format($grand_total*(-1),2);
				}
				
				$printingStr .= "<tr>
					<td align=\"right\" colspan=\"4\"><strong>".$this->db->translate('Grand Total')." :</strong></td>
					<td align=\"right\"><strong>$grand_totalstr</strong></td>
				</tr>";
				
				$totalpayment = 0;
				$ppSql = "SELECT payment_method, payment_amount, payment_datetime FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
				$ppQueryObj = $this->db->query($ppSql, array());
				if($ppQueryObj){
					while($onerow = $ppQueryObj->fetch(PDO::FETCH_OBJ)){
						
						$payment_amount = $onerow->payment_amount;
						if($timeformat=='24 hour'){$payment_datetime =  date($dateformat.' H:i', strtotime($onerow->payment_datetime));}
						else{$payment_datetime =  date($dateformat.' g:i a', strtotime($onerow->payment_datetime));}
				
						$totalpayment = $totalpayment+$payment_amount;
						$payment_amountstr = $currency.number_format($payment_amount,2);
						if($payment_amount<0){
							$payment_amountstr = '-'.$currency.number_format($payment_amount*(-1),2);
						}
						
						$printingStr .= "<tr>
								<td align=\"right\" colspan=\"4\">
									$payment_datetime $onerow->payment_method ".$this->db->translate('Payment')."
								</td>
								<td align=\"right\">
									$payment_amountstr
								</td>
							</tr>";						
					}
				}
				
				if($grand_total>$totalpayment){
					$amountDue = $grand_total-$totalpayment;
					$credit_days = $pos_onerow->credit_days;
					$salesTime = strtotime($pos_onerow->sales_datetime);
					$DueByStr = $this->db->translate('Total amount due by')." ".date($dateformat, strtotime("+$credit_days day", $salesTime));
					if($fromPage =='Orders'){$DueByStr = "";}
					
					$printingStr .= "<tr class=\"border\">
						<td align=\"center\" colspan=\"3\">$DueByStr</td>
						<td align=\"right\" nowrap><strong>".$this->db->translate('Amount Due')." :</strong></td>
						<td align=\"right\"><strong>$currency".number_format($amountDue,2)."</strong></td>
					</tr>";
				}								
				
				if($amount_due<0){
					$amount_due = $amount_due*-1;
					$printingStr .= "<tr class=\"border\">
						<td colspan=\"5\" align=\"center\">".$this->db->translate('Please give change amount of')." $currency".number_format($amount_due,2)."</td>
					</tr>";
				}
				
				$printingStr .= "</table>
							</td>
						</tr>"; 
				if($notes==1){
					$printingStr .= '<tr>
						<td>'.$this->getPublicNotes('pos', $pos_id).'</td>
					</tr>';
				}
				
				$printingStr .= "<tr>
							<td align=\"center\">".nl2br(stripslashes($invoice_message))."</p>								
							</td>
						</tr>";
						
				if($customerObj){
					$marketing_data = $this->showCustMarkData($customerrow->offers_email);
					if($marketing_data !=''){
						$printingStr .= "<tr>
							<td align=\"center\" colspan=\"2\">$marketing_data</td>
						</tr>";
					}
				}
				$printingStr .= "</table>
							</body>
						</html>";
				return $printingStr;
			}
			else{
				$jsonResponse = array();
				$jsonResponse['login'] = '';
				$jsonResponse['table_id'] = $pos_id;
				$jsonResponse['for_table'] = 'pos';
				$jsonResponse['fromPage'] = $fromPage;
				
				$orientation = 'portrait';
				$top_margin = $bottom_margin = 0;
				$left_margin = $right_margin = 15;
				
				$jsonResponse['printerName'] = $printerName;
				$jsonResponse['orientation'] = $orientation;
				$jsonResponse['top_margin'] = $top_margin;
				$jsonResponse['bottom_margin'] = $bottom_margin;
				$jsonResponse['right_margin'] = $right_margin;
				$jsonResponse['left_margin'] = $left_margin;

				if($fromPage =='Orders'){
					$title = $this->db->translate('Print Order')." #: o$invoice_no";
				}
				else{
					$title = $this->db->translate('Sales Receipt');			
				}
				$logo_size = 'Small Logo';
				$logo_placement = 'Left';
				
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
				$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;
				$company_info = $invoice_message_above = $invoice_message = $value = '';
				
				$varNameVal = 'invoice_setup';
				if($fromPage == 'Orders'){$varNameVal = 'orders_print';}
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = '$varNameVal'", array());
                if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				$jsonResponse['title'] = $title;
				$jsonResponse['company_info'] = nl2br(stripslashes($company_info));
				$jsonResponse['logo_placement'] = $logo_placement;
				
				$companylogo = "";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$pics = glob($filePath."*.jpg");
				if($pics){
					foreach($pics as $onePicture){
						$onePicture = "http://$domainName".str_replace('./', '/', $onePicture);
						$companylogo = $onePicture;
					}				
				}
				$jsonResponse['logo_size'] = $logo_size;
				$jsonResponse['companylogo'] = $companylogo;
									
				$customerName = array();
				if($customer_name == 1 && $customerObj){
					if($customerrow->company !=''){
						$customerName[] = stripslashes(trim((string) $customerrow->company));
					}
					$customerName[] = stripslashes(trim("$customerrow->first_name $customerrow->last_name"));
				}
				$jsonResponse['customerName'] = $customerName;
				
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
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
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
                
				$invoice_no = $pos_onerow->invoice_no;
				if($invoice_no ==0){
					$invoice_no = $pos_onerow->pos_id;
				}
				$jsonResponse['invoice_no'] = intval($invoice_no);

				$salesPerson = '';
				if($sales_person==1 && $pos_onerow->employee_id>0){
					$userObj2 = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id", array());
					if($userObj2){
						$userOneRow = $userObj2->fetch(PDO::FETCH_OBJ);
						$salesPerson = stripslashes(trim("$userOneRow->user_first_name $userOneRow->user_last_name"));
					}
				}
				$jsonResponse['salesPerson'] = $salesPerson;
				$jsonResponse['barcode'] = $barcode;
				$jsonResponse['invoice_message_above'] = $invoice_message_above;

				$jsonResponse['invoiceDate'] = $pos_onerow->sales_datetime;
				
				$colSpan = 2;
				if($logo_placement=='Left' && $companylogo ==''){$colSpan = 1;}
				$jsonResponse['colSpan'] = $colSpan;

				$cartData = array();
				$taxable_total = $nontaxable_total = 0.00;							
				$pos_id = $pos_onerow->pos_id;
				$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
				$query = $this->db->query($sqlquery, array());
				if($query){
					$i=0;
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						$i++;
						$pos_cart_id = $row->pos_cart_id;
						$item_id = $row->item_id;
						$item_type = $row->item_type;
						$qty = floatval($row->qty);
						$shipping_qty = floatval($row->shipping_qty);
						if(in_array($fromPage, array('POS', 'Orders')) || $item_type =='one_time' || ($fromPage=='Repairs' && (empty($repairStatus) || !in_array($repairStatus, array('Finished', 'Invoiced', 'Cancelled'))))){
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
							$itemquery = $this->db->query($sqlitem, array());
							if($itemquery){
								while($newitem_row = $itemquery->fetch(PDO::FETCH_OBJ)){
									$imei_info = $newitem_row->item_number;
									$description = str_replace("$newitem_row->item_number", '', $description);
									
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
						
						$sales_price = round($row->sales_price,2);
						
						$discount_is_percent = $row->discount_is_percent;
						$discount = $row->discount;
						
						$total = round($sales_price*$shipping_qty,2);
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
						
						if($sales_price !=0 || $print_price_zero>0 || $emailYN>0){
							$cartData[] = array('description'=>$description, 'add_description'=>$add_description, 'newimei_info'=>$newimei_info, 'shipping_qty'=>floatval($shipping_qty), 'sales_price'=>round($sales_price,2), 'discount_value'=>round($discount_value,2));
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
				$ppQueryObj = $this->db->query($ppSql, array());
				if($ppQueryObj){
					while($onerow = $ppQueryObj->fetch(PDO::FETCH_OBJ)){
						
						$payment_amount = round($onerow->payment_amount,2);
						
						$totalpayment = $totalpayment+$payment_amount;
						$paymentData[] = array('payment_datetime'=>$onerow->payment_datetime, 'payment_method'=>$onerow->payment_method, 'payment_amount'=>round($payment_amount,2));
					}
				}
				$jsonResponse['paymentData'] = $paymentData;
				$jsonResponse['totalpayment'] = round($totalpayment,2);
				$amountDue = 0;
				if($grand_total>$totalpayment){
					$amountDue = $grand_total-$totalpayment;
					$credit_days = $pos_onerow->credit_days;
					$salesTime = strtotime($pos_onerow->sales_datetime);
					$DueByStr = $this->db->translate('Total amount due by')." ";
					if($fromPage =='Orders'){$DueByStr = "";}

					$jsonResponse['DueByStr'] = $DueByStr;
					$jsonResponse['amountDueDate'] = date('Y-m-d', strtotime("+$credit_days day", $salesTime));
				}
				$jsonResponse['amountDue'] = round($amountDue,2);
				
                $getNotes = array();
				if($notes==1){
					$getNotes = $this->getPublicNotes('pos', $pos_id, 1);
				}
				$jsonResponse['getNotes'] = $getNotes;
				
				$jsonResponse['invoice_message'] = nl2br(stripslashes($invoice_message));
				$marketing_data = '';
				if($customerObj){
					$marketing_data = $this->showCustMarkData($customerrow->offers_email);
				}
				$jsonResponse['marketing_data'] = $marketing_data;
				return $jsonResponse;
			}
		}
	}
	
	public function repairInvoicesInfo($repairs_id, $printType, $emailYN=0){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$domainName = OUR_DOMAINNAME;
		
		$Common = new Common($this->db);
		$Carts = new Carts($this->db);
		
		$printingStr = '';
		$pos_id = 0;
		$title = $this->db->translate('Repair Summary of Ticket');
		$repairsObj = $this->db->query("SELECT * FROM repairs WHERE repairs_id = :repairs_id AND accounts_id = $accounts_id", array('repairs_id'=>$repairs_id),1);
		if($repairsObj){
			$repairs_onerow = $repairsObj->fetch(PDO::FETCH_OBJ);
			$ticket_no = $repairs_onerow->ticket_no;
			$title .= ' t' .$ticket_no;
			$pos_id = $repairs_onerow->pos_id;
		}
		else{
			return '';
		}
		
		$posObj = $this->db->query("SELECT * FROM pos WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
		if($posObj){
			$pos_onerow = $posObj->fetch(PDO::FETCH_OBJ);
			$invoice_no = $pos_onerow->invoice_no;
			$customerObj = $this->db->query("SELECT * FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = $pos_onerow->customer_id", array());
			if($customerObj){
				$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);
			}
			$customername = $customeremail = $offers_email = $customerphone = $customeraddress = $editcustomers = '';
			
			$employee_id = $pos_onerow->employee_id;
			$salesman_name = '';
			if($employee_id>0){
				$salesmanObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $employee_id", array());
				if($salesmanObj){
					$salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ);
					$salesman_name = trim(stripslashes("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
				}
			}
			$assign_to = $repairs_onerow->assign_to;
			$assignToName = '';
			if($assign_to>0){
				$assignObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $assign_to", array());
				if($assignObj){
					$assignRow = $assignObj->fetch(PDO::FETCH_OBJ);
					$assignToName = trim(stripslashes("$assignRow->user_first_name $assignRow->user_last_name"));
				}
			}
			
			if(empty($repairs_onerow->status) || !in_array($repairs_onerow->status, array('Finished', 'Invoiced', 'Cancelled'))){
				$qtyfield = 'qty';
			}
			else{
				$qtyfield = 'shipping_qty';
			}
			if($printType == 'small'){
				
				$jsonResponse = array();
				$jsonResponse['login'] = '';
					
				$left_margin = $right_margin = 15;
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'small_print'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				$jsonResponse['left_margin'] = $left_margin;
				$jsonResponse['right_margin'] = $right_margin;

				$title = $this->db->translate('Repair Ticket');
				$logo_size = 'Small Logo';
				$logo_placement = 'Left';
				$company_info = $repair_message = $value = '';
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = $technician = $short_description = $imei = $brand = $bin_location = $lock_password = 1;
				$customer_secondary_phone = $customer_type = $barcode = $status = $duedatetime = $print_price_zero = $notes = 0;
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				$jsonResponse['logo_placement'] = $logo_placement;
				$jsonResponse['company_info'] = nl2br(stripslashes($company_info));
				
				if($repairs_onerow->status=='Estimate'){
					$title = $this->db->translate('Estimate');
				}
				$jsonResponse['title'] = $title;

				$companylogo = "";
                $filePath = "./assets/accounts/a_$accounts_id/app_logo_";
                $pics = glob($filePath."*.jpg");
                if($pics){
                    foreach($pics as $onePicture){
                        $onePicture = "//$domainName".str_replace('./', '/', $onePicture);
                        $companylogo = $onePicture;
                    }				
                }
				$jsonResponse['companylogo'] = $companylogo;

				$jsonResponse['sales_datetime'] = $pos_onerow->sales_datetime;
				$jsonResponse['created_on'] = $repairs_onerow->created_on;

				$customerName = array();
				if($customer_name == 1 && $customerObj){
					if($customerrow->company !=''){
						$customerName[] = stripslashes(trim((string) $customerrow->company));
					}
					$customerName[] = stripslashes(trim("$customerrow->first_name $customerrow->last_name"));
				}
				$jsonResponse['customerName'] = $customerName;
				
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

				$contactNo = array();
				if($customer_phone==1 && $customerObj && $customerrow->contact_no !=''){
					$contactNo[] = $customerrow->contact_no;
				}			
				if($customer_secondary_phone==1 && $customerObj && $customerrow->secondary_phone !=''){
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
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
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

				$ticket_no = $repairs_onerow->ticket_no;
				if($ticket_no ==0){
					$ticket_no = $repairs_onerow->repairs_id;
				}
				$jsonResponse['ticket_no'] = intval($ticket_no);

				$salesPerson = '';
				if($sales_person==1 && $pos_onerow->employee_id>0){
					$userObj2 = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id", array());
					if($userObj2){
						$userOneRow = $userObj2->fetch(PDO::FETCH_OBJ);
						$salesPerson = stripslashes(trim("$userOneRow->user_first_name $userOneRow->user_last_name"));
					}
				}
				$jsonResponse['salesPerson'] = $salesPerson;
				$jsonResponse['barcode'] = $barcode;
				
				$jsonResponse['invoiceDate'] = $repairs_onerow->created_on;

				$statusStr = '';
				if($status == 1){
					$statusStr .= $repairs_onerow->status;
				}
				$jsonResponse['statusStr'] = $statusStr;

				$jsonResponse['duedatetime'] = intval($duedatetime);
				$jsonResponse['due_datetime'] = $repairs_onerow->due_datetime;			
				$jsonResponse['due_time'] = $repairs_onerow->due_time;
				
				$technicianStr = '';
				if($technician==1){
					$technicianStr .= $assignToName;
				}
				$jsonResponse['technicianStr'] = $technicianStr;			
				
				$problemStr = '';
				if($short_description==1){
					$problemStr .= stripslashes(trim((string) "$repairs_onerow->problem"));
				}
				$jsonResponse['problemStr'] = $problemStr;			
				
				$customData = array();
				if($repairs_onerow->custom_data !=''){
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'repairs' ORDER BY order_val ASC", array());
					if($queryCFObj){
						$custom_data = unserialize($repairs_onerow->custom_data);
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
				$jsonResponse['custom_fields'] = $customData;
				
				$imei_or_serial_noStr = $Brand_Model_DetailsStr = '';
				if($repairs_onerow->properties_id>0 && ($imei==1 || $brand==1)){
					$cPSql = "SELECT bm.brand, bm.model, p.more_details, p.imei_or_serial_no FROM properties p, brand_model bm WHERE p.properties_id = $repairs_onerow->properties_id AND p.accounts_id = $prod_cat_man AND p.brand_model_id = bm.brand_model_id AND p.properties_publish = 1 GROUP BY p.properties_id ORDER BY bm.brand ASC, bm.model ASC, p.more_details ASC, p.imei_or_serial_no ASC";
					$cPObj = $this->db->query($cPSql, array());
					if($cPObj){
						$propertiesRow = $cPObj->fetch(PDO::FETCH_OBJ);
						if($imei==1){
							$imei_or_serial_noStr = $propertiesRow->imei_or_serial_no;
						}
						if($brand==1){
							$Brand_Model_DetailsStr = trim(stripslashes("$propertiesRow->brand $propertiesRow->model $propertiesRow->more_details"));
						}
					}
				}
				$jsonResponse['imei_or_serial_noStr'] = $imei_or_serial_noStr;
				$jsonResponse['Brand_Model_DetailsStr'] = $Brand_Model_DetailsStr;
				
				$bin_locationStr = '';
				if($bin_location==1 && $repairs_onerow->bin_location !=''){
					$bin_locationStr = $repairs_onerow->bin_location;
				}
				$jsonResponse['bin_locationStr'] = $bin_locationStr;
				
				$lock_passwordStr = '';
				if($lock_password==1 && $repairs_onerow->lock_password !=''){
					$lock_passwordStr .= $repairs_onerow->lock_password;
				}
				$jsonResponse['lock_passwordStr'] = $lock_passwordStr;
				$jsonResponse['status'] = $repairs_onerow->status;

				$taxable_total = $nontaxable_total = 0.00;
				$cartData = array();
				$query = $this->db->query("SELECT * FROM pos_cart WHERE pos_id = $pos_id", array());
				if($query){
					$i=0;
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						
						$pos_cart_id = $row->pos_cart_id;
						$item_id = $row->item_id;
						$item_type = $row->item_type;
						$qty = $row->qty;
						$shipping_qty = $row->shipping_qty;
						if($item_type =='one_time'){$shipping_qty = $qty;}
						
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
							$itemquery = $this->db->query($sqlitem, array());
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
						
						$sales_price = round($row->sales_price,2);
						
						$discount_is_percent = $row->discount_is_percent;
						$discount = $row->discount;
						
						$qty = $row->$qtyfield;
						if($item_type =='one_time'){$qty = $row->qty;}
						
						if($qty>1){
							$description = "<strong>[$qty@$currency$sales_price]</strong> $description";
						}
						$total = round($sales_price*$qty,2);
						if($discount_is_percent>0){
							$discount_value = round($total*0.01*$discount,2);
						}
						else{ 
							$discount_value = round($discount*$qty,2);
						}
						
						$taxable = $row->taxable;																		
						if($taxable>0){
							$taxable_total = $taxable_total+$total-$discount_value;
						}
						else{
							$nontaxable_total = $nontaxable_total+$total-$discount_value;
						}
						
						if($sales_price !=0 || $print_price_zero>0 || $emailYN>0){
							$i++;
							$cartData[] = array('description'=>$description, 'add_description'=>$add_description, 'newimei_info'=>$newimei_info, 'total'=>round($total,2), 'discount_value'=>round($discount_value,2));
						}
					}
				}

				$jsonResponse['cartData'] = $cartData;

				//------------------------------//
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
				//------------------------------//

				if($tax_inclusive1>0){$taxes_total1 = 0;}
				if($tax_inclusive2>0){$taxes_total2 = 0;}
				$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
				
				$totalpayment = 0;
				$paymentData = array();
				$ppSql = "SELECT payment_method, payment_amount, payment_datetime FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
				$ppQueryObj = $this->db->query($ppSql, array());
				if($ppQueryObj){
					while($onerow = $ppQueryObj->fetch(PDO::FETCH_OBJ)){
						
						$payment_amount = round($onerow->payment_amount,2);
						
						$totalpayment = $totalpayment+$payment_amount;
						$paymentData[] = array('payment_datetime'=>$onerow->payment_datetime, 'payment_method'=>$onerow->payment_method, 'payment_amount'=>round($payment_amount,2));
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
				
				$jsonResponse['repair_message'] = nl2br(stripslashes($repair_message));
				
				$additional_disclaimer = '';
				if($repairs_onerow->problem !=''){
					$problemObj = $this->db->query("SELECT additional_disclaimer FROM repair_problems WHERE accounts_id = $prod_cat_man AND name = '".addslashes(stripslashes($repairs_onerow->problem))."' AND additional_disclaimer !=''", array());
					if($problemObj){
						$additional_disclaimer .= $problemObj->fetch(PDO::FETCH_OBJ)->additional_disclaimer;
					}	
				}
				$jsonResponse['additional_disclaimer'] = $additional_disclaimer;
				
				$SmallNotes = array();
				if($notes==1){
					$SmallNotes = $this->getPublicSmallNotes('repairs', $repairs_id);
				}
				$jsonResponse['SmallNotes'] = $SmallNotes;

				$jsonResponse['formsPublicData'] = $this->getPublicFormData('repairs', $repairs_id, 1, 'lr', 0, 1);
			
				return $jsonResponse;
			
			}
			elseif($emailYN==1){
				
				$dateformat = $_SESSION["dateformat"]??'m/d/Y';
				$timeformat = $_SESSION["timeformat"]??'12 hour';
				$printingStr = '';
				$printingStr .= '<!DOCTYPE html>
				<html>
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
					<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />					
					<title>'.$title.'</title>
					<style type="text/css">
						@page {size:portrait;}
						body{ font-family:Arial, sans-serif, Helvetica; min-width:99%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
						h2{font-size:22px; height:20px; margin-bottom:0; padding-bottom:0; font-weight:500;}
						.h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
						address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
						.pright15{padding-right:15px;}
						.ptop10{padding-top:10px;}
						.pbottom10{padding-bottom:10px;}
						.mbottom0{ margin-bottom:0px;}
						table{border-collapse:collapse;}
						.border th{background:#F5F5F6;}
						.border td, .border th{ border:1px solid #DDDDDD; padding:4px 10px; vertical-align: top;}
					</style>					
				</head>
				<body>';
				
				$title = $this->db->translate('Repair Ticket');
				$logo_size = 'Small Logo';
				$logo_placement = 'Left';
				$company_info = $repair_message = $value = '';
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = $technician = $short_description = $imei = $brand = $bin_location = $lock_password = 1;
				$customer_secondary_phone = $customer_type = $barcode = $status = $duedatetime = $print_price_zero = $notes = 0;
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
                if($printType == 'large' && $repairs_onerow->status=='Estimate'){
					$title = $this->db->translate('Estimate');
				}								
				$companylogo = "";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$pics = glob($filePath."*.jpg");
				if($pics){
					foreach($pics as $onePicture){
						$onePicture = "http://$domainName".str_replace('./', '/', $onePicture);
						if($logo_size=='Large Logo'){
							$style = 'max-height:150px;max-width:350px;';
						}
						else{
							$style = 'max-height:100px;max-width:150px;';
						}
						$companylogo = "<img style=\"$style\" src=\"$onePicture\" title=\"".$this->db->translate('Logo')."\" />";
					}				
				}				
					
				$printingStr .= '<table cellpadding="0" cellspacing="1" width="100%">
						<tr>
							<td align="center">
								<h2>'.$title.'</h2>
							</td>
						</tr>
						<tr>
							<td>
								<table width="100%" cellpadding="0" cellspacing="0">';
									
				$customerName = '';
				if($customer_name == 1 && $customerObj){
					if($customerrow->company !=''){
						$customerName .= stripslashes(trim((string) $customerrow->company)).'<br />';
					}
					$customerName .= stripslashes(trim("$customerrow->first_name $customerrow->last_name"));
				}
				$customerAddress = '';
				if($customer_address==1 && $customerObj){
					if($customerrow->shipping_address_one !=''){										
						$customerAddress .= '<br>'.stripslashes(trim((string) "$customerrow->shipping_address_one"));
					}
					if($customerrow->shipping_city !='' || $customerrow->shipping_state !='' || $customerrow->shipping_zip !=''){
						if($logo_placement !='Center'){$customerAddress .= '<br>';}
						$customerAddress .= stripslashes(trim("$customerrow->shipping_city $customerrow->shipping_state $customerrow->shipping_zip"));
					}
				}
				$contactNo = '';
				if($customer_phone==1 && $customerObj && $customerrow->contact_no !=''){
					$contactNo = '<br>'.$customerrow->contact_no;
				}
				if($customer_secondary_phone==1 && $customerObj && $customerrow->secondary_phone !=''){
					$contactNo .= '<br>'.$customerrow->secondary_phone;
				}
				$customerEmail = '';
				if($customer_email==1 && $customerObj && $customerrow->email !=''){
					$customerEmail = '<br>'.$customerrow->email;
				}
				if($customer_type==1 && $customerObj){
					if($customerrow->customer_type !='')
						$customerEmail .= '<br>'.$customerrow->customer_type;
				}
				
				$customFieldsData = "";																	
				if($customerObj && $customerrow->custom_data !=''){
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
					if($queryCFObj){
						$custom_data = unserialize($customerrow->custom_data);
						while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
							$field_name = stripslashes($oneCustomFields->field_name);
							$checked = '';
							if(!empty($value) && array_key_exists("cf$oneCustomFields->custom_fields_id",$value) && $value["cf$oneCustomFields->custom_fields_id"]>0 && !empty($custom_data)){
								if(array_key_exists($field_name, $custom_data) && $custom_data[$field_name] !=''){
									$customFieldsData .= '<br>'.$field_name.": ".$custom_data[$field_name];
								}
							}
						}
					}
				}
				$ticket_no = $repairs_onerow->ticket_no;
				if($ticket_no ==0){
					$ticket_no = $repairs_onerow->repairs_id;
				}
				$salesPerson = '';
				if($sales_person==1 && $pos_onerow->employee_id>0){
					$userObj2 = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id", array());
					if($userObj2){
						$userOneRow = $userObj2->fetch(PDO::FETCH_OBJ);
						$salesPerson = "<p>".$this->db->translate('Sales Person')." : ".stripslashes(trim("$userOneRow->user_first_name $userOneRow->user_last_name"))."</p>";
					}
				}
				$barCodeStr = '';
				if($barcode==1){
					$barCodeStr = "<div style=\"clear:both\"></div>
									<img style=\"max-width:96%;\" src=\"http://$domainName/Createbarcode/t$ticket_no\" alt=\"t$ticket_no\"></img>
									<div style=\"clear:both\"></div>";
				}
				
				if($timeformat=='24 hour'){$invoiceDate =  date($dateformat.' H:i', strtotime($repairs_onerow->created_on));}
				else{$invoiceDate =  date($dateformat.' g:i a', strtotime($repairs_onerow->created_on));}
					
				$colSpan = 2;
				if($logo_placement=='Center'){
					if($companylogo !=''){
						$printingStr .= "<tr><td colspan=\"2\" align=\"center\" class=\"ptop10 pbottom10\">$companylogo</td></tr>";
					}
					
					$printingStr .= "<tr>
							<td colspan=\"2\" align=\"center\">".nl2br($company_info)."</td>
						</tr>
						<tr>
							<td align=\"left\" width=\"50%\">
								<address class=\"mbottom0\">
									<span>".$this->db->translate('Bill To').":</span> <strong>$customerName</strong>
									$customerAddress
									$contactNo
									$customerEmail
									$customFieldsData
								</address>
							</td>
							<td align=\"right\">
								<h4>".$this->db->translate('Ticket')." #: t$ticket_no</h4>
								$salesPerson
								$barCodeStr
								<p>$invoiceDate</p>";
				
					if($status == 1){
						$printingStr .= "<br />".$this->db->translate('Status').": $repairs_onerow->status";
					}
					
					if($duedatetime == 1 && !in_array($repairs_onerow->due_datetime, array('0000-00-00', '1000-01-01'))){
						$printingStr .= "<br />".stripslashes($this->db->translate('Due Date'))." : ".date($dateformat, strtotime($repairs_onerow->due_datetime)).' '.$repairs_onerow->due_time;
					}
					
					$printingStr .= "</td>
						</tr>";
				}
				else{
					$printingStr .= "<tr>";
					if($companylogo !=''){
						$printingStr .= "<td width=\"150\" valign=\"top\" class=\"pright15\">$companylogo</td>";
					}
					else{$colSpan = 1;}
					
					$printingStr .= "<td align=\"left\" valign=\"top\"> 
							".nl2br($company_info)."
						</td>
						<td width=\"35%\" align=\"right\" rowspan=\"2\">
							<address class=\"mbottom0\">
								<span>".$this->db->translate('Bill To').":</span> <strong>$customerName</strong>
								$customerAddress
								$contactNo
								$customerEmail
								$customFieldsData
							</address>
							<h4>".$this->db->translate('Ticket')." #: t$ticket_no</h4>
							$salesPerson
							$barCodeStr
							<p>$invoiceDate</p>";
					if($status == 1){
						$printingStr .= $this->db->translate('Status').": $repairs_onerow->status";
					}
					if($duedatetime == 1 && !in_array($repairs_onerow->due_datetime, array('0000-00-00', '1000-01-01'))){
						$printingStr .= "<br />".stripslashes($this->db->translate('Due Date')).": ".date($dateformat, strtotime($repairs_onerow->due_datetime)).' '.$repairs_onerow->due_time;
					}
					$printingStr .= "</td>
					</tr>";
				}
				
				$printingStr .= "</table>
							</td>
						</tr>
						<tr>
							<td valign=\"top\" class=\"pbottom10\" align=\"left\">
								<table class=\"border\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
									<tr>
										<th width=\"50%\" align=\"justify\">".$this->db->translate('Ticket Info')."</th>
										<th align=\"justify\">".$this->db->translate('Hardware Info')."</th>
									</tr>
									<tr>
										<td valign=\"top\">";
				$ticketInfo = '';
				if($technician==1){
					$ticketInfo .= "<b>".$this->db->translate('Technician')."</b> $assignToName";
				}
				
				if($short_description==1){
					if($ticketInfo !=''){$ticketInfo .='<br />';}
					$ticketInfo .="<b>".$this->db->translate('Problem').":</b> ".stripslashes(trim((string) "$repairs_onerow->problem"));
				}
				$repairCustField = '';
				if($repairs_onerow->custom_data !=''){
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'repairs' ORDER BY order_val ASC", array());
					if($queryCFObj){
						$custom_data = unserialize($repairs_onerow->custom_data);
						while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
							$field_name = stripslashes($oneCustomFields->field_name);
							$checked = '';
							if(!empty($value) && array_key_exists("cf$oneCustomFields->custom_fields_id",$value) && $value["cf$oneCustomFields->custom_fields_id"]>0 && !empty($custom_data)){
								if(array_key_exists($field_name, $custom_data) && $custom_data[$field_name] !=''){
									if($ticketInfo !=''){$ticketInfo .='<br />';}
									$ticketInfo .= "<b>$field_name</b>: $custom_data[$field_name]";
								}
							}
						}
					}
				}
				$printingStr .= "$ticketInfo
								</td>
                                <td valign=\"top\">";
				$hardwareInfo = '';
				if($repairs_onerow->properties_id>0 && ($imei==1 || $brand==1)){
					$cPSql = "SELECT bm.brand, bm.model, p.more_details, p.imei_or_serial_no FROM properties p, brand_model bm WHERE p.properties_id = $repairs_onerow->properties_id AND p.accounts_id = $prod_cat_man AND p.brand_model_id = bm.brand_model_id AND p.properties_publish = 1 GROUP BY p.properties_id ORDER BY bm.brand ASC, bm.model ASC, p.more_details ASC, p.imei_or_serial_no ASC";
					$cPObj = $this->db->query($cPSql, array());
					if($cPObj){
						$propertiesRow = $cPObj->fetch(PDO::FETCH_OBJ);
						if($imei==1){
							$hardwareInfo .="<b>".$this->db->translate('IMEI/Serial No.').":</b> $propertiesRow->imei_or_serial_no";
						}
						if($brand==1){
							if($hardwareInfo !=''){$hardwareInfo .='<br />';}
							$hardwareInfo .="<b>".$this->db->translate('Brand/Model/More Details:').":</b> ".trim(stripslashes("$propertiesRow->brand $propertiesRow->model $propertiesRow->more_details"));
						}
					}
				}
				
				if($bin_location==1 && $repairs_onerow->bin_location !=''){
					if($hardwareInfo !=''){$hardwareInfo .='<br />';}
					$hardwareInfo .="<b>".$this->db->translate('Bin Location').":</b> $repairs_onerow->bin_location";
				}
				
				if($lock_password==1 && $repairs_onerow->lock_password !=''){
					if($hardwareInfo !=''){$hardwareInfo .='<br />';}
					$hardwareInfo .="<b>".$this->db->translate('Password').":</b> $repairs_onerow->lock_password";
				}
				$printingStr .= "$hardwareInfo</td>
                                        </tr>                            
                                    </table>
                                </td>
                            </tr>";
				if($status=='Estimate'){
					$printingStr .= "<tr>
						<td align=\"center\">
							<b>".$this->db->translate('Estimate')."</b>
						</td>
					</tr>";
				}
				$printingStr .= "<tr>
							<td>						
								<table class=\"border\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
									<tr>
										<th width=\"3%\" align=\"right\">#</th>
										<th align=\"left\">".$this->db->translate('Description')."</th>
										<th width=\"8%\">".$this->db->translate('QTY')."</th>
										<th width=\"12%\">".$this->db->translate('Unit Price')."</th>
										<th width=\"15%\">".$this->db->translate('Total')."</th>
									</tr>";
									
				if($pos_onerow){
					$taxable_total = $nontaxable_total = 0.00;							
					$pos_id = $pos_onerow->pos_id;
					$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
					$query = $this->db->query($sqlquery, array());
					if($query){
						$i=0;
						while($row = $query->fetch(PDO::FETCH_OBJ)){
							$i++;
							$pos_cart_id = $row->pos_cart_id;
							$item_id = $row->item_id;
							$item_type = $row->item_type;
							$qty = $row->qty;
							$shipping_qty = $row->$qtyfield;
							if($item_type =='one_time'){$shipping_qty = $qty;}
							
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
								$itemquery = $this->db->query($sqlitem, array());
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
							$sales_pricestr = $currency.number_format($sales_price,2);
							if($sales_price<0){
								$sales_pricestr = "-$currency".number_format($sales_price*(-1),2);
							}
							
							$discount_is_percent = $row->discount_is_percent;
							$discount = $row->discount;
							
							$total = round($sales_price*$shipping_qty,2);
							$totalstr = $currency.number_format($total,2);
							if($total <0 ){
								$totalstr = "-$currency".number_format(($total*(-1)),2);
							}
							if($discount_is_percent>0){
								$discount_value = round($total*0.01*$discount,2);
							}
							else{
								$discount_value = round($discount*$shipping_qty,2);
							}
							
							if($discount_value>0){
								$totalstr .= "<br />-$currency".number_format($discount_value,2);
							}
							elseif($discount_value<0){
								$totalstr .= "<br />$currency".number_format(($discount_value*(-1)),2);
							}
							
							$taxable = $row->taxable;																		
							if($taxable>0){
								$taxable_total = $taxable_total+$total-$discount_value;
							}
							else{
								$nontaxable_total = $nontaxable_total+$total-$discount_value;
							}
							$newimei_info = implode('<br />', $newimei_info);
							if($sales_price !=0 || $print_price_zero>0){
								
								if(!empty($add_description)){$add_description = "<br>$add_description";}
								if(!empty($newimei_info)){$newimei_info = "<br>$newimei_info";}

								$printingStr .= "<tr>
												<td align=\"right\">$i</td>
												<td align=\"left\">
													$description$newimei_info
													<span>$newimei_info</span>
												</td>
												<td align=\"right\">$shipping_qty</td>
												<td align=\"right\">$sales_pricestr</td>
												<td align=\"right\">$totalstr</td>
											</tr>";
							}
						}
					}
					else{
						$printingStr .= "<tr><td colspan=\"5\">".$this->db->translate('There is no data found')."</td></tr>";
					}
				}
				else{
					$printingStr .= "<tr><td colspan=\"5\">".$this->db->translate('There is no data found')."</td></tr>";
				}
				
				if($pos_onerow->taxes_name1 !=''){
					$taxable_totalstr = $currency.number_format($taxable_total,2);
					if($taxable_total<0){
						$taxable_totalstr = "-$currency".number_format($taxable_total*(-1),2);
					}
					
					$printingStr .= "<tr>
						<td align=\"right\" colspan=\"4\"><strong>".$this->db->translate('Taxable Total')." :</strong></td>
						<td align=\"right\"><strong>$taxable_totalstr</strong></td>
					</tr>";
				}
				
				$taxes_total1 = 0;					
				$tax_inclusive1 = $pos_onerow->tax_inclusive1;
				if($pos_onerow->taxes_name1 !=''){
					$tiStr = '';
					if($tax_inclusive1>0){$tiStr = ' Inclusive';}
					
					$taxes_total1 = $Common->calculateTax($taxable_total, $pos_onerow->taxes_percentage1, $pos_onerow->tax_inclusive1);
					$taxes_totalstr = $currency.number_format($taxes_total1,2);
					if($taxes_total1<0){
						$taxes_totalstr = "-$currency".number_format($taxes_total1*(-1),2);
					}					
					
					$printingStr .= "<tr>
						<td align=\"right\" colspan=\"4\"><strong>$pos_onerow->taxes_name1 ($pos_onerow->taxes_percentage1%$tiStr) :</strong></td>
						<td align=\"right\"><strong>$taxes_totalstr</strong></td>
					</tr>";
				}
				
				$taxes_total2 = 0;					
				$tax_inclusive2 = $pos_onerow->tax_inclusive2;
				if($pos_onerow->taxes_name2 !=''){
					$tiStr = '';
					if($tax_inclusive2>0){$tiStr = ' Inclusive';}
					
					$taxes_total2 = $Common->calculateTax($taxable_total, $pos_onerow->taxes_percentage2, $pos_onerow->tax_inclusive2);
					$taxes_totalstr = $currency.number_format($taxes_total2,2);
					if($taxes_total2<0){
						$taxes_totalstr = "-$currency".number_format($taxes_total2*(-1),2);
					}
					
					$printingStr .= "<tr>
						<td align=\"right\" colspan=\"4\"><strong>$pos_onerow->taxes_name2 ($pos_onerow->taxes_percentage2%$tiStr) :</strong></td>
						<td align=\"right\"><strong>$taxes_totalstr</strong></td>
					</tr>";
				}
				
				if($nontaxable_total>0 || $nontaxable_total<0){
					$taxable_totalstr = $currency.number_format($nontaxable_total,2);
					if($nontaxable_total<0){
						$taxable_totalstr = "-$currency".number_format($nontaxable_total*(-1),2);
					}
					
					$printingStr .= "<tr>
						<td align=\"right\" colspan=\"4\"><strong>".$this->db->translate('Non Taxable Total')." :</strong></td>
						<td align=\"right\"><strong>$taxable_totalstr</strong></td>
					</tr>";
				}
				
				if($tax_inclusive1>0){$taxes_total1 = 0;}
				if($tax_inclusive2>0){$taxes_total2 = 0;}
				$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
				$grand_totalstr = $currency.number_format($grand_total,2);
				if($grand_total<0){
					$grand_totalstr = "-$currency".number_format($grand_total*(-1),2);
				}
				
				$printingStr .= "<tr>
					<td align=\"right\" colspan=\"4\"><strong>".$this->db->translate('Grand Total')." :</strong></td>
					<td align=\"right\"><strong>$grand_totalstr</strong></td>
				</tr>";
				
				$totalpayment = 0;
				$ppSql = "SELECT payment_method, payment_amount, payment_datetime FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
				$ppQueryObj = $this->db->query($ppSql, array());
				if($ppQueryObj){
					while($onerow = $ppQueryObj->fetch(PDO::FETCH_OBJ)){
						
						$payment_amount = $onerow->payment_amount;
						if($timeformat=='24 hour'){$payment_datetime =  date($dateformat.' H:i', strtotime($onerow->payment_datetime));}
						else{$payment_datetime =  date($dateformat.' g:i a', strtotime($onerow->payment_datetime));}
				
						$totalpayment = $totalpayment+$payment_amount;
						$payment_amountstr = $currency.number_format($payment_amount,2);
						if($payment_amount<0){
							$payment_amountstr = '-'.$currency.number_format($payment_amount*(-1),2);
						}
						
						$printingStr .= "<tr>
								<td align=\"right\" colspan=\"4\">
									$payment_datetime $onerow->payment_method ".$this->db->translate('Payment')."
								</td>
								<td align=\"right\">
									$payment_amountstr
								</td>
							</tr>";						
					}
				}
				
				if($grand_total>$totalpayment){
					$amountDue = $grand_total-$totalpayment;
					$credit_days = $pos_onerow->credit_days;
					$salesTime = strtotime($pos_onerow->sales_datetime);
					
					$printingStr .= "<tr class=\"border\">
						<td align=\"center\" colspan=\"3\">".$this->db->translate('Total amount due by')." ".date($dateformat, strtotime("+$credit_days day", $salesTime))."</td>
						<td align=\"right\" nowrap><strong>".$this->db->translate('Amount Due')." :</strong></td>
						<td align=\"right\"><strong>$currency".number_format($amountDue,2)."</strong></td>
					</tr>";
				}								
				
				$printingStr .= "</table>
							</td>
						</tr>
						<tr>
							<td align=\"center\">
								<p><br/>".nl2br(stripslashes($repair_message))."</p>"; 
				if($repairs_onerow->problem !=''){
					$problemObj = $this->db->query("SELECT additional_disclaimer FROM repair_problems WHERE accounts_id = $prod_cat_man AND name = '".addslashes(stripslashes($repairs_onerow->problem))."' AND additional_disclaimer !=''", array());
					if($problemObj){
						$printingStr .= '<p>'.$problemObj->fetch(PDO::FETCH_OBJ)->additional_disclaimer.'</p>';
					}	
				}
				
				$printingStr .= '</td>
                            </tr>';
				if($notes==1){
					$printingStr .= '<tr>
						<td>'.$this->getPublicNotes('repairs', $repairs_id).'</td>
					</tr>';
				}
				
				$formsPublicData = $this->getPublicFormData('repairs', $repairs_id, 1, 'lr');
				if($formsPublicData !=''){
					$printingStr .= "<tr>
						<td align=\"center\">$formsPublicData</td>
					</tr>";
				}
				$printingStr .= "</table>
							</body>
						</html>";
				return $printingStr;

			}
			else{
				$jsonResponse = array();
				$jsonResponse['login'] = '';
				$jsonResponse['table_id'] = $repairs_id;
				$jsonResponse['for_table'] = 'repairs';
				
				$title = $this->db->translate('Repair Ticket');
				$logo_size = 'Small Logo';
				$logo_placement = 'Left';
				$company_info = $repair_message = $value = '';
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = $technician = $short_description = $imei = $brand = $bin_location = $lock_password = 1;
				$customer_secondary_phone = $customer_type = $barcode = $status = $duedatetime = $print_price_zero = $notes = 0;
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				$jsonResponse['logo_placement'] = $logo_placement;
				$jsonResponse['company_info'] = nl2br(stripslashes($company_info));
				
				if($repairs_onerow->status=='Estimate'){
					$title = $this->db->translate('Estimate');
				}
				$jsonResponse['title'] = $title;

				$companylogo = "";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$pics = glob($filePath."*.jpg");
				if($pics){
					foreach($pics as $onePicture){
						$onePicture = "http://$domainName".str_replace('./', '/', $onePicture);
						$companylogo = $onePicture;
					}				
				}
				$jsonResponse['logo_size'] = $logo_size;
				$jsonResponse['companylogo'] = $companylogo;
				$colSpan = 2;
				if($logo_placement=='Left' && $companylogo ==''){$colSpan = 1;}
				$jsonResponse['colSpan'] = $colSpan;
				
				$customerName = array();
				if($customer_name == 1 && $customerObj){
					if($customerrow->company !=''){
						$customerName[] = stripslashes(trim((string) $customerrow->company));
					}
					$customerName[] = stripslashes(trim("$customerrow->first_name $customerrow->last_name"));
				}
				$jsonResponse['customerName'] = $customerName;

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

				$contactNo = array();
				if($customer_phone==1 && $customerObj && $customerrow->contact_no !=''){
					$contactNo[] = $customerrow->contact_no;
				}			
				if($customer_secondary_phone==1 && $customerObj && $customerrow->secondary_phone !=''){
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
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers' ORDER BY order_val ASC", array());
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

				$ticket_no = floatval($repairs_onerow->ticket_no);
				if($ticket_no ==0){
					$ticket_no = floatval($repairs_onerow->repairs_id);
				}
				$jsonResponse['ticket_no'] = intval($ticket_no);

				$salesPerson = '';
				if($sales_person==1 && $pos_onerow->employee_id>0){
					$userObj2 = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id", array());
					if($userObj2){
						$userOneRow = $userObj2->fetch(PDO::FETCH_OBJ);
						$salesPerson = stripslashes(trim("$userOneRow->user_first_name $userOneRow->user_last_name"));
					}
				}
				$jsonResponse['salesPerson'] = $salesPerson;
				$jsonResponse['barcode'] = $barcode;
				
				$jsonResponse['invoiceDate'] = $repairs_onerow->created_on;

				$statusStr = '';
				if($status == 1){
					$statusStr .= $repairs_onerow->status;
				}
				$jsonResponse['statusStr'] = $statusStr;		
				
				$jsonResponse['duedatetime'] = intval($duedatetime);
				$jsonResponse['due_datetime'] = $repairs_onerow->due_datetime;			
				$jsonResponse['due_time'] = $repairs_onerow->due_time;			
				
				$ticketInfo = array();
				if($technician==1){
					$ticketInfo['technician'] = $assignToName;
				}
				if($short_description==1){
					$ticketInfo['problem'] = stripslashes(trim((string) "$repairs_onerow->problem"));
				}
				if($repairs_onerow->custom_data !=''){
					$queryCFObj = $this->db->query("SELECT custom_fields_id, field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'repairs' ORDER BY order_val ASC", array());
					if($queryCFObj){
						$custom_data = unserialize($repairs_onerow->custom_data);
						while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
							$field_name = stripslashes($oneCustomFields->field_name);
							if(!empty($value) && array_key_exists("cf$oneCustomFields->custom_fields_id",$value) && $value["cf$oneCustomFields->custom_fields_id"]>0 && !empty($custom_data)){
								if(array_key_exists($field_name, $custom_data) && $custom_data[$field_name] !=''){
									$ticketInfo['custom_fields'][$field_name] = $custom_data[$field_name];
								}
							}
						}
					}
				}
				$jsonResponse['ticketInfo'] = $ticketInfo;
				
				$IMEI_Serial_No = $Brand_Model_Details = $Bin_Location = $Password = '';
				if($repairs_onerow->properties_id>0 && ($imei==1 || $brand==1)){
					$cPSql = "SELECT bm.brand, bm.model, p.more_details, p.imei_or_serial_no FROM properties p, brand_model bm WHERE p.properties_id = $repairs_onerow->properties_id AND p.accounts_id = $prod_cat_man AND p.brand_model_id = bm.brand_model_id AND p.properties_publish = 1 GROUP BY p.properties_id ORDER BY bm.brand ASC, bm.model ASC, p.more_details ASC, p.imei_or_serial_no ASC";
					$cPObj = $this->db->query($cPSql, array());
					if($cPObj){
						$propertiesRow = $cPObj->fetch(PDO::FETCH_OBJ);
						if($imei==1){
							$IMEI_Serial_No .= $propertiesRow->imei_or_serial_no;
						}
						if($brand==1){
							$Brand_Model_Details .= trim(stripslashes("$propertiesRow->brand $propertiesRow->model $propertiesRow->more_details"));
						}
					}
				}
				
				if($bin_location==1 && $repairs_onerow->bin_location !=''){
					$Bin_Location .= $repairs_onerow->bin_location;
				}
				
				if($lock_password==1 && $repairs_onerow->lock_password !=''){
					$Password .= $repairs_onerow->lock_password;
				}
				$jsonResponse['IMEI_Serial_No'] = $IMEI_Serial_No;
				$jsonResponse['Brand_Model_Details'] = $Brand_Model_Details;
				$jsonResponse['Bin_Location'] = $Bin_Location;
				$jsonResponse['Password'] = $Password;

				$taxable_total = $nontaxable_total = 0.00;
				$cartData = array();
				if($pos_onerow){
												
					$pos_id = $pos_onerow->pos_id;
					$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
					$query = $this->db->query($sqlquery, array());
					if($query){
						$i=0;
						while($row = $query->fetch(PDO::FETCH_OBJ)){
							$i++;
							$pos_cart_id = $row->pos_cart_id;
							$item_id = $row->item_id;
							$item_type = $row->item_type;
							$qty = floatval($row->qty);
							$shipping_qty = floatval($row->$qtyfield);
							if($item_type =='one_time'){$shipping_qty = $qty;}
							
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
								$itemquery = $this->db->query($sqlitem, array());
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
							
							$sales_price = round($row->sales_price,2);							
							$discount_is_percent = $row->discount_is_percent;
							$discount = $row->discount;
							
							$total = round($sales_price*$shipping_qty,2);
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
							
							if($sales_price !=0 || $print_price_zero>0){							
								$cartData[] = array('description'=>$description, 'add_description'=>$add_description, 'newimei_info'=>$newimei_info, 'shipping_qty'=>floatval($shipping_qty), 'sales_price'=>round($sales_price,2), 'discount_value'=>round($discount_value,2));
							}
						}
					}
				}
				$jsonResponse['cartData'] = $cartData;
				
				//-----------------------------------//
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
				//--------------------------------------//

				if($tax_inclusive1>0){$taxes_total1 = 0;}
				if($tax_inclusive2>0){$taxes_total2 = 0;}
				$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
				
				$totalpayment = 0;
				$paymentData = array();
				$ppSql = "SELECT payment_method, payment_amount, payment_datetime FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change'";
				$ppQueryObj = $this->db->query($ppSql, array());
				if($ppQueryObj){
					while($onerow = $ppQueryObj->fetch(PDO::FETCH_OBJ)){
						
						$payment_amount = round($onerow->payment_amount,2);
						
						$totalpayment = $totalpayment+$payment_amount;
						$paymentData[] = array('payment_datetime'=>$onerow->payment_datetime, 'payment_method'=>$onerow->payment_method, 'payment_amount'=>round($payment_amount,2));
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
				
				$jsonResponse['repair_message'] = nl2br(stripslashes($repair_message));
				
				$additional_disclaimer = '';
				if($repairs_onerow->problem !=''){
					$problemObj = $this->db->query("SELECT additional_disclaimer FROM repair_problems WHERE accounts_id = $prod_cat_man AND name = '".addslashes(stripslashes($repairs_onerow->problem))."' AND additional_disclaimer !=''", array());
					if($problemObj){
						$additional_disclaimer .= $problemObj->fetch(PDO::FETCH_OBJ)->additional_disclaimer;
					}	
				}
				$jsonResponse['additional_disclaimer'] = $additional_disclaimer;
				
				$noteData = array();
				if($notes==1){
					$noteData = $this->getPublicNotes('repairs', $repairs_id,1);
				}
				$jsonResponse['noteData'] = $noteData;

				$jsonResponse['formsPublicData'] = $this->getPublicFormData('repairs', $repairs_id, 1, 'lr', 0, 1);
			
				return $jsonResponse;
			}
		}
	}
	
	public function labelsInfo($frompage='PO', $returnType='HTML'){
		$domainName = OUR_DOMAINNAME;
		$printingStr = '';
		if(isset($_SESSION["accounts_id"])){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			if($returnType=='HTML'){
				$currency = $_SESSION["currency"]??'৳';
				$loadLangFile = $_SESSION["language"]??'English';
				$title = $this->db->translate('Product Barcode Print');
				
				$accObj = $this->db->query("SELECT label_prints_count FROM accounts WHERE accounts_id = $accounts_id", array());
				if($accObj){
					$label_prints_count = intval($accObj->fetch(PDO::FETCH_OBJ)->label_prints_count);
					$label_prints_count = floor($label_prints_count+1);
					$this->db->update('accounts', array('label_prints_count'=>$label_prints_count), $accounts_id);
				}
				
				$printingStr .= '<!DOCTYPE html>
				<html>
				<head>
					<meta charset="utf-8">
					<title>'.$title.'</title>
					<script language="JavaScript" type="text/javascript">
						var currency = \''.$currency.'\';
						var loadLangFile = \''.$loadLangFile.'\';
						var OS;
						var langModifiedData = {};
						var languageData = {};
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
						".'
					</script>';
					if($loadLangFile !='English'){
						$printingStr .= "<script src=\"/assets/js-".swVersion."/languages/$loadLangFile.js\"></script>";

						if(isset($_SESSION) && array_key_exists('languageJSVar', $_SESSION)){
							$languageJSVar = $_SESSION["languageJSVar"];
							if(!empty($languageJSVar)){
								$printingStr .= "
								<script language=\"JavaScript\" type=\"text/javascript\">
								langModifiedData = {";
								foreach($languageJSVar as $varName=>$varValue){
									$printingStr .= '
						\''.trim((string) $varName).'\':stripslashes(\''.addslashes($varValue).'\'),';
								}
								$printingStr .= '}
								</script>';
							}
						}
					}
					$printingStr .= '<script type="module" src="/assets/js-'.swVersion.'/'.printsJS.'"></script>
				</head>
				<body></body>
				</html>';

				return $printingStr;
			}
			elseif($returnType=='commonInfo'){
				$Common = new Common($this->db);

				$commonInfo = array();
				$commonInfo['CompanyName'] = $_SESSION["company_name"];	
				$vData = $Common->variablesData('barcode_labels', $accounts_id);
				if(!empty($vData)){
					if(in_array($frompage, array('PO','IT', 'product'))){
						if(array_key_exists('productLabel', $vData)){
							$productLabel = $vData['productLabel'];
						}
					}
					if(in_array($frompage, array('PO', 'IMEI', 'IT'))){
						if(array_key_exists('deviceLabel', $vData)){
							$deviceLabel = $vData['deviceLabel'];
						}
					}
					if($frompage=='Repairs'){
						if(array_key_exists('repairTicketLabel', $vData)){
							$repairTicketLabel = $vData['repairTicketLabel'];
						}
					}
					if($frompage=='repairCustomer'){
						if(array_key_exists('repairCustomerLabel', $vData)){
							$repairCustomerLabel = $vData['repairCustomerLabel'];
						}
					}
				}
				if(in_array($frompage, array('PO','IT', 'product'))){
					$commonInfo['productLabel'] = str_replace("\r\n","\n", $productLabel);
				}
				if(in_array($frompage, array('PO', 'IMEI', 'IT'))){
					$commonInfo['deviceLabel'] = str_replace("\r\n","\n", $deviceLabel);
				}
				if($frompage=='Repairs'){
					$commonInfo['repairTicketLabel'] = str_replace("\r\n","\n", $repairTicketLabel);
				}
				if($frompage=='repairCustomer'){
					$commonInfo['repairCustomerLabel'] = str_replace("\r\n","\n", $repairCustomerLabel);
				}
				
				$vData = $Common->variablesData('label_printer', $accounts_id);
				$labelSizeMissing = true;
				$labelwidth = 57;
				$labelheight = 31;
				$fontSize = 'Regular';
				$fontFamily = 'Arial';
				$units = 'mm';
				$top_margin = $right_margin = $bottom_margin = $left_margin = 0;
				
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
				$commonInfo['fontSize'] = $fontSize;
				if(empty($fontFamily)){$fontFamily = 'Arial';}
				$commonInfo['fontFamily'] = $fontFamily;
				$commonInfo['labelSizeMissing'] = $labelSizeMissing;

				$commonInfo['labelwidth'] = $labelwidth*3.7795275591;
				$commonInfo['labelheight'] = $labelheight*3.7795275591;
				$commonInfo['top_margin'] = $top_margin;
				$commonInfo['right_margin'] = $right_margin;
				$commonInfo['bottom_margin'] = $bottom_margin;
				$commonInfo['left_margin'] = $left_margin;
				$commonInfo['orientation'] = $orientation;
				$commonInfo['title'] = $this->db->translate('Product Barcode Print');

				return $commonInfo;
			}
		}
		
		
	}
	
	public function poInvoicesInfo($po_id, $fromPage = 'Purchase_orders', $emailYN=0, $accounts_id = 0, $prod_cat_man = 0){
		if(isset($_SESSION["prod_cat_man"])){$prod_cat_man = $_SESSION["prod_cat_man"]??0;}
		if(isset($_SESSION["accounts_id"])){$accounts_id = $_SESSION["accounts_id"]??0;}
		$currency = '৳';
		if(isset($_SESSION["currency"])){$currency = $_SESSION["currency"]??'৳';}
		
		$domainName = OUR_DOMAINNAME;
		$onlyDomainName = OUR_DOMAINNAME;
		
		$companylogo = "";
		$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
		$pics = glob($filePath."*.jpg");
		if($pics){		
			foreach($pics as $onePicture){
				$onePicture = "//$onlyDomainName".str_replace('./', '/', $onePicture);
				$companylogo = $onePicture;
			}				
		}
		$company_info = '';
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;	
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('company_info', $value)){
					$company_info = $value['company_info'];
				}
			}
		}
		
		$po_message = '';
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'po_setup'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;	
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('po_message', $value)){
					$po_message = $value['po_message'];
				}
			}
		}
		
		if($fromPage == 'Inventory_Transfer'){
			$title = $this->db->translate('Inventory Transfer Print-po #:');
		}
		else{
			$title = $this->db->translate('Purchase Orders');
		}
		
		$poObj = $this->db->query("SELECT * FROM po WHERE po_id = :po_id AND accounts_id = $accounts_id", array('po_id'=>$po_id),1);
		if($poObj){
			$po_onerow = $poObj->fetch(PDO::FETCH_OBJ);
			$po_number = $po_onerow->po_number;
			$title .= ' # p' .$po_number;
		}
		else{
			return '';
		}
		
		if($emailYN==1){
			$dateformat = $_SESSION["dateformat"]??'m/d/Y';
			$this->db->writeIntoLog($companylogo);
			$printingStr = '<!DOCTYPE html>
						<html>
						<head>
							<meta charset="utf-8">
							<meta name="viewport" content="width=device-width, initial-scale=1">
							<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
							<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />					
							<title>'.$title.'</title>
							<style type="text/css">
								@page {size:portrait;}
								body{ font-family:Arial, sans-serif, Helvetica; min-width:99%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
								h2{font-size:22px; height:20px; margin-bottom:0; padding-bottom:0; font-weight:500;}
								.h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
								address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
								.pright15{padding-right:15px;}
								.ptop10{padding-top:10px;}
								.mbottom0{ margin-bottom:0px;}
								table{border-collapse:collapse;}
								.border th{background:#F5F5F6;}
								.border td, .border th{ border:1px solid #DDDDDD; padding:4px 10px; vertical-align: top;}
								.bgblack{background: #2f3949;color: #c6d2e5;}
							</style>					
						</head>
						<body>';
			$supplierName = '';
			$supplier_id = $po_onerow->supplier_id;
			if($fromPage == 'Inventory_Transfer'){
				$topTitle = $this->db->translate('Inventory Transfer');
				if($po_onerow->transfer==1){$toOrFrom = $this->db->translate('To');}
				else{$toOrFrom = $this->db->translate('from');}
				$suplierLabel = $this->db->translate('Transfer')." $toOrFrom ".$this->db->translate('Info');
				$supplierObj = $this->db->query("SELECT accounts.company_subdomain, user.user_first_name, user.user_last_name, user.user_email, accounts.company_phone_no FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND user.is_admin = 1 AND accounts.accounts_id = $supplier_id", array());
				if($supplierObj){
					$supplierrow = $supplierObj->fetch(PDO::FETCH_OBJ);
					$supplierName .= $supplierrow->company_subdomain."<br>";
					$supplierName .= $supplierrow->user_first_name." ".$supplierrow->user_last_name."<br>";
					if($supplierrow->user_email !='')
						$supplierName .= $supplierrow->user_email."<br>";
					if($supplierrow->company_phone_no !='')
						$supplierName .= $supplierrow->company_phone_no."<br>";
				}
				$PO_NumberLabel = $this->db->translate('Transfer');
			}
			else{
				$topTitle = $this->db->translate('Purchase Orders');
				$suplierLabel = $this->db->translate('Supplier Info');
				$supplierObj = $this->db->query("SELECT first_name, last_name, email, contact_no, company FROM suppliers WHERE suppliers_id = $supplier_id", array());
				if($supplierObj){
					$supplier_row = $supplierObj->fetch(PDO::FETCH_OBJ);
					$supplierName .= $supplier_row->company."</br>";
					$supplierName .= $supplier_row->first_name." ".$supplier_row->last_name."</br>";
					if($supplier_row->email !='')
						$supplierName .= $supplier_row->email."</br>";
					if($supplier_row->contact_no !='')
						$supplierName .= $supplier_row->contact_no."</br>";
				}
				$PO_NumberLabel = $this->db->translate('PO Number');
			}
			if($companylogo !=''){
				$companylogo = "<td width=\"150\" valign=\"top\" class=\"pright15\"><img style=\"max-height:100px;max-width:135px;float:left;\" src=\"$companylogo\" title=\"".$this->db->translate('Logo')."\" /></td>";
			}
			$printingStr .= "<table cellpadding=\"0\" cellspacing=\"1\" width=\"100%\">
							<tr>
								<tr>
								<td align=\"center\">
									<h2>$topTitle</h2>
								</td>
							</tr>
							<tr>
								<td>							
									<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
										<tr>
											$companylogo
											<td align=\"left\" valign=\"top\">                                
												".nl2br($company_info)."
											</td>
											<td width=\"35%\" align=\"right\" rowspan=\"2\">
												<address class=\"mbottom0\">
													<strong>$suplierLabel</strong>
													<p>$supplierName</p>
												</address>
												<h4>$PO_NumberLabel #: p$po_onerow->po_number</h4>
												<p>".date($dateformat, strtotime($po_onerow->po_datetime))."</p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table class=\"border\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
										<tr>
											<th width=\"5%\" align=\"right\">#</th>
											<th align=\"left\">".$this->db->translate('Description')."</th>
											<th width=\"12%\">".$this->db->translate('Ordered QTY')."</th>
											<th width=\"16%\">".$this->db->translate('Received QTY')."</th>
											<th width=\"10%\">".$this->db->translate('Unit Price')."</th>
											<th width=\"10%\">".$this->db->translate('Total')."</th>
										</tr>";
			$po_id = $po_onerow->po_id;
			$return_po = $po_onerow->return_po;
			
			$query = $this->db->query("SELECT * FROM po_items WHERE po_id = $po_id", array());
			$grand_total = $grandOrdQty = $grandRecQty = 0;
			if($query){
				$i=0;
				while($row = $query->fetch(PDO::FETCH_OBJ)){
					$i++;
					$po_items_id = $row->po_items_id;
					$product_id = $row->product_id;
					$item_type = $row->item_type;
					$product_name = $sku = $colour_name = '';
					if($item_type=='one_time'){
						$pcObj =  $this->db->query("SELECT description FROM pos_cart WHERE pos_cart_id = $product_id", array());
						if($pcObj){
							$product_name = $pcObj->fetch(PDO::FETCH_OBJ)->description;
						}
					}
					else{
						$productObj =  $this->db->query("SELECT p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id", array());
						if($productObj){
							$product_row = $productObj->fetch(PDO::FETCH_OBJ);
							
							$product_name = stripslashes($product_row->product_name);
							$manufacturer_name = $product_row->manufacture;
							if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}
							
							$colour_name = $product_row->colour_name;
							if($colour_name !=''){$product_name .= ' '.$colour_name;}
							
							$storage = $product_row->storage;
							if($storage !=''){$product_name .= ' '.$storage;}
							
							$physical_condition_name = $product_row->physical_condition_name;
							if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
							
							$sku = $product_row->sku;
							$product_name .= " ($sku)";
						}											
					}
					$description = $product_name;
					$ordered_qty = $row->ordered_qty;
					$received_qty = $row->received_qty;
					$cost = $row->cost;
					$coststr = $currency.number_format($cost,2);
					if($cost<0){
						$coststr = "-$currency".number_format($cost*(-1),2);
					}
					$total = round($ordered_qty*$cost,2);
					$totalstr = $currency.number_format($total,2);
					if($total <0 ){
						$totalstr = "-$currency".number_format($total*(-1),2);
					}
					$grand_total = $grand_total + $total;
					$grandOrdQty += $ordered_qty;
					$grandRecQty += $received_qty;
					$item_type = $row->item_type;										
					$item_type = $row->item_type;										
					if($item_type=='cellphones'){

						$description = "<div class=\"width100per\">
											<div class=\"col-sm-12 padding0\">$description</div>";
									
						$sqlitem = "SELECT i.item_id, i.item_number, i.carrier_name, pci.return_po_items_id, pci.po_or_return FROM item i, po_cart_item pci WHERE (pci.po_items_id = $po_items_id OR pci.return_po_items_id = $po_items_id) AND i.accounts_id = $accounts_id AND i.item_id = pci.item_id GROUP BY i.item_id ORDER BY i.item_id ASC";
						$itemquery = $this->db->query($sqlitem, array());
						if($itemquery){
							while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
								$item_id = $itemrow->item_id;
								$item_number = $itemrow->item_number;
								
								$carrier_name = $itemrow->carrier_name;
								if($carrier_name !=''){$item_number = $item_number.' '.$carrier_name;}
								
								$return_po_items_id = $itemrow->return_po_items_id;
								$po_or_return = $itemrow->po_or_return;
								if($return_po_items_id>0 && $po_or_return ==0){
									$item_number .= ' <span style="padding: 5px; margin-left:15px;" class="bgblack">'.$this->db->translate('Return').'</span>';
								}
								
								$description .= "<div class=\"col-sm-12  padding0 font15normal ptop10\">
													$item_number
												</div>
												";
							}
						}
						$description .= "</div>";
					}
					
					$bgproperty = '';
					if($ordered_qty>$received_qty){$bgproperty = ' style="background-color:#f2dede"';}
			
					$printingStr .= "<tr>
									<td$bgproperty align=\"right\" valign=\"top\">$i</td>
									<td$bgproperty align=\"left\" valign=\"top\">
										$description
									</td>
									<td$bgproperty align=\"right\" valign=\"top\">$ordered_qty</td>
									<td$bgproperty align=\"right\" valign=\"top\">$received_qty</td>
									<td$bgproperty align=\"right\" valign=\"top\">$coststr</td>
									<td$bgproperty align=\"right\" valign=\"top\">$totalstr</td>
								</tr>";
				}
			}
			else{
				$printingStr .= "<tr><td colspan=\"6\">".$this->db->translate('There is no data found')."</td></tr>";
			}
			
			$subTotalStr = $this->db->translate('Total');
			if($po_onerow->taxes !=0 || $po_onerow->shipping !=0){
				$subTotalStr = $this->db->translate('Subtotal');
			}
			$grand_totalstr = $currency.number_format($grand_total,2);
			if($grand_total<0){
				$grand_totalstr = "-$currency".number_format($grand_total*(-1),2);
			}
			
			$printingStr .= "<tr>
								<td align=\"right\" colspan=\"2\">&nbsp</td>
								<td align=\"right\"><strong>$grandOrdQty</strong></td>
								<td align=\"right\"><strong>$grandRecQty</strong></td>
								<td align=\"right\"><strong>$subTotalStr</strong></td>
								<td align=\"right\"><strong>$grand_totalstr</strong></td>
							</tr>";
										
			if($po_onerow->taxes !=0 || $po_onerow->shipping !=0){
				$taxesTotal = 0;									
				if($po_onerow->taxes !=0){
					if($po_onerow->tax_is_percent==0)
						$taxesTotal = $po_onerow->taxes;
					else
						$taxesTotal = $grand_total*$po_onerow->taxes*0.01;
					
					$taxesTotalstr = $currency.number_format($taxesTotal,2);
					if($taxesTotal<0){
						$taxesTotalstr = "-$currency".number_format($taxesTotal*(-1),2);
					}
					$printingStr .= "<tr>
										<td align=\"right\" colspan=\"5\"><strong>".$this->db->translate('Taxes')."</strong></td>
										<td align=\"right\"><strong>$taxesTotalstr</strong></td>
									</tr>";
				}
				$shippingTotal = 0;									
				if($po_onerow->shipping !=0){
					$shippingTotal = $po_onerow->shipping;
					
					$shippingTotalstr = $currency.number_format($shippingTotal,2);
					if($shippingTotal<0){
						$shippingTotalstr = "-$currency".number_format($shippingTotal*(-1),2);
					}
					$printingStr .= "<tr>
										<td align=\"right\" colspan=\"5\"><strong>".$this->db->translate('Shipping Cost')."</strong></td>
										<td align=\"right\"><strong>$shippingTotalstr</strong></td>
									</tr>";
				}
				
				$grand_total = $grand_total+$taxesTotal+$shippingTotal;
				$grand_totalstr = $currency.number_format($grand_total,2);
				if($grand_total<0){
					$grand_totalstr = "-$currency".number_format($grand_total*(-1),2);
				}
				
				$printingStr .= "<tr>
									<td align=\"right\" colspan=\"5\">".$this->db->translate('Grand Total')."</strong></td>
									<td align=\"right\"><strong>$grand_totalstr</strong></td>
								</tr>";
			}
			$printingStr .= "</table>
								</td>
							</tr>                
							<tr>
								<td>".$this->getPublicNotes('po', $po_id)."</td>
							</tr>                
							<tr>
								<td align=\"center\">
									<p><br />".nl2br(stripslashes($po_message))."</p>                        
								</td>
							</tr>
						</table>
					</body>
				</html>";
				
			return $printingStr;
		}
		else{
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			$jsonResponse['title'] = $title;
			
			$supplierName = array();
			$supplier_id = $po_onerow->supplier_id;
			if($fromPage == 'Inventory_Transfer'){
				$topTitle = $this->db->translate('Inventory Transfer');
				if($po_onerow->transfer==1){$toOrFrom = $this->db->translate('To');}
				else{$toOrFrom = $this->db->translate('from');}
				$suplierLabel = $this->db->translate('Transfer')." $toOrFrom ".$this->db->translate('Info');
				$supplierObj = $this->db->query("SELECT accounts.company_subdomain, user.user_first_name, user.user_last_name, user.user_email, accounts.company_phone_no FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND user.is_admin = 1 AND accounts.accounts_id = $supplier_id", array());
				if($supplierObj){
					$supplierrow = $supplierObj->fetch(PDO::FETCH_OBJ);
					$supplierName[] = $supplierrow->company_subdomain;
					$supplierName[] = trim(stripslashes($supplierrow->user_first_name." ".$supplierrow->user_last_name));
					if($supplierrow->user_email !='')
						$supplierName[] = $supplierrow->user_email;
					if($supplierrow->company_phone_no !='')
						$supplierName[] = $supplierrow->company_phone_no;
				}
				$PO_NumberLabel = $this->db->translate('Transfer');
			}
			else{
				$topTitle = $this->db->translate('Purchase Orders');
				$suplierLabel = $this->db->translate('Supplier Info');
				$supplierObj = $this->db->query("SELECT first_name, last_name, email, contact_no, company FROM suppliers WHERE suppliers_id = $supplier_id", array());
				if($supplierObj){
					$supplier_row = $supplierObj->fetch(PDO::FETCH_OBJ);
					$supplierName[] = $supplier_row->company;
					$supplierName[] = trim(stripslashes($supplier_row->first_name." ".$supplier_row->last_name));
					if($supplier_row->email !='')
						$supplierName[] = $supplier_row->email;
					if($supplier_row->contact_no !='')
						$supplierName[] = $supplier_row->contact_no;
				}
				$PO_NumberLabel = $this->db->translate('PO Number');
			}
			
			$jsonResponse['topTitle'] = $topTitle;
			$jsonResponse['companylogo'] = $companylogo;
			$jsonResponse['company_info'] = nl2br(stripslashes($company_info));			
			$jsonResponse['suplierLabel'] = $suplierLabel;
			$jsonResponse['supplierName'] = $supplierName;
			$jsonResponse['PO_NumberLabel'] = $PO_NumberLabel;
			$jsonResponse['po_number'] = intval($po_onerow->po_number);
			$jsonResponse['tax_is_percent'] = intval($po_onerow->tax_is_percent);
			$jsonResponse['po_datetime'] = $po_onerow->po_datetime;
			
			$po_id = $po_onerow->po_id;
			$return_po = $po_onerow->return_po;
			
			$cartData = array();
			$query = $this->db->query("SELECT * FROM po_items WHERE po_id = $po_id", array());
			$grand_total = 0;
			if($query){
				$i=0;
				while($row = $query->fetch(PDO::FETCH_OBJ)){
					$i++;
					$po_items_id = $row->po_items_id;
					$product_id = $row->product_id;
					$item_type = $row->item_type;
					$product_name = $sku = $colour_name = '';
					if($item_type=='one_time'){
						$pcObj =  $this->db->query("SELECT description FROM pos_cart WHERE pos_cart_id = $product_id", array());
						if($pcObj){
							$product_name = $pcObj->fetch(PDO::FETCH_OBJ)->description;
						}
					}
					else{
						$productObj =  $this->db->query("SELECT p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id = $prod_cat_man", array());
						if($productObj){
							$product_row = $productObj->fetch(PDO::FETCH_OBJ);
							
							$product_name = stripslashes($product_row->product_name);
							$manufacturer_name = $product_row->manufacture;
							if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}
							
							$colour_name = $product_row->colour_name;
							if($colour_name !=''){$product_name .= ' '.$colour_name;}
							
							$storage = $product_row->storage;
							if($storage !=''){$product_name .= ' '.$storage;}
							
							$physical_condition_name = $product_row->physical_condition_name;
							if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
							
							$sku = $product_row->sku;
							$product_name .= " ($sku)";
						}											
					}
					$description = $product_name;
					$ordered_qty = floatval($row->ordered_qty);
					$received_qty = floatval($row->received_qty);
					$cost = round($row->cost,2);
					$total = round($ordered_qty*$cost,2);
					$grand_total = $grand_total + $total;
					$item_type = $row->item_type;
					$item_numberInfo = array();
					if($item_type=='cellphones'){
						$sqlitem = "SELECT i.item_id, i.item_number, i.carrier_name, pci.return_po_items_id, pci.po_or_return FROM item i, po_cart_item pci WHERE (pci.po_items_id = $po_items_id OR pci.return_po_items_id = $po_items_id) AND i.accounts_id = $accounts_id AND i.item_id = pci.item_id GROUP BY i.item_id ORDER BY i.item_id ASC";
						$itemquery = $this->db->query($sqlitem, array());
						if($itemquery){
							while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
								$item_id = $itemrow->item_id;
								$item_number = $itemrow->item_number;
								
								$carrier_name = $itemrow->carrier_name;
								if($carrier_name !=''){$item_number = $item_number.' '.$carrier_name;}
								
								$return_po_items_id = $itemrow->return_po_items_id;
								$po_or_return = $itemrow->po_or_return;

								$item_numberInfo[] = array('return_po_items_id'=>$return_po_items_id, 'po_or_return'=>$po_or_return, 'item_number'=>$item_number);
							}
						}
					}
					
					$bgproperty = '';
					if($ordered_qty>$received_qty){$bgproperty = '#f2dede';}
					
					$cartData[] = array('bgproperty'=>$bgproperty, 'item_type'=>$item_type, 'description'=>$description, 'ordered_qty'=>$ordered_qty, 'received_qty'=>$received_qty, 'cost'=>$cost, 'total'=>$total, 'item_numberInfo'=>$item_numberInfo);
				}
			}
			$jsonResponse['cartData'] = $cartData;			
			$jsonResponse['taxes'] = round($po_onerow->taxes,2);
			$jsonResponse['shipping'] = round($po_onerow->shipping,2);
			$jsonResponse['getNotes'] =$this->getPublicNotes('po', $po_id, 1);
			$jsonResponse['po_message'] = nl2br(trim(stripslashes((string) $po_message)));
				
			return $jsonResponse;
		}		
	}
	
	function stockTakeInfo($stock_take_id, $sview2_type, $keyword_search){
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$domainName = OUR_DOMAINNAME;
		$title = $this->db->translate('Stock Take Information');
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['title'] = $title;
		
		$onePicture = "";
		$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
		$pics = glob($filePath."*.jpg");
		if($pics){		
			foreach($pics as $onePicture){
				$onePicture = "//$domainName".str_replace('./', '/', $onePicture);
			}				
		}
		$jsonResponse['onePicture'] = $onePicture;
		$company_info = '';
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;	
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('company_info', $value)){
					$company_info = nl2br($value['company_info']);
				}
			}
		}
		$jsonResponse['company_info'] = stripslashes($company_info);

		$filterSql = "";
		$bindData = array('stock_take_id'=>$stock_take_id);
		if(!empty($keyword_search)){
			$catIds = array();
			$tableObj =  $this->db->query("SELECT category_id FROM category WHERE accounts_id = $prod_cat_man AND category_name LIKE CONCAT('%', :keyword_search, '%')", array('keyword_search'=>$keyword_search));
			if($tableObj){
				while($sOneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$catIds[] = $sOneRow->category_id;
				}
			}
			if(!empty($catIds)){
				$filterSql .= " AND p.category_id IN (".implode(', ', $catIds).")";
			}
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		if($sview2_type !='All'){
			if($sview2_type==1){
				$filterSql .= " AND sti.inventory_count !=-1";
			}
			else{
				$filterSql .= " AND sti.inventory_count !=-1 AND sti.inventory_current != sti.inventory_count";
			}
		}
		
		$sql = "SELECT ST.*, manufacturer.name AS manufacture FROM stock_take ST LEFT JOIN manufacturer ON (ST.manufacturer_id = manufacturer.manufacturer_id) WHERE ST.stock_take_id = :stock_take_id AND ST.accounts_id = $accounts_id";
		$stock_takeObj = $this->db->query($sql, array('stock_take_id'=>$stock_take_id));
		if($stock_takeObj){
			$stock_take_onerow = $stock_takeObj->fetch(PDO::FETCH_OBJ);
			$stock_take_id = $stock_take_onerow->stock_take_id;
			$category_id = $stock_take_onerow->category_id;
			$status = $stock_take_onerow->status;
			
			$categoryname = '';
			$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $category_id", array());
			if($categoryObj){
				$category_row = $categoryObj->fetch(PDO::FETCH_OBJ);
				$categoryname = stripslashes(trim((string) $category_row->category_name));
			}
			$jsonResponse['categoryname'] = $categoryname;
			$manufacture = stripslashes(trim((string) $stock_take_onerow->manufacture));
			
			$jsonResponse['reference'] = stripslashes(trim((string) $stock_take_onerow->reference));
			$jsonResponse['manufacture'] = $manufacture;
			$jsonResponse['date_completed'] = $stock_take_onerow->date_completed;
			
			$cartData = array();
			$sqlquery = "SELECT sti.*, p.category_id, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku, p.product_type FROM stock_take_items sti, inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE sti.stock_take_id = :stock_take_id AND i.accounts_id = $accounts_id $filterSql AND sti.product_id = p.product_id AND p.product_id = i.product_id ORDER BY manufacture ASC, p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC";
			$STIData = $this->db->querypagination($sqlquery, $bindData);
			if($STIData){
				$i=0;
				$categoryIds = array(0=>'');
				foreach($STIData as $row){
					$categoryIds[$row['category_id']] = '';
				}
				
				if(!empty($categoryIds)){
					$tableObj =  $this->db->query("SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($categoryIds)).")", array());
					if($tableObj){
						while($sOneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$categoryIds[$sOneRow->category_id] = stripslashes($sOneRow->category_name);
						}
					}
				}
				
				foreach($STIData as $row){
					$i++;
					$stock_take_items_id = $row['stock_take_items_id'];
					
					$product_type = $row['product_type'];
					$categoryId = $row['category_id'];
					$categoryName = $categoryIds[$categoryId]??'';
					$manufacture = stripslashes((string) $row['manufacture']);
					$productName = stripslashes($row['product_name']);
					if(!empty($row['colour_name'])){$productName .= ' '.$row['colour_name'];}
					if(!empty($row['storage'])){$productName .= ' '.$row['storage'];}
					if(!empty($row['physical_condition_name'])){$productName .= ' '.$row['physical_condition_name'];}
					
					$sku = $row['sku'];				
					$product_id = $row['product_id'];
					$regular_price = round($row['regular_price'],2);
					$ave_cost = round($row['ave_cost'],2);
					$inventory_current = floatval($row['inventory_current']);
					$inventory_count = floatval($row['inventory_count']);
					
					$note = stripslashes(trim((string) $row['note']));
					
					$cartData[] = array('manufacture'=>$manufacture, 'categoryName'=>$categoryName, 'productName'=>$productName, 'sku'=>$sku, 'inventory_current'=>$inventory_current, 'inventory_count'=>$inventory_count, 'ave_cost'=>$ave_cost, 'regular_price'=>$regular_price, 'note'=>$note);
					
				}
				$jsonResponse['cartData'] = $cartData;
				$jsonResponse['note'] = $note;
				
			}
			$jsonResponse['noteData'] = $this->getPublicNotes('stock_take', $stock_take_id, 1);
		}
		
		
		return  $jsonResponse;
	}
	
	public function endOfDayInfo($eod_date, $printType, $drawer=''){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$Common = new Common($this->db);
		$multiple_cash_drawers = 0;
		$cdArray = array();
		$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
		if(!empty($cdData)){
			extract($cdData);
			$cdArray = explode('||',$cash_drawers);
		}
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['company_name'] = trim((string) $_SESSION["company_name"]);
		$jsonResponse['title'] = $this->db->translate('End of Day Report');
		
		if($printType == 'small'){
			$left_margin = $right_margin = 15;
			$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'small_print'", array());
			if($varObj){
				$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$value = unserialize($value);
					extract($value);
				}
			}
			$jsonResponse['left_margin'] = round($left_margin,2);
			$jsonResponse['right_margin'] = round($right_margin,2);
			$jsonResponse['drawer'] = $drawer;
			$jsonResponse['todayDate'] = date('Y-m-d');
			$jsonResponse['eod_date'] = $eod_date;
							
			$eod_date = date('Y-m-d',strtotime($eod_date));
			$startdate = $eod_date.' 00:00:00';
			$enddate = $eod_date.' 23:59:59';
			
			$paymentmethodarray = $paymentmethodvaluearray = array();
			$end_of_day_id = 0;
			$comments = '';
			$bindData = array('eod_date'=>$eod_date);
			$sql = "SELECT end_of_day_id, payment_method, counted, comments FROM end_of_day WHERE accounts_id = $accounts_id AND eod_date = :eod_date";
			if(!empty($cdArray) && $multiple_cash_drawers>0){
				$sql .= " AND drawer = :drawer";
				$bindData['drawer'] = $drawer;
			}
			$sql .= " ORDER BY end_of_day_id ASC";
			$queryObj = $this->db->query($sql, $bindData);
			if($queryObj){
				while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$end_of_day_id = $onerow->end_of_day_id;
					$payment_method = $onerow->payment_method;
					$counted = round($onerow->counted,2);
					$comments = stripslashes(trim((string) $onerow->comments));
					$paymentmethodarray[] = $payment_method;
					$paymentmethodvaluearray[$payment_method] = round($counted,2);
				}
			}			
			
			$calculatedCash = 0.00;
			$strextra = "FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id";
			$bindData = array();
			if(!empty($cdArray) && $multiple_cash_drawers>0){
				$strextra .= " AND pos_payment.drawer = :drawer";
				$bindData['drawer'] = $drawer;
			}
			
			if($startdate !='' && $enddate !=''){
				$strextra .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}
			$strextra .= " AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id GROUP BY pos_payment.payment_method";

			$sqlquery = "SELECT pos_payment.payment_method, SUM(pos_payment.payment_amount) AS total_payment_amount $strextra";
			$queryObj = $this->db->query($sqlquery, $bindData);
			$posPaymentData = array();
			if($queryObj){
				while($onegrouprow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$payment_method = $onegrouprow->payment_method;
					$calculated = round($onegrouprow->total_payment_amount,2);
					$counted = 0.00;
					if($paymentmethodarray !='' && in_array($payment_method, $paymentmethodarray)){
						$counted = $paymentmethodvaluearray[$payment_method];
					}
					if($payment_method=='Cash'){
						$calculatedCash = $calculated;						
					}					
					else{
						
						$posPaymentData[] = array('payment_method'=>$payment_method, 'calculated'=>round($calculated,2), 'counted'=>round($counted,2));
					}
				}
			}
			$jsonResponse['posPaymentData'] = $posPaymentData;

			$starting_cash = $paymentmethodvaluearray['Starting Balance']??0;
			
			$petty_cash = 0;
			$bindData = array('eod_date'=>$eod_date);
			$sqlPettyCash = "SELECT SUM(add_sub*amount) AS totalPettyCash FROM petty_cash WHERE accounts_id = $accounts_id AND eod_date = :eod_date";
			if(!empty($cdArray) && $multiple_cash_drawers>0){
				$sqlPettyCash .= " AND drawer = :drawer";
				$bindData['drawer'] = $drawer;
			}
			$sqlPettyCash .= " GROUP BY eod_date";

			$queryPettyCashObj = $this->db->query($sqlPettyCash, $bindData);			
			if($queryPettyCashObj){
				$petty_cash = $queryPettyCashObj->fetch(PDO::FETCH_OBJ)->totalPettyCash;
			}
			
			$counted_cash = $paymentmethodvaluearray['Cash']??0;

			$jsonResponse['counted_cash'] = round($counted_cash,2);
			$jsonResponse['starting_cash'] = round($starting_cash,2);
			$jsonResponse['petty_cash'] = round($petty_cash,2);
			$jsonResponse['calculatedCash'] = round($calculatedCash,2);

			$petty_cashData = array();
			$bindData = array('eod_date'=>$eod_date);
			$sql = "SELECT * FROM petty_cash WHERE accounts_id = $accounts_id AND petty_cash_publish = 1 AND eod_date = :eod_date";
			if(!empty($cdArray) && $multiple_cash_drawers>0){
				$sql .= " AND drawer = :drawer";
				$bindData['drawer'] = $drawer;
			}
			$sql .= " ORDER BY petty_cash_id ASC";
			$queryObj = $this->db->query($sql, $bindData);                    
			if($queryObj){
				while($onegrouprow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$petty_cash_id = $onegrouprow->petty_cash_id;
					$add_sub = $onegrouprow->add_sub;
					$type = 'Subtraction';
					if($add_sub>0){
						$type = 'Addition';
					}
					$amount = $onegrouprow->amount*$add_sub;
					$reason = stripslashes(trim((string) $onegrouprow->reason));
					
					$petty_cashData[] = array('reason'=>$reason, 'type'=>$type, 'amount'=>round($amount,2));
				}
			}
			
			$jsonResponse['petty_cashData'] = $petty_cashData;
			$jsonResponse['comments'] = $comments;
		}
		else{
			$jsonResponse['drawer'] = $drawer;
			$jsonResponse['todayDate'] = date('Y-m-d');
			$jsonResponse['eod_date'] = $eod_date;
			
			$eod_date = date('Y-m-d',strtotime($eod_date));
			$startdate = $eod_date.' 00:00:00';
			$enddate = $eod_date.' 23:59:59';
			$accounts_id = $_SESSION["accounts_id"]??0;
			$currency = $_SESSION["currency"]??'৳';
			
			$paymentmethodarray = $paymentmethodvaluearray = array();
			$end_of_day_id = 0;
			$comments = '';
			$bindData = array('eod_date'=>$eod_date);
			$sql = "SELECT end_of_day_id, payment_method, counted, comments FROM end_of_day WHERE accounts_id = $accounts_id AND eod_date = :eod_date";
			if(!empty($cdArray) && $multiple_cash_drawers>0){
				$sql .= " AND drawer = :drawer";
				$bindData['drawer'] = $drawer;
			}
			$sql .= " ORDER BY end_of_day_id ASC";
			$queryObj = $this->db->query($sql, $bindData);			
			if($queryObj){
				while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$end_of_day_id = $onerow->end_of_day_id;
					$payment_method = $onerow->payment_method;
					$counted = $onerow->counted;
					$comments = stripslashes(trim((string) $onerow->comments));
					$paymentmethodarray[] = $payment_method;
					$paymentmethodvaluearray[$payment_method] = round($counted,2);
				}
			}			
			
			$calculatedCash = 0.00;
			$strextra = "FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id";
			$bindData = array();
			if(!empty($cdArray) && $multiple_cash_drawers>0){
				$strextra .= " AND pos_payment.drawer = :drawer";
				$bindData['drawer'] = $drawer;
			}
			
			if($startdate !='' && $enddate !=''){
				$strextra .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}
			$strextra .= " AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id GROUP BY pos_payment.payment_method";
			$sqlquery = "SELECT pos_payment.payment_method, SUM(pos_payment.payment_amount) AS total_payment_amount $strextra";
			$queryObj = $this->db->query($sqlquery, $bindData);
			$posPaymentData = array();
			if($queryObj){
				while($onegrouprow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$payment_method = $onegrouprow->payment_method;
					$calculated = round($onegrouprow->total_payment_amount,2);
					$counted = 0.00;
					if($paymentmethodarray !='' && in_array($payment_method, $paymentmethodarray)){
						$counted = $paymentmethodvaluearray[$payment_method];
					}
					$difference = $counted-$calculated;
								
					if($payment_method=='Cash'){
						$calculatedCash = $calculated;						
					}					
					else{
						$posPaymentData[] = array('payment_method'=>$payment_method, 'calculated'=>round($calculated,2), 'counted'=>round($counted,2));
					}
				}
			}
			$jsonResponse['posPaymentData'] = $posPaymentData;
			
			$starting_cash = $paymentmethodvaluearray['Starting Balance']??0;
			
			$petty_cash = 0;
			$bindData = array('eod_date'=>$eod_date);
			$sqlPettyCash = "SELECT SUM(add_sub*amount) AS totalPettyCash FROM petty_cash WHERE accounts_id = $accounts_id AND eod_date = :eod_date";
			if(!empty($cdArray) && $multiple_cash_drawers>0){
				$sqlPettyCash .= " AND drawer = :drawer";
				$bindData['drawer'] = $drawer;
			}
			$sqlPettyCash .= " GROUP BY eod_date";
			$queryPettyCashObj = $this->db->query($sqlPettyCash, $bindData);			
			if($queryPettyCashObj){
				$petty_cash = round($queryPettyCashObj->fetch(PDO::FETCH_OBJ)->totalPettyCash,2);
			}
			
			$counted_cash = $paymentmethodvaluearray['Cash']??0;

			$jsonResponse['counted_cash'] = round($counted_cash,2);
			$jsonResponse['starting_cash'] = round($starting_cash,2);
			$jsonResponse['petty_cash'] = round($petty_cash,2);
			$jsonResponse['calculatedCash'] = round($calculatedCash,2);
			
			$bindData = array('eod_date'=>$eod_date);
			$sql = "SELECT * FROM petty_cash WHERE accounts_id = $accounts_id AND eod_date = :eod_date";
			if(!empty($cdArray) && $multiple_cash_drawers>0){
				$sql .= " AND drawer = :drawer";
				$bindData['drawer'] = $drawer;
			}
			$sql .= " AND petty_cash_publish = 1 ORDER BY petty_cash_id ASC";
			$queryObj = $this->db->query($sql, $bindData);
			$petty_cashData = array();
			if($queryObj){
				while($onegrouprow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$petty_cash_id = $onegrouprow->petty_cash_id;
					$add_sub = $onegrouprow->add_sub;
					$type = 'Subtraction';
					if($add_sub>0){
						$type = 'Addition';
					}
					$amount = $onegrouprow->amount*$add_sub;
					$reason = stripslashes(trim((string) $onegrouprow->reason));					
					$petty_cashData[] = array('reason'=>$reason, 'type'=>$type, 'amount'=>round($amount,2));
				}
			}
			
			$jsonResponse['petty_cashData'] = $petty_cashData;
			$jsonResponse['comments'] = $comments;
			
		}
	
		return $jsonResponse;
	}
	
	public function endOfDayLists($eod_date){
		$sortingType = 'dateTime ASC';
		$accounts_id = $_SESSION["accounts_id"]??0;	
		$currency = $_SESSION["currency"]??'৳';
		$startdate = $enddate = '';
		if($eod_date !='' && $eod_date !='null'){
			$filterdatearray = explode(' - ', $eod_date);
			if(is_array($filterdatearray) && count($filterdatearray)>1){
				$startdate = $filterdatearray[0];
				$enddate = $filterdatearray[1];
				$startdate = date('Y-m-d', strtotime($startdate));
				$enddate = date('Y-m-d', strtotime($enddate));
			}
		}
		else{
			$startdate = date('Y-m-d');
			$enddate = date('Y-m-d');
		}
		$startdate .= ' 00:00:00';
		$enddate .= ' 23:59:59';		
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['title'] = $this->db->translate('End of Day Report');
		$jsonResponse['todayDate'] = date('Y-m-d');
		$jsonResponse['startdate'] = date('Y-m-d', strtotime($startdate));
		$jsonResponse['enddate'] = date('Y-m-d', strtotime($enddate));
		$jsonResponse['companyName'] = $_SESSION["company_name"];
		
		$startdate2 = date('Y-m-d',strtotime($startdate));
		$enddate2 = date('Y-m-d',strtotime($enddate));
		
		$filterSql = $filterSql2 = "";
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			$filterSql .= " AND (pos_payment.payment_datetime BETWEEN :startdate AND :enddate)";
			$filterSql2 .= " AND (eod_date BETWEEN :startdate2 AND :enddate2)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
			$bindData['startdate2'] = $startdate2;
			$bindData['enddate2'] = $enddate2;
		}
		
		$sqlquery ="SELECT eod_date AS dateTime, drawer FROM petty_cash WHERE accounts_id = $accounts_id $filterSql2 AND petty_cash_publish = 1";
		$sqlquery .= " UNION ALL ";
		$sqlquery .=" SELECT SUBSTRING(pos_payment.payment_datetime, 1, 10) AS dateTime, pos_payment.drawer AS drawer FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id $filterSql AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id";
		$sqlquery .= " GROUP BY dateTime, drawer ORDER BY $sortingType";

		$query = $this->db->querypagination($sqlquery, $bindData);
		$endOfDayData = array();
		if($query){
			foreach($query as $oneRow){
				$endOfDayData[$oneRow['dateTime']][$oneRow['drawer']] = '';
			}
		}
		
		$posPaymentData = array();
		$tabledata = array();
		if(empty($endOfDayData)){
			$jsonResponse['posPaymentData'] = $posPaymentData;
			return $jsonResponse;
		}

		foreach($endOfDayData as $payment_datetime=>$drawerInfo){
			foreach($drawerInfo as $drawer=>$drawerVal){
				$i=0;

				$onestartdate = $payment_datetime.' 00:00:00';
				$oneenddate = $payment_datetime.' 23:59:59';
				$totalPettyCash = 0;
				$sqlPettyCash = "SELECT SUM(add_sub*amount) AS totalPettyCash FROM petty_cash WHERE accounts_id = $accounts_id AND eod_date = '$payment_datetime' AND drawer='$drawer' GROUP BY eod_date";
				$queryPettyCashObj = $this->db->query($sqlPettyCash, array());
				if($queryPettyCashObj){
					$totalPettyCash = $queryPettyCashObj->fetch(PDO::FETCH_OBJ)->totalPettyCash;
				}
				
				$eodpaymentmethodvaluearray = array();
				$eod_date = $payment_datetime;
				$comments = $newcomments = '';
				$end_of_day_id = 0;
				$eodsql = "SELECT end_of_day_id, eod_date, payment_method, counted, comments FROM end_of_day WHERE accounts_id = $accounts_id AND eod_date = '$eod_date' AND drawer = '$drawer' ORDER BY end_of_day_id ASC";
				$eodquery = $this->db->query($eodsql, array());			
				if($eodquery){
					while($eodonerow = $eodquery->fetch(PDO::FETCH_OBJ)){
						$eod_date = $eodonerow->eod_date;
						$payment_method = $eodonerow->payment_method;
						if($payment_method=='Cash'){
							$eodpaymentmethodvaluearray[$payment_method] = $eodonerow->counted;
							$end_of_day_id = $eodonerow->end_of_day_id;
						}
						else{
							$eodpaymentmethodvaluearray[$payment_method] = round($eodonerow->counted,2);
						}
						if($eodonerow->comments !=''){
							$newcomments = stripslashes(trim((string) $eodonerow->comments));
						}
					}
				}

				$StartingBalance = floatval($eodpaymentmethodvaluearray['Starting Balance']??0.00);
				$Cash = floatval($eodpaymentmethodvaluearray['Cash']??0.00);
				$eodpaymentmethodvaluearray['Cash'] = $Cash-$StartingBalance;
				
				$subPosPaymentData = array();
				$possql = "SELECT pos_payment.payment_method, SUM(pos_payment.payment_amount) AS total_payment_amount 
							FROM pos, pos_payment 
							WHERE pos.accounts_id = $accounts_id AND pos_payment.drawer = '$drawer' AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id 
								AND (pos_payment.payment_datetime BETWEEN :onestartdate AND :oneenddate) 
								GROUP BY pos_payment.payment_method";
				$posquery = $this->db->querypagination($possql, array('onestartdate'=>$onestartdate, 'oneenddate'=>$oneenddate));                    
				if($posquery){					
					foreach($posquery as $posonerow){
						$i++;
						$payment_method = $posonerow['payment_method'];
						
						$calculated = round($posonerow['total_payment_amount'],2);						
						$counted = $eodpaymentmethodvaluearray[$payment_method]??0.00;	
												
						if($i==1){
							if($end_of_day_id>0){
								if($newcomments !=''){
									$border = '';
									if($comments !=''){$border = '<hr style="margin-top:10px;margin-bottom: 10px;"/>';}
									$comments .= "<div style=\"display:block\">$border$newcomments</div>";
								}
							}							
						}

						$subPosPaymentData[] = array('i'=>$i, 'end_of_day_id'=>intval($end_of_day_id), 'payment_datetime'=>$payment_datetime, 'payment_method'=>$payment_method, 'calculated'=>round($calculated,2), 'counted'=>round($counted,2), 'comments'=>$comments, 'drawer'=>$drawer);
							
					}
				}

				if($totalPettyCash !=0){
					$counted = 0;
					if(empty($subPosPaymentData) && array_key_exists('Cash', $eodpaymentmethodvaluearray)){
						$counted = $eodpaymentmethodvaluearray['Cash']??0.00;
					}
					$i++;
					$subPosPaymentData[] = array('i'=>$i, 'end_of_day_id'=>intval($end_of_day_id), 'payment_datetime'=>$payment_datetime, 'payment_method'=>'Petty Cash', 'calculated'=>round($totalPettyCash,2), 'counted'=>round($counted,2), 'comments'=>$comments, 'drawer'=>$drawer);
				}
				
				if(!empty($subPosPaymentData)){						
					$posPaymentData[] = array('subPosPaymentData'=>$subPosPaymentData);
				}
			}
			
			$jsonResponse['posPaymentData'] = $posPaymentData;
		}
		return $jsonResponse;
	}

	public function showCustMarkData($offers_email){
		$returnVal = '';
		if($offers_email !='' && $offers_email>0){
			$marketing_data = '';
			$accounts_id = $_SESSION["accounts_id"]??0;
			$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'marketing_data'", array());
			if($varObj){
				$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$value = unserialize($value);
					extract($value);
					$returnVal = nl2br(stripslashes(trim((string) $marketing_data)));
				}
			}
		}
		return $returnVal;
	}
		
	public function getPublicSmallNotes($note_for, $table_id){
		$noteData = array();
		if($note_for !='' && $table_id>0){
			$accounts_id = $_SESSION["accounts_id"]??0;
			$user_id = $_SESSION["user_id"]??0;
			
			$sqlquery = "SELECT n.note, n.created_on AS created_on, n.user_id, 'notes' AS fromTable FROM notes n WHERE n.accounts_id = $accounts_id AND n.table_id = $table_id AND n.note_for = '$note_for' AND n.publics>0 UNION ALL SELECT ds.note, ds.created_on AS created_on, ds.user_id, 'digital_signature' AS fromTable FROM digital_signature ds WHERE ds.accounts_id = $accounts_id AND ds.table_id = $table_id AND ds.for_table = '$note_for' ORDER BY created_on DESC";		
			$query = $this->db->query($sqlquery, array());
			$i = 1;								
			if($query){
				$accounts_idname = '';
				$userObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $user_id", array());
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
							$userObj2 = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $createduser_id", array());
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
						$user_name .= $this->db->translate('System');
					}

					$noteData[] = array('fromTable'=>$onerow->fromTable, 'note'=>$note, 'created_on'=>$onerow->created_on, 'user_name'=>$user_name);
				}
			}
		}
		return $noteData;
	}
	
	public function getPublicNotes($note_for, $table_id, $isArray = 0){
		$str = '';
		$noteData = array();
		if($note_for !='' && $table_id>0){
			$accounts_id = $_SESSION["accounts_id"]??0;
			$user_id = $_SESSION["user_id"]??0;
			$dateformat = $_SESSION["dateformat"]??'m/d/Y';
			$timeformat = $_SESSION["timeformat"]??'12 hour';
			
			$sqlquery = "SELECT n.note, n.created_on AS created_on, n.user_id, 'notes' AS fromTable FROM notes n WHERE n.accounts_id = $accounts_id AND n.table_id = $table_id AND n.note_for = '$note_for' AND n.publics>0 UNION ALL SELECT ds.note, ds.created_on AS created_on, ds.user_id, 'digital_signature' AS fromTable FROM digital_signature ds WHERE ds.accounts_id = $accounts_id AND ds.table_id = $table_id AND ds.for_table = '$note_for' ORDER BY created_on DESC";		
			$query = $this->db->query($sqlquery, array());
			$i = 1;								
			
			if($query){
				$str .= '<div style="clear: both;margin:15px 0;width:99.80%;text-align:left; float:left;">
							<div style="background: linear-gradient(to bottom, #FAFAFA 0%, #E9E9E9 100%) repeat-x scroll 0 0 #E9E9E9;border: 1px solid #D5D5D5;border-top-left-radius: 4px;border-top-right-radius: 4px;height: 40px;line-height: 40px;padding-left:15px;">
								<h3 style="font-size: 14px !important;margin:0; padding:0;color: #555555;display: inline-block;line-height: 18px;text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);"> '.$this->db->translate('Note History').' </h3>
							</div>
							<div style="background:#fff;border: 1px solid #D5D5D5; float:left; width:100%;border-radius: 0px 0px 5px 5px;padding:20px 0px; margin-top: -1px;">
								<table width="99%" cellpadding="5" cellspacing="0">';
				$accounts_idname = '';
				$userObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $user_id", array());
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
							$userObj2 = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $createduser_id", array());
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
						$user_name .= $this->db->translate('System');
					}

					if($isArray>0){
						$noteData[] = array('fromTable'=>$onerow->fromTable, 'note'=>$note, 'created_on'=>$onerow->created_on, 'user_name'=>$user_name);
					}
					else{
						if($timeformat=='24 hour'){$created_on =  date($dateformat.' H:i', strtotime($onerow->created_on));}
						else{$created_on =  date($dateformat.' g:i a', strtotime($onerow->created_on));}
						if($onerow->fromTable=='digital_signature') {
							$note = '<div class="clear"></div><img style="max-width:100%;" alt="'.$this->db->translate('Signature').'" src="'.$note.'">';
						}
						$border = '';
						if($i>1){$border = '<hr />';}
						$str .= "<tr>
								<td style=\"padding-top: 5px;word-wrap: break-word;font-size: 14px;line-height: 21px; padding:0 20px;\">
									$border
									<strong>$created_on  By $user_name</strong><br />
									$note
								</td>
							</tr>";							
						$i++;					
					}
				}
				
				$str .= '</table>
						</div>
					</div>';
			}
		}
		if($isArray>0){
			return $noteData;
		}
		else{
			return $str;
		}
	}
	
	public function getPublicFormData($form_for, $table_id, $form_public, $viewfor = 'lr', $forms_data_id=0, $isArray = 0){
			
		$str = '';
		$returnData = array();
		if($form_for !='' && $table_id>0){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			$dateformat = $_SESSION["dateformat"]??'m/d/Y';
			$domainName = OUR_DOMAINNAME;
		
			$fdIdSql = '';
			if($forms_data_id>0){
				$fdIdSql = " AND fd.forms_data_id = $forms_data_id";
			}
			$publicSql = '';
			if($form_public>0){
				$publicSql = " AND fd.form_public = $form_public";
			}
			$sqlquery = "SELECT fd.form_name, fd.last_updated, fd.form_data, fs.form_definitions, fd.accounts_id FROM forms_data fd, forms fs WHERE fd.table_id = $table_id$fdIdSql AND fd.accounts_id = $accounts_id AND fs.form_for = '$form_for' $publicSql AND fd.last_updated NOT IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') AND fd.forms_id = fs.forms_id ORDER BY fd.created_on DESC";		
			$query = $this->db->query($sqlquery, array());			
			if($query){
				$fs15 = '15px';
				if($viewfor=='sm'){$fs15 = '12px';}
				
				$str .= '<div style="clear: both;margin:15px 0;width:100%; text-align:left; float:left;">
							<div style="background: linear-gradient(to bottom, #FAFAFA 0%, #E9E9E9 100%) repeat-x scroll 0 0 #E9E9E9;border: 1px solid #D5D5D5;border-top-left-radius: 4px;border-top-right-radius: 4px;height: 40px;line-height: 40px;padding-left:15px;">
								<h3 style="font-size:11px !important;margin:0; padding:0;color: #555555;display: inline-block;line-height: 18px;text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);"> '.$this->db->translate('Name of').' '.ucfirst($form_for).' '.$this->db->translate('Form').' </h3>
							</div>
							<div style="background:#fff;border: 1px solid #D5D5D5; float:left; width:100%;border-radius: 0px 0px 5px 5px;padding:20px 0px; margin-top: -1px;">
								<table width="99%" cellpadding="5" cellspacing="0">';
				$i = 0;			
				while($onerow = $query->fetch(PDO::FETCH_OBJ)){
					$i++;
					$form_name = trim((string) stripslashes($onerow->form_name));
					$form_name = " <h3 style=\"margin:0 0 5px;font-size:$fs15 !important;\">$form_name <span style=\"font-weight:normal\">".date($dateformat, strtotime($onerow->last_updated)).'</span></h3>';
					
					$form_definitions = $onerow->form_definitions;
					if(!empty($form_definitions)){
						$form_definitions = unserialize($form_definitions);
					}
					$form_data = $onerow->form_data;
					if(!empty($form_data)){
						$form_data = unserialize($form_data);
					}
					$formStr = '';
					$formsData = array();
					if(!empty($form_definitions)){
						foreach($form_definitions as $oneFieldRow){
							$order_val = trim((string) $oneFieldRow['order_val']);
							$oneFieldLb = trim((string) $oneFieldRow['field_name']);
							$field_type = $oneFieldRow['field_type'];
							$parameters = $oneFieldRow['parameters'];
					
							$fieldVal = $fieldType = '';
							if(!empty($form_data) && array_key_exists($oneFieldLb, $form_data)){
								$value = $form_data[$oneFieldLb];
								$fieldArray = explode('||', $value);
								$fieldVal = $fieldArray[0];
								if(count($fieldArray)>1){$fieldType = $fieldArray[1];}								
							}
							
							if($isArray>0){
								if($field_type=='TextOnly' && $parameters !=''){
									$parameters = nl2br($parameters);
								}
								elseif($fieldVal !=''){								
									if($fieldType=='UploadImage' && $fieldVal !=''){
										$fieldVal = "//$domainName$fieldVal";
									}
									elseif($fieldType=='Signature' && $fieldVal !=''){
										$dsNotesObj = $this->db->query("SELECT note FROM digital_signature WHERE digital_signature_id = $fieldVal", array());
										if($dsNotesObj){
											$fieldVal = trim((string) stripslashes($dsNotesObj->fetch(PDO::FETCH_OBJ)->note));
										}
									}
								}
								$formsData[] = array('field_type'=>$field_type, 'fieldType'=>$fieldType, 'oneFieldLb'=>$oneFieldLb, 'fieldVal'=>$fieldVal, 'parameters'=>$parameters);
							}
							else{
								if($field_type=='TextOnly' && $parameters !=''){
									$formStr .= "<p>".nl2br($parameters)."</p>";
								}
								elseif($field_type=='SectionBreak'){
									if($parameters !='')
										$formStr .= "<p class=\"txtbold\">$parameters<hr class=\"mtop10 mbottom10\"></p>";
									else
										$formStr .= "<hr class=\"mtop10 mbottom10\">";
								}
								elseif($fieldVal !=''){								
									if($fieldType=='UploadImage' && $fieldVal !=''){
										$onePicture = "//$domainName$fieldVal";
										$formStr .= "<p style=\"margin:0\"><strong>$oneFieldLb : </strong><img align=\"center\" src=\"$onePicture\" style=\"max-width:100%; margin-bottom:10px;\"></p>";
									}
									elseif($fieldType=='Signature' && $fieldVal !=''){
										$dsNotesObj = $this->db->query("SELECT note FROM digital_signature WHERE digital_signature_id = $fieldVal", array());
										if($dsNotesObj){
											$note = trim((string) stripslashes($dsNotesObj->fetch(PDO::FETCH_OBJ)->note));
											if($note !=''){
												$formStr .= "<p style=\"margin:0\"><strong>$oneFieldLb :</p>";
												$formStr .= "<div style=\"clear:both\"></div>
															<p style=\"margin:0\"><img style=\"max-width:100%;\" alt=\"$fieldType\" src=\"$note\" style=\"max-width:100%; margin-bottom:10px;\"></p>";
											}				
										}
									}
									else{
										$formStr .= "<p style=\"margin:0\"><strong>$oneFieldLb : </strong>$fieldVal";
									}
								}
							}
						}
					}
					$border = '';
					if($i>1){$border = '<hr />';}
					$str .= "<tr>
								<td style=\"padding-top: 5px;word-wrap: break-word;font-size:11px;line-height: 21px; padding:0 20px;\">
									$border
									$form_name
									$formStr
								</td>
							</tr>";
					$returnData[] = array('i'=>$i, 'form_name'=>$form_name, 'last_updated'=>date($dateformat, strtotime($onerow->last_updated)), 'formsData'=>$formsData);
				}
				
				$str .= '</table>
						</div>
					</div>
								';
			}
		}
		
		if($isArray>0){
			return $returnData;
		}
		else{
			return $str;
		}
	}

}
?>