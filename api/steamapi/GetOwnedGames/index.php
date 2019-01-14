<?php
	require_once("../config.php");
	
	$steamid = mysql_real_escape_string($_GET['steamid']);	
	if(isset($_GET['include_played_free_games'])) { 
		$include_played_free_games = mysql_real_escape_string($_GET['include_played_free_games']); 
	} else {
		$include_played_free_games = false;
	}
	
	if(isset($_GET['include_appinfo'])) {
		$include_appinfo = mysql_real_escape_string($_GET['include_appinfo']);
	} else {
		$include_appinfo = 1;
	}
		
	if (is_numeric($steamid)) {
		$url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=".$steamAPIkey."&steamid=".$steamid."&include_appinfo=".$include_appinfo."&include_played_free_games=".$include_played_free_games."&format=json";
		$file_headers = @get_headers($url);
		if(strpos($file_headers[0], "Internal Server Error") != 0) { $up = false; }
		else { $up = true; }
		
		if ($up) {		
			$filestring = file_get_contents($url);
			echo $filestring;
		}
	}
?>