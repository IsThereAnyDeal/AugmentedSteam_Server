<?php
namespace SteamPeek;

use GuzzleHttp\Client;
use Monolog\Logger;

class SteamPeek {
    private const CACHE_TTL = 10*24*60*60;

    private $logger;
    private $guzzle;

    public function __construct(Client $guzzle, Logger $logger) {
        $this->guzzle = $guzzle;
        $this->logger = $logger;
    }

    public function getSimilar(int $appid, int $preferedCount=5, bool $randomOrder=false): array {
        $result = $this->getCached($appid);
        if (is_null($result)) {
            $result = $this->load($appid);
            if (empty($result)) { return []; }
        }

        if ($randomOrder) {
            shuffle($result);
        }

        return array_slice($result, 0, $preferedCount);
    }

    private function getCached(int $appid): ?array {
        $select = \dibi::query(
            "SELECT [data]
             FROM [similar]
             WHERE [appid]=%i AND [timestamp] > %i",
            $appid, time()-self::CACHE_TTL)->fetchSingle();

        if ($select === false) {
            return null;
        }

        $json = json_decode($select, true);
        if ($json === false) {
            return null;
        }

        return $json;
    }

    private function load(int $appid) {
        $apikey = \Config::SteamPeekKey;
        try {
            $response = $this->guzzle->get(\Config::SteampeekEndpoint."?apiver=2&appid={$appid}&apikey={$apikey}");
        } catch(\Exception $e) {
            return [];
        }

        $body = (string)$response->getBody();
        $data = json_decode($body, true);

        if (!$data) { return []; }

        $this->logger->info("$appid loaded from api");

        if (empty($data['response']['success']) || $data['response']['success'] != 1) {
            return [];
        }

        $result = $data['response']['results'];

        \dibi::query(
            "INSERT INTO [similar] ([appid], [data], [timestamp])
             VALUES (%i, %s, %i) ON DUPLICATE KEY UPDATE [data]=VALUES([data]), [timestamp]=VALUES([timestamp])",
            $appid, json_encode($result), time());

        return $result;
    }

}
