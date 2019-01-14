<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();

	
// Select record for passed twitch channel
$channel = mysql_real_escape_string($_GET['channel'], $con);

$url = "https://api.twitch.tv/kraken/streams?channel=".$channel;

$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Client-ID: " . Config::TwitchApiKey
  )
);

$context = stream_context_create($opts);

$html = file_get_contents($url, false, $context);
echo $html;
