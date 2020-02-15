<?php

require_once __DIR__ . "/../code/autoloader.php";

$db = \Core\Database::connect();

$logger = Log::channel("cron");
$logger->info("Start game streaming update");

$logger->info(" -> Fetching NVIDIA GeForce Now");

$apps = [];

// GeForce Now
{
    $url = Config::GeforceNowEndpoint;
    $result = \Core\Load::load($url);
    $json = json_decode($result, true);

    if (!is_array($json)) {
        throw new \Exception("No data");
    }

    foreach($json as $item){
        if(! @isset($item["steamUrl"]) || empty($item["steamUrl"])) continue;

        $appid = intval(explode("/", $item["steamUrl"])[4]);
        if($appid === 0) continue;

        if(! @isset($item[$appid])) $item[$appid] = [];
        $apps[$appid]["geforce_now"] = true;
    }
}

// Intentionally kept this way in the event that we start tracking multiple streaming services
$stack = new \Database\Stack(10, "streaming", [
    "appid", "geforce_now"
]);
$stack->onDuplicateKeyUpdate("geforce_now");

foreach($apps as $appid => $_) {
    $stack->stack([
        "appid" => $appid,
        "geforce_now" => $apps[$appid]["geforce_now"]
    ]);
}

$stack->saveStack();

\dibi::query("DELETE FROM [streaming] WHERE [timestamp] < %s", date("Y-m-d H:i:s", time() - 86400));

$logger->info("Finish streaming update");
