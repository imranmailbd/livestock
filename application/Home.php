<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Home{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	public function index(){}	
	
	public function AJ_index_MoreInfo(){

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$admin_id = $_SESSION["admin_id"]??0;
		$status = 'Trial';

		$jsonResponse = array();
		$jsonResponse['login'] = '';		
		$jsonResponse['accounts_id'] = $accounts_id;
		
		$showvideo = 0;
		if(isset($_SESSION["status"])){$status = $_SESSION["status"];}
		$popup_message = '';
		$userObj = $this->db->query("SELECT popup_message FROM user WHERE user_id = $user_id", array());
		if($userObj){
			$popup_message = $userObj->fetch(PDO::FETCH_OBJ)->popup_message;	
		}
		
		$jsonResponse['popup_message'] = $popup_message;

		if($popup_message !='' && strlen(trim((string) strip_tags($popup_message)))>10 && $accounts_id != 6){
			$this->db->update('user', array('popup_message'=>'', 'last_updated'=> date('Y-m-d H:i:s')), $user_id);
		}
		$price_per_location = 39;
		$accountsObj = $this->db->query("SELECT price_per_location FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accountsObj){
			$price_per_location = $accountsObj->fetch(PDO::FETCH_OBJ)->price_per_location;	
		}

		$jsonResponse['price_per_location'] = $price_per_location;
		$jsonResponse['status'] = $status;
		$jsonResponse['is_admin'] = intval($_SESSION['is_admin']);

		if(in_array(OUR_DOMAINNAME, array('machouse.com.bd'))){
			
			$updateMessage = '';
			$userObj = $this->db->query("SELECT popup_message FROM user WHERE user_id = 1", array());
			if($userObj){
				$updateMessage = $userObj->fetch(PDO::FETCH_OBJ)->popup_message;	
			}
			$jsonResponse['updateMessage'] = $updateMessage;
			$messageLength = strlen(strip_tags($updateMessage));
			$jsonResponse['messageLength'] = $messageLength;
		}
		
		return json_encode($jsonResponse);
	}
	
	public function help(){
		
	}	

	public function helpForm(){
	
		$returnData = array();
		$returnData['login'] = '';
		$user_id = $_SESSION["user_id"]??0;
		$user_email = '';
		$userObj = $this->db->query("SELECT user_email FROM user WHERE user_id = $user_id", array());
		if($userObj){
			$user_email = $userObj->fetch(PDO::FETCH_OBJ)->user_email;	
		}
		$returnData['helpemail'] = $user_email;
		$returnData['helpname'] = $_SESSION["user_first_name"];
		return json_encode($returnData);
	}
	
	public function sendHelpMail(){
		$returnStr = '';
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$accountsObj = $this->db->query("SELECT customer_service_email, status, company_subdomain FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accountsObj){
			$accountsOneRow = $accountsObj->fetch(PDO::FETCH_OBJ);	
			
			$customer_service_email = $accountsOneRow->customer_service_email;				
			$company_subdomain = $accountsOneRow->company_subdomain;
			$status = $accountsOneRow->status;
			
			$name = $_POST['helpname']??'';
			$email = $_POST['helpemail']??'';
			$helpbrowser = $_POST['helpbrowser']??'';
			$helpurl = $_POST['helpurl']??'';
			$subject = $_POST['helpsubject']??'';
			$subject = $company_subdomain." SUPPORT ".$subject;
			$description = $_POST['helpdescription']??'';
						
			$folderpath = "./assets/helpFiles";
			if(!is_dir($folderpath)){mkdir($folderpath, 0777);}
			$filename = $_FILES['attachment']['name'];
			$tmpFileName = $_FILES['attachment']['tmp_name'];
			
			$efilename = explode('.', $filename);
			$ext = strtolower($efilename[count($efilename) - 1]); 
			
			$filename = $accounts_id.'_ContactUs_'.substr(time(),7,3).".".$ext;
			$attachedpath = "$folderpath/$filename";
			$filePath = '';
			if(move_uploaded_file($tmpFileName, $attachedpath)){
				$filePath = $_SERVER['DOCUMENT_ROOT'] . str_replace('./', '/', $attachedpath);	
			}
			
			$mail_body = "<p>";
			$mail_body .= "Name: <b>$name</b><br>";
			$mail_body .= "Email: $email<br>";
			$mail_body .= "<br>";
			$mail_body .= "Message :<br>";
			$mail_body .= nl2br($description)."<br>";
			$mail_body .= "<br />";
			$mail_body .= "Page URL: $helpurl<br />";
			$mail_body .= "Browser Info: $helpbrowser";
			$mail_body .= "<br />";
			$mail_body .= "Status: $status";
			$mail_body .= "</p>";
						
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $this->db->supportEmail('Host');
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = $this->db->supportEmail('Username');
			$mail->Password = $this->db->supportEmail('Password');
			
			if($email==''){$email = $this->db->supportEmail('info');}
			$mail->addReplyTo($email, $_SESSION["company_name"]);
			$mail->setFrom($email, $name);
			$mail->clearAddresses();
			$mail->addAddress($this->db->supportEmail(OUR_DOMAINNAME), COMPANYNAME);
			$mail->Subject = $subject;
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			//Build a simple message body
			$mail->Body = $mail_body;
			if(!empty($filePath)){
				$mail->AddAttachment($filePath, $filename);
			}
			if($mail->send()){
				$returnStr = 'sent';
			}
			else{
				$returnStr = "Sorry! Could not send mail. Try again later.";
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function notpermitted(){}
	
	public function rat(){
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$search_str = 'Request Additional Time';
		$accountsObj = $this->db->query("SELECT company_subdomain, customer_service_email, trial_days, created_on FROM accounts WHERE accounts_id = $accounts_id", array());
		if($accountsObj){
			$accountsOneRow = $accountsObj->fetch(PDO::FETCH_OBJ);
			
			$sub_domain = $accountsOneRow->company_subdomain;
			$customer_service_email = $accountsOneRow->customer_service_email;
			if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
				
			$trial_days = $accountsOneRow->trial_days;
			$created_on = strtotime($accountsOneRow->created_on);
			$newtrial_days = 7+floor((strtotime(date('Y-m-d'))-$created_on) / 86400);
			
			$this->db->update('accounts', array('trial_days'=>$newtrial_days), $accounts_id);
			$_SESSION["trial_days"]= $newtrial_days;
			$returnStr = "Ok";
		}
		$prevuri = "/Home/index?msg=$returnStr";
		if($returnStr =='Ok' && isset($_SERVER['HTTP_REFERER'])) {
			$prevuri = $_SERVER['HTTP_REFERER'];
		}
		
		return "<meta http-equiv = \"refresh\" content = \"0; url = $prevuri\" />";
	}
	
	function handleErr(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;

		$name = $POST['name']??'';
		if(is_array($name)){$name = implode(', ', $name);}
		$message = $POST['message']??'';
		if(is_array($message)){$message = implode(', ', $message);}
		$url = $POST['url']??'';
		if(is_array($url)){$url = implode(', ', $url);}

		$this->db->writeIntoLog($name . ', Message: '.$message . ', Page Url: '.$url);
		return json_encode(array('returnMsg'=>'Saved'));
	}
	
}
?>