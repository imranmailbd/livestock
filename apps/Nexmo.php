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
			//$this->db->writeIntoLog('This is not a delivery receipt');
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
	
	public function sendSMS($smstophone='', $smsmessage=''){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		if(empty($smstophone) && isset($POST['smstophone']))
			$smstophone = addslashes($POST['smstophone']??'');
		if(empty($smsmessage) && isset($POST['smsmessage']))
			$smsmessage = addslashes($POST['smsmessage']??'');
		
		if($this->bulkSMSPassword !='' && $this->bulkSMSSecretToken !='' && $this->bulkSMSSenderID !='' && !empty($smstophone)){
			$smsmessage = stripslashes(trim((string) strip_tags($smsmessage)));
			$url = 'http://rest.bulkSMS.com/sms/json?' . http_build_query([
				'type' => 'unicode',
				'api_key' => $this->bulkSMSPassword,
				'api_secret' => $this->bulkSMSSecretToken,
				'to' => $smstophone,
				'from' => $this->bulkSMSSenderID,
				'text' => $smsmessage
			]);
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = json_decode(curl_exec($ch));
			$messages = (array) $response->messages[0];
			$status = $messages['status']??1;
			if($status==0){
				$returnStr = 'sent';
			}
			else{
				$accounts_id = $_SESSION["accounts_id"]??0;
				//$this->db->writeIntoLog("BulkSMS Account ID: $accounts_id, line #131 issue: ".$messages['error-text']);
				$returnStr = $messages['error-text']??'error';
			}
		}
		else{
			$returnStr = $this->db->translate('Please setup SMS messaging.');
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
}
?>