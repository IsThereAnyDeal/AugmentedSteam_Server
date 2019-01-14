<?php
	require_once("../config.php");
	
	$background_base = "http://cdn.steamcommunity.com/economy/image/";

	// Select record for passed APPID
	$steam64 = mysql_real_escape_string($_GET['steam64'], $con);
	
	$sql = mysql_query("SELECT * FROM `profile_users` WHERE `steam64`='".$steam64."'");	
	while($row = mysql_fetch_array($sql)) {
		if ($row['profile_background_img']) {
			echo $background_base . $row['profile_background_img'] . "/252fx160f";
			exit();
		}
	}
	
	$result = mysql_query("SELECT * FROM  `profile_users` LEFT JOIN `profile_backgrounds` ON `profile_users`.`profile_background_id` =  `profile_backgrounds`.`id` WHERE steam64='".$steam64."'");
	
	while($row = mysql_fetch_array($result)) {
		echo $row['smallurl'];
	}
?>