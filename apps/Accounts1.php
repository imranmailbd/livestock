<?php
class Accounts{
	protected $db;
	public function __construct($db){$this->db = $db;}
	private $active_ledger, $account_type, $sub_group_id, $parent_ledger_id, $visible_on, $keyword_search, $ledger_id, $fdate, $date_range, $CountList, $publish, $voucher_type, $subGroOpt, $parLedOpt, $vouTypOpt, $totalRows, $tableRows, $page, $rowHeight;
	
	public function dashboard(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$returnHTML = "<div class=\"row mtop15\">
				<div class=\"col-sm-12\">
					<div class=\"widget mbottom10\">
						<div class=\"widget-header\">
							<h3>Accounts Modules list</h3>
						</div> <!-- /widget-header -->
						<div class=\"widget-content module-center\">                
							<ul class=\"inventory txtcenter\">";
				
		$linksInfo = array();
		$linksInfo[] = array('category.png', 'Accounts/subGroup', 'Manage Sub-Group');
		$linksInfo[] = array('ledger.png', 'Accounts/ledger', 'Manage Ledger');
		$linksInfo[] = array('receipt.png', 'Accounts/receiptVoucher', 'Receipt Voucher');
		$linksInfo[] = array('payment.png', 'Accounts/paymentVoucher', 'Payment Voucher');
		$linksInfo[] = array('journal.png', 'Accounts/journalVoucher', 'Journal Voucher');
		$linksInfo[] = array('contra.png', 'Accounts/contraVoucher', 'Contra Voucher');
		$linksInfo[] = array('purchase.png', 'Accounts/purchaseVoucher', 'Purchase Voucher');
		$linksInfo[] = array('sales.png', 'Accounts/salesVoucher', 'Sales Voucher');
		
		foreach($linksInfo as $links){
			$returnHTML .= "<li>
			<div class=\"homeiconmenu blue_bg boxshadow\">
			<a class=\"firstclild sidebarlink txtwhite\" href=\"/$links[1]\" title=\"$links[2]\">
			<h4 class=\"moduleName\">$links[2]</h4>
			<img src=\"/assets/images/Accounts/$links[0]\" alt=\"$links[1]\">
			</a>
			</div>
			</li>";
		}
		
		$returnHTML .= "</ul>
			</div>
			</div>				
			</div>
			</div>
			<div class=\"row mtop15\">
			<div class=\"col-sm-12\">
			<div class=\"widget mbottom10\">
			<div class=\"widget-header\">
			<h3>Accounts Reports</h3>
			</div> <!-- /widget-header -->
			<div class=\"widget-content module-center\">                
			<ul class=\"inventory txtcenter\">";
				
		$linksInfo = array();
		$linksInfo[] = array('dayBook.png', 'Accounts/dayBook', 'Day Book');
		$linksInfo[] = array('ledgerReport.png', 'Accounts/ledgerReport', 'Ledger Report');
		$linksInfo[] = array('trialBalance.png', 'Accounts/trialBalance', 'Trial Balance');
		$linksInfo[] = array('payment.png', 'Accounts/receiptPayment', 'Receipt & Payment');
		//$linksInfo[] = array('ledgerReport.png', 'Accounts/cOGS', 'COGS (Cost of Good Sold)');
		//$linksInfo[] = array('incomeStatement.png', 'Accounts/incomeStatement', 'Income Statement');
		//$linksInfo[] = array('financialPosition.png', 'Accounts/financialPosition', 'Financial Position');
		//$linksInfo[] = array('cashFlow.png', 'Accounts/cashFlow', 'Cash Flow');
		//$linksInfo[] = array('shareholderEquity.png', 'Accounts/shareholderEquity', 'Shareholder Equity');
		foreach($linksInfo as $links){
			$returnHTML .= "<li>
		<div class=\"homeiconmenu blue_bg boxshadow\">
		<a class=\"firstclild sidebarlink txtwhite\" href=\"/$links[1]\" title=\"$links[2]\">
		<h4 class=\"moduleName\">$links[2]</h4>
		<img src=\"/assets/images/Accounts/$links[0]\" alt=\"$links[1]\">
		</a>
		</div>
		</li>";
		}
		
		$returnHTML .= "</ul>
		</div>
		</div>				
		</div>
		</div>";
		return $returnHTML;
	}
		
	public function subGroup(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$this->pageTitle = $GLOBALS['title'];
		$list_filters = $_SESSION['list_filters']??array();
		
		$faccount_type = $list_filters['faccount_type']??0;
		$this->account_type = $faccount_type;
		
		$keyword_search = $list_filters['keyword_search']??'';
		$this->keyword_search = $keyword_search;
		$this->CountList = 'Count';
		
		$this->subGroupData();
		$totalRows = $this->totalRows;
		
		$page = !empty($GLOBALS['segment4name']) ? intval($GLOBALS['segment4name']):1;
		if($page<=0){$page = 1;}
		if(!isset($_SESSION['limit'])){$_SESSION['limit'] = 'auto';}
		$limit = $_SESSION['limit'];
		
		$this->rowHeight = 34;
		$this->page = $page;
		$this->CountList = 'List';
		$tableRows = $this->subGroupData();
		
		$accTypOpt = "<option value=\"0\">All Account Type</option>";
		$accountTypes = $this->accountTypes();
		foreach($accountTypes as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($faccount_type==$oneOptVal){$selected = ' selected';}
			$accTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		
		$limOpt = '';
		$limOpts = array(15, 20, 25, 50, 100, 500);
		foreach($limOpts as $oneOpt){
			$selected = '';
			if($limit==$oneOpt){$selected = ' selected';}
			$limOpt .= "<option$selected value=\"$oneOpt\">$oneOpt</option>";
		}
		
		$innerHTMLStr = "<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<input type=\"hidden\" name=\"page\" id=\"page\" value=\"$this->page\">
		<input type=\"hidden\" name=\"rowHeight\" id=\"rowHeight\" value=\"$this->rowHeight\">
		<input type=\"hidden\" name=\"totalTableRows\" id=\"totalTableRows\" value=\"$this->totalRows\">
		<div class=\"row\">
			<div class=\"col-xs-12 col-sm-6\">
				<h1 class=\"metatitle floatleft\">Sub-Group List <i class=\"fa fa-info-circle txt16normal\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Sub-Group List\"></i></h1>
				<button class=\"btn cursor hilightbutton p2x10 floatright\" onClick=\"AJgetSubGroupPopup(0);\" title=\"Create New Sub-Group\">
					<i class=\"fa fa-plus\"></i> Sub-Group
				</button>
			</div>				
			<div class=\"col-xs-12 col-sm-3\">
				<select name=\"faccount_type\" id=\"faccount_type\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$accTypOpt
				</select>
			</div>				
			<div class=\"col-xs-12 col-sm-3\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Search Information\" value=\"$keyword_search\" id=\"keyword_search\" name=\"keyword_search\" class=\"form-control\" maxlength=\"50\" />
					<span class=\"input-group-addon cursor\" onClick=\"checkAndLoadFilterData();\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Search Information\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"row\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
						<thead class=\"cf\">
							<tr>
								<th align=\"left\" width=\"5%\">SL#</th>
								<th align=\"left\" width=\"35%\">Group Name</th>
								<th align=\"left\">Sub-Group Name</th>
								<th align=\"left\" width=\"10%\">Action</th>
							</tr>
						</thead>
						<tbody id=\"tableRows\">
							$tableRows
						</tbody>
					</table>
				</div>
			</div>    
		</div>
		<div class=\"row mtop10\">
			<div class=\"col-xs-12\">
				<select class=\"form-control width100 floatleft\" name=\"limit\" id=\"limit\" onChange=\"checkloadTableRows();\">
					<option value=\"auto\">Auto</option>
					$limOpt
				</select>
				<label id=\"fromtodata\"></label>
				<div class=\"floatright\" id=\"Pagination\"></div>
			</div>
		</div>";
		
		$htmlStr = $this->htmlBody($innerHTMLStr);
		return $htmlStr;
	}
	
	public function AJgetSubGroupPopup(){
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$sub_group_id = intval(trim($_POST['sub_group_id']??0));
		$account_type = 0;
		$subGroupData = array();
		$subGroupData['login'] = '';
		if($prod_cat_man==0){$subGroupData['login'] = 'session_ended';}
		$subGroupData['sub_group_id'] = 0;
		$subGroupData['name'] = '';
			
		if($sub_group_id>0 && $prod_cat_man>0){
			$subGroObj = $this->db->query("SELECT * FROM sub_group WHERE sub_group_id = :sub_group_id AND accounts_id = $prod_cat_man", array('sub_group_id'=>$sub_group_id),1);
			if($subGroObj){
				$oneRow = $subGroObj->fetch(PDO::FETCH_OBJ);
				
				$subGroupData['sub_group_id'] = $oneRow->sub_group_id;
				$account_type = $oneRow->account_type;
				$subGroupData['name'] = stripslashes(trim($oneRow->name));
			}
		}
		$subGroupData['account_type'] = $account_type;
		$accTypOpt = "<option value=\"0\">Select Group Name</option>";
		$accountTypes = $this->accountTypes();
		foreach($accountTypes as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($account_type==$oneOptVal){$selected = ' selected';}
			$accTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		$subGroupData['accTypOpt'] = $accTypOpt;
		return json_encode($subGroupData);
	}
	
	public function AJsaveSubGroup(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$sub_group_id =0;
			$savemsg = '';
			$returnStr = 'Ok';
			$sub_group_id = $_POST['sub_group_id']??0;
			$account_type = $_POST['account_type']??0;
			$name = addslashes(trim($_POST['name']??''));
			$accountTypes = $this->accountTypes();
			$accountType = $accountTypes[$account_type];
			if(empty($name)){
				$savemsg = 'error';
				$returnStr = "<p>Name is missing</p>";
			}
			elseif($account_type==0){
				$savemsg = 'error';
				$returnStr = "<p>Account Type is missing</p>";
			}
			else{
				$saveData = array();
				$saveData['accounts_id'] = $prod_cat_man;
				$saveData['user_id'] = $user_id;
				$saveData['account_type'] = $account_type;
				$saveData['name'] = $name;
				
				$duplSql = "SELECT sub_group_publish, sub_group_id FROM sub_group WHERE accounts_id = $prod_cat_man AND name = :name AND account_type = :account_type";
				$bindData = array('name'=>$name, 'account_type'=>$account_type);
				if($sub_group_id>0){
					$duplSql .= " AND sub_group_id != :sub_group_id";
					$bindData['sub_group_id'] = $sub_group_id;
				}
				$duplSql .= " LIMIT 0, 1";
				$duplRows = 0;
				$subGroObj = $this->db->querypagination($duplSql, $bindData);
				if($subGroObj){
					foreach($subGroObj as $onerow){
						$duplRows = 1;
						$sub_group_publish = $onerow['sub_group_publish'];
						if($sub_group_id==0 && $sub_group_publish==0){
							$sub_group_id = $onerow['sub_group_id'];
							$this->db->update('sub_group', array('sub_group_publish'=>1), $sub_group_id);
							$duplRows = 0;
							$returnStr = 'Update';
						}
					}
				}
				
				if($duplRows>0){
					$savemsg = 'error';
					$returnStr = "<p>$accountType::$name already exists. Please try again different name or type.</p>";
				}
				else{
					if($sub_group_id==0){
						$saveData['created_on'] = date('Y-m-d H:i:s');
						$sub_group_id = $this->db->insert('sub_group', $saveData);
						if($sub_group_id){						
							$returnStr = 'Add';
						}
						else{
							$returnStr = 'Adding sub group';
						}
					}
					else{
						$prevName = $prevAccountType = '';
						$oneTRowObj = $this->db->querypagination("SELECT * FROM sub_group WHERE sub_group_id = $sub_group_id", array());
						if($oneTRowObj){
							$prevName = $onerow[0]['name'];
							$prevAccountType = $accountTypes[0][$onerow['account_type']];
						}
						
						$update = $this->db->update('sub_group', $saveData, $sub_group_id);
						if($update){
							$teData = $changed = $moreInfo = array();
							if($prevName != $name){
								$changed['name'] = array($prevName, $name);
							}					
							if($prevAccountType != $accountType){
								$changed['account_type'] = array($prevAccountType, $accountType);
							}					
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $accounts_id;
							$teData['user_id'] = $user_id;
							$teData['record_for'] = 'sub_group';
							$teData['record_id'] = $sub_group_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);
							
							$returnStr = 'Update';
						}
						elseif($returnStr == 'Ok'){
							$returnStr = 'No changes / Error occurred while updating data! Please try again.';
						}
					}
				}
			}
			
			$array = array('login'=>'', 'sub_group_id'=>$sub_group_id, 'savemsg'=>$savemsg, 'returnStr'=>$returnStr);
			return json_encode($array);		
		}
	}
	
	public function AJgetPage_subGroup(){
		$faccount_type = $_POST['faccount_type']??0;
		$keyword_search = $_POST['keyword_search']??'';
		$totalRows = $_POST['totalRows']??0;
		$rowHeight = $_POST['rowHeight']??34;
		$page = $_POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $_POST['limit']??'auto';
		
		$this->account_type = $faccount_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($GLOBALS['segment4name']=='filter'){
			$this->CountList = 'Count';
			$this->subGroupData();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$this->rowHeight = $rowHeight;
		$this->CountList = 'List';
		
		$jsonResponse['tableRows'] = $this->subGroupData();
		
		return json_encode($jsonResponse);
	}
	
	private function subGroupData(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$reponseData = array('login'=>'');
		if($prod_cat_man==0){$reponseData['login'] = 'session_ended';}
		$faccount_type = $this->account_type;
		$keyword_search = $this->keyword_search;
		$CountList = $this->CountList;
		
		$_SESSION["current_module"] = "subGroup";
		$_SESSION["list_filters"] = array('faccount_type'=>$faccount_type, 'keyword_search'=>$keyword_search);
		
		$filterSql = "FROM sub_group WHERE accounts_id = $prod_cat_man";
		$bindData = array();
		if($faccount_type >0){
			$filterSql .= " AND account_type = :account_type";
			$bindData['account_type'] = $faccount_type;
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim($keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND name LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim($keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql .= " AND sub_group_publish = 1";
		if($CountList == 'Count'){
			$sql = "SELECT COUNT(sub_group_id) AS totalrows $filterSql";
			$query = $this->db->query($sql, $bindData);
			if($query){
				$totalRows = $query->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			$this->totalRows = $totalRows;
		}
		else{
			$limit = $_SESSION["limit"];		
			$rowHeight = $this->rowHeight;
			$page = $this->page;
			$totalRows = $this->totalRows;
			if(in_array($limit, array('', 'auto'))){
				$screenHeight = $_COOKIE['screenHeight']??480;
				$headerHeight = $_COOKIE['headerHeight']??300;
				$bodyHeight = floor($screenHeight-$headerHeight);
				$limit = floor($bodyHeight/$rowHeight);
				if($limit<=0){$limit = 1;}
			}
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			
			$sql = "SELECT * $filterSql ORDER BY account_type ASC, name ASC LIMIT $starting_val, $limit";
			$dataObj = $this->db->querypagination($sql, $bindData);
			$tableRows = '';
			if($dataObj){
				$sl=$starting_val;
				$accountTypes = $this->accountTypes();
				foreach($dataObj as $oneRow){
					$sl++;
					$sub_group_id = $oneRow['sub_group_id'];
					$account_type = $accountTypes[$oneRow['account_type']];
					$editIcon = "<a href=\"javascript:void(0);\" onClick=\"AJgetSubGroupPopup($sub_group_id);\" title=\"Change this Account Information\"><i class=\"fa fa-edit txt18\"></i></a>";
					$editIcon .= " &emsp; <a href=\"javascript:void(0);\" onClick=\"AJremoveData('sub_group', $sub_group_id, '$oneRow[name]');\" title=\"Archive this Sub-Group\"><i class=\"fa fa-remove errormsg txt18\"></i></a>";
					
					$tableRows .= "<tr>
		<td data-title=\"SL#\" align=\"left\">$sl</td>
		<td data-title=\"Group Name\" align=\"left\">".stripslashes($account_type)."</td>
		<td data-title=\"Sub-Group Name\" align=\"left\">".stripslashes($oneRow['name'])."</td>
		<td data-title=\"Action\" align=\"center\">$editIcon</td>
		</tr>";
				}				
			}
			else{
				$tableRows .= "<tr>
		<td colspan=\"3\" data-title=\"No data found\">There is no data found</td>
		</tr>";
			}
			return $tableRows;
		}
	}
	
	public function setSubGroOpt($account_type=1, $callFunc = 1){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$account_type = intval(trim($_POST['account_type']??$account_type));
			$subGroupData = array();
			$subGroupData['login'] = '';
			$subGroOpt = "<option value=\"0\">Select Sub-Group Name</option>";
			if($account_type>0){
				$subGroOpts = $this->db->query("SELECT sub_group_id, name FROM sub_group WHERE accounts_id = $prod_cat_man AND account_type = :account_type AND sub_group_publish = 1 ORDER BY account_type ASC, name ASC", array('account_type'=>$account_type));
				if($subGroOpts){
					while($oneRow = $subGroOpts->fetch(PDO::FETCH_OBJ)){
						$oneOptVal = $oneRow->sub_group_id;
						$oneOptLabel = stripslashes(trim($oneRow->name));
						$subGroOpt .= "<option value=\"$oneOptVal\">$oneOptLabel</option>";
					}
				}
			}
			if($callFunc==1){
				return $subGroOpt;
			}
			else{
				$subGroupData['subGroOpt'] = $subGroOpt;
				
				return json_encode($subGroupData);
			}
		}
	}
	
	//===========Ledger==================//

	public function ledger(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$this->pageTitle = $GLOBALS['title'];
		$list_filters = $_SESSION['list_filters']??array();
		
		$factive_ledger = $list_filters['factive_ledger']??1;
		$faccount_type = $list_filters['faccount_type']??0;
		$fsub_group_id = $list_filters['fsub_group_id']??0;
		$fparent_ledger_id = $list_filters['fparent_ledger_id']??0;
		$fvisible_on = $list_filters['fvisible_on']??'';
		$keyword_search = $list_filters['keyword_search']??'';
				
		$this->active_ledger = $factive_ledger;
		$this->account_type = $faccount_type;
		$this->sub_group_id = $fsub_group_id;
		$this->parent_ledger_id = $fparent_ledger_id;
		$this->visible_on = $fvisible_on;
		$this->keyword_search = $keyword_search;
		$ledgerData = $this->ledgerData();
		
		$subGroOpt = "<option value=\"0\">All Sub-Group</option>";
		$parLedOpt = "<option value=\"0\">All Parent Ledger</option>";
		$tableRows = '';
		if(!empty($ledgerData) && $ledgerData['login']==''){
			$subGroOpt .= $ledgerData['subGroOpt']??'';
			$parLedOpt .= $ledgerData['parLedOpt']??'';
			$tableRows .= $ledgerData['tableRows']??'';			
		}
		
		$visOnOpt = "<option value=\"\">Visible All Voucher</option>";
		$voucherTypes = $this->voucherTypes();
		foreach($voucherTypes as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($fvisible_on==$oneOptVal){$selected = ' selected';}
			$visOnOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$accTypOpt = "<option value=\"0\">All Group</option>";
		$accountTypes = $this->accountTypes();
		foreach($accountTypes as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($faccount_type==$oneOptVal){$selected = ' selected';}
			$accTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$activeLedgers = array('1'=>'Active Ledger', '2'=>'In-Active Ledger', '3'=>'All Ledger');
		$accLedOpt = "";
		foreach($activeLedgers as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($factive_ledger==$oneOptVal){$selected = ' selected';}
			$accLedOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}		
		
		$innerHTMLStr = "<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<div class=\"row\">
			<div class=\"col-sm-12\">
				<h1 class=\"metatitle floatleft\">$GLOBALS[title] <i class=\"fa fa-info-circle txt16normal\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Manage Ledger Information\"></i></h1>
				<button class=\"floatright mleft10 pbottom10 btn cursor hilightbutton p2x10\" onClick=\"AJgetLedgerPopup(0, 0);\" title=\"Create New Ledger\">
					<i class=\"fa fa-plus\"></i> Ledger
				</button>
			</div>
		</div>
		<div class=\"row\">			
			<div class=\"col-sm-2\">
				<select name=\"factive_ledger\" id=\"factive_ledger\" class=\"form-control\" onChange=\"ledgerData();\">
					$accLedOpt
				</select>
			</div>
			<div class=\"col-sm-2 pleft0\">
				<select name=\"faccount_type\" id=\"faccount_type\" class=\"form-control\" onChange=\"ledgerData();\">
					$accTypOpt
				</select>
			</div>
			<div class=\"col-sm-2 pleft0\">
				<select name=\"fsub_group_id\" id=\"fsub_group_id\" class=\"form-control\" onChange=\"ledgerData();\">
					$subGroOpt
				</select>
			</div>
			<div class=\"col-sm-2 pleft0\">
				<select name=\"fparent_ledger_id\" id=\"fparent_ledger_id\" class=\"form-control\" onChange=\"ledgerData();\">
					$parLedOpt
				</select>
			</div>
			<div class=\"col-sm-2 pleft0\">
				<select name=\"fvisible_on\" id=\"fvisible_on\" class=\"form-control\" onChange=\"ledgerData();\">
					$visOnOpt
				</select>
			</div>
			<div class=\"col-sm-2 pleft0\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Search Ledger Name\" value=\"$keyword_search\" id=\"keyword_search\" name=\"keyword_search\" class=\"form-control\" maxlength=\"50\" />
					<span class=\"input-group-addon cursor\" onClick=\"ledgerData();\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Search Information\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"row mtop15\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
						<thead class=\"cf\">
							<tr>
							<th align=\"left\" width=\"5%\">Group Name</th>
							<th align=\"left\" width=\"5%\">Sub-Group Name</th>
							<th align=\"left\">Ledger Name</th>
							<th align=\"left\" width=\"20%\">Visible On</th>
							<th align=\"center\" width=\"5%\">Debit</th>
							<th align=\"center\" width=\"5%\">Credit</th>
							<th align=\"center\" width=\"5%\">Create Voucher</th>
							<th align=\"center\" width=\"5%\">Action</th>
							</tr>
						</thead>
						<tbody id=\"tableRows\">
							$tableRows
						</tbody>
					</table>
				</div>
			</div>    
		</div>";
		
		$htmlStr = $this->htmlBody($innerHTMLStr);
		return $htmlStr;
	}
	
	public function AJgetPage_ledger(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			
			$factive_ledger = $_POST['factive_ledger']??1;
			$faccount_type = $_POST['faccount_type']??0;
			$fsub_group_id = $_POST['fsub_group_id']??0;
			$fparent_ledger_id = $_POST['fparent_ledger_id']??0;
			$fvisible_on = $_POST['fvisible_on']??'';
			$keyword_search = $_POST['keyword_search']??'';
					
			$this->active_ledger = $factive_ledger;
			$this->account_type = $faccount_type;
			$this->sub_group_id = $fsub_group_id;
			$this->parent_ledger_id = $fparent_ledger_id;
			$this->visible_on = $fvisible_on;
			$this->keyword_search = $keyword_search;
			$ledgerData = $this->ledgerData();
			
			$subGroOpt = "<option value=\"0\">All Sub-Group</option>";
			$parLedOpt = "<option value=\"0\">All Parent Ledger</option>";
			$tableRows = '';
			if(!empty($ledgerData)){
				$subGroOpt .= $ledgerData['subGroOpt']??'';
				$parLedOpt .= $ledgerData['parLedOpt']??'';
				$tableRows .= $ledgerData['tableRows']??'';			
			}
			$jsonResponse['subGroOpt'] = $subGroOpt;
			$jsonResponse['parLedOpt'] = $parLedOpt;
			$jsonResponse['tableRows'] = $tableRows;
			
			return json_encode($jsonResponse);
		}
	}
	
	function ledgerData(){
		$reponseData = array('login'=>'');
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			$reponseData['login'] = 'session_ended';
		}

		$factive_ledger = $this->active_ledger;
		$faccount_type = $this->account_type;
		$fsub_group_id = $this->sub_group_id;
		$fparent_ledger_id = $this->parent_ledger_id;
		$fvisible_on = $this->visible_on;
		$keyword_search = $this->keyword_search;
		
		$filterSql = array();
		if($faccount_type >0){$filterSql[] = "account_type = $faccount_type";}		
		if($fsub_group_id >0){$filterSql[] = "sub_group_id = $fsub_group_id";}		
		if($fparent_ledger_id >0){$filterSql[] = "(ledger_id = $fparent_ledger_id OR parent_ledger_id = $fparent_ledger_id)";}		
		if(!empty($fvisible_on)){$filterSql[] = "visible_on LIKE '%$fvisible_on%'";}
		if($factive_ledger <3){
			if($factive_ledger == 1){
				$filterSql[] = "(closing_date = 0 OR closing_date>".time().")";
			}
			else{
				$filterSql[] = "(closing_date > 0 AND closing_date <".time().")";
			}
		}
		
		if($keyword_search !=''){$filterSql[] = "name LIKE '%$keyword_search%'";}
		$select = '*';
		if(!empty($filterSql)){
			$select = 'ledger_id, sub_group_id, parent_ledger_id, parent_sub_ledger_id, parent_sub2_ledger_id, ledger_count';
			$filterSql = "WHERE ".implode(' AND ', $filterSql);
		}
		
		$subGroOpt = "";
		$parLedOpt = "";
		$sql = "SELECT $select FROM ledger $filterSql ORDER BY parent_ledger_id ASC, parent_sub_ledger_id ASC, parent_sub2_ledger_id ASC, name ASC";
		$dataObj = $dataObj = $this->db->query($sql, array());
		$tableRows = '';
		if($dataObj){
			$accountTypes = $this->accountTypes();
			$debitCredits = $this->debitCredits();
			$loadPage = 'Accounts/ledger';
			$editPer = $hidePer = 1;
			
			$allLedIds = $subGroIds = $ledIdInfos = array();
			$checkLedIds = array();
			while($oneRow = $dataObj->fetch(PDO::FETCH_OBJ)){
				$checkLedIds[$oneRow->ledger_id] = '';				
				$sub_group_id = $oneRow->sub_group_id;
				$subGroIds[$sub_group_id] = '';

				$parent_ledger_id = $oneRow->parent_ledger_id;
				$parent_sub_ledger_id = $oneRow->parent_sub_ledger_id;
				$parent_sub2_ledger_id = $oneRow->parent_sub2_ledger_id;
				$ledger_id = $oneRow->ledger_id;				
				if($parent_ledger_id==0){
					$parent_ledger_id = $ledger_id;
					$parent_sub_ledger_id = 0;
					$parent_sub2_ledger_id = 0;
					$ledger_id = 0;
				}
				else if($parent_sub_ledger_id==0){
					$parent_sub_ledger_id = $ledger_id;
					$parent_sub2_ledger_id = 0;
					$ledger_id = 0;
				}
				else if($parent_sub2_ledger_id==0){
					$parent_sub2_ledger_id = $ledger_id;
					$ledger_id = 0;
				}
				if(!empty($filterSql)){
					$ledIdInfos[$parent_ledger_id] = array();
					$ledIdInfos[$parent_sub_ledger_id] = array();
					$ledIdInfos[$parent_sub2_ledger_id] = array();
					$ledIdInfos[$ledger_id] = array();

					$allLedIds[$parent_ledger_id][$parent_sub_ledger_id][$parent_sub2_ledger_id][$ledger_id] = '';
				}
				else{
					$allLedIds[$parent_ledger_id][$parent_sub_ledger_id][$parent_sub2_ledger_id][$ledger_id] = array($oneRow->account_type, $sub_group_id, stripslashes(trim($oneRow->name)), $oneRow->visible_on, $oneRow->debit, $oneRow->credit, $oneRow->ledger_publish, $oneRow->closing_date, $oneRow->ledger_count);
				}
			}			
			
			if(!empty($subGroIds)){	
				$tableObj = $this->db->query("SELECT sub_group_id, name FROM sub_group WHERE sub_group_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						if(array_key_exists($oneRow->sub_group_id, $subGroIds)){
							$subGroOpt .= "<option value=\"$oneRow->sub_group_id\">".stripslashes(trim($oneRow->name))."</option>";
						}
						$subGroIds[$oneRow->sub_group_id] = stripslashes(trim($oneRow->name));
					}
				}
			
			}
			if(!empty($filterSql)){
				//$tableRows .= json_encode($ledIdInfos);
				if(!empty($ledIdInfos)){
					$tableObj = $this->db->query("SELECT * FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($ledIdInfos)).") ORDER BY name ASC", array());
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$ledIdInfos[$oneRow->ledger_id] = $oneRow;
						}
					}							
				}
				$parLedIds = array();
				if(!empty($allLedIds)){
					foreach($allLedIds as $sparent_ledger_id=>$parMoreRows){
						$ledgerOneRow = $ledIdInfos[$sparent_ledger_id]??array();
						if(!empty($ledgerOneRow)){
							$ssubGroupName = $subGroIds[$ledgerOneRow->sub_group_id]??'';
							$sname = stripslashes(trim($ledgerOneRow->name));
							$parLedOpt .= "<option value=\"$sparent_ledger_id\">$sname</option>";
							$parLedIds[$sparent_ledger_id] = $accountTypes[$ledgerOneRow->account_type]." || $ssubGroupName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->credit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date || $ledgerOneRow->ledger_count";
						}
					}			
				}
				
				if(!empty($parLedIds)){
					asort($parLedIds);
					$a = 0;
					foreach($parLedIds as $sparent_ledger_id=>$oneParLedInfo){
						$parLedInfo = $allLedIds[$sparent_ledger_id];
						$a++;
						$oneLedInfo1 = explode(' || ', $oneParLedInfo);								
						$arrow = 0;
						$tableRows .= $this->ledOneRowHTML("$a. ".$oneLedInfo1[0], $oneLedInfo1[1], $oneLedInfo1[2], $oneLedInfo1[3],  $debitCredits[$oneLedInfo1[4]], $debitCredits[$oneLedInfo1[5]], $oneLedInfo1[6], $oneLedInfo1[7], $editPer, $hidePer, $arrow, $sparent_ledger_id, $oneLedInfo1[8]);
						
						if(!empty($parLedInfo)){
							$parSubLedIds = array();
							foreach($parLedInfo as $sparent_sub_ledger_id=>$moreRows){
								$ledgerOneRow = $ledIdInfos[$sparent_sub_ledger_id]??array();
								if(!empty($ledgerOneRow)){
									$ssubGroupName = $subGroIds[$ledgerOneRow->sub_group_id]??'';
									$sname = stripslashes(trim($ledgerOneRow->name));
									$parSubLedIds[$sparent_sub_ledger_id] = $accountTypes[$ledgerOneRow->account_type]." || $ssubGroupName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->credit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date || $ledgerOneRow->ledger_count";
								}
							}
							asort($parSubLedIds);
							foreach($parSubLedIds as $sparent_sub_ledger_id=>$oneParSubLedInfo){
								$parSubLedInfo = $parLedInfo[$sparent_sub_ledger_id];
								$a++;								
								$arrow = 1;
								$oneLedInfo2 = explode(' || ', $oneParSubLedInfo);
								$tableRows .= $this->ledOneRowHTML("$a. ".$oneLedInfo2[0], $oneLedInfo2[1], $oneLedInfo2[2], $oneLedInfo2[3],  $debitCredits[$oneLedInfo2[4]], $debitCredits[$oneLedInfo2[5]], $oneLedInfo2[6], $oneLedInfo2[7], $editPer, $hidePer, $arrow, $sparent_sub_ledger_id, $oneLedInfo2[8]);
								if($oneLedInfo1[0] != $oneLedInfo2[0]){$tableRows .= "|$sparent_sub_ledger_id|";}
								if(!empty($parSubLedInfo)){
									$parSub2LedIds = array();
									foreach($parSubLedInfo as $parent_sub2_ledger_id=>$moreRows){
										$ledgerOneRow = $ledIdInfos[$parent_sub2_ledger_id]??array();
										if(!empty($ledgerOneRow)){
											$ssubGroupName = $subGroIds[$ledgerOneRow->sub_group_id]??'';
											$sname = stripslashes(trim($ledgerOneRow->name));
											$parSub2LedIds[$parent_sub2_ledger_id] = $accountTypes[$ledgerOneRow->account_type]." || $ssubGroupName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->credit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date || $ledgerOneRow->ledger_count";
										}
									}
									asort($parSub2LedIds);
									foreach($parSub2LedIds as $sparent_sub2_ledger_id=>$oneParSub2LedInfo){
										$parSub2LedInfo = $parSubLedInfo[$sparent_sub2_ledger_id];
										$a++;
										$arrow = 2;
										$oneLedInfo3 = explode(' || ', $oneParSub2LedInfo);
										$tableRows .= $this->ledOneRowHTML("$a. ".$oneLedInfo3[0], $oneLedInfo3[1], $oneLedInfo3[2], $oneLedInfo3[3],  $debitCredits[$oneLedInfo3[4]], $debitCredits[$oneLedInfo3[5]], $oneLedInfo3[6], $oneLedInfo3[7], $editPer, $hidePer, $arrow, $sparent_sub2_ledger_id, $oneLedInfo3[8]);
										if($oneLedInfo1[0] != $oneLedInfo3[0] || $oneLedInfo3[0] != $oneLedInfo2[0]){$tableRows .= "|$sparent_sub2_ledger_id|";}
										if(!empty($parSub2LedInfo)){													
											$sledgerIds = array();
											foreach($parSub2LedInfo as $sledger_id=>$oneLedInfo){
												$ledgerOneRow = $ledIdInfos[$sledger_id]??array();
												if(!empty($ledgerOneRow)){
													$ssubGroupName = $subGroIds[$ledgerOneRow->sub_group_id]??'';
													$sname = stripslashes(trim($ledgerOneRow->name));
													$sledgerIds[$sledger_id] = $accountTypes[$ledgerOneRow->account_type]." || $ssubGroupName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->credit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date || $ledgerOneRow->ledger_count";
												}
											}
											asort($sledgerIds);
											foreach($sledgerIds as $sledger_id=>$oneLedInfo){
												$a++;
												$arrow = 3;
												$oneLedInfo4 = explode(' || ', $oneLedInfo);
												if($oneLedInfo1[0] != $oneLedInfo4[0] || $oneLedInfo2[0] != $oneLedInfo4[0] || $oneLedInfo3[0] != $oneLedInfo4[0]){$tableRows .= "|$sledger_id|";}
												$tableRows .= $this->ledOneRowHTML("$a. ".$oneLedInfo4[0], $oneLedInfo4[1], $oneLedInfo4[2], $oneLedInfo4[3],  $debitCredits[$oneLedInfo4[4]], $debitCredits[$oneLedInfo4[5]], $oneLedInfo4[6], $oneLedInfo4[7], $editPer, $hidePer, $arrow, $sledger_id, $oneLedInfo4[8]);
											}
										}
									}
								}
							}
						}								
					}
				}
			}
			else{
				$parLedIds = array();
				if(!empty($allLedIds)){
					foreach($allLedIds as $parent_ledger_id=>$moreRows){
						if(array_key_exists('0', $moreRows)){
							$oneLedInfo = $moreRows[0][0][0];
							$accountType = $accountTypes[$oneLedInfo[0]];
							$subGroupName = $subGroIds[$oneLedInfo[1]];
							$name = $oneLedInfo[2];
							$parLedIds[$parent_ledger_id] = "$accountType || $subGroupName || $name";
							$parLedOpt .= "<option value=\"$parent_ledger_id\">$name</option>";
						}
					}			
				}
				
				if(!empty($parLedIds)){
					asort($parLedIds);
					$a = 0;
					foreach($parLedIds as $parent_ledger_id=>$parLedName){
						$parLedInfo = $allLedIds[$parent_ledger_id];
						$a++;
						$oneLedInfo = $parLedInfo[0][0][0];
						$accountType = $accountTypes[$oneLedInfo[0]];
						$subGroupName = $subGroIds[$oneLedInfo[1]];
						$name = $oneLedInfo[2];
						$visible_on = $oneLedInfo[3];
						$debit = $debitCredits[$oneLedInfo[4]];
						$credit = $debitCredits[$oneLedInfo[5]];
						$ledger_publish = $oneLedInfo[6];
						$closing_date = $oneLedInfo[7];
						$ledger_count = $oneLedInfo[8];
						$arrow = 0;
						$ledger_id = $parent_ledger_id;
						if(array_key_exists($ledger_id, $checkLedIds)){unset($checkLedIds[$ledger_id]);}
						
						$tableRows .= $this->ledOneRowHTML("$a. ".$accountType, $subGroupName, $name, $visible_on, $debit, $credit, $ledger_publish, $closing_date, $editPer, $hidePer, $arrow, $ledger_id, $ledger_count);

						if(array_key_exists('0', $parLedInfo)){unset($parLedInfo[0]);}
						
						if(!empty($parLedInfo)){
							$parSubLedIds = array();
							foreach($parLedInfo as $parent_sub_ledger_id=>$moreRows){
								if(array_key_exists('0', $moreRows)){
									$oneLedInfo = $moreRows[0][0];
									$accountType = $accountTypes[$oneLedInfo[0]];
									$subGroupName = $subGroIds[$oneLedInfo[1]];
									$name = $oneLedInfo[2];
									$parSubLedIds[$parent_sub_ledger_id] = "$accountType || $subGroupName || $name";
								}
							}
							asort($parSubLedIds);
							foreach($parSubLedIds as $parent_sub_ledger_id=>$parSubLedName){
								$parSubLedInfo = $parLedInfo[$parent_sub_ledger_id];
								$a++;
								$oneLedInfo = $parSubLedInfo[0][0];
								$accountType = $accountTypes[$oneLedInfo[0]];
								$subGroupName = $subGroIds[$oneLedInfo[1]];
								$name = $oneLedInfo[2];
								$visible_on = $oneLedInfo[3];
								$debit = $debitCredits[$oneLedInfo[4]];
								$credit = $debitCredits[$oneLedInfo[5]];
								$ledger_publish = $oneLedInfo[6];
								$closing_date = $oneLedInfo[7];
								$ledger_count = $oneLedInfo[8];
								$arrow = 1;
								$ledger_id = $parent_sub_ledger_id;
								if(array_key_exists($ledger_id, $checkLedIds)){unset($checkLedIds[$ledger_id]);}

								$tableRows .= $this->ledOneRowHTML("$a. ".$accountType, $subGroupName, $name, $visible_on, $debit, $credit, $ledger_publish, $closing_date, $editPer, $hidePer, $arrow, $ledger_id, $ledger_count);
								
								if(array_key_exists('0', $parSubLedInfo)){unset($parSubLedInfo[0]);}

								if(!empty($parSubLedInfo)){
									$parSub2LedIds = array();
									foreach($parSubLedInfo as $parent_sub2_ledger_id=>$moreRows){
										if(array_key_exists('0', $moreRows)){
											$oneLedInfo = $moreRows[0][0];
											$accountType = $accountTypes[$oneLedInfo[0]];
											$subGroupName = $subGroIds[$oneLedInfo[1]];
											$name = $oneLedInfo[2];
											$parSub2LedIds[$parent_sub2_ledger_id] = "$accountType || $subGroupName || $name";
										}
									}
									asort($parSub2LedIds);
									foreach($parSub2LedIds as $parent_sub2_ledger_id=>$parSub2LedName){
										$parSub2LedInfo = $parSubLedInfo[$parent_sub2_ledger_id];
										$a++;
										$oneLedInfo = $parSub2LedInfo[0];
										$accountType = $accountTypes[$oneLedInfo[0]];
										$subGroupName = $subGroIds[$oneLedInfo[1]];
										$name = $oneLedInfo[2];
										$visible_on = $oneLedInfo[3];
										$debit = $debitCredits[$oneLedInfo[4]];
										$credit = $debitCredits[$oneLedInfo[5]];
										$ledger_publish = $oneLedInfo[6];
										$closing_date = $oneLedInfo[7];
										$ledger_count = $oneLedInfo[8];
										$arrow = 2;
										$ledger_id = $parent_sub2_ledger_id;
										if(array_key_exists($ledger_id, $checkLedIds)){unset($checkLedIds[$ledger_id]);}

										$tableRows .= $this->ledOneRowHTML("$a. ".$accountType, $subGroupName, $name, $visible_on, $debit, $credit, $ledger_publish, $closing_date, $editPer, $hidePer, $arrow, $ledger_id, $ledger_count);
										
										if(array_key_exists('0', $parSub2LedInfo)){unset($parSub2LedInfo[0]);}

										if(!empty($parSub2LedInfo)){
											
											$ledgerIds = array();
											foreach($parSub2LedInfo as $ledger_id=>$oneLedInfo){
												$accountType = $accountTypes[$oneLedInfo[0]];
												$subGroupName = $subGroIds[$oneLedInfo[1]];
												$name = $oneLedInfo[2];
												$ledgerIds[$ledger_id] = "$accountType || $subGroupName || $name";
											}
											asort($ledgerIds);
											foreach($ledgerIds as $ledger_id=>$LedName){
												$oneLedInfo = $parSub2LedInfo[$ledger_id];
												$a++;
												$accountType = $accountTypes[$oneLedInfo[0]];
												$subGroupName = $subGroIds[$oneLedInfo[1]];
												$name = $oneLedInfo[2];
												$visible_on = $oneLedInfo[3];
												$debit = $debitCredits[$oneLedInfo[4]];
												$credit = $debitCredits[$oneLedInfo[5]];
												$ledger_publish = $oneLedInfo[6];
												$closing_date = $oneLedInfo[7];
												$ledger_count = $oneLedInfo[8];
												$arrow = 3;
												if(array_key_exists($ledger_id, $checkLedIds)){unset($checkLedIds[$ledger_id]);}
												$tableRows .= $this->ledOneRowHTML("$a. ".$accountType, $subGroupName, $name, $visible_on, $debit, $credit, $ledger_publish, $closing_date, $editPer, $hidePer, $arrow, $ledger_id, $ledger_count);
												
											}
										}
									}
								}
							}
						}
					}
				}
			}
			//$tableRows = json_encode($checkLedIds).$tableRows;
		}
		else{
			$tableRows .= "<tr>
			<td colspan=\"10\" data-title=\"No data found\">There is no data found</td>
			</tr>";
		}
		
		$reponseData['subGroOpt'] = $subGroOpt;
		$reponseData['parLedOpt'] = $parLedOpt;
		$reponseData['tableRows'] = $tableRows;
		
		return $reponseData;
	}
	
	function ledOneRowHTML($accountType, $subGroupName, $name, $visible_on, $debit, $credit, $ledger_publish, $closing_date, $editPer, $hidePer, $arrow, $ledger_id, $ledger_count=0){
		
		$voucherTypes = $this->voucherTypes();
		$arrowStr = '';
		if($arrow>0){
			for($l=0; $l<$arrow; $l++){
				$arrowStr .= '&emsp;';
			}
		}
		
		if(empty($arrowStr))
			$arrowStr = '<img src="/assets/images/Accounts/firstarrow.png" alt="Parent">&nbsp ';
		else
			$arrowStr .= '<img src="/assets/images/Accounts/sontabarrow.png" alt="Parent">&nbsp ';

		$visible_onStr = 'All Voucher';
		if(!empty($visible_on)){
			$visibleOns = array_flip(explode(',', $visible_on));
			foreach($visibleOns as $key=>$value){
				if($key>0 && array_key_exists($key, $voucherTypes)){
					$visibleOns[$key] = $voucherTypes[$key].' Voucher';
				}
				else{
					unset($visibleOns[$key]);
				}
			}
			if(!empty($visibleOns)){
				$visible_onStr = implode(', ', $visibleOns);
			}
		}
		
		$cls = '';
		if($ledger_publish==2){$cls = ' class="lightyellowrow"';}
		$editIcon = "<a href=\"/Accounts/ledgerView/$ledger_id\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle txt18\"></i></a>";
		if($editPer==1){
			$editIcon .= " &nbsp; <a href=\"javascript:void(0);\" onClick=\"AJgetLedgerPopup(0, $ledger_id);\" title=\"Change this Account Information\"><i class=\"fa fa-edit txt18\"></i></a>";
		}
		if($hidePer==1){
			$editIcon .= " &nbsp; <a href=\"javascript:void(0);\" onClick=\"AJarchive_Popup('ledger', $ledger_id, '".strip_tags($name)."', $ledger_publish);\" title=\"";
			if($ledger_publish==2){
				$editIcon .= "Active this Ledger\"><i class=\"fa fa-arrow-circle-left errormsg";
			}
			else{
				$editIcon .= "Archive this Ledger\"><i class=\"fa fa-remove";
			}
			$editIcon .= " txt18\"></i></a>";
		}
		if($closing_date>0 && $closing_date<strtotime(date('Y-m-d'))){$cls = ' class="lightpinkrow"';}
		
		$ledger_countStr = 'Yes';
		if($ledger_count>0){$ledger_countStr = 'No';}
		return "<tr$cls>
		<td nowrap data-title=\"Group Name\" align=\"left\">$accountType</td>
		<td nowrap data-title=\"Sub-Group Name\" align=\"left\">$subGroupName</td>
		<td data-title=\"Ledger Name\" align=\"left\">$arrowStr$name ($ledger_id)</td>
		<td data-title=\"Visible On\" align=\"left\">$visible_onStr</td>
		<td nowrap data-title=\"Debit\" align=\"center\">$debit</td>
		<td nowrap data-title=\"Credit\" align=\"center\">$credit</td>
		<td nowrap data-title=\"Create Voucher\" align=\"center\">$ledger_countStr</td>
		<td nowrap data-title=\"Action\" align=\"center\" nowrap>$editIcon</td>
		</tr>";
	}
	
	function AJgetLedgerPopup(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$ledgerData = $visible_on = array();
			$ledger_id = intval(trim($_POST['ledger_id']??0));
			$sparent_ledger_id = intval(trim($_POST['sparent_ledger_id']??0));
			$sparObj = false;
			$account_type = $sub_group_id = $parent_ledger_id = $parent_sub_ledger_id = $parent_sub2_ledger_id = 0;
			if($sparent_ledger_id>0){
				$sparObj = $this->getOneRowById('ledger', $sparent_ledger_id);
				if($sparObj){
					$account_type = $sparObj->account_type;
					$sub_group_id = $sparObj->sub_group_id;
					if($sparObj->parent_ledger_id>0){						
						$parent_ledger_id = $sparObj->parent_ledger_id;
						if($sparObj->parent_sub_ledger_id>0){
							$parent_sub_ledger_id = $sparObj->parent_sub_ledger_id;
							if($sparObj->parent_sub2_ledger_id>0){
								$parent_sub2_ledger_id = $sparObj->parent_sub2_ledger_id;						
							}
							else{$parent_sub2_ledger_id = $sparObj->ledger_id;}
						}
						else{$parent_sub_ledger_id = $sparObj->ledger_id;}
					}
					else{$parent_ledger_id = $sparObj->ledger_id;}					
				}
			}
			$debit = -1;
			$credit = 1;
			$ledgerData['login'] = '';
			$ledgerData['name'] = '';
			$ledgerData['opening_balance'] = 0;
			$ledgerData['opening_date'] = '';
			$ledgerData['closing_date'] = '';
			
			if($ledger_id>0){
				$oneRow = $this->getOneRowById('ledger', $ledger_id);
				if($oneRow){
					$ledger_id = $oneRow->ledger_id;
					$account_type = $oneRow->account_type;
					$sub_group_id = $oneRow->sub_group_id;
					$parent_ledger_id = $oneRow->parent_ledger_id;
					$parent_sub_ledger_id = $oneRow->parent_sub_ledger_id;
					$parent_sub2_ledger_id = $oneRow->parent_sub2_ledger_id;
					$ledgerData['name'] = stripslashes(trim($oneRow->name));
					$debit = $oneRow->debit;
					$credit = $oneRow->credit;
					if(!empty($oneRow->visible_on)){
						$visible_on = explode(',', $oneRow->visible_on);
					}
					$ledgerData['opening_balance'] = $oneRow->opening_balance;
					if($oneRow->opening_date>0){
						$ledgerData['opening_date'] = date('Y-m-d', $oneRow->opening_date);
					}
					if($oneRow->closing_date>0){
						$ledgerData['closing_date'] = date('Y-m-d', $oneRow->closing_date);
					}
				}
				else{
					$ledger_id = 0;
				}
			}
			
			$ledgerData['ledger_id'] = $ledger_id;
			$accTypOpt = "<option value=\"0\">Select Group Name</option>";
			$accTypOpts = $this->accountTypes();
			foreach($accTypOpts as $oneOptVal=>$oneOptLabel){
				$accTypOpt .= "<option value=\"$oneOptVal\">$oneOptLabel</option>";
			}
			$ledgerData['accTypOpt'] = $accTypOpt;
			$ledgerData['account_type'] = $account_type;
						
			$ledgerData['subGroOpt'] = $this->setSubGroOpt($account_type, 1);
			$ledgerData['sub_group_id'] = $sub_group_id;
			
			$ledgerData['parLedOpt'] = $this->setParLedOpt($sub_group_id);
			$ledgerData['parent_ledger_id'] = $parent_ledger_id;
			
			$ledgerData['parSubLedOpt'] = $this->setParSubLedOpt($parent_ledger_id);
			$ledgerData['parent_sub_ledger_id'] = $parent_sub_ledger_id;
			
			$ledgerData['parSub2LedOpt'] = $this->setParSub2LedOpt($parent_sub_ledger_id);
			$ledgerData['parent_sub2_ledger_id'] = $parent_sub2_ledger_id;
			
			$visOnOpt = "<div class=\"row\">";
			$voucherTypes = $this->voucherTypes();
			foreach($voucherTypes as $oneOptVal=>$oneOptLabel){
				$checked = '';
				if(in_array($oneOptVal, $visible_on)){$checked = ' checked';}
				$visOnOpt .= "<div class=\"col-md-6\"><label class=\"cursor\" style=\"font-weight:normal\"><input name=\"visible_on[]\" type=\"checkbox\"$checked value=\"$oneOptVal\"> $oneOptLabel</lable></div>";
			}
			$visOnOpt .= "</div>";
			$ledgerData['visOnOpt'] = $visOnOpt;
			$ledgerData['debit'] = $debit;
			$ledgerData['credit'] = $credit;
			
			return json_encode($ledgerData);
		}		
	}
	
	function setParLedOpt($sub_group_id, $post=0){
		$parLedOpt = "<option value=\"0\">Parent Ledger</option>";
		if($sub_group_id>0){
			$sql = "SELECT ledger_id, name FROM ledger WHERE sub_group_id = $sub_group_id AND parent_ledger_id = 0 AND parent_sub_ledger_id = 0 AND ledger_publish = 1 ORDER BY name ASC";
			$tableObj = $this->db->query($sql, array());
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$parLedOpt .= "<option value=\"$oneRow->ledger_id\">".stripslashes(trim($oneRow->name))."</option>";
				}
			}
		}
		if($post ==1){
			return $parLedOpt;
		}
		else{
			return $parLedOpt;
		}
	}
	
	function setParSub2LedOpt($parent_sub_ledger_id, $post=0){
		$parSub2LedOpt = "<option value=\"0\">Sub-Parent Ledger</option>";
		if($parent_sub_ledger_id>0){
			$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE parent_sub_ledger_id = $parent_sub_ledger_id AND parent_sub2_ledger_id = 0 AND ledger_publish = 1 ORDER BY name ASC", array());
			if($tableObj){
				foreach($tableObj as $oneRow){
					$parSub2LedOpt .= "<option value=\"$oneRow->ledger_id\">".stripslashes(trim($oneRow->name))."</option>";
				}
			}
		}
		if($post ==1){
			return $parSub2LedOpt;
		}
		else{
			return $parSub2LedOpt;
		}
	}

	function setParSubLedOpt($parent_ledger_id, $post=0){
		$parSubLedOpt = "<option value=\"0\">Sub-Parent Ledger</option>";
		if($parent_ledger_id>0){
			$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE parent_ledger_id = $parent_ledger_id AND parent_sub_ledger_id = 0 AND ledger_publish = 1 ORDER BY name ASC", array());
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$parSubLedOpt .= "<option value=\"$oneRow->ledger_id\">".stripslashes(trim($oneRow->name))."</option>";
				}
			}
		}
		if($post ==1){
			return $parSubLedOpt;
		}
		else{
			return $parSubLedOpt;
		}
	}

	public function AJgetLedgerPopup1220(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$ledger_id = intval(trim($_POST['ledger_id']??0));
			$sparent_ledger_id = intval(trim($_POST['sparent_ledger_id']??0));
			$sParOneRow = array();
			if($sparent_ledger_id>0){
				$sParOneRow = $this->getOneRowById('ledger', $sparent_ledger_id);
			}
			$account_type = $sub_group_id = $parent_ledger_id = 0;
			$debit = -1;
			$credit = 1;
			$ledgerData = $visible_on = array();
			$ledgerData['login'] = '';
			$ledgerData['name'] = '';
			$ledgerData['opening_balance'] = 0;
			$ledgerData['opening_date'] = '';
			$ledgerData['closing_date'] = '';
			
			if($ledger_id>0){
				$oneRow = $this->getOneRowById('ledger', $ledger_id);
				if(!empty($oneRow)){
					$ledger_id = $oneRow->ledger_id;
					$account_type = $oneRow->account_type;
					$sub_group_id = $oneRow->sub_group_id;
					$parent_ledger_id = $oneRow->parent_ledger_id;
					$ledgerData['name'] = stripslashes(trim($oneRow->name));
					$debit = $oneRow->debit;
					$credit = $oneRow->credit;
					if(!empty($oneRow->visible_on)){
						$visible_on = explode(',', $oneRow->visible_on);
					}
					$ledgerData['opening_balance'] = $oneRow->opening_balance;
					if($oneRow->opening_date>0){
						$ledgerData['opening_date'] = date('Y-m-d', $oneRow->opening_date);
					}
					if($oneRow->closing_date>0){
						$ledgerData['closing_date'] = date('Y-m-d', $oneRow->closing_date);
					}
				}
				else{
					$ledger_id = 0;
				}
			}
			
			$ledgerData['ledger_id'] = $ledger_id;
			$ledgerData['account_type'] = $account_type;
			$ledgerData['sub_group_id'] = $sub_group_id;
			$ledgerData['parent_ledger_id'] = $parent_ledger_id;
			
			if(!empty($sParOneRow)){
				$accTypOpts = $this->accountTypes();
				$accountType = $accTypOpts[$sParOneRow->account_type];
				$accTypOpt = "<option value=\"$sParOneRow->account_type\">$accountType</option>";
			}
			else{
				$accTypOpt = "<option value=\"0\">Select Group Name</option>";
				$accTypOpts = $this->accountTypes();
				foreach($accTypOpts as $oneOptVal=>$oneOptLabel){
					$selected = '';
					if($account_type==$oneOptVal){$selected = ' selected';}
					$accTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
				}
			}
			$ledgerData['accTypOpt'] = $accTypOpt;
			if($sParOneRow){
				$sub_group_id = $sParOneRow->sub_group_id;
				$subGroName = $this->getOneFieldById('sub_group', $sub_group_id, 'name');
				$subGroOpt = "<option value=\"$sub_group_id\">$subGroName</option>";
			}
			else{
				$subGroOpt = "<option value=\"0\">Select Sub-Group Name</option>";
				if($account_type>0){
					$subGroOpts = $this->db->query("SELECT sub_group_id, name FROM sub_group WHERE account_type = $account_type AND sub_group_publish = 1 ORDER BY account_type ASC, name ASC", array());
					if($subGroOpts){
						while($oneRow = $subGroOpts->fetch(PDO::FETCH_OBJ)){
							$oneOptVal = $oneRow->sub_group_id;
							$oneOptLabel = $oneRow->name;
							$selected = '';
							if($sub_group_id==$oneOptVal){$selected = ' selected';}
							$subGroOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
						}
					}
				}
			}
			$ledgerData['subGroOpt'] = $subGroOpt;
			
			$parLedOpt = "<option value=\"0\">Parent Ledger</option>";
			$sqlPL = "SELECT ledger_id, name FROM ledger WHERE";
			if($sparent_ledger_id>0){
				$sqlPL .= " ledger_id = $sparent_ledger_id AND";
			}
			elseif($ledger_id>0){
				$sqlPL .= " account_type = $account_type AND sub_group_id = $sub_group_id AND";
			}
			$sqlPL .= " ledger_publish = 1 ORDER BY name ASC";
			$parLedOpts = $this->db->query($sqlPL, array());
			if($parLedOpts){
				if($sparent_ledger_id>0){$parLedOpt = "";}
				while($oneRow = $parLedOpts->fetch(PDO::FETCH_OBJ)){
					$oneOptVal = $oneRow->ledger_id;
					$oneOptLabel = $oneRow->name;
					$selected = '';
					if($parent_ledger_id==$oneOptVal){$selected = ' selected';}
					$parLedOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
				}
			}
			$ledgerData['parLedOpt'] = $parLedOpt;
			
			$visOnOpt = "<div class=\"row\">";
			$voucherTypes = $this->voucherTypes();
			foreach($voucherTypes as $oneOptVal=>$oneOptLabel){
				$checked = '';
				if(in_array($oneOptVal, $visible_on)){$checked = ' checked';}
				$visOnOpt .= "<div class=\"col-md-6\"><label class=\"cursor\" style=\"font-weight:normal\"><input name=\"visible_on[]\" type=\"checkbox\"$checked value=\"$oneOptVal\"> $oneOptLabel</lable></div>";
			}
			$visOnOpt .= "</div>";
			$ledgerData['visOnOpt'] = $visOnOpt;
			$ledgerData['debit'] = $debit;
			$ledgerData['credit'] = $credit;
			
			return json_encode($ledgerData);
		}		
	}
	
	function AJsaveLedger(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$accounts_id = $_SESSION["accounts_id"]??0;		
			$user_id = $_SESSION["user_id"]??0;
			$savemsg = 'error';
			$message = 'Could not add / update';
			$ledger_id = $_POST['ledger_id']??0;
			$account_type = $_POST['account_type']??0;
			$sub_group_id = $_POST['sub_group_id']??0;
			$parent_ledger_id = $_POST['parent_ledger_id']??0;
			$parent_sub_ledger_id = $_POST['parent_sub_ledger_id']??0;
			$parent_sub2_ledger_id = $_POST['parent_sub2_ledger_id']??0;
			$name = addslashes(trim($_POST['name']??''));
			$visible_on = implode(',', $_POST['visible_on']??array());
			$debit = $_POST['debit']??-1;
			$credit = $_POST['credit']??1;
			$opening_balance = $_POST['opening_balance']??0;
			$opening_date = $_POST['opening_date']??0;
			if(strlen($opening_date)==10){$opening_date = strtotime($opening_date);}
			else{$opening_date = 0;}
			$closing_date = $_POST['closing_date']??0;
			if(strlen($closing_date)==10){$closing_date = strtotime($closing_date);}
			else{$closing_date = 0;}

			$accountTypes = $this->accountTypes();
			$accountType = $accountTypes[$account_type];
			if(!empty($name) && $account_type>0){
				$saveData = array();
				$saveData['accounts_id'] = $accounts_id;
				$saveData['user_id'] = $user_id;
				$saveData['account_type'] = $account_type;
				$saveData['sub_group_id'] = $sub_group_id;
				$saveData['parent_ledger_id'] = $parent_ledger_id;
				$saveData['parent_sub_ledger_id'] = $parent_sub_ledger_id;
				$saveData['parent_sub2_ledger_id'] = $parent_sub2_ledger_id;
				$saveData['name'] = $name;
				$saveData['visible_on'] = $visible_on;
				$saveData['debit'] = $debit;
				$saveData['credit'] = $credit;
				$saveData['opening_balance'] = $opening_balance;
				$saveData['opening_date'] = $opening_date;
				$saveData['closing_date'] = $closing_date;
				
				$duplSql = "SELECT ledger_id FROM ledger WHERE name = :name AND account_type = :account_type AND parent_ledger_id = :parent_ledger_id";
				if($ledger_id>0){
					$duplSql .= " AND ledger_id != $ledger_id";
				}
				$duplRows = $this->db->querypagination($duplSql, array('name'=>$name, 'account_type'=>$account_type, 'parent_ledger_id'=>$parent_ledger_id));
				if($duplRows){
					$savemsg = 'error';
					$message .= "<p>$accountType::$name already exists. Please try again different name or type.</p>";
					foreach($duplRows as $oneRow){
						$ledger_id = $oneRow['ledger_id'];
						$update = $this->db->update('ledger', array('ledger_publish'=>1), $ledger_id);
						if($update){
							$this->updateLedCount($ledger_id, $parent_ledger_id, $parent_sub_ledger_id, $parent_sub2_ledger_id);
							$savemsg = 'Updated';
						}
					}
				}
				else{
					if($ledger_id==0){
						$saveData['created_on'] = date('Y-m-d H:i:s');
						$ledger_id = $this->db->insert('ledger', $saveData);
						if(!$ledger_id){
							$message .= "<p>Opps!! Could not insert.</p>";
						}
						else{
							$this->updateLedCount($ledger_id, $parent_ledger_id, $parent_sub_ledger_id, $parent_sub2_ledger_id);
							$savemsg = 'Added';
							$message .= "<p>Added Successfully.</p>";
						}
					}
					else{
						$prevName = $prevAccountType = '';
						$onerow = $this->getOneRowById('ledger', $ledger_id);
						if(!empty($onerow)){
							$prevName = $onerow->name;
							$prevAccountType = $accountTypes[$onerow->account_type];
						}
						$update = $this->db->update('ledger', $saveData, $ledger_id);
						if($update){
							$this->updateLedCount($ledger_id, $parent_ledger_id, $parent_sub_ledger_id, $parent_sub2_ledger_id);
							$savemsg = 'Updated';
							$message .= "<p>Updated Successfully.</p>";		

							$teData = $changed = $moreInfo = array();
							if($prevName != $name){
								$changed['name'] = array($prevName, $name);
							}					
							if($prevAccountType != $accountType){
								$changed['account_type'] = array($prevAccountType, $accountType);
							}					
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $accounts_id;
							$teData['user_id'] = $user_id;
							$teData['record_for'] = 'ledger';
							$teData['record_id'] = $ledger_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);					
						}
						else{
							$message .= "<p>Opps!! Could not update.</p>";
						}
					}
				}
			}
			else{
				$message .= "<p>Group Type / Ledger Name is missing.</p>";
			}
			
			$array = array('login'=>'', 'ledger_id'=>$ledger_id, 'savemsg'=>$savemsg, 'message'=>$message);
			return json_encode($array);		
		}
		
	}
	
	function updateLedCount($ledger_id, $parent_ledger_id, $parent_sub_ledger_id, $parent_sub2_ledger_id){
		$ledger_count = 0;
		$tableObj = $this->db->query("SELECT ledger_id FROM ledger WHERE (parent_ledger_id = $ledger_id OR  parent_sub_ledger_id = $ledger_id OR  parent_sub2_ledger_id = $ledger_id) AND ledger_publish=1 ORDER BY name ASC", array());
		if($tableObj){
			$ledger_count = 1;
		}
		$this->db->update('ledger', array('ledger_count'=>$ledger_count), $ledger_id);
		//log_message('error', "ledger_id:$ledger_id, ledger_count: $ledger_count, $parent_ledger_id, $parent_sub_ledger_id, $parent_sub2_ledger_id");

		if($parent_ledger_id>0){
			$this->db->update('ledger', array('ledger_count'=>1), $parent_ledger_id);
		}
		if($parent_sub_ledger_id>0){
			$this->db->update('ledger', array('ledger_count'=>1), $parent_sub_ledger_id);
		}
		if($parent_sub2_ledger_id>0){
			$this->db->update('ledger', array('ledger_count'=>1), $parent_sub2_ledger_id);
		}
	}
	
	//===========Ledger View Page==================//
	public function ledgerView($ledger_id){
		
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$dateformat = $_SESSION["dateformat"]??'Y-m-d';
		$oneRow = $this->getOneRowById('ledger', $ledger_id);
		if(!empty($oneRow)){
			$this->pageTitle = $GLOBALS['title'];
			$innerHTMLStr = "";
		
			$ledger_id = $oneRow->ledger_id;
			$user_id = $oneRow->user_id;
			$accountTypes = $this->accountTypes();
			$debitCredits = $this->debitCredits();
			$voucherTypes = $this->voucherTypes();
			
			$account_type = $oneRow->account_type;
			$sub_group_id = $oneRow->sub_group_id;
			$subGroupName = $this->getOneFieldById('sub_group', $sub_group_id, 'name');
			$parent_ledger_id = $oneRow->parent_ledger_id;
			$name = stripslashes(trim($oneRow->name));
			$debit = $oneRow->debit;
			$credit = $oneRow->credit;
			$visible_on = stripslashes(trim($oneRow->visible_on));
			if(empty($visible_on)){
				$visible_on = 'All Voucher';
			}
			else{
				$visibleOns = array_flip(explode(',', $visible_on));
				foreach($visibleOns as $key=>$value){
					if($key>0 && array_key_exists($key, $voucherTypes)){
						$visibleOns[$key] = $voucherTypes[$key].' Voucher';
					}
					else{
						unset($visibleOns[$key]);
					}
				}
				if(!empty($visibleOns)){
					$visible_on = implode(', ', $visibleOns);
				}
			}
			$openingBalance = $oneRow->opening_balance;
			if($oneRow->opening_date>0){
				$opening_date = date('d.m.Y', $oneRow->opening_date);
			}
			else{$opening_date = '&nbsp';}
			if($oneRow->closing_date>0){
				$closing_date = date('d.m.Y', $oneRow->closing_date);
			}
			else{$closing_date = '&nbsp';}
			
			$incomeTotal = $openingBalance;
			$expenseTotal = 0;
			$parentName = 'Parent';
			if($parent_ledger_id>0){
				$parentName = stripslashes(trim($this->getOneFieldById('ledger', $parent_ledger_id, 'name')));
			}

			$editPer = $hidePer = 1;
			$a = 0;
			$sonLedIdsStr = '';
			$sonLedIds = $impSonLedIds = array();
			$sonLedIds[$ledger_id] = '';
			$sonTableObj = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_ledger_id = $ledger_id ORDER BY name ASC", array());
			if(!$sonTableObj){
				$sonTableObj = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_sub_ledger_id = $ledger_id ORDER BY name ASC", array());
				if(!$sonTableObj){
					$sonTableObj = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_sub2_ledger_id = $ledger_id ORDER BY name ASC", array());
				}
			}

			if($sonTableObj){
				while($sonOneRow = $sonTableObj->fetch(PDO::FETCH_OBJ)){
					$mledger_id = $sonOneRow->ledger_id;
					$sonLedIds[$mledger_id] = '';

					$sonTableObj1 = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_sub_ledger_id = $mledger_id ORDER BY name ASC", array());
					if(!$sonTableObj){
						$sonTableObj1 = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_sub2_ledger_id = $mledger_id ORDER BY name ASC", array());
					}
					if($sonTableObj1){
						while($sonOneRow1 = $sonTableObj1->fetch(PDO::FETCH_OBJ)){
							$nledger_id = $sonOneRow1->ledger_id;
							$sonLedIds[$nledger_id] = '';

							$sonTableObj2 = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_sub2_ledger_id = $nledger_id ORDER BY name ASC", array());
							if($sonTableObj2){	
								while($sonOneRow2 = $sonTableObj2->fetch(PDO::FETCH_OBJ)){
									$oledger_id = $sonOneRow2->ledger_id;
									$sonLedIds[$oledger_id] = '';
								}		
							}
						}
					}
				}
				$impSonLedIds = $sonLedIds;
			}
			else{
				$sonLedIdsStr .= "<tr>
				<td colspan=\"10\" data-title=\"No data found\">There is no data found</td>
				</tr>";
			}

			$sql = "SELECT vl.debit_credit, vl.amount FROM voucher v, voucher_list vl WHERE vl.ledger_id IN (".implode(', ', array_keys($sonLedIds)).") AND v.voucher_publish=2 AND v.voucher_id = vl.voucher_id";
			$dataObj = $this->db->query($sql, array());
			$totDeb = $totCre = 0;
			if($dataObj){
				while($vOneRow = $dataObj->fetch(PDO::FETCH_OBJ)){
					$vdebit_credit = $vOneRow->debit_credit;
					$vamount = $vOneRow->amount;
					$vdebit = $vcredit = 0;
					if($vdebit_credit==1){$vdebit = $vamount;}
					else{$vcredit = $vamount;}
					$totDeb += $vdebit;
					$totCre += $vcredit;
				}
			}	
			$incomeTotal += $totDeb;
			$expenseTotal += $totCre;

			$innerHTMLStr = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"/assets/css/daterangepicker.css\" />
			<script type=\"text/javascript\" src=\"/assets/js/moment.min.js\"></script>
			<script type=\"text/javascript\" src=\"/assets/js/daterangepicker.js\"></script>
			<div class=\"row\">
				<div class=\"col-sm-12\">
					<header class=\"ptopbottom15 txtleft\">
						<div class=\"col-sm-12 col-md-6\">
							<div class=\"row\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Account Type: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									$accountTypes[$account_type]::$subGroupName
								</div>
							</div>							
							<div class=\"row\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Parent Account Name: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									$parentName
								</div>
							</div>							
							<div class=\"row\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Account Name: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									<h4 class=\"padding0 margin0\" id=\"ledgerName\">$name</h4>
								</div>
							</div>
							<div class=\"row\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Visible On: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									$visible_on
								</div>
							</div>
							<div class=\"row\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Debit / Credit: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									".$debitCredits[$debit].' / '.$debitCredits[$credit]."
								</div>
							</div>
							<div class=\"row\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Opening Date: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									$opening_date
								</div>
							</div>
							<div class=\"row\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Closing Date: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									$closing_date
								</div>
							</div>
							<div class=\"row\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Opening Balance: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									".$this->taka_format($openingBalance, 2, 'TK')."
								</div>
							</div>
							<div class=\"row\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Debit Total: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									".$this->taka_format(round($incomeTotal-$openingBalance,2), 2, 'TK')."
								</div>
							</div>
							<div class=\"row\" style=\"color:red\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Credit Total: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">
									".$this->taka_format($expenseTotal, 2, 'TK')."
								</div>
							</div>
							<div class=\"row\" style=\"color:green\">
								<div class=\"col-sm-6 col-md-4 pright0 txtright\">
									<label>Closing Balance: </label>
								</div>
								<div class=\"col-sm-6 col-md-8\">";
									$closingBalance = $incomeTotal-$expenseTotal;
									$innerHTMLStr .= $this->taka_format($closingBalance, 2, 'TK')."
								</div>
							</div>
							<div class=\"row mbottom10\">
								<div class=\"col-sm-6 col-md-12\">
									<button type=\"button\" class=\"btn cursor hilightbutton p2x10\" onClick=\"AJgetLedgerPopup(0, $ledger_id);\"><i class=\"fa fa-edit\"></i> Change Information</button>
									<button class=\"mleft10 pbottom10 btn cursor hilightbutton p2x10\" onClick=\"AJgetLedgerPopup($ledger_id, 0);\" title=\"Create New Ledger\"><i class=\"fa fa-plus\"></i> New Ledger</button>
									<button class=\"mleft10 pbottom10 btn cursor hilightbutton p2x10\" onClick=\"window.location='/Accounts/ledger/';\" title=\"Back to List\">
										<i class=\"fa fa-list\"></i> Back to List
									</button>
								</div>
							</div>
						</div>
						<div class=\"col-sm-12 col-md-6\">
							<div id=\"chartContainer\" style=\"height: 350px; width: 100%;\"></div>
						</div>
						<script src=\"/assets/js/jquery.canvasjs.min.js\"></script>
						<script type=\"text/javascript\">
							window.onload = function() {
								var options = {
									title: {text: \"\"},
									data: [{
											type: \"pie\",
											startAngle: 90,
											showInLegend: \"true\",
											legendText: \"{label}\",
											indexLabel: \"{label} ({y})\",
											yValueFormatString:\"#,##0.#\"%\"\",
											dataPoints: [
												{ label: \"Debit\", y:$incomeTotal },
												{ label: \"Credit\", y: $expenseTotal }
											]
									}]
								};
								j(\"#chartContainer\").CanvasJSChart(options);
							}						
						</script>						
					</header>
				</div>
			</div>		
			<div class=\"row\">
				<div class=\"col-sm-12\">
					<div class=\"widget mbottom10\">";
			$tableRows1 = '';
			if(!empty($impSonLedIds)){
				unset($impSonLedIds[$ledger_id]);
				$sonSql = "SELECT ledger_id, parent_ledger_id, parent_sub_ledger_id, parent_sub2_ledger_id FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($impSonLedIds)).") ORDER BY parent_ledger_id ASC, parent_sub_ledger_id ASC, parent_sub2_ledger_id ASC, name ASC";
				$sonDataObj = $this->db->query($sonSql, array());
				$allLedIds = $ledIdInfos = array();
				while($sonOneRow = $sonDataObj->fetch(PDO::FETCH_OBJ)){
					$sparent_ledger_id = $sonOneRow->parent_ledger_id;
					$sparent_sub_ledger_id = $sonOneRow->parent_sub_ledger_id;
					$sparent_sub2_ledger_id = $sonOneRow->parent_sub2_ledger_id;
					$sledger_id = $sonOneRow->ledger_id;				
					if($sparent_ledger_id==0){
						$sparent_ledger_id = $sledger_id;
						$sparent_sub_ledger_id = $sparent_sub2_ledger_id = $sledger_id = 0;
					}
					else if($sparent_sub_ledger_id==0){
						$sparent_sub_ledger_id = $sledger_id;
						$sparent_sub2_ledger_id = $sledger_id = 0;
					}
					else if($sparent_sub2_ledger_id==0){
						$sparent_sub2_ledger_id = $sledger_id;
						$sledger_id = 0;
					}
					$ledIdInfos[$sparent_ledger_id] = array();
					$ledIdInfos[$sparent_sub_ledger_id] = array();
					$ledIdInfos[$sparent_sub2_ledger_id] = array();
					$ledIdInfos[$sledger_id] = array();
					$allLedIds[$sparent_ledger_id][$sparent_sub_ledger_id][$sparent_sub2_ledger_id][$sledger_id] = '';
				}
				if(!empty($ledIdInfos)){
					$tableObj = $this->db->query("SELECT * FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($ledIdInfos)).") ORDER BY name ASC", array());
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$ledIdInfos[$oneRow->ledger_id] = $oneRow;
						}
					}							
				}
				$parLedIds = array();
				if(!empty($allLedIds)){
					foreach($allLedIds as $sparent_ledger_id=>$parMoreRows){
						$ledgerOneRow = $ledIdInfos[$sparent_ledger_id]??array();
						if(!empty($ledgerOneRow)){
							$ssubGroupName = $this->getOneFieldById('sub_group', $ledgerOneRow->sub_group_id, 'name');
							$sname = stripslashes(trim($ledgerOneRow->name));
							if($sparent_ledger_id==$ledger_id){$sname = "<span class=\"txtDeepRed txtbold\">$sname</span>";}
							$parLedIds[$sparent_ledger_id] = $accountTypes[$ledgerOneRow->account_type]." || $ssubGroupName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->credit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date || $ledgerOneRow->ledger_count";
						}
					}			
				}
				
				if(!empty($parLedIds)){
					asort($parLedIds);
					$a = 0;
					foreach($parLedIds as $sparent_ledger_id=>$oneParLedInfo){
						$parLedInfo = $allLedIds[$sparent_ledger_id];
						$a++;
						$oneLedInfo = explode(' || ', $oneParLedInfo);								
						$arrow = 0;
						$sonLedIdsStr .= $this->ledOneRowHTML("$a. ".$oneLedInfo[0], $oneLedInfo[1], $oneLedInfo[2], $oneLedInfo[3],  $debitCredits[$oneLedInfo[4]], $debitCredits[$oneLedInfo[5]], $oneLedInfo[6], $oneLedInfo[7], $editPer, $hidePer, $arrow, $sparent_ledger_id, $oneLedInfo[8]);
						
						if(!empty($parLedInfo)){
							$parSubLedIds = array();
							foreach($parLedInfo as $sparent_sub_ledger_id=>$moreRows){
								$ledgerOneRow = $ledIdInfos[$sparent_sub_ledger_id]??array();
								if(!empty($ledgerOneRow)){
									$ssubGroupName = $this->getOneFieldById('sub_group', $ledgerOneRow->sub_group_id, 'name');
									$sname = stripslashes(trim($ledgerOneRow->name));
									if($sparent_sub_ledger_id==$ledger_id){$sname = "<span class=\"txtDeepRed txtbold\">$sname</span>";}
									$parSubLedIds[$sparent_sub_ledger_id] = $accountTypes[$ledgerOneRow->account_type]." || $ssubGroupName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->credit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date || $ledgerOneRow->ledger_count";
								}
							}
							asort($parSubLedIds);
							foreach($parSubLedIds as $sparent_sub_ledger_id=>$oneParSubLedInfo){
								$parSubLedInfo = $parLedInfo[$sparent_sub_ledger_id];
								$a++;								
								$arrow = 1;
								$oneLedInfo = explode(' || ', $oneParSubLedInfo);
								$sonLedIdsStr .= $this->ledOneRowHTML("$a. ".$oneLedInfo[0], $oneLedInfo[1], $oneLedInfo[2], $oneLedInfo[3],  $debitCredits[$oneLedInfo[4]], $debitCredits[$oneLedInfo[5]], $oneLedInfo[6], $oneLedInfo[7], $editPer, $hidePer, $arrow, $sparent_sub_ledger_id, $oneLedInfo[8]);
						
								if(!empty($parSubLedInfo)){
									$parSub2LedIds = array();
									foreach($parSubLedInfo as $parent_sub2_ledger_id=>$moreRows){
										$ledgerOneRow = $ledIdInfos[$parent_sub2_ledger_id]??array();
										if(!empty($ledgerOneRow)){
											$ssubGroupName = $this->getOneFieldById('sub_group', $ledgerOneRow->sub_group_id, 'name');
											$sname = stripslashes(trim($ledgerOneRow->name));
											if($parent_sub2_ledger_id==$ledger_id){$sname = "<span class=\"txtDeepRed txtbold\">$sname</span>";}
											$parSub2LedIds[$parent_sub2_ledger_id] = $accountTypes[$ledgerOneRow->account_type]." || $ssubGroupName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->credit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date || $ledgerOneRow->ledger_count";
										}
									}
									asort($parSub2LedIds);
									foreach($parSub2LedIds as $sparent_sub2_ledger_id=>$oneParSub2LedInfo){
										$parSub2LedInfo = $parSubLedInfo[$sparent_sub2_ledger_id];
										$a++;
										$arrow = 2;
										$oneLedInfo = explode(' || ', $oneParSub2LedInfo);
										$sonLedIdsStr .= $this->ledOneRowHTML("$a. ".$oneLedInfo[0], $oneLedInfo[1], $oneLedInfo[2], $oneLedInfo[3],  $debitCredits[$oneLedInfo[4]], $debitCredits[$oneLedInfo[5]], $oneLedInfo[6], $oneLedInfo[7], $editPer, $hidePer, $arrow, $sparent_sub2_ledger_id, $oneLedInfo[8]);
						
										if(!empty($parSub2LedInfo)){													
											$sledgerIds = array();
											foreach($parSub2LedInfo as $sledger_id=>$oneLedInfo){
												$ledgerOneRow = $ledIdInfos[$sledger_id]??array();
												if(!empty($ledgerOneRow)){
													$ssubGroupName = $this->getOneFieldById('sub_group', $ledgerOneRow->sub_group_id, 'name');
													$sname = stripslashes(trim($ledgerOneRow->name));
													if($sledger_id==$ledger_id){$sname = "<span class=\"txtDeepRed txtbold\">$sname</span>";}
													$sledgerIds[$sledger_id] = $accountTypes[$ledgerOneRow->account_type]." || $ssubGroupName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->credit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date || $ledgerOneRow->ledger_count";
												}
											}
											asort($sledgerIds);
											foreach($sledgerIds as $sledger_id=>$oneLedInfo){
												$a++;
												$arrow = 3;
												$oneLedInfo = explode(' || ', $oneLedInfo);
												$sonLedIdsStr .= $this->ledOneRowHTML("$a. ".$oneLedInfo[0], $oneLedInfo[1], $oneLedInfo[2], $oneLedInfo[3],  $debitCredits[$oneLedInfo[4]], $debitCredits[$oneLedInfo[5]], $oneLedInfo[6], $oneLedInfo[7], $editPer, $hidePer, $arrow, $sledger_id, $oneLedInfo[8]);
												
											}
										}
									}
								}
							}
						}								
					}
				}
				$tableRows1 .= '<table class="col-md-12 table-bordered table-striped table-condensed cf listing">
					<thead class="cf">
						<tr>
						<th align="left" width="5%">Group Name</th>
						<th align="left" width="5%">Sub-Group Name</th>
						<th align="left">Ledger Name</th>
						<th align="left" width="20%">Visible On</th>
						<th align="center" width="5%">Debit</th>
						<th align="center" width="5%">Credit</th>
						<th align="center" width="5%">Create Voucher</th>
						<th align="center" width="5%">Action</th>
						</tr>
					</thead>
					<tbody>'.$sonLedIdsStr.'</tbody>
				</table>';
			}
			if(!empty($tableRows1)){
				$innerHTMLStr .= "<div class=\"widget-header\">
								<div class=\"row\">
									<div class=\"col-sm-12\" style=\"position:relative;\">
										<h3>All Son Ledger of $name</h3>
									</div>
								</div>
							</div>
							<div class=\"widget-content padding0\">						
								<div class=\"row\">
									<div class=\"col-sm-12\" style=\"position:relative;\">
										$tableRows1
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class=\"row\">
					<div class=\"col-sm-12\">
						<div class=\"widget mbottom10\">";
			}
					
			$list_filters = $_SESSION["list_filters"]??array();
			$date_range = array_key_exists('date_range', $list_filters) ? $list_filters['date_range']:$GLOBALS['segment6name'];
			if(empty($date_range)){$date_range = date('Y-m-d', strtotime("-1 day")).' - '.date('Y-m-d');}
						
			$this->ledger_id = implode('_', array_keys($sonLedIds));
			$this->date_range = $date_range;
			$this->filterHAndOptionsLedger();
			
			$page = !empty($GLOBALS['segment5name']) ? intval($GLOBALS['segment5name']):1;
			if($page<=0){$page = 1;}
			if(!isset($_SESSION["limit"])){$_SESSION["limit"] = 'auto';}
			$limit = $_SESSION["limit"];
			
			$this->page = $page;
			$this->rowHeight = 34;
			
			$tableRows = $this->loadHTableRowsLedger();
			
			$limitOpt = '';
			$limitOpts = array(15, 20, 25, 50, 100, 500);
			foreach($limitOpts as $oneOpt){
				$selected = '';
				if($limit==$oneOpt){$selected = ' selected';}
				$limitOpt .= "<option$selected value=\"$oneOpt\">$oneOpt</option>";
			}
			
			$innerHTMLStr .= "<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]/$ledger_id\">
						<input type=\"hidden\" name=\"page\" id=\"page\" value=\"$this->page\">
						<input type=\"hidden\" name=\"rowHeight\" id=\"rowHeight\" value=\"$this->rowHeight\">
						<input type=\"hidden\" name=\"totalTableRows\" id=\"totalTableRows\" value=\"$this->totalRows\">
						<input type=\"hidden\" name=\"publicsShow\" id=\"table_idValue\" value=\"".implode('_', array_keys($sonLedIds))."\">
						<div class=\"widget-header\">
							<div class=\"row\">
								<div class=\"col-sm-12 col-md-4 paddingright0\" style=\"position:relative;\">
									<h3>Voucher All Transaction</h3>									
									<button class=\"floatright btn cursor p2x10 marginright15\" onClick=\"printLedger();\" title=\"Print $name\">
										<i class=\"fa fa-print\"></i> Print
									</button>
									<input type=\"hidden\" id=\"reportInfo\" value=\"Account Type: $accountTypes[$account_type], &emsp; Parent Account: $parentName, &emsp;  Account Name: $name\">
								</div>
								<div class=\"col-sm-12 col-md-3\" style=\"position:relative;\">
									<div class=\"input-group\">
										<input type=\"text\" name=\"date_range\" id=\"date_range\" class=\"form-control\" placeholder=\"From to Todate\" value=\"$date_range\" /> 
										<span class=\"input-group-addon cursor\" onClick=\"checkAndLoadFilterData();\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Search by date range\">
											<i class=\"fa fa-search\"></i>
										</span>
									</div>
								</div>
								<div class=\"col-sm-12 col-md-5\" style=\"position:relative;\">
									<select class=\"form-control width100 floatleft\" name=\"limit\" id=\"limit\" onChange=\"checkloadTableRows();\">
										<option value=\"auto\">Auto</option>
										$limitOpt
									</select>
									<label id=\"fromtodata\"></label>
									<div class=\"floatright\" id=\"Pagination\"></div>
								</div>
							</div>
						</div>
						<div class=\"widget-content padding0\">						
							<div class=\"row\">
								<div class=\"col-sm-12\" style=\"position:relative;\">
									<div id=\"no-more-tables\">
										<table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
											<thead class=\"cf\">
												<tr>
													<th align=\"left\" width=\"3%\">SL#</th>
													<th align=\"left\" width=\"5%\">Voucher Date</th>
													<th align=\"left\" width=\"20%\">Particulars</th>
													<th align=\"left\">Narration</th>
													<th align=\"left\" width=\"7%\">Voucher No.</th>
													<th align=\"center\" width=\"3%\">Qty</th>
													<th align=\"center\" width=\"8%\">Unit Price</th>
													<th align=\"center\" width=\"8%\">Debit</th>
													<th align=\"center\" width=\"8%\">Credit</th>
													<th align=\"center\" width=\"8%\">Balance</th>
												</tr>
											</thead>
											<tbody id=\"tableRows\">$tableRows</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>					
					</div>
				</div>
			</div>";
			
			$htmlStr = $this->htmlBody($innerHTMLStr);
			return $htmlStr;
		}
		else{
			return "<meta http-equiv = \"refresh\" content = \"0; url = /Accounts/ledger\" />";
		}
	}
	
	private function filterHAndOptionsLedger(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$fledger_id = $this->ledger_id;
		$date_range = $this->date_range;
		
		$filterSql = "v.voucher_publish=2 AND ";
		$allLedIds = $bindData = array();
		if($fledger_id !=0){
			$allLedIds = explode('_', $fledger_id);
			$filterSql .= "vl.ledger_id IN (".implode(', ', $allLedIds).") AND ";
		}
		
		$openingDate = date('Y-m-d');
		if(!empty($date_range)){
			$date_ranges = explode(' - ', $date_range);
			if(!empty($date_ranges) && count($date_ranges)>1){
				$openingDate = $startdate = date('Y-m-d', strtotime($date_ranges[0]));
				$enddate = date('Y-m-d', strtotime($date_ranges[1]));
				$filterSql .= "v.voucher_date BETWEEN :startdate AND :enddate AND ";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}				
		}
		$sql = "SELECT vl.voucher_id FROM voucher v, voucher_list vl WHERE $filterSql v.voucher_id = vl.voucher_id";
		$totalRows = 0;
		$vouObj = $this->db->query($sql, $bindData);
		if($vouObj){
			$totalRows += $vouObj->rowCount();
		}
	
		$this->totalRows = $totalRows;		
	}
	
    private function loadHTableRowsLedger(){
        
		$limit = $_SESSION["limit"];
		$rowHeight = $this->rowHeight;
		$page = $this->page;
		$totalRows = $this->totalRows;
		$fledger_id = $this->ledger_id;
		$date_range = $this->date_range;
		$accountTypes = $this->accountTypes();
		$debitCredits = $this->debitCredits();
		$voucherTypes = $this->voucherTypes();
		
		if(in_array($limit, array('', 'auto'))){
			$screenHeight = $_COOKIE['screenHeight']??480;
			$headerHeight = $_COOKIE['headerHeight']??300;
			$bodyHeight = floor($screenHeight-$headerHeight);
			$limit = floor($bodyHeight/$rowHeight);
			if($limit<=0){$limit = 1;}
		}
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'$';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		
		$filterSql = "v.voucher_publish=2 AND ";
		$allLedIds = $bindData = array();
		if($fledger_id !=0){
			$allLedIds = explode('_', $fledger_id);
			$filterSql .= "vl.ledger_id IN (".implode(', ', $allLedIds).") AND ";
		}
		$openingDate = date('Y-m-d');
		if(!empty($date_range)){
			$date_ranges = explode(' - ', $date_range);
			if(!empty($date_ranges) && count($date_ranges)>1){
				$openingDate = $startdate = date('Y-m-d', strtotime($date_ranges[0]));
				$enddate = date('Y-m-d', strtotime($date_ranges[1]));
				$filterSql .= "v.voucher_date BETWEEN :startdate AND :enddate AND ";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}				
		}
		
		$trasOpeBal = 0;
		if(!empty($allLedIds)){
			$sql = "SELECT SUM(opening_balance) AS trasOpeBal FROM ledger WHERE ledger_id IN (".implode(', ', $allLedIds).") AND ledger_publish=1";
			$dataObj = $this->db->query($sql, array());
			if($dataObj){
				while($oneRow = $dataObj->fetch(PDO::FETCH_OBJ)){
					$trasOpeBal += $oneRow->trasOpeBal;
				}
			}
		}
		
		$sql = "SELECT SUM(vl.debit_credit*vl.amount) AS trasOpeBal FROM voucher v, voucher_list vl WHERE vl.ledger_id IN (".implode(', ', $allLedIds).") AND v.voucher_date < '$openingDate' AND v.voucher_publish=2 AND v.voucher_id = vl.voucher_id";
		$dataObj = $this->db->query($sql, array());
		if($dataObj){
			while($oneRow = $dataObj->fetch(PDO::FETCH_OBJ)){
				$trasOpeBal += $oneRow->trasOpeBal;
			}
		}		
		
		$balance = $trasOpeBal;
		$totDeb = $totCre = $totQty = 0;
		$sl=$starting_val;
		$sl++;
		$tableRows = "<tr class=\"lightpinkrow\">
		<td data-title=\"SL\" align=\"left\">$sl</td>
		<td data-title=\"Voucher#\" align=\"right\" colspan=\"4\"><strong>Opening Balance</strong></td>
		<td data-title=\"Debit\" align=\"right\"><strong>".$this->taka_format($totQty,0)."</strong></td>
		<td data-title=\"Voucher#\" align=\"right\">&nbsp;</td>
		<td data-title=\"Debit\" align=\"right\"><strong>".$this->taka_format($totDeb, 2, 'TK')."</strong></td>
		<td data-title=\"Credit\" align=\"right\"><strong>".$this->taka_format($totCre, 2, 'TK')."</strong></td>
		<td data-title=\"Balance\" align=\"right\"><strong>".$this->taka_format($balance, 2, 'TK')."</strong></td>
		</tr>";

		$sql = "SELECT v.voucher_id, v.voucher_date, v.voucher_type, v.voucher_no, vl.narration, vl.qty, vl.unit_price, vl.debit_credit, vl.amount FROM voucher v, voucher_list vl WHERE $filterSql v.voucher_id = vl.voucher_id ORDER BY v.voucher_date ASC, v.voucher_type ASC, v.voucher_no ASC LIMIT $starting_val, $limit";
		$dataObj = $this->db->querypagination($sql, $bindData);
		if($dataObj){
			
			$accountTypes = $this->accountTypes();
			$debitCredits = $this->debitCredits();
			
			$voucherData = $vDateNote = $ledIds = $vouParData = array();
			foreach($dataObj as $oneRow){
				$vDateNote[$oneRow['voucher_id']] = array($oneRow['voucher_no'], $oneRow['voucher_type'], $oneRow['voucher_date'], $oneRow['debit_credit'], '');
				$voucherData[$oneRow['voucher_id']][] = array($oneRow['narration'], $oneRow['debit_credit'], $oneRow['amount'], $oneRow['qty'], $oneRow['unit_price']);
			}
			
			if(count($allLedIds)==1){
				$sql2 = "SELECT v.voucher_id, vl.debit_credit, vl.ledger_id FROM voucher v, voucher_list vl WHERE v.voucher_id IN (".implode(', ', array_keys($vDateNote)).") AND vl.ledger_id NOT IN ($allLedIds[0]) AND v.voucher_id = vl.voucher_id ORDER BY v.voucher_id DESC, vl.voucher_list_id ASC";
				$dataObj2 = $this->db->query($sql2, array());
				if($dataObj2){	
					while($oneRow = $dataObj2->fetch(PDO::FETCH_OBJ)){
						$ledIds[$oneRow->ledger_id] = '';
						$vouParData[$oneRow->voucher_id][$oneRow->debit_credit][$oneRow->ledger_id] = '';
					}
				}
			}
			else{
				$sql2 = "SELECT v.voucher_id, vl.debit_credit, vl.ledger_id FROM voucher v, voucher_list vl WHERE v.voucher_id IN (".implode(', ', array_keys($vDateNote)).") AND v.voucher_id = vl.voucher_id ORDER BY v.voucher_id DESC, vl.voucher_list_id ASC";
				$dataObj2 = $this->db->query($sql2, array());
				if($dataObj2){						
					while($oneRow = $dataObj2->fetch(PDO::FETCH_OBJ)){
						if(in_array($oneRow->ledger_id, $allLedIds)){
							$ledIds[$oneRow->ledger_id] = '';
							$vouParData[$oneRow->voucher_id][$oneRow->debit_credit][$oneRow->ledger_id] = '';
						}
					}
				}
			}
			if(!empty($ledIds)){
				$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($ledIds)).") ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$ledIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
					}
				}
			}
								
			if(!empty($vouParData)){
				foreach($vouParData as $voucher_id=>$debCreInfo){
					$particularNames = array();
					$ledDebCre = $vDateNote[$voucher_id][3];
					$parDebCre = -1;
					if($ledDebCre==-1){$parDebCre = 1;}

					if(!empty($debCreInfo)){						
						foreach($debCreInfo as $parDebCre=>$ledger_idInfo){
							foreach($ledger_idInfo as $ledger_id=>$nullVal){
								$particularNames[$ledIds[$ledger_id]] = '';
							}
						}
					}
					$vDateNote[$voucher_id][4] = implode(', ', array_keys($particularNames));
				}
			}

			$sl = $starting_val;
			foreach($voucherData as $voucher_id=>$voucherInfo){
				$sl++;
				$vDateNoteData = $vDateNote[$voucher_id];
				$voucher_no = $vDateNoteData[0];
				$voucher_type = $vDateNoteData[1];
				$vt = substr($voucherTypes[$voucher_type], 0,1).'V';
				$voucher_date = $vDateNoteData[2];
				$particularName = $vDateNoteData[4];
				$colSpan = count($voucherInfo);
				
				$printIcon = "<a class=\"floatright mleft10\" href=\"javascript:void(0);\" onClick=\"printbyuri('/Accounts/voucherPrint/$voucher_id');\" title=\"Print this Voucher Information\"><i class=\"fa fa-print txt18\"></i></a>";
				
				$l=0;
				$voucherCols = "<td rowspan=\"$colSpan\" data-title=\"Created Date\" align=\"center\">$sl</td>
				<td rowspan=\"$colSpan\" data-title=\"Created Date\" align=\"center\">".date('d.m.Y', strtotime($voucher_date))."</td>
				<td data-title=\"Particulars Name\" align=\"left\" rowspan=\"$colSpan\">$particularName</td>";
				foreach($voucherInfo as $oneRow){
					$l++;
					if($l>1){$voucherCols = '';}
					$narration = $oneRow[0];
					$debit_credit = $oneRow[1];
					$amount = $oneRow[2];
					$qty = $oneRow[3];
					$unit_price = $oneRow[4];
					$debit = $credit = 0;
					if($debit_credit==1){$debit = $amount;}
					else{$credit = $amount;}
					$totQty += $qty;
					$balance += $debit-$credit;

					$totDeb += $debit;
					$totCre += $credit;
					
					$tableRows .= "<tr>$voucherCols
					<td data-title=\"Narration\" align=\"left\">".stripslashes($narration)."</td>
					<td data-title=\"Voucher#\" align=\"left\">$vt$voucher_no $printIcon</td>
					<td data-title=\"Debit\" align=\"right\">".$this->taka_format($qty,0)."</td>
					<td data-title=\"Debit\" align=\"right\">$currency".$this->taka_format($unit_price)."</td>
					<td data-title=\"Debit\" align=\"right\">$currency".$this->taka_format($debit)."</td>
					<td data-title=\"Credit\" align=\"right\">$currency".$this->taka_format($credit)."</td>
					<td data-title=\"Balance\" align=\"right\">$currency".$this->taka_format($balance)."</td>
					</tr>";
				}
			}
		}
		else{
			$tableRows .= "<tr>
			<td colspan=\"8\" data-title=\"No data found\">There is no data found</td>
			</tr>";
		}
		$sl++;

		$tableRows .= "<tr class=\"lightpinkrow\">
			<td data-title=\"Grand Total\" align=\"right\" colspan=\"5\"><strong>Grand Total: </strong></td>
			<td data-title=\"Qty\" align=\"right\"><strong>$totQty</strong></td>
			<td data-title=\"Unit Price\" align=\"right\">&nbsp;</td>
			<td data-title=\"Debit\" align=\"right\"><strong>\$".$this->taka_format($totDeb, 2, 'TK')."</strong></td>
			<td data-title=\"Credit\" align=\"right\"><strong>\$".$this->taka_format($totCre, 2, 'TK')."</strong></td>
			<td data-title=\"Credit\" align=\"right\"><strong>".$this->taka_format($balance, 2, 'TK')."</td>
			</tr>";
		
		return $tableRows;
    }
		
	public function AJgetHPageLedger($segment4name){
	
		$ledger_id = $_POST['ledger_id']??0;
		$date_range = $_POST['date_range']??'';
		$totalRows = $_POST['totalRows']??0;
		$rowHeight = $_POST['rowHeight']??34;
		$page = $_POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $_POST['limit']??'auto';
		
		$this->ledger_id = $ledger_id;
		$this->date_range = $date_range;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptionsLedger();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$this->rowHeight = $rowHeight;
		$jsonResponse['tableRows'] = $this->loadHTableRowsLedger();
		
		return json_encode($jsonResponse);
	}
	
	public function receiptVoucher(){
		$this->voucher_type = 1;		
		return $this->voucher1();
	}
	
	public function paymentVoucher(){
		$this->voucher_type = 2;		
		return $this->voucher1();
	}
	
	public function journalVoucher(){
		$this->voucher_type = 3;		
		return $this->voucher1();
	}
	
	public function contraVoucher(){
		$this->voucher_type = 4;		
		return $this->voucher1();
	}
	
	public function purchaseVoucher(){
		$this->voucher_type = 5;		
		return $this->voucher2();
	}
	
	public function salesVoucher(){
		$this->voucher_type = 6;		
		return $this->voucher2();
	}
	
	//===========Voucher1 Page==================//
	private function voucher1(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$this->pageTitle = $GLOBALS['title'];
		$list_filters = $_SESSION['list_filters']??array();
		
		$this->publish = $fpublish = $list_filters['fpublish']??">0";
		$fvoucher_type = $this->voucher_type;
		$this->account_type = $faccount_type = $list_filters['faccount_type']??0;
		$this->sub_group_id = $fsub_group_id = $list_filters['fsub_group_id']??0;
		$this->ledger_id = $fledger_id = $list_filters['fledger_id']??0;
		$this->keyword_search = $keyword_search = $list_filters['keyword_search']??'';
		
		$this->CountList = 'Count';
		
		$this->Voucher1Data();
		$totalRows = $this->totalRows;
		$subGroOpt = $this->subGroOpt;
		$parLedOpt = $this->parLedOpt;
		
		$page = !empty($GLOBALS['segment4name']) ? intval($GLOBALS['segment4name']):1;
		if($page<=0){$page = 1;}
		if(!isset($_SESSION['limit'])){$_SESSION['limit'] = 'auto';}
		$limit = $_SESSION['limit'];
		
		$this->rowHeight = 34;
		$this->page = $page;
		$this->CountList = 'List';
		$tableRows = $this->Voucher1Data();
		
		$pubOpt = "<option value=\">0\">Pending+Approved</option>";
		$pubOpts = array('=1'=>'Pending', '=2'=>'Approved', '=0'=>'Archived');
		foreach($pubOpts as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($fpublish==$oneOptVal){$selected = ' selected';}
			$pubOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$accTypOpt = "<option value=\"0\">All Account Type</option>";
		$accountTypes = $this->accountTypes();
		foreach($accountTypes as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($faccount_type==$oneOptVal){$selected = ' selected';}
			$accTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
				
		$limOpt = '';
		$limOpts = array(15, 20, 25, 50, 100, 500);
		foreach($limOpts as $oneOpt){
			$selected = '';
			if($limit==$oneOpt){$selected = ' selected';}
			$limOpt .= "<option$selected value=\"$oneOpt\">$oneOpt</option>";
		}
		
		$ledAutComData = array();
		$ledSql = "SELECT ledger_id, name FROM ledger WHERE accounts_id = $prod_cat_man AND (visible_on='' OR visible_on LIKE CONCAT('%', :fvoucher_type, '%')) AND ledger_count = 0 AND ledger_publish = 1 ORDER BY parent_ledger_id ASC, name ASC";
		$tableObj = $this->db->query($ledSql, array('fvoucher_type'=>$fvoucher_type));
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$name = stripslashes($oneRow->name);
				$ledAutComData[] = array('label' => $name, 'lId' => $oneRow->ledger_id);
			}
		}
		$ledAutComRes = json_encode($ledAutComData);
		
		$innerHTMLStr = "<script type=\"text/javascript\">var ledAutComRes = $ledAutComRes</script>
		<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<input type=\"hidden\" name=\"page\" id=\"page\" value=\"$this->page\">
		<input type=\"hidden\" name=\"rowHeight\" id=\"rowHeight\" value=\"$this->rowHeight\">
		<input type=\"hidden\" name=\"totalTableRows\" id=\"totalTableRows\" value=\"$this->totalRows\">
		<input type=\"hidden\" name=\"fvoucher_type\" id=\"fvoucher_type\" value=\"$this->voucher_type\">
		<div class=\"row\">
			<div class=\"col-sm-12 col-md-2\">
				<button class=\"btn cursor hilightbutton p2x10\" onClick=\"AJgetVoucher1Popup(0, $fvoucher_type);\" title=\"Create New $GLOBALS[title]\">
					<i class=\"fa fa-plus\"></i> $GLOBALS[title]
				</button>
			</div>
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<select name=\"fpublish\" id=\"fpublish\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$pubOpt
				</select>
			</div>
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<select name=\"faccount_type\" id=\"faccount_type\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$accTypOpt
				</select>
			</div>				
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<select name=\"fsub_group_id\" id=\"fsub_group_id\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$subGroOpt
				</select>
			</div>
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<select name=\"fledger_id\" id=\"fledger_id\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$parLedOpt
				</select>
			</div>
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Search Information\" value=\"$keyword_search\" id=\"keyword_search\" name=\"keyword_search\" class=\"form-control\" maxlength=\"50\" />
					<span class=\"input-group-addon cursor\" onClick=\"checkAndLoadFilterData();\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Search Information\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"row\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
						<thead class=\"cf\">
							<tr>
								<th align=\"left\" width=\"10%\">Created Date</th>
								<th align=\"left\" width=\"10%\">Voucher No.</th>
								<th align=\"left\">Ledger Name</th>
								<th align=\"center\" width=\"20%\">Narration</th>
								<th align=\"center\" width=\"10%\">Debit</th>
								<th align=\"center\" width=\"10%\">Credit</th>
							</tr>
						</thead>
						<tbody id=\"tableRows\">
							$tableRows
						</tbody>
					</table>
				</div>
			</div>    
		</div>
		<div class=\"row mtop10\">
			<div class=\"col-xs-12\">
				<select class=\"form-control width100 floatleft\" name=\"limit\" id=\"limit\" onChange=\"checkloadTableRows();\">
					<option value=\"auto\">Auto</option>
					$limOpt
				</select>
				<label id=\"fromtodata\"></label>
				<div class=\"floatright\" id=\"Pagination\"></div>
			</div>
		</div>";
		
		$htmlStr = $this->htmlBody($innerHTMLStr);
		return $htmlStr;
	}
	
	public function AJgetPage_Voucher1(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$fpublish = $_POST['fpublish']??">0";
		$fvoucher_type = $_POST['fvoucher_type']??1;
		$faccount_type = $_POST['faccount_type']??0;
		$fsub_group_id = $_POST['fsub_group_id']??0;
		$fledger_id = $_POST['fledger_id']??0;
		$keyword_search = $_POST['keyword_search']??'';
		
		$totalRows = $_POST['totalRows']??0;
		$rowHeight = $_POST['rowHeight']??34;
		$page = $_POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $_POST['limit']??'auto';
		
		$this->publish = $fpublish;
		$this->voucher_type = $fvoucher_type;
		$this->account_type = $faccount_type;
		$this->sub_group_id = $fsub_group_id;
		$this->ledger_id = $fledger_id;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		if($prod_cat_man==0){
			$jsonResponse['login'] = 'session_ended';
		}
		else{
			//===If filter options changes===//
			if($GLOBALS['segment4name']=='filter'){
				$this->CountList = 'Count';
				$this->Voucher1Data();
				$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
				$jsonResponse['subGroOpt'] = $this->subGroOpt;
				$jsonResponse['parLedOpt'] = $this->parLedOpt;
			}
			
			$this->page = $page;
			$this->totalRows = $totalRows;
			$this->rowHeight = $rowHeight;
			$this->CountList = 'List';
			
			$jsonResponse['tableRows'] = $this->Voucher1Data();
		}
		return json_encode($jsonResponse);
	}
	
	private function Voucher1Data(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$reponseData = array('login'=>'');
		if($prod_cat_man==0){$reponseData['login'] = 'session_ended';}
		
		$fpublish = $this->publish;
		$fvoucher_type = $this->voucher_type;
		$faccount_type = $this->account_type;
		$fsub_group_id = $this->sub_group_id;
		$fledger_id = $this->ledger_id;
		$keyword_search = $this->keyword_search;
		$CountList = $this->CountList;
		
		$_SESSION["current_module"] = "subGroup";
		$_SESSION["list_filters"] = array('fpublish'=>$fpublish, 'fvoucher_type'=>$fvoucher_type, 'faccount_type'=>$faccount_type, 'fsub_group_id'=>$fsub_group_id, 'fledger_id'=>$fledger_id, 'keyword_search'=>$keyword_search);
		
		$filterSql = "FROM voucher v, voucher_list vl, ledger l WHERE v.accounts_id = $accounts_id AND v.voucher_type = $fvoucher_type AND v.voucher_publish$fpublish AND ";
		$bindData = array();
		if($faccount_type >0){$filterSql .= "l.account_type = $faccount_type AND ";}		
		if($fsub_group_id >0){$filterSql .= "l.sub_group_id = $fsub_group_id AND ";}		
		if($fledger_id >0){$filterSql .= "l.ledger_id = $fledger_id AND ";}		
		
		if($faccount_type >0){
			$filterSql .= "l.account_type = :account_type AND ";
			$bindData['account_type'] = $faccount_type;
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim($keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= "CONCAT_WS(' ', v.voucher_no, vl.narration) LIKE CONCAT('%', :keyword_search$num, '%') AND ";
				$bindData['keyword_search'.$num] = trim($keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql .= " v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id";
		if($CountList == 'Count'){
			$totalRows = 0;
			$subGroIds = $LedIds = array();
			$sql = "SELECT l.sub_group_id, l.ledger_id $filterSql";
			$query = $this->db->querypagination($sql, $bindData);
			if($query){
				$totalRows = count($query);
				foreach($query as $getOneRow){
					$subGroIds[$getOneRow['sub_group_id']] = '';
					if($getOneRow['ledger_id']>0)
						$LedIds[$getOneRow['ledger_id']] = '';
				}
			}
			
			$subGroOpt = "<option value=\"0\">Select Sub-Group Name</option>";
			if(!empty($subGroIds)){
				$tableObj = $this->db->query("SELECT sub_group_id, name FROM sub_group WHERE sub_group_id IN (".implode(', ', array_keys($subGroIds)).") AND sub_group_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$subGroOpt .= "<option value=\"$oneRow->sub_group_id\">".stripslashes(trim($oneRow->name))."</option>";
					}
				}				
			}
			
			$parLedOpt = "<option value=\"0\">Parent Ledger</option>";
			if(!empty($LedIds)){
				$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($LedIds)).") AND ledger_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$parLedOpt .= "<option value=\"$oneRow->ledger_id\">".stripslashes(trim($oneRow->name))."</option>";
					}
				}
			}
			
			$this->totalRows = $totalRows;
			$this->subGroOpt = $subGroOpt;
			$this->parLedOpt = $parLedOpt;
		}
		else{
			$limit = $_SESSION["limit"];		
			$rowHeight = $this->rowHeight;
			$page = $this->page;
			$totalRows = $this->totalRows;
			if(in_array($limit, array('', 'auto'))){
				$screenHeight = $_COOKIE['screenHeight']??480;
				$headerHeight = $_COOKIE['headerHeight']??300;
				$bodyHeight = floor($screenHeight-$headerHeight);
				$limit = floor($bodyHeight/$rowHeight);
				if($limit<=0){$limit = 1;}
			}
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			
			$sql = "SELECT v.voucher_id, v.voucher_date, v.voucher_no, v.voucher_publish, vl.ledger_id, vl.narration, vl.debit_credit, vl.amount $filterSql ORDER BY v.voucher_no DESC LIMIT $starting_val, $limit";
			$dataObj = $this->db->querypagination($sql, $bindData);
			$tableRows = '';
			if($dataObj){
				$sl=$starting_val;
				$accountTypes = $this->accountTypes();
				$debitCredits = $this->debitCredits();
				$LedIds = $voucherData = $vDateNote = array();
				foreach($dataObj as $oneRow){
					$LedIds[$oneRow['ledger_id']] = '';
					$vDateNote[$oneRow['voucher_id']] = array($oneRow['voucher_no'], $oneRow['voucher_date'], $oneRow['voucher_publish']);
					$voucherData[$oneRow['voucher_id']][] = array($oneRow['ledger_id'], $oneRow['narration'], $oneRow['debit_credit'], $oneRow['amount']);
				}
				
				if(!empty($LedIds)){
					$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($LedIds)).") AND ledger_publish = 1 ORDER BY name ASC", array());
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
						}
					}
				}
				
				if(!array_key_exists(0, $LedIds)){$LedIds[0] = 'Parent';}
				$totDeb = $totCre = 0;
				foreach($voucherData as $voucher_id=>$voucherInfo){
					$vDateNoteData = $vDateNote[$voucher_id];
					$voucher_no = $vDateNoteData[0];
					$voucher_date = $vDateNoteData[1];
					$voucher_publish = $vDateNoteData[2];
					$colSpan = count($voucherInfo);
					$rowClass = '';
					if($voucher_publish==0){$rowClass = ' class="errormsg"';}
					elseif($voucher_publish==1){$rowClass = ' class="lightyellowrow"';}
					
					$editIcon = '<br>';
					if($voucher_publish==2){
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJupdate_Data('voucher', $voucher_id, 'Back to pending approve Voucher# $voucher_no', 'voucher_publish', 1);\" title=\"Back to pending approve\"><i class=\"fa fa-arrow-circle-left errormsg txt18\"></i></a>";
					}
					elseif($voucher_publish==1){
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJupdate_Data('voucher', $voucher_id, 'Archive Voucher# $voucher_no', 'voucher_publish', 0);\" title=\"Archive this Voucher\"><i class=\"fa fa-remove errormsg txt18\"></i></a>";
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJupdate_Data('voucher', $voucher_id, 'Approve Voucher# $voucher_no', 'voucher_publish', 2);\" title=\"Approve this Voucher\"><i class=\"fa fa-check-square successmsg txt18\"></i></a>";
					}
					elseif($voucher_publish==0){
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJupdate_Data('voucher', $voucher_id, 'Back to pending Voucher# $voucher_no', 'voucher_publish', 1);\" title=\"Back to pending approve\"><i class=\"fa fa-check errormsg txt18\"></i></a>";
					}
					
					$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJgetVoucher1Popup($voucher_id, $fvoucher_type);\" title=\"Change this Voucher Information\"><i class=\"fa fa-edit txt18\"></i></a>";
					if($voucher_publish>0){
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"printbyuri('/Accounts/voucherPrint/$voucher_id');\" title=\"Print this Voucher Information\"><i class=\"fa fa-print txt18\"></i></a>";
					}
					
					$l=0;
					$voucherCols = "<td rowspan=\"$colSpan\" data-title=\"Created Date\" align=\"center\">".date('d.m.Y', strtotime($voucher_date))."</td>
		<td data-title=\"Voucher#\" nowrap align=\"center\" rowspan=\"$colSpan\">$voucher_no $editIcon</td>";
					
					foreach($voucherInfo as $oneRow){
						$l++;
						if($l>1){$voucherCols = '';}
						$ledger_id = $oneRow[0];
						$narration = $oneRow[1];
						$debit_credit = $oneRow[2];
						$amount = $oneRow[3];
						$ledgerName = $LedIds[$ledger_id];
						$debit = $credit = 0;
						if($debit_credit==1){$debit = $amount;}
						else{$credit = $amount;}
						$totDeb += $debit;
						$totCre += $credit;
						$tableRows .= "<tr$rowClass>$voucherCols
		<td data-title=\"Ledger Name\" align=\"left\">".stripslashes($ledgerName)."</td>
		<td data-title=\"Narration\" align=\"left\">".stripslashes($narration)."</td>
		<td data-title=\"Debit\" align=\"right\">".$this->taka_format($debit, 2, 'TK')."</td>
		<td data-title=\"Credit\" align=\"right\">".$this->taka_format($credit, 2, 'TK')."</td>
		</tr>";
					}
				}
				$tableRows .= "<tr class=\"lightpinkrow\">
		<td data-title=\"Grand Total\" align=\"right\" colspan=\"4\"><strong>Grand Total: </strong></td>
		<td data-title=\"Debit\" align=\"right\"><strong>".$this->taka_format($totDeb, 2, 'TK')."</strong></td>
		<td data-title=\"Credit\" align=\"right\"><strong>".$this->taka_format($totCre, 2, 'TK')."</strong></td>
		</tr>";
			}
			else{
				$tableRows .= "<tr>
		<td colspan=\"6\" data-title=\"No data found\">There is no data found</td>
		</tr>";
			}
			return $tableRows;
		}
	}
	
	public function AJgetVoucher1Popup(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$voucher_id = intval(trim($_POST['voucher_id']??0));
			$voucher_type = intval(trim($_POST['voucher_type']??1));
			$voucherLists = array();
			$voucher1Data = array();
			$voucher1Data['login'] = '';
			$voucher1Data['voucher_id'] = 0;
			$voucher_date = date('Y-m-d');
			$voucher_no = 0;
			if($voucher_id>0){
				$oneRow = $this->getOneRowById('voucher', $voucher_id);
				if($oneRow){
					$voucher1Data['voucher_id'] = $oneRow->voucher_id;
					$voucher_date = $oneRow->voucher_date;
					$voucher_type = $oneRow->voucher_type;
					$voucher_no = $oneRow->voucher_no;
					$voucherListObj = $this->db->query("SELECT * FROM voucher_list WHERE voucher_id = $voucher_id ORDER BY voucher_list_id ASC", array());
					if($voucherListObj){
						while($listOneRow = $voucherListObj->fetch(PDO::FETCH_OBJ)){
							$ledgerName = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
							$narration = stripslashes($listOneRow->narration);
							$voucherLists[] = array($listOneRow->voucher_list_id, $listOneRow->ledger_id, $ledgerName, $narration, $listOneRow->debit_credit, $listOneRow->amount);
						}
					}
				}
			}
			
			if($voucher_no == 0){
				$lastVObj = $this->db->query("SELECT voucher_date FROM voucher WHERE voucher_type = $voucher_type ORDER BY voucher_id DESC LIMIT 0, 1", array());
				if($lastVObj){
					while($lastVOneRow = $lastVObj->fetch(PDO::FETCH_OBJ)){
						$voucher_date = $lastVOneRow->voucher_date;
					}
				}
				$voucher_no = $this->getVoucherNo($voucher_date, $voucher_type, $voucher_id);
			}
			$voucher1Data['voucher_date'] = $voucher_date;
			$voucher1Data['voucher_no'] = $voucher_no;
			
			$voucherTypes = $this->voucherTypes();
			$voucherType = $voucherTypes[$voucher_type];
			$voucher1Data['voucher_type'] = $voucher_type;
			$voucher1Data['voucherType'] = $voucherType;
			$voucher1Data['voucher_no'] = $voucher_no;
			$voucher1Data['voucherLists'] = $voucherLists;
			
			return json_encode($voucher1Data);
		}
	}
	
	public function AJsaveVoucher1(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$user_id = $_SESSION["user_id"]??0;
		
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$savemsg = $message = '';
			
			$voucher_id = $_POST['voucher_id']??0;
			$voucher_type = $_POST['voucher_type']??0;
			$voucher_no = $_POST['voucher_no']??0;
			$voucher_date = $_POST['voucher_date']??'1000-01-01';
			
			$voucher_listIds = $_POST['voucher_list_id']??array();
			$ledgerIds = $_POST['ledger_id']??array();
			$narrationIds = $_POST['narration']??array();
			$debCreIds = $_POST['debit_credit']??array();
			$debitIds = $_POST['debit']??array();
			$creditIds = $_POST['credit']??array();
						
			if(empty($voucher_date) || strlen($voucher_date)<10){$voucher_date = date('Y-m-d');}
			else{$voucher_date = date('Y-m-d', strtotime($voucher_date));}
			$voucherTypes = $this->voucherTypes();
			$voucherType = $voucherTypes[$voucher_type];
			
			if($voucher_id==0){
				$voucher_no = $this->getVoucherNo($voucher_date, $voucher_type, $voucher_id);;
			}
			
			if($voucher_no>0 && $voucher_type>0){
				$saveData = array();
				$saveData['accounts_id'] = $accounts_id;
				$saveData['user_id'] = $user_id;
				$saveData['voucher_type'] = $voucher_type;
				$saveData['voucher_no'] = $voucher_no;
				$saveData['voucher_date'] = $voucher_date;
				
				$duplSql = "SELECT voucher_publish, voucher_id FROM voucher WHERE accounts_id = $accounts_id AND voucher_date = :voucher_date AND voucher_no = :voucher_no AND voucher_type = :voucher_type";
				$bindData = array('voucher_date'=>$voucher_date, 'voucher_no'=>$voucher_no, 'voucher_type'=>$voucher_type);
				if($voucher_id>0){
					$duplSql .= " AND voucher_id != :voucher_id";
					$bindData['voucher_id'] = $voucher_id;
				}
				$duplSql .= " LIMIT 0, 1";
				$duplRows = 0;
				$subGroObj = $this->db->querypagination($duplSql, $bindData);
				if($subGroObj){
					foreach($subGroObj as $onerow){
						$duplRows = 1;
						$voucher_publish = $onerow['voucher_publish'];
						if($voucher_id==0 && $voucher_publish==0){
							$voucher_id = $onerow['voucher_id'];
							$this->db->update('voucher', array('voucher_publish'=>1), $voucher_id);
							$duplRows = 0;
							$returnStr = 'Update';
						}
					}
				}				
				
				if($duplRows>0){
					$savemsg = 'error';
					$returnStr = "<p>$voucherType::$voucher_no already exists. Please try again different voucher no or type.</p>";
				}
				else{
					if($voucher_id==0){
						$saveData['created_on'] = date('Y-m-d H:i:s');
						$saveData['voucher_publish'] = 2;
						$voucher_id = $this->db->insert('voucher', $saveData);
						if(!$voucher_id){
							$savemsg = 'error';
							$message .= "<p>Opps!! Could not insert.</p>";
						}
					}
					else{
						$prevVoucherNo = $prevVoucherType = '';
						$onerow = $this->getOneRowById('voucher', $voucher_id);
						if($onerow){
							$prevVoucherNo = $onerow->voucher_no;
							$prevVoucherType = $voucherTypes[$onerow->voucher_type];
						}
						$update = $this->db->update('voucher', $saveData, $voucher_id);
						if($update){					
							$teData = $changed = $moreInfo = array();
							if($prevVoucherNo != $voucher_no){
								$changed['voucher_no'] = array($prevVoucherNo, $voucher_no);
							}					
							if($prevVoucherType != $voucherType){
								$changed['voucher_type'] = array($prevVoucherType, $voucherType);
							}					
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $accounts_id;
							$teData['user_id'] = $user_id;
							$teData['record_for'] = 'voucher';
							$teData['record_id'] = $voucher_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);					
						}
					}
				}
				
				if($savemsg ==''){
					$vlIds = array();
					$dataObj = $this->db->querypagination("SELECT voucher_list_id FROM voucher_list WHERE voucher_id = $voucher_id", array());
					if($dataObj){
						foreach($dataObj as $oneRow){
							$vlIds[$oneRow['voucher_list_id']] = '';
						}
					}
					
					if(!empty($ledgerIds)){
						for($l=0;$l<count($ledgerIds); $l++){
							$voucher_list_id = $voucher_listIds[$l];
							$ledger_id = $ledgerIds[$l];
							$narration = $narrationIds[$l];
							$debit_credit = $debCreIds[$l];
							$debit = $debitIds[$l];
							$credit = $creditIds[$l];
							$amount = $debit;
							if($debit_credit ==-1){$amount = $credit;}
							
							$saveDLData = array('voucher_id'=>$voucher_id, 'ledger_id'=>$ledger_id, 'narration'=>$narration, 'debit_credit'=>$debit_credit, 'amount'=>$amount);
							if($voucher_list_id==0){
								$this->db->insert('voucher_list', $saveDLData);
								$ledgerOneRow = $this->getOneRowById('ledger', $ledger_id);
								if($ledgerOneRow && $ledgerOneRow->record_for=='customers' && $ledgerOneRow->record_id>0){
									$this->posPayments($ledgerOneRow->record_id, $amount, 0);
								}							
							}								
							else{
								if(array_key_exists($voucher_list_id, $vlIds))
									unset($vlIds[$voucher_list_id]);
								$this->db->update('voucher_list', $saveDLData, $voucher_list_id);
							}
						}
					}			
					
					if(!empty($vlIds)){
						foreach($vlIds as $vlId=>$val){
							$this->db->delete('voucher_list', 'voucher_list_id', $vlId);
						}
					}
				}
			}
			else{
				$savemsg = 'error';
				$message .= "<p>Voucher Type / Voucher no. is missing.</p>";
			}
			
			return json_encode(array('login'=>'', 'voucher_id'=>$voucher_id, 'savemsg'=>$savemsg, 'message'=>$message));
		}
	}
	
	public function voucherPrint(){
		$voucher_id = $GLOBALS['segment4name'];
		if($voucher_id==0){
			return 'There is no voucher found';
		}
		else{
			$accounts_id = $_SESSION["accounts_id"]??0;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$currency = $_SESSION["currency"]??'$';
			$dateformat = $_SESSION["dateformat"]??'m/d/Y';
			$timeformat = $_SESSION["timeformat"]??'12 hour';
			$domainName = OUR_DOMAINNAME;
			
			$htmlStr = '<!DOCTYPE html>
			<html>
			<head>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
				<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
				
				<title>Voucher Information</title>
				<style type="text/css">
					@page {size:portrait;}
					body{ font-family:Arial, sans-serif, Helvetica; width:100%; margin:0; padding:15px 0 0;background:#fff;color:#000;line-height:15px; font-size: 11px;}
					h2{font-size:22px; height:20px; margin-bottom:0; padding-bottom:0; font-weight:500;}
					.h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
					address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
					.pright15{padding-right:15px;}
					.ptop10{padding-top:10px;}
					.pbottom10{padding-bottom:10px;}
					.mbottom0{ margin-bottom:0px;}
					table{border-collapse:collapse;}
					.border th{background:#F5F5F6;}
					.border td, .border th{ border:1px solid #DDDDDD; padding:5px 10px; vertical-align: top;}
					.floatleft{ float:left !important;}
					.floatright{ float:right !important;}
					.w30perc{width:30%;margin-bottom:20px; padding-left:13px; padding-right:13px;}
					.borderbottom{padding:5px 0 5px 0; border-bottom:1px solid #CCC !important;}
				</style>
			</head>
			<body>';
			
			$logo_size = 'Small Logo';
			$logo_placement = 'Left';
			
			$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
			$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;
			$company_info = $invoice_message_above = $invoice_message = $value = '';
			
			$varNameVal = 'invoice_setup';
			$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = '$varNameVal'", array());
			if($varObj){
				$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
				if(!empty($value)){
					$value = unserialize($value);
					extract($value);
				}
			}
							
			$companylogo = "";
			$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
			$pics = glob($filePath."*.png");
			if($pics){
				foreach($pics as $onePicture){
					$onePicture = "//$domainName".str_replace('./', '/', $onePicture);
					if($logo_size=='Large Logo'){
						$companylogo = "<img style=\"max-height:150px;max-width:350px;\" src=\"$onePicture\" title=\"Logo\" />";
					}
					else{
						$companylogo = "<img style=\"max-height:100px;max-width:150px;\" src=\"$onePicture\" title=\"Logo\" />";
					}
				}				
			}
			$oneRow = $this->getOneRowById('voucher', $voucher_id);
			if(!empty($oneRow)){
				$voucher_date = date('d.m.Y', strtotime($oneRow->voucher_date));
				$voucher_type = $oneRow->voucher_type;
				$voucher_no = $oneRow->voucher_no;	
				$voucherTypes = $this->voucherTypes();
				$voucherType = $voucherTypes[$voucher_type];
				$user_id = $oneRow->user_id;
				$preparedBy = stripslashes($this->getOneFieldById('user', $user_id, 'user_first_name'));
				$preparedBy .= ' '.stripslashes($this->getOneFieldById('user', $user_id, 'user_last_name'));
				$voucherListObj = $this->db->query("SELECT * FROM voucher_list WHERE voucher_id = $voucher_id ORDER BY voucher_list_id ASC", array());
				
				$companylogo = "";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$pics = glob($filePath."*.png");
				if($pics){
					foreach($pics as $onePicture){
						$onePicture = "//$domainName".str_replace('./', '/', $onePicture);
						$companylogo = "<td rowspan=\"2\" width=\"100\" align=\"left\">
							<img style=\"max-height:100px;max-width:100%;\" src=\"$onePicture\" title=\"Logo\" />
						</td>";
					}				
				}
				
				$logo_size = 'Small Logo';
				$logo_placement = 'Left';
				
				$company_info = $invoice_message_above = $invoice_message = $value = '';
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
				$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;
				$varNameVal = 'invoice_setup';
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = '$varNameVal'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
								
				$creditCount = 0;
				$rowCount = 0;
				$voucherData = array();
				if($voucherListObj){
					while($listOneRow = $voucherListObj->fetch(PDO::FETCH_OBJ)){
						if($listOneRow->debit_credit==-1){
							$creditCount++;
						}
						$voucherData[] = $listOneRow;
						$rowCount++;
					}
				}
				
				$htmlStr .= '<div style="position:relative; width:100%; min-height:500px;">
					<table cellpadding="0" cellspacing="1" width="100%">						
						<tr>
							<td align="center">
								<table width="100%" cellpadding="0" cellspacing="0">
									<tr>
										'.$companylogo.'
										<td align="center" style="padding-right:100px">
											<h1 style="height:20px;">'.COMPANYNAME.'</h1>
										</td>
									</tr>
									<tr>
										<td align="center" style="padding-right:100px">
											'.$company_info.'
										</td>
									</tr>
									<tr>
										<td colspan="2" align="center" >
											<h2 style="width:180px;border:1px solid #333;padding:8px 10px; margin-bottom:0px">'.$voucherType.' Voucher</h2>
										</td>
									</tr>';
									
									$fromLebel = 'Received from ';
									$vnStr = substr($voucherType, 0, 1)."V-".sprintf("%02d", $voucher_no);
									if($voucher_type==2){
										$fromLebel = 'Paid by ';
									}
									
									if($creditCount==1){							
										$fromNames = array();
										foreach($voucherData as $listOneRow){
											if($listOneRow->debit_credit==-1){
												$fromLedgerId = $listOneRow->ledger_id;
												$fromLedgerPId = $this->getOneFieldById('ledger', $fromLedgerId, 'parent_ledger_id');
												if($fromLedgerPId>0){
													$fromLedgerPPId = $this->getOneFieldById('ledger', $fromLedgerPId, 'parent_ledger_id');
													if($fromLedgerPPId>0){
														$fromNames[] = stripslashes($this->getOneFieldById('ledger', $fromLedgerPPId, 'name'));
													}
													$fromNames[] = stripslashes($this->getOneFieldById('ledger', $fromLedgerPId, 'name'));
												}
												$fromNames[] = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
											}
										}
										$fromName = implode(', ', $fromNames);
										
										$htmlStr .= '<tr>
											<td align="left" colspan="2">
												<br>
												<table width="100%" cellpadding="0" cellspacing="0" style="line-height:30px;">
													<tr>
														<td width="50%" align="left">
															<strong>'.$fromLebel.' : <span style="text-transform: uppercase;">'.$fromName.'</span></strong>
														</td>
														<td align="right">
															<strong>Voucher No. : '.$vnStr.'</strong><br>
															Date : '.$voucher_date.'
														</td>
													</tr>
												</table>
											</td>
										</tr>';
									}
									else{
										$htmlStr .= '<tr>
											<td colspan="2" align="right" valign="top">
												<strong>Voucher No. : '.$vnStr.'</strong><br>
												Date : '.$voucher_date.'
											</td>
										</tr>';
									}
								$htmlStr .= '</table>
							</td>
						</tr>
						<tr>
							<td class="ptop15">
								<table class="border" width="100%" cellpadding="0" cellspacing="0">
									<thead>
									<tr>
										<th width="5%">SL#</th>
										<th>Particulars</th>
										<th width="40%">Narrations</th>';
										
										if($creditCount==1){
											$htmlStr .= '<th width="15%">Amount</th>';
										}
										else{
											$htmlStr .= '<th width="15%">Debit</th>
											<th width="15%">Credit</th>';
										}
										
									$htmlStr .= '</tr>
									</thead>
									<tbody>';
										
										$rowHeight = 100;
										if($rowCount>2 && $creditCount==1){$rowHeight = ceil($rowHeight/($rowCount-1));}
										else{$rowHeight = ceil($rowHeight/$rowCount);}
										if($rowHeight<30){$rowHeight = 30;}
										$totAmount = $totDeb = $totCre = 0;								
										
										if(!empty($voucherData)){
											$sl=0;
											foreach($voucherData as $listOneRow){
												$debit_credit = $listOneRow->debit_credit;
												if($creditCount==1){
													if($debit_credit==1){
														$sl++;
														$ledgerName = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
														$narration = stripslashes($listOneRow->narration);
														$amount = $listOneRow->amount;
														$totAmount += $amount;
														
														$htmlStr .= "<tr>
																		<td style=\"height:$rowHeight"."px\" valign=\"center\" align=\"right\" data-title=\"SL\">$sl</td>
																		<td data-title=\"Particular\" valign=\"center\">$ledgerName</td>
																		<td data-title=\"Narration\" valign=\"center\">$narration</td>
																		<td data-title=\"Amount\" valign=\"center\" align=\"right\">".$this->taka_format($amount, 2, 'TK')."</td>
																	</tr>";
													}
												}
												else{
													$sl++;
													$ledgerName = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
													$narration = stripslashes($listOneRow->narration);
													
													$debit = $credit = 0;
													if($debit_credit==1){$debit = $amount;}
													else{$credit = $amount;}
													$totDeb += $debit;
													$totCre += $credit;
													
													$htmlStr .= "<tr>
																	<td style=\"height:$rowHeight"."px\" valign=\"center\" align=\"right\" data-title=\"SL\">$sl</td>
																	<td data-title=\"Particular\" valign=\"center\">$ledgerName</td>
																	<td data-title=\"Narration\" valign=\"center\">$narration</td>
																	<td data-title=\"Debit\" valign=\"center\" align=\"right\">".$this->taka_format($debit, 2, 'TK')."</td>
																	<td data-title=\"Credit\" valign=\"center\" align=\"right\">".$this->taka_format($credit, 2, 'TK')."</td>
																</tr>";
												}
											}
											
											if($creditCount==1){
												$htmlStr .= "<tr>
													<td data-title=\"Total\" colspan=\"3\" align=\"right\"><strong>Total:&emsp;</strong></td>
													<td data-title=\"Total Amount\" align=\"right\"><strong>".$this->taka_format($totAmount, 2, 'TK')."</strong></td>
												</tr>";
											}
											else{
												$htmlStr .= "<tr>
													<td data-title=\"Total\" colspan=\"3\" align=\"right\"><strong>Total:&emsp;</strong></td>
													<td data-title=\"Total Debit\"><strong>".$this->taka_format($totDeb, 2, 'TK')."</strong></td>
													<td data-title=\"Total Credit\"><strong>".$this->taka_format($totCre, 2, 'TK')."</strong></td>
												</tr>";
											}
										}
										else{
											$htmlStr .= '<tr>
															<td colspan="4" data-title="No data found">There is no data found</td>
														</tr>';
										}
										if($creditCount !=1){$totAmount += $totDeb;}
									$htmlStr .= '</tbody>
								</table>';
								
								$totAmount = str_replace(',', '', $this->taka_format($totAmount));
								$Common = new Common($this->db);
								$totalinwords = $Common->makewords($totAmount, 'taka');
								
								$htmlStr .= '<div style="width:97.5%;display: block; border:1px solid #333;padding:8px 10px; margin:15px auto 30px;"><strong>In Word:</strong> '.$totalinwords.' Only.</div>
							</td>
						</tr>
						<tr>
							<td style="padding:0 0 20px">
								<table style="padding:0px" width="100%" border="0" cellspacing="0" cellpadding="0">
								  <tr>
									<td align="center" width="25%">
										<p>'.$preparedBy.'</p>
									</td>
								  </tr>
								  <tr>
									<td align="center" width="25%">
										<div style="width:50%;display: block; border-top:1px dotted #333;padding:8px 10px; margin:0 auto;">
											<strong>Prepared by</strong>
										</div>
									</td>
									<td align="center">
										<div style="width:50%;display: block; border-top:1px dotted #333;padding:8px 10px; margin:0 auto;">
											<strong>Accountant</strong>
										</div>
									</td>
									<td align="center" width="25%">
										<div style="width:50%;display: block; border-top:1px dotted #333;padding:8px 10px; margin:0 auto;">
											<strong>Approved by</strong>
										</div>
									</td>
									<td align="center" width="25%">
										<div style="width:50%;display: block; border-top:1px dotted #333;padding:8px 10px; margin:0 auto;">
											<strong>Received by</strong>
										</div>
									</td>
								  </tr>
							</table>
							</td>
						</tr>
					</table>
				</div>';
			}
			$htmlStr .= '</div>
			</body>
			</html>';
		}
		return $htmlStr;
	}
	
	private function posPayments($customers_id, $paymentAmount, $edit=0){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$user_id = $_SESSION["user_id"]??0;
		$Common = new Common($this->db);
		
		$dataObj = $this->db->query("SELECT pos_id, taxes_percentage1, tax_inclusive1, taxes_percentage2, tax_inclusive2, transport FROM pos WHERE accounts_id = $accounts_id AND customer_id = $customers_id AND pos_publish = 1 AND (pos_type = 'Sale' OR (pos_type in ('Order', 'Repairs') AND order_status = 2)) AND is_due = 1 ORDER BY pos_id ASC", array());
		if($dataObj){
			while($oneRow = $dataObj->fetch(PDO::FETCH_OBJ)){
				$pos_id = $oneRow->pos_id;
				
				$iTaxable = $iNonTaxable = 0;
				$query = $this->db->query("SELECT sales_price, shipping_qty, discount_is_percent, discount, taxable FROM pos_cart WHERE pos_id = $pos_id", array());
				if($query){
					while($pos_cartrow = $query->fetch(PDO::FETCH_OBJ)){
						$sales_price = $pos_cartrow->sales_price;
						$shipping_qty = $pos_cartrow->shipping_qty;

						$qtyvalue = round($sales_price*$shipping_qty,2);
						
						$discount_is_percent = $pos_cartrow->discount_is_percent;
						$discount = $pos_cartrow->discount;
						if($discount_is_percent>0){
							$discount_value = round($qtyvalue*0.01*$discount,2);
						}
						else{
							$discount_value = round($discount,2);
						}

						$taxable = $pos_cartrow->taxable;
						if($taxable>0){
							$iTaxable += $qtyvalue-$discount_value;
						}
						else{
							$iNonTaxable += $qtyvalue-$discount_value;
						}												
					}
				}
				
				$iTaxableExclude = $iTaxable+$iNonTaxable;
				$taxes_total1 = $Common->calculateTax($iTaxable, $oneRow->taxes_percentage1, $oneRow->tax_inclusive1);
				$taxes_total2 = $Common->calculateTax($iTaxable, $oneRow->taxes_percentage2, $oneRow->tax_inclusive2);
				$iTaxes = $taxes_total1+$taxes_total2;
				$iTransport = $oneRow->transport;
				$iPayment = $Common->getInvoicePayment($pos_id);
				
				$totalDues = $iTaxableExclude+$iTaxes+$iTransport-$iPayment;
				if($paymentAmount>0 && $totalDues>0){
					$payment_amount = $totalDues;
					if($paymentAmount<$totalDues){$payment_amount = $paymentAmount;}
					$ppData = array('pos_id' => $pos_id,
									'payment_method' => 'Cash',
									'payment_amount' => round($payment_amount,2),
									'payment_datetime' => date('Y-m-d H:i:s'),	
									'user_id' => $user_id,
									'more_details' => '',
									'drawer' => '');
					$pos_payment_id = $this->db->insert('pos_payment', $ppData);
					if($pos_payment_id){
						$paymentAmount -= $payment_amount;
						$totalDues -= $payment_amount;
					}
				}
				
				if($totalDues<=0){
					$this->db->update('pos', array('is_due'=>0), $pos_id);
				}
				if($paymentAmount<=0){return true;}				
			}
		}
		
		return true;							
	}
	
	//===========Voucher2 Page==================//
	private function voucher2(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$this->pageTitle = $GLOBALS['title'];
		$list_filters = $_SESSION['list_filters']??array();
		
		$this->publish = $fpublish = $list_filters['fpublish']??">0";
		$fvoucher_type = $this->voucher_type;
		$this->account_type = $faccount_type = $list_filters['faccount_type']??0;
		$this->sub_group_id = $fsub_group_id = $list_filters['fsub_group_id']??0;
		$this->ledger_id = $fledger_id = $list_filters['fledger_id']??0;
		$this->keyword_search = $keyword_search = $list_filters['keyword_search']??'';
		
		$this->CountList = 'Count';
		
		$this->Voucher2Data();
		$totalRows = $this->totalRows;
		$subGroOpt = $this->subGroOpt;
		$parLedOpt = $this->parLedOpt;
		
		$page = !empty($GLOBALS['segment4name']) ? intval($GLOBALS['segment4name']):1;
		if($page<=0){$page = 1;}
		if(!isset($_SESSION['limit'])){$_SESSION['limit'] = 'auto';}
		$limit = $_SESSION['limit'];
		
		$this->rowHeight = 34;
		$this->page = $page;
		$this->CountList = 'List';
		$tableRows = $this->Voucher2Data();
		
		$pubOpt = "<option value=\">0\">Pending+Approved</option>";
		$pubOpts = array('=1'=>'Pending', '=2'=>'Approved', '=0'=>'Archived');
		foreach($pubOpts as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($fpublish==$oneOptVal){$selected = ' selected';}
			$pubOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$accTypOpt = "<option value=\"0\">All Account Type</option>";
		$accountTypes = $this->accountTypes();
		foreach($accountTypes as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($faccount_type==$oneOptVal){$selected = ' selected';}
			$accTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
				
		$limOpt = '';
		$limOpts = array(15, 20, 25, 50, 100, 500);
		foreach($limOpts as $oneOpt){
			$selected = '';
			if($limit==$oneOpt){$selected = ' selected';}
			$limOpt .= "<option$selected value=\"$oneOpt\">$oneOpt</option>";
		}
		
		$ledAutComData = array();
		$ledSql = "SELECT ledger_id, name FROM ledger WHERE accounts_id = $prod_cat_man AND (visible_on='' OR visible_on LIKE CONCAT('%', :fvoucher_type, '%')) AND ledger_count = 0 AND ledger_publish = 1 ORDER BY parent_ledger_id ASC, name ASC";
		$tableObj = $this->db->query($ledSql, array('fvoucher_type'=>$fvoucher_type));
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$name = stripslashes($oneRow->name);
				$ledAutComData[] = array('label' => $name, 'lId' => $oneRow->ledger_id);
			}
		}
		$ledAutComRes = json_encode($ledAutComData);
		
		$innerHTMLStr = "<script type=\"text/javascript\">var ledAutComRes = $ledAutComRes</script>
		<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<input type=\"hidden\" name=\"page\" id=\"page\" value=\"$this->page\">
		<input type=\"hidden\" name=\"rowHeight\" id=\"rowHeight\" value=\"$this->rowHeight\">
		<input type=\"hidden\" name=\"totalTableRows\" id=\"totalTableRows\" value=\"$this->totalRows\">
		<input type=\"hidden\" name=\"fvoucher_type\" id=\"fvoucher_type\" value=\"$this->voucher_type\">
		<div class=\"row\">
			<div class=\"col-sm-12 col-md-2\">
				<button class=\"btn cursor hilightbutton p2x10\" onClick=\"AJgetVoucher2Popup(0, $fvoucher_type);\" title=\"Create New $GLOBALS[title]\">
					<i class=\"fa fa-plus\"></i> $GLOBALS[title]
				</button>
			</div>
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<select name=\"fpublish\" id=\"fpublish\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$pubOpt
				</select>
			</div>
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<select name=\"faccount_type\" id=\"faccount_type\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$accTypOpt
				</select>
			</div>				
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<select name=\"fsub_group_id\" id=\"fsub_group_id\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$subGroOpt
				</select>
			</div>
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<select name=\"fledger_id\" id=\"fledger_id\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$parLedOpt
				</select>
			</div>
			<div class=\"col-sm-3 col-md-2 pbottom10 pleft0\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Search Information\" value=\"$keyword_search\" id=\"keyword_search\" name=\"keyword_search\" class=\"form-control\" maxlength=\"50\" />
					<span class=\"input-group-addon cursor\" onClick=\"checkAndLoadFilterData();\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Search Information\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"row\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
						<thead class=\"cf\">
							<tr>
								<th align=\"left\" width=\"10%\">Created Date</th>
								<th align=\"left\" width=\"10%\">Voucher No.</th>
								<th align=\"left\">Ledger Name</th>
								<th align=\"center\" width=\"20%\">Narration</th>
								<th align=\"center\" width=\"10%\">Debit</th>
								<th align=\"center\" width=\"10%\">Credit</th>
							</tr>
						</thead>
						<tbody id=\"tableRows\">
							$tableRows
						</tbody>
					</table>
				</div>
			</div>    
		</div>
		<div class=\"row mtop10\">
			<div class=\"col-xs-12\">
				<select class=\"form-control width100 floatleft\" name=\"limit\" id=\"limit\" onChange=\"checkloadTableRows();\">
					<option value=\"auto\">Auto</option>
					$limOpt
				</select>
				<label id=\"fromtodata\"></label>
				<div class=\"floatright\" id=\"Pagination\"></div>
			</div>
		</div>";
		
		$htmlStr = $this->htmlBody($innerHTMLStr);
		return $htmlStr;
	}
	
	public function AJgetPage_Voucher2(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$fpublish = $_POST['fpublish']??">0";
		$fvoucher_type = $_POST['fvoucher_type']??5;
		$faccount_type = $_POST['faccount_type']??0;
		$fsub_group_id = $_POST['fsub_group_id']??0;
		$fledger_id = $_POST['fledger_id']??0;
		$keyword_search = $_POST['keyword_search']??'';
		
		$totalRows = $_POST['totalRows']??0;
		$rowHeight = $_POST['rowHeight']??34;
		$page = $_POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $_POST['limit']??'auto';
		
		$this->publish = $fpublish;
		$this->voucher_type = $fvoucher_type;
		$this->account_type = $faccount_type;
		$this->sub_group_id = $fsub_group_id;
		$this->ledger_id = $fledger_id;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		if($prod_cat_man==0){
			$jsonResponse['login'] = 'session_ended';
		}
		else{
			//===If filter options changes===//
			if($GLOBALS['segment4name']=='filter'){
				$this->CountList = 'Count';
				$this->Voucher2Data();
				$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
				$jsonResponse['subGroOpt'] = $this->subGroOpt;
				$jsonResponse['parLedOpt'] = $this->parLedOpt;
			}
			
			$this->page = $page;
			$this->totalRows = $totalRows;
			$this->rowHeight = $rowHeight;
			$this->CountList = 'List';
			
			$jsonResponse['tableRows'] = $this->Voucher2Data();
		}
		return json_encode($jsonResponse);
	}
	
	private function Voucher2Data(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$reponseData = array('login'=>'');
		if($prod_cat_man==0){$reponseData['login'] = 'session_ended';}
		
		$fpublish = $this->publish;
		$fvoucher_type = $this->voucher_type;
		$faccount_type = $this->account_type;
		$fsub_group_id = $this->sub_group_id;
		$fledger_id = $this->ledger_id;
		$keyword_search = $this->keyword_search;
		$CountList = $this->CountList;
		
		$_SESSION["current_module"] = "subGroup";
		$_SESSION["list_filters"] = array('fpublish'=>$fpublish, 'fvoucher_type'=>$fvoucher_type, 'faccount_type'=>$faccount_type, 'fsub_group_id'=>$fsub_group_id, 'fledger_id'=>$fledger_id, 'keyword_search'=>$keyword_search);
		
		$filterSql = "FROM voucher v, voucher_list vl, ledger l WHERE v.accounts_id = $accounts_id AND v.voucher_type = $fvoucher_type AND v.voucher_publish$fpublish AND ";
		$bindData = array();
		if($faccount_type >0){$filterSql .= "l.account_type = $faccount_type AND ";}		
		if($fsub_group_id >0){$filterSql .= "l.sub_group_id = $fsub_group_id AND ";}		
		if($fledger_id >0){$filterSql .= "l.ledger_id = $fledger_id AND ";}		
		
		if($faccount_type >0){
			$filterSql .= "l.account_type = :account_type AND ";
			$bindData['account_type'] = $faccount_type;
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim($keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= "CONCAT_WS(' ', v.voucher_no, vl.narration) LIKE CONCAT('%', :keyword_search$num, '%') AND ";
				$bindData['keyword_search'.$num] = trim($keyword_searches[$num]);
				$num++;
			}
		}
		$filterSql .= " v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id";
		if($CountList == 'Count'){
			$totalRows = 0;
			$subGroIds = $LedIds = array();
			$sql = "SELECT l.sub_group_id, l.ledger_id $filterSql";
			$query = $this->db->querypagination($sql, $bindData);
			if($query){
				$totalRows = count($query);
				foreach($query as $getOneRow){
					$subGroIds[$getOneRow['sub_group_id']] = '';
					if($getOneRow['ledger_id']>0)
						$LedIds[$getOneRow['ledger_id']] = '';
				}
			}
			
			$subGroOpt = "<option value=\"0\">Select Sub-Group Name</option>";
			if(!empty($subGroIds)){
				$tableObj = $this->db->query("SELECT sub_group_id, name FROM sub_group WHERE sub_group_id IN (".implode(', ', array_keys($subGroIds)).") AND sub_group_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$subGroOpt .= "<option value=\"$oneRow->sub_group_id\">".stripslashes(trim($oneRow->name))."</option>";
					}
				}				
			}
			
			$parLedOpt = "<option value=\"0\">Parent Ledger</option>";
			if(!empty($LedIds)){
				$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($LedIds)).") AND ledger_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$parLedOpt .= "<option value=\"$oneRow->ledger_id\">".stripslashes(trim($oneRow->name))."</option>";
					}
				}
			}
			
			$this->totalRows = $totalRows;
			$this->subGroOpt = $subGroOpt;
			$this->parLedOpt = $parLedOpt;
		}
		else{
			$limit = $_SESSION["limit"];		
			$rowHeight = $this->rowHeight;
			$page = $this->page;
			$totalRows = $this->totalRows;
			if(in_array($limit, array('', 'auto'))){
				$screenHeight = $_COOKIE['screenHeight']??480;
				$headerHeight = $_COOKIE['headerHeight']??300;
				$bodyHeight = floor($screenHeight-$headerHeight);
				$limit = floor($bodyHeight/$rowHeight);
				if($limit<=0){$limit = 1;}
			}
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			
			$sql = "SELECT v.voucher_id, v.voucher_date, v.voucher_no, v.voucher_publish, vl.ledger_id, vl.narration, vl.debit_credit, vl.amount $filterSql ORDER BY v.voucher_no DESC LIMIT $starting_val, $limit";
			$dataObj = $this->db->querypagination($sql, $bindData);
			$tableRows = '';
			if($dataObj){
				$sl=$starting_val;
				$accountTypes = $this->accountTypes();
				$debitCredits = $this->debitCredits();
				$LedIds = $voucherData = $vDateNote = array();
				foreach($dataObj as $oneRow){
					$LedIds[$oneRow['ledger_id']] = '';
					$vDateNote[$oneRow['voucher_id']] = array($oneRow['voucher_no'], $oneRow['voucher_date'], $oneRow['voucher_publish']);
					$voucherData[$oneRow['voucher_id']][] = array($oneRow['ledger_id'], $oneRow['narration'], $oneRow['debit_credit'], $oneRow['amount']);
				}
				
				if(!empty($LedIds)){
					$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($LedIds)).") AND ledger_publish = 1 ORDER BY name ASC", array());
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
						}
					}
				}
				
				if(!array_key_exists(0, $LedIds)){$LedIds[0] = 'Parent';}
				$totDeb = $totCre = 0;
				foreach($voucherData as $voucher_id=>$voucherInfo){
					$vDateNoteData = $vDateNote[$voucher_id];
					$voucher_no = $vDateNoteData[0];
					$voucher_date = $vDateNoteData[1];
					$voucher_publish = $vDateNoteData[2];
					$colSpan = count($voucherInfo);
					$rowClass = '';
					if($voucher_publish==0){$rowClass = ' class="errormsg"';}
					elseif($voucher_publish==1){$rowClass = ' class="lightyellowrow"';}
					
					$editIcon = '<br>';
					if($voucher_publish==2){
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJupdate_Data('voucher', $voucher_id, 'Back to pending approve Voucher# $voucher_no', 'voucher_publish', 1);\" title=\"Back to pending approve\"><i class=\"fa fa-arrow-circle-left errormsg txt18\"></i></a>";
					}
					elseif($voucher_publish==1){
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJupdate_Data('voucher', $voucher_id, 'Archive Voucher# $voucher_no', 'voucher_publish', 0);\" title=\"Archive this Voucher\"><i class=\"fa fa-remove errormsg txt18\"></i></a>";
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJupdate_Data('voucher', $voucher_id, 'Approve Voucher# $voucher_no', 'voucher_publish', 2);\" title=\"Approve this Voucher\"><i class=\"fa fa-check-square successmsg txt18\"></i></a>";
					}
					elseif($voucher_publish==0){
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJupdate_Data('voucher', $voucher_id, 'Back to pending Voucher# $voucher_no', 'voucher_publish', 1);\" title=\"Back to pending approve\"><i class=\"fa fa-check errormsg txt18\"></i></a>";
					}
					
					$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"AJgetVoucher2Popup($voucher_id, $fvoucher_type);\" title=\"Change this Voucher Information\"><i class=\"fa fa-edit txt18\"></i></a>";
					if($voucher_publish>0){
						$editIcon .= "<a href=\"javascript:void(0);\" onClick=\"printbyuri('/Accounts/voucher2Print/$voucher_id');\" title=\"Print this Voucher Information\"><i class=\"fa fa-print txt18\"></i></a>";
					}
					
					$l=0;
					$voucherCols = "<td rowspan=\"$colSpan\" data-title=\"Created Date\" align=\"center\">".date('d.m.Y', strtotime($voucher_date))."</td>
		<td data-title=\"Voucher#\" nowrap align=\"center\" rowspan=\"$colSpan\">$voucher_no $editIcon</td>";
					
					foreach($voucherInfo as $oneRow){
						$l++;
						if($l>1){$voucherCols = '';}
						$ledger_id = $oneRow[0];
						$narration = $oneRow[1];
						$debit_credit = $oneRow[2];
						$amount = $oneRow[3];
						$ledgerName = $LedIds[$ledger_id];
						$debit = $credit = 0;
						if($debit_credit==1){$debit = $amount;}
						else{$credit = $amount;}
						$totDeb += $debit;
						$totCre += $credit;
						$tableRows .= "<tr$rowClass>$voucherCols
		<td data-title=\"Ledger Name\" align=\"left\">".stripslashes($ledgerName)."</td>
		<td data-title=\"Narration\" align=\"left\">".stripslashes($narration)."</td>
		<td data-title=\"Debit\" align=\"right\">".$this->taka_format($debit, 2, 'TK')."</td>
		<td data-title=\"Credit\" align=\"right\">".$this->taka_format($credit, 2, 'TK')."</td>
		</tr>";
					}
				}
				$tableRows .= "<tr class=\"lightpinkrow\">
		<td data-title=\"Grand Total\" align=\"right\" colspan=\"4\"><strong>Grand Total: </strong></td>
		<td data-title=\"Debit\" align=\"right\"><strong>".$this->taka_format($totDeb, 2, 'TK')."</strong></td>
		<td data-title=\"Credit\" align=\"right\"><strong>".$this->taka_format($totCre, 2, 'TK')."</strong></td>
		</tr>";
			}
			else{
				$tableRows .= "<tr>
		<td colspan=\"6\" data-title=\"No data found\">There is no data found</td>
		</tr>";
			}
			return $tableRows;
		}
	}
	
	public function AJgetVoucher2Popup(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$voucher_id = intval(trim($_POST['voucher_id']??0));
			$voucher_type = intval(trim($_POST['voucher_type']??1));
			$voucherLists = array();
			$voucher2Data = array();
			$voucher2Data['login'] = '';
			$voucher2Data['voucher_id'] = 0;
			$voucher2Data['pi_invoice_no'] = '';
			$voucher2Data['pi_invoice_date'] = date('Y-m-d');
			$voucher2Data['lc_phone_no'] = '';
			$voucher2Data['lc_date'] = date('Y-m-d');
			$voucher_date = date('Y-m-d');
			$voucher_no = 0;
			if($voucher_id>0){
				$oneRow = $this->getOneRowById('voucher', $voucher_id);
				if($oneRow){
					$voucher2Data['voucher_id'] = $oneRow->voucher_id;
					$voucher_date = $oneRow->voucher_date;
					$voucher_type = $oneRow->voucher_type;
					$voucher_no = $oneRow->voucher_no;
					$voucher2Data['pi_invoice_no'] = $oneRow->pi_invoice_no;
					$voucher2Data['pi_invoice_date'] = $oneRow->pi_invoice_date;
					$voucher2Data['lc_phone_no'] = $oneRow->lc_phone_no;
					$voucher2Data['lc_date'] = $oneRow->lc_date;
					
					$voucherListObj = $this->db->query("SELECT * FROM voucher_list WHERE voucher_id = $voucher_id ORDER BY voucher_list_id ASC", array());
					if($voucherListObj){
						while($listOneRow = $voucherListObj->fetch(PDO::FETCH_OBJ)){
							$ledgerName = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
							$narration = stripslashes($listOneRow->narration);
							$voucherLists[] = array($listOneRow->voucher_list_id, $listOneRow->ledger_id, $ledgerName, $narration, $listOneRow->debit_credit, $listOneRow->qty, $listOneRow->unit_price, $listOneRow->amount);
						}
					}
				}
			}
			
			if($voucher_no == 0){
				$lastVObj = $this->db->query("SELECT voucher_date FROM voucher WHERE voucher_type = $voucher_type ORDER BY voucher_id DESC LIMIT 0, 1", array());
				if($lastVObj){
					while($lastVOneRow = $lastVObj->fetch(PDO::FETCH_OBJ)){
						$voucher_date = $lastVOneRow->voucher_date;
					}
				}
				$voucher_no = $this->getVoucherNo($voucher_date, $voucher_type, $voucher_id);
			}
			$voucher2Data['voucher_date'] = $voucher_date;
			$voucher2Data['voucher_no'] = $voucher_no;
			
			$voucherTypes = $this->voucherTypes();
			$voucherType = $voucherTypes[$voucher_type];
			$voucher2Data['voucher_type'] = $voucher_type;
			$voucher2Data['voucherType'] = $voucherType;
			$voucher2Data['voucher_no'] = $voucher_no;
			$voucher2Data['voucherLists'] = $voucherLists;
			
			return json_encode($voucher2Data);
		}
	}
	
	public function AJsaveVoucher2(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$user_id = $_SESSION["user_id"]??0;
		
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$savemsg = $message = '';
			
			$voucher_id = $_POST['voucher_id']??0;
			$voucher_type = $_POST['voucher_type']??0;
			$voucher_no = $_POST['voucher_no']??0;
			$voucher_date = $_POST['voucher_date']??'1000-01-01';
			
			$pi_invoice_no = $_POST['pi_invoice_no']??0;
			$pi_invoice_date = date('Y-m-d', strtotime($_POST['pi_invoice_date']??'1000-01-01'));
			$lc_phone_no = $_POST['lc_phone_no']??0;
			$lc_date = date('Y-m-d', strtotime($_POST['lc_date']??'1000-01-01'));
			
			$voucher_listIds = $_POST['voucher_list_id']??array();
			$ledgerIds = $_POST['ledger_id']??array();
			$narrationIds = $_POST['narration']??array();
			$debCreIds = $_POST['debit_credit']??array();
			$qtyIds = $_POST['qty']??array();
			$unitPriceIds = $_POST['unit_price']??array();
			$debitIds = $_POST['debit']??array();
			$creditIds = $_POST['credit']??array();
						
			if(empty($voucher_date) || strlen($voucher_date)<10){$voucher_date = date('Y-m-d');}
			else{$voucher_date = date('Y-m-d', strtotime($voucher_date));}
			$voucherTypes = $this->voucherTypes();
			$voucherType = $voucherTypes[$voucher_type];
			
			if($voucher_id==0){
				$voucher_no = $this->getVoucherNo($voucher_date, $voucher_type, $voucher_id);
			}
			
			if($voucher_no>0 && $voucher_type>0){
				$saveData = array();
				$saveData['accounts_id'] = $accounts_id;
				$saveData['user_id'] = $user_id;
				$saveData['voucher_type'] = $voucher_type;
				$saveData['voucher_no'] = $voucher_no;
				$saveData['voucher_date'] = $voucher_date;
				
				$saveData['pi_invoice_no'] = $pi_invoice_no;
				$saveData['pi_invoice_date'] = $pi_invoice_date;
				$saveData['lc_phone_no'] = $lc_phone_no;
				$saveData['lc_date'] = $lc_date;
				
				$duplSql = "SELECT voucher_publish, voucher_id FROM voucher WHERE accounts_id = $accounts_id AND voucher_date = :voucher_date AND voucher_no = :voucher_no AND voucher_type = :voucher_type";
				$bindData = array('voucher_date'=>$voucher_date, 'voucher_no'=>$voucher_no, 'voucher_type'=>$voucher_type);
				if($voucher_id>0){
					$duplSql .= " AND voucher_id != :voucher_id";
					$bindData['voucher_id'] = $voucher_id;
				}
				$duplSql .= " LIMIT 0, 1";
				$duplRows = 0;
				$subGroObj = $this->db->querypagination($duplSql, $bindData);
				if($subGroObj){
					foreach($subGroObj as $onerow){
						$duplRows = 1;
						$voucher_publish = $onerow['voucher_publish'];
						if($voucher_id==0 && $voucher_publish==0){
							$voucher_id = $onerow['voucher_id'];
							$this->db->update('voucher', array('voucher_publish'=>1), $voucher_id);
							$duplRows = 0;
							$returnStr = 'Update';
						}
					}
				}				
				
				if($duplRows>0){
					$savemsg = 'error';
					$returnStr = "<p>$voucherType::$voucher_no already exists. Please try again different voucher no or type.</p>";
				}
				else{
					if($voucher_id==0){
						$saveData['created_on'] = date('Y-m-d H:i:s');
						$saveData['voucher_publish'] = 2;
						$voucher_id = $this->db->insert('voucher', $saveData);
						if(!$voucher_id){
							$savemsg = 'error';
							$message .= "<p>Opps!! Could not insert.</p>";
						}
					}
					else{
						$prevVoucherNo = $prevVoucherType = '';
						$onerow = $this->getOneRowById('voucher', $voucher_id);
						if($onerow){
							$prevVoucherNo = $onerow->voucher_no;
							$prevVoucherType = $voucherTypes[$onerow->voucher_type];
						}
						$update = $this->db->update('voucher', $saveData, $voucher_id);
						if($update){					
							$teData = $changed = $moreInfo = array();
							if($prevVoucherNo != $voucher_no){
								$changed['voucher_no'] = array($prevVoucherNo, $voucher_no);
							}					
							if($prevVoucherType != $voucherType){
								$changed['voucher_type'] = array($prevVoucherType, $voucherType);
							}					
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $accounts_id;
							$teData['user_id'] = $user_id;
							$teData['record_for'] = 'voucher';
							$teData['record_id'] = $voucher_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);					
						}
					}
				}
				
				if($savemsg ==''){
					$vlIds = array();
					$dataObj = $this->db->querypagination("SELECT voucher_list_id FROM voucher_list WHERE voucher_id = $voucher_id", array());
					if($dataObj){
						foreach($dataObj as $oneRow){
							$vlIds[$oneRow['voucher_list_id']] = '';
						}
					}
					
					if(!empty($ledgerIds)){
						for($l=0;$l<count($ledgerIds); $l++){
							$voucher_list_id = $voucher_listIds[$l];
							$ledger_id = $ledgerIds[$l];
							$narration = $narrationIds[$l];
							$debit_credit = $debCreIds[$l];
							$qty = $qtyIds[$l];
							$unit_price = $unitPriceIds[$l];
							$debit = $debitIds[$l];
							$credit = $creditIds[$l];
							$amount = $debit;
							if($debit_credit ==-1){$amount = $credit;}
							
							$saveDLData = array('voucher_id'=>$voucher_id, 'ledger_id'=>$ledger_id, 'narration'=>$narration, 'debit_credit'=>$debit_credit, 'qty'=>$qty, 'unit_price'=>$unit_price, 'amount'=>$amount);
							if($voucher_list_id==0)
								$this->db->insert('voucher_list', $saveDLData);
							else{
								if(array_key_exists($voucher_list_id, $vlIds))
									unset($vlIds[$voucher_list_id]);
								$this->db->update('voucher_list', $saveDLData, $voucher_list_id);
							}
						}
					}			
					
					if(!empty($vlIds)){
						foreach($vlIds as $vlId=>$val){
							$this->db->delete('voucher_list', 'voucher_list_id', $vlId);
						}
					}
				}
			}
			else{
				$savemsg = 'error';
				$message .= "<p>Voucher Type / Voucher no. is missing.</p>";
			}
			
			return json_encode(array('login'=>'', 'voucher_id'=>$voucher_id, 'savemsg'=>$savemsg, 'message'=>$message));
		}
	}
	
	public function voucher2Print(){
		$voucher_id = $GLOBALS['segment4name'];
		if($voucher_id==0){
			return 'There is no voucher found';
		}
		else{
			$accounts_id = $_SESSION["accounts_id"]??0;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$currency = $_SESSION["currency"]??'$';
			$dateformat = $_SESSION["dateformat"]??'m/d/Y';
			$timeformat = $_SESSION["timeformat"]??'12 hour';
			$domainName = OUR_DOMAINNAME;
			
			$htmlStr = '<!DOCTYPE html>
			<html>
			<head>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
				<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
				
				<title>Voucher Information</title>
				<style type="text/css">
					@page {size:portrait;}
					body{ font-family:Arial, sans-serif, Helvetica; width:100%; margin:0; padding:15px 0 0;background:#fff;color:#000;line-height:15px; font-size: 11px;}
					h2{font-size:22px; height:20px; margin-bottom:0; padding-bottom:0; font-weight:500;}
					.h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
					address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
					.pright15{padding-right:15px;}
					.ptop10{padding-top:10px;}
					.pbottom10{padding-bottom:10px;}
					.mbottom0{ margin-bottom:0px;}
					table{border-collapse:collapse;}
					.border th{background:#F5F5F6;}
					.border td, .border th{ border:1px solid #DDDDDD; padding:5px 10px; vertical-align: top;}
					.floatleft{ float:left !important;}
					.floatright{ float:right !important;}
					.w30perc{width:30%;margin-bottom:20px; padding-left:13px; padding-right:13px;}
					.borderbottom{padding:5px 0 5px 0; border-bottom:1px solid #CCC !important;}
				</style>
			</head>
			<body>';
			
			$logo_size = 'Small Logo';
			$logo_placement = 'Left';
			
			$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
			$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;
			$company_info = $invoice_message_above = $invoice_message = $value = '';
			
			$varNameVal = 'invoice_setup';
			$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = '$varNameVal'", array());
			if($varObj){
				$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
				if(!empty($value)){
					$value = unserialize($value);
					extract($value);
				}
			}
							
			$companylogo = "";
			$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
			$pics = glob($filePath."*.png");
			if($pics){
				foreach($pics as $onePicture){
					$onePicture = "//$domainName".str_replace('./', '/', $onePicture);
					if($logo_size=='Large Logo'){
						$companylogo = "<img style=\"max-height:150px;max-width:350px;\" src=\"$onePicture\" title=\"Logo\" />";
					}
					else{
						$companylogo = "<img style=\"max-height:100px;max-width:150px;\" src=\"$onePicture\" title=\"Logo\" />";
					}
				}				
			}
			$oneRow = $this->getOneRowById('voucher', $voucher_id);
			if(!empty($oneRow)){
				$voucher_date = date('d.m.Y', strtotime($oneRow->voucher_date));
				$voucher_type = $oneRow->voucher_type;
				$voucher_no = $oneRow->voucher_no;	
				$voucherTypes = $this->voucherTypes();
				$voucherType = $voucherTypes[$voucher_type];
				$user_id = $oneRow->user_id;
				$preparedBy = stripslashes($this->getOneFieldById('user', $user_id, 'user_first_name'));
				$preparedBy .= ' '.stripslashes($this->getOneFieldById('user', $user_id, 'user_last_name'));
				$voucherListObj = $this->db->query("SELECT * FROM voucher_list WHERE voucher_id = $voucher_id ORDER BY voucher_list_id ASC", array());
				
				$companylogo = "";
				$filePath = "./assets/accounts/a_$accounts_id/app_logo_";
				$pics = glob($filePath."*.png");
				if($pics){
					foreach($pics as $onePicture){
						$onePicture = "//$domainName".str_replace('./', '/', $onePicture);
						$companylogo = "<td rowspan=\"2\" width=\"100\" align=\"left\">
							<img style=\"max-height:100px;max-width:100%;\" src=\"$onePicture\" title=\"Logo\" />
						</td>";
					}				
				}
				
				$logo_size = 'Small Logo';
				$logo_placement = 'Left';
				
				$company_info = $invoice_message_above = $invoice_message = $value = '';
				$customer_name = $customer_address = $customer_phone = $customer_email = $sales_person = 1;
				$secondary_phone = $customer_type = $barcode = $print_price_zero = $notes = 0;
				$varNameVal = 'invoice_setup';
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = '$varNameVal'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
								
				$creditCount = 0;
				$rowCount = 0;
				$voucherData = array();
				if($voucherListObj){
					while($listOneRow = $voucherListObj->fetch(PDO::FETCH_OBJ)){
						if($listOneRow->debit_credit==-1){
							$creditCount++;
						}
						$voucherData[] = $listOneRow;
						$rowCount++;
					}
				}
				
				$htmlStr .= '<div style="position:relative; width:100%; min-height:500px;">
					<table cellpadding="0" cellspacing="1" width="100%">						
						<tr>
							<td align="center">
								<table width="100%" cellpadding="0" cellspacing="0">
									<tr>
										'.$companylogo.'
										<td align="center" style="padding-right:100px">
											<h1 style="height:20px;">'.COMPANYNAME.'</h1>
										</td>
									</tr>
									<tr>
										<td align="center" style="padding-right:100px">
											'.$company_info.'
										</td>
									</tr>
									<tr>
										<td colspan="2" align="center" >
											<h2 style="width:180px;border:1px solid #333;padding:8px 10px; margin-bottom:0px">'.$voucherType.' Voucher</h2>
										</td>
									</tr>';
									
									$vnStr = substr($voucherType, 0, 1)."V-".sprintf("%02d", $voucher_no);
									if($voucher_type==6){
										$fromLebel = 'Customer Name ';
										$pi_invoiceLabel = 'Invoice';
										$qtyLabel = 'Dollar';
										$UnitPriceLabel = 'Currency Rate';
									}
									else{
										$fromLebel = 'Supplier Name ';
										$pi_invoiceLabel = 'PI';
										$qtyLabel = 'Qty';
										$UnitPriceLabel = 'Unit Price';
									}
									
									if($creditCount==1){							
										$fromNames = array();
										foreach($voucherData as $listOneRow){
											if($listOneRow->debit_credit==-1){
												$fromNames[] = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
											}
										}
										$fromName = implode(', ', $fromNames);
										
										$htmlStr .= '<tr>
											<td align="left" colspan="2">
												<br>
												<table width="100%" cellpadding="0" cellspacing="0" style="line-height:30px;">
													<tr>
														<td colspan="6" align="left">
															<strong>'.$fromLebel.' : <span style="text-transform: uppercase;">'.$fromName.'</span></strong>
														</td>
													</tr>
													<tr>
														<td width="33%" align="left">
															<strong>Voucher No. : '.$vnStr.'</strong><br>
															Voucher Date : '.$voucher_date.'
														</td>
														<td width="33%" align="left">
															<strong>'.$pi_invoiceLabel.' No. : '.$oneRow->pi_invoice_no.'</strong><br>
															'.$pi_invoiceLabel.' Date : '.date('d.m.Y', strtotime($oneRow->pi_invoice_date)).'
														</td>
														<td width="33%" align="left">
															<strong>LC No.: '.$oneRow->lc_phone_no.'</strong><br>
															<div>LC Date : '.date('d.m.Y', strtotime($oneRow->lc_date)).'</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>';
									}
									else{
										$htmlStr .= '<tr>
											<td colspan="2" align="right" valign="top">
												<strong>Voucher No. : '.$vnStr.'</strong><br>
												Date : '.$voucher_date.'
											</td>
										</tr>';
									}
								$htmlStr .= '</table>
							</td>
						</tr>
						<tr>
							<td class="ptop15">
								<table class="border" width="100%" cellpadding="0" cellspacing="0">
									<thead>
									<tr>
										<th width="5%">SL#</th>
										<th>Particulars</th>
										<th width="35%">Narrations</th>
										<th width="10%">'.$qtyLabel.'</th>
										<th width="10%">'.$UnitPriceLabel.'</th>';
										
										if($creditCount==1){
											$htmlStr .= '<th width="15%">Amount</th>';
										}
										else{
											$htmlStr .= '<th width="15%">Debit</th>
											<th width="15%">Credit</th>';
										}
										
									$htmlStr .= '</tr>
									</thead>
									<tbody>';
										
										$rowHeight = 100;
										if($rowCount>2 && $creditCount==1){$rowHeight = ceil($rowHeight/($rowCount-1));}
										else{$rowHeight = ceil($rowHeight/$rowCount);}
										if($rowHeight<30){$rowHeight = 30;}
										$totAmount = $totDeb = $totCre = 0;								
										
										if(!empty($voucherData)){
											$sl=0;
											foreach($voucherData as $listOneRow){
												$debit_credit = $listOneRow->debit_credit;
												$qty = $listOneRow->qty;
												$unit_price = $listOneRow->unit_price;
												
												if($creditCount==1){
													if($debit_credit==1){
														$sl++;
														$ledgerName = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
														$narration = stripslashes($listOneRow->narration);
														$amount = $listOneRow->amount;
														$totAmount += $amount;
														
														$htmlStr .= "<tr>
															<td style=\"height:$rowHeight"."px\" valign=\"center\" align=\"right\" data-title=\"SL\">$sl</td>
															<td data-title=\"Particular\" valign=\"center\">$ledgerName</td>
															<td data-title=\"Narration\" valign=\"center\">$narration</td>
															<td data-title=\"Qty\" valign=\"center\">".$this->taka_format($qty, 2, '')."</td>
															<td data-title=\"Unit Price\" valign=\"center\" align=\"right\">".$this->taka_format($unit_price, 2, 'TK')."</td>
															<td data-title=\"Amount\" valign=\"center\" align=\"right\">".$this->taka_format($amount, 2, 'TK')."</td>
														</tr>";
													}
												}
												else{
													$sl++;
													$ledgerName = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
													$narration = stripslashes($listOneRow->narration);
													
													$debit = $credit = 0;
													if($debit_credit==1){$debit = $amount;}
													else{$credit = $amount;}
													$totDeb += $debit;
													$totCre += $credit;
													
													$htmlStr .= "<tr>
														<td style=\"height:$rowHeight"."px\" valign=\"center\" align=\"right\" data-title=\"SL\">$sl</td>
														<td data-title=\"Particular\" valign=\"center\">$ledgerName</td>
														<td data-title=\"Narration\" valign=\"center\">$narration</td>
														<td data-title=\"Qty\" valign=\"center\">".$this->taka_format($qty, 2, '')."</td>
														<td data-title=\"Unit Price\" valign=\"center\" align=\"right\">".$this->taka_format($unit_price, 2, 'TK')."</td>
														<td data-title=\"Debit\" valign=\"center\" align=\"right\">".$this->taka_format($debit, 2, 'TK')."</td>
														<td data-title=\"Credit\" valign=\"center\" align=\"right\">".$this->taka_format($credit, 2, 'TK')."</td>
													</tr>";
												}
											}
											
											if($creditCount==1){
												$htmlStr .= "<tr>
													<td data-title=\"Total\" colspan=\"5\" align=\"right\"><strong>Total:&emsp;</strong></td>
													<td data-title=\"Total Amount\" align=\"right\"><strong>".$this->taka_format($totAmount, 2, 'TK')."</strong></td>
												</tr>";
											}
											else{
												$htmlStr .= "<tr>
													<td data-title=\"Total\" colspan=\"5\" align=\"right\"><strong>Total:&emsp;</strong></td>
													<td data-title=\"Total Debit\"><strong>".$this->taka_format($totDeb, 2, 'TK')."</strong></td>
													<td data-title=\"Total Credit\"><strong>".$this->taka_format($totCre, 2, 'TK')."</strong></td>
												</tr>";
											}
										}
										else{
											$htmlStr .= '<tr>
															<td colspan="6" data-title="No data found">There is no data found</td>
														</tr>';
										}
										if($creditCount !=1){$totAmount += $totDeb;}
									$htmlStr .= '</tbody>
								</table>';
								
								$totAmount = str_replace(',', '', $this->taka_format($totAmount));
								$Common = new Common($this->db);
								$totalinwords = $Common->makewords($totAmount, 'taka');
								
								$htmlStr .= '<div style="width:97.5%;display: block; border:1px solid #333;padding:8px 10px; margin:15px auto 30px;"><strong>In Word:</strong> '.$totalinwords.' Only.</div>
							</td>
						</tr>
						<tr>
							<td style="padding:0 0 20px">
								<table style="padding:0px" width="100%" border="0" cellspacing="0" cellpadding="0">
								  <tr>
									<td align="center" width="25%">
										<p>'.$preparedBy.'</p>
									</td>
								  </tr>
								  <tr>
									<td align="center" width="25%">
										<div style="width:50%;display: block; border-top:1px dotted #333;padding:8px 10px; margin:0 auto;">
											<strong>Prepared by</strong>
										</div>
									</td>
									<td align="center">
										<div style="width:50%;display: block; border-top:1px dotted #333;padding:8px 10px; margin:0 auto;">
											<strong>Accountant</strong>
										</div>
									</td>
									<td align="center" width="25%">
										<div style="width:50%;display: block; border-top:1px dotted #333;padding:8px 10px; margin:0 auto;">
											<strong>Approved by</strong>
										</div>
									</td>
									<td align="center" width="25%">
										<div style="width:50%;display: block; border-top:1px dotted #333;padding:8px 10px; margin:0 auto;">
											<strong>Received by</strong>
										</div>
									</td>
								  </tr>
							</table>
							</td>
						</tr>
					</table>
				</div>';
			}
			$htmlStr .= '</div>
			</body>
			</html>';
		}
		return $htmlStr;
	}
	
	//================Reports:: dayBook===================//		
	public function dayBook(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$this->pageTitle = $GLOBALS['title'];
		$list_filters = $_SESSION['list_filters']??array();
		
		$date_range = $list_filters['date_range']??'';
		$this->date_range = $date_range;
		
		$this->publish = $fpublish = $list_filters['fpublish']??"=2";
		$this->voucher_type = $fvoucher_type = $list_filters['fvoucher_type']??0;
		$this->account_type = $faccount_type = $list_filters['faccount_type']??0;
		$this->sub_group_id = $fsub_group_id = $list_filters['fsub_group_id']??0;
		$this->ledger_id = $fledger_id = $list_filters['fledger_id']??0;
		$this->keyword_search = $keyword_search = $list_filters['keyword_search']??'';
		
		$this->CountList = 'Count';
		
		$this->dayBookData();
		$totalRows = $this->totalRows;
		$subGroOpt = $this->subGroOpt;
		$parLedOpt = $this->parLedOpt;
		$vouTypOpt = $this->vouTypOpt;
		
		$page = !empty($GLOBALS['segment4name']) ? intval($GLOBALS['segment4name']):1;
		if($page<=0){$page = 1;}
		if(!isset($_SESSION['limit'])){$_SESSION['limit'] = 'auto';}
		$limit = $_SESSION['limit'];
		
		$this->rowHeight = 34;
		$this->page = $page;
		$this->CountList = 'List';
		$tableRows = $this->dayBookData();
		
		$pubOpt = "<option value=\"=2\">Approved</option>";
		$pubOpts = array('=1'=>'Pending', '=0'=>'Archived', '>0'=>'Pending+Approved');
		foreach($pubOpts as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($fpublish==$oneOptVal){$selected = ' selected';}
			$pubOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$accTypOpt = "<option value=\"0\">All Account Type</option>";
		$accountTypes = $this->accountTypes();
		foreach($accountTypes as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($faccount_type==$oneOptVal){$selected = ' selected';}
			$accTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$limOpt = '';
		$limOpts = array(15, 20, 25, 50, 100, 500);
		foreach($limOpts as $oneOpt){
			$selected = '';
			if($limit==$oneOpt){$selected = ' selected';}
			$limOpt .= "<option$selected value=\"$oneOpt\">$oneOpt</option>";
		}
		
		$innerHTMLStr = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"/assets/css/daterangepicker.css\" />
		<script type=\"text/javascript\" src=\"/assets/js/moment.min.js\"></script>
		<script type=\"text/javascript\" src=\"/assets/js/daterangepicker.js\"></script>
		<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<input type=\"hidden\" name=\"page\" id=\"page\" value=\"$this->page\">
		<input type=\"hidden\" name=\"rowHeight\" id=\"rowHeight\" value=\"$this->rowHeight\">
		<input type=\"hidden\" name=\"totalTableRows\" id=\"totalTableRows\" value=\"$this->totalRows\">
		<div class=\"row\">
			<div class=\"col-sm-4 col-md-2\">
				<select name=\"fvoucher_type\" id=\"fvoucher_type\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$vouTypOpt
				</select>
			</div>
			<div class=\"col-sm-2 col-md-1 pleft0\">
				<select name=\"fpublish\" id=\"fpublish\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$pubOpt
				</select>
			</div>
			<div class=\"col-sm-2 col-md-1 pleft0 pright0\">
				<select name=\"faccount_type\" id=\"faccount_type\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$accTypOpt
				</select>
			</div>				
			<div class=\"col-sm-4 col-md-2 pright0\">
				<select name=\"fsub_group_id\" id=\"fsub_group_id\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$subGroOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pright0\">
				<select name=\"fledger_id\" id=\"fledger_id\" class=\"form-control\" onChange=\"checkAndLoadFilterData();\">
					$parLedOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pright0\">
				<input type=\"text\" name=\"date_range\" id=\"date_range\" class=\"form-control width180px floatright\" placeholder=\"From to Todate\" value=\"$date_range\" /> 
			</div>
			<div class=\"col-sm-4 col-md-2 pbottom10\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Voucher# / Narration\" value=\"$keyword_search\" id=\"keyword_search\" name=\"keyword_search\" class=\"form-control\" maxlength=\"50\" />
					<span class=\"input-group-addon cursor\" onClick=\"checkAndLoadFilterData();\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Voucher# / Narration\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"row\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
						<thead class=\"cf\">
							<tr>
								<th align=\"left\" width=\"10%\">Voucher Date</th>
								<th align=\"left\" width=\"10%\">Voucher No.</th>
								<th align=\"left\" width=\"25%\">Ledger Name</th>
								<th align=\"center\">Narration</th>
								<th align=\"center\" width=\"10%\">Debit</th>
								<th align=\"center\" width=\"10%\">Credit</th>
							</tr>
						</thead>
						<tbody id=\"tableRows\">
							$tableRows
						</tbody>
					</table>
				</div>
			</div>    
		</div>
		<div class=\"row mtop10\">
			<div class=\"col-xs-12\">
				<select class=\"form-control width100 floatleft\" name=\"limit\" id=\"limit\" onChange=\"checkloadTableRows();\">
					<option value=\"auto\">Auto</option>
					$limOpt
				</select>
				<label id=\"fromtodata\"></label>
				<div class=\"floatright\" id=\"Pagination\"></div>
			</div>
		</div>";
		
		$htmlStr = $this->htmlBody($innerHTMLStr);
		return $htmlStr;
	}
	
	public function AJgetPage_dayBook(){
		$date_range = $_POST['date_range']??'';
		$fpublish = $_POST['fpublish']??2;
		$fvoucher_type = $_POST['fvoucher_type']??'=2';
		$faccount_type = $_POST['faccount_type']??0;
		$fsub_group_id = $_POST['fsub_group_id']??0;
		$fledger_id = $_POST['fledger_id']??0;
		$keyword_search = $_POST['keyword_search']??'';
		$totalRows = $_POST['totalRows']??0;
		$rowHeight = $_POST['rowHeight']??34;
		$page = $_POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $_POST['limit']??'auto';
		
		$this->date_range = $date_range;
		$this->publish = $fpublish;
		$this->voucher_type = $fvoucher_type;
		$this->account_type = $faccount_type;
		$this->sub_group_id = $fsub_group_id;
		$this->ledger_id = $fledger_id;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($GLOBALS['segment4name']=='filter'){
			$this->CountList = 'Count';
			$this->dayBookData();
			$jsonResponse['subGroOpt'] = $this->subGroOpt;
			$jsonResponse['parLedOpt'] = $this->parLedOpt;
			$jsonResponse['vouTypOpt'] = $this->vouTypOpt;
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$this->rowHeight = $rowHeight;
		$this->CountList = 'List';
		
		$jsonResponse['tableRows'] = $this->dayBookData();
		
		return json_encode($jsonResponse);
	}
	
	private function dayBookData(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$reponseData = array('login'=>'');
		if($prod_cat_man==0){$reponseData['login'] = 'session_ended';}
		$voucherTypes = $this->voucherTypes();
		
		$date_range = $this->date_range;
		$fpublish = $this->publish;
		$fvoucher_type = $this->voucher_type;
		$faccount_type = $this->account_type;
		$fsub_group_id = $this->sub_group_id;
		$fledger_id = $this->ledger_id;
		$keyword_search = $this->keyword_search;
		$CountList = $this->CountList;
		
		$_SESSION["current_module"] = "dayBook";
		$_SESSION["list_filters"] = array('faccount_type'=>$faccount_type, 'keyword_search'=>$keyword_search);
		
		$filterSql = "v.voucher_publish$fpublish AND ";
		$bindData = array();
		if(!empty($date_range)){
			$date_ranges = explode(' - ', $date_range);
			if(!empty($date_ranges) && count($date_ranges)>1){
				$startdate = date('Y-m-d', strtotime($date_ranges[0]));
				$enddate = date('Y-m-d', strtotime($date_ranges[1]));
				$filterSql .= "v.voucher_date BETWEEN :startdate AND :enddate AND ";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}				
		}
		if($fvoucher_type >0){
			$filterSql .= "v.voucher_type = :fvoucher_type AND ";
			$bindData['fvoucher_type'] = $fvoucher_type;
		}		
		if($faccount_type >0){
			$filterSql .= "l.account_type = :faccount_type AND ";
			$bindData['faccount_type'] = $faccount_type;
		}		
		if($fsub_group_id >0){
			$filterSql .= "l.sub_group_id = :fsub_group_id AND ";
			$bindData['fsub_group_id'] = $fsub_group_id;
		}		
		if($fledger_id >0){
			$filterSql .= "l.ledger_id = :fledger_id AND ";
			$bindData['fledger_id'] = $fledger_id;
		}		
		if($keyword_search !=''){
			$keyword_search = addslashes(trim($keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= "CONCAT_WS(' ', v.voucher_no, vl.narration) LIKE CONCAT('%', :keyword_search$num, '%') AND ";
				$bindData['keyword_search'.$num] = trim($keyword_searches[$num]);
				$num++;
			}
		}
		
		if($CountList == 'Count'){
			$totalRows = 0;
			$sql = "SELECT v.voucher_type, l.sub_group_id, l.ledger_id FROM voucher v, voucher_list vl, ledger l WHERE $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id GROUP BY v.voucher_type, l.sub_group_id, l.ledger_id";
			$dataObj = $this->db->querypagination($sql, $bindData);
			$vouTypeIds = $subGroIds = $LedIds = array();
			if($dataObj){
				$totalRows = count($dataObj);
				foreach($dataObj as $oneRow){
					$vouTypeIds[$oneRow['voucher_type']] = $voucherTypes[$oneRow['voucher_type']];
					$subGroIds[$oneRow['sub_group_id']] = '';
					if($oneRow['ledger_id']>0)
						$LedIds[$oneRow['ledger_id']] = '';
				}
			}
			
			$vouTypOpt = "<option value=\"0\">All Voucher</option>";
			if(!empty($vouTypeIds)){
				foreach($vouTypeIds as $value=>$label){
					$vouTypOpt .= "<option value=\"$value\">".stripslashes(trim($label))."</option>";
				}				
			}
			$subGroOpt = "<option value=\"0\">Select Sub-Group Name</option>";
			if(!empty($subGroIds)){
				$tableObj = $this->db->query("SELECT sub_group_id, name FROM sub_group WHERE sub_group_id IN (".implode(', ', array_keys($subGroIds)).") AND sub_group_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
						$subGroOpt .= "<option value=\"$oneRow->sub_group_id\">".stripslashes(trim($oneRow->name))."</option>";
					}
				}				
			}
			$parLedOpt = "<option value=\"0\">Parent Ledger</option>";
			if(!empty($LedIds)){
				$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($LedIds)).") AND ledger_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
						$parLedOpt .= "<option value=\"$oneRow->ledger_id\">".stripslashes(trim($oneRow->name))."</option>";
					}
				}
			}
			$this->vouTypOpt = $vouTypOpt;
			$this->subGroOpt = $subGroOpt;
			$this->parLedOpt = $parLedOpt;			
			$this->totalRows = $totalRows;
		}
		else{
			$limit = $_SESSION["limit"];		
			$rowHeight = $this->rowHeight;
			$page = $this->page;
			$totalRows = $this->totalRows;
			if(in_array($limit, array('', 'auto'))){
				$screenHeight = $_COOKIE['screenHeight']??480;
				$headerHeight = $_COOKIE['headerHeight']??300;
				$bodyHeight = floor($screenHeight-$headerHeight);
				$limit = floor($bodyHeight/$rowHeight);
				if($limit<=0){$limit = 1;}
			}
			$starting_val = ($page-1)*$limit;
			if($starting_val>$totalRows){$starting_val = 0;}
			
			$sql = "SELECT v.voucher_id, v.voucher_type, v.voucher_date, v.voucher_no, v.voucher_publish, vl.ledger_id, vl.narration, vl.debit_credit, vl.amount FROM voucher v, voucher_list vl, ledger l WHERE $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id ORDER BY v.voucher_date ASC, vl.voucher_list_id ASC LIMIT $starting_val, $limit";
			$dataObj = $this->db->querypagination($sql, $bindData);
			$tableRows = '';
			if($dataObj){
				$accountTypes = $this->accountTypes();
				$debitCredits = $this->debitCredits();
				
				$LedIds = $voucherData = $vDateNote = array();
				foreach($dataObj as $oneRow){
					$LedIds[$oneRow['ledger_id']] = '';
					$vDateNote[$oneRow['voucher_id']] = array($oneRow['voucher_type'], $oneRow['voucher_no'], $oneRow['voucher_date'], $oneRow['voucher_publish']);
					$voucherData[$oneRow['voucher_id']][] = array($oneRow['ledger_id'], $oneRow['narration'], $oneRow['debit_credit'], $oneRow['amount']);
				}
				
				if(!empty($LedIds)){
					$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($LedIds)).") AND ledger_publish = 1 ORDER BY name ASC", array());
					if($tableObj){
						while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
							$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
						}
					}
				}
				if(!array_key_exists(0, $LedIds)){$LedIds[0] = 'Parent';}
				$totDeb = $totCre = 0;
				foreach($voucherData as $voucher_id=>$voucherInfo){
					$vDateNoteData = $vDateNote[$voucher_id];
					$voucher_type = substr($voucherTypes[$vDateNoteData[0]], 0, 1).'V';
					$voucher_no = $vDateNoteData[1];
					$voucher_date = $vDateNoteData[2];
					$voucher_publish = $vDateNoteData[3];
					$colSpan = count($voucherInfo);
					$rowClass = '';
					if($voucher_publish==0){$rowClass = ' class="errormsg"';}
					elseif($voucher_publish==1){$rowClass = ' class="lightyellowrow"';}
					
					$editIcon = '';						
					if($voucher_publish>0){
						$editIcon .= "<a class=\"floatright mleft10\" href=\"javascript:void(0);\" onClick=\"printbyuri('/Accounts/voucherPrint/$voucher_id');\" title=\"Print this Voucher Information\"><i class=\"fa fa-print txt18\"></i></a>";
					}
					
					$l=0;
					$voucherCols = "<td rowspan=\"$colSpan\" data-title=\"Created Date\" align=\"center\">".date('d.m.Y', strtotime($voucher_date))."</td>
		<td data-title=\"Voucher#\" align=\"left\" rowspan=\"$colSpan\">$voucher_type$voucher_no $editIcon</td>";
					foreach($voucherInfo as $oneRow){
						$l++;
						if($l>1){$voucherCols = '';}
						$ledger_id = $oneRow[0];
						$narration = $oneRow[1];
						$debit_credit = $oneRow[2];
						$amount = $oneRow[3];
						$ledgerName = $LedIds[$ledger_id];
						$debit = $credit = 0;
						if($debit_credit==1){$debit = $amount;}
						else{$credit = $amount;}
						$totDeb += $debit;
						$totCre += $credit;
						$tableRows .= "<tr$rowClass>$voucherCols
		<td data-title=\"Ledger Name\" align=\"left\">".stripslashes($ledgerName)."</td>
		<td data-title=\"Narration\" align=\"left\">".stripslashes($narration)."</td>
		<td data-title=\"Debit\" align=\"right\">".$this->taka_format($debit, 2, 'TK')."</td>
		<td data-title=\"Credit\" align=\"right\">".$this->taka_format($credit, 2, 'TK')."</td>
		</tr>";
					}
				}
				$tableRows .= "<tr class=\"lightpinkrow\">
		<td data-title=\"Grand Total\" align=\"right\" colspan=\"4\"><strong>Grand Total: </strong></td>
		<td data-title=\"Debit\" align=\"right\"><strong>".$this->taka_format($totDeb, 2, 'TK')."</strong></td>
		<td data-title=\"Credit\" align=\"right\"><strong>".$this->taka_format($totCre, 2, 'TK')."</strong></td>
		</tr>";
			}
			else{
				$tableRows .= "<tr>
		<td colspan=\"6\" data-title=\"No data found\">There is no data found</td>
		</tr>";
			}
			return $tableRows;
		}
	}
	
	//===========Reports:: ledgerReport==================//
	public function ledgerReport(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$this->pageTitle = $GLOBALS['title'];
		$list_filters = $_SESSION['list_filters']??array();
		
		$date_range = $list_filters['date_range']??'';
		$this->date_range = $date_range;		
		$this->voucher_type = $fvoucher_type = $list_filters['fvoucher_type']??0;
		$this->account_type = $faccount_type = $list_filters['faccount_type']??0;
		$this->sub_group_id = $fsub_group_id = $list_filters['fsub_group_id']??0;
		$this->ledger_id = $fledger_id = $list_filters['fledger_id']??0;
		$this->keyword_search = $keyword_search = $list_filters['keyword_search']??'';
		
		$tableRows = $this->ledgerReportData();
		
		$subGroOpt = $this->subGroOpt;
		$parLedOpt = $this->parLedOpt;
		$vouTypOpt = $this->vouTypOpt;
		
		$accTypOpt = "<option value=\"0\">All Account Type</option>";
		$accountTypes = $this->accountTypes();
		foreach($accountTypes as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($faccount_type==$oneOptVal){$selected = ' selected';}
			$accTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$innerHTMLStr = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"/assets/css/daterangepicker.css\" />
		<script type=\"text/javascript\" src=\"/assets/js/moment.min.js\"></script>
		<script type=\"text/javascript\" src=\"/assets/js/daterangepicker.js\"></script>
		<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<div class=\"row\">
			<div class=\"col-sm-4 col-md-2\">
				<select name=\"fvoucher_type\" id=\"fvoucher_type\" class=\"form-control\" onChange=\"checkAndLoadData();\">
					$vouTypOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pleft0 pright0\">
				<select name=\"faccount_type\" id=\"faccount_type\" class=\"form-control\" onChange=\"checkAndLoadData();\">
					$accTypOpt
				</select>
			</div>				
			<div class=\"col-sm-4 col-md-2 pright0\">
				<select name=\"fsub_group_id\" id=\"fsub_group_id\" class=\"form-control\" onChange=\"checkAndLoadData();\">
					$subGroOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pright0\">
				<select name=\"fledger_id\" id=\"fledger_id\" class=\"form-control\" onChange=\"checkAndLoadData();\">
					$parLedOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pright0\">
				<input type=\"text\" name=\"date_range\" id=\"date_range\" class=\"form-control width180px floatright\" placeholder=\"From to Todate\" value=\"$date_range\" /> 
			</div>
			<div class=\"col-sm-4 col-md-2 pbottom10\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Voucher# / Narration\" value=\"$keyword_search\" id=\"keyword_search\" name=\"keyword_search\" class=\"form-control\" maxlength=\"50\" />
					<span class=\"input-group-addon cursor\" onClick=\"checkAndLoadData();\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Voucher# / Narration\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"row\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
						<thead class=\"cf\">
							<tr>
								<th align=\"left\">Particular</th>
								<th align=\"left\" width=\"10%\">Opening Balance</th>
								<th align=\"center\" width=\"10%\">Debit</th>
								<th align=\"center\" width=\"10%\">Credit</th>
								<th align=\"left\" width=\"10%\">Closing Balance</th>
							</tr>
						</thead>
						<tbody id=\"tableRows\">
							$tableRows
						</tbody>
					</table>
				</div>
			</div>    
		</div>";
		
		$htmlStr = $this->htmlBody($innerHTMLStr);
		return $htmlStr;
	}
	
	public function AJgetPage_ledgerReport(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			
			$date_range = $_POST['date_range']??'';
			$fvoucher_type = $_POST['fvoucher_type']??'=2';
			$faccount_type = $_POST['faccount_type']??0;
			$fsub_group_id = $_POST['fsub_group_id']??0;
			$fledger_id = $_POST['fledger_id']??0;
			$keyword_search = $_POST['keyword_search']??'';
					
			$this->date_range = $date_range;
			$this->voucher_type = $fvoucher_type;
			$this->account_type = $faccount_type;
			$this->sub_group_id = $fsub_group_id;
			$this->ledger_id = $fledger_id;
			$this->keyword_search = $keyword_search;
			
			$tableRows = $this->ledgerReportData();
			
			$subGroOpt = $this->subGroOpt;
			$parLedOpt = $this->parLedOpt;
			$vouTypOpt = $this->vouTypOpt;
			
			$jsonResponse['subGroOpt'] = $subGroOpt;
			$jsonResponse['parLedOpt'] = $parLedOpt;
			$jsonResponse['vouTypOpt'] = $vouTypOpt;
			$jsonResponse['tableRows'] = $tableRows;
			
			return json_encode($jsonResponse);
		}
	}
	
	private function ledgerReportData(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		
		$date_range = $this->date_range;
		$fvoucher_type = $this->voucher_type;
		$faccount_type = $this->account_type;
		$fsub_group_id = $this->sub_group_id;
		$fledger_id = $this->ledger_id;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "ledgerReport";
		$_SESSION["list_filters"] = array('date_range'=>$date_range, 'fvoucher_type'=>$fvoucher_type, 'faccount_type'=>$faccount_type, 'fsub_group_id'=>$fsub_group_id, 'fledger_id'=>$fledger_id, 'keyword_search'=>$keyword_search);
		
		$filterSql = "v.voucher_publish = 2 AND ";
		$bindData = array();
		if(!empty($date_range)){
			$date_ranges = explode(' - ', $date_range);
			if(!empty($date_ranges) && count($date_ranges)>1){
				$startdate = date('Y-m-d', strtotime($date_ranges[0]));
				$enddate = date('Y-m-d', strtotime($date_ranges[1]));
				$filterSql .= "v.voucher_date BETWEEN :startdate AND :enddate AND ";
				$bindData['startdate'] = $startdate;
				$bindData['enddate'] = $enddate;
			}				
		}
		if($fvoucher_type >0){
			$filterSql .= "v.voucher_type = :fvoucher_type AND ";
			$bindData['fvoucher_type'] = $fvoucher_type;
		}		
		if($faccount_type >0){
			$filterSql .= "l.account_type = :faccount_type AND ";
			$bindData['faccount_type'] = $faccount_type;
		}		
		if($fsub_group_id >0){
			$filterSql .= "l.sub_group_id = :fsub_group_id AND ";
			$bindData['fsub_group_id'] = $fsub_group_id;
		}		
		if($fledger_id >0){
			$filterSql .= "l.ledger_id = :fledger_id AND ";
			$bindData['fledger_id'] = $fledger_id;
		}		
		if($keyword_search !=''){
			$keyword_search = addslashes(trim($keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= "CONCAT_WS(' ', v.voucher_no, vl.narration) LIKE CONCAT('%', :keyword_search$num, '%') AND ";
				$bindData['keyword_search'.$num] = trim($keyword_searches[$num]);
				$num++;
			}
		}
		
		$vouTypeIds = $subGroIds = $LedIds = array();
		$voucherTypes = $this->voucherTypes();
		$accountTypes = $this->accountTypes();
		$debitCredits = $this->debitCredits();
					
		$tableRows = '';
		$sql = "SELECT l.parent_ledger_id, l.sub_group_id, l.parent_sub_ledger_id, vl.ledger_id, v.voucher_id, v.voucher_type, v.voucher_date, v.voucher_no, v.voucher_publish, vl.narration, vl.debit_credit, vl.amount FROM voucher v, voucher_list vl, ledger l WHERE $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id ORDER BY v.voucher_date ASC, vl.voucher_list_id ASC";
		$dataObj = $this->db->querypagination($sql, $bindData);
		if($dataObj){
			$allLedIds = array();
			foreach($dataObj as $oneRow){
				$vouTypeIds[$oneRow['voucher_type']] = $voucherTypes[$oneRow['voucher_type']];
				$subGroIds[$oneRow['sub_group_id']] = '';
				$ledger_id = $oneRow['ledger_id'];
				if($ledger_id>0){$LedIds[$ledger_id] = '';}
				$parent_ledger_id = $oneRow['parent_ledger_id'];
				$parent_sub_ledger_id = $oneRow['parent_sub_ledger_id'];
				if($parent_ledger_id==0){
					$parent_ledger_id = $ledger_id;
					$parent_sub_ledger_id = 0;
					$ledger_id = 0;
				}
				if($parent_sub_ledger_id==0){
					$parent_sub_ledger_id = $ledger_id;
					$ledger_id = 0;
				}
				
				$voucher_id = $oneRow['voucher_id'];
				$debit_credit = $oneRow['debit_credit'];
				$amount = $oneRow['amount'];
				
				if(array_key_exists($parent_ledger_id, $allLedIds)){
					if(array_key_exists($parent_sub_ledger_id, $allLedIds[$parent_ledger_id])){
						if(array_key_exists($ledger_id, $allLedIds[$parent_ledger_id][$parent_sub_ledger_id])){
							if(array_key_exists($voucher_id, $allLedIds[$parent_ledger_id][$parent_sub_ledger_id][$ledger_id])){
								if(array_key_exists($debit_credit, $allLedIds[$parent_ledger_id][$parent_sub_ledger_id][$ledger_id][$voucher_id])){
									$amount += $allLedIds[$parent_ledger_id][$parent_sub_ledger_id][$ledger_id][$voucher_id][$debit_credit];
								}
							}
						}
					}
				}
				$allLedIds[$parent_ledger_id][$parent_sub_ledger_id][$ledger_id][$voucher_id][$debit_credit] = $amount;
			}
								
			$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_publish = 1 ORDER BY name ASC", array());
			if($tableObj){
				while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
					$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
				}
			}
			
			if(!array_key_exists(0, $LedIds)){$LedIds[0] = '';}
			$totOBalance = $totDeb = $totCre = 0;
			foreach($allLedIds as $PLId=>$PLInfo){
				$PLName = $LedIds[$PLId];
				$PLOBalance = $PLtotDeb = $PLtotCre = 0;
				$PLStr = '';
				foreach($PLInfo as $SPLId=>$SPLInfo){
					$SPLName = $LedIds[$SPLId];
					$SPLOBalance = $SPLtotDeb = $SPLtotCre = 0;
					$SPLStr = '';
					foreach($SPLInfo as $LId=>$LInfo){
						$LName = $LedIds[$LId];
						$LOBalance = $LtotDeb = $LtotCre = 0;
						foreach($LInfo as $VId=>$VInfo){
							foreach($VInfo as $DC=>$value){
								if($DC==1){$LtotDeb += $value;}
								else{$LtotCre += $value;}
							}
						}
						$LCBalance = $LOBalance+$LtotDeb-$LtotCre;
						$SPLOBalance += $LOBalance;
						$SPLtotDeb += $LtotDeb;
						$SPLtotCre += $LtotCre;
						
						if(!empty($LName)){
							$SPLStr .= "<tr>
		<td data-title=\"Particular Name\" align=\"left\">&emsp; &emsp;&emsp; &emsp;".stripslashes($LName)."</td>
		<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($LOBalance)."</td>
		<td data-title=\"Debit\" align=\"right\">".$this->taka_format($LtotDeb)."</td>
		<td data-title=\"Credit\" align=\"right\">".$this->taka_format($LtotCre)."</td>
		<td data-title=\"Closing Balance\" align=\"right\">".$this->taka_format($LCBalance)."</td>
		</tr>";
						}
					}
					$SPLCBalance = $SPLOBalance+$SPLtotDeb-$SPLtotCre;
					$PLOBalance += $SPLOBalance;
					$PLtotDeb += $SPLtotDeb;
					$PLtotCre += $SPLtotCre;
					
					if(!empty($SPLName)){
						$PLStr .= "<tr>
		<td data-title=\"Particular Name\" align=\"left\"><strong>&emsp; &emsp;".stripslashes($SPLName)."</strong></td>
		<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($SPLOBalance)."</strong></td>
		<td data-title=\"Debit\" align=\"right\"><strong>".$this->taka_format($SPLtotDeb)."</strong></td>
		<td data-title=\"Credit\" align=\"right\"><strong>".$this->taka_format($SPLtotCre)."</strong></td>
		<td data-title=\"Closing Balance\" align=\"right\"><strong>".$this->taka_format($SPLCBalance)."</strong></td>
		</tr>$SPLStr";
					}
				}
				$PLCBalance = $PLOBalance+$PLtotDeb-$PLtotCre;
				$totOBalance += $PLOBalance;
				$totDeb += $PLtotDeb;
				$totCre += $PLtotCre;
				
				$tableRows .= "<tr>
		<td data-title=\"Particular Name\" align=\"left\"><strong>".stripslashes($PLName)."</strong></td>
		<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($PLOBalance)."</strong></td>
		<td data-title=\"Debit\" align=\"right\"><strong>".$this->taka_format($PLtotDeb)."</strong></td>
		<td data-title=\"Credit\" align=\"right\"><strong>".$this->taka_format($PLtotCre)."</strong></td>
		<td data-title=\"Closing Balance\" align=\"right\"><strong>".$this->taka_format($PLCBalance)."</strong></td>
		</tr>$PLStr";
			}
			$totCBalance = $totOBalance+$totDeb-$totCre;
			$tableRows .= "<tr class=\"lightpinkrow\">
		<td data-title=\"Grand Total\" align=\"right\"><strong>Grand Total: </strong></td>
		<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($totOBalance)."</strong></td>
		<td data-title=\"Debit\" align=\"right\"><strong>".$this->taka_format($totDeb)."</strong></td>
		<td data-title=\"Credit\" align=\"right\"><strong>".$this->taka_format($totCre)."</strong></td>
		<td data-title=\"Closing Balance\" align=\"right\"><strong>".$this->taka_format($totCBalance)."</strong></td>
		</tr>";
		}
		else{
			$tableRows .= "<tr>
		<td colspan=\"6\" data-title=\"No data found\">There is no data found</td>
		</tr>";
		}
		
		$vouTypOpt = "<option value=\"0\">All Voucher</option>";
		if(!empty($vouTypeIds)){
			foreach($vouTypeIds as $value=>$label){
				$vouTypOpt .= "<option value=\"$value\">".stripslashes(trim($label))."</option>";
			}				
		}
		$subGroOpt = "<option value=\"0\">Select Sub-Group Name</option>";
		if(!empty($subGroIds)){
			$tableObj = $this->db->query("SELECT sub_group_id, name FROM sub_group WHERE sub_group_id IN (".implode(', ', array_keys($subGroIds)).") AND sub_group_publish = 1 ORDER BY name ASC", array());
			if($tableObj){
				while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
					$subGroOpt .= "<option value=\"$oneRow->sub_group_id\">".stripslashes(trim($oneRow->name))."</option>";
				}
			}				
		}
		$parLedOpt = "<option value=\"0\">Parent Ledger</option>";
		if(!empty($LedIds)){
			$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($LedIds)).") AND ledger_publish = 1 ORDER BY name ASC", array());
			if($tableObj){
				while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
					$parLedOpt .= "<option value=\"$oneRow->ledger_id\">".stripslashes(trim($oneRow->name))."</option>";
				}
			}
		}
		$this->vouTypOpt = $vouTypOpt;
		$this->subGroOpt = $subGroOpt;
		$this->parLedOpt = $parLedOpt;
			
		return $tableRows;
	}
	
	//===========Reports:: trialBalance==================//
	public function trialBalance(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$this->pageTitle = $GLOBALS['title'];
		$list_filters = $_SESSION['list_filters']??array();
		
		$fdate = $list_filters['fdate']??date('Y-m-d');
		$this->fdate = $fdate;		
		$this->views_type = $fviews_type = $list_filters['fviews_type']??2;
		
		$tableRows = $this->trialBalanceData();
		
		$vieTypOpt = "";
		$viewsTyps = array('1'=>'Group Only', '2'=>'Group + Sub GROUP', '3'=>'Details');
		foreach($viewsTyps as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($fviews_type==$oneOptVal){$selected = ' selected';}
			$vieTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$innerHTMLStr = "<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<div class=\"row\">
			<div class=\"col-sm-4 col-md-7 pright0\">
				<button class=\"btn cursor p2x10 marginright15\" onClick=\"printTrialBalance();\" title=\"Print $this->pageTitle\">
					<i class=\"fa fa-print\"></i> Print
				</button>
			</div>				
			<div class=\"col-sm-4 col-md-3 pbottom10 pright0\">
				<div class=\"input-group\">
					<span class=\"input-group-addon cursor\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Views Type\">
						Views Type
					</span>
					<select name=\"fviews_type\" id=\"fviews_type\" class=\"form-control\" onChange=\"checkAndLoadData();\">
						$vieTypOpt
					</select>
				</div>
			</div>
			<div class=\"col-sm-4 col-md-2 pbottom10\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Trial Balance Date\" value=\"".date('Y-m-d')."\" id=\"fdate\" name=\"fdate\" class=\"form-control\" maxlength=\"10\" />
					<span class=\"input-group-addon cursor\" onClick=\"checkAndLoadData();\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Search Trial Balance\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"row\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
						<thead class=\"cf\">
							<tr>
								<th rowspan=\"2\" align=\"left\">Particular</th>
								<th align=\"center\" colspan=\"2\"> Amount (Balance)</th>
							</tr>
							<tr>
								<th align=\"center\" width=\"150\">Debit</th>
								<th align=\"center\" width=\"150\">Credit</th>
							</tr>
						</thead>
						<tbody id=\"tableRows\">
							$tableRows
						</tbody>
					</table>
				</div>
			</div>    
		</div>";
		
		$htmlStr = $this->htmlBody($innerHTMLStr);
		return $htmlStr;
	}
	
	public function AJgetPage_trialBalance(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			
			$fdate = $_POST['fdate']??date('Y-m-d');
			$fviews_type = $_POST['fviews_type']??2;
					
			$this->fdate = $fdate;
			$this->views_type = $fviews_type;
			
			$tableRows = $this->trialBalanceData();
			
			$jsonResponse['tableRows'] = $tableRows;
			
			return json_encode($jsonResponse);
		}
	}
	
	private function trialBalanceData(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		
		$fdate = $this->fdate;
		$fviews_type = $this->views_type;
		
		$_SESSION["current_module"] = "trialBalance";
		$_SESSION["list_filters"] = array('fdate'=>$fdate, 'fviews_type'=>$fviews_type);
		
		$filterSql = "v.voucher_publish = 2 AND ";
		$bindData = array();
		$lFilter = '';
		if(!empty($fdate)){
			$enddate = date('Y-m-d', strtotime($fdate));
			$filterSql .= "v.voucher_date <= :enddate AND ";
			$bindData['enddate'] = $enddate;
			$lFilter = "substring(created_on,1,10) <='$enddate' AND ";
		}
		
		$ledData = $LedIds = $subGroIds = array();
		
		$tableObj = $this->db->query("SELECT account_type, sub_group_id, ledger_id, name, debit, opening_balance FROM ledger WHERE $lFilter ledger_publish = 1 ORDER BY name ASC", array());
		if($tableObj){
			while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
				$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
				if($oneRow->opening_balance !=0){
					$account_type = $oneRow->account_type;
					$sub_group_id = $oneRow->sub_group_id;
					$subGroIds[$sub_group_id] = '';
					$ledger_id = $oneRow->ledger_id;
					$debit_credit = $oneRow->debit;
					$debit_credit = 1;
					$amount = $oneRow->opening_balance*$debit_credit;
					$debCrAcc = 'D';
					if(in_array($account_type, array(2,3,4))){$debCrAcc = 'C';}
					
					if(array_key_exists($account_type, $ledData)){
						if(array_key_exists($sub_group_id, $ledData[$account_type])){
							if(array_key_exists($debCrAcc, $ledData[$account_type][$sub_group_id])){
								if(array_key_exists($ledger_id, $ledData[$account_type][$sub_group_id][$debCrAcc])){
									$amount += $ledData[$account_type][$sub_group_id][$debCrAcc][$ledger_id];
								}
							}
						}
					}
					$ledData[$account_type][$sub_group_id][$debCrAcc][$ledger_id] = $amount;
				}
			}
		}
		
		$sql = "SELECT l.account_type, l.sub_group_id, l.ledger_id, vl.debit_credit, vl.amount FROM voucher v, voucher_list vl, ledger l WHERE $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id ORDER BY v.voucher_date ASC, vl.voucher_list_id ASC";
		$dataObj = $this->db->query($sql, $bindData);
		if($dataObj){
			$accountTypes = $this->accountTypes();
			$debitCredits = $this->debitCredits();
			
			while($oneRow=$dataObj->fetch(PDO::FETCH_OBJ)){
				$account_type = $oneRow->account_type;
				$sub_group_id = $oneRow->sub_group_id;
				$subGroIds[$sub_group_id] = '';
				$ledger_id = $oneRow->ledger_id;
				$debit_credit = $oneRow->debit_credit;
				$amount = $oneRow->amount*$debit_credit;
				$debCrAcc = 'D';
				if(in_array($account_type, array(2,3,4))){$debCrAcc = 'C';}
				
				if(array_key_exists($account_type, $ledData)){
					if(array_key_exists($sub_group_id, $ledData[$account_type])){
						if(array_key_exists($debCrAcc, $ledData[$account_type][$sub_group_id])){
							if(array_key_exists($ledger_id, $ledData[$account_type][$sub_group_id][$debCrAcc])){
								$amount += $ledData[$account_type][$sub_group_id][$debCrAcc][$ledger_id];
							}
						}
					}
				}
				$ledData[$account_type][$sub_group_id][$debCrAcc][$ledger_id] = $amount;
			}
		
		}
		
		$tableRows = '';
		if(!empty($ledData)){
			if(!empty($subGroIds)){
				$tableObj = $this->db->query("SELECT sub_group_id, name FROM sub_group WHERE sub_group_id IN (".implode(', ', array_keys($subGroIds)).") AND sub_group_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
						$subGroIds[$oneRow->sub_group_id] = stripslashes(trim($oneRow->name));
					}
				}
			}
			
			$totDeb = $totCre = 0;
			ksort($ledData);
			foreach($ledData as $account_type=>$ATInfo){
				$accTypName = $accountTypes[$account_type];
				$ATtotDeb = $ATtotCre = 0;
				$ATStr = '';
				ksort($ATInfo);					
				foreach($ATInfo as $sub_group_id=>$SGInfo){
					$SGName = $subGroIds[$sub_group_id];
					$SGtotDeb = $SGtotCre = 0;
					$SGStr = '';
					foreach($SGInfo as $debCrAcc=>$DCInfo){
						$LADetails = array();
						foreach($DCInfo as $LId=>$triBal){
							
							$debAmount = $creAmount = 0;
							if($debCrAcc=='D'){$debAmount += $triBal;}
							else{$creAmount += $triBal;}
							$SGtotDeb += $debAmount;
							$SGtotCre += $creAmount;
							
							if($fviews_type >2){
								$ledName = '';
								$allLedIds = $this->getLedIds($LId);
								foreach($allLedIds as $oneLID=>$moreIds){
									$ledName = $LedIds[$oneLID];										
									if(!empty($moreIds)){
										foreach($moreIds as $oneLID2=>$moreIds2){
											$ledName2 = $LedIds[$oneLID2];
											if(!empty($moreIds2)){
												foreach($moreIds2 as $oneLID3=>$moreIds3){
													$ledName3 = $LedIds[$oneLID3];
													if(!empty($moreIds3)){
														foreach($moreIds3 as $oneLID4=>$moreIds4){
															$ledName4 = $LedIds[$oneLID4];
															$LADetails[$ledName][$ledName2][$ledName3][$ledName4] = $triBal;
														}
													}
													else{
														$LADetails[$ledName][$ledName2][$ledName3] = $triBal;
													}
												}
											}
											else{
												$LADetails[$ledName][$ledName2] = $triBal;
											}
										}
									}
									else{
										$LADetails[$ledName] = $triBal;
									}
								}
							}
						}
						if(!empty($LADetails)){
							ksort($LADetails);
							foreach($LADetails as $LName1=>$triBalInfo1){
								$debAmount1 = $creAmount1 = 0;
								$SGStr1 = '';
								if(!is_array($triBalInfo1)){
									if($debCrAcc=='D'){$debAmount1 += floatval($triBalInfo1);}
									else{$creAmount1 += floatval($triBalInfo1);}									
								}
								else{
									foreach($triBalInfo1 as $LName2=>$triBalInfo2){
										$debAmount2 = $creAmount2 = 0;
										$SGStr2 = '';
										if(!is_array($triBalInfo2)){
											if($debCrAcc=='D'){$debAmount2 += floatval($triBalInfo2);}
											else{$creAmount2 += floatval($triBalInfo2);}
										}
										else{
											foreach($triBalInfo2 as $LName3=>$triBalInfo3){
												$debAmount3 = $creAmount3 = 0;
												$SGStr3 = '';
												if(!is_array($triBalInfo3)){
													if($debCrAcc=='D'){$debAmount3 += floatval($triBalInfo3);}
													else{$creAmount3 += floatval($triBalInfo3);}
												}
												else{
													foreach($triBalInfo3 as $LName4=>$triBalInfo4){
														$debAmount4 = $creAmount4 = 0;
														if(!is_array($triBalInfo4)){																
															if($debCrAcc=='D'){$debAmount4 += floatval($triBalInfo4);}
															else{$creAmount4 += floatval($triBalInfo4);}
															$debAmount3 += $debAmount4;
															$creAmount3 += $creAmount4;
														}
														
														$debAmountStr = '&nbsp;';
														if($debAmount4 !=0){
															$debAmountStr = $this->taka_format($debAmount4, 2, 'TK');
														}
														$creAmountStr = '&nbsp;';
														if($creAmount4 !=0){
															$creAmountStr = $this->taka_format($creAmount4, 2, 'TK');
														}
														
														if($debAmount4 !=0 || $creAmount4 !=0){
															$SGStr3 .= "<tr>
		<td data-title=\"Particular Name\" align=\"left\">&emsp;&emsp;&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Ledger\" class=\"mtop-6\">&nbsp;$LName4</td>
		<td nowrap data-title=\"Debit\" align=\"right\">$debAmountStr</td>
		<td nowrap data-title=\"Credit\" align=\"right\">$creAmountStr</td>
		</tr>";
														}
													}
												}
												
												$debAmount2 += $debAmount3;
												$creAmount2 += $creAmount3;
												$debAmountStr = '&nbsp;';
												if($debAmount3 !=0){
													$debAmountStr = $this->taka_format($debAmount3, 2, 'TK');
												}
												$creAmountStr = '&nbsp;';
												if($creAmount3 !=0){
													$creAmountStr = $this->taka_format($creAmount3, 2, 'TK');
												}
												if($debAmount3 !=0 || $creAmount3 !=0){
													$SGStr2 .= "<tr>
		<td data-title=\"Particular Name\" align=\"left\">&emsp;&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Ledger\" class=\"mtop-6\">&nbsp;$LName3</td>
		<td nowrap data-title=\"Debit\" align=\"right\">$debAmountStr</td>
		<td nowrap data-title=\"Credit\" align=\"right\">$creAmountStr</td>
		</tr>
		$SGStr3";
												}
											}												
										}
										
										$debAmount1 += $debAmount2;
										$creAmount1 += $creAmount2;
										$debAmountStr = '&nbsp;';
										if($debAmount2 !=0){
											$debAmountStr = $this->taka_format($debAmount2, 2, 'TK');
										}
										$creAmountStr = '&nbsp;';
										if($creAmount2 !=0){
											$creAmountStr = $this->taka_format($creAmount2, 2, 'TK');
										}
										if($debAmount2 !=0 || $creAmount2 !=0){
											$SGStr1 .= "<tr>
		<td data-title=\"Particular Name\" align=\"left\">&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Ledger\" class=\"mtop-6\">&nbsp;$LName2</td>
		<td nowrap data-title=\"Debit\" align=\"right\">$debAmountStr</td>
		<td nowrap data-title=\"Credit\" align=\"right\">$creAmountStr</td>
		</tr>
		$SGStr2";
										}
									}		
								}
								
								$debAmountStr = '&nbsp;';
								if($debAmount1 !=0){
									$debAmountStr = $this->taka_format($debAmount1, 2, 'TK');
								}
								$creAmountStr = '&nbsp;';
								if($creAmount1 !=0){
									$creAmountStr = $this->taka_format($creAmount1, 2, 'TK');
								}
								if($debAmount1 !=0 || $creAmount1 !=0){
									$SGStr .= "<tr>
		<td data-title=\"Particular Name\" align=\"left\">&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Ledger\" class=\"mtop-6\">&nbsp;$LName1</td>
		<td nowrap data-title=\"Debit\" align=\"right\">$debAmountStr</td>
		<td nowrap data-title=\"Credit\" align=\"right\">$creAmountStr</td>
		</tr>
		$SGStr1";
								}
							}
						}
					}
					$ATtotDeb += $SGtotDeb;
					$ATtotCre += $SGtotCre;
					
					if($fviews_type >1){
						$debAmountStr = '&nbsp;';
						if($SGtotDeb !=0){
							$debAmountStr = "&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;".$this->taka_format($SGtotDeb, 2, 'TK');
						}
						$creAmountStr = '&nbsp;';
						if($SGtotCre !=0){
							$creAmountStr = "&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;".$this->taka_format($SGtotCre, 2, 'TK');
						}
						if($SGtotDeb !=0 || $SGtotCre !=0){
							$ATStr .= "<tr class=\"lightbluerow\">
		<td data-title=\"Particular Name\" align=\"left\">&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;<strong>$SGName</strong></td>
		<td nowrap data-title=\"Debit\" align=\"left\"><strong>$debAmountStr</strong></td>
		<td nowrap data-title=\"Credit\" align=\"left\"><strong>$creAmountStr</strong></td>
		</tr>$SGStr";
						}
					}
				}
				
				$totDeb += $ATtotDeb;
				$totCre += $ATtotCre;
				
				if($ATtotDeb !=0 || $ATtotCre !=0){
					$debAmountStr = '&nbsp;';
					if($ATtotDeb !=0){
						$debAmountStr = $this->taka_format($ATtotDeb, 2, 'TK');
					}
					$creAmountStr = '&nbsp;';
					if($ATtotCre !=0){
						$creAmountStr = $this->taka_format($ATtotCre, 2, 'TK');
					}
					if($ATtotDeb !=0 || $ATtotCre !=0){
						$tableRows .= "<tr class=\"lightgreenrow\">
		<td data-title=\"Particular Name\" align=\"left\"><strong><img src=\"/assets/images/Accounts/firstarrow.png\" alt=\"Group\" class=\"mtop-6\">&nbsp$accTypName</strong></td>
		<td nowrap data-title=\"Debit\" align=\"left\"><strong>$debAmountStr</strong></td>
		<td nowrap data-title=\"Credit\" align=\"left\"><strong>$creAmountStr</strong></td>
		</tr>$ATStr";
					}
				}
			}
			
			$debAmountStr = '&nbsp;';
			if($totDeb !=0){
				$debAmountStr = $this->taka_format($totDeb, 2, 'TK');
			}
			$creAmountStr = '&nbsp;';
			if($totCre !=0){
				$creAmountStr = $this->taka_format($totCre, 2, 'TK');
			}
			if($totDeb !=0 || $totCre !=0){
				$tableRows .= "<tr class=\"lightpinkrow\">
		<td data-title=\"Particular Name\" align=\"right\"><strong>Grand Total:</strong></td>
		<td nowrap data-title=\"Debit\" align=\"right\"><strong>$debAmountStr</strong></td>
		<td nowrap data-title=\"Credit\" align=\"right\"><strong>$creAmountStr</strong></td>
		</tr>";
			}			
		}
		else{
			$tableRows .= "<tr>
		<td colspan=\"3\" data-title=\"No data found\">There is no data found</td>
		</tr>";
		}
		
		return $tableRows;
	}
	
	//================Common Function ===================//
	private function getOneRowById($tablename, $idVal){
		$returnObj = array();
		$idName = $tablename.'_id';
		$ledObj = $this->db->query("SELECT * FROM $tablename WHERE $idName = $idVal", array());
		if($ledObj){
			$returnObj = $ledObj->fetch(PDO::FETCH_OBJ);
		}
		return $returnObj;
	}
	
	private function getOneFieldById($tablename, $idVal, $fieldName){
		$returnVal = '';
		$idName = $tablename.'_id';
		$ledObj = $this->db->query("SELECT $fieldName FROM $tablename WHERE $idName = $idVal", array());
		if($ledObj){
			$returnVal = $ledObj->fetch(PDO::FETCH_OBJ)->$fieldName;
		}
		return $returnVal;
	}
	
	private function getAllSonLedgerIds($ledId){
		$ledgerIds = array();
		if($ledId>0){
			$tableObj = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_ledger_id = $ledId ORDER BY ledger_id ASC", array());
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$ledgerIds[] = $oneRow->ledger_id;
				}
			}
		}
		return $ledgerIds;
	}
	
	private function getArrows($ledId, $parId){
		$arrow = 0;
		if($parId>0){
			$arrow++;
			$parId1 = 0;
			$tableObj = $this->db->querypagination("SELECT parent_ledger_id FROM ledger WHERE ledger_id = $parId", array());
			if(!empty($tableObj)){
				foreach ($tableObj[0] as $key=>$value) {
					$parId1 = stripslashes($value);
				}
			}
			
			if($parId1>0){
				$arrow++;
				$parId2 = 0;
				$tableObj = $this->db->querypagination("SELECT parent_ledger_id FROM ledger WHERE ledger_id = $parId1", array());
				if(!empty($tableObj)){
					foreach ($tableObj[0] as $key=>$value) {
						$parId2 = stripslashes($value);
					}
				}
				
				if($parId2>0){
					$arrow++;
					$parId3 = 0;
					$tableObj = $this->db->querypagination("SELECT parent_ledger_id FROM ledger WHERE ledger_id = $parId2", array());
					if(!empty($tableObj)){
						foreach ($tableObj[0] as $key=>$value) {
							$parId3 = stripslashes($value);
						}
					}
					if($parId3>0){
						$arrow++;					
					}
				}
			}
		}
		
		$returnStr = '';
		if($arrow>0){
			for($l=0; $l<$arrow; $l++){
				$returnStr .= '&emsp;';
			}
		}
		
		if(empty($returnStr))
			$returnStr = '<img src="/assets/images/Accounts/firstarrow.png" alt="Parent">&nbsp ';
		else
			$returnStr .= '<img src="/assets/images/Accounts/sontabarrow.png" alt="Parent">&nbsp ';
		return $returnStr;
	}
	
	private function ledOneRowStr($oneRow, $accountType, $subGroupName, $debitCredits, $voucherTypes, $a, $col=1){
		$tableRows = "";
		if(!empty($oneRow)){
			$ledger_id = $oneRow->ledger_id;
			$name = stripslashes(trim($oneRow->name));
			
			$arrow = $this->getArrows($ledger_id, $oneRow->parent_ledger_id);
											
			$visible_on = 'All Voucher';
			if(!empty($oneRow->visible_on)){
				$visibleOns = array_flip(explode(',', $oneRow->visible_on));
				foreach($visibleOns as $key=>$value){
					if($key>0 && array_key_exists($key, $voucherTypes)){
						$visibleOns[$key] = $voucherTypes[$key].' Voucher';
					}
					else{
						unset($visibleOns[$key]);
					}
				}
				if(!empty($visibleOns)){
					$visible_on = implode(', ', $visibleOns);
				}
			}
			$debit = $debitCredits[$oneRow->debit];
			$credit = $debitCredits[$oneRow->credit];
			$cls = '';
			if($oneRow->ledger_publish==2){$cls = ' class="lightyellowrow"';}
			$editIcon = "<a href=\"/Accounts/ledgerView/$ledger_id\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle txt18\"></i></a>";
			$editIcon .= " <a href=\"javascript:void(0);\" onClick=\"AJgetLedgerPopup(0, $oneRow->ledger_id);\" title=\"Change this Account Information\"><i class=\"fa fa-edit txt18\"></i></a>";
			$editIcon .= " <a href=\"javascript:void(0);\" onClick=\"AJarchive_Popup('ledger', $ledger_id, '$oneRow->name', $oneRow->ledger_publish);\" title=\"";
			if($oneRow->ledger_publish==2){
				$editIcon .= "Active this Ledger\"><i class=\"fa fa-arrow-circle-left errormsg";
			}
			else{
				$editIcon .= "Archive this Ledger\"><i class=\"fa fa-remove";
			}
			$editIcon .= " txt18\"></i></a>";
			
			if($oneRow->closing_date>0 && $oneRow->closing_date<strtotime(date('Y-m-d'))){$cls = ' class="lightyellowrow"';}
			
			$tableRows = "<tr$cls>
		<td data-title=\"SL#\" align=\"left\">$ledger_id</td>";
			if($col==1){
				$tableRows .= "<td data-title=\"Group Name\" align=\"left\">$accountType</td>
		<td data-title=\"Sub-Group Name\" nowrap align=\"left\">$subGroupName</td>";
			}
			$tableRows .= "<td data-title=\"Ledger Name\" align=\"left\">$arrow$name</td>
		<td data-title=\"Visible On\" align=\"left\">$visible_on</td>
		<td data-title=\"Debit\" align=\"center\">$debit</td>
		<td data-title=\"Credit\" align=\"center\">$credit</td>
		<td data-title=\"Action\" align=\"center\" nowrap>$editIcon</td>
		</tr>";
		}
		
		return $tableRows;
	}
	
	public function oneRowArchive(){
		$returnstr = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$user_id = $_SESSION["user_id"]??0;
		
		if($prod_cat_man==0){
			$returnstr = 'login';
		}
		else{
			$tableName = $_POST['tableName']??'';
			$idValue = $_POST['idValue']??0;
			$description = $_POST['description']??'';
			$activeInActive = $_POST['activeInActive']??1;
			if($activeInActive==1){
				$activeInActive = 2;
				$message = "Archived successfully.";
			}
			else{
				$activeInActive = 1;
				$message = "Actived successfully.";
			}
			$update = $this->db->update($tableName, array($tableName.'_publish'=>$activeInActive), $idValue);
			if($update){
				$returnstr = $message;
			}
			else{
				$returnstr = 'Error occured while archive/active data!';
			}
		}
		return $returnstr;
	}
	
	private function updateAllLedgerCount($ledId){
		if($ledId>0){
			$ledger_count = 0;
			$countObj = $this->db->query("SELECT COUNT(ledger_id) AS totalrows FROM ledger WHERE parent_ledger_id = $ledId ORDER BY ledger_id ASC", array());
			if($countObj){
				$ledger_count = $countObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			$this->db->update('ledger', array('ledger_count'=>$ledger_count), $ledId);
		}
	}
	
	function getVoucherNo($voucher_date, $voucher_type, $voucher_id=0){
		$accounts_id = $_SESSION["accounts_id"]??0;
		if($voucher_id>0){
			$voucher_no = $this->getOneFieldById('voucher', $voucher_id, 'voucher_no');
		}
		else{
			$voucher_no = 1;
			$vouObj = $this->db->querypagination("SELECT voucher_no FROM voucher WHERE accounts_id = $accounts_id AND voucher_type = $voucher_type ORDER BY voucher_no DESC LIMIT 0, 1", array());
			if($vouObj){
				$voucher_no = $vouObj[0]['voucher_no']+1;
			}
		}
		return $voucher_no;
	}
			
	//=======================Reports Module==================//
	
	function trialBalanceData11($funcParameter){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		if($prod_cat_man==0){
			$reponseData['login'] = 'session_ended';
		}
		else{
			$reponseData = array('login'=>'');
					
			$fdate = array_key_exists('fdate', $funcParameter)?addslashes(trim($funcParameter['fdate'])):date('Y-m-d');
			$fviews_type = intval(array_key_exists('fviews_type', $funcParameter)?addslashes(trim($funcParameter['fviews_type'])):2);
			
			$_SESSION["current_module"] = "trialBalance";
			$_SESSION["list_filters"] = $funcParameter;
			$filterSql = "v.voucher_publish = 2 AND ";
			$lFilter = '';
			if(!empty($fdate)){
				$enddate = date('Y-m-d', strtotime($fdate));
				$filterSql .= "v.voucher_date <='$enddate' AND ";
				$lFilter = "substring(created_on,1,10) <='$enddate' AND ";
			}
			
			
			$ledData = $LedIds = $subGroIds = array();
			
			$tableObj = $this->Common_model->getallrowbysqlquery("SELECT account_type, sub_group_id, ledger_id, name, debit, opening_balance FROM ledger WHERE $lFilter ledger_publish = 1 ORDER BY name ASC");
			if($tableObj){
				foreach($tableObj as $oneRow){
					$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
					if($oneRow->opening_balance !=0){
						$account_type = $oneRow->account_type;
						$sub_group_id = $oneRow->sub_group_id;
						$subGroIds[$sub_group_id] = '';
						$ledger_id = $oneRow->ledger_id;
						$debit_credit = $oneRow->debit;
						$debit_credit = 1;
						$amount = $oneRow->opening_balance*$debit_credit;
						$debCrAcc = 'D';
						if(in_array($account_type, array(2,3,4))){$debCrAcc = 'C';}
						
						if(array_key_exists($account_type, $ledData)){
							if(array_key_exists($sub_group_id, $ledData[$account_type])){
								if(array_key_exists($debCrAcc, $ledData[$account_type][$sub_group_id])){
									if(array_key_exists($ledger_id, $ledData[$account_type][$sub_group_id][$debCrAcc])){
										$amount += $ledData[$account_type][$sub_group_id][$debCrAcc][$ledger_id];
									}
								}
							}
						}
						$ledData[$account_type][$sub_group_id][$debCrAcc][$ledger_id] = $amount;
					}
				}
			}
			
			$sql = "SELECT l.account_type, l.sub_group_id, l.ledger_id, vl.debit_credit, vl.amount FROM voucher v, voucher_list vl, ledger l WHERE $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id ORDER BY v.voucher_date ASC, vl.voucher_list_id ASC";
			$dataObj = $this->Common_model->getallrowbysqlquery($sql);
			
			if($dataObj){
				
				$segment1name = $this->uri->segment(1, 'Accounts');
				$segment2name = $this->uri->segment(2, 'trialBalance');
				$loadPage = "$segment1name/$segment2name";
				$editPer = $this->Common_model->permission_on_module($loadPage, 'edit');
				$hidePer = $this->Common_model->permission_on_module($loadPage, 'hide');
				$accountTypes = $this->accountTypes();
				$debitCredits = $this->debitCredits();
				
				foreach($dataObj as $oneRow){
					$account_type = $oneRow->account_type;
					$sub_group_id = $oneRow->sub_group_id;
					$subGroIds[$sub_group_id] = '';
					$ledger_id = $oneRow->ledger_id;
					$debit_credit = $oneRow->debit_credit;
					$amount = $oneRow->amount*$debit_credit;
					$debCrAcc = 'D';
					if(in_array($account_type, array(2,3,4))){$debCrAcc = 'C';}
					
					if(array_key_exists($account_type, $ledData)){
						if(array_key_exists($sub_group_id, $ledData[$account_type])){
							if(array_key_exists($debCrAcc, $ledData[$account_type][$sub_group_id])){
								if(array_key_exists($ledger_id, $ledData[$account_type][$sub_group_id][$debCrAcc])){
									$amount += $ledData[$account_type][$sub_group_id][$debCrAcc][$ledger_id];
								}
							}
						}
					}
					$ledData[$account_type][$sub_group_id][$debCrAcc][$ledger_id] = $amount;
				}
			
			}
			
			$tableRows = '';
			if(!empty($ledData)){
				if(!empty($subGroIds)){
					$tableObj = $this->Common_model->getallrowbysqlquery("SELECT sub_group_id, name FROM sub_group WHERE sub_group_id IN (".implode(', ', array_keys($subGroIds)).") AND sub_group_publish = 1 ORDER BY name ASC");
					if($tableObj){
						foreach($tableObj as $oneRow){
							$subGroIds[$oneRow->sub_group_id] = stripslashes(trim($oneRow->name));
						}
					}
				}
				
				$totDeb = $totCre = 0;
				ksort($ledData);
				foreach($ledData as $account_type=>$ATInfo){
					$accTypName = $accountTypes[$account_type];
					$ATtotDeb = $ATtotCre = 0;
					$ATStr = '';
					ksort($ATInfo);					
					foreach($ATInfo as $sub_group_id=>$SGInfo){
						$SGName = $subGroIds[$sub_group_id];
						$SGtotDeb = $SGtotCre = 0;
						$SGStr = '';
						foreach($SGInfo as $debCrAcc=>$DCInfo){
							$LADetails = array();
							foreach($DCInfo as $LId=>$triBal){
								
								$debAmount = $creAmount = 0;
								if($debCrAcc=='D'){$debAmount += $triBal;}
								else{$creAmount += $triBal;}
								$SGtotDeb += $debAmount;
								$SGtotCre += $creAmount;
								
								if($fviews_type >2){
									$ledName = '';
									$allLedIds = $this->getLedIds($LId);
									foreach($allLedIds as $oneLID=>$moreIds){
										$ledName = $LedIds[$oneLID];										
										if(!empty($moreIds)){
											foreach($moreIds as $oneLID2=>$moreIds2){
												$ledName2 = $LedIds[$oneLID2];
												if(!empty($moreIds2)){
													foreach($moreIds2 as $oneLID3=>$moreIds3){
														$ledName3 = $LedIds[$oneLID3];
														if(!empty($moreIds3)){
															foreach($moreIds3 as $oneLID4=>$moreIds4){
																$ledName4 = $LedIds[$oneLID4];
																$LADetails[$ledName][$ledName2][$ledName3][$ledName4] = $triBal;
															}
														}
														else{
															$LADetails[$ledName][$ledName2][$ledName3] = $triBal;
														}
													}
												}
												else{
													$LADetails[$ledName][$ledName2] = $triBal;
												}
											}
										}
										else{
											$LADetails[$ledName] = $triBal;
										}
									}
								}
							}
							if(!empty($LADetails)){
								ksort($LADetails);
								foreach($LADetails as $LName1=>$triBalInfo1){
									$debAmount1 = $creAmount1 = 0;
									$SGStr1 = '';
									if(!is_array($triBalInfo1)){
										if($debCrAcc=='D'){$debAmount1 += floatval($triBalInfo1);}
										else{$creAmount1 += floatval($triBalInfo1);}									
									}
									else{
										foreach($triBalInfo1 as $LName2=>$triBalInfo2){
											$debAmount2 = $creAmount2 = 0;
											$SGStr2 = '';
											if(!is_array($triBalInfo2)){
												if($debCrAcc=='D'){$debAmount2 += floatval($triBalInfo2);}
												else{$creAmount2 += floatval($triBalInfo2);}
											}
											else{
												foreach($triBalInfo2 as $LName3=>$triBalInfo3){
													$debAmount3 = $creAmount3 = 0;
													$SGStr3 = '';
													if(!is_array($triBalInfo3)){
														if($debCrAcc=='D'){$debAmount3 += floatval($triBalInfo3);}
														else{$creAmount3 += floatval($triBalInfo3);}
													}
													else{
														foreach($triBalInfo3 as $LName4=>$triBalInfo4){
															$debAmount4 = $creAmount4 = 0;
															if(!is_array($triBalInfo4)){																
																if($debCrAcc=='D'){$debAmount4 += floatval($triBalInfo4);}
																else{$creAmount4 += floatval($triBalInfo4);}
																$debAmount3 += $debAmount4;
																$creAmount3 += $creAmount4;
															}
															
															$debAmountStr = '&nbsp;';
															if($debAmount4 !=0){
																$debAmountStr = $this->taka_format($debAmount4);
															}
															$creAmountStr = '&nbsp;';
															if($creAmount4 !=0){
																$creAmountStr = $this->taka_format($creAmount4);
															}
															$SGStr3 .= "<tr>
						<td data-title=\"Particular Name\" align=\"left\">&emsp;&emsp;&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Ledger\" class=\"mtop-6\">&nbsp;$LName4</td>
						<td nowrap data-title=\"Debit\" align=\"right\">$debAmountStr</td>
						<td nowrap data-title=\"Credit\" align=\"right\">$creAmountStr</td>
						</tr>";
														}
													}
													
													$debAmount2 += $debAmount3;
													$creAmount2 += $creAmount3;
													$debAmountStr = '&nbsp;';
													if($debAmount3 !=0){
														$debAmountStr = $this->taka_format($debAmount3);
													}
													$creAmountStr = '&nbsp;';
													if($creAmount3 !=0){
														$creAmountStr = $this->taka_format($creAmount3);
													}
													$SGStr2 .= "<tr>
				<td data-title=\"Particular Name\" align=\"left\">&emsp;&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Ledger\" class=\"mtop-6\">&nbsp;$LName3</td>
				<td nowrap data-title=\"Debit\" align=\"right\">$debAmountStr</td>
				<td nowrap data-title=\"Credit\" align=\"right\">$creAmountStr</td>
				</tr>
				$SGStr3";
												}												
											}
											
											$debAmount1 += $debAmount2;
											$creAmount1 += $creAmount2;
											$debAmountStr = '&nbsp;';
											if($debAmount2 !=0){
												$debAmountStr = $this->taka_format($debAmount2);
											}
											$creAmountStr = '&nbsp;';
											if($creAmount2 !=0){
												$creAmountStr = $this->taka_format($creAmount2);
											}
											$SGStr1 .= "<tr>
		<td data-title=\"Particular Name\" align=\"left\">&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Ledger\" class=\"mtop-6\">&nbsp;$LName2</td>
		<td nowrap data-title=\"Debit\" align=\"right\">$debAmountStr</td>
		<td nowrap data-title=\"Credit\" align=\"right\">$creAmountStr</td>
		</tr>
		$SGStr2";
										}		
									}
									
									$debAmountStr = '&nbsp;';
									if($debAmount1 !=0){
										$debAmountStr = $this->taka_format($debAmount1);
									}
									$creAmountStr = '&nbsp;';
									if($creAmount1 !=0){
										$creAmountStr = $this->taka_format($creAmount1);
									}
									$SGStr .= "<tr>
		<td data-title=\"Particular Name\" align=\"left\">&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Ledger\" class=\"mtop-6\">&nbsp;$LName1</td>
		<td nowrap data-title=\"Debit\" align=\"right\">$debAmountStr</td>
		<td nowrap data-title=\"Credit\" align=\"right\">$creAmountStr</td>
		</tr>
		$SGStr1";
								}
							}
						}
						$ATtotDeb += $SGtotDeb;
						$ATtotCre += $SGtotCre;
						
						if($fviews_type >1){
							$debAmountStr = '&nbsp;';
							if($SGtotDeb !=0){
								$debAmountStr = "&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;".$this->taka_format($SGtotDeb);
							}
							$creAmountStr = '&nbsp;';
							if($SGtotCre !=0){
								$creAmountStr = "&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;".$this->taka_format($SGtotCre);
							}
							$ATStr .= "<tr class=\"lightbluerow\">
		<td data-title=\"Particular Name\" align=\"left\">&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;<strong>$SGName</strong></td>
		<td nowrap data-title=\"Debit\" align=\"left\"><strong>$debAmountStr</strong></td>
		<td nowrap data-title=\"Credit\" align=\"left\"><strong>$creAmountStr</strong></td>
		</tr>$SGStr";
						}
					}
					
					$totDeb += $ATtotDeb;
					$totCre += $ATtotCre;
					
					if($ATtotDeb !=0 || $ATtotCre !=0){
						$debAmountStr = '&nbsp;';
						if($ATtotDeb !=0){
							$debAmountStr = $this->taka_format($ATtotDeb);
						}
						$creAmountStr = '&nbsp;';
						if($ATtotCre !=0){
							$creAmountStr = $this->taka_format($ATtotCre);
						}
						$tableRows .= "<tr class=\"lightgreenrow\">
		<td data-title=\"Particular Name\" align=\"left\"><strong><img src=\"/assets/images/firstarrow.png\" alt=\"Group\" class=\"mtop-6\">&nbsp$accTypName</strong></td>
		<td nowrap data-title=\"Debit\" align=\"left\"><strong>$debAmountStr</strong></td>
		<td nowrap data-title=\"Credit\" align=\"left\"><strong>$creAmountStr</strong></td>
		</tr>$ATStr";
					}
				}
				
				$debAmountStr = '&nbsp;';
				if($totDeb !=0){
					$debAmountStr = $this->taka_format($totDeb);
				}
				$creAmountStr = '&nbsp;';
				if($totCre !=0){
					$creAmountStr = $this->taka_format($totCre);
				}
				
				$tableRows .= "<tr class=\"lightpinkrow\">
		<td data-title=\"Particular Name\" align=\"right\"><strong>Grand Total:</strong></td>
		<td nowrap data-title=\"Debit\" align=\"right\"><strong>$debAmountStr</strong></td>
		<td nowrap data-title=\"Credit\" align=\"right\"><strong>$creAmountStr</strong></td>
		</tr>";
				
			}
			else{
				$tableRows .= "<tr>
		<td colspan=\"3\" data-title=\"No data found\">There is no data found</td>
		</tr>";
			}
			
			$reponseData['tableRows'] = $tableRows;
		}
		return $reponseData;
	}
	
	//===========Reports:: receiptPayment==================//
	public function receiptPayment(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$this->pageTitle = $GLOBALS['title'];
		$list_filters = $_SESSION['list_filters']??array();
		
		$date_range = $list_filters['date_range']??date('Y-m-d').' - '.date('Y-m-d');
		$this->date_range = $date_range;		
		$this->views_type = $fviews_type = $list_filters['fviews_type']??2;
		
		$returnData = $this->receiptPaymentData();
        $receiptsRows = $paymentsRows = '';
        if(!empty($returnData) && $returnData['login']==''){
            $receiptsRows = array_key_exists('receiptsRows', $returnData)?$returnData['receiptsRows']:'';
            $paymentsRows = array_key_exists('paymentsRows', $returnData)?$returnData['paymentsRows']:'';
        }
        
		$vieTypOpt = "";
		$viewsTyps = array('1'=>'Group Only', '2'=>'Group + Sub GROUP', '3'=>'Details');
		foreach($viewsTyps as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($fviews_type==$oneOptVal){$selected = ' selected';}
			$vieTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$innerHTMLStr = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"/assets/css/daterangepicker.css\" />
		<script type=\"text/javascript\" src=\"/assets/js/moment.min.js\"></script>
		<script type=\"text/javascript\" src=\"/assets/js/daterangepicker.js\"></script>
		<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<div class=\"row\">
			<div class=\"col-sm-4 col-md-7 pright0\">
				<button class=\"btn cursor p2x10 marginright15\" onClick=\"printreceiptPayment();\" title=\"Print $this->pageTitle\">
					<i class=\"fa fa-print\"></i> Print
				</button>
			</div>				
			<div class=\"col-sm-4 col-md-3 pbottom10 pright0\">
				<div class=\"input-group\">
					<span class=\"input-group-addon cursor\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Views Type\">
						Views Type
					</span>
					<select name=\"fviews_type\" id=\"fviews_type\" class=\"form-control\" onChange=\"loadRecPayData();\">
						$vieTypOpt
					</select>
				</div>
			</div>
			<div class=\"col-sm-4 col-md-2 pbottom10\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Date Range\" value=\"$date_range\" id=\"date_range\" name=\"date_range\" class=\"form-control\" maxlength=\"23\" />
					<span class=\"input-group-addon cursor\" onClick=\"loadRecPayData();\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Search Trial Balance\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"row\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
                <div id=\"no-more-tables\">
                    <table class=\"col-md-12\">
                        <tr>
                            <td width=\"49%\" valign=\"top\">
                                <table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
                                    <thead class=\"cf\">
                                        <tr>
                                            <th align=\"center\">RECEIPTS</th>
                                            <th width=\"15%\" align=\"center\">Ledger Amount</th>
                                            <th width=\"15%\" align=\"center\">Group Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id=\"receiptsRows\">$receiptsRows</tbody>
                                </table>
                            </td>
                            <td width=\"5\" valign=\"top\">&nbsp;</td>
                            <td valign=\"top\">
                                <table class=\"col-md-12 table-bordered table-striped table-condensed cf listing\">
                                    <thead class=\"cf\">
                                        <tr>
                                            <th align=\"center\">PAYMENTS</th>
                                            <th width=\"15%\" align=\"center\">Ledger Amount</th>
                                            <th width=\"15%\" align=\"center\">Group Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id=\"paymentsRows\">$paymentsRows</tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
			</div>    
		</div>";
		
		$htmlStr = $this->htmlBody($innerHTMLStr);
		return $htmlStr;
	}
	
	public function AJgetPage_receiptPayment(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			
			$date_range = $_POST['date_range']??'';
			$fviews_type = $_POST['fviews_type']??2;
					
			$this->date_range = $date_range;
			$this->views_type = $fviews_type;
			
			$returnData = $this->receiptPaymentData();
            $receiptsRows = $paymentsRows = '';
            if(!empty($returnData) && $returnData['login']==''){
                $receiptsRows = array_key_exists('receiptsRows', $returnData)?$returnData['receiptsRows']:'';
                $paymentsRows = array_key_exists('paymentsRows', $returnData)?$returnData['paymentsRows']:'';
            }
            
			$jsonResponse['receiptsRows'] = $receiptsRows;
            $jsonResponse['paymentsRows'] = $paymentsRows;
			
			return json_encode($jsonResponse);
		}
	}
	
	private function receiptPaymentData(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$cashParentIds = array(1);
		$cashLedIdNotCond = " AND ledger_id != 6";
		$bankParentIds = array(26);
		$date_range = $this->date_range;
		$fviews_type = $this->views_type;

		$reponseData = array('login'=>'');

		$_SESSION["current_module"] = "receiptPayment";
		$_SESSION["list_filters"] = array('date_range'=>$date_range, 'fviews_type'=>$fviews_type);
		
		$filterSql = "v.voucher_publish = 2 AND ";
		$bindData = array();
		$receiptsRows = $paymentsRows = $startdate = $enddate = '';
		$filterSql = "";
		if(!empty($date_range)){
			$date_ranges = explode(' - ', $date_range);
            if(!empty($date_ranges) && count($date_ranges)>1){
                $startdate = date('Y-m-d', strtotime($date_ranges[0]));
                $enddate = date('Y-m-d', strtotime($date_ranges[1]));
                $filterSql .= "v.voucher_date BETWEEN '$startdate' AND '$enddate' AND ";
            }
		}
		//================Cash / Bank Ledger Ids===================//
		$ledIdInfos = $cashLedgerIds = $cashParSonLedIds = $bankLedgerIds = $bankParSonLedIds = array();
		//------------Cash Ledger Ids------------//
		foreach($cashParentIds as $oneLedId){
			$cashLedgerIds[$oneLedId] = 0;
		}
		$parLedOpts = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_ledger_id IN (".implode(', ', $cashParentIds).") $cashLedIdNotCond AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
		if($parLedOpts){
			while($oneRow = $parLedOpts->fetch(PDO::FETCH_OBJ)){
				$cashLedgerIds[$oneRow->ledger_id] = 0;

				$parLedOpts2 = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_sub_ledger_id = $oneRow->ledger_id AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
				if($parLedOpts2){
					while($oneRow2 = $parLedOpts2->fetch(PDO::FETCH_OBJ)){
						$cashLedgerIds[$oneRow2->ledger_id] = 0;

						$parLedOpts3 = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_sub2_ledger_id = $oneRow2->ledger_id AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
						if($parLedOpts3){
							while($oneRow3 = $parLedOpts3->fetch(PDO::FETCH_OBJ)){
								$cashLedgerIds[$oneRow3->ledger_id] = 0;								
							}
						}
					}
				}
			}			
		}

		$sonSql = "SELECT ledger_id, parent_ledger_id, parent_sub_ledger_id, parent_sub2_ledger_id FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($cashLedgerIds)).") AND ledger_publish=1 ORDER BY parent_ledger_id ASC, parent_sub_ledger_id ASC, name ASC";
		$sonDataObj = $this->db->query($sonSql, array());		
		if($sonDataObj){
			while($sonOneRow = $sonDataObj->fetch(PDO::FETCH_OBJ)){
				$sparent_ledger_id = $sonOneRow->parent_ledger_id;
				$sparent_sub_ledger_id = $sonOneRow->parent_sub_ledger_id;
				$sparent_sub2_ledger_id = $sonOneRow->parent_sub2_ledger_id;
				$sledger_id = $sonOneRow->ledger_id;				
				if($sparent_ledger_id==0){
					$sparent_ledger_id = $sledger_id;
					$sparent_sub_ledger_id = $sparent_sub2_ledger_id = $sledger_id = 0;
				}
				else if($sparent_sub_ledger_id==0){
					$sparent_sub_ledger_id = $sledger_id;
					$sparent_sub2_ledger_id = $sledger_id = 0;
				}
				else if($sparent_sub2_ledger_id==0){
					$sparent_sub2_ledger_id = $sledger_id;
					$sledger_id = 0;
				}
				$ledIdInfos[$sparent_ledger_id] = '';
				$ledIdInfos[$sparent_sub_ledger_id] = '';
				$ledIdInfos[$sparent_sub2_ledger_id] = '';
				$ledIdInfos[$sledger_id] = '';

				$cashParSonLedIds[$sparent_ledger_id][$sparent_sub_ledger_id][$sparent_sub2_ledger_id][$sledger_id] = '';
			}
		}
		
		//-------------Bank Ledger Ids----------//
		foreach($bankParentIds as $oneLedId){
			$bankLedgerIds[$oneLedId] = 0;
		}
		$parLedOpts = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_ledger_id IN (".implode(', ', $bankParentIds).") AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
		if($parLedOpts){
			while($oneRow = $parLedOpts->fetch(PDO::FETCH_OBJ)){
				$bankLedgerIds[$oneRow->ledger_id] = 0;

				$parLedOpts2 = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_sub_ledger_id = $oneRow->ledger_id AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
				if($parLedOpts2){
					while($oneRow2 = $parLedOpts2->fetch(PDO::FETCH_OBJ)){
						$bankLedgerIds[$oneRow2->ledger_id] = 0;

						$parLedOpts3 = $this->db->query("SELECT ledger_id FROM ledger WHERE parent_sub2_ledger_id = $oneRow2->ledger_id AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
						if($parLedOpts3){
							while($oneRow3 = $parLedOpts3->fetch(PDO::FETCH_OBJ)){
								$bankLedgerIds[$oneRow3->ledger_id] = 0;								
							}
						}
					}
				}						
			}			
		}

		$sonSql = "SELECT ledger_id, parent_ledger_id, parent_sub_ledger_id, parent_sub2_ledger_id FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($bankLedgerIds)).") AND ledger_publish=1 ORDER BY parent_ledger_id ASC, parent_sub_ledger_id ASC, name ASC";
		$sonDataObj = $this->db->query($sonSql, array());		
		if($sonDataObj){
			while($sonOneRow = $sonDataObj->fetch(PDO::FETCH_OBJ)){
				$sparent_ledger_id = $sonOneRow->parent_ledger_id;
				$sparent_sub_ledger_id = $sonOneRow->parent_sub_ledger_id;
				$sparent_sub2_ledger_id = $sonOneRow->parent_sub2_ledger_id;
				$sledger_id = $sonOneRow->ledger_id;				
				if($sparent_ledger_id==0){
					$sparent_ledger_id = $sledger_id;
					$sparent_sub_ledger_id = $sparent_sub2_ledger_id = $sledger_id = 0;
				}
				else if($sparent_sub_ledger_id==0){
					$sparent_sub_ledger_id = $sledger_id;
					$sparent_sub2_ledger_id = $sledger_id = 0;
				}
				else if($sparent_sub2_ledger_id==0){
					$sparent_sub2_ledger_id = $sledger_id;
					$sledger_id = 0;
				}
				$ledIdInfos[$sparent_ledger_id] = '';
				$ledIdInfos[$sparent_sub_ledger_id] = '';
				$ledIdInfos[$sparent_sub2_ledger_id] = '';
				$ledIdInfos[$sledger_id] = '';

				$bankParSonLedIds[$sparent_ledger_id][$sparent_sub_ledger_id][$sparent_sub2_ledger_id][$sledger_id] = '';
			}
		}

		//================Cash / Bank Transaction===================//
		//-------------Cash Transaction-------------//		
		$openingCashTransaction = $dateRangeCashTransaction = array();
		if(!empty($cashLedgerIds)){
			$sql = "SELECT vl.ledger_id, v.voucher_date, vl.amount, vl.debit_credit FROM voucher v, voucher_list vl WHERE vl.ledger_id IN (".implode(', ', array_keys($cashLedgerIds)).")";
			if(!empty($enddate)){
				$sql .= " AND v.voucher_date <= '$enddate'";
			}
			$sql .= " AND vl.amount !=0 AND v.voucher_publish=2 AND v.voucher_id = vl.voucher_id";
			$tableObj = $this->db->query($sql, array());
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$voucher_date = $oneRow->voucher_date;
					$totalAmount = $oneRow->amount*$oneRow->debit_credit;

					if(!empty($startdate) && $voucher_date>=$startdate){
						if(array_key_exists($oneRow->ledger_id, $dateRangeCashTransaction)){
							$totalAmount = $dateRangeCashTransaction[$oneRow->ledger_id] + $totalAmount;
						}
						$dateRangeCashTransaction[$oneRow->ledger_id] = $totalAmount;
					}
					else{
						if(array_key_exists($oneRow->ledger_id, $openingCashTransaction)){
							$totalAmount = $openingCashTransaction[$oneRow->ledger_id] + $totalAmount;
						}
						$openingCashTransaction[$oneRow->ledger_id] = $totalAmount;
					}
				}
			}
		}

		//===============Bank Transaction================//		
		$openingBankTransaction = $dateRangeBankTransaction = array();
		if(!empty($bankLedgerIds)){
			$sql = "SELECT vl.ledger_id, v.voucher_date, vl.amount, vl.debit_credit FROM voucher v, voucher_list vl WHERE vl.ledger_id IN (".implode(', ', array_keys($bankLedgerIds)).")";
			if(!empty($enddate)){
				$sql .= " AND v.voucher_date <= '$enddate'";
			}
			$sql .= " AND vl.amount !=0 AND v.voucher_publish=2 AND v.voucher_id = vl.voucher_id";
			$tableObj = $this->db->query($sql, array());
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$voucher_date = $oneRow->voucher_date;
					$totalAmount = $oneRow->amount*$oneRow->debit_credit;

					if(!empty($startdate) && $voucher_date>=$startdate){
						if(array_key_exists($oneRow->ledger_id, $dateRangeBankTransaction)){
							$totalAmount = $dateRangeBankTransaction[$oneRow->ledger_id] + $totalAmount;
						}
						$dateRangeBankTransaction[$oneRow->ledger_id] = $totalAmount;
					}
					else{
						if(array_key_exists($oneRow->ledger_id, $openingBankTransaction)){
							$totalAmount = $openingBankTransaction[$oneRow->ledger_id] + $totalAmount;
						}
						$openingBankTransaction[$oneRow->ledger_id] = $totalAmount;
					}
				}
			}			
		}
		
		//=================Cash / Bank Ledgers information=================//
       	if(!empty($ledIdInfos)){
			$sql = "SELECT ledger_id, name, opening_balance, ledger_count FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($ledIdInfos)).") AND ledger_publish=1 ORDER BY name ASC";
            $tableObj = $this->db->query($sql, array());
            if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
                    $ledIdInfos[$oneRow->ledger_id] = array($oneRow->name, $oneRow->opening_balance, $oneRow->ledger_count);
                }
            }
        }
		
        //========================Opening Balance View ==================//
		//-----------------Cash View----------------//
		$cashOpeningBalance = 0;
		foreach($cashParentIds as $parLedId){
			$parLedInfo =  $ledIdInfos[$parLedId]??array();
			if(!empty($parLedInfo)){
				$parLedName = "$parLedInfo[0] &emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parLedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
				$parLedOpenBalance = $parLedInfo[1];
				if(array_key_exists($parLedId, $openingCashTransaction)){
					$parLedOpenBalance += $openingCashTransaction[$parLedId];
				}
				//---------------Son1 Ledger Transaction-------------//
				$son1LedInfo =  $cashParSonLedIds[$parLedId]??array();
				$son1LedIdInfo = array();
				if(!empty($son1LedInfo)){
					foreach($son1LedInfo as $sonledger_id=>$sonMoreRows){
						$son1LedRow = $ledIdInfos[$sonledger_id]??array();
						if(!empty($son1LedRow)){
							$son1LedIdInfo[$sonledger_id] = stripslashes(trim($son1LedRow[0]));
						}
					}			
				}
				if(!empty($son1LedIdInfo)){
					asort($son1LedIdInfo);
					foreach($son1LedIdInfo as $son1LedId=>$son1LedName){
						$son1LedRow =  $ledIdInfos[$son1LedId]??array();
						$son1LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son1LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
						$son1LedOpenBalance = $son1LedRow[1];
						if(array_key_exists($son1LedId, $openingCashTransaction)){
							$son1LedOpenBalance += $openingCashTransaction[$son1LedId];
						}
						//---------------Son2 Ledger Transaction-------------//
						$son2LedInfo =  $son1LedInfo[$son1LedId]??array();
						$son2LedIdInfo = array();
						if(!empty($son2LedInfo)){
							foreach($son2LedInfo as $sonledger_id=>$sonMoreRows){
								$son2LedRow = $ledIdInfos[$sonledger_id]??array();
								if(!empty($son2LedRow)){
									$son2LedIdInfo[$sonledger_id] = stripslashes(trim($son2LedRow[0]));
								}
							}
						}
						if(!empty($son2LedIdInfo)){
							asort($son2LedIdInfo);
							foreach($son2LedIdInfo as $son2LedId=>$son2LedName){
								$son2LedRow =  $ledIdInfos[$son2LedId]??array();
								$son2LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son2LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
								$son2LedOpenBalance = $son2LedRow[1];
								if(array_key_exists($son2LedId, $openingCashTransaction)){
									$son2LedOpenBalance += $openingCashTransaction[$son2LedId];
								}
								//---------------Son3 Ledger Transaction-------------//
								$son3LedInfo =  $son2LedInfo[$son2LedId]??array();
								$son3LedIdInfo = array();
								if(!empty($son3LedInfo)){
									foreach($son3LedInfo as $sonledger_id=>$sonMoreRows){
										$son3LedRow = $ledIdInfos[$sonledger_id]??array();
										if(!empty($son3LedRow)){
											$son3LedIdInfo[$sonledger_id] = stripslashes(trim($son3LedRow[0]));
										}
									}
								}
								if(!empty($son3LedIdInfo)){
									asort($son3LedIdInfo);
									foreach($son3LedIdInfo as $son3LedId=>$son3LedName){
										$son3LedRow =  $ledIdInfos[$son3LedId]??array();
										$son3LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son3LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
										$son3LedOpenBalance = $son3LedRow[1];
										if(array_key_exists($son3LedId, $openingCashTransaction)){
											$son3LedOpenBalance += $openingCashTransaction[$son3LedId];
										}
										
										$son2LedOpenBalance += $son3LedOpenBalance;

										if($fviews_type>2 && $son3LedOpenBalance !=0){
											$receiptsRows .= "<tr>";
											$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; &emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son3LedName</td>";
											$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son3LedOpenBalance,2)."</td>";
											$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
											$receiptsRows .= "</tr>";
										}
									}
								}

								$son1LedOpenBalance += $son2LedOpenBalance;

								if($fviews_type>2 && $son2LedOpenBalance !=0){
									$receiptsRows .= "<tr>";
									$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son2LedName</td>";
									$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son2LedOpenBalance,2)."</td>";
									$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
									$receiptsRows .= "</tr>";
								}
							}
						}

						$parLedOpenBalance += $son1LedOpenBalance;
						$clsbold = '';
						if($fviews_type>2){$clsbold = ' class="txtbold"';}
						if($fviews_type>1 && $son1LedOpenBalance !=0){
							$receiptsRows .= "<tr$clsbold>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son1LedName</td>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son1LedOpenBalance,2)."</td>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
							$receiptsRows .= "</tr>";
						}
					}
				}

				$cashOpeningBalance += $parLedOpenBalance;
				$clsbold = '';
				if($fviews_type>1){$clsbold = ' class="txtbold"';}
				$receiptsRows .= "<tr$clsbold>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><img src=\"/assets/images/Accounts/firstarrow.png\" alt=\"Parent Ledger\">&nbsp $parLedName</td>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($parLedOpenBalance,2)."</td>";
				$receiptsRows .= "</tr>";
			}
		}

		//-----------------Bank View----------------//
		$bankOpeningBalance = 0;
		foreach($bankParentIds as $parLedId){
			$parLedInfo =  $ledIdInfos[$parLedId]??array();
			if(!empty($parLedInfo)){
				$parLedName = "$parLedInfo[0] &emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parLedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
				$parLedOpenBalance = $parLedInfo[1];
				if(array_key_exists($parLedId, $openingBankTransaction)){
					$parLedOpenBalance += $openingBankTransaction[$parLedId];
				}
				//---------------Son1 Ledger Transaction-------------//
				$son1LedInfo =  $bankParSonLedIds[$parLedId]??array();
				$son1LedIdInfo = array();
				if(!empty($son1LedInfo)){
					foreach($son1LedInfo as $sonledger_id=>$sonMoreRows){
						$son1LedRow = $ledIdInfos[$sonledger_id]??array();
						if(!empty($son1LedRow)){
							$son1LedIdInfo[$sonledger_id] = stripslashes(trim($son1LedRow[0]));
						}
					}			
				}
				if(!empty($son1LedIdInfo)){
					asort($son1LedIdInfo);
					foreach($son1LedIdInfo as $son1LedId=>$son1LedName){
						$son1LedRow =  $ledIdInfos[$son1LedId]??array();
						$son1LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son1LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
						$son1LedOpenBalance = $son1LedRow[1];
						if(array_key_exists($son1LedId, $openingBankTransaction)){
							$son1LedOpenBalance += $openingBankTransaction[$son1LedId];
						}
						//---------------Son2 Ledger Transaction-------------//
						$son2LedInfo =  $son1LedInfo[$son1LedId]??array();
						$son2LedIdInfo = array();
						if(!empty($son2LedInfo)){
							foreach($son2LedInfo as $sonledger_id=>$sonMoreRows){
								$son2LedRow = $ledIdInfos[$sonledger_id]??array();
								if(!empty($son2LedRow)){
									$son2LedIdInfo[$sonledger_id] = stripslashes(trim($son2LedRow[0]));
								}
							}
						}
						if(!empty($son2LedIdInfo)){
							asort($son2LedIdInfo);
							foreach($son2LedIdInfo as $son2LedId=>$son2LedName){
								$son2LedRow =  $ledIdInfos[$son2LedId]??array();
								$son2LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son2LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
								$son2LedOpenBalance = $son2LedRow[1];
								if(array_key_exists($son2LedId, $openingBankTransaction)){
									$son2LedOpenBalance += $openingBankTransaction[$son2LedId];
								}
								//---------------Son3 Ledger Transaction-------------//
								$son3LedInfo =  $son2LedInfo[$son2LedId]??array();
								$son3LedIdInfo = array();
								if(!empty($son3LedInfo)){
									foreach($son3LedInfo as $sonledger_id=>$sonMoreRows){
										$son3LedRow = $ledIdInfos[$sonledger_id]??array();
										if(!empty($son3LedRow)){
											$son3LedIdInfo[$sonledger_id] = stripslashes(trim($son3LedRow[0]));
										}
									}
								}
								if(!empty($son3LedIdInfo)){
									asort($son3LedIdInfo);
									foreach($son3LedIdInfo as $son3LedId=>$son3LedName){
										$son3LedRow =  $ledIdInfos[$son3LedId]??array();
										$son3LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son3LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
										$son3LedOpenBalance = $son3LedRow[1];
										if(array_key_exists($son3LedId, $openingBankTransaction)){
											$son3LedOpenBalance += $openingBankTransaction[$son3LedId];
										}
										
										$son2LedOpenBalance += $son3LedOpenBalance;

										if($fviews_type>2 && $son3LedOpenBalance !=0){
											$receiptsRows .= "<tr>";
											$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; &emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son3LedName</td>";
											$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son3LedOpenBalance,2)."</td>";
											$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
											$receiptsRows .= "</tr>";
										}
									}
								}

								$son1LedOpenBalance += $son2LedOpenBalance;

								if($fviews_type>2 && $son2LedOpenBalance !=0){
									$receiptsRows .= "<tr>";
									$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son2LedName</td>";
									$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son2LedOpenBalance,2)."</td>";
									$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
									$receiptsRows .= "</tr>";
								}
							}
						}

						$parLedOpenBalance += $son1LedOpenBalance;
						$clsbold = '';
						if($fviews_type>2){$clsbold = ' class="txtbold"';}
						if($fviews_type>1 && $son1LedOpenBalance !=0){
							$receiptsRows .= "<tr$clsbold>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son1LedName</td>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son1LedOpenBalance,2)."</td>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
							$receiptsRows .= "</tr>";
						}
					}
				}

				$bankOpeningBalance += $parLedOpenBalance;
				$clsbold = '';
				if($fviews_type>1){$clsbold = ' class="txtbold"';}
				$receiptsRows .= "<tr$clsbold>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><img src=\"/assets/images/Accounts/firstarrow.png\" alt=\"Parent Ledger\">&nbsp $parLedName</td>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($parLedOpenBalance,2)."</td>";
				$receiptsRows .= "</tr>";
			}
		}

		$openingBalanceTotal = $cashOpeningBalance + $bankOpeningBalance;
		$receiptsRows .= "<tr class=\"lightgreenrow\">";
        $receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><strong>Opening Balance: </strong></td>";
        $receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
        $receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($openingBalanceTotal)."</strong></td>";
        $receiptsRows .= "</tr>";

		//========================Date Range Receipt / Payment Voucher=======================//
		//-----------------Receipt----------------//
		$ledIds = $subGroupIds = $receiptsData = $paymentsData = array();
		$sql = "SELECT l.sub_group_id, l.parent_ledger_id, l.parent_sub_ledger_id, l.parent_sub2_ledger_id, vl.ledger_id, vl.amount, vl.debit_credit FROM voucher v, voucher_list vl, ledger l WHERE v.voucher_type = 1 AND v.voucher_publish=2 AND $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$debit_credit = $oneRow->debit_credit;
				if($debit_credit==-1){
					$ledgerTotal = $oneRow->amount;
					$sub_group_id = $oneRow->sub_group_id;
					$parent_ledger_id = $oneRow->parent_ledger_id;
					$parent_sub_ledger_id = $oneRow->parent_sub_ledger_id;
					$parent_sub2_ledger_id = $oneRow->parent_sub2_ledger_id;
					$ledger_id = $oneRow->ledger_id;
				
					if($parent_ledger_id==0){
						$parent_ledger_id = $ledger_id;
						$parent_sub_ledger_id = $parent_sub2_ledger_id = $ledger_id = 0;
					}
					elseif($parent_sub_ledger_id==0){
						$parent_sub_ledger_id = $ledger_id;
						$parent_sub2_ledger_id = $ledger_id = 0;
					}
					elseif($parent_sub2_ledger_id==0){
						$parent_sub2_ledger_id = $ledger_id;
						$ledger_id = 0;
					}

					$subGroupIds[$sub_group_id] = '';
					$ledIds[$parent_ledger_id] = '';
					$ledIds[$parent_sub_ledger_id] = '';
					$ledIds[$parent_sub2_ledger_id] = '';
					$ledIds[$ledger_id] = '';

					if(array_key_exists($sub_group_id, $receiptsData)){
						if(array_key_exists($parent_ledger_id, $receiptsData[$sub_group_id])){
							if(array_key_exists($parent_sub_ledger_id, $receiptsData[$sub_group_id][$parent_ledger_id])){
								if(array_key_exists($parent_sub2_ledger_id, $receiptsData[$sub_group_id][$parent_ledger_id][$parent_sub_ledger_id])){
									if(array_key_exists($ledger_id, $receiptsData[$sub_group_id][$parent_ledger_id][$parent_sub_ledger_id][$parent_sub2_ledger_id])){
										$ledgerTotal += $receiptsData[$sub_group_id][$parent_ledger_id][$parent_sub_ledger_id][$parent_sub2_ledger_id][$ledger_id];
									}
								}
							}
						}
					}

					$receiptsData[$sub_group_id][$parent_ledger_id][$parent_sub_ledger_id][$parent_sub2_ledger_id][$ledger_id] = $ledgerTotal;
				}
			}
		}

		//-----------------Payment----------------//
		$sql = "SELECT l.sub_group_id, l.parent_ledger_id, l.parent_sub_ledger_id, l.parent_sub2_ledger_id, l.ledger_id, vl.amount, vl.debit_credit FROM voucher v, voucher_list vl, ledger l WHERE v.voucher_type = 2 AND v.voucher_publish=2 AND $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$ledgerTotal = $oneRow->amount;
				$sub_group_id = $oneRow->sub_group_id;
				$parent_ledger_id = $oneRow->parent_ledger_id;
				$parent_sub_ledger_id = $oneRow->parent_sub_ledger_id;
				$parent_sub2_ledger_id = $oneRow->parent_sub2_ledger_id;
				$ledger_id = $oneRow->ledger_id;
				$debit_credit = $oneRow->debit_credit;
				
				if($debit_credit==1){
					if($parent_ledger_id==0){
						$parent_ledger_id = $ledger_id;
						$parent_sub_ledger_id = $parent_sub2_ledger_id = $ledger_id = 0;
					}
					elseif($parent_sub_ledger_id==0){
						$parent_sub_ledger_id = $ledger_id;
						$parent_sub2_ledger_id = $ledger_id = 0;
					}
					elseif($parent_sub2_ledger_id==0){
						$parent_sub2_ledger_id = $ledger_id;
						$ledger_id = 0;
					}

					$subGroupIds[$sub_group_id] = '';
					$ledIds[$parent_ledger_id] = '';
					$ledIds[$parent_sub_ledger_id] = '';
					$ledIds[$parent_sub2_ledger_id] = '';
					$ledIds[$ledger_id] = '';

					if(array_key_exists($sub_group_id, $paymentsData)){
						if(array_key_exists($parent_ledger_id, $paymentsData[$sub_group_id])){
							if(array_key_exists($parent_sub_ledger_id, $paymentsData[$sub_group_id][$parent_ledger_id])){
								if(array_key_exists($parent_sub2_ledger_id, $paymentsData[$sub_group_id][$parent_ledger_id][$parent_sub_ledger_id])){
									if(array_key_exists($ledger_id, $paymentsData[$sub_group_id][$parent_ledger_id][$parent_sub_ledger_id][$parent_sub2_ledger_id])){
										$ledgerTotal += $paymentsData[$sub_group_id][$parent_ledger_id][$parent_sub_ledger_id][$parent_sub2_ledger_id][$ledger_id];
									}
								}
							}
						}
					}

					$paymentsData[$sub_group_id][$parent_ledger_id][$parent_sub_ledger_id][$parent_sub2_ledger_id][$ledger_id] = $ledgerTotal;
				}
			}
		}
		
		//-----------------Ledger Names----------------//
		if(!array_key_exists(0, $ledIds)){$ledIds[0] = '';}
		if(!empty($ledIds)){
			$sql = "SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($ledIds)).") AND ledger_publish=1 ORDER BY name ASC";
            $tableObj = $this->db->query($sql, array());
            if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
                    $ledIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
                }
            }
        }

		//-----------------Sub-Group Names----------------//
		if(!empty($subGroupIds)){
			$sql = "SELECT sub_group_id, name FROM sub_group WHERE sub_group_id IN (".implode(', ', array_keys($subGroupIds)).") AND sub_group_publish = 1 ORDER BY name ASC";
            $tableObj = $this->db->query($sql, array());
            if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
                    $subGroupIds[$oneRow->sub_group_id] = stripslashes(trim($oneRow->name));
                }
            }
		}

		//========================Receipt View=======================//
		$receiptsTotal = 0;
		if(!empty($receiptsData)){
			foreach($receiptsData as $sub_group_id=>$subGroupInfo){
				$subGroupName = $subGroupIds[$sub_group_id]??'';
				$subGroupTotal = 0;
				foreach($subGroupInfo as $parent_ledger_id=>$parentLedgerInfo){
					$parentLedgerName = $ledIds[$parent_ledger_id]??'';
					$parentLedgerTotal = 0;
					foreach($parentLedgerInfo as $parent_sub_ledger_id=>$subParentLedgerInfo){
						$subParentLedgerName = $ledIds[$parent_sub_ledger_id]??'';
						$subParentLedgerTotal = 0;
						$parentExists = 0;
						foreach($subParentLedgerInfo as $parent_sub2_ledger_id=>$sub2ParentLedgerInfo){
							$sub2ParentLedgerName = $ledIds[$parent_sub2_ledger_id]??'';
							$sub2ParentLedgerTotal = 0;
							foreach($sub2ParentLedgerInfo as $ledger_id=>$ledgerTotal){
								$ledgerName = $ledIds[$ledger_id]??'';
								$sub2ParentLedgerTotal += floatval($ledgerTotal);
								if($fviews_type>=3 && $ledgerName !=''){	
									$ledgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$ledger_id/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
									$parentExists++;								
									$receiptsRows .= "<tr>";
									$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp;&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$ledgerName</td>";
									$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($ledgerTotal,2)."</td>";
									$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
									$receiptsRows .= "</tr>";
								}
							}
							$subParentLedgerTotal += $sub2ParentLedgerTotal;
							if($fviews_type>=3 && $sub2ParentLedgerName !=''){								
								$sub2ParentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parent_sub2_ledger_id/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
								$clsbold = '';
								if($fviews_type>=3 && $parentExists>0){$clsbold = ' class="txtbold"';}
								$receiptsRows .= "<tr>";
								$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"$clsbold>&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$sub2ParentLedgerName</td>";
								$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"$clsbold>".$this->taka_format($sub2ParentLedgerTotal,2)."</td>";
								$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
								$receiptsRows .= "</tr>";
							}
						}
						$parentLedgerTotal += $subParentLedgerTotal;
						if($fviews_type>=2 && $subParentLedgerName !=''){								
							$subParentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parent_sub_ledger_id/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
							$clsbold = '';
							if($fviews_type>=3 && $parentExists>0){$clsbold = ' class="txtbold"';}
							$receiptsRows .= "<tr>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"$clsbold>&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$subParentLedgerName</td>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"$clsbold>".$this->taka_format($subParentLedgerTotal,2)."</td>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
							$receiptsRows .= "</tr>";
						}
					}

					$subGroupTotal += $parentLedgerTotal;
					if($fviews_type>=2 && $parentLedgerName !=''){
						$parentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parent_ledger_id/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
						$clsbold = '';
						if($fviews_type>=3){$clsbold = ' class="txtbold"';}
						$receiptsRows .= "<tr>";
						$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"$clsbold>&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$parentLedgerName</td>";
						$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"$clsbold>".$this->taka_format($parentLedgerTotal,2)."</td>";
						$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
						$receiptsRows .= "</tr>";
					}
				}
				$receiptsTotal += $subGroupTotal;
				$receiptsRows .= "<tr class=\"lightashrow0\">";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><strong>$subGroupName</strong></td>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($subGroupTotal,2)."</strong></td>";
				$receiptsRows .= "</tr>";
			}
		}
		         
        $receiptsRows .= "<tr class=\"lightgreenrow\">";
        $receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><strong>Receipts Total: </strong></td>";
        $receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
        $receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($receiptsTotal,2)."</strong></td>";
        $receiptsRows .= "</tr>";
        
        $grandReceiptsTotal = $openingBalanceTotal+$receiptsTotal;
        $receiptsRows .= "<tr class=\"lightpinkrow\">";
        $receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><strong>Grand Total: </strong></td>";
        $receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
        $receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($grandReceiptsTotal,2)."</strong></td>";
        $receiptsRows .= "</tr>";
       
        //======================Payments View======================//
        $paymentsTotal = 0;					
		if(!empty($paymentsData)){
			foreach($paymentsData as $sub_group_id=>$subGroupInfo){
				$subGroupName = $subGroupIds[$sub_group_id]??'';
				$subGroupTotal = 0;
				foreach($subGroupInfo as $parent_ledger_id=>$parentLedgerInfo){
					$parentLedgerName = $ledIds[$parent_ledger_id]??'';
					$parentLedgerTotal = 0;
					foreach($parentLedgerInfo as $parent_sub_ledger_id=>$subParentLedgerInfo){
						$subParentLedgerName = $ledIds[$parent_sub_ledger_id]??'';
						$subParentLedgerTotal = 0;
						$parentExists = 0;
						foreach($subParentLedgerInfo as $parent_sub2_ledger_id=>$sub2ParentLedgerInfo){
							$sub2ParentLedgerName = $ledIds[$parent_sub2_ledger_id]??'';
							$sub2ParentLedgerTotal = $sonEx = 0;
							foreach($sub2ParentLedgerInfo as $ledger_id=>$ledgerTotal){
								$ledgerName = $ledIds[$ledger_id]??'';									
								$sub2ParentLedgerTotal = round($sub2ParentLedgerTotal+$ledgerTotal, 5);
								if($fviews_type>=3 && $ledgerName !=''){	
									$ledgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$ledger_id/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
									$sonEx++;								
									$paymentsRows .= "<tr>";
									$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp;&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$ledgerName</td>";
									$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($ledgerTotal,2)."</td>";
									$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
									$paymentsRows .= "</tr>";
								}
							}
							$subParentLedgerTotal = round($subParentLedgerTotal+$sub2ParentLedgerTotal, 5);
							
							if($fviews_type>=3 && $parent_sub2_ledger_id>0){
								$sub2ParentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parent_sub2_ledger_id/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";	
								$parentExists++;								
								$clsbold = '';
								if($fviews_type>=3 && $sonEx>0){$clsbold = ' class="txtbold"';}
								$paymentsRows .= "<tr$clsbold>";
								$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\"$clsbold>&emsp;&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$sub2ParentLedgerName</td>";
								$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\"$clsbold>".$this->taka_format($sub2ParentLedgerTotal,2)."</td>";
								$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
								$paymentsRows .= "</tr>";
							}
						}
						$parentLedgerTotal = round($parentLedgerTotal+$subParentLedgerTotal,5);
						if($fviews_type>=3 && $parent_sub_ledger_id>0){
							$subParentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parent_sub_ledger_id/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
							$clsbold = '';
							if($fviews_type>=3 && $parentExists>0){$clsbold = ' class="txtbold PearlBG"';}
							$paymentsRows .= "<tr$clsbold>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$subParentLedgerName</td>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($subParentLedgerTotal,2)."</td>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
							$paymentsRows .= "</tr>";
						}
					}

					$subGroupTotal = round($subGroupTotal+$parentLedgerTotal,5);
					if($fviews_type>=2 && $parent_ledger_id>0){
						$parentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parent_ledger_id/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
						$clsbold = '';
						if($fviews_type>=3){$clsbold = ' class="txtbold lightashrow1"';}
						$paymentsRows .= "<tr$clsbold>";
						$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$parentLedgerName</td>";
						$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($parentLedgerTotal,2)."</td>";
						$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
						$paymentsRows .= "</tr>";
					}
				}
				$paymentsTotal += $subGroupTotal;
				$paymentsRows .= "<tr class=\"lightbluerow\">";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><strong>$subGroupName</strong></td>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($subGroupTotal)."</strong></td>";
				$paymentsRows .= "</tr>";
			}
		}

		$paymentsRows .= "<tr class=\"lightgreenrow\">";
		$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><strong>Payments Total: </strong></td>";
		$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
		$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($paymentsTotal)."</strong></td>";
		$paymentsRows .= "</tr>";
		
		//========================Closing Balance View ==================//
		//-----------------Cash View----------------//
		$cashOpeningBalance = $cashClosingBalance = 0;
		foreach($cashParentIds as $parLedId){
			$parLedInfo =  $ledIdInfos[$parLedId]??array();
			if(!empty($parLedInfo)){
				$parLedName = "$parLedInfo[0] &emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parLedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
				$parLedOpenBalance = $parLedInfo[1];
				if(array_key_exists($parLedId, $openingCashTransaction)){$parLedOpenBalance += $openingCashTransaction[$parLedId];}
				$parLedClosingBalance = 0;
				if(array_key_exists($parLedId, $dateRangeCashTransaction)){$parLedClosingBalance += $dateRangeCashTransaction[$parLedId];}

				//---------------Son1 Ledger Transaction-------------//
				$son1LedInfo =  $cashParSonLedIds[$parLedId]??array();
				$son1LedIdInfo = array();
				if(!empty($son1LedInfo)){
					foreach($son1LedInfo as $sonledger_id=>$sonMoreRows){
						$son1LedRow = $ledIdInfos[$sonledger_id]??array();
						if(!empty($son1LedRow)){
							$son1LedIdInfo[$sonledger_id] = stripslashes(trim($son1LedRow[0]));
						}
					}			
				}
				if(!empty($son1LedIdInfo)){
					asort($son1LedIdInfo);
					foreach($son1LedIdInfo as $son1LedId=>$son1LedName){
						$son1LedRow =  $ledIdInfos[$son1LedId]??array();
						$son1LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son1LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
						$son1LedOpenBalance = $son1LedRow[1];
						if(array_key_exists($son1LedId, $openingCashTransaction)){$son1LedOpenBalance += $openingCashTransaction[$son1LedId];}
						$son1LedClosingBalance = 0;
						if(array_key_exists($son1LedId, $dateRangeCashTransaction)){$son1LedClosingBalance += $dateRangeCashTransaction[$son1LedId];}

						//---------------Son2 Ledger Transaction-------------//
						$son2LedInfo =  $son1LedInfo[$son1LedId]??array();
						$son2LedIdInfo = array();
						if(!empty($son2LedInfo)){
							foreach($son2LedInfo as $sonledger_id=>$sonMoreRows){
								$son2LedRow = $ledIdInfos[$sonledger_id]??array();
								if(!empty($son2LedRow)){
									$son2LedIdInfo[$sonledger_id] = stripslashes(trim($son2LedRow[0]));
								}
							}
						}
						if(!empty($son2LedIdInfo)){
							asort($son2LedIdInfo);
							foreach($son2LedIdInfo as $son2LedId=>$son2LedName){
								$son2LedRow =  $ledIdInfos[$son2LedId]??array();
								$son2LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son2LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
								$son2LedOpenBalance = $son2LedRow[1];
								if(array_key_exists($son2LedId, $openingCashTransaction)){$son2LedOpenBalance += $openingCashTransaction[$son2LedId];}
								$son2LedClosingBalance = 0;
								if(array_key_exists($son2LedId, $dateRangeCashTransaction)){$son2LedClosingBalance += $dateRangeCashTransaction[$son2LedId];}

								//---------------Son3 Ledger Transaction-------------//
								$son3LedInfo =  $son2LedInfo[$son2LedId]??array();
								$son3LedIdInfo = array();
								if(!empty($son3LedInfo)){
									foreach($son3LedInfo as $sonledger_id=>$sonMoreRows){
										$son3LedRow = $ledIdInfos[$sonledger_id]??array();
										if(!empty($son3LedRow)){
											$son3LedIdInfo[$sonledger_id] = stripslashes(trim($son3LedRow[0]));
										}
									}
								}
								if(!empty($son3LedIdInfo)){
									asort($son3LedIdInfo);
									foreach($son3LedIdInfo as $son3LedId=>$son3LedName){
										$son3LedRow =  $ledIdInfos[$son3LedId]??array();
										$son3LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son3LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
										
										$son3LedOpenBalance = $son3LedRow[1];
										if(array_key_exists($son3LedId, $openingCashTransaction)){$son3LedOpenBalance += $openingCashTransaction[$son3LedId];}										
										$son2LedOpenBalance += $son3LedOpenBalance;

										$son3LedClosingBalance = 0;
										if(array_key_exists($son3LedId, $dateRangeCashTransaction)){$son3LedClosingBalance += $dateRangeCashTransaction[$son3LedId];}										
										$son2LedClosingBalance += $son3LedClosingBalance;

										if($fviews_type>2){
											$balance = $son3LedOpenBalance+$son3LedClosingBalance;
											$paymentsRows .= "<tr>";
											$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; &emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son3LedName <span class=\"floatright\">".$this->taka_format($son3LedOpenBalance,2)."</span></td>";
											$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son3LedClosingBalance,2)."</td>";
											$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($balance,2)."</td>";
											$paymentsRows .= "</tr>";
										}
									}
								}

								$son1LedOpenBalance += $son2LedOpenBalance;
								$son1LedClosingBalance += $son2LedClosingBalance;

								if($fviews_type>2){
									$balance = $son2LedOpenBalance+$son2LedClosingBalance;
									$paymentsRows .= "<tr>";
									$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son2LedName<span class=\"floatright\">".$this->taka_format($son2LedOpenBalance,2)."</span></td>";
									$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son2LedClosingBalance,2)."</td>";
									$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($balance,2)."</td>";
									$paymentsRows .= "</tr>";
								}
							}
						}

						$parLedOpenBalance += $son1LedOpenBalance;
						$parLedClosingBalance += $son1LedClosingBalance;
						$clsbold = '';
						if($fviews_type>2){$clsbold = ' class="txtbold"';}
						if($fviews_type>1){
							$balance = $son1LedOpenBalance+$son1LedClosingBalance;
							$paymentsRows .= "<tr$clsbold>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son1LedName <span class=\"floatright\">".$this->taka_format($son1LedOpenBalance,2)."</span></td>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son1LedClosingBalance,2)."</td>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($balance,2)."</td>";
							$paymentsRows .= "</tr>";
						}
					}
				}

				$cashOpeningBalance += $parLedOpenBalance;
				$cashClosingBalance += $parLedClosingBalance;
				$balance = $parLedOpenBalance+$parLedClosingBalance;
				$clsbold = '';
				if($fviews_type>1){$clsbold = ' class="txtbold"';}
				$paymentsRows .= "<tr$clsbold>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><img src=\"/assets/images/Accounts/firstarrow.png\" alt=\"Parent Ledger\">&nbsp $parLedName <span class=\"floatright\">".$this->taka_format($parLedOpenBalance,2)."</span></td>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($parLedClosingBalance,2)."</td>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($balance,2)."</td>";
				$paymentsRows .= "</tr>";
			}
		}

		//-----------------Bank View----------------//
		$bankOpeningBalance = $bankClosingBalance = 0;
		foreach($bankParentIds as $parLedId){
			$parLedInfo =  $ledIdInfos[$parLedId]??array();
			if(!empty($parLedInfo)){
				$parLedName = "$parLedInfo[0] &emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$parLedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
				
				$parLedOpenBalance = $parLedInfo[1];
				if(array_key_exists($parLedId, $openingBankTransaction)){$parLedOpenBalance += $openingBankTransaction[$parLedId];}
				$parLedClosingBalance = 0;
				if(array_key_exists($parLedId, $dateRangeBankTransaction)){$parLedClosingBalance += $dateRangeBankTransaction[$parLedId];}

				//---------------Son1 Ledger Transaction-------------//
				$son1LedInfo =  $bankParSonLedIds[$parLedId]??array();
				$son1LedIdInfo = array();
				if(!empty($son1LedInfo)){
					foreach($son1LedInfo as $sonledger_id=>$sonMoreRows){
						$son1LedRow = $ledIdInfos[$sonledger_id]??array();
						if(!empty($son1LedRow)){
							$son1LedIdInfo[$sonledger_id] = stripslashes(trim($son1LedRow[0]));
						}
					}			
				}
				if(!empty($son1LedIdInfo)){
					asort($son1LedIdInfo);
					foreach($son1LedIdInfo as $son1LedId=>$son1LedName){
						$son1LedRow =  $ledIdInfos[$son1LedId]??array();
						$son1LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son1LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
						
						$son1LedOpenBalance = $son1LedRow[1];
						if(array_key_exists($son1LedId, $openingBankTransaction)){$son1LedOpenBalance += $openingBankTransaction[$son1LedId];}
						$son1LedClosingBalance = 0;
						if(array_key_exists($son1LedId, $dateRangeBankTransaction)){$son1LedClosingBalance += $dateRangeBankTransaction[$son1LedId];}

						//---------------Son2 Ledger Transaction-------------//
						$son2LedInfo =  $son1LedInfo[$son1LedId]??array();
						$son2LedIdInfo = array();
						if(!empty($son2LedInfo)){
							foreach($son2LedInfo as $sonledger_id=>$sonMoreRows){
								$son2LedRow = $ledIdInfos[$sonledger_id]??array();
								if(!empty($son2LedRow)){
									$son2LedIdInfo[$sonledger_id] = stripslashes(trim($son2LedRow[0]));
								}
							}
						}
						if(!empty($son2LedIdInfo)){
							asort($son2LedIdInfo);
							foreach($son2LedIdInfo as $son2LedId=>$son2LedName){
								$son2LedRow =  $ledIdInfos[$son2LedId]??array();
								$son2LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son2LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
								
								$son2LedOpenBalance = $son2LedRow[1];
								if(array_key_exists($son2LedId, $openingBankTransaction)){$son2LedOpenBalance += $openingBankTransaction[$son2LedId];}
								$son2LedClosingBalance = 0;
								if(array_key_exists($son2LedId, $dateRangeBankTransaction)){$son2LedClosingBalance += $dateRangeBankTransaction[$son2LedId];}

								//---------------Son3 Ledger Transaction-------------//
								$son3LedInfo =  $son2LedInfo[$son2LedId]??array();
								$son3LedIdInfo = array();
								if(!empty($son3LedInfo)){
									foreach($son3LedInfo as $sonledger_id=>$sonMoreRows){
										$son3LedRow = $ledIdInfos[$sonledger_id]??array();
										if(!empty($son3LedRow)){
											$son3LedIdInfo[$sonledger_id] = stripslashes(trim($son3LedRow[0]));
										}
									}
								}
								if(!empty($son3LedIdInfo)){
									asort($son3LedIdInfo);
									foreach($son3LedIdInfo as $son3LedId=>$son3LedName){
										$son3LedRow =  $ledIdInfos[$son3LedId]??array();
										$son3LedName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$son3LedId/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
										
										$son3LedOpenBalance = $son3LedRow[1];
										if(array_key_exists($son3LedId, $openingBankTransaction)){$son3LedOpenBalance += $openingBankTransaction[$son3LedId];}
										$son3LedClosingBalance = 0;
										if(array_key_exists($son3LedId, $dateRangeBankTransaction)){$son3LedClosingBalance += $dateRangeBankTransaction[$son3LedId];}
										$son2LedOpenBalance += $son3LedOpenBalance;
										$son2LedClosingBalance += $son3LedClosingBalance;

										if($fviews_type>2){
											$balance = $son3LedOpenBalance+$son3LedClosingBalance;
											$paymentsRows .= "<tr>";
											$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; &emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son3LedName <span class=\"floatright\">".$this->taka_format($son3LedOpenBalance,2)."</span></td>";
											$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son3LedClosingBalance,2)."</td>";
											$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($balance,2)."</td>";
											$paymentsRows .= "</tr>";
										}
									}
								}

								$son1LedOpenBalance += $son2LedOpenBalance;
								$son1LedClosingBalance += $son2LedClosingBalance;
								if($fviews_type>2){
									$balance = $son2LedOpenBalance+$son2LedClosingBalance;
									$paymentsRows .= "<tr>";
									$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son2LedName <span class=\"floatright\">".$this->taka_format($son2LedOpenBalance,2)."</span></td>";
									$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son2LedClosingBalance,2)."</td>";
									$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($balance,2)."</td>";
									$paymentsRows .= "</tr>";
								}
							}
						}

						$parLedOpenBalance += $son1LedOpenBalance;
						$parLedClosingBalance += $son1LedClosingBalance;
						$balance = $son1LedOpenBalance+$son1LedClosingBalance;
						$clsbold = '';
						if($fviews_type>2){$clsbold = ' class="txtbold"';}
						if($fviews_type>1){
							$paymentsRows .= "<tr$clsbold>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp; <img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp $son1LedName <span class=\"floatright\">".$this->taka_format($son1LedOpenBalance,2)."</span></td>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($son1LedClosingBalance,2)."</td>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($balance,2)."</td>";
							$paymentsRows .= "</tr>";
						}
					}
				}

				$bankOpeningBalance += $parLedOpenBalance;
				$bankClosingBalance += $parLedClosingBalance;
				$balance = $parLedOpenBalance+$parLedClosingBalance;
				$clsbold = '';
				if($fviews_type>1){$clsbold = ' class="txtbold"';}
				$paymentsRows .= "<tr$clsbold>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><img src=\"/assets/images/Accounts/firstarrow.png\" alt=\"Parent Ledger\">&nbsp $parLedName <span class=\"floatright\">".$this->taka_format($parLedOpenBalance,2)."</span></td>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($parLedClosingBalance,2)."</td>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($balance,2)."</td>";
				$paymentsRows .= "</tr>";
			}
		}

		$closingBalanceTotal = $cashOpeningBalance + $cashClosingBalance + $bankOpeningBalance + $bankClosingBalance;
        
        $paymentsRows .= "<tr class=\"lightgreenrow\">";
        $paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><strong>Closing Balance: </strong></td>";
        $paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
        $paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($closingBalanceTotal)."</strong></td>";
        $paymentsRows .= "</tr>";

        $grandTotal = $closingBalanceTotal+$paymentsTotal;
        $paymentsRows .= "<tr class=\"lightpinkrow\">";
        $paymentsRows .= "<td data-title=\"Grand Total\" align=\"left\"><strong>Grand Total: </strong></td>";
        $paymentsRows .= "<td data-title=\"\" align=\"right\">&nbsp;</td>";
        $paymentsRows .= "<td data-title=\"Amount\" align=\"right\"><strong>".$this->taka_format($grandTotal)."</strong></td>";
        $paymentsRows .= "</tr>";
		
        $reponseData['receiptsRows'] = $receiptsRows;
        $reponseData['paymentsRows'] = $paymentsRows;
		
		return $reponseData;
	}
	
	function cOGS(){
		$front_data['commonmodel'] = $this->Common_model;	
		$front_data['title'] = 'COGS (Cost of Good Sold)';
		$front_data['selfClass'] = $this;
		$permission = $this->Common_model->permission_on_module("Accounts/".$this->uri->segment(2, ''), 'view');
		if($permission==0){$loadPage = 'Accounts/permissionPage';}
		else{$loadPage = 'Accounts/'.$this->uri->segment(2, '');}
		$this->load->view($loadPage, $front_data);
	}
	
	function incomeStatement(){
		$front_data['commonmodel'] = $this->Common_model;	
		$front_data['title'] = 'Income Statement';
		$front_data['selfClass'] = $this;
		$permission = $this->Common_model->permission_on_module("Accounts/".$this->uri->segment(2, ''), 'view');
		if($permission==0){$loadPage = 'Accounts/permissionPage';}
		else{$loadPage = 'Accounts/'.$this->uri->segment(2, '');}
		$this->load->view($loadPage, $front_data);
	}
	
	function financialPosition(){
		$front_data['commonmodel'] = $this->Common_model;	
		$front_data['title'] = 'Financial Position';
		$front_data['selfClass'] = $this;
		$permission = $this->Common_model->permission_on_module("Accounts/".$this->uri->segment(2, ''), 'view');
		if($permission==0){$loadPage = 'Accounts/permissionPage';}
		else{$loadPage = 'Accounts/'.$this->uri->segment(2, '');}
		$this->load->view($loadPage, $front_data);
	}
	
	function cashFlow(){
		$front_data['commonmodel'] = $this->Common_model;	
		$front_data['title'] = 'Cash Flow';
		$front_data['selfClass'] = $this;
		$permission = $this->Common_model->permission_on_module("Accounts/".$this->uri->segment(2, ''), 'view');
		if($permission==0){$loadPage = 'Accounts/permissionPage';}
		else{$loadPage = 'Accounts/'.$this->uri->segment(2, '');}
		$this->load->view($loadPage, $front_data);
	}
	
	function shareholderEquity(){
		$front_data['commonmodel'] = $this->Common_model;	
		$front_data['title'] = 'Shareholder Equity';
		$front_data['selfClass'] = $this;
		$permission = $this->Common_model->permission_on_module("Accounts/".$this->uri->segment(2, ''), 'view');
		if($permission==0){$loadPage = 'Accounts/permissionPage';}
		else{$loadPage = 'Accounts/'.$this->uri->segment(2, '');}
		$this->load->view($loadPage, $front_data);
	}
	
	//=====================Commonly Used function =====================//
	function AJupdate_Data(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$user_id = $_SESSION["user_id"]??0;
		
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$tableName = $_POST['tableName']??'';
			$idValue = $_POST['idValue']??0;
			$description = $_POST['description']??'';
			$fieldName = $_POST['fieldName']??'';
			$updateValue = $_POST['updateValue']??'';
			$oldFieldValue = $this->getOneFieldById($tableName, $idValue, $fieldName);
			
			$update = $this->db->update($tableName, array($fieldName=>$updateValue), $idValue);
			if($update){
				$teData = $changed = $moreInfo = array();
				$changed[$fieldName] = array($oldFieldValue, $updateValue);
				$teData['created_on'] = date('Y-m-d H:i:s');
				$teData['accounts_id'] = $accounts_id;
				$teData['user_id'] = $user_id;
				$teData['record_for'] = $tableName;
				$teData['record_id'] = $idValue;
				$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
				$this->db->insert('track_edits', $teData);					
				$returnstr = 'Updated successfully';
			}
			else{
				$returnstr = 'Error occured while updating data!';
			}
			return $returnstr;
		}
	}
	
	function AJgetLedgerBalance(){
		$user_id = $_SESSION["user_id"]??0;
		$ledger_id = $_POST['ledger_id']??0;
		$balance = 0;
		if($ledger_id>0){
			$balance += floatval($this->getOneFieldById('ledger', $ledger_id, 'opening_balance'));
			$vlSql = "SELECT SUM(debit_credit*amount) AS balance FROM voucher_list WHERE ledger_id = :ledger_id";
			$vlObj = $this->db->query($vlSql, array('ledger_id'=>$ledger_id));
			if($vlObj){
				while($oneRow = $vlObj->fetch(PDO::FETCH_OBJ)){
					$balance += floatval($oneRow->balance);
				}
			}
		}
		return json_encode(array('login'=>'', 'balance'=>$balance));
	}
	
	function getLedIds($ledId){
		$ledIds = array();
		$parId1 = $this->getOneFieldById('ledger', $ledId, 'parent_ledger_id');
		if($parId1>0){
			$parId2 = $this->getOneFieldById('ledger', $parId1, 'parent_ledger_id');
			if($parId2>0){
				$parId3 = $this->getOneFieldById('ledger', $parId2, 'parent_ledger_id');
				if($parId3>0){
					$parId4 = $this->getOneFieldById('ledger', $parId3, 'parent_ledger_id');
					if($parId4>0){
						$ledIds[$parId4][$parId3][$parId2][$parId1][$ledId] = array();
					}
					else{
						$ledIds[$parId3][$parId2][$parId1][$ledId] = array();
					}
				}
				else{
					$ledIds[$parId2][$parId1][$ledId] = array();
				}
			}
			else{
				$ledIds[$parId1][$ledId] = array();
			}
		}
		else{
			$ledIds[$ledId] = array();
		}
		return $ledIds;
	}
	
	function accountTypes($withDesc=0){
		$data = array('1'=>'Assets',
					'2'=>'Liabilities',
					'3'=>'Equity',
					'4'=>'Revenue/Income',
					'5'=>'Expenses',
					'6'=>'Purchase');
		return $data;
	}
	
	function debitCredits(){
		return array('1'=>'Increase','-1'=>'Decrease');
	}
	
	function voucherTypes(){
		return array(1=>'Receipt', 2=>'Payment', 3=>'Journal', 4=>'Contra', 5=>'Purchase', 6=>'Sales');
	}
	
	function transactionTypes(){
		return array('1'=>'Credit','-1'=>'Debit');
	}
		
	public function htmlBody($pageMiddle){
	    
		$segment3name = $GLOBALS['segment3name'];
		$gettingStartedModules = $GLOBALS['viewFunctions'];
		unset($gettingStartedModules['sview']);

		if($segment3name !='ledger'){
		    $returnHTML .= "<div class=\"row\">
			<div class=\"col-sm-12\">
				<h1 class=\"singin2\">$this->pageTitle <i class=\"fa fa-info-circle txt16normal\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Captures Accounts Settings\"></i></h1>
			</div>   
		</div>
		<div class=\"row\">
			<div class=\"col-md-2 col-sm-3 pright0\">
				<div style=\"margin-top:0;\" class=\"bs-callout well bs-callout-info\">					
					<a href=\"javascript:void(0);\" id=\"settingsleftsidemenu\">
						<i class=\"fa fa-align-justify fa-2\"></i>                                        
					</a>
					<ul class=\"leftsidemenu settingslefthide\">";
						foreach($gettingStartedModules as $module=>$moduletitle){
							$linkstr = "<a href=\"/Accounts/$module\" title=\"$moduletitle\"><span>$moduletitle</span></a>";
							$activeclass = '';
							if(strcmp($GLOBALS['segment3name'],$module)==0){
								$linkstr = "<h4>$moduletitle</h4>";
								$activeclass = ' class="activeclass"';
							}
							$returnHTML .= "<li$activeclass>$linkstr</li>";
						}
					$returnHTML .= "</ul>
				</div>
			</div>			
			<div class=\"col-md-10 col-sm-9\">";
		}
			
		$returnHTML .= "<div class=\"bs-callout well bs-callout-info\" style=\"margin-top:0; border-left:1px solid #EEEEEE; background:#FFF;\">
					$pageMiddle
				</div>";
		if($segment3name !='ledger'){
			$returnHTML .= "</div>
			</div>";
		}
		
		return $returnHTML;
	}
	
	function taka_format($amount = 0, $floatPoints=2, $currency = ''){
		$negYN = 1;
		if($amount<0){$negYN = -1;}
		if($floatPoints==0){$amount = round($amount);}
		else{$amount = round($amount, $floatPoints);}
		if($amount==0){$negYN = 1;}
		
		$amount = $amount*$negYN;
		$tmp = explode(".",$amount);  // for float or double values
		$strMoney = "";
		$amount = $tmp[0];
		$strMoney .= substr($amount, -3,3 ) ;
		$amount = substr($amount, 0,-3 ) ;
		while(strlen($amount)>0)
		{
			$strMoney = substr($amount, -2,2 ).",".$strMoney;
			$amount = substr($amount, 0,-2 );
		}
		$floatVal = 0;
		if(isset($tmp[1])){
			$floatVal = (string)sprintf("%02d", $tmp[1]);
		}
		else{
			$floatVal = (string)sprintf("%02d", $floatVal);
		}
		
		if($negYN<0){$strMoney = "-$currency$strMoney";}
		else{$strMoney = "$currency$strMoney";}
		if($floatPoints>0){
			$strMoney .= '.'.$floatVal;
		}
		
		return $strMoney;
	}
	
}
?>