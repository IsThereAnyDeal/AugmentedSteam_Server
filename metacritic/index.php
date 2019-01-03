<?php
	require_once("../config.php");
	
	$mcurl = mysql_real_escape_string($_GET['mcurl']);
	
	// gets and returns a new value, storing it in the database
	function GetNewValue($url, $connection) {
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
			preg_match("/metascore_w user(.+)\">(.+)<\/div>/", $filestring, $matches);
			$the_value = $matches[2];
			$sql = "INSERT INTO `metacritic` (`mcurl`, `score`) VALUES ('".mysql_real_escape_string($url)."', ".mysql_real_escape_string($the_value).")";
			$result = mysql_query($sql, $connection);
			return $the_value;
		}
	}
	
	if (substr($mcurl, 0, 26) == "http://www.metacritic.com/") {	
		// checks to see if the value is cached
		$result = mysql_query("SELECT * FROM metacritic WHERE mcurl='".mysql_real_escape_string($mcurl)."' LIMIT 1", $con);
		$num_rows = mysql_num_rows($result);
		
		// if cached, return the database value
		if ($num_rows > 0) {
			while ($row = mysql_fetch_array($result)) {
				$access_time = strtotime($row['access_time']);
				$current_time = time();
								
				if ($current_time - $access_time >= 28800) {
					$sql = "DELETE FROM `metacritic` WHERE `mcurl` = '".mysql_real_escape_string($mcurl)."'";
					$result = mysql_query($sql, $con);
					$text = GetNewValue($mcurl, $con);
				} else {
					$text = $row['score'];
				}	
			}
			
		// if not cached or expired, get new value
		} else {
			$text = GetNewValue($mcurl, $con);
		}
		
		echo $text;
	}
	exit;
?>