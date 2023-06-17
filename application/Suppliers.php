<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Suppliers{
	protected $db;
	private int $page, $totalRows, $suppliers_id;
	private string $sorting_type, $data_type, $keyword_search, $history_type;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}

   private function filterAndOptionsSupplier(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$sorting_type = $this->sorting_type;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Suppliers";
		$_SESSION["list_filters"] = array('sorting_type'=>$sorting_type, 'keyword_search'=>$keyword_search);
		$sqlPublish = " AND suppliers_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND suppliers_publish = 0";
		}
		
		$filterSql = "FROM suppliers WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(suppliers_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;
	}
	
   private function loadTableRowsSupplier(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];		
		$page = $this->page;
		$totalRows = $this->totalRows;
		$ssorting_type = $this->sorting_type;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'TRIM(UPPER(CONCAT_WS(\' \', company, first_name, last_name))) ASC', 
								1=>'company ASC', 
								2=>'first_name ASC', 
								3=>'last_name ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}

		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND suppliers_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND suppliers_publish = 0";
		}
		$filterSql = "FROM suppliers WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', first_name, last_name, company, email, contact_no, secondary_phone, fax)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT suppliers_id, accounts_id, created_on, company, first_name, last_name, email, contact_no $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		
		$query = $this->db->querypagination($sqlquery, $bindData);
		$i = $starting_val+1;

		$tabledata = array();
		if($query){
			foreach($query as $oneRow){
				$suppliers_id = $oneRow['suppliers_id'];
				
				$name = stripslashes($oneRow['company']);
				$first_name = stripslashes($oneRow['first_name']);
				if($name !=''){$name .= ', ';}
				$name .= $first_name;
				$last_name = stripslashes($oneRow['last_name']);
				if($name !=''){$name .= ' ';}
				$name .= $last_name;
				
				$email = $oneRow['email'];
				if($email==''){$email = '&nbsp;';}
				$contact_no = $oneRow['contact_no'];
				if($contact_no==''){$contact_no = '&nbsp;';}
				
				$tabledata[] = array($suppliers_id, $name, $email, $contact_no);
			}
		}
		return $tabledata;
   }
	
	public function AJgetPageSupplier($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sorting_type = $POST['sorting_type']??0;
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->sorting_type = $sorting_type;
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptionsSupplier();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRowsSupplier();
		
		return json_encode($jsonResponse);
	}	
	
	public function AJget_SuppliersPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$suppliers_id = intval($POST['suppliers_id']??0);
		
		$suppliersData = array();
		$suppliersData['login'] = '';
		$suppliersData['suppliers_id'] = 0;
		$suppliersData['company'] = '';
		$suppliersData['first_name'] = '';
		$suppliersData['last_name'] = '';
		$suppliersData['email'] = '';
		$suppliersData['offers_email'] = 0;
		$suppliersData['contact_no'] = '';
		$suppliersData['secondary_phone'] = '';
		$suppliersData['fax'] = '';
		$suppliersData['shipping_address_one'] = '';
		$suppliersData['shipping_address_two'] = '';
		$suppliersData['shipping_city'] = '';
		$suppliersData['shipping_state'] = '';
		$suppliersData['shipping_zip'] = '';
		$suppliersData['shipping_country'] = '';			
		$suppliersData['created_on'] = '';
		$suppliersData['last_updated'] = '';
		$suppliersData['accounts_id'] = '';
		$suppliersData['website'] = '';
		
		if($suppliers_id>0 && $prod_cat_man>0){
			
			$suppliersObj = $this->db->query("SELECT * FROM suppliers WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man", array('suppliers_id'=>$suppliers_id),1);
			if($suppliersObj){
				$suppliersRow = $suppliersObj->fetch(PDO::FETCH_OBJ);
				$suppliersData['suppliers_id'] = $suppliers_id;				
				$suppliersData['company'] = stripslashes(trim((string) $suppliersRow->company));
				$suppliersData['first_name'] = stripslashes(trim((string) $suppliersRow->first_name));
				$suppliersData['last_name'] = stripslashes(trim((string) $suppliersRow->last_name));
				$suppliersData['email'] = trim((string) $suppliersRow->email);
				$suppliersData['offers_email'] = intval($suppliersRow->offers_email);
				$suppliersData['contact_no'] = trim((string) $suppliersRow->contact_no);
				$suppliersData['secondary_phone'] = trim((string) $suppliersRow->secondary_phone);
				$suppliersData['fax'] = trim((string) $suppliersRow->fax);
				
				$suppliersData['shipping_address_one'] = trim((string) $suppliersRow->shipping_address_one);
				$suppliersData['shipping_address_two'] = trim((string) $suppliersRow->shipping_address_two);
				$suppliersData['shipping_city'] = trim((string) $suppliersRow->shipping_city);
				$suppliersData['shipping_state'] = trim((string) $suppliersRow->shipping_state);
				$suppliersData['shipping_zip'] = trim((string) $suppliersRow->shipping_zip);
				$suppliersData['shipping_country'] = trim((string) $suppliersRow->shipping_country);
				if($suppliersRow->shipping_country=='0'){
					$suppliersData['shipping_country'] = '';
				}
					
				$suppliersData['created_on'] = $suppliersRow->created_on;
				$suppliersData['last_updated'] = $suppliersRow->last_updated;
				$suppliersData['accounts_id'] = $suppliersRow->accounts_id;
				$suppliersData['website'] = stripslashes(trim((string) $suppliersRow->website));
			}
		}
		
		return json_encode($suppliersData);
	}
	
	public function AJsave_Suppliers(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = $supplier_name = '';
		$suppliers_id = intval($POST['suppliers_id']??0);
		$frompage = addslashes(trim((string) $POST['frompage']??''));
		$first_name = addslashes(trim((string) $POST['first_name']??''));
		$first_name = $this->db->checkCharLen('suppliers.first_name', $first_name);
		
		$last_name = addslashes(trim((string) $POST['last_name']??''));
		$last_name = $this->db->checkCharLen('suppliers.last_name', $last_name);
		
		$company = addslashes(trim((string) $POST['company']??''));
		$company = $this->db->checkCharLen('suppliers.company', $company);
		
		$email = trim((string) $POST['email']??'');
		$email = $this->db->checkCharLen('suppliers.email', $email);
		
		$user_id = $_SESSION["user_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$suppliersData = array();
		$suppliersData['first_name'] = $first_name;
		$suppliersData['company'] = $company;
		
		$suppliersData['last_name'] = $last_name;
		$suppliersData['email'] = $email;	
		$offers_email = intval($POST['offers_email']??0);
		$suppliersData['offers_email'] = $offers_email;
		$suppliersData['contact_no'] = $this->db->checkCharLen('suppliers.contact_no', trim((string) $POST['contact_no']??''));
		$suppliersData['secondary_phone'] = $this->db->checkCharLen('suppliers.secondary_phone', trim((string) $POST['secondary_phone']??''));
		$suppliersData['fax'] = $this->db->checkCharLen('suppliers.fax', trim((string) $POST['fax']??''));
		$suppliersData['shipping_address_one'] = $this->db->checkCharLen('suppliers.shipping_address_one', trim((string) $POST['shipping_address_one']??''));
		$suppliersData['shipping_address_two'] = $this->db->checkCharLen('suppliers.shipping_address_two', trim((string) $POST['shipping_address_two']??''));
		$suppliersData['shipping_city'] = $this->db->checkCharLen('suppliers.shipping_city', trim((string) $POST['shipping_city']??''));
		$suppliersData['shipping_state'] = $this->db->checkCharLen('suppliers.shipping_state', trim((string) $POST['shipping_state']??''));
		$suppliersData['shipping_zip'] = $this->db->checkCharLen('suppliers.shipping_zip', trim((string) $POST['shipping_zip']??''));
		$suppliersData['shipping_country'] = $this->db->checkCharLen('suppliers.shipping_country', trim((string) $POST['shipping_country']??''));
		$suppliersData['website'] = $this->db->checkCharLen('suppliers.website', trim((string) $POST['website']??''));
		$suppliersData['accounts_id'] = $prod_cat_man;
		$suppliersData['user_id'] = $user_id;

		if($suppliers_id==0){
			$oldsuppliers_id = $suppliers_publish = 0;
			$suppliersObj = $this->db->query("SELECT suppliers_id, suppliers_publish FROM suppliers WHERE accounts_id = $prod_cat_man AND company = :company AND email = :email", array('company'=>$company, 'email'=>$email));
			if($suppliersObj){
				while($onerow = $suppliersObj->fetch(PDO::FETCH_OBJ)){
					$oldsuppliers_id = $onerow->suppliers_id;
					$suppliers_publish = intval($onerow->suppliers_publish);
				}
			}
			if($oldsuppliers_id>0){
				if($suppliers_publish>0){
					$savemsg = 'nameEmailExist';
				}
				else{
					$savemsg = 'nameEmailExistInArchive';
				}
			}
			else{
				$suppliersData['created_on'] = date('Y-m-d H:i:s');
				$suppliersData['last_updated'] = date('Y-m-d H:i:s');
					
				$suppliers_id = $this->db->insert('suppliers', $suppliersData);
				if($suppliers_id){
					
					$id = $suppliers_id;
					$savemsg = 'add-success';
				}
				else{
					$savemsg = 'errorAdding';
				}
			}				
		}
		else{
			$oldsuppliers_id = $suppliers_publish = 0;
			$suppliersObj = $this->db->query("SELECT suppliers_id, suppliers_publish FROM suppliers WHERE accounts_id = $prod_cat_man AND company = :company AND email = :email AND suppliers_id != :suppliers_id", array('company'=>$company, 'email'=>$email, 'suppliers_id'=>$suppliers_id));
			if($suppliersObj){
				while($onerow = $suppliersObj->fetch(PDO::FETCH_OBJ)){
					$oldsuppliers_id = $onerow->suppliers_id;
					$suppliers_publish = intval($onerow->suppliers_publish);
				}
			}
			if($oldsuppliers_id>0){
				if($suppliers_publish>0){
					$savemsg = 'companyEmailExist';
				}
				else{
					$savemsg = 'nameEmailExistInArchive';
				}
			}
			else{
				$oneTRowObj = $this->db->querypagination("SELECT * FROM suppliers WHERE suppliers_id = $suppliers_id", array());
				
				$update = $this->db->update('suppliers', $suppliersData, $suppliers_id);
				if($update){
					$changed = array();
					foreach($suppliersData as $fieldName=>$fieldValue){
						$prevFieldVal = $oneTRowObj[0][$fieldName];
						if($prevFieldVal != $fieldValue){
							if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
							if($fieldValue=='1000-01-01'){$fieldValue = '';}
							$changed[$fieldName] = array($prevFieldVal, $fieldValue);
						}
					}
					if(!empty($changed)){
						$moreInfo = $teData = array();
						$teData['created_on'] = date('Y-m-d H:i:s');
						$teData['accounts_id'] = $_SESSION["accounts_id"];
						$teData['user_id'] = $_SESSION["user_id"];
						$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'suppliers');
						$teData['record_id'] = $suppliers_id;
						$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
						$this->db->insert('track_edits', $teData);							
					}
				}
				
				$id = $suppliers_id;
				$savemsg = 'update-success';
				
			}
			
			if($company !='')
				$supplier_name .= "$company, ";
			$supplier_name .= $first_name.' '.$last_name;
			if($email !='')
				$supplier_name .= " ($email)";
		}			
	
		$supplierOpt = array();
		if($frompage=='addpo' || $frompage=='Products'){
			$supplierssql = "SELECT company, email, suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND company !='' AND (suppliers_publish = 1 OR (suppliers_id = :suppliers_id AND suppliers_publish = 0)) ORDER BY company ASC, email ASC";
			$suppliersquery = $this->db->query($supplierssql, array('suppliers_id'=>$suppliers_id));
			if($suppliersquery){
				while($onerow = $suppliersquery->fetch(PDO::FETCH_OBJ)){
					$opsuppliers_id = $onerow->suppliers_id;
					$optLabel = stripslashes($onerow->company);
					
					if($onerow->email !='')
						$optLabel .= " ($onerow->email)";	
					$supplierOpt[$opsuppliers_id] = $optLabel;
				}
			}
		}		
		
		$array = array( 'login'=>'',
						'savemsg'=>$savemsg,
						'suppliers_id'=>$suppliers_id,
						'supplier_name'=>$supplier_name,
						'supplierOpt'=>$supplierOpt);
		return json_encode($array);
	}
	
	public function view(){}
	
	public function AJ_view_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$suppliers_id = intval($POST['suppliers_id']??0);

		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['allowed'] = $_SESSION["allowed"];
			
		$suppliersObj = $this->db->query("SELECT * FROM suppliers WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man", array('suppliers_id'=>$suppliers_id),1);
		if($suppliersObj){
			$list = false;
			$suppliersarray = $suppliersObj->fetch(PDO::FETCH_OBJ);
			$list_filters = $_SESSION["list_filters"]??array();
			$shistory_type = $list_filters['shistory_type']??'';
		
			$suppliers_id = $suppliersarray->suppliers_id;
			$secondary_phone = $suppliersarray->secondary_phone;
			
			$shipping_address = '';
			$shipping_address_one = $suppliersarray->shipping_address_one;
			if($shipping_address_one !=''){
				$shipping_address .= $shipping_address_one;
			}
			$shipping_address_two = $suppliersarray->shipping_address_two;
			if($shipping_address_two !=''){
				if($shipping_address != ''){$shipping_address .= '<br />';}
				$shipping_address .= $shipping_address_two;
			}
			$shipping_city = $suppliersarray->shipping_city;
			if($shipping_city !=''){
				if($shipping_address != ''){$shipping_address .= '<br />';}
				$shipping_address .= $shipping_city;
			}
			$shipping_state = $suppliersarray->shipping_state;
			if($shipping_state !=''){
				if($shipping_address != ''){$shipping_address .= ' ';}
				$shipping_address .= $shipping_state;
			}
			$shipping_zip = $suppliersarray->shipping_zip;
			if($shipping_zip !=''){
				if($shipping_zip != ''){$shipping_address .= ' ';}
				$shipping_address .= $shipping_zip;
			}
			$shipping_country = $suppliersarray->shipping_country;
			if($shipping_country !='' || $shipping_country !='0'){
				if($shipping_address != ''){$shipping_address .= '<br />'.$shipping_country;}
			}
			
			$company = stripslashes($suppliersarray->company);
			$email = $suppliersarray->email;
			$contact_no = $suppliersarray->contact_no;
			$suppliers_publish = $suppliersarray->suppliers_publish;
			
			$jsonResponse['company'] = $company;
			$jsonResponse['email'] = $email;
			$jsonResponse['contact_no'] = $contact_no;
			$jsonResponse['shipping_address'] = $shipping_address;
			$jsonResponse['suppliers_publish'] = intval($suppliers_publish);
			$jsonResponse['suppliers_id'] = $suppliers_id;
			$jsonResponse['suppliers_publish'] = $suppliers_publish;
			
		}

		return json_encode($jsonResponse);
	}
    
	private function filterHAndOptionsSupplier(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$suppliers_id = $this->suppliers_id;
		$shistory_type = $this->history_type;
		
		$bindData = array();
		$bindData['suppliers_id'] = $suppliers_id;
		$totalRows = 0;
		$actFeedTitleArray = array();
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Supplier Created')==0){
				$filterSql = "SELECT COUNT(suppliers_id) AS totalrows FROM suppliers 
						WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man";
			}
			elseif(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT COUNT(po_id) AS totalrows FROM po 
						WHERE accounts_id = $accounts_id AND supplier_id = :suppliers_id AND transfer = 0";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Suppliers/view/', :suppliers_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Suppliers/view/', :suppliers_id)  
					UNION ALL SELECT COUNT(suppliers_id) AS totalrows FROM suppliers 
						WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man 
					UNION ALL SELECT COUNT(po_id) AS totalrows FROM po 
						WHERE accounts_id = $accounts_id AND supplier_id = :suppliers_id AND transfer = 0 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id 
					UNION ALL SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
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
			WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Suppliers/view/', :suppliers_id)  
		UNION ALL SELECT 'Supplier Created' AS afTitle FROM suppliers 
			WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man 
		UNION ALL SELECT 'Purchase Order Created' AS afTitle FROM po 
			WHERE accounts_id = $accounts_id AND supplier_id = :suppliers_id AND transfer = 0 
		UNION ALL 
			SELECT 'Track Edits' AS afTitle FROM track_edits 
			WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id 
		UNION ALL SELECT 'Notes Created' AS afTitle FROM notes 
			WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
		$tableObj = $this->db->query($Sql, array('suppliers_id'=>$suppliers_id));
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
	
   private function loadHTableRowsSuppliers(){
        
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$suppliers_id = $this->suppliers_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$bindData = array();
		$bindData['suppliers_id'] = $suppliers_id;
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Supplier Created')==0){
				$filterSql = "SELECT 'suppliers' as tablename, created_on as tabledate, suppliers_id as table_id, 'Supplier Created' as activity_feed_title FROM suppliers 
							WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man";
			}
			elseif(strcmp($shistory_type, 'Purchase Order Created')==0){
				$filterSql = "SELECT 'po' as tablename, po_datetime as tabledate, po_id as table_id, 'Purchase Order Created' as activity_feed_title FROM po 
							WHERE accounts_id = $accounts_id and supplier_id = :suppliers_id AND transfer = 0";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Suppliers/view/', :suppliers_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' as tablename, created_on as tabledate, activity_feed_id as table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Suppliers/view/', :suppliers_id)  
						UNION ALL SELECT 'suppliers' as tablename, created_on as tabledate, suppliers_id as table_id, 'Supplier Created' as activity_feed_title FROM suppliers 
							WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man 
						UNION ALL SELECT 'po' as tablename, po_datetime as tabledate, po_id as table_id, 'Purchase Order Created' as activity_feed_title FROM po 
							WHERE accounts_id = $accounts_id and supplier_id = :suppliers_id AND transfer = 0 
						UNION ALL SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id 
						UNION ALL SELECT 'notes' AS tablename, created_on as tabledate,  notes_id as table_id, 'Notes Created' as activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id 
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
			foreach($query as $grpOneRow){
				$activity_feed_title = $grpOneRow['activity_feed_title'];
				$tablename = $grpOneRow['tablename'];
				$table_id = $grpOneRow['table_id'];
				$getHMoreInfo = $Activity_Feed->getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title);
				if(!empty($getHMoreInfo)){
					$tabledata[] = $getHMoreInfo;
				}
			}
		}
		return $tabledata;
   }
		
	public function AJgetHPageSupplier($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$suppliers_id = intval($POST['suppliers_id']??0);
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->suppliers_id = $suppliers_id;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptionsSupplier();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;	
			$jsonResponse['actFeeTitOpt'] = $this->actFeeTitOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRowsSuppliers();
		
		return json_encode($jsonResponse);
	}
	
	public function AJmergeSupplier(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = $savemsg = '';
		$id = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$suppliers_id = intval($POST['fromsuppliers_id']??0);
		$tosuppliers_id = intval($POST['tosuppliers_id']??0);
		$fromCustObj = $this->db->query("SELECT * FROM suppliers WHERE suppliers_id = :suppliers_id", array('suppliers_id'=>$suppliers_id), 1);
		if($fromCustObj){
			$fromCustRow = $fromCustObj->fetch(PDO::FETCH_OBJ);
			$toCustObj = $this->db->query("SELECT * FROM suppliers WHERE suppliers_id = :suppliers_id", array('suppliers_id'=>$tosuppliers_id), 1);
			if($toCustObj){
				$toCustRow = $toCustObj->fetch(PDO::FETCH_OBJ);
				
				$updateData = array();
				if(!empty($fromCustRow->last_name) && empty($toCustRow->last_name)){
					$updateData['last_name'] = $fromCustRow->last_name;
				}
				if(!empty($fromCustRow->email) && empty($toCustRow->email)){
					$updateData['email'] = $fromCustRow->email;
				}
				if(!empty($fromCustRow->company) && empty($toCustRow->company)){
					$updateData['company'] = $fromCustRow->company;
				}
				if(!empty($fromCustRow->contact_no)){
					if(empty($toCustRow->contact_no)){
						$updateData['contact_no'] = $fromCustRow->contact_no;
					}
					elseif(empty($toCustRow->secondary_phone)){
						$updateData['contact_no'] = $fromCustRow->contact_no;
					}
				}
				if(!empty($updateData)){
					$this->db->update('suppliers', $updateData, $tosuppliers_id);
				}
				$update = $this->db->update('suppliers', array('suppliers_publish'=>0), $suppliers_id);
				if($update){
					$id = $suppliers_id;
					$savemsg = 'Success';
					$filterSql = "SELECT activity_feed_id FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'suppliers' AND activity_feed_link LIKE CONCAT('/Suppliers/view/', :suppliers_id)";
					$tableObj = $this->db->query($filterSql, array('suppliers_id'=>$suppliers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$activity_feed_link = '/Customers/view/'.$tosuppliers_id;
							$this->db->update('activity_feed', array('activity_feed_link'=>$activity_feed_link), $oneRow->activity_feed_id);
						}
					}
					
					$filterSql = "SELECT po_id FROM po WHERE accounts_id = $accounts_id AND supplier_id = :suppliers_id";
					$tableObj = $this->db->query($filterSql, array('suppliers_id'=>$suppliers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('po', array('supplier_id'=>$tosuppliers_id), $oneRow->po_id);
						}
					}
					
					$filterSql = "SELECT track_edits_id FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'suppliers' AND record_id = :suppliers_id ";
					$tableObj = $this->db->query($filterSql, array('suppliers_id'=>$suppliers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('track_edits', array('record_id'=>$tosuppliers_id), $oneRow->track_edits_id);
						}
					}
					
					$filterSql = "SELECT notes_id FROM notes WHERE accounts_id = $accounts_id AND note_for = 'suppliers' AND table_id = :suppliers_id";
					$tableObj = $this->db->query($filterSql, array('suppliers_id'=>$suppliers_id));
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$this->db->update('notes', array('table_id'=>$tosuppliers_id), $oneRow->notes_id);
						}
					}
					
					$note_for = $this->db->checkCharLen('notes.note_for', 'suppliers');
					$noteData=array('table_id'=> $suppliers_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $_SESSION["accounts_id"],
									'user_id'=> $_SESSION["user_id"],
									'note'=> "This supplier's all information has been merged to $toCustRow->first_name $toCustRow->last_name",
									'publics'=>0);
					$notes_id = $this->db->insert('notes', $noteData);
					
				}
			}			
		}
		return json_encode(array('login'=>'', 'savemsg'=>$savemsg, 'id'=>$id));
	}
	
   public function AJ_suppliers_archive(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnmsg = '';
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$suppliers_id = intval($POST['suppliers_id']??0);
		$suppliers_name = $POST['suppliers_name']??'';
		if($suppliers_name !=''){
			if($suppliers_id>0){
				$sql = "SELECT company, email FROM suppliers WHERE accounts_id = $prod_cat_man AND suppliers_id = :suppliers_id ORDER BY suppliers_id ASC";
				$query = $this->db->query($sql, array('suppliers_id'=>$suppliers_id),1);
				if($query){
					$onesuppliersrrow = $query->fetch(PDO::FETCH_OBJ);
					$autosuppliers_name = stripslashes($onesuppliersrrow->company);

					if($onesuppliersrrow->email !='')
						$autosuppliers_name .= " ($onesuppliersrrow->email)";

					if($suppliers_name !="$autosuppliers_name"){
						$customers_id = 0;
					}
				}
			}

			if($suppliers_id==0 && $suppliers_name != ''){

				$autosuppliers_name = $suppliers_name;
				$email = '';
				if(strpos($suppliers_name, ' (') !== false) {
					$scustomerexplode = explode(' (', $suppliers_name);
					if(count($scustomerexplode)>1){
						$autosuppliers_name = trim((string) $scustomerexplode[0]);
						$email = str_replace(')', '', $scustomerexplode[1]);
					}
				}

				$strextra = " AND company LIKE CONCAT('%', :autosuppliers_name, '%')";
				$bindData['autosuppliers_name'] = $autosuppliers_name;
				if($email !=''){
					$strextra .= " AND TRIM(CONCAT_WS(' ', email, contact_no)) = :email";
					$bindData['email'] = $email;
				}
				$strextra .= " GROUP BY company, email";
				$sqlquery = "SELECT suppliers_id FROM suppliers WHERE accounts_id = $prod_cat_man AND suppliers_publish = 1 $strextra LIMIT 0, 1";
				$query = $this->db->querypagination($sqlquery, $bindData);
				if($query){
					foreach($query as $onegrouprow){
						$suppliers_id = $onegrouprow['suppliers_id'];
					}
				}
			}
		}

		if($suppliers_id>0){

			$sql = "SELECT company, email FROM suppliers WHERE suppliers_id = :suppliers_id AND accounts_id = $prod_cat_man AND suppliers_publish = 1 ORDER BY suppliers_id ASC LIMIT 0,1";
			$query = $this->db->querypagination($sql, array('suppliers_id'=>$suppliers_id),1);
			if($query){
				foreach($query as $onerow){
					$autosuppliers_name = stripslashes($onerow['company']);

					if($onerow['email'] !='')
						$autosuppliers_name .= " ($onerow[email])";

					$updatetable = $this->db->update('suppliers', array('suppliers_publish'=>0), $suppliers_id);
					if($updatetable){
						$activity_feed_title = "Supplier archived";
						$activity_feed_title = $this->db->checkCharLen('activity_feed.activity_feed_title', $activity_feed_title);
						$activity_feed_link = "/Suppliers/view/$suppliers_id";
						$activity_feed_link = $this->db->checkCharLen('activity_feed.activity_feed_link', $activity_feed_link);
						
						$afData = array('created_on' => date('Y-m-d H:i:s'),
										'last_updated' => date('Y-m-d H:i:s'),
										'accounts_id' => $_SESSION["accounts_id"],
										'user_id' => $_SESSION["user_id"],
										'activity_feed_title' => $activity_feed_title,
										'activity_feed_name' => $autosuppliers_name,
										'activity_feed_link' => $activity_feed_link,
										'uri_table_name' => "suppliers",
										'uri_table_field_name' =>"suppliers_publish",
										'field_value' => 0);
						$this->db->insert('activity_feed', $afData);
						
						$returnmsg = 'archive-success';
					}
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnmsg));
   }
	
}
?>