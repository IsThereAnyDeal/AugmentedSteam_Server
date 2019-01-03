<?php
	require_once("../config.php");
	
	$appid = mysql_real_escape_string($_GET['appid']);
	
	if (is_numeric($appid)) {
	
		$url = $wsgf_server.$appid;
		
		$filestring = file_get_contents($url);
		$filestring = str_replace("<4kGrade>", "<FourKGrade>", $filestring);
		$filestring = str_replace("</4kGrade>", "</FourKGrade>", $filestring);
		
		$xml = simplexml_load_string($filestring);
		
		echo json_encode($xml);
		
	}
?>