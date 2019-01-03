<?php
	require_once("../config.php");
	
	$background_base = "//cdn.steamcommunity.com/economy/image/";

	// Select record for passed steamID
	$userid = mysql_real_escape_string($_GET['steam64']); 
	
	if (is_numeric($userid)) {
		$sql = mysql_query("SELECT * FROM `profile_users` WHERE `steam64`='".mysql_real_escape_string($userid)."'");	
		while($row = mysql_fetch_array($sql)) {
			if ($row['profile_background_img']) {
				echo $background_base . $row['profile_background_img'];
				exit();
			}
		}
		
		$result = mysql_query("SELECT * FROM  `profile_users` LEFT JOIN `profile_backgrounds` ON `profile_users`.`profile_background_id` =  `profile_backgrounds`.`id` WHERE steam64='".mysql_real_escape_string($userid)."'");
		
		while($row = mysql_fetch_array($result)) {
			echo $row['url'];
		}
	}
?>