<?php
	require_once("../config.php");
	
	// Get values to convert
	$usd = 0; $gbp = 0; $eur = 0; $rub = 0; $brl = 0; $jpy = 0; $nok = 0;
	$idr = 0; $myr = 0; $php = 0; $sgd = 0; $thb = 0; $vnd = 0; $krw = 0;
	$try = 0; $uah = 0; $mxn = 0; $cad = 0; $aud = 0; $nzd = 0; $inr = 0;
	$hkd = 0; $twd = 0; $cny = 0; $sar = 0; $zar = 0; $aed = 0; $chf = 0;
	$clp = 0; $pen = 0; $cop = 0; $local = 0;
	if (isset($_GET['usd'])) { $usd = mysql_real_escape_string($_GET['usd']); }
	if (isset($_GET['gbp'])) { $gbp = mysql_real_escape_string($_GET['gbp']); }
	if (isset($_GET['eur'])) { $eur = mysql_real_escape_string($_GET['eur']); }
	if (isset($_GET['rub'])) { $rub = mysql_real_escape_string($_GET['rub']); }
	if (isset($_GET['brl'])) { $brl = mysql_real_escape_string($_GET['brl']); }
	if (isset($_GET['jpy'])) { $jpy = mysql_real_escape_string($_GET['jpy']); }
	if (isset($_GET['nok'])) { $nok = mysql_real_escape_string($_GET['nok']); }
	if (isset($_GET['idr'])) { $idr = mysql_real_escape_string($_GET['idr']); }
	if (isset($_GET['myr'])) { $myr = mysql_real_escape_string($_GET['myr']); }
	if (isset($_GET['php'])) { $php = mysql_real_escape_string($_GET['php']); }
	if (isset($_GET['sgd'])) { $sgd = mysql_real_escape_string($_GET['sgd']); }
	if (isset($_GET['thb'])) { $thb = mysql_real_escape_string($_GET['thb']); }
	if (isset($_GET['vnd'])) { $vnd = mysql_real_escape_string($_GET['vnd']); }
	if (isset($_GET['krw'])) { $krw = mysql_real_escape_string($_GET['krw']); }
	if (isset($_GET['try'])) { $try = mysql_real_escape_string($_GET['try']); }
	if (isset($_GET['uah'])) { $uah = mysql_real_escape_string($_GET['uah']); }
	if (isset($_GET['mxn'])) { $mxn = mysql_real_escape_string($_GET['mxn']); }
	if (isset($_GET['cad'])) { $cad = mysql_real_escape_string($_GET['cad']); }
	if (isset($_GET['aud'])) { $aud = mysql_real_escape_string($_GET['aud']); }
	if (isset($_GET['nzd'])) { $nzd = mysql_real_escape_string($_GET['nzd']); }
	if (isset($_GET['inr'])) { $inr = mysql_real_escape_string($_GET['inr']); }
	if (isset($_GET['hkd'])) { $hkd = mysql_real_escape_string($_GET['hkd']); }
	if (isset($_GET['twd'])) { $twd = mysql_real_escape_string($_GET['twd']); }
	if (isset($_GET['cny'])) { $cny = mysql_real_escape_string($_GET['cny']); }
	if (isset($_GET['sar'])) { $sar = mysql_real_escape_string($_GET['sar']); }
	if (isset($_GET['zar'])) { $zar = mysql_real_escape_string($_GET['zar']); }
	if (isset($_GET['aed'])) { $aed = mysql_real_escape_string($_GET['aed']); }
	if (isset($_GET['chf'])) { $chf = mysql_real_escape_string($_GET['chf']); }
	if (isset($_GET['clp'])) { $clp = mysql_real_escape_string($_GET['clp']); }
	if (isset($_GET['pen'])) { $pen = mysql_real_escape_string($_GET['pen']); }
	if (isset($_GET['cop'])) { $cop = mysql_real_escape_string($_GET['cop']); }
	if (isset($_GET['local'])) { $local = mysql_real_escape_string($_GET['local']); }
	
	// Query conversion table
	$sql = "SELECT * FROM `currency` WHERE `Base`='" . $local . "' LIMIT 1";	
	$result = mysql_query($sql, $con);	
		
	// Convert!
	while($currency = mysql_fetch_array($result)) {
		$usd = $usd / $currency['USD'];
		$gbp = $gbp / $currency['GBP'];
		$eur = $eur / $currency['EUR'];
		$rub = $rub / $currency['RUB'];
		$brl = $brl / $currency['BRL'];
		$jpy = $jpy / $currency['JPY'];
		$nok = $nok / $currency['NOK'];
		$idr = $idr / $currency['IDR'];
		$myr = $myr / $currency['MYR'];
		$php = $php / $currency['PHP'];
		$sgd = $sgd / $currency['SGD'];
		$thb = $thb / $currency['THB'];
		$vnd = $vnd / $currency['VND'];
		$krw = $krw / $currency['KRW'];
		$try = $try / $currency['TRY'];
		$uah = $uah / $currency['UAH'];
		$mxn = $mxn / $currency['MXN'];
		$cad = $cad / $currency['CAD'];
		$aud = $aud / $currency['AUD'];
		$nzd = $nzd / $currency['NZD'];
		$inr = $inr / $currency['INR'];
		$hkd = $hkd / $currency['HKD'];
		$twd = $twd / $currency['TWD'];
		$cny = $cny / $currency['CNY'];
		$sar = $sar / $currency['SAR'];
		$zar = $zar / $currency['ZAR'];
		$aed = $aed / $currency['AED'];
		$chf = $chf / $currency['CHF'];
		$clp = $clp / $currency['CLP'];
		$pen = $pen / $currency['PEN'];
		$cop = $cop / $currency['COP'];
	}	
	
	$total = $usd + $gbp + $eur + $rub + $brl + $jpy + $nok + $idr + $myr + $php + $sgd + $thb + $vnd + $krw + $try + $uah + $mxn + $cad + $aud + $nzd + $inr + $hkd + $twd + $cny + $sar + $zar + $aed + $chf + $clp + $pen + $cop;
	echo $total;
	
	mysql_close($con);
?>