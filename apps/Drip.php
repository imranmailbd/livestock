<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Drip{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	public function runDrip(){
		$helpData = array();
      $mailBody = "Hello {{name}},\r\n
      I know it has been only a short time since you have signed up but I wanted to check in and see how things were coming along.\r\n
      If you would like a personal one and one demonstration just hit reply and let us know.\r\n
      Let me know if you have any questions or comments about our software service.\r\n
      You can get really good video tutorials on the HELP page inside our application software.\r\n
      Your account details are\r\n
      URL: http://{{sub-domain}}.".OUR_DOMAINNAME."
      email: {{email}}\r\n
      Thanks!\r\n
      Dennis
      www.".OUR_DOMAINNAME;
      $helpData[1] = array('{{name}} for your {{sub-domain}}.".OUR_DOMAINNAME." account', $mailBody);

      $mailBody = "{{company}}\r\n
      Were you able to create products and services and add inventory to your account?  You will need to do that before you can do much with the cash register or repair tickets.\r\n
      Also, you will want to go into the \"Getting Started\" and \"Setup\" modules from the home page to customize your account with options like currency, time zone, invoice numbers, logos, messages printed on invoice and much more.\r\n
      Your account details are\r\n
      URL: http://{{sub-domain}}.".OUR_DOMAINNAME."
      email: {{email}}\r\n
      Thanks,\r\n
      Dennis
      www.".OUR_DOMAINNAME;
      $helpData[2] = array('{{name}} trial account information', $mailBody);

      $mailBody = "Hello {{name}},\r\n
      Your account details are\r\n
      URL: http://{{sub-domain}}.".OUR_DOMAINNAME."
      email: {{email}}\r\n
      You can view training videos and get help from us by logging into your account and clicking the HELP link at the top of any page.\r\n
      Thanks\r\n
      Your SK POS Support Team";
      $helpData[3] = array('{{company}} your SK POS Trial Account Info', $mailBody);

      $mailBody = "Hello {{name}},\r\n
      You created a FREE trial and we wanted to let you know if you need more time just ask!!!!!  You can log into your trial account and click the link at the top for more time.  If you have any questions just hit reply and ask away.\r\n
      Your account details are\r\n
      URL: http://{{sub-domain}}.".OUR_DOMAINNAME."
      email: {{email}}\r\n
      Thanks,\r\n
      Dennis
      www.".OUR_DOMAINNAME;
      $helpData[7] = array('Cellular Phone Retail and Repair Store Software', $mailBody);

      $mailBody = "Hello {{name}},\r\n
      It has already been 14 days since you signed up for your account.  We have added a button at the top of the screen to allow you to extend your trial by 7 days to give you more time to play with the trial.\r\n
      Your account details are\r\n
      URL: http://{{sub-domain}}.".OUR_DOMAINNAME."
      email: {{email}}\r\n
      Thanks,\r\n
      Dennis
      www.".OUR_DOMAINNAME;
      $helpData[14] = array('Your SK POS Trial Account Can Be Extended', $mailBody);
      
      if(!empty($helpData)){
         
         $mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $this->db->supportEmail('Host');
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = $this->db->supportEmail('Username');
			$mail->Password = $this->db->supportEmail('Password');
         
         foreach($helpData as $registrationDays=>$oneRow){
            $subject = $oneRow[0];
            $mailbody = nl2br(stripslashes(trim($oneRow[1])));
            $addSql = '';//" AND a.accounts_id IN (1, 7832, 8046, 8062, 9470, 10042, 10043)";
            $registrationDate = date('Y-m-d', strtotime("-$registrationDays day"));
            $sql = "SELECT a.accounts_id, u.user_first_name, u.user_last_name, u.user_email, a.company_name, a.company_subdomain FROM accounts a, user u WHERE a.status = 'Trial' $addSql AND substring(a.created_on,1,10) = '$registrationDate' AND a.accounts_id = u.accounts_id AND u.is_admin = 1 AND a.location_of = 0 ORDER BY a.accounts_id ASC";
            $queryObj = $this->db->query($sql, array());
            if($queryObj){
               while($oneRow = $queryObj->fetch(PDO::FETCH_OBJ)){

                  $accounts_id = $oneRow->accounts_id;
                  $name = stripslashes(trim("$oneRow->user_first_name $oneRow->user_last_name"));
                  $email = stripslashes(trim($oneRow->user_email));
                  $company = stripslashes(trim($oneRow->company_name));
                  $subdomain = stripslashes(trim($oneRow->company_subdomain));
                  
                  $updateSubject = str_replace('{{name}}', $name, $subject);
                  $updateSubject = str_replace('{{email}}', $email, $updateSubject);
                  $updateSubject = str_replace('{{company}}', $company, $updateSubject);
                  $updateSubject = str_replace('{{sub-domain}}', $subdomain, $updateSubject);
                  
                  $updateMailbody = str_replace('{{name}}', $name, $mailbody);
                  $updateMailbody = str_replace('{{email}}', $email, $updateMailbody);
                  $updateMailbody = str_replace('{{company}}', $company, $updateMailbody);
                  $updateMailbody = str_replace('{{sub-domain}}', $subdomain, $updateMailbody);
                  
                  //=========Mailing Start============//
                  $mail->addReplyTo($this->db->supportEmail('support'), COMPANYNAME);
                  $mail->setFrom($this->db->supportEmail('info'), COMPANYNAME);				
                  $mail->ClearAllRecipients();
                  $mail->addAddress($email, $name);
                  //$mail->addBCC('den.romano@gmail.com');
                  $mail->Subject = $updateSubject;
                  $mail->isHTML(true);
                  $mail->CharSet = 'UTF-8';
                  
                  //Build a simple message body
                  $mail->Body = '<p>'.$updateMailbody.'</p>';
                  $mail->send();
                  
               }
            }
         }
      }
	}

   public function inventoryValueInsert(){
		$tableData = array();
		
		$accountsIds = "7870, 7922, 6751, 3907, 6974, 5510, 4818";
				
		$sqlquery = "SELECT p.product_id AS product_id, p.accounts_id AS accounts_id, p.product_type, i.current_inventory AS current_inventory, i.ave_cost FROM inventory i, product p WHERE i.accounts_id IN ($accountsIds) AND i.product_id = p.product_id AND p.product_type = 'Standard' AND p.manage_inventory_count=1 
				UNION SELECT p.product_id AS product_id, p.accounts_id AS accounts_id, p.product_type, count(item.item_id) AS current_inventory, i.ave_cost FROM inventory i, product p LEFT JOIN item ON (item.product_id = p.product_id and item.in_inventory = 1) WHERE i.accounts_id IN ($accountsIds) AND i.product_id = p.product_id AND p.product_type = 'Live Stocks' 
				GROUP BY p.product_id ORDER BY accounts_id ASC, product_id ASC";
		$query = $this->db->querypagination($sqlquery, array());
		$productInfo = array();
		$Common = new Common($this->db);
		if($query){
			foreach($query as $onegrouprow1){
				$product_id = $onegrouprow1['product_id'];
				$accounts_id = $onegrouprow1['accounts_id'];
				$product_type = $onegrouprow1['product_type'];
				$qty = $onegrouprow1['current_inventory'];
				if($product_type=='Live Stocks'){
					$ave_cost = 0.00;
					if($qty>0){
						$mobileProdAveCost = $Common->mobileProdAveCost($accounts_id, $product_id, ' AND in_inventory=1');
						$ave_cost = $mobileProdAveCost[0];
					}
				}
				else{
					$ave_cost = $onegrouprow1['ave_cost'];
				}
				if($qty<0){$ave_cost = 0.00;}
				$productInfo[$product_id] = array($qty, $ave_cost);
			}
		}
		
		if(!empty($productInfo)){
			foreach($productInfo as $product_id=>$productDetails){
				$curQty = $productDetails[0];
				$curAveCost = $productDetails[1];
				
            //====================Insert into temp_inventory_report=================//
            $dupDataCount = 0;
            $search_date = date('Y-m-d',strtotime("-1 days"));
            $invRepCountObj = $this->db->query("SELECT temp_inventory_report_id FROM temp_inventory_report WHERE accounts_id = $accounts_id AND product_id = $product_id AND search_date = :search_date", array('search_date'=>$search_date));
            if($invRepCountObj){	
               $dupDataCount = $invRepCountObj->fetch(PDO::FETCH_OBJ)->temp_inventory_report_id;	
            }
            if($dupDataCount==0){
               $teData = array();
               $teData['search_date'] = $search_date;
               $teData['accounts_id'] = $accounts_id;
               $teData['product_id'] = $product_id;
               $teData['current_qty'] = $curQty;
               $teData['current_ave_cost'] = $curAveCost;
               $this->db->insert('temp_inventory_report', $teData);
            }
			}
		}
   }

}
?>