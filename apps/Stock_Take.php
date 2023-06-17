<?php
class Stock_Take{
	protected $db;
	private int $page, $totalRows, $category_id, $stock_take_id;
	private string $sorting_type, $view_type, $view2_type, $keyword_search, $history_type;
	private array $catOpt;
	
	public function __construct($db){$this->db = $db;}
		
	public function lists(){}
	
	private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$ssorting_type = $this->sorting_type;
		$sview_type = $this->view_type;
		$scategory_id = $this->category_id;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Stock_Take";
		$_SESSION["list_filters"] = array('ssorting_type'=>$ssorting_type, 'sview_type'=>$sview_type, 'scategory_id'=>$scategory_id, 'keyword_search'=>$keyword_search);
				
		$bindData = array();
		$filterSql = "";
		$bindData = array();
		if(!empty($sview_type)){
			$filterSql .= " AND ST.status = :sview_type";
			$bindData['sview_type'] = $sview_type;
		}
		
		if($scategory_id>0){
			$filterSql .= " AND ST.category_id = :scategory_id";
			$bindData['scategory_id'] = $scategory_id;
		}
			
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, ST.reference)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}

		$totalRows = 0;
		$queryObj = $this->db->query("SELECT COUNT(ST.stock_take_id) AS totalrows FROM stock_take ST LEFT JOIN manufacturer ON (ST.manufacturer_id = manufacturer.manufacturer_id) WHERE ST.accounts_id = $accounts_id $filterSql", $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}

		$catOpts = array();
		$Sql = "SELECT ST.category_id FROM stock_take ST LEFT JOIN manufacturer ON (ST.manufacturer_id = manufacturer.manufacturer_id) WHERE ST.accounts_id = $accounts_id $filterSql GROUP BY ST.category_id";
		$sqlObj = $this->db->query($Sql, $bindData);
		if($sqlObj){
			while($oneRow = $sqlObj->fetch(PDO::FETCH_OBJ)){
				$catOpts[$oneRow->category_id] = '';
			}
		}
		
		$catOpt = array();
		if(!empty($catOpts)){
			$categoryObj = $this->db->query("SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($catOpts)).") ORDER BY category_name ASC", array());
			if($categoryObj){
				while($oneRow = $categoryObj->fetch(PDO::FETCH_OBJ)){
					$optval = $oneRow->category_id;
					$optlabel = trim((string) stripslashes($oneRow->category_name));
					$catOpt[$optval] = $optlabel;
				}
			}
		}
		
		$this->totalRows = $totalRows;
		$this->catOpt = $catOpt;
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
		$scategory_id = $this->category_id;
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'ST.created_on DESC', 
								1=>'ST.created_on ASC', 
								2=>'ST.date_completed DESC', 
								3=>'ST.date_completed ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}

		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$filterSql = "";
		$bindData = array();
		if(!empty($sview_type)){
			$filterSql .= " AND ST.status = :sview_type";
			$bindData['sview_type'] = $sview_type;
		}
		
		if($scategory_id>0){
			$filterSql .= " AND ST.category_id = :scategory_id";
			$bindData['scategory_id'] = $scategory_id;
		}
			
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', ST.manufacture, ST.reference)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT ST.*, manufacturer.name AS manufacture FROM stock_take ST LEFT JOIN manufacturer ON (ST.manufacturer_id = manufacturer.manufacturer_id) WHERE ST.accounts_id = $accounts_id $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		
		$query = $this->db->querypagination($sqlquery, $bindData);			
		$tabledata = array();
		if($query){
			$catIds = array();
			foreach($query as $oneRow){
				$catIds[$oneRow['category_id']] = '';
			}

			if(!empty($catIds)){
				$categoryObj = $this->db->query("SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($catIds)).")", array());
				if($categoryObj){
					while($categoryrow = $categoryObj->fetch(PDO::FETCH_OBJ)){
						$catIds[$categoryrow->category_id] = trim((string) stripslashes("$categoryrow->category_name"));
					}
				}
			}

			foreach($query as $rowstock_take){
				$stock_take_id = $rowstock_take['stock_take_id'];
				$categoryname = $catIds[$rowstock_take['category_id']]??'&nbsp;';
				$reference = stripslashes(trim((string) $rowstock_take['reference']));
				$manufacture = stripslashes(trim((string) $rowstock_take['manufacture']));
				$status = $rowstock_take['status'];
				
				$tabledata[] = array($stock_take_id, substr($rowstock_take['created_on'], 0, 10), $reference, $manufacture, $categoryname, $rowstock_take['date_completed'], $status);
			}
		}
		
		return $tabledata;
    }
	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$ssorting_type = intval($POST['ssorting_type']??0);
		$sview_type = $POST['sview_type']??'Open';
		$scategory_id = intval($POST['scategory_id']??0);
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->sorting_type = $ssorting_type;
		$this->view_type = $sview_type;
		$this->category_id = $scategory_id;
		$this->keyword_search = $keyword_search;
		
		$jsonResstock_takense = array();
		$jsonResstock_takense['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResstock_takense['totalRows'] = $totalRows = $this->totalRows;
			$jsonResstock_takense['catOpt'] = $this->catOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResstock_takense['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResstock_takense);
	}
		
	public function add(){}
	
	public function AJ_add_MoreInfo(){
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$category_id = $_SESSION["category_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$catOpt = array();
		if($category_id>0){
			$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $category_id", array());
			if($categoryObj){
				$categoryrow = $categoryObj->fetch(PDO::FETCH_OBJ);
				$category_name = trim((string) stripslashes("$categoryrow->category_name"));
				$catOpt[$category_id] = $category_name;
			}
		}
		if(empty($catOpt)){
			$categoryql = "SELECT category_id, category_name FROM category WHERE accounts_id = $prod_cat_man AND category_publish = 1 ORDER BY category_name ASC";
			$categoryObj = $this->db->query($categoryql, array());
			if($categoryObj){
				while($onerow = $categoryObj->fetch(PDO::FETCH_OBJ)){
					$category_name = stripslashes(trim((string) $onerow->category_name));
					$catOpt[$onerow->category_id] = $category_name;
				}
			}
		}
		$jsonResponse['category_id'] = $category_id;
		$jsonResponse['catOpt'] = $catOpt;
		
		$manOpt = array();
		$manSql = "SELECT manufacturer_id, name FROM manufacturer WHERE accounts_id = $prod_cat_man AND name !='' AND manufacturer_publish = 1 ORDER BY name ASC";
		$manObj = $this->db->query($manSql, array());
		if($manObj){
			while($onerow = $manObj->fetch(PDO::FETCH_OBJ)){
				$name = stripslashes(trim((string) $onerow->name));
				$manOpt[$onerow->manufacturer_id] = $name;
			}
		}
		$jsonResponse['manOpt'] = $manOpt;

		return json_encode($jsonResponse);
	}
		
	public function edit(){}
	
	public function AJ_edit_MoreInfo(){
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$POST = json_decode(file_get_contents('php://input'), true);
		$stock_take_id = intval($POST['stock_take_id']??0);

		$accounts_id = $_SESSION['accounts_id']??0;

		$sql = "SELECT ST.*, manufacturer.name AS manufacture FROM stock_take ST LEFT JOIN manufacturer ON (ST.manufacturer_id = manufacturer.manufacturer_id) WHERE ST.stock_take_id = :stock_take_id AND ST.accounts_id = $accounts_id";
		$stock_takeObj = $this->db->query($sql, array('stock_take_id'=>$stock_take_id));
		if($stock_takeObj){
			$stock_take_onerow = $stock_takeObj->fetch(PDO::FETCH_OBJ);
			$stock_take_id = $stock_take_onerow->stock_take_id;
			$category_id = $stock_take_onerow->category_id;
			$status = $stock_take_onerow->status;
			
			$categoryname = '';
			$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $category_id", array());
			if($categoryObj){
				$category_row = $categoryObj->fetch(PDO::FETCH_OBJ);
				$categoryname = stripslashes(trim((string) $category_row->category_name));
			}
			$manufacture = stripslashes(trim((string) $stock_take_onerow->manufacture));
			
			$jsonResponse['stock_take_id'] = $stock_take_id;
			$jsonResponse['category_id'] = $category_id;
			$jsonResponse['status'] = $status;
			$jsonResponse['categoryname'] = $categoryname;
			$jsonResponse['manufacture'] = $manufacture;
			$jsonResponse['date_completed'] = $stock_take_onerow->date_completed;
			$jsonResponse['reference'] = $stock_take_onerow->reference;
		}
		else{
			$jsonResponse['login'] = 'Stock_Take/lists/';
		}

		return json_encode($jsonResponse);
	}
	
	private function filterHAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$stock_take_id = $this->stock_take_id;
		$sview2_type = $this->view2_type;
		$keyword_search = $this->keyword_search;
		$_SESSION["current_module"] = "Stock_Take";
		$_SESSION["list_filters"] = array('sview2_type'=>$sview2_type, 'keyword_search'=>$keyword_search);
		
		$totalRows = 0;
		$filterSql = "";
		$bindData = array('stock_take_id'=>$stock_take_id);
		if(!empty($keyword_search)){
			$catIds = array();
			$tableObj =  $this->db->query("SELECT category_id FROM category WHERE category_name LIKE CONCAT('%', :keyword_search, '%')", array('keyword_search'=>$keyword_search));
			if($tableObj){
				while($sOneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$catIds[] = $sOneRow->category_id;
				}
			}
			if(!empty($catIds)){
				$filterSql .= " AND p.category_id IN (".implode(', ', $catIds).")";
			}
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		if($sview2_type !='All'){
			if($sview2_type==1){
				$filterSql .= " AND sti.inventory_count !=-1";
			}
			else{
				$filterSql .= " AND sti.inventory_count !=-1 AND sti.inventory_current != sti.inventory_count";
			}
		}
		$sql = "SELECT COUNT(sti.stock_take_items_id) AS totalRows FROM stock_take_items sti, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE sti.stock_take_id = :stock_take_id $filterSql AND sti.product_id = p.product_id";
		$STIObj = $this->db->query($sql, $bindData);
		if($STIObj){
			$totalRows += $STIObj->fetch(PDO::FETCH_OBJ)->totalRows;
		}
		
		$this->totalRows = $totalRows;		
	}
	
    private function loadHTableRows(){
        $limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$stock_take_id = $this->stock_take_id;
		$sview2_type = $this->view2_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		
		$status = 'Open';
		$stock_takeObj = $this->db->query("SELECT status FROM stock_take WHERE stock_take_id = $stock_take_id AND accounts_id = $accounts_id", array());
		if($stock_takeObj){
			$status = $stock_takeObj->fetch(PDO::FETCH_OBJ)->status;
		}
		
		$filterSql = "";
		$bindData = array('stock_take_id'=>$stock_take_id);
		if(!empty($keyword_search)){
			$catIds = array();
			$tableObj =  $this->db->query("SELECT category_id FROM category WHERE accounts_id = $prod_cat_man AND category_name LIKE CONCAT('%', :keyword_search, '%')", array('keyword_search'=>$keyword_search));
			if($tableObj){
				while($sOneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$catIds[] = $sOneRow->category_id;
				}
			}
			if(!empty($catIds)){
				$filterSql .= " AND p.category_id IN (".implode(', ', $catIds).")";
			}
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		if($sview2_type !='All'){
			if($sview2_type==1){
				$filterSql .= " AND sti.inventory_count !=-1";
			}
			else{
				$filterSql .= " AND sti.inventory_count !=-1 AND sti.inventory_current != sti.inventory_count";
			}
		}
		
		$sqlquery = "SELECT sti.*, p.category_id, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name, p.sku, p.product_type FROM stock_take_items sti, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE sti.stock_take_id = :stock_take_id $filterSql AND sti.product_id = p.product_id ORDER BY manufacturer.name ASC, p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC LIMIT $starting_val, $limit";
		$STIData = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($STIData){
			$i=0;
			$categoryIds = array(0=>'');
			foreach($STIData as $row){
				$categoryIds[$row['category_id']] = '';
			}
			if(!empty($categoryIds)){
				$tableObj =  $this->db->query("SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($categoryIds)).")", array());
				if($tableObj){
					while($sOneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$categoryIds[$sOneRow->category_id] = stripslashes($sOneRow->category_name);
					}
				}
			}
			
			foreach($STIData as $row){
				$i++;
				$stock_take_items_id = $row['stock_take_items_id'];
				
				$product_type = $row['product_type'];
				$categoryId = $row['category_id'];
				$categoryName = $categoryIds[$categoryId]??'';
				$manufacture = stripslashes((string) $row['manufacture']);
				$productName = stripslashes((string) $row['product_name']);
				if(!empty($row['colour_name'])){$productName .= ' '.$row['colour_name'];}
				if(!empty($row['storage'])){$productName .= ' '.$row['storage'];}
				if(!empty($row['physical_condition_name'])){$productName .= ' '.$row['physical_condition_name'];}
				
				$sku = $row['sku'];
				
				$product_id = $row['product_id'];
				$inventory_current = $row['inventory_current'];
				$inventory_count = $row['inventory_count'];
				if($inventory_count==-1){$inventory_count = '';}
				else{$inventory_count = floatval($inventory_count);}
				$note = stripslashes(trim((string) $row['note']));
				
				$tabledata[] = array(intval($stock_take_items_id), $manufacture, $categoryName, $productName, $sku, floatval($inventory_current), $product_type, $inventory_count, $note);
			}
		}
		return  $tabledata;
    }
	
	public function AJgetHPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$sstock_take_id = intval($POST['sstock_take_id']??0);		
		$sview2_type = $POST['sview2_type']??'All';		
		$keyword_search = $POST['keyword_search']??'';		
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->stock_take_id = $sstock_take_id;
		$this->view2_type = $sview2_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResstock_takense = array();
		$jsonResstock_takense['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptions();
			$jsonResstock_takense['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResstock_takense['tableRows'] = $this->loadHTableRows();
		
		return json_encode($jsonResstock_takense);
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

		$stock_take_id = intval($segment5name);
		
		$htmlStr = "";
		
		$stock_takeObj = $this->db->query("SELECT stock_take_id FROM stock_take WHERE stock_take_id = :stock_take_id AND accounts_id = $accounts_id", array('stock_take_id'=>$stock_take_id),1);
		if($stock_takeObj){
			$stock_take_id = $stock_takeObj->fetch(PDO::FETCH_OBJ)->stock_take_id;
			$htmlStr = '<!DOCTYPE html>
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
		return $htmlStr;
	}
	
	public function AJ_prints_large_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$stock_take_id = intval($POST['stock_take_id']);
		$sview2_type = $POST['sview2_type'];
		$keyword_search = $POST['keyword_search'];
		
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->stockTakeInfo($stock_take_id, $sview2_type, $keyword_search);
		
		return json_encode($jsonResponse);
	}
	
	public function AJsend_STEmail(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$stock_take_id = intval($POST['stock_take_id']??0);
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
			$mail_body = $Printing->stock_takeInvoicesInfo($stock_take_id);
			
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
				$note_for = $this->db->checkCharLen('notes.note_for', 'stock_take');
				$noteData = array();
				$noteData['table_id'] = $stock_take_id;
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
	
	public function AJ_save_stock_take(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = 0;
		$savemsg = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$Common = new Common($this->db);
		$stock_takeData = array();
		$stock_takeData['created_on'] = date('Y-m-d H:i:s');
		$status = $this->db->checkCharLen('stock_take.status', 'Open');
		$stock_takeData['status'] = $status;
		
		$category_id = intval($POST['category_id']??0);		
		$stock_takeData['category_id'] = intval($category_id);		
		$manufacturer_id = intval($POST['manufacturer_id']??0);				
		$stock_takeData['manufacture'] = '';
		$stock_takeData['manufacturer_id'] = $manufacturer_id;		
		$stock_takeData['reference'] = $this->db->checkCharLen('stock_take.reference', trim((string) $POST['reference']??''));
		$stock_takeData['accounts_id'] = $accounts_id;
		$stock_takeData['user_id'] = $user_id;
		$stock_takeData['date_completed'] = '1000-01-01';
		
		$stock_take_id = $this->db->insert('stock_take', $stock_takeData);           
		if($stock_take_id){
			
			$extrastr = "";
			if($manufacturer_id >0){
				$extrastr .= " AND p.manufacturer_id = $manufacturer_id";
			}
			if($category_id >0){
				$extrastr .= " AND p.category_id = $category_id";
			}
			
			$stock_take_itemsData = array();
			$stock_take_itemsData['stock_take_id'] = $stock_take_id;
			$stock_take_itemsData['inventory_count'] = -1;
			$stock_take_itemsData['note'] = '';
			
			$sql = "SELECT p.product_id, p.product_type, i.current_inventory, i.regular_price, i.ave_cost FROM inventory i, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE i.accounts_id = $accounts_id $extrastr AND p.manage_inventory_count = 1 AND p.product_publish = 1 AND i.product_id = p.product_id ORDER BY TRIM(CONCAT_WS(' ', manufacturer.name, p.product_name)) ASC";
			$queryObj = $this->db->query($sql, array());
			if($queryObj){
				while($onerow = $queryObj->fetch(PDO::FETCH_OBJ)){
					$stock_take_itemsData['product_id'] = $product_id = $onerow->product_id;
					$inventory_current = $onerow->current_inventory;
					$regular_price = $onerow->regular_price;
					$ave_cost = $onerow->ave_cost;
					$current_imeis = $counted_imeis = '';
					if($onerow->product_type=='Live Stocks'){
						$inventory_current = $ave_cost = 0;
						$itemSql = "SELECT item_number FROM item WHERE product_id = $product_id AND accounts_id = $accounts_id AND in_inventory = 1";
						$itemQuery = $this->db->query($itemSql, array());
						if($itemQuery){
							$itemNumbers = array();
							while($itemOneRow = $itemQuery->fetch(PDO::FETCH_OBJ)){
								$itemNumbers[$itemOneRow->item_number] = '';
							}
							$inventory_current = count($itemNumbers);
							$current_imeis = implode('|', array_keys($itemNumbers));
							
						}
						if(!empty($current_imeis)){							
							$mobileProdAveCost = $Common->mobileProdAveCost($accounts_id, $product_id, ' AND in_inventory=1');
							$ave_cost = $mobileProdAveCost[0];
						}
					}
					
					$stock_take_itemsData['inventory_current'] = $inventory_current;
					$stock_take_itemsData['regular_price'] = $regular_price;
					$stock_take_itemsData['ave_cost'] = $ave_cost;
					$stock_take_itemsData['current_imeis'] = $current_imeis;
					$stock_take_itemsData['counted_imeis'] = $counted_imeis;
					$this->db->insert('stock_take_items', $stock_take_itemsData);
				}
			}
			
			$id = $stock_take_id;
			$savemsg = 'add-success';
		}
		else{
			$savemsg = 'error';
		}
			
		$array = array( 'login'=>'','id'=>$id,'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
    public function saveChangeST(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnData = array();
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$stock_take_id = intval($POST['stock_take_id']??0);
		$stock_takeData = array();
		$category_id = intval($POST['category_id']??0);		
		$manufacturer_id = intval($POST['manufacturer_id']??0);
		$stock_takeData['reference'] = $this->db->checkCharLen('stock_take.reference', trim((string) $POST['reference']??''));
						
		if($stock_take_id>0){
			$oneTRowObj = $this->db->querypagination("SELECT ST.*, manufacturer.name AS manufacture FROM stock_take ST LEFT JOIN manufacturer ON (ST.manufacturer_id = manufacturer.manufacturer_id) WHERE ST.stock_take_id = $stock_take_id", array());
			$changed = array();
			
			$update = $this->db->update('stock_take', $stock_takeData, $stock_take_id);
			if($update){
				if($oneTRowObj){
					foreach($stock_takeData as $fieldName=>$fieldValue){
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
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'stock_take');
					$teData['record_id'] = $stock_take_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);							
				}
			}			
				
			$stock_take_onerow = $this->db->querypagination("SELECT ST.*, manufacturer.name AS manufacture FROM stock_take ST LEFT JOIN manufacturer ON (ST.manufacturer_id = manufacturer.manufacturer_id) WHERE ST.stock_take_id = $stock_take_id", array());
			$reference = $stock_take_onerow[0]['reference'];				
			$category_id = $stock_take_onerow[0]['category_id'];
			$categoryname = '';
			$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $category_id", array());
			if($categoryObj){
				$category_row = $categoryObj->fetch(PDO::FETCH_OBJ);
				$categoryname = stripslashes(trim((string) $category_row->category_name));
			}
			$manufacture = stripslashes(trim((string) $stock_take_onerow[0]['manufacture']));
			$returnData['reference'] = $reference;
			$returnData['manufacture'] = $manufacture;
			$returnData['categoryname'] = $categoryname;
			$returnData['date_completed'] = $stock_take_onerow[0]['date_completed'];
		}
		
		return json_encode(array('login'=>'', 'returnData'=>$returnData));
	}
	
	public function showSTData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$stock_take_id = intval($POST['stock_take_id']??0);
		$stock_takeData = array();
		$stock_takeData['login'] = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$stock_takeObj = $this->db->query("SELECT * FROM stock_take WHERE stock_take_id = :stock_take_id AND accounts_id = $accounts_id", array('stock_take_id'=>$stock_take_id),1);
		if($stock_takeObj){
			$stock_takeRow = $stock_takeObj->fetch(PDO::FETCH_OBJ);
			
			$stock_takeData['stock_take_id'] = trim((string) $stock_takeRow->stock_take_id);
			$stock_takeData['status'] = trim((string) $stock_takeRow->status);
			$stock_takeData['manufacturer_id'] = $manufacturer_id = trim((string) $stock_takeRow->manufacturer_id);
			$manOpt = array();
			if($prod_cat_man>0){
				$sqlmanufacturer = "SELECT manufacturer_id, name FROM manufacturer WHERE accounts_id = $prod_cat_man AND (manufacturer_publish = 1 OR (manufacturer_id = :manufacturer_id AND manufacturer_publish = 0)) ORDER BY name ASC";
				$manufacturerquery = $this->db->query($sqlmanufacturer, array('manufacturer_id'=>$manufacturer_id));
				if($manufacturerquery){
					while($onemanufacturerrow = $manufacturerquery->fetch(PDO::FETCH_OBJ)){
						$omanufacturer_name = stripslashes(trim((string) $onemanufacturerrow->name));
						if($omanufacturer_name !=''){
							$manOpt[$onemanufacturerrow->manufacturer_id] = $omanufacturer_name;
						}
					}
				}
			}
			$stock_takeData['manOpt'] = $manOpt;
			
			$stock_takeData['category_id'] = $category_id = trim((string) $stock_takeRow->category_id);
			$catOpt = array();
			if($prod_cat_man>0){
				$sqlcategory = "SELECT category_id, category_name FROM category WHERE accounts_id = $prod_cat_man AND (category_publish = 1 OR (category_id = $category_id AND category_publish = 0)) ORDER BY category_name ASC";
				$categoryquery = $this->db->query($sqlcategory, array());
				if($categoryquery){
					while($onecategoryrow = $categoryquery->fetch(PDO::FETCH_OBJ)){
						$ocategory_id = $onecategoryrow->category_id;
						$ocategory_name = stripslashes(trim((string) $onecategoryrow->category_name));
						$catOpt[$ocategory_id] = $ocategory_name;
					}
				}
			}
			$stock_takeData['catOpt'] = $catOpt;
			
			$stock_takeData['reference'] = trim((string) $stock_takeRow->reference);
		}
		return json_encode($stock_takeData);
	}

	public function AJupdateSTIIC(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 'error';
		$stock_take_items_id = intval($POST['stock_take_items_id']??0);
		$idPre = $POST['idPre']??'invCnt';
		$fieldName = 'inventory_count';
		if($idPre=='invCnt'){
			$fieldVal = intval($POST['fieldVal']??0);
		}
		else{
			$fieldName = 'note';
			$fieldVal = $POST['fieldVal']??'';
		}				
		if($stock_take_items_id>0){
			$update = $this->db->update('stock_take_items', array($fieldName=>$fieldVal), $stock_take_items_id);
			if($update){
				$returnStr = 'Updated';
			}
			else{
				$returnStr = 'Done';				
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJupdateSTIICBySKU(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = 'error';
		$itemType = 'Standard';
		$id = 0;
		$stock_take_id = intval($POST['stock_take_id']??0);
		$SKU_Barcode = $POST['SKU_Barcode']??'';
		$message = "";
		$stock_take_items_id = $inventory_count = 0;
		$sqlquery = "SELECT sti.*, manufacturer.name AS manufacture, p.sku FROM stock_take_items sti, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE sti.stock_take_id = :stock_take_id AND sti.current_imeis LIKE CONCAT('%', :item_number, '%') AND p.product_type ='Live Stocks' AND sti.product_id = p.product_id ORDER BY manufacture ASC, p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC LIMIT 0, 1";
		$STIData = $this->db->querypagination($sqlquery, array('stock_take_id'=>$stock_take_id, 'item_number'=>$SKU_Barcode));
		if($STIData){
			$itemType = 'Live Stocks';
			foreach($STIData as $row){
				$currentImeis = explode('|',$row['current_imeis']);
				$countedImeis = array();
				if(!empty($row['counted_imeis'])){
					$countedImeis = explode('|',$row['counted_imeis']);
				}
				if(in_array($SKU_Barcode, $currentImeis)){
					$stock_take_items_id = $row['stock_take_items_id'];
					$inventory_current = $row['inventory_current'];
					if(!in_array($SKU_Barcode, $countedImeis)){
						
						$countedImeis[] = $SKU_Barcode;
						$inventory_count = count($countedImeis);
						$counted_imeis = implode('|', $countedImeis);
						$update = $this->db->update('stock_take_items', array('inventory_count'=>$inventory_count, 'counted_imeis'=>$counted_imeis), $stock_take_items_id);
						if($update){
							
							$action = 'Added';
						}
						$message = array('status'=>'added','sku'=>$row['sku'], 'SKU_Barcode'=>$SKU_Barcode, 'inventory_current'=>$inventory_current, 'inventory_count'=>$inventory_count);
					}
					else{
						$action = 'Duplicate';
						$inventory_count = count($countedImeis);
						$message = array('status'=>'duplicate','sku'=>$row['sku'], 'SKU_Barcode'=>$SKU_Barcode, 'inventory_current'=>$inventory_current, 'inventory_count'=>$inventory_count);
					}
				}
			}
		}
		
		if($action == 'error'){
			$sqlquery = "SELECT sti.*, manufacturer.name AS manufacture, p.sku FROM stock_take_items sti, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE sti.stock_take_id = :stock_take_id AND p.sku = :sku AND p.product_type !='Live Stocks' AND sti.product_id = p.product_id ORDER BY manufacture ASC, p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC LIMIT 0, 1";
			$STIData = $this->db->querypagination($sqlquery, array('stock_take_id'=>$stock_take_id, 'sku'=>$SKU_Barcode));
			if($STIData){
				foreach($STIData as $row){
					$stock_take_items_id = $row['stock_take_items_id'];
					$inventory_current = $row['inventory_current'];
					$inventory_count = $row['inventory_count'];
					if($inventory_count==-1){$inventory_count = 0;}
					$inventory_count++;
					$message = array('status'=>'error', 'SKU_Barcode'=>$SKU_Barcode, 'inventory_current'=>$inventory_current, 'inventory_count'=>$inventory_count );
					$update = $this->db->update('stock_take_items', array('inventory_count'=>$inventory_count), $stock_take_items_id);
					if($update){
						$action = 'Added';
					}
				}
			}
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'id'=>$stock_take_items_id, 'message'=>$message, 'fieldVal'=>$inventory_count, 'itemType'=>$itemType));
	}
	
    public function confirmCompleteST(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$stock_take_id = intval($POST['stock_take_id']??0);
		$status = $this->db->checkCharLen('stock_take.status', 'Closed');
		$action = '';
		$updatestock_takes = $this->db->update('stock_take', array('status'=>$status, 'date_completed'=>date('Y-m-d H:i:s')), $stock_take_id);
		if($updatestock_takes){
			$sqlquery = "SELECT sti.*, manufacturer.name AS manufacture, p.product_type, p.sku FROM stock_take_items sti, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE sti.stock_take_id = :stock_take_id AND sti.inventory_count >=0 AND sti.product_id = p.product_id ORDER BY manufacture ASC, p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC";
			$STIData = $this->db->querypagination($sqlquery, array('stock_take_id'=>$stock_take_id));
			if($STIData){				
				foreach($STIData as $row){
					$product_id = $row['product_id'];
					$sku = $row['sku'];
					$itemType = $row['product_type'];
					if($itemType == 'Live Stocks'){
						$currentImeis = explode('|',$row['current_imeis']);
						$countedImeis = array();
						if(!empty($row['counted_imeis'])){
							$countedImeis = explode('|',$row['counted_imeis']);
						}
						if(!empty($currentImeis)){
							foreach($currentImeis as $oneIMEI){
								if(!in_array($oneIMEI, $countedImeis)){
									$item_id = 0;
									$itemObj = $this->db->query("SELECT item_id FROM item WHERE accounts_id = $accounts_id AND product_id = $product_id AND item_number = '$oneIMEI'", array(), 1);
									if($itemObj){
										$item_id = $itemObj->fetch(PDO::FETCH_OBJ)->item_id;
									}
									if($item_id>0){
										$updateitem = $this->db->update('item', array('in_inventory'=>0, 'last_updated'=>date('Y-m-d H:i:s')), $item_id);
										if($updateitem){
											$notes = $this->db->translate('REMOVED FROM INVENTORY');
											$notes .= '<br />Stock Take counted adjustment';
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
									}									
								}
							}
						}
					}
					else{
						$existing_inventory = $row['inventory_current'];
						$total_inventory = $row['inventory_count'];
						$new_inventory = $total_inventory-$existing_inventory;
						if($new_inventory != 0){
						
							$inventory_id = $prevInventory = 0;
							$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE accounts_id = $accounts_id AND product_id = $product_id", array(),1);
							if($inventoryObj){
								$invRow = $inventoryObj->fetch(PDO::FETCH_OBJ);
								$inventory_id =  $invRow->inventory_id;
								$prevInventory =  $invRow->current_inventory;
							}
						
							if($inventory_id>0){
								$total_inventory = $new_inventory+$prevInventory;
								$updateproduct = $this->db->update('inventory', array('current_inventory'=>$total_inventory), $inventory_id);
								if($updateproduct){									
									$note_for = $this->db->checkCharLen('notes.note_for', 'product');
									$noteData=array('table_id'=> $product_id,
													'note_for'=> $note_for,
													'created_on'=> date('Y-m-d H:i:s'),
													'last_updated'=> date('Y-m-d H:i:s'),
													'accounts_id'=> $_SESSION["accounts_id"],
													'user_id'=> $_SESSION["user_id"],
													'note'=> "$new_inventory inventory has been adjusted $sku",
													'publics'=>0);
									$notes_id = $this->db->insert('notes', $noteData);
									
								}
							}
						}
					}
				}
			}
			
			$action = 'Completed';
		}
		return json_encode(array('login'=>'', 'action'=>$action));
	 }
	
	
	 public function updatestock_takeReOpen(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$stock_take_id = intval($POST['stock_take_id']??0);
		
		$status = $this->db->checkCharLen('stock_take.status', 'Open');
		$this->db->update('stock_take', array('status'=>$status), $stock_take_id);
		
		$stock_take_id = 0;
		$stock_takeSql = "SELECT stock_take_id FROM stock_take WHERE accounts_id = $accounts_id AND stock_take_id = $stock_take_id ORDER BY stock_take_id ASC";
		$stock_takeObj = $this->db->query($stock_takeSql, array());
		if($stock_takeObj){
			$stock_take_id = $stock_takeObj->fetch(PDO::FETCH_OBJ)->stock_take_id;
		}
		$note_for = $this->db->checkCharLen('notes.note_for', 'stock_take');		
		$noteData=array('table_id'=> $stock_take_id,
						'note_for'=> $note_for,
						'created_on'=> date('Y-m-d H:i:s'),
						'last_updated'=> date('Y-m-d H:i:s'),
						'accounts_id'=> $_SESSION["accounts_id"],
						'user_id'=> $_SESSION["user_id"],
						'note'=> $this->db->translate('PO was re-open.')." ".$this->db->translate('Purchase Order')." p$stock_take_id",
						'publics'=>0);
		$notes_id = $this->db->insert('notes', $noteData);
		
		return json_encode(array('login'=>'', 'returnStr'=>$stock_take_id));
	}
	
	public function updatestock_take_item(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$Common = new Common($this->db);
		$stock_take_items_id = intval($POST['stock_take_items_id']??0);
		$newcost = floatval($POST['cost']??0.00);
		$newordered_qty = floatval($POST['ordered_qty']??0);
		$newreceived_qty = floatval($POST['received_qty']??0);
		$newordered_qty_total = round($newcost*$newordered_qty,2);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$action = $status = '';
		$stock_take_id = 0;
		
		$stock_takeItemObj = $this->db->query("SELECT * FROM stock_take_items WHERE stock_take_items_id = :stock_take_items_id", array('stock_take_items_id'=>$stock_take_items_id),1);
		if($stock_takeItemObj){
			$stock_take_itemsrow = $stock_takeItemObj->fetch(PDO::FETCH_OBJ);
		
			$stock_take_id = $stock_take_itemsrow->stock_take_id;
			$stock_takeObj = $this->db->query("SELECT status FROM stock_take WHERE accounts_id = $accounts_id AND stock_take_id = :stock_take_id", array('stock_take_id'=>$stock_take_id),1);
			if($stock_takeObj){
				$status = $stock_takeObj->fetch(PDO::FETCH_OBJ)->status;
			}
			$item_type = $stock_take_itemsrow->item_type;
			$product_id = $stock_take_itemsrow->product_id;
			$oldCost = $stock_take_itemsrow->cost;
			$old_stock_take_ordered_qty = $stock_take_itemsrow->ordered_qty;
			$old_stock_take_received_qty = $stock_take_itemsrow->received_qty;
			
			$stock_take_itemsupdatedata = array('cost'=>$newcost,
										'ordered_qty'=>$newordered_qty,
										'received_qty'=>$newreceived_qty);	
			$updatestock_take_items = $this->db->update('stock_take_items', $stock_take_itemsupdatedata, $stock_take_items_id);
			if($updatestock_take_items){
				$changed = array();
				if($oldCost != $newcost){
					$changed['cost'] = array($oldCost, number_format($newcost,2));
					
					if($item_type=='livestocks'){
						$Common->updateMobileAvgCost($accounts_id, $stock_take_items_id, $stock_take_itemsrow->created_on);
					}
				}
				if($old_stock_take_ordered_qty != $newordered_qty){$changed['ordered_qty'] = array($old_stock_take_ordered_qty, $newordered_qty);}
				if($old_stock_take_received_qty != $newreceived_qty){$changed['received_qty'] = array($old_stock_take_received_qty, $newreceived_qty);}
				
				if(!empty($changed)){
					$product_name = '';
					if($item_type=='one_time'){
						$pcObj =  $this->db->query("SELECT description FROM stock_takes_cart WHERE stock_takes_cart_id = $product_id", array());
						if($pcObj){
							$product_name = $pcObj->fetch(PDO::FETCH_OBJ)->description;
						}
					}
					else{
						$productObj =  $this->db->query("SELECT p.product_type, p.manage_inventory_count, p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.product_id = $product_id AND p.accounts_id = $prod_cat_man", array());
						if($productObj){
							$product_row = $productObj->fetch(PDO::FETCH_OBJ);

							$product_name = stripslashes($product_row->product_name);
							$manufacture_name = $product_row->manufacture;
							if($manufacture_name !=''){$product_name = stripslashes(trim((string) $manufacture_name.' '.$product_name));}

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
					
					$moreInfo = array('table'=>'stock_take_items', 'id'=>$stock_take_items_id, 'product_id'=>$product_id, 'description'=>$product_name);
					$teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'stock_take');
					$teData['record_id'] = $stock_take_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);
				}
				
				if($item_type=='one_time'){
					$this->db->update('stock_takes_cart', array('ave_cost'=>$newcost), $product_id);
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
								
								$current_inventory = $inventoryrow->current_inventory-$old_stock_take_received_qty+$newreceived_qty;
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
			$cartsData = $this->loadSTCartData($stock_take_id, $status);
		}
		
		return json_encode(array('login'=>'', 'action'=>$action, 'stock_take_id'=>$stock_take_id, 'cartsData'=>$cartsData));
	}
	
	public function cancelST(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$stock_take_id = intval($POST['stock_take_id']??0);
		$status = trim((string) $POST['status']??'Cancel');
		$status = $this->db->checkCharLen('stock_take.status', $status);
		
		$action = '';
		$updatestock_take = $this->db->update('stock_take', array('status'=>$status), $stock_take_id);
		if($updatestock_take){
			$action = 'Canceled';
		}
		return json_encode(array('login'=>'', 'action'=>$action));
	}
	
	public function fetching_editdata($stock_take_id=0, $shistory_type=''){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION['accounts_id']??0;		
		$stock_take_id = intval($POST['sstock_take_id']??$stock_take_id);
		$shistory_type = $POST['shistory_type']??$shistory_type;
		$currency = $_SESSION["currency"]??'৳';
		
		$bindData = array();
		$bindData['stock_take_id'] = $stock_take_id;
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Stock Take Created')==0){
				$filterSql = "SELECT 'stock_take' AS tablename, created_on AS tabledate, stock_take_id AS table_id, 'Stock Take Created' AS activity_feed_title FROM stock_take 
					WHERE stock_take_id = :stock_take_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'stock_take' AND table_id = :stock_take_id";
			}
			else{
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'stock_take' AND record_id = :stock_take_id";
			}
			$filterSql .= " ORDER BY tabledate DESC";
		}
		else{
			$filterSql = "SELECT 'stock_take' AS tablename, created_on AS tabledate, stock_take_id AS table_id, 'Stock Take Created' AS activity_feed_title FROM stock_take 
						WHERE stock_take_id = :stock_take_id AND accounts_id = $accounts_id 
					UNION ALL 
					SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'stock_take' AND table_id = :stock_take_id 
					UNION ALL 
					SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'stock_take' AND record_id = :stock_take_id 
					ORDER BY tabledate DESC";
		}
		$query = $this->db->querypagination($filterSql, $bindData);

		$tabledata = $actFeeTitOpts = array();
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
				if($shistory_type ==''){
					$actFeeTitOpts[$activity_feed_title] = '';
				}
				$tablename = $onerow['tablename'];
				$table_id = $onerow['table_id'];
				$getHMoreInfo = $Activity_Feed->getHMoreInfo($table_id, $tablename, $userIdNames, $activity_feed_title);
				if(!empty($getHMoreInfo)){
					$tabledata[] = $getHMoreInfo;
				}
			}
		}

		if(empty($actFeeTitOpts)){
			$Sql = "SELECT 'Stock Take Created' AS afTitle FROM stock_take 
						WHERE stock_take_id = :stock_take_id AND accounts_id = $accounts_id 
					UNION ALL 
					SELECT 'Notes Created' AS afTitle FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'stock_take' AND table_id = :stock_take_id 
					UNION ALL 
					SELECT 'Track Edits' AS afTitle FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'stock_take' AND record_id = :stock_take_id 
					GROUP BY afTitle";
			$tableObj = $this->db->query($Sql, array('stock_take_id'=>$stock_take_id));
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$actFeeTitOpts[$oneRow->afTitle] = '';
				}
			}
		}

		$actFeeTitOpt = array();
		if(!empty($actFeeTitOpts)){
			ksort($actFeeTitOpts);
			$actFeeTitOpt = array_keys($actFeeTitOpts);
		}

		return json_encode(array('login'=>'', 'tabledata'=>$tabledata, 'actFeeTitOpt'=>$actFeeTitOpt));
    
	}

	function showSTCartIMEI(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$action = 'error';
		$stock_take_items_id = intval($POST['stock_take_items_id']??0);
		$currentImeis = $autoCurIMEI = $countedImeis = array();
		$sqlquery = "SELECT sti.*, manufacturer.name AS manufacture, p.sku FROM stock_take_items sti, product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE sti.stock_take_items_id = :stock_take_items_id AND p.product_type ='Live Stocks' AND sti.product_id = p.product_id ORDER BY manufacture ASC, p.product_name ASC, p.colour_name ASC, p.storage ASC, p.physical_condition_name ASC LIMIT 0, 1";
		$STIData = $this->db->querypagination($sqlquery, array('stock_take_items_id'=>$stock_take_items_id));
		if($STIData){
			foreach($STIData as $row){
				$currentImeis = explode('|',$row['current_imeis']);
				if(!empty($currentImeis)){
					foreach($currentImeis as $oneIMEI){
						$autoCurIMEI[] = array('label'=>$oneIMEI);
					}
				}				
				if(!empty($row['counted_imeis'])){
					$countedImeis = explode('|',$row['counted_imeis']);
				}
				$action = 'Done';
			}
		}
		return json_encode(array('login'=>'', 'action'=>$action, 'id'=>$stock_take_items_id, 'currentImeis'=>$currentImeis, 'autoCurIMEI'=>$autoCurIMEI, 'countedImeis'=>$countedImeis));
	}

	public function AJupdateSTIimei(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 'error';
		$stock_take_items_id = intval($POST['stock_take_items_id']??0);
		$inventory_count = intval($POST['inventory_count']??0);
		$counted_imeis = $POST['counted_imeis']??'';
		if($stock_take_items_id>0){
			$update = $this->db->update('stock_take_items', array('inventory_count'=>$inventory_count, 'counted_imeis'=>$counted_imeis), $stock_take_items_id);
			if($update){
				$returnStr = 'Updated';
			}
			else{
				$returnStr = 'Done';				
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
}
?>