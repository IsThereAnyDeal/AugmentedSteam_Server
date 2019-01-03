<?php
	require_once("../config.php");
	
	$appid = mysql_real_escape_string($_GET['appid']);
	
	if (is_numeric($appid)) {	
		$url = $pcgw_server.$appid."-5D-5D/format%3Djson";		
		$filestring = file_get_contents($url);		
		echo $filestring;
	}
?>