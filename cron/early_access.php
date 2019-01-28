<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$logger = Log::channel("cron");
$logger->info("Start early access");

$guzzle = new GuzzleHttp\Client();

\dibi::begin();

\dibi::query("DELETE FROM [early_access]");

$stack = new \Database\Stack(500, "early_access", ["appid"]);
$stack->setIgnore(true);

$pages = 1;
$p = 1;
do {
	try {
		$request = $guzzle->request("GET", "https://store.steampowered.com/search/results?term=&genre=Early%20Access&page=".$p);
	} catch (\GuzzleHttp\Exception\GuzzleException $e) {
		$logger->info($e->getMessage());
		// TODO second try?
		continue;
	}
	$page = (string)$request->getBody();

	if ($p == 1 && preg_match_all("#&page=(\d+)#", $page, $pm)) {
		$pages = max($pm[1]);
	}

	if (!preg_match_all("#data-ds-appid=\"(\d+)\"#", $page, $m)) {
		continue;
	}

	foreach($m[1] as $appid) {
		$stack->stack(["appid" => $appid]);
	}

	$p++;

	sleep(5); // throttle ourselves
} while ($p < $pages);

$stack->saveStack();

\dibi::commit();

$logger->info("Finish early access");
