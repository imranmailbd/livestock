<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Inventory_Transfer{
	protected $db;
	private int $page, $totalRows, $po_id;
	private string $view_type, $suppliers_id, $keyword_search, $history_type;
	private array $supOpt, $actFeeTitOpt;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}

	private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sview_type = $this->view_type;
		$ssuppliers_id = $this->suppliers_id;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = $GLOBALS['segment2name'];
		$_SESSION["list_filters"] = array('sview_type'=>$sview_type, 'ssuppliers_id'=>$ssuppliers_id, 'keyword_search'=>$keyword_search);
				
		$bindData = array();
		$filterSql = "";
		$bindData = array();
		if(!empty($sview_type)){
			$filterSql .= " AND status = :sview_type";
			$bindData['sview_type'] = $sview_type;
		}
		
		if($ssuppliers_id>0){
			$filterSql .= " AND supplier_id = :ssuppliers_id";
			$bindData['ssuppliers_id'] = $ssuppliers_id;
		}
			
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', po_number, lot_ref_no, paid_by, suppliers_invoice_no)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$queryObj = $this->db->query("SELECT COUNT(po_id) AS totalrows FROM po WHERE accounts_id = $accounts_id AND transfer >0 $filterSql", $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}

		$sql = "SELECT supplier_id FROM po WHERE accounts_id = $accounts_id AND transfer >0 $filterSql GROUP BY supplier_id";
		$query = $this->db->query($sql, $bindData);
		$supOpts = array();
		if($query){
			while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
				$supOpts[$oneRow->supplier_id] = '';
			}
		}

		$supOpt = array();
		if(!empty($supOpts)){
			$suppliersObj = $this->db->query("SELECT accounts_id, company_subdomain FROM accounts WHERE accounts_id IN (".implode(', ', array_keys($supOpts)).")", array());
			if($suppliersObj){
				while($oneRow = $suppliersObj->fetch(PDO::FETCH_OBJ)){
					$optval = $oneRow->accounts_id;
					$optlabel = stripslashes(trim((string) $oneRow->company_subdomain));
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
		$limit = $_SESSION["limit"]??'auto';
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$sview_type = $this->view_type;
		$ssuppliers_id = $this->suppliers_id;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$filterSql = "";
		$bindData = array();
		if(!empty($sview_type)){
			$filterSql .= " AND status = :sview_type";
			$bindData['sview_type'] = $sview_type;
		}
		
		if($ssuppliers_id>0){
			$filterSql .= " AND supplier_id = :ssuppliers_id";
			$bindData['ssuppliers_id'] = $ssuppliers_id;
		}
			
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', po_number, lot_ref_no, paid_by, suppliers_invoice_no)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql .= " ORDER BY po_datetime DESC, po_number DESC";
		
		$sql = "SELECT * FROM po WHERE accounts_id = $accounts_id AND transfer >0 $filterSql LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sql, $bindData);
		$i = $starting_val+1;

		$tabledata = array();
		if($query){

			$suppliersarray = array();
			$supplierssqlquery = $this->db->querypagination("SELECT accounts_id, company_subdomain FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man) AND status != 'SUSPENDED' GROUP BY accounts_id", array());
			if($supplierssqlquery){
				foreach($supplierssqlquery as $suppliersrow){
					$suppliersarray[$suppliersrow['accounts_id']] = stripslashes(trim((string) "$suppliersrow[company_subdomain]"));
				}
			}

			foreach($query as $rowpo){
				$po_id = $rowpo['po_id'];

				$total = 0;
				$res = $this->db->query("SELECT SUM(received_qty*cost) AS totalValue FROM po_items WHERE po_id = $po_id", array());
				if($res){
					$totalValue = $res->fetch(PDO::FETCH_OBJ)->totalValue;
					if(is_numeric($totalValue)){
						$total = round($totalValue, 2);
					}
				}
				
				$po_number = $rowpo['po_number'];
				$status = $rowpo['status'];

				$transfer = $rowpo['transfer'];
				if($transfer==1){
					$transferfrom_id = $rowpo['accounts_id'];
					$transferto_id = $rowpo['supplier_id'];
				}
				else{
					$transferfrom_id = $rowpo['supplier_id'];
					$transferto_id = $rowpo['accounts_id'];
				}

				$transferfrom_name = '';
				if($transferfrom_id>0 && array_key_exists($transferfrom_id, $suppliersarray)){
					$transferfrom_name = stripslashes($suppliersarray[$transferfrom_id]);
					if($rowpo['lot_ref_no'] !='' && $transfer==2){$transferfrom_name .= " ($rowpo[lot_ref_no])";}
				}
				
				$transfertoname = '';
				if($transferto_id>0 && array_key_exists($transferto_id, $suppliersarray)){
					$transfertoname = stripslashes($suppliersarray[$transferto_id]);
					if($rowpo['lot_ref_no'] !='' && $transfer==1){$transfertoname .= " ($rowpo[lot_ref_no])";}
				}

				$return_po = $rowpo['return_po'];
				$returnstr = '';
				if($return_po>0){
					$returnstr = 'Return';
				}
			
				$tabledata[] = array($po_number, date('Y-m-d', strtotime($rowpo['po_datetime'])), intval($po_number), $transferfrom_name, $transfertoname, round($total,2), $status);
			
			}
		}
		
		return $tabledata;
    }
	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sview_type = $POST['sview_type']??'Open';
		$ssuppliers_id = $POST['ssuppliers_id']??'';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
				
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
	
	public function AJ_add_MoreInfo(){
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$supOpt = array();
		$sqlQuery = $this->db->querypagination("SELECT accounts_id, company_subdomain FROM accounts WHERE (accounts_id = $prod_cat_man OR location_of = $prod_cat_man) AND accounts_id !=$accounts_id AND status != 'SUSPENDED' GROUP BY accounts_id ORDER BY company_subdomain ASC", array());
		if($sqlQuery){
			foreach($sqlQuery as $onerow){
				$optionVal = $onerow['accounts_id'];
				$optionLabel = stripslashes(trim((string) "$onerow[company_subdomain]"));
				$supOpt[$optionVal] = $optionLabel;
			}
		}
		$jsonResponse['supOpt'] = $supOpt;
		
		return json_encode($jsonResponse);
	}
	
	public function add(){}
	
	public function edit(){	
		$accounts_id = $_SESSION['accounts_id']??0;
		$po_number = intval($GLOBALS['segment4name']);
		$sql = "SELECT transfer FROM po WHERE po_number = :po_number AND accounts_id = $accounts_id";
		$poObj = $this->db->query($sql, array('po_number'=>$po_number));
		if($poObj){
			$transfer = $poObj->fetch(PDO::FETCH_OBJ)->transfer;
			if($transfer==0){
				return "<meta http-equiv = \"refresh\" content = \"0; url = /Purchase_orders/edit/$po_number\" />";
			}
		}
	}

	public function AJ_edit_MoreInfo(){
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$user_id = $_SESSION['user_id']??0;
		$segment2name = $GLOBALS['segment2name'];
		$segment3name = $GLOBALS['segment3name'];
		$POST = json_decode(file_get_contents('php://input'), true);
		$po_number = intval($POST['po_number']);

		$sql = "SELECT * FROM po WHERE po_number = :po_number AND accounts_id = $accounts_id";
		$poObj = $this->db->query($sql, array('po_number'=>$po_number));
		if($poObj){
			$po_onerow = $poObj->fetch(PDO::FETCH_OBJ);
			$transfer = $po_onerow->transfer;
			if($transfer==0){
				return "<meta http-equiv = \"refresh\" content = \"0; url = /Purchase_orders/edit/$po_number\" />";
			}
			
			$po_id = $po_onerow->po_id;
			$supplier_id = $po_onerow->supplier_id;
			$status = $po_onerow->status;
			$jsonResponse['status'] = $status;
			$jsonResponse['po_number'] = $po_number;

			$company_subdomain = $user_email = '';
			$supplierObj = $this->db->query("SELECT accounts.company_subdomain, user.user_email FROM user, accounts WHERE accounts.accounts_id = user.accounts_id AND user.is_admin = 1 AND accounts.accounts_id = $supplier_id", array());
			if($supplierObj){
				$supplier_row = $supplierObj->fetch(PDO::FETCH_OBJ);
				$company_subdomain = stripslashes($supplier_row->company_subdomain);
				$user_email = $supplier_row->user_email;
			}
			$jsonResponse['company_subdomain'] = $company_subdomain;
			$jsonResponse['user_email'] = $user_email;
			if($transfer==1){
				$toOrFrom = 'to';
			}
			else{
				$toOrFrom = 'from';
			}
			$jsonResponse['toOrFrom'] = $toOrFrom;

			$pCustomFields = 0;
			$queryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'product'", array());
			if($queryObj){
				$pCustomFields = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			$jsonResponse['pCustomFields'] = $pCustomFields;
			
			$pPermission = 1;
			if(!empty($_SESSION["allowed"]) && !array_key_exists(5, $_SESSION["allowed"])) {
				$pPermission = 0;
			}
			$jsonResponse['pPermission'] = $pPermission;
			$jsonResponse['cols'] = 5;

			$jsonResponse['cartsData'] = $this->loadITCartData($po_id, $status);
			$jsonResponse['po_id'] = $po_id;
			$jsonResponse['supplier_id'] = $supplier_id;
			$jsonResponse['admin_id'] = $_SESSION["admin_id"];
			$jsonResponse['accounts_id'] = $_SESSION["accounts_id"];
			$showCartAvgCost = '';
			if(($_SESSION["admin_id"] >0) || $_SESSION["accounts_id"]<=6){
				$showCartAvgCost = $this->showCartAvgCost($po_id, $po_onerow->po_datetime);
			}
			$jsonResponse['showCartAvgCost'] = $showCartAvgCost;
			$jsonResponse['po_datetime'] = $po_onerow->po_datetime;
			$jsonResponse['transfer'] = $transfer;
			$jsonResponse['itaccounts_id'] = $po_onerow->accounts_id;
			$jsonResponse['lot_ref_no'] = $po_onerow->lot_ref_no;
			$jsonResponse['paid_by'] = $po_onerow->paid_by;
		}
		else{
			$jsonResponse['login'] = 'Inventory_Transfer/lists/';
		}
		
		return json_encode($jsonResponse);
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
			if(strcmp($shistory_type, 'Inventory Transfer Created')==0){
				$filterSql = "SELECT COUNT(po_id) AS totalrows FROM po 
							WHERE po_id = :po_id AND accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'po' AND table_id = :po_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'po' AND record_id = :po_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Inventory_Transfer/edit/', :po_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
				$bindData['po_id'] = $po_number;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Inventory_Transfer/edit/', $po_number) 
					UNION ALL 
						SELECT COUNT(po_id) AS totalrows FROM po 
							WHERE po_id = :po_id and accounts_id = $accounts_id 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'po' AND record_id = :po_id 
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
				WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Inventory_Transfer/edit/', $po_number) 
			UNION ALL 
				SELECT 'Inventory Transfer Created' AS afTitle FROM po 
					WHERE po_id = :po_id and accounts_id = $accounts_id 
			UNION ALL 
				SELECT 'Track Edits' AS afTitle FROM track_edits 
				WHERE accounts_id = $accounts_id AND record_for = 'po' AND record_id = :po_id 
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
		
		$bindData = array();
		$bindData['po_id'] = $po_id;            
		$po_number = 0;
		$poObj = $this->db->query("SELECT po_number FROM po WHERE po_id = $po_id AND accounts_id = $accounts_id", array());
		if($poObj){
			$po_number = $poObj->fetch(PDO::FETCH_OBJ)->po_number;
		}
				
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Inventory Transfer Created')==0){
				$filterSql = "SELECT 'po' AS tablename, po_datetime AS tabledate, po_id AS table_id, 'Inventory Transfer Created' AS activity_feed_title FROM po 
					WHERE po_id = :po_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'po' AND table_id = :po_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'po' AND record_id = :po_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Inventory_Transfer/edit/', :po_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
				$bindData['po_id'] = $po_number;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'po' AND activity_feed_link = CONCAT('/Inventory_Transfer/edit/', $po_number)  
					UNION ALL 
					SELECT 'po' AS tablename, po_datetime AS tabledate, po_id AS table_id, 'Inventory Transfer Created' AS activity_feed_title FROM po 
						WHERE po_id = :po_id AND accounts_id = $accounts_id 
					UNION ALL 
					SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'po' AND table_id = :po_id 
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
		$htmlStr = "";
		
		$po_number = intval($segment5name);
		$segment6name = $GLOBALS['segment6name'];
		$poObj = $this->db->query("SELECT po_id FROM po WHERE po_number = :po_number AND accounts_id = $accounts_id", array('po_number'=>$po_number),1);
		if($poObj){
			$po_id = $poObj->fetch(PDO::FETCH_OBJ)->po_id;
			$Printing = new Printing($this->db);
			
			if($segment4name=='barcode'){
				return $Printing->labelsInfo('IT', 'HTML');
			}
			elseif($segment4name=='label_MoreInfo'){
				$Common = new Common($this->db);
				$jsonResponse = array();
				$jsonResponse['login'] = '';
				$commonInfo = $Printing->labelsInfo('IT', 'commonInfo');
								
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
										$Price = number_format($regular_price,2);
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
				$currency = $_SESSION["currency"]??'৳';
				$dateformat = $_SESSION["dateformat"]??'m/d/Y';
				if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
				else{$calenderDate = 'MM/DD/YYYY';}
				$timeformat = $_SESSION["timeformat"]??'12 hour';
				$loadLangFile = $_SESSION["language"]??'English';

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
			$jsonResponse = $Printing->poInvoicesInfo($po_id, 'Inventory_Transfer');
		}		
		return json_encode($jsonResponse);
	}
	
	public function AJsend_ITEmail(){
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
			$mail_body = $Printing->poInvoicesInfo($po_id, 'Inventory_Transfer', 1);
			
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
			$mail->Subject = $this->db->translate('Inventory Transfer');
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
	
	public function AJsave_IT(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = 0;
		$savemsg = '';
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$poData = array();
		$poData['po_datetime'] = date('Y-m-d H:i:s');
		$poData['last_updated'] = date('Y-m-d H:i:s');
		$po_number = 1;
		$poObj = $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $accounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
		if($poObj){
			$po_number = $poObj[0]['po_number']+1;
		}
		$poData['po_number'] = $po_number;
		$poData['lot_ref_no'] = '';
		$poData['paid_by'] = '';
		$poData['supplier_id'] = intval($POST['supplier_id']??0);
		$poData['date_expected'] = date('Y-m-d');
		$poData['return_po'] = 0;
		$status = $this->db->checkCharLen('po.status', 'Open');
		$poData['status'] = $status;
		$poData['accounts_id'] = $accounts_id;
		$poData['user_id'] = $_SESSION["user_id"]??0;
		$poData['tax_is_percent'] = 0;
		$poData['taxes'] = 0.000;
		$poData['shipping'] = 0.00;
		$suppliers_invoice_no = $this->db->checkCharLen('po.suppliers_invoice_no', '');
		$poData['suppliers_invoice_no'] = $suppliers_invoice_no;
		$poData['invoice_date'] = date('Y-m-d');
		$poData['date_paid'] = date('Y-m-d');
		$poData['transfer'] = 1;
		
		$po_id = $this->db->insert('po', $poData);
		if($po_id){
			$id = $po_number;
			$savemsg = 'add-success';
		}
		else{
			$savemsg = 'error';
		}

		$array = array( 'login'=>'', 'id'=>$id, 'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	public function AJ_search_sku(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$search_sku = $POST['keyword_search']??'';
		$results = array();			
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$bindData = array();
		$sql = "SELECT item.item_number, manufacturer.name AS manufacture, product.product_name, product.colour_name, product.storage, product.physical_condition_name 
				FROM item, product LEFT JOIN manufacturer ON (product.manufacturer_id = manufacturer.manufacturer_id) 
				WHERE item.accounts_id = $accounts_id AND product.product_id = item.product_id 
					AND item.in_inventory = 1 
					AND item.item_publish = 1 AND product.product_publish = 1";
		$seleced_search = addslashes(trim((string) $search_sku));
		if ( $seleced_search == "" ) { $seleced_search = " "; }
		$seleced_searches = explode (" ", $seleced_search);
		if ( strpos($seleced_search, " ") === false ) {$seleced_searches[0] = $seleced_search;}
		$num = 0;
		while ( $num < sizeof($seleced_searches) ) {
			$sql .= " AND TRIM(CONCAT_WS(' ', item.item_number, product.sku, manufacturer.name, product.product_name, product.colour_name, product.storage, product.physical_condition_name)) LIKE CONCAT('%', :seleced_search$num, '%')";
			$bindData['seleced_search'.$num] = trim((string) $seleced_searches[$num]);
			$num++;
		}			 
		$sql .= " GROUP BY item.item_id ORDER BY manufacture ASC, product.product_name ASC, product.colour_name ASC, product.storage ASC, product.physical_condition_name ASC, item_number ASC";
		$query = $this->db->querypagination($sql, $bindData);
		if($query){
			
			foreach($query as $onerow){
				$name = stripslashes((string) $onerow['manufacture']);
				$product_name = stripslashes($name.' '.$onerow['product_name']);
				
				$colour_name = $onerow['colour_name'];
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $onerow['storage'];
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $onerow['physical_condition_name'];
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$item_number = $onerow['item_number'];
				$stockQty = 1;
				$str1 = "$item_number - $product_name";
				
				$results[] = array('labelval'=>$item_number,'label'=>$str1, 'stockQty'=>$stockQty);
			}
		} 
		
		$sql = "SELECT p.product_id, p.sku, p.product_type, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.manage_inventory_count, i.current_inventory 
				from inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id AND i.product_id = p.product_id AND p.product_publish = 1 
				 AND p.product_type IN ('Standard', 'Live Stocks') 
				 AND ((p.manage_inventory_count = 0 OR p.manage_inventory_count is null) OR (p.manage_inventory_count=1 AND i.current_inventory>0) OR p.allow_backorder = 1)";
		$bindData = array();
		$num = 0;
		while ( $num < sizeof($seleced_searches) ) {
			$sql .= " AND TRIM(CONCAT_WS(' ', p.sku, manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name)) LIKE CONCAT('%', :seleced_search$num, '%')";
			$bindData['seleced_search'.$num] = trim((string) $seleced_searches[$num]);
			$num++;
		}			
		$sql .= " GROUP BY p.sku";
		$query1 = $this->db->querypagination($sql, $bindData);
		if($query1){
			
			foreach($query1 as $onerow){
				$name = stripslashes((string) $onerow['manufacture']);
				$product_name = stripslashes($name.' '.$onerow['product_name']);
				
				$colour_name = $onerow['colour_name'];
				if($colour_name !=''){$product_name .= ' '.$colour_name;}
				
				$storage = $onerow['storage'];
				if($storage !=''){$product_name .= ' '.$storage;}
				
				$physical_condition_name = $onerow['physical_condition_name'];
				if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}

				$sku = $onerow['sku'];
				$label = "$product_name ($sku)";					
				$manage_inventory_count = intval($onerow['manage_inventory_count']);
				if($manage_inventory_count==1){
					$stockQty = $onerow['current_inventory'];
				}
				else{
					$stockQty = "*";
				}
				
				$results[] = array('labelval'=>$sku,'label'=>$label,'stockQty'=>$stockQty);
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$results));
	}

	public function allITImeiList(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$returnvalue = $POST['returnvalue']??'';
		$product_id = intval($POST['product_id']??0);
		$name = $POST['name']??'';
		
		$strextra = "FROM item WHERE accounts_id = $accounts_id AND product_id = $product_id AND in_inventory = 1 AND item_publish = 1";
		$bindData = array();
		if($name !=''){
			$strextra .= " AND (item_number LIKE CONCAT('%', :name, '%'))";
			$bindData['name'] = $name;
		}
		
		if($returnvalue=='datacount'){
			$sqlquery = "SELECT COUNT(item_id) AS counttotalrows $strextra";	
			$dataCount = 0;
			$queryObj = $this->db->query($sqlquery, $bindData);
			if($queryObj){
				$dataCount = $queryObj->fetch(PDO::FETCH_OBJ)->counttotalrows;
			}
			return json_encode(array('login'=>'', 'dataCount'=>$dataCount));
		}
		else{
			$sqlquery = "SELECT * $strextra ORDER BY item_number ASC";		
			$starting_val = intval($POST['starting_val']??0);
			$list_per_page = 12;
			$sqlquery .= " LIMIT $starting_val, $list_per_page";
			$query = $this->db->querypagination($sqlquery, $bindData);
			$tableData = array();
			if($query){
				foreach($query as $rowitem){
					
					$item_number = $rowitem['item_number'];
					$item_id = $rowitem['item_id'];
					$product_id = $rowitem['product_id'];
					
					$productObj = $this->db->query("SELECT p.*, manufacturer.name AS manufacture FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id = $prod_cat_man", array());
					if($productObj){
						$rowproduct = $productObj->fetch(PDO::FETCH_OBJ);
						
						$inventoryObj = $this->db->query("SELECT * FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
						if($inventoryObj){
							$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
						
							$product_name = stripslashes($rowproduct->product_name);
							$manufacturer_name = $rowproduct->manufacture;
							if($manufacturer_name !=''){$product_name = stripslashes(trim($manufacturer_name.' '.$product_name));}
							
							$colour_name = $rowproduct->colour_name;
							if($colour_name !=''){$product_name .= ' '.$colour_name;}
							
							$storage = $rowproduct->storage;
							if($storage !=''){$product_name .= ' '.$storage;}
							
							$physical_condition_name = $rowproduct->physical_condition_name;
							if($physical_condition_name !=''){$product_name .= ' '.$physical_condition_name;}
								
							$shortproduct_name = $product_name;
							
							if(strlen($product_name)>55){$shortproduct_name = substr($product_name, 0, 55).'..';}
							$sku = stripslashes($rowproduct->sku);
							
							$regular_price = $inventoryrow->regular_price;						
							$current_inventory = 1;
							$tableData[] = array('item_id'=>$item_id, 'item_number'=>$item_number, 'product_name'=>$product_name, 'shortproduct_name'=>$shortproduct_name, 'regular_price'=>$regular_price, 'current_inventory'=>$current_inventory);
						}
					}
				}
			}
			return json_encode(array('login'=>'', 'tableData'=>$tableData));
		}
	}
	
    public function addToITCart(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $status = '';		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$po_id = intval($POST['po_id']??0);
		$supplier_id = intval($POST['supplier_id']??0);
		$oneporow = false;
		$poObj = $this->db->query("SELECT * FROM po WHERE po_id = :po_id AND accounts_id = $accounts_id", array('po_id'=>$po_id),1);
		if($poObj){
			$oneporow = $poObj->fetch(PDO::FETCH_OBJ);
		}
		$fieldname = $POST['fieldname']??'';
		$fieldvalue = $POST['fieldvalue']??'';
		$po_items_id = 0;
		$created_on = date('Y-m-d H:i:s');
		$last_updated = date('Y-m-d H:i:s');

		if($fieldvalue !='' && $oneporow){
			if($poObj){
				$po_id = $oneporow->po_id;
				$status = $oneporow->status;				
			}
			$sql = "SELECT * FROM product WHERE accounts_id = $prod_cat_man and product_publish = 1 and $fieldname = :$fieldname and product_type IN ('Standard', 'Live Stocks') ORDER BY product_id ASC limit 0,1";
			$query = $this->db->querypagination($sql, array($fieldname=>$fieldvalue));
			if($query){
				foreach($query as $productonetow){
					$product_id = $productonetow['product_id'];
					$product_type = $productonetow['product_type'];
					$manage_inventory_count = intval($productonetow['manage_inventory_count']);

					$sql1 = "SELECT * FROM po_items WHERE po_id = $po_id and product_id = $product_id ORDER BY po_items_id ASC limit 0,1";
					$query1 = $this->db->querypagination($sql1, array());
					if($query1){
						foreach($query1 as $po_itemsrow){
							$po_items_id = $po_itemsrow['po_items_id'];
							$product_id = $po_itemsrow['product_id'];
							$item_type = $po_itemsrow['item_type'];
							$oldCost = $po_itemsrow['cost'];
							$old_po_ordered_qty = $po_itemsrow['ordered_qty'];
							$old_po_received_qty = $po_itemsrow['received_qty'];
							$ordered_qty = $old_po_ordered_qty;
							$received_qty = $old_po_received_qty;
							if($product_type =='Standard'){
								$ordered_qty++;
								$received_qty++;
							
								$updatedata = array('ordered_qty'=>$ordered_qty, 'received_qty'=>$received_qty);
								$updatepo_items = $this->db->update('po_items', $updatedata, $po_items_id);
								if($updatepo_items){
									$changed = array();
									if($old_po_ordered_qty != $ordered_qty){$changed['ordered_qty'] = array($old_po_ordered_qty, $ordered_qty);}
									if($old_po_received_qty != $received_qty){$changed['received_qty'] = array($old_po_received_qty, $received_qty);}
									
									if(!empty($changed)){
										$product_name = '';
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
										
										$moreInfo = array('table'=>'po_items', 'id'=>$po_items_id, 'product_id'=>$product_id, 'description'=>$product_name);
										$record_for = 'po_items';
										$record_id = $po_items_id;
										$teData = array();
										$teData['created_on'] = date('Y-m-d H:i:s');
										$teData['accounts_id'] = $_SESSION["accounts_id"];
										$teData['user_id'] = $_SESSION["user_id"];
										$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', $record_for);
										$teData['record_id'] = $record_id;
										$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
										$this->db->insert('track_edits', $teData);
									}
									
									$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE accounts_id = $accounts_id and product_id = $product_id", array());
									if($inventoryObj){
										$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
										$newinventory = $inventoryrow->current_inventory-1;
										$updateproduct = $this->db->update('inventory', array('current_inventory'=>$newinventory), $inventoryrow->inventory_id);
										$action = 'Update';
									}
								}
							}
							else{
								$action = 'Update';
							}
						}
					}
					else{

						$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory, ave_cost FROM inventory WHERE accounts_id = $accounts_id and product_id = $product_id", array());
						if($inventoryObj){
							$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
							$ave_cost = $inventoryrow->ave_cost;
							if($ave_cost<0){$ave_cost = 0;}
							$ordered_qty = $received_qty = 0;
							$item_type = 'livestocks';
							if($product_type =='Standard'){
								$item_type = 'product';
								$ordered_qty++;
								$received_qty++;
							}
							
							$poiData = array('created_on'=>date('Y-m-d H:i:s'),
											'user_id'=>$_SESSION["user_id"],
											'po_id'=>$po_id,
											'product_id'=>$product_id,
											'item_type'=>$item_type,
											'ordered_qty'=>$ordered_qty,
											'received_qty'=>$received_qty,
											'cost'=>round($ave_cost,2));
							$po_items_id = $this->db->insert('po_items', $poiData);
							if($po_items_id){
								$action = 'Add';
								if($product_type =='Standard'){
									$newinventory = $inventoryrow->current_inventory-1;
									$updateproduct = $this->db->update('inventory', array('current_inventory'=>$newinventory), $inventoryrow->inventory_id);
								}
							}
						}
						else{
							$action = 'notProductTransfer';
						}
					}
				}
			}
			else{
				$newitem_row = false;
				$itemObj =  $this->db->query("SELECT * FROM item WHERE item_number = :item_number AND accounts_id = $accounts_id AND item_publish = 1 AND in_inventory = 1", array('item_number'=>$fieldvalue));
				if($itemObj){
					$newitem_row = $itemObj->fetch(PDO::FETCH_OBJ);
				}

				if($newitem_row){
					$item_id = $newitem_row->item_id;

					$product_id = $newitem_row->product_id;
					$sql1 = "SELECT * FROM po_items WHERE po_id = $po_id and product_id = $product_id ORDER BY po_items_id ASC limit 0,1";
					$query1 = $this->db->querypagination($sql1, array());
					if($query1){
						foreach($query1 as $po_itemsrow){
							$po_items_id = $po_itemsrow['po_items_id'];
							$received_qty = $po_itemsrow['received_qty'];
							$cost = $po_itemsrow['cost'];
							
							$poCIArray = $this->db->querypagination("SELECT po_items_id, po_cart_item_id FROM po_cart_item WHERE item_id = $item_id AND return_po_items_id = 0 ORDER BY po_cart_item_id DESC LIMIT 0,1", array());
							if($poCIArray){
								$poCIRow = $poCIArray[0];
								
								$po_cart_item_id = $poCIRow['po_cart_item_id'];
								$oldpo_items_id = $poCIRow['po_items_id'];
								$oldCost = $cost;
								$oldPOITObj =  $this->db->query("SELECT cost FROM po_items WHERE po_items_id = $oldpo_items_id", array());
								if($oldPOITObj){
									$oldCost = $oldPOITObj->fetch(PDO::FETCH_OBJ)->cost;
								}
								
								$this->db->update('po_cart_item', array('return_po_items_id'=>$po_items_id), $po_cart_item_id);

								$poCartItemData = array('po_items_id' => $po_items_id,
														'item_id' => $item_id,
														'return_po_items_id' => 0);
								$this->db->insert('po_cart_item', $poCartItemData);

								$shipping_qty = 0;
								$po_cart_itemObj2 = $this->db->query("SELECT count(po_cart_item_id) AS totalShipQty FROM po_cart_item WHERE po_items_id = $po_items_id AND return_po_items_id = 0", array());
								if($po_cart_itemObj2){
									$shipping_qty = $po_cart_itemObj2->fetch(PDO::FETCH_OBJ)->totalShipQty;
								}
								$newAvgCost = round($po_itemsrow['received_qty']*$cost,2);
								$new_ave_cost = $cost;
								if($shipping_qty !=0 && $shipping_qty != $received_qty){
									$new_ave_cost = round(($newAvgCost+$oldCost)/$shipping_qty,2);	
								}
							
								$updatedata = array('ordered_qty'=>$shipping_qty, 'received_qty'=>$shipping_qty, 'cost'=>$new_ave_cost);
								$updatepo_items = $this->db->update('po_items', $updatedata, $po_items_id);
								if($updatepo_items){
									$this->db->update('item', array('in_inventory'=>0), $item_id);
									$action = 'Update';
								}
							}
						}
					}
					else{
						$cost = 0;
						$sql55 = "SELECT po_items.cost FROM po_items, po_cart_item WHERE po_cart_item.item_id = $item_id AND po_items.po_items_id = po_cart_item.po_items_id ORDER BY po_cart_item.po_cart_item_id DESC limit 0,1";
						$query55 = $this->db->querypagination($sql55, array());
						if($query55){
							foreach($query55 as $po_itemsrow22){
								$cost = $po_itemsrow22['cost'];
							}
						}
						$item_type = $this->db->checkCharLen('po_items.item_type', 'livestocks');
					
						$poiData = array('created_on'=>date('Y-m-d H:i:s'),
										'user_id'=>$_SESSION["user_id"],
										'po_id'=>$po_id,
										'product_id'=>$product_id,
										'item_type'=>$item_type,
										'cost'=>round($cost,2),
										'ordered_qty'=>1,
										'received_qty'=>1);
						$po_items_id = $this->db->insert('po_items', $poiData);
						if($po_items_id){

							$poCIArray = $this->db->querypagination("SELECT po_cart_item_id FROM po_cart_item WHERE item_id = $item_id AND return_po_items_id = 0 ORDER BY po_cart_item_id DESC LIMIT 0,1", array());
							if($poCIArray){
								$poCIRow = $poCIArray[0];
								$po_cart_item_id = $poCIRow['po_cart_item_id'];
								$this->db->update('po_cart_item', array('return_po_items_id'=>$po_items_id), $po_cart_item_id);

								$poCartItemData = array('po_items_id' => $po_items_id,
														'item_id' => $item_id,
														'return_po_items_id' => 0);
								$this->db->insert('po_cart_item', $poCartItemData);

								$this->db->update('item', array('in_inventory'=>0), $item_id);
								$action = 'Add';
							}
							else{
								$action = 'notProductOrder';
							}
						}
					}
				}
				else{
					$action = 'imeiNotFound';
				}
			}
		}
		else{
			$action = 'noOrderFound';
		}
		$cartsData = array();
		if($action =='Add' || $action == 'Update'){
			$cartsData = $this->loadITCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	}

    public function loadITCartData($po_id, $status){
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$poCartsData = array();
		$poObj =  $this->db->query("SELECT * FROM po WHERE po_id = :po_id AND accounts_id = $accounts_id", array('po_id'=>$po_id),1);
		if($poObj){
			$oneporow = $poObj->fetch(PDO::FETCH_OBJ);
			$return_po = $oneporow->return_po;
			$transfer = $oneporow->transfer;

			$po_itemsObj =  $this->db->query("SELECT * FROM po_items WHERE po_id = $po_id", array());
			$total_item = 0;
			if($po_itemsObj){
				$total_item = $po_itemsObj->rowCount();
				$i=0;
				while($row = $po_itemsObj->fetch(PDO::FETCH_OBJ)){
					$i++;
					$po_items_id = $row->po_items_id;
					$product_id = $item_id = $row->product_id;
					$item_type = $row->item_type;

					$product_name = $sku = '';
					$productObj =  $this->db->query("SELECT p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id = $prod_cat_man", array());
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
					}

					$cost = round($row->cost,2);

					$received_qty = floatval($row->received_qty);
					$cellPhoneData = array();
					if($item_type=='livestocks'){
						$sqlitem = "SELECT i.item_id, i.item_number, i.carrier_name, pci.return_po_items_id, pci.po_or_return FROM item i, po_cart_item pci WHERE pci.po_items_id = $po_items_id AND i.accounts_id = $accounts_id AND i.item_id = pci.item_id ORDER BY i.item_id ASC";
						$itemquery = $this->db->query($sqlitem, array());
						if($itemquery){
							while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
								$item_id = $itemrow->item_id;
								$pos_cart_item_id = 0;
								$pciObj =  $this->db->query("SELECT pos_cart_item_id FROM pos_cart_item WHERE item_id = $item_id", array());
								if($pciObj){
									$pos_cart_item_id = $pciObj->fetch(PDO::FETCH_OBJ)->pos_cart_item_id;
								}
								$item_number = $itemrow->item_number;								
								$carrier_name = $itemrow->carrier_name;
								$cellPhoneData[] = array('item_id'=>$item_id, 'carrier_name'=>$carrier_name, 'item_number'=>$item_number);
							}
						}
					}

					$poCartsData[] = array('po_items_id'=>$po_items_id, 'product_id'=>$product_id, 'item_type'=>$item_type, 'status'=>$status, 'product_name'=>$product_name, 'sku'=>$sku, 'cellPhoneData'=>$cellPhoneData, 'received_qty'=>$received_qty, 'cost'=>$cost);
				}
			}
		}
		return $poCartsData;
    }
	
	public function removeThisITItem(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $status = '';
		$po_id = $newreceived_qty = 0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;

		$po_items_id = intval($POST['po_items_id']??0);
		if($po_items_id>0){
			$Common = new Common($this->db);
			$po_itemsObj =  $this->db->query("SELECT po_items_id, po_id, product_id, cost, received_qty FROM po_items WHERE po_items_id = :po_items_id", array('po_items_id'=>$po_items_id),1);
			if($po_itemsObj){
				$po_itemsrow =  $po_itemsObj->fetch(PDO::FETCH_OBJ);
				
				$po_items_id = $po_itemsrow->po_items_id;
				$po_id = $po_itemsrow->po_id;
				$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
				if($poObj){				
					$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
				}
				$product_id = $po_itemsrow->product_id;					
				$old_po_cost = $po_itemsrow->cost;
				$old_po_received_qty = $po_itemsrow->received_qty;
				$old_po_totalcost = round($old_po_cost*$old_po_received_qty,2);
				
				$deletepo_items = $this->db->delete('po_items', 'po_items_id', $po_items_id);
				if($deletepo_items){
					
					$productObj = $this->db->query("SELECT product_type, manage_inventory_count FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man", array());
					if($productObj){
							
						$product_row = $productObj->fetch(PDO::FETCH_OBJ);
						
						$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory, ave_cost FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
						if($inventoryObj){
							$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
							
							$product_type = $product_row->product_type;
							$manage_inventory_count = $product_row->manage_inventory_count;
							//==================Current product condition===============//
							$current_inventory = $inventoryrow->current_inventory;
							$orderRepairShipQty = $Common->getOrderRepairShipQty($accounts_id, $product_id);
							$current_inventory += $orderRepairShipQty;
							
							//=============Undo product condition =================//
							$undo_inventory = $current_inventory;
						
							if($product_type !='Live Stocks'){
								if($old_po_received_qty !=0){
									$undo_inventory = $current_inventory+$old_po_received_qty;
								}
							}
							
							//===============New Product Condition==================//
							if($product_type !='Live Stocks'){
								$this->db->update('inventory', array('current_inventory'=>($undo_inventory-$orderRepairShipQty)), $inventoryrow->inventory_id);
								$Common = new Common($this->db);
								$Common->updateProdAveCost($accounts_id, $product_id);
							}								
						}
					}
					$action = 'Removed';
				}
			}
		}
		
		$cartsData = array();
		if($action =='Removed'){
			$cartsData = $this->loadITCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	}
	
	public function removeIMEIFromITCart(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $status = '';
		$po_id = $newreceived_qty = 0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;

		$po_items_id = intval($POST['po_items_id']??0);
		$item_id = intval($POST['item_id']??0);

		if($po_items_id>0 &&  $item_id>0){
			$po_itemsObj =  $this->db->query("SELECT po_id, product_id, received_qty, cost, item_type FROM po_items WHERE po_items_id = :po_items_id", array('po_items_id'=>$po_items_id),1);
			if($po_itemsObj){
				$oldPORRow =  $po_itemsObj->fetch(PDO::FETCH_OBJ);
				$po_id = $oldPORRow->po_id;
				$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
				if($poObj){				
					$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
				}
				$product_id = $oldPORRow->product_id;
				$item_type = $oldPORRow->item_type;
				$old_po_received_qty = $oldPORRow->received_qty;
				$cost = $oldPORRow->cost;
				$prevTotalCost = round($old_po_received_qty*$cost,2);				
				
				$poCartItemRemove = 0;
				$poCIArray = $this->db->querypagination("SELECT po_cart_item_id FROM po_cart_item WHERE item_id = $item_id AND po_items_id = $po_items_id AND return_po_items_id = 0 ORDER BY po_cart_item_id DESC LIMIT 0,1", array());
				if($poCIArray){			
					$poCIRow = $poCIArray[0];								
					$po_cart_item_id = $poCIRow['po_cart_item_id'];
					$this->db->delete('po_cart_item', 'po_cart_item_id', $po_cart_item_id);
					$poCartItemRemove++;
				}
				if($poCartItemRemove>0){
					
					$oldpo_items_id = $oldpo_cart_item_id = 0;
					$poCIArray = $this->db->querypagination("SELECT po_items_id, po_cart_item_id FROM po_cart_item WHERE item_id = $item_id AND return_po_items_id = $po_items_id ORDER BY po_cart_item_id DESC LIMIT 0,1", array());
					if($poCIArray){			
						$poCIRow = $poCIArray[0];								
						$oldpo_cart_item_id = $poCIRow['po_cart_item_id'];
						$oldpo_items_id = $poCIRow['po_items_id'];
						$this->db->update('po_cart_item', array('return_po_items_id'=>0), $oldpo_cart_item_id);							
					}
					
					$updateitem = $this->db->update('item', array('in_inventory'=>1), $item_id);
					if($updateitem){
						$newreceived_qty = 0;
						$po_cart_itemObj2 = $this->db->query("SELECT count(po_cart_item_id) AS totalShipQty FROM po_cart_item WHERE po_items_id = $po_items_id AND return_po_items_id = 0", array());
						if($po_cart_itemObj2){
							$newreceived_qty = $po_cart_itemObj2->fetch(PDO::FETCH_OBJ)->totalShipQty;
						}
												
						$changed = array();
						if($old_po_received_qty != $newreceived_qty){$changed['received_qty'] = array($old_po_received_qty, $newreceived_qty);}
						
						if(!empty($changed)){
							$description = $this->db->translate('Remove IMEI Number').' '.$this->db->translate('from').' ';
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
							
							$moreInfo = array('table'=>'po_items', 'id'=>$po_items_id, 'product_id'=>$product_id, 'description'=>$description);
							$teData = array();
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $_SESSION["accounts_id"];
							$teData['user_id'] = $_SESSION["user_id"];
							$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'po');
							$teData['record_id'] = $po_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);
						}
					
						$oldCost = $cost;
						$oldPOITObj =  $this->db->query("SELECT cost FROM po_items WHERE po_items_id = $oldpo_items_id", array());
						if($oldPOITObj){
							$oldCost = $oldPOITObj->fetch(PDO::FETCH_OBJ)->cost;
						}
						$new_ave_cost = $oldCost;
						if($newreceived_qty !=0){
							$new_ave_cost = round(($prevTotalCost-$oldCost)/$newreceived_qty,2);
						}
						
						$updatepo_items = $this->db->update('po_items', array('ordered_qty'=>$newreceived_qty, 'received_qty'=>$newreceived_qty, 'cost'=>$new_ave_cost), $po_items_id);
						$action = 'Removed';
					}
				}
			}
		}
		
		$cartsData = array();
		if($action =='Removed'){
			$cartsData = $this->loadITCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	}
	
	public function updateit_item(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $status = '';
		$po_id = 0;
		$po_items_id = intval($POST['po_items_id']??0);
		$newcost = floatval($POST['cost']??0.00);
		$newreceived_qty = floatval($POST['received_qty']??0);
		$accounts_id = $_SESSION["accounts_id"]??0;	
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;

		$po_itemsObj =  $this->db->query("SELECT po_id, po_items_id, product_id, cost, ordered_qty, received_qty, item_type FROM po_items WHERE po_items_id = :po_items_id", array('po_items_id'=>$po_items_id),1);
		if($po_itemsObj){
			$po_itemsrow =  $po_itemsObj->fetch(PDO::FETCH_OBJ);
			$Common = new Common($this->db);
			$po_id = $po_itemsrow->po_id;
			$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
			if($poObj){				
				$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
			}
			$item_type = $po_itemsrow->item_type;
			$po_items_id = $po_itemsrow->po_items_id;
			$product_id = $po_itemsrow->product_id;
			
			$old_po_cost = $po_itemsrow->cost;
			$old_po_ordered_qty = $po_itemsrow->ordered_qty;
			$old_po_received_qty = $po_itemsrow->received_qty;
			$old_po_totalcost = round($old_po_cost*$old_po_received_qty,2);
			$po_itemsData = array('cost'=>$newcost,
								'ordered_qty'=>$newreceived_qty,
								'received_qty'=>$newreceived_qty);
			$updatepo_items = $this->db->update('po_items', $po_itemsData, $po_items_id);
			if($updatepo_items){
				
				$changed = array();
				if($old_po_cost != $newcost){$changed['cost'] = array($old_po_cost, number_format($newcost,2));}
				if($old_po_ordered_qty != $newreceived_qty){$changed['ordered_qty'] = array($old_po_ordered_qty, $newreceived_qty);}
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
				
				$productObj = $this->db->query("SELECT product_type FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man AND product_publish = 1", array());
				if($productObj){
						
					$product_row = $productObj->fetch(PDO::FETCH_OBJ);
					
					$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory, ave_cost FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
					if($inventoryObj){
						$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
						
						$product_type = $product_row->product_type;
						//==================Current product condition===============//
						$current_inventory = $inventoryrow->current_inventory;
						$orderRepairShipQty = $Common->getOrderRepairShipQty($accounts_id, $product_id);
						$current_inventory += $orderRepairShipQty;
						
						//=============Undo product condition =================//
						$undo_inventory = $current_inventory;
					
						if($product_type !='Live Stocks'){
							if($old_po_received_qty>0){										
								$undo_inventory = $current_inventory+$old_po_received_qty;
							}
						}
						
						//===============New Product Condition==================//
						$new_current_inventory = $undo_inventory-$newreceived_qty;
						$additionalreceived_qty = 0;
						
						if($product_type !='Live Stocks'){							
							$additionalreceived_qty = $newreceived_qty-$undo_inventory;
							$updateproduct = $this->db->update('inventory', array('current_inventory'=>($new_current_inventory-$orderRepairShipQty)), $inventoryrow->inventory_id);
							
							$Common = new Common($this->db);
							$Common->updateProdAveCost($accounts_id, $product_id);
						}
					}
				}					
				$action = 'Update';
			}				
		}
		$cartsData = array();
		if($action == 'Update'){
			$cartsData = $this->loadITCartData($po_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'po_id'=>$po_id, 'cartsData'=>$cartsData));
	}

	public function itCancel(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$po_id = intval($POST['po_id']??0);
		$status = trim((string) $POST['status']??'');
		$status = $this->db->checkCharLen('po.status', $status);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$returnStr = 0;
		$updatepo = $this->db->update('po', array('status'=>$status), $po_id);
		if($updatepo){
			$returnStr++;
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
    public function update_it_complete(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$po_id = intval($POST['po_id']??0);
		$poSql = "SELECT * FROM po WHERE po_id = :po_id AND accounts_id = $accounts_id";
		$poObj =  $this->db->query($poSql, array('po_id'=>$po_id),1);
		if($poObj){
			$po_row = $poObj->fetch(PDO::FETCH_OBJ);
			$Common = new Common($this->db);
			$accounts_id = $po_row->accounts_id;
			$toaccounts_id = $po_row->supplier_id;
			$status = 'Closed';
			
			//==============Add New PO for To location================//
			$poData = (array) $po_row;
			unset($poData['po_id']);
			unset($poData['return_po']);
			$po_number = 1;
			$poObj2 =  $this->db->querypagination("SELECT po_number FROM po WHERE accounts_id = $toaccounts_id ORDER BY po_number DESC LIMIT 0, 1", array());
			if($poObj2){
				$po_number = $poObj2[0]['po_number']+1;
			}
			$touser_id = $_SESSION["user_id"];
			$toUserObj2 =  $this->db->querypagination("SELECT user_id FROM user WHERE accounts_id = $toaccounts_id AND is_admin = 1 ORDER BY user_id ASC LIMIT 0, 1", array());
			if($toUserObj2){
				$touser_id = $toUserObj2[0]['user_id'];
			}
			$poData['po_datetime'] = date('Y-m-d H:i:s');
			$poData['return_po'] = 0;
			$lot_ref_no = $this->db->checkCharLen('po.lot_ref_no', $poData['po_number']);
			$poData['lot_ref_no'] = $lot_ref_no;
			$poData['paid_by'] = $this->db->checkCharLen('po.paid_by', $poData['paid_by']);
			$poData['po_number'] = $po_number;
			$poData['accounts_id'] = $toaccounts_id;
			$poData['user_id'] = $touser_id;
			$poData['supplier_id'] = $accounts_id;
			$poData['transfer'] = 2;
			$status = $this->db->checkCharLen('po.status', $status);
			$poData['status'] = $status;

			$newpo_id = $this->db->insert('po', $poData);

			//==============End PO for To location================//

			if($newpo_id>0){
				$sqlquery = "SELECT * FROM po_items WHERE po_id = $po_id";
				$query = $this->db->query($sqlquery, array());
				if($query){
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						$po_items_id = $row->po_items_id;
						$product_id = $row->product_id;
						$old_po_cost = $row->cost;
						$old_po_received_qty = $row->received_qty;
						$old_po_totalcost = round($old_po_cost*$old_po_received_qty,2);

						//==============Add New po_items for To location================//
						$poiData = (array) $row;
						unset($poiData['po_items_id']);
						$poiData['created_on'] = date('Y-m-d H:i:s');
						$poiData['user_id'] = $touser_id;
						$poiData['po_id'] = $newpo_id;
						$newpo_items_id = $this->db->insert('po_items', $poiData);
						$returnStr = 1;

						//==============End po_items for To location================//
						$productObj = $this->db->query("SELECT * FROM product WHERE accounts_id = $prod_cat_man AND product_id = $product_id AND product_publish=1", array());
						if($productObj){
							$product_row = $productObj->fetch(PDO::FETCH_OBJ);

							$item_type = $row->item_type;
							if($item_type=='livestocks'){

								$sqlitem = "SELECT i.* FROM item i, po_cart_item pci WHERE pci.po_items_id = $po_items_id AND i.accounts_id = $accounts_id AND i.item_id = pci.item_id ORDER BY i.item_id ASC";
								$itemquery = $this->db->query($sqlitem, array());
								if($itemquery){
									while($itemrow = $itemquery->fetch(PDO::FETCH_OBJ)){
										$item_id = $itemrow->item_id;
										$item_number = $itemrow->item_number;

										//==============Add New PO for To location================//
										$itemData = (array) $itemrow;
										unset($itemData['item_id']);
										unset($itemData['created_on']);
										$itemData['accounts_id'] = $toaccounts_id;
										$itemData['in_inventory'] = 1;
										$itemData['last_updated'] = date('Y-m-d H:i:s');
										$newitem_id = $newin_inventory = 0;
										$itemObj2 = $this->db->query("SELECT item_id, in_inventory FROM item WHERE accounts_id = $toaccounts_id and item_number = '$item_number' ORDER BY item_id ASC", array());
										if($itemObj2){
											$newitemRow = $itemObj2->fetch(PDO::FETCH_OBJ);
											$newitem_id = $newitemRow->item_id;
											$newin_inventory = $newitemRow->in_inventory;
										}
										if($newin_inventory>0){
											$this->db->update('item', $itemData, $newitem_id);
										}
										else{
											$itemData['created_on'] = date('Y-m-d H:i:s');
											$newitem_id = $this->db->insert('item', $itemData);
										}
										$poReturnCartItemData=array('po_items_id' => $newpo_items_id,
																	'item_id' => $newitem_id,
																	'return_po_items_id' => 0);
										$this->db->insert('po_cart_item', $poReturnCartItemData);
									}
								}
							}
							else{
								$product_type = $product_row->product_type;
								$category_id = $product_row->category_id;

								//==============Inventory for To Location Update===================//
								$inventoryObj2 = $this->db->query("SELECT inventory_id, current_inventory, ave_cost FROM inventory WHERE accounts_id = $toaccounts_id and product_id = $product_id", array());
								if($inventoryObj2){
									$toinventoryrow = $inventoryObj2->fetch(PDO::FETCH_OBJ);

									$current_inventory = $toinventoryrow->current_inventory;
									$orderRepairShipQty = $Common->getOrderRepairShipQty($toaccounts_id, $product_id);
									$current_inventory += $orderRepairShipQty;
							
									//=============Undo product condition =================//
									$newinventory = $current_inventory;
									$newinventory = $current_inventory+$old_po_received_qty;
									
									$updateproduct = $this->db->update('inventory', array('current_inventory'=>($newinventory-$orderRepairShipQty)), $toinventoryrow->inventory_id);
									$Common = new Common($this->db);
									$Common->updateProdAveCost($accounts_id, $product_id);
									$Common->updateProdAveCost($toaccounts_id, $product_id);
								}
							}
						}

						$this->db->update('po_items', array('ordered_qty'=>-$old_po_received_qty, 'received_qty' => -$old_po_received_qty), $po_items_id);

					}
				}
				$status = $this->db->checkCharLen('po.status', 'Closed');
				$lot_ref_no = $this->db->checkCharLen('po.lot_ref_no', $po_number);
				$updatepos = $this->db->update('po', array('status'=>$status, 'lot_ref_no'=>$lot_ref_no), $po_id);

			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
    }
    
	public function cancelIT(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 0;
		
		$po_id = intval($POST['po_id']??0);
		$status = trim((string) $POST['status']??'');
		$status = $this->db->checkCharLen('po.status', $status);
				
		$updatepo = $this->db->update('po', array('status'=>$status), $po_id);
		if($updatepo){
			$returnStr++;
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}	
		
    public function saveBulkITData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = $status = '';
		$po_id = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;		
		$po_items_id = intval($POST['po_items_id']??0);
		$newcost = floatval($POST['cost']??0);
		$received_qty = floatval($POST['received_qty']??0);
		$bulkimei = $POST['bulkimei']??'';
		$newTotalCost = $received_qty*$newcost;
		$returnData = array('login'=>'');
		
		$poItemObj = $this->db->query("SELECT po_id, cost, product_id, ordered_qty, received_qty, item_type FROM po_items WHERE po_items_id = :po_items_id", array('po_items_id'=>$po_items_id),1);
		if($poItemObj){
			$onepo_itemsrow = $poItemObj->fetch(PDO::FETCH_OBJ);
			$po_id = $onepo_itemsrow->po_id;
			$poObj = $this->db->query("SELECT status FROM po WHERE accounts_id = $accounts_id AND po_id = :po_id", array('po_id'=>$po_id),1);
			if($poObj){				
				$status = $poObj->fetch(PDO::FETCH_OBJ)->status;
			}
			$item_type = $onepo_itemsrow->item_type;
			$product_id = $onepo_itemsrow->product_id;
			$oldCost = $onepo_itemsrow->cost;
			$old_po_ordered_qty = $onepo_itemsrow->ordered_qty;
			$old_po_received_qty = $onepo_itemsrow->received_qty;
			$prevTotalCost = round($old_po_received_qty*$oldCost,2);
			$newTotalCost = 0;
							
			if($bulkimei !=''){
				$product_id = $onepo_itemsrow->product_id;                    
				$item_numberData = preg_split("/\\r\\n|\\r|\\n/", $bulkimei);

				if(count($item_numberData)>0){
					$imeisaved = $imeismallerthan = $imeilongerthan = $duplicateimei = 0;
					$totalIMEI = count($item_numberData);
					foreach($item_numberData as $item_number){
						$item_number = addslashes(trim((string) $item_number));
						if($item_number=='' || strlen($item_number)<2 || strlen($item_number)>20){}
						else{

							$itemObj = $this->db->query("SELECT item_id FROM item WHERE accounts_id = $accounts_id AND in_inventory = 1 AND item_number = '$item_number'", array());
							if($itemObj){
								$item_id = $itemObj->fetch(PDO::FETCH_OBJ)->item_id;
																	
								$poCIArray = $this->db->querypagination("SELECT po_cart_item_id FROM po_cart_item WHERE item_id = $item_id AND return_po_items_id = 0 ORDER BY po_cart_item_id DESC LIMIT 0,1", array());
								if($poCIArray){
									$poCIRow = $poCIArray[0];
									$po_cart_item_id = $poCIRow['po_cart_item_id'];
									
									$this->db->update('po_cart_item', array('return_po_items_id'=>$po_items_id), $po_cart_item_id);
									
									$this->db->update('item', array('in_inventory'=>0), $item_id);
									
									$poCartItemData = array('po_items_id' => $po_items_id,
															'item_id' => $item_id,
															'return_po_items_id' => 0);
									$this->db->insert('po_cart_item', $poCartItemData);
								}								
							}
						}
					}
				}
			}
			
			$newreceived_qty = 0;
			$pociObj = $this->db->query("SELECT COUNT(po_cart_item_id) AS counttotalrows FROM po_cart_item WHERE po_items_id = $po_items_id", array());
			if($pociObj){
				$newreceived_qty = $pociObj->fetch(PDO::FETCH_OBJ)->counttotalrows;
			}
			
			$updatedata = array('ordered_qty'=>$newreceived_qty, 'received_qty'=>$newreceived_qty, 'cost'=>$newcost);
			$returnData['updatedata'] = $updatedata;
			$returnData['po_items_id'] = $po_items_id;
			$updatepo_items = $this->db->update('po_items', $updatedata, $po_items_id);
			if($updatepo_items){
				$changed = array();
				if($oldCost != $newcost){$changed['cost'] = array($oldCost, number_format($newcost,2));}
				if($old_po_ordered_qty != $newreceived_qty){$changed['ordered_qty'] = array($old_po_ordered_qty, $newreceived_qty);}
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
				
				$action = 'Add';
			}
		}
		$cartsData = array();
		if($action =='Add'){
			$cartsData = $this->loadITCartData($po_id, $status);
		}
		$returnData['action'] = $action;
		$returnData['po_id'] = $po_id;
		$returnData['status'] = $status;
		$returnData['cartsData'] = $cartsData;
		
		return json_encode($returnData);
	}
	
	public function showCartAvgCost($po_id, $checkDateTime){
		$avgCostData = array();
		if(isset($_SESSION["prod_cat_man"])){
			$Common = new Common($this->db);
			$sqlquery = "SELECT po.accounts_id, po.supplier_id, po.po_number, po.lot_ref_no, po.po_datetime, poi.po_items_id, manufacturer.name AS manufacture, p.product_name, p.sku, po.transfer, poi.received_qty FROM po, po_items poi, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE po.po_id = $po_id AND poi.item_type = 'livestocks' AND po.transfer>0 AND po.po_id = poi.po_id AND p.product_id = poi.product_id ORDER BY poi.po_items_id ASC";
			$query = $this->db->query($sqlquery, array());
			if($query){
				while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
					if($oneRow->transfer==2){
						$accounts_id = $oneRow->supplier_id;
					}
					else{
						$accounts_id = $oneRow->accounts_id;
					}
					$returnData = $Common->itCartCellphoneAveCost($oneRow->po_items_id, $oneRow->po_datetime, 0, $oneRow->transfer, $accounts_id);
					if(!empty($returnData)){
						$cost = round($returnData[0],2);
						$newCost = round($returnData[1],2);
						$cartQty = $returnData[2];
						$newQty = $returnData[3];
						$IMEIStr = $returnData[4];
						$cls = '';
						if($cost !=$newCost || $cartQty != $newQty){
							$cls = 'bgyellow';
						}
						$avgCostData[] = array('cls'=>$cls, 'po_items_id'=>$oneRow->po_items_id, 'productName'=>stripslashes(trim("$oneRow->manufacture $oneRow->product_name ($oneRow->sku)")), 'IMEIStr'=>$IMEIStr, 'cost'=>$cost, 'received_qty'=>$oneRow->received_qty, 'newCost'=>$newCost);
					}
				}
			}
		}
		return $avgCostData;
	}
	
	public function updateITCartMobileAveCost(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$po_id = intval($POST['po_id']??0);
		$po_datetime = $POST['po_datetime']??'1000-01-01';
		$transfer = $POST['transfer']??1;
		$accounts_id = intval($POST['accounts_id']??0);
		$supplier_id = intval($POST['supplier_id']??0);
		
		if($po_id>0){
			if($transfer==2){
				$poAccountsId = $supplier_id;
			}
			else{
				$poAccountsId = $accounts_id;
			}
			$Common = new Common($this->db);
			$sqlquery = "SELECT po_items_id FROM po_items WHERE po_id = $po_id  AND item_type = 'livestocks' ORDER BY po_items_id ASC";
			$query = $this->db->query($sqlquery, array());
			if($query){
				while($row = $query->fetch(PDO::FETCH_OBJ)){
					$returnData = $Common->itCartCellphoneAveCost($row->po_items_id, $po_datetime, 1, $transfer, $poAccountsId);
					$returnStr = json_encode($returnData);
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	//================Joining Class==============//
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