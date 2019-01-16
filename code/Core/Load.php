<?php
namespace Core;

use GuzzleHttp\Client;

class Load {

    public static function load(string $url): string {
        $client = new Client();
        $response = $client->request("GET", $url);
        return (string)$response->getBody();
    }

}
