<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();


// Select record for passed APPID
$appid = mysql_real_escape_string($_GET['appid']);
$cur = mysql_real_escape_string($_GET['cur']);
$foil = "";
$return = "";
$value = 0;
$count = 0;
if(isset($_GET['foil'])) { $foil = mysql_real_escape_string($_GET['foil']); }

if (is_numeric ($appid)) {
    $sql = "SELECT price_".$cur." FROM market_data WHERE appid=".$appid." AND `type`='card'";
    if ($cur == "usd") { $sql = "SELECT price FROM market_data WHERE appid=".$appid." AND `type`='card'"; }
    if ($foil) { $sql = $sql." AND rarity='foil'"; }
    else { $sql = $sql." AND rarity !='foil'"; }
    $result = mysql_query($sql);
    while($row = mysql_fetch_array($result))
        {
            $return = $return.$row[0]."<br>";
            $value = $value + $row[0];
            $count = $count + 1;
        }
}

if ($count > 0) {
    $display = $value / $count;
    $display = round($display, 2);
}

echo $display;

// Close database connection
mysql_close($con);
?>
