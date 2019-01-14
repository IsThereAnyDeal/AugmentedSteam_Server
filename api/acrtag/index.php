<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();


$sql = "SELECT * FROM `acrtag`";
$result = mysql_query($sql, $con);

$text = '{"acrtag": [';

while($acrtag = mysql_fetch_array($result)) {
    $text = $text . "\"" . $acrtag['subid'] . "\",";
}
$text = substr($text, 0, -1);
$text = $text . "]}";

echo $text;
