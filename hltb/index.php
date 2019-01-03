<?php
	require_once("../config.php");

	// sanitation function
	function strip($string) {
		$string = str_replace("&#189;", "", $string);
		$string = str_replace("<div class='time_100' >", "", $string);
		$string = str_replace("<div class='time_90' >", "", $string);
		$string = str_replace("<div class='time_80' >", "", $string);
		$string = str_replace("<div class='time_70' >", "", $string);
		$string = str_replace("<div class='time_60' >", "", $string);
		$string = str_replace("<div class='time_50' >", "", $string);
		$string = str_replace("<div class='time_40' >", "", $string);
		$string = str_replace("<div class='time_30' >", "", $string);
		$string = str_replace("<div class='time_20' >", "", $string);
		$string = str_replace("<div class='time_10' >", "", $string);
		$string = str_replace("<div class='time_00' >", "", $string);
		
		$string = str_replace("<div class='time_100' >", "", $string);
		$string = str_replace("<div class='time_90' >", "", $string);
		$string = str_replace("<div class='time_80' >", "", $string);
		$string = str_replace("<div class='time_70' >", "", $string);
		$string = str_replace("<div class='time_60' >", "", $string);
		$string = str_replace("<div class='time_50' >", "", $string);
		$string = str_replace("<div class='time_40' >", "", $string);
		$string = str_replace("<div class='time_30' >", "", $string);
		$string = str_replace("<div class='time_20' >", "", $string);
		$string = str_replace("<div class='time_10' >", "", $string);
		$string = str_replace("<div class='time_00' >", "", $string);
		
		$string = str_replace("</div>", "", $string);
		$string = str_replace("<div>", "", $string);
		
		return $string;
	}
	
	// Select record for passed APPID
	$appid = mysql_real_escape_string($_GET['appid']);
	
	if (is_numeric ($appid)) {
		$result = mysql_query("SELECT appid, hltb_id FROM game_links WHERE appid=".$appid." LIMIT 1");
		$rowcount = mysql_num_rows($result);
		while($row = mysql_fetch_array($result)) {
			$hltb_url = "http://www.howlongtobeat.com/game.php?id=".$row['hltb_id'];
			$hltb_submit = "http://www.howlongtobeat.com/submit_add.php?gid=".$row['hltb_id'];
		}	
	}
	
	if ($rowcount == 1) {
		$return = "{\"hltb\":{";
		
		$main_story = ""; $main_extras = ""; $comp = "";
		
		$file_headers = @get_headers($hltb_url);
		if($file_headers[0] == 'HTTP/1.0 500 Internal Server Error') {
			$up = false;
		} else {
			$up = true;
		}	
		
		if ($up) {
			$filestring = file_get_contents($hltb_url);
			if (preg_match("/<h5>Main Story<\/h5>\n(.+)\n/", $filestring, $matches)) {			  
			  $main_story = $matches[1];
			}
			
			if (preg_match("/<h5>Main \+ Extras<\/h5>\n(.+)\n/", $filestring, $matches)) {			  
			  $main_extras = $matches[1];
			}
			
			if (preg_match("/<h5>Completionist<\/h5>\n(.+)\n/", $filestring, $matches)) {			  
			  $comp = $matches[1];
			}
			
			$main_story = strip($main_story);
			$main_extras = strip($main_extras);
			$comp = strip($comp);
			
			$return = $return."\"main_story\": \"".trim($main_story)."\", \"main_extras\": \"".trim($main_extras)."\", \"comp\": \"".trim($comp)."\", \"url\": \"".$hltb_url."\", \"submit_url\": \"".$hltb_submit."\"}}";
			
			echo $return;
		}
	} 
?>