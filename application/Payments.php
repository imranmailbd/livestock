<?php
class Payments{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	public function loadPOSPayment($frompage, $pos_id){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']??$pos_id);
		$allowed = array();
		if(isset($_SESSION["allowed"])){$allowed = $_SESSION["allowed"];}
		//$this->db->writeIntoLog($allowed);
		$todaytime = strtotime(date('Y-m-d'));
		$tableData = array();
		if($pos_id>0){
			$ppSql = "SELECT * FROM pos_payment WHERE pos_id = :pos_id AND payment_method != 'Change' ORDER BY pos_payment_id ASC";
			$paymentObj = $this->db->query($ppSql, array('pos_id'=>$pos_id),1);
			if($paymentObj){
				$i=0;
				while($row = $paymentObj->fetch(PDO::FETCH_OBJ)){
					$trusticon = $squareYN = 0;
					$datadatetime = strtotime(date('Y-m-d', strtotime($row->payment_datetime)));
					if($datadatetime==$todaytime || empty($allowed)){
						if(strtoupper($row->payment_method)=='SQUAREUP'){$squareYN = 1;}
						$trusticon++;						
					}
					$colSpan = 7;
					if($frompage=='POS'){$colSpan = 5;}
					elseif($frompage=='Repairs'){$colSpan = 6;}
					
					$tableData[] = array('pos_payment_id'=> intval($row->pos_payment_id), 'colSpan'=>$colSpan, 'payment_amount'=>round($row->payment_amount,2), 'payment_method'=>$row->payment_method, 'payment_datetime'=>$row->payment_datetime, 'drawer'=>$row->drawer, 'trusticon'=>$trusticon, 'squareYN'=>$squareYN);
				}
			}
		}
		return $tableData;
	}
	
    public function addPOSPayment($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$pos_id = intval($POST['pos_id']??0);
		
		if($frompage=='POS' && $pos_id == 0){
			$pos_id = $_SESSION["pos_id"]??0;
			if($pos_id==0){					
				$employee_id = $_SESSION['employee_id']??0;
				if($employee_id == 0 ){$employee_id = $user_id;}					
				$customer_id = $_SESSION["customer_id"]??0;
			
				//=============collect user last new invoice no================//
				if($customer_id==0 || $customer_id==''){
					$customerObj = $this->db->query("SELECT default_customer FROM accounts WHERE accounts_id = $accounts_id", array());
					if($customerObj){
						$customer_id = $customerObj->fetch(PDO::FETCH_OBJ)->default_customer;
					}
				}
				$taxes_name1 = $taxes_name2 = '';
				$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
				if(isset($_SESSION["taxes_id1"])){
					$taxes_id1 = $_SESSION["taxes_id1"];
					if($taxes_id1>0){
						$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id1 ORDER BY taxes_id DESC LIMIT 0, 1", array());
						if($taxesObj){
							$taxes_name1 = $taxesObj[0]['taxes_name'];
							$taxes_percentage1 = $taxesObj[0]['taxes_percentage'];
							$tax_inclusive1 = $taxesObj[0]['tax_inclusive'];
						}				
					}
				}
				if(isset($_SESSION["taxes_id2"])){
					$taxes_id2 = $_SESSION["taxes_id2"];
					if($taxes_id2>0){
						$taxesObj = $this->db->querypagination("SELECT * FROM taxes WHERE taxes_id = $taxes_id2 ORDER BY taxes_id DESC LIMIT 0, 1", array());
						if($taxesObj){
							$taxes_name2 = $taxesObj[0]['taxes_name'];
							$taxes_percentage2 = $taxesObj[0]['taxes_percentage'];
							$tax_inclusive2 = $taxesObj[0]['tax_inclusive'];
						}				
					}
				}
				
				if($taxes_name1 =='' || $taxes_name2 == ''){
					$taxesObj = $this->db->query("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND taxes_publish = 1 ORDER BY taxes_name ASC", array());
					if($taxesObj){
						$no_of_default_rows = 0;
						while($taxesonerow = $taxesObj->fetch(PDO::FETCH_OBJ)){
							
							$staxes_name = $taxesonerow->taxes_name;
							$staxes_percentage = $taxesonerow->taxes_percentage;							
							$default_tax = $taxesonerow->default_tax;
							$tax_inclusive = $taxesonerow->tax_inclusive;
							if($taxes_name1==''){
								$taxes_name1 = $staxes_name;
								$taxes_percentage1 = $staxes_percentage;
								$tax_inclusive1 = $tax_inclusive;
							}
							
							if($default_tax>0){
								$no_of_default_rows++;
								if($no_of_default_rows==1 && $taxes_name1==''){
									$taxes_name1 = $staxes_name;
									$taxes_percentage1 = $staxes_percentage;
									$tax_inclusive1 = $tax_inclusive;
								}
								
								if($no_of_default_rows==2 && $taxes_name2==''){
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
				$_SESSION["pos_id"] = $pos_id;
			}
		}
		$action = '';
		$sqlquery = "SELECT invoice_no, pos_type, order_status FROM pos WHERE pos_id = $pos_id";
		$queryObj = $this->db->query($sqlquery, array());
		if($queryObj){
			$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
			$invoice_no = $posRow->invoice_no;
			$pos_type = $posRow->pos_type;
			$order_status = $posRow->order_status;
			if(($invoice_no>0 && $frompage=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
				$action = 'reload';
			}
		}
		
		if(empty($action)){
			$payment_method = $POST['payment_method']??'';
			$payment_amount = floatval($POST['payment_amount']??0);			
			$drawer = $POST['drawer']??'';
			
			$posObj = $this->db->query("SELECT pos_id FROM pos WHERE pos_id = :pos_id AND accounts_id = $accounts_id", array('pos_id'=>$pos_id),1);
			if($posObj){
				$payment_method = $this->db->checkCharLen('pos_payment.payment_method', $payment_method);
				$drawer = $this->db->checkCharLen('pos_payment.drawer', $drawer);
				$ppData = array('pos_id' => $pos_id,
								'payment_method' => $payment_method,
								'payment_amount' => round($payment_amount,2),
								'payment_datetime' => date('Y-m-d H:i:s'),	
								'user_id' => $user_id,
								'more_details' => '',
								'drawer' => $drawer);
				$pos_payment_id = $this->db->insert('pos_payment', $ppData);
				if($pos_payment_id){
					if($frompage =='Repairs'){									
						$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
						if($repairsObj){
							$repairs_id = $repairsObj->fetch(PDO::FETCH_OBJ)->repairs_id;
							$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
						}
					}
					$action = 'Add';
				}
			}
		}
		$paymentData = array();
		if($action == 'Add' && $pos_id>0){
			$paymentData = $this->loadPOSPayment($frompage, $pos_id);
		}
		return json_encode(array('login'=>'', 'action'=>$action, 'paymentData'=>$paymentData));
	}
	
	public function removePOSPayment($frompage){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$pos_id = intval($POST['pos_id']??0);
		$pos_payment_id = intval($POST['pos_payment_id']??0);
		$action = '';
		
		$sqlquery = "SELECT invoice_no, pos_type, order_status FROM pos WHERE pos_id = $pos_id";
		$queryObj = $this->db->query($sqlquery, array());
		if($queryObj){
			$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
			$invoice_no = $posRow->invoice_no;
			$pos_type = $posRow->pos_type;
			$order_status = $posRow->order_status;
			if(($invoice_no>0 && $frompage=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
				$action = 'reload';
			}
		}
		
		if(empty($action)){
			$payment_method = $payment_datetime = '';
			$payment_amount = 0;
			$dateformat = $_SESSION["dateformat"]??'m/d/Y';
			$timeformat = $_SESSION["timeformat"]??'12 hour';
			$currency = $_SESSION["currency"]??'৳';
			$sqlquery = "SELECT payment_method, payment_amount, payment_datetime FROM pos_payment WHERE pos_payment_id = $pos_payment_id";
			$ppObj = $this->db->query($sqlquery, array());
			if($ppObj){
				$ppOneRow = $ppObj->fetch(PDO::FETCH_OBJ);
				$payment_method = $ppOneRow->payment_method;
				$payment_amount = $ppOneRow->payment_amount;
				if($timeformat=='24 hour'){$payment_datetime =  date($dateformat.' H:i', strtotime($ppOneRow->payment_datetime));}
				else{$payment_datetime =  date($dateformat.' g:i a', strtotime($ppOneRow->payment_datetime));}
			}
			$deletepos_payment = $this->db->delete('pos_payment', 'pos_payment_id', $pos_payment_id);
			if($deletepos_payment){
				$description = $this->db->translate('DELETE a PAYMENT from');
				if($frompage =='Repairs'){
					$description .= ' Repairs';
					$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
					if($repairsObj){
						$repairs_id = $repairsObj->fetch(PDO::FETCH_OBJ)->repairs_id;
						$this->db->update('repairs', array('last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
					}
				}
				elseif($frompage =='Orders'){$description .= ' Orders';}
				elseif($frompage =='POS'){$description .= ' POS';}
				$description .= " ($payment_datetime $payment_method $currency$payment_amount)";
				$moreInfo = array('table'=>'pos_payment', 'id'=>$pos_payment_id, 'description'=>$description);
				$teData = array();
				$teData['created_on'] = date('Y-m-d H:i:s');
				$teData['accounts_id'] = $_SESSION["accounts_id"];
				$teData['user_id'] = $_SESSION["user_id"];
				$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
				$teData['record_id'] = $pos_id;
				$teData['details'] = json_encode(array('changed'=>array(), 'moreInfo'=>$moreInfo));
				$this->db->insert('track_edits', $teData);
				
				$action = 'Removed';
			}
		}
		$paymentData = array();
		if($action == 'Removed' && $pos_id>0){
			$paymentData = $this->loadPOSPayment($frompage, $pos_id);
		}
		return json_encode(array('login'=>'', 'action'=>$action, 'paymentData'=>$paymentData));
	}
	
}
?>