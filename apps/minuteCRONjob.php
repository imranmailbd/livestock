<?php
$serverPath = getcwd();
if($serverPath=='/home/machouse'){
	define('OUR_DOMAINNAME', 'machouse.com.bd');
	define('COMPANYNAME', 'Dazzle PVT. Ltd.');
}
else{
	define('OUR_DOMAINNAME', 'machousel.com.bd');
	define('COMPANYNAME', 'SK POS ERP');
}
require_once ("$serverPath/apps/Db.php");
$timezone = 'America/New_York';
date_default_timezone_set($timezone);

$db = new Db();
$before15minutes = date('Y-m-d H:i:s', time()-15*60);
$sql = "SELECT user.user_id, user.accounts_id, ulh.user_login_history_id FROM user_login_history ulh, user WHERE user.last_request<'$before15minutes' AND user.login_ck_id !='' AND ulh.logout_datetime IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') AND user.user_id = ulh.user_id ORDER BY user.accounts_id ASC, user.user_id ASC, ulh.user_login_history_id ASC";
$query = $db->query($sql, array());
if($query){
	$prevAccountsId = 0;
	while($oneRow = $query->fetch(PDO::FETCH_OBJ)){
		$user_id = $oneRow->user_id;
		$accounts_id = $oneRow->accounts_id;
		if($accounts_id != $prevAccountsId){
			$timezone = 'America/New_York';
			$variablesObj = $db->query("SELECT value FROM variables WHERE  accounts_id = $accounts_id AND name = 'account_setup' AND value !=''", array());
			if($variablesObj){
				$valueArray = unserialize($variablesObj->fetch(PDO::FETCH_OBJ)->value);
				if(array_key_exists('timezone', $valueArray)){
					$timezone = $valueArray['timezone'];
				}
			}
			date_default_timezone_set($timezone);
		}

		$db->update('user_login_history', array('logout_datetime'=>date('Y-m-d H:i:s'), 'logout_by'=>'No heartbeat'), $oneRow->user_login_history_id);

		$updated_array = array('last_updated'=> date('Y-m-d H:i:s'), 'login_ck_id'=>'');
		$db->update('user', $updated_array, $user_id);

		$prevAccountsId = $accounts_id;
	}
}
?>
+
