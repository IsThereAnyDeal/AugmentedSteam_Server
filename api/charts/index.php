<?php
	require_once("../config.php");
	$appid = mysql_real_escape_string($_GET['appid']);
	
	// Redirect some apps
	switch ($appid) {
		case 201270:
			$appid = 34330;
			break;
		case 262341:
			$appid = 39210;
			break;
		case 262342:
			$appid = 39210;
			break;
	}
	
	function GetNewValue($pass_appid, $connection) {
		$url = "http://steamcharts.com/app/".$pass_appid;
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
			$filestring = file_get_contents($url);		
			$find_start  = '<div id="app-heading" class="content">';
			$find_end = '<div id="app-hours-content" class="content">';
			
			$pos = strpos($filestring, $find_start);
			$pos_end = strpos($filestring, $find_end);
			
			$substring = substr($filestring, $pos, ($pos_end - $pos));
			
			preg_match_all("/<span class=\"num\">(.+)<\/span>/", $substring, $matches);
			$now = number_format($matches[1][0]);
			$peak = number_format($matches[1][1]);
			$allpeak = number_format($matches[1][2]);
			
			$sql = "INSERT INTO `steamcharts` (`appid`, `one_hour`, `one_day`, `all_time`) VALUES (".mysql_real_escape_string($pass_appid).", '".mysql_real_escape_string($now)."', '".mysql_real_escape_string($peak)."', '".mysql_real_escape_string($allpeak)."')";
			$result = mysql_query($sql, $connection);
			return "\"current\": \"".trim($now)."\", \"peaktoday\": \"".trim($peak)."\", \"peakall\": \"".trim($allpeak)."\"}}";
		}
	}
	
	if (is_numeric($appid)) {
		// checks to see if the value is cached
		$result = mysql_query("SELECT * FROM steamcharts WHERE appid='".$appid."' LIMIT 1", $con);
		$num_rows = mysql_num_rows($result);
		
		// if cached, return the database value
		if ($num_rows > 0) {
			while ($row = mysql_fetch_array($result)) {
				$access_time = strtotime($row['access_time']);
				$current_time = time();
								
				if ($current_time - $access_time >= 3600) {
					$sql = "DELETE FROM `steamcharts` WHERE `appid` = ".mysql_real_escape_string($appid);
					$result = mysql_query($sql, $con);
					$text = GetNewValue($appid, $con);
					$return = "{\"chart\":{" . $text;
				} else {
					$return = "{\"chart\":{";
					$return = "{\"chart\":{\"current\": \"".trim($row['one_hour'])."\", \"peaktoday\": \"".trim($row['one_day'])."\", \"peakall\": \"".trim($row['all_time'])."\"}}";;
				}	
			}
			
		// if not cached or expired, get new value
		} else {
			$text = GetNewValue($appid, $con);
			$return = "{\"chart\":{" . $text;
		}
		
		echo $return;
	}
	
	exit; 
?>