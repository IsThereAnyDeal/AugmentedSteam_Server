<?php

require_once __DIR__ . "/../../code/autoloader.php";

\Core\Database::connect();
$steamId = \Account::login();

const validValues = [
    "mr" => ["less", "hd", "wqhd", "4k"],
    "fs" => ["yes", "no"],
    "fr" => ["30", "fi", "va", "ns"],
    "gs" => ["yes", "no"],
    "pw" => ["yes", "no"],
    "gc" => ["nvidia", "amd", "intel", "ns"],
];

$endpoint = new \Api\Endpoint();
$endpoint->params([], [], [
    "appid",
    "mr",
    "fs",
    "fr",
    "gs",
    "pw",
    "gc",
], []);

$response = new \Api\Response();

function invalidArg($key, $value) {
    $GLOBALS["response"]->fail("invalid_request", "$value is not in the domain of $key", 400);
}

foreach (validValues as $key => $values) {
    $passedArg = $endpoint->getParam($key);
    if (!in_array($passedArg, $values)) {
        invalidArg($key, $passedArg);
    }
}

$appid = $endpoint->getParamAsInt("appid");

// App IDs are always a multiple of 10
if ($appid < 10 || $appid % 10 !== 0) { invalidArg("appid", $appid); }

\dibi::query(
    "INSERT INTO [game_survey] ([appid], [steamid], [mr], [fs], [fr], [gs], [pw], [gc]) VALUES (%i, %i, %s, %s, %s, %s, %s, %s)",
    $appid,
    $steamId,
    $endpoint->getParam("mr"),
    $endpoint->getParam("fs"),
    $endpoint->getParam("fr"),
    $endpoint->getParam("gs"),
    $endpoint->getParam("pw"),
    $endpoint->getParam("gc"),
);

$response->respond();
