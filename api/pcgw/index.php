<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();

	
$appid = mysql_real_escape_string($_GET['appid']);

if (is_numeric($appid)) {
    $url = Config::PCGWEndpoint.$appid."-5D-5D/format%3Djson";
    $filestring = file_get_contents($url);
    echo $filestring;
}
