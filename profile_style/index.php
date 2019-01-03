<?php
	require_once("../config.php");

	// Select record for passed steamID
	$userid = mysql_real_escape_string($_GET['steam64']); 
	
	if (is_numeric($userid)) {
		$sql = mysql_query("SELECT * FROM `profile_style_users` WHERE `steam64`='".mysql_real_escape_string($userid)."' LIMIT 1");	
		while($row = mysql_fetch_array($sql)) {
			if ($row['profile_style']) {
				echo $background_base . $row['profile_style'];
				exit();
			}
		}
	}
?>