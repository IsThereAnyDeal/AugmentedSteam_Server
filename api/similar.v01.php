<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["appid"], [
        "count" => 5,
        "shuffle" => false
    ]);

$response = new \Api\Response();

$guzzle = new \GuzzleHttp\Client();
$logger = Log::channel("similar", true);
$similar = new \SteamPeek\SteamPeek($guzzle, $logger);
$data = $similar->getSimilar(
    $endpoint->getParamAsInt("appid"),
    $endpoint->getParamAsInt("count"),
    (bool)$endpoint->getParam("shuffle")
);

$response
    ->data($data)
    ->respond();
