<?php
class Accounts{
	protected $db;
	public function __construct($db){$this->db = $db;}
	private $account_type, $groups_id, $groups_id1, $visible_on, $keyword_search, $ledger_id, $fdate, $date_range, $CountList, $publish, $voucher_type, $groupsOpt, $groupsOpt1, $ledgerOpt, $vouTypOpt, $totalRows, $tableRows, $page, $rowHeight;
	private int $data_type;

	public function dashboard(){}

	public function groups(){}

	public function AJgetGroupsPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$groups_id = intval(trim($POST['groups_id']??0));
		$account_type = $parent_group_id = 0;
		$groupsData = array();
		$groupsData['login'] = '';
		if($prod_cat_man==0){$groupsData['login'] = 'session_ended';}
		$groupsData['groups_id'] = 0;
		$groupsData['name'] = '';
		
		if($groups_id>0 && $prod_cat_man>0){
			$subGroObj = $this->db->query("SELECT * FROM groups WHERE groups_id = :groups_id AND accounts_id = $prod_cat_man", array('groups_id'=>$groups_id),1);
			if($subGroObj){
				$oneRow = $subGroObj->fetch(PDO::FETCH_OBJ);
				
				$groupsData['groups_id'] = $oneRow->groups_id;
				$account_type = intval($oneRow->account_type);
				$parent_group_id = intval($oneRow->parent_group_id);
				$groupsData['name'] = stripslashes(trim($oneRow->name));
			}
		}
		$groupsData['account_type'] = $account_type;
		$groupsData['parGroOpts'] = $this->setParGroOpt($account_type);
		$groupsData['parent_group_id'] = $parent_group_id;
		return json_encode($groupsData);
	}

	function setParGroOpt($account_type, $post=0){
		$parGroOpts = array();
		if($account_type>0){
			$sql = "SELECT groups_id, name FROM groups WHERE account_type = $account_type AND group_position<=3 AND groups_publish = 1 ORDER BY name ASC";
			$tableObj = $this->db->query($sql, array());
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$parGroOpts[$oneRow->groups_id] = stripslashes(trim($oneRow->name));
				}
			}
		}

		if($post ==1){
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			$jsonResponse['parGroOpt'] = $parGroOpts;
			return json_encode($jsonResponse);
		}
		else{
			return $parGroOpts;
		}
	}

	function getGroupPosition($parent_group_id){
		$group_position = 0;
		if($parent_group_id>0){
			$group_position++;
			$parentGroupId = intval($this->getOneFieldById('groups', $parent_group_id, 'parent_group_id'));
			if($parentGroupId>0){
				$group_position++;
				$parentGroupId2 = intval($this->getOneFieldById('groups', $parentGroupId, 'parent_group_id'));
				if($parentGroupId2>0){
					$group_position++;
				}
			}
		}
		return $group_position;
	}

	public function AJsaveGroups(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$groups_id = 0;
			$savemsg = '';
			$returnStr = 'Ok';
			$groups_id = $POST['groups_id']??0;
			$account_type = intval($POST['account_type']??0);
			$parent_group_id = intval($POST['parent_group_id']??0);
			$name = addslashes(trim($POST['name']??''));
			$accountTypes = $this->accountTypes();
			$accountType = $accountTypes[$account_type];
			if(empty($name)){
				$savemsg = 'error';
				$returnStr = "Name is missing";
			}
			elseif($account_type==0){
				$savemsg = 'error';
				$returnStr = "Account Type is missing";
			}
			else{
				$group_position = $this->getGroupPosition($parent_group_id);

				$saveData = array();
				$saveData['accounts_id'] = $prod_cat_man;
				$saveData['user_id'] = $user_id;
				$saveData['account_type'] = $account_type;
				$saveData['parent_group_id'] = $parent_group_id;
				$saveData['name'] = $name;
				$saveData['group_position'] = $group_position;
				
				$duplSql = "SELECT groups_publish, groups_id FROM groups WHERE accounts_id = $prod_cat_man AND UPPER(TRIM(name)) = :name AND account_type = :account_type AND parent_group_id = :parent_group_id";
				$bindData = array('name'=>strtoupper(trim((string) $name)), 'parent_group_id'=>$parent_group_id, 'account_type'=>$account_type);
				if($groups_id>0){
					$duplSql .= " AND groups_id != :groups_id";
					$bindData['groups_id'] = $groups_id;
				}
				$duplSql .= " LIMIT 0, 1";
				$duplRows = 0;
				$subGroObj = $this->db->querypagination($duplSql, $bindData);
				if($subGroObj){
					foreach($subGroObj as $onerow){
						$duplRows = 1;
						$groups_publish = $onerow['groups_publish'];
						if($groups_id==0 && $groups_publish==0){
							$groups_id = $onerow['groups_id'];
							$this->db->update('groups', array('groups_publish'=>1), $groups_id);
							$duplRows = 0;
							$returnStr = 'Update';
						}
					}
				}
				
				if($duplRows>0){
					$savemsg = 'error';
					$returnStr = "$accountType::$name already exists. Please try again different name or type.";
				}
				else{
					if($groups_id==0){
						$saveData['created_on'] = date('Y-m-d H:i:s');
						$groups_id = $this->db->insert('groups', $saveData);
						if($groups_id){						
							$returnStr = 'Add';
						}
						else{
							$returnStr = 'Adding sub group';
						}
					}
					else{
						$prevName = $prevAccountType = '';
						$oneTRowObj = $this->db->querypagination("SELECT * FROM groups WHERE groups_id = $groups_id", array());
						if($oneTRowObj){
							$prevName = $oneTRowObj[0]['name'];
							$prevAccountType = $accountTypes[0][$oneTRowObj['account_type']];
						}
						
						$update = $this->db->update('groups', $saveData, $groups_id);
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
							$teData['record_for'] = 'groups';
							$teData['record_id'] = $groups_id;
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
			
			$array = array('login'=>'', 'groups_id'=>$groups_id, 'savemsg'=>$savemsg, 'returnStr'=>$returnStr);
			return json_encode($array);		
		}
	}
	
	public function AJgetPage_groups(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??0;
		$faccount_type = $POST['faccount_type']??0;
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = $POST['totalRows']??0;
		$rowHeight = $POST['rowHeight']??34;
		$page = $POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $POST['limit']??'auto';
		
		$this->data_type = $sdata_type;
		$this->account_type = $faccount_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($GLOBALS['segment4name']=='filter'){
			$this->CountList = 'Count';
			$this->groupsData();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$this->rowHeight = $rowHeight;

		$this->CountList = 'List';		
		$jsonResponse['tableRows'] = $this->groupsData();
		
		return json_encode($jsonResponse);
	}
	
	private function groupsData(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$reponseData = array('login'=>'');
		if($prod_cat_man==0){$reponseData['login'] = 'session_ended';}
		$faccount_type = $this->account_type;
		$keyword_search = $this->keyword_search;
		$CountList = $this->CountList;
		$sdata_type = $this->data_type;

		$_SESSION["current_module"] = "groups";
		$_SESSION["list_filters"] = array('faccount_type'=>$faccount_type, 'keyword_search'=>$keyword_search);
		
		$filterSql = "FROM groups WHERE accounts_id = $prod_cat_man";
		$bindData = array();
		if($faccount_type >0){
			$filterSql .= " AND account_type = :account_type";
			$bindData['account_type'] = $faccount_type;
		}
		$publishData = array('1'=>1, '2'=>0);
		if(array_key_exists($sdata_type, $publishData)){
			$filterSql .= " AND groups_publish = $publishData[$sdata_type]";
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
		
		if($CountList == 'Count'){
			$sql = "SELECT COUNT(groups_id) AS totalrows $filterSql";
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
			
			$sql = "SELECT * $filterSql ORDER BY account_type ASC, parent_group_id asc, name ASC LIMIT $starting_val, $limit";
			$dataObj = $this->db->querypagination($sql, $bindData);
			$tableRows = array();
			if($dataObj){
				$sl=$starting_val;
				$accountTypes = $this->accountTypes();
				foreach($dataObj as $oneRow){
					$sl++;
					$groups_id = $oneRow['groups_id'];
					$account_type = $accountTypes[$oneRow['account_type']];
					$parent_group_id = $oneRow['parent_group_id'];
					$parentGroupName = '';
					if($parent_group_id>0){
						$parentGroupName = stripslashes(trim((string) $this->getOneFieldById('groups', $parent_group_id, 'name')));
					}
					
					$tableRows[] = array($groups_id, stripslashes($account_type), $parentGroupName, stripslashes($oneRow['name']), intval($oneRow['groups_publish']));
				}				
			}
			return $tableRows;
		}
	}
	
	public function setGroupsOpt($account_type=1, $callFunc = 1){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$account_type = intval(trim($POST['account_type']??$account_type));
			$groupsData = array();
			$groupsData['login'] = '';
			$groupsOpt = array();
			if($account_type>0){
				$groupsOpts = $this->db->query("SELECT groups_id, name FROM groups WHERE accounts_id = $prod_cat_man AND account_type = :account_type AND parent_group_id = 0 AND groups_publish = 1 ORDER BY account_type ASC, name ASC", array('account_type'=>$account_type));
				if($groupsOpts){
					while($oneRow = $groupsOpts->fetch(PDO::FETCH_OBJ)){
						$oneOptVal = $oneRow->groups_id;
						$oneOptLabel = stripslashes(trim($oneRow->name));
						$groupsOpt[$oneOptVal] = $oneOptLabel;
					}
				}
			}
			
			if($callFunc==1){
				return $groupsOpt;
			}
			else{
				$groupsData['groupsOpt'] = $groupsOpt;
				
				return json_encode($groupsData);
			}
		}
	}
	
	function checkSaveGroups($account_type, $parent_group_id, $name){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;

		$group_position = $this->getGroupPosition($parent_group_id);

		$saveData = array();
		$saveData['accounts_id'] = $prod_cat_man;
		$saveData['user_id'] = $user_id;
		$saveData['account_type'] = $account_type;
		$saveData['parent_group_id'] = $parent_group_id;
		$saveData['name'] = $name;
		$saveData['group_position'] = $group_position;
		$groups_id = 0;
		$duplSql = "SELECT groups_publish, groups_id FROM groups WHERE accounts_id = $prod_cat_man AND UPPER(TRIM(name)) = :name AND account_type = :account_type AND parent_group_id = :parent_group_id LIMIT 0, 1";
		$bindData = array('name'=>strtoupper(trim((string) $name)), 'parent_group_id'=>$parent_group_id, 'account_type'=>$account_type);
		$subGroObj = $this->db->querypagination($duplSql, $bindData);
		if($subGroObj){
			foreach($subGroObj as $onerow){
				$groups_id = $onerow['groups_id'];
				if($onerow['groups_publish']==0){
					$this->db->update('groups', array('groups_publish'=>1), $groups_id);
				}
			}
		}
		
		if($groups_id==0){
			$saveData['created_on'] = date('Y-m-d H:i:s');
			$groups_id = $this->db->insert('groups', $saveData);
		}
		return $groups_id;
	}
	//===========Ledger==================//
	public function ledger(){}
	
	public function AJgetPage_ledger(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			
			$sdata_type = $POST['sdata_type']??1;
			$faccount_type = $POST['faccount_type']??0;
			$fgroups_id = $POST['fgroups_id']??0;
			$fgroups_id1 = $POST['fgroups_id1']??0;
			$fvisible_on = intval($POST['fvisible_on']??0);
			$keyword_search = $POST['keyword_search']??'';
			
			$this->data_type = $sdata_type;
			$this->account_type = $faccount_type;
			$this->groups_id = $fgroups_id;
			$this->groups_id1 = $fgroups_id1;
			$this->visible_on = $fvisible_on;
			$this->keyword_search = $keyword_search;
			$ledgerData = $this->ledgerData();
			
			$groupsOpt = array(0=>'All Group');
			$groupsOpt1 = array(0=>'All Group1');
			$tableRows = array();
			if(!empty($ledgerData)){
				$groupsOpt = $ledgerData['groupsOpt']??$groupsOpt;
				$groupsOpt1 = $ledgerData['groupsOpt1']??$groupsOpt1;

				$tableRows = $ledgerData['tableRows']??array();
			}

			$jsonResponse['groupsOpt'] = $groupsOpt;
			$jsonResponse['groupsOpt1'] = $groupsOpt1;
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

		$sdata_type = $this->data_type;
		$faccount_type = $this->account_type;
		$fgroups_id = $this->groups_id;
		$fgroups_id1 = $this->groups_id1;
		$fvisible_on = $this->visible_on;
		$keyword_search = $this->keyword_search;
		
		$filterSql = array();
		if($faccount_type >0){$filterSql[] = "account_type = $faccount_type";}
		if($fgroups_id >0){$filterSql[] = "groups_id = $fgroups_id";}
		if($fgroups_id1 >0){$filterSql[] = "(ledger_id = $fgroups_id1 OR groups_id1 = $fgroups_id1)";}
		if($fvisible_on>0){$filterSql[] = "visible_on LIKE '%$fvisible_on%'";}
		if($sdata_type <3){
			$filterSql[] = "ledger_publish = $sdata_type";
		}
		
		if($keyword_search !=''){$filterSql[] = "name LIKE '%$keyword_search%'";}
		if(!empty($filterSql)){
			$filterSql = "WHERE ".implode(' AND ', $filterSql);
		}
		
		$groupsOpt = $groupsOpt1 = array();
		$sql = "SELECT * FROM ledger $filterSql ORDER BY groups_id1 ASC, groups_id2 ASC, groups_id3 ASC, name ASC";
		$dataObj = $dataObj = $this->db->query($sql, array());
		$tableRows = array();
		if($dataObj){
			$loadPage = 'Accounts/ledger';
			$editPer = $hidePer = 1;
			
			$allLedIds = $groupsIds = $groupsIds1 = $groupsIdInfos = $ledgerIds = array();
			$checkLedIds = array();
			while($oneRow = $dataObj->fetch(PDO::FETCH_OBJ)){
				$checkLedIds[$oneRow->ledger_id] = '';				
				$groups_id = $oneRow->groups_id;
				$groupsIds[$groups_id] = '';

				$groups_id1 = $oneRow->groups_id1;
				$groupsIds1[$groups_id1] = '';

				$groups_id2 = $oneRow->groups_id2;
				$groups_id3 = $oneRow->groups_id3;

				$ledger_id = $oneRow->ledger_id;
				$ledgerIds[$ledger_id] = '';

				$groupsIdInfos[$groups_id] = array();
				$groupsIdInfos[$groups_id1] = array();
				$groupsIdInfos[$groups_id2] = array();
				$groupsIdInfos[$groups_id3] = array();

				$allLedIds[$groups_id][$groups_id1][$groups_id2][$groups_id3][$ledger_id] = array($oneRow->account_type, stripslashes(trim($oneRow->name)), $oneRow->visible_on, intval($oneRow->debit), intval($oneRow->ledger_publish), $oneRow->closing_date);
			}
			
			if(!empty($groupsIdInfos)){	
				$tableObj = $this->db->query("SELECT groups_id, name FROM groups WHERE groups_id IN (".implode(',', array_keys($groupsIdInfos)).") ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						if(array_key_exists($oneRow->groups_id, $groupsIds)){
							$groupsIds[$oneRow->groups_id] = stripslashes(trim($oneRow->name));
						}
						if(array_key_exists($oneRow->groups_id, $groupsIds1)){
							$groupsIds1[$oneRow->groups_id] = stripslashes(trim($oneRow->name));
						}
						$groupsIdInfos[$oneRow->groups_id] = stripslashes(trim($oneRow->name));
					}
				}			
			}

			if(!empty($ledgerIds)){	
				$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(',', array_keys($ledgerIds)).") ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
						$ledgerIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
					}
				}			
			}

			if(!empty($allLedIds)){
				$a = 0;
				$this->db->writeIntoLog('filterSql:'.$filterSql.', allLedIds: ' . json_encode($allLedIds));

				foreach($allLedIds as $groups_id=>$oneGroupsInfo){
					$groupsName = $groupsIdInfos[$groups_id]??'';
					if($groups_id>0){
						$groupsOpt[$groups_id] = $groupsName;
					}
					$ledgerName = $VisibleOn = $Debit = $Publish = $closingDate = '';
					foreach($oneGroupsInfo as $groups_id1=>$oneGroups1Info){
						$groups1Name = '';
						if($groups_id1>0){
							$groups1Name = $groupsIdInfos[$groups_id1]??'';								
							$groupsOpt1[$groups_id1] = $groups1Name;
						}

						foreach($oneGroups1Info as $groups_id2=>$oneGroups2Info){
							$groups2Name = '';
							if($groups_id2>0){
								$groups2Name = $groupsIdInfos[$groups_id2]??'';
							}
							foreach($oneGroups2Info as $groups_id3=>$oneGroups3Info){
								$groups3Name = '';
								if($groups_id3>0){
									$groups3Name = $groupsIdInfos[$groups_id3]??'';
								}

								foreach($oneGroups3Info as $ledger_id=>$oneLedgerInfo){
									$account_type = intval($oneLedgerInfo[0]);
									$groupNames = array();
									if(!empty($groupsName)){$groupNames[] = $groupsName;}
									if(!empty($groups1Name)){$groupNames[] = $groups1Name;}
									if(!empty($groups2Name)){$groupNames[] = $groups2Name;}
									if(!empty($groups3Name)){$groupNames[] = $groups3Name;}
									$groupName = implode(' => ', $groupNames);
									$ledgerName = $oneLedgerInfo[1];
									if($ledger_id>0){
										$ledgerName = $ledgerIds[$ledger_id]??'';
									}
									$VisibleOn = $oneLedgerInfo[2];
									$Debit = $oneLedgerInfo[3];
									$Publish = $oneLedgerInfo[4];
									$closingDate = $oneLedgerInfo[5];
									$closingDateStr = '';
									if(strlen($closingDate)>8){
										$closingDateStr = date('Y-m-d', $closingDate);
									}

									$tableRows[] = array($ledger_id, $account_type, $groupName, $ledgerName, $VisibleOn, $Debit, $closingDateStr, $Publish);
								}
							}
						}
					}
				}
			}
		}
		
		$reponseData['groupsOpt'] = $groupsOpt;
		$reponseData['groupsOpt1'] = $groupsOpt1;
		$reponseData['tableRows'] = $tableRows;
		
		return $reponseData;
	}
	
	function AJgetLedgerPopup(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$ledgerData = $visible_on = array();
			$ledger_id = intval(trim($POST['ledger_id']??0));
			$sgroups_id1 = intval(trim($POST['sgroups_id1']??0));
			$sparObj = false;
			$account_type = $groups_id = $groups_id1 = $groups_id2 = $groups_id3 = 0;
			
			$debit = -1;
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
					$groups_id = $oneRow->groups_id;
					$groups_id1 = $oneRow->groups_id1;
					$groups_id2 = $oneRow->groups_id2;
					$groups_id3 = $oneRow->groups_id3;
					$ledgerData['name'] = stripslashes(trim($oneRow->name));
					$debit = $oneRow->debit;
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
						
			$ledgerData['groupsOpt'] = $this->setGroupsOpt($account_type, 1);
			$ledgerData['groups_id'] = $groups_id;
			
			$ledgerData['sonGroups1Opt'] = $this->setSonGroupsOpt($groups_id);
			$ledgerData['groups_id1'] = $groups_id1;
			
			$ledgerData['sonGroups2Opt'] = $this->setSonGroupsOpt($groups_id1);
			$ledgerData['groups_id2'] = $groups_id2;
			
			$ledgerData['sonGroups3Opt'] = $this->setSonGroupsOpt($groups_id2);
			$ledgerData['groups_id3'] = $groups_id3;
						
			$ledgerData['visible_on'] = $visible_on;
			$ledgerData['debit'] = $debit;
			
			return json_encode($ledgerData);
		}		
	}
	
	function setSonGroupsOpt($groups_id, $post=0){
		$sonGroupOpt = array();
		if($groups_id>0){
			$tableObj = $this->db->query("SELECT groups_id, name FROM groups WHERE parent_group_id = $groups_id AND groups_publish = 1 ORDER BY name ASC", array());
			if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$sonGroupOpt[$oneRow->groups_id] = stripslashes(trim($oneRow->name));
				}
			}
		}

		if($post ==1){
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			$jsonResponse['sonGroupOpt'] = $sonGroupOpt;
			return json_encode($jsonResponse);
		}
		else{
			return $sonGroupOpt;
		}
	}

	function AJsaveLedger(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$accounts_id = $_SESSION["accounts_id"]??0;		
			$user_id = $_SESSION["user_id"]??0;
			$savemsg = 'error';
			$message = 'Could not add / update';
			$ledger_id = intval($POST['ledger_id']??0);
			$account_type = intval($POST['account_type']??0);

			$groups_id = intval($POST['groups_id']??0);
			$groups_name = $POST['groups_name']??'';
			if(!empty($groups_name) && $groups_id == 0){
				$parent_group_id = 0;
				$groups_id = $this->checkSaveGroups($account_type, $parent_group_id, $groups_name);
			}
			$groups_id1 = intval($POST['groups_id1']??0);
			$groups_name1 = $POST['groups_name1']??'';
			if(!empty($groups_name1) && $groups_id1 == 0){
				$parent_group_id = $groups_id;
				$groups_id1 = $this->checkSaveGroups($account_type, $parent_group_id, $groups_name1);
			}
			$groups_id2 = intval($POST['groups_id2']??0);
			$groups_name2 = $POST['groups_name2']??'';
			if(!empty($groups_name2) && $groups_id2 == 0){
				$parent_group_id = $groups_id1;
				$groups_id2 = $this->checkSaveGroups($account_type, $parent_group_id, $groups_name2);
			}
			
			$groups_id3 = intval($POST['groups_id3']??0);
			$groups_name3 = $POST['groups_name3']??'';
			if(!empty($groups_name3) && $groups_id3 == 0){
				$parent_group_id = $groups_id2;
				$groups_id3 = $this->checkSaveGroups($account_type, $parent_group_id, $groups_name3);
			}
			
			$name = addslashes(trim($POST['name']??''));
			$visible_on = $POST['visible_on[]']??array();
			$visible_onStr = '';
			if(!empty($visible_on) && is_array($visible_on)){
				$newVisibleOn = array();
				foreach($visible_on as $oneVisibleOn){
					if(!empty($oneVisibleOn)){
						$newVisibleOn[$oneVisibleOn] = '';
					}
				}
				$visible_onStr = implode(',', array_keys($newVisibleOn));
			}
			$debit = intval($POST['debit']??-1);
			$opening_balance = floatval($POST['opening_balance']??0);
			$opening_date = $POST['opening_date']??0;
			if(strlen($opening_date)==10){$opening_date = strtotime($opening_date);}
			else{$opening_date = 0;}
			$closing_date = $POST['closing_date']??0;
			if(strlen($closing_date)==10){$closing_date = strtotime($closing_date);}
			else{$closing_date = 0;}

			$accountTypes = $this->accountTypes();
			$accountType = $accountTypes[$account_type];
			if(!empty($name) && $account_type>0){
				$saveData = array();
				$saveData['accounts_id'] = $accounts_id;
				$saveData['user_id'] = $user_id;
				$saveData['account_type'] = $account_type;
				$saveData['groups_id'] = $groups_id;
				$saveData['groups_id1'] = $groups_id1;
				$saveData['groups_id2'] = $groups_id2;
				$saveData['groups_id3'] = $groups_id3;
				$saveData['name'] = $name;
				$saveData['debit'] = $debit;
				$saveData['visible_on'] = $visible_onStr;
				$saveData['opening_balance'] = $opening_balance;
				$saveData['opening_date'] = $opening_date;
				$saveData['closing_date'] = $closing_date;
				$saveData['last_updated'] = date('Y-m-d H:i:s');
				
				$duplSql = "SELECT ledger_id FROM ledger WHERE name = :name AND account_type = :account_type AND groups_id1 = :groups_id1";
				if($ledger_id>0){
					$duplSql .= " AND ledger_id != $ledger_id";
				}
				$duplRows = $this->db->querypagination($duplSql, array('name'=>$name, 'account_type'=>$account_type, 'groups_id1'=>$groups_id1));
				
				if(!empty($duplRows)){
					$savemsg = 'error';
					$message = "<p>$accountType::$name already exists. Please try again different name or type.</p>";
					foreach($duplRows as $oneRow){
						$ledger_id = $oneRow['ledger_id'];
						$update = $this->db->update('ledger', array('ledger_publish'=>1), $ledger_id);
						if($update){
							$savemsg = 'Updated';
						}
					}
				}
				else{
					
					if($ledger_id==0){
						$saveData['created_on'] = date('Y-m-d H:i:s');
						$ledger_id = $this->db->insert('ledger', $saveData);
						if(!$ledger_id){
							$message = "Opps!! Could not insert.";
						}
						else{
							$savemsg = 'Added';
							$message = "Added Successfully.";
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
							$savemsg = 'Updated';
							$message = "Updated Successfully.";		

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
							$message = "Opps!! Could not update.";
						}
					}
				}
			}
			else{
				$message = "Group Type / Ledger Name is missing.";
			}

			$array = array('login'=>'', 'ledger_id'=>$ledger_id, 'savemsg'=>$savemsg, 'message'=>$message);
			return json_encode($array);
		}
	}
	
	//===========Ledger View Page==================//
	public function ledgerView($ledger_id){
		
	}

	public function AJ_ledgerview_moreInfo($ledger_id){
		$POST = json_decode(file_get_contents('php://input'), true);
		$ledger_id = intval($POST['ledger_id']??0);

		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$dateformat = $_SESSION["dateformat"]??'Y-m-d';
		$sonLedIdsData = array();

		$oneRow = $this->getOneRowById('ledger', $ledger_id);
		if(!empty($oneRow)){
			$innerHTMLStr = "";
		
			$ledger_id = $oneRow->ledger_id;
			$user_id = $oneRow->user_id;
			
			$account_type = $oneRow->account_type;
			
			$jsonResponse['ledger_id'] = $ledger_id;
			$jsonResponse['account_type'] = $account_type;
			$jsonResponse['name'] = $name = stripslashes(trim($oneRow->name));
			$jsonResponse['groups_id'] = intval($oneRow->groups_id);
			$jsonResponse['debit'] = intval($oneRow->debit);
			$this->pageTitle = 'Ledger Details Info for '.$name;
			
			$groups_id = $oneRow->groups_id;
			$groupsName = $this->getOneFieldById('groups', $groups_id, 'name');
			$jsonResponse['groupsName'] = $groupsName;
			$groups_id1 = $oneRow->groups_id1;
			$visible_on = stripslashes(trim($oneRow->visible_on));
			 
			$jsonResponse['visible_on'] = $visible_on;
			$openingBalance = $oneRow->opening_balance;
			$jsonResponse['openingBalance'] = $openingBalance;
			if($oneRow->opening_date>0){
				$opening_date = date('d.m.Y', $oneRow->opening_date);
			}
			else{$opening_date = '';}
			$jsonResponse['opening_date'] = $opening_date;
			if($oneRow->closing_date>0){
				$closing_date = date('d.m.Y', $oneRow->closing_date);
			}
			else{$closing_date = '';}
			$jsonResponse['closing_date'] = $closing_date;
			
			$incomeTotal = $openingBalance;
			$expenseTotal = 0;
			$parentName = 'Parent';
			if($groups_id1>0){
				$parentName = stripslashes(trim($this->getOneFieldById('ledger', $groups_id1, 'name')));
			}
			$jsonResponse['parentName'] = $parentName;
			$name = stripslashes(trim($oneRow->name));
			$jsonResponse['name'] = $name;
			
			$editPer = $hidePer = 1;
			$a = 0;
			$sonLedIds = $impSonLedIds = array();
			$sonLedIds[$ledger_id] = '';
			$sonTableObj = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id1 = $ledger_id ORDER BY name ASC", array());
			if(!$sonTableObj){
				$sonTableObj = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id2 = $ledger_id ORDER BY name ASC", array());
				if(!$sonTableObj){
					$sonTableObj = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id3 = $ledger_id ORDER BY name ASC", array());
				}
			}

			if($sonTableObj){
				while($sonOneRow = $sonTableObj->fetch(PDO::FETCH_OBJ)){
					$mledger_id = $sonOneRow->ledger_id;
					$sonLedIds[$mledger_id] = '';

					$sonTableObj1 = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id2 = $mledger_id ORDER BY name ASC", array());
					if(!$sonTableObj){
						$sonTableObj1 = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id3 = $mledger_id ORDER BY name ASC", array());
					}
					if($sonTableObj1){
						while($sonOneRow1 = $sonTableObj1->fetch(PDO::FETCH_OBJ)){
							$nledger_id = $sonOneRow1->ledger_id;
							$sonLedIds[$nledger_id] = '';

							$sonTableObj2 = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id3 = $nledger_id ORDER BY name ASC", array());
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

			$jsonResponse['incomeTotal'] = $incomeTotal;
			$jsonResponse['expenseTotal'] = $expenseTotal;
			
			if(!empty($impSonLedIds)){
				unset($impSonLedIds[$ledger_id]);
				$sonSql = "SELECT ledger_id, groups_id1, groups_id2, groups_id3 FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($impSonLedIds)).") ORDER BY groups_id1 ASC, groups_id2 ASC, groups_id3 ASC, name ASC";
				$sonDataObj = $this->db->query($sonSql, array());
				$allLedIds = $ledIdInfos = array();
				while($sonOneRow = $sonDataObj->fetch(PDO::FETCH_OBJ)){
					$sgroups_id1 = $sonOneRow->groups_id1;
					$sgroups_id2 = $sonOneRow->groups_id2;
					$sgroups_id3 = $sonOneRow->groups_id3;
					$sledger_id = $sonOneRow->ledger_id;				
					if($sgroups_id1==0){
						$sgroups_id1 = $sledger_id;
						$sgroups_id2 = $sgroups_id3 = $sledger_id = 0;
					}
					else if($sgroups_id2==0){
						$sgroups_id2 = $sledger_id;
						$sgroups_id3 = $sledger_id = 0;
					}
					else if($sgroups_id3==0){
						$sgroups_id3 = $sledger_id;
						$sledger_id = 0;
					}
					$ledIdInfos[$sgroups_id1] = array();
					$ledIdInfos[$sgroups_id2] = array();
					$ledIdInfos[$sgroups_id3] = array();
					$ledIdInfos[$sledger_id] = array();
					$allLedIds[$sgroups_id1][$sgroups_id2][$sgroups_id3][$sledger_id] = '';
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
					foreach($allLedIds as $sgroups_id1=>$parMoreRows){
						$ledgerOneRow = $ledIdInfos[$sgroups_id1]??array();
						if(!empty($ledgerOneRow)){
							$sgroupsName = $this->getOneFieldById('groups', $ledgerOneRow->groups_id, 'name');
							$sname = stripslashes(trim($ledgerOneRow->name));
							$parLedIds[$sgroups_id1] = $accountTypes[$ledgerOneRow->account_type]." || $sgroupsName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date";
						}
					}			
				}
				
				if(!empty($parLedIds)){
					asort($parLedIds);
					$a = 0;
					foreach($parLedIds as $sgroups_id1=>$oneParLedInfo){
						$parLedInfo = $allLedIds[$sgroups_id1];
						$a++;
						$oneLedInfo = explode(' || ', $oneParLedInfo);								
						$arrow = 0;
						$sonLedIdsData[] = array($oneLedInfo[0], $oneLedInfo[1], $oneLedInfo[2], $oneLedInfo[3],  $debitCredits[intval($oneLedInfo[4])], $debitCredits[intval($oneLedInfo[5])], $oneLedInfo[6], $oneLedInfo[7], $editPer, $hidePer, $arrow, $sgroups_id1, $oneLedInfo[8]);
						
						if(!empty($parLedInfo)){
							$parSubLedIds = array();
							foreach($parLedInfo as $sgroups_id2=>$moreRows){
								$ledgerOneRow = $ledIdInfos[$sgroups_id2]??array();
								if(!empty($ledgerOneRow)){
									$sgroupsName = $this->getOneFieldById('groups', $ledgerOneRow->groups_id, 'name');
									$sname = stripslashes(trim($ledgerOneRow->name));
									$parSubLedIds[$sgroups_id2] = $accountTypes[$ledgerOneRow->account_type]." || $sgroupsName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date";
								}
							}
							asort($parSubLedIds);
							foreach($parSubLedIds as $sgroups_id2=>$oneParSubLedInfo){
								$parSubLedInfo = $parLedInfo[$sgroups_id2];
								$a++;								
								$arrow = 1;
								$oneLedInfo = explode(' || ', $oneParSubLedInfo);
								$sonLedIdsData[] = array($oneLedInfo[0], $oneLedInfo[1], $oneLedInfo[2], $oneLedInfo[3],  $debitCredits[intval($oneLedInfo[4])], $debitCredits[intval($oneLedInfo[5])], $oneLedInfo[6], $oneLedInfo[7], $editPer, $hidePer, $arrow, $sgroups_id2, $oneLedInfo[8]);
						
								if(!empty($parSubLedInfo)){
									$parSub2LedIds = array();
									foreach($parSubLedInfo as $groups_id3=>$moreRows){
										$ledgerOneRow = $ledIdInfos[$groups_id3]??array();
										if(!empty($ledgerOneRow)){
											$sgroupsName = $this->getOneFieldById('groups', $ledgerOneRow->groups_id, 'name');
											$sname = stripslashes(trim($ledgerOneRow->name));
											$parSub2LedIds[$groups_id3] = $accountTypes[$ledgerOneRow->account_type]." || $sgroupsName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date";
										}
									}
									asort($parSub2LedIds);
									foreach($parSub2LedIds as $sgroups_id3=>$oneParSub2LedInfo){
										$parSub2LedInfo = $parSubLedInfo[$sgroups_id3];
										$a++;
										$arrow = 2;
										$oneLedInfo = explode(' || ', $oneParSub2LedInfo);
										$sonLedIdsData[] = array($oneLedInfo[0], $oneLedInfo[1], $oneLedInfo[2], $oneLedInfo[3],  $debitCredits[intval($oneLedInfo[4])], $debitCredits[intval($oneLedInfo[5])], $oneLedInfo[6], $oneLedInfo[7], $editPer, $hidePer, $arrow, $sgroups_id3, $oneLedInfo[8]);
						
										if(!empty($parSub2LedInfo)){													
											$sledgerIds = array();
											foreach($parSub2LedInfo as $sledger_id=>$oneLedInfo){
												$ledgerOneRow = $ledIdInfos[$sledger_id]??array();
												if(!empty($ledgerOneRow)){
													$sgroupsName = $this->getOneFieldById('groups', $ledgerOneRow->groups_id, 'name');
													$sname = stripslashes(trim($ledgerOneRow->name));
													$sledgerIds[$sledger_id] = $accountTypes[$ledgerOneRow->account_type]." || $sgroupsName || $sname || $ledgerOneRow->visible_on || $ledgerOneRow->debit || $ledgerOneRow->ledger_publish || $ledgerOneRow->closing_date";
												}
											}
											asort($sledgerIds);
											foreach($sledgerIds as $sledger_id=>$oneLedInfo){
												$a++;
												$arrow = 3;
												$oneLedInfo = explode(' || ', $oneLedInfo);
												$sonLedIdsData[] = array($oneLedInfo[0], $oneLedInfo[1], $oneLedInfo[2], $oneLedInfo[3],  $debitCredits[intval($oneLedInfo[4])], $debitCredits[intval($oneLedInfo[5])], $oneLedInfo[6], $oneLedInfo[7], $editPer, $hidePer, $arrow, $sledger_id, $oneLedInfo[8]);
												
											}
										}
									}
								}
							}
						}								
					}
				}			
			}
		}
			
		$jsonResponse['sonLedIdsData'] = $sonLedIdsData;
		return json_encode($jsonResponse);
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
		$currency = $_SESSION["currency"]??'৳';
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

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['starting_val'] = $starting_val;
		$jsonResponse['balance'] = $balance;
		$voucherData = $vDateNote = $ledIds = $vouParData = array();

		$sql = "SELECT v.voucher_id, v.voucher_date, v.voucher_type, v.voucher_no, vl.narration, vl.qty, vl.unit_price, vl.debit_credit, vl.amount FROM voucher v, voucher_list vl WHERE $filterSql v.voucher_id = vl.voucher_id ORDER BY v.voucher_date ASC, v.voucher_type ASC, v.voucher_no ASC LIMIT $starting_val, $limit";
		$dataObj = $this->db->querypagination($sql, $bindData);
		if($dataObj){
			
			$accountTypes = $this->accountTypes();
			$debitCredits = $this->debitCredits();
			
			
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
		}
		$jsonResponse['voucherData'] = $voucherData;
		$jsonResponse['vDateNote'] = $vDateNote;
		
		return $jsonResponse;
   	}
		
	public function AJgetHPageLedger($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$ledger_id = $POST['ledger_id']??0;
		$date_range = $POST['date_range']??'';
		$totalRows = $POST['totalRows']??0;
		$rowHeight = $POST['rowHeight']??34;
		$page = $POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $POST['limit']??'auto';
		
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
	
	public function AJautoComplete_Ledger(){
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$POST = json_decode(file_get_contents('php://input'), true);
		$keyword_search = $POST['keyword_search']??'';
		$voucherTypeVal = intval($POST['voucherTypeVal']??0);
		$responseData = array();
		$ledSql = "SELECT ledger_id, name FROM ledger WHERE accounts_id = $prod_cat_man AND (visible_on='' OR visible_on LIKE '%$voucherTypeVal%')";
		if($keyword_search !=''){$ledSql .= " AND name LIKE '%$keyword_search%'";}
		$ledSql .= " AND ledger_publish = 1 ORDER BY account_type ASC, groups_id ASC, groups_id1 ASC, groups_id2 ASC, groups_id3 ASC, name ASC";
		//$responseData[] = array('label' => $ledSql, 'lId' => 0);
		$tableObj = $this->db->query($ledSql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$name = stripslashes($oneRow->name);
				$responseData[] = array('label' => $name, 'lId' => $oneRow->ledger_id);
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$responseData));
	}

	public function receiptVoucher(){}
	
	public function paymentVoucher(){}
	
	public function journalVoucher(){}
	
	public function contraVoucher(){}
	
	public function purchaseVoucher(){}
	
	public function salesVoucher(){}
	
	//===========Voucher1 Page==================//
	
	public function AJgetPage_Voucher1(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$fpublish = intval($POST['fpublish']??"1");
		$fvoucher_type = $POST['fvoucher_type']??1;
		$faccount_type = $POST['faccount_type']??0;
		$fgroups_id = $POST['fgroups_id']??0;
		$fledger_id = $POST['fledger_id']??0;
		$keyword_search = $POST['keyword_search']??'';
		
		$totalRows = $POST['totalRows']??0;
		$rowHeight = $POST['rowHeight']??34;
		$page = $POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $POST['limit']??'auto';
		
		$this->publish = $fpublish;
		$this->voucher_type = $fvoucher_type;
		$this->account_type = $faccount_type;
		$this->groups_id = $fgroups_id;
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
		if($fvoucher_type<=0 || $fvoucher_type>6){$fvoucher_type = 1;}
		$faccount_type = $this->account_type;
		$fgroups_id = $this->groups_id;
		$fledger_id = $this->ledger_id;
		$keyword_search = $this->keyword_search;
		$CountList = $this->CountList;
		
		$_SESSION["current_module"] = $this->voucherTypesSegments($fvoucher_type);

		$_SESSION["list_filters"] = array('fpublish'=>$fpublish, 'fvoucher_type'=>$fvoucher_type, 'faccount_type'=>$faccount_type, 'fgroups_id'=>$fgroups_id, 'fledger_id'=>$fledger_id, 'keyword_search'=>$keyword_search);
		$publishOptions = array('>0', '=1', '=2', '=0');
		$filterSql = "FROM voucher v, voucher_list vl, ledger l WHERE v.accounts_id = $accounts_id AND v.voucher_type = $fvoucher_type AND v.voucher_publish$publishOptions[$fpublish] AND ";
		
		$bindData = array();
		if($faccount_type >0){$filterSql .= "l.account_type = $faccount_type AND ";}		
		if($fgroups_id >0){$filterSql .= "l.groups_id = $fgroups_id AND ";}		
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

		$filterSql .= " v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id GROUP BY v.voucher_id";
		if($CountList == 'Count'){
			$totalRows = 0;
			$sql = "SELECT v.voucher_id $filterSql";
			$query = $this->db->querypagination($sql, $bindData);
			if($query){
				$totalRows = count($query);
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
			
			$sql = "SELECT v.voucher_id, v.voucher_date, v.voucher_no, v.voucher_publish $filterSql ORDER BY v.voucher_no DESC LIMIT $starting_val, $limit";
			$dataObj = $this->db->querypagination($sql, $bindData);
			$tableRows = array();
			if($dataObj){
				$sl = $starting_val;
				$accountTypes = $this->accountTypes();
				$debitCredits = $this->debitCredits();
				$LedIds = $voucherData = $voucherListData = array();
				foreach($dataObj as $oneRow){
					$voucher_id = $oneRow['voucher_id'];
					$voucherData[$voucher_id] = array($oneRow['voucher_no'], $oneRow['voucher_date'], $oneRow['voucher_publish']);
					
					$oneVLData = array();
					$tableObj = $this->db->query("SELECT ledger_id, narration, debit_credit, amount FROM voucher_list WHERE voucher_id = $voucher_id ORDER BY voucher_list_id ASC", array());
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$LedIds[$oneRow->ledger_id] = '';
							$oneVLData[] = array($oneRow->ledger_id, stripslashes(trim($oneRow->narration)), $oneRow->debit_credit, $oneRow->amount);
						}
					}
					$voucherListData[$voucher_id] = $oneVLData;
				}
				
				if(!empty($LedIds)){
					$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($LedIds)).") AND ledger_publish = 1 ORDER BY name ASC", array());
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
						}
					}
				}

				$tableRows = array($voucherData, $voucherListData, $LedIds);
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
			$POST = json_decode(file_get_contents('php://input'), true);
			$voucher_id = intval(trim($POST['voucher_id']??0));
			$voucher_type = intval(trim($POST['voucher_type']??1));
			$voucherLists = $voucher1Data = array();
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
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = $message = '';
		
		$voucher_id = $POST['voucher_id']??0;
		$voucher_type = $POST['voucher_type']??0;
		$voucher_no = $POST['voucher_no']??0;
		$voucher_date = $POST['voucher_date']??'1000-01-01';
		
		$voucher_listIds = $POST['voucher_list_id[]']??array();
		$ledgerIds = $POST['ledger_id[]']??array();
		$narrationIds = $POST['narration[]']??array();
		$debCreIds = $POST['debit_credit[]']??array();
		$debitIds = $POST['debit[]']??array();
		$creditIds = $POST['credit[]']??array();
					
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
				$returnStr = "$voucherType::$voucher_no already exists. Please try again different voucher no or type.";
			}
			else{
				if($voucher_id==0){
					$saveData['created_on'] = date('Y-m-d H:i:s');
					$saveData['voucher_publish'] = 2;
					$voucher_id = $this->db->insert('voucher', $saveData);
					if(!$voucher_id){
						$savemsg = 'error';
						$message .= "Opps!! Could not insert.";
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
			$message .= "Voucher Type / Voucher no. is missing.";
		}
		
		return json_encode(array('login'=>'', 'voucher_id'=>$voucher_id, 'savemsg'=>$savemsg, 'message'=>$message));
	}
	
	public function voucherPrint(){
		$voucher_id = $GLOBALS['segment4name'];
		if($voucher_id==0){
			return 'There is no voucher found';
		}
		else{
			$accounts_id = $_SESSION["accounts_id"]??0;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$currency = $_SESSION["currency"]??'৳';
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
												$fromLedgerPId = $this->getOneFieldById('ledger', $fromLedgerId, 'groups_id1');
												if($fromLedgerPId>0){
													$fromLedgerPPId = $this->getOneFieldById('ledger', $fromLedgerPId, 'groups_id1');
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
												$amount = floatval($listOneRow->amount);
												$ledgerName = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
												$narration = stripslashes($listOneRow->narration);
														
												if($creditCount==1){
													if($debit_credit==1){
														$sl++;
														
														$totAmount += $amount;
														
														$htmlStr .= "<tr>
																		<td style=\"height:$rowHeight"."px\" valign=\"center\" align=\"right\" data-title=\"SL\">ss $sl</td>
																		<td data-title=\"Particular\" valign=\"center\">$ledgerName</td>
																		<td data-title=\"Narration\" valign=\"center\">$narration</td>
																		<td data-title=\"Amount\" valign=\"center\" align=\"right\">".$this->taka_format($amount, 2, 'TK')."</td>
																	</tr>";
													}
												}
												else{
													$sl++;													
													$debit = $credit = 0;
													if($debit_credit==1){$debit = $amount;}
													else{$credit = $amount;}
													$totDeb += $debit;
													$totCre += $credit;
													
													$debitStr = $creditStr = '&nbsp;';
													if($debit !=0){$debitStr = $this->taka_format($debit, 2, 'TK');}
													if($credit !=0){$creditStr = $this->taka_format($credit, 2, 'TK');}
													$htmlStr .= "<tr>
																	<td style=\"height:$rowHeight"."px\" valign=\"center\" align=\"right\" data-title=\"SL\">$sl</td>
																	<td data-title=\"Particular\" valign=\"center\">$ledgerName</td>
																	<td data-title=\"Narration\" valign=\"center\">$narration</td>
																	<td data-title=\"Debit\" valign=\"center\" align=\"right\">$debitStr</td>
																	<td data-title=\"Credit\" valign=\"center\" align=\"right\">$creditStr</td>
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
								$totalinwords = $Common->makewords($totAmount);
								
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
	
	//===========Voucher2 Page==================//		
	public function AJgetPage_Voucher2(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$fpublish = intval($POST['fpublish']??"1");
		$fvoucher_type = $POST['fvoucher_type']??5;
		$faccount_type = $POST['faccount_type']??0;
		$fgroups_id = $POST['fgroups_id']??0;
		$fledger_id = $POST['fledger_id']??0;
		$keyword_search = $POST['keyword_search']??'';
		
		$totalRows = $POST['totalRows']??0;
		$rowHeight = $POST['rowHeight']??34;
		$page = $POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $POST['limit']??'auto';
		
		$this->publish = $fpublish;
		$this->voucher_type = $fvoucher_type;
		$this->account_type = $faccount_type;
		$this->groups_id = $fgroups_id;
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
		if($fvoucher_type<=0 || $fvoucher_type>6){$fvoucher_type = 1;}
		$faccount_type = $this->account_type;
		$fgroups_id = $this->groups_id;
		$fledger_id = $this->ledger_id;
		$keyword_search = $this->keyword_search;
		$CountList = $this->CountList;
		
		$_SESSION["current_module"] = $this->voucherTypesSegments($fvoucher_type);

		$_SESSION["list_filters"] = array('fpublish'=>$fpublish, 'fvoucher_type'=>$fvoucher_type, 'faccount_type'=>$faccount_type, 'fgroups_id'=>$fgroups_id, 'fledger_id'=>$fledger_id, 'keyword_search'=>$keyword_search);
		$publishOptions = array('>0', '=1', '=2', '=0');
		$filterSql = "FROM voucher v, voucher_list vl, ledger l WHERE v.accounts_id = $accounts_id AND v.voucher_type = $fvoucher_type AND v.voucher_publish$publishOptions[$fpublish] AND ";
		
		$bindData = array();
		if($faccount_type >0){$filterSql .= "l.account_type = $faccount_type AND ";}		
		if($fgroups_id >0){$filterSql .= "l.groups_id = $fgroups_id AND ";}		
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

		$filterSql .= " v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id GROUP BY v.voucher_id";
		if($CountList == 'Count'){
			$totalRows = 0;
			$sql = "SELECT v.voucher_id $filterSql";
			$query = $this->db->querypagination($sql, $bindData);
			if($query){
				$totalRows = count($query);
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
			
			$sql = "SELECT v.voucher_id, v.voucher_date, v.voucher_no, v.voucher_publish $filterSql ORDER BY v.voucher_no DESC LIMIT $starting_val, $limit";
			$dataObj = $this->db->querypagination($sql, $bindData);
			$tableRows = array();
			if($dataObj){
				$sl = $starting_val;
				$accountTypes = $this->accountTypes();
				$debitCredits = $this->debitCredits();
				$LedIds = $voucherData = $voucherListData = array();
				foreach($dataObj as $oneRow){
					$voucher_id = $oneRow['voucher_id'];
					$voucherData[$voucher_id] = array($oneRow['voucher_no'], $oneRow['voucher_date'], $oneRow['voucher_publish']);
					
					$oneVLData = array();
					$tableObj = $this->db->query("SELECT ledger_id, narration, debit_credit, qty, unit_price, amount FROM voucher_list WHERE voucher_id = $voucher_id ORDER BY voucher_list_id ASC", array());
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$LedIds[$oneRow->ledger_id] = '';
							$oneVLData[] = array($oneRow->ledger_id, stripslashes(trim($oneRow->narration)), $oneRow->debit_credit, $oneRow->qty, $oneRow->unit_price, $oneRow->amount);
						}
					}
					$voucherListData[$voucher_id] = $oneVLData;
				}
				
				if(!empty($LedIds)){
					$tableObj = $this->db->query("SELECT ledger_id, name FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($LedIds)).") AND ledger_publish = 1 ORDER BY name ASC", array());
					if($tableObj){
						while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
							$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
						}
					}
				}

				$tableRows = array($voucherData, $voucherListData, $LedIds);
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
			$POST = json_decode(file_get_contents('php://input'), true);
			$voucher_id = intval(trim($POST['voucher_id']??0));
			$voucher_type = intval(trim($POST['voucher_type']??1));
			$voucherLists = $voucher2Data = array();
			$voucher2Data['login'] = '';
			$voucher2Data['voucher_id'] = 0;
			$voucher_date = date('Y-m-d');
			$voucher_no = 0;
			$pi_invoice_no = $pi_invoice_date = $lc_phone_no = $lc_date = $notes = '';
			if($voucher_id>0){
				$oneRow = $this->getOneRowById('voucher', $voucher_id);
				if($oneRow){
					$voucher2Data['voucher_id'] = $oneRow->voucher_id;
					$voucher_date = $oneRow->voucher_date;
					$voucher_type = $oneRow->voucher_type;
					$voucher_no = $oneRow->voucher_no;
					$pi_invoice_no = stripslashes(trim((string) $oneRow->pi_invoice_no));
					$pi_invoice_date = $oneRow->pi_invoice_date;
					if(in_array($pi_invoice_date, array('0000-00-00', '1000-01-01'))){$pi_invoice_date = '';}
					$lc_phone_no = stripslashes(trim((string) $oneRow->lc_phone_no));
					$lc_date = $oneRow->lc_date;
					if(in_array($lc_date, array('0000-00-00', '1000-01-01'))){$lc_date = '';}
					$voucnotesher_no = stripslashes(trim((string) $oneRow->voucher_no));
					
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
			$voucher2Data['pi_invoice_no'] = $pi_invoice_no;
			$voucher2Data['pi_invoice_date'] = $pi_invoice_date;
			$voucher2Data['lc_phone_no'] = $lc_phone_no;
			$voucher2Data['lc_date'] = $lc_date;
			$voucher2Data['notes'] = $notes;
			
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
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = $message = '';
		
		$voucher_id = $POST['voucher_id']??0;
		$voucher_type = $POST['voucher_type']??0;
		$voucher_no = $POST['voucher_no']??0;
		$voucher_date = $POST['voucher_date']??'1000-01-01';
		
		$voucher_listIds = $POST['voucher_list_id[]']??array();
		$ledgerIds = $POST['ledger_id[]']??array();
		$narrationIds = $POST['narration[]']??array();
		$debCreIds = $POST['debit_credit[]']??array();
		$debitIds = $POST['debit[]']??array();
		$creditIds = $POST['credit[]']??array();
		$qtyIds = $POST['qty[]']??array();
		$unitPriceIds = $POST['unit_price[]']??array();
					
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
				$returnStr = "$voucherType::$voucher_no already exists. Please try again different voucher no or type.";
			}
			else{
				if($voucher_id==0){
					$saveData['created_on'] = date('Y-m-d H:i:s');
					$saveData['voucher_publish'] = 2;
					$voucher_id = $this->db->insert('voucher', $saveData);
					if(!$voucher_id){
						$savemsg = 'error';
						$message .= "Opps!! Could not insert.";
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
						$qty = $qtyIds[$l];
						$unit_price = $unitPriceIds[$l];
						$amount = $debit;
						if($debit_credit ==-1){$amount = $credit;}
						
						$saveDLData = array('voucher_id'=>$voucher_id, 'ledger_id'=>$ledger_id, 'narration'=>$narration, 'debit_credit'=>$debit_credit, 'qty'=>$qty, 'unit_price'=>$unit_price, 'amount'=>$amount);
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
			$message .= "Voucher Type / Voucher no. is missing.";
		}
		
		return json_encode(array('login'=>'', 'voucher_id'=>$voucher_id, 'savemsg'=>$savemsg, 'message'=>$message));
	}
	
	public function voucher2Print(){
		$voucher_id = $GLOBALS['segment4name'];
		if($voucher_id==0){
			return 'There is no voucher found';
		}
		else{
			$accounts_id = $_SESSION["accounts_id"]??0;
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			$currency = $_SESSION["currency"]??'৳';
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
												$fromLedgerId = $listOneRow->ledger_id;
												$fromLedgerPId = $this->getOneFieldById('ledger', $fromLedgerId, 'groups_id1');
												if($fromLedgerPId>0){
													$fromLedgerPPId = $this->getOneFieldById('ledger', $fromLedgerPId, 'groups_id1');
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
												$amount = floatval($listOneRow->amount);
												$ledgerName = stripslashes($this->getOneFieldById('ledger', $listOneRow->ledger_id, 'name'));
												$narration = stripslashes($listOneRow->narration);
														
												if($creditCount==1){
													if($debit_credit==1){
														$sl++;
														
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
													$debit = $credit = 0;
													if($debit_credit==1){$debit = $amount;}
													else{$credit = $amount;}
													$totDeb += $debit;
													$totCre += $credit;
													
													$debitStr = $creditStr = '&nbsp;';
													if($debit !=0){$debitStr = $this->taka_format($debit, 2, 'TK');}
													if($credit !=0){$creditStr = $this->taka_format($credit, 2, 'TK');}
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
													<td data-title=\"Total\" colspan=\"4\" align=\"right\"><strong>Total:&emsp;</strong></td>
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
								$totalinwords = $Common->makewords($totAmount);
								
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
		$this->groups_id = $fgroups_id = $list_filters['fgroups_id']??0;
		$this->ledger_id = $fledger_id = $list_filters['fledger_id']??0;
		$this->keyword_search = $keyword_search = $list_filters['keyword_search']??'';
		
		$this->CountList = 'Count';
		
		$this->dayBookData();
		$totalRows = $this->totalRows;
		$groupsOpt = $this->groupsOpt;
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
		
		$innerHTMLStr = "
		<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<input type=\"hidden\" name=\"page\" id=\"page\" value=\"$this->page\">
		<input type=\"hidden\" name=\"rowHeight\" id=\"rowHeight\" value=\"$this->rowHeight\">
		<input type=\"hidden\" name=\"totalTableRows\" id=\"totalTableRows\" value=\"$this->totalRows\">
		<div class=\"flexSpaBetRow\">
			<div class=\"col-sm-4 col-md-2\">
				<select name=\"fvoucher_type\" id=\"fvoucher_type\" class=\"form-control changeLoadFilterData\">
					$vouTypOpt
				</select>
			</div>
			<div class=\"col-sm-2 col-md-1 pleft0\">
				<select name=\"fpublish\" id=\"fpublish\" class=\"form-control changeLoadFilterData\">
					$pubOpt
				</select>
			</div>
			<div class=\"col-sm-2 col-md-1 pleft0 pright0\">
				<select name=\"faccount_type\" id=\"faccount_type\" class=\"form-control changeLoadFilterData\">
					$accTypOpt
				</select>
			</div>				
			<div class=\"col-sm-4 col-md-2 pright0\">
				<select name=\"fgroups_id\" id=\"fgroups_id\" class=\"form-control changeLoadFilterData\">
					$groupsOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pright0\">
				<select name=\"fledger_id\" id=\"fledger_id\" class=\"form-control changeLoadFilterData\">
					$parLedOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pright0\">
				<input type=\"text\" name=\"date_range\" id=\"date_range\" class=\"form-control width180px floatright\" placeholder=\"From to Todate\" value=\"$date_range\" /> 
			</div>
			<div class=\"col-sm-4 col-md-2 pbottom10\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Voucher# / Narration\" value=\"$keyword_search\" id=\"keyword_search\" name=\"keyword_search\" class=\"form-control pressLoadFilterData\" maxlength=\"50\" />
					<span class=\"input-group-addon cursor clickLoadFilterData\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Voucher# / Narration\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"flexSpaBetRow\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"table-bordered table-striped table-condensed cf listing\">
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
		<div class=\"flexSpaBetRow\">
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
		$POST = json_decode(file_get_contents('php://input'), true);
		$date_range = $POST['date_range']??'';
		$fpublish = $POST['fpublish']??2;
		$fvoucher_type = $POST['fvoucher_type']??'=2';
		$faccount_type = $POST['faccount_type']??0;
		$fgroups_id = $POST['fgroups_id']??0;
		$fledger_id = $POST['fledger_id']??0;
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = $POST['totalRows']??0;
		$rowHeight = $POST['rowHeight']??34;
		$page = $POST['page']??1;
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = $POST['limit']??'auto';
		
		$this->date_range = $date_range;
		$this->publish = $fpublish;
		$this->voucher_type = $fvoucher_type;
		$this->account_type = $faccount_type;
		$this->groups_id = $fgroups_id;
		$this->ledger_id = $fledger_id;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($GLOBALS['segment4name']=='filter'){
			$this->CountList = 'Count';
			$this->dayBookData();
			$jsonResponse['groupsOpt'] = $this->groupsOpt;
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
		$fgroups_id = $this->groups_id;
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
		if($fgroups_id >0){
			$filterSql .= "l.groups_id = :fgroups_id AND ";
			$bindData['fgroups_id'] = $fgroups_id;
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
			$sql = "SELECT v.voucher_type, l.groups_id, l.ledger_id FROM voucher v, voucher_list vl, ledger l WHERE $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id GROUP BY v.voucher_type, l.groups_id, l.ledger_id";
			$dataObj = $this->db->querypagination($sql, $bindData);
			$vouTypeIds = $subGroIds = $LedIds = array();
			if($dataObj){
				$totalRows = count($dataObj);
				foreach($dataObj as $oneRow){
					$vouTypeIds[$oneRow['voucher_type']] = $voucherTypes[$oneRow['voucher_type']];
					$subGroIds[$oneRow['groups_id']] = '';
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
			$groupsOpt = "<option value=\"0\">Select Sub-Group Name</option>";
			if(!empty($subGroIds)){
				$tableObj = $this->db->query("SELECT groups_id, name FROM groups WHERE groups_id IN (".implode(', ', array_keys($subGroIds)).") AND groups_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
						$groupsOpt .= "<option value=\"$oneRow->groups_id\">".stripslashes(trim($oneRow->name))."</option>";
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
			$this->groupsOpt = $groupsOpt;
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
						$editIcon .= "<a class=\"floatright mleft10 printbyuri\" data-uri=\"/Accounts/voucherPrint/$voucher_id\" href=\"javascript:void(0);\" title=\"Print this Voucher Information\"><i class=\"fa fa-print txt18\"></i></a>";
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
		$this->groups_id = $fgroups_id = $list_filters['fgroups_id']??0;
		$this->ledger_id = $fledger_id = $list_filters['fledger_id']??0;
		$this->keyword_search = $keyword_search = $list_filters['keyword_search']??'';
		
		$tableRows = $this->ledgerReportData();
		
		$groupsOpt = $this->groupsOpt;
		$parLedOpt = $this->parLedOpt;
		$vouTypOpt = $this->vouTypOpt;
		
		$accTypOpt = "<option value=\"0\">All Account Type</option>";
		$accountTypes = $this->accountTypes();
		foreach($accountTypes as $oneOptVal=>$oneOptLabel){
			$selected = '';
			if($faccount_type==$oneOptVal){$selected = ' selected';}
			$accTypOpt .= "<option$selected value=\"$oneOptVal\">$oneOptLabel</option>";
		}
		
		$innerHTMLStr = "
		<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<div class=\"flexSpaBetRow\">
			<div class=\"col-sm-4 col-md-2\">
				<select name=\"fvoucher_type\" id=\"fvoucher_type\" class=\"form-control changeLoadFilterData\">
					$vouTypOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pleft0 pright0\">
				<select name=\"faccount_type\" id=\"faccount_type\" class=\"form-control changeLoadFilterData\">
					$accTypOpt
				</select>
			</div>				
			<div class=\"col-sm-4 col-md-2 pright0\">
				<select name=\"fgroups_id\" id=\"fgroups_id\" class=\"form-control changeLoadFilterData\">
					$groupsOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pright0\">
				<select name=\"fledger_id\" id=\"fledger_id\" class=\"form-control changeLoadFilterData\">
					$parLedOpt
				</select>
			</div>
			<div class=\"col-sm-4 col-md-2 pright0\">
				<input type=\"text\" name=\"date_range\" id=\"date_range\" class=\"form-control pressLoadFilterData\" placeholder=\"From to Todate\" value=\"$date_range\" /> 
			</div>
			<div class=\"col-sm-4 col-md-2 pbottom10\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Voucher# / Narration\" value=\"$keyword_search\" id=\"keyword_search\" name=\"keyword_search\" class=\"form-control pressLoadFilterData\" maxlength=\"50\" />
					<span class=\"input-group-addon cursor clickLoadFilterData\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Voucher# / Narration\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"flexSpaBetRow\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"table-bordered table-striped table-condensed cf listing\">
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
			$POST = json_decode(file_get_contents('php://input'), true);
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			
			$date_range = $POST['date_range']??'';
			$fvoucher_type = $POST['fvoucher_type']??'=2';
			$faccount_type = $POST['faccount_type']??0;
			$fgroups_id = $POST['fgroups_id']??0;
			$fledger_id = $POST['fledger_id']??0;
			$keyword_search = $POST['keyword_search']??'';
					
			$this->date_range = $date_range;
			$this->voucher_type = $fvoucher_type;
			$this->account_type = $faccount_type;
			$this->groups_id = $fgroups_id;
			$this->ledger_id = $fledger_id;
			$this->keyword_search = $keyword_search;
			
			$tableRows = $this->ledgerReportData();
			
			$groupsOpt = $this->groupsOpt;
			$parLedOpt = $this->parLedOpt;
			$vouTypOpt = $this->vouTypOpt;
			
			$jsonResponse['groupsOpt'] = $groupsOpt;
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
		$fgroups_id = $this->groups_id;
		$fledger_id = $this->ledger_id;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "ledgerReport";
		$_SESSION["list_filters"] = array('date_range'=>$date_range, 'fvoucher_type'=>$fvoucher_type, 'faccount_type'=>$faccount_type, 'fgroups_id'=>$fgroups_id, 'fledger_id'=>$fledger_id, 'keyword_search'=>$keyword_search);
		
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
		if($fgroups_id >0){
			$filterSql .= "l.groups_id = :fgroups_id AND ";
			$bindData['fgroups_id'] = $fgroups_id;
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
		$sql = "SELECT l.groups_id1, l.groups_id, l.groups_id2, vl.ledger_id, v.voucher_id, v.voucher_type, v.voucher_date, v.voucher_no, v.voucher_publish, vl.narration, vl.debit_credit, vl.amount FROM voucher v, voucher_list vl, ledger l WHERE $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id ORDER BY v.voucher_date ASC, vl.voucher_list_id ASC";
		$dataObj = $this->db->querypagination($sql, $bindData);
		if($dataObj){
			$allLedIds = array();
			foreach($dataObj as $oneRow){
				$vouTypeIds[$oneRow['voucher_type']] = $voucherTypes[$oneRow['voucher_type']];
				$subGroIds[$oneRow['groups_id']] = '';
				$ledger_id = $oneRow['ledger_id'];
				if($ledger_id>0){$LedIds[$ledger_id] = '';}
				$groups_id1 = $oneRow['groups_id1'];
				$groups_id2 = $oneRow['groups_id2'];
				if($groups_id1==0){
					$groups_id1 = $ledger_id;
					$groups_id2 = 0;
					$ledger_id = 0;
				}
				if($groups_id2==0){
					$groups_id2 = $ledger_id;
					$ledger_id = 0;
				}
				
				$voucher_id = $oneRow['voucher_id'];
				$debit_credit = $oneRow['debit_credit'];
				$amount = $oneRow['amount'];
				
				if(array_key_exists($groups_id1, $allLedIds)){
					if(array_key_exists($groups_id2, $allLedIds[$groups_id1])){
						if(array_key_exists($ledger_id, $allLedIds[$groups_id1][$groups_id2])){
							if(array_key_exists($voucher_id, $allLedIds[$groups_id1][$groups_id2][$ledger_id])){
								if(array_key_exists($debit_credit, $allLedIds[$groups_id1][$groups_id2][$ledger_id][$voucher_id])){
									$amount += $allLedIds[$groups_id1][$groups_id2][$ledger_id][$voucher_id][$debit_credit];
								}
							}
						}
					}
				}
				$allLedIds[$groups_id1][$groups_id2][$ledger_id][$voucher_id][$debit_credit] = $amount;
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
		$groupsOpt = "<option value=\"0\">Select Sub-Group Name</option>";
		if(!empty($subGroIds)){
			$tableObj = $this->db->query("SELECT groups_id, name FROM groups WHERE groups_id IN (".implode(', ', array_keys($subGroIds)).") AND groups_publish = 1 ORDER BY name ASC", array());
			if($tableObj){
				while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
					$groupsOpt .= "<option value=\"$oneRow->groups_id\">".stripslashes(trim($oneRow->name))."</option>";
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
		$this->groupsOpt = $groupsOpt;
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
		<div class=\"flexSpaBetRow\">
			<div class=\"col-sm-4 col-md-7 pright0\">
				<button class=\"btn cursor p2x10 marginright15 printTrialBalance\" title=\"Print $this->pageTitle\">
					<i class=\"fa fa-print\"></i> Print
				</button>
			</div>				
			<div class=\"col-sm-4 col-md-3 pbottom10 pright0\">
				<div class=\"input-group\">
					<span class=\"input-group-addon cursor\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Views Type\">
						Views Type
					</span>
					<select name=\"fviews_type\" id=\"fviews_type\" class=\"form-control changeLoadFilterData\">
						$vieTypOpt
					</select>
				</div>
			</div>
			<div class=\"col-sm-4 col-md-2 pbottom10\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Trial Balance Date\" value=\"".date('Y-m-d')."\" id=\"fdate\" name=\"fdate\" class=\"form-control\" maxlength=\"10\" />
					<span class=\"input-group-addon cursor clickLoadFilterData\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Search Trial Balance\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"flexSpaBetRow\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
				<div id=\"no-more-tables\">
					<table class=\"table-bordered table-striped table-condensed cf listing\">
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
			$POST = json_decode(file_get_contents('php://input'), true);
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			
			$fdate = $POST['fdate']??date('Y-m-d');
			$fviews_type = $POST['fviews_type']??2;
					
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
		
		$tableObj = $this->db->query("SELECT account_type, groups_id, ledger_id, name, debit, opening_balance FROM ledger WHERE $lFilter ledger_publish = 1 ORDER BY name ASC", array());
		if($tableObj){
			while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
				$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
				if($oneRow->opening_balance !=0){
					$account_type = $oneRow->account_type;
					$groups_id = $oneRow->groups_id;
					$subGroIds[$groups_id] = '';
					$ledger_id = $oneRow->ledger_id;
					$debit_credit = $oneRow->debit;
					$debit_credit = 1;
					$amount = $oneRow->opening_balance*$debit_credit;
					$debCrAcc = 'D';
					if(in_array($account_type, array(2,3,4))){$debCrAcc = 'C';}
					
					if(array_key_exists($account_type, $ledData)){
						if(array_key_exists($groups_id, $ledData[$account_type])){
							if(array_key_exists($debCrAcc, $ledData[$account_type][$groups_id])){
								if(array_key_exists($ledger_id, $ledData[$account_type][$groups_id][$debCrAcc])){
									$amount += $ledData[$account_type][$groups_id][$debCrAcc][$ledger_id];
								}
							}
						}
					}
					$ledData[$account_type][$groups_id][$debCrAcc][$ledger_id] = $amount;
				}
			}
		}
		
		$sql = "SELECT l.account_type, l.groups_id, l.ledger_id, vl.debit_credit, vl.amount FROM voucher v, voucher_list vl, ledger l WHERE $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id ORDER BY v.voucher_date ASC, vl.voucher_list_id ASC";
		$dataObj = $this->db->query($sql, $bindData);
		if($dataObj){
			$accountTypes = $this->accountTypes();
			$debitCredits = $this->debitCredits();
			
			while($oneRow=$dataObj->fetch(PDO::FETCH_OBJ)){
				$account_type = $oneRow->account_type;
				$groups_id = $oneRow->groups_id;
				$subGroIds[$groups_id] = '';
				$ledger_id = $oneRow->ledger_id;
				$debit_credit = $oneRow->debit_credit;
				$amount = $oneRow->amount*$debit_credit;
				$debCrAcc = 'D';
				if(in_array($account_type, array(2,3,4))){$debCrAcc = 'C';}
				
				if(array_key_exists($account_type, $ledData)){
					if(array_key_exists($groups_id, $ledData[$account_type])){
						if(array_key_exists($debCrAcc, $ledData[$account_type][$groups_id])){
							if(array_key_exists($ledger_id, $ledData[$account_type][$groups_id][$debCrAcc])){
								$amount += $ledData[$account_type][$groups_id][$debCrAcc][$ledger_id];
							}
						}
					}
				}
				$ledData[$account_type][$groups_id][$debCrAcc][$ledger_id] = $amount;
			}
		
		}
		
		$tableRows = '';
		if(!empty($ledData)){
			if(!empty($subGroIds)){
				$tableObj = $this->db->query("SELECT groups_id, name FROM groups WHERE groups_id IN (".implode(', ', array_keys($subGroIds)).") AND groups_publish = 1 ORDER BY name ASC", array());
				if($tableObj){
					while($oneRow=$tableObj->fetch(PDO::FETCH_OBJ)){
						$subGroIds[$oneRow->groups_id] = stripslashes(trim($oneRow->name));
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
				foreach($ATInfo as $groups_id=>$SGInfo){
					$SGName = $subGroIds[$groups_id];
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
	
	private function getAllSonLedgerIds($ledId){
		$ledgerIds = array();
		if($ledId>0){
			$tableObj = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id1 = $ledId ORDER BY ledger_id ASC", array());
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
			$tableObj = $this->db->querypagination("SELECT groups_id1 FROM ledger WHERE ledger_id = $parId", array());
			if(!empty($tableObj)){
				foreach ($tableObj[0] as $key=>$value) {
					$parId1 = stripslashes($value);
				}
			}
			
			if($parId1>0){
				$arrow++;
				$parId2 = 0;
				$tableObj = $this->db->querypagination("SELECT groups_id1 FROM ledger WHERE ledger_id = $parId1", array());
				if(!empty($tableObj)){
					foreach ($tableObj[0] as $key=>$value) {
						$parId2 = stripslashes($value);
					}
				}
				
				if($parId2>0){
					$arrow++;
					$parId3 = 0;
					$tableObj = $this->db->querypagination("SELECT groups_id1 FROM ledger WHERE ledger_id = $parId2", array());
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
	
	private function ledOneRowStr($oneRow, $accountType, $groupsName, $debitCredits, $voucherTypes, $a, $col=1){
		$tableRows = "";
		if(!empty($oneRow)){
			$ledger_id = $oneRow->ledger_id;
			$name = stripslashes(trim($oneRow->name));
			
			$arrow = $this->getArrows($ledger_id, $oneRow->groups_id1);
											
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
			$debit = $debitCredits[intval($oneRow->debit)];
			$credit = $debitCredits[intval($oneRow->credit)];
			$cls = '';
			if($oneRow->ledger_publish==2){$cls = ' class="lightyellowrow"';}
			$editIcon = "<a href=\"/Accounts/ledgerView/$ledger_id\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle txt18\"></i></a>";
			$editIcon .= " <a href=\"javascript:void(0);\" class=\"AJgetLedgerPopup\" data-spid=\"0\" data-id=\"$oneRow->ledger_id\" title=\"Change this Account Information\"><i class=\"fa fa-edit txt18\"></i></a>";
			$editIcon .= " <a href=\"javascript:void(0);\" class=\"AJarchive_Popup\" data-table=\"ledger\" data-id=\"$ledger_id\" data-description=\"".strip_tags($oneRow->name)."\" data-publish=\"$oneRow->ledger_publish\" title=\"";
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
		<td data-title=\"Sub-Group Name\" align=\"left\">$groupsName</td>";
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
		$returnData = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$user_id = $_SESSION["user_id"]??0;
		
		if($prod_cat_man==0){
			$returnData = 'login';
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$tableName = $POST['tableName']??'';
			$idValue = $POST['idValue']??0;
			$description = $POST['description']??'';
			$activeInActive = $POST['activeInActive']??1;
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
				$returnData = $message;
			}
			else{
				$returnData = 'Error occured while archive/active data!';
			}
		}
		return json_encode(array('login'=>'', 'returnData'=>$returnData));
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
			
			$tableObj = $this->Common_model->getallrowbysqlquery("SELECT account_type, groups_id, ledger_id, name, debit, opening_balance FROM ledger WHERE $lFilter ledger_publish = 1 ORDER BY name ASC");
			if($tableObj){
				foreach($tableObj as $oneRow){
					$LedIds[$oneRow->ledger_id] = stripslashes(trim($oneRow->name));
					if($oneRow->opening_balance !=0){
						$account_type = $oneRow->account_type;
						$groups_id = $oneRow->groups_id;
						$subGroIds[$groups_id] = '';
						$ledger_id = $oneRow->ledger_id;
						$debit_credit = $oneRow->debit;
						$debit_credit = 1;
						$amount = $oneRow->opening_balance*$debit_credit;
						$debCrAcc = 'D';
						if(in_array($account_type, array(2,3,4))){$debCrAcc = 'C';}
						
						if(array_key_exists($account_type, $ledData)){
							if(array_key_exists($groups_id, $ledData[$account_type])){
								if(array_key_exists($debCrAcc, $ledData[$account_type][$groups_id])){
									if(array_key_exists($ledger_id, $ledData[$account_type][$groups_id][$debCrAcc])){
										$amount += $ledData[$account_type][$groups_id][$debCrAcc][$ledger_id];
									}
								}
							}
						}
						$ledData[$account_type][$groups_id][$debCrAcc][$ledger_id] = $amount;
					}
				}
			}
			
			$sql = "SELECT l.account_type, l.groups_id, l.ledger_id, vl.debit_credit, vl.amount FROM voucher v, voucher_list vl, ledger l WHERE $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id ORDER BY v.voucher_date ASC, vl.voucher_list_id ASC";
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
					$groups_id = $oneRow->groups_id;
					$subGroIds[$groups_id] = '';
					$ledger_id = $oneRow->ledger_id;
					$debit_credit = $oneRow->debit_credit;
					$amount = $oneRow->amount*$debit_credit;
					$debCrAcc = 'D';
					if(in_array($account_type, array(2,3,4))){$debCrAcc = 'C';}
					
					if(array_key_exists($account_type, $ledData)){
						if(array_key_exists($groups_id, $ledData[$account_type])){
							if(array_key_exists($debCrAcc, $ledData[$account_type][$groups_id])){
								if(array_key_exists($ledger_id, $ledData[$account_type][$groups_id][$debCrAcc])){
									$amount += $ledData[$account_type][$groups_id][$debCrAcc][$ledger_id];
								}
							}
						}
					}
					$ledData[$account_type][$groups_id][$debCrAcc][$ledger_id] = $amount;
				}
			
			}
			
			$tableRows = '';
			if(!empty($ledData)){
				if(!empty($subGroIds)){
					$tableObj = $this->Common_model->getallrowbysqlquery("SELECT groups_id, name FROM groups WHERE groups_id IN (".implode(', ', array_keys($subGroIds)).") AND groups_publish = 1 ORDER BY name ASC");
					if($tableObj){
						foreach($tableObj as $oneRow){
							$subGroIds[$oneRow->groups_id] = stripslashes(trim($oneRow->name));
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
					foreach($ATInfo as $groups_id=>$SGInfo){
						$SGName = $subGroIds[$groups_id];
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
		
		$innerHTMLStr = "
		<input type=\"hidden\" name=\"pageURI\" id=\"pageURI\" value=\"$GLOBALS[segment2name]/$GLOBALS[segment3name]\">
		<div class=\"flexSpaBetRow\">
			<div class=\"col-sm-4 col-md-7 pright0\">
				<button class=\"btn cursor p2x10 marginright15 printreceiptPayment\" title=\"Print $this->pageTitle\">
					<i class=\"fa fa-print\"></i> Print
				</button>
			</div>				
			<div class=\"col-sm-4 col-md-3 pbottom10 pright0\">
				<div class=\"input-group\">
					<span class=\"input-group-addon cursor\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Views Type\">
						Views Type
					</span>
					<select name=\"fviews_type\" id=\"fviews_type\" class=\"form-control changeLoadRecPayData\">
						$vieTypOpt
					</select>
				</div>
			</div>
			<div class=\"col-sm-4 col-md-2 pbottom10\">
				<div class=\"input-group\">
					<input type=\"text\" placeholder=\"Date Range\" value=\"$date_range\" id=\"date_range\" name=\"date_range\" class=\"form-control pressLoadRecPayData\" maxlength=\"23\" />
					<span class=\"input-group-addon cursor clickLoadRecPayData\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Search Trial Balance\">
						<i class=\"fa fa-search\"></i>
					</span>
				</div>
			</div>
		</div>
		<div class=\"flexSpaBetRow\">
			<div class=\"col-sm-12\" style=\"position:relative;\">
                <div id=\"no-more-tables\">
                    <table class=\"col-md-12\">
                        <tr>
                            <td width=\"49%\" valign=\"top\">
                                <table class=\"table-bordered table-striped table-condensed cf listing\">
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
                                <table class=\"table-bordered table-striped table-condensed cf listing\">
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
			$POST = json_decode(file_get_contents('php://input'), true);
			$jsonResponse = array();
			$jsonResponse['login'] = '';
			
			$date_range = $POST['date_range']??'';
			$fviews_type = $POST['fviews_type']??2;
					
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
		$parLedOpts = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id1 IN (".implode(', ', $cashParentIds).") $cashLedIdNotCond AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
		if($parLedOpts){
			while($oneRow = $parLedOpts->fetch(PDO::FETCH_OBJ)){
				$cashLedgerIds[$oneRow->ledger_id] = 0;

				$parLedOpts2 = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id2 = $oneRow->ledger_id AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
				if($parLedOpts2){
					while($oneRow2 = $parLedOpts2->fetch(PDO::FETCH_OBJ)){
						$cashLedgerIds[$oneRow2->ledger_id] = 0;

						$parLedOpts3 = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id3 = $oneRow2->ledger_id AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
						if($parLedOpts3){
							while($oneRow3 = $parLedOpts3->fetch(PDO::FETCH_OBJ)){
								$cashLedgerIds[$oneRow3->ledger_id] = 0;								
							}
						}
					}
				}
			}			
		}

		$sonSql = "SELECT ledger_id, groups_id1, groups_id2, groups_id3 FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($cashLedgerIds)).") AND ledger_publish=1 ORDER BY groups_id1 ASC, groups_id2 ASC, name ASC";
		$sonDataObj = $this->db->query($sonSql, array());		
		if($sonDataObj){
			while($sonOneRow = $sonDataObj->fetch(PDO::FETCH_OBJ)){
				$sgroups_id1 = $sonOneRow->groups_id1;
				$sgroups_id2 = $sonOneRow->groups_id2;
				$sgroups_id3 = $sonOneRow->groups_id3;
				$sledger_id = $sonOneRow->ledger_id;				
				if($sgroups_id1==0){
					$sgroups_id1 = $sledger_id;
					$sgroups_id2 = $sgroups_id3 = $sledger_id = 0;
				}
				else if($sgroups_id2==0){
					$sgroups_id2 = $sledger_id;
					$sgroups_id3 = $sledger_id = 0;
				}
				else if($sgroups_id3==0){
					$sgroups_id3 = $sledger_id;
					$sledger_id = 0;
				}
				$ledIdInfos[$sgroups_id1] = '';
				$ledIdInfos[$sgroups_id2] = '';
				$ledIdInfos[$sgroups_id3] = '';
				$ledIdInfos[$sledger_id] = '';

				$cashParSonLedIds[$sgroups_id1][$sgroups_id2][$sgroups_id3][$sledger_id] = '';
			}
		}
		
		//-------------Bank Ledger Ids----------//
		foreach($bankParentIds as $oneLedId){
			$bankLedgerIds[$oneLedId] = 0;
		}
		$parLedOpts = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id1 IN (".implode(', ', $bankParentIds).") AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
		if($parLedOpts){
			while($oneRow = $parLedOpts->fetch(PDO::FETCH_OBJ)){
				$bankLedgerIds[$oneRow->ledger_id] = 0;

				$parLedOpts2 = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id2 = $oneRow->ledger_id AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
				if($parLedOpts2){
					while($oneRow2 = $parLedOpts2->fetch(PDO::FETCH_OBJ)){
						$bankLedgerIds[$oneRow2->ledger_id] = 0;

						$parLedOpts3 = $this->db->query("SELECT ledger_id FROM ledger WHERE groups_id3 = $oneRow2->ledger_id AND accounts_id = $prod_cat_man AND ledger_publish = 1 ORDER BY name ASC", array());
						if($parLedOpts3){
							while($oneRow3 = $parLedOpts3->fetch(PDO::FETCH_OBJ)){
								$bankLedgerIds[$oneRow3->ledger_id] = 0;								
							}
						}
					}
				}						
			}			
		}

		$sonSql = "SELECT ledger_id, groups_id1, groups_id2, groups_id3 FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($bankLedgerIds)).") AND ledger_publish=1 ORDER BY groups_id1 ASC, groups_id2 ASC, name ASC";
		$sonDataObj = $this->db->query($sonSql, array());		
		if($sonDataObj){
			while($sonOneRow = $sonDataObj->fetch(PDO::FETCH_OBJ)){
				$sgroups_id1 = $sonOneRow->groups_id1;
				$sgroups_id2 = $sonOneRow->groups_id2;
				$sgroups_id3 = $sonOneRow->groups_id3;
				$sledger_id = $sonOneRow->ledger_id;				
				if($sgroups_id1==0){
					$sgroups_id1 = $sledger_id;
					$sgroups_id2 = $sgroups_id3 = $sledger_id = 0;
				}
				else if($sgroups_id2==0){
					$sgroups_id2 = $sledger_id;
					$sgroups_id3 = $sledger_id = 0;
				}
				else if($sgroups_id3==0){
					$sgroups_id3 = $sledger_id;
					$sledger_id = 0;
				}
				$ledIdInfos[$sgroups_id1] = '';
				$ledIdInfos[$sgroups_id2] = '';
				$ledIdInfos[$sgroups_id3] = '';
				$ledIdInfos[$sledger_id] = '';

				$bankParSonLedIds[$sgroups_id1][$sgroups_id2][$sgroups_id3][$sledger_id] = '';
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
			$sql = "SELECT ledger_id, name, opening_balance FROM ledger WHERE ledger_id IN (".implode(', ', array_keys($ledIdInfos)).") AND ledger_publish=1 ORDER BY name ASC";
            $tableObj = $this->db->query($sql, array());
            if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
                    $ledIdInfos[$oneRow->ledger_id] = array($oneRow->name, $oneRow->opening_balance);
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
		$ledIds = $groupsIds = $receiptsData = $paymentsData = array();
		$sql = "SELECT l.groups_id, l.groups_id1, l.groups_id2, l.groups_id3, vl.ledger_id, vl.amount, vl.debit_credit FROM voucher v, voucher_list vl, ledger l WHERE v.voucher_type = 1 AND v.voucher_publish=2 AND $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$debit_credit = $oneRow->debit_credit;
				if($debit_credit==-1){
					$ledgerTotal = $oneRow->amount;
					$groups_id = $oneRow->groups_id;
					$groups_id1 = $oneRow->groups_id1;
					$groups_id2 = $oneRow->groups_id2;
					$groups_id3 = $oneRow->groups_id3;
					$ledger_id = $oneRow->ledger_id;
				
					if($groups_id1==0){
						$groups_id1 = $ledger_id;
						$groups_id2 = $groups_id3 = $ledger_id = 0;
					}
					elseif($groups_id2==0){
						$groups_id2 = $ledger_id;
						$groups_id3 = $ledger_id = 0;
					}
					elseif($groups_id3==0){
						$groups_id3 = $ledger_id;
						$ledger_id = 0;
					}

					$groupsIds[$groups_id] = '';
					$ledIds[$groups_id1] = '';
					$ledIds[$groups_id2] = '';
					$ledIds[$groups_id3] = '';
					$ledIds[$ledger_id] = '';

					if(array_key_exists($groups_id, $receiptsData)){
						if(array_key_exists($groups_id1, $receiptsData[$groups_id])){
							if(array_key_exists($groups_id2, $receiptsData[$groups_id][$groups_id1])){
								if(array_key_exists($groups_id3, $receiptsData[$groups_id][$groups_id1][$groups_id2])){
									if(array_key_exists($ledger_id, $receiptsData[$groups_id][$groups_id1][$groups_id2][$groups_id3])){
										$ledgerTotal += $receiptsData[$groups_id][$groups_id1][$groups_id2][$groups_id3][$ledger_id];
									}
								}
							}
						}
					}

					$receiptsData[$groups_id][$groups_id1][$groups_id2][$groups_id3][$ledger_id] = $ledgerTotal;
				}
			}
		}

		//-----------------Payment----------------//
		$sql = "SELECT l.groups_id, l.groups_id1, l.groups_id2, l.groups_id3, l.ledger_id, vl.amount, vl.debit_credit FROM voucher v, voucher_list vl, ledger l WHERE v.voucher_type = 2 AND v.voucher_publish=2 AND $filterSql v.voucher_id = vl.voucher_id AND vl.ledger_id = l.ledger_id";
		$tableObj = $this->db->query($sql, array());
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$ledgerTotal = $oneRow->amount;
				$groups_id = $oneRow->groups_id;
				$groups_id1 = $oneRow->groups_id1;
				$groups_id2 = $oneRow->groups_id2;
				$groups_id3 = $oneRow->groups_id3;
				$ledger_id = $oneRow->ledger_id;
				$debit_credit = $oneRow->debit_credit;
				
				if($debit_credit==1){
					if($groups_id1==0){
						$groups_id1 = $ledger_id;
						$groups_id2 = $groups_id3 = $ledger_id = 0;
					}
					elseif($groups_id2==0){
						$groups_id2 = $ledger_id;
						$groups_id3 = $ledger_id = 0;
					}
					elseif($groups_id3==0){
						$groups_id3 = $ledger_id;
						$ledger_id = 0;
					}

					$groupsIds[$groups_id] = '';
					$ledIds[$groups_id1] = '';
					$ledIds[$groups_id2] = '';
					$ledIds[$groups_id3] = '';
					$ledIds[$ledger_id] = '';

					if(array_key_exists($groups_id, $paymentsData)){
						if(array_key_exists($groups_id1, $paymentsData[$groups_id])){
							if(array_key_exists($groups_id2, $paymentsData[$groups_id][$groups_id1])){
								if(array_key_exists($groups_id3, $paymentsData[$groups_id][$groups_id1][$groups_id2])){
									if(array_key_exists($ledger_id, $paymentsData[$groups_id][$groups_id1][$groups_id2][$groups_id3])){
										$ledgerTotal += $paymentsData[$groups_id][$groups_id1][$groups_id2][$groups_id3][$ledger_id];
									}
								}
							}
						}
					}

					$paymentsData[$groups_id][$groups_id1][$groups_id2][$groups_id3][$ledger_id] = $ledgerTotal;
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
		if(!empty($groupsIds)){
			$sql = "SELECT groups_id, name FROM groups WHERE groups_id IN (".implode(', ', array_keys($groupsIds)).") AND groups_publish = 1 ORDER BY name ASC";
            $tableObj = $this->db->query($sql, array());
            if($tableObj){
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
                    $groupsIds[$oneRow->groups_id] = stripslashes(trim($oneRow->name));
                }
            }
		}

		//========================Receipt View=======================//
		$receiptsTotal = 0;
		if(!empty($receiptsData)){
			foreach($receiptsData as $groups_id=>$groupsInfo){
				$groupsName = $groupsIds[$groups_id]??'';
				$groupsTotal = 0;
				foreach($groupsInfo as $groups_id1=>$parentLedgerInfo){
					$parentLedgerName = $ledIds[$groups_id1]??'';
					$parentLedgerTotal = 0;
					foreach($parentLedgerInfo as $groups_id2=>$subParentLedgerInfo){
						$subParentLedgerName = $ledIds[$groups_id2]??'';
						$subParentLedgerTotal = 0;
						$parentExists = 0;
						foreach($subParentLedgerInfo as $groups_id3=>$sub2ParentLedgerInfo){
							$sub2ParentLedgerName = $ledIds[$groups_id3]??'';
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
								$sub2ParentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$groups_id3/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
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
							$subParentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$groups_id2/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
							$clsbold = '';
							if($fviews_type>=3 && $parentExists>0){$clsbold = ' class="txtbold"';}
							$receiptsRows .= "<tr>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"$clsbold>&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$subParentLedgerName</td>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"$clsbold>".$this->taka_format($subParentLedgerTotal,2)."</td>";
							$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
							$receiptsRows .= "</tr>";
						}
					}

					$groupsTotal += $parentLedgerTotal;
					if($fviews_type>=2 && $parentLedgerName !=''){
						$parentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$groups_id1/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
						$clsbold = '';
						if($fviews_type>=3){$clsbold = ' class="txtbold"';}
						$receiptsRows .= "<tr>";
						$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"$clsbold>&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$parentLedgerName</td>";
						$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"$clsbold>".$this->taka_format($parentLedgerTotal,2)."</td>";
						$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
						$receiptsRows .= "</tr>";
					}
				}
				$receiptsTotal += $groupsTotal;
				$receiptsRows .= "<tr class=\"lightashrow0\">";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><strong>$groupsName</strong></td>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
				$receiptsRows .= "<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($groupsTotal,2)."</strong></td>";
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
			foreach($paymentsData as $groups_id=>$groupsInfo){
				$groupsName = $groupsIds[$groups_id]??'';
				$groupsTotal = 0;
				foreach($groupsInfo as $groups_id1=>$parentLedgerInfo){
					$parentLedgerName = $ledIds[$groups_id1]??'';
					$parentLedgerTotal = 0;
					foreach($parentLedgerInfo as $groups_id2=>$subParentLedgerInfo){
						$subParentLedgerName = $ledIds[$groups_id2]??'';
						$subParentLedgerTotal = 0;
						$parentExists = 0;
						foreach($subParentLedgerInfo as $groups_id3=>$sub2ParentLedgerInfo){
							$sub2ParentLedgerName = $ledIds[$groups_id3]??'';
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
							
							if($fviews_type>=3 && $groups_id3>0){
								$sub2ParentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$groups_id3/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";	
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
						if($fviews_type>=3 && $groups_id2>0){
							$subParentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$groups_id2/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
							$clsbold = '';
							if($fviews_type>=3 && $parentExists>0){$clsbold = ' class="txtbold PearlBG"';}
							$paymentsRows .= "<tr$clsbold>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp;&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$subParentLedgerName</td>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($subParentLedgerTotal,2)."</td>";
							$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
							$paymentsRows .= "</tr>";
						}
					}

					$groupsTotal = round($groupsTotal+$parentLedgerTotal,5);
					if($fviews_type>=2 && $groups_id1>0){
						$parentLedgerName .= "&emsp; <a target=\"_blank\" href=\"/Accounts/ledgerView/$groups_id1/1/$date_range\" title=\"View this Ledger Information\"><i class=\"fa fa fa-info-circle\"></i></a>";
						$clsbold = '';
						if($fviews_type>=3){$clsbold = ' class="txtbold lightashrow1"';}
						$paymentsRows .= "<tr$clsbold>";
						$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\">&emsp;<img src=\"/assets/images/Accounts/sontabarrow.png\" alt=\"Sub-Group\" class=\"mtop-6\">&nbsp;$parentLedgerName</td>";
						$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">".$this->taka_format($parentLedgerTotal,2)."</td>";
						$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
						$paymentsRows .= "</tr>";
					}
				}
				$paymentsTotal += $groupsTotal;
				$paymentsRows .= "<tr class=\"lightbluerow\">";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"left\"><strong>$groupsName</strong></td>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\">&nbsp;</td>";
				$paymentsRows .= "<td data-title=\"Opening Balance\" align=\"right\"><strong>".$this->taka_format($groupsTotal)."</strong></td>";
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
		$returnData = '';
		if($prod_cat_man==0){
			return json_encode(array('login'=>'session_ended'));
		}
		else{
			$POST = json_decode(file_get_contents('php://input'), true);
			$tableName = $POST['tableName']??'';
			$idValue = $POST['idValue']??0;
			$description = $POST['description']??'';
			$fieldName = $POST['fieldName']??'';
			$updateValue = $POST['updateValue']??'';
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
				$returnData = 'Updated successfully';
			}
			else{
				$returnData = 'Error occured while updating data!';
			}
		}
		return json_encode(array('login'=>'', 'returnData'=>$returnData));
	}
	
	function AJgetLedgerBalance(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$user_id = $_SESSION["user_id"]??0;
		$ledger_id = $POST['ledger_id']??0;
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
		$parId1 = $this->getOneFieldById('ledger', $ledId, 'groups_id1');
		if($parId1>0){
			$parId2 = $this->getOneFieldById('ledger', $parId1, 'groups_id1');
			if($parId2>0){
				$parId3 = $this->getOneFieldById('ledger', $parId2, 'groups_id1');
				if($parId3>0){
					$parId4 = $this->getOneFieldById('ledger', $parId3, 'groups_id1');
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
	
	function voucherTypesSegments($withDesc=0){
		$data = array(1=>'receiptVoucher', 2=>'paymentVoucher', 3=>'journalVoucher', 4=>'contraVoucher', 5=>'purchaseVoucher', 6=>'salesVoucher');
		if($withDesc>0 && array_key_exists($withDesc, $data)){return $data[$withDesc];}
		else{return $data;}
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

		$returnHTML = "";
		
		if($segment3name !='ledger'){
		   $returnHTML .= "
			<link rel=\"stylesheet\" href=\"/assets/css-".swVersion."/adminStyles.css\">
			<div class=\"flexStartRow\">
			 	<h2 style=\"padding: 5px; text-align: start;\">
					$this->pageTitle <i class=\"fa fa-info-circle\" style=\"font-size: 16px;\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"\" data-original-title=\"This page captures the accounts settings\" data-tooltip-active=\"true\"></i>
				</h2>
			</div>
			<div class=\"flexSpaBetRow\">
				<div class=\"columnMD2 columnSM3\" style=\"margin: 0;\">
					<div style=\"padding-top: 0;\" class=\"innerContainer\">
						<a href=\"javascript:void(0);\" id=\"secondarySideMenu\">
							<i class=\"fa fa-align-justify\" style=\"margin-bottom: 10px; font-size: 2em;\"></i>
						</a>
						<ul class=\"secondaryNavMenu settingslefthide\">";
							foreach($gettingStartedModules as $module=>$moduletitle){
								$linkstr = "<a href=\"/Accounts/$module\" title=\"$moduletitle\"><span>$moduletitle</span></a>";
								$activeclass = '';
								if(strcmp($GLOBALS['segment3name'],$module)==0){
									$linkstr = "<h4 style=\"font-size: 18px;\">$moduletitle</h4>";
									$activeclass = ' class="activeclass"';
								}
								$returnHTML .= "<li$activeclass>$linkstr</li>";
							}
						$returnHTML .= "</ul>
					</div>
				</div>
				<div class=\"columnMD10 columnSM9\" style=\"margin: 0;\">";
		}
			
		$returnHTML .= "<div class=\"innerContainer\" style=\"background: #fff;\">
			$pageMiddle
		</div>";

		if($segment3name !='ledger'){
			$returnHTML .= "</div>
			</div>";
		}
		
		$returnHTML .= "<script type=\"module\">
			import {triggerEvent, addCustomeEventListener} from '/assets/js-".swVersion."/".commonJS."';
			import {filter_Accounts_".$segment3name.", loadTableRows_Accounts_".$segment3name."} from '/assets/js-".swVersion."/Accounts.js';
			addCustomeEventListener('filter', filter_Accounts_".$segment3name.");
			addCustomeEventListener('loadTableRows', loadTableRows_Accounts_".$segment3name.");
		</script>";
		return $returnHTML;
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
	
	function taka_format($amount = 0, $floatPoints=2, $currency = ''){
		$amount = floatval($amount);
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