<?php
class Accounts{
	protected $db;
	private int $page, $totalRows;
	private string $sorting_type, $data_type, $keyword_search, $history_type;
	public string $pageTitle;
	private array $actFeeTitOpt;

	public function __construct($db){$this->db = $db;}
	
	public function dashboard(){}
	
	public function AJ_dashboard_MoreInfo(){
		
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		return json_encode($jsonResponse);
	}
	
	//========================For Sub-Group module=======================//    		
	public function sub_group(){}
	
	public function AJsave_sub_group(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$returnStr = 'Ok';		
		$savemsg = '';
		$sub_group_id = intval($POST['sub_group_id']??0);
		$account_type = intval($POST['account_type']??0);
		$name = addslashes(trim((string) $POST['name']??''));
		$name = $this->db->checkCharLen('sub_group.name', $name);
		
		$conditionarray = array();
		$conditionarray['account_type'] = $account_type;
		$conditionarray['name'] = $name;
		$conditionarray['created_on'] = date('Y-m-d H:i:s');
		$conditionarray['accounts_id'] = $prod_cat_man;
		$conditionarray['user_id'] = $user_id;
		
		$duplSql = "SELECT sub_group_publish, sub_group_id FROM sub_group WHERE accounts_id = $prod_cat_man AND account_type = :account_type AND UPPER(name) = :name";
		$bindData = array('account_type'=>$account_type, 'name'=>strtoupper($name));
		if($sub_group_id>0){
			$duplSql .= " AND sub_group_id != :sub_group_id";
			$bindData['sub_group_id'] = $sub_group_id;
		}

		$duplSql .= " LIMIT 0, 1";
		$duplRows = 0;
		$sub_groupObj = $this->db->querypagination($duplSql, $bindData);
		if($sub_groupObj){
			foreach($sub_groupObj as $onerow){
				$duplRows = 1;
				$sub_group_publish = $onerow['sub_group_publish'];
				if($sub_group_publish==0){
					$sub_group_id = $onerow['sub_group_id'];
					$this->db->update('sub_group', array('sub_group_publish'=>1), $sub_group_id);
					$duplRows = 0;
					$savemsg = 'Update';
				}
			}
		}
		
		if($duplRows>0){
			$savemsg = 'error';
			$returnStr = 'Name_Already_Exist';
		}
		elseif($account_type==0){
			$savemsg = 'error';
			$returnStr = 'Missing Account Type';
		}
		else{			
			if($sub_group_id==0){
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$sub_group_id = $this->db->insert('sub_group', $conditionarray);
				if($sub_group_id){						
					$savemsg = 'Add';
				}
				else{
					$returnStr = 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('sub_group', $conditionarray, $sub_group_id);
				if($update){					
					$savemsg = 'Update';
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'savemsg'=>$savemsg));
	}
	
	public function AJgetPage_sub_group($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions_sub_group();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows_sub_group();
		
		return json_encode($jsonResponse);
	}
	
	private function filterAndOptions_sub_group(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Accounts";
		$_SESSION["list_filters"] = array('keyword_search'=>$keyword_search);
		
		$sqlPublish = " AND sub_group_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND sub_group_publish = 0";
		}		
		$filterSql = "FROM sub_group WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', account_type, name)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$strextra ="SELECT COUNT(sub_group_id) AS totalrows $filterSql";
		$query = $this->db->query($strextra, $bindData);
		if($query){
			$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;		
	}
	
   private function loadTableRows_sub_group(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$sqlPublish = " AND sub_group_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND sub_group_publish = 0";
		}		
		$filterSql = "FROM sub_group WHERE accounts_id = $prod_cat_man $sqlPublish";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', account_type, name)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlquery = "SELECT * $filterSql ORDER BY account_type ASC, name ASC LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		$tabledata = array();
		if($query){
			foreach($query as $onerow){
				$sub_group_id = $onerow['sub_group_id'];
				$account_type = intval($onerow['account_type']);
				$name = trim((string) stripslashes($onerow['name']));
				$tabledata[] = array($sub_group_id, $account_type, $name);
			}
		}
		return $tabledata;
   }
}
?>