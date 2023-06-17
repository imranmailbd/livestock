<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Purchase_orders{
	protected $db;
	private int $page, $totalRows, $suppliers_id, $po_id;
	private string $sorting_type, $view_type, $keyword_search, $history_type;
	private array $supOpt, $actFeeTitOpt;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
	private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$ssorting_type = $this->sorting_type;
		$sview_type = $this->view_type;
		$ssuppliers_id = $this->suppliers_id;
		$keyword_search = $this->keyword_search;
		
		$bindData = array();
		$filterSql = "";
		$bindData = array();
		if(in_array($sview_type, array('Open', 'Closed'))){
			$filterSql .= " AND status = :sview_type";
			$bindData['sview_type'] = $sview_type;
		}
		elseif(strcmp($sview_type, 'return_po')==0){
			$filterSql .= " AND return_po = 1";
		}
		elseif(strcmp($sview_type, 'unpaid_po')==0){
			$filterSql .= " AND date_paid IN ('1000-01-01', '0000-00-00') AND status != 'Cancel'";
		}
		
		if($ssuppliers_id>0){
			$filterSql .= " AND supplier_id = :ssuppliers_id";
			$bindData['ssuppliers_id'] = $ssuppliers_id;
		}
			
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			$bindData['po_number'] = str_replace('p', '', strtolower($keyword_search));
			
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND (po_number LIKE CONCAT('%', :po_number, '%') OR TRIM(CONCAT_WS(' ', lot_ref_no, paid_by, suppliers_invoice_no)) LIKE CONCAT('%', :keyword_search$num, '%'))";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$queryObj = $this->db->query("SELECT COUNT(po_id) AS totalrows FROM po WHERE accounts_id = $accounts_id $filterSql AND transfer = 0", $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}

		$supOpts = array();
		$expSql = "SELECT supplier_id FROM po WHERE accounts_id = $accounts_id $filterSql AND transfer = 0 GROUP BY supplier_id";
		$expObj = $this->db->query($expSql, $bindData);
		if($expObj){
			while($oneRow = $expObj->fetch(PDO::FETCH_OBJ)){
				$supOpts[$oneRow->supplier_id] = '';
			}
		}
		
		$supOpt = array();
		if(count($supOpts)>0){
			$suppliersObj = $this->db->query("SELECT suppliers_id, company, first_name, last_name FROM suppliers WHERE suppliers_id IN (".implode(', ', array_keys($supOpts)).") ORDER BY company ASC, first_name ASC, last_name ASC", array());
			if($suppliersObj){
				while($oneRow = $suppliersObj->fetch(PDO::FETCH_OBJ)){
					$optval = $oneRow->suppliers_id;
					$optlabel = trim(stripslashes("$oneRow->company $oneRow->first_name $oneRow->last_name"));
					$supOpt[$optval] = $optlabel;
				}
			}
		}
		
		$this->totalRows = $totalRows;
		$this->supOpt = $supOpt;
	}
	
    private function loadTableRows(){
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$limit = $_SESSION["limit"]??'auto';
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$ssorting_type = $this->sorting_type;
		$sview_type = $this->view_type;
		$ssuppliers_id = $this->suppliers_id;
		$keyword_search = $this->keyword_search;

		$sortingTypeData = array(0=>'po_datetime DESC, po_number DESC', 
								1=>'po_datetime DESC', 
								2=>'po_number DESC', 
								3=>'date_expected ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$filterSql = "";
		$bindData = array();
		if(in_array($sview_type, array('Open', 'Closed'))){
			$filterSql .= " AND status = :sview_type";
			$bindData['sview_type'] = $sview_type;
		}
		elseif(strcmp($sview_type, 'return_po')==0){
			$filterSql .= " AND return_po = 1";
		}
		elseif(strcmp($sview_type, 'unpaid_po')==0){
			$filterSql .= " AND date_paid IN ('1000-01-01', '0000-00-00') AND status != 'Cancel'";
		}
		
		if($ssuppliers_id>0){
			$filterSql .= " AND supplier_id = :ssuppliers_id";
			$bindData['ssuppliers_id'] = $ssuppliers_id;
		}
			
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			$bindData['po_number'] = str_replace('p', '', strtolower($keyword_search));
			
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND (po_number LIKE CONCAT('%', :po_number, '%') OR TRIM(CONCAT_WS(' ', lot_ref_no, paid_by, suppliers_invoice_no)) LIKE CONCAT('%', :keyword_search$num, '%'))";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * FROM po WHERE accounts_id = $accounts_id AND transfer =0 $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		
		$query = $this->db->querypagination($sqlquery, $bindData);
		//return array($sql,  $bindData);
		$tabledata = array();
		if($query){
			$poId = $suppliersId = array();
			foreach($query as $oneRow){
				$poId[] = $oneRow['po_id'];
				if(empty($suppliersId) || !in_array($oneRow['supplier_id'], $suppliersId)){
					$suppliersId[] = $oneRow['supplier_id'];
				}
			}

			$suppliersData = array();
			if(!empty($suppliersId)){
				$suppliersObj = $this->db->query("SELECT suppliers_id, company, first_name, last_name FROM suppliers WHERE suppliers_id IN (".implode(', ', $suppliersId).")", array());
				if($suppliersObj){
					while($suppliersrow = $suppliersObj->fetch(PDO::FETCH_OBJ)){
						$company = trim((string) stripslashes("$suppliersrow->company"));
						if($suppliersrow->first_name !='' || $suppliersrow->last_name !=''){
							$company .= trim(stripslashes(", $suppliersrow->first_name $suppliersrow->last_name"));
						}
						$suppliersData[$suppliersrow->suppliers_id] = $company;
					}
				}
			}

			$totalCostData = array();
			if($poId !='' && is_array($poId)){
				$poObj = $this->db->query("SELECT po_id, SUM(received_qty*cost) AS totalCost FROM po_items WHERE po_id IN (".implode(', ', $poId).") GROUP BY po_id", array());

				if($poObj){
					while($poOneRow = $poObj->fetch(PDO::FETCH_OBJ)){
						$totalCostData[$poOneRow->po_id] = round($poOneRow->totalCost,2);
					}
				}
			}

			foreach($query as $rowpo){
				$po_id = $rowpo['po_id'];
				$total = $totalCostData[$po_id]??0;

				$po_number = intval($rowpo['po_number']);
				$suppliers_id = $rowpo['supplier_id'];
				$suppliername = '&nbsp;';
				if($suppliers_id>0 && array_key_exists($suppliers_id, $suppliersData)){
					$suppliername = $suppliersData[$suppliers_id];
				}
				$lot_ref_no = $rowpo['lot_ref_no'];
				$paid_by = $rowpo['paid_by'];
				$tax_is_percent = intval($rowpo['tax_is_percent']);
				$taxes = round($rowpo['taxes'], (2+$tax_is_percent));
				$shipping = round($rowpo['shipping'],2);
				$status = $rowpo['status'];
				$return_po = intval($rowpo['return_po']);
				
				$tabledata[] = array($po_number, $rowpo['po_datetime'], $po_number, $lot_ref_no, $suppliername, $tax_is_percent, $taxes, $shipping, $total, $rowpo['date_expected'], $return_po, $status);
			}
		}
		
		return $tabledata;
    }
	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$ssorting_type = $POST['ssorting_type']??0;
		$sview_type = $POST['sview_type']??'Open';
		$ssuppliers_id = intval($POST['ssuppliers_id']??0);
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->sorting_type = $ssorting_type;
		$this->view_type = $sview_type;
		$this->suppliers_id = $ssuppliers_id;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['supOpt'] = $this->supOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}
	
	public function add(){}
	
	public function AJ_add_MoreInfo(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$suppliers_id = $_SESSION["supplier_id"]??0;
		$currency = $_SESSION["currency"]??'৳';

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['supplier_name'] = '';
		$jsonResponse['suppliers_id'] = 0;
		$supOpt = array();
		if($suppliers_id>0){
			$jsonResponse['suppliers_id'] = $suppliers_id;

			$supplierObj = $this->db->query("SELECT first_name, last_name FROM suppliers WHERE suppliers_id = $suppliers_id", array());
			if($supplierObj){
				$supplierrow = $supplierObj->fetch(PDO::FETCH_OBJ);
				$supplier_name = trim(stripslashes("$supplierrow->first_name $supplierrow->last_name"));
				$jsonResponse['supplier_name'] = $supplier_name;
			}
		}
		if($jsonResponse['supplier_name'] ==''){
			$supplierssql = "SELECT company, email, suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company !='' AND suppliers_publish = 1 ORDER BY company ASC, email ASC";
			$suppliersObj = $this->db->query($supplierssql, array());
			if($suppliersObj){
				while($onerow = $suppliersObj->fetch(PDO::FETCH_OBJ)){
					$suppliers_id = $onerow->suppliers_id;
					$company = stripslashes($onerow->company);					
					if($onerow->email !='')
						$company .= " ($onerow->email)";				
					
					$supOpt[$suppliers_id] = $company;
				}
			}
		}
		$jsonResponse['supOpt'] = $supOpt;
		$jsonResponse['date_expected'] = date('Y-m-d');
		$jsonResponse['invoice_date'] = date('Y-m-d');
		$jsonResponse['date_paid'] = date('Y-m-d');
		
		return json_encode($jsonResponse);
	}
	
	public function returnPO(){}
	
	public function confirmReturn(){}
	
	public function AJ_confirmReturn_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$POST = json_decode(file_get_contents('php://input'), true);

		$po_number = intval($POST['po_number']);
		$po_id = $suppliers_id = $lot_ref_no = $paid_by = $product_id = $qty = $cost = $po_items_id = 0;
		$item_type = $item_number = '';	
		$poOneRow = false;	
		if(!empty($po_number) && $po_number>0){
			$sqlquery = "SELECT po.po_id, po.supplier_id, po.lot_ref_no, po.paid_by, po_items.po_items_id, po_items.product_id, po_items.item_type, po_items.received_qty, po_items.cost FROM po, po_items WHERE po.accounts_id = $accounts_id AND po.po_id = po_items.po_id AND po.return_po = 1 AND po.po_number = :po_number ORDER BY po_items.po_items_id ASC";
			//$jsonResponse['sqlquery'] = $sqlquery;
			$query = $this->db->query($sqlquery, array('po_number'=>$po_number), 1);
			if($query){
				while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
					$po_id = $oneRow->po_id;
					$suppliers_id = $oneRow->supplier_id;
					$lot_ref_no = $oneRow->lot_ref_no;
					$paid_by = $oneRow->paid_by;
					$po_items_id = $oneRow->po_items_id;
					$product_id = $oneRow->product_id;
					$item_type = $oneRow->item_type;
					if($item_type !='' && $item_type == 'livestocks'){
						$item_number = '';
						$sqlitem = "SELECT i.item_number FROM item i, po_cart_item pci WHERE pci.return_po_items_id = $po_items_id AND i.accounts_id = $accounts_id AND i.item_id = pci.item_id ORDER BY i.item_id ASC";
						$itemObj = $this->db->query($sqlitem, array());
						if($itemObj){
							$item_number = $itemObj->fetch(PDO::FETCH_OBJ)->item_number;
						}
					}
					else{
						$qty = $oneRow->received_qty;
					}				
					$cost = $oneRow->cost;
				}
			}			
		}
		
		$supplier_name = '';
		if($suppliers_id>0){ 
			$supplierObj = $this->db->query("SELECT company FROM suppliers WHERE suppliers_id = $suppliers_id", array());
			if($supplierObj){
				$supplier_name = $supplierObj->fetch(PDO::FETCH_OBJ)->company;
			}
		}
		
		$product_name = '';
		if($product_id>0){ 
			$product_name = '';
			$productObj = $this->db->query("SELECT p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id = $prod_cat_man", array());
			if($productObj){
				$pOneRow = $productObj->fetch(PDO::FETCH_OBJ);
				$product_name = stripslashes($pOneRow->product_name);
				$manufacturer_name = $pOneRow->manufacture;
				if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}
				
				$colour_name = $pOneRow->colour_name;
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $pOneRow->storage;
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $pOneRow->physical_condition_name;
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
				
				$sku = $pOneRow->sku;
				if($sku !=''){$product_name .= " ($sku)";}
			}											
		}
		
		$readonly = '';
		if($item_type !='' && $item_type == 'livestocks'){
			$readonly = 'readonly';
			$qty = $item_number;
		}

		$jsonResponse['supplier_name'] = $supplier_name;
		$jsonResponse['lot_ref_no'] = $lot_ref_no;
		$jsonResponse['paid_by'] = $paid_by;
		$jsonResponse['product_name'] = $product_name;
		$jsonResponse['readonly'] = $readonly;
		$jsonResponse['qty'] = $qty;
		$jsonResponse['po_id'] = $po_id;
		$jsonResponse['po_items_id'] = $po_items_id;
		$jsonResponse['cost'] = $cost;
		$jsonResponse['item_type'] = $item_type;
		$jsonResponse['po_number'] = $po_number;
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_edit_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$user_id = $_SESSION['user_id']??0;
		$currency = $_SESSION["currency"]??'৳';
		$po_number = $POST['po_number'];
		
		$jsonResponse['po_number'] = $po_number;
		$sql = "SELECT * FROM po WHERE po_number = :po_number AND accounts_id = $accounts_id";
		$poObj = $this->db->query($sql, array('po_number'=>$po_number));
		if($poObj){
			$po_onerow = $poObj->fetch(PDO::FETCH_OBJ);
			$po_id = $po_onerow->po_id;
			$jsonResponse['po_id'] = intval($po_id);
			$jsonResponse['po_number'] = intval($po_number);

			$status = $po_onerow->status;
			$jsonResponse['status'] = $status;

			$company = $suppliername = $supplieremail = '';
			$suppliers_id = $po_onerow->supplier_id;
			$supPermission = 0;
			$supplierObj = $this->db->query("SELECT first_name, last_name, email, company FROM suppliers WHERE suppliers_id = $suppliers_id", array());
			if($supplierObj){
				$supplier_row = $supplierObj->fetch(PDO::FETCH_OBJ);
				$first_name = $supplier_row->first_name;
				$last_name = $supplier_row->last_name;
				$suppliername = stripslashes("$first_name $last_name");
				$supplieremail = $supplier_row->email;
				$company = stripslashes($supplier_row->company);
				$supPermission = 1;
				if(!empty($_SESSION["allowed"]) && !array_key_exists(25, $_SESSION["allowed"])) {
					$supPermission = 0;
				}
			}
			$jsonResponse['suppliers_id'] = intval($suppliers_id);
			$jsonResponse['suppliername'] = $suppliername;
			$jsonResponse['supplieremail'] = $supplieremail;
			$jsonResponse['company'] = $company;
			$jsonResponse['supPermission'] = $supPermission;
			$jsonResponse['date_expected'] = $po_onerow->date_expected;
			$jsonResponse['cols'] = 7;
			
			$pCustomFields = 0;
			$queryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'product'", array());
			if($queryObj){
				$pCustomFields = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			$jsonResponse['pCustomFields'] = intval($pCustomFields);
			
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
			$jsonResponse['shipping'] = round($po_onerow->shipping,2);
			$jsonResponse['lot_ref_no'] = $po_onerow->lot_ref_no;
			$jsonResponse['paid_by'] = $po_onerow->paid_by;

			$jsonResponse['cartsData'] = $this->loadPOCartData($po_id, $status);
			$jsonResponse['tax_is_percent'] = $tax_is_percent = intval($po_onerow->tax_is_percent);
			$jsonResponse['taxes'] = round($po_onerow->taxes, (2+$tax_is_percent));
			$jsonResponse['return_po'] = intval($po_onerow->return_po);
			
		}
		else{
			$jsonResponse['login'] = 'Purchase_orders/lists/';
		}
		return json_encode($jsonResponse);
	}

	public function edit($segment4name){
		$accounts_id = $_SESSION['accounts_id']??0;
		$po_number = $segment4name;
		
		$sql = "SELECT transfer FROM po WHERE po_number = :po_number AND accounts_id = $accounts_id";
		$poObj = $this->db->query($sql, array('po_number'=>$po_number));
		if($poObj){
			$po_onerow = $poObj->fetch(PDO::FETCH_OBJ);
			if($po_onerow->transfer>0){
				return "<meta http-equiv = \"refresh\" content = \"0; url = /Inventory_Transfer/edit/$po_number\" />";
			}
		}
	}
	
	private function filterHAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$po_id = $this->po_id;
		$shistory_type = $this->history_type;
		
		$filterSql = '';
		$bindData = array();
		$bindData['po_id'] = $po_id;
		$po_number = 0;
		$poObj = $this->db->query("SELECT po_number FROM po WHERE po_id = $po_id AND accounts_id = $accounts_id", array());
		if($poObj){
			$po_number = $poObj->fetch(PDO::FETCH_OBJ)->po_number;
		}
		
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT COUNT(po_id) AS totalrows FROM po 
							WHERE po_id = :po_id AND accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'po' AND table_id = :po_id";
			}
			elseif(strcmp($shistory_type, 'Product Added')==0){
				$filterSql = "SELECT COUNT(po_items_id) AS totalrows FROM po_items 
						WHERE po_id = :po_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'po' AND record_id = :po_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Purchase_orders/edit/', :po_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
				$bindData['po_id'] = $po_number;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Purchase_orders/edit/', $po_number) 
					UNION ALL 
						SELECT COUNT(po_id) AS totalrows FROM po 
						WHERE po_id = :po_id and accounts_id = $accounts_id 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'po' AND record_id = :po_id 
					UNION ALL 
						SELECT COUNT(po_items_id) AS totalrows FROM po_items 
						WHERE po_id = :po_id 
					UNION ALL 
						SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'po' AND table_id = :po_id";
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
			WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Purchase_orders/edit/', $po_number) 
		UNION ALL 
			SELECT 'Purchase Order Created' AS afTitle FROM po 
			WHERE po_id = :po_id and accounts_id = $accounts_id 
		UNION ALL 
			SELECT 'Track Edits' AS afTitle FROM track_edits 
			WHERE accounts_id = $accounts_id AND record_for = 'po' AND record_id = :po_id 
		UNION ALL 
			SELECT 'Product Added' AS afTitle FROM po_items 
			WHERE po_id = :po_id 
		UNION ALL 
			SELECT 'Notes Created' AS afTitle FROM notes 
			WHERE accounts_id = $accounts_id AND note_for = 'po' AND table_id = :po_id";
		$tableObj = $this->db->query($Sql, array('po_id'=>$po_id));
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
		$po_id = $this->po_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		
		$bindData = array();
		$bindData['po_id'] = $po_id;            
		$po_number = 0;
		$poObj = $this->db->query("SELECT po_number FROM po WHERE po_id = $po_id AND accounts_id = $accounts_id", array());
		if($poObj){
			$po_number = $poObj->fetch(PDO::FETCH_OBJ)->po_number;
		}
		
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT 'po' AS tablename, po_datetime AS tabledate, po_id AS table_id, 'Purchase Order Created' AS activity_feed_title FROM po 
					WHERE po_id = :po_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'po' AND table_id = :po_id";
			}
			elseif(strcmp($shistory_type, 'Product Added')==0){
				$filterSql = "SELECT 'po_items' AS tablename, created_on AS tabledate, po_items_id AS table_id, 'Product Added' AS activity_feed_title FROM po_items 
							WHERE po_id = :po_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'po' AND record_id = :po_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Purchase_orders/edit/', :po_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
				$bindData['po_id'] = $po_number;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Purchase_orders/edit/', $po_number)  
					UNION ALL 
					SELECT 'po' AS tablename, po_datetime AS tabledate, po_id AS table_id, 'Purchase Order Created' AS activity_feed_title FROM po 
						WHERE po_id = :po_id AND accounts_id = $accounts_id 
					UNION ALL 
					SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'po' AND table_id = :po_id 
					UNION ALL 
					SELECT 'po_items' AS tablename, created_on AS tabledate, po_items_id AS table_id, 'Product Added' AS activity_feed_title FROM po_items 
						WHERE po_id = :po_id 
					UNION ALL 
					SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'po' AND record_id = :po_id 
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
		$spo_id = intval($POST['spo_id']??0);
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->po_id = $spo_id;
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
		
		$po_number = intval($segment5name);
		$segment6name = $GLOBALS['segment6name'];
		$poObj = $this->db->query("SELECT po_id FROM po WHERE po_number = :po_number AND accounts_id = $accounts_id", array('po_number'=>$po_number),1);
		if($poObj){
			$po_id = $poObj->fetch(PDO::FETCH_OBJ)->po_id;
			$Printing = new Printing($this->db);
			
			if($segment4name=='barcode'){
				$htmlStr .= $Printing->labelsInfo('PO', 'HTML');
			}
			elseif($segment4name=='label_MoreInfo'){
				$Common = new Common($this->db);
				$jsonResponse = array();
				$jsonResponse['login'] = '';
				$commonInfo = $Printing->labelsInfo('PO', 'commonInfo');
								
				$labelsInfo = array();
				$sqlquery = "SELECT product_id, item_type, po_items_id, received_qty FROM po_items WHERE po_id = $po_id AND item_type !='one_time' ORDER BY po_items_id ASC";
				$query = $this->db->query($sqlquery, array());
				if($query){					
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						$product_id = $row->product_id;
						$item_type = $row->item_type;
						$ProductName = $Price = $ProdBarcode = $custom_data = '';
						if($product_id>0){
							$productObj = $this->db->query("SELECT p.*, manufacturer.name AS manufacture FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_id = :product_id", array('product_id'=>$product_id),1);
							if($productObj){
								$product_onerow = $productObj->fetch(PDO::FETCH_OBJ);
								
								$ProductName = stripslashes(trim((string) $product_onerow->product_name));
								$manufacturer_name = stripslashes(trim((string) $product_onerow->manufacture));
								if($manufacturer_name !=''){$ProductName = stripslashes(trim($manufacturer_name.' '.$ProductName));}

								if($item_type=='livestocks'){
									$colour_name = $product_onerow->colour_name;
									if($colour_name !=''){$ProductName .= ' '.$colour_name;}
									
									$storage = $product_onerow->storage;
									if($storage !=''){$ProductName .= ' '.$storage;}
									
									$physical_condition_name = $product_onerow->physical_condition_name;
									if($physical_condition_name !=''){$ProductName .= ' '.$physical_condition_name;}
								}

								$inventoryObj = $this->db->query("SELECT regular_price FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_onerow->product_id", array());
								if($inventoryObj){
									$regular_price = $inventoryObj->fetch(PDO::FETCH_OBJ)->regular_price;
									if($regular_price>0){
										$Price = $currency.number_format($regular_price,2);
									}
								}
								
								$ProdBarcode = stripslashes(trim((string) $product_onerow->sku));
								$custom_data = stripslashes(trim((string) $product_onerow->custom_data));
							}
						}
						
						$po_items_id = $row->po_items_id;
						$received_qty = $row->received_qty;

						if($received_qty<0){$received_qty *= -1;}
						if($item_type=='livestocks'){
							$sqlitem = "SELECT i.item_id FROM item i, po_cart_item pci WHERE (pci.po_items_id = $po_items_id OR pci.return_po_items_id = $po_items_id) AND i.accounts_id = $accounts_id AND i.item_id = pci.item_id ORDER BY i.item_id ASC";
							$query1 = $this->db->query($sqlitem, array());
							if($query1){
								while($item_onerow=$query1->fetch(PDO::FETCH_OBJ)){
									if($item_onerow->item_id>0){
										$itemObj = $this->db->query("SELECT carrier_name, item_number, custom_data FROM item WHERE accounts_id = $accounts_id AND item_id = :item_id", array('item_id'=>$item_onerow->item_id),1);
										if($itemObj){
											$oneItemRow = $itemObj->fetch(PDO::FETCH_OBJ);
											$deviceName = $ProductName;
											if($oneItemRow->carrier_name !=''){$deviceName .= ' '.$oneItemRow->carrier_name;}
											$Barcode = $oneItemRow->item_number;
											$customFieldsData = $Common->customFormFields('devices', $oneItemRow->custom_data);

											$labelsInfo[] = array('item_type'=>$item_type, 'ProductName'=>$deviceName, 'Price'=>$Price, 'Barcode'=>$Barcode, 'customFieldsData'=>$customFieldsData);
										}
									}
								}
							}
						}
						else{	
							$customFieldsData = $Common->customFormFields('product', $custom_data);
							for($r=0; $r<$received_qty; $r++){
								if($product_id>0){
									$labelsInfo[] = array('item_type'=>$item_type, 'ProductName'=>$ProductName, 'Price'=>$Price, 'Barcode'=>$ProdBarcode, 'customFieldsData'=>$customFieldsData);
								}
							}
						}			
					}
				}

				$jsonResponse['commonInfo'] = $commonInfo;
				$jsonResponse['labelsInfo'] = $labelsInfo;

				return json_encode($jsonResponse);
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

	public function AJ_prints_large_MoreInfo(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$POST = json_decode(file_get_contents('php://input'), true);
		$po_number = $POST['po_number'];
		$po_number = intval($po_number);
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$poObj = $this->db->query("SELECT po_id FROM po WHERE po_number = :po_number AND accounts_id = $accounts_id", array('po_number'=>$po_number),1);
		if($poObj){
			$po_id = $poObj->fetch(PDO::FETCH_OBJ)->po_id;
			$Printing = new Printing($this->db);
			$jsonResponse = $Printing->poInvoicesInfo($po_id);
		}		
		return json_encode($jsonResponse);
	}
	
	public function AJsend_POEmail(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$po_id = intval($POST['po_id']??0);
		$email_address = $POST['email_address']??'';
			
		$returnStr = '';
		if(empty($email_address) || strlen($email_address)<6){
			$returnStr = 'notSendMail';
		}
		else{
			
			$Printing = new Printing($this->db);
			
			$customer_service_email = '';
			$accObj = $this->db->query("SELECT customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accObj){
				$customer_service_email = $accObj->fetch(PDO::FETCH_OBJ)->customer_service_email;
			}
			if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
			$mail_body = $Printing->poInvoicesInfo($po_id, 'Purchase_orders', 1);
			
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $this->db->supportEmail('Host');
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = $this->db->supportEmail('Username');
			$mail->Password = $this->db->supportEmail('Password');
			
			$mail->addReplyTo($customer_service_email, $_SESSION["company_name"]);               
			$mail->setFrom($this->db->supportEmail('do_not_reply'), $_SESSION["company_name"]);
			$mail->clearAddresses();
			$mail->addAddress($email_address, "");
			$mail->Subject = $this->db->translate('Purchase Orders');
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			$mail->Body = $mail_body;
			if($mail->send()){
				$note_for = $this->db->checkCharLen('notes.note_for', 'po');
				$noteData = array();
				$noteData['table_id'] = $po_id;
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
	
	public function AJ_save_returnpo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = 0;
		$savemsg = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;			
		$accounts_id = $_SESSION["accounts_id"]??0;
		$suppliers_id = intval($POST['supplier_id']??0);
		
		$poData = array();
		$poData['supplier_id'] = intval($POST['supplier_id']??0);
		if($POST['date_expected'] !=''){
			$poData['date_expected'] = date('Y-m-d', strtotime($POST['date_expected']));
		}
		else{
			$poData['date_expected'] = '1000-01-01';
		}
		$lot_ref_no = $this->db->checkCharLen('po.lot_ref_no', trim((string) $POST['lot_ref_no']??''));
		$poData['lot_ref_no'] = $lot_ref_no;
		$paid_by = '';//$this->db->checkCharLen('po.paid_by', trim((string) $POST['paid_by']??''));
		$poData['paid_by'] = $paid_by;
		$poData['tax_is_percent'] = $tax_is_percent = intval($POST['tax_is_percent']??1);
		$taxes = floatval($POST['taxes']??0.00);
		if(empty($taxes)){$taxes = 0.000;}
		$poData['taxes'] = round($taxes, (2+$tax_is_percent));
		$shipping = floatval($POST['shipping']??0.00);
		if(empty($shipping)){$shipping = 0.00;}
		$poData['shipping'] = round($shipping,2);
		$suppliers_invoice_no = $this->db->checkCharLen('po.suppliers_invoice_no', trim((string) $POST['suppliers_invoice_no']??''));
		$poData['suppliers_invoice_no'] = $suppliers_invoice_no;
		if($POST['invoice_date'] !=''){
			$poData['invoice_date'] = date('Y-m-d', strtotime($POST['invoice_date']??'1000-01-01'));
		}
		else{
			$poData['invoice_date'] = '1000-01-01';
		}
		if($POST['date_paid'] !=''){
			$poData['date_paid'] = date('Y-m-d', strtotime($POST['date_paid']??'1000-01-01'));
		}
		else{
			$poData['date_paid'] = '1000-01-01';
		}
		$poData['po_datetime'] = date('Y-m-d H:i:s');
		$poData['last_updated'] = date('Y-m-d H:i:s');
		$poData['accounts_id'] = $accounts_id;
		$poData['user_id'] = $_SESSION["user_id"]??0;
		$poData['return_po'] = 1;
		$status = $this->db->checkCharLen('po.status', 'Open');
		$poData['status'] = $status;
		$poData['transfer'] = 0;
		$poData['po_number'] = 0;
		
		$po_id = $this->db->insert('po', $poData);
		
		//=============collect user last new Ticket no================//
		if($po_id){
			$po_number = 1;
			$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
			if($poObj){
				$po_number = $poObj[0]['po_number']+1;
				$this->db->update('po', array('po_number'=>$po_number), $po_id);
			}			
		
			$product_id = intval($POST['product_id']??0);
			$product_type = $POST['product_type']??'';
			$qty_or_imei = intval($POST['qty_or_imei']??0);
			$item_id = intval($POST['item_id']??0);
			
			//=======================for insert into po_items========================//
			$productObj = $this->db->query("SELECT product_id FROM product WHERE product_id = :product_id AND accounts_id = $prod_cat_man AND product_publish = 1", array('product_id'=>$product_id),1);
			if($productObj){
				$product_id = $productObj->fetch(PDO::FETCH_OBJ)->product_id;
				
				$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory, ave_cost FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
				if($inventoryObj){
					$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
					
					$item_type = 'product';
					
					$cost = $inventoryrow->ave_cost;
					if($product_type=='Live Stocks' && $item_id>0){
						$item_type = 'livestocks';
						$qty = -1;
						$Common = new Common($this->db);
						$oneIMEIAveCost = $Common->oneIMEIAveCost($product_id, $item_id, date('Y-m-d H:i:s'));
						$cost = $oneIMEIAveCost[0];
					}
					else{
						$qty = floatval($qty_or_imei)*(-1);
					}
					
					$item_type = $this->db->checkCharLen('po_items.item_type', $item_type);
					$poiData =array('created_on'=>date('Y-m-d H:i:s'),
									'user_id'=>$_SESSION["user_id"],
									'po_id'=>$po_id,
									'product_id'=>$product_id,
									'item_type'=>$item_type,
									'cost'=>round($cost,2),
									'ordered_qty'=>$qty,
									'received_qty'=>$qty);
					$po_items_id = $this->db->insert('po_items', $poiData);
					if($po_items_id){
						
						//=================Product current_inventory update==============//
						if($product_type=='Live Stocks' && $item_id>0){
							
							$this->db->update('item', array('in_inventory'=>0), $item_id);
							
							$poCIArray = $this->db->querypagination("SELECT po_cart_item_id FROM po_cart_item WHERE item_id = $item_id AND return_po_items_id = 0 ORDER BY po_cart_item_id DESC LIMIT 0,1", array());
							if($poCIArray){			
								$poCIRow = $poCIArray[0];								
								$po_cart_item_id = $poCIRow['po_cart_item_id'];
								$this->db->update('po_cart_item', array('po_or_return'=>'0','return_po_items_id'=>$po_items_id), $po_cart_item_id);
							}
						}
						else{
							//=============New inventory condition =================//
							$current_inventory = $inventoryrow->current_inventory+$qty;
							$this->db->update('inventory', array('current_inventory'=>$current_inventory), $inventoryrow->inventory_id);
						}														
					}
				}
			}
			
			$id = $po_number;
			$savemsg = 'add-success';
		}
		else{
			$savemsg = 'error';
		}
	
		$array = array( 'login'=>'',
						'id'=>$id,
						'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	public function AJ_PO_Supplier_Product(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$results = array();
		$accounts_id = $_SESSION["accounts_id"]??0;
		$suppliers_id = intval($POST['supplier_id']??0);
		$product_name = $POST['product_name']??'';
		
		$extrastr = " AND p.product_type != 'Labor/Services'";
		$bindData = array();
		if($product_name !=''){
			$seleced_search = addslashes(trim((string) $product_name));
			if ( $seleced_search == "" ) { $seleced_search = " "; }
			$seleced_searches = explode (" ", $seleced_search);
			if ( strpos($seleced_search, " ") === false ) {$seleced_searches[0] = $seleced_search;}
			$num = 0;
			while ( $num < sizeof($seleced_searches) ) {
				$extrastr .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku)) LIKE CONCAT('%', :seleced_search$num, '%')";
				$bindData['seleced_search'.$num] = trim((string) $seleced_searches[$num]);
				$num++;
			}
		}
		
		$sql = "SELECT p.product_id, SUM(po_items.received_qty) AS poInventory, p.product_type, p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name 
				FROM po, po_items, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) 
				WHERE po.accounts_id = $accounts_id AND po.transfer = 0 AND po.po_id = po_items.po_id AND po_items.product_id = p.product_id AND po.return_po = 0 AND po_items.received_qty >0 
					AND po.supplier_id = $suppliers_id AND p.product_publish = 1 
					$extrastr GROUP BY p.product_id ORDER BY TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name)) ASC, p.sku ASC";
		$query = $this->db->querypagination($sql, $bindData);
		if($query){
			foreach($query as $onerow){
				$product_id = $onerow['product_id'];
				$poInventory = floatval($onerow['poInventory']);
				$product_type = $onerow['product_type'];
				$name = trim(stripslashes((string) $onerow['manufacture']));
				$product_name = trim(stripslashes((string) $name.' '.$onerow['product_name']));
				
				$colour_name = trim(stripslashes((string) $onerow['colour_name']));
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = trim(stripslashes((string) $onerow['storage']));
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = trim(stripslashes((string) $onerow['physical_condition_name']));
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$sku = $onerow['sku'];
				$current_inventory = 0;
				$inventoryObj2 = $this->db->query("SELECT current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
				if($inventoryObj2){
					$current_inventory = $inventoryObj2->fetch(PDO::FETCH_OBJ)->current_inventory;
				}
				
				$label = "$product_name ($sku)";
				if($product_type == 'Live Stocks'){
					$current_inventory = 0;
					$itemObj = $this->db->query("SELECT COUNT(item_id) AS current_inventory FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND item_publish = 1 AND in_inventory = 1", array());
					if($itemObj){
						$current_inventory = $itemObj->fetch(PDO::FETCH_OBJ)->current_inventory;
					}
					if($poInventory<$current_inventory){$current_inventory = $poInventory;}
					$label .=" ($current_inventory)";
				}
				elseif($current_inventory>0){
					if($poInventory<$current_inventory){$current_inventory = $poInventory;}
					$label .=" ($current_inventory)";
				}
				
				$results[] = array('label'=> $label,'id'=> $product_id,'prdty'=> $product_type,'inv'=> $current_inventory);
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$results));
	}
	
	public function AJ_showitem_numberDropdown(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$suppliers_id = intval($POST['supplier_id']??0);		
		$product_id = intval($POST['product_id']??0);		
		$keyword_search = addslashes($POST['keyword_search']??'');
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$results = array();
		$sqlitem = "SELECT i.item_id, i.item_number FROM item i, po_cart_item pci, po, po_items pi WHERE po.supplier_id = :supplier_id AND i.product_id = :product_id AND i.item_number LIKE CONCAT('%', :keyword_search, '%') AND i.accounts_id = $accounts_id AND i.in_inventory = 1 AND po.po_id = pi.po_id AND pi.po_items_id = pci.po_items_id AND i.item_id = pci.item_id ORDER BY i.item_number ASC";
		$itemquery = $this->db->query($sqlitem, array('product_id'=>$product_id, 'keyword_search'=>$keyword_search, 'supplier_id'=>$suppliers_id));
		if($itemquery){
			while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
				$results[] = array('label'=>$itemrow->item_number, 'item_id'=>$itemrow->item_id);
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$results));
	}
	
	public function AJ_save_po(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = 0;
		$savemsg = '';
		$poData = array();
		$poData['supplier_id'] = intval($POST['supplier_id']??0);
		if($POST['date_expected'] !=''){
			$poData['date_expected'] = date('Y-m-d', strtotime($POST['date_expected']));
		}
		else{
			$poData['date_expected'] = '1000-01-01';
		}
		$lot_ref_no = $this->db->checkCharLen('po.lot_ref_no', trim((string) $POST['lot_ref_no']??''));
		$poData['lot_ref_no'] = $lot_ref_no;
		$paid_by = '';//$this->db->checkCharLen('po.paid_by', trim((string) $POST['paid_by']??''));
		$poData['paid_by'] = $paid_by;
		$poData['tax_is_percent'] = $tax_is_percent = intval($POST['tax_is_percent']??1);
		$taxes = floatval($POST['taxes']??0.00);
		if(empty($taxes)){$taxes = 0.000;}
		$poData['taxes'] = round($taxes, (2+$tax_is_percent));
		$shipping = floatval($POST['shipping']??0.00);
		if(empty($shipping)){$shipping = 0.00;}
		$poData['shipping'] = round($shipping,2);
		$suppliers_invoice_no = $this->db->checkCharLen('po.suppliers_invoice_no', trim((string) $POST['suppliers_invoice_no']??''));
		$poData['suppliers_invoice_no'] = $suppliers_invoice_no;
		if($POST['invoice_date'] !=''){
			$poData['invoice_date'] = date('Y-m-d', strtotime($POST['invoice_date']??'1000-01-01'));
		}
		else{
			$poData['invoice_date'] = '1000-01-01';
		}
		if($POST['date_paid'] !=''){
			$poData['date_paid'] = date('Y-m-d', strtotime($POST['date_paid']??'1000-01-01'));
		}
		else{
			$poData['date_paid'] = '1000-01-01';
		}
		$poData['po_datetime'] = date('Y-m-d H:i:s');
		$poData['last_updated'] = date('Y-m-d H:i:s');
		$poData['accounts_id'] = $accounts_id = $_SESSION["accounts_id"]??0;
		$poData['user_id'] = $_SESSION["user_id"]??0;
		$poData['return_po'] = 0;
		$status = $this->db->checkCharLen('po.status', 'Open');
		$poData['status'] = $status;
		$poData['transfer'] = 0;
		$poData['po_number'] = 0;
		
		$po_id = $this->db->insert('po', $poData);           
		if($po_id){
			$po_number = 1;
			$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
			if($poObj){
				$po_number = $poObj[0]['po_number']+1;
				$this->db->update('po', array('po_number'=>$po_number), $po_id);
			}
			$id = $po_number;
			$savemsg = 'add-success';
		}
		else{
			$savemsg = 'error';
		}			
			
		$array = array( 'login'=>'','id'=>$id,'savemsg'=>$savemsg);
		return json_encode($array);
	}

	public function save_confirmReturn(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = 0;
		$savemsg = $message = '';
		//return json_encode($POST);
		$po_id = intval($POST['po_id']??0);
		$item_type = $POST['item_type']??'';
		$po_number = $POST['po_number']??'';
		$note = addslashes(trim((string) $POST['note']??''));
		$created_on = $last_updated = date('Y-m-d H:i:s');
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;			
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		$status = $this->db->checkCharLen('po.status', 'Closed');
		$updatepos = $this->db->update('po', array('status'=>$status), $po_id);
		
		$po_items_id = intval($POST['po_items_id']??0);
		$newcost = round(floatval($POST['cost']??0.00),2);
		$item_number = '';
		if($item_type=='livestocks'){
			$newreceived_qty = -1;
			$item_number = $POST['qty']??'';
		}
		else{
			$newreceived_qty = round(floatval($POST['qty']??0),2);
		}
		$newordered_qty_total = round($newcost*$newreceived_qty,2);			
		$oldqty = floatval($POST['oldqty']??0);
		if($oldqty != $newreceived_qty){
			$sql1 = "SELECT * FROM po_items WHERE po_items_id = :po_items_id";
			$query1 = $this->db->query($sql1, array('po_items_id'=>$po_items_id),1);
			if($query1){
				$po_itemsrow = $query1->fetch(PDO::FETCH_OBJ);
									
				$product_id = $po_itemsrow->product_id;
				
				$old_po_received_qty = $po_itemsrow->received_qty;
				
				$po_itemsupdatedata = array('cost'=>$newcost,
											'received_qty'=>$newreceived_qty);	
				$updatepo_items = $this->db->update('po_items', $po_itemsupdatedata, $po_items_id);
				
				$productObj = $this->db->query("SELECT product_type FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man AND product_publish = 1", array());
				if($productObj){
					$product_type = $productObj->fetch(PDO::FETCH_OBJ)->product_type;
					if($product_type !='Live Stocks'){
						$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
						if($inventoryObj){
							$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
						
							$current_inventory = $inventoryrow->current_inventory-$old_po_received_qty+$newreceived_qty;
							$this->db->update('inventory', array('current_inventory'=>$current_inventory), $inventoryrow->inventory_id);
						}
					}
					
					if($product_type =='Live Stocks'){
						$Common = new Common($this->db);
						$Common->updateMobileAvgCost($accounts_id, $po_itemsrow->po_items_id, $po_itemsrow->created_on);
					}
				}
			}
		}
		
		$po_number = 0;
		$sql = "SELECT * FROM po WHERE po_id = $po_id";
		$query = $this->db->querypagination($sql, array());
		if($query){
			$Common = new Common($this->db);
			foreach($query as $onerow){
				$supplier_id = intval($onerow['supplier_id']);
				$po_number = intval($onerow['po_number']);
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

				$BulkSMS = new BulkSMS($this->db);
				$BulkSMS->sendPOSMS($supplier_id, $po_number, $grand_total, $amountPaid, $onerow['date_expected']);
		
			}
		}
		
		if($note !=''){
			$note_for = $this->db->checkCharLen('notes.note_for', 'po');
			$noteData=array('table_id'=> $po_id,
							'note_for'=> $note_for,
							'created_on'=> date('Y-m-d H:i:s'),
							'last_updated'=> date('Y-m-d H:i:s'),
							'accounts_id'=> $_SESSION["accounts_id"],
							'user_id'=> $_SESSION["user_id"],
							'note'=> $note,
							'publics'=>1);
			$notes_id = $this->db->insert('notes', $noteData);
			
		}
		
		$id = $po_number;
		$savemsg = 'update-success';
	
		$array = array( 'login'=>'', 'id'=>$id, 'savemsg'=>$savemsg, 'message'=>$message);
		return json_encode($array);
	}
	
   public function saveChangePOSupplier(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['savemsg'] = 'error';

		$po_id = intval($POST['po_id']??0);
		$supplier_id = intval($POST['supplier_id']??0);
		$poData = array();
		$poData['supplier_id'] = $supplier_id;
		$poData['last_updated'] = date('Y-m-d H:i:s');
						
		if($po_id>0){
			$oneTRow = false;
			$oneTRowObj = $this->db->query("SELECT * FROM po WHERE po_id = $po_id", array());
			if($oneTRowObj){
				$oneTRow = $oneTRowObj->fetch(PDO::FETCH_OBJ);
			}
			
			$update = $this->db->update('po', $poData, $po_id);
			if($update){
				$changed = array();
				if($oneTRow && $oneTRow->supplier_id != $supplier_id){
					$oldSupplierName = '';
					$tableObj = $this->db->query("SELECT first_name, last_name FROM suppliers WHERE suppliers_id = $oneTRow->supplier_id", array());
					if($tableObj){
						$tableRow = $tableObj->fetch(PDO::FETCH_OBJ);
						$oldSupplierName = trim(stripslashes("$tableRow->first_name $tableRow->last_name"));
					}
					$supplierName = '';
					$tableObj = $this->db->query("SELECT first_name, last_name FROM suppliers WHERE suppliers_id = $supplier_id", array());
					if($tableObj){
						$tableRow = $tableObj->fetch(PDO::FETCH_OBJ);
						$supplierName = trim(stripslashes("$tableRow->first_name $tableRow->last_name"));
					}
					$changed['Supplier Name'] = array($oldSupplierName, $supplierName);
				}
				
				if(!empty($changed)){
					$moreInfo = $teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = 'po';
					$teData['record_id'] = $po_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);							
				}
				$jsonResponse['savemsg'] = 'Updated';
				$tableObj = $this->db->query("SELECT company, first_name, last_name, email FROM suppliers WHERE suppliers_id = $supplier_id", array());
				if($tableObj){
					$tableRow = $tableObj->fetch(PDO::FETCH_OBJ);
					$jsonResponse['company'] = trim((string) stripslashes($tableRow->company));
					$jsonResponse['name'] = trim(stripslashes($tableRow->first_name.' '.$tableRow->last_name));
					$jsonResponse['email'] = trim((string) stripslashes($tableRow->email));
				}
			}
		}
		
		return json_encode($jsonResponse);
	}
	
   public function saveChangePO(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['savemsg'] = 'error';

		$po_id = intval($POST['po_id']??0);
		$poData = array();
		$date_expected = '1000-01-01';
		if($POST['date_expected'] !=''){
			$date_expected = date('Y-m-d', strtotime($POST['date_expected']));
		}
		$jsonResponse['date_expected'] = $date_expected;
		$poData['date_expected'] = $date_expected;
		$lot_ref_no = $this->db->checkCharLen('po.lot_ref_no', trim((string) $POST['lot_ref_no']??''));
		$poData['lot_ref_no'] = $lot_ref_no;
		$jsonResponse['lot_ref_no'] = $lot_ref_no;
		$paid_by = $this->db->checkCharLen('po.paid_by', trim((string) $POST['paid_by']??''));
		$poData['paid_by'] = $paid_by;
		$jsonResponse['paid_by'] = $paid_by;

		$poData['tax_is_percent'] = $tax_is_percent = intval($POST['tax_is_percent']??1);
		$poData['taxes'] = $taxes = round($POST['taxes'], (2+$tax_is_percent));
		$jsonResponse['taxes'] = $taxes;

		$jsonResponse['tax_is_percent'] = $tax_is_percent;
		
		$poData['shipping'] = $shipping = round($POST['popup_shipping']??0.00,2);
		$jsonResponse['shipping'] = $shipping;
		
		$suppliers_invoice_no = $this->db->checkCharLen('po.suppliers_invoice_no', trim((string) $POST['suppliers_invoice_no']??''));
		$poData['suppliers_invoice_no'] = $suppliers_invoice_no;
		if($POST['invoice_date'] !=''){
			$poData['invoice_date'] = date('Y-m-d', strtotime($POST['invoice_date']));
		}
		else{
			$poData['invoice_date'] = '1000-01-01';
		}
		if($POST['date_paid'] !=''){
			$poData['date_paid'] = date('Y-m-d', strtotime($POST['date_paid']));
		}
		else{
			$poData['date_paid'] = '1000-01-01';
		}
		$poData['last_updated'] = date('Y-m-d H:i:s');
						
		if($po_id>0){
			$oneTRowObj = $this->db->querypagination("SELECT * FROM po WHERE po_id = $po_id", array());
			$changed = array();
			
			$update = $this->db->update('po', $poData, $po_id);
			if($update){
				$jsonResponse['savemsg'] = 'Updated';

				if($oneTRowObj){
					unset($poData['last_updated']);
					foreach($poData as $fieldName=>$fieldValue){
						$prevFieldVal = $oneTRowObj[0][$fieldName];
						if($prevFieldVal != $fieldValue){
							if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
							if($fieldValue=='1000-01-01'){$fieldValue = '';}
							$changed[$fieldName] = array($prevFieldVal, $fieldValue);
						}
					}						
				}
				if(!empty($changed)){
					$moreInfo = $teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'po');
					$teData['record_id'] = $po_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);							
				}
			}
		}
		
		return json_encode($jsonResponse);
	}
	
   public function addProductToPOCart(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $status = '';
		$user_id = $_SESSION["user_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$po_id = intval($POST['po_id']??0);
		$suppliers_id = intval($POST['supplier_id']??0);
		$fieldname = $POST['fieldname']??'';
		$fieldvalue = $POST['fieldvalue']??'';
		$po_items_id = 0;
		$created_on = date('Y-m-d H:i:s');
		$last_updated = date('Y-m-d H:i:s');

		$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
		if($poObj){				
			$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
			
			if($fieldvalue !=''){
				$sql = "SELECT * FROM product WHERE accounts_id = $prod_cat_man AND product_publish = 1 AND $fieldname = :fieldvalue";
				$sql .= " AND product_type in ('Standard', 'Live Stocks')";
				$sql .= " ORDER BY product_id ASC LIMIT 0,1";
				$query = $this->db->querypagination($sql, array('fieldvalue'=>$fieldvalue));
				if($query){
					foreach($query as $productonetow){
						$product_id = $productonetow['product_id'];
						$product_type = $productonetow['product_type'];

						$item_type = 'product';
						if($product_type=='Live Stocks'){
							$item_type = 'livestocks';
						}

						$sql1 = "SELECT * FROM po_items WHERE po_id = $po_id AND product_id = $product_id ORDER BY po_items_id ASC LIMIT 0,1";
						$query1 = $this->db->querypagination($sql1, array());
						if($query1){
							foreach($query1 as $po_itemsrow){
								$po_items_id = $po_itemsrow['po_items_id'];
								$cost = $po_itemsrow['cost'];
								$ordered_qty = $po_itemsrow['ordered_qty']+1;

								$updatepo_items = $this->db->update('po_items', array('ordered_qty'=>$ordered_qty), $po_items_id);
								if($updatepo_items){
									$action = 'Update';
								}
							}
						}

						if($po_items_id==0){

							$cost = 0;
							$sql55 = "SELECT po_items.cost FROM po, po_items WHERE po.accounts_id = $accounts_id AND po_items.product_id = $product_id AND po.transfer = 0 AND po.po_id = po_items.po_id AND po.supplier_id =:supplier_id ORDER BY po_items.po_items_id DESC LIMIT 0,1";
							$query55 = $this->db->querypagination($sql55, array('supplier_id'=>$suppliers_id),1);
							if($query55){
								foreach($query55 as $po_itemsrow22){
									$cost = $po_itemsrow22['cost'];
								}
							}
							$item_type = $this->db->checkCharLen('po_items.item_type', $item_type);
							$qty = 1;
							$poiData = array('created_on'=>date('Y-m-d H:i:s'),
											'user_id'=>$_SESSION["user_id"],
											'po_id'=>$po_id,
											'product_id'=>$product_id,
											'item_type'=>$item_type,
											'cost'=>round($cost,2),
											'ordered_qty'=>$qty,
											'received_qty'=>0);
							$po_items_id = $this->db->insert('po_items', $poiData);
							if($po_items_id){									
								$action = 'Add';
							}
							else{
								$action = 'Add_Product_Order';
							}
						}
					}
				}
				else{
					$action = 'Product_Found_Sku';
				}
			}
		}
		else{
			$action = 'No_Order_Found';
		}
		$cartsData = array();
		if($action =='Add' || $action == 'Update'){
			$cartsData = $this->loadPOCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	}
	
   public function loadPOCartData($po_id, $status){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$Carts = new Carts($this->db);
		
		$po_id = intval($POST['po_id']??$po_id);
		$status = $POST['status']??$status;
		
		$poSql = "SELECT * FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id LIMIT 0,1";
		$poObj = $this->db->querypagination($poSql, array('po_id'=>$po_id),1);
		$poCartsData = array();
		if($poObj){
			foreach($poObj as $onePoData){
				$return_po = $onePoData['return_po'];
				$po_id = $onePoData['po_id'];
				
				$sqlquery = "SELECT * FROM po_items WHERE po_id = $po_id";
				$poItemsData = $this->db->querypagination($sqlquery, array());
				$total_item = count($poItemsData);
				if($poItemsData){
					$i=0;
					$order_receive_eq_count= 0;
					foreach($poItemsData as $row){
						$i++;
						$po_items_id = $row['po_items_id'];
						$product_id = $item_id = $row['product_id'];
						$item_type = $row['item_type'];

						$product_name = $sku = $product_type = '';
						$manage_inventory_count = 0;
						if($item_type=='one_time'){
							$pcObj =  $this->db->query("SELECT description FROM pos_cart WHERE pos_cart_id = $product_id", array());
							if($pcObj){
								$product_name = $pcObj->fetch(PDO::FETCH_OBJ)->description;
							}
						}
						else{
							$productObj =  $this->db->query("SELECT p.product_type, p.manage_inventory_count, p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id = $prod_cat_man", array());
							if($productObj){
								$product_row = $productObj->fetch(PDO::FETCH_OBJ);

								$product_name = stripslashes($product_row->product_name);
								$manufacturer_name = $product_row->manufacture;
								if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}

								$colour_name = $product_row->colour_name;
								if($colour_name !=''){$product_name .= ' '.$colour_name;}

								$storage = $product_row->storage;
								if($storage !=''){$product_name .= ' '.$storage;}

								$physical_condition_name = $product_row->physical_condition_name;
								if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

								$sku = $product_row->sku;
								$product_type = $product_row->product_type;
								$manage_inventory_count = $product_row->manage_inventory_count;
							}
						}
						
						$cost = round($row['cost'],2);
						$ordered_qty = floatval($row['ordered_qty']);
						$received_qty = floatval($row['received_qty']);
						$imeicount = 0;
						$cellPhoneData = array();
						if($item_type=='livestocks'){
							$sqlitem = "SELECT item.*, pci.return_po_items_id, pci.po_or_return FROM item, po_cart_item pci WHERE item.accounts_id = $accounts_id AND (pci.po_items_id = $po_items_id OR pci.return_po_items_id = $po_items_id) AND item.item_id = pci.item_id ORDER BY item.item_id ASC";
							$itemquery = $this->db->query($sqlitem, array());
							if($itemquery){
								while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
									$imeicount++;
									$item_id = $itemrow->item_id;
									
									$cartUsedCount = 0;
									$cucObj = $this->db->query("SELECT COUNT(pos_cart_item_id) AS cartUsedCount FROM pos_cart_item WHERE item_id = $item_id", array());
									if($cucObj){
										$cartUsedCount = intval($cucObj->fetch(PDO::FETCH_OBJ)->cartUsedCount);
									}
									
									$in_inventory = intval($itemrow->in_inventory);
									$item_number = $itemrow->item_number;
									$carrier_name = $itemrow->carrier_name;

									$cellPhoneData[] = array('item_id'=>$item_id, 'carrier_name'=>$carrier_name, 'return_po_items_id'=>$itemrow->return_po_items_id, 'po_or_return'=>$itemrow->po_or_return, 'cartUsedCount'=>$cartUsedCount, 'in_inventory'=>$in_inventory, 'item_number'=>$item_number);
								}
							}
						}
						
						$total = round($received_qty*$cost,2);
						if($ordered_qty == $received_qty) {
							$order_receive_eq_count = $order_receive_eq_count+1;
						}

						$NeedHaveOnPOInfo = array();
						$NeedHaveOnPOInfo['product_type'] = $product_type;
						$NeedHaveOnPOInfo['manage_inventory_count'] = intval($manage_inventory_count);
						$NeedHaveOnPOInfo['need'] = 0;
						$NeedHaveOnPOInfo['have'] = 0;
						$NeedHaveOnPOInfo['onPO'] = 0;
						if(in_array($product_type, array('Standard', 'Live Stocks')) && $manage_inventory_count>0){
							$NHPInfo = $Carts->NeedHaveOnPO($product_id, $product_type, 1);
							$NeedHaveOnPOInfo['need'] = $NHPInfo[0];
							$NeedHaveOnPOInfo['have'] = $NHPInfo[1];
							$NeedHaveOnPOInfo['onPO'] = $NHPInfo[2];
						}

						$poCartsData[] = array('po_items_id'=>$po_items_id, 'product_id'=>$product_id, 'item_type'=>$item_type, 'status'=>$status, 'product_name'=>$product_name, 'sku'=>$sku, 'cellPhoneData'=>$cellPhoneData, 'imeicount'=>$imeicount, 'ordered_qty'=>$ordered_qty, 'received_qty'=>$received_qty, 'cost'=>$cost, 'HaveOnPO'=>$NeedHaveOnPOInfo, 'total'=>$total);
					}
				}
			}
		}
		
		return $poCartsData;
   }
	
	public function updatePOItem(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $status = '';
		$po_id = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		
		$po_items_id = intval($POST['po_items_id']??0);
		$item_number = $this->db->checkCharLen('item.item_number', strtoupper(trim((string) addslashes($POST['item_number']??''))));
		
		$poItemObj = $this->db->query("SELECT po_id, product_id, received_qty FROM po_items WHERE po_items_id = :po_items_id", array('po_items_id'=>$po_items_id),1);
		if($poItemObj){
			$onepo_itemsrow = $poItemObj->fetch(PDO::FETCH_OBJ);
		
			$po_id = $onepo_itemsrow->po_id;
			$po_number = '';
			$poObj = $this->db->query("SELECT po_number, status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
			if($poObj){				
				$poOneRow = $poObj->fetch(PDO::FETCH_OBJ);
				$po_number = $poOneRow->po_number;
				$status = $poOneRow->status;
			}
			$product_id = $onepo_itemsrow->product_id;
			$pototalreceived_qty = $onepo_itemsrow->received_qty+1;
			
			$checkItemId = $checkInInventory = 0;
			$sqlitem = "SELECT item_id, in_inventory FROM item WHERE accounts_id = $accounts_id AND item_number = :item_number ORDER BY in_inventory DESC, item_number ASC LIMIT 0,1";
			$itemObj = $this->db->querypagination($sqlitem, array('item_number'=>$item_number));
			if($itemObj){
				foreach($itemObj as $oneItemRow){
					$checkItemId = $oneItemRow['item_id'];
					$checkInInventory = $oneItemRow['in_inventory'];
				}
			}
			
			if($checkInInventory ==0){
				$productObj = $this->db->query("SELECT product_id FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man AND product_publish = 1", array());
				if($productObj){	
				
					$itemData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $accounts_id,		
									'user_id' => $user_id,
									'product_id' => $product_id,
									'item_number' => $item_number,
									'carrier_name' => "",
									'in_inventory' => 1,
									'is_pos'=>0,
									'custom_data'=>''
									);
					$item_id = $this->db->insert('item', $itemData);
					if($item_id){
						
						$poCartItemData = array('po_items_id' => $po_items_id,
												'item_id' => $item_id,
												'return_po_items_id' => 0);
						$this->db->insert('po_cart_item', $poCartItemData);
						
						$received_qty = 0;
						$sqlitem = "SELECT count(po_cart_item_id) AS received_qty FROM po_cart_item WHERE po_items_id = $po_items_id LIMIT 0,1";
						$itemObj = $this->db->querypagination($sqlitem, array());
						if($itemObj){
							foreach($itemObj as $oneItemRow){
								$received_qty = $oneItemRow['received_qty'];
							}
						}
						
						$updatepo_items = $this->db->update('po_items', array('received_qty'=>$received_qty), $po_items_id);
						if($updatepo_items){
							$action = 'Add';
						}						
					}
				}
			}/*
			elseif($checkItemId>0){
				$sqlitem = "SELECT i.item_id FROM item i, po_cart_item pci WHERE i.item_id = '$checkItemId' AND i.accounts_id = $accounts_id AND i.in_inventory = 1 AND i.item_id = pci.item_id AND pci.po_items_id !=$po_items_id LIMIT 0,1";
				$itemObj = $this->db->querypagination($sqlitem, array());
				if($itemObj){
					foreach($itemObj as $oneItemRow){
						$checkItemId = $oneItemRow['item_id'];
						$this->db->update('item', array('product_id'=>$product_id, 'in_inventory'=>1), $checkItemId);
				
						$poCartItemData = array('po_items_id' => $po_items_id,
												'item_id' => $checkItemId,
												'return_po_items_id' => 0
												);
						$this->db->insert('po_cart_item', $poCartItemData);
						
						$received_qty = 0;
						$sqlitem = "SELECT count(po_cart_item_id) AS received_qty FROM po_cart_item WHERE po_items_id = $po_items_id LIMIT 0,1";
						$itemObj = $this->db->querypagination($sqlitem, array());
						if($itemObj){
							foreach($itemObj as $oneItemRow){
								$received_qty = $oneItemRow['received_qty'];
							}
						}
						
						$updatepo_items = $this->db->update('po_items', array('received_qty'=>$received_qty), $po_items_id);
						if($updatepo_items){								
							$action = 'Update';
						}
					}
				}				
			}	
			*/		
		}
		
		$cartsData = array();
		if($action =='Add' || $action == 'Update'){
			$cartsData = $this->loadPOCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	
	}

   public function saveBulkData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$message = $action = $status = '';
		$smallerIMEI = $largerIMEI = $duplicateIMEI = $savedIMEI = '';
		$po_id = $pototalreceived_qty = 0;
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;

		$po_items_id = intval($POST['po_items_id']??0);
		$ordered_qty = floatval($POST['ordered_qty']??0);
		$bulkimei = $POST['bulkimei']??'';
		
		$poItemObj = $this->db->query("SELECT po_id, product_id, received_qty FROM po_items WHERE po_items_id = :po_items_id", array('po_items_id'=>$po_items_id),1);
		if($poItemObj){
			$onepo_itemsrow = $poItemObj->fetch(PDO::FETCH_OBJ);
			$po_id = $onepo_itemsrow->po_id;
			$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
			if($poObj){
				$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
			}
			
			if($bulkimei !=''){
				$product_id = $onepo_itemsrow->product_id;
				$pototalreceived_qty = $onepo_itemsrow->received_qty;

				$item_numberData = preg_split("/\\r\\n|\\r|\\n/", $bulkimei);

				if(count($item_numberData)>0){
					$imeisaved = $imeismallerthan = $imeilongerthan = $duplicateimei = 0;
					$totalIMEI = count($item_numberData);
					foreach($item_numberData as $item_number){
						$item_number = $this->db->checkCharLen('item.item_number', addslashes(trim((string) $item_number)));
						if($item_number==''){
							$totalIMEI--;
						}
						elseif(strlen($item_number)<2){
							$imeismallerthan++;
						}
						elseif(strlen($item_number)>20){
							$imeilongerthan++;
						}
						elseif($imeisaved<$ordered_qty){
							$po_id = $onepo_itemsrow->po_id;

							$checkItemId = $checkInInventory = 0;
							$sqlitem = "SELECT item_id, in_inventory FROM item WHERE accounts_id = $accounts_id AND item_number = :item_number ORDER BY in_inventory DESC, item_number ASC LIMIT 0,1";
							$itemObj = $this->db->querypagination($sqlitem, array('item_number'=>$item_number));
							if($itemObj){
								foreach($itemObj as $oneItemRow){
									$checkItemId = $oneItemRow['item_id'];
									$checkInInventory = $oneItemRow['in_inventory'];
								}
							}								
							
							if($checkInInventory==0){
								$productObj = $this->db->query("SELECT product_id FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man AND product_publish = 1", array());
								if($productObj){	
								
									$itemData = array('created_on' => date('Y-m-d H:i:s'),
													'last_updated' => date('Y-m-d H:i:s'),
													'accounts_id' => $accounts_id,		
													'user_id' => $user_id,
													'product_id' => $product_id,
													'item_number' => $item_number,
													'carrier_name' => "",
													'in_inventory' => 1,
													'is_pos'=>0,
													'custom_data'=>''
													);
									$item_id = $this->db->insert('item', $itemData);
									if($item_id){
										
										$poCartItemData = array('po_items_id' => $po_items_id,
																'item_id' => $item_id,
																'return_po_items_id' => 0);
										$this->db->insert('po_cart_item', $poCartItemData);
										$imeisaved++;
										$pototalreceived_qty++;					
									}
								}
							}
							elseif($checkItemId>0){
								$duplicateimei++;
								
							}
							
							/*
							$countitemquery = 0;
							$itemObj = $this->db->query("SELECT COUNT(item_id) AS counttotalrows, SUM(in_inventory) AS inInvCount FROM item WHERE accounts_id = $accounts_id AND item_number = '$item_number'", array());
							if($itemObj){
								$itemRow = $itemObj->fetch(PDO::FETCH_OBJ);
								$countitemquery = $itemRow->counttotalrows;
								$inInvCount = $itemRow->inInvCount;
								if($countitemquery != $inInvCount){

									$sqlitem = "SELECT i.item_id FROM item i, po_cart_item pci WHERE i.accounts_id = $accounts_id AND i.item_number = '$item_number' AND pci.po_items_id !=$po_items_id AND i.in_inventory = 0 AND i.item_id = pci.item_id LIMIT 0,1";
									$itemObj = $this->db->querypagination($sqlitem, array());
									if($itemObj){
										foreach($itemObj as $oneItemRow){
											$checkItemId = $oneItemRow['item_id'];

											$poCartItemData = array('po_items_id' => $po_items_id,
																	'item_id' => $checkItemId,
																	'return_po_items_id' => 0);
											$this->db->insert('po_cart_item', $poCartItemData);

											$this->db->update('item', array('in_inventory'=>1), $checkItemId);
											$imeisaved++;
											$pototalreceived_qty++;
										}
									}
								}
							}

							if($countitemquery==0){
								$itemData = array('created_on' => date('Y-m-d H:i:s'),
												'last_updated' => date('Y-m-d H:i:s'),
												'accounts_id' => $accounts_id,		
												'user_id' => $user_id,
												'product_id' => $product_id,
												'item_number' => $item_number,
												'carrier_name' => "",
												'in_inventory' => 1,
												'is_pos'=>0,
												'custom_data'=>''
												);
								$item_id = $this->db->insert('item', $itemData);
								if($item_id){
									$poCartItemData = array('po_items_id' => $po_items_id,
															'item_id' => $item_id,
															'return_po_items_id' => 0);
									$this->db->insert('po_cart_item', $poCartItemData);

									$imeisaved++;
									$pototalreceived_qty++;
								}
							}
							else{
								$duplicateimei++;
							}
							*/
						
						}
					}

					if($imeismallerthan>0){
						$message .= "smallerIMEI";
						$smallerIMEI = $imeismallerthan;
					}

					if($imeilongerthan>0){
						$message .= "|largerIMEI";
						$largerIMEI = $imeilongerthan;
					}

					if($duplicateimei>0){
						$message .= "|duplicateIMEI";
						$duplicateIMEI = $duplicateimei;
					}

					if($imeisaved>0){
						$action = 'Add';
						
						$sqlitem = "SELECT count(po_cart_item_id) AS received_qty FROM po_cart_item WHERE po_items_id = $po_items_id LIMIT 0,1";
						$itemObj = $this->db->querypagination($sqlitem, array());
						if($itemObj){
							foreach($itemObj as $oneItemRow){
								$pototalreceived_qty = $oneItemRow['received_qty'];
							}
						}
						
						$updatepo_items = $this->db->update('po_items', array('received_qty'=>$pototalreceived_qty), $po_items_id);

						if(count($item_numberData)>$imeisaved){
							$message .= "|IMEIsavedError";
							$savedIMEI = $imeisaved;
						}
						else{
							$message .= "|IMEIsaved";
							$savedIMEI = $imeisaved;
						}
					}
					else{
						$message .= "|noIMEIsaved";
					}
				}
			}
		}
		else{
			$message .= "|missingIMEI";
		}
						
		$cartsData = array();
		if($action =='Add'){
			$cartsData = $this->loadPOCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData, 'received_qty'=>$pototalreceived_qty, 'message'=>$message, 
		'smallerIMEI'=>$smallerIMEI, 'largerIMEI'=>$largerIMEI, 'duplicateIMEI'=>$duplicateIMEI, 'savedIMEI'=>$savedIMEI));
	
   }
	
   public function saveChangeImeiOnPO(){
		$POST = $_POST;//json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$item_id = intval($POST['item_id']??0);
		$carrier_name = $this->db->checkCharLen('item.carrier_name', $POST['carrier_name']??'');
		$action = $status = '';
		$po_id = intval($POST['po_id']??0);
		$customFields = $POST['customFields']??0;
		$updatedata = array('carrier_name'=>$carrier_name);
		if($item_id>0){
			if($customFields>0){
				$Common = new Common($this->db);
				$updatedata['custom_data'] = $Common->postCustomFormFields('devices');
			}
			$oneTRowObj = $this->db->querypagination("SELECT * FROM item WHERE item_id = $item_id", array());
			$updatepos = $this->db->update('item', $updatedata, $item_id);
			if($updatepos){
				
				if($oneTRowObj){
					$changed = array();
					foreach($updatedata as $fieldName=>$fieldValue){
						$prevFieldVal = $oneTRowObj[0][$fieldName];
						if($prevFieldVal != $fieldValue){
							if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
							if($fieldValue=='1000-01-01'){$fieldValue = '';}
							if($fieldName=='product_id'){
								$Common = new Common($this->db);
								$fieldName = 'Model';
								if($prevFieldVal==0){$prevFieldVal = '';}
								elseif($prevFieldVal>0){
									$prevFieldVal = '';
									$productObj = $this->db->query("SELECT manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_id = :product_id", array('product_id'=>$prevFieldVal),1);
									if($productObj){
										$pOneRow = $productObj->fetch(PDO::FETCH_OBJ);
										$prevFieldVal = stripslashes(trim("$pOneRow->manufacture $pOneRow->product_name $pOneRow->colour_name $pOneRow->storage $pOneRow->physical_condition_name"));
									}
								}
								if($fieldValue==0){$fieldValue = '';}
								elseif($fieldValue>0){
									$prevFieldVal = '';
									$productObj = $this->db->query("SELECT manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_id = :product_id", array('product_id'=>$fieldValue),1);
									if($productObj){
										$pOneRow = $productObj->fetch(PDO::FETCH_OBJ);
										$fieldValue = stripslashes(trim("$pOneRow->manufacture $pOneRow->product_name $pOneRow->colour_name $pOneRow->storage $pOneRow->physical_condition_name"));
									}
								}
							}
							$changed[$fieldName] = array($prevFieldVal, $fieldValue);
						}
					}						
					
					if(!empty($changed)){
						$changed["Item changed on PO"] = array();
						$moreInfo = $teData = array();
						$teData['created_on'] = date('Y-m-d H:i:s');
						$teData['accounts_id'] = $_SESSION["accounts_id"];
						$teData['user_id'] = $_SESSION["user_id"];
						$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'item');
						$teData['record_id'] = $item_id;
						$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
						$this->db->insert('track_edits', $teData);							
					}
				}
			}
			
			$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
			if($poObj){
				$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
			}
			
			$action = 'Update';
		}
						
		$cartsData = array();
		if($action =='Update'){
			$cartsData = $this->loadPOCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	}
	
   public function update_po_complete(){
      $accounts_id = $_SESSION["accounts_id"]??0;
		$POST = json_decode(file_get_contents('php://input'), true);
		$po_id = intval($POST['po_id']??0);
		$status = $this->db->checkCharLen('po.status', 'Closed');
		$returnStr = 0;
		$updatepos = $this->db->update('po', array('status'=>$status), $po_id);
		if($updatepos){
	    	$po_number = 0;
    		$sql = "SELECT * FROM po WHERE po_id = $po_id";
			$query = $this->db->querypagination($sql, array());
			if($query){
				$Common = new Common($this->db);
				foreach($query as $onerow){
					$supplier_id = intval($onerow['supplier_id']);
					$po_number = intval($onerow['po_number']);
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

					$BulkSMS = new BulkSMS($this->db);
					$BulkSMS->sendPOSMS($supplier_id, $po_number, $grand_total, $amountPaid, $onerow['date_expected']);
			
				}
			}
        	
		   $note_for = $this->db->checkCharLen('notes.note_for', 'po');		
    		$noteData=array('table_id'=> $po_id,
    						'note_for'=> $note_for,
    						'created_on'=> date('Y-m-d H:i:s'),
    						'last_updated'=> date('Y-m-d H:i:s'),
    						'accounts_id'=> $_SESSION["accounts_id"],
    						'user_id'=> $_SESSION["user_id"],
    						'note'=> $this->db->translate('Marked Completed')." ".$this->db->translate('Purchase Order')." p$po_number",
    						'publics'=>0);
    		$this->db->insert('notes', $noteData);
			
			$returnStr = 1;
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}	
	
	public function updatepoReOpen(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$po_id = intval($POST['po_id']??0);
		
		$status = $this->db->checkCharLen('po.status', 'Open');
		$this->db->update('po', array('status'=>$status), $po_id);
		
		$po_number = 0;
		$poSql = "SELECT po_number FROM po WHERE accounts_id = $accounts_id AND po_id = $po_id ORDER BY po_number ASC";
		$poObj = $this->db->query($poSql, array());
		if($poObj){
			$po_number = $poObj->fetch(PDO::FETCH_OBJ)->po_number;
		}
		$note_for = $this->db->checkCharLen('notes.note_for', 'po');		
		$noteData=array('table_id'=> $po_id,
						'note_for'=> $note_for,
						'created_on'=> date('Y-m-d H:i:s'),
						'last_updated'=> date('Y-m-d H:i:s'),
						'accounts_id'=> $_SESSION["accounts_id"],
						'user_id'=> $_SESSION["user_id"],
						'note'=> $this->db->translate('PO was re-open.')." ".$this->db->translate('Purchase Order')." p$po_number",
						'publics'=>0);
		$notes_id = $this->db->insert('notes', $noteData);
		
		return json_encode(array('login'=>'', 'returnStr'=>$po_number));
	}
	
	public function updatepo_item(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$Common = new Common($this->db);
		$po_items_id = intval($POST['po_items_id']??0);
		$newcost = floatval($POST['cost']??0.00);
		$newordered_qty = floatval($POST['ordered_qty']??0);
		$newreceived_qty = floatval($POST['received_qty']??0);
		$newordered_qty_total = round($newcost*$newordered_qty,2);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$action = $status = '';
		$po_id = 0;
		
		$poItemObj = $this->db->query("SELECT * FROM po_items WHERE po_items_id = :po_items_id", array('po_items_id'=>$po_items_id),1);
		if($poItemObj){
			$po_itemsrow = $poItemObj->fetch(PDO::FETCH_OBJ);
		
			$po_id = $po_itemsrow->po_id;
			$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
			if($poObj){
				$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
			}
			$item_type = $po_itemsrow->item_type;
			$product_id = $po_itemsrow->product_id;
			$oldCost = $po_itemsrow->cost;
			$old_po_ordered_qty = $po_itemsrow->ordered_qty;
			$old_po_received_qty = $po_itemsrow->received_qty;
			
			$po_itemsupdatedata = array('cost'=>$newcost,
										'ordered_qty'=>$newordered_qty,
										'received_qty'=>$newreceived_qty);	
			$updatepo_items = $this->db->update('po_items', $po_itemsupdatedata, $po_items_id);
			if($updatepo_items){
				$changed = array();
				if($oldCost != $newcost){
					$changed['cost'] = array($oldCost, number_format($newcost,2));
					
					if($item_type=='livestocks'){
						$Common->updateMobileAvgCost($accounts_id, $po_items_id, $po_itemsrow->created_on);
					}
				}
				if($old_po_ordered_qty != $newordered_qty){$changed['ordered_qty'] = array($old_po_ordered_qty, $newordered_qty);}
				if($old_po_received_qty != $newreceived_qty){$changed['received_qty'] = array($old_po_received_qty, $newreceived_qty);}
				
				if(!empty($changed)){
					$product_name = '';
					if($item_type=='one_time'){
						$pcObj =  $this->db->query("SELECT description FROM pos_cart WHERE pos_cart_id = $product_id", array());
						if($pcObj){
							$product_name = $pcObj->fetch(PDO::FETCH_OBJ)->description;
						}
					}
					else{
						$productObj =  $this->db->query("SELECT p.product_type, p.manage_inventory_count, p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id = $prod_cat_man", array());
						if($productObj){
							$product_row = $productObj->fetch(PDO::FETCH_OBJ);

							$product_name = stripslashes($product_row->product_name);
							$manufacturer_name = $product_row->manufacture;
							if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}

							$colour_name = $product_row->colour_name;
							if($colour_name !=''){$product_name .= ' '.$colour_name;}

							$storage = $product_row->storage;
							if($storage !=''){$product_name .= ' '.$storage;}

							$physical_condition_name = $product_row->physical_condition_name;
							if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

							$sku = $product_row->sku;
							$product_type = $product_row->product_type;
							$manage_inventory_count = $product_row->manage_inventory_count;
							
							$product_name .= " (<a href=\"/Products/view/$product_id\" class=\"txtunderline txtblue\" title=\"".$this->db->translate('View Product Details')."\">".$sku." <i class=\"fa fa-link\"></i></a>)";
						}
					}
					
					$moreInfo = array('table'=>'po_items', 'id'=>$po_items_id, 'product_id'=>$product_id, 'description'=>$product_name);
					$teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'po');
					$teData['record_id'] = $po_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);
				}
				
				if($item_type=='one_time'){
					$this->db->update('pos_cart', array('ave_cost'=>$newcost), $product_id);
				}
				else{
					
					$productObj = $this->db->query("SELECT product_type, manage_inventory_count FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man AND product_publish = 1", array());
					if($productObj){						
						$productOneRow = $productObj->fetch(PDO::FETCH_OBJ);
						$product_type = $productOneRow->product_type;
						$manage_inventory_count = $productOneRow->manage_inventory_count;
						
						if($product_type =='Standard'){
							//=================Update Average cost =============//
							$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
							if($inventoryObj){
								$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
								
								$current_inventory = $inventoryrow->current_inventory-$old_po_received_qty+$newreceived_qty;
								$updateData = array('current_inventory'=>$current_inventory);
								if($manage_inventory_count>0){
									$ave_cost = $Common->productAvgCost($accounts_id, $product_id, 1);
									$updateData['ave_cost'] = $ave_cost;
								}
								
								$this->db->update('inventory', $updateData, $inventoryrow->inventory_id);
							}
						}
					}
				}
				$action = 'Update';
			}
		}		
						
		$cartsData = array();
		if($action =='Update'){
			$cartsData = $this->loadPOCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	}
	
   public function removeThisPOItem(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$Common = new Common($this->db);
		$newreceived_qty = 0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$po_items_id = intval($POST['po_items_id']??0);
		$action = $status = '';
		$po_id = 0;
		
		if($po_items_id>0){
			$poItemObj = $this->db->query("SELECT * FROM po_items WHERE po_items_id = :po_items_id", array('po_items_id'=>$po_items_id),1);
			if($poItemObj){
				$po_itemsrow = $poItemObj->fetch(PDO::FETCH_OBJ);
		
				$po_id = $po_itemsrow->po_id;
				$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
				if($poObj){
					$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
				}
				$product_id = $po_itemsrow->product_id;
				
				$old_po_received_qty = $po_itemsrow->received_qty;
				$old_po_cost = $po_itemsrow->cost;
				
				$productObj = $this->db->query("SELECT p.product_type, p.manage_inventory_count, p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id = $prod_cat_man", array());
				if($productObj){
					$product_row = $productObj->fetch(PDO::FETCH_OBJ);
					$product_type = $product_row->product_type;
					
					$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
						
						$this->db->delete('po_items', 'po_items_id', $po_items_id);
						$action = 'Removed';
						
						$product_name = stripslashes($product_row->product_name);
						$manufacturer_name = $product_row->manufacture;
						if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}

						$colour_name = $product_row->colour_name;
						if($colour_name !=''){$product_name .= ' '.$colour_name;}

						$storage = $product_row->storage;
						if($storage !=''){$product_name .= ' '.$storage;}

						$physical_condition_name = $product_row->physical_condition_name;
						if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

						$sku = $product_row->sku;
						$manage_inventory_count = $product_row->manage_inventory_count;
						
						$product_name .= " (<a href=\"/Products/view/$product_id\" class=\"txtunderline txtblue\" title=\"".$this->db->translate('View Product Details')."\">".$sku." <i class=\"fa fa-link\"></i></a>)";
						$changed = array($this->db->translate('Remove product')=>$product_name);
						$moreInfo = array('table'=>'po_items', 'id'=>$po_items_id, 'product_id'=>$product_id);
						$teData = array();
						$teData['created_on'] = date('Y-m-d H:i:s');
						$teData['accounts_id'] = $_SESSION["accounts_id"];
						$teData['user_id'] = $_SESSION["user_id"];
						$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'po');
						$teData['record_id'] = $po_id;
						$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
						$this->db->insert('track_edits', $teData);
						
						if($product_type == 'Standard'){						
							
							$current_inventory = $inventoryrow->current_inventory-$old_po_received_qty;
							$updateData = array('current_inventory'=>$current_inventory);
							if($manage_inventory_count>0){
								$ave_cost = $Common->productAvgCost($accounts_id, $product_id, 1);
								$updateData['ave_cost'] = $ave_cost;
							}
							
							$this->db->update('inventory', $updateData, $inventoryrow->inventory_id);			
						}
						elseif($product_type =='Live Stocks'){
							$sqlitem = "SELECT i.item_id FROM item i, po_cart_item pci WHERE pci.po_items_id = $po_items_id AND i.accounts_id = $accounts_id AND i.item_id = pci.item_id ORDER BY i.item_id ASC";
							$itemObj2 = $this->db->query($sqlitem, array());
							if($itemObj2){
								while($itemrow = $itemObj2->fetch(PDO::FETCH_OBJ)){
									$item_id = $itemrow->item_id;
									$this->db->delete('item', 'item_id', $item_id);
								}
							}
							$Common->updateMobileAvgCost($accounts_id, $po_items_id, $po_itemsrow->created_on);
						}
					}
				}
			}
		}
				
		$cartsData = array();
		if($action =='Removed'){
			$cartsData = $this->loadPOCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	
	}
	
	public function removeIMEIFromPOCart(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$newreceived_qty = 0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$po_items_id = intval($POST['po_items_id']??0);
		$item_id = intval($POST['item_id']??0);
		$action = $status = '';
		$po_id = 0;
		
		if($po_items_id>0 &&  $item_id>0){
			$oneTRowObj2 = $this->db->querypagination("SELECT item_number, in_inventory FROM item WHERE item_id = $item_id", array());
			$in_inventory = 0;
			if($oneTRowObj2){
				$in_inventory = intval($oneTRowObj2[0]['in_inventory']);
			}
			$posUseCount = 0;
			$posObj = $this->db->query("SELECT COUNT(pos_cart_item_id) AS countData FROM pos_cart_item WHERE item_id = :item_id", array('item_id'=>$item_id),1);
			if($posObj){
				$posUseCount = $posObj->fetch(PDO::FETCH_OBJ)->countData;
			}
			if($in_inventory>0 && $posUseCount==0){
				$poItemObj = $this->db->query("SELECT po_id, product_id, received_qty, cost, item_type FROM po_items WHERE po_items_id = :po_items_id", array('po_items_id'=>$po_items_id),1);
				if($poItemObj){
					$po_itemsrow = $poItemObj->fetch(PDO::FETCH_OBJ);
					
					$po_id = $po_itemsrow->po_id;
					$po_number = '';
					$poObj = $this->db->query("SELECT po_number, status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
					if($poObj){
						$poOneRow = $poObj->fetch(PDO::FETCH_OBJ);
						$status = $poOneRow->status;
						$po_number = $poOneRow->po_number;
					}
					$product_id = $po_itemsrow->product_id;
					$item_type = $po_itemsrow->item_type;
					$old_po_received_qty = $po_itemsrow->received_qty;
					$old_po_cost = $po_itemsrow->cost;
					
					$newreceived_qty = floor($old_po_received_qty-1);
					
					$poCartItemRemove = 0;
					$poCIArray = $this->db->querypagination("SELECT po_cart_item_id FROM po_cart_item WHERE item_id = $item_id AND po_items_id = $po_items_id AND return_po_items_id = 0 ORDER BY po_cart_item_id DESC LIMIT 0,1", array());
					if($poCIArray){			
						$poCIRow = $poCIArray[0];								
						$po_cart_item_id = $poCIRow['po_cart_item_id'];
						$this->db->delete('po_cart_item', 'po_cart_item_id', $po_cart_item_id);
						$poCartItemRemove++;
					}
					if($poCartItemRemove>0){
						$item_number = $oneTRowObj2[0]['item_number'];
							
						$countPoCartItem = 0;
						$sqlitem = "SELECT COUNT(po_cart_item_id) AS countPoCartItem FROM po_cart_item WHERE item_id = $item_id";
						$itemObj = $this->db->query($sqlitem, array());
						if($itemObj){
							$countPoCartItem = $itemObj->fetch(PDO::FETCH_OBJ)->countPoCartItem;
						}
						if($countPoCartItem==0){
							$delete_item = $this->db->delete('item', 'item_id', $item_id);
						}
						else{
							$this->db->update('item', array('in_inventory'=>0), $item_id);
							
							$notes = "Remove Item from PO: <a href=\"/Purchase_orders/edit/$po_number\" class=\"txtunderline txtblue\" title=\"Edit\">$po_number <i class=\"fa fa-link\"></i></a>";
							$note_for = $this->db->checkCharLen('notes.note_for', 'item');
							$noteData=array('table_id'=> $item_id,
											'note_for'=> $note_for,
											'created_on'=> date('Y-m-d H:i:s'),
											'last_updated'=> date('Y-m-d H:i:s'),
											'accounts_id'=> $_SESSION["accounts_id"],
											'user_id'=> $_SESSION["user_id"],
											'note'=> $notes,
											'publics'=>0);
							$notes_id = $this->db->insert('notes', $noteData);
						}
						
						$sqlitem = "SELECT COUNT(po_cart_item_id) AS countPoCartItem FROM po_cart_item WHERE po_items_id = $po_items_id";
						$itemObj = $this->db->query($sqlitem, array());
						if($itemObj){
							$newreceived_qty = $itemObj->fetch(PDO::FETCH_OBJ)->countPoCartItem;
						}						
						$updatepo_items = $this->db->update('po_items', array('received_qty'=>$newreceived_qty), $po_items_id);
						if($updatepo_items){
							$changed = array();
							if($old_po_received_qty != $newreceived_qty){$changed['received_qty'] = array($old_po_received_qty, $newreceived_qty);}
							
							if(!empty($changed)){
								$description = $this->db->translate('from').' ';
								if($item_type=='one_time'){
									$pcObj =  $this->db->query("SELECT description FROM pos_cart WHERE pos_cart_id = $product_id", array());
									if($pcObj){
										$description .= $pcObj->fetch(PDO::FETCH_OBJ)->description;
									}
								}
								else{
									$productObj =  $this->db->query("SELECT p.product_type, p.manage_inventory_count, p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id = $prod_cat_man", array());
									if($productObj){
										$product_row = $productObj->fetch(PDO::FETCH_OBJ);

										$product_name = stripslashes($product_row->product_name);
										$manufacturer_name = $product_row->manufacture;
										if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}

										$colour_name = $product_row->colour_name;
										if($colour_name !=''){$product_name .= ' '.$colour_name;}

										$storage = $product_row->storage;
										if($storage !=''){$product_name .= ' '.$storage;}

										$physical_condition_name = $product_row->physical_condition_name;
										if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

										$sku = $product_row->sku;
										$product_type = $product_row->product_type;
										$manage_inventory_count = $product_row->manage_inventory_count;
										
										$product_name .= " (<a href=\"/Products/view/$product_id\" class=\"txtunderline txtblue\" title=\"".$this->db->translate('View Product Details')."\">".$sku." <i class=\"fa fa-link\"></i></a>)";
										$description .= $product_name;
									}
								}
								
								$changed[$this->db->translate('Remove IMEI Number')] = array($item_number, "");
								$changed[$description] = array();
								$moreInfo = array('table'=>'po_items', 'id'=>$po_items_id, 'product_id'=>$product_id);
								$teData = array();
								$teData['created_on'] = date('Y-m-d H:i:s');
								$teData['accounts_id'] = $_SESSION["accounts_id"];
								$teData['user_id'] = $_SESSION["user_id"];
								$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'po');
								$teData['record_id'] = $po_id;
								$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
								$this->db->insert('track_edits', $teData);
							}
						}
						
						$action = 'Removed';
					}
				}
			}
			else{
				$action ='UsedOnPOSCart';
			}
		}
		
		$cartsData = array();
		if($action =='Removed'){
			$cartsData = $this->loadPOCartData($po_id, $status);
		}		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
		
	}

	public function showPOData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$po_id = intval($POST['po_id']??0);
		$poData = array();
		$poData['login'] = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$poObj = $this->db->query("SELECT * FROM po WHERE po_id = :po_id AND accounts_id = $accounts_id", array('po_id'=>$po_id),1);
		if($poObj){
			$poRow = $poObj->fetch(PDO::FETCH_OBJ);
			
			$poData['po_id'] = $po_id;
			$poData['po_datetime'] = $poRow->po_datetime;
			$poData['po_number'] = intval($poRow->po_number);
			$poData['lot_ref_no'] = trim((string) $poRow->lot_ref_no);
			$poData['paid_by'] = trim((string) $poRow->paid_by);
			$poData['date_expected'] = $poRow->date_expected;
			$poData['tax_is_percent'] = $tax_is_percent = intval($poRow->tax_is_percent);
			$poData['taxes'] = round($poRow->taxes, (2+$tax_is_percent));
			$poData['shipping'] = round($poRow->shipping,2);
			$poData['suppliers_invoice_no'] = trim((string) $poRow->suppliers_invoice_no);
			$poData['invoice_date'] = $poRow->invoice_date;
			$poData['date_paid'] = $poRow->date_paid;
			$poData['supplier_id'] = intval($poRow->supplier_id);
			$poData['status'] = trim((string) $poRow->status);
		}
		return json_encode($poData);
	}
	
	public function showImeiOnPO(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$poData = array();
		$poData['login'] = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$Common = new Common($this->db);
		
		$item_id = intval($POST['item_id']??0);
		$product_id = intval($POST['product_id']??0);
		$carrier_name = trim((string) $POST['carrier_name']??'');
		$item_number = $custom_data = '';
		$itemObj = $this->db->query("SELECT * FROM item WHERE accounts_id = $accounts_id AND item_id = :item_id", array('item_id'=>$item_id),1);
		if($itemObj){
			$item_onerow = $itemObj->fetch(PDO::FETCH_OBJ);						
			$item_number = $item_onerow->item_number;
			$carrier_name = $item_onerow->carrier_name;
			$custom_data = trim((string) $item_onerow->custom_data);
		}
		
		$carOpts = array();
		$vData = $Common->variablesData('product_setup', $accounts_id);
		if(!empty($vData) && array_key_exists('carriers', $vData)){
			$carOpts = explode('||',$vData['carriers']);
		}
		if(!in_array('', $carOpts)){
			$carOpts[] = '';
		}
		
		$customFields = 0;
		$cqueryObj = $this->db->query("SELECT field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'devices'", array());
		if($cqueryObj){
			$customFieldNames = array();
			while($oneCFRow = $cqueryObj->fetch(PDO::FETCH_OBJ)){
				$customFieldNames[] = $oneCFRow->field_name;
			}
			$customFields = count($customFieldNames);
		}
		
		$poData['carrier_name'] = $carrier_name;
		$poData['item_number'] = $item_number;
		$poData['customFields'] = $customFields;
		$poData['carOpts'] = $carOpts;
		$poData['customFieldsData'] = $Common->customFormFields('devices', $custom_data);
		
		return json_encode($poData);
	}
	
	public function poCancel(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$po_id = intval($POST['po_id']??0);
		$status = trim((string) $POST['status']??'');
		$status = $this->db->checkCharLen('po.status', $status);
		
		$returnStr = 0;
		$updatepo = $this->db->update('po', array('status'=>$status), $po_id);
		if($updatepo){
			$returnStr++;
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJautoComplete_cartProductPO($segment2name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$results = array();
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$keyword_search = $POST['keyword_search']??'';
		
		$extrastr = "";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$extrastr .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$extrastr .= " AND p.product_type in ('Standard', 'Live Stocks')";
		
		$sql = "SELECT p.product_id, p.product_type, p.category_id, p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.manage_inventory_count 
				FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_publish = 1 $extrastr ORDER BY TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name)) ASC, p.sku ASC";
		$query = $this->db->query($sql, $bindData);
		if($query){
			while($onerow = $query->fetch(PDO::FETCH_OBJ)){
				$product_id = $onerow->product_id;
				$product_type = $onerow->product_type;
				$name = stripslashes((string) $onerow->manufacture);
				$product_name = stripslashes($name.' '.$onerow->product_name);
				
				$colour_name = $onerow->colour_name;
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $onerow->storage;
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $onerow->physical_condition_name;
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$sku = $onerow->sku;					
				$category_id = $onerow->category_id;
				$stockQty = 0;
				$label = "$product_name ($sku)";
				$manage_inventory_count = $onerow->manage_inventory_count;
				if($manage_inventory_count==1 && $product_type != 'Live Stocks'){
					$stockQty = 0;
					$inventoryObj = $this->db->query("SELECT current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$stockQty = $inventoryObj->fetch(PDO::FETCH_OBJ)->current_inventory;
					}
				}
				elseif(strcmp($product_type,'Live Stocks')==0){
					$stockQty = 0;
					$queryObj = $this->db->query("SELECT COUNT(item_id) AS current_inventory FROM item WHERE accounts_id = $accounts_id AND product_id = $product_id AND item_publish = 1 AND in_inventory = 1", array());
					if($queryObj){
						$stockQty = $queryObj->fetch(PDO::FETCH_OBJ)->current_inventory;
					}
				}
				else{
					$stockQty = "*";
				}
				
				$results[] = array( 'labelval' => $sku,
									'label'    => $label,
									'stockQty'    => $stockQty);
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$results));
	}
	
	public function confirmReceiveAllP(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $status = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$Common = new Common($this->db);
		
		$po_id = intval($POST['po_id']??0);
		$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
		if($poObj){				
			$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
			
			$poItemsObj = $this->db->querypagination("SELECT * FROM po_items WHERE po_id = :po_id AND item_type = 'product' AND ordered_qty>received_qty ORDER BY po_items_id ASC", array('po_id'=>$po_id),1);
			if($poItemsObj){
				foreach($poItemsObj as $onerow){
					$po_items_id = $onerow['po_items_id'];
					$product_id = $onerow['product_id'];
					$old_po_received_qty = $onerow['received_qty'];
					$old_po_cost = $newcost = $onerow['cost'];
					$newreceived_qty = $onerow['ordered_qty'];
					$old_po_totalcost = round($old_po_cost*$old_po_received_qty,2);
					
					$updatepo_items = $this->db->update('po_items', array('received_qty'=>$newreceived_qty), $po_items_id);
					if($updatepo_items){
						$action = 'Update';
						$inventoryObj = $this->db->query("SELECT p.product_type, p.manage_inventory_count, i.inventory_id, i.current_inventory, i.ave_cost FROM product p, inventory i WHERE p.product_id = $product_id AND i.accounts_id = $accounts_id AND p.product_id = i.product_id", array());
						if($inventoryObj){							
							$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
							$product_type = $inventoryrow->product_type;
							$manage_inventory_count = $inventoryrow->manage_inventory_count;
							$current_inventory = $inventoryrow->current_inventory;
							
							if($product_type =='Standard'){
								//=============Undo product condition =================//
								if($old_po_received_qty !=0){								
									$current_inventory = $current_inventory-$old_po_received_qty;
								}
								
								//===============New Product Condition==================//
								$new_current_inventory = $current_inventory+$newreceived_qty;
								$updateData = array('current_inventory'=>$new_current_inventory);
								if($manage_inventory_count>0){
									$ave_cost = $Common->productAvgCost($accounts_id, $product_id, 1);
									$updateData['ave_cost'] = $ave_cost;
								}								
								$this->db->update('inventory', $updateData, $inventoryrow->inventory_id);
							}	
						}
					}
				}
			}
		}
		
		$cartsData = array();
		if($action =='Add' || $action == 'Update'){
			$cartsData = $this->loadPOCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	}
	
	//================Joining Class==============//
	public function AJget_SuppliersPopup(){
		$Suppliers = new Suppliers($this->db);
		return $Suppliers->AJget_SuppliersPopup();
	}
	
	public function AJsave_Suppliers(){
		$Suppliers = new Suppliers($this->db);
		return $Suppliers->AJsave_Suppliers();
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