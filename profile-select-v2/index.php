<?php
	require_once("../config.php");
	$current_selected = "";	
	$background_base = "//steamcommunity.com/economy/image/";
	
	$return = "{\"games\":{";
	
	$result = mysql_query("SELECT * FROM `profile_users` WHERE `steam64`='".mysql_real_escape_string($_GET['steam64'], $con)."' LIMIT 1");
	
	while($user = mysql_fetch_array($result)) {
		$current_selected = $user['appid'];
	}
	
	$result = mysql_query('SELECT DISTINCT Replace(Replace(`game`, "Rare", ""), "Uncommon", "") As Game, appid FROM `market_data` WHERE `type`="background" ORDER BY `game` ASC');
	while($row = mysql_fetch_array($result)) {
		$game = $row['Game'];
		if ($row['appid'] == $current_selected) {
			$return = $return . '"'.$game.'": {"appid":"'.$row['appid'].'","selected":true},';
		} else {
			$return = $return . '"'.$game.'": {"appid":"'.$row['appid'].'","selected":false},';
		}	
	}
	
	$return = substr($return, 0, -1);
	$return = $return . "}}";
	
	echo $return;

	// Close database connection
	mysql_close($con);
?>