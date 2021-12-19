<?php

require_once __DIR__ . "/../../code/autoloader.php";
require_once __DIR__ . "/../../code/Survey/ValidValues.php";

\Core\Database::connect();
$steamId = \Account::login();

$endpoint = new \Api\Endpoint();
$endpoint->params([], [], [
    "appid",
    "steam_id"
], [
    "framerate" => NULL,
    "optimized" => NULL,
    "lag" => NULL,
    "graphics_settings" => NULL,
    "bg_sound" => NULL,
    "good_controls" => NULL,
]);

$response = new \Api\Response();

function invalidArg($key, $value) {
    $GLOBALS["response"]->fail("invalid_request", "$value is not in the domain of $key", 400);
}

foreach (SURVEY_VALID_VALUES as $key => $values) {
    $passedArg = $endpoint->getParam($key);
    if (!is_null($passedArg) && !in_array($passedArg, $values, true)) {
        invalidArg($key, $passedArg);
    }
}

$appid = $endpoint->getParamAsInt("appid");

// App IDs are always a multiple of 10
if ($appid < 10 || $appid % 10 !== 0) { invalidArg("appid", $appid); }

function toBoolean($key) {
    $value = $GLOBALS["endpoint"]->getParam($key);

    if ($value === "yes") {
        return true;
    } else if ($value === "no") {
        return false;
    } else {
        return NULL;
    }
}

\dibi::query(
    "INSERT INTO [game_survey] ([appid], [steamid], [framerate], [optimized], [lag], [graphics_settings], [bg_sound], [good_controls])
    VALUES (%i, %i, %s, %b, %b, %s, %b, %b)
        ON DUPLICATE KEY UPDATE
            [framerate]=VALUES([framerate]),
            [optimized]=VALUES([optimized]),
            [lag]=VALUES([lag]),
            [graphics_settings]=VALUES([graphics_settings]),
            [bg_sound]=VALUES([bg_sound]),
            [good_controls]=VALUES([good_controls])",
    $appid,
    $steamId,
    $endpoint->getParam("framerate"),
    toBoolean("optimized"),
    toBoolean("lag"),
    $endpoint->getParam("graphics_settings"),
    toBoolean("bg_sound"),
    toBoolean("good_controls"),
);

$response->respond();
