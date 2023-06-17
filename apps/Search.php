<?php
class Search{
	protected $db;
	public function __construct($db){$this->db = $db;}
	
	public function AJ_globalsearch(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$keyword_search = $POST['keyword_search']??'';
		$keyword_search = trim((string) addslashes($keyword_search));
		
		$results = array();
		//============================For IMEI Number Searching=============================//
		$sql = "SELECT item_id, item_number FROM item WHERE accounts_id = $accounts_id AND item_number LIKE CONCAT(:keyword_search, '%') AND item_publish = 1 GROUP BY item_number ORDER BY item_number ASC LIMIT 0,100";
		$query = $this->db->query($sql, array('keyword_search'=>$keyword_search));
		if($query){
			while($onerow = $query->fetch(PDO::FETCH_OBJ)){
				
				$item_id = $onerow->item_id;
				$item_number = $onerow->item_number;
				
				$str1 = "IMEI - $item_number";
				$results[] = array( 'lv'=> str_replace('/', '%5C', $item_number),
									'label' => $str1,
									't' =>'m',
									'i' =>$item_id
									);				
			}
		} 
		
		$sql = "SELECT r.repairs_id, r.ticket_no, p.imei_or_serial_no FROM repairs r, properties p WHERE r.accounts_id = $accounts_id AND p.imei_or_serial_no LIKE CONCAT(:keyword_search, '%') AND r.properties_id = p.properties_id AND r.repairs_publish = 1 ORDER BY p.imei_or_serial_no ASC";
		$query = $this->db->query($sql, array('keyword_search'=>$keyword_search));
		if($query){
			while($onerow = $query->fetch(PDO::FETCH_OBJ)){
				
				$repairs_id = $onerow->repairs_id;
				$imei_or_serial_no = $onerow->imei_or_serial_no;
				
				$str1 = "Repair T$onerow->ticket_no - $imei_or_serial_no";
				$results[] = array( 'lv'=> $imei_or_serial_no,
									'label' => $str1,
									't' =>'r',
									'i' =>$repairs_id
									);				
			}
		} 				
	
		//============================For Order ID Searching=============================//			
		if(strtoupper(substr($keyword_search, 0, 1))=='O' && is_numeric(substr($keyword_search, 1, 1))){
			$searchorderstr = substr($keyword_search, 1, 50);
			$sql = "SELECT pos_id, invoice_no FROM pos WHERE accounts_id = $accounts_id AND invoice_no LIKE CONCAT(:keyword_search, '%') AND pos_publish = 1 AND pos_type = 'Order' AND order_status = 1 ORDER BY invoice_no ASC";
			$query = $this->db->query($sql, array('keyword_search'=>$searchorderstr));
			if($query){
				while($onerow = $query->fetch(PDO::FETCH_OBJ)){
					
					$pos_id = $onerow->pos_id;
					$invoice_no = stripslashes($onerow->invoice_no);
					
					if($invoice_no =='' || $invoice_no ==0){}
					else{
						$str2 = "Order - $invoice_no";
						$results[] = array( 'lv'=> $pos_id,
											'label' => $str2,
											't' =>'o',
											'i' =>$invoice_no
											);
					}
				}
			} 				
		}
		
		//============================For Invoice No Searching=============================//			
		if(strtoupper(substr($keyword_search, 0, 1))=='S' && is_numeric(substr($keyword_search, 1, 1))){
			$searchorderstr = substr($keyword_search, 1, 50);
			$sql = "SELECT pos_id, invoice_no FROM pos WHERE accounts_id = $accounts_id AND invoice_no LIKE CONCAT(:keyword_search, '%') AND pos_publish = 1 AND (pos_type != 'Order' or (pos_type = 'Order' AND order_status = 2)) ORDER BY invoice_no ASC";
			$query = $this->db->query($sql, array('keyword_search'=>$searchorderstr));
			if($query){
				while($onerow = $query->fetch(PDO::FETCH_OBJ)){
					
					$pos_id = $onerow->pos_id;
					$invoice_no = stripslashes($onerow->invoice_no);
					
					if($invoice_no =='' || $invoice_no ==0){}
					else{
						$str2 = "Invoice - $invoice_no";
						$results[] = array( 'lv'=> $pos_id,
											'label' => $str2,
											't' =>'s',
											'i' =>$invoice_no
											);
					}
				}
			} 				
		}
		
		//============================For Purchase Order Searching=============================//			
		if(strtoupper(substr($keyword_search, 0, 1))=='P' && is_numeric(substr($keyword_search, 1, 1))){
			$searchorderstr = substr($keyword_search, 1, 50);
			$sql = "SELECT po_id, po_number FROM po WHERE accounts_id = $accounts_id AND po_number LIKE CONCAT(:keyword_search, '%') AND po_publish = 1 ORDER BY po_number ASC";
			$query = $this->db->query($sql, array('keyword_search'=>$searchorderstr));
			if($query){
				while($onerow = $query->fetch(PDO::FETCH_OBJ)){
					
					$po_id = $onerow->po_id;
					$po_number = stripslashes($onerow->po_number);
					
					if($po_number =='' || $po_number ==0){}
					else{
						$str2 = "PO - $po_number";
						$results[] = array( 'lv'=> $po_id,
											'label' => $str2,
											't' =>'p',
											'i' =>$po_number
											);
					}
				}
			} 				
		}
		
		//============================For Ticket Number Searching=============================//			
		if(strtoupper(substr($keyword_search, 0, 1))=='T' && is_numeric(substr($keyword_search, 1, 1))){
			$searchorderstr = substr($keyword_search, 1, 50);
			$sql = "SELECT repairs_id, ticket_no FROM repairs WHERE accounts_id = $accounts_id AND ticket_no LIKE CONCAT(:keyword_search, '%') AND repairs_publish = 1 ORDER BY ticket_no ASC";
			$query = $this->db->query($sql, array('keyword_search'=>$searchorderstr));
			if($query){
				while($onerow = $query->fetch(PDO::FETCH_OBJ)){
					
					$repairs_id = $onerow->repairs_id;
					$ticket_no = stripslashes($onerow->ticket_no);
					
					if($ticket_no =='' || $ticket_no ==0){}
					else{
						$str2 = "Repair - T$ticket_no";
						$results[] = array( 'lv'=> $repairs_id,
											'label' => $str2,
											't' =>'t',
											'i' =>$repairs_id
											);
					}
				}
			} 				
		}
		
		//============================For Customer Searching=============================//
		
		$customerssql = "select TRIM(CONCAT_WS(' ', first_name, last_name)) as customer_name, customers_id, contact_no, email from customers where accounts_id = $prod_cat_man";
		$seleced_search = addslashes(trim((string) $keyword_search));
		if ( $seleced_search == "" ) { $seleced_search = " "; }
		$seleced_searches = explode (" ", $seleced_search);
		if ( strpos($seleced_search, " ") === false ) {$seleced_searches[0] = $seleced_search;}
		$bindData = array();
		$num = 0;
		while ( $num < sizeof($seleced_searches) ) {
			$customerssql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no)) LIKE CONCAT('%', :seleced_search$num, '%')";
			$bindData['seleced_search'.$num] = trim((string) $seleced_searches[$num]);
			$num++;
		}
		$customerssql .=" ORDER BY first_name ASC, last_name ASC";
		$query = $this->db->query($customerssql, $bindData);
		if($query){
			while($onerow = $query->fetch(PDO::FETCH_OBJ)){
				$customers_id = $onerow->customers_id;
				$customer_name = stripslashes($onerow->customer_name);
				
				if($onerow->email !='')
					$customer_name .= " ($onerow->email)";
				elseif($onerow->contact_no !='')
					$customer_name .= " ($onerow->contact_no)";
				
				$results[] = array(	'lv'=> $customer_name,
									'label' => $customer_name,
									't' =>'c',
									'i' =>$customers_id
									);
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$results));
	}
	
	public function submitsearch(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';			
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$keyword_search = trim((string) addslashes($POST['keyword_search']??''));
		
		//============================For IMEI Number Searching=============================//
		$sql = "SELECT item_number FROM item WHERE accounts_id = $accounts_id AND item_number LIKE CONCAT(:keyword_search, '%') AND item_publish = 1 ORDER BY item_number ASC LIMIT 0, 1";
		$query = $this->db->querypagination($sql, array('keyword_search'=>$keyword_search));
		if($query){
			foreach($query as $onerow){
				$returnStr = '/IMEI/view/'.str_replace('/', '%5C', $onerow['item_number']);
			}
		}
		
		//============================For Order ID Searching=============================//			
		if(strtoupper(substr($keyword_search, 0, 1))=='O' && is_numeric(substr($keyword_search, 1, 1))){
			$searchorderstr = substr($keyword_search, 1, 50);
			$sql = "SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id AND invoice_no LIKE CONCAT(:keyword_search, '%') AND pos_publish = 1 AND pos_type = 'Order' AND order_status = 1 ORDER BY invoice_no ASC LIMIT 0, 1";
			$query = $this->db->querypagination($sql, array('keyword_search'=>$searchorderstr));
			if($query){
				foreach($query as $onerow){
					$returnStr = '/Orders/edit/'.$onerow['invoice_no'];
				}
			} 				
		}
		
		//============================For Invoice No Searching=============================//			
		if(strtoupper(substr($keyword_search, 0, 1))=='S' && is_numeric(substr($keyword_search, 1, 1))){
			$searchorderstr = substr($keyword_search, 1, 50);
			$sql = "SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id AND invoice_no LIKE CONCAT(:keyword_search, '%') AND pos_publish = 1 AND (pos_type != 'Order' or (pos_type = 'Order' AND order_status = 2)) ORDER BY invoice_no ASC LIMIT 0, 1";
			$query = $this->db->querypagination($sql, array('keyword_search'=>$searchorderstr));
			if($query){
				foreach($query as $onerow){
					$returnStr = '/Invoices/view/'.$onerow['invoice_no'];
				}
			} 				
		}
		
		//============================For Purchase Order Searching=============================//			
		if(strtoupper(substr($keyword_search, 0, 1))=='P' && is_numeric(substr($keyword_search, 1, 1))){
			$searchorderstr = substr($keyword_search, 1, 50);
			$sql = "SELECT po_number FROM po WHERE accounts_id = $accounts_id AND po_number LIKE CONCAT(:keyword_search, '%') AND po_publish = 1 ORDER BY po_number ASC LIMIT 0, 1";
			$query = $this->db->querypagination($sql, array('keyword_search'=>$searchorderstr));
			if($query){
				foreach($query as $onerow){
					$returnStr = '/Purchase_orders/edit/'.$onerow['po_number'];
				}
			} 				
		}
		
		//============================For Ticket Number Searching=============================//			
		if(strtoupper(substr($keyword_search, 0, 1))=='T' && is_numeric(substr($keyword_search, 1, 1))){
			$searchorderstr = substr($keyword_search, 1, 50);
			$sql = "SELECT repairs_id FROM repairs WHERE accounts_id = $accounts_id AND ticket_no LIKE CONCAT(:keyword_search, '%') AND repairs_publish = 1 ORDER BY ticket_no ASC LIMIT 0, 1";
			$query = $this->db->querypagination($sql, array('keyword_search'=>$searchorderstr));
			if($query){
				foreach($query as $onerow){
					$returnStr = '/Repairs/edit/'.$onerow['repairs_id'];
				}
			} 				
		}
		
		//============================For Customer Searching=============================//
			
		$customerssql = "SELECT customers_id FROM customers WHERE accounts_id = $prod_cat_man";
		$seleced_search = addslashes(trim((string) $keyword_search));
		if ( $seleced_search == "" ) { $seleced_search = " "; }
		$seleced_searches = explode (" ", $seleced_search);
		if ( strpos($seleced_search, " ") === false ) {$seleced_searches[0] = $seleced_search;}
		$bindData = array();
		$num = 0;
		while ( $num < sizeof($seleced_searches) ) {
			$customerssql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no)) LIKE CONCAT('%', :seleced_search$num, '%')";
			$bindData['seleced_search'.$num] = trim((string) $seleced_searches[$num]);
			$num++;
		}
		$customerssql .= " ORDER BY first_name ASC, last_name ASC LIMIT 0, 1";
		$customersquery = $this->db->querypagination($customerssql, $bindData);
		if($customersquery){
			foreach($customersquery as $onerow){
				$returnStr = '/Customers/view/'.$onerow['customers_id'];
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}		
}
?>