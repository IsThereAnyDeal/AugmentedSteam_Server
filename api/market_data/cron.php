<?php
	require_once("../config.php");
	
	// Get data from private market data API server
	$data = file_get_contents($steamtoolsapi_server);	
	$data_array = json_decode($data, true);
	
	if (!empty($data_array)) {
		// Get currency conversion values
		$sql = "SELECT * FROM `currency` WHERE `Base`='USD'";
		$result = mysql_query($sql, $con);
		while ($row = mysql_fetch_array($result)) {
			$brl = $row['BRL'];
			$rub = $row['RUB'];
			$gbp = $row['GBP'];
			$eur = $row['EUR'];
			$jpy = $row['JPY'];
			$nok = $row['NOK'];
			$idr = $row['IDR'];
			$myr = $row['MYR'];
			$php = $row['PHP'];
			$sgd = $row['SGD'];
			$thb = $row['THB'];
			$vnd = $row['VND'];
			$krw = $row['KRW'];
			$try = $row['TRY'];
			$uah = $row['UAH'];
			$mxn = $row['MXN'];
			$cad = $row['CAD'];
			$aud = $row['AUD'];
			$nzd = $row['NZD'];
		}
		
		// Delete existing database entries
		$sql = "TRUNCATE TABLE `market_data`";
		$result = mysql_query($sql, $con);
		
		foreach ($data_array as &$value) {
			$game = $value['game'];
			$name = $value['name'];
			$img = $value['img'];
			$appid = $value['appid'];
			$url = $value['url'];
			$price = $value['price'];
			$quantity = $value['quantity'];
			$type = $value['type'];
			$modified = $value['modified'];
			$rarity = $value['rarity'];
			
			$price_brl = round(($price * $brl), 2);
			$price_rub = round(($price * $rub), 2);
			$price_gbp = round(($price * $gbp), 2);
			$price_eur = round(($price * $eur), 2);
			$price_jpy = round(($price * $jpy), 2);
			$price_nok = round(($price * $nok), 2);
			$price_idr = round(($price * $idr), 2);
			$price_myr = round(($price * $myr), 2);
			$price_php = round(($price * $php), 2);
			$price_sgd = round(($price * $sgd), 2);
			$price_thb = round(($price * $thb), 2);
			$price_vnd = round(($price * $vnd), 2);
			$price_krw = round(($price * $krw), 2);
			$price_try = round(($price * $try), 2);
			$price_uah = round(($price * $uah), 2);
			$price_mxn = round(($price * $mxn), 2);
			$price_cad = round(($price * $cad), 2);
			$price_aud = round(($price * $aud), 2);
			$price_nzd = round(($price * $nzd), 2);
			
			$sql = 'INSERT INTO `market_data` VALUES ( "", "'.$game.'", "'.$name.'", "'.$img.'", "'.$appid.'", "'.$url.'", '.$price.', '.$price_brl.', '.$price_rub.', '.$price_gbp.', '.$price_eur.', '.$price_jpy.', '.$price_nok.', '.$price_idr.', '.$price_myr.', '.$price_php.', '.$price_sgd.', '.$price_thb.', '.$price_vnd.', '.$price_krw.', '.$price_try.', '.$price_uah.', '.$price_mxn.', '.$price_cad.', '.$price_aud.', '.$price_nzd.', '.$quantity.', "'.$type.'", "'.$modified.'", "'.$rarity.'" )';
			$result = mysql_query($sql, $con);
		}
	} else {
		echo "Data from external site not found.";
	}
?>