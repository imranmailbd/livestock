<?php
class Commissions{
	protected $db;
	private int $page, $totalRows, $commissions_id;
	private string $rule_field, $sorting_type, $keyword_search, $history_type;
	private array $ruleFieOpt;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
	public function view(){}
	
    private function filterAndOptions(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$srule_field = $this->rule_field;
		$sorting_type = $this->sorting_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Commissions";
		$_SESSION["list_filters"] = array('srule_field'=>$srule_field, 'sorting_type'=>$sorting_type, 'keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		if($srule_field !='All'){
			$filterSql .= " AND rule_field = :rule_field";
			$bindData['rule_field'] = $srule_field;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', start_date, rule_match, rule_field, end_date)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}

		$totalRows = 0;
		$comSql = "SELECT COUNT(commissions_id) AS totalrows FROM commissions WHERE accounts_id = $accounts_id $filterSql AND commissions_publish = 1";
							
		$queryObj = $this->db->query($comSql, $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;						
		}

		$ruleFieOpt = array();
		$tableObj = $this->db->query("SELECT rule_field FROM commissions WHERE accounts_id = $accounts_id $filterSql AND commissions_publish = 1 GROUP BY rule_field", $bindData);
		if($tableObj){
			$ruleFieOpts = array();
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$ruleFieOpts[$oneRow->rule_field] = '';
			}
			ksort($ruleFieOpts);
			$ruleFieOpt = array_keys($ruleFieOpts);
		}
		
		$this->totalRows = $totalRows;
		$this->ruleFieOpt = $ruleFieOpt;
	}
	
    private function loadTableRows(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$limit = $_SESSION["limit"];
		
		$page = $this->page;
		$totalRows = $this->totalRows;
		$srule_field = $this->rule_field;
		$ssorting_type = $this->sorting_type;
		$keyword_search = $this->keyword_search;

		$sortingTypeData = array(0=>'start_date DESC', 
								1=>'start_date ASC', 
								2=>'rule_field ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}

		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$filterSql = "FROM commissions WHERE accounts_id = $accounts_id AND commissions_publish = 1";
		$bindData = array();
		if($srule_field !='All'){
			$filterSql .= " AND rule_field = :rule_field";
			$bindData['rule_field'] = $srule_field;
		}

		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', start_date, rule_match, rule_field, end_date)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}		
            
		$sqlquery = "SELECT * $filterSql";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);
		
		$tabledata = array();
		if($query){
			$categoryIds = $manufacturerIds = $skus = $salesmanIds = array();
			foreach($query as $oneRow){
				$rule_field = stripslashes($oneRow['rule_field']);
				$rule_match = stripslashes($oneRow['rule_match']);
				if(strcmp('Category', $rule_field)==0){$categoryIds[$rule_match] = '';}
				elseif(strcmp('Manufacturer', $rule_field)==0){$manufacturerIds[intval($rule_match)] = '';}
				elseif(strcmp('Product SKU', $rule_field)==0){$skus[$rule_match] = '';}
				$salesmanIds[$oneRow['salesman']] = '';
			}
			if(!empty($categoryIds)){
				$catObj = $this->db->query("SELECT category_id, category_name FROM category WHERE category_id IN (".implode(', ', array_keys($categoryIds)).") AND accounts_id = $prod_cat_man", array());
				if($catObj){
					while($catOneRow = $catObj->fetch(PDO::FETCH_OBJ)){
						$categoryIds[$catOneRow->category_id] = stripslashes(trim((string) $catOneRow->category_name));
					}
				}
			}
			if(!empty($manufacturerIds)){
				$catObj = $this->db->query("SELECT manufacturer_id, name FROM manufacturer WHERE manufacturer_id IN (".implode(', ', array_keys($manufacturerIds)).") AND accounts_id = $prod_cat_man", array());
				if($catObj){
					while($catOneRow = $catObj->fetch(PDO::FETCH_OBJ)){
						$manufacturerIds[$catOneRow->manufacturer_id] = stripslashes(trim((string) $catOneRow->name));
					}
				}
			}
			
			if(!empty($skus)){
				$sqlProd = "SELECT p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.sku IN ('".implode("', '", array_keys($skus))."')";
				$prodObj = $this->db->query($sqlProd, array());
				if($prodObj){
					while($prodOneRow = $prodObj->fetch(PDO::FETCH_OBJ)){
						$skus[$prodOneRow->sku] = stripslashes(trim("$prodOneRow->manufacture $prodOneRow->product_name $prodOneRow->colour_name $prodOneRow->storage $prodOneRow->physical_condition_name"));
					}
				}
			}
			
			if(!empty($salesmanIds)){
				$salesmanObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id IN (".implode(', ', array_keys($salesmanIds)).") AND user_publish = 1", array(), 1);
				if($salesmanObj){
					while($salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ)){
						$name = stripslashes(trim("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
						$salesmanIds[$salesmanRow->user_id] = $name;
					}
				}
			}
			
			foreach($query as $oneRow){
				$commissions_id = $oneRow['commissions_id'];
				$rule_field = stripslashes($oneRow['rule_field']);
				$rule_match = stripslashes($oneRow['rule_match']);
				if(strcmp('Category', $rule_field)==0){
					$rule_match = $categoryIds[$rule_match];
				}
				elseif(strcmp('Manufacturer', $rule_field)==0){
					$rule_match = $manufacturerIds[$rule_match];
				}
				elseif(strcmp('Product SKU', $rule_field)==0){
					$rule_match = "$rule_match ($skus[$rule_match])";
				}
				
				$amount = round($oneRow['amount'],2);
				$is_percent = $oneRow['is_percent'];
				$salesman = $salesmanIds[$oneRow['salesman']]??'';

				$is_cost = $oneRow['is_cost'];
				
				$tabledata[] = array($commissions_id, $oneRow['start_date'], $oneRow['end_date'], $rule_field, $rule_match, $salesman, $amount, $is_cost, $is_percent);
				
			}
		}
		return $tabledata;
    }
	
	public function AJgetPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$commissions_id = intval($POST['commissions_id']??0);
		
		$salesman = 0;
		$rule_field = $rule_match = '';
		$commissionsData = array();
		$commissionsData['login'] = '';
		$commissionsData['start_date'] = '';
		$commissionsData['end_date'] = '';		
		$commissionsData['is_percent'] = 0;		
		$commissionsData['is_cost'] = 0;
		$commissionsData['amount'] = 0.00;
		
		if($commissions_id>0 && $accounts_id>0){
			$commissionsObj = $this->db->query("SELECT * FROM commissions WHERE commissions_id = :commissions_id AND accounts_id = $accounts_id AND commissions_publish = 1", array('commissions_id'=>$commissions_id),1);
			if($commissionsObj){
				$commissionsRow = $commissionsObj->fetch(PDO::FETCH_OBJ);	

				$rule_field = trim((string) $commissionsRow->rule_field);
				$rule_match = trim((string) $commissionsRow->rule_match);
				$commissionsData['start_date'] = $commissionsRow->start_date;
				$commissionsData['end_date'] = $commissionsRow->end_date;
				$commissionsData['is_percent'] = intval($commissionsRow->is_percent);
				$commissionsData['is_cost'] = intval($commissionsRow->is_cost);
				$commissionsData['amount'] = round($commissionsRow->amount, 2);
				$salesman = trim((string) $commissionsRow->salesman);
			}
		}
		
		$commissionsData['rule_field'] = $rule_field;
		$rule_fieldOptions = array();
		$query = array('Category', 'Manufacturer', 'Product SKU');
		if($query){
			foreach($query as $label){
				$rule_fieldOptions[] = $label;
			}
		}
		$commissionsData['rule_fieldOptions'] = $rule_fieldOptions;
		
		$commissionsData['rule_match'] = $rule_match;
		$rule_matchOptions = array();
		if($rule_field !='' && isset($_SESSION["prod_cat_man"])){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			
			if(strcmp('Category', $rule_field)==0){
				$sql = "SELECT p.category_id, category.category_name FROM product p LEFT JOIN category ON (p.category_id = category.category_id) WHERE p.accounts_id = $prod_cat_man AND p.product_publish = 1 GROUP BY p.category_id ORDER BY category.category_name ASC";
				$catObj = $this->db->query($sql, array());
				if($catObj){
					while($onerow = $catObj->fetch(PDO::FETCH_OBJ)){
						$optval = $onerow->category_id;
						$label = stripslashes(trim((string) $onerow->category_name));
						if($label !=''){
							$rule_matchOptions[$optval] = $label;
						}
					}
				}
			}
			elseif(strcmp('Manufacturer', $rule_field)==0){
				$sql = "SELECT p.manufacturer_id, manufacturer.name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_publish = 1 GROUP BY p.manufacturer_id ORDER BY manufacturer.name ASC";
				$catObj = $this->db->query($sql, array());
				if($catObj){
					while($onerow = $catObj->fetch(PDO::FETCH_OBJ)){
						$optval = $onerow->manufacturer_id;
						$label = stripslashes(trim((string) $onerow->name));
						if($label !=''){
							$rule_matchOptions[$optval] = $label;
						}
					}
				}
			}
			else{
				$sql = "SELECT sku FROM product WHERE accounts_id = $prod_cat_man AND product_publish = 1 GROUP BY sku ORDER BY sku ASC";
				$skuObj = $this->db->query($sql, array());
				if($skuObj){
					while($onerow = $skuObj->fetch(PDO::FETCH_OBJ)){
						$label = $optval = $onerow->sku;
						if($label !=''){
							$rule_matchOptions[$optval] = $label;
						}
					}
				}
			}			
		}
		$commissionsData['rule_matchOptions'] = $rule_matchOptions;
		
		$salesmanOptions = array();
		$salesmanObj = $this->db->query("SELECT user_id, user_first_name, user_last_name, user_email FROM user WHERE accounts_id = $accounts_id AND user_publish = 1", array(), 1);
		if($salesmanObj){
			while($salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ)){
				$name = stripslashes(trim("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
				if($salesmanRow->user_email !=''){$name .= " ($salesmanRow->user_email)";}
				$salesmanOptions[$salesmanRow->user_id] = $name;
			}
		}

		$commissionsData['salesmanOptions'] = $salesmanOptions;
		$commissionsData['salesman'] = trim((string) $salesman);
		
		return json_encode($commissionsData);
	}
	
	public function AJsaveCommissions(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = $returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$commissions_id = intval($POST['commissions_id']??0);
		
		$conditionarray = array();
		$start_date = $end_date = '1000-01-01';
		if(trim((string) $POST['start_date']) !='' &&  !in_array(trim((string) $POST['start_date']), array('0000-00-00', '1000-01-01'))){
			$start_date = date('Y-m-d', strtotime(trim((string) $POST['start_date'])));
		}
		if(trim((string) $POST['end_date']) !='' &&  !in_array(trim((string) $POST['end_date']), array('0000-00-00', '1000-01-01'))){
			$end_date = date('Y-m-d', strtotime(trim((string) $POST['end_date'])));
		}
		$conditionarray['start_date'] = $start_date;
		$conditionarray['end_date'] = $end_date;
		
		$rule_field = trim((string) $POST['rule_field']);
		$rule_field = $this->db->checkCharLen('commissions.rule_field', $rule_field);
		$conditionarray['rule_field'] = $rule_field;
		
		$rule_match = trim((string) $POST['rule_match']);
		$rule_match = $this->db->checkCharLen('commissions.rule_match', $rule_match);
		$conditionarray['rule_match'] = $rule_match;
		
		$conditionarray['is_percent'] = intval($POST['is_percent']??1);
		$conditionarray['is_cost'] = intval($POST['is_cost']??0);
		$conditionarray['amount'] = floatval($POST['amount']??0.00);
		$salesman = intval($POST['salesman']??0);
		$conditionarray['salesman'] = $salesman;
		if($commissions_id==0){
			$countTableData = 0;
			$commissionsObj = $this->db->query("SELECT COUNT(commissions_id) AS totalrows FROM commissions WHERE accounts_id = $accounts_id AND salesman = :salesman AND rule_field = :rule_field AND rule_match = :rule_match AND start_date = :start_date AND end_date = :end_date", array('salesman'=>$salesman,'rule_field'=>$rule_field, 'rule_match'=>$rule_match, 'start_date'=>$start_date, 'end_date'=>$end_date));
			if($commissionsObj){
				$countTableData = $commissionsObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			if($countTableData>0){
				$savemsg = 'error';
				$returnStr .= 'Name_Already_Exist';
			}
			else{
				$conditionarray['accounts_id'] = $accounts_id;
				$conditionarray['user_id'] = $user_id;
				$conditionarray['created_on'] = date('Y-m-d H:i:s');

				$commissions_id = $this->db->insert('commissions', $conditionarray);
				if(!$commissions_id){
					$savemsg = 'error';
					$returnStr .= 'errorOnAdding';
				}
			}
		}
		else{
			$countTableData = 0;
			$commissionsObj = $this->db->query("SELECT COUNT(commissions_id) AS totalrows FROM commissions WHERE accounts_id = $accounts_id AND salesman = :salesman AND rule_field = :rule_field AND rule_match = :rule_match AND start_date = :start_date AND end_date = :end_date AND commissions_id != :commissions_id", array('salesman'=>$salesman,'rule_field'=>$rule_field, 'rule_match'=>$rule_match, 'start_date'=>$start_date, 'end_date'=>$end_date, 'commissions_id'=>$commissions_id));
			if($commissionsObj){
				$countTableData = $commissionsObj->fetch(PDO::FETCH_OBJ)->totalrows;
			}
			if($countTableData>0){
				$savemsg = 'error';
				$returnStr .= 'Name_Already_Exist';
			}
			else{
				$oneTRowObj = $this->db->querypagination("SELECT * FROM commissions WHERE commissions_id = $commissions_id", array());
				
				$update = $this->db->update('commissions', $conditionarray, $commissions_id);
				if($update){
					
					if($oneTRowObj){
						$changed = array();
						$Common = new Common($this->db);
						foreach($conditionarray as $fieldName=>$fieldValue){
							$prevFieldVal = $oneTRowObj[0][$fieldName];
							if($prevFieldVal != $fieldValue){
								if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
								if($fieldValue=='1000-01-01'){$fieldValue = '';}
								if($fieldName=='salesman'){
									$fieldName = 'Salesman';
									if($prevFieldVal==0){$prevFieldVal = '';}
									elseif($prevFieldVal>0){$prevFieldVal = $Common->getOneRowFields('user', array('user_id'=>$prevFieldVal), array('user_first_name', 'user_last_name'));}
									if($fieldValue==0){$fieldValue = '';}
									elseif($fieldValue>0){$fieldValue = $Common->getOneRowFields('user', array('user_id'=>$fieldValue), array('user_first_name', 'user_last_name'));}
								}
								elseif($fieldName=='rule_match'){
									$fieldName = 'Rule Match';
									if($prevFieldVal !=''){
										$prevRuleField = $oneTRowObj[0]['rule_field'];
										if($prevRuleField=='Category'){
											$prevFieldVal = $Common->getOneRowFields('category', array('category_id'=>$prevFieldVal), 'category_name');
										}
										elseif($prevRuleField=='Manufacturer'){
											$prevFieldVal = $Common->getOneRowFields('manufacturer', array('manufacturer_id'=>$prevFieldVal), 'name');
										}
									}
									if($fieldValue !=''){
										if($rule_field=='Category'){
											$fieldValue = $Common->getOneRowFields('category', array('category_id'=>$fieldValue), 'category_name');
										}
										elseif($rule_field=='Manufacturer'){
											$fieldValue = $Common->getOneRowFields('manufacturer', array('manufacturer_id'=>$fieldValue), 'name');
										}
									}
								}
								elseif($fieldName=='is_cost'){
									$fieldName = $this->db->translate('Based on');
									if($prevFieldVal==0){$prevFieldVal = $this->db->translate('SALES');}
									elseif($prevFieldVal>0){$prevFieldVal = $this->db->translate('Cost');}
									if($fieldValue==0){$fieldValue = $this->db->translate('SALES');}
									elseif($fieldValue>0){$fieldValue = $this->db->translate('Cost');}
								}
								$changed[$fieldName] = array($prevFieldVal, $fieldValue);
							}
						}						
					
						if(!empty($changed)){
							$moreInfo = $teData = array();
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $_SESSION["accounts_id"];
							$teData['user_id'] = $_SESSION["user_id"];
							$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'commissions');
							$teData['record_id'] = $commissions_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);							
						}
					}					
				}
			}
		}
	
		$array = array( 'login'=>'', 'commissions_id'=>$commissions_id,
			'savemsg'=>$savemsg,
			'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	public function AJ_remove_Commissions(){
		$POST = json_decode(file_get_contents('php://input'), true);		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$commissions_id = intval($POST['commissions_id']??0);
		$returnmsg = '';
		$removeCount = 0;
			
		$sqlquery = "SELECT 'activity_feed' as tablename, activity_feed_id as table_id FROM activity_feed 
					WHERE accounts_id = $accounts_id and uri_table_name = 'commissions' and activity_feed_link = :activity_feed_link 
					UNION ALL 
					SELECT 'track_edits' as tablename, track_edits_id as table_id FROM track_edits 
					WHERE accounts_id = $accounts_id AND record_for = 'commissions' AND record_id = :commissions_id 
					UNION ALL 
					SELECT 'commissions' as tablename, commissions_id as table_id FROM commissions 
						WHERE commissions_id = :commissions_id and accounts_id = $accounts_id";
		$query = $this->db->query($sqlquery, array('activity_feed_link'=>"/Commissions/view/$commissions_id", 'commissions_id'=>$commissions_id));
		if($query){
			while($onerow = $query->fetch(PDO::FETCH_OBJ)){
				
				$tablename = $onerow->tablename;
				$tableidname = $tablename.'_id';
				$tableidvalue = $onerow->table_id;
				
				$dtrow = $this->db->delete($tablename, $tableidname, $tableidvalue);
				if($dtrow>0){
					$removeCount++;
				}				
			}
		}
		return json_encode(array( 'login'=>'', 'returnmsg'=>$returnmsg, 'removeCount'=>$removeCount));
	}
	
	private function filterHAndOptions(){
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$scommissions_id = $this->commissions_id;
		$shistory_type = $this->history_type;
		
		$bindData = array();
		$bindData['commissions_id'] = $scommissions_id;
		
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Commission Created')==0){
				$filterSql = "SELECT COUNT(commissions_id) AS totalrows FROM commissions 
							WHERE commissions_id = :commissions_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'commissions' AND table_id = :commissions_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'commissions' AND record_id = :commissions_id";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'commissions' AND activity_feed_link = CONCAT('/Commissions/view/', :commissions_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'commissions' AND activity_feed_link = CONCAT('/Commissions/view/', :commissions_id) 
					UNION ALL 
						SELECT COUNT(commissions_id) AS totalrows FROM commissions 
							WHERE commissions_id = :commissions_id and accounts_id = $accounts_id 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'commissions' AND record_id = :commissions_id 
					UNION ALL 
						SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'commissions' AND table_id = :commissions_id";
		}
		
		$totalRows = 0;
		$tableObj = $this->db->query($filterSql, $bindData);
		if($tableObj){
			while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
				$totalRows += $oneRow->totalrows;
			}
		}
		
		$this->totalRows = $totalRows;
	}
	
    private function loadHTableRows(){

		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$scommissions_id = $this->commissions_id;
		$shistory_type = $this->history_type;

		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$bindData = array();
		$bindData['commissions_id'] = $scommissions_id;            
		if($shistory_type !=''){
			
			if(strcmp($shistory_type, 'Commission Created')==0){
				$filterSql = "SELECT 'commissions' AS tablename, created_on AS tabledate, commissions_id AS table_id, 'Commission Created' AS activity_feed_title FROM commissions 
					WHERE commissions_id = :commissions_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'commissions' AND table_id = :commissions_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND record_for = 'commissions' AND record_id = :commissions_id";
			}
			else{
				$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'commissions' AND activity_feed_link = CONCAT('/Commissions/view/', :commissions_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'commissions' AND activity_feed_link = CONCAT('/Commissions/view/', :commissions_id)  
					UNION ALL 
					SELECT 'commissions' AS tablename, created_on AS tabledate, commissions_id AS table_id, 'Commission Created' AS activity_feed_title FROM commissions 
						WHERE commissions_id = :commissions_id AND accounts_id = $accounts_id 
					UNION ALL SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
						WHERE accounts_id = $accounts_id AND record_for = 'commissions' AND record_id = :commissions_id 
					UNION ALL 
						SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'commissions' AND table_id = :commissions_id 
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
 
	public function showRuleMatchOptions(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		$commissions_id = intval($POST['commissions_id']??0);
		$rule_field = $POST['rule_field']??'';
		$rule_match = '';
		
		if($commissions_id>0 && isset($_SESSION["accounts_id"])){
			$accounts_id = $_SESSION["accounts_id"]??0;
			$commissionsObj = $this->db->query("SELECT rule_match FROM commissions WHERE commissions_id = :commissions_id AND accounts_id = $accounts_id AND commissions_publish = 1", array('commissions_id'=>$commissions_id),1);
			if($commissionsObj){
				$rule_match = $commissionsObj->fetch(PDO::FETCH_OBJ)->rule_match;
			}
		}
		$jsonResponse['rule_match'] = $rule_match;
		
		$ruleMatchOpt = array();
		if($rule_field !='' && isset($_SESSION["prod_cat_man"])){
			$prod_cat_man = $_SESSION["prod_cat_man"]??0;
			if(strcmp('Category', $rule_field)==0){
				$sql = "SELECT p.category_id, category.category_name FROM product p LEFT JOIN category ON (p.category_id = category.category_id) WHERE p.accounts_id = $prod_cat_man AND p.product_publish = 1 GROUP BY p.category_id ORDER BY category.category_name ASC";
				$catObj = $this->db->query($sql, array());
				if($catObj){
					while($onerow = $catObj->fetch(PDO::FETCH_OBJ)){
						$optval = $onerow->category_id;
						$label = stripslashes(trim((string) $onerow->category_name));
						if($label !=''){
							$ruleMatchOpt[$optval] = $label;
						}
					}
				}
			}
			elseif(strcmp('Manufacturer', $rule_field)==0){
				$sql = "SELECT p.manufacturer_id, manufacturer.name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.product_publish = 1 GROUP BY p.manufacturer_id ORDER BY manufacturer.name ASC";
				$catObj = $this->db->query($sql, array());
				if($catObj){
					while($onerow = $catObj->fetch(PDO::FETCH_OBJ)){
						$optval = $onerow->manufacturer_id;
						$label = stripslashes(trim((string) $onerow->name));
						if($label !=''){
							$ruleMatchOpt[$optval] = $label;
						}
					}
				}
			}
			else{
				$sql = "SELECT sku FROM product WHERE accounts_id = $prod_cat_man AND product_publish = 1 GROUP BY sku ORDER BY sku ASC";
				$skuObj = $this->db->query($sql, array());
				if($skuObj){
					while($onerow = $skuObj->fetch(PDO::FETCH_OBJ)){
						$label = $optval = $onerow->sku;
						if($label !=''){
							$ruleMatchOpt[$optval] = $label;
						}
					}
				}
			}			
		}
		$jsonResponse['ruleMatchOpt'] = $ruleMatchOpt;
		return json_encode($jsonResponse);
	}
	
	public function report(){}
	
	public function fetching_reportdata(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$tableData = array();
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$semployee_id = intval($POST['semployee_id']??0);
		$date_range = $POST['date_range']??'';
		$showing_type = $POST['showing_type'];

		$startdate = $startdate1 = $enddate = $enddate1 = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d',strtotime($date_rangearray[0])).' 00:00:00';
				$startdate1 = date('Y-m-d',strtotime($date_rangearray[0]));
				$enddate = date('Y-m-d',strtotime($date_rangearray[1])).' 23:59:59';
				$enddate1 = date('Y-m-d',strtotime($date_rangearray[1]));
			}
		}

		$semployee_idArray = array();
		$sql = "SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id";
		if($semployee_id>0){$sql .= " AND user_id = $semployee_id";}

		$userObj = $this->db->query($sql, array());
		if($userObj){
			while($oneRow = $userObj->fetch(PDO::FETCH_OBJ)){
				$semployee_idArray[$oneRow->user_id] = trim("$oneRow->user_first_name $oneRow->user_last_name");
			}
		}

		$sqlquery = "SELECT employee_id FROM pos 
					WHERE accounts_id = $accounts_id AND pos_publish = 1 
						AND (pos_type = 'Sale' OR (pos_type IN ('Order', 'Repairs') AND order_status = 2))";
		if($semployee_id>0){$sqlquery .= " AND employee_id = $semployee_id";}
		$bindData = array();
		if($startdate !='' && $enddate !=''){
			//$sqlquery .= " AND (sales_datetime between '$startdate' and '$enddate')";
			$sqlquery .= " AND (sales_datetime between :startdate and :enddate)";
			$bindData['startdate'] = $startdate;
			$bindData['enddate'] = $enddate;
		}
		$sqlquery .= " GROUP BY employee_id";
		$posObj = $this->db->query($sqlquery, $bindData);
		$employeeIds = array();
		if($posObj){
			while($onegrouprow = $posObj->fetch(PDO::FETCH_OBJ)){
				$employee_id = $onegrouprow->employee_id;
				$employeeIds[$employee_id] = stripslashes($semployee_idArray[$employee_id]??'');
			}
		}
		
		$strextra = "SELECT * FROM commissions WHERE accounts_id = $accounts_id AND commissions_publish = 1";
		$bindData = array();
		if($startdate1 !='' && $enddate1 !=''){
			$strextra .= " AND ((start_date <= :startdate1 AND end_date >= :startdate1) OR (start_date <= :enddate1 AND end_date >= :enddate1) OR (start_date BETWEEN :startdate1 AND :enddate1) OR (end_date BETWEEN :startdate1 AND :enddate1) OR (start_date IN ('0000-00-00', '1000-01-01') AND end_date >= :startdate1) OR (start_date <= :enddate1 AND end_date IN ('0000-00-00', '1000-01-01')))";
			$bindData['startdate1'] = $startdate1;
			$bindData['enddate1'] = $enddate1;
		}

		$comObj = $this->db->query($strextra, $bindData);
		$commissionArrays = array();
		if($comObj){
			while($onerow = $comObj->fetch(PDO::FETCH_OBJ)){
				$start_date = $onerow->start_date;
				if(in_array($start_date, array('0000-00-00', '1000-01-01')) || strtotime($start_date)<strtotime($startdate1)){
					 $cstart_date = $startdate1;
				}
				else{
					$cstart_date = $start_date;
				}
				$end_date = $onerow->end_date;
				if( in_array($end_date, array('0000-00-00', '1000-01-01')) || strtotime($end_date)>strtotime($enddate1)){
					$cend_date = $enddate1;
				}
				else{
					$cend_date = $end_date;
				}
				$salesman = $onerow->salesman;
				
				$commissionArrays[] = array($cstart_date, $cend_date, $onerow->rule_field, $onerow->rule_match, $onerow->is_percent, round($onerow->amount,2), $salesman, $onerow->is_cost);
			}
		}

		$boldclass = '';
		if($showing_type=='Detailed'){
			$boldclass = ' class="txt14bold"';
		}
		$employeeOpts = array();
		if(!empty($employeeIds)){
			asort($employeeIds);
			
			foreach($employeeIds as $employee_id=>$employeename){
				
				$totalprice = 0;
				$totalcommissions = 0;

				if(!empty($commissionArrays)){
					$tableSubData = array();
					foreach($commissionArrays as $comOneRow){

						$start_date = $comOneRow[0];
						$end_date = $comOneRow[1];
						$rule_field = $comOneRow[2];
						$rule_match = $comOneRow[3];
						$is_percent = $comOneRow[4];
						$amount = $comOneRow[5];
						$salesman = $comOneRow[6];
						$is_cost = $comOneRow[7];
						
						$commitionstr = $amount;
						
						$bindData = array();
						if($salesman>0){
							if($salesman==$employee_id){$runQuery = true;}
							else{$runQuery = false;}
						}
						else{
							$runQuery = true;
						}
						
						if($runQuery){
							
							$sumcondition = "pos.employee_id = $employee_id";
							if(!in_array($start_date, array('', '0000-00-00', '1000-01-01')) && !in_array($end_date, array('', '0000-00-00', '1000-01-01'))){
								$sumcondition .= " AND (pos.sales_datetime BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59')";
							}

							if(strcmp('Category', $rule_field)==0){
								$sumcondition .= " AND product.category_id = $rule_match";
							}
							elseif(strcmp('Manufacturer', $rule_field)==0){
								$sumcondition .= " AND product.manufacturer_id = $rule_match";
							}
							elseif(strcmp('Product SKU', $rule_field)==0){
								$sumcondition .= " AND product.sku = '$rule_match'";
							}
							$substrextra = $detailsfields = '';
							$rowtotalqty = $rowtotalprice = $rowtotalcommissions = 0;
							if($showing_type=='Detailed'){
								$detailsfields = "pos.invoice_no, pos.customer_id, pos.sales_datetime,";
							}
							$sumsql = "SELECT pos_cart.*, $detailsfields pos.taxes_percentage1, pos.tax_inclusive1, pos.taxes_percentage2, pos.tax_inclusive2 
										FROM pos, pos_cart, product 
										WHERE pos.accounts_id = $accounts_id AND pos.pos_publish = 1 AND (pos.pos_type = 'Sale' OR (pos.pos_type in ('Order', 'Repairs') AND pos.order_status = 2)) AND $sumcondition 
											AND pos.pos_id = pos_cart.pos_id 
											AND pos_cart.item_id = product.product_id 
										ORDER BY pos.pos_id ASC, pos_cart.pos_cart_id ASC";
							
							$sumquery = $this->db->querypagination($sumsql, $bindData);
							if($sumquery){
								$num_rows = count($sumquery);
								if($num_rows>0){
									$statusDetails = array();
									$prevpos_id = 0;
									for($r=0; $r<$num_rows; $r++){

										$pos_cartrow = $sumquery[$r];

										$pos_id = $pos_cartrow['pos_id'];
										$nextpos_id = 0;
										if(($r+1)<$num_rows){
											$nextrow = $sumquery[$r+1];
											$nextpos_id = $nextrow['pos_id'];
										}

										if($pos_id != $prevpos_id){
											$qtytotal = 0;
											$qtytotalvalue = 0;
											$qtytotalcommissions = 0;
										}

										$prevpos_id = $pos_id;

										$pos_cart_id = $pos_cartrow['pos_cart_id'];
										$item_type = $pos_cartrow['item_type'];

										$sales_price = $pos_cartrow['sales_price'];
										if($is_cost==1){
											$sales_price = $pos_cartrow['ave_cost'];
										}
										elseif($is_cost==2){
											$sales_price = $sales_price - $pos_cartrow['ave_cost'];
										}
										$shipping_qty = $pos_cartrow['shipping_qty'];

										$qtyvalue = round($sales_price*$shipping_qty,2);
										$discount_is_percent = $pos_cartrow['discount_is_percent'];
										$discount = $pos_cartrow['discount'];
										if($discount_is_percent>0){
											$discount_value = round($qtyvalue*0.01*$discount,2);
										}
										else{
											$discount_value = round($discount*$shipping_qty,2);
										}
										$qtyvalue = $qtyvalue-$discount_value;

										if($is_percent>0){
											$commitionvalue = round($qtyvalue*0.01*$amount,2);
										}
										else{
											$commitionvalue = round($amount*$shipping_qty,2);
										}

										$qtytotal += $shipping_qty;
										$qtytotalvalue += $qtyvalue;

										$qtytotalcommissions += $commitionvalue;


										if($pos_id != $nextpos_id){
											$rowtotalqty += $qtytotal;
											$rowtotalprice += $qtytotalvalue;
											$rowtotalcommissions += $qtytotalcommissions;

											if($showing_type=='Detailed'){

												$customers_id = $pos_cartrow['customer_id'];
												$customername = '';
												$customersObj = $this->db->query("SELECT first_name, last_name FROM customers WHERE customers_id = $customers_id", array());
												if($customersObj){
													$customersOneRow = $customersObj->fetch(PDO::FETCH_OBJ);
													$customername = stripslashes(trim("$customersOneRow->first_name $customersOneRow->last_name"));
												}
												$invoice_no = $pos_cartrow['invoice_no'];
												if($is_percent==0){
													$qtytotalvalue = $qtytotal;
												}

												$statusDetails[] = array($pos_cartrow['sales_datetime'], $invoice_no, $commitionstr, $qtytotalvalue, $qtytotalcommissions);
											}
										}
									}

									$totalprice += $rowtotalprice;
									$totalcommissions += $rowtotalcommissions;
									
									if($is_percent==0){$rowtotalprice = $rowtotalqty;}
									
									if(strcmp('Category', $rule_field)==0){
										$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $rule_match", array());
										if($categoryObj){
											$rule_match = $categoryObj->fetch(PDO::FETCH_OBJ)->category_name;
										}
									}
									elseif(strcmp('Manufacturer', $rule_field)==0){
										$manufacturerObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = $rule_match", array());
										if($manufacturerObj){
											$rule_match = $manufacturerObj->fetch(PDO::FETCH_OBJ)->name;
										}
									}
									
									$fromtoStr = '';
									if( !in_array($start_date, array('0000-00-00', '1000-01-01'))){
										$fromtoStr = $start_date;
									}
									if( !in_array($end_date, array('0000-00-00', '1000-01-01'))){
										$fromtoStr .= " - ".$end_date;
									}

									$tableSubData[] = array('boldclass'=>$boldclass, 'rule_field'=>$rule_field, 'rule_match'=>$rule_match, 'fromtoStr'=>$fromtoStr, 'commitionstr'=>$commitionstr, 'rowtotalprice'=>$rowtotalprice, 'rowtotalcommissions'=>$rowtotalcommissions, 'statusDetails'=>$statusDetails, 'is_percent'=>$is_percent);
								}
							}
							
						}
					}

					if(!empty($tableSubData)){
						$employeeOpts[$employee_id] = $employeename;
						$tableData[] = array('employeename'=>$employeename, 'totalcommissions'=>$totalcommissions, 'tableSubData'=>$tableSubData);
					}
				}
			}
		}
		return json_encode(array('login'=>'', 'tableData'=>$tableData, 'employeeOpts'=>$employeeOpts));
    }
	
	//========================ASync========================//	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$srule_field = $POST['srule_field']??'All';
		$sorting_type = $POST['sorting_type']??0;
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->rule_field = $srule_field;
		$this->sorting_type = $sorting_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['ruleFieOpt'] = $this->ruleFieOpt;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}

	public function AJgetHPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$commissions_id = intval($POST['commissions_id']??0);
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->commissions_id = $commissions_id;
		$this->history_type = $shistory_type;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterHAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;			
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		$jsonResponse['tableRows'] = $this->loadHTableRows();
		
		return json_encode($jsonResponse);
	}

	public function AJ_view_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);

		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$commissions_id = intval($POST['commissions_id']??0);

		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$commissionsObj = $this->db->query("SELECT * FROM commissions WHERE commissions_id = :commissions_id AND accounts_id = $accounts_id AND commissions_publish = 1", array('commissions_id'=>$commissions_id),1);
		if($commissionsObj){
			$commissionsarray = $commissionsObj->fetch(PDO::FETCH_OBJ);	
			
			$commissions_id = $commissionsarray->commissions_id;
			$start_date = $end_date = '';
			if( !in_array($commissionsarray->start_date, array('0000-00-00', '1000-01-01'))){
				$start_date = $commissionsarray->start_date;
			}
			
			if( !in_array($commissionsarray->end_date, array('0000-00-00', '1000-01-01'))){
				$end_date = $commissionsarray->end_date;
			}
			
			$rule_field = $commissionsarray->rule_field;
			$rule_match = stripslashes($commissionsarray->rule_match);
			if(strcmp('Category', $rule_field)==0){
				$categoryObj = $this->db->query("SELECT category_name FROM category WHERE category_id = $rule_match", array());
				if($categoryObj){
					$rule_match = $categoryObj->fetch(PDO::FETCH_OBJ)->category_name;
				}
			}
			elseif(strcmp('Manufacturer', $rule_field)==0){
				$manufacturerObj = $this->db->query("SELECT name FROM manufacturer WHERE manufacturer_id = $rule_match", array());
				if($manufacturerObj){
					$rule_match = $manufacturerObj->fetch(PDO::FETCH_OBJ)->name;
				}
			}
			elseif(strcmp('Product SKU', $rule_field)==0){
				$sqlProd = "SELECT p.sku, manufacturer.name AS manufacture, p.product_name, p.colour_name, p.storage, p.physical_condition_name FROM product p LEFT JOIN manufacturer ON (p.manufacturer_id = manufacturer.manufacturer_id) WHERE p.accounts_id = $prod_cat_man AND p.sku ='".addslashes($rule_match)."'";
				$prodObj = $this->db->query($sqlProd, array());
				if($prodObj){
					while($prodOneRow = $prodObj->fetch(PDO::FETCH_OBJ)){
						$rule_match = stripslashes(trim("$rule_match ($prodOneRow->manufacture $prodOneRow->product_name $prodOneRow->colour_name $prodOneRow->storage $prodOneRow->physical_condition_name)"));
					}
				}
			}

			$is_percent = $commissionsarray->is_percent;
			$is_cost = $commissionsarray->is_cost;
			$amount = round($commissionsarray->amount,2);
			$salesman = $commissionsarray->salesman;
			$salesmanStr = '';
			if($salesman>0){
				$salesmanObj = $this->db->query("SELECT user_first_name, user_last_name, user_email FROM user WHERE accounts_id = $accounts_id AND user_id = $salesman", array());
				if($salesmanObj){
					$salesmanRow = $salesmanObj->fetch(PDO::FETCH_OBJ);
					$salesmanStr = stripslashes(trim("$salesmanRow->user_first_name $salesmanRow->user_last_name"));
					if($salesmanRow->user_email !=''){$salesmanStr .= " ($salesmanRow->user_email)";}				
				}
			}

			$jsonResponse['start_date'] = $start_date;
			$jsonResponse['end_date'] = $end_date;
			$jsonResponse['rule_field'] = $rule_field;
			$jsonResponse['rule_match'] = $rule_match;
			$jsonResponse['is_percent'] = $is_percent;
			$jsonResponse['amount'] = $amount;
			$jsonResponse['salesmanStr'] = $salesmanStr;
			$jsonResponse['commissions_publish'] = $commissionsarray->commissions_publish;
			$jsonResponse['commissions_id'] = $commissions_id;

			$Sql = "SELECT activity_feed_title AS afTitle FROM activity_feed 
					WHERE accounts_id = $accounts_id AND uri_table_name = 'commissions' AND activity_feed_link = CONCAT('/Commissions/view/', :commissions_id) 
				UNION ALL 
					SELECT 'Commission Created' AS afTitle FROM commissions 
						WHERE commissions_id = :commissions_id and accounts_id = $accounts_id 
				UNION ALL 
					SELECT 'Track Edits' AS afTitle FROM track_edits 
					WHERE accounts_id = $accounts_id AND record_for = 'commissions' AND record_id = :commissions_id 
				UNION ALL 
					SELECT 'Notes Created' AS afTitle FROM notes 
					WHERE accounts_id = $accounts_id AND note_for = 'commissions' AND table_id = :commissions_id";
			$actFeeTitOpt = array();
			$tableObj = $this->db->query($Sql, array('commissions_id'=>$commissions_id));
			if($tableObj){
				$actFeeTitOpts = array();
				while($oneRow = $tableObj->fetch(PDO::FETCH_OBJ)){
					$actFeeTitOpts[$oneRow->afTitle] = '';
				}
				ksort($actFeeTitOpts);
				$actFeeTitOpt = array_keys($actFeeTitOpts);
			}
			$jsonResponse['actFeeTitOpt'] = $actFeeTitOpt;
		}
		else{
			$jsonResponse['login'] = 'Commissions/lists/';
		}

		return json_encode($jsonResponse);
	}
	
}
?>