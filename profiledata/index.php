<?php
	require_once("../config.php");
	
	$steam64 = mysql_real_escape_string($_GET['steam64'], $con);	
	$current_time = time();
	
	function GetNewRepValue($the_steam64, $connection) {
		$sr_url = $steamrep_server.$the_steam64;
		$file_headers = @get_headers($url);
		switch ($file_headers[0]) {
			case 'HTTP/1.0 500 Internal Server Error':
				$up = false;
				break;
			case 'HTTP/1.0 429 Unknown';
				$up = false;
				break;
			default: 
				$up = true;
		}
		
		if ($up) {
			$filestring = file_get_contents($sr_url);		
			$array=json_decode(json_encode(simplexml_load_string($filestring)),true);
			
			$full = "";
			if (isset($array["reputation"]["full"])) { $full = $array["reputation"]["full"]; }
			
			$sql = "INSERT INTO `steamrep` (`steam64`, `rep`) VALUES (".mysql_real_escape_string($the_steam64).", '".mysql_real_escape_string($full)."')";
			$result = mysql_query($sql, $connection);
			return mysql_real_escape_string($full);
		}
	}
	
	if (is_numeric($steam64)) {
		// Setup global variables
		$return = "{";
		
		$background_base = "//steamcommunity.com/economy/image/";
	
		// Get Supporter data
		$result = mysql_query("SELECT * FROM  `supporter_users` LEFT JOIN `supporter_badges` ON `supporter_users`.`badge_id` = `supporter_badges`.`id` WHERE steam_id='".$steam64."' ORDER BY `badge_id`");
		$num_rows = mysql_num_rows($result);		
		$supporter_json = '"supporter":{ "steam_id": "' . $steam64 . '", "num_badges": "' . $num_rows . '", "badges": [';		
		while($row = mysql_fetch_array($result)) {
			$supporter_json = $supporter_json . '{ "link": "' . $row['link'] . '", "title": "' . $row['title'] . '", "img": "' . $row['img'] . '" },';
		}
		if (substr($supporter_json, -1) == ",") { $supporter_json = substr($supporter_json, 0, -1); }
		$supporter_json = $supporter_json . "]}";
		
		$return = $return . $supporter_json;
		
		// Get SteamRep Data
		$result = mysql_query("SELECT * FROM steamrep WHERE steam64='".$steam64."' LIMIT 1", $con);
		$num_rows = mysql_num_rows($result);
		
		if ($num_rows > 0) {
			while ($row = mysql_fetch_array($result)) {
				$access_time = strtotime($row['access_time']);
				if ($current_time - $access_time >= 86400) {
					$sql = "DELETE FROM `steamrep` WHERE `steam64` = '".mysql_real_escape_string($steam64)."'";
					$result = mysql_query($sql, $con);
					$full = GetNewRepValue($steam64, $con);
				} else {
					$full = $row["rep"];
				}	
			}
		} else {
			$full = GetNewRepValue($steam64, $con);
		}
		
		$full_array = explode(",", $full);
		$full_html = "<div style='font-size: 11px; font-family: Verdana; margin-bottom: 10px;'>";
		foreach ($full_array as $text) {
			if (strpos(strtolower($text),"scammer") !== FALSE) { $full_html = $full_html . "<img src='http://api.enhancedsteam.com/steamrep/banned.png'><a href='http://steamrep.com/profiles/" . $steam64 ."' style='color: #FF0000;'>" . $text . "</a><br> "; }
			elseif (strpos(strtolower($text),"valve admin") !== FALSE) { $full_html = $full_html . "<img src='http://api.enhancedsteam.com/steamrep/valve.png'><a href='http://steamrep.com/profiles/" . $steam64 ."' style='color: #A50F79;'>VALVE EMPLOYEE</a><br> "; }
			elseif (strpos(strtolower($text),"caution") !== FALSE) { $full_html = $full_html . "<img src='http://api.enhancedsteam.com/steamrep/caution.png'><a href='http://steamrep.com/profiles/" . $steam64 ."' style='color: #FC970A;'>" . $text . "</a><br> "; }
			elseif (strpos(strtolower($text),"banned") !== FALSE) { $full_html = $full_html . "<img src='http://api.enhancedsteam.com/steamrep/banned.png'><a href='http://steamrep.com/profiles/" . $steam64 ."' style='color: #FF0000;'>" . $text . "</a><br> "; }
			elseif (strpos(strtolower($text),"admin") !== FALSE) { $full_html = $full_html . "<img src='http://api.enhancedsteam.com/steamrep/okay.png'><a href='http://steamrep.com/profiles/" . $steam64 ."' style='color: #008000;'>" . $text . "</a><br> "; }
			elseif (strpos(strtolower($text),"middleman") !== FALSE) { $full_html = $full_html . "<img src='http://api.enhancedsteam.com/steamrep/okay.png'><a href='http://steamrep.com/profiles/" . $steam64 ."' style='color: #008000;'>" . $text . "</a><br> "; }
			elseif (strpos(strtolower($text),"donator") !== FALSE) { $full_html = $full_html . "<img src='http://api.enhancedsteam.com/steamrep/donate.png'><a href='http://steamrep.com/profiles/" . $steam64 ."' style='color: #0F67A1;'>" . $text . "</a><br> "; }			
			else {$full_html = $full_html . $text . ", "; }
		}
		$full_html = $full_html . "</div>";
		
		if ($full_html == "<div style='font-size: 11px; font-family: Verdana; margin-bottom: 10px;'></div>") { $full_html = ""; }
		if ($full_html == "<div style='font-size: 11px; font-family: Verdana; margin-bottom: 10px;'>, </div>") { $full_html = ""; }
		
		$return = $return . ",\"steamrep\": \"" . $full_html . "\"";
		$return = $return . ",\"steamrepv2\": \"" . $full . "\"";
		
		// Get profile style data
		$sql = mysql_query("SELECT * FROM `profile_style_users` WHERE `steam64`='".mysql_real_escape_string($steam64)."' LIMIT 1");	
		$style_json = "";
		while($row = mysql_fetch_array($sql)) {
			if ($row['profile_style']) {
				$style_json = ",\"profile_style\": {\"style\": \"" . $row['profile_style'] . "\"}";
			}
		}
		if ($style_json == "") {
			$style_json = ",\"profile_style\": {\"style\": \"\"}";
		}
		
		$return = $return . $style_json;
		
		// Get background data
		$sql = mysql_query("SELECT * FROM `profile_users` WHERE `steam64`='".mysql_real_escape_string($steam64)."'");
		while($row = mysql_fetch_array($sql)) {
			if ($row['profile_background_img']) {
				$return = $return . ",\"profile\": {\"background\": \"" . $background_base . $row['profile_background_img'] . "\", \"background-small\": \"" . $background_base . $row['profile_background_img'] . "/252fx160f\", \"appid\": " . $row['appid'] . "}";
			}
		}
		
		// Output data
		echo $return . "}";
	}
	
	exit;
	
?>