<?php
	require_once("../config.php");
	ini_set('max_execution_time', 300);
	
	$background_base = "http://cdn.steamcommunity.com/economy/image/";
	
	$return = "{\"backgrounds\":{";
	
	$result = mysql_query("SELECT * FROM `profile_users` WHERE `steam64`='".mysql_real_escape_string($_GET['steam64'], $con)."' LIMIT 1");
	
	while($user = mysql_fetch_array($result)) {
		$current_selected = $user['profile_background_img'];
	}
	
	$result = mysql_query("SELECT * FROM `market_data` WHERE `type`='background' ORDER BY `game`, `name` ASC");		
	while($row = mysql_fetch_array($result)) {
		$img = $background_base . $row['img'];
		$img_small = $img . "/252fx160f";
		$game = $row['game'];
		$game = str_replace(" Rare", "", $game);
		$game = str_replace(" Uncommon", "", $game);			
		
		$name = $row['name'];
		$name = str_replace("(Profile Background)", "", $name);
		$name = str_replace($game . " - ", "", $name);
		
		if ($row['img'] == $current_selected) {
			$return = $return . '"'.$row[img].'":{"id":"'.$img_small.'", "index":"'.$row[img].'", "selected":true, "text":"'.$game.' - '.$name.'"},';	
		} else {
			$return = $return . '"'.$row[img].'":{"id":"'.$img_small.'", "index":"'.$row[img].'", "selected":false, "text":"'.$game.' - '.$name.'"},';
		}
	}
	
	$return = substr($return, 0, -1);
	$return = $return . "}}";
	
	echo $return;

	// Close database connection
	mysql_close($con);
?>