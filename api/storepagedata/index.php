<?php
	require_once("../config.php");
	
	$appid = mysql_real_escape_string($_GET['appid']);
	$r_all = 0; $r_pos = 0; $r_stm = 0; $mcurl = ""; $oc = "";
	if (isset($_GET['r_all']) && !empty($_GET['r_all'])) { $r_all = mysql_real_escape_string($_GET['r_all']); }
	if (isset($_GET['r_pos']) && !empty($_GET['r_pos'])) { $r_pos = mysql_real_escape_string($_GET['r_pos']); }
	if (isset($_GET['r_stm']) && !empty($_GET['r_stm'])) { $r_stm = mysql_real_escape_string($_GET['r_stm']); }
	if (isset($_GET['mcurl']) && !empty($_GET['mcurl'])) { $mcurl = mysql_real_escape_string($_GET['mcurl']); }
	if (isset($_GET['oc']) && !empty($_GET['oc'])) { $oc = mysql_real_escape_string($_GET['oc']); }
	$current_time = time();
		
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
	
	function GetNewChartValue($the_appid, $connection) {		
		// Redirect some apps
		switch ($the_appid) {
			case 201270:
				$the_appid = 34330;
				break;
			case 262341:
				$the_appid = 39210;
				break;
			case 262342:
				$the_appid = 39210;
				break;
		}
		$url = "http://steamcharts.com/app/".$the_appid;
		$file_headers = @get_headers($url);
		switch ($file_headers[0]) {
			case 'HTTP/1.0 500 Internal Server Error':
				$up = false;
				break;
			case 'HTTP/1.0 429 Unknown';
				$up = false;
				break;
			case 'HTTP/1.0 404 Not Found';
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
			
			$sql = "INSERT INTO `steamcharts` (`appid`, `one_hour`, `one_day`, `all_time`) VALUES (".mysql_real_escape_string($the_appid).", '".mysql_real_escape_string($now)."', '".mysql_real_escape_string($peak)."', '".mysql_real_escape_string($allpeak)."')";
			$result = mysql_query($sql, $connection);
			return "\"current\": \"".trim($now)."\", \"peaktoday\": \"".trim($peak)."\", \"peakall\": \"".trim($allpeak)."\"}";
		}
	}
	
	function GetNewSpyValue($the_appid, $connection) {
		$url = Config::SteamSpyEndpoint."&appid=".$the_appid;
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
			
			// Convert JSON results into variables
			$array = json_decode($filestring, true);
			
			// Then, insert these values into the database
			$sql = "INSERT INTO `steamspy` (`appid`, `owners`, `owners_variance`, `players_forever`, `players_forever_variance`, `players_2weeks`, `players_2weeks_variance`, `average_forever`, `average_2weeks`) VALUES (".mysql_real_escape_string($the_appid).", ".mysql_real_escape_string($array["owners"]).", ".mysql_real_escape_string($array["owners_variance"]).", ".mysql_real_escape_string($array["players_forever"]).", ".mysql_real_escape_string($array["players_forever_variance"]).", ".mysql_real_escape_string($array["players_2weeks"]).", ".mysql_real_escape_string($array["players_2weeks_variance"]).", ".mysql_real_escape_string($array["average_forever"]).", ".mysql_real_escape_string($array["average_2weeks"]).")";
			$result = mysql_query($sql, $connection);
			
			// Finally, format the output
			return "\"steamspy\":{\"owners\": \"".$array["owners"]."\", \"owners_variance\": \"".$array["owners_variance"]."\", \"players_forever\": \"".$array["players_forever"]."\", \"players_forever_variance\": \"".$array["players_forever_variance"]."\", \"players_2weeks\": \"".$array["players_2weeks"]."\", \"players_2weeks_variance\": \"".$array["players_2weeks_variance"]."\", \"average_forever\": \"".$array["average_forever"]."\", \"average_2weeks\": \"".$array["average_2weeks"]."\"}";
		}
	}
	
	function GetNewMCValue($url, $connection) {
		$file_headers = @get_headers($url);
		switch ($file_headers[0]) {
			case 'HTTP/1.0 500 Internal Server Error':
				$up = false;
				break;
			case 'HTTP/1.0 429 Unknown';
				$up = false;
				break;
			case 'HTTP/1.1 429 Too Many Requests';
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
	
	function GetNewOCValue($the_appid, $connection) {
		$url = Config::OpenCriticEndpoint.$the_appid."&key=".Config::OpenCriticKey;
		$file_headers = @get_headers($url);
		switch ($file_headers[0]) {
			case 'HTTP/1.0 500 Internal Server Error':
				$up = false;
				break;
			case 'HTTP/1.0 429 Unknown';
				$up = false;
				break;
			case 'HTTP/1.1 404 Not Found';
				$up = false;
				break;	
			default: 
				$up = true;
		}
		
		if ($up) {
			$filestring = file_get_contents($url);
			if ($filestring) {
				$array = json_decode($filestring, true);
				
				// Strip stuff from JSON we don't need
				if (isset($array["reviews"])) { unset($array["reviews"]); }
				if (isset($array["id"])) { unset($array["id"]); }
				if (isset($array["sku_id"])) { unset($array["sku_id"]); }
				if (isset($array["name"])) { unset($array["name"]); }
				
				
				// Strip stuff from topReviews we don't need
				foreach ($array["topReviews"] as &$value) {
					if ($value["ScoreFormat"]) { unset ($value["ScoreFormat"]); }
					if ($value["OutletId"]) { unset ($value["OutletId"]); }
					if ($value["platform"]) { unset ($value["platform"]); }
					if ($value["language"]) { unset ($value["language"]); }
					unset ($value["isQuoteManual"]);
				}
				unset($value);
				$oc_jason = json_encode($array);
				$oc_jason = str_replace("\/", "/", $oc_jason);
				$oc_jason = str_replace("\u00a0", " ", $oc_jason);
				$oc_jason = str_replace("\u2013", "-", $oc_jason);
				
				$oc_jason = str_replace("openCriticScore", "score", $oc_jason);
				$oc_jason = str_replace("openCriticUrl", "url", $oc_jason);
				$oc_jason = str_replace("topReviews", "reviews", $oc_jason);
				$oc_jason = str_replace("publishedDate", "date", $oc_jason);
				$oc_jason = str_replace("externalUrl", "rURL", $oc_jason);
				$oc_jason = str_replace("outletName", "name", $oc_jason);
				$oc_jason = str_replace("displayScore", "dScore", $oc_jason);
				$oc_jason = str_replace("reviewCount", "count", $oc_jason);
				$sql = "INSERT INTO `opencritic` (`appid`, `json`) VALUES (".mysql_real_escape_string($the_appid).", '".mysql_real_escape_string($oc_jason)."')";
				$result = mysql_query($sql, $connection);
				return $oc_jason;
			} else {
				return "";
			}
		}
	}
	
	if (is_numeric($appid)) {
		if (is_numeric($r_all) && is_numeric($r_pos) && is_numeric($r_stm)) {
			$result = mysql_query("SELECT * FROM `steam_reviews` WHERE `appid`='".mysql_real_escape_string($appid)."' LIMIT 1", $con);
			$num_rows = mysql_num_rows($result);
			if ($num_rows > 0) {
				while ($row = mysql_fetch_array($result)) {
					$access_time = strtotime($row['update_time']);								
					if ($current_time - $access_time >= 43200) {
						// check to make sure the numbers haven't gone down
						if ($r_all > $row['total'] || $r_pos > $row['pos'] || $r_stm > $row['stm'] || $current_time - $access_time >= 259200) {						
							// delete the existing row, add new data to cache
							mysql_query("DELETE FROM `steam_reviews` WHERE `appid` = ".mysql_real_escape_string($appid), $con);
							mysql_query("INSERT INTO `steam_reviews` (appid, total, pos, stm) VALUES('".mysql_real_escape_string($appid)."', ".mysql_real_escape_string($r_all).",".mysql_real_escape_string($r_pos).",".mysql_real_escape_string($r_stm).")", $con);
						}
					}
				}
			} else {
				mysql_query("INSERT INTO `steam_reviews` (appid, total, pos, stm) VALUES('".mysql_real_escape_string($appid)."', ".mysql_real_escape_string($r_all).",".mysql_real_escape_string($r_pos).",".mysql_real_escape_string($r_stm).")", $con);
			}
		}
		
		// Setup return variable
		$return = "{";
		
		// Get SteamChart data
		$result = mysql_query("SELECT * FROM steamcharts WHERE appid='".$appid."' LIMIT 1", $con);
		$num_rows = mysql_num_rows($result);
		
		// if cached, return the database value
		if ($num_rows > 0) {
			while ($row = mysql_fetch_array($result)) {
				$access_time = strtotime($row['access_time']);
								
				if ($current_time - $access_time >= 3600) {
					$sql = "DELETE FROM `steamcharts` WHERE `appid` = ".mysql_real_escape_string($appid);
					$result = mysql_query($sql, $con);
					$text = GetNewChartValue($appid, $con);
					$chart_json = "\"chart\":{" . $text;
				} else {
					$chart_json = "\"chart\":{";
					$chart_json = "\"chart\":{\"current\": \"".trim($row['one_hour'])."\", \"peaktoday\": \"".trim($row['one_day'])."\", \"peakall\": \"".trim($row['all_time'])."\"}";;
				}	
			}
			
		// if not cached or expired, get new value
		} else {
			$text = GetNewChartValue($appid, $con);
			$chart_json = "\"chart\":{" . $text;
		}
		
		if ($chart_json == "\"chart\":{") {
			$chart_json = "\"chart\":{}";
		}
		
		$return = $return . "\"charts\":{" . $chart_json . "}";
		
		// Get OpenCritic data
		if (isset($oc)) {
			$result = mysql_query("SELECT * FROM opencritic WHERE appid=".$appid." LIMIT 1", $con);
			$num_rows = mysql_num_rows($result);
			$oc_json = "";
			
			// if cached, return the database value
			if ($num_rows > 0) {
				while ($row = mysql_fetch_array($result)) {
					$access_time = strtotime($row['access_time']);
									
					if ($current_time - $access_time >= 3600) {
						$sql = "DELETE FROM `opencritic` WHERE `appid` = ".mysql_real_escape_string($appid);
						$result = mysql_query($sql, $con);
						$oc_json_text = GetNewOCValue($appid, $con);
						if ($oc_json_text) { $oc_json = "\"oc\":" . $oc_json_text; }
					} else {
						$oc_json_text = $row['json'];
						if ($oc_json_text) { $oc_json = "\"oc\":" . $oc_json_text; }
					}
				}
				
			// if not cached or expired, get new value
			} else {
				$oc_json_text = GetNewOCValue($appid, $con);
				if ($oc_json_text) { $oc_json = "\"oc\":" . $oc_json_text; }
			}
			
			if ($oc_json != "") { $return = $return . "," . $oc_json; }
		}
		
		// Get SteamSpy data
		$result = mysql_query("SELECT * FROM steamspy WHERE appid='".$appid."' LIMIT 1", $con);
		$num_rows = mysql_num_rows($result);
		
		// if cached, return the database value
		if ($num_rows > 0) {
			while ($row = mysql_fetch_array($result)) {
				$access_time = strtotime($row['access_time']);
								
				if ($current_time - $access_time >= 43200) {
					$sql = "DELETE FROM `steamspy` WHERE `appid` = '".mysql_real_escape_string($appid)."'";
					$result = mysql_query($sql, $con);
					$spy_json = GetNewSpyValue($appid, $con);
				} else {
					$spy_json = "\"steamspy\":{\"owners\": \"".$row["owners"]."\", \"owners_variance\": \"".$row["owners_variance"]."\", \"players_forever\": \"".$row["players_forever"]."\", \"players_forever_variance\": \"".$row["players_forever_variance"]."\", \"players_2weeks\": \"".$row["players_2weeks"]."\", \"players_2weeks_variance\": \"".$row["players_2weeks_variance"]."\", \"average_forever\": \"".$row["average_forever"]."\", \"average_2weeks\": \"".$row["average_2weeks"]."\"}";
				}	
			}
			
		// if not cached or expired, get new value
		} else {
			$spy_json = GetNewSpyValue($appid, $con);
		}
		
		$return = $return . "," . $spy_json;
		
		// Get WSGF data
		$url = Config::WSGFEndpoint.$appid;
		
		$filestring = file_get_contents($url);
		$filestring = str_replace("<4kGrade>", "<FourKGrade>", $filestring);
		$filestring = str_replace("</4kGrade>", "</FourKGrade>", $filestring);
		
		$xml = simplexml_load_string($filestring);
		$wsgf_json = json_encode($xml);
		$wsgf_json = str_replace("{\"0\":", "", $wsgf_json);
		$wsgf_json = str_replace("\"\\n\"}", "", $wsgf_json);
		$wsgf_json = str_replace("{\"node\":{", "\"wsgf\":{", $wsgf_json);
		$wsgf_json = str_replace("}}", "}", $wsgf_json);
		
		if (strlen($wsgf_json)) {
			$return = $return . "," . $wsgf_json;
		}	
		
		// Get Survey data
		$sql = mysql_query("SELECT * FROM `game_survey` WHERE `appid`='".$appid."'");
		$num_rows = mysql_num_rows($sql);
		$survey_json = "";
		if ($num_rows == 0) {
			$survey_json = $survey_json . "\"survey\": {\"success\": false,";
		} else {
			$survey_json = $survey_json . "\"survey\": {\"success\": true, \"responses\": ".$num_rows.",";
		}
		
		// Initialize variables
		$fr_30 = $fr_fi = $fr_va = 0;
		$gs_y = $gs_n = 0;	
		$nvidia = $amd = $intel = $other = 0;
		$nvidia_y = $amd_y = $intel_y = $other_y = 0;
		$less = $hd = $wqhd = $fk = 1;
		$less_y = $hd_y = $wqhd_y = $fk_y = 0;
		
		// Gather data from the records
		while($row = mysql_fetch_array($sql)) {
			switch ($row["fr"]) {
				case "30":
					$fr_30 = $fr_30 + 1;
					break;
				case "fi":
					$fr_fi = $fr_fi + 1;
					break;
				case "va":
					$fr_va = $fr_va + 1;
					break;
			}
			
			if ($row["mr"] == "less") { 
				$less = $less + 1; 
				if ($row["fs"] == "yes") {
					$less_y = $less_y + 1;
				}
			}
			if ($row["mr"] == "hd") { 
				$hd = $hd + 1; 
				if ($row["fs"] == "yes") {
					$hd_y = $hd_y + 1;
				}
			}
			if ($row["mr"] == "wqhd") { 
				$wqhd = $wqhd + 1; 
				if ($row["fs"] == "yes") {
					$wqhd_y = $wqhd_y + 1;
				}
			}
			if ($row["mr"] == "4k") { 
				$fk = $fk + 1; 
				if ($row["fs"] == "yes") {
					$fk_y = $fk_y + 1;
				}
			}
			
			if ($row["gs"] == "yes") { $gs_y = $gs_y + 1; }
			if ($row["gs"] == "no") { $gs_n = $gs_n + 1; }
			
			if ($row["gc"] == "nvidia") { $nvidia = $nvidia + 1; }
			if ($row["gc"] == "amd") { $amd = $amd + 1; }
			if ($row["gc"] == "intel") { $intel = $intel + 1; }
			if ($row["gc"] == "ns") { $other = $other + 1; }
			
			if ($row["gc"] == "nvidia" && $row["pw"] == "yes") { $nvidia_y = $nvidia_y + 1; }
			if ($row["gc"] == "amd" && $row["pw"] == "yes") { $amd_y = $amd_y + 1; }
			if ($row["gc"] == "intel" && $row["pw"] == "yes") { $intel_y = $intel_y + 1; }
			if ($row["gc"] == "ns" && $row["pw"] == "yes") { $other_y = $other_y + 1; }
		}
		
		// Determine Framerate rendered
		if ($num_rows == 0) { $num_rows = 1; }
		
		if ($fr_30 >= $fr_fi && $fr_30 >= $fr_va) {
			$survey_json = $survey_json . "\"fr\": \"30\",";
			$survey_json = $survey_json . "\"frp\": ".round(($fr_30 / $num_rows)*100).",";
		} else {
			if ($fr_fi > $fr_30 && $fr_fi > $fr_va) {
				$survey_json = $survey_json . "\"fr\": \"fi\",";
				$survey_json = $survey_json . "\"frp\": ".round(($fr_fi / $num_rows)*100).",";
			} else {
				if ($fr_va >= $fr_30 && $fr_va >= $fr_fi) {
					$survey_json = $survey_json . "\"fr\": \"va\",";
					$survey_json = $survey_json . "\"frp\": ".round(($fr_va / $num_rows)*100).",";
				}
			}
		}
		
		// Determine resolution rendered
		$less_avg = $less_y / $less;
		$hd_avg = $hd_y / $hd;
		$wqhd_avg = $wqhd_y / $wqhd;
		$fk_avg = $fk_y / $fk;
		
		if ($fk_avg >= $wqhd_avg && $fk_avg >= $hd_avg && $fk_avg >= $less_avg) {
			$survey_json = $survey_json . "\"mr\": \"4k\",";
		} else {
			if ($wqhd_avg >= $fk_avg && $wqhd_avg >= $hd_avg && $wqhd_avg >= $less_avg) {
				$survey_json = $survey_json . "\"mr\": \"wqhd\",";
			} else {
				if ($hd_avg >= $fk_avg && $hd_avg >= $wqhd_avg && $hd_avg >= $less_avg) {
					$survey_json = $survey_json . "\"mr\": \"hd\",";
				} else {
					if ($less_avg >= $fk_avg && $less_avg >= $wqhd_avg && $less_avg >= $hd_avg) {					
						$survey_json = $survey_json . "\"mr\": \"less\",";
					} else {
						$survey_json = $survey_json . "\"mr\": \"less\",";	
					}
				}
			}
		}
		
		// Determine Game Settings rendered
		if ($gs_y >= $gs_n) { 
			$survey_json = $survey_json . "\"gs\": true,"; 
		} else {
			$survey_json = $survey_json . "\"gs\": false,"; 
		}
		
		// Determine satisfaction rates rendered
		if ($nvidia > 0) {
			$survey_json = $survey_json . "\"nvidia\": ".round(($nvidia_y / $nvidia)*100).",";
		}
		if ($amd > 0) {
			$survey_json = $survey_json . "\"amd\": ".round(($amd_y / $amd)*100).",";
		}
		if ($intel > 0) {
			$survey_json = $survey_json . "\"intel\": ".round(($intel_y / $intel)*100).",";
		}
		if ($other > 0) {
			$survey_json = $survey_json . "\"other\": ".round(($other_y / $other)*100).",";
		}
		
		// Close JSON text
		$survey_json = substr($survey_json, 0, -1);
		$survey_json = $survey_json . "}";
		
		$return = $return . "," . $survey_json;
		
		// Get EXFGLS data
		$exfgls_json = "\"exfgls\":{ \"appid\": " . $appid . ", \"excluded\": ";	
		$result = mysql_query("SELECT * FROM exfgls WHERE appid=".$appid);
		$num_rows = mysql_num_rows($result);

		if ($num_rows > 0) {
			$exfgls_json = $exfgls_json."true}";
		} else {
			$exfgls_json = $exfgls_json."false}";
		}
		
		$return = $return . "," . $exfgls_json;
		
		// Get hltb data
		$result = mysql_query("SELECT appid, hltb_id FROM game_links WHERE appid=".$appid." LIMIT 1");
		$rowcount = mysql_num_rows($result);
		while($row = mysql_fetch_array($result)) {
			$hltb_url = "http://www.howlongtobeat.com/game.php?id=".$row['hltb_id'];
			$hltb_submit = "https://howlongtobeat.com/submit.php?s=add&gid=".$row['hltb_id'];
		}
		
		$hltb_json = "\"hltb\":{";
		
		if ($rowcount == 1) {
			$file_headers = @get_headers($hltb_url);
			if($file_headers[0] == 'HTTP/1.0 500 Internal Server Error') {
				$up = false;
			} else {
				$up = true;
			}	
			
			if ($up) {
				$main_story = ""; $main_extras = ""; $comp = "";
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
				
				$hltb_json = $hltb_json."\"success\": true, \"main_story\": \"".trim($main_story)."\", \"main_extras\": \"".trim($main_extras)."\", \"comp\": \"".trim($comp)."\", \"url\": \"".$hltb_url."\", \"submit_url\": \"".$hltb_submit."\"}";
			} else {
				$hltb_json = $hltb_json."\"success\": false }";
			}
		} else {
			$hltb_json = $hltb_json."\"success\": false }";
		}
		
		$return = $return . "," . $hltb_json;
		
		// Get optional Metacritic data
		if (isset($mcurl)) {
			if (substr($mcurl, 0, 26) == "http://www.metacritic.com/") {
				// checks to see if the value is cached
				$result = mysql_query("SELECT * FROM metacritic WHERE mcurl='".$mcurl."' LIMIT 1", $con);
				$num_rows = mysql_num_rows($result);
				
				// if cached, return the database value
				if ($num_rows > 0) {
					while ($row = mysql_fetch_array($result)) {
						$access_time = strtotime($row['access_time']);
										
						if ($current_time - $access_time >= 28800) {
							$sql = "DELETE FROM `metacritic` WHERE `mcurl` = '".mysql_real_escape_string($mcurl)."'";
							$result = mysql_query($sql, $con);
							$text = GetNewMCValue($mcurl, $con);
							if ($text == "") { $text = "0"; }
							$mc_json = "\"metacritic\":{\"userscore\": " . $text . "}";
						} else {
							$mc_json = "\"metacritic\":{\"userscore\": " . $row['score'] . "}";
						}	
					}
					
				// if not cached or expired, get new value
				} else {
					$text = GetNewMCValue($mcurl, $con);
					if ($text == "") { $text = "0"; }
					$mc_json = "\"metacritic\":{\"userscore\": " . $text . "}";
				}
				
				$return = $return . "," . $mc_json;
			}
		}
		
		// Output data
		echo $return . "}";
	}
	
	exit;
?>
