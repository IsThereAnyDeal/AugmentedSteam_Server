<?php
namespace Core;

use GuzzleHttp\Client;

class Load {

    public static function load(string $url, array $curlOptions = []): string {
        $client = new Client();
        $response = $client->request("GET", $url, ["curl" => $curlOptions]);
        return (string)$response->getBody();
    }

}
