<?php
class Integrations{
	
	protected $db;
	public string $pageTitle;
	public function __construct($db){$this->db = $db;}
	
	public function bulkSMS(){}
	
	public function AJ_bulkSMS_MoreInfo(){
		$accounts_id = $_SESSION['accounts_id']??0;
		$variables_id = $leadingZeros = 0;
		$bulkSMSPassword = $bulkSMSSecretToken = $bulkSMSCountryCode = $bulkSMSinvoice = $bulkSMSpo = $bulkSMSSenderID = $bulkSMSEmail = '';
			
		$company_country_name = '';
		$usersObj3 = $this->db->query("SELECT company_country_name FROM accounts WHERE accounts_id = $accounts_id", array());
		if($usersObj3){
			$company_country_name = $usersObj3->fetch(PDO::FETCH_OBJ)->company_country_name;
		}
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
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['variables_id'] = $variables_id;
		$jsonResponse['leadingZeros'] = $leadingZeros;
		$jsonResponse['bulkSMSPassword'] = $bulkSMSPassword;
		$jsonResponse['bulkSMSSecretToken'] = $bulkSMSSecretToken;
		$jsonResponse['bulkSMSCountryCode'] = $bulkSMSCountryCode;
		$jsonResponse['bulkSMSinvoice'] = $bulkSMSinvoice;
		$jsonResponse['bulkSMSpo'] = $bulkSMSpo;
		$jsonResponse['bulkSMSSenderID'] = $bulkSMSSenderID;
		$jsonResponse['bulkSMSEmail'] = $bulkSMSEmail;
		$jsonResponse['company_country_name'] = $company_country_name;
		$jsonResponse['subdomain'] = $GLOBALS['subdomain'];
		$jsonResponse['OUR_DOMAINNAME'] = OUR_DOMAINNAME;
		
		return json_encode($jsonResponse);
	}
	
   public function AJsave_bulkSMS(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$variables_id = '';
		$savemsg = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$bulkSMSPassword = addslashes($POST['bulkSMSPassword']??'');
		$bulkSMSSecretToken = addslashes($POST['bulkSMSSecretToken']??'');
		$bulkSMSCountryCode = str_replace('+', '', addslashes($POST['bulkSMSCountryCode']??''));
		$bulkSMSSenderID = addslashes($POST['bulkSMSSenderID']??'');
		$bulkSMSinvoice = addslashes($POST['bulkSMSinvoice']??'');
		$bulkSMSpo = addslashes($POST['bulkSMSpo']??'');
		$leadingZeros = intval($POST['leadingZeros']??0);
		$bulkSMSEmail = addslashes($POST['bulkSMSEmail']??'');
		$savemsg = 'Ok';
		$variables_id = 0;
		$queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='sms_messaging'", array());
		if($queryObj){
			$variables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		}
		$valueData = array();
		$valueData['bulkSMSPassword'] = $bulkSMSPassword;
		$valueData['bulkSMSSecretToken'] = $bulkSMSSecretToken;
		$valueData['bulkSMSCountryCode'] = $bulkSMSCountryCode;
		$valueData['bulkSMSinvoice'] = $bulkSMSinvoice;
		$valueData['bulkSMSpo'] = $bulkSMSpo;
		$valueData['bulkSMSSenderID'] = $bulkSMSSenderID;
		$valueData['bulkSMSEmail'] = $bulkSMSEmail;
		$valueData['leadingZeros'] = $leadingZeros;

		$value = serialize($valueData);
		$data=array('accounts_id'=>$accounts_id,
			'name'=>$this->db->checkCharLen('variables.name', 'sms_messaging'),
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

		$array = array( 'login'=>'', 'savemsg'=>$savemsg,
			'variables_id'=>$variables_id);
		return json_encode($array);
   }
	
	public function squareup(){}
	
	public function AJ_squareup_MoreInfo(){
		 $accounts_id = $_SESSION['accounts_id']??0;
		 $variables_id = 0;
		 $sqrup_currency_code = '';
							 
		 $varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'cr_card_processing' AND value !=''", array());
		 if($varObj){
			 $variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			 $variables_id =  $variablesData->variables_id;
			 $value = $variablesData->value;
			 if(!empty($value)){
				 $value = unserialize($value);
				 extract($value);
				 if(!empty($sqrup_currency_code) && $variables_id>0){
				 }
			 }
		 }
		 $jsonResponse = array();
		 $jsonResponse['login'] = '';
		 $jsonResponse['variables_id'] = $variables_id;
		 $jsonResponse['sqrup_currency_code'] = $sqrup_currency_code;
		 
		 return json_encode($jsonResponse);
	}
 
	public function AJsave_squareup(){
		 $POST = json_decode(file_get_contents('php://input'), true);
		 $savemsg = 'error';
		 $accounts_id = $_SESSION["accounts_id"]??0;
		 $sqrup_currency_code = addslashes($POST['sqrup_currency_code']??'');
 
		 $crvariables_id = 0;
		 $queryObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id=$accounts_id AND name='cr_card_processing'", array());
		 if($queryObj){
			 $crvariables_id = $queryObj->fetch(PDO::FETCH_OBJ)->variables_id;
		 }
 
		 $poData = array();
		 $Common = new Common($this->db);
		 $vData = $Common->variablesData('payment_options', $accounts_id);
		 if(!empty($vData)){
			 extract($vData);
			 $poData = explode('||', $payment_options);
			 $poData = array_flip($poData);
		 }
 
		 if($sqrup_currency_code !=''){
			 $valueData['sqrup_currency_code'] = $sqrup_currency_code;
 
			 $value = serialize($valueData);
			 $data=array('accounts_id'=>$accounts_id,
				 'name'=>$this->db->checkCharLen('variables.name', 'cr_card_processing'),
				 'value'=>$value,
				 'last_updated'=> date('Y-m-d H:i:s'));
			 if($crvariables_id==0){
				 $crvariables_id = $this->db->insert('variables', $data);
				 if($crvariables_id){
					 $savemsg = 'insert-success';
				 }
			 }
			 else{
				 $update = $this->db->update('variables', $data, $crvariables_id);
				 if($update){
					 $savemsg = 'update-success';
				 }
			 }
 
			 $poData['Squareup'] = '';
		 }
		 else{
			 if($crvariables_id>0){
				 $this->db->delete('variables', 'variables_id', $crvariables_id);
				 $savemsg = 'update-success';
 
				 if(!empty($poData) && array_key_exists('Squareup', $poData)){
					 unset($poData['Squareup']);
				 }
			 }
		 }
		 
		 if(!empty($poData)){
			 $variables_id = 0;
			 $varObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id = $accounts_id AND name = 'payment_options'", array());
			 if($varObj){
				 $variables_id = $varObj->fetch(PDO::FETCH_OBJ)->variables_id;
			 }
			 $value = serialize(array('payment_options'=>implode('||', array_keys($poData))));
			 $data=array('accounts_id'=>$accounts_id,
				 'name'=>$this->db->checkCharLen('variables.name', 'payment_options'),
				 'value'=>$value,
				 'last_updated'=> date('Y-m-d H:i:s'));
			 if($variables_id==0){
				 $this->db->insert('variables', $data);
			 }
			 else{
				 $this->db->update('variables', $data, $variables_id);
			 }
		 }
 
		 $array = array( 'login'=>'', 'savemsg'=>$savemsg,
			 'variables_id'=>$crvariables_id);
		 return json_encode($array);
	}
 
}
?>