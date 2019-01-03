<?php
	require_once("../config.php");

	// Select record for passed steam ID
	$steam_id = mysql_real_escape_string($_GET['steam_id'], $con);
	$result = mysql_query("SELECT * FROM  `supporter_users` LEFT JOIN `supporter_badges` ON `supporter_users`.`badge_id` = `supporter_badges`.`id` WHERE steam_id='".$steam_id."' ORDER BY `badge_id` LIMIT 4");
	$num_rows = mysql_num_rows($result);
	
	$html = '{ "steam_id": "' . $steam_id . '", "num_badges": "' . $num_rows . '", "badges": [';
	
	while($row = mysql_fetch_array($result)) {
		$html = $html . '{ "link": "' . $row[link] . '", "title": "' . $row[title] . '", "img": "' . $row[img] . '" },';
	}
	
	if (substr($html, -1) == ",") { $html = substr($html, 0, -1); }
	
	$html = $html . "]}";
	
	echo $html;
?>