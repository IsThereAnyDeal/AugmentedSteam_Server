<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$logger = Log::channel("cron");
$logger->info("Market update start");

$guzzle = new \GuzzleHttp\Client();
$request = $guzzle->request("GET", Config::SteamToolsApiEndpoint);

$data = $request->getBody();
$json = json_decode((string)$data, true);

if (empty($json)) {
	$logger->notice("No market data");
	die();
}

$stack = new \Database\Stack(500, "market_data", [
	"title", "game", "name", "img", "appid", "url", "price", "quantity", "type", "modified", "rarity"
]);

\dibi::begin();

\dibi::query("DELETE FROM [market_data]");

foreach($json as $a) {
    $stack->stack([
	    "title" => preg_replace("#\s+(Rare|Uncommon|\(Foil\))\s*$#", "", $a['game']),
		"game" => $a['game'],
		"name" => $a['name'],
		"img" => $a['img'],
		"appid" => $a['appid'],
		"url" => $a['url'],
		"price" => $a['price'],
		"quantity" => $a['quantity'],
		"type" => $a['type'],
		"modified" => $a['modified'],
		"rarity" => $a['rarity'],
	]);
}

$stack->saveStack();

\dibi::commit();

$logger->info("Market update end");
