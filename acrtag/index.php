<?php
	require_once("../config.php");
	
	$sql = "SELECT * FROM `acrtag`";	
	$result = mysql_query($sql, $con);
	
	$text = '{"acrtag": [';
	
	while($acrtag = mysql_fetch_array($result)) {
		$text = $text . "\"" . $acrtag['subid'] . "\",";
	}
	$text = substr($text, 0, -1);
	$text = $text . "]}";
	
	echo $text;
?>