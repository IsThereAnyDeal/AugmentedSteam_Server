<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$logger = Log::channel("cron");
$logger->info("Start early access");

$guzzle = new GuzzleHttp\Client();

$stack = new \Database\Stack(100, "early_access", ["appid", "timestamp"]);
$stack->setIgnore(true);

$start = time();

$pages = 1;
$p = 1;
do {
    $logger->info("early access p$p");
	try {
		$request = $guzzle->request("GET", "https://store.steampowered.com/search/results?term=&genre=Early%20Access&page=".$p);
	} catch (\GuzzleHttp\Exception\GuzzleException $e) {
		$logger->info($e->getMessage());
		// TODO second try?
        $p++;
        continue;
	}
	$page = (string)$request->getBody();

	if ($p == 1 && preg_match_all("#&page=(\d+)#", $page, $pm)) {
		$pages = max($pm[1]);
	}

	if (!preg_match_all("#data-ds-appid=\"(\d+)\"#", $page, $m)) {
	    $p++;
		continue;
	}

	foreach($m[1] as $appid) {
		$stack->stack(["appid" => $appid, "timestamp" => time()]);
	}

	$p++;

	sleep(5); // throttle ourselves
} while ($p < $pages);

$stack->saveStack();

\dibi::query("DELETE FROM [early_access] WHERE [timestamp] < %i", $start);

$logger->info("Finish early access");
