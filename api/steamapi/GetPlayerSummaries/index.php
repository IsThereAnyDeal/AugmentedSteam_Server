<?php
	require_once("../config.php");
	
	$steamids = mysql_real_escape_string($_GET['steamids']);
	
	if ($steamids) {
		$url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".Config::SteamApiKey."&steamids=".$steamids."&format=json";
		$file_headers = @get_headers($url);
		if(strpos($file_headers[0], "Internal Server Error") != 0) { $up = false; }
		else { $up = true; }
		
		if ($up) {
			$filestring = file_get_contents($url);
			echo $filestring;
		}
	}
?>
