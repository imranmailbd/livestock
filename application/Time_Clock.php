<?php
class Time_Clock{
	protected $db;
	private int $page, $totalRows, $user_id;
	private string $data_type, $sorting_type, $keyword_search;
	
	public function __construct($db){$this->db = $db;}
		
	public function lists(){
		$timeclock_enabled = 0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		if(isset($_SESSION["timeclock_enabled"])){$timeclock_enabled = $_SESSION["timeclock_enabled"];}
		$sql = "SELECT timeclock_enabled FROM accounts WHERE accounts_id = $accounts_id";
		$queryObj = $this->db->query($sql, array());
		if($queryObj){
			$timeclock_enabled = $queryObj->fetch(PDO::FETCH_OBJ)->timeclock_enabled;
		}
		$_SESSION["timeclock_enabled"] = $timeclock_enabled;

		return '<input type="hidden" id="timeclock_enableds" value="'.$timeclock_enabled.'">';
	}
	
	public function view(){}

    private function filterAndOptions(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$sdata_type = $this->data_type;
		$sorting_type = $this->sorting_type;
		$keyword_search = $this->keyword_search;
		
		$_SESSION["current_module"] = "Time_Clock";
		$_SESSION["list_filters"] = array('sdata_type'=>$sdata_type, 'sorting_type'=>$sorting_type, 'keyword_search'=>$keyword_search);
		
		$filterSql = '';
		$bindData = array();
		if($keyword_search !=''){
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', user_first_name, user_last_name, employee_number, pin)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$totalRows = 0;
		$sqlPublish = " AND user_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND user_publish = 0";
		}
		$sql = "SELECT COUNT(user_id) AS totalrows FROM user WHERE accounts_id = $accounts_id $sqlPublish $filterSql";
		$queryObj = $this->db->query($sql, $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		$this->totalRows = $totalRows;
	}
	
    private function loadTableRows(){
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$currency = $_SESSION["currency"]??'৳';            
		$limit = $_SESSION["limit"];
		
		$page = $this->page;
		$totalRows = $this->totalRows;
		$sdata_type = $this->data_type;
		$ssorting_type = $this->sorting_type;			
		$keyword_search = $this->keyword_search;
		
		$sortingTypeData = array(0=>'TRIM(UPPER(CONCAT_WS(\' \', user_first_name, user_last_name))) ASC', 
								1=>'user_first_name ASC', 
								2=>'user_last_name ASC');
		if(empty($ssorting_type) || !array_key_exists($ssorting_type, $sortingTypeData)){
			$ssorting_type = 0;
			$this->sorting_type = $ssorting_type;
		}
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		$filterSql = "";
		$bindData = array();
		if($keyword_search !=''){
			$keyword_search = addslashes(trim((string) $keyword_search));
			if ( $keyword_search == "" ) { $keyword_search = " "; }
			$keyword_searches = explode (" ", $keyword_search);
			if ( strpos($keyword_search, " ") === false ) {$keyword_searches[0] = $keyword_search;}
			$num = 0;
			while ( $num < sizeof($keyword_searches) ) {
				$filterSql .= " AND TRIM(CONCAT_WS(' ', user_first_name, user_last_name, employee_number, pin)) LIKE CONCAT('%', :keyword_search$num, '%')";
				$bindData['keyword_search'.$num] = trim((string) $keyword_searches[$num]);
				$num++;
			}
		}
		
		$sqlPublish = " AND user_publish = 1";
		if($sdata_type=='Archived'){
			$sqlPublish = " AND user_publish = 0";
		}
		$sqlquery = "SELECT user_id, accounts_id, created_on, user_first_name, user_last_name, user_email, employee_number AS userEmpNo, pin FROM user WHERE accounts_id = $accounts_id $filterSql $sqlPublish";
		$sqlquery .= " ORDER BY ".$sortingTypeData[$ssorting_type];
		$sqlquery .= " LIMIT $starting_val, $limit";
		$query = $this->db->querypagination($sqlquery, $bindData);

		$tabledata = array();
		if($query){
			foreach($query as $oneRow){
				$user_id = $oneRow['user_id'];
				$name = stripslashes($oneRow['user_first_name']);
				$last_name = stripslashes($oneRow['user_last_name']);
				if($name !=''){$name .= ' ';}
				$name .= $last_name;

				$user_email = $oneRow['user_email'];
				if(!empty($user_email) && strlen($user_email)>=4){$name .= " (from User)";}
				$userEmpNo = '&nbsp;';
				if(!empty($oneRow['userEmpNo'])){$userEmpNo = $oneRow['userEmpNo'];}
				$pin = $oneRow['pin'];
				if($pin==''){$pin = '&nbsp;';}
				
				$tabledata[] = array($user_id, $name, $userEmpNo, $pin);
			}
		}
		
		return $tabledata;
    }
	
	public function AJgetPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = intval($POST['user_id']??0);
		$userData = array();
		$userData['login'] = '';
		$userData['user_id'] = 0;
		$userData['user_first_name'] = '';
		$userData['user_last_name'] = '';
		$userData['userEmpNo'] = '';
		$userData['pin'] = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		if($accounts_id>0 && $user_id>0){
			$userObj = $this->db->query("SELECT user_first_name, user_last_name, employee_number AS userEmpNo, pin FROM user WHERE user_id = :user_id AND accounts_id = $accounts_id AND user_publish = 1", array('user_id'=>$user_id),1);
			if($userObj){
				$userRow = $userObj->fetch(PDO::FETCH_OBJ);

				$userData['user_id'] = $user_id;
				$userData['user_first_name'] = trim((string) $userRow->user_first_name);
				$userData['user_last_name'] = trim((string) $userRow->user_last_name);
				$userData['userEmpNo'] = trim((string) $userRow->userEmpNo);
				$userData['pin'] = trim((string) $userRow->pin);
			}
		}
		return json_encode($userData);
	}
	
	public function AJsaveTime_Clock(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$user_id =0;
		$savemsg = $returnStr = '';
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;

		$user_id = intval($POST['user_id']??0);
		
		$saveData = array();
		$saveData['employee_number'] = $userEmpNo = $this->db->checkCharLen('user.employee_number', $POST['userEmpNo']??'');
		$saveData['pin'] = $this->db->checkCharLen('user.pin', $POST['pin']??'');
		$saveData['user_first_name'] = $user_first_name = $this->db->checkCharLen('user.user_first_name', $POST['user_first_name']??'');
		$saveData['user_last_name'] = $user_last_name = $this->db->checkCharLen('user.user_last_name', $POST['user_last_name']??'');
		$saveData['last_updated'] = date('Y-m-d H:i:s');
		
		$duplSql = "SELECT COUNT(user_id) AS totalrows FROM user WHERE accounts_id = $accounts_id AND employee_number = :userEmpNo AND user_publish = 1";
		$bindData = array('userEmpNo'=>$userEmpNo);
		if($user_id>0){
			$duplSql .= " AND user_id != :user_id";
			$bindData['user_id'] = $user_id;
		}
		$duplRows = 0;
		$duptObj = $this->db->query($duplSql, $bindData);
		if($duptObj){
			$duplRows = $duptObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		if($duplRows>0){
			$savemsg = 'error';
			$returnStr .= 'Name_Already_Exist';
		}
		else{
			if($user_id==0){
				$saveData['accounts_id'] = $accounts_id;
				$saveData['password_hash'] = '';
				$saveData['changepass_link'] = '';
				$saveData['user_email'] = '';
				$saveData['user_roll'] = '';
				$saveData['lastlogin_datetime'] = '1000-01-01 00:00:00';
				$saveData['popup_message'] = '';
				$saveData['login_message'] = '';
				$saveData['login_ck_id'] = '';
				$saveData['is_admin'] = 0;
				$saveData['created_on'] = date('Y-m-d H:i:s');
				$saveData['last_request'] = '1000-01-01 00:00:00';
				$user_id = $this->db->insert('user', $saveData);
				if(!$user_id){
					$savemsg = 'error';
					$returnStr .= 'errorOnAdding';
				}
			}
			else{
				$update = $this->db->update('user', $saveData, $user_id);
				if($update){
					$note_for = $this->db->checkCharLen('notes.note_for', 'user');
					$noteData=array('table_id'=> $user_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $_SESSION["accounts_id"],
									'user_id'=> $_SESSION["user_id"],
									'note'=> $this->db->translate('Employee was edited'),
									'publics'=>0);
					$notes_id = $this->db->insert('notes', $noteData);
					
				}
				else{
					$savemsg = 'error';
				}
			}
		}

		$array = array( 'login'=>'', 'user_id'=>$user_id,
						'savemsg'=>$savemsg,
						'returnStr'=>$returnStr);
		return json_encode($array);
	}
	
	public function AJgetHPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$timeformat = $_SESSION['timeformat']??'12 hour';

		$time_clock_id = intval($POST['time_clock_id']??0);
		$tuser_id = intval($POST['tuser_id']??0);
		$timeClockData = array();
		$timeClockData['login'] = '';
		$timeClockData['time_clock_id'] = 0;
		$timeClockData['clock_in_date'] = date('Y-m-d');
		if($timeformat=='12 hour'){
			$timeClockData['clock_in_hour'] = date('h');
		}
		else{
			$timeClockData['clock_in_hour'] = date('H');
		}
		$timeClockData['clock_in_minute'] = date('i');
		$timeClockData['clock_in_ampm'] = date('A');
		$timeClockData['clock_out_date'] = '';
		$timeClockData['clock_out_hour'] = '';
		$timeClockData['clock_out_minute'] = '';
		$timeClockData['clock_out_ampm'] = date('A');
		$timeClockData['userEmpNo'] = '';
		$timeClockData['user_id'] = $tuser_id;
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$userObj = $this->db->query("SELECT employee_number AS userEmpNo FROM user WHERE user_id = $tuser_id", array(),1);
		if($userObj){
			$timeClockData['userEmpNo'] = $userObj->fetch(PDO::FETCH_OBJ)->userEmpNo;
		}
		if($accounts_id>0 && $time_clock_id>0){

			$time_clockObj = $this->db->query("SELECT * FROM time_clock WHERE time_clock_id = :time_clock_id AND accounts_id = $accounts_id", array('time_clock_id'=>$time_clock_id),1);
			if($time_clockObj){
				$time_clockRow = $time_clockObj->fetch(PDO::FETCH_OBJ);
				$timeClockData['time_clock_id'] = $time_clockRow->time_clock_id;
				$timeClockData['user_id'] = $user_id = $time_clockRow->user_id;
				
				if(!in_array($time_clockRow->clocked_in, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
					$timeClockData['clock_in_date'] = $time_clockRow->clocked_in;
					
					if($timeformat=='12 hour'){
						$timeClockData['clock_in_hour'] = date('h', strtotime($time_clockRow->clocked_in));
					}
					else{
						$timeClockData['clock_in_hour'] = date('H', strtotime($time_clockRow->clocked_in));
					}
					
					$timeClockData['clock_in_minute'] = date('i', strtotime($time_clockRow->clocked_in));
					$timeClockData['clock_in_ampm'] = date('A', strtotime($time_clockRow->clocked_in));
				}
				if(!in_array($time_clockRow->clocked_out, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
					$timeClockData['clock_out_date'] = $time_clockRow->clocked_out;
					if($timeformat=='12 hour'){
						$timeClockData['clock_out_hour'] = date('h', strtotime($time_clockRow->clocked_out));
					}
					else{
						$timeClockData['clock_out_hour'] = date('H', strtotime($time_clockRow->clocked_out));
					}
					$timeClockData['clock_out_hour'] = date('h', strtotime($time_clockRow->clocked_out));
					$timeClockData['clock_out_minute'] = date('i', strtotime($time_clockRow->clocked_out));
					$timeClockData['clock_out_ampm'] = date('A', strtotime($time_clockRow->clocked_out));
				}
			}
		}
		return json_encode($timeClockData);
	}
	
	public function AJupdateDefaultTimeClock(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$fieldval = intval($POST['fieldval']??0);
		$update = $this->db->update('accounts', array('timeclock_enabled'=>$fieldval), $accounts_id);
		if($update){
			$_SESSION["timeclock_enabled"] = $fieldval;
			$returnStr = 1;
		}
		else{$returnStr = 0;}
		
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr, 'fieldval'=>$fieldval, 'timeclock_enabled'=>$_SESSION["timeclock_enabled"]));		
	}
	
	public function AJupdate_Time_Clock(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$accounts_id = $_SESSION["accounts_id"]??0;
		$time_clock_id = intval($POST['time_clock_id']??0);
		$tuser_id = intval($POST['tuser_id']??0);
		$returnStr = 'Error';
	
		if(empty($tuser_id)){
			$returnStr = 'timeClockMissing';
		}
		else{
			$timeClockData = array();
			$clock_in_date = $POST['clock_in_date']??'';
			$clock_in_hour = $POST['clock_in_hour']??0;
			$clock_in_minute = $POST['clock_in_minute']??0;
			$clock_in_ampm = $POST['clock_in_ampm']??'AM';
			if($clock_in_ampm=='PM' && $clock_in_hour<12){$clock_in_hour += 12;}
			else if($clock_in_ampm=='AM' && $clock_in_hour==12){$clock_in_hour = 0;}
			
			if($clock_in_hour==24){$clock_in_hour = 0;}
			$timeClockData['clocked_in'] = $clocked_in = date('Y-m-d H:i:s');
			if($clock_in_date !=''){
				$timeClockData['clocked_in'] = $clocked_in = date('Y-m-d H:i:s', strtotime("$clock_in_date $clock_in_hour:$clock_in_minute:00"));
			}

			$clock_out_date = $POST['clock_out_date']??'';
			$clock_out_hour = intval($POST['clock_out_hour']??0);
			$clock_out_minute = intval($POST['clock_out_minute']??0);
			$clock_out_ampm = $POST['clock_out_ampm']??'AM';
			if($clock_out_ampm=='PM' && $clock_out_hour<12){$clock_out_hour += 12;}
			else if($clock_out_ampm=='AM' && $clock_out_hour==12){$clock_out_hour = 0;}
			if($clock_out_hour==24){$clock_out_hour = 0;}
			$timeClockData['clocked_out'] = '1000-01-01 00:00:00';
			if($clock_out_date !=''){
				$timeClockData['clocked_out'] = date('Y-m-d H:i:s', strtotime("$clock_out_date $clock_out_hour:$clock_out_minute:00"));
			}
			
			if($time_clock_id==0){
				$timeClockData['accounts_id'] = $accounts_id;
				$timeClockData['user_id'] = $tuser_id;

				$time_clock_id = $this->db->insert('time_clock', $timeClockData);
				$returnStr = 'Add';
			}
			else{
				$this->db->update('time_clock', $timeClockData, $time_clock_id);
				
				$changed = array();
				$queryObj = $this->db->query("SELECT * FROM time_clock WHERE accounts_id = $accounts_id AND time_clock_id = :time_clock_id", array('time_clock_id'=>$time_clock_id));
				if($queryObj){
					$tcOneRow = $queryObj->fetch(PDO::FETCH_OBJ);
					if($tcOneRow->clocked_in != $timeClockData['clocked_in']){
						$changed['clocked_in'] = array($tcOneRow->clocked_in, $timeClockData['clocked_in']);
					}
					if(array_key_exists('clocked_out', $timeClockData)){
						if($tcOneRow->clocked_out != $timeClockData['clocked_out']){
							$changed['clocked_out'] = array($tcOneRow->clocked_out, $timeClockData['clocked_out']);
						}
					}
				}
				
				$user_id = 0;
				$queryObj = $this->db->query("SELECT user_id FROM user WHERE accounts_id = $accounts_id AND user_id = :tuser_id", array('tuser_id'=>$tuser_id));
				if($queryObj){
					$user_id = $queryObj->fetch(PDO::FETCH_OBJ)->user_id;
				}
				
				if(!empty($changed)){
					$moreInfo = array('description'=>$this->db->translate('Time clock was edited.'));
					$teData = array();
					$teData['created_on'] = date('Y-m-d H:i:s');
					$teData['accounts_id'] = $_SESSION["accounts_id"];
					$teData['user_id'] = $_SESSION["user_id"];
					$teData['record_for'] = $this->db->checkCharLen('track_edits.record_for', 'user');
					$teData['record_id'] = $user_id;
					$teData['details'] = json_encode(array('changed'=>$changed, 'moreInfo'=>$moreInfo));
					$this->db->insert('track_edits', $teData);						
				}
				else{
					$note_for = $this->db->checkCharLen('notes.note_for', 'user');
					$noteData=array('table_id'=> $user_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $_SESSION["accounts_id"],
									'user_id'=> $_SESSION["user_id"],
									'note'=> $this->db->translate('Time clock was edited.')." there is no changes made.",
									'publics'=>0);
					$notes_id = $this->db->insert('notes', $noteData);
				}
				
				$returnStr = 'Update';
			}
		}
		echo json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	private function filterHAndOptions(){
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $this->user_id;
		
		$bindData = array();
		$bindData['user_id'] = $user_id;
		$filterSql = "SELECT COUNT(time_clock_id) AS totalrows FROM time_clock WHERE accounts_id = $accounts_id AND user_id = :user_id";
		$totalRows = 0;
		$queryObj = $this->db->query($filterSql, $bindData);
		if($queryObj){
			$totalRows = $queryObj->fetch(PDO::FETCH_OBJ)->totalrows;
		}
		
		$this->totalRows = $totalRows;
		
	}
	
    private function loadHTableRows(){

		$limit = $_SESSION["limit"];
		$page = $this->page;
		$totalRows = $this->totalRows;
		$user_id = $this->user_id;
		
		$starting_val = ($page-1)*$limit;
		if($starting_val>$totalRows){$starting_val = 0;}
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$tabledata = array();
		
		if($user_id>0){
			$bindData = array();			
			$filterSql = "SELECT * FROM time_clock WHERE accounts_id = $accounts_id  AND user_id = $user_id ORDER BY clocked_in DESC LIMIT $starting_val, $limit";
			$query = $this->db->querypagination($filterSql, $bindData);
			if($query){
				foreach($query as $oneRow){
					$time_clock_id = intval($oneRow['time_clock_id']);
					$clocked_in = $oneRow['clocked_in'];
					$clocked_out = $oneRow['clocked_out'];

					$weekDay = $clockInDate = $clockInTime = '';
					if(!in_array($clocked_in, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
						$weekDay = date('l', strtotime($clocked_in));
						$clockInDate = $clocked_in;
						$clockInTime = $clocked_in;
					}
					$clockOutDate = $clockOutTime = $times = '';
					if(!in_array($clocked_out, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
						$clockOutDate = $clocked_out;
						$clockOutTime = $clocked_out;

						$totalTimePerDay = strtotime($clocked_out)-strtotime($clocked_in);
						$hours = 0;
						$minutes = 0;
						if($totalTimePerDay>0){
							$totalMinutes = floor($totalTimePerDay/60);
							if($totalMinutes>0){
								$hours = floor($totalMinutes/60);
								$minutes = ($totalMinutes%60);
							}
						}

						$times = "$hours hrs $minutes min";
					}

					$tabledata[] = array($time_clock_id, $weekDay, $clockInDate, $clockInTime, $clockOutDate, $clockOutTime, $times);
				}
			}
		}

		return $tabledata;
    }
 	
	public function report(){}

	public function fetching_reportdata(){
		$POST = json_decode(file_get_contents('php://input'), true);
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$currency = $_SESSION["currency"]??'৳';
		$date_range = $POST['date_range']??'';
		$showing_type = $POST['showing_type']??'';

		$startdate = '';
		$startdate1 = '';
		$enddate = '';
		$enddate1 = '';
		if($date_range !='' && $date_range !='null'){
			$date_rangearray = explode(' - ', $date_range);
			if(is_array($date_rangearray) && count($date_rangearray)>1){
				$startdate = date('Y-m-d',strtotime($date_rangearray[0])).' 00:00:00';
				$startdate1 = date('Y-m-d',strtotime($date_rangearray[0]));
				$enddate = date('Y-m-d',strtotime($date_rangearray[1])).' 23:59:59';
				$enddate1 = date('Y-m-d',strtotime($date_rangearray[1]));
			}
		}

		$printedonstr = '';
		if($startdate1 !='' &&  $enddate1 !=''){
			$printedonstr = $startdate1.' - '.$enddate1;
		}
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		$jsonResponse['todayDate'] = date('Y-m-d');
		$jsonResponse['printedonstr'] = $printedonstr;

		$colspan5 = 0;
		$boldclass = $bgashclass = '';
		if(strcmp($showing_type, 'Detailed')==0){
			$colspan5 = 7;
			$boldclass = ' txtbold';
			$bgashclass = ' bgash';
		}
		$jsonResponse['colspan5'] = $colspan5;
		$jsonResponse['boldclass'] = $boldclass;
		$jsonResponse['bgashclass'] = $bgashclass;

		//================For Expense==================//
		if(strcmp($showing_type, 'Detailed')==0){
			$time_clockstr = "SELECT *";
		}
		else{
			$time_clockstr = "SELECT user_id, SUM(CASE WHEN clocked_out NOT IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN (UNIX_TIMESTAMP(clocked_out)-UNIX_TIMESTAMP(clocked_in)) ELSE 0 END) AS totalTime";
		}
		$time_clockstr .= " FROM time_clock WHERE accounts_id = $accounts_id";
		if($startdate1 !='' && $enddate1 !=''){
			$time_clockstr .= " and (clocked_in between '$startdate' and '$enddate')";
		}
		if(strcmp($showing_type, 'Summary')==0){
			$time_clockstr .= " GROUP BY user_id";
		}
		$time_clockstr .= " ORDER BY user_id ASC, clocked_in ASC";
		if(strcmp($showing_type, 'Summary')==0){
			$query = $this->db->query($time_clockstr, array());
		}
		else{
			$query = $this->db->querypagination($time_clockstr, array());
		}

		$tabledata = array();
		if($query){

			$employeeData = array();
			$employeeObj = $this->db->query("SELECT user_id, user_first_name, user_last_name FROM user WHERE accounts_id = $accounts_id", array());
			if($employeeObj){
				while($employeeRow = $employeeObj->fetch(PDO::FETCH_OBJ)){
					$employeeData[$employeeRow->user_id] = trim(stripslashes("$employeeRow->user_first_name $employeeRow->user_last_name"));
				}
			}

			if(strcmp($showing_type, 'Summary')==0){
				while($onegrouprow = $query->fetch(PDO::FETCH_OBJ)){
					$user_id = $onegrouprow->user_id;
					$employeeName = $employeeData[$user_id]??'&nbsp;';
					$totalTime = $onegrouprow->totalTime;
					$hours = 0;
					$minutes = 0;
					if($totalTime>0){
						$totalMinutes = floor($totalTime/60);
						if($totalMinutes>0){
							$hours = floor($totalMinutes/60);
							$minutes = ($totalMinutes%60);
						}
					}

					$tabledata[] = array('employeeName'=>$employeeName, 'hours'=>$hours, 'minutes'=>$minutes, 'details'=>array());
				}
			}
			else{
				$num_rows = count($query);
				if($num_rows>0){

					$prevuser_id = '';

					for($r=0; $r<$num_rows; $r++){

						$oneRowData = $query[$r];
						$user_id = $oneRowData['user_id'];
						$nextuser_id = '';
						if(($r+1)<$num_rows){
							$nextrow = $query[$r+1];
							$nextuser_id = $nextrow['user_id'];
						}

						if($user_id != $prevuser_id){
							$totalTime =0;
							$employeeNumberStr = array();
						}

						$clocked_in = $oneRowData['clocked_in'];
						$clocked_out = $oneRowData['clocked_out'];
						$weekDay = '';
						if(!in_array($clocked_in, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
							$weekDay = date('l', strtotime($clocked_in));
						}
						$times = '';
						if(!in_array($clocked_out, array('0000-00-00 00:00:00', '1000-01-01 00:00:00'))){
							$totalTimePerDay = strtotime($clocked_out)-strtotime($clocked_in);
							$hours = 0;
							$minutes = 0;
							if($totalTimePerDay>0){
								$totalMinutes = floor($totalTimePerDay/60);
								if($totalMinutes>0){
									$hours = floor($totalMinutes/60);
									$minutes = ($totalMinutes%60);
								}
							}
							$times = "$hours hrs $minutes min";
							$totalTime += $totalTimePerDay;
						}

						if($showing_type=='Detailed'){
							$employeeNumberStr[] = array('weekDay'=>$weekDay, 'clocked_in'=>$clocked_in, 'clocked_out'=>$clocked_out, 'times'=>$times);
						}

						if($user_id != $nextuser_id){
							$employeeName = $employeeData[$user_id]??'&nbsp;';							
							$hours = 0;
							$minutes = 0;
							if($totalTime>0){
								$totalMinutes = floor($totalTime/60);
								if($totalMinutes>0){
									$hours = floor($totalMinutes/60);
									$minutes = ($totalMinutes%60);
								}
							}

							$tabledata[] = array('employeeName'=>$employeeName, 'hours'=>$hours, 'minutes'=>$minutes, 'details'=>$employeeNumberStr);
						}
						$prevuser_id = $user_id;

					}
				}
			}
		}
		$jsonResponse['tabledata'] = $tabledata;
		
		return json_encode($jsonResponse);
    }

	//========================ASync========================//		
	public function AJgetPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$sdata_type = $POST['sdata_type']??'All';
		$sorting_type = $POST['sorting_type']??0;
		$keyword_search = $POST['keyword_search']??'';
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->data_type = $sdata_type;
		$this->sorting_type = $sorting_type;
		$this->keyword_search = $keyword_search;
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';
		//===If filter options changes===//	
		if($segment4name=='filter'){
			$this->filterAndOptions();
			$jsonResponse['totalRows'] = $totalRows = $this->totalRows;
		}
		$this->page = $page;
		$this->totalRows = $totalRows;
		
		$jsonResponse['tableRows'] = $this->loadTableRows();
		
		return json_encode($jsonResponse);
	}	
	
	public function AJ_view_MoreInfo(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = intval($POST['user_id']??0);
		$employeeObj = $this->db->query("SELECT * FROM user WHERE user_id = :user_id AND accounts_id = $accounts_id", array('user_id'=>$user_id),1);
		if($employeeObj){
			$listPage = false;
			$employeeOneRow = $employeeObj->fetch(PDO::FETCH_OBJ);	

			$user_id = $employeeOneRow->user_id;
			$name = stripslashes(trim($employeeOneRow->user_first_name.' '.$employeeOneRow->user_last_name));
			$userEmpNo = $employeeOneRow->employee_number;
			$user_email = $employeeOneRow->user_email;
			$pin = $employeeOneRow->pin;
			$user_publish = $employeeOneRow->user_publish;
			
			$jsonResponse['user_id'] = $user_id;
			$jsonResponse['name'] = $name;
			$jsonResponse['userEmpNo'] = $userEmpNo;
			$jsonResponse['user_email'] = $user_email;
			$jsonResponse['pin'] = $pin;
			$jsonResponse['user_publish'] = intval($user_publish);
		}
		else{
			$jsonResponse['login'] = 'Accounts_Receivables/lists/';
		}

		return json_encode($jsonResponse);
	}

	public function AJgetHPage($segment4name){
		$POST = json_decode(file_get_contents('php://input'), true);
		$user_id = intval($POST['user_id']??0);
		$totalRows = intval($POST['totalRows']??0);
		$page = intval($POST['page']??1);
		if($page<=0){$page = 1;}
		$_SESSION["limit"] = intval($POST['limit']??15);
		
		$this->user_id = $user_id;
		
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
	
    public function AJ_employee_archive(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnmsg = '';
		$prod_cat_man = $_SESSION["prod_cat_man"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = intval($POST['employee_id']??0);

		if($user_id>0){
			$employeeObj = $this->db->query("SELECT * FROM user WHERE user_id = :user_id AND accounts_id = $accounts_id", array('user_id'=>$user_id),1);
			if($employeeObj){
				$employeeOneRow = $employeeObj->fetch(PDO::FETCH_OBJ);	

				$name ='';
				$user_id = $employeeOneRow->user_id;
				$name = stripslashes(trim($employeeOneRow->user_first_name.' '.$employeeOneRow->user_last_name));
				$userEmpNo = $employeeOneRow->employee_number;
				if(!empty($userEmpNo)){
					$name .= " ($userEmpNo)";
				}
			
				$updatetable = $this->db->update('user', array('user_publish'=>0), $user_id);
				if($updatetable){
					$note_for = $this->db->checkCharLen('notes.note_for', 'user');
					$noteData=array('table_id'=> $user_id,
									'note_for'=> $note_for,
									'created_on'=> date('Y-m-d H:i:s'),
									'last_updated'=> date('Y-m-d H:i:s'),
									'accounts_id'=> $_SESSION["accounts_id"],
									'user_id'=> $_SESSION["user_id"],
									'note'=> $this->db->translate('Employee archived successfully.')." $name",
									'publics'=>0);
					$notes_id = $this->db->insert('notes', $noteData);
					
					$returnmsg = 'archive-success';
				}
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnmsg));
    }

}
?>