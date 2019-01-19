<?php
namespace SteamRep;

use Config;

class SteamRep {

    private const CACHE_LIMIT = 86400;

    private $steamId;

    public function __construct(int $steamId) {
        $this->steamId = $steamId;
    }

    public function getRep(): array {
        $select = \dibi::query("SELECT [access_time], [rep], FROM [steamrep] WHERE [steam64]=%i", $this->steamId)->fetch();

        if (!empty($select) && $select['access_time']->getTimestamp() >= time()-self::CACHE_LIMIT) {
            return explode(",", $select['rep']);
        }

        \dibi::query("DELETE FROM [steamrep] WHERE [steam64]=%i", $this->steamId);
        return $this->getNewRep();
    }

    private function getNewRep(): array {
        $url = Config::SteamRepEndpoint.$this->steamId;

        try {
            $guzzle = new \GuzzleHttp\Client();
            $response = $guzzle->request("GET", $url);

            $body = (string)$response->getBody();
            $xml = simplexml_load_string($body);

            if ($xml['status'] == "exists") {
                $status = (string)$xml->reputation->full;

                \dibi::query("INSERT INTO [steamrep] %v", [
                    "steam64" => $this->steamId,
                    "rep" => $status
                ]);
                return explode(",", $status);
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // no handling on guzzle exception
        }

        return [];
    }

}
