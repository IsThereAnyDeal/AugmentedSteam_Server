<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();



// Select record for passed APPID
$appid = mysql_real_escape_string($_GET['appid']);

$return = "{\"dlc\":{";

if (is_numeric ($appid)) {
	$result = mysql_query("SELECT COUNT(*) as count,appid,dlc_category FROM gamedata WHERE appid=".$appid." GROUP BY appid,dlc_category ORDER BY count DESC LIMIT 3");
	while($row = mysql_fetch_array($result))
		{
			$result2 = mysql_query("SELECT * FROM dlc_category WHERE id=".$row['dlc_category']." LIMIT 1");
			while($dlc = mysql_fetch_array($result2))
				{
					$return = $return . '"'.$dlc['category_name'].'":{"icon":"'.$dlc['category_icon'].'","text":"'.$dlc['category_text'].'"},';
				}
		}
}

$return = substr($return, 0, -1);
$return = $return . "}}";

echo $return;

// Close database connection
mysql_close($con);
exit;
