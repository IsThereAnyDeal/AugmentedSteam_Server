<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();


// Select record for passed APPID
$raw_appids = mysql_real_escape_string($_GET['appids']);
$appids = explode(",",mysql_real_escape_string($_GET['appids']));
$cur = "usd";
if(isset($_GET['cur'])) { $cur = mysql_real_escape_string($_GET['cur']); }
$foil = "";
if(isset($_GET['foil'])) { $foil = mysql_real_escape_string($_GET['foil']); }

$display = "{\"avg_values\": {";

if (count($appids) > 0) {
    $sql = "SELECT `appid`,ROUND(AVG(`price_".$cur."`),2) as avg FROM market_data WHERE `type`='card' AND `appid` in (".$raw_appids.") ";
    if ($cur == "usd") { $sql = "SELECT `appid`,ROUND(AVG(`price`),2) as avg FROM market_data WHERE `type`='card' AND `appid` in (".$raw_appids.") "; }
    if ($foil) { $sql = $sql." AND rarity='foil'"; }
    else { $sql = $sql." AND rarity !='foil'"; }
    $sql = $sql . " GROUP BY `appid`";
    $result = mysql_query($sql);
    while($row = mysql_fetch_array($result))
    {
        $display = $display . "\"".$row["appid"]."\": ".$row["avg"].",";
    }
}

if (substr($display, -1, 1) == ',')
{
    $display = substr($display, 0, -1);
}

$display = $display . "}}";

echo $display;

// Close database connection
mysql_close($con);
