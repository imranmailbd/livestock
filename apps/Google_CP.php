<?php
if (session_status() == PHP_SESSION_NONE) {session_start();}
class Google_CP{
	public $ch;
	public $httpResponse;
	const PRINTERS_SEARCH_URL = "http://www.google.com/cloudprint/search";
	const PRINT_URL = "http://www.google.com/cloudprint/submit";
    const JOBS_URL = "http://www.google.com/cloudprint/jobs";
	const CLIENT_ID = '987124515536-7p5en898ttlmub9fv3ccaalgqjgc039h.apps.googleusercontent.com';
	const CLIENT_SECRET = '7C8kutrSc8GRRDd64cPjovG6';
	
	private $authtoken;
	private $refreshtoken;
	
	public function __construct($url = null){
		$this->ch = curl_init();
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION,true);
		curl_setopt( $this->ch, CURLOPT_HEADER,false);
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt( $this->ch, CURLOPT_HTTPAUTH,CURLAUTH_ANY);
	
		$this->authtoken = "";
		if(isset($url)) {
			$this->setUrl($url);
		}
	}
	
	public function index(){
		if(isset($_GET['code']) && isset($_GET['state'])){
			$code = isset($_GET['code']) ? $_GET['code']:'';
			$accounts_id = isset($_GET['state']) ? $_GET['state']:0;
			//var_dump($_GET);exit;
			$subdomain = '';
			$authConfig = array('code' 			=> $code,
								'client_id' 	=> self::CLIENT_ID,
								'client_secret' => self::CLIENT_SECRET,
								'redirect_uri' 	=> $REDIRECT_URI,
								'grant_type'    => "authorization_code"
								);
			$responseObj = $this->getAccessToken('http://www.googleapis.com/oauth2/v4/token', $authConfig);
			
			$accessToken = $responseObj->access_token;
			$refreshToken = '';
			if (isset($responseObj->refresh_token)) {
				$refreshToken = $responseObj->refresh_token;
			}
			
			if($accounts_id>0){
				$queryObj = $this->db->query("SELECT company_subdomain FROM accounts WHERE accounts_id = $accounts_id", array());
				if($queryObj){
					$subdomain = $queryObj->fetch(PDO::FETCH_OBJ)->company_subdomain;
				}
				
				$variables_id = 0;
				$value = array('code'=>$code, 'accessToken'=>$accessToken, 'refreshToken'=>$refreshToken, 'Large_Paper_Printer'=>'', 'Receipt_Paper_Printer'=>'', 'Label_Printer'=>'');
				
				$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'Google_CP'", array());
				if($varObj){
					$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
					$variables_id = $variablesData->variables_id;
					$value = $variablesData->value;
					if(!empty($value)){
						$value = unserialize($value);
						$value['code'] = $code;
						$value['accessToken'] = $accessToken;
						if($refreshToken !=''){
							$value['refreshToken'] = $refreshToken;
						}
					}
				}
				
				$value = serialize($value);
				$data = array('accounts_id'=>$accounts_id,
							'name'=>$this->db->checkCharLen('variables.name', 'Google_CP'),
							'value'=>$value,
							'last_updated'=> date('Y-m-d H:i:s'));
				if($variables_id==0){
					$variables_id = $this->db->insert('variables', $data);
					if($variables_id){
						$checkmsg = 'Ok';
					}
					else{
						$checkmsg = 'Google Cloud Print could not update.';
					}
				}
				else{
					$update = $this->db->update('variables', $data, $variables_id);
					if($update){
						$checkmsg = 'Ok';
					}
					else{
						$checkmsg = 'Google Cloud Print could not update.';
					}
				}
			}
			else{
				$checkmsg = 'There is no account ID found.';
			}
			
			if($checkmsg=='Ok'){
				if($refreshToken !=''){
					$redirectURI = 'http://'.$subdomain.'.'.OUR_DOMAINNAME.'/Integrations/Google_CP';
					return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURI\" />";
				}
				else{
					$redirectURI = 'http://'.$subdomain.'.'.OUR_DOMAINNAME.'/Integrations/Google_CP/revokeToken';
					return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURI\" />";
				}
			}
			else{
				return "<h3>$checkmsg</h3>
					<p>$subdomain</p>";
					
				$this->db->writeIntoLog($checkmsg);
			}
		}
		elseif(isset($_GET['sendPrinter'])){
			$POST = json_decode(file_get_contents('php://input'), true);
			$returnStr = '';
			$prod_cat_man = $_SESSION['prod_cat_man']??0;
			$accounts_id = $_SESSION['accounts_id']??0;
			$printerID = $POST['printerID']??'';
			$id = $POST['id']??'';
			$printType = $POST['printType']??'';
			$amount_due = $POST['amount_due']??0;
			
			$accessToken = $refreshToken = $Large_Paper_Printer = $LargeOrientation = $LargeMedSiz = $Receipt_Paper_Printer = $ReceiptOrientation = $ReceiptMedSiz = $Label_Printer = $LabelOrientation = $LabelMedSiz = '';
			$margins = array('top_microns'=>0, 'right_microns'=>0, 'bottom_microns'=>0, 'left_microns'=>0);
			if($accounts_id>0){
				$varObj = $this->db->query("SELECT variables_id, value FROM variables WHERE accounts_id = $accounts_id AND name = 'Google_CP' AND value !=''", array());
				if($varObj){
					$varOneRow = $varObj->fetch(PDO::FETCH_OBJ);
					$value = $varOneRow->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
						
						$this->setAuthToken($accessToken);
						$printers = $this->getPrinters();
						if(count($printers)==0 && $refreshToken !=''){
							$accessToken = $this->getAccessTokenByRefreshToken($refreshToken);
							if(!empty($accessToken)){
								$value['accessToken'] = $accessToken;
								$value = serialize($value);
								$data = array('accounts_id'=>$accounts_id,
											'name'=>$this->db->checkCharLen('variables.name', 'Google_CP'),
											'value'=>$value,
											'last_updated'=> date('Y-m-d H:i:s'));
								$update = $this->db->update('variables', $data, $varOneRow->variables_id);
								
								$this->setAuthToken($accessToken);						
								$printers = $this->getPrinters();
							}		
						}
						if(empty($printers)){
							$accessToken ='';
						}
					}
				}
			
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'label_printer' AND value !=''", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('top_margin', $value)){
							$margins['top_microns'] = round(floatval($value['top_margin'])*263.6);
						}
						if(array_key_exists('right_margin', $value)){
							$margins['right_microns'] = round(floatval($value['right_margin'])*263.6);
						}
						if(array_key_exists('bottom_margin', $value)){
							$margins['bottom_microns'] = round(floatval($value['bottom_margin'])*263.6);
						}
						if(array_key_exists('left_margin', $value)){
							$margins['left_microns'] = round(floatval($value['left_margin'])*263.6);
						}				
					}
				}
			}
			
			if($accessToken !=''){
				
				$Printing = new Printing($this->db);
					
				$printjobtitle = $contents = "Test";
				$barcodeType = array('product', 'IMEI', 'PO', 'Repairs', 'Customer', 'test');
				
				if(in_array($printType, array('large', 'small', 'olarge', 'osmall'))){
					$invoice_no = 0;
					$pos_id = $id;
					$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id AND pos_id = :pos_id", array('pos_id'=>$pos_id),1);
					if($posObj){
						$invoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
					}
					$printjobtitle = "Invoice #s$invoice_no";
					$fromPage = 'Invoices';
					if(in_array($printType, array('olarge', 'osmall'))){
						$fromPage = 'Orders';
						$printType = str_replace('o', '', $printType);
					}
					$contents = $Printing->invoicesInfo($pos_id, $printType, 1, $amount_due, $fromPage);			
				}
				elseif(in_array($printType,  array('LRepairs', 'SRepairs'))){
					$ticket_no = 0;
					$posObj = $this->db->query("SELECT ticket_no FROM repairs WHERE accounts_id = $accounts_id AND repairs_id = :repairs_id", array('repairs_id'=>$id),1);
					if($posObj){
						$ticket_no = $posObj->fetch(PDO::FETCH_OBJ)->ticket_no;
					}
					$printjobtitle = "Repair Ticket #t$ticket_no";
					if($printType=='LRepairs'){$printType = 'large';}
					elseif($printType=='SRepairs'){$printType = 'small';}
					
					$contents = $Printing->repairInvoicesInfo($id, $printType, 1, $amount_due);			
				}
				elseif(in_array($printType,  array('POInvoice', 'ITPOInvoice'))){
					$po_number = 0;
					$posObj = $this->db->query("SELECT po_number FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$id),1);
					if($posObj){
						$po_number = $posObj->fetch(PDO::FETCH_OBJ)->po_number;
					}
					if($printType=='ITPOInvoice'){
						$printjobtitle = "Inventory Transfer";
					}
					else{
						$printjobtitle = "Repair Ticket";
					}
					$printjobtitle .= " #p$po_number";
					$contents = $Printing->poInvoicesInfo($id, 1);
					$printType = 'large';
				}
				elseif(in_array($printType,  array('EODLarge', 'EODSmall'))){
					$printjobtitle = "End Day Report";
					if($printType=='EODLarge'){
						$printType = "large";
					}
					else{
						$printType = "small";
					}
					$printjobtitle .= " $id";
					$drawer = '';
					$contents = $Printing->endOfDayInfo($id, $printType, $drawer, 1);
				}
				elseif(in_array($printType,$barcodeType)){
					$titleField = 'sku';
					$sql = "SELECT $titleField FROM product WHERE accounts_id = $prod_cat_man AND product_id = :id";
					
					$printjobtitle = "Barcode Print - ";
					if($printType=='IMEI'){
						$titleField = 'item_number';
						$sql = "SELECT $titleField FROM item WHERE accounts_id = $accounts_id AND item_id = :id";
					}
					elseif($printType=='PO'){
						$printjobtitle .= 'p';
						$titleField = 'po_number';
						$sql = "SELECT $titleField FROM po WHERE accounts_id = $accounts_id AND po_number = :id";
					}
					elseif($printType=='Repairs'){
						$titleField = 'ticket_no';
						$sql = "SELECT $titleField FROM repairs WHERE accounts_id = $accounts_id AND repairs_id = :id";
					}
					elseif($printType=='Customer'){
						$printjobtitle .= 'Customer info of t';
						$titleField = 'ticket_no';
						$sql = "SELECT $titleField FROM repairs WHERE accounts_id = $accounts_id AND repairs_id = :id";
					}
					elseif($printType=='test'){
						$printjobtitle .= 'Barcode Testing';
						$titleField = '';
						$sql = "";
					}
					if(!empty($sql)){
						$pObj = $this->db->query($sql, array('id'=>$id),1);
						if($pObj){
							$printjobtitle .= $pObj->fetch(PDO::FETCH_OBJ)->$titleField;
						}
					}			
					
					$contents = '';
					if($printType=='PO'){
						$poObj = $this->db->query("SELECT po_id FROM po WHERE po_number = :id AND accounts_id = $accounts_id", array('id'=>$id));
						if($poObj){
							$po_id = $poObj->fetch(PDO::FETCH_OBJ)->po_id;		
							
							$sqlquery = "SELECT product_id, item_type, po_items_id, received_qty FROM po_items WHERE po_id = $po_id ORDER BY po_items_id ASC";
							$query = $this->db->query($sqlquery, array());
							if($query){
								while($row = $query->fetch(PDO::FETCH_OBJ)){
									$product_id = $row->product_id;
									$item_type = $row->item_type;
									$po_items_id = $row->po_items_id;
									$received_qty = $row->received_qty;
									if($received_qty<0){$received_qty *= -1;}
									if($item_type=='livestocks'){
										$sqlitem = "SELECT i.item_id FROM item i, po_cart_item pci WHERE pci.po_items_id = $po_items_id AND i.accounts_id = $accounts_id AND i.item_id = pci.item_id ORDER BY i.item_id ASC";
										$query1 = $this->db->query($sqlitem, array());
										if($query1){
											while($item_onerow=$query1->fetch(PDO::FETCH_OBJ)){
												if($item_onerow->item_id>0){
													$contents .= $Printing->labelsInfo($item_onerow->item_id, 'IMEI');
												}
											}
										}
									}
									else{				
										for($r=0; $r<$received_qty; $r++){
											if($product_id>0){
												$contents .= $Printing->labelsInfo($product_id, 'product');
											}
										}
									}			
								}
							}
						}
					}
					elseif($printType=='test'){
						$labelwidth = 57;
						$labelheight = 31;
						$units = 'mm';
						$top_margin = $right_margin = $bottom_margin = $left_margin = 0;
						$font_size = 'Regular';
						$orientation = 'Portrait';
						$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'label_printer'", array());
						if($varObj){
							$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
							if(!empty($value)){
								$value = unserialize($value);
								extract($value);
								if($label_size=='customSize'){
									if($units=='Inches'){
										$labelwidth = round(round($label_sizeWidth,2)*25.4);
										$labelheight = round(round($label_sizeHeight,2)*25.4);
									}
									else{
										$labelwidth = round($label_sizeWidth);
										$labelheight = round($label_sizeHeight);
									}
								}
								else{
									list($labelwidth, $labelheight) = explode('|', $label_size);
								}
							}
						}
						
						$labelwidth = round($labelwidth*3.7795275591);
						$labelheight = round($labelheight*3.7795275591);
						$top_margin = intval($top_margin)+1;
						$right_margin = intval($right_margin)+3;
						$bottom_margin = intval($bottom_margin);
						$left_margin = intval($left_margin)+1;
								
						if($top_margin !=0){$labelheight = $labelheight-$top_margin;}
						if($bottom_margin !=0){$labelheight = $labelheight-$bottom_margin;}
						if($right_margin !=0){$labelwidth = $labelwidth-$right_margin;}
						if($left_margin !=0){$labelwidth = $labelwidth-$left_margin;}
						
						$font_sizeOptions = array('Small'=>'11', 'Regular'=>'12', 'Large'=>'13');
						$fontsize = $font_sizeOptions[$font_size];
						$lineheight = $fontsize;
						$marginCSS = '';
						if($top_margin !=0 || $right_margin !=0 || $bottom_margin !=0 || $left_margin !=0)
							$marginCSS = 'margin:'.$top_margin.'px '.$right_margin.'px '.$bottom_margin.'px '.$left_margin.'px;';
						$printCSS = '@media print{@page {size:'.strtolower($orientation).';margin: 0px;}}';
						$contents .= '<!DOCTYPE html>
	<html>
	<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
									<title>'.$printjobtitle.'</title>
									<style type="text/css">
										body{font-family:Arial, sans-serif, Helvetica;margin:0; padding:0;background:#fff;color:#000;}
										'.$printCSS.'
									</style>
								</head>
								<body>
								<div style="width:'.$labelwidth.'px; height:'.$labelheight.'px;page-break-after: always;">
									<div style="text-align:justify;width:'.$labelwidth.'px; height:'.$labelheight.'px;border:1px solid #000;overflow:hidden; position:relative;font-size:'.$fontsize.'px;color:#000;line-height:'.$lineheight.'px; background:#fff;'.$marginCSS.'">        
										This is a sample label. Please make a test print and you should be able to see all 4 sides of the border around this text. If you do not then you need to increase the margin on any side you do not see when printed until you do see it.
									</div>
								</div>
							</body>
						</html>';
					}
					else{
						$contents .= $Printing->labelsInfo($id, $printType, 1);
					}
				}
				
				$orientation = $media_size = '';
				if($printType=='large'){
					$orientation = $LargeOrientation;
					$media_size = $LargeMedSiz;
				}
				elseif($printType=='small'){
					$orientation = $ReceiptOrientation;
					$media_size = $ReceiptMedSiz;
				}
				elseif(in_array($printType, $barcodeType)){
					$orientation = $LabelOrientation;
					$media_size = $LabelMedSiz;
				}
				
				if(empty($orientation)){$orientation = 'PORTRAIT';}
				if(empty($media_size)){$media_size = '210000||297000';}
				$mediaSizeData = explode('||', $media_size);
				$height_microns = intval($mediaSizeData[0]);
				$width_microns = intval($mediaSizeData[1]);
				if($height_microns==0 || $width_microns ==0){
					$height_microns = 210000;
					$width_microns = 297000;
				}
				
				$ticket = array('margins'=>$margins, 'orientation'=>$orientation, 'width_microns'=>$width_microns, 'height_microns'=>$height_microns, 'printType'=>$printType);
				
				$printerResponse = $this->sendPrintToPrinter($printerID, $printjobtitle, $contents, $ticket);
				if($printerResponse['status']){
					$_SESSION["jobId"] = $printerResponse['id'];
					$returnStr = 'Printing has been started.';
				}
				else{
					$returnStr = 'Printing failed.';
				}
			}	
			else{
				$returnStr = 'There has been an ERROR (NULL) please contact support';
				$this->db->writeIntoLog('There has been a ERROR (NULL) please contact support');
			}
			
			return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
		}
	}
	
	public function getAuth(){
		$REDIRECT_URI = 'http://'.OUR_DOMAINNAME.'/Google_CP';
		$segment4name = $GLOBALS['segment4name'];
		$redirectConfig = array('client_id'		=> self::CLIENT_ID,
								'redirect_uri' 	=> $REDIRECT_URI,
								'response_type' => 'code',
								'scope'         => 'http://www.googleapis.com/auth/cloudprint',
								'access_type'   => 'offline',
								'include_granted_scopes'=>'true',
								'state'			=> "$segment4name");
		$redirectURI = "http://accounts.google.com/o/oauth2/v2/auth?".http_build_query($redirectConfig);
		return "<meta http-equiv = \"refresh\" content = \"0; url = $redirectURI\" />";

	}
	
	public function setAuthToken($token) {
		$this->authtoken = $token;
	}
	
	public function getAccessTokenByRefreshToken($refreshToken) {
		$post_fields = array('refresh_token'	=> $refreshToken,
							'client_id' 	=> self::CLIENT_ID,
							'client_secret' => self::CLIENT_SECRET,
							"grant_type"    => "refresh_token");
		$responseObj =  $this->getAccessToken('http://www.googleapis.com/oauth2/v3/token', $post_fields);
		if(isset($responseObj->access_token))
			return $responseObj->access_token;
		else	
			return '';
	}
	
	public function getAccessToken($url,$post_fields){
		
		$this->setUrl($url);
		$this->setPostData($post_fields);
		$this->send();
		$response = json_decode($this->getResponse());
		return $response;
	}
	
	public function getPrinters(){
		
		if(empty($this->authtoken)) {
			throw new Exception("Please first login to Google");
		}
		
		$authheaders = array(
			"Authorization: Bearer " .$this->authtoken
		);
		
		$this->setUrl(self::PRINTERS_SEARCH_URL);
		$this->setHeaders($authheaders);
		$this->send();
		$responsedata = $this->getResponse();
		
		$printers = json_decode($responsedata);
		if(is_null($printers)){
			// We dont have printers so return balnk array
			return array();
		}
		else{
			// We have printers so returns printers as array
			return $this->parsePrinters($printers);
		}
	}
	
	public function sendPrintToPrinter($printerid, $printjobtitle, $contents, $ticket = '') {
		if(empty($this->authtoken)) {
			throw new Exception("Please first login to Google by calling loginToGoogle function");
		}
		
		if(empty($printerid)) {
			throw new Exception("Please provide printer ID");	
		}
		$orientation = 'portrait';
		$width_microns = 210000;
		$height_microns = 297000;
		$printType = 'large';
		$barcodeType = array('product', 'IMEI', 'PO', 'Repairs', 'Customer', 'test');
		$margins = array('top_microns'=>0, 'right_microns'=>0, 'bottom_microns'=>0, 'left_microns'=>0);
	
		if(!empty($ticket)){
			$margins = $ticket['margins'];
			$orientation = strtolower($ticket['orientation']);
			$width_microns = $ticket['width_microns'];
			$height_microns = $ticket['height_microns'];
			$printType = $ticket['printType'];
		}
		$contentType = 'text/html';
		
		if($orientation=='portrait'){$orientation = 0;}
		else{$orientation = 1;}
		
		$ticketInfo = $printInfo = array();
		$ticketInfo['version'] = '1.0';
		
		$printInfo['page_orientation'] = array('type'=>$orientation);
		if($printType != 'large'){
			$printInfo['margins'] = $margins;
		}
		$bigNumber = $width_microns;
		if($bigNumber<$height_microns){$bigNumber = $height_microns;}
		$printInfo['media_size'] = array('width_microns'=>$bigNumber, 'height_microns'=>$bigNumber);//$height_microns
		//$printInfo['media_size'] = array('width_microns'=>297000, 'height_microns'=>70000, 'is_continuous_feed'=>true);//For Testing
		
		if($printType == 'small'){		
			$printInfo['media_size'] = array('width_microns'=>$width_microns, 'height_microns'=>$height_microns, 'is_continuous_feed'=>true);//For Testing}		
		}
		
		$printInfo['fit_to_page'] = array('type'=>"NO_FITTING");
		//====================printInfo============//		
		$ticketInfo['print'] = $printInfo;
		//$this->db->writeIntoLog(json_encode($ticketInfo).$contents);
		
		$post_fields = array(				
			'printerid' => $printerid,
			'title' => $printjobtitle,
			'contentTransferEncoding' => 'base64',
			'content' => base64_encode($contents), // encode file content as base64
			'contentType' => $contentType,
			'ticket' => json_encode($ticketInfo)			
		);
		
		$authheaders = array(
			"Authorization: Bearer " . $this->authtoken
		);
		
		// Make http call for sending print Job
		$this->setUrl(self::PRINT_URL);
		$this->setPostData($post_fields);
		$this->setHeaders($authheaders);
		$this->send();
		$response = json_decode($this->getResponse());
		
		if(!empty($response) && $response->success=="1") {
			return array('status' =>true,'errorcode' =>'','errormessage'=>"", 'id' => $response->job->id);
		}
		else {			
			return array('status' =>false,'errorcode' =>$response->errorCode,'errormessage'=>$response->message);
		}
	}
	
    public function updatePrinterInfo($post_fields){
       
		// Prepare auth headers with auth token
        $authheaders = array(
            "Authorization: Bearer " .$this->authtoken
        );

		$this->setUrl('http://www.google.com/cloudprint/update');
		$this->setPostData($post_fields);
		$this->setHeaders($authheaders);
		$this->send();
		$response = json_decode($this->getResponse());
		
		if($response->success=="1") {
			// We have printers so returns printers as array
			return $response->message;
		}
		else{
			// We dont have printers so return balnk array
			return $response->message;
		}
    }
	
    public function printerInfo($printerid, $accessToken){
		$POST = json_decode(file_get_contents('php://input'), true);
		$json = false;
		if(isset($POST['printerId'])){$json = true;}
		$printerId = $POST['printerId']??$printerid;
		$accessToken = $POST['accessToken']??$accessToken;
	
		$this->setAuthToken($accessToken);
        $post_fields = array('printerid' => $printerid,
							//'use_cdd'=>true
							);
		
		// Prepare auth headers with auth token
        $authheaders = array(
            "Authorization: Bearer " .$this->authtoken
        );

		$this->setUrl('http://www.google.com/cloudprint/printer');
		$this->setPostData($post_fields);
		$this->setHeaders($authheaders);
		$this->send();
		$response = (array) json_decode($this->getResponse());
		//var_dump($response);
		//$this->db->writeIntoLog(json_encode($response));
		$orientation = $media_size = array();
		$dorientation = $dmedia_size = '';
		if(!empty($response) && array_key_exists('success', $response) && $response['success']) {
			// We have printers so returns printers as array
			$printersData = (array) $response['printers'];
			$printersData = $printersData[0]->capabilities->printer;
			if(isset($printersData->page_orientation)){
				$orientationData = $printersData->page_orientation->option;
				if(!empty($orientationData)){
					foreach($orientationData as $OrientationRow){
						$OrientationRow = (array) $OrientationRow;
						$orientation[] = $OrientationRow['type'];
						if(array_key_exists('is_default', $OrientationRow)){$dorientation = $OrientationRow['type'];}
					}
				}
			}
			if(isset($printersData->media_size)){
				$media_sizeData = $printersData->media_size->option;
				if(!empty($media_sizeData)){
					foreach($media_sizeData as $MedSizRow){
						$MedSizRow = (array) $MedSizRow;
						$MedSizHeight = $MedSizRow['height_microns'];
						$MedSizWidth = $MedSizRow['width_microns'];
						$MedSizValue = "$MedSizHeight||$MedSizWidth";
						$media_size[$MedSizValue] = "$MedSizRow[custom_display_name] ($MedSizWidth x $MedSizHeight)";
						if(array_key_exists('is_default', $MedSizRow)){$dmedia_size = $MedSizValue;}
					}
				}
			}
		}
		$returnData = array('orientation'=>$orientation, 'dorientation'=>$dorientation, 'media_size'=>$media_size, 'dmedia_size'=>$dmedia_size);
		if($json){
			return json_encode($returnData);
		}
		else{
			return $returnData;
		}
    }
	
	public function jobStatus($printerid, $jobid){
        // Prepare auth headers with auth token
        $authheaders = array(
            "Authorization: Bearer " .$this->authtoken
        );
		$post_fields = array(				
			'printerid' => $printerid
		);
        // Make http call for sending print Job
        $this->setUrl(self::JOBS_URL);
        $this->setHeaders($authheaders);
        $this->setPostData($post_fields);
		$this->send();
        $responsedata = json_decode($this->getResponse());

        foreach ($responsedata->jobs as $job)
            if ($job->id == $jobid)
                return $job;

        return 'UNKNOWN';
    }
	
	public function parsePrinters($jsonobj){
		
		$printers = array();
		
		if (isset($jsonobj->printers)) {
			foreach ($jsonobj->printers as $gcpprinter) {
				$printers[] = array('id' =>$gcpprinter->id,'name' =>$gcpprinter->name,'displayName' =>$gcpprinter->displayName,
						    'ownerName' => @$gcpprinter->ownerName, 'connectionStatus' => $gcpprinter->connectionStatus,
						    );
			}
		}
		return $printers;
	}
	
	public function setUrl($url) {
		curl_setopt( $this->ch, CURLOPT_URL, $url );
	}
	
	public function setPostData( $params ) {		
		curl_setopt( $this->ch, CURLOPT_POST, true );
		curl_setopt ( $this->ch, CURLOPT_POSTFIELDS,$params);
	}
	
	public function setHeaders($headers) {
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
	}
	
	public function send() {
		$this->httpResponse = curl_exec( $this->ch );
	}
	
	public function getResponse() {
		return $this->httpResponse;
	}
	
	public function __destruct() {
		curl_close($this->ch);
	}
}
?>