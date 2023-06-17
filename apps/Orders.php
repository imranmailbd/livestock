<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Orders{
	protected $db;
	private int $page, $totalRows, $employee_id, $pos_id;
	private string $sorting_type, $view_type, $keyword_search, $history_type;
	private array $vieTypOpt, $empIdOpt, $actFeeTitOpt;
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
	private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$ssorting_type = $this->sorting_type;
		$sview_type = $this->view_type;
		$semployee_id = $this->employee_id;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Invoices";
		$_SESSION["list_filters"] = array('ssorting_type'=>$ssorting_type, 'semployee_id'=>$semployee_id, 'keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		if($semployee_id >0){
			$filterSql .= " AND employee_id = :employee_id";
			$bindData['employee_id'] = $semployee_id;
		}
		if($sview_type !='All'){
			if($sview_type==1){$filterSql .= " AND status NOT IN ('Quotes')";}
			else{				
				$filterSql .= " AND status = :sview_type";
				$bindData['sview_type'] = $sview_type;
			}
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			$filterSql .= " AND (invoice_no LIKE CONCAT('%', :invoice_no, '%') OR customer_id in ( SELECT customers_id FROM customers WHERE accounts_id = $prod_cat_man";
			$bindData['invoice_no'] = str_replace('o', '', strtolower($keyword_search));
					
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
			$filterSql .= "))";
		}

		$totalRows = 0;
		$queryObj = $this->db->query("SELECT COUNT(pos_id) AS totalrows FROM pos WHERE accounts_id = $accounts_id $filterSql AND pos_publish = 1 AND pos_type = 'Order' AND order_status =1", $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}

		$strextra ="SELECT employee_id FROM pos WHERE accounts_id = $accounts_id $filterSql AND pos_publish = 1 AND pos_type = 'Order' AND order_status =1 GROUP BY employee_id";
		$query = $this->db->query($strextra, $bindData);
		$empIds = array();
		if($query){
			while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
				$empIds[$oneRow->employee_id] = '';
			}
		}
		
		$empIdOpt = array();
		if(!empty($empIds)){
			$salesmanObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id IN (".implode(', ', array_keys($empIds)).")", array());
			if($salesmanObj){
				while($salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ)){							
					$optlabel = trim(stripslashes("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
					$empIdOpt[$salesmanRow->user_id] = $optlabel;
				}
			}
		}
		
		$vieTypOpt = array();
		
		$vieTypOpts = array('Waiting on Customer');
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'orderStatuses'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('order_statuses', $value)){
					$vieTypOpts = explode('||', $value['order_statuses']);
				}
			}
		}
		
		if(!empty($vieTypOpts)){
			foreach($vieTypOpts as $optValue){
				if(!empty($optValue))
					$vieTypOpt[$optValue] = stripslashes($optValue);
			}					
		}

		$this->totalRows = $totalRows;
		$this->vieTypOpt = $vieTypOpt;
		$this->empIdOpt = $empIdOpt;
	}
	
    private function loadTableRows(){
		
		if(!in_array($GLOBALS['segment3name'], array('lists', 'AJgetPage', 'edit'))){
			$this->db->writeIntoLog("Call from: $GLOBALS[segment2name]/$GLOBALS[segment3name]");
		}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"]??'auto';
		$Common = new Common($this->db);
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$ssorting_type = $this->sorting_type;
		$semployee_id = $this->employee_id;
		$sview_type = $this->view_type;
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'pos.sales_datetime DESC, pos.invoice_no DESC', 
								1=>'pos.sales_datetime DESC', 
								2=>'pos.invoice_no DESC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$filterSql = "";
		$bindData = array();
		if($semployee_id >0){
			$filterSql .= " AND pos.employee_id = :employee_id";
			$bindData['employee_id'] = $semployee_id;
		}
		if($sview_type !='All'){
			if($sview_type==1){$filterSql .= " AND pos.status NOT IN ('Quotes')";}
			else{				
				$filterSql .= " AND pos.status = :sview_type";
				$bindData['sview_type'] = $sview_type;
			}
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			$filterSql .= " AND (pos.invoice_no LIKE CONCAT('%', :invoice_no, '%') OR pos.customer_id in ( SELECT customers_id FROM customers WHERE accounts_id = $prod_cat_man";
			$bindData['invoice_no'] = str_replace('o', '', strtolower($keyword_search));
					
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
			$filterSql .= "))";
		}
		
		$sqlquery = "SELECT pos.*, SUM(CASE WHEN pos_cart.taxable >0 AND pos_cart.discount_is_percent>0 THEN (pos_cart.sales_price*pos_cart.qty)-(pos_cart.sales_price*pos_cart.qty*pos_cart.discount/100) WHEN pos_cart.taxable>0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.qty)-(pos_cart.qty*pos_cart.discount) ELSE 0 END) AS taxableTotal, 
		SUM(CASE WHEN pos_cart.taxable = 0 AND pos_cart.discount_is_percent >0 THEN (pos_cart.sales_price*pos_cart.qty)-(pos_cart.sales_price*pos_cart.qty*pos_cart.discount/100) WHEN pos_cart.taxable=0 AND pos_cart.discount_is_percent=0 THEN (pos_cart.sales_price*pos_cart.qty)-(pos_cart.qty*pos_cart.discount) ELSE 0 END) AS nonTaxableTotal 
		FROM pos LEFT JOIN pos_cart ON (pos.pos_id = pos_cart.pos_id) WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_type = 'Order' AND pos.order_status=1 
		$filterSql GROUP BY pos.pos_id";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			$customersId = $salesmanId = $customersData = $salesmanData =  $statusData =$posIds = array();
			foreach($query as $oneRow){
				if(!in_array($oneRow['customer_id'], $customersId)){
					array_push($customersId,$oneRow['customer_id']);
				}
				if(!in_array($oneRow['employee_id'], $salesmanId)){
					array_push($salesmanId, $oneRow['employee_id']);
				}
				if(empty($statusData) || !in_array($oneRow['status'], $statusData)){$statusData[] = $oneRow['status'];}
				$posIds[$oneRow['pos_id']] = 0;
			}					
			
			if(!empty($customersId)){
				$customersObj = $this->db->query("SELECT customers_id, company, first_name, last_name FROM customers WHERE customers_id IN (".implode(', ', $customersId).")", array());
				if($customersObj){
					while($customersrow = $customersObj->fetch(PDO::FETCH_OBJ)){							
						$name = trim(stripslashes("$customersrow->first_name $customersrow->last_name"));
						if(!empty($customersrow->company)){$name = "$customersrow->company, ".$name;}
						$customersData[$customersrow->customers_id] = $name;
					}
				}
			}					
			
			if(!empty($salesmanId)){
				$salesmanObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id IN (".implode(', ', $salesmanId).")", array());
				if($salesmanObj){
					while($salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ)){							
						$salesmanData[$salesmanRow->user_id] = trim(stripslashes("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
					}
				}
			}
			
			if(!empty($statusData)){
				$order_statusesarray = $statusColors = array();
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'orderStatuses'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('order_statuses', $value)){
							$order_statusesarray = explode('||',$value['order_statuses']);
						}
						if(array_key_exists('status_colors', $value)){
							$statusColors = explode('||',$value['status_colors']);
						}
					}
				}				

				if(count($order_statusesarray)>0){
					$c = 1;
					foreach($order_statusesarray as $oneorder_statuses){
						$oneorder_statuses = stripslashes($oneorder_statuses);
						if($oneorder_statuses !=''){
							$bgcolor = '#FFFFFF';
							if(!empty($statusColors) && array_key_exists($c,$statusColors)){
								$bgcolor = $statusColors[$c];
							}
							$color = '#FFFFFF';
							if(strtoupper($bgcolor)=='#FFFFFF'){$color = '#000000';}
							if(!in_array($oneorder_statuses, $statusColors)){
								$statusColors[$oneorder_statuses] = " style=\"background:$bgcolor; color:$color; padding:2px 8px;\"";
							}
						}
						$c++;
					}
				}
				if(in_array('Canceled', $statusData)){
					$statusColors['Canceled'] = " style=\"background:#FFFFFF; color:#000000;padding:2px 8px;\"";
				}
				if(in_array('New', $statusData)){
					$statusColors['New'] = " style=\"background:#5cb85c; color:#FFFFFF;padding:2px 8px;\"";
				}
				if(in_array('Quotes', $statusData)){
					$statusColors['Quotes'] = " style=\"background:#FFFFFF; color:#000000;padding:2px 8px;\"";
				}
			}
			
			if(!empty($posIds)){
				$posPayObj = $this->db->query("SELECT pos_id, SUM(payment_amount) AS paidAmount FROM pos_payment WHERE pos_id IN (".implode(', ', array_keys($posIds)).") GROUP BY pos_id", array());
				if($posPayObj){
					while($posPayRow = $posPayObj->fetch(PDO::FETCH_OBJ)){							
						$posIds[$posPayRow->pos_id] = round($posPayRow->paidAmount,2);
					}
				}
			}
			
			foreach($query as $oneRow){
			
				$pos_id = $oneRow['pos_id'];
				$invoice_no = $oneRow['invoice_no'];
				if($invoice_no ==0){
					$invoice_no = $oneRow['pos_id'];
				}
				$customer_id = $oneRow['customer_id'];
				$customername = $customersData[$customer_id]??'&nbsp;';
				
				$date =  date('Y-m-d', strtotime($oneRow['sales_datetime']));
				
				$employee_id = $oneRow['employee_id'];
				$salesname = $salesmanData[$employee_id]??'&nbsp;';
				$status = $oneRow['status'];
				if(!empty($statusColors) && array_key_exists($status, $statusColors)){
					$status = "<span$statusColors[$status]>$status</span>";
				}
				$taxableTotal = round($oneRow['taxableTotal'],2);
				$nonTaxableTotal = round($oneRow['nonTaxableTotal'],2);
				
				$taxes_total1 = $Common->calculateTax($taxableTotal, $oneRow['taxes_percentage1'], $oneRow['tax_inclusive1']);
				$taxes_total2 = $Common->calculateTax($taxableTotal, $oneRow['taxes_percentage2'], $oneRow['tax_inclusive2']);

				$tax_inclusive1 = $oneRow['tax_inclusive1'];
				$tax_inclusive2 = $oneRow['tax_inclusive2'];
					
				$taxestotal = $taxes_total1+$taxes_total2;
				
				$grand_total = $taxableTotal+$taxestotal+$nonTaxableTotal;
				if($tax_inclusive1>0){
					$grand_total -= $taxes_total1;
				}
				if($tax_inclusive2>0){
					$grand_total -= $taxes_total2;
				}
				
				$paidAmount = $posIds[$pos_id]??0;
				$dueAmount = $grand_total-$paidAmount;
				
				$tabledata[] = array($invoice_no, $date, "o$invoice_no", $customername, $salesname, $status, round($taxableTotal,2), round($taxestotal,2), round($nonTaxableTotal,2), round($grand_total,2), round($paidAmount,2), round($dueAmount,2));
			}
		}
		
		return $tabledata;
    }
	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$ssorting_type = $POST['ssorting_type']??0;
		$semployee_id = intval($POST['semployee_id']??0);
		if(empty($semployee_id)){$semployee_id = 0;}
		$sview_type = $POST['sview_type']??'';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->sorting_type = $ssorting_type;
		$this->employee_id = $semployee_id;
		$this->view_type = $sview_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['vieTypOpt'] = $this->vieTypOpt;
			$jsonResponse['empIdOpt'] = $this->empIdOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_add_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;

		$customFields = 0;
		$queryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers'", array());
		if($queryObj){
			$customFields = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$jsonResponse['customFields'] = $customFields;
		
		$salesman_id = $_SESSION["user_id"]??0;
		$jsonResponse['salesman_id'] = $salesman_id;
		
		$salManOpt = array();
		$sqlquery = "SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id AND (user_publish =1 OR (user_publish =0 AND user_id= :user_id)) ORDER BY user_first_name asc, user_last_name asc";
		$query = $this->db->query($sqlquery, array('user_id'=>$salesman_id));
		if($query){
			while($useronerow = $query->fetch(PDO::FETCH_OBJ)){
				$user_id = $useronerow->user_id;
				$optLabel = stripslashes(trim("$useronerow->user_first_name $useronerow->user_last_name"));
				$salManOpt[$user_id] = $optLabel;
			}
		}
        $jsonResponse['salManOpt'] = $salManOpt;
		
		return json_encode($jsonResponse);
	}
	
	public function add(){}
	
	public function AJ_edit_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$user_id = $_SESSION['user_id']??0;
		$invoice_no = $POST['invoice_no'];
		
		$jsonResponse['invoice_no'] = $invoice_no;
		
		$posObj = $this->db->query("SELECT * FROM pos WHERE invoice_no = :invoice_no AND accounts_id = $accounts_id AND pos_type = 'Order'", array('invoice_no'=>$invoice_no),1);
		if($posObj){
			$Common = new Common($this->db);
			$pos_onerow = $posObj->fetch(PDO::FETCH_OBJ);
			$pos_id = $pos_onerow->pos_id;
			$order_status = $pos_onerow->order_status;
			$jsonResponse['order_status'] = $order_status;
			
			$order_statusesarray = $statusColors = array();
			$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'orderStatuses'", array());
			if($varObj){
				$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$value = unserialize($value);
					if(array_key_exists('order_statuses', $value)){
						$order_statusesarray = explode('||', $value['order_statuses']);
						if(!empty($order_statusesarray)){
							$p = 0;
							foreach($order_statusesarray as $oneStatus){
								if(in_array($oneStatus, array('', 'New', 'Quotes'))){unset($order_statusesarray[$p]);}
								$p++;
							}
						}
						array_splice( $order_statusesarray, 0, 0, array('New', 'Quotes'));
					}
					if(array_key_exists('status_colors', $value)){
						$statusColors = explode('||', $value['status_colors']);
					}
				}
			}
			$jsonResponse['statusColors'] = $statusColors;//
			$status = $pos_onerow->status;
			$jsonResponse['status'] = $status;
			
			$currentStatusBG = '#5cb85c';
			$OrdStaOpt = array();
			if($status=='Quotes'){
				$bgcolor = '#FFFFFF';
				if(!empty($statusColors) && array_key_exists(1, $statusColors)){$bgcolor = $statusColors[1];}
				$currentStatusBG = $bgcolor;
				$color = '#FFFFFF';
				if(strtoupper($bgcolor)=='#FFFFFF'){$color = '#000000';}
				$OrdStaOpt[] = array('Quotes', $bgcolor, $color);
			}
						
			$bgcolor = '#5cb85c';
			if(!empty($statusColors) && array_key_exists(0,$statusColors)){$bgcolor = $statusColors[0];}
			$color = '#FFFFFF';
			if(strtoupper($bgcolor)=='#FFFFFF'){$color = '#000000';}
			if($status=='New'){$currentStatusBG = $bgcolor;}
			$OrdStaOpt[] = array('New', $bgcolor, $color);
			
			if(!empty($order_statusesarray)){
				$c = 0;
				foreach($order_statusesarray as $oneOrdStatus){
					$oneOrdStatus = stripslashes(trim((string) $oneOrdStatus));
					if($oneOrdStatus !='' && !in_array($oneOrdStatus, array('New', 'Finished'))){
						$bgcolor = '#FFFFFF';
						if(!empty($statusColors) && array_key_exists($c,$statusColors)){
							$bgcolor = $statusColors[$c];
						}
						$color = '#FFFFFF';
						if(strtoupper($bgcolor)=='#FFFFFF'){$color = '#000000';}
						if($status==$oneOrdStatus){
							$currentStatusBG = $bgcolor;
						}
						$OrdStaOpt[] = array($oneOrdStatus, $bgcolor, $color);
					}
					$c++;
				}
			}
			
			$color = '#FFFFFF';			
			if(strtoupper($currentStatusBG)=='#FFFFFF'){$color = '#000000';}
			$jsonResponse['color'] = $color;
			$jsonResponse['currentStatusBG'] = $currentStatusBG;
			$jsonResponse['OrdStaOpt'] = $OrdStaOpt;
			$jsonResponse['pos_id'] = $pos_id;
			
			$customer_id = $pos_onerow->customer_id;
			$customername = $customeremail = $offers_email = $customerphone = $customeraddress = $editcustomers = '';
			$available_credit = 0;
			$customerObj = $this->db->query("SELECT customers_id, first_name, last_name, email, contact_no, credit_limit FROM customers WHERE customers_id = $customer_id", array());
			if($customerObj){
				$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);
				$customer_id = $customerrow->customers_id;
				$first_name = $customerrow->first_name;
				$last_name = $customerrow->last_name;
				$customername = trim(stripslashes($first_name).' '.stripslashes($last_name));
				$customeremail = $customerrow->email;
				$customerphone = $customerrow->contact_no;
				$credit_limit = $customerrow->credit_limit;
				if($credit_limit>0){
					$availCreditData = $Common->calAvailCr($customer_id, $credit_limit, 1);
					if(array_key_exists('available_credit', $availCreditData)){
						$available_credit = $availCreditData['available_credit'];
					}
				}
			}
			$customFields = 0;
			$queryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers'", array());
			if($queryObj){
				$customFields = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			
			$jsonResponse['customeremail'] = $customeremail;
			$jsonResponse['customer_id'] = $customer_id;
			$jsonResponse['customFields'] = $customFields;
			$jsonResponse['customername'] = $customername;
			$jsonResponse['customerphone'] = $customerphone;
			$jsonResponse['available_credit'] = $available_credit;

			$employee_id = $pos_onerow->employee_id;
			$jsonResponse['employee_id'] = $employee_id;
			$salesman_name = '';
			if($employee_id>0){
				$salesmanObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $employee_id", array());
				if($salesmanObj){
					$salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ);
					$salesman_name = trim(stripslashes("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
				}
			}
			$jsonResponse['salesman_name'] = $salesman_name;
			
			$jsonResponse['sales_datetime'] = $pos_onerow->sales_datetime;

			$Carts = new Carts($this->db);
			$jsonResponse['cartsData'] = $Carts->loadCartData('Orders', $pos_id);

			$pCustomFields = 0;
			$queryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'product'", array());
			if($queryObj){
				$pCustomFields = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			$jsonResponse['pCustomFields'] = $pCustomFields;
			
			$pPermission = 1;
			if(!empty($_SESSION["allowed"]) && array_key_exists(7, $_SESSION["allowed"]) && in_array('cnanp', $_SESSION["allowed"][7])) {
				$pPermission = 0;
			}
			$jsonResponse['pPermission'] = $pPermission;
			
			$subPermission = array();
			if(!empty($_SESSION["allowed"]) && array_key_exists(7, $_SESSION["allowed"])) {
				$subPermission = $_SESSION["allowed"][7];
			}
			$jsonResponse['subPermission'] = $subPermission;
			
			$no_of_result_rows = $no_of_default_rows = 0;
			$option1 = $option2 = array();
			$option1Val = $option2Val = 0;
			$taxes_name1 = $pos_onerow->taxes_name1;
			$taxes_percentage1 = $pos_onerow->taxes_percentage1;
			$tax_inclusive1 = $pos_onerow->tax_inclusive1;
			$taxes_name2 = $pos_onerow->taxes_name2;
			$taxes_percentage2 = $pos_onerow->taxes_percentage2;
			$tax_inclusive2 = $pos_onerow->tax_inclusive2;
			$display = '';
			$taxesObj = $this->db->query("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND taxes_publish = 1 ORDER BY taxes_name ASC", array());
			if($taxesObj){
				$no_of_result_rows = $taxesObj->rowCount();
				while($taxesonerow = $taxesObj->fetch(PDO::FETCH_OBJ)){                                            
					$taxes_id = $taxesonerow->taxes_id;
					$staxes_name = $taxesonerow->taxes_name;
					$staxes_percentage = $taxesonerow->taxes_percentage;
					$stax_inclusive = $taxesonerow->tax_inclusive;
					
					$default_tax = $taxesonerow->default_tax;
					if($default_tax>0){
						$no_of_default_rows++;
					}
					$selected1 = '';
					$selected2 = '';
					if($taxes_name1==''){
						$taxes_name1 = $staxes_name;
						$taxes_percentage1 = $staxes_percentage;
						$tax_inclusive1 = $stax_inclusive;
						if($no_of_default_rows==1){
							$taxes_name1 = $staxes_name;
							$taxes_percentage1 = $staxes_percentage;
							$option1Val = $taxes_id;
						}
						
						if($no_of_default_rows==2){
							$taxes_name2 = $staxes_name;
							$taxes_percentage2 = $staxes_percentage;
							$tax_inclusive2 = $stax_inclusive;
							$option2Val = $taxes_id;
						}
					}
					else{
						if(strcmp($taxes_name1, $staxes_name)==0){
							$option1Val = $taxes_id;
						}
						if(strcmp($taxes_name2, $staxes_name)==0){
							$option2Val = $taxes_id;
						}
					}
					$tiStr = '';
					if($stax_inclusive>0){$tiStr = ' Inclusive';}
					
					$option1[$taxes_id] = "$staxes_name ($staxes_percentage%$tiStr)";
					$option2[$taxes_id] = "$staxes_name ($staxes_percentage%$tiStr)";
				}
			}
			else{
				$display = 'display:none';
			}
			$jsonResponse['display'] = $display;
			$jsonResponse['no_of_result_rows'] = $no_of_result_rows;
			$jsonResponse['taxes_name1'] = $taxes_name1;
			$jsonResponse['taxes_percentage1'] = $taxes_percentage1;
			$jsonResponse['tax_inclusive1'] = $tax_inclusive1;
			$jsonResponse['taxes_name2'] = $taxes_name2;
			$jsonResponse['taxes_percentage2'] = $taxes_percentage2;
			$jsonResponse['tax_inclusive2'] = $tax_inclusive2;

			$jsonResponse['no_of_default_rows'] = $no_of_default_rows;
			$jsonResponse['option1'] = $option1;
			$jsonResponse['option1Val'] = $option1Val;
			$jsonResponse['option2'] = $option2;
			$jsonResponse['option2Val'] = $option2Val;

			$Payments = new Payments($this->db);
			$jsonResponse['paymentData'] = $Payments->loadPOSPayment('Orders', $pos_id);			
			$jsonResponse['payment_datetime'] = date('Y-m-d H:i:s');
			
			$paymentgetwayarray = array();
			$vData = $Common->variablesData('payment_options', $accounts_id);
			if(!empty($vData)){
				extract($vData);
				$paymentgetwayarray = explode('||',$payment_options);
			}
			$metOpt = array();
			if(!empty($paymentgetwayarray)){
				foreach($paymentgetwayarray as $onePayOption){
					$onePayOption = trim((string) $onePayOption);
					if($onePayOption !=''){
						$metOpt[] = $onePayOption;
					}
				}
			}
			
			$jsonResponse['metOpt'] = $metOpt;

			$multiple_cash_drawers = 0;
			$cash_drawers = '';
			$cdArray = array();
			$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
			if(!empty($cdData)){
				extract($cdData);
				$cdArray = explode('||',$cash_drawers);
			}
			$drawer = isset($_COOKIE['drawer'])?$_COOKIE['drawer']:'';
			$jsonResponse['drawer'] = $drawer;
			$drawerOpt = array();
			if($multiple_cash_drawers>0 && !empty($cdArray)){
				foreach($cdArray as $oneCDOption){
					$oneCDOption = trim((string) $oneCDOption);
					if(!empty($oneCDOption)){
						$drawerOpt[addslashes(stripslashes($oneCDOption))] = '';
					}
				}
				if(!empty($drawerOpt)){$drawerOpt = array_keys($drawerOpt);}
			}
			else{
				$multiple_cash_drawers = 0;
			}
			$jsonResponse['multiple_cash_drawers'] = $multiple_cash_drawers;
			$jsonResponse['draOpt'] = $drawerOpt;

			$returnURL = "http://$GLOBALS[subdomain].".OUR_DOMAINNAME."/Orders/edit/$invoice_no/";	
			$jsonResponse['returnURL'] = $returnURL;

			$sqrup_currency_code = '';
			$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'cr_card_processing' AND value !=''", array());
			if($varObj){
				$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
				$value = $variablesData->value;
				if(!empty($value)){
					$value = unserialize($value);
					extract($value);
				}
			}
			$jsonResponse['sqrup_currency_code'] = $sqrup_currency_code;

			$webcallbackurl = '';
			if(OUR_DOMAINNAME=='machousel.com.bd'){
				$webcallbackurl = 'demo.';
			}
			$webcallbackurl .= OUR_DOMAINNAME;
			$jsonResponse['webcallbackurl'] = $webcallbackurl;
			$jsonResponse['accounts_id'] = $accounts_id;
			$jsonResponse['user_id'] = $user_id;

			$ashbdclass = '';
			if($available_credit>0){
				$ashbdclass = 'bgtitle';
			}
			$jsonResponse['ashbdclass'] = $ashbdclass;
			
			$default_invoice_printer = 'Small';
			$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
			if($varObj){
				$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
				if(!empty($value)){
					$value = unserialize($value);
					if(array_key_exists('default_invoice_printer', $value)){
						$default_invoice_printer = $value['default_invoice_printer'];
						if($default_invoice_printer=='' || is_null($default_invoice_printer)){$default_invoice_printer = 'Small';}
					}
				}
			}
			$jsonResponse['default_invoice_printer'] = $default_invoice_printer;
			
		}
		else{
			$jsonResponse['login'] = 'Orders/lists/';
		}
		
		return json_encode($jsonResponse);
	}
	
	public function edit($segment4name){
		$accounts_id = $_SESSION['accounts_id']??0;		
		$posObj = $this->db->query("SELECT order_status FROM pos WHERE invoice_no = :invoice_no AND accounts_id = $accounts_id AND pos_type = 'Order'", array('invoice_no'=>$segment4name),1);
		if($posObj){
			$order_status = $posObj->fetch(PDO::FETCH_OBJ)->order_status;			
			if($order_status==2){
				return "<meta http-equiv = \"refresh\" content = \"0; url = /Orders/\" />";
			}
		}
	}
	
	function confirmShippedAllP(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $status = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$Common = new Common($this->db);
		
		$pos_id = intval($POST['pos_id']??0);
		$posObj = $this->db->query("SELECT status, invoice_no, pos_type, order_status FROM pos WHERE accounts_id = $accounts_id AND pos_id = :pos_id", array('pos_id'=>$pos_id),1);
		if($posObj){
			$posRow = $posObj->fetch(PDO::FETCH_OBJ);
			$invoice_no = $posRow->invoice_no;
			$pos_type = $posRow->pos_type;
			$order_status = $posRow->order_status;
			$status = $posRow->status;
			
			if(in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2){
				$action = 'reload';
			}
			else if($status =='Quotes'){
				$action = 'canNotAdd';
			}
			else{
				$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_id = :pos_id", array('pos_id'=>$pos_id),1);
				if($pos_cartObj){
					while($pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
						
						if($pos_cartrow->item_type == 'product' && $pos_cartrow->require_serial_no==0){
							$pos_cart_id = $pos_cartrow->pos_cart_id;
							$pos_id = $pos_cartrow->pos_id;							
							$product_id = $pos_cartrow->item_id;
							$maxqty = $shipping_qty = $pos_cartrow->qty;
							
							$oldsales_price = $pos_cartrow->sales_price;
							$oldshipping_qty = $pos_cartrow->shipping_qty;
							$oldtaxable = $pos_cartrow->taxable;
							$description = stripslashes(trim((string) $pos_cartrow->description));
							$additionalshipping_qty = $shipping_qty-$oldshipping_qty;
						
							$inventoryObj = $this->db->query("SELECT p.manage_inventory_count, p.allow_backorder, p.taxable, i.inventory_id, i.current_inventory FROM product p, inventory i WHERE p.product_id = $product_id AND i.accounts_id = $accounts_id AND p.product_id = i.product_id", array());
							if($inventoryObj){
								$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
								
								$current_inventory = $inventoryrow->current_inventory;
								$manage_inventory_count = $inventoryrow->manage_inventory_count;
								$taxable = $inventoryrow->taxable;
								$allow_backorder = $inventoryrow->allow_backorder;
								
								$allowsale = 0;
								if($manage_inventory_count==0 || ($manage_inventory_count==1 && ($current_inventory >= $additionalshipping_qty || $allow_backorder > 0))){
									$allowsale = 1;
								}
								else{
									if($current_inventory>0){
										$maxqty = $oldshipping_qty+$current_inventory;
										$additionalshipping_qty = $current_inventory;
										$allowsale = 1;
									}
									else{
										$action = 'There is no inventory available.';
									}
								}

								if($allowsale==1){									
									$pcUpdateData=array('shipping_qty'=>$maxqty,
														'taxable'=>$taxable);
									$tableUpdate = $this->db->update('pos_cart', $pcUpdateData, $pos_cart_id);
									if($tableUpdate){
										
										$changed = array();
										$fieldName = 'shipping_qty';
										$prevFieldVal = $oldshipping_qty;
										$fieldValue = $maxqty;										
										if($prevFieldVal != $fieldValue){
											$changed[$fieldName] = array($prevFieldVal, $fieldValue);
										}
										
										$fieldName = 'taxable';
										$prevFieldVal = $oldtaxable;
										$fieldValue = $taxable;										
										if($prevFieldVal != $fieldValue){
											$changed[$fieldName] = array($prevFieldVal, $fieldValue);
										}
										
										if(!empty($changed)){											
											$moreInfo = array('table'=>'pos_cart', 'id'=>$pos_cart_id, 'product_id'=>$product_id, 'description'=>$description);
											$record_for = 'pos';
											$record_id = $pos_id;
											$teData = array();
											$teData['created_on'] = date('Y-m-d H:i:s');
											$teData['accounts_id'] = $_SESSION["accounts_id"];
											$teData['user_id'] = $_SESSION["user_id"];
											$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', $record_for);
											$teData['record_id'] = $record_id;
											$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
											$this->db->insert('track_edits', $teData);
										}
																		
										if($additionalshipping_qty >0 || $additionalshipping_qty <0){
											$newcurrent_inventory = floor($current_inventory-$additionalshipping_qty);
											$updateproduct = $this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventoryrow->inventory_id);
										}

										$action = 'Update';
									}
								}
							}
						}
					}
				}				
			}
		}
		$cartsData = array();
		if($action == 'Update'){
			$Carts = new Carts($this->db);
			$cartsData = $Carts->loadCartData('Orders', $pos_id);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'pos_id'=>$pos_id, 'cartsData'=>$cartsData));
	}
	
	public function AJsave_orderStatus(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$pos_id = intval($POST['pos_id']??0);		
		$status = $POST['status']??'';
		$oldStatus = $POST['oldstatus']??'';
			
		$action = '';
		$cartsData = array();
		if($pos_id>0 && $status !=''){
			$status = $this->db->checkCharLen('pos.status', $status);
			$update = $this->db->update('pos', array('status'=>$status, 'last_updated'=>date('Y-m-d H:i:s')), $pos_id);
			if($update){
				
				$changed = array();
				$changed['status'] = array($oldStatus, $status);
				if(!empty($changed)){
					$moreInfo = $teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
					$teData['record_id'] = $pos_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);							
				}
				
				$action = 'Changed';
				if($oldStatus=='Quotes'){
					$Carts = new Carts($this->db);
					$cartsData = $Carts->loadCartData('Orders', $pos_id);
				}
			}		
		}
				
		return json_encode(array('login'=>'', 'action'=>$action, 'cartsData'=>$cartsData));
	}
	
	private function filterHAndOptions(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$spos_id = $this->pos_id;
		$shistory_type = $this->history_type;
		$filterSql = '';
		$bindData = array();
		$bindData['pos_id'] = $spos_id;
		$invoice_no = 0;
		$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE pos_id = $spos_id AND accounts_id = $accounts_id", array());
		if($posObj){
			$invoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
		}
		
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Orders Created')==0){
				$filterSql = "SELECT COUNT(pos_id) AS totalrows FROM pos 
							WHERE pos_id = :pos_id AND accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Signature Created')==0){
				$filterSql = "SELECT COUNT(digital_signature_id) AS totalrows FROM digital_signature 
						WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Orders/edit/', :pos_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
				$bindData['pos_id'] = $invoice_no;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Orders/edit/', $invoice_no) 
					UNION ALL 
						SELECT COUNT(pos_id) AS totalrows FROM pos 
							WHERE pos_id = :pos_id and accounts_id = $accounts_id 
					UNION ALL 
						SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id 
					UNION ALL 
						SELECT COUNT(digital_signature_id) AS totalrows FROM digital_signature 
						WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
		}
		
		$totalRows = 0;
		$tableObj = $this->db->query($filterSql, $bindData);
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;
			}
		}		
		$this->totalRows = $totalRows;

		
		$actFeeTitOpt = array();
		$Sql = "SELECT activity_feed_title AS afTitle FROM activity_feed 
			WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Orders/edit/', $invoice_no) 
		UNION ALL 
			SELECT 'Orders Created' AS afTitle FROM pos 
				WHERE pos_id = :pos_id and accounts_id = $accounts_id 
		UNION ALL 
			SELECT 'Notes Created' AS afTitle FROM notes 
			WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id 
		UNION ALL 
			SELECT 'Track Edits' AS afTitle FROM track_edits 
			WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id 
		UNION ALL 
			SELECT 'Signature Created' AS afTitle FROM digital_signature 
			WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
		$tableObj = $this->db->query($Sql, array('pos_id'=>$spos_id));
		if($tableObj){
			$actFeeTitOpts = array();
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$actFeeTitOpts[$oneRow->afTitle] = '';
			}
			ksort($actFeeTitOpts);
			$actFeeTitOpt = array_keys($actFeeTitOpts);
		}	
		$this->actFeeTitOpt = $actFeeTitOpt;

	}
	
    private function loadHTableRows(){
		
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$spos_id = $this->pos_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		$bindData = array();
		$bindData['pos_id'] = $spos_id;            
		$invoice_no = 0;
		$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE pos_id = $spos_id AND accounts_id = $accounts_id", array());
		if($posObj){
			$invoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
		}
		
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Orders Created')==0){
				$filterSql = "SELECT 'pos' AS tablename, created_on AS tabledate, pos_id AS table_id, 'Orders Created' AS activity_feed_title FROM pos 
					WHERE pos_id = :pos_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Signature Created')==0){
				$filterSql = "SELECT 'digital_signature' AS tablename, created_on AS tabledate, digital_signature_id AS table_id, 'Signature Created' AS activity_feed_title FROM digital_signature 
							WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Orders/edit/', :pos_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
				$bindData['pos_id'] = $invoice_no;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'pos' AND activity_feed_link = CONCAT('/Orders/edit/', $invoice_no)  
					UNION ALL 
					SELECT 'pos' AS tablename, created_on AS tabledate, pos_id AS table_id, 'Orders Created' AS activity_feed_title FROM pos 
						WHERE pos_id = :pos_id AND accounts_id = $accounts_id 
					UNION ALL 
					SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'pos' AND table_id = :pos_id 
					UNION ALL 
					SELECT 'digital_signature' AS tablename, created_on AS tabledate, digital_signature_id AS table_id, 'Signature Created' AS activity_feed_title FROM digital_signature 
						WHERE accounts_id = $accounts_id AND for_table = 'pos' AND table_id = :pos_id 
					UNION ALL 
					SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'pos' AND record_id = :pos_id 
					ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		$query = $this->db->querypagination($filterSql, $bindData);

		$tabledata = array();
		if($query){
			$userIdNames = array();
			$userObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id", array());
			if($userObj){
				while($userOneRow = $userObj->fetch(PDO::FETCH_OBJ)){
					$userIdNames[$userOneRow->user_id] = trim("$userOneRow->user_first_name $userOneRow->user_last_name");
				}
			}
			$Activity_Feed = new Activity_Feed($this->db);
			foreach($query as $onerow){
				$activity_feed_title = $onerow['activity_feed_title'];
				$tablename = $onerow['tablename'];
				$table_id = $onerow['table_id'];
				$getHMoreInfo = $Activity_Feed->getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title);
				if(!empty($getHMoreInfo)){
					$tabledata[] = $getHMoreInfo;
				}
			}
		}

		return $tabledata;
    }
	
	public function AJgetHPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$spos_id = intval($POST['spos_id']??0);
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->pos_id = $spos_id;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;	
			$jsonResponse['actFeeTitOpt'] = $this->actFeeTitOpt;	
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRows();
		
		return json_encode($jsonResponse);
	}
	
	public function AJsave_Orders(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = 0;
		$savemsg = '';
		$message = '';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$customer_id = intval($POST['customer_id']??0);
		$customer_name = $POST['customer_name']??'';
		if($customer_name !=''){
			if($customer_id>0){
				$customerObj = $this->db->query("SELECT first_name, last_name, company, email, contact_no FROM customers WHERE customers_id = $customer_id", array());
				if($customerObj){
					$customerRow = $customerObj->fetch(PDO::FETCH_OBJ);
					$name = trim((string) stripslashes($customerRow->company));
					$email = trim((string) stripslashes($customerRow->email));
					$contact_no = trim((string) stripslashes($customerRow->contact_no));
					$first_name = trim((string) stripslashes($customerRow->first_name));
					if($name !=''){$name .= ', ';}
					$name .= $first_name;
					$last_name = trim((string) stripslashes($customerRow->last_name));
					if($name !=''){$name .= ' ';}
					$name .= $last_name;
					
					if($email !=''){
						$name .= " ($email)";
					}
					elseif($contact_no !=''){
						$name .= " ($contact_no)";
					}
					
					if($customer_name !="$name"){
						$customer_id = 0;
					}
				}
			}
			
			if($customer_id==0 && $customer_name != ''){
				
				$autocustomer_name = $customer_name;
				$email = '';
				if(strpos($customer_name, ' (') !== false) {
					$scustomerexplode = explode(' (', $customer_name);
					if(count($scustomerexplode)>1){
						$autocustomer_name = trim((string) $scustomerexplode[0]);
						$email = str_replace(')', '', $scustomerexplode[1]);
					}						
				}
				$autocustomer_name = addslashes($autocustomer_name);
				$bindData = array();
				$strextra = " AND CONCAT(company, ', ', first_name, ' ', last_name) LIKE CONCAT('%', :autocustomer_name, '%')";
				$bindData['autocustomer_name'] = $autocustomer_name;
				if($email !=''){
					$strextra .= " AND (email = :email OR contact_no = :email)";
					$bindData['email'] = $email;
				}
				$strextra .= " GROUP BY TRIM(CONCAT_WS(' ', first_name, last_name)), email";
				$sqlquery = "SELECT customers_id FROM customers WHERE accounts_id = $prod_cat_man $strextra LIMIT 0, 1";
				$query = $this->db->querypagination($sqlquery, $bindData);
				if($query){
					foreach($query as $onegrouprow){
						$customer_id = $onegrouprow['customers_id'];
					}
				}
			}
		}
				
		if($customer_id==0){
			$savemsg = 'error';
			$message .= 'noCustomerName';
		}
		else{
		
			$pos_id = intval($POST['pos_id']??0);
			$sales_datetime = date('Y-m-d H:i:s');
			$salesman_id = intval($POST['salesman_id']??0);				
			$user_id = $_SESSION["user_id"]??0;
			$created_on = date('Y-m-d H:i:s');
			$last_updated = date('Y-m-d H:i:s');
			
			if(array_key_exists('startQuotes', $POST) && $POST['startQuotes']=='Yes'){
				$ostatus = 'Quotes';
			}
			else{
				$ostatus = 'New';
			}
			
			if($pos_id==0){
				$status = 'Trial';
				if(isset($_SESSION["status"])){$status = $_SESSION["status"];}
				if(in_array($status, array('SUSPENDED', 'CANCELED'))){
					return json_encode(array('login'=>'session_ended'));
				}
				
				//=============collect user last new invoice no================//
				$invoice_no = 1;
				$poObj = $this->db->querypagination("SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id ORDER BY invoice_no DESC LIMIT 0, 1", array());
				if($poObj){
					$invoice_no = $poObj[0]['invoice_no']+1;
				}
				
				$taxes_name1 = $taxes_name2 = '';
				$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
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
							if($no_of_default_rows==1){
								$taxes_name1 = $staxes_name;
								$taxes_percentage1 = $staxes_percentage;
								$tax_inclusive1 = $tax_inclusive;
							}
							
							if($no_of_default_rows==2){
								$taxes_name2 = $staxes_name;
								$taxes_percentage2 = $staxes_percentage;
								$tax_inclusive2 = $tax_inclusive;
							}
						}
					}
				}
				
				$posData = array('invoice_no' => $invoice_no, 
								'sales_datetime' => $sales_datetime, 
								'employee_id' => $salesman_id, 
								'customer_id' => $customer_id, 
								'pos_type' => 'Order', 
								'taxes_name1' => $taxes_name1,
								'taxes_percentage1' => $taxes_percentage1,
								'tax_inclusive1' => $tax_inclusive1,
								'taxes_name2' => $taxes_name2,
								'taxes_percentage2' => $taxes_percentage2,
								'tax_inclusive2' => $tax_inclusive2,
								'created_on' => date('Y-m-d H:i:s'),
								'last_updated' => date('Y-m-d H:i:s'), 
								'user_id' => $user_id, 
								'accounts_id' => $accounts_id,
								'credit_days' => 0, 
								'is_due' => 0, 
								'status' => $ostatus);

				$pos_id = $this->db->insert('pos', $posData);
				if($pos_id){							
					$id = $invoice_no;
					$savemsg = '';
				}
				else{
					$savemsg = 'error';
					$message .= 'errorAdding';
				}
			}		
			else{
				$savemsg = 'error';
				$message .= 'errorAdding';
			}
		}
	
		$array = array( 'login'=>'','id'=>$id,
						'savemsg'=>$savemsg,
						'message'=>$message);
		return json_encode($array);
	}
	
	public function showEmplyeeOptions(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$emplyeeOpts = array();
		$accounts_id = $_SESSION["accounts_id"]??0;
		$employee_id = intval($POST['employee_id']??0);
		
		$sqlquery = "SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id AND (user_publish =1 OR (user_publish =0 AND user_id= :user_id))";
		$userObj = $this->db->query($sqlquery, array('user_id'=>$employee_id));
		if($userObj){
			while($useronerow = $userObj->fetch(PDO::FETCH_OBJ)){
				$emplyeeOpts[$useronerow->user_id] = stripslashes(trim("$useronerow->user_first_name $useronerow->user_last_name"));
			}
		}
		return json_encode(array('login'=>'', 'emplyeeOpts'=>$emplyeeOpts));
	}
	
	public function saveChangeOrderInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$pos_id = intval($POST['pos_id']??0);		
		$invoice_number = $POST['invoice_number']??'';		
		$employee_id = intval($POST['employee_id']??0);
		if(empty($employee_id)){$employee_id = 0;}
		$returnValue = $prevEmployeeId = 0;
		$sqlquery = "SELECT invoice_no, pos_type, order_status, employee_id FROM pos WHERE pos_id = $pos_id";
		$queryObj = $this->db->query($sqlquery, array());
		if($queryObj){
			$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
			$invoice_no = $posRow->invoice_no;
			$prevEmployeeId = $posRow->employee_id;
			$pos_type = $posRow->pos_type;
			$order_status = $posRow->order_status;
			if(in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2){
				$returnValue = 1000;
			}
		}
		$returnStr = 'error';
		if($returnValue==0){
			$changed = array();
			
			$updatepos = $this->db->update('pos', array('employee_id'=>$employee_id, 'last_updated'=>date('Y-m-d H:i:s')), $pos_id);
			if($updatepos){
				$Common = new Common($this->db);
				if($prevEmployeeId != $employee_id){					
					$fieldName = 'Salesman';
					$prevFieldVal = $prevEmployeeId;
					$fieldValue = $employee_id;
					if($prevFieldVal==0){$prevFieldVal = '';}
					elseif($prevFieldVal>0){$prevFieldVal = $Common->getOneRowFields('user', array('user_id'=>$prevFieldVal), array('user_first_name', 'user_last_name'));}
					if($fieldValue==0){$fieldValue = '';}
					elseif($fieldValue>0){$fieldValue = $Common->getOneRowFields('user', array('user_id'=>$fieldValue), array('user_first_name', 'user_last_name'));}
					$changed[$fieldName] = array($prevFieldVal, $fieldValue);
				}
				
				if($employee_id>0){
					$returnStr = $Common->getOneRowFields('user', array('user_id'=>$employee_id), array('user_first_name', 'user_last_name'));
				}
			}
			
			if(!empty($changed)){
				$moreInfo = $teData = array();
				$teData['created_on'] = date('Y-m-d H:i:s');
				$teData['accounts_id'] = $_SESSION["accounts_id"];
				$teData['user_id'] = $_SESSION["user_id"];
				$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
				$teData['record_id'] = $pos_id;
				$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
				$this->db->insert('track_edits', $teData);							
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
		
	public function completeOrder(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$savemsg = 'error';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$pos_id = intval($POST['pos_id']??0);		
		$completed = $POST['completed']??0;
		$order_status = $POST['order_status']??0;
		$user_id = $_SESSION["user_id"]??0;
		$todayDate = date('Y-m-d');
		$Common = new Common($this->db);
		$returnBeforeUpdate = 0;
		$status = 'Invoiced';
		if($completed==0 || $order_status==0){
			$sqlquery = "SELECT pos_cart_id FROM pos_cart WHERE pos_id = :pos_id";
			$query = $this->db->querypagination($sqlquery, array('pos_id'=>$pos_id),1);
			if($query){
				$returnBeforeUpdate += count($query);
			}
			if($returnBeforeUpdate>0){
				return json_encode(array('login'=>'', 'returnStr'=>'There are some cart on this ORDER. Please remove all cart then try to CANCEL', 'savemsg'=>$savemsg));
			}
			$sqlquery = "SELECT pos_payment_id FROM pos_payment WHERE pos_id = :pos_id";
			$query = $this->db->querypagination($sqlquery, array('pos_id'=>$pos_id),1);
			if($query){
				$returnBeforeUpdate += count($query);
			}
			if($returnBeforeUpdate>0){
				return json_encode(array('login'=>'', 'returnStr'=>'There are some payment on this ORDER. Please remove all are then try to CANCEL', 'savemsg'=>$savemsg));
			}
			$status = 'Canceled';
		}
		
		$updatepos = $this->db->update('pos', array('order_status'=>$order_status, 'sales_datetime'=>date('Y-m-d H:i:s'), 'status'=>$status), $pos_id);
		if($updatepos){
			
			if($completed>0){
				$invoice_no = 1;
				$poObj = $this->db->querypagination("SELECT invoice_no FROM pos WHERE pos_id = $pos_id ORDER BY invoice_no DESC LIMIT 0, 1", array());
				if($poObj){
					$invoice_no = $poObj[0]['invoice_no'];
				}
				
				//===========Update ave_cost into pos_cart table============//
				$pos_cartSql = "SELECT * FROM pos_cart WHERE pos_id = $pos_id ORDER BY pos_cart_id ASC";
				$pos_cartObj = $this->db->query($pos_cartSql, array());
				if($pos_cartObj){
					while($onepos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
						$pos_cart_id = $onepos_cartrow->pos_cart_id;
						$product_id = $onepos_cartrow->item_id;
						$item_type = $onepos_cartrow->item_type;
						if($product_id==0){
							$ave_cost = $onepos_cartrow->ave_cost;
						}
						else{
							$ave_cost = 0;
						}
						
						if($item_type == 'one_time' && $order_status==2){
							
							$poSql = "SELECT po.po_id FROM po, po_items WHERE po.accounts_id = $accounts_id AND po_items.item_type = 'one_time' AND po_items.product_id = $pos_cart_id AND po.po_id = po_items.po_id GROUP BY po_items.po_items_id ORDER BY po_items.po_items_id ASC LIMIT 0,1";
							$poData = $this->db->querypagination($poSql, array());
							if($poData !=''){
								foreach($poData as $poRow){
									$po_id = $poRow['po_id'];									
									$lot_ref_no = "o$invoice_no";				
									$this->db->update('po', array('lot_ref_no'=>$lot_ref_no), $po_id);
								}
							}
						}
						
						if($item_type !='livestocks'){
							$inventoryObj = $this->db->query("SELECT i.ave_cost, i.ave_cost_is_percent, p.manage_inventory_count FROM product p, inventory i WHERE p.product_id = $product_id AND i.accounts_id = $accounts_id AND p.product_id = i.product_id", array());
							if($inventoryObj){
								$inventoryOneRow = $inventoryObj->fetch(PDO::FETCH_OBJ);
								$ave_cost = $inventoryOneRow->ave_cost;
								$ave_cost_is_percent = $inventoryOneRow->ave_cost_is_percent;
								$manage_inventory_count = $inventoryOneRow->manage_inventory_count;
								if($ave_cost_is_percent>0 && $manage_inventory_count==0){
									if($onepos_cartrow->discount_is_percent>0){
										$discount_value = round($onepos_cartrow->sales_price*0.01*$onepos_cartrow->discount,2);
									}
									else{
										$discount_value = round($onepos_cartrow->discount,2);
									}
									$ave_cost = round(($onepos_cartrow->sales_price-$discount_value)*$ave_cost*0.01,2);
								}
							}
						}
						
						$this->db->update('pos_cart', array('ave_cost'=>$ave_cost), $pos_cart_id);			
						if($item_type =='livestocks'){
							$Common->cartCellphoneAveCost($pos_cart_id, date('Y-m-d H:i:s'), 1);
						}
					}
				}
				
				$changemethod = $POST['changemethod']??'Cash';
				$amount_due = floatval($POST['amount_due']??0);			
				$pos_payment_id = $payment_amount = 0;
				$payment_datetime = '';					
				
				$sqlquery = "SELECT * FROM pos_payment WHERE pos_id = :pos_id AND payment_method = 'Cash' ORDER BY pos_payment_id DESC LIMIT 0,1";
				$query = $this->db->querypagination($sqlquery, array('pos_id'=>$pos_id),1);
				if($query){
					foreach($query as $row){
						$pos_payment_id = $row['pos_payment_id'];
						$payment_amount = $row['payment_amount'];
						$payment_datetime = $row['payment_datetime'];
					}
				}
				
				$updatecash = 0;
				if($changemethod=='Cash' && $pos_payment_id>0 && strcmp($todayDate, substr($payment_datetime,0,10))==0 && $amount_due<0){
					
					if((-1*$amount_due) < $payment_amount){
						$payment_amount = $payment_amount+$amount_due;
						
						$pos_paymentdata =array('payment_amount'=>$payment_amount,
												'payment_datetime'=>date('Y-m-d H:i:s')
												);
						$this->db->update('pos_payment', $pos_paymentdata, $pos_payment_id);
						$updatecash = 1;
					}
				}
				
				if($updatecash == 0 && $amount_due<0){
					$payment_method = $this->db->checkCharLen('pos_payment.payment_method', $changemethod);
					$drawer = $this->db->checkCharLen('pos_payment.drawer', '');
			
					$ppData =array('pos_id'=>$pos_id,
									'payment_method'=>$payment_method,
									'payment_amount'=>round($amount_due,2),
									'payment_datetime'=>date('Y-m-d H:i:s'),
									'user_id' => $user_id,
									'more_details' => '',
									'drawer' => $drawer);
					$pos_payment_id = $this->db->insert('pos_payment', $ppData);
				}
				
				$sql = "SELECT * FROM pos WHERE pos_id = $pos_id";
				$query = $this->db->querypagination($sql, array());
				if($query){
					foreach($query as $onerow){
						$customer_id = $onerow['customer_id'];
						$is_due = $onerow['is_due'];
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
						$dueAmount = round($grand_total-$amountPaid,2);
						
						if($dueAmount>0){
							$credit_days = $credit_limit = 0;
							$customersObj = $this->db->querypagination("SELECT credit_days, credit_limit FROM customers WHERE customers_id = $customer_id AND accounts_id = $prod_cat_man AND credit_days>0 ORDER BY accounts_id DESC LIMIT 0, 1", array());
							if($customersObj){
								$credit_days = $customersObj[0]['credit_days'];
								$credit_limit = $customersObj[0]['credit_limit'];							
							}
							if($credit_limit>0){
								$this->db->update('pos', array('credit_days'=>$credit_days, 'is_due'=>1), $pos_id);
							}
						}
						elseif($dueAmount<0){
							$lpos_payment_id = 0;
							$newpayment_amount = $dueAmount;
							$sqlquery = "SELECT pos_payment_id, payment_amount FROM pos_payment WHERE pos_id = $pos_id AND payment_method = 'Cash' ORDER BY pos_payment_id DESC LIMIT 0,1";
							$query = $this->db->querypagination($sqlquery, array());
							if($query){
								foreach($query as $row){
									$lpos_payment_id = $row['pos_payment_id'];
									$newpayment_amount = $row['payment_amount']+$dueAmount;
								}
							}
							
							if($lpos_payment_id>0){
								if($newpayment_amount==0){
									$this->db->delete('pos_payment', 'pos_payment_id', $lpos_payment_id);
								}
								else{
									$pos_paymentdata =array('payment_amount'=>$newpayment_amount,
															'payment_datetime'=>date('Y-m-d H:i:s')
															);
									$this->db->update('pos_payment', $pos_paymentdata, $lpos_payment_id);
								}
							}
							elseif($newpayment_amount !=0){
								$payment_method = $this->db->checkCharLen('pos_payment.payment_method', 'Cash');
								$drawer = $this->db->checkCharLen('pos_payment.drawer', '');
			
								$ppData = array('pos_id'=>$pos_id,
												'payment_method'=>$payment_method,
												'payment_amount'=>round($newpayment_amount,2),
												'payment_datetime'=>date('Y-m-d H:i:s'),
												'user_id' => $user_id,
												'more_details' => '',
												'drawer' => $drawer);
								$pos_payment_id = $this->db->insert('pos_payment', $ppData);
							}
						}
						elseif($is_due==1 && $dueAmount==0){
							$this->db->update('pos', array('credit_days'=>0, 'is_due'=>0), $pos_id);
						}
							
						$BulkSMS = new BulkSMS($this->db);
						$BulkSMS->sendInvoiceSMS($customer_id, $onerow['invoice_no'], $grand_total, $amountPaid, $onerow['sales_datetime']);
					}
				}
								
				$email_address = '';
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('invoice_backup_email', $value))
							$email_address = $value['invoice_backup_email'];
					}
				}
				if($email_address !=''){
					$invoice_no = 0;
					$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
					if($posObj){
						$invoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
					}
					$customer_service_email = '';
					$accObj = $this->db->query("SELECT customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
					if($accObj){
						$customer_service_email = $accObj->fetch(PDO::FETCH_OBJ)->customer_service_email;
					}
					if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
					
					$Printing = new Printing($this->db);		
					$mail_body = $Printing->invoicesInfo($pos_id, 'large', $amount_due, 'Invoices', 1);
					
					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = $this->db->supportEmail('Host');
					$mail->Port = 587;
					$mail->SMTPAuth = true;
					$mail->Username = $this->db->supportEmail('Username');
					$mail->Password = $this->db->supportEmail('Password');
					
					$mail->addReplyTo($customer_service_email, $_SESSION["company_name"]);
					$subdomain = $GLOBALS['subdomain'];
					$mail->setFrom($this->db->supportEmail('do_not_reply'), $_SESSION["company_name"]);
					$mail->clearAddresses();
					$mail->addAddress($email_address, "");
					$mail->Subject = $this->db->translate('Sales Invoice # s').$invoice_no;
					$mail->CharSet = 'UTF-8';
					$mail->isHTML(true);
					//Build a simple message body
					$mail->Body = $mail_body;					
					$mail->send();
				}
			}
			else{
				$changed = array($this->db->translate('Order has cancelled.')=>'');
				$moreInfo = $teData = array();
				$teData['created_on'] = date('Y-m-d H:i:s');
				$teData['accounts_id'] = $_SESSION["accounts_id"];
				$teData['user_id'] = $_SESSION["user_id"];
				$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'pos');
				$teData['record_id'] = $pos_id;
				$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
				$this->db->insert('track_edits', $teData);							
				
				$updatepos = $this->db->update('pos', array('order_status'=>2, 'status'=>'Canceled'), $pos_id);
		
			}
			$savemsg = 'Success';
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function addCartsBulkIMEI($segment2name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $message = '';
		$smallerIMEI = $largerIMEI = $duplicateIMEI = $savedIMEI = '';
		$qty = $shipping_qty = $pototalreceived_qty = 0;
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;

		$pos_cart_id = intval($POST['pos_cart_id']??0);
		$bulkimei = $POST['bulkimei']??'';

		$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
		if($pos_cartObj){
			$onepos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ);

			$pos_id = $onepos_cartrow->pos_id;
			$sqlquery = "SELECT invoice_no, pos_type, order_status FROM pos WHERE pos_id = $pos_id";
			$queryObj = $this->db->query($sqlquery, array());
			if($queryObj){
				$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
				$invoice_no = $posRow->invoice_no;
				$pos_type = $posRow->pos_type;
				$order_status = $posRow->order_status;
				if(($invoice_no>0 && $segment2name=='POS') || (in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2)){
					$action = 'reload';
				}
			}
			
			if(empty($action)){
				$product_id = $onepos_cartrow->item_id;
				$qty = $onepos_cartrow->qty;
				$shipping_qty = $preshipqty = $onepos_cartrow->shipping_qty;
			   
				if($bulkimei !=''){

					$item_numberData = preg_split("/\\r\\n|\\r|\\n/", $bulkimei);

					if(count($item_numberData)>0){
						$imeisaved = $imeismallerthan = $imeilongerthan = $duplicateimei = 0;
						$totalIMEI = count($item_numberData);
						foreach($item_numberData as $item_number){
							$item_number = addslashes(trim((string) $item_number));
							if($item_number==''){
								$totalIMEI--;
							}
							elseif(strlen($item_number)<2){
								$imeismallerthan++;
							}
							elseif(strlen($item_number)>20){
								$imeilongerthan++;
							}
							else{

								$sqlitem = "SELECT item_id FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_number = :item_number AND in_inventory = 1 AND item_publish = 1 ORDER BY item_number ASC LIMIT 0,1";
								$itemquery = $this->db->querypagination($sqlitem, array('item_number'=>$item_number));
								if($itemquery){
									foreach($itemquery as $itemrow){

										$item_id = $itemrow['item_id'];
										$shipping_qty++;

										if($qty<$shipping_qty){$qty = $shipping_qty;}
										$updatepc = $this->db->update('pos_cart', array('qty'=>$qty, 'shipping_qty'=>$shipping_qty), $pos_cart_id);
										if($updatepc){
											$returnstr = 1;
										}

										$updateitem = $this->db->update('item', array('in_inventory'=>0, 'is_pos'=>1), $item_id);
										if($updateitem){
											$returnstr = 1;
										}

										$pciData =array('pos_cart_id'=>$pos_cart_id, 
														'item_id'=>$item_id, 
														'sale_or_refund'=>1, 
														'return_pos_cart_id'=>0);
										$pos_cart_item_id = $this->db->insert('pos_cart_item', $pciData);
										$imeisaved++;
										$preshipqty = $shipping_qty;
									}
								}
							}
						}
					}

					if($imeismallerthan>0){
						$message .= 'smallerIMEI';
						$smallerIMEI = $imeismallerthan;
					}

					if($imeilongerthan>0){
						$message .= '|largerIMEI';
						$largerIMEI = $imeilongerthan;
					}

					if($duplicateimei>0){
						$message .= '|duplicateIMEI';
						$duplicateIMEI = $duplicateimei;
					}

					if($imeisaved>0){
						$action = 'Add';
						if(count($item_numberData)>$imeisaved){
							$message .= '|IMEIsavedError';
							$savedIMEI = $imeisaved;
						}
						else{
							$message .= '|IMEIsaved';
							$savedIMEI = $imeisaved;
						}
					}
					else{
						$message .= '|noIMEIsaved';
					}
				}
			}
		}
		else{
			$action = 'missing';
			$message .= '|missingIMEI';
		}
		
		$cartsData = array();
		if($action == 'Add' && $pos_id>0){
			$Carts = new Carts($this->db);
			$cartsData = $Carts->loadCartData('Orders', $pos_id);
		}
		return json_encode(array('login'=>'', 'qty'=>$qty, 'shipping_qty'=>$shipping_qty, 'message'=>$message, 'action'=>$action, 'cartsData'=>$cartsData, 'smallerIMEI'=>$smallerIMEI, 'largerIMEI'=>$largerIMEI, 'duplicateIMEI'=>$duplicateIMEI, 'savedIMEI'=>$savedIMEI ));
		
    }
		
	public function prints($segment4name, $segment5name){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$language = $_SESSION["language"]??'English';
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

		$htmlStr = "";
		
		$pos_id = intval($segment5name);
		$segment7name = $GLOBALS['segment7name'];
		
		$posObj = $this->db->query("SELECT pos_id, invoice_no FROM pos WHERE accounts_id = $accounts_id AND pos_id = :pos_id", array('pos_id'=>$pos_id),1);
		if($posObj){
			$posOneRow = $posObj->fetch(PDO::FETCH_OBJ);
			$pos_id = $posOneRow->pos_id;
			$invoice_no = $posOneRow->invoice_no;
			if($segment7name=='signature'){ 
				$htmlStr .= '<!DOCTYPE html>
					<html>
					<head>
						<meta charset="utf-8">
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
						<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
						<script language="JavaScript" type="text/javascript">var currency = \''.$currency.'\';var calenderDate = \''.$calenderDate.'\';var timeformat = \''.$timeformat.'\';var loadLangFile = \''.$loadLangFile.'\';
						var langModifiedData = {};
						var OS;
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
			".'</script>
						<script src="/assets/js-'.swVersion.'/languages/'.$language.'.js"></script>
						<link rel="stylesheet" href="/assets/css-'.swVersion.'/style.css">';

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
						
					$htmlStr .= '</head>
					<body>
						<div id="viewPageInfo"></div>
						<script type="module" src="/assets/js-'.swVersion.'/'.printsJS.'"></script>
					</body>
					</html>';
			}
			else{
				$htmlStr .= '<!DOCTYPE html>
					<html>
					<head>
						<meta charset="utf-8">
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
						<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
						<script language="JavaScript" type="text/javascript">var currency = \''.$currency.'\';var calenderDate = \''.$calenderDate.'\';var timeformat = \''.$timeformat.'\';var loadLangFile = \''.$loadLangFile.'\';
						var langModifiedData = {};
						var OS;
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
			".'</script>
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
						
					$htmlStr .= '</head>
					<body>
						<div id="viewPageInfo"></div>
						<script type="module" src="/assets/js-'.swVersion.'/'.printsJS.'"></script>
					</body>					
					</html>';
			}
		}
		return $htmlStr;
	}
	
	public function updateCustomerInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;

		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']??0);
		$customer_id = intval($POST['customer_id']??0);
		$savemsg = 'update-error';
		$returnData = array();
		if($pos_id>0){			
			
			$updatepos = $this->db->update('pos', array('customer_id'=>$customer_id, 'last_updated'=>date('Y-m-d H:i:s')), $pos_id);
			if($updatepos){
				$savemsg = 'update-success';

				$customername = $customeremail = $customerphone = $customeraddress = '';
				$customers_id = $cCustomFields = $available_credit = 0;
				$customerObj = $this->db->query("SELECT * FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = $customer_id", array());
				if($customerObj){
					$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);

					$cqueryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers'", array());
					if($cqueryObj){
						$cCustomFields = $cqueryObj->fetch(PDO::FETCH_OBJ)->totalrows;
					}
					$customers_id = $customerrow->customers_id;
					$company = $customerrow->company;
					$first_name = $customerrow->first_name;
					$last_name = $customerrow->last_name;
				
					$customername = $company;
					if($customername !=''){$customername .= ', ';}
					$customername .= $first_name;
					if($customername !=''){$customername .= ' ';}
					$customername .= $last_name;
							
					$customeremail = $customerrow->email;
					$customerphone = $customerrow->contact_no;

					$credit_limit = round($customerrow->credit_limit,2);
					if($credit_limit>0){
						$Common = new Common($this->db);
						$availCreditData = $Common->calAvailCr($customers_id, $credit_limit, 1);
						$available_credit = $availCreditData['available_credit']??0;
					}
				}

				$returnData['available_credit'] = floatval($available_credit);
				$returnData['customers_id'] = intval($customers_id);
				$returnData['cCustomFields'] = intval($cCustomFields);
				$returnData['customeremail'] = $customeremail;
				$returnData['customername'] = $customername;
				$returnData['customerphone'] = $customerphone;
				
			}
		}

		$array = array( 'login'=>'',
			'savemsg'=>$savemsg,
			'returnData'=>$returnData);
		return json_encode($array);

	}

	public function AJ_prints_small_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = $POST['pos_id'];
		$amount_due = $POST['amount_due']??0;
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->invoicesInfo($pos_id, 'small', $amount_due, 'Orders');
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_pick_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = $POST['pos_id'];
		$amount_due = $POST['amount_due']??0;
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->invoicesInfo($pos_id, 'pick', $amount_due, 'Orders');
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_large_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = $POST['pos_id'];
		$amount_due = $POST['amount_due']??0;
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->invoicesInfo($pos_id, 'large', $amount_due, 'Orders');
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_signature_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = $POST['pos_id'];
		$amount_due = $POST['amount_due']??0;
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->invoicesInfo($pos_id, 'large', $amount_due, 'Orders');
		
		return json_encode($jsonResponse);
	}
	
	public function AJsend_OrdersEmail(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$pos_id = intval($POST['pos_id']??0);
		$email_address = $POST['email_address']??'';
		$amount_due = floatval($POST['amount_due']??0);
		if($email_address =='' || is_null($email_address)){
			$returnStr = 'notSendMail';
		}
		else{
			
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $this->db->supportEmail('Host');
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = $this->db->supportEmail('Username');
			$mail->Password = $this->db->supportEmail('Password');
			
			$Printing = new Printing($this->db);
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			$customer_service_email = '';
			$accObj = $this->db->query("SELECT customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accObj){
				$customer_service_email = $accObj->fetch(PDO::FETCH_OBJ)->customer_service_email;
			}
			if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
			$mail_body = $Printing->invoicesInfo($pos_id, 'large', $amount_due, 'Orders', 1);
			
			$mail->addReplyTo($customer_service_email, $_SESSION["company_name"]);               
			$mail->setFrom($this->db->supportEmail('do_not_reply'), $_SESSION["company_name"]);
			$mail->clearAddresses();
			$mail->addAddress($email_address, "");
			$mail->Subject = $this->db->translate('Order Pick');
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			$mail->Body = $mail_body;
			if($mail->send()){
				$note_for = $this->db->checkCharLen('notes.note_for', 'pos');
				$noteData = array();
				$noteData['table_id'] = $pos_id;
				$noteData['note_for'] = $note_for;
				$noteData['last_updated'] = date('Y-m-d H:i:s');
				$noteData['accounts_id'] = $accounts_id;			
				$noteData['user_id'] = $_SESSION["user_id"];
				$noteData['note'] = $email_address;
				$noteData['publics'] = 0;
				$noteData['created_on'] = date('Y-m-d H:i:s');
				$notes_id = $this->db->insert('notes', $noteData);
				$returnStr = 'Ok';
			}
			else{
				$returnStr = 'notSendMail';
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	//================Joining Class==============//
	public function AJget_CustomersPopup(){
		$Customers = new Customers($this->db);
		return $Customers->AJget_CustomersPopup();
	}
	
	public function AJsave_Customers(){
		$Customers = new Customers($this->db);
		return $Customers->AJsave_Customers();
	}

	public function AJget_ProductsPopup(){
		$Products = new Products($this->db);
		return $Products->AJget_ProductsPopup();
	}
	
	public function AJsave_Products(){
		$Products = new Products($this->db);
		return $Products->AJsave_Products();
	}
	
	public function AJget_ManufOpt(){
		$Products = new Products($this->db);
		return $Products->AJget_ManufOpt();
	}
	
	public function AJget_brandOpt(){
		$Products = new Products($this->db);
		return $Products->AJget_brandOpt();
	}
}
?>