<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["to"]);

$response = new \Api\Response();

$converter = \Price\Converter::getConverter();
$data = $converter->getAllConversionsTo($endpoint->getParamAsArray("to"));

$response
    ->data($data)
    ->respond();
