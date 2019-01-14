<?php
	require_once("../config.php");
	
	$background_base = "//steamcommunity.com/economy/image/";
	$current_selected = "";
	
	$return = "{\"backgrounds\":{";
	
	$result = mysql_query("SELECT * FROM `profile_users` WHERE `steam64`='".mysql_real_escape_string($_GET['steam64'], $con)."' LIMIT 1");
	
	while($user = mysql_fetch_array($result)) {
		$current_selected = $user['profile_background_img'];
	}
	
	$result = mysql_query('SELECT `appid`,`name`,`img` FROM `market_data` WHERE `appid` = "'.mysql_real_escape_string($_GET['appid'], $con).'" AND `type`="background" ORDER BY `name` ASC');
	while($row = mysql_fetch_array($result)) {
		$img = $background_base . $row['img'];
		$img_small = $img . "/252fx160f";
		
		$name = $row['name'];
		$name = str_replace("(Profile Background)", "", $name);
		
		if ($row['img'] == $current_selected) {
			$return = $return . '"'.$name.'":{"id":"'.$img_small.'", "index":"'.$row['img'].'", "selected":true, "text":"'.$name.'"},';
		} else {
			$return = $return . '"'.$name.'":{"id":"'.$img_small.'", "index":"'.$row['img'].'", "selected":false, "text":"'.$name.'"},';
		}	
	}
	
	$return = substr($return, 0, -1);
	$return = $return . "}}";
	
	echo $return;
	
	// Close database connection
	mysql_close($con);
?>