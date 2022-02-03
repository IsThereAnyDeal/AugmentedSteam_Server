<?php

namespace AugmentedSteam\Server\Loader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class SimpleLoader
{
    private Client $guzzle;

    public function __construct(Client $guzzle) {
        $this->guzzle = $guzzle;
    }

    public function get(string $url, array $curlOptions = []): ?ResponseInterface {
        try {
            return $this->guzzle->get($url, [
                "headers" => [
                    "User-Agent" => "AugmentedSteam/1.0 (+bots@isthereanydeal.com)",
                ],
                "curl" => $curlOptions
            ]);
        } catch (GuzzleException $e) {
            // no special handling
        }
        return null;
    }
}
