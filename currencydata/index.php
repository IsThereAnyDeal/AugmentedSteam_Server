<?php
	require_once("../config.php");
	
	$base = strtoupper($_GET['base']);
	$base = mysql_real_escape_string($base);
	
	$types = array("USD", "GBP", "EUR", "RUB", "BRL", "JPY", "NOK", "IDR", "MYR", "PHP", "SGD", "THB", "VND", "KRW", "TRY", "UAH", "MXN", "CAD", "AUD", "NZD", "INR", "HKD", "TWD", "CNY", "SAR", "ZAR", "AED", "CHF", "CLP", "PEN", "COP", "UYU","ILS","PLN","ARS","CRC","KZT","KWD","QAR");
	
	if (in_array($base, $types)) {		
		// Query conversion table
		$sql = "SELECT EUR,GBP,USD,RUB,BRL,JPY,NOK,IDR,MYR,PHP,SGD,THB,VND,KRW,TRY,UAH,MXN,CAD,AUD,NZD,INR,HKD,TWD,CNY,SAR,ZAR,AED,CHF,CLP,PEN,COP,UYU,ILS,PLN,ARS,CRC,KZT,KWD,QAR FROM `currency` WHERE base='" . $base . "'";	
		$result = mysql_query($sql, $con);	
			
		$rows = array();
		while($r = mysql_fetch_assoc($result)) {
			$rows[] = $r;
		}
		$all = json_encode($rows);
		$all = str_replace("[", "", $all);
		$all = str_replace("]", "", $all);
		$all = str_replace("\n", "", $all);
		$all = str_replace("\r", "", $all);
		
		$all = "{\"".$base."\":" . $all . "}";
		
		echo $all;
	}
?>