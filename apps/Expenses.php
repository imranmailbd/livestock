<?php
class Expenses{
	protected $db;
	private int $page, $totalRows, $vendors_id, $expenses_id;
	private string $date_range, $expense_type, $sorting_type, $keyword_search, $history_type;
	private array $venOpt, $expTypOpt, $actFeeTitOpt;
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
	public function view(){}

	private function filterAndOptions(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$svendors_id = $this->vendors_id;
		$date_range = $this->date_range;
		$sexpense_type = $this->expense_type;
		$sorting_type = $this->sorting_type;
		$keyword_search = $this->keyword_search;
		$startdate = $enddate = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0])).' 00:00:00';
				$enddate = date('Y-m-d', strtotime($date_rangearray[1])).' 23:59:59';
			}
		}
		
		$_SESSION["current_module"] = "Expenses";
		$_SESSION["list_filters"] = array('svendors_id'=>$svendors_id, 'sexpense_type'=>$sexpense_type, 'sorting_type'=>$sorting_type, 'date_range'=>$date_range, 'keyword_search'=>$keyword_search);
		
		$filterSql = '';
		$bindData = array();
		if($svendors_id>0){
			$filterSql .= " AND vendors_id = :vendors_id";
			$bindData['vendors_id'] = $svendors_id;
		}
		if($sexpense_type !=''){
			$filterSql .= " AND expense_type = :expense_type";
			$bindData['expense_type'] = $sexpense_type;
		}
		if($startdate !='' && $enddate !=''){
			$filterSql .= " AND bill_date BETWEEN :startdate AND :enddate";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}
		if($keyword_search !=''){
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', bill_date, bill_number, bill_paid, ref)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$queryObj = $this->db->query("SELECT COUNT(expenses_id) AS totalrows FROM expenses WHERE accounts_id = $accounts_id $filterSql AND expenses_publish = 1", $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}

		$venOpt = array();
		$vendorsIds = $expTypes = array();
		$expSql = "SELECT expense_type, vendors_id FROM expenses WHERE accounts_id = $accounts_id $filterSql AND expenses_publish = 1 GROUP BY expense_type, vendors_id ORDER BY expense_type ASC, vendors_id ASC";
		$expObj = $this->db->query($expSql, $bindData);
		if($expObj){
			while($oneRow = $expObj->fetch(PDO::FETCH_OBJ)){
				$expTypes[$oneRow->expense_type] = '';
				$vendorsIds[$oneRow->vendors_id] = '';
			}
			ksort($expTypes);
			$expTypes = array_keys($expTypes);
		}		
		
		if(!empty($vendorsIds)){
			$vendorsStr = "SELECT vendors_id, name FROM vendors WHERE vendors_id IN (".implode(', ', array_keys($vendorsIds)).") ORDER BY name ASC";
			$vendorsObj = $this->db->query($vendorsStr, array());
			if($vendorsObj){			
				while($oneRow = $vendorsObj->fetch(PDO::FETCH_OBJ)){				
					$vendorsIds[$oneRow->vendors_id] = trim((string) stripslashes("$oneRow->name"));
				}
			}
		}
		
		$this->totalRows = $totalRows;
		$this->venOpt = $vendorsIds;
		$this->expTypOpt = $expTypes;
	}
	
    private function loadTableRows(){
	
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		
		$page = $this->page;
		$totalRows = $this->totalRows;
		$svendors_id = $this->vendors_id;
		$date_range = $this->date_range;
		$sexpense_type = $this->expense_type;
		$ssorting_type = $this->sorting_type;			
		$keyword_search = $this->keyword_search;
		$sortingTypeData = array(0=>'bill_date DESC', 
								1=>'bill_date ASC', 
								2=>'expense_type ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}

		$startdate = $enddate = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0])).' 00:00:00';
				$enddate = date('Y-m-d', strtotime($date_rangearray[1])).' 23:59:59';
			}
		}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
				
		$strextra = "FROM expenses WHERE accounts_id = $accounts_id AND expenses_publish = 1";
		$bindData = array();
		if($svendors_id>0){
			$strextra .= " AND vendors_id = :vendors_id";
			$bindData['vendors_id'] = $svendors_id;
		}
		if($sexpense_type !=''){
			$strextra .= " AND expense_type = :expense_type";
			$bindData['expense_type'] = $sexpense_type;
		}
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND bill_date BETWEEN :startdate AND :enddate";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$strextra .= " AND TRIM(CONCAT_WS(' ', bill_date, bill_number, bill_paid, ref)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
	
		$sqlquery = "SELECT * $strextra";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];

		$sqlquery .= " limit $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);

		$tabledata = array();
		if($query){
			$vendorsId = array();
			foreach($query as $oneRow){
				if(empty($vendorsId) || !in_array($oneRow['vendors_id'], $vendorsId)){
					$vendorsId[] = $oneRow['vendors_id'];
				}
			}

			$vendorsData = array();
			if(!empty($vendorsId)){
				$vendorsObj = $this->db->query("SELECT vendors_id, name FROM vendors WHERE vendors_id IN (".implode(', ', $vendorsId).")", array());
				if($vendorsObj){
					while($vendorsrow = $vendorsObj->fetch(PDO::FETCH_OBJ)){
						$vendorsData[$vendorsrow->vendors_id] = trim((string) stripslashes("$vendorsrow->name"));
					}
				}
			}
			
			foreach($query as $expensesarray){
				$expenses_id = intval($expensesarray['expenses_id']);
				$expense_type = trim((string) stripslashes($expensesarray['expense_type']));
				$bill_number = stripslashes($expensesarray['bill_number']);
				$vendors_id = intval($expensesarray['vendors_id']);
				$vendors_name = trim((string) stripslashes($vendorsData[$vendors_id]??''));

				$bill_amount = round($expensesarray['bill_amount'],2);
				$ref = stripslashes(trim((string) $expensesarray['ref']));
				
				$tabledata[] = array($expenses_id, $expensesarray['bill_date'], $bill_number, $expense_type, $vendors_name, $bill_amount, $expensesarray['bill_paid'], $ref);
				
			}
		}

		return $tabledata;
	}
	
	public function AJgetPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$expenses_id = $POST['expenses_id']??0;
		
		$expense_type = '';
		$vendors_id = 0;
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['bill_date'] = '';
		$jsonResponse['bill_number'] = '';		
		$jsonResponse['bill_paid'] = '';
		$jsonResponse['ref'] = '';		
		$jsonResponse['bill_amount'] = 0.00;
		if($expenses_id>0 && $accounts_id>0){
			$expensesObj = $this->db->query("SELECT * FROM expenses WHERE expenses_id = :expenses_id AND accounts_id = $accounts_id AND expenses_publish = 1", array('expenses_id'=>$expenses_id),1);
			if($expensesObj){
				$expensesRow = $expensesObj->fetch(PDO::FETCH_OBJ);	

				$expense_type = trim((string) $expensesRow->expense_type);
				$vendors_id = $expensesRow->vendors_id;
				$jsonResponse['bill_date'] = $expensesRow->bill_date;
				$jsonResponse['bill_number'] = trim((string) $expensesRow->bill_number);
				$jsonResponse['bill_paid'] = $expensesRow->bill_paid;
				$jsonResponse['ref'] = trim((string) $expensesRow->ref);
				$jsonResponse['bill_amount'] = round($expensesRow->bill_amount,2);
			}
		}
		
		$jsonResponse['expense_type'] = $expense_type;
		$expense_typeOptions = $vendors_idOptions = array();
		if(isset($_SESSION["prod_cat_man"])){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			
			$sql = "SELECT name FROM expense_type WHERE accounts_id = $prod_cat_man AND (expense_type_publish = 1 OR (name = :name AND expense_type_publish = 0)) GROUP BY name ORDER BY name ASC";
			$etObj = $this->db->query($sql, array('name'=>$expense_type));
			if($etObj){
				while($onerow = $etObj->fetch(PDO::FETCH_OBJ)){
					$label = stripslashes(trim((string) $onerow->name));
					if($label !=''){
						$expense_typeOptions[] = stripslashes($label);
					}
				}
			}
			
			$jsonResponse['vendors_id'] = $vendors_id;
			$sql = "SELECT vendors_id, name FROM vendors WHERE accounts_id = $prod_cat_man AND (vendors_publish = 1 OR (vendors_id = :vendors_id AND vendors_publish = 0)) GROUP BY name ORDER BY name ASC";
			$vendorsObj = $this->db->query($sql, array('vendors_id'=>$vendors_id));
			if($vendorsObj){
				while($onerow = $vendorsObj->fetch(PDO::FETCH_OBJ)){				
					$label = stripslashes(trim((string) $onerow->name));
					if($label !=''){
						$vendors_idOptions[$onerow->vendors_id] = $label;
					}
				}
			}
		}
		$jsonResponse['expense_typeOptions'] = $expense_typeOptions;
		$jsonResponse['vendors_idOptions'] = $vendors_idOptions;
		
		return json_encode($jsonResponse);
	}
	
	public function AJsaveExpense(){
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$expenses_id =0;
		$savemsg = $returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		
		$expenses_id = $POST['expenses_id']??0;
		$expense_type = $this->db->checkCharLen('expenses.expense_type', $POST['expense_type']??'');
		$expense_type_name = $this->db->checkCharLen('expense_type.name', $POST['expense_type_name']??'');
		$vendors_id = $POST['vendors_id']??0;
		$vendors_name = $POST['vendors_name']??'';
		$vendors_name = $this->db->checkCharLen('vendors.name', $vendors_name);
		$saveData = array();
		$saveData['accounts_id'] = $accounts_id;
		$saveData['user_id'] = $user_id;

		//===========================for expense_type==================//
		if($expense_type_name !='' && $expense_type=='' && $prod_cat_man>0){

			$countTableData = 0;
			$expTypObj = $this->db->query("SELECT name FROM expense_type WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name", array('name'=>strtoupper($expense_type_name)));
			if($expTypObj){
				$expense_type = stripslashes(trim((string) $expTypObj->fetch(PDO::FETCH_OBJ)->name));
			}
			else{
				$exptylearray = array('name' => $expense_type_name,
					'created_on' => date('Y-m-d H:i:s'),
					'accounts_id' => $prod_cat_man,
					'user_id' => $user_id
				);
				$this->db->insert('expense_type', $exptylearray);
				$expense_type = $expense_type_name;
			}
		}
		$saveData['expense_type'] = $expense_type;

		//===========================for vendors==================//
		if($vendors_name !='' && $vendors_id==0 && $prod_cat_man>0){
			$vendorsObj = $this->db->query("SELECT vendors_id, vendors_publish FROM vendors WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name", array('name'=>strtoupper($vendors_name)));
			if($vendorsObj){
				while($oneRowVen = $vendorsObj->fetch(PDO::FETCH_OBJ)){
					$vendors_publish = $oneRowVen->vendors_publish;
					if($vendors_publish==0){
						$this->db->update('vendors', array('vendors_publish'=>1), $oneRowVen->vendors_id);
					}
					$vendors_id = trim((string) $oneRowVen->vendors_id);
				}
			}
			else{
				$vendorsarray = array('name' => $vendors_name,
					'created_on' => date('Y-m-d H:i:s'),
					'accounts_id' => $prod_cat_man,
					'user_id' => $user_id
				);
				$vendors_id = $this->db->insert('vendors', $vendorsarray);
			}
		}
		$saveData['vendors_id'] = $vendors_id;
		
		$bill_date = $POST['bill_date']??'';
		if($bill_date !=''){$bill_date = date('Y-m-d', strtotime(trim((string) $bill_date)));}
		else{$bill_date = '1000-01-01';}
		$saveData['bill_date'] = $bill_date;
		
		$bill_paid = $POST['bill_paid']??'';
		if(trim((string) $bill_paid) !=''){
			$bill_paid = date('Y-m-d', strtotime(trim((string) $bill_paid)));
		}
		else{
			$bill_paid = '1000-01-01';
		}
		$saveData['bill_paid'] = $bill_paid;
		$saveData['bill_amount'] = floatval(trim((string) $POST['bill_amount']??0.00));
		$bill_number = $this->db->checkCharLen('expenses.bill_number', trim((string) $POST['bill_number']??''));
		$saveData['bill_number'] = $bill_number;
		$saveData['ref'] = $this->db->checkCharLen('expenses.ref', addslashes(trim((string) $POST['ref']??'')));
		
		$duplSql = "SELECT COUNT(expenses_id) AS totalrows FROM expenses WHERE accounts_id = $accounts_id AND vendors_id = :vendors_id AND bill_number = :bill_number";
		$bindData = array('vendors_id'=>$vendors_id, 'bill_number'=>$bill_number);
		if($expenses_id>0){
			$duplSql .= " AND expenses_id != :expenses_id";
			$bindData['expenses_id'] = $expenses_id;
		}
		$duplRows = 0;
		$expensesObj = $this->db->query($duplSql, $bindData);
		if($expensesObj){
			$duplRows = $expensesObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		if($duplRows>0){
			$savemsg = 'error';
			$returnStr .= 'Name_Already_Exist';
		}
		else{
			if($expenses_id==0){
				$saveData['created_on'] = date('Y-m-d H:i:s');
				$expenses_id = $this->db->insert('expenses', $saveData);
				if(!$expenses_id){
					$savemsg = 'error';
					$returnStr .= 'errorOnAdding';
				}
			}
			else{
				$oneTRowObj = $this->db->querypagination("SELECT * FROM expenses WHERE expenses_id = $expenses_id", array());
				
				$update = $this->db->update('expenses', $saveData, $expenses_id);
				if($update){
					if($oneTRowObj){						
						$changed = array();
						foreach($saveData as $fieldName=>$fieldValue){
							$prevFieldVal = $oneTRowObj[0][$fieldName];
							if($prevFieldVal != $fieldValue){
								if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
								if($fieldValue=='1000-01-01'){$fieldValue = '';}
								if($fieldName=='vendors_id'){
									$Common = new Common($this->db);
									$fieldName = 'Vendor Name';
									if($prevFieldVal==0){$prevFieldVal = '';}
									elseif($prevFieldVal>0){$prevFieldVal = $Common->getOneRowFields('vendors', array('vendors_id'=>$prevFieldVal), 'name');}
									if($fieldValue==0){$fieldValue = '';}
									elseif($fieldValue>0){$fieldValue = $Common->getOneRowFields('vendors', array('vendors_id'=>$fieldValue), 'name');}
								}
								$changed[$fieldName] = array($prevFieldVal, $fieldValue);
							}
						}
						
						if(!empty($changed)){
							$moreInfo = $teData = array();
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $_SESSION["accounts_id"];
							$teData['user_id'] = $_SESSION["user_id"];
							$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'expenses');
							$teData['record_id'] = $expenses_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);							
						}
					}
				}
				else{
					$savemsg = 'error';
				}
			}
		}
	
		$array = array('login'=>'', 'expenses_id'=>$expenses_id, 'savemsg'=>$savemsg, 'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	public function AJremoveExpense(){										
	
		$POST = json_decode(file_get_contents('php://input'), true);
		$expenses_id = $POST['expenses_id']??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$returnmsg = '';
		$removeCount = 0;
		
		$sqlquery = "SELECT 'activity_feed' AS tablename, activity_feed_id AS table_id FROM activity_feed WHERE accounts_id = $accounts_id AND uri_table_name = 'expenses' AND activity_feed_link = :activity_feed_link 
					UNION SELECT 'track_edits' AS tablename, track_edits_id AS table_id FROM track_edits WHERE accounts_id = $accounts_id AND record_for = 'expenses' AND record_id = :expenses_id 
					UNION SELECT 'expenses' as tablename, expenses_id as table_id FROM expenses WHERE expenses_id = :expenses_id and accounts_id = $accounts_id";
		$query = $this->db->query($sqlquery, array('activity_feed_link'=>"/Expenses/view/$expenses_id", 'expenses_id'=>$expenses_id));
		if($query){
			while($onerow = $query->fetch(PDO::FETCH_OBJ)){
				$tablename = $onerow->tablename;				
				$dtrow = $this->db->delete($tablename, $tablename.'_id', $onerow->table_id);
				if($dtrow>0){$removeCount++;}
			}
		}
		return json_encode(array('login'=>'', 'returnmsg'=>$returnmsg, 'removeCount'=>$removeCount));
	}
	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$svendors_id = (int)($POST['svendors_id']??0);
		$date_range = $POST['date_range']??'';
		$sexpense_type = $POST['sexpense_type']??'';
		$sorting_type = $POST['sorting_type']??0;
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->date_range = $date_range;
		$this->vendors_id = $svendors_id;
		$this->expense_type = $sexpense_type;
		$this->sorting_type = $sorting_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['venOpt'] = $this->venOpt;
			$jsonResponse['expTypOpt'] = $this->expTypOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}	
	
	public function AJgetHPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$expenses_id = $POST['expenses_id']??0;
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->expenses_id = $expenses_id;
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
	
	public function AJ_view_MoreInfo(){
		
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$expenses_id = $POST['expenses_id']??0;
		
		$expense_type = $vendors_name = '';
		$vendors_id = 0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['bill_date'] = '';
		$jsonResponse['bill_number'] = '';		
		$jsonResponse['bill_paid'] = '';
		$jsonResponse['ref'] = '';		
		$jsonResponse['bill_amount'] = 0;	
		$jsonResponse['expenses_publish'] = 0;
				
		if($expenses_id>0 && $accounts_id>0){
			$expensesObj = $this->db->query("SELECT * FROM expenses WHERE expenses_id = :expenses_id AND accounts_id = $accounts_id", array('expenses_id'=>$expenses_id),1);
			if($expensesObj){
				$expensesRow = $expensesObj->fetch(PDO::FETCH_OBJ);	

				$expense_type = trim((string) $expensesRow->expense_type);
				$vendors_id = $expensesRow->vendors_id;				
				if($vendors_id >0){
					$vendorsObj = $this->db->query("SELECT name FROM vendors WHERE vendors_id = $vendors_id", array());
					if($vendorsObj){
						$vendors_name = $vendorsObj->fetch(PDO::FETCH_OBJ)->name;
					}
				}
				
				$jsonResponse['bill_date'] = $expensesRow->bill_date;
				$jsonResponse['bill_number'] = trim((string) $expensesRow->bill_number);
				$jsonResponse['bill_paid'] = $expensesRow->bill_paid;
				$jsonResponse['ref'] = trim((string) $expensesRow->ref);
				$jsonResponse['bill_amount'] = round($expensesRow->bill_amount,2);
				$jsonResponse['expenses_publish'] = floor($expensesRow->expenses_publish);
				
			}
			else{
				$jsonResponse['login'] = 'Expenses/lists/';
			}
		}
		$jsonResponse['expense_type'] = $expense_type;
		$jsonResponse['vendors_name'] = $vendors_name;
		$jsonResponse['expenses_id'] = $expenses_id;
		
		return json_encode($jsonResponse);
	}

	private function filterHAndOptions(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$expenses_id = $this->expenses_id;
		$shistory_type = $this->history_type;
		$filterSql = '';
		$bindData = array();
		$bindData['expenses_id'] = $expenses_id;
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Expense Created')==0){
				$filterSql = "SELECT COUNT(expenses_id) AS totalrows FROM expenses 
					WHERE expenses_id = :expenses_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
					WHERE accounts_id = $accounts_id AND note_for = 'expenses' AND table_id = :expenses_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
					WHERE accounts_id = $accounts_id AND record_for = 'expenses' AND record_id = :expenses_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
					WHERE accounts_id = $accounts_id AND uri_table_name = 'expenses' AND activity_feed_link = CONCAT('/Expenses/view/', :expenses_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'expenses' AND activity_feed_link = CONCAT('/Expenses/view/', :expenses_id) 
					UNION ALL 
						SELECT COUNT(expenses_id) AS totalrows FROM expenses 
							WHERE expenses_id = :expenses_id and accounts_id = $accounts_id 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'expenses' AND record_id = :expenses_id 
					UNION ALL 
						SELECT COUNT(notes_id) AS totalrows FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'expenses' AND table_id = :expenses_id";
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
				WHERE accounts_id = $accounts_id AND uri_table_name = 'expenses' AND activity_feed_link = CONCAT('/Expenses/view/', :expenses_id) 
			UNION ALL 
				SELECT 'Expense Created' AS afTitle FROM expenses 
					WHERE expenses_id = :expenses_id and accounts_id = $accounts_id 
			UNION ALL 
				SELECT 'Track Edits' AS afTitle FROM track_edits 
					WHERE accounts_id = $accounts_id AND record_for = 'expenses' AND record_id = :expenses_id 
			UNION ALL 
				SELECT 'Notes Created' AS afTitle FROM notes 
				WHERE accounts_id = $accounts_id AND note_for = 'expenses' AND table_id = :expenses_id";
		$tableObj = $this->db->query($Sql, array('expenses_id'=>$expenses_id));
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
		$expenses_id = $this->expenses_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$bindData = array();
		$bindData['expenses_id'] = $expenses_id;            
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Expense Created')==0){
				$filterSql = "SELECT 'expenses' AS tablename, created_on AS tabledate, expenses_id AS table_id, 'Expense Created' AS activity_feed_title FROM expenses 
					WHERE expenses_id = :expenses_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'expenses' AND table_id = :expenses_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'expenses' AND record_id = :expenses_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'expenses' AND activity_feed_link = CONCAT('/Expenses/view/', :expenses_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'expenses' AND activity_feed_link = CONCAT('/Expenses/view/', :expenses_id)  
					UNION ALL 
					SELECT 'expenses' AS tablename, created_on AS tabledate, expenses_id AS table_id, 'Expense Created' AS activity_feed_title FROM expenses 
						WHERE expenses_id = :expenses_id AND accounts_id = $accounts_id 
					UNION ALL SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'expenses' AND record_id = :expenses_id 
					UNION ALL 
					SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'expenses' AND table_id = :expenses_id 
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

	public function profit_loss(){}

	public function AJ_profit_loss_MoreInfo(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['showing_type'] = 'Summary';
		$jsonResponse['paymenttype'] = 'Cash';
		$jsonResponse['startdate'] = date('Y-m-d', time()-518400);
		$jsonResponse['enddate'] = date('Y-m-d');
		return json_encode($jsonResponse);
	}
	
	public function AJprofit_lossData(){
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$date_range = $POST['date_range']??'';
		$spaymenttype = $POST['paymenttype']??'';
		$showing_type = $POST['showing_type']??'';
		$Common = new Common($this->db);
		
		$startdate = $startdate1 = $enddate = $enddate1 = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0])).' 00:00:00';
				$startdate1 = date('Y-m-d', strtotime($date_rangearray[0]));
				$enddate = date('Y-m-d', strtotime($date_rangearray[1])).' 23:59:59';
				$enddate1 = date('Y-m-d', strtotime($date_rangearray[1]));
			}
		}

		$jsonResponse['todayDate'] = date('Y-m-d');
		$jsonResponse['startdate'] = $startdate1;
		$jsonResponse['enddate'] = $enddate1;
		
		$colspan2 = $colspan3 = 0;
		$colspan4 = 2;
		$boldclass = $bgashclass = '';
		if(strcmp($showing_type, 'Detailed')==0){
			$colspan2 = 2;
			$colspan3 = 5;
			$colspan4 = 6;
			$boldclass = ' txtbold';
			$bgashclass = ' bgash';
		}
		$jsonResponse['colspan2'] = $colspan2;
		$jsonResponse['colspan3'] = $colspan3;
		$jsonResponse['colspan4'] = $colspan4;
		$jsonResponse['boldclass'] = $boldclass;
		$jsonResponse['bgashclass'] = $bgashclass;

		//================For Income==================//
		$incomeTotal = 0;
		$bindData = array();
		if(strcmp($showing_type, 'Detailed')==0){
			if(strcmp($spaymenttype, 'Cash')==0){
				$paymentSql = "SELECT pos.pos_id, pos_payment.payment_datetime AS date, pos.invoice_no, pos_payment.payment_amount";
			}
			else{
				$paymentSql = "SELECT pos.pos_id, pos.sales_datetime AS date, pos.invoice_no, pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, pos_cart.sales_price, pos_cart.shipping_qty, pos_cart.taxable, pos_cart.discount_is_percent, pos_cart.discount";
			}
		}
		else{
			if(strcmp($spaymenttype, 'Cash')==0){
				$paymentSql = "SELECT pos.pos_id, sum(pos_payment.payment_amount) AS totpayment_amount";
			}
			else{
				$paymentSql = "SELECT pos.pos_id, pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2, pos_cart.sales_price, pos_cart.shipping_qty, pos_cart.taxable, pos_cart.discount_is_percent, pos_cart.discount";
			}
		}
		if(strcmp($spaymenttype, 'Cash')==0){
			$paymentSql .= " FROM pos, pos_payment WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pos_payment.pos_id";
		}
		else{
			$paymentSql .= " FROM pos, pos_cart WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND pos.pos_id = pos_cart.pos_id AND (pos.pos_type = 'Sale' OR (pos.pos_type IN ('Order', 'Repairs') AND pos.order_status = 2))";
		}
		if($startdate !='' && $enddate !=''){
			if(strcmp($spaymenttype, 'Cash')==0){
				$paymentSql .= " AND pos_payment.payment_datetime BETWEEN :startdate AND :enddate";
			}
			else{
				$paymentSql .= " AND pos.sales_datetime BETWEEN :startdate AND :enddate";
			}
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}
		if($spaymenttype !=''){
			if(strcmp($spaymenttype, 'Cash')==0){
				//$paymentSql .= " and pos_payment.payment_method = 'Cash'";
			}
		}

		$incomedetails = array();
		if(strcmp($showing_type, 'Detailed')==0){
			$query = $this->db->querypagination($paymentSql, $bindData);
			if($query){
				$num_rows = count($query);
				if($num_rows>0){
					$prevpos_id = 0;

					for($r=0; $r<$num_rows; $r++){

						$pospaymentrow = $query[$r];

						$pos_id = $pospaymentrow['pos_id'];
						$nextpos_id = 0;
						if(($r+1)<$num_rows){
							$nextrow = $query[$r+1];
							$nextpos_id = $nextrow['pos_id'];
						}

						if($pos_id != $prevpos_id){
							$totalpayment_amount = $taxable_total = $totalnontaxable = 0.00;
						}

						$prevpos_id = $pos_id;
						if(strcmp($spaymenttype, 'Cash')==0){
							$payment_amount = $pospaymentrow['payment_amount'];
							$totalpayment_amount += $payment_amount;
						}
						else{
							$sales_price = $pospaymentrow['sales_price'];
							$shipping_qty = $pospaymentrow['shipping_qty'];

							$qtyvalue = round($sales_price*$shipping_qty,2);

							$discount_is_percent = $pospaymentrow['discount_is_percent'];
							$discount = $pospaymentrow['discount'];
							if($discount_is_percent>0){
								$discount_value = round($qtyvalue*0.01*$discount,2);
							}
							else{
								$discount_value = round($discount*$shipping_qty,2);
							}

							$taxable = $pospaymentrow['taxable'];
							if($taxable>0){
								$taxable_total += $qtyvalue-$discount_value;
							}
							else{
								$totalnontaxable += $qtyvalue-$discount_value;
							}
						}
						
						if($pos_id != $nextpos_id){
							
							$invoice_no = intval($pospaymentrow['invoice_no']);

							if(strcmp($spaymenttype, 'Cash') !=0){
								$taxes_total1 = $Common->calculateTax($taxable_total, $pospaymentrow['taxes_percentage1'], $pospaymentrow['tax_inclusive1']);
								$taxes_total2 = $Common->calculateTax($taxable_total, $pospaymentrow['taxes_percentage2'], $pospaymentrow['tax_inclusive2']);

								$tax_inclusive1 = $pospaymentrow['tax_inclusive1'];
								$tax_inclusive2 = $pospaymentrow['tax_inclusive2'];

								$Taxes = 0;
								if($tax_inclusive1==0){$Taxes += $taxes_total1;}
								if($tax_inclusive2==0){$Taxes += $taxes_total2;}
								$totalpayment_amount = $taxable_total+$Taxes+$totalnontaxable;
							}
							$incomeTotal += $totalpayment_amount;
							
							$incomedetails[] = array($pospaymentrow['date'], $invoice_no, round($totalpayment_amount,2));
						}
					}
				}
			}
		}
		else{
			if(strcmp($spaymenttype, 'Cash')==0){
				$query = $this->db->query($paymentSql, $bindData);
				if($query){
					while($onegrouprow = $query->fetch(PDO::FETCH_OBJ)){
						$incomeTotal += $onegrouprow->totpayment_amount;
					}
				}
			}
			else{
				$query = $this->db->querypagination($paymentSql, $bindData);
				if($query){
					$num_rows = count($query);
					if($num_rows>0){
						$prevpos_id = 0;

						for($r=0; $r<$num_rows; $r++){

							$pospaymentrow = $query[$r];

							$pos_id = $pospaymentrow['pos_id'];
							$nextpos_id = 0;
							if(($r+1)<$num_rows){
								$nextrow = $query[$r+1];
								$nextpos_id = $nextrow['pos_id'];
							}

							if($pos_id != $prevpos_id){
								$totalpayment_amount = $taxable_total = $totalnontaxable = 0.00;
							}

							$prevpos_id = $pos_id;
							
							$sales_price = $pospaymentrow['sales_price'];
							$shipping_qty = $pospaymentrow['shipping_qty'];

							$qtyvalue = round($sales_price*$shipping_qty,2);

							$discount_is_percent = $pospaymentrow['discount_is_percent'];
							$discount = $pospaymentrow['discount'];
							if($discount_is_percent>0){
								$discount_value = round($qtyvalue*0.01*$discount,2);
							}
							else{
								$discount_value = round($discount*$shipping_qty,2);
							}

							$taxable = $pospaymentrow['taxable'];
							if($taxable>0){
								$taxable_total += $qtyvalue-$discount_value;
							}
							else{
								$totalnontaxable += $qtyvalue-$discount_value;
							}
							
							if($pos_id != $nextpos_id){
								
								$taxes_total1 = $Common->calculateTax($taxable_total, $pospaymentrow['taxes_percentage1'], $pospaymentrow['tax_inclusive1']);
								$taxes_total2 = $Common->calculateTax($taxable_total, $pospaymentrow['taxes_percentage2'], $pospaymentrow['tax_inclusive2']);

								$tax_inclusive1 = $pospaymentrow['tax_inclusive1'];
								$tax_inclusive2 = $pospaymentrow['tax_inclusive2'];

								$Taxes = 0;
								if($tax_inclusive1==0){$Taxes += $taxes_total1;}
								if($tax_inclusive2==0){$Taxes += $taxes_total2;}
								$totalpayment_amount = $taxable_total+ $Taxes+$totalnontaxable;
								
								$incomeTotal += $totalpayment_amount;
							}
						}
					}
				}
			}
		}

		$jsonResponse['incomedetails'] = $incomedetails;

		$jsonResponse['incomeTotal'] = $incomeTotal;

		//================For Cost of Goods==================//
		$costTotal = $costTaxTotal = $costShippingTotal = 0;
		$additionalfield = '';
		if(strcmp($showing_type, 'Detailed')==0){
			$additionalfield = "po.date_paid, po.invoice_date, po.po_number, po.supplier_id, ";
		}

		$coststr = "SELECT $additionalfield po.tax_is_percent, po.taxes, po.shipping, sum(po_items.received_qty*po_items.cost) AS totPOCost 
						FROM po, po_items 
						WHERE po.accounts_id = $accounts_id AND po.po_publish = 1 AND po.po_id = po_items.po_id";
		if($startdate1 !='' && $enddate1 !=''){
			if(strcmp($spaymenttype, 'Cash')==0){
				$coststr .= " and (po.date_paid between '$startdate1' and '$enddate1')";
			}
			else{
				$coststr .= " and (po.invoice_date between '$startdate1' and '$enddate1')";
			}
		}
		$coststr .= " GROUP BY po.po_id ORDER BY po.po_id ASC";
		$costDetailed = array();
		$query3 = $this->db->query($coststr, array());
		if($query3){
			while($onegrouprow = $query3->fetch(PDO::FETCH_OBJ)){
				$totPOCost = round($onegrouprow->totPOCost,2);
				if($onegrouprow->taxes !==0){
					$taxes = $onegrouprow->taxes;
					if($onegrouprow->tax_is_percent>0){
						$costTaxTotal += round($totPOCost*0.01*$taxes,2);
					}
					else{
						$costTaxTotal += $taxes;
					}
				}
				if(strcmp($showing_type, 'Detailed')==0){
					$supplier_id = $onegrouprow->supplier_id;
					$supplier_name = '';
					if($supplier_id >0){
						$suppliersObj = $this->db->query("SELECT first_name, last_name FROM suppliers WHERE suppliers_id = $supplier_id", array());
						if($suppliersObj){
							$suppliersOneRow = $suppliersObj->fetch(PDO::FETCH_OBJ);
							$supplier_name = stripslashes(trim("$suppliersOneRow->first_name $suppliersOneRow->last_name"));
						}
					}
					$costDetailed[] = array($onegrouprow->date_paid, intval($onegrouprow->po_number), $supplier_name, round($totPOCost,2));
				}
				$costShippingTotal += $onegrouprow->shipping;

				$costTotal += $totPOCost;
			}
		}
		$jsonResponse['costDetailed'] = $costDetailed;
		$jsonResponse['costTotal'] = round($costTotal,2);
		$jsonResponse['costTaxTotal'] = round($costTaxTotal,2);
		$jsonResponse['costShippingTotal'] = round($costShippingTotal,2);

		//================For Gross Income==================//
		$grossIncome = $incomeTotal-$costTotal-$costTaxTotal-$costShippingTotal;
		$jsonResponse['grossIncome'] = $grossIncome;

		//================For Expense==================//
		if(strcmp($showing_type, 'Detailed')==0){
			$expensestr = "SELECT expenses_id, expense_type, bill_number, bill_paid, bill_date, vendors_id, bill_amount";
		}
		else{
			$expensestr = "SELECT expense_type, sum(bill_amount) AS totbill_amount";
		}
		$expensestr .= " FROM expenses 
						WHERE accounts_id = $accounts_id AND expenses_publish = 1";
		if($startdate1 !='' && $enddate1 !=''){
			if(strcmp($spaymenttype, 'Cash')==0){
				$expensestr .= " and (bill_paid between '$startdate1' and '$enddate1')";
			}
			else{
				$expensestr .= " and (bill_date between '$startdate1' and '$enddate1')";
			}
		}
		if(strcmp($showing_type, 'Summary')==0){
			$expensestr .= " GROUP BY expense_type";
		}
		$expensestr .= " ORDER BY expense_type ASC, expenses_id ASC";
		if(strcmp($showing_type, 'Summary')==0){
			$query = $this->db->query($expensestr, array());
		}
		else{
			$query = $this->db->querypagination($expensestr, array());
		}
		$expenseTotal = 0;
		$expenseTableData = array();
		if($query){
			if(strcmp($showing_type, 'Summary')==0){
				while($onegrouprow = $query->fetch(PDO::FETCH_OBJ)){
					$expense_type = $onegrouprow->expense_type;
					$totbill_amount = round($onegrouprow->totbill_amount,2);
					$expenseTotal += $totbill_amount;
					$expenseTableData[] = array($expense_type, $totbill_amount);
				}
			}
			else{
				$num_rows = count($query);
				if($num_rows>0){

					$prevexpense_type = '';

					for($r=0; $r<$num_rows; $r++){

						$expenseOneRow = $query[$r];

						$expenses_id = $expenseOneRow['expenses_id'];
						$expense_type = $expenseOneRow['expense_type'];
						$nextexpense_type = '';
						if(($r+1)<$num_rows){
							$nextrow = $query[$r+1];
							$nextexpense_type = $nextrow['expense_type'];
						}

						if($expense_type != $prevexpense_type){
							$totbill_amount = 0.00;
							$expenseTypeData = array();
						}

						$vendors_id = $expenseOneRow['vendors_id'];
						$vendors_name = '';
						if($vendors_id >0){
							$vendorsObj = $this->db->query("SELECT name FROM vendors WHERE vendors_id = $vendors_id", array());
							if($vendorsObj){
								$vendors_name = $vendorsObj->fetch(PDO::FETCH_OBJ)->name;
							}
						}
						$bill_number = $expenseOneRow['bill_number'];
						$bill_amount = round($expenseOneRow['bill_amount'],2);
						$totbill_amount +=$bill_amount;

						if($showing_type=='Detailed'){
							$expenseTypeData[] = array(intval($expenses_id), $bill_number, $expenseOneRow['bill_paid'], $vendors_name, $bill_amount);
						}

						if($expense_type != $nextexpense_type){
							$expenseTotal += $totbill_amount;

							$expenseTableData[] = array($expense_type, round($totbill_amount,2));
							$expenseTableData[] = array('||', $expenseTypeData);
						}
						$prevexpense_type = $expense_type;
					}
				}
			}
			$jsonResponse['expenseTotal'] = $expenseTotal;
		}

		$jsonResponse['expenseTableData'] = $expenseTableData;

		//================For Net Income==================//
		$netIncome = $grossIncome-$expenseTotal;
		$jsonResponse['netIncome'] = $netIncome;

		return json_encode($jsonResponse);
    }
	
	public function prints(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$language = $_SESSION["language"]??'English';
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

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
		return $htmlStr;		
	}
	
	public function AJ_prints_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$date_range = $POST['dr']??'';
		$svendors_id = $POST['vid']??0;
		$sexpense_type = $POST['et']??'';
		$ssorting_type = $POST['st']??0;
		$keyword_search = $POST['ks']??'';
		$sortingTypeData = array(0=>'bill_date DESC', 
								1=>'bill_date ASC', 
								2=>'expense_type ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
		}

		$accounts_id = $_SESSION["accounts_id"]??0;	
		$svendorsStr = '';
		if($svendors_id>0){
			$tableObj = $this->db->query("SELECT name FROM vendors WHERE vendors_id = $svendors_id", array());
			if($tableObj){
				$svendorsStr = stripslashes(trim((string) $tableObj->fetch(PDO::FETCH_OBJ)->name));
			}
		}

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['company_name'] = $_SESSION['company_name'];
		$jsonResponse['todayDate'] = date('Y-m-d');
		$jsonResponse['date_range'] = $date_range;
		$jsonResponse['svendorsStr'] = $svendorsStr;
		$jsonResponse['sexpense_type'] = $sexpense_type;
		$jsonResponse['keyword_search'] = $keyword_search;
		
		$startdate = $enddate = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d', strtotime($date_rangearray[0])).' 00:00:00';
				$enddate = date('Y-m-d', strtotime($date_rangearray[1])).' 23:59:59';
			}
		}
				
		$strextra = "FROM expenses WHERE accounts_id = $accounts_id AND expenses_publish = 1";
		$bindData = array();
		if($svendors_id>0){
			$strextra .= " AND vendors_id = :vendors_id";
			$bindData['vendors_id'] = $svendors_id;
		}
		if($sexpense_type !=''){
			$strextra .= " AND expense_type = :expense_type";
			$bindData['expense_type'] = $sexpense_type;
		}
		if($startdate !='' && $enddate !=''){
			$strextra .= " AND bill_date BETWEEN :startdate AND :enddate";
			$bindData["startdate"] = $startdate;
			$bindData["enddate"] = $enddate;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$strextra .= " AND TRIM(CONCAT_WS(' ', bill_date, bill_number, bill_paid, ref)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		$tableData = array();
		$sqlquery = "SELECT * $strextra";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$query = $this->db->querypagination($sqlquery, $bindData);
		if($query){
			$vendorsId = array();
			foreach($query as $oneRow){
				if(empty($vendorsId) || !in_array($oneRow['vendors_id'], $vendorsId)){
					$vendorsId[] = $oneRow['vendors_id'];
				}
			}

			$vendorsData = array();
			if(!empty($vendorsId)){
				$vendorsObj = $this->db->query("SELECT vendors_id, name FROM vendors WHERE vendors_id IN (".implode(', ', $vendorsId).")", array());
				if($vendorsObj){
					while($vendorsrow = $vendorsObj->fetch(PDO::FETCH_OBJ)){
						$vendorsData[$vendorsrow->vendors_id] = trim((string) stripslashes("$vendorsrow->name"));
					}
				}
			}
			
			foreach($query as $expensesarray){
				$expenses_id = $expensesarray['expenses_id'];
				$expense_type = trim((string) stripslashes($expensesarray['expense_type']));

				$bill_number = stripslashes($expensesarray['bill_number']);
				$vendors_id = $expensesarray['vendors_id'];
				$vendors_name = trim((string) stripslashes($vendorsData[$vendors_id]??''));

				$bill_amount = round($expensesarray['bill_amount'],2);
				$ref = stripslashes(trim((string) $expensesarray['ref']));

				$tableData[] = array($expensesarray['bill_date'], $bill_number, $expense_type, $vendors_name, $bill_amount, $expensesarray['bill_paid'], $ref);
				
			}
		}
		$jsonResponse['tableData'] = $tableData;

		return json_encode($jsonResponse);
	}
	
}