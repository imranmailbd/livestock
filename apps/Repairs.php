<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Repairs{	
	protected $db;
	private int $page, $totalRows, $assign_to, $repairs_id;
	private string $sorting_type, $view_type, $keyword_search, $history_type;
	private array $vieTypOpt, $assToOpt, $actFeeTitOpt;

	public function __construct($db){$this->db = $db;}
	
	public function lists(){
		$accounts_id = $_SESSION['accounts_id']??0;
		$ssorting_type = 0;
		$sortingTypeData = array(0=>'customers.first_name ASC', 
								1=>'customers.last_name ASC', 
								2=>'repairs.due_datetime ASC, repairs.due_time ASC', 
								3=>'repairs.last_updated DESC',
								4=>'repairs.ticket_no ASC',
								5=>'repairs.ticket_no DESC',
								6=>'repairs.status ASC',
								7=>'repairs.problem ASC',
								8=>'user.user_first_name ASC, user.user_last_name ASC');

		$vieTypOpts = array('Assigned','On Hold','Waiting on Customer','Waiting for Parts');
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists('repair_statuses', $value)){
					$repair_statuses = $value['repair_statuses'];
					if($repair_statuses !=''){
						$vieTypOpts = explode('||',$repair_statuses);
					}
				}
				if(array_key_exists('repair_sort', $value)){
					$ssorting_type = intval($value['repair_sort']);
				}			
			}
		}

		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
		}
		$htmlStr = '<input type="hidden" id="defaultssorting_type" value="'.$ssorting_type.'">';		
		return $htmlStr;
	}
	
	private function filterAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$ssorting_type = $this->sorting_type;
		$sview_type = $this->view_type;
		$sassign_to = $this->assign_to;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Repairs";
		$_SESSION["list_filters"] = array('ssorting_type'=>$ssorting_type, 'sview_type'=>$sview_type, 'sassign_to'=>$sassign_to, 'keyword_search'=>$keyword_search);
		
		$filterSql = "";
		$bindData = array();
		$filterSql = "";
		if($sview_type !='All'){
			if($sview_type==1){$filterSql .= " AND repairs.status NOT IN ('Cancelled', 'Invoiced', 'Estimate')";}
			elseif($sview_type ==2){$filterSql .= " AND repairs.status IN ('Cancelled', 'Invoiced')";}
			else{				
				$filterSql .= " AND repairs.status = :sview_type";
				$bindData['sview_type'] = $sview_type;
			}
		}
		if($sassign_to>0){
			$bindData['sassign_to'] = $sassign_to;
			$filterSql .= " AND repairs.assign_to = :sassign_to";
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$sticket_no = str_replace('t', '', strtolower(trim((string) $keyword_searches[$num])));
				$filterSql .= " AND (repairs.ticket_no LIKE CONCAT(:ticket_no$num, '%') OR TRIM(CONCAT_WS(' ', repairs.problem, repairs.status, repairs.lock_password, properties.more_details, customers.company, customers.first_name, customers.last_name, properties.imei_or_serial_no, properties.more_details)) LIKE CONCAT('%', :keyword_search$num, '%') OR properties.brand_model_id IN (SELECT brand_model_id FROM brand_model WHERE brand_model.accounts_id = $prod_cat_man AND TRIM(CONCAT_WS(' ', brand_model.brand, brand_model.model)) LIKE CONCAT('%', :keyword_search$num, '%')))";
				$bindData['ticket_no'.$num] = $sticket_no;
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$queryObj = $this->db->query("SELECT COUNT(repairs.repairs_id) AS totalrows FROM repairs, properties, customers WHERE repairs.accounts_id = $accounts_id $filterSql AND repairs.customer_id = customers.customers_id AND repairs.properties_id = properties.properties_id", $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		
		$Sql ="SELECT repairs.assign_to, repairs.status FROM repairs, properties, customers WHERE repairs.accounts_id = $accounts_id $filterSql AND repairs.customer_id = customers.customers_id AND repairs.properties_id = properties.properties_id GROUP BY repairs.assign_to, repairs.status";		
		$query = $this->db->query($Sql, $bindData);
		$assToOpts = $statusOpts = array();
		if($query){
			while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
				$assToOpts[$oneRow->assign_to] = '';
				$statusOpts[trim((string) $oneRow->status)] = '';
			}
		}
		
		$assToOpt =array();
		if(count($assToOpts)>0){
			$assingSql = "SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id IN (".implode(', ', array_keys($assToOpts)).") AND accounts_id = $prod_cat_man ORDER BY user_first_name ASC, user_last_name ASC";
			$assQuery = $this->db->query($assingSql, array());
			if($assQuery){
				while($oneRow = $assQuery->fetch(PDO::FETCH_OBJ)){
					$assToOpt[$oneRow->user_id] = stripslashes(trim("$oneRow->user_first_name $oneRow->user_last_name"));
				}
			}
		}

		//=======================View Type================//
		$vieTypOpt = array();
		if(!empty($statusOpts)){
			foreach($statusOpts as $optValue=>$val){
				if(!empty($optValue) && !array_key_exists($optValue, $vieTypOpt))
					$vieTypOpt[$optValue] = stripslashes($optValue);
			}					
		}
		if(empty($ssorting_type)){$ssorting_type = 0;}

		$this->totalRows = $totalRows;
		$this->vieTypOpt = $vieTypOpt;
		$this->assToOpt = $assToOpt;
	}
	
    private function loadTableRows(){
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$limit = $_SESSION["limit"]??'auto';
		$Common = new Common($this->db);
		
		$page = $this->page;
		$totalRows = $this->totalRows;		
		$ssorting_type = $this->sorting_type;
		$sview_type = $this->view_type;
		$sassign_to = $this->assign_to;
		$keyword_search = $this->keyword_search;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		$sortingTypeData = array(0=>'customers.first_name ASC', 
								1=>'customers.last_name ASC', 
								2=>'repairs.due_datetime ASC, repairs.due_time ASC', 
								3=>'repairs.last_updated DESC',
								4=>'repairs.ticket_no ASC',
								5=>'repairs.ticket_no DESC',
								6=>'repairs.status ASC',
								7=>'repairs.problem ASC',
								8=>'user.user_first_name ASC, user.user_last_name ASC');

		$filterSql = "";
		$bindData = array();
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}
		if($ssorting_type==8){
			$filterSql = "FROM customers, properties, repairs LEFT JOIN user ON (user.user_id = repairs.assign_to) WHERE repairs.accounts_id = $accounts_id AND repairs.customer_id = customers.customers_id AND repairs.properties_id = properties.properties_id";
		}
		else{
			$filterSql = "FROM repairs, properties, customers WHERE repairs.accounts_id = $accounts_id AND repairs.customer_id = customers.customers_id AND repairs.properties_id = properties.properties_id";
		}
		if($sview_type !='All'){
			if($sview_type==1){$filterSql .= " AND repairs.status NOT IN ('Cancelled', 'Invoiced', 'Estimate')";}
			elseif($sview_type ==2){$filterSql .= " AND repairs.status IN ('Cancelled', 'Invoiced')";}
			else{				
				$filterSql .= " AND repairs.status = :sview_type";
				$bindData['sview_type'] = $sview_type;
			}
		}
		if($sassign_to>0){
			$bindData['sassign_to'] = $sassign_to;
			$filterSql .= " AND repairs.assign_to = :sassign_to";
		}
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$sticket_no = str_replace('t', '', strtolower(trim((string) $keyword_searches[$num])));
				$filterSql .= " AND (repairs.ticket_no LIKE CONCAT(:ticket_no$num, '%') OR TRIM(CONCAT_WS(' ', repairs.problem, repairs.status, repairs.lock_password, properties.more_details, customers.company, customers.first_name, customers.last_name, properties.imei_or_serial_no, properties.more_details)) LIKE CONCAT('%', :keyword_search$num, '%') OR properties.brand_model_id IN (SELECT brand_model_id FROM brand_model WHERE brand_model.accounts_id = $prod_cat_man AND TRIM(CONCAT_WS(' ', brand_model.brand, brand_model.model)) LIKE CONCAT('%', :keyword_search$num, '%')))";
				$bindData['ticket_no'.$num] = $sticket_no;
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}

		$filterSql .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		
		$sql = "SELECT repairs.*, properties.brand_model_id, properties.more_details, customers.company, customers.first_name, customers.last_name $filterSql LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sql, $bindData);
		$tabledata = array();
		$todaydate = strtotime(date('m/d/Y'));
		if($query){
			$brandModelIds = $statusData = $assignIds = array();
			foreach($query as $repairsOneRow){
				if(empty($assignIds) || !in_array($repairsOneRow['assign_to'], $assignIds)){$assignIds[] = $repairsOneRow['assign_to'];}
				if(empty($brandModelIds) || !in_array($repairsOneRow['brand_model_id'], $brandModelIds)){$brandModelIds[] = $repairsOneRow['brand_model_id'];}
				$statusData[$repairsOneRow['status']] = '';
			}
			
			$assign_toArray = array();
			if(!empty($assignIds)){
				$usersql = "SELECT user_id, user_first_name, user_last_name FROM user WHERE user_id IN (".implode(', ', $assignIds).") ORDER BY user_first_name ASC, user_last_name ASC";
				$userquery = $this->db->querypagination($usersql, array());					
				if($userquery !=''){
					foreach($userquery as $rowuser){
						$user_id = $rowuser['user_id'];
						$user_first_name = stripslashes($rowuser['user_first_name']);
						$user_last_name = stripslashes($rowuser['user_last_name']);
						
						$assign_toArray[$user_id] = trim((string) "$user_first_name $user_last_name");
					}
				}
			}
			
			if(!empty($statusData)){
				$statusData = array_keys($statusData);
				$repair_statusesarray = $status_colors = array();
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('repair_statuses', $value)){
							$repair_statusesarray = explode('||',$value['repair_statuses']);
							if(!empty($repair_statusesarray)){
								$p = 0;
								foreach($repair_statusesarray as $oneStatus){
									if(in_array($oneStatus, array('New', 'Finished'))){unset($repair_statusesarray[$p]);}
									$p++;
								}
							}
							array_splice( $repair_statusesarray, 0, 0, array('New', 'Finished'));
						}
						if(array_key_exists('status_colors', $value)){
							$status_colors = explode('||',$value['status_colors']);
						}
					}
				}
				//array_unshift($repair_statusesarray,"Finished");
				//array_unshift($repair_statusesarray,"New");
				$statusColors = array();
				if(in_array('Invoiced', $statusData)){
					$statusColors['Invoiced'] = " style=\"background:#FFFFFF; color:#000000; padding:2px 8px;\"";
				}
				if(in_array('Cancelled', $statusData)){
					$statusColors['Cancelled'] = " style=\"background:#FFFFFF; color:#000000;padding:2px 8px;\"";
				}
				if(in_array('New', $statusData)){
					$bgcolor = '#5cb85c';
					if(!empty($status_colors) && array_key_exists(0,$status_colors)){
						$bgcolor = $status_colors[0];
					}
					$statusColors['New'] = " style=\"background:$bgcolor; color:#FFFFFF;padding:2px 8px;\"";
				}
				if(in_array('Estimate', $statusData)){
					$statusColors['Estimate'] = " style=\"background:#FFFFFF; color:#000000;padding:2px 8px;\"";
				}
				if(in_array('Finished', $statusData)){
					$bgcolor = '#000000';
					if(!empty($status_colors) && array_key_exists(1,$status_colors)){
						$bgcolor = $status_colors[1];
					}
					$statusColors['Finished'] = " style=\"background:$bgcolor; color:#FFFFFF;padding:2px 8px;\"";
				}
				
				if(count($repair_statusesarray)>0){
					$c = 0;
					foreach($repair_statusesarray as $onerepair_statuses){
						$onerepair_statuses = stripslashes($onerepair_statuses);
						if(!empty($onerepair_statuses)){
							$bgcolor = '#FFFFFF';
							if(!empty($status_colors) && array_key_exists($c,$status_colors)){
								$bgcolor = $status_colors[$c];
							}
							$color = '#FFFFFF';
							if(strtoupper($bgcolor)=='#FFFFFF'){$color = '#000000';}
							if(!in_array($onerepair_statuses, array('New', 'Finished', 'Invoiced', 'Cancelled', 'Estimate'))){
								$statusColors[$onerepair_statuses] = " style=\"background:$bgcolor; color:$color; padding:2px 8px;\"";
							}
						}
						$c++;
					}
				}
			}
			
			$brandModelData = array();
			if(!empty($brandModelIds)){
				$bMSql = "SELECT brand_model_id, brand, model FROM brand_model WHERE accounts_id = $prod_cat_man AND brand_model_id IN (".implode(', ', $brandModelIds).") ORDER BY brand ASC, model ASC";
				$bMObj = $this->db->query($bMSql, array());
				if($bMObj){
					while($oneRow = $bMObj->fetch(PDO::FETCH_OBJ)){
						$brandModelData[$oneRow->brand_model_id] = trim(stripslashes("$oneRow->brand $oneRow->model"));
					}
				}
			}
			
			foreach($query as $rowrepairs){
				
				$repairs_id = $rowrepairs['repairs_id'];
				$ticket_no = stripslashes($rowrepairs['ticket_no']);
				
				$customername = stripslashes($rowrepairs['company']);
				if($customername !=''){$customername .=', ';}
				$customername .= stripslashes(trim("$rowrepairs[first_name] $rowrepairs[last_name]"));
				$assign_to = $rowrepairs['assign_to'];
				$technicianName = '';
				if(is_array($assign_toArray) && array_key_exists($assign_to, $assign_toArray)){
					$technicianName = $assign_toArray[$assign_to];
				}
				
				$problem = stripslashes(trim((string) "$rowrepairs[problem]"));
				$brand_model_id = $rowrepairs['brand_model_id'];
				$brandModel = $brandModelData[$brand_model_id]??'';
				$more_details = $rowrepairs['more_details'];
				if($brandModel !='' || $more_details !=''){
					$problem .= '<br />'.trim(stripslashes("$brandModel $more_details"));
				}
				$status = $rowrepairs['status'];
				
				$due_datetime = '';
				if(!in_array($rowrepairs['due_datetime'], array('0000-00-00', '1000-01-01')) && !in_array($status, array('Invoiced','Cancelled'))){
					$due_datetime = $rowrepairs['due_datetime'];
				}
				
				$since = time() - strtotime($rowrepairs['last_updated']);
				$last_updated = $Common->time_since($since);
				if(!empty($statusColors) && array_key_exists($status, $statusColors)){
					$status = stripslashes("<span$statusColors[$status]>$status</span>");
				}
				$tabledata[] = array($repairs_id, $customername, $problem, $rowrepairs['created_on'], $ticket_no, $last_updated, $due_datetime, $rowrepairs['due_time'], $status, $technicianName);
			}
		}
		
		return $tabledata;
    }
	
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$ssorting_type = $POST['ssorting_type']??0;
		$sview_type = $POST['sview_type']??1;
		$sassign_to = intval($POST['sassign_to']??0);
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->sorting_type = $ssorting_type;
		$this->view_type = $sview_type;
		if(!is_int($sassign_to)){$this->db->writeIntoLog("Error on Repairs#366: A/C Id: $_SESSION[accounts_id], sassign_to: $sassign_to, ASCII: ".ord($sassign_to).", segment2name: $GLOBALS[segment2name], segment3name: $GLOBALS[segment3name]");$sassign_to = 0;}
		$this->assign_to = $sassign_to;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
			$jsonResponse['assToOpt'] = $this->assToOpt;
			$jsonResponse['vieTypOpt'] = $this->vieTypOpt;
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
		$customFields = 0;
		$queryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers'", array());
		if($queryObj){
			$customFields = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$jsonResponse['customFields'] = $customFields;
				
		$formsFields = array();
		$formObj = $this->db->query("SELECT forms_id, form_name, required FROM forms WHERE accounts_id = $prod_cat_man AND form_for = 'repairs' AND form_condition = 'Create Repair' AND forms_publish = 1", array());
		if($formObj){
			while($oneForm = $formObj->fetch(PDO::FETCH_OBJ)){
				$formsFields[$oneForm->forms_id] = array(stripslashes(trim($oneForm->form_name)), intval($oneForm->required));
			}
		}
		$jsonResponse['formsFields'] = $formsFields;

		$proOpt = array();
		if($prod_cat_man>0){
			$sqlproblem = "SELECT name FROM repair_problems WHERE accounts_id = $prod_cat_man AND repair_problems_publish = 1 ORDER BY name ASC";
			$problemquery = $this->db->query($sqlproblem, array());
			if($problemquery){
				while($oneproblemrow = $problemquery->fetch(PDO::FETCH_OBJ)){
					$oproblem_name = stripslashes(trim((string) $oneproblemrow->name));
					if(!empty($oproblem_name)){
						$proOpt[$oproblem_name] = '';
					}
				}
			}
			if(!empty($proOpt)){$proOpt = array_keys($proOpt);}
		}
		$jsonResponse['proOpt'] = $proOpt;

		$formsInfo = array();
		if(!empty($formsFields)){			
			$loadSessFormInfo = $this->loadSessFormInfo(1);
			if(!empty($loadSessFormInfo) && array_key_exists('returnData', $loadSessFormInfo)){
				$formsInfo = $loadSessFormInfo['returnData'];
			}
		}
		$jsonResponse['formsInfo'] = $formsInfo;

		$notify_default_email = $notify_default_sms = '';
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'notifications'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				foreach($value as $oneNotifyRow){
					if($notify_default_email=='' && $oneNotifyRow['email_body'] !=''){
						$notify_default_email = $oneNotifyRow['email_body'];
					}
					if($notify_default_sms=='' && $oneNotifyRow['sms_text'] !=''){
						$notify_default_sms = $oneNotifyRow['sms_text'];
					}
				}
			}

			$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'sms_messaging' AND value !=''", array());
			if(!$varObj){$notify_default_sms = '';}
		}
		
        $jsonResponse['notify_default_email'] = $notify_default_email;
		$jsonResponse['notify_default_sms'] = $notify_default_sms;
		
		return json_encode($jsonResponse);
	}
	
	public function add(){}

	public function removeFormRow(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$formsInfo = array();
		if(isset($_SESSION["formsInfo"])){
			$formsInfo = $_SESSION["formsInfo"];
		}
		$forms_id = intval($POST['forms_id']??0);
		if(array_key_exists($forms_id, $formsInfo)){
			unset($formsInfo[$forms_id]);
			$_SESSION["formsInfo"] = $formsInfo;
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJ_edit_MoreInfo(){

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$prod_cat_man = $_SESSION['prod_cat_man']??0;
		$accounts_id = $_SESSION['accounts_id']??0;
		$user_id = $_SESSION['user_id']??0;
		$currency = $_SESSION["currency"]??'৳';
		
		$POST = json_decode(file_get_contents('php://input'), true);
		$repairs_id = $POST['repairs_id'];
		$repairsObj = $this->db->query("SELECT * FROM repairs WHERE repairs_id = :repairs_id AND accounts_id = $accounts_id", array('repairs_id'=>$repairs_id),1);
		if($repairsObj){
			$repairs_array = $repairsObj->fetch(PDO::FETCH_OBJ);
			$Common = new Common($this->db);
			$Carts = new Carts($this->db);
			$Payments = new Payments($this->db);

			$repairs_id = $repairs_array->repairs_id;
			$jsonResponse['repairs_id'] = intval($repairs_id);
			$pos_id = $repairs_array->pos_id;
			$ticket_no = intval($repairs_array->ticket_no);
			$jsonResponse['ticket_no'] = intval($ticket_no);
			
			$status = $repairs_array->status;
			$jsonResponse['status'] = $status;

			$repair_statusesarray = $status_colors = array();
			$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
			if($varObj){
				$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
				if(!empty($value)){
					$value = unserialize($value);
					if(array_key_exists('repair_statuses', $value)){
						$repair_statusesarray = explode('||', $value['repair_statuses']);
						if(!empty($repair_statusesarray)){
							$p = 0;
							foreach($repair_statusesarray as $oneStatus){
								if(in_array($oneStatus, array('New', 'Finished'))){unset($repair_statusesarray[$p]);}
								$p++;
							}
						}
						array_splice( $repair_statusesarray, 0, 0, array('New', 'Finished'));
					}
					if(array_key_exists('status_colors', $value) && !empty($value['status_colors'])){
						$status_colors = explode('||', $value['status_colors']);
					}
				}
			}
						
			$repairsStaOpts = array();
			$currentStatusBG = '#5cb85c';
			$color = '#FFFFFF';

			if(in_array($status, array('Invoiced', 'Cancelled'))){
				$repairsStaOpts[$status] = array($currentStatusBG, $color);
			}
			else{
				//==========For Estimate==============//
				$bgcolor = '#FFFFFF';
				$color = '#000000';
				if(strcmp($status, 'Estimate')==0){$repairsStaOpts[$status] = array($bgcolor, $color);}
				
				//==========For New==============//
				$bgcolor = '#5cb85c';
				if(!empty($status_colors) && array_key_exists(0,$status_colors) && !empty($status_colors[0])){$bgcolor = $status_colors[0];}				
				$color = '#FFFFFF';
				if(strtoupper($bgcolor)=='#FFFFFF'){$color = '#000000';}
				if(strcmp($status, 'New')==0){$currentStatusBG = $bgcolor;}
				$repairsStaOpts['New'] = array($bgcolor, $color);
			
				//==========For Others==============//
				if(count($repair_statusesarray)>0){
					$c = 0;
					foreach($repair_statusesarray as $onerepair_statuses){
						$onerepair_statuses = stripslashes($onerepair_statuses);
						if($onerepair_statuses !=''){
							$bgcolor = '#FFFFFF';
							if(!empty($status_colors) && array_key_exists($c,$status_colors)){$bgcolor = $status_colors[$c];}
							$color = '#FFFFFF';
							if(strtoupper($bgcolor)=='#FFFFFF'){$color = '#000000';}
							
							if(!in_array($onerepair_statuses, array('New', 'Finished'))){
								if(strcmp($status, $onerepair_statuses)==0){$currentStatusBG = $bgcolor;}
								$repairsStaOpts[$onerepair_statuses] = array($bgcolor, $color);
							}
						}
						$c++;
					}
				}
				
				//==========For Finished==============//
				$bgcolor = '#000000';
				if(!empty($status_colors) && array_key_exists(1,$status_colors)){$bgcolor = $status_colors[1];}
				$color = '#FFFFFF';
				if(strtoupper($bgcolor)=='#FFFFFF'){$color = '#000000';}							
				if(strcmp($status, 'Finished')==0){$currentStatusBG = $bgcolor;}
				$repairsStaOpts['Finished'] = array($bgcolor, $color);
			}

			$color = '#FFFFFF';			
			if(strtoupper($currentStatusBG)=='#FFFFFF'){$color = '#000000';}
			$jsonResponse['currentStatusBG'] = $currentStatusBG;
			$jsonResponse['currentColor'] = $color;
			$jsonResponse['repairsStaOpts'] = $repairsStaOpts;

			$customer_id = $invoice_no = 0;
			$posObj = $this->db->query("SELECT * FROM pos WHERE pos_id = $pos_id AND accounts_id = $accounts_id", array());
			if($posObj){
				$pos_onerow = $posObj->fetch(PDO::FETCH_OBJ);
				$customer_id = $pos_onerow->customer_id;
				$invoice_no = intval($pos_onerow->invoice_no);
				
			}
			$jsonResponse['invoice_no'] = intval($invoice_no);
			$jsonResponse['customer_id'] = intval($customer_id);

			//====================Linked Ticket=============//
			$from_repairs_id = intval($repairs_array->from_repairs_id);
			$linkedRepairsID = $linkedTickerNo = $linkedRepairsID2 = $linkedTickerNo2 = '';
			$vltrepairsData = $this->db->querypagination("SELECT repairs_id, ticket_no FROM repairs WHERE accounts_id = $accounts_id AND from_repairs_id = $repairs_id ORDER BY ticket_no DESC LIMIT 0, 1", array());
			if(!empty($vltrepairsData)){
				$linkedRepairsID = intval($vltrepairsData[0]['repairs_id']);
				$linkedTickerNo = intval($vltrepairsData[0]['ticket_no']);
			}
			if($from_repairs_id>0){
				$vltrepairsData = $this->db->querypagination("SELECT ticket_no FROM repairs WHERE accounts_id = $accounts_id AND repairs_id = $from_repairs_id ORDER BY ticket_no DESC LIMIT 0, 1", array());
				if(is_array($vltrepairsData)){
					$linkedRepairsID2 = $from_repairs_id;
					$linkedTickerNo2 = intval($vltrepairsData[0]['ticket_no']);
				}
			}
			$jsonResponse['linkedRepairsID'] = intval($linkedRepairsID);
			$jsonResponse['linkedTickerNo'] = intval($linkedTickerNo);
			$jsonResponse['linkedRepairsID2'] = intval($linkedRepairsID2);
			$jsonResponse['linkedTickerNo2'] = intval($linkedTickerNo2);

			//====================Customer Info==================//
			$customername = $customeremail = $customerphone = $customeraddress = '';
			$customers_id = $available_credit = $cCustomFields = 0;
			$customerObj = $this->db->query("SELECT * FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = $customer_id", array());
			if($customerObj){
				$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);
				
				$cqueryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers'", array());
				if($cqueryObj){
					$cCustomFields = $cqueryObj->fetch(PDO::FETCH_OBJ)->totalrows;
				}
				$customers_id = $customerrow->customers_id;
				$company = $customerrow->company;
				$first_name = $customerrow->first_name;
				$last_name = $customerrow->last_name;
			
				$customername = $company;
				if($customername !=''){$customername .= ', ';}
				$customername .= $first_name;
				if($customername !=''){$customername .= ' ';}
				$customername .= $last_name;
						
				$customeremail = $customerrow->email;
				$customerphone = $customerrow->contact_no;
				$credit_limit = $customerrow->credit_limit;
				if($credit_limit>0){
					$availCreditData = $Common->calAvailCr($customers_id, $credit_limit, 1);
					if(array_key_exists('available_credit', $availCreditData)){
						$available_credit = round($availCreditData['available_credit'],2);
					}
				}
			}
			$jsonResponse['customers_id'] = intval($customers_id);
			$jsonResponse['cCustomFields'] = intval($cCustomFields);
			$jsonResponse['customeremail'] = $customeremail;
			$jsonResponse['customername'] = stripslashes($customername);
			$jsonResponse['customerphone'] = $customerphone;
			$jsonResponse['available_credit'] = round($available_credit,2);
			
			//==================Properties===============//
			$properties_id = $repairs_array->properties_id;
			$jsonResponse['properties_id'] = intval($properties_id);
			$brand = $model = $more_details = $imei_or_serial_no = '';
			if($properties_id>0){
				$propertiesObj = $this->db->query("SELECT * FROM properties WHERE properties_id = $properties_id AND accounts_id = $prod_cat_man AND properties_publish = 1", array());
				if($propertiesObj){
					$propertiesRow = $propertiesObj->fetch(PDO::FETCH_OBJ);			
					
					$imei_or_serial_no = stripslashes(trim((string) $propertiesRow->imei_or_serial_no));
					$brand_model_id = trim((string) $propertiesRow->brand_model_id);
					if($brand_model_id>0){
						$brandModelObj = $this->db->query("SELECT brand, model FROM brand_model WHERE brand_model_id = $brand_model_id AND accounts_id = $prod_cat_man", array());
						if($brandModelObj){
							$brandModelRow = $brandModelObj->fetch(PDO::FETCH_OBJ);	
							$brand = stripslashes($brandModelRow->brand);
							$model = stripslashes($brandModelRow->model);
						}
					}
					$more_details = stripslashes(trim((string) $propertiesRow->more_details));
				}
			}
			$jsonResponse['imei_or_serial_no'] = $imei_or_serial_no;
			$jsonResponse['brand'] = $brand;
			$jsonResponse['model'] = $model;
			$jsonResponse['more_details'] = $more_details;

			//=================Custom Fields for Repairs=============//
			$cusDataInfo = $Common->customViewInfo('repairs', $repairs_array->custom_data);	
			$jsonResponse['rCustomFields'] = $cusDataInfo[0];
			$jsonResponse['rCustomFieldsData'] = $cusDataInfo[1];

			//=================Repairs Form=============//			
			$formsCount = 0;
			$formsOptions = $showFormsData = array();
			$formsObj = $this->db->query("SELECT forms_id, form_name FROM forms WHERE accounts_id = $prod_cat_man AND form_for = 'repairs' AND forms_publish = 1 ORDER BY form_name ASC", array());
			if($formsObj){
				while($onefRow = $formsObj->fetch(PDO::FETCH_OBJ)){
					$formsOptions[$onefRow->forms_id] = stripslashes($onefRow->form_name);
					$formsCount++;
				}
			}
			$jsonResponse['formsCount'] = $formsCount;
			$jsonResponse['formsOptions'] = $formsOptions;
			if($formsCount>0){	
				$formsDataObj = $this->db->query("SELECT * FROM forms_data WHERE accounts_id = $accounts_id AND table_id = $repairs_id ORDER BY form_name ASC", array());
				if($formsDataObj){
					while($oneFDRow = $formsDataObj->fetch(PDO::FETCH_OBJ)){
						$last_updated = $oneFDRow->last_updated;
						
						$form_required = '';
						if($oneFDRow->required>0 && in_array($oneFDRow->last_updated, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
							$form_required = addslashes(stripslashes($oneFDRow->form_name));
						}
						$showFormsData[] = array('forms_data_id'=>intval($oneFDRow->forms_data_id), 'forms_id'=>intval($oneFDRow->forms_id), 'table_id'=>intval($oneFDRow->table_id), 'form_public'=>intval($oneFDRow->form_public), 
												'required'=>intval($oneFDRow->required), 'form_required'=>$form_required, 'form_name'=>stripslashes($oneFDRow->form_name), 'last_updated'=>$last_updated);
					}
				}
			}
			$jsonResponse['showFormsData'] = $showFormsData;

			//=================Repairs Information====================//
			$jsonResponse['problem'] = stripslashes($repairs_array->problem);
			$jsonResponse['due_datetime'] = $repairs_array->due_datetime;
			$jsonResponse['due_time'] = $repairs_array->due_time;
			$jsonResponse['notify_email'] = $repairs_array->notify_email;
			$jsonResponse['notify_sms'] = $repairs_array->notify_sms;
			$jsonResponse['notify_how'] = intval($repairs_array->notify_how);
			$jsonResponse['lock_password'] = stripslashes($repairs_array->lock_password);
			$jsonResponse['bin_location'] = stripslashes($repairs_array->bin_location);

			$technicianName = '';
			$userObj = $this->db->querypagination("SELECT user_first_name, user_last_name FROM user WHERE user_id = $repairs_array->assign_to LIMIT 0,1", array());
			if($userObj){
				foreach($userObj as $rowuser){
					$technicianName = trim(stripslashes("$rowuser[user_first_name] $rowuser[user_last_name]"));
				}
			}
			$jsonResponse['technicianName'] = $technicianName;

			$salesmanName = '';
			if($posObj){
				$userObj = $this->db->querypagination("SELECT user_first_name, user_last_name FROM user WHERE user_id = $pos_onerow->employee_id LIMIT 0,1", array());
				if($userObj){
					foreach($userObj as $rowuser){
						$salesmanName = trim(stripslashes("$rowuser[user_first_name] $rowuser[user_last_name]"));
					}
				}
			}
			$jsonResponse['salesmanName'] = $salesmanName;
			
			//======================For Status ('Invoiced','Cancelled')===============//
			if(in_array($status, array('Invoiced','Cancelled'))){
				
				$cartData = array();
				$taxable_total = $nontaxable_total = 0.00;						
				$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
				$query = $this->db->query($sqlquery, array());
				if($query){
					$i=0;
					while($row = $query->fetch(PDO::FETCH_OBJ)){
						$i++;
						$pos_cart_id = $row->pos_cart_id;
						$item_id = $row->item_id;
						$item_type = $row->item_type;
						$description = stripslashes(trim((string) $row->description));
						if($item_type =='one_time'){$description .= " [1]";}
						
						$add_description = stripslashes(trim((string) $row->add_description));
						if($add_description !=''){
							$description .= "<p>".nl2br($add_description)."</p>";
						}
						
						$require_serial_no = $row->require_serial_no;
						$newimei_info = '';										
						if($item_type=='livestocks'){
							$sqlitem = "SELECT item.item_number, item.carrier_name, pos_cart_item.sale_or_refund, pos_cart_item.return_pos_cart_id FROM item, pos_cart_item WHERE item.accounts_id = $accounts_id AND item.item_id = pos_cart_item.item_id AND pos_cart_item.pos_cart_id = $pos_cart_id";
							$itemquery = $this->db->query($sqlitem, array());
							if($itemquery){
								while($newitem_row = $itemquery->fetch(PDO::FETCH_OBJ)){
									$imei_info = $newitem_row->item_number;
									$description = str_replace("$newitem_row->item_number", '',$description);
									
									$carrier_name = $newitem_row->carrier_name;
									if($carrier_name !=''){
										$imei_info .= ' '.$carrier_name;
									}
									
									$sale_or_refund = $newitem_row->sale_or_refund;
									if($sale_or_refund==0){
										$imei_info .= ' (Refund)';
									}
									$return_qtystr = '';
									if($newitem_row->return_pos_cart_id >0 ){
										$return_qtystr = "<span class=\"padding6 mleft15 bgblack\">Refunded</span>";
									}
									
									if($imei_info !=''){
										$newimei_info .= "<p>$imei_info $return_qtystr</p>";
									}                                                    
								}
							}
						}
						elseif($item_type=='product' && $require_serial_no>0){
							$newimei_info .= $Carts->getSerialInfo($pos_cart_id);										
						}
						
						$sales_price = round($row->sales_price,2);
						
						$qty = floatval($row->qty);
						$shipping_qty = floatval($row->shipping_qty);
						$return_qty = floatval($row->return_qty);
						
						$total =round($sales_price* $shipping_qty,2);
						$discount_is_percent = $row->discount_is_percent;
						$discount = $row->discount;
						if($discount_is_percent>0){
							$discount_value = round($total*0.01*$discount,2);
						}
						else{ 
							$discount_value = round($discount* $shipping_qty,2);
						}
												
						$taxable = $row->taxable;																		
						if($taxable>0){
							$taxable_total = $taxable_total+$total-$discount_value;
						}
						else{
							$nontaxable_total = $nontaxable_total+$total-$discount_value;
						}
						
						$cartData[] = array('pos_cart_id'=>intval($pos_cart_id), 'i'=>$i, 'description'=>$description, 'newimei_info'=>$newimei_info, 'shipping_qty'=>floatval($shipping_qty), 
										'return_qty'=>floatval($return_qty), 'sales_price'=>round($sales_price,2), 'total'=>round($total,2), 'discount_value'=>round($discount_value,2));
					}
				}
				$jsonResponse['cartData'] = $cartData;
				
				$jsonResponse['posObj'] = 0;
				$taxes_name1 = '';
				if($posObj){
					$jsonResponse['posObj'] = 1;

					
					//----------------------------//				
					$jsonResponse['taxable_total'] = round($taxable_total,2);
					$jsonResponse['taxes_name1'] = $pos_onerow->taxes_name1;			
					$jsonResponse['tax_inclusive1'] = $tax_inclusive1 = intval($pos_onerow->tax_inclusive1);
					$jsonResponse['taxes_percentage1'] = $taxes_percentage1 = floatval($pos_onerow->taxes_percentage1);
					$taxes_total1 = 0;
					if($pos_onerow->taxes_name1 !=''){
						$taxes_total1 = $Common->calculateTax($taxable_total, $taxes_percentage1, $tax_inclusive1);
					}
					$jsonResponse['taxes_total1'] = round($taxes_total1,2);

					$jsonResponse['taxes_name2'] = $pos_onerow->taxes_name2;			
					$jsonResponse['tax_inclusive2'] = $tax_inclusive2 = intval($pos_onerow->tax_inclusive2);
					$jsonResponse['taxes_percentage2'] = $taxes_percentage2 = floatval($pos_onerow->taxes_percentage2);
					$taxes_total2 = 0;
					if($pos_onerow->taxes_name2 !=''){
						$taxes_total2 = $Common->calculateTax($taxable_total, $taxes_percentage2, $tax_inclusive2);
					}
					$jsonResponse['taxes_total2'] = round($taxes_total2,2);
					$jsonResponse['nontaxable_total'] = round($nontaxable_total,2);
					//-------------------------------//

					if($pos_onerow->tax_inclusive1>0){$taxes_total1 = 0;}
					if($pos_onerow->tax_inclusive2>0){$taxes_total2 = 0;}
					$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;

					$totalpayment = 0;
					$paymentData = array();
					$ppSql = "SELECT * FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' ORDER BY pos_payment_id ASC";
					$paymentObj = $this->db->query($ppSql, array());
					if($paymentObj){
						while($onerow = $paymentObj->fetch(PDO::FETCH_OBJ)){
							$payment_amount = round($onerow->payment_amount,2);
										
							$totalpayment += $payment_amount;
							$paymentData[] = array($onerow->payment_datetime, $onerow->payment_method, $payment_amount);
						}
					}
					$jsonResponse['totalpayment'] = round($totalpayment,2);
					$jsonResponse['paymentData'] = $paymentData;
				}				
			}
			else{
				$startNotifyPopup = 0;
				if($jsonResponse['notify_how']>0 && $status=='New'){
					$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'notifications'", array());
					if($varObj){
						$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
						if(!empty($value)){
							$value = unserialize($value);
							if(array_key_exists($status, $value)){
								$startNotifyPopup++;
							}
						}
					}
				}
				$jsonResponse['startNotifyPopup'] = $startNotifyPopup;

				$pSubPermission = array();
				if(!empty($_SESSION["allowed"]) && array_key_exists(2, $_SESSION["allowed"])) {
					$pSubPermission = $_SESSION["allowed"][2];
				}
				$jsonResponse['pSubPermission'] = $pSubPermission;

				$pCustomFields = 0;
				$queryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'product'", array());
				if($queryObj){
					$pCustomFields = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
				}
				$jsonResponse['pCustomFields'] = intval($pCustomFields);

				$cartsData = $Carts->loadCartData('Repairs', $pos_id);
				$jsonResponse['cartsData'] = $cartsData;

				$taxesRowCount = $defaultTaxCount = $olddefaultTaxCount = $taxesRowCount1 = 0;
				$taxes_name1 = $taxes_name2 = '';
				$taxesOption1 = $taxesOption2 = array();
				$option1Val = $option2Val = 0;
				$taxableTotalDisplay = 1;
				$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
				if($posObj){
					$taxes_name1 = $pos_onerow->taxes_name1;
					if(!empty($taxes_name1)){$olddefaultTaxCount++;}
					$taxes_percentage1 = $pos_onerow->taxes_percentage1;
					$tax_inclusive1 = $pos_onerow->tax_inclusive1;
					$taxes_name2 = $pos_onerow->taxes_name2;
					if(!empty($taxes_name2)){$olddefaultTaxCount++;}
					$taxes_percentage2 = $pos_onerow->taxes_percentage2;
					$tax_inclusive2 = $pos_onerow->tax_inclusive2;
				}
				
				$taxesObj = $this->db->query("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND taxes_publish = 1 ORDER BY taxes_name ASC", array());
				if($taxesObj){
					$taxesRowCount = $taxesObj->rowCount();
					while($taxesonerow = $taxesObj->fetch(PDO::FETCH_OBJ)){                                            
						$taxes_id = $taxesonerow->taxes_id;
						$staxes_name = $taxesonerow->taxes_name;
						$staxes_percentage = floatval($taxesonerow->taxes_percentage);
						$stax_inclusive = intval($taxesonerow->tax_inclusive);
						
						$default_tax = $taxesonerow->default_tax;
						if($default_tax>0){
							$defaultTaxCount++;
						}
						$selected1 = '';
						$selected2 = '';
						if(($taxes_name1=='' && $defaultTaxCount==1) || ($taxes_name1=='' && $taxesRowCount1==0)){
							$taxesRowCount1++;
							$taxes_name1 = $staxes_name;
							$taxes_percentage1 = $staxes_percentage;
							$tax_inclusive1 = $stax_inclusive;				
						}
						
						if($taxes_name2=='' && $defaultTaxCount==2){
							$taxes_name2 = $staxes_name;
							$taxes_percentage2 = $staxes_percentage;
							$tax_inclusive2 = $stax_inclusive;
						}
						
						if(strcmp($taxes_name1, $staxes_name)==0){							
							$option1Val = $taxes_id;
						}
						if(strcmp($taxes_name2, $staxes_name)==0){
							$option2Val = $taxes_id;
						}
						
						$tiStr = '';
						if($stax_inclusive>0){$tiStr = ' Inclusive';}
						
						$taxesOption1[$taxes_id] = "$staxes_name ($staxes_percentage%$tiStr)";
						$taxesOption2[$taxes_id] = "$staxes_name ($staxes_percentage%$tiStr)";
					}
				}
				else{
					$taxableTotalDisplay = 0;
				}
				if($defaultTaxCount<$olddefaultTaxCount){$defaultTaxCount = $olddefaultTaxCount;}
				
				$jsonResponse['taxableTotalDisplay'] = $taxableTotalDisplay;
				$jsonResponse['option1Val'] = intval($option1Val);
				$jsonResponse['option2Val'] = intval($option2Val);
				$jsonResponse['taxesOption1'] = $taxesOption1;
				$jsonResponse['taxesOption2'] = $taxesOption2;
				$jsonResponse['taxesRowCount'] = intval($taxesRowCount);
				$jsonResponse['taxes_name1'] = $taxes_name1;
				$jsonResponse['tax_inclusive1'] = intval($tax_inclusive1);
				$jsonResponse['taxes_percentage1'] = floatval($taxes_percentage1);
				$jsonResponse['defaultTaxCount'] = $defaultTaxCount;
				$tax1 = '';
				if($defaultTaxCount>1){$tax1 = '1';}
				$jsonResponse['tax1'] = $tax1;
				$jsonResponse['taxes_name2'] = $taxes_name2;
				$jsonResponse['tax_inclusive2'] = intval($tax_inclusive2);
				$jsonResponse['taxes_percentage2'] = floatval($taxes_percentage2);

				//====================Payment Options================//
				$jsonResponse['paymentData'] = $Payments->loadPOSPayment('Repairs', $pos_id);
				$jsonResponse['payment_datetime'] = date('Y-m-d H:i:s');
				
				$methodOpts = array();
				$vData = $Common->variablesData('payment_options', $accounts_id);
				if(!empty($vData)){
					extract($vData);
					$methodOpts = explode('||',$payment_options);
				}
				$methodOpt = array();
				if(!empty($methodOpts)){
					foreach($methodOpts as $onePayOption){
						$onePayOption = trim((string) $onePayOption);
						if(!empty($onePayOption)){
							$methodOpt[$onePayOption] = '';
						}
					}
					if(!empty($methodOpt)){$methodOpt = array_keys($methodOpt);}					
				}
				$jsonResponse['methodOpt'] = $methodOpt;
				
				$multiple_cash_drawers = 0;
				$casDraOpts = array();
				$cdData = $Common->variablesData('multiple_drawers', $accounts_id);
				if(!empty($cdData)){
					$cash_drawers = '';
					extract($cdData);
					$casDraOpts = explode('||',$cash_drawers);
				}
				$drawer = $_COOKIE['drawer']??'';
				$drawerOpt = array();
				if($multiple_cash_drawers>0 && !empty($casDraOpts)){
					foreach($casDraOpts as $oneCDOption){
						$oneCDOption = trim((string) $oneCDOption);
						if(!empty($oneCDOption)){
							$drawerOpt[addslashes(stripslashes($oneCDOption))] = '';
						}
					}
					if(!empty($drawerOpt)){$drawerOpt = array_keys($drawerOpt);}
				}
				else{
					$multiple_cash_drawers = 0;
				}
				$jsonResponse['drawer'] = $drawer;
				$jsonResponse['multiple_cash_drawers'] = $multiple_cash_drawers;
				$jsonResponse['drawerOpt'] = $drawerOpt;

				$sqrup_currency_code = '';
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'cr_card_processing' AND value !=''", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						extract($value);
					}
				}
				$jsonResponse['sqrup_currency_code'] = $sqrup_currency_code;

				$webcallbackurl = '';
				if(OUR_DOMAINNAME=='machousel.com.bd'){$webcallbackurl = 'demo.';}
				$webcallbackurl .= OUR_DOMAINNAME;
				$jsonResponse['webcallbackurl'] = $webcallbackurl;
				$jsonResponse['accounts_id'] = intval($accounts_id);
				$jsonResponse['user_id'] = intval($user_id);
				$avaCreRowSty = 1;
				$ashbdclass = '';
				if($available_credit==0){
					$avaCreRowSty = 0;
					$ashbdclass = 'bgtitle';
				}
				$jsonResponse['avaCreRowSty'] = $avaCreRowSty;
				$jsonResponse['ashbdclass'] = $ashbdclass;
				$jsonResponse['pos_id'] = intval($pos_id);
				
				$default_invoice_printer = 'Small';
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(is_array($value) && array_key_exists('default_invoice_printer', $value)){
							$default_invoice_printer = $value['default_invoice_printer'];
							if($default_invoice_printer=='' || is_null($default_invoice_printer)){$default_invoice_printer = 'Small';}
						}
					}
				}
				$jsonResponse['default_invoice_printer'] = $default_invoice_printer;

			}
		}
		else{
			$jsonResponse['login'] = 'Repairs/lists/';
		}

		return json_encode($jsonResponse);
	}
	
	public function edit($segment4name){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$repairs_id = $segment4name;
		$repairsObj = $this->db->query("SELECT repairs_id FROM repairs WHERE repairs_id = :repairs_id AND accounts_id = $accounts_id", array('repairs_id'=>$repairs_id),1);
		if(!$repairsObj){
			$listspage = '/Repairs/lists/';
			return "<meta http-equiv = \"refresh\" content = \"0; url = $listspage\" />";
		}
	}
		
	private function filterHAndOptions(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$repairs_id = $this->repairs_id;
		$shistory_type = $this->history_type;
		
		$filterSql = '';
		$bindData = array();
		$bindData['repairs_id'] = $repairs_id;
		$pos_id = 0;
		$repairsObj = $this->db->query("SELECT pos_id FROM repairs WHERE repairs_id = $repairs_id AND accounts_id = $accounts_id", array());
		if($repairsObj){
			$pos_id = $repairsObj->fetch(PDO::FETCH_OBJ)->pos_id;
		}
		
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Repairs Created')==0){
				$filterSql = "SELECT COUNT(repairs_id) AS totalrows FROM repairs 
							WHERE repairs_id = :repairs_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'repairs' AND table_id = :repairs_id";
			}
			elseif(strcmp($shistory_type, 'Signature Created')==0){
				$filterSql = "SELECT COUNT(digital_signature_id) AS totalrows FROM digital_signature 
						WHERE accounts_id = $accounts_id AND for_table = 'repairs' AND table_id = :repairs_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$filterSql = "SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND ((record_for ='repairs' AND record_id = :repairs_id) OR (record_for = 'pos' AND record_id = $pos_id))";
			}
			else{
				$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'repairs' AND activity_feed_link = CONCAT('/Repairs/edit/', :repairs_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
		}
		else{			
			$filterSql = "SELECT COUNT(activity_feed_id) AS totalrows FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'repairs' AND activity_feed_link = CONCAT('/Repairs/edit/', :repairs_id) 
					UNION ALL 
						SELECT COUNT(repairs_id) AS totalrows FROM repairs 
							WHERE repairs_id = :repairs_id and accounts_id = $accounts_id 
					UNION ALL 
						SELECT COUNT(notes_id) AS totalrows FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'repairs' AND table_id = :repairs_id 
					UNION ALL 
						SELECT COUNT(track_edits_id) AS totalrows FROM track_edits 
						WHERE accounts_id = $accounts_id AND ((record_for ='repairs' AND record_id = :repairs_id) OR (record_for = 'pos' AND record_id = $pos_id)) 
					UNION ALL 
						SELECT COUNT(digital_signature_id) AS totalrows FROM digital_signature 
						WHERE accounts_id = $accounts_id AND for_table = 'repairs' AND table_id = :repairs_id";
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
			WHERE accounts_id = $accounts_id AND uri_table_name = 'repairs' AND activity_feed_link = CONCAT('/Repairs/edit/', :repairs_id) 
		UNION ALL 
			SELECT 'Repairs Created' AS afTitle FROM repairs 
				WHERE repairs_id = :repairs_id and accounts_id = $accounts_id 
		UNION ALL 
			SELECT 'Notes Created' AS afTitle FROM notes 
			WHERE accounts_id = $accounts_id AND note_for = 'repairs' AND table_id = :repairs_id 
		UNION ALL 
			SELECT 'Track Edits' AS afTitle FROM track_edits 
			WHERE accounts_id = $accounts_id AND ((record_for ='repairs' AND record_id = :repairs_id) OR (record_for = 'pos' AND record_id = $pos_id)) 
		UNION ALL 
			SELECT 'Signature Created' AS afTitle FROM digital_signature 
			WHERE accounts_id = $accounts_id AND for_table = 'repairs' AND table_id = :repairs_id";
		$tableObj = $this->db->query($Sql, array('repairs_id'=>$repairs_id));
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
		$repairs_id = $this->repairs_id;
		$shistory_type = $this->history_type;
	
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';
		
		$bindData = array();
		$bindData['repairs_id'] = $repairs_id;            
		if($shistory_type !=''){
			if(strcmp($shistory_type, 'Repairs Created')==0){
				$filterSql = "SELECT 'repairs' AS tablename, created_on AS tabledate, repairs_id AS table_id, 'Repairs Created' AS activity_feed_title FROM repairs 
					WHERE repairs_id = :repairs_id and accounts_id = $accounts_id";
			}
			elseif(strcmp($shistory_type, 'Notes Created')==0){
				$filterSql = "SELECT 'notes' AS tablename, created_on AS tabledate, notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
							WHERE accounts_id = $accounts_id AND note_for = 'repairs' AND table_id = :repairs_id";
			}
			elseif(strcmp($shistory_type, 'Signature Created')==0){
				$filterSql = "SELECT 'digital_signature' AS tablename, created_on AS tabledate, digital_signature_id AS table_id, 'Signature Created' AS activity_feed_title FROM digital_signature 
							WHERE accounts_id = $accounts_id AND for_table = 'repairs' AND table_id = :repairs_id";
			}
			elseif(strcmp($shistory_type, 'Track Edits')==0){
				$pos_id = 0;
				$repairsObj = $this->db->query("SELECT pos_id FROM repairs WHERE repairs_id = $repairs_id AND accounts_id = $accounts_id", array());
				if($repairsObj){
					$pos_id = $repairsObj->fetch(PDO::FETCH_OBJ)->pos_id;
				}
				
				$filterSql = "SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
							WHERE accounts_id = $accounts_id AND ((record_for ='repairs' AND record_id = :repairs_id) OR (record_for = 'pos' AND record_id = $pos_id))";
			}
			else{
				$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
							WHERE accounts_id = $accounts_id AND uri_table_name = 'repairs' AND activity_feed_link = CONCAT('/Repairs/edit/', :repairs_id)";
				$filterSql .= " AND activity_feed_title = :shistory_type";
				$bindData['shistory_type'] = $shistory_type;
			}
			$filterSql .= " ORDER BY tabledate DESC LIMIT $starting_val, $limit";
		}
		else{
			$pos_id = 0;
			$repairsObj = $this->db->query("SELECT pos_id FROM repairs WHERE repairs_id = $repairs_id AND accounts_id = $accounts_id", array());
			if($repairsObj){
				$pos_id = $repairsObj->fetch(PDO::FETCH_OBJ)->pos_id;
			}
			
			$filterSql = "SELECT 'activity_feed' AS tablename, created_on AS tabledate, activity_feed_id AS table_id, activity_feed_title FROM activity_feed 
						WHERE accounts_id = $accounts_id AND uri_table_name = 'repairs' AND activity_feed_link = CONCAT('/Repairs/edit/', :repairs_id)  
					UNION ALL 
					SELECT 'repairs' AS tablename, created_on AS tabledate, repairs_id AS table_id, 'Repairs Created' AS activity_feed_title FROM repairs 
						WHERE repairs_id = :repairs_id AND accounts_id = $accounts_id 
					UNION ALL 
					SELECT 'notes' AS tablename, created_on AS tabledate,  notes_id AS table_id, 'Notes Created' AS activity_feed_title FROM notes 
						WHERE accounts_id = $accounts_id AND note_for = 'repairs' AND table_id = :repairs_id 
					UNION ALL 
					SELECT 'track_edits' AS tablename, created_on AS tabledate, track_edits_id AS table_id, 'Track Edits' AS activity_feed_title FROM track_edits 
						WHERE accounts_id = $accounts_id AND ((record_for ='repairs' AND record_id = :repairs_id) OR (record_for = 'pos' AND record_id = $pos_id)) 
					UNION ALL 
					SELECT 'digital_signature' AS tablename, created_on AS tabledate, digital_signature_id AS table_id, 'Signature Created' AS activity_feed_title FROM digital_signature 
						WHERE accounts_id = $accounts_id AND for_table = 'repairs' AND table_id = :repairs_id 
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
		$srepairs_id = intval($POST['srepairs_id']??0);	
		$shistory_type = $POST['shistory_type']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->repairs_id = $srepairs_id;
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
	
	public function saveandshowstatus(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$repairs_id = intval($POST['repairs_id']??0);		
		$status = $POST['status']??'';
		$pos_id = 0;
		
		$action = '';
		if($repairs_id>0 && $status !=''){
			$oldStatus = '';
			$oneRRowObj = $this->db->querypagination("SELECT status, pos_id FROM repairs WHERE repairs_id = $repairs_id", array());
			if($oneRRowObj){
				$oldStatus = $oneRRowObj[0]['status'];
				$pos_id = $oneRRowObj[0]['pos_id'];
			}
			if(!in_array($oldStatus, array('Invoiced', 'Cancelled'))){
				$status = $this->db->checkCharLen('repairs.status', $status);
				$update = $this->db->update('repairs', array('status'=>$status, 'last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
				if($update){			
					$user_id = $_SESSION["user_id"]??0;
					$prod_cat_man = $_SESSION["prod_cat_man"]??0;		
					$user_name = '';
					$userObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $user_id", array());
					if($userObj){
						$userrow = $userObj->fetch(PDO::FETCH_OBJ);
						$user_name = trim(stripslashes("$userrow->user_first_name $userrow->user_last_name"));
					}
					$note_for = $this->db->checkCharLen('notes.note_for', 'repairs');
					
					$changed = array();
					$changed['status'] = array($oldStatus, $status);
					if(!empty($changed)){
						$moreInfo = $teData = array();
						$teData['created_on'] = date('Y-m-d H:i:s');
						$teData['accounts_id'] = $_SESSION["accounts_id"];
						$teData['user_id'] = $_SESSION["user_id"];
						$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'repairs');
						$teData['record_id'] = $repairs_id;
						$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
						$this->db->insert('track_edits', $teData);							
					}

					//===============Update Inventory==============//
					$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_id = $pos_id ORDER BY pos_cart_id ASC", array());
					if($pos_cartObj){
						while($pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
							$pos_cart_id = $pos_cartrow->pos_cart_id;
							$product_id = $pos_cartrow->item_id;
							
							$productObj = $this->db->query("SELECT product_type FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man", array());
							if($productObj){
								$product_type = $productObj->fetch(PDO::FETCH_OBJ)->product_type;
								
								$qty = $pos_cartrow->qty;
								if($pos_cartrow->item_type =='livestocks'){
									$qty = $pos_cartrow->shipping_qty;
								}
								elseif($pos_cartrow->item_type=='product' && $pos_cartrow->require_serial_no>0){
									$snObj = $this->db->query("SELECT count(serial_number_id) as counttotalrows FROM serial_number WHERE pos_cart_id = :pos_cart_id", array('pos_cart_id'=>$pos_cart_id),1);
									if($snObj){$qty = $snObj->fetch(PDO::FETCH_OBJ)->counttotalrows;}
								}
								
								$oldshipping_qty = $pos_cartrow->shipping_qty;

								if($pos_cartrow->item_type =='livestocks'){
									$shipping_qty = $qty;
									$pos_cart_itemObj2 = $this->db->query("SELECT count(pos_cart_item_id) AS totalShipQty FROM pos_cart_item WHERE pos_cart_id = $pos_cart_id AND return_pos_cart_id = 0", array());
									if($pos_cart_itemObj2){
										$shipping_qty = $pos_cart_itemObj2->fetch(PDO::FETCH_OBJ)->totalShipQty;
									}
								}
								else{
									$shipping_qty = 0;
									if($status=='Finished'){
										$shipping_qty = $qty;
									}
								}

								$pcUpdate = $this->db->update('pos_cart', array('shipping_qty'=>$shipping_qty), $pos_cart_id);
								if($pcUpdate){
									$additionalQty = -($shipping_qty-$oldshipping_qty);
									if($additionalQty !=0){
										$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE product_id = $product_id AND accounts_id = $accounts_id", array());
										if($inventoryObj){
											$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
											$current_inventory = $inventoryrow->current_inventory;
											if($pos_cartrow->item_type !='livestocks'){
												$newcurrent_inventory = floor($current_inventory+$additionalQty);
												$this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventoryrow->inventory_id);
											}
										}
									}
								}
							}
						}
					}
					
					$action = 'Changed';
				}
			}
			else{
				$action = 'This ticket is already '.$oldStatus.'. You could not change this status.';
			}
		}
		$cartsData = array();
		if($action == 'Changed' && $pos_id>0){
			$Carts = new Carts($this->db);
			$cartsData = $Carts->loadCartData('Repairs', $pos_id);
		}
		return json_encode(array('login'=>'', 'action'=>$action, 'cartsData'=>$cartsData));
	}
	
	public function AJ_changestatuscancelled(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;

		$pos_id = intval($POST['pos_id']??0);
		$repairs_id = intval($POST['repairs_id']??0);
		$status = 'Cancelled';
		$returnval = 'error';
				
		if($repairs_id>0){
			$oldStatus = '';
			$repairObj = $this->db->query("SELECT status FROM repairs WHERE repairs_id = :repairs_id", array('repairs_id'=>$repairs_id),1);
			if($repairObj){
				$oldStatus = $repairObj->fetch(PDO::FETCH_OBJ)->status;
			}
			$status = $this->db->checkCharLen('repairs.status', $status);
			$update = $this->db->update('repairs', array('status'=>$status, 'last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
			if($update){
			
				$user_id = $_SESSION["user_id"]??0;
				$accounts_id = $_SESSION["accounts_id"]??0;		
				$user_name = '';
				$userObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $user_id", array());
				if($userObj){
					$userrow = $userObj->fetch(PDO::FETCH_OBJ);
					$user_name = trim(stripslashes("$userrow->user_first_name $userrow->user_last_name"));
				}
				
				$changed = array();
				$changed['status'] = array($oldStatus, $status);
				if(!empty($changed)){
					$moreInfo = $teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'repairs');
					$teData['record_id'] = $repairs_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);							
				}
				
				$returnval = 'Ok';
				
				$invoice_no = 0;
				$posObj = $this->db->query("SELECT invoice_no FROM pos WHERE pos_id = :pos_id", array('pos_id'=>$pos_id),1);
				if($posObj){
					$invoice_no = $posObj->fetch(PDO::FETCH_OBJ)->invoice_no;
				}
				
				$pos_cartObj = $this->db->query("SELECT * FROM pos_cart WHERE pos_id = :pos_id ORDER BY pos_cart_id ASC", array('pos_id'=>$pos_id),1);
				if($pos_cartObj){
					while($pos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
						$pos_cart_id = $pos_cartrow->pos_cart_id;
						$product_id = $pos_cartrow->item_id;
						$shipping_qty = $pos_cartrow->shipping_qty;
						$productObj = $this->db->query("SELECT product_type FROM product WHERE product_id = $product_id AND accounts_id = $prod_cat_man", array());
						if($productObj){
							$product_type = $productObj->fetch(PDO::FETCH_OBJ)->product_type;
							
							$inventoryObj = $this->db->query("SELECT inventory_id, current_inventory FROM inventory WHERE product_id = $pos_cartrow->item_id AND accounts_id = $accounts_id", array());
							if($inventoryObj){
								$inventoryrow = $inventoryObj->fetch(PDO::FETCH_OBJ);
								
								$current_inventory = $inventoryrow->current_inventory;
							
								if($product_type !='Live Stocks'){
									$newcurrent_inventory = floor($current_inventory+$shipping_qty);
									$updateproduct = $this->db->update('inventory', array('current_inventory'=>$newcurrent_inventory), $inventoryrow->inventory_id);			
								}
							}
						}
					}
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnval));
	}
	
	public function save_repairs(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$id = 0;
		$savemsg = $message = '';			
		$sales_datetime = $created_on = $last_updated = $payment_datetime = date('Y-m-d H:i:s');
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$repair_statusesarray = array('Assigned','On Hold','Waiting on Customer','Waiting for Parts');
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'repairs_setup'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;					
			if(!empty($value)){
				$value = unserialize($value);
				extract($value);
				if($repair_statuses !=''){
					$repair_statusesarray = explode('||',$repair_statuses);
				}
			}
		}
		
		$status ='';
		$customer_id = intval($POST['customer_id']??0);
		$customer_name = addslashes($POST['customer_name']??'');
		if($customer_name !=''){
			
			if($customer_id>0){
				$customerObj = $this->db->query("SELECT first_name, last_name, company, email, contact_no FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = :customer_id", array('customer_id'=>$customer_id),1);
				if($customerObj){
					$onecustomerrow = $customerObj->fetch(PDO::FETCH_OBJ);
					$name = trim((string) stripslashes($onecustomerrow->company));
					$email = trim((string) stripslashes($onecustomerrow->email));
					$contact_no = trim((string) stripslashes($onecustomerrow->contact_no));
					$first_name = trim((string) stripslashes($onecustomerrow->first_name));
					if($name !=''){$name .= ', ';}
					$name .= $first_name;
					$last_name = trim((string) stripslashes($onecustomerrow->last_name));
					if($name !=''){$name .= ' ';}
					$name .= $last_name;
					
					if($email !=''){
						$name .= " ($email)";
					}
					elseif($contact_no !=''){
						$name .= " ($contact_no)";
					}
					
					if(stripslashes($customer_name) !="$name"){
						$customer_id = 0;
					}
				}
			}
			
			if($customer_id==0 && $customer_name != ''){
				$autocustomer_name = $customer_name;
				$email = '';
				if(strpos($customer_name, ' (') !== false) {
					$scustomerexplode = explode(' (', $customer_name);
					if(count($scustomerexplode)>1){
						$autocustomer_name = trim((string) $scustomerexplode[0]);
						$email = str_replace(')', '', $scustomerexplode[1]);
					}
				}
				
				$bindData = array();
				$autocustomer_name = addslashes($autocustomer_name);
				$strextra = " AND CONCAT(company, ', ', first_name, ' ', last_name) LIKE CONCAT('%', :autocustomer_name, '%')";
				$bindData['autocustomer_name'] = $autocustomer_name;
				if($email !=''){
					$strextra .= " AND (email = :email OR contact_no = :email)";
					$bindData['email'] = $email;
				}
				
				$strextra .= " GROUP BY TRIM(CONCAT_WS(' ', first_name, last_name)), email";
				$sqlquery = "SELECT customers_id FROM customers WHERE accounts_id = $prod_cat_man $strextra LIMIT 0, 1";
				$query = $this->db->querypagination($sqlquery, $bindData);
				if($query){						
					foreach($query as $onegrouprow){
						$customer_id = $onegrouprow['customers_id'];
					}						
				}
			}
		}
				
		if($customer_id==0){
			$savemsg = 'error';
			$message .= 'noCustomerName';
		}
		else{
			$status = 'Trial';
			if(isset($_SESSION["status"])){$status = $_SESSION["status"];}
			if(in_array($status, array('SUSPENDED', 'CANCELED'))){
				return json_encode(array('login'=>'session_ended'));
			}
			
			$user_id = $_SESSION["user_id"]??0;
			
			$customer_devices = intval($POST['customer_devices']??0);
			
			$due_datetime = '1000-01-01';
			if(strlen($POST['due_datetime'])==10){
				$due_datetime = date('Y-m-d', strtotime($POST['due_datetime']));
			}
			$due_time = $POST['due_time']??'';
			if($due_time === NULL || is_null($due_time)) {$due_time = '';}
			$due_time = $this->db->checkCharLen('repairs.due_time', $due_time);
			
			$problem = addslashes(trim((string) $POST['problem']??''));
			$problem_name = addslashes(trim((string) $POST['problem_name']??''));
			if(empty($problem) && !empty($problem_name)){$problem = $problem_name;}
			$problem = $this->db->checkCharLen('repair_problems.name', $problem);
			
			if($problem !=''){
				$totalrows = 0;
				$queryProblemObj = $this->db->query("SELECT name FROM repair_problems WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name", array('name'=>strtoupper($problem)));
				if($queryProblemObj){
					while($oneRowRP = $queryProblemObj->fetch(PDO::FETCH_OBJ)){
						$problem = trim((string) $oneRowRP->name);
					}					
				}
				else{
					$rpData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $prod_cat_man,
									'user_id' => $_SESSION["user_id"],
									'name' => $problem,
									'additional_disclaimer'=>'');
					$this->db->insert('repair_problems', $rpData);						
				}
			}
			
			$properties_id = $customer_devices;
			$lock_password = $POST['lock_password']??'';
			$lock_password = $this->db->checkCharLen('repairs.lock_password', $lock_password);
			
			$bin_location = $POST['bin_location']??'';	
			$bin_location = $this->db->checkCharLen('repairs.bin_location', $bin_location);
			$user_id = $_SESSION["user_id"]??0;
			$accounts_id = $_SESSION["accounts_id"]??0;
			
			//==================POS Insert==================//
			$taxes_name1 = $taxes_name2 = '';
			$taxes_percentage1 = $tax_inclusive1 = $taxes_percentage2 = $tax_inclusive2 = 0;
			
			$taxesObj = $this->db->query("SELECT * FROM taxes WHERE accounts_id = $accounts_id AND taxes_publish = 1 ORDER BY taxes_name ASC", array());
			if($taxesObj){
				$defaultTaxCount = 0;
				while($taxesonerow = $taxesObj->fetch(PDO::FETCH_OBJ)){
					
					$staxes_name = $taxesonerow->taxes_name;
					$staxes_percentage = $taxesonerow->taxes_percentage;							
					$default_tax = $taxesonerow->default_tax;
					$tax_inclusive = $taxesonerow->tax_inclusive;
					if($taxes_name1==''){
						$taxes_name1 = $staxes_name;
						$taxes_percentage1 = $staxes_percentage;
						$tax_inclusive1 = $tax_inclusive;
					}
					
					if($default_tax>0){
						$defaultTaxCount++;
						if($defaultTaxCount==1){
							$taxes_name1 = $staxes_name;
							$taxes_percentage1 = $staxes_percentage;
							$tax_inclusive1 = $tax_inclusive;
						}
						
						if($defaultTaxCount==2){
							$taxes_name2 = $staxes_name;
							$taxes_percentage2 = $staxes_percentage;
							$tax_inclusive2 = $tax_inclusive;
						}
					}
				}
			}
			
			$posData = array('invoice_no' => 0, 
							'sales_datetime' => $sales_datetime,
							'employee_id' => $_SESSION["user_id"],
							'customer_id' => $customer_id, 
							'taxes_name1' => $taxes_name1,
							'taxes_percentage1' => $taxes_percentage1,
							'tax_inclusive1' => $tax_inclusive1,
							'taxes_name2' => $taxes_name2,
							'taxes_percentage2' => $taxes_percentage2,
							'tax_inclusive2' => $tax_inclusive2,
							'pos_type' => 'Repairs',
							'created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'user_id' => $user_id, 
							'accounts_id' => $accounts_id, 
							'credit_days' => 0, 
							'is_due' => 0, 
							'status' => 'New');											
			$pos_id = $this->db->insert('pos', $posData);
			
			//=============Repairs Insert==================//
			
			if(array_key_exists('startEstimate', $POST) && $POST['startEstimate']=='Yes'){
				$status = 'Estimate';
			}
			else{
				$status = 'New';
			}
			$status = $this->db->checkCharLen('repairs.status', $status);
			
			$notify_how = intval($POST['notify_how']??0);
			
			$notify_email = $POST['notify_email']??'';	
			$notify_email = $this->db->checkCharLen('repairs.notify_email', $notify_email);
			
			$notify_sms = $POST['notify_sms']??'';
			$notify_sms = $this->db->checkCharLen('repairs.notify_sms', $notify_sms);
			
			$repairsData = array('from_repairs_id' => 0,		
								'pos_id' => $pos_id,		
								'customer_id' => $customer_id,		
								'ticket_no' => 0,
								'created_on' => date('Y-m-d H:i:s'),
								'last_updated' => date('Y-m-d H:i:s'),
								'accounts_id' => $accounts_id,
								'user_id' => $user_id,
								'problem' => $problem,
								'properties_id' => $properties_id,
								'due_datetime' => $due_datetime,
								'lock_password' => $lock_password,
								'due_time'=>$due_time,
								'assign_to' => $_SESSION["user_id"],
								'status' => $status,
								'bin_location' => $bin_location,
								'notify_how' => $notify_how,
								'notify_email' => $notify_email,
								'notify_sms' => $notify_sms,
								'custom_data'=>''
								);
			
			$repairs_id = $this->db->insert('repairs', $repairsData);
			if($repairs_id){
				
				$ticket_no = 1;
				$repairsObj = $this->db->querypagination("SELECT ticket_no FROM repairs WHERE accounts_id = $accounts_id ORDER BY ticket_no DESC LIMIT 0, 1", array());
				if($repairsObj){
					$ticket_no = $repairsObj[0]['ticket_no']+1;
					$this->db->update('repairs', array('ticket_no'=>$ticket_no), $repairs_id);
				}
				
				//===========Forms Start=============//
				$brand = $model = '';
				if(!empty($properties_id)){
					$repairsObj = $this->db->querypagination("SELECT brand_model_id FROM properties WHERE properties_id = $properties_id ORDER BY properties_id DESC LIMIT 0, 1", array());
					if($repairsObj){
						$brand_model_id = $repairsObj[0]['brand_model_id'];
						
						$brandModelObj = $this->db->querypagination("SELECT brand, model FROM brand_model WHERE brand_model_id = $brand_model_id ORDER BY brand_model_id DESC LIMIT 0, 1", array());
						if($brandModelObj){
							$brand = $brandModelObj[0]['brand'];
							$model = $brandModelObj[0]['model'];
						}
					}
				}
				
				if(isset($_SESSION["formsInfo"])){
					foreach($_SESSION["formsInfo"] as $forms_id=>$formsRow){
						
						$form_name = $formsRow['form_name'];
						$form_name = $this->db->checkCharLen('forms_data.form_name', $form_name);
		
						$form_public = $formsRow['form_public'];
						$required = $formsRow['required'];
						$form_data = $formsRow['form_data'];
						
						$fdData = array('created_on'=> date('Y-m-d H:i:s'),
										'last_updated'=> date('Y-m-d H:i:s'),
										'accounts_id'=> $accounts_id,
										'user_id'=> $user_id,
										'forms_id'=> $forms_id,
										'table_id'=> $repairs_id,
										'form_name'=> $form_name,
										'form_public'=> $form_public,
										'required'=> $required,
										'form_data'=>$form_data);						
						$forms_data_id = $this->db->insert('forms_data', $fdData);
						
					}	
					unset($_SESSION["formsInfo"]);
				}
				
				$brandCond = '';
				if($brand !=''){$brandCond = " OR form_matches LIKE '".addslashes($brand)."%'";}
				$sqlForms = "SELECT * FROM forms WHERE accounts_id = $prod_cat_man AND form_for = 'repairs' AND (form_condition = 'All Repairs' OR (form_condition = 'Problem' AND form_matches = '".addslashes($problem)."') OR (form_condition = 'Brand/Model' AND (form_matches = '".addslashes($brandCond)."'))) AND forms_publish = 1";
				$formsObjData = $this->db->query($sqlForms, array());
				if($formsObjData){
					while($formsRow  = $formsObjData->fetch(PDO::FETCH_OBJ)){
						$forms_id = $formsRow->forms_id;
						$form_name = $formsRow->form_name;
						$form_name = $this->db->checkCharLen('forms_data.form_name', $form_name);
						$form_public = $formsRow->form_public;
						$required = $formsRow->required;
						
						$fdData = array('created_on'=> date('Y-m-d H:i:s'),
										'last_updated'=> '1000-01-01 00:00:00',
										'accounts_id'=> $accounts_id,
										'user_id'=> $user_id,
										'forms_id'=> $forms_id,
										'table_id'=> $repairs_id,
										'form_name'=> $form_name,
										'form_public'=> $form_public,
										'required'=> $required,
										'form_data'=>'');						
						$forms_data_id = $this->db->insert('forms_data', $fdData);
						
					}
				}
				
				//===========Forms End=============//
				
				$savemsg = 'add-success';
				$id = $repairs_id;
			}				
			else{
				$savemsg = 'error';
				$message .= "errorAdding";
			}
		}
			
		$array = array( 'login'=>'','id'=>intval($id),
						'savemsg'=>$savemsg,
						'message'=>$message);
		return json_encode($array);
	}
	
	public function updateCustomerInfo(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;

		$POST = json_decode(file_get_contents('php://input'), true);
		$repairs_id = intval($POST['repairs_id']??0);
		$pos_id = intval($POST['pos_id']??0);
		$customer_id = intval($POST['customer_id']??0);
		$savemsg = 'update-error';
		$returnData = array();
		if($repairs_id>0){
			$properties_id = 0;
			$repairsObj = $this->db->query("SELECT properties_id FROM repairs WHERE repairs_id = $repairs_id", array());
			if($repairsObj){
				$properties_id = $repairsObj->fetch(PDO::FETCH_OBJ)->properties_id;
			}

			$imei_or_serial_no = $brand = $model = $more_details = '';
			$brand_model_id = 0;
			if($properties_id>0 && $prod_cat_man>0){
				$propertiesObj = $this->db->query("SELECT * FROM properties WHERE properties_id = :properties_id AND accounts_id = $prod_cat_man", array('properties_id'=>$properties_id),1);
				if($propertiesObj){
					$propertiesRow = $propertiesObj->fetch(PDO::FETCH_OBJ);	
					
					$imei_or_serial_no = stripslashes(trim((string) $propertiesRow->imei_or_serial_no));
					$brand_model_id = trim((string) $propertiesRow->brand_model_id);
					if($brand_model_id>0){
						$brandModelObj = $this->db->query("SELECT brand, model FROM brand_model WHERE brand_model_id = $brand_model_id AND accounts_id = $prod_cat_man", array());
						if($brandModelObj){
							$brandModelRow = $brandModelObj->fetch(PDO::FETCH_OBJ);	
							$brand = trim((string) $brandModelRow->brand);
							$model = trim((string) $brandModelRow->model);
						}
					}
					$more_details = stripslashes(trim((string) $propertiesRow->more_details));
				}
			}

			$propertiesId = 0;
			$propertiesObj = $this->db->query("SELECT properties_id, properties_publish FROM properties WHERE accounts_id = $prod_cat_man AND customers_id = :customers_id AND imei_or_serial_no = :imei_or_serial_no AND brand_model_id = $brand_model_id AND more_details = :more_details", array('customers_id'=>$customer_id, 'imei_or_serial_no'=>$imei_or_serial_no, 'more_details'=>$more_details));
			if($propertiesObj){
				$pOneRow = $propertiesObj->fetch(PDO::FETCH_OBJ);
				$propertiesId = $pOneRow->properties_id;
				$properties_publish = $pOneRow->properties_publish;
				if($properties_publish==0){
					$update = $this->db->update('properties', array('properties_publish'=>1), $propertiesId);
					if($update){
						$note_for = $this->db->checkCharLen('notes.note_for', 'customers');
						$noteData=array('table_id'=> $customers_id,
										'note_for'=> $note_for,
										'created_on'=> date('Y-m-d H:i:s'),
										'last_updated'=> date('Y-m-d H:i:s'),
										'accounts_id'=> $_SESSION["accounts_id"],
										'user_id'=> $_SESSION["user_id"],
										'note'=> $this->db->translate('Property was edited')." $brand $model $imei_or_serial_no $more_details",
										'publics'=>0);
						$notes_id = $this->db->insert('notes', $noteData);						
					}
				}
			}
			if($propertiesId==0){
				$conditionarray = array();
				$conditionarray['accounts_id'] = $prod_cat_man;			
				$conditionarray['user_id'] = $_SESSION["user_id"];
				$conditionarray['last_updated'] = date('Y-m-d H:i:s');
				$conditionarray['customers_id'] = $customer_id;
				$conditionarray['imei_or_serial_no'] = $imei_or_serial_no;
				$conditionarray['brand_model_id'] = $brand_model_id;
				$conditionarray['more_details'] = $more_details;	
				$conditionarray['created_on'] = date('Y-m-d H:i:s');
				$propertiesId = $this->db->insert('properties', $conditionarray);
				if(!$propertiesId){
					$propertiesId = 0;
				}
			}

			$updateRepairs = $this->db->update('repairs', array('customer_id'=>$customer_id, 'properties_id'=>$propertiesId, 'last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
			if($updateRepairs){
				$updatepos = $this->db->update('pos', array('customer_id'=>$customer_id, 'last_updated'=>date('Y-m-d H:i:s')), $pos_id);
				$savemsg = 'update-success';

				$customername = $customeremail = $customerphone = $customeraddress = '';
				$customers_id = $cCustomFields = $available_credit = 0;
				$customerObj = $this->db->query("SELECT * FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = $customer_id", array());
				if($customerObj){
					$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);

					$cqueryObj = $this->db->query("SELECT COUNT(custom_fields_id) AS totalrows FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'customers'", array());
					if($cqueryObj){
						$cCustomFields = $cqueryObj->fetch(PDO::FETCH_OBJ)->totalrows;
					}
					$customers_id = $customerrow->customers_id;
					$company = $customerrow->company;
					$first_name = $customerrow->first_name;
					$last_name = $customerrow->last_name;
				
					$customername = $company;
					if($customername !=''){$customername .= ', ';}
					$customername .= $first_name;
					if($customername !=''){$customername .= ' ';}
					$customername .= $last_name;
							
					$customeremail = $customerrow->email;
					$customerphone = $customerrow->contact_no;

					$credit_limit = round($customerrow->credit_limit,2);
					if($credit_limit>0){
						$Common = new Common($this->db);
						$availCreditData = $Common->calAvailCr($customers_id, $credit_limit, 1);
						$available_credit = $availCreditData['available_credit']??0;
					}
				}

				$returnData['available_credit'] = floatval($available_credit);
				$returnData['propertiesId'] = intval($propertiesId);
				$returnData['customers_id'] = intval($customers_id);
				$returnData['cCustomFields'] = intval($cCustomFields);
				$returnData['customeremail'] = $customeremail;
				$returnData['customername'] = stripslashes($customername);
				$returnData['customerphone'] = $customerphone;
				
			}
		}

		$array = array( 'login'=>'',
			'savemsg'=>$savemsg,
			'returnData'=>$returnData);
		return json_encode($array);

	}

	public function updaterepairscomplete(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$repairs_id = intval($POST['repairs_id']??0);		
		$pos_id = intval($POST['pos_id']??0);		
		$sales_datetime = $created_on = $payment_datetime = date('Y-m-d H:i:s');
		$Common = new Common($this->db);
		$returnstr = 0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$pos_type = 'Repairs';
		$sqlquery = "SELECT invoice_no, pos_type, order_status FROM pos WHERE pos_id = $pos_id";
		$queryObj = $this->db->query($sqlquery, array());
		if($queryObj){
			$posRow = $queryObj->fetch(PDO::FETCH_OBJ);
			$invoice_no = $posRow->invoice_no;
			$pos_type = $posRow->pos_type;
			$order_status = $posRow->order_status;
			if(in_array($pos_type, array('Order', 'Repairs')) && $order_status == 2){
				$returnstr = $invoice_no;
				$status = $this->db->checkCharLen('repairs.status', 'Invoiced');
				$this->db->update('repairs', array('status'=>$status, 'notify_how'=>0, 'notify_email'=>'', 'notify_sms'=>'', 'last_updated'=>date('Y-m-d H:i:s')), $repairs_id);			
			}
		}
		
		if($returnstr==0){
			//=============collect user last new invoice no================//
			$invoice_no = 1;
			$poObj = $this->db->querypagination("SELECT invoice_no FROM pos WHERE accounts_id = $accounts_id ORDER BY invoice_no DESC LIMIT 0, 1", array());
			if($poObj){
				$invoice_no = $poObj[0]['invoice_no']+1;
			}
			
			$posarray=array('invoice_no'=>$invoice_no,
							'sales_datetime'=>$sales_datetime,
							'created_on' => date('Y-m-d H:i:s'),
							'last_updated' => date('Y-m-d H:i:s'),
							'order_status'=>2,
							'status'=>'Invoiced'
							);
			$updatepos = $this->db->update('pos', $posarray, $pos_id);
			if($updatepos){
				
				$status = $this->db->checkCharLen('repairs.status', 'Invoiced');
				$this->db->update('repairs', array('status'=>$status, 'notify_how'=>0, 'notify_email'=>'', 'notify_sms'=>'', 'last_updated'=>date('Y-m-d H:i:s')), $repairs_id);			
				
				//===========Update ave_cost into pos_cart table============//
				$pos_cartSql = "SELECT * FROM pos_cart WHERE pos_id = $pos_id ORDER BY pos_cart_id ASC";
				$pos_cartObj = $this->db->query($pos_cartSql, array());
				if($pos_cartObj){
					while($onepos_cartrow = $pos_cartObj->fetch(PDO::FETCH_OBJ)){
						$pos_cart_id = $onepos_cartrow->pos_cart_id;
						$product_id = $onepos_cartrow->item_id;
						$item_type = $onepos_cartrow->item_type;
						if($product_id==0){
							$ave_cost = $onepos_cartrow->ave_cost;
						}
						else{
							$ave_cost = 0;
						}
						
						if($item_type == 'one_time'){
							$poSql = "SELECT po.po_id FROM po, po_items WHERE po.accounts_id = $accounts_id AND po_items.item_type = 'one_time' AND po_items.product_id = $pos_cart_id AND po.po_id = po_items.po_id GROUP BY po_items.po_items_id ORDER BY po_items.po_items_id ASC LIMIT 0,1";
							$poData = $this->db->querypagination($poSql, array());
							if($poData !=''){
								foreach($poData as $poRow){
									$po_id = $poRow['po_id'];
									$lot_ref_no = "t$invoice_no";
				
									$this->db->update('po', array('lot_ref_no'=>$lot_ref_no), $po_id);
								}
							}
						}
						
						if($item_type !='livestocks'){
							$inventoryObj = $this->db->query("SELECT i.ave_cost, i.ave_cost_is_percent, p.manage_inventory_count FROM product p, inventory i WHERE p.product_id = $product_id AND i.accounts_id = $accounts_id AND p.product_id = i.product_id", array());
							if($inventoryObj){
								$inventoryOneRow = $inventoryObj->fetch(PDO::FETCH_OBJ);
								$ave_cost = $inventoryOneRow->ave_cost;
								$ave_cost_is_percent = $inventoryOneRow->ave_cost_is_percent;
								$manage_inventory_count = $inventoryOneRow->manage_inventory_count;
								if($ave_cost_is_percent>0 && $manage_inventory_count==0){
									
									if($onepos_cartrow->discount_is_percent>0){
										$discount_value = round($onepos_cartrow->sales_price*0.01*$onepos_cartrow->discount,2);
									}
									else{
										$discount_value = round($onepos_cartrow->discount,2);
									}

									$ave_cost = round(($onepos_cartrow->sales_price-$discount_value)*$ave_cost*0.01,2);
								}
							}
						}
						
						$this->db->update('pos_cart', array('ave_cost'=>$ave_cost), $pos_cart_id);			
						if($item_type =='livestocks'){
							$Common->cartCellphoneAveCost($pos_cart_id, date('Y-m-d H:i:s'), 1);
						}
					}
				}
				
				$changemethod = $POST['changemethod']??'';
				$amount_due = floatval($POST['amount_due']??0);
		
				$pos_payment_id = $payment_amount = 0;
				$payment_date = '';
				$sqlquery = "SELECT pos_payment_id, payment_amount, payment_datetime FROM pos_payment WHERE pos_id = $pos_id AND payment_method = 'Cash' ORDER BY pos_payment_id DESC LIMIT 0,1";
				$query = $this->db->querypagination($sqlquery, array());
				if($query){
					foreach($query as $row){
						$pos_payment_id = $row['pos_payment_id'];
						$payment_amount = $row['payment_amount'];
						$payment_date = date('Y-m-d', strtotime($row['payment_datetime']));
					}
				}
				
				$today_date = date('Y-m-d');
				$updatecash = 0;
				if($changemethod=='Cash' && $pos_payment_id>0 && strcmp($today_date, $payment_date)==0 && $amount_due<0){
					
					if((-1*$amount_due) < $payment_amount){
						$payment_amount = $payment_amount+$amount_due;
						
						$pos_paymentdata =array('payment_amount'=>$payment_amount,
												'payment_datetime'=>$payment_datetime
												);
						$this->db->update('pos_payment', $pos_paymentdata, $pos_payment_id);
						$updatecash = 1;
					}
				}
				
				if($updatecash == 0 && $amount_due<0){
					$payment_method = $this->db->checkCharLen('pos_payment.payment_method', $changemethod);
					$drawer = $this->db->checkCharLen('pos_payment.drawer', '');
					$ppData = array('pos_id'=>$pos_id,
									'payment_method'=>$payment_method,
									'payment_amount'=>round($amount_due,2),
									'payment_datetime'=>$payment_datetime,
									'user_id' => $user_id,
									'more_details' => '',
									'drawer' => $drawer);
					$pos_payment_id = $this->db->insert('pos_payment', $ppData);
				}
								
				//===========Final check for Grand Total and Payment Total======================//
				$sql = "SELECT * FROM pos WHERE pos_id = $pos_id";
				$query = $this->db->querypagination($sql, array());
				if($query){
					foreach($query as $onerow){
						$customer_id = $onerow['customer_id'];
						$is_due = $onerow['is_due'];
						$taxable_total = $nontaxable_total = 0.00;
						
						$sqlquery = "SELECT * FROM pos_cart WHERE pos_id = $pos_id";
						$query = $this->db->query($sqlquery, array());
						if($query){
							while($row = $query->fetch(PDO::FETCH_OBJ)){
								$sales_price = $row->sales_price;
								$shipping_qty = $row->shipping_qty;
								$total =round($sales_price * $shipping_qty,2);
								$discount_is_percent = $row->discount_is_percent;
								$discount = $row->discount;
								if($discount_is_percent>0){
									$discount_value = round($total*0.01*$discount,2);
								}
								else{ 
									$discount_value = round($discount*$shipping_qty,2);
								}
								$taxable = $row->taxable;																		
								if($taxable>0){
									$taxable_total = $taxable_total+$total-$discount_value;
								}
								else{
									$nontaxable_total = $nontaxable_total+$total-$discount_value;
								}						
							}
						}
						
						$taxes_total1 = 0;					
						$tax_inclusive1 = $onerow['tax_inclusive1'];
						if($onerow['taxes_name1'] !=''){
							$taxes_total1 = $Common->calculateTax($taxable_total, $onerow['taxes_percentage1'], $tax_inclusive1);
						}
						$taxes_total2 = 0;					
						$tax_inclusive2 = $onerow['tax_inclusive2'];
						if($onerow['taxes_name2'] !=''){
							$taxes_total2 = $Common->calculateTax($taxable_total, $onerow['taxes_percentage2'], $tax_inclusive2);
						}
						
						if($tax_inclusive1>0){$taxes_total1 = 0;}
						if($tax_inclusive2>0){$taxes_total2 = 0;}
						$grand_total = $taxable_total+$taxes_total1+$taxes_total2+$nontaxable_total;
						
						$amountPaid = 0;
						$sqlquery = "SELECT SUM(payment_amount) AS totalpayment FROM pos_payment WHERE pos_id = $pos_id AND payment_method != 'Change' GROUP BY pos_id";
						$queryObj = $this->db->query($sqlquery, array());
						if($queryObj){
							$amountPaid = $queryObj->fetch(PDO::FETCH_OBJ)->totalpayment;
						}
						$dueAmount = round($grand_total-$amountPaid,2);
						
						if($dueAmount>0){
							$credit_days = $credit_limit = 0;
							$customersObj = $this->db->querypagination("SELECT credit_days, credit_limit FROM customers WHERE customers_id = $customer_id AND accounts_id = $prod_cat_man AND credit_days>0 ORDER BY accounts_id DESC LIMIT 0, 1", array());
							if($customersObj){
								$credit_days = $customersObj[0]['credit_days'];
								$credit_limit = $customersObj[0]['credit_limit'];
							}
							if($credit_limit>0){
								$this->db->update('pos', array('credit_days'=>$credit_days, 'is_due'=>1), $pos_id);
							}
						}
						elseif($dueAmount<0){
							$lpos_payment_id = 0;
							$newpayment_amount = $dueAmount;
							$sqlquery = "SELECT pos_payment_id, payment_amount FROM pos_payment WHERE pos_id = $pos_id AND payment_method = 'Cash' ORDER BY pos_payment_id DESC LIMIT 0,1";
							$query = $this->db->querypagination($sqlquery, array());
							if($query){
								foreach($query as $row){
									$lpos_payment_id = $row['pos_payment_id'];
									$newpayment_amount = $row['payment_amount']+$dueAmount;
								}
							}
							
							if($lpos_payment_id>0){
								if($newpayment_amount==0){
									$this->db->delete('pos_payment', 'pos_payment_id', $lpos_payment_id);
								}
								else{
									$pos_paymentdata =array('payment_amount'=>$newpayment_amount,
															'payment_datetime'=>date('Y-m-d H:i:s')
															);
									$this->db->update('pos_payment', $pos_paymentdata, $lpos_payment_id);
								}
							}
							elseif($newpayment_amount !=0){
								$payment_method = $this->db->checkCharLen('pos_payment.payment_method', 'Cash');
								$drawer = $this->db->checkCharLen('pos_payment.drawer', '');
								$ppData = array('pos_id'=>$pos_id,
												'payment_method'=>$payment_method,
												'payment_amount'=>round($newpayment_amount,2),
												'payment_datetime'=>date('Y-m-d H:i:s'),
												'user_id' => $user_id,
												'more_details' => '',
												'drawer' => $drawer);
								$pos_payment_id = $this->db->insert('pos_payment', $ppData);
							}
						}
						elseif($is_due==1 && $dueAmount==0){
							$this->db->update('pos', array('credit_days'=>0, 'is_due'=>0), $pos_id);
						}							
					}
				}
				
				$email_address = '';
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'invoice_setup'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('invoice_backup_email', $value))
							$email_address = $value['invoice_backup_email'];
					}
				}
				if($email_address !=''){
					$customer_service_email = '';
					$accObj = $this->db->query("SELECT customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
					if($accObj){
						$customer_service_email = $accObj->fetch(PDO::FETCH_OBJ)->customer_service_email;
					}
					if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
					
					$Printing = new Printing($this->db);
					$mail_body = $Printing->invoicesInfo($pos_id, 'large', $amount_due, 'Invoices', 1);
					
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
					$mail->Subject = $this->db->translate('Sales Invoice # s').$invoice_no;
					$mail->CharSet = 'UTF-8';
					$mail->isHTML(true);
					//Build a simple message body
					$mail->Body = $mail_body;					
					$mail->send();
				}
					
				$returnstr = $invoice_no;
			}
			
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnstr));
	}
	
	public function showRepairsData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$repairsData = array();
		$repairsData['login'] = '';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$repairs_id = intval($POST['repairs_id']??0);
		
		$repairsObj = $this->db->query("SELECT * FROM repairs WHERE accounts_id = $accounts_id AND repairs_id = :repairs_id", array('repairs_id'=>$repairs_id),1);
		if($repairsObj){
			$repairsRow = $repairsObj->fetch(PDO::FETCH_OBJ);
			
			$repairsData['repairs_id'] = $repairsRow->repairs_id;				
			$repairsData['problem'] = $problem = trim((string) $repairsRow->problem);
			$problemOptions = array();
			$sqlproblem = "SELECT name FROM repair_problems WHERE accounts_id = $prod_cat_man AND (repair_problems_publish = 1 OR (name = :name AND repair_problems_publish = 0)) ORDER BY name ASC";
			$problemObj = $this->db->query($sqlproblem, array('name'=>$problem));
			if($problemObj){
				while($oneproblemrow = $problemObj->fetch(PDO::FETCH_OBJ)){
					$oproblem_name = stripslashes(trim((string) $oneproblemrow->name));
					if($oproblem_name !=''){
						$problemOptions[$oproblem_name] = '';
					}
				}
				ksort($problemOptions);
				$problemOptions = array_keys($problemOptions);
			}
			$repairsData['problemOptions'] = $problemOptions;
			$due_datetime = $repairsRow->due_datetime;
			if(in_array($due_datetime, array('0000-00-00', '1000-01-01', '0000-00-00 00:00:00', '1000-01-01 00:00:00'))){$due_datetime = '';}
			$repairsData['due_datetime'] = $due_datetime;
			$repairsData['due_time'] = trim((string) $repairsRow->due_time);
			$repairsData['notify_how'] = intval($repairsRow->notify_how);
			$repairsData['notify_email'] = $repairsRow->notify_email;
			$repairsData['notify_sms'] = $repairsRow->notify_sms;
			$notify_default_email = $notify_default_sms = '';
			if($accounts_id>0){
				$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'notifications'", array());
				if($varObj){
					$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						foreach($value as $oneNotifyRow){
							if($notify_default_email=='' && $oneNotifyRow['email_body'] !=''){
								$notify_default_email = $oneNotifyRow['email_body'];
							}
							if($notify_default_sms=='' && $oneNotifyRow['sms_text'] !=''){
								$notify_default_sms = $oneNotifyRow['sms_text'];
							}
						}
					}
					$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'sms_messaging' AND value !=''", array());
					if(!$varObj){$notify_default_sms = '';}
				}
			}
			$repairsData['notify_default_email'] = $notify_default_email;
			$repairsData['notify_default_sms'] = $notify_default_sms;
			
			$repairsData['lock_password'] = trim((string) $repairsRow->lock_password);
			$repairsData['bin_location'] = trim((string) $repairsRow->bin_location);
			$repairsData['assign_to'] = $repairsRow->assign_to;
			$technicianOptions = array();					
			$userObj = $this->db->querypagination("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id AND user_publish = 1 GROUP BY user_id ORDER BY user_first_name ASC, user_last_name ASC", array());
			if($userObj){
				foreach($userObj as $rowuser){
					$user_FullName = trim(stripslashes("$rowuser[user_first_name] $rowuser[user_last_name]"));
					$technicianOptions[$rowuser['user_id']] = $user_FullName;
				}
			}
			$repairsData['technicianOptions'] = $technicianOptions;
			$pos_id = $repairsRow->pos_id;
			$repairsData['pos_id'] = $pos_id;
			$employee_id = 0;
			$posObj = $this->db->query("SELECT employee_id FROM pos WHERE pos_id = $pos_id", array());
			if($posObj){
				$employee_id = $posObj->fetch(PDO::FETCH_OBJ)->employee_id;	
			}
			$repairsData['employee_id'] = $employee_id;
			$Common = new Common($this->db);
			$repairsData['customFieldsData'] = $Common->customFormFields('repairs', $repairsRow->custom_data);
		}
		return json_encode($repairsData);
	}
	
	public function saveChangeRepairInfo(){
		$POST = $_POST;//json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$showBasicInfo = $showCustomInfo = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$repairs_id = intval($POST['repairs_id']??0);

		$jsonResponse = array();
		$jsonResponse['login'] = '';
		
		if($repairs_id>0){

			$problem = $POST['problem']??'';			
			$problem_name = addslashes(trim((string) $POST['problem_name']??''));
			if(empty($problem) && !empty($problem_name)){$problem = $problem_name;}

			$problem = $this->db->checkCharLen('repair_problems.name', $problem);
			
			if($problem !=''){
				$totalrows = 0;
				$queryProblemObj = $this->db->query("SELECT name FROM repair_problems WHERE accounts_id = $prod_cat_man AND UPPER(name) = :name", array('name'=>strtoupper($problem)));
				if($queryProblemObj){
					while($oneRowRP = $queryProblemObj->fetch(PDO::FETCH_OBJ)){
						$problem = trim((string) $oneRowRP->name);
					}					
				}
				else{
					$rpData = array('created_on' => date('Y-m-d H:i:s'),
									'last_updated' => date('Y-m-d H:i:s'),
									'accounts_id' => $prod_cat_man,
									'user_id' => $_SESSION["user_id"],
									'name' => $problem,
									'additional_disclaimer'=>'');
					$this->db->insert('repair_problems', $rpData);	
				}
			}
			
			$conditionarray = array();
			$conditionarray['problem'] = $problem;
			$due_datetime = '1000-01-01';
			if(strlen($POST['due_datetime'])==10){
				$due_datetime = date('Y-m-d', strtotime($POST['due_datetime']));
			}
			$conditionarray['due_datetime'] = $due_datetime;
			
			$due_time = $POST['due_time']??'';
			$due_time = $this->db->checkCharLen('repairs.due_time', $due_time);
			$conditionarray['due_time'] = $due_time;
			
			$notify_how = intval($POST['notify_how']??0);
			$conditionarray['notify_how'] = $notify_how;
			
			$notify_email = $POST['notify_email']??'';
			$notify_email = $this->db->checkCharLen('repairs.notify_email', $notify_email);
			$conditionarray['notify_email'] = $notify_email;
			
			$notify_sms = $POST['notify_sms']??'';
			$notify_sms = $this->db->checkCharLen('repairs.notify_sms', $notify_sms);
			$conditionarray['notify_sms'] = $notify_sms;
			
			$lock_password = $POST['lock_password']??'';
			$lock_password = $this->db->checkCharLen('repairs.lock_password', $lock_password);
			$conditionarray['lock_password'] = $lock_password;
			
			$bin_location = $POST['bin_location']??'';
			$bin_location = $this->db->checkCharLen('repairs.bin_location', $bin_location);
			$conditionarray['bin_location'] = $bin_location;				
			
			$assign_to = intval($POST['assign_to']??0);
			$conditionarray['assign_to'] = $assign_to;
			
			$pos_id = intval($POST['pos_id']??0);			
			$employee_id = intval($POST['employee_id']??0);
			
			//===========================for POS==================//
			$Common = new Common($this->db);
			$prevEmployeeId = 0;
			if($pos_id>0){
				$prevEmployeeId = $Common->getOneRowFields('pos', array('pos_id'=>$pos_id), 'employee_id');
				$this->db->update('pos', array('employee_id'=>$employee_id), $pos_id);
			}				
			$conditionarray['custom_data'] = $custom_data = $Common->postCustomFormFields('repairs', $repairs_id);
			
			$conditionarray['last_updated'] = date('Y-m-d H:i:s');
			
			$oneTRowObj = $this->db->querypagination("SELECT * FROM repairs WHERE repairs_id = $repairs_id", array());
			$changed = array();
			if($prevEmployeeId != $employee_id){
				$fieldName = 'Salesman';
				$prevFieldVal = $prevEmployeeId;
				$fieldValue = $employee_id;
				if($prevFieldVal==0){$prevFieldVal = '';}
				elseif($prevFieldVal>0){$prevFieldVal = $Common->getOneRowFields('user', array('user_id'=>$prevFieldVal), array('user_first_name', 'user_last_name'));}
				if($fieldValue==0){$fieldValue = '';}
				elseif($fieldValue>0){$fieldValue = $Common->getOneRowFields('user', array('user_id'=>$fieldValue), array('user_first_name', 'user_last_name'));}
				$changed[$fieldName] = array($prevFieldVal, $fieldValue);
			}
			
			$update = $this->db->update('repairs', $conditionarray, $repairs_id);
			if($update){
				$brand = $model = '';
				$properties_id = 0;
				if($oneTRowObj){
					$properties_id = $oneTRowObj[0]['properties_id'];
					unset($conditionarray['last_updated']);
					foreach($conditionarray as $fieldName=>$fieldValue){
						$prevFieldVal = $oneTRowObj[0][$fieldName];
						if($prevFieldVal != $fieldValue){
							if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
							if($fieldValue=='1000-01-01'){$fieldValue = '';}
							if($fieldName=='assign_to'){
								$fieldName = 'Technician';
								if($prevFieldVal==0){$prevFieldVal = '';}
								elseif($prevFieldVal>0){$prevFieldVal = $Common->getOneRowFields('user', array('user_id'=>$prevFieldVal), array('user_first_name', 'user_last_name'));}
								if($fieldValue==0){$fieldValue = '';}
								elseif($fieldValue>0){$fieldValue = $Common->getOneRowFields('user', array('user_id'=>$fieldValue), array('user_first_name', 'user_last_name'));}
							}
							elseif($fieldName=='custom_data'){
								
								$custom_data1 = $custom_data2 = array();
								if(!empty($prevFieldVal)){$custom_data1 = unserialize($prevFieldVal);}
								if(!empty($fieldValue)){$custom_data2 = unserialize($fieldValue);}
								if(!empty($custom_data1) || !empty($custom_data2)){
									
									if(!empty($custom_data1) && !empty($custom_data2)){
										$mergeData = array_merge_recursive($custom_data1, $custom_data2);
											
										foreach($mergeData as $mKey=>$mValue){
											if(array_key_exists($mKey, $custom_data1) && array_key_exists($mKey, $custom_data2)){
												$twoData = $mValue;
												
												if($mValue[0] ==$mValue[1]){
													unset($custom_data1[$mKey]);
													unset($custom_data2[$mKey]);
												}
											}
										}
									}
									elseif(!empty($custom_data1)){
										foreach($custom_data1 as $mKey=>$mValue){
											if($custom_data1[$mKey] == ''){
												unset($custom_data1[$mKey]);
											}
										}
									}
									elseif(!empty($custom_data2)){
										foreach($custom_data2 as $mKey=>$mValue){
											if($custom_data2[$mKey] == ''){
												unset($custom_data2[$mKey]);
											}
										}
									}
											
									if(!empty($custom_data1)){$prevFieldVal = serialize($custom_data1);}
									else{$prevFieldVal = '';}
									if(!empty($custom_data2)){$fieldValue = serialize($custom_data2);}
									else{$fieldValue = '';}									
								}
							}
							$changed[$fieldName] = array($prevFieldVal, $fieldValue);
						}
					}						
				}
		
				if(!empty($changed)){
					$moreInfo = $teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'repairs');
					$teData['record_id'] = $repairs_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);							
				}
			
				if($properties_id>0){					
					$repairsObj = $this->db->querypagination("SELECT brand_model_id FROM properties WHERE properties_id = $properties_id ORDER BY properties_id DESC LIMIT 0, 1", array());
					if($repairsObj){
						$brand_model_id = $repairsObj[0]['brand_model_id'];						
						$brandModelObj = $this->db->querypagination("SELECT brand, model FROM brand_model WHERE brand_model_id = $brand_model_id ORDER BY brand_model_id DESC LIMIT 0, 1", array());
						if($brandModelObj){
							$brand = $brandModelObj[0]['brand'];
							$model = $brandModelObj[0]['model'];
						}
					}
				}
				
				$formsIds = array();
				$fdObj = $this->db->query("SELECT forms_id FROM forms_data WHERE accounts_id = $accounts_id AND table_id = $repairs_id", array());
				if($fdObj){
					while($fdRow  = $fdObj->fetch(PDO::FETCH_OBJ)){
						$formsIds[] = $fdRow->forms_id;
					}
				}					
				//===========Forms End=============//
				
				$savemsg = 'Save';
				$technicianName = '';
				$userObj = $this->db->querypagination("SELECT user_first_name, user_last_name FROM user WHERE user_id = $assign_to LIMIT 0,1", array());
				if($userObj){
					foreach($userObj as $rowuser){
						$technicianName = trim(stripslashes("$rowuser[user_first_name] $rowuser[user_last_name]"));
					}
				}
				$salesmanName = '';
				$userObj = $this->db->querypagination("SELECT user_first_name, user_last_name FROM user WHERE user_id = $employee_id LIMIT 0,1", array());
				if($userObj){
					foreach($userObj as $rowuser){
						$salesmanName = trim(stripslashes("$rowuser[user_first_name] $rowuser[user_last_name]"));
					}
				}
				
				$jsonResponse['problem'] = $problem;
				$jsonResponse['due_datetime'] = $due_datetime;
				$jsonResponse['due_time'] = $due_time;
				$jsonResponse['notify_how'] = intval($notify_how);
				$jsonResponse['notify_email'] = $notify_email;
				$jsonResponse['notify_sms'] = $notify_sms;
				$jsonResponse['lock_password'] = $lock_password;
				$jsonResponse['bin_location'] = $bin_location;
				$jsonResponse['technicianName'] = $technicianName;
				$jsonResponse['salesmanName'] = $salesmanName;
				$custom_data = '';
				$posObj = $this->db->query("SELECT custom_data FROM repairs WHERE repairs_id = $repairs_id", array());
				if($posObj){
					$custom_data = $posObj->fetch(PDO::FETCH_OBJ)->custom_data;
				}

				$cusDataInfo = $Common->customViewInfo('repairs', $custom_data);
				$jsonResponse['rCustomFields'] = $cusDataInfo[0];
				$jsonResponse['rCustomFieldsData'] = $cusDataInfo[1];
			}
		}

		$jsonResponse['savemsg'] = $savemsg;
		return json_encode($jsonResponse);
	}
	
	public function AJget_problemOpt(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$problem = trim((string) $POST['problem']??'');
		$sqlproblem = "SELECT name FROM repair_problems WHERE accounts_id = $prod_cat_man AND (repair_problems_publish = 1 OR (name = :name AND repair_problems_publish = 0)) ORDER BY name ASC";
		$problemquery = $this->db->query($sqlproblem, array('name'=>$problem));
		if($problemquery){
			while($oneproblemrow = $problemquery->fetch(PDO::FETCH_OBJ)){
				$oproblem_name = stripslashes(trim((string) $oneproblemrow->name));
				if($oproblem_name !=''){
					$selected = '';
					if(strcmp($oproblem_name, $problem)==0){
						$selected = ' selected="selected"';
					}
					$returnStr .= "<option$selected value=\"$oproblem_name\">$oproblem_name</option>";
				}
			}
		}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
		
	public function AJsend_RepairsEmail($repairs_id = 0, $email_address = ''){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$repairs_id = intval($POST['repairs_id']??$repairs_id);
		$email_address = $POST['email_address']??$email_address;
		
		if($email_address =='' || is_null($email_address)){
			$returnStr = 'notSendMail';
		}
		else{
			$Printing = new Printing($this->db);
			$mail_body = $Printing->repairInvoicesInfo($repairs_id, 'large', 1);
		
			$ticket_no = 0;
			$posObj = $this->db->query("SELECT ticket_no FROM repairs WHERE accounts_id = $accounts_id AND repairs_id = :repairs_id", array('repairs_id'=>$repairs_id),1);
			if($posObj){
				$ticket_no = $posObj->fetch(PDO::FETCH_OBJ)->ticket_no;
			}
			$customer_service_email = '';
			$accObj = $this->db->query("SELECT customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
			if($accObj){
				$customer_service_email = $accObj->fetch(PDO::FETCH_OBJ)->customer_service_email;
			}
			if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
			
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
			$mail->Subject = $this->db->translate('Repair Ticket')." ".$this->db->translate('summary of t')." $ticket_no";
			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';
			$mail->Body = $mail_body;
			if($mail->send()){
				$note_for = $this->db->checkCharLen('notes.note_for', 'repairs');
				$noteData = array();
				$noteData['table_id'] = $repairs_id;
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
				
	public function checkStatusNotification(){
		$accounts_id = $_SESSION["accounts_id"]??0;		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$POST = json_decode(file_get_contents('php://input'), true);
		$repairs_id = intval($POST['repairs_id']??0);
		$repairs_status = trim((string) $POST['repairs_status']??'');
		if($repairs_status==''){$repairs_status = 'Finished';}
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$customersData = array();
		$customersData['login'] = '';
		$customersData['notify_how'] = 0;
		$customersData['notify_email'] = '';
		$customersData['notify_sms'] = '';
		$customersData['emaillabel'] = 'Email Address';
		
		$notify_default_subject = $notify_default_email = $notify_default_sms = '';
		$varObjSms = false;
		$varObj = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'notifications'", array());
		if($varObj){
			$value = $varObj->fetch(PDO::FETCH_OBJ)->value;
			if(!empty($value)){
				$value = unserialize($value);
				if(array_key_exists($repairs_status, $value)){
					$oneNotifyRow = $value[$repairs_status];
					if($notify_default_subject=='' && $oneNotifyRow['subject'] !=''){
						$notify_default_subject = $oneNotifyRow['subject'];
					}
					if($notify_default_email=='' && $oneNotifyRow['email_body'] !=''){
						$notify_default_email = $oneNotifyRow['email_body'];
					}
					if($notify_default_sms=='' && $oneNotifyRow['sms_text'] !=''){
						$notify_default_sms = $oneNotifyRow['sms_text'];
					}
				}
			}
			$varObjSms = $this->db->query("SELECT value FROM variables WHERE accounts_id = $accounts_id AND name = 'sms_messaging' AND value !=''", array());
			if(!$varObjSms){$notify_default_sms = '';}
		}
		
		if($repairs_id>0 && (!empty($notify_default_subject) || $notify_default_email !='' || $notify_default_sms !='')){
			$repairsObj = $this->db->query("SELECT * FROM repairs WHERE repairs_id = :repairs_id AND accounts_id = $accounts_id", array('repairs_id'=>$repairs_id),1);
			if($repairsObj){
				$repairsRow = $repairsObj->fetch(PDO::FETCH_OBJ);
				$ticket_no = trim((string) $repairsRow->ticket_no);
				$pos_id = $repairsRow->pos_id;
				
				$notify_default_subject = str_replace('{{TicketNumber}}', $ticket_no, $notify_default_subject);
				$notify_default_email = str_replace('{{TicketNumber}}', $ticket_no, $notify_default_email);
				$notify_default_sms = str_replace('{{TicketNumber}}', $ticket_no, $notify_default_sms);
				
				if(strpos($notify_default_subject, '{{FirstName}}') !==false || strpos($notify_default_subject, '{{LastName}}') !==false || strpos($notify_default_email, '{{FirstName}}') !==false || strpos($notify_default_email, '{{LastName}}') !==false || strpos($notify_default_sms, '{{FirstName}}') !==false || strpos($notify_default_sms, '{{LastName}}') !==false){
					$customer_id = trim((string) $repairsRow->customer_id);					
					$customersObj = $this->db->query("SELECT first_name, last_name FROM customers WHERE customers_id = $customer_id", array());
					if($customersObj){
						$cusRow =$customersObj->fetch(PDO::FETCH_OBJ);
						$first_name = stripslashes($cusRow->first_name);
						$last_name = stripslashes($cusRow->last_name);
						
						$notify_default_subject = str_replace('{{FirstName}}', $first_name, $notify_default_subject);
						$notify_default_subject = str_replace('{{LastName}}', $last_name, $notify_default_subject);
						$notify_default_email = str_replace('{{FirstName}}', $first_name, $notify_default_email);
						$notify_default_email = str_replace('{{LastName}}', $last_name, $notify_default_email);
						$notify_default_sms = str_replace('{{FirstName}}', $first_name, $notify_default_sms);
						$notify_default_sms = str_replace('{{LastName}}', $last_name, $notify_default_sms);
					}
				}
				
				if(strpos($notify_default_subject, '{{IMEINumber}}') !==false || strpos($notify_default_subject, '{{BrandName}}') !==false || strpos($notify_default_subject, '{{ModelName}}') !==false || strpos($notify_default_subject, '{{MoreDetails}}') !==false || strpos($notify_default_email, '{{IMEINumber}}') !==false || strpos($notify_default_email, '{{BrandName}}') !==false || strpos($notify_default_email, '{{ModelName}}') !==false || strpos($notify_default_email, '{{MoreDetails}}') !==false || strpos($notify_default_sms, '{{IMEINumber}}') !==false || strpos($notify_default_sms, '{{BrandName}}') !==false || strpos($notify_default_sms, '{{ModelName}}') !==false || strpos($notify_default_sms, '{{MoreDetails}}') !==false){
					$properties_id = $repairsRow->properties_id;
					if($properties_id>0){
						$propertiesObj = $this->db->query("SELECT * FROM properties WHERE properties_id = $properties_id AND accounts_id = $prod_cat_man AND properties_publish = 1", array());
						if($propertiesObj){
							$propertiesRow = $propertiesObj->fetch(PDO::FETCH_OBJ);	
							
							if(strpos($notify_default_email, '{{IMEINumber}}') !==false || strpos($notify_default_sms, '{{IMEINumber}}') !==false){
								$imei_or_serial_no = trim((string) $propertiesRow->imei_or_serial_no);
								$notify_default_subject = str_replace('{{IMEINumber}}', $imei_or_serial_no, $notify_default_subject);
								$notify_default_email = str_replace('{{IMEINumber}}', $imei_or_serial_no, $notify_default_email);
								$notify_default_sms = str_replace('{{IMEINumber}}', $imei_or_serial_no, $notify_default_sms);
							}
							
							$brand_model_id = trim((string) $propertiesRow->brand_model_id);
							if($brand_model_id>0){
								$brandModelObj = $this->db->query("SELECT brand, model FROM brand_model WHERE brand_model_id = $brand_model_id AND accounts_id = $prod_cat_man", array());
								if($brandModelObj){
									$brandModelRow = $brandModelObj->fetch(PDO::FETCH_OBJ);	
							
									if(strpos($notify_default_email, '{{BrandName}}') !==false || strpos($notify_default_sms, '{{BrandName}}') !==false){
										$brand = trim((string) $brandModelRow->brand);
										$notify_default_subject = str_replace('{{BrandName}}', $brand, $notify_default_subject);
										$notify_default_email = str_replace('{{BrandName}}', $brand, $notify_default_email);
										$notify_default_sms = str_replace('{{BrandName}}', $brand, $notify_default_sms);
									}
							
									if(strpos($notify_default_email, '{{ModelName}}') !==false || strpos($notify_default_sms, '{{ModelName}}') !==false){
										$model = trim((string) $brandModelRow->model);
										$notify_default_subject = str_replace('{{ModelName}}', $model, $notify_default_subject);
										$notify_default_email = str_replace('{{ModelName}}', $model, $notify_default_email);
										$notify_default_sms = str_replace('{{ModelName}}', $model, $notify_default_sms);
									}
								}
							}
							
							if(strpos($notify_default_email, '{{MoreDetails}}') !==false || strpos($notify_default_sms, '{{MoreDetails}}') !==false){
								$more_details = trim((string) $propertiesRow->more_details);
								$notify_default_subject = str_replace('{{MoreDetails}}', $more_details, $notify_default_subject);
								$notify_default_email = str_replace('{{MoreDetails}}', $more_details, $notify_default_email);
								$notify_default_sms = str_replace('{{MoreDetails}}', $more_details, $notify_default_sms);
							}							
						}
					}
				}
				
				if(strpos($notify_default_subject, '{{ProblemName}}') !==false || strpos($notify_default_email, '{{ProblemName}}') !==false || strpos($notify_default_sms, '{{ProblemName}}') !==false){
					$problem = trim((string) $repairsRow->problem);					
					$notify_default_subject = str_replace('{{ProblemName}}', $problem, $notify_default_subject);
					$notify_default_email = str_replace('{{ProblemName}}', $problem, $notify_default_email);
					$notify_default_sms = str_replace('{{ProblemName}}', $problem, $notify_default_sms);
				}
				
				if(strpos($notify_default_subject, '{{RepairStatus}}') !==false || strpos($notify_default_email, '{{RepairStatus}}') !==false || strpos($notify_default_sms, '{{RepairStatus}}') !==false){
					$status = trim((string) $repairsRow->status);					
					$notify_default_subject = str_replace('{{RepairStatus}}', $status, $notify_default_subject);
					$notify_default_email = str_replace('{{RepairStatus}}', $status, $notify_default_email);
					$notify_default_sms = str_replace('{{RepairStatus}}', $status, $notify_default_sms);
				}
				
				if(!in_array($repairsRow->due_datetime, array('0000-00-00', '1000-01-01')) && (strpos($notify_default_subject, '{{DueDateTime}}') !==false || strpos($notify_default_email, '{{DueDateTime}}') !==false || strpos($notify_default_sms, '{{DueDateTime}}') !==false)){
					$due_datetime = date(str_replace('y', 'Y', $dateformat), strtotime($repairsRow->due_datetime))." $repairsRow->due_time";
					$notify_default_subject = str_replace('{{DueDateTime}}', $due_datetime, $notify_default_subject);
					$notify_default_email = str_replace('{{DueDateTime}}', $due_datetime, $notify_default_email);
					$notify_default_sms = str_replace('{{DueDateTime}}', $due_datetime, $notify_default_sms);
				}
				else{
					$notify_default_subject = str_replace('{{DueDateTime}}', '', $notify_default_subject);
					$notify_default_email = str_replace('{{DueDateTime}}', '', $notify_default_email);
					$notify_default_sms = str_replace('{{DueDateTime}}', '', $notify_default_sms);
				}
				
				$CustomField = '';					
				if($repairsRow->custom_data !=''){
					$queryCFObj = $this->db->query("SELECT field_name FROM custom_fields WHERE accounts_id = $prod_cat_man AND field_for = 'repairs' ORDER BY order_val ASC", array());
					if($queryCFObj){
						$custom_data = unserialize($repairsRow->custom_data);
						while($oneCustomFields = $queryCFObj->fetch(PDO::FETCH_OBJ)){
							$field_name = stripslashes($oneCustomFields->field_name);
							if(array_key_exists($field_name, $custom_data) && $custom_data[$field_name] !=''){
								if($CustomField !=''){$CustomField .="<br />";}
								$CustomField .= '<b>'.$field_name.":</b> ".$custom_data[$field_name];
							}
						}
					}
				}
				$notify_default_subject = str_replace('{{CustomField}}', $CustomField, $notify_default_subject);
				$notify_default_email = str_replace('{{CustomField}}', $CustomField, $notify_default_email);
				$notify_default_sms = str_replace('{{CustomField}}', $CustomField, $notify_default_sms);
				
				$notify_sms = trim((string) $repairsRow->notify_sms);
				$bulkSMSCountryCode = '';
				if($varObjSms){
					$leadingZeros = 0;
					$value = $varObjSms->fetch(PDO::FETCH_OBJ)->value;
					if(!empty($value)){
						$value = unserialize($value);
						if(array_key_exists('bulkSMSCountryCode', $value)){
							$bulkSMSCountryCode = '+'.$value['bulkSMSCountryCode'];
						}
						if(array_key_exists('leadingZeros', $value)){
							$leadingZeros = $value['leadingZeros'];
						}
						if($leadingZeros>0){$notify_sms = ltrim((string) $notify_sms, '0');}
					}
				}
				$customersData['notify_how'] = intval($repairsRow->notify_how);
				$customersData['notify_email'] = trim((string) $repairsRow->notify_email);
				$customersData['notify_sms'] = $bulkSMSCountryCode.$notify_sms;
			}
		}
		$customersData['notify_default_subject'] = stripslashes($notify_default_subject);
		$customersData['notify_default_email'] = stripslashes($notify_default_email);
		$customersData['notify_default_sms'] = stripslashes($notify_default_sms);
				
		return json_encode($customersData);
	}
	
	public function sendRepairEmailSMS(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$repairs_id = intval($POST['repairs_id']??0);
		$status = addslashes($POST['repairs_status']??'');
		$returnval = 'error';
		if($repairs_id>0 && $status !=''){
			$status = $this->db->checkCharLen('repairs.status', $status);
			$update = $this->db->update('repairs', array('status'=>$status, 'last_updated'=>date('Y-m-d H:i:s')), $repairs_id);
			if($update){
				$customer_service_email = '';
				$accObj = $this->db->query("SELECT customer_service_email FROM accounts WHERE accounts_id = $accounts_id", array());
				if($accObj){
					$customer_service_email = $accObj->fetch(PDO::FETCH_OBJ)->customer_service_email;
				}
				if($customer_service_email==''){$customer_service_email = $this->db->supportEmail('info');}
				
				$notify_how = intval($POST['notify_how']??0);
				if($notify_how==1){
					$notify_default_subject = nl2br(trim((string) $POST['notify_default_subject']??''));
					$notify_default_email = nl2br(trim((string) $POST['notify_default_email']??''));
					$notify_email = trim((string) $POST['notify_email']??'');
					
					$subject = $notify_default_subject;
					
					$mail_body = "<p>$notify_default_email</p>";
					
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
					$mail->addAddress($notify_email, "");
					$mail->Subject = $subject;
					$mail->isHTML(true);
					$mail->CharSet = 'UTF-8';
					$mail->Body = $mail_body;
					$mail->send();

					$note_for = $this->db->checkCharLen('notes.note_for', 'repairs');
					$noteData=array('table_id'=> $repairs_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $_SESSION["accounts_id"],
									'user_id'=> $_SESSION["user_id"],
									'note'=> $this->db->translate('Email sent to').' '.$notify_email." for status: $status",
									'publics'=>0);
					$notes_id = $this->db->insert('notes', $noteData);
				}
				elseif($notify_how==2){
					$notify_default_sms = nl2br(trim((string) $POST['notify_default_sms']??''));
					$notify_sms = trim((string) $POST['notify_sms']??'');
					$queryObj = $this->db->query("SELECT value FROM variables WHERE accounts_id=$accounts_id AND name='sms_messaging'", array());
					if($queryObj){
						$value = $queryObj->fetch(PDO::FETCH_OBJ)->value;
						if(!empty($value)){
							$value = unserialize($value);
							$BulkSMS = new BulkSMS($this->db);
							$returnval = $BulkSMS->sendSMS($notify_sms, $notify_default_sms);
							
							$note_for = $this->db->checkCharLen('notes.note_for', 'repairs');
							$noteData=array('table_id'=> $repairs_id,
											'note_for'=> $note_for,
											'created_on'=> date('Y-m-d H:i:s'),
											'last_updated'=> date('Y-m-d H:i:s'),
											'accounts_id'=> $_SESSION["accounts_id"],
											'user_id'=> $_SESSION["user_id"],
											'note'=> $this->db->translate('SMS sent to').' '.$notify_sms." for status: $status",
											'publics'=>0);
							$notes_id = $this->db->insert('notes', $noteData);
						}
					}
				}
									
				$user_id = $_SESSION["user_id"]??0;
				$accounts_id = $_SESSION["accounts_id"]??0;		
				$user_name = '';
				$userObj = $this->db->query("SELECT user_first_name, user_last_name FROM user WHERE user_id = $user_id", array());
				if($userObj){
					$userrow = $userObj->fetch(PDO::FETCH_OBJ);
					$user_name = trim(stripslashes("$userrow->user_first_name $userrow->user_last_name"));
				}
				$note_for = $this->db->checkCharLen('notes.note_for', 'repairs');
				$noteData=array('table_id'=> $repairs_id,
								'note_for'=> $note_for,
								'created_on'=> date('Y-m-d H:i:s'),
								'last_updated'=> date('Y-m-d H:i:s'),
								'accounts_id'=> $_SESSION["accounts_id"],
								'user_id'=> $_SESSION["user_id"],
								'note'=> $this->db->translate('Changed Status to')." $status",
								'publics'=>0);
				$notes_id = $this->db->insert('notes', $noteData);
				
				if($returnval =='error')
					$returnval = 'Ok';
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnval));
	}	
	
	public function createLinkedTicket(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$repairs_id = intval($POST['repairs_id']??0);
		$newrepairs_id = 0;
		if($repairs_id>0){				
			
			$repairsData = $this->db->querypagination("SELECT * FROM repairs WHERE accounts_id = $accounts_id AND repairs_id = :repairs_id", array('repairs_id'=>$repairs_id),1);
			if($repairsData){
				$repairsRow = $repairsData[0];
				
				//===============POS Data Insert===========//
				$pos_id = 0;
				$from_pos_id = $repairsRow['pos_id'];
				$from_ticket_no = $repairsRow['ticket_no'];
				$posData = $this->db->querypagination("SELECT * FROM pos WHERE accounts_id = $accounts_id AND pos_id = $from_pos_id", array());
				if($posData){
					$status = 'Trial';
					if(isset($_SESSION["status"])){$status = $_SESSION["status"];}
					if(in_array($status, array('SUSPENDED', 'CANCELED'))){
						return json_encode(array('login'=>'session_ended'));
					}
					
					$posRow = $posData[0];
					unset($posRow['pos_id']);
					$posRow['invoice_no'] = 0;
					$posRow['sales_datetime'] = date('Y-m-d H:i:s');
					$posRow['created_on'] = date('Y-m-d H:i:s');
					$posRow['last_updated'] = date('Y-m-d H:i:s');
					$posRow['user_id'] = $user_id;
					$posRow['order_status'] = 1;
					$posRow['credit_days'] = 0;
					$posRow['is_due'] = 0; 
					$posRow['status'] = 'New';
					$pos_id = $this->db->insert('pos', $posRow);
				}
				
				unset($repairsRow['repairs_id']);
				$repairsRow['from_repairs_id'] = $from_repairs_id = $repairs_id;
				$repairsRow['pos_id'] = $pos_id;
				$repairsRow['ticket_no'] = 0;
				$repairsRow['due_datetime'] = '1000-01-01';
				$repairsRow['due_time'] = '';
				$repairsRow['created_on'] = date('Y-m-d H:i:s');
				$repairsRow['last_updated'] = date('Y-m-d H:i:s');
				$repairsRow['user_id'] = $user_id;
				$repairsRow['status'] = 'New';
				$repairs_id = $this->db->insert('repairs', $repairsRow);
				if($repairs_id){
					
					$ticket_no = 1;
					$repairsObj = $this->db->querypagination("SELECT ticket_no FROM repairs WHERE accounts_id = $accounts_id ORDER BY ticket_no DESC LIMIT 0, 1", array());
					if($repairsObj){
						$ticket_no = $repairsObj[0]['ticket_no']+1;
						$this->db->update('repairs', array('ticket_no'=>$ticket_no), $repairs_id);
					}
					
					$savemsg = 'Saved';
					$newrepairs_id = $repairs_id;
					
					//===========Forms Start=============//
					$properties_id = $repairsRow['properties_id'];
					$brand = $model = '';
					if(!empty($properties_id)){
						$repairsObj = $this->db->querypagination("SELECT brand_model_id FROM properties WHERE properties_id = $properties_id ORDER BY properties_id DESC LIMIT 0, 1", array());
						if($repairsObj){
							$brand_model_id = $repairsObj[0]['brand_model_id'];
							
							$brandModelObj = $this->db->querypagination("SELECT brand, model FROM brand_model WHERE brand_model_id = $brand_model_id ORDER BY brand_model_id DESC LIMIT 0, 1", array());
							if($brandModelObj){
								$brand = $brandModelObj[0]['brand'];
								$model = $brandModelObj[0]['model'];
							}
						}
					}
					$brandCond = '';
					if($brand !=''){$brandCond = " OR form_matches LIKE '".addslashes($brand)."%'";}
					
					$problem = $repairsRow['problem'];
					$sqlForms = "SELECT * FROM forms WHERE accounts_id = $prod_cat_man AND form_for = 'repairs' AND (form_condition = 'All Repairs' OR (form_condition = 'Problem' AND form_matches = '".addslashes($problem)."') OR (form_condition = 'Brand/Model' AND (form_matches = '".addslashes($brandCond)."'))) AND forms_publish = 1";
					$formsObjData = $this->db->query($sqlForms, array());
					if($formsObjData){
						while($formsRow  = $formsObjData->fetch(PDO::FETCH_OBJ)){
							$forms_id = $formsRow->forms_id;
							$form_name = $formsRow->form_name;
							$form_name = $this->db->checkCharLen('forms_data.form_name', $form_name);
							$form_public = $formsRow->form_public;
							$required = $formsRow->required;
							
							$fdData = array('created_on'=> date('Y-m-d H:i:s'),
											'last_updated'=> '1000-01-01 00:00:00',
											'accounts_id'=> $accounts_id,
											'user_id'=> $user_id,
											'forms_id'=> $forms_id,
											'table_id'=> $repairs_id,
											'form_name'=> $form_name,
											'form_public'=> $form_public,
											'required'=> $required,
											'form_data'=>'');						
							$forms_data_id = $this->db->insert('forms_data', $fdData);
							
						}
					}						
					$note_for = $this->db->checkCharLen('notes.note_for', 'repairs');
					$noteData = array(	'table_id' => $repairs_id, 
										'note_for' => $note_for,
										'note' => $this->db->translate('This ticket was created from Ticket #').$from_ticket_no,
										'created_on' => date('Y-m-d H:i:s'),
										'last_updated' => date('Y-m-d H:i:s'),
										'user_id' => $user_id,
										'accounts_id' => $accounts_id,
										'publics'=>0
										);
					$this->db->insert('notes', $noteData);						
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$savemsg, 'newrepairs_id'=>intval($newrepairs_id)));
	}
	
	public function loadSessFormInfo($isArray=0){
	
		$returnData = array();
		$formsInfo = array();
		if(isset($_SESSION["formsInfo"])){
			$formsInfo = $_SESSION["formsInfo"];
		}
		if(!empty($formsInfo)){
			foreach($formsInfo as $forms_id=>$oneFormRow){
				$returnData[] = array('forms_id'=>$forms_id, 'form_name'=>stripslashes($oneFormRow['form_name']), 'form_public'=>$oneFormRow['form_public'], 'required'=>$oneFormRow['required']);
			}
		}
		
		if($isArray>0){
			return array('login'=>'', 'returnData'=>$returnData);
		}
		else{
			return json_encode(array('login'=>'', 'returnData'=>$returnData));
		}
	}
	
	public function prints($segment4name, $segment5name){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

		$htmlStr = "";
		$language = $_SESSION["language"]??'English';
		$repairs_id = intval($segment5name);
		$segment6name = $GLOBALS['segment6name'];
		
		$repairsObj = $this->db->query("SELECT * FROM repairs WHERE repairs_id = :repairs_id AND accounts_id = $accounts_id", array('repairs_id'=>$repairs_id),1);
		if($repairsObj){
			$repairsOneRow = $repairsObj->fetch(PDO::FETCH_OBJ);
			$repairs_id = $repairsOneRow->repairs_id;
			$Printing = new Printing($this->db);
			if($segment4name == 'label'){
				$htmlStr .= $Printing->labelsInfo('Repairs', 'HTML');
			}
			else if($segment4name == 'label_MoreInfo'){
				$jsonResponse = array();
				$jsonResponse['login'] = '';
				$Common = new Common($this->db);
				$commonInfo = $Printing->labelsInfo('Repairs', 'commonInfo');
				
				$DueDate = trim(date(str_replace('y', 'Y', $dateformat), strtotime($repairsOneRow->due_datetime))." $repairsOneRow->due_time");
				$customer_id = $repairsOneRow->customer_id;
				$Company = $FirstName = $LastName = $PhoneNo = '';
				if($customer_id>0){
					$customerObj = $this->db->query("SELECT company, first_name, last_name, contact_no FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = $customer_id", array());
					if($customerObj){
						$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);	
						$Company = $customerrow->company;
						$FirstName = $customerrow->first_name;
						$LastName = $customerrow->last_name;
						$PhoneNo = $customerrow->contact_no;
					}
				}

				$BrandModel = $MoreDeails = $ImeiSerial = '';
				$properties_id = $repairsOneRow->properties_id;
				if($properties_id>0){
					$propertiesObj = $this->db->query("SELECT * FROM properties WHERE properties_id = $properties_id AND accounts_id = $prod_cat_man AND properties_publish = 1", array());
					if($propertiesObj){
						$propertiesRow = $propertiesObj->fetch(PDO::FETCH_OBJ);	
						
						$properties_id = $propertiesRow->properties_id;
						$customers_id = $propertiesRow->customers_id;
						$brand_model_id = trim($propertiesRow->brand_model_id);
						if($brand_model_id>0){
							$brandModelObj = $this->db->query("SELECT brand, model FROM brand_model WHERE brand_model_id = $brand_model_id AND accounts_id = $prod_cat_man", array());
							if($brandModelObj){
								$brandModelRow = $brandModelObj->fetch(PDO::FETCH_OBJ);	
								$BrandModel = trim(stripslashes("$brandModelRow->brand $brandModelRow->model"));
							}
						}
						$MoreDeails = trim($propertiesRow->more_details);
						$ImeiSerial = trim($propertiesRow->imei_or_serial_no);
					}
				}
				
				$Barcode = 't'.$repairsOneRow->ticket_no;
				$Problem = $repairsOneRow->problem;
				$Password = $repairsOneRow->lock_password;

				$jsonResponse['commonInfo'] = $commonInfo;
				$jsonResponse['DueDate'] = $DueDate;
				$jsonResponse['Company'] = $Company;
				$jsonResponse['FirstName'] = $FirstName;
				$jsonResponse['LastName'] = $LastName;
				$jsonResponse['PhoneNo'] = $PhoneNo;
				$jsonResponse['BrandModel'] = $BrandModel;
				$jsonResponse['MoreDeails'] = $MoreDeails;
				$jsonResponse['ImeiSerial'] = $ImeiSerial;
				$jsonResponse['Problem'] = $Problem;
				$jsonResponse['Password'] = $Password;
				$jsonResponse['TicketNo'] = $repairsOneRow->ticket_no;
				$jsonResponse['Barcode'] = $Barcode;
				$jsonResponse['customFieldsData'] = $Common->customFormFields('repairs', $repairsOneRow->custom_data);

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
				".'
						</script>
						<script src="/assets/js-'.swVersion.'/languages/'.$language.'.js"></script>';
						if($segment6name=='signature'){					
							$htmlStr .= '<link rel="stylesheet" href="/assets/css-'.swVersion.'/style.css">';
						}

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
	
	public function AJ_prints_small_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$repairs_id = $POST['repairs_id'];
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->repairInvoicesInfo($repairs_id, 'small', 0);
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_large_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$repairs_id = $POST['repairs_id'];
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->repairInvoicesInfo($repairs_id, 'large', 0);
		
		return json_encode($jsonResponse);
	}
	
	public function AJ_prints_signature_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$repairs_id = $POST['repairs_id'];
		$Printing = new Printing($this->db);
		$jsonResponse = $Printing->repairInvoicesInfo($repairs_id, 'large', 0);
		
		return json_encode($jsonResponse);
	}
	
	public function customer(){
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		
		$htmlStr = "";
		$repairs_id = intval($GLOBALS['segment5name']);
		$customers_id = intval($GLOBALS['segment6name']);
		$customersObj = $this->db->query("SELECT first_name FROM customers WHERE customers_id = :customers_id AND accounts_id = $prod_cat_man", array('customers_id'=>$customers_id),1);
		if($customersObj){
			$Printing = new Printing($this->db);
			$customers_array = $customersObj->fetch(PDO::FETCH_OBJ);
			if($GLOBALS['segment4name'] == 'label'){
				$htmlStr .= $Printing->labelsInfo('Customer', 'HTML');
			}
			else if($GLOBALS['segment4name'] == 'label_MoreInfo'){
				$jsonResponse = array();
				$jsonResponse['login'] = '';
				$Common = new Common($this->db);
				$commonInfo = $Printing->labelsInfo('repairCustomer', 'commonInfo');
				
				$Company = $FirstName = $LastName = $PhoneNo = $custom_data = '';
				if($customers_id>0){
					$customerObj = $this->db->query("SELECT company, first_name, last_name, contact_no, custom_data FROM customers WHERE accounts_id = $prod_cat_man AND customers_id = $customers_id", array());
					if($customerObj){
						$customerrow = $customerObj->fetch(PDO::FETCH_OBJ);	
						$Company = $customerrow->company;
						$FirstName = $customerrow->first_name;
						$LastName = $customerrow->last_name;
						$PhoneNo = $customerrow->contact_no;
						$custom_data = $customerrow->custom_data;
					}
				}
				
				$TicketNo = $DueDate = '';
                $repairsObj = $this->db->query("SELECT * FROM repairs WHERE repairs_id = :repairs_id AND accounts_id = $accounts_id", array('repairs_id'=>$repairs_id),1);
        		if($repairsObj){
        			$repairsOneRow = $repairsObj->fetch(PDO::FETCH_OBJ);
        			$TicketNo = $repairsOneRow->ticket_no;
        			$DueDate = trim(date(str_replace('y', 'Y', $dateformat), strtotime($repairsOneRow->due_datetime))." $repairsOneRow->due_time");
				
        		}
        		
				$jsonResponse['commonInfo'] = $commonInfo;
				$jsonResponse['Company'] = $Company;
				$jsonResponse['FirstName'] = $FirstName;
				$jsonResponse['LastName'] = $LastName;
				$jsonResponse['PhoneNo'] = $PhoneNo;
				$jsonResponse['TicketNo'] = $TicketNo;
				$jsonResponse['DueDate'] = $DueDate;
				$jsonResponse['customFieldsData'] = $Common->customFormFields('customers', $custom_data);

				return json_encode($jsonResponse);
			}
		}
		return $htmlStr;
	}
	
	public function AJget_formsData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$table_id = intval($POST['table_id']??0);
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$returnData = array();
		if($table_id>0){				
			$formsDataObj = $this->db->query("SELECT * FROM forms_data WHERE accounts_id = $accounts_id AND table_id = $table_id ORDER BY form_name ASC", array());
			if($formsDataObj){
				while($oneFDRow = $formsDataObj->fetch(PDO::FETCH_OBJ)){
					$returnData[] = array('forms_data_id'=>intval($oneFDRow->forms_data_id), 'forms_id'=>intval($oneFDRow->forms_id), 'table_id'=>intval($oneFDRow->table_id), 'form_public'=>intval($oneFDRow->form_public), 'required'=>intval($oneFDRow->required), 'last_updated'=>$oneFDRow->last_updated, 'form_name'=>$oneFDRow->form_name);
				}
			}			
		}
		return json_encode(array('login'=>'', 'returnData'=>$returnData));
	}

	public function AJget_formDataPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$repairsData = array();
		$repairsData['login'] = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$forms_data_id = intval($POST['forms_data_id']??0);
		$forms_id = intval($POST['forms_id']??0);
		$form_public = $required = $signature = $fileCount = 0;
		$form_for = 'repairs';
		$form_definitions = $form_dataArray = array();
		$formsObj = $this->db->query("SELECT * FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = :forms_id", array('forms_id'=>$forms_id),1);
		if($formsObj){
			$formsData = $formsObj->fetch(PDO::FETCH_OBJ);
			$form_public = $formsData->form_public;
			$required = $formsData->required;
			$form_for = $formsData->form_for;
			$form_definitions = $formsData->form_definitions;
			if($form_definitions !=''){
				$form_definitions = unserialize($form_definitions);					
			}
		}
		$repairsData['form_for'] = $form_for;
		$form_name = '';
		if($forms_data_id>0 && $prod_cat_man>0){
			$forms_dataObj = $this->db->query("SELECT * FROM forms_data WHERE forms_data_id = :forms_data_id AND accounts_id = $accounts_id", array('forms_data_id'=>$forms_data_id),1);
			if($forms_dataObj){
				$forms_dataData = $forms_dataObj->fetch(PDO::FETCH_OBJ);
				$form_public = $forms_dataData->form_public;
				$form_name = stripslashes(trim((string) $forms_dataData->form_name));
				$required = $forms_dataData->required;
				$fdform_data = $forms_dataData->form_data;
				if(!empty($fdform_data)){
					$form_dataArray = unserialize($fdform_data);
				}
			}
		}
		$repairsData['form_name'] = $form_name;
					
		$formFieldsData = array();
		if(!empty($form_dataArray)){		
			foreach($form_dataArray as $field_name=>$value){
				$field_name = stripslashes(trim((string) $field_name));
				$fieldArray = explode('||', $value);
				$value = stripslashes(trim((string) $fieldArray[0]));
				$field_type=$fieldArray[1];
				$signatureCode = '';
				if($field_type=='Signature' && $value !=''){
					$dsNotesObj = $this->db->query("SELECT note FROM digital_signature WHERE digital_signature_id = $value AND accounts_id = $accounts_id", array());
					if($dsNotesObj){
						$note = trim((string) stripslashes($dsNotesObj->fetch(PDO::FETCH_OBJ)->note));
						if($note !=''){
							$signatureCode = $note;
						}
					}
				}
				$parameters = '';
				if(!empty($form_definitions) && in_array($field_type, array('TextOnly', 'SectionBreak'))){
					foreach($form_definitions as $oneFieldRow){
						$parameters=$oneFieldRow['parameters'];
					}
				}
	
				if(in_array($field_type, array('TextAreaBox', 'TextOnly'))){
					$value = nl2br($value);
				}
				elseif($field_type=='UploadImage'){
					if($value !=''){
						$value = str_replace('.png', '.jpg', $value);
						$attachedpath = '.'.$value;
						if (!file_exists($attachedpath)){$value = '';}
					}
				}
				$formFieldsData[] = array('field_name'=>$field_name, 'field_type'=>$field_type, 'value'=>$value, 'signatureCode'=>$signatureCode, 'parameters'=>$parameters);
			}
		}
		$form_definitionsData = array();
		if(!empty($form_definitions)){
			foreach($form_definitions as $oneFieldRow){
				$order_val=trim((string) $oneFieldRow['order_val']);
				$field_name = stripslashes(trim((string) $oneFieldRow['field_name']));
				$field_required=trim((string) $oneFieldRow['field_required']);
				$field_type=$oneFieldRow['field_type'];
				$parameters=$oneFieldRow['parameters'];
				if($field_type=='TextOnly'){
					$parameters = stripslashes(trim((string) nl2br($parameters)));
				}
	
				$signatureCode = $value = '';		
				if(!empty($form_dataArray) && array_key_exists($field_name, $form_dataArray)){
					$value = $form_dataArray[$field_name];
					$fieldArray = explode('||', $value);
					$value = $fieldArray[0];
				}
				
				if($field_type=='Signature' && $value !=''){
					$dsNotesObj = $this->db->query("SELECT note FROM digital_signature WHERE digital_signature_id = $value AND accounts_id = $accounts_id", array());
					if($dsNotesObj){
						$note = trim((string) stripslashes($dsNotesObj->fetch(PDO::FETCH_OBJ)->note));
						if($note !=''){
							$signatureCode = $note;
						}
					}
				}
				if(in_array($field_type, array('TextAreaBox', 'TextOnly'))){
					$value = nl2br($value);
				}
				elseif($field_type=='UploadImage' && $value !=''){
					$value = str_replace('.png', '.jpg', $value);
					$attachedpath = '.'.$value;
					if (!file_exists($attachedpath)){$value = '';}
				}	
	
				$form_definitionsData[] = array('order_val'=>$order_val, 'field_type'=>$field_type, 'field_name'=>$field_name, 'field_required'=>$field_required, 'parameters'=>$parameters, 'signatureCode'=>$signatureCode, 'value'=>$value);
			}
		}
		
		$repairsData['form_public'] = $form_public;
		$repairsData['required'] = $required;
		$repairsData['formFieldsData'] = $formFieldsData;
		$repairsData['form_definitionsData'] = $form_definitionsData;
	
		return json_encode($repairsData);
	}
	
	public function AJsave_formsData(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = 'error';
		$message = $returnStr = '';
		$form_for = 'repairs';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		
		$newforms_data_id = 0;
		$forms_data_id = intval($POST['forms_data_id']??0);
		$table_id = intval($POST['table_id']??0);
		$forms_id = intval($POST['forms_id']??0);
		$form_name = $this->db->checkCharLen('forms_data.form_name', $POST['form_name']??'');
		$form_public = intval($POST['form_public']??0);
		$required = intval($POST['required']??0);
		
		if($table_id==0){
			$savemsg = '';
			$formsInfo = array();
			if(isset($_SESSION["formsInfo"])){
				$formsInfo = $_SESSION["formsInfo"];
			}
			$forms_dataarray = $form_data = array();
			$conditionarray['form_name'] = $form_name;
			$conditionarray['form_public'] = $form_public;
			$conditionarray['required'] = $required;
			
			$formsObj = $this->db->query("SELECT form_definitions FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = :forms_id", array('forms_id'=>$forms_id),1);
			if($formsObj){
				$form_definitions = $formsObj->fetch(PDO::FETCH_OBJ)->form_definitions;
				if($form_definitions !=''){
					$form_definitions = unserialize($form_definitions);
					if(is_array($form_definitions)){
						
						foreach($form_definitions as $oneFieldRow){
							$order_val = trim((string) $oneFieldRow['order_val']);
							$field_name = trim((string) $oneFieldRow['field_name']);
							$field_type = trim((string) $oneFieldRow['field_type']);
							
							if(array_key_exists('ff'.$order_val, $POST)){
								$form_data[$field_name] = trim((string) $POST['ff'.$order_val]).'||'.$field_type;
							}									
						}
					}
				}
			}
			$conditionarray['form_data'] = serialize($form_data);
			$formsInfo[$forms_id] = $conditionarray;
			$_SESSION["formsInfo"] = $formsInfo;
			
		}
		else{
			
			if($forms_data_id==0 && $forms_id>0 && $form_name !=''){
				$totalrows = 0;
				$queryProblemObj = $this->db->query("SELECT COUNT(forms_data_id) AS totalrows FROM forms_data WHERE accounts_id = $accounts_id AND table_id = :table_id AND form_name = :form_name", array('table_id'=>$table_id, 'form_name'=>$form_name));
				if($queryProblemObj){
					$totalrows = $queryProblemObj->fetch(PDO::FETCH_OBJ)->totalrows;						
				}
				if($totalrows>0){
					$message = 'duplicateFormName';
				}
				else{
					$fdData = array('created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> '1000-01-01 00:00:00',
									'accounts_id'=> $accounts_id,
									'user_id'=> $user_id,
									'forms_id'=> $forms_id,
									'table_id'=> $table_id,
									'form_name'=> $form_name,
									'form_public'=> $form_public,
									'required'=> $required,
									'form_data'=>'');						
					$forms_data_id = $newforms_data_id = $this->db->insert('forms_data', $fdData);
				
					if($forms_data_id){
						$changed = array("\"$form_name\" ".$this->db->translate('Forms data added successfully.'));
												
						if(!empty($changed)){
							$moreInfo = $teData = array();
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $_SESSION["accounts_id"];
							$teData['user_id'] = $_SESSION["user_id"];
							$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'repairs');
							$teData['record_id'] = $table_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);							
						}
					}
				}
			}			
			
			if($forms_id>0 && $forms_data_id>0 && $form_name !=''){				
				
				$totalrows = 0;
				if($newforms_data_id==0){
					$queryProblemObj = $this->db->query("SELECT COUNT(forms_data_id) AS totalrows FROM forms_data WHERE accounts_id = $accounts_id AND table_id = :table_id AND form_name = :form_name AND forms_data_id != :forms_data_id", array('table_id'=>$table_id, 'form_name'=>$form_name, 'forms_data_id'=>$forms_data_id));
					if($queryProblemObj){
						$totalrows = $queryProblemObj->fetch(PDO::FETCH_OBJ)->totalrows;						
					}
				}
				
				if($totalrows>0){$message .= 'duplicateFormName';}
				else{
					
					$conditionarray = $form_data = array();
					$conditionarray['form_name'] = $form_name;
					$formsObj = $this->db->query("SELECT forms_id, form_definitions FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = :forms_id", array('forms_id'=>$forms_id),1);
					if($formsObj){
						$formsOneRow = $formsObj->fetch(PDO::FETCH_OBJ);
						$forms_id = $formsOneRow->forms_id;
						$form_definitions = $formsOneRow->form_definitions;
						if($form_definitions !=''){
							$form_definitions = unserialize($form_definitions);
							if(is_array($form_definitions)){
								
								foreach($form_definitions as $oneFieldRow){
									$order_val=trim((string) $oneFieldRow['order_val']);
									$field_name=trim((string) $oneFieldRow['field_name']);
									$field_type=trim((string) $oneFieldRow['field_type']);
									
									if(array_key_exists('ff'.$order_val, $POST)){
										$fieldNameVal = trim((string) $POST['ff'.$order_val]);
										$form_data[$field_name] = $fieldNameVal.'||'.$field_type;
										if($field_type=='UploadImage'){
											$strPos = strpos($fieldNameVal, '_'.$table_id.'_forms_'.$forms_id.'_data_0_ID_');
											if($strPos !==false){
												$picName = str_replace('/assets/accounts/a_'.$accounts_id.'/', '', $fieldNameVal);
												$attachedpath = './assets/accounts/a_'.$accounts_id.'/'.$picName;
												if (!file_exists($attachedpath)){
													$form_data[$field_name] = '';
													$returnStr = 'Picture Not Exits: '.$picName;
												}
												else{
													$picturesrc = str_replace('./', '/', $attachedpath);
													if (extension_loaded('imagick')){
														$im = new Imagick($_SERVER['DOCUMENT_ROOT'] . $picturesrc);
														$im->optimizeImageLayers(); // Optimize the image layers			
														$im->setImageCompression(Imagick::COMPRESSION_JPEG);// Compression and quality
														$im->setImageCompressionQuality(0);
														$picturesrc1 = str_replace('_data_0_ID_', '_data_'.$forms_data_id.'_ID_', $picturesrc);
														$returnStr = 'Picture Exits2: '.$picturesrc1;
														
														$im->writeImages($_SERVER['DOCUMENT_ROOT'] . $picturesrc1, true);// Write the image back
														unlink('.'.$picturesrc); // delete file
														$form_data[$field_name] = str_replace('_data_0_ID_', '_data_'.$forms_data_id.'_ID_', $form_data[$field_name]);
													}
												}
											}
										}
									}
								}
							}
						}
					}
					
					$conditionarray['form_public'] = $form_public;
					$conditionarray['required'] = $required;				
					$conditionarray['form_data'] = serialize($form_data);					
					$conditionarray['last_updated'] = date('Y-m-d H:i:s');
					$conditionarray['user_id'] = $user_id;
					
					$oneTRowObj = $this->db->querypagination("SELECT * FROM forms_data WHERE forms_data_id = $forms_data_id", array());
					$update = $this->db->update('forms_data', $conditionarray, $forms_data_id);
					if($update){
						$changed = array();
						if($oneTRowObj){
							unset($conditionarray['last_updated']);
							unset($conditionarray['user_id']);
							foreach($conditionarray as $fieldName=>$fieldValue){
								$prevFieldVal = $oneTRowObj[0][$fieldName];
								if($prevFieldVal != $fieldValue){
									if($prevFieldVal=='1000-01-01'){$prevFieldVal = '';}
									if($fieldValue=='1000-01-01'){$fieldValue = '';}
									if($fieldName=='form_data'){
										
										$form_data1 = $form_data2 = array();
										if(!empty($prevFieldVal)){$form_data1 = unserialize($prevFieldVal);}
										if(!empty($fieldValue)){$form_data2 = unserialize($fieldValue);}
										if(!empty($form_data1) || !empty($form_data2)){
											
											if(!empty($form_data1) && !empty($form_data2)){
												$mergeData = array_merge_recursive($form_data1, $form_data2);
													
												foreach($mergeData as $mKey=>$mValue){
													if(array_key_exists($mKey, $form_data1) && array_key_exists($mKey, $form_data2)){
														$twoData = $mValue;
														
														if($mValue[0] ==$mValue[1]){
															unset($form_data1[$mKey]);
															unset($form_data2[$mKey]);
														}
														if(strpos($mValue[0], 'assets/accounts/') !==false){
															unset($form_data1[$mKey]);
															unset($form_data2[$mKey]);
														}
													}
												}
											}
											elseif(!empty($form_data1)){
												foreach($form_data1 as $mKey=>$mValue){
													if($form_data1[$mKey] == ''){
														unset($form_data1[$mKey]);
													}
													if(strpos($mValue, 'assets/accounts/') !==false){
														unset($form_data1[$mKey]);
													}
												}
											}
											elseif(!empty($form_data2)){
												foreach($form_data2 as $mKey=>$mValue){
													if($form_data2[$mKey] == ''){
														unset($form_data2[$mKey]);
													}
													if(strpos($mValue, 'assets/accounts/') !==false){
														unset($form_data2[$mKey]);
													}
												}
											}
													
											if(!empty($form_data1)){$prevFieldVal = serialize($form_data1);}
											else{$prevFieldVal = '';}
											if(!empty($form_data2)){$fieldValue = serialize($form_data2);}
											else{$fieldValue = '';}									
										}
									}
									if($prevFieldVal != $fieldValue){
										$changed[$fieldName] = array($prevFieldVal, $fieldValue);
									}
								}
							}						
						}
						
						if(!empty($changed)){
							$moreInfo = $teData = array();
							$teData['created_on'] = date('Y-m-d H:i:s');
							$teData['accounts_id'] = $_SESSION["accounts_id"];
							$teData['user_id'] = $_SESSION["user_id"];
							$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'repairs');
							$teData['record_id'] = $table_id;
							$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
							$this->db->insert('track_edits', $teData);							
						}
						$savemsg = 'Save';
					}
				}
			}
		}
	
		$array = array( 'login'=>'', 'savemsg'=>$savemsg, 'message'=>$message, 'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	public function AJget_formFieldsPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$repairsData = array();
		$repairsData['login'] = '';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$forms_data_id = intval($POST['forms_data_id']??0);
		$forms_id = intval($POST['forms_id']??0);
		$form_public = $required = $signature = $fileCount = 0;
		$form_name = '';
		$form_definitions = $form_dataArray = array();
		$formsObj = $this->db->query("SELECT * FROM forms WHERE accounts_id = $prod_cat_man AND forms_id = :forms_id", array('forms_id'=>$forms_id),1);
		if($formsObj){
			$formsData = $formsObj->fetch(PDO::FETCH_OBJ);
			$form_public = $formsData->form_public;
			$form_name = stripslashes($formsData->form_name);
			$required = $formsData->required;
			$form_definitions = $formsData->form_definitions;
			if($form_definitions !=''){
				$form_definitions = unserialize($form_definitions);					
			}
		}
		
		if($forms_data_id>0 && $prod_cat_man>0){
			$forms_dataObj = $this->db->query("SELECT * FROM forms_data WHERE forms_data_id = :forms_data_id AND accounts_id = $accounts_id", array('forms_data_id'=>$forms_data_id),1);
			if($forms_dataObj){
				$forms_dataData = $forms_dataObj->fetch(PDO::FETCH_OBJ);
				$form_public = $forms_dataData->form_public;
				$form_name = stripslashes($forms_dataData->form_name);
				$required = $forms_dataData->required;
				$form_data = $forms_dataData->form_data;
				if(!empty($form_data)){
					$form_dataArray = unserialize($form_data);
				}
			}
		}
		
		$repairsData['form_name'] = $form_name;
		$formFieldsData = array();
		$digital_signature_id = "";
		if(!empty($form_definitions)){
			foreach($form_definitions as $oneFieldRow){
				$order_val=trim((string) $oneFieldRow['order_val']);
				$field_name= $oneFieldRow['field_name'];
				$field_required=trim((string) $oneFieldRow['field_required']);			
				$field_type=$oneFieldRow['field_type'];
				$parameters=$oneFieldRow['parameters'];
				if($field_type=='TextOnly'){$parameters=stripslashes(trim((string) nl2br($parameters)));}
				
				$value = '';
				if(!empty($form_dataArray) && array_key_exists($field_name, $form_dataArray)){
					$value = $form_dataArray[$field_name];
					$fieldArray = explode('||', $value);
					$value = $fieldArray[0];						
				}
				if($field_type=='Signature' && $value !=''){
					$dsNotesObj = $this->db->query("SELECT note FROM digital_signature WHERE digital_signature_id = $value AND accounts_id = $accounts_id", array());
					if($dsNotesObj){
						$note = trim((string) stripslashes($dsNotesObj->fetch(PDO::FETCH_OBJ)->note));
						if($note !=''){
							$digital_signature_id = $value;
							$value = $note;
						}
					}
				}
				if($field_type=='UploadImage' && $value !=''){
					$attachedpath = '.'.$value;
					if (!file_exists($attachedpath)){$value = '';}
				}					
				
				$formFieldsData[] = array('order_val'=>$order_val, 'field_name'=>$field_name, 'field_required'=>$field_required, 'field_type'=>$field_type, 'parameters'=>$parameters,'value'=>$value, 'digital_signature_id'=>$digital_signature_id);
			}
		}
		
		$repairsData['form_public'] = $form_public;
		$repairsData['required'] = $required;
		$repairsData['formFieldsData'] = $formFieldsData;

		return json_encode($repairsData);
	}
		
	public function formsprints(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$form_for = $GLOBALS['segment4name'];
		$table_id = $GLOBALS['segment5name'];
		$language = $_SESSION["language"]??'English';
		$currency = $_SESSION["currency"]??'৳';
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		if(strcmp(strtoupper($dateformat), 'D-M-Y')==0){$calenderDate = 'DD-MM-YYYY';}
		else{$calenderDate = 'MM/DD/YYYY';}
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$loadLangFile = $_SESSION["language"]??'English';

		$htmlStr = '';
		$sqlquery = "SELECT fd.forms_data_id FROM forms_data fd, forms fs WHERE fd.table_id = :table_id AND fd.accounts_id = $accounts_id AND fs.form_for = :form_for AND fd.forms_id = fs.forms_id ORDER BY fd.created_on DESC";		
		$repairsObj = $this->db->query($sqlquery, array('table_id'=>$table_id, 'form_for'=>$form_for));
		if($repairsObj){
			$htmlStr .= '<!DOCTYPE html>
			<html>
			<head>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<meta name="description" content="'.COMPANYNAME.' is a operating system that manages all of the important aspects of running a cell phone retail and cell phone repair store." />
				<meta name="keywords" content="Smartphone Inventory | Repair Ticketing System | Warranty Management Service | Customers Relation Management | Point of Sale" />
				<script language="JavaScript" type="text/javascript">var currency = \''.$currency.'\';var calenderDate = \''.$calenderDate.'\';var timeformat = \''.$timeformat.'\';
				var langModifiedData = {};
				var OS;
				var loadLangFile = \''.$loadLangFile.'\';
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

	public function AJ_formsprints_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$form_for = $POST['form_for'];
		$table_id = $POST['table_id'];
		$form_public = $POST['form_public'];
		$viewfor = $POST['viewfor'];
		$forms_data_id = $POST['forms_data_id'];
		
		$Printing = new Printing($this->db);
		$tableData = $Printing->getPublicFormData($form_for, $table_id, $form_public, $viewfor, $forms_data_id, 1);
		$jsonResponse = array( 'login'=>'', 'tableData'=>$tableData);
		return json_encode($jsonResponse);
	}
	
	public function AJget_propertiesOpt(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$customers_id = intval($POST['customers_id']??0);
	
		$returnStr = "<option value=\"\"></option>";
		if($customers_id>0){
			$cPSql = "SELECT p.properties_id, bm.brand, bm.model, p.more_details, p.imei_or_serial_no FROM properties p, brand_model bm WHERE p.accounts_id = $prod_cat_man AND p.customers_id = :customers_id AND p.brand_model_id = bm.brand_model_id AND p.properties_publish = 1 GROUP BY p.properties_id ORDER BY bm.brand ASC, bm.model ASC, p.more_details ASC, p.imei_or_serial_no ASC";
			$cPObj = $this->db->query($cPSql, array('customers_id'=>$customers_id),1);
			if($cPObj){
				while($oneRow = $cPObj->fetch(PDO::FETCH_OBJ)){
					$optionLabel = stripslashes(trim("$oneRow->brand $oneRow->model $oneRow->more_details $oneRow->imei_or_serial_no"));
					if($optionLabel !=''){
						$returnStr .= "<option value=\"$oneRow->properties_id\">$optionLabel</option>";
					}
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	//================Joining Class==============//
	public function AJget_CustomersPopup(){
		$Customers = new Customers($this->db);
		return $Customers->AJget_CustomersPopup();
	}
	
	public function AJsave_Customers(){
		$Customers = new Customers($this->db);
		return $Customers->AJsave_Customers();
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

	public function AJget_propertiesPopup(){
		$Customers = new Customers($this->db);
		return $Customers->AJget_propertiesPopup();
	}
	
	public function AJsave_properties(){
		$Customers = new Customers($this->db);
		return $Customers->AJsave_properties();
	}
	
	public function AJget_modelOpt(){
		$Customers = new Customers($this->db);
		return $Customers->AJget_modelOpt();
	}
}
?>