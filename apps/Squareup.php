<?php
class Squareup{
	protected $db;
	public function __construct($db){
		$this->db = $db;
	}
	
	public function callbackurls(){
		//$this->db->writeIntoLog("SquareupCallback: ".json_encode($_REQUEST));
		
		$responseStr = '';
		if(array_key_exists('com_squareup_pos_CLIENT_TRANSACTION_ID', $_REQUEST)){
			$responseStr = $_REQUEST['com_squareup_pos_CLIENT_TRANSACTION_ID'];
		}
		elseif(array_key_exists('com_squareup_pos_SERVER_TRANSACTION_ID', $_REQUEST)){
			$responseStr = $_REQUEST['com_squareup_pos_SERVER_TRANSACTION_ID'];
		}
		$segment5Value = "$responseStr;";
		$redirectURI = '';
		$metaData = array();
		if(array_key_exists('com_squareup_pos_REQUEST_METADATA', $_REQUEST)){
			$metaData = explode('|', $_REQUEST['com_squareup_pos_REQUEST_METADATA']);
		}
		elseif(array_key_exists('data', $_REQUEST)){
			$data = json_decode($_REQUEST['data']);
			$data = (array) $data;
			if(!empty($data) && array_key_exists('status', $data) && $data['status']=='ok'){
				if(array_key_exists('state', $data)){
					$metaData = explode('|', stripslashes($data['state']));
				}
				if(array_key_exists('client_transaction_id', $data)){
					$responseStr = $data['client_transaction_id'];
				}
				elseif(array_key_exists('transaction_id', $data)){
					$responseStr = $data['transaction_id'];
				}
			}
		}
		
		if(!empty($metaData) && is_array($metaData) && count($metaData)>4){
			
			$redirectURI = $metaData[0];
			$pos_id = $metaData[1];
			$payment_amount = round($metaData[2]/100,2);
			$accounts_id = $metaData[3];
			$user_id = $metaData[4];
			$segment5Value .="$pos_id|$metaData[2]|$accounts_id|$user_id";
		}
		
		if($responseStr !='' && $redirectURI !=''){
			$posObj = $this->db->query("SELECT pos_id FROM pos WHERE pos_id = :pos_id AND accounts_id = :accounts_id", array('pos_id'=>$pos_id, 'accounts_id'=>$accounts_id),1);
			if($posObj){
				$timezone = '';				
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'account_setup'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$varData = unserialize($value);
						if(!empty($varData) && array_key_exists('timezone', $varData)){
							$timezone = $varData["timezone"];
						}
					}
				}
				if($timezone =='' || is_null($timezone)){$timezone = 'America/New_York';}
				date_default_timezone_set($timezone);
				
				$payment_method = $this->db->checkCharLen('pos_payment.payment_method', 'Squareup');
				$drawer = $this->db->checkCharLen('pos_payment.drawer', '');
				$more_details = json_encode($_REQUEST);
				$ppData = array('pos_id' => $pos_id,
								'payment_method' => $payment_method,
								'payment_amount' => round($payment_amount,2),	
								'payment_datetime' => date('Y-m-d H:i:s'),
								'user_id' => $user_id,
								'more_details' => $more_details,
								'drawer' => $drawer);
				$pos_payment_id = $this->db->insert('pos_payment', $ppData);
			}
			else{
				$redirectURI = "$redirectURI/squareUpPayment/$payment_amount/$segment5Value";
			}
		}
		
		if($redirectURI == ''){
			$redirectURI = "http://".OUR_DOMAINNAME."/Home/";
		}
		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURI\" />";

	}

	public function fallbackurl(){
		$redirectURI = '';
		if($_REQUEST['com_squareup_pos_REQUEST_METADATA']??'' !=''){
			$metaData = explode('|', $_REQUEST['com_squareup_pos_REQUEST_METADATA']);
			if(is_array($metaData) && count($metaData)>4){
				$redirectURI = $metaData[0];
			}
		}
		
		if($redirectURI == ''){
			$redirectURI = "http://".OUR_DOMAINNAME."/Home/";
		}
		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURI\" />";

	}
}
?>