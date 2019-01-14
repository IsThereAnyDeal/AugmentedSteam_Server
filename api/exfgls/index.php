<?php
	require_once("../config.php");

	// Select record for passed APPID
	$appid = "";
	if (isset($_GET['appid'])) { $appid = mysql_real_escape_string($_GET['appid']); }

	$return = "{\"exfgls\":{ \"appid\": " . $appid . ", \"excluded\": ";

	if ($appid <> "") {
		if (is_numeric ($appid)) {
			$result = mysql_query("SELECT * FROM exfgls WHERE appid=".$appid);
			$num_rows = mysql_num_rows($result);

			if ($num_rows > 0) {
				$return = $return."true}}";
			} else {
				$return = $return."false}}";
			}
		}
	} else {
		$sql = "SELECT * FROM `exfgls`";	
		$result = mysql_query($sql, $con);
		
		$text = '{"exfgls": [';
		
		while($exfgls = mysql_fetch_array($result)) {
			$text = $text . "\"" . $exfgls['appid'] . "\",";
		}
		$text = substr($text, 0, -1);
		$text = $text . "]}";
		
		echo $text;
		exit;
	}

	echo $return;
	exit;
?>
