<?php
	require_once("../config.php");
	
	$cur = array("USD", "GBP", "EUR", "RUB", "BRL", "JPY", "NOK", "IDR", "MYR", "PHP", "SGD", "THB", "KRW", "TRY", "MXN", "CAD", "AUD", "NZD", "INR", "HKD", "CNY", "ZAR", "CHF", "ILS", "PLN", "VND", "UAH", "TWD", "SAR", "AED", "CLP", "PEN", "COP", "UYU", "ARS", "CRC", "KZT", "KWD", "QAR");
	
	$raw = file_get_contents("https://openexchangerates.org/api/latest.json?app_id=".$openexchangeapi);
	$raw_array = json_decode($raw, true);
	
	foreach ($cur as $basevalue) {		
		$usd = $raw_array['rates']['USD'] / $raw_array['rates'][$basevalue];
		$aud = $raw_array['rates']['AUD'] / $raw_array['rates'][$basevalue];
		$brl = $raw_array['rates']['BRL'] / $raw_array['rates'][$basevalue];
		$cad = $raw_array['rates']['CAD'] / $raw_array['rates'][$basevalue];
		$chf = $raw_array['rates']['CHF'] / $raw_array['rates'][$basevalue];
		$cny = $raw_array['rates']['CNY'] / $raw_array['rates'][$basevalue];
		$gbp = $raw_array['rates']['GBP'] / $raw_array['rates'][$basevalue];
		$hkd = $raw_array['rates']['HKD'] / $raw_array['rates'][$basevalue];
		$idr = $raw_array['rates']['IDR'] / $raw_array['rates'][$basevalue];
		$inr = $raw_array['rates']['INR'] / $raw_array['rates'][$basevalue];
		$jpy = $raw_array['rates']['JPY'] / $raw_array['rates'][$basevalue];
		$krw = $raw_array['rates']['KRW'] / $raw_array['rates'][$basevalue];
		$mxn = $raw_array['rates']['MXN'] / $raw_array['rates'][$basevalue];
		$myr = $raw_array['rates']['MYR'] / $raw_array['rates'][$basevalue];
		$nok = $raw_array['rates']['NOK'] / $raw_array['rates'][$basevalue];
		$nzd = $raw_array['rates']['NZD'] / $raw_array['rates'][$basevalue];
		$php = $raw_array['rates']['PHP'] / $raw_array['rates'][$basevalue];
		$rub = $raw_array['rates']['RUB'] / $raw_array['rates'][$basevalue];
		$sgd = $raw_array['rates']['SGD'] / $raw_array['rates'][$basevalue];
		$try = $raw_array['rates']['TRY'] / $raw_array['rates'][$basevalue];
		$zar = $raw_array['rates']['ZAR'] / $raw_array['rates'][$basevalue];
		$eur = $raw_array['rates']['EUR'] / $raw_array['rates'][$basevalue];
		$ils = $raw_array['rates']['ILS'] / $raw_array['rates'][$basevalue];
		$pln = $raw_array['rates']['PLN'] / $raw_array['rates'][$basevalue];
		$vnd = $raw_array['rates']['VND'] / $raw_array['rates'][$basevalue];
		$uah = $raw_array['rates']['UAH'] / $raw_array['rates'][$basevalue];
		$thb = $raw_array['rates']['THB'] / $raw_array['rates'][$basevalue];
		$twd = $raw_array['rates']['TWD'] / $raw_array['rates'][$basevalue];
		$sar = $raw_array['rates']['SAR'] / $raw_array['rates'][$basevalue];
		$aed = $raw_array['rates']['AED'] / $raw_array['rates'][$basevalue];
		$clp = $raw_array['rates']['CLP'] / $raw_array['rates'][$basevalue];
		$pen = $raw_array['rates']['PEN'] / $raw_array['rates'][$basevalue];
		$cop = $raw_array['rates']['COP'] / $raw_array['rates'][$basevalue];
		$uyu = $raw_array['rates']['UYU'] / $raw_array['rates'][$basevalue];
		$ars = $raw_array['rates']['ARS'] / $raw_array['rates'][$basevalue];
		$crc = $raw_array['rates']['CRC'] / $raw_array['rates'][$basevalue];
		$kzt = $raw_array['rates']['KZT'] / $raw_array['rates'][$basevalue];
		$kwd = $raw_array['rates']['KWD'] / $raw_array['rates'][$basevalue];
		$qar = $raw_array['rates']['QAR'] / $raw_array['rates'][$basevalue];
		
		$sql = "UPDATE `currency` SET `AUD`=".$aud.", `BRL`=".$brl.", `CAD`=".$cad.", `CHF`=".$chf.", `CNY`=".$cny.", `GBP`=".$gbp.", `HKD`=".$hkd.", `IDR`=".$idr.", `INR`=".$inr.", `JPY`=".$jpy.", `KRW`=".$krw.", `MXN`=".$mxn.", `MYR`=".$myr.", `NOK`=".$nok.", `NZD`=".$nzd.", `PHP`=".$php.", `RUB`=".$rub.", `SGD`=".$sgd.", `TRY`=".$try.", `ZAR`=".$zar.", `EUR`=".$eur.", `USD`=".$usd.", `ILS`=".$ils.", `PLN`=".$pln.", `VND`=".$vnd.", `UAH`=".$uah.", `THB`=".$thb.", `TWD`=".$twd.", `SAR`=".$sar.", `AED`=".$aed.", `CLP`=".$clp.", `PEN`=".$pen.", `COP`=".$cop.", `UYU`=".$uyu.", `ARS`=".$ars.", `CRC`=".$crc.", `KZT`=".$kzt.", `KWD`=".$kwd.", `QAR`=".$qar." WHERE `Base`='".$basevalue."'";
		$result = mysql_query($sql, $con);
	}
?>