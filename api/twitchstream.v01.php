<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["channel"]);

$guzzle = new \GuzzleHttp\Client();
try {
    $twitch = new \Twitch\Api\Endpoint\GetStreams($guzzle);
    $twitch->setUserLogin($endpoint->getParam("channel"));
} catch (\Exception $e) {
    (new \Api\Response())->fail();
}

(new \Api\Response())
    ->data($twitch->getItemEnumerator()->current())
    ->respond();
