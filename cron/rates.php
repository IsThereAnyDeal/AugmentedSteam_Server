<?php
require_once __DIR__ . "/../code/autoloader.php";

$db = \Core\Database::connect();

$logger = Log::channel("cron");
$logger->info("Start rates update");

$guzzle = new GuzzleHttp\Client();

$db->begin();
\Price\Converter::getConverter()
    ->updateAll();
$db->commit();

$logger->info("Finish rates update");
