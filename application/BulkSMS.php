<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class BulkSMS{
	
	protected $db;
	private string $bulkSMSPassword, $bulkSMSSecretToken, $bulkSMSSenderID;
	
	public function __construct($db){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$this->db = $db;
		$bulkSMSPassword = $bulkSMSSecretToken = $bulkSMSSenderID = '';
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'sms_messaging' AND value !=''", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			$value = $variablesData->value;
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
			}
		}
		$this->bulkSMSPassword = $bulkSMSPassword;
		$this->bulkSMSSecretToken = $bulkSMSSecretToken;
		if($bulkSMSSenderID==''){$bulkSMSSenderID = $_SESSION["company_name"]??'';}
		$this->bulkSMSSenderID = $bulkSMSSenderID;
	}
	
	public function replySMS(){
		$request = array_merge($_GET, $_POST);
		$returnStr = '';
		// Check that this is a delivery receipt.
		if (!isset($request['messageId']) && !isset($request['status'])) {
			//$this->db->writeIntoLog('BulkSMS line #9 issue: '.json_encode($request));
			return $returnStr;
		}
		
		if (isset($request['msisdn']) && isset($request['to']) && isset($request['text'])) {
			
			$bulkSMSFromMobile = $request['msisdn'];
			$bulkSMSSenderID = $request['to'];
			
			$variablesData = $this->db->querypagination("SELECT value FROM variables WHERE name = 'sms_messaging' AND value LIKE '%$bulkSMSSenderID%' LIMIT 0, 1", array());
			if($variablesData){
				
				foreach($variablesData as $oneRow){
					
					$oneRowValue = unserialize($oneRow['value']);
					
					if(is_array($oneRowValue) and array_key_exists('bulkSMSEmail', $oneRowValue)){
						$replyEmail = $oneRowValue['bulkSMSEmail'];
						if($replyEmail !=''){
							
							$mail = new PHPMailer;
							$mail->isSMTP(); 
							$mail->Host = "smtp.".OUR_DOMAINNAME;
							$mail->Port = 587;
							$mail->setFrom($this->db->supportEmail('do_not_reply'), $bulkSMSFromMobile);
							$mail->clearAddresses();
							$mail->addAddress($replyEmail, $bulkSMSSenderID);
							$mail->Subject = 'Reply SMS from '.$bulkSMSFromMobile;
							$mail->isHTML(true);
							$mail->CharSet = 'UTF-8';
							//Build a simple message body
							$mail->Body = "<p>
			* * * DO NOT HIT REPLY * * *<br>
			From Phone : <strong>$bulkSMSFromMobile</strong><br>
			<br>
			Reply SMS : $request[text]
			</p>";
							//Send the message, check for errors
							if (!$mail->send()) {
								//$this->db->writeIntoLog("Your message could not send.");
							}
						}
					}
				}
			}
			else{
				//$this->db->writeIntoLog('BulkSMS Sender ID is not matching.');
			}
		}
		else {
			//$this->db->writeIntoLog('BulkSMS line #62 issue: '.json_encode($request));	
		}
		return $returnStr;
	}
	
	public function sendSMS($smstophone='', $smsmessage='', $returnArray = 0){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		if(empty($smstophone) && isset($POST['smstophone']))
			$smstophone = addslashes($POST['smstophone']??'');
		if(empty($smsmessage) && isset($POST['smsmessage']))
			$smsmessage = addslashes($POST['smsmessage']??'');
		
		if($this->bulkSMSPassword !='' && $this->bulkSMSSecretToken !='' && $this->bulkSMSSenderID !='' && !empty($smstophone)){
			
			$smsmessage = stripslashes(trim((string) strip_tags($smsmessage)));
			$token = $this->bulkSMSSecretToken;
			$url = "http://api.greenweb.com.bd/api.php?json";
			$data= array('to'=>"$smstophone", 'message'=>"$smsmessage", 'token'=>"$token");

			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_ENCODING, '');
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$smsresult = curl_exec($ch);
			$smsError = curl_error($ch);
			$statusData = (array)json_decode($smsresult);
			$status = '';
			if(!empty($statusData) && is_array($statusData)){
				$status = $statusData[0]->status;
				$statusmsg = $statusData[0]->statusmsg;
			}
			//$this->db->writeIntoLog("smstophone: $smstophone, status: $status, smsmessage: $smsmessage, statusData: ".json_encode($statusData));
			if($status=='SENT'){
				$returnStr = 'sent';
			}
			else{
				$returnStr = 'Could not send SMS to '.$smstophone;
			}
		}
		else{
			$returnStr = $this->db->translate('Please setup SMS messaging.');
		}
		///$this->db->writeIntoLog("returnStr: $returnStr");
		if($returnArray==1){
			return array('login'=>'', 'returnStr'=>$returnStr);
		}

		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}

	public function AJsendInvoiceSMS(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']??0);
		$returnStr = 'error';
		$sql = "SELECT * FROM pos WHERE pos_id = $pos_id";
		$query = $this->db->querypagination($sql, array());
		if($query){
			$Common = new Common($this->db);
			foreach($query as $onerow){
				$customer_id = intval($onerow['customer_id']);
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

				if($this->sendInvoiceSMS($customer_id, $onerow['invoice_no'], $grand_total, $amountPaid, $onerow['sales_datetime'])){
					$returnStr = 'Success';
				}
				else{
					$returnStr = 'Could not send SMS. Please contact with Support.';
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}

	public function sendInvoiceSMS($customers_id, $InvoiceNumber, $InvoiceTotal, $PaymentTotal, $InvoiceDate=''){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$FirstName = $LastName = $contact_no = $smsContact_no = $bulkSMSinvoice = '';
		$queryObj = $this->db->query("SELECT first_name, last_name, contact_no FROM customers WHERE customers_id=$customers_id AND customers_publish = 1", array());
		if($queryObj){
			$onecustomerrow = $queryObj->fetch(PDO::FETCH_OBJ);
			$FirstName = stripslashes(trim($onecustomerrow->first_name));
			$LastName = stripslashes(trim($onecustomerrow->last_name));
			$contact_no = $onecustomerrow->contact_no;
		}

		if(!empty($contact_no)){
			$smsContact_no = $contact_no;
			$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id=$accounts_id AND name='sms_messaging'", array());
			if($queryObj){
				$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$value = unserialize($value);

					if(array_key_exists('bulkSMSinvoice', $value)){
						$bulkSMSinvoice = $value['bulkSMSinvoice'];
					}
					$bulkSMSCountryCode = '';
					$leadingZeros = 0;
					if(array_key_exists('bulkSMSCountryCode', $value)){
						$bulkSMSCountryCode = '+'.$value['bulkSMSCountryCode'];
					}
					if(array_key_exists('leadingZeros', $value)){
						$leadingZeros = $value['leadingZeros'];
					}
					if($leadingZeros>0){
						$smsContact_no = ltrim((string) $smsContact_no, '0');
					}
					if(strlen($bulkSMSCountryCode)>1){
						$withoutPlusCountryCode = ltrim((string) $bulkSMSCountryCode, '0');
						$smsContact_no = ltrim((string) $smsContact_no, $withoutPlusCountryCode);
						$smsContact_no = ltrim((string) $smsContact_no, $bulkSMSCountryCode);
					}
					$smsContact_no = $bulkSMSCountryCode.$smsContact_no;
				}
			}
		}

		if(!empty($bulkSMSinvoice) && !empty($smsContact_no)){
			
			if(strpos($bulkSMSinvoice, '{{FirstName}}') !==false){
				$bulkSMSinvoice = str_replace('{{FirstName}}', $FirstName, $bulkSMSinvoice);
			}
			if(strpos($bulkSMSinvoice, '{{LastName}}') !==false){
				$bulkSMSinvoice = str_replace('{{LastName}}', $LastName, $bulkSMSinvoice);
			}
			if(strpos($bulkSMSinvoice, '{{CompanyName}}') !==false){
				$bulkSMSinvoice = str_replace('{{CompanyName}}', COMPANYNAME, $bulkSMSinvoice);
			}
			if(strpos($bulkSMSinvoice, '{{InvoiceNumber}}') !==false){
				$bulkSMSinvoice = str_replace('{{InvoiceNumber}}', 's'.$InvoiceNumber, $bulkSMSinvoice);
			}
			if(strpos($bulkSMSinvoice, '{{InvoiceTotal}}') !==false){
				$bulkSMSinvoice = str_replace('{{InvoiceTotal}}', $InvoiceTotal, $bulkSMSinvoice);
			}
			if(strpos($bulkSMSinvoice, '{{PaymentTotal}}') !==false){
				$bulkSMSinvoice = str_replace('{{PaymentTotal}}', $PaymentTotal, $bulkSMSinvoice);
			}
			if(strpos($bulkSMSinvoice, '{{TotalDues}}') !==false){
				$TotalDues = $InvoiceTotal-$PaymentTotal;
				$bulkSMSinvoice = str_replace('{{TotalDues}}', $TotalDues, $bulkSMSinvoice);
			}
			if(strpos($bulkSMSinvoice, '{{InvoiceDate}}') !==false){
				$InvoiceDate = date($dateformat, strtotime($InvoiceDate));
				$bulkSMSinvoice = str_replace('{{InvoiceDate}}', $InvoiceDate, $bulkSMSinvoice);
			}
			if(strpos($bulkSMSinvoice, '{{InvoiceURL}}') !==false){
				$encode_sd = base64_encode($GLOBALS['subdomain']);
				$encode_invNo = base64_encode($InvoiceNumber);
				$InvoiceURL = 'http://'.OUR_DOMAINNAME.'/widget/?sd='.$encode_sd.'&module=invoice&invNo='.$encode_invNo;
				$bulkSMSinvoice = str_replace('{{InvoiceURL}}', $InvoiceURL, $bulkSMSinvoice);
			}
			
			$sendSMS = $this->sendSMS($smsContact_no, $bulkSMSinvoice, 1);
			if($sendSMS['returnStr'] == 'sent'){
				return true;
			}
			return false;
		}
	}
	
	public function AJsendPOSMS(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$po_id = intval($POST['po_id']??0);
		$returnStr = 'error';

		$sql = "SELECT * FROM po WHERE po_id = $po_id";
		$query = $this->db->querypagination($sql, array());
		if($query){
			$Common = new Common($this->db);
			foreach($query as $onerow){
				$supplier_id = intval($onerow['supplier_id']);
				$grand_total = 0.00;
				
				$sqlquery = "SELECT * FROM po_items WHERE po_id = $po_id";
				$query = $this->db->query($sqlquery, array());
				if($query){
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						$cost = $row->cost;
						$received_qty = $row->received_qty;
						$total = round($cost * $received_qty,2);
						$grand_total += $total;											
					}
				}

				$amountPaid = 0;
				$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM po_payment WHERE po_id = $po_id AND payment_method != 'Change' GROUP BY po_id";
				$queryObj = $this->db->query($sqlquery, array());
				if($queryObj){
					$amountPaid = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
				}

				if($this->sendPOSMS($supplier_id, $onerow['po_number'], $grand_total, $amountPaid, $onerow['date_expected'])){
					$returnStr = 'Success';
				}
				else{
					$returnStr = 'Could not send SMS. Please contact with Support.';
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}

	public function sendPOSMS($suppliers_id, $PONumber, $POTotal, $PaymentTotal, $PODateExpected=''){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$FirstName = $LastName = $contact_no = $smsContact_no = $bulkSMSpo = '';
		$queryObj = $this->db->query("SELECT company, first_name, last_name, contact_no FROM suppliers WHERE suppliers_id = $suppliers_id AND suppliers_publish = 1", array());
		if($queryObj){
			$onesupplierrow = $queryObj->fetch(PDO::FETCH_OBJ);
			$FirstName = stripslashes(trim("$onesupplierrow->company $onesupplierrow->first_name"));
			$LastName = stripslashes(trim($onesupplierrow->last_name));
			$contact_no = $onesupplierrow->contact_no;
		}

		if(!empty($contact_no)){
			$smsContact_no = $contact_no;
			$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id=$accounts_id AND name='sms_messaging'", array());
			if($queryObj){
				$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$value = unserialize($value);

					if(array_key_exists('bulkSMSpo', $value)){
						$bulkSMSpo = $value['bulkSMSpo'];
					}
					$bulkSMSCountryCode = '';
					$leadingZeros = 0;
					if(array_key_exists('bulkSMSCountryCode', $value)){
						$bulkSMSCountryCode = '+'.$value['bulkSMSCountryCode'];
					}
					if(array_key_exists('leadingZeros', $value)){
						$leadingZeros = $value['leadingZeros'];
					}
					if($leadingZeros>0){
						$smsContact_no = ltrim((string) $smsContact_no, '0');
					}
					if(strlen($bulkSMSCountryCode)>1){
						$withoutPlusCountryCode = ltrim((string) $bulkSMSCountryCode, '0');
						$smsContact_no = ltrim((string) $smsContact_no, $withoutPlusCountryCode);
						$smsContact_no = ltrim((string) $smsContact_no, $bulkSMSCountryCode);
					}
					$smsContact_no = $bulkSMSCountryCode.$smsContact_no;
				}
			}
		}

		if(!empty($bulkSMSpo) && !empty($smsContact_no)){
			
			if(strpos($bulkSMSpo, '{{FirstName}}') !==false){
				$bulkSMSpo = str_replace('{{FirstName}}', $FirstName, $bulkSMSpo);
			}
			if(strpos($bulkSMSpo, '{{LastName}}') !==false){
				$bulkSMSpo = str_replace('{{LastName}}', $LastName, $bulkSMSpo);
			}
			if(strpos($bulkSMSpo, '{{CompanyName}}') !==false){
				$bulkSMSpo = str_replace('{{CompanyName}}', COMPANYNAME, $bulkSMSpo);
			}
			if(strpos($bulkSMSpo, '{{PONumber}}') !==false){
				$bulkSMSpo = str_replace('{{PONumber}}', 'p'.$PONumber, $bulkSMSpo);
			}
			if(strpos($bulkSMSpo, '{{POTotal}}') !==false){
				$bulkSMSpo = str_replace('{{POTotal}}', $POTotal, $bulkSMSpo);
			}
			if(strpos($bulkSMSpo, '{{PaymentTotal}}') !==false){
				$bulkSMSpo = str_replace('{{PaymentTotal}}', $PaymentTotal, $bulkSMSpo);
			}
			if(strpos($bulkSMSpo, '{{TotalDues}}') !==false){
				$TotalDues = $POTotal-$PaymentTotal;
				$bulkSMSpo = str_replace('{{TotalDues}}', $TotalDues, $bulkSMSpo);
			}
			if(strpos($bulkSMSpo, '{{PODateExpected}}') !==false){
				$PODateExpected = date($dateformat, strtotime($PODateExpected));
				$bulkSMSpo = str_replace('{{PODateExpected}}', $PODateExpected, $bulkSMSpo);
			}
			if(strpos($bulkSMSpo, '{{POURL}}') !==false){
				$encode_sd = base64_encode($GLOBALS['subdomain']);
				$encode_invNo = base64_encode($PONumber);
				$POURL = 'http://'.OUR_DOMAINNAME.'/widget/?sd='.$encode_sd.'&module=po&poNo='.$encode_invNo;
				$bulkSMSpo = str_replace('{{POURL}}', $POURL, $bulkSMSpo);
			}
			
			$sendSMS = $this->sendSMS($smsContact_no, $bulkSMSpo, 1);
			if($sendSMS['returnStr'] == 'sent'){
				return true;
			}
			return false;
		}
	}
	
}
?>