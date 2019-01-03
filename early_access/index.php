<?php
	require_once("../config.php");
	
	$sql = "SELECT * FROM `early_access`";	
	$result = mysql_query($sql, $con);
	
	$text = '{"ea": [';
	
	while($ea = mysql_fetch_array($result)) {
		$text = $text . "\"" . $ea['appid'] . "\",";
	}
	if ($text != '{"ea": [') {
		$text = substr($text, 0, -1);
	}
	$text = $text . "]}";
	
	echo $text;
?>