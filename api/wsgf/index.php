<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();

	
$appid = mysql_real_escape_string($_GET['appid']);

if (is_numeric($appid)) {

    $url = Config::WSGFEndpoint.$appid;

    $filestring = file_get_contents($url);
    $filestring = str_replace("<4kGrade>", "<FourKGrade>", $filestring);
    $filestring = str_replace("</4kGrade>", "</FourKGrade>", $filestring);

    $xml = simplexml_load_string($filestring);

    echo json_encode($xml);

}
