<?php
	header("Access-Control-Allow-Origin: *");
	
	$steam64 = $_GET['steam64'];
	
	if (is_numeric($steam64)) {	
		$url = "http://steamrep.com/api/beta2/reputation/".$steam64;
		$filestring = file_get_contents($url);		
		$array=json_decode(json_encode(simplexml_load_string($filestring)),true);
		
		$full = $array["reputation"]["full"];
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
		
		echo $full_html;
	}
?>