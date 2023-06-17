<?php
class Appointment_Calendar{
	protected $db;
	
	public function __construct($db){$this->db = $db;}
	
	public function lists(){}
	
	public function AJ_lists_MoreInfo(){
		
		$jsonResponse = array();
		$jsonResponse['login'] = '';

		$POST = json_decode(file_get_contents('php://input'), true);
		$appointment_date = $POST['appointment_date']??0;
		
		$accounts_id = $_SESSION["accounts_id"]??0;
		$dateformat = $_SESSION["dateformat"]??'m/d/Y';
		$timeformat = $_SESSION["timeformat"]??'12 hour';
		$variables_id = 0;
		$days_in_view = 1;
		$starttime = '9';
		$endtime = '17';
		$varObj = $this->db->query("SELECT * FROM variables WHERE accounts_id = $accounts_id AND name = 'appointments'", array());
		if($varObj){
			$variablesData = $varObj->fetch(PDO::FETCH_OBJ);
			$variables_id =  $variablesData->variables_id;
			if($variablesData->value !=''){
				$value = $variablesData->value;
				if(!empty($value)){
					$value = unserialize($value);
					extract($value);
				}
			}
		}
		if(!isset($days_in_view) || $days_in_view == 0 || $days_in_view == ''){$days_in_view = 1;}
		$oneViewWidth = round(100/$days_in_view,4);
		
		$appDate = date(str_replace('y', 'Y', $dateformat), strtotime($appointment_date));
		$endDate = date(str_replace('y', 'Y', $dateformat), strtotime($appDate." +".($days_in_view-1)." day"));

		$jsonResponse['variables_id'] = $variables_id;
		$jsonResponse['days_in_view'] = $days_in_view;
		$jsonResponse['starttime'] = $starttime;
		$jsonResponse['endtime'] = $endtime;
		$jsonResponse['oneViewWidth'] = $oneViewWidth;
		$jsonResponse['appDate'] = $appDate;
		$jsonResponse['endDate'] = $endDate;
		$curDateInfo = array();
		$timewidth = 10+($days_in_view*2);
		for($l=0; $l<$days_in_view; $l++){

			$sappdate = date('Y-m-d', strtotime($appointment_date." +$l day"));
			$curDateStr = date("l $dateformat", strtotime($sappdate));
			
			$timeRows = array();
			for($i = $starttime; $i <= $endtime; $i++){
				if($timeformat=='24 hour'){$hour =  $i;}
				else{$hour = date("ga", strtotime("$i:00"));}	
				$timeRows[] = array($hour, '');
				$sappdatetime = date('Y-m-d H', strtotime($sappdate.' '.$i.':00:00'));
				$sql = "SELECT * FROM appointments WHERE accounts_id = $accounts_id AND appdatetime LIKE CONCAT(:sappdatetime, '%') ORDER BY appdatetime ASC";
				$appObj = $this->db->query($sql, array('sappdatetime'=>$sappdatetime));
				if($appObj){
					while($oneRow = $appObj->fetch(PDO::FETCH_OBJ)){
						$appointments_id = $oneRow->appointments_id;
						if($timeformat=='24 hour'){$dhours =  date('H:i', strtotime($oneRow->appdatetime));}
						else{$dhours = date('g:ia', strtotime($oneRow->appdatetime));}
						$timeRows[] = array($dhours, nl2br(stripslashes($oneRow->description)), $appointments_id, date('i', strtotime($oneRow->appdatetime)));
					}
				}
			}
			$curDateInfo[$curDateStr] = $timeRows;
		}
		$jsonResponse['curDateInfo'] = $curDateInfo;
		return json_encode($jsonResponse);
	}

	public function saveVariablesAppointments(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg ='error';
		
		$appointments_idarray = array();
		$accounts_id = $_SESSION["accounts_id"]??0;
		$days_in_view = $POST['days_in_view']??0;
		$starttime = $POST['starttime']??'';
		$endtime = $POST['endtime']??'';
		
		$variables_id = $POST['variables_id']??0;

		if($variables_id==0){
			$varObj = $this->db->query("SELECT variables_id FROM variables WHERE accounts_id = $accounts_id AND name = 'appointments'", array());
			if($varObj){
				$variables_id = $varObj->fetch(PDO::FETCH_OBJ)->variables_id;
			}
		}
		
		$value = serialize(array('days_in_view'=>$days_in_view, 'starttime'=>$starttime, 'endtime'=>$endtime));
		$data=array('accounts_id'=>$accounts_id,
					'name'=>$this->db->checkCharLen('variables.name', 'appointments'),
					'value'=>$value,
					'last_updated'=> date('Y-m-d H:i:s'));
		if($variables_id==0){
			$variables_id = $this->db->insert('variables', $data);
			if($variables_id){
				$savemsg = 'insert-success';
			}
		}
		else{
			$update = $this->db->update('variables', $data, $variables_id);
			if($update){
				$savemsg = 'update-success';
			}
		}
		
		$array = array( 'login'=>'',
						'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	public function AJgetPopup(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		
		$appointments_id = $POST['appointments_id']??0;
		if($appointments_id>0){
			$appointmentsObj = $this->db->query("SELECT description FROM appointments WHERE appointments_id = :appointments_id AND accounts_id = $accounts_id", array('appointments_id'=>$appointments_id),1);
			if($appointmentsObj){
				$returnStr = trim((string) stripslashes($appointmentsObj->fetch(PDO::FETCH_OBJ)->description));
			}
		}
		return json_encode(array('login'=>'', 'returnStr'=>$returnStr));
	}
	
	public function AJsaveAppointment_Calendar(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$savemsg = '';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$user_id = $_SESSION["user_id"]??0;
		$appointments_id = $POST['appointments_id']??0;
		$appdate = date('Y-m-d', strtotime(trim((string) $POST['appdate'])));
		$apphour = trim((string) $POST['apphour']);
		$appminutes = trim((string) $POST['appminutes']);
		
		$acData = array();
		$acData['accounts_id'] = $accounts_id;
		$acData['appdatetime'] = date('Y-m-d H:i:s', strtotime($appdate.' '.$apphour.':'.$appminutes.':00'));
		$acData['description'] = addslashes(trim((string) $POST['appdescription']));			
		$acData['user_id'] = $user_id;
		
		if($appointments_id==0){
			
			$acData['created_on'] = date('Y-m-d H:i:s');
			
			$appointments_id = $this->db->insert('appointments', $acData);
			if($appointments_id){}
			else{
				$savemsg = 'errorAdding';
			}
		}
		else{
			
			$update = $this->db->update('appointments', $acData, $appointments_id);
			if($update){}
			else{
				$savemsg = 'errorNoChange';
			}
		}
		
		$array = array( 'login'=>'',
						'appointments_id'=>$appointments_id,
						'savemsg'=>$savemsg);
		return json_encode($array);
	}
	
	public function AJremoveAppointment_Calendar(){
		$POST = json_decode(file_get_contents('php://input'), true);
		$returnStr = 'error';
		$accounts_id = $_SESSION["accounts_id"]??0;
		$removeCount = 0;
		$appointments_id = $POST['appointments_id'];
		$removeAction = $this->db->delete('appointments', 'appointments_id', $appointments_id);
		if($removeAction){$removeCount++;}
		
		return json_encode(array('login'=>'', 'removeCount'=>$removeCount));
	}
}
?>