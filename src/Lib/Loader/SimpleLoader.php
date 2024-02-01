<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Loader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

readonly class SimpleLoader
{
    public function __construct(
        private Client $guzzle
    ) {}

    public function get(string $url, array $curlOptions = []): ?ResponseInterface {
        try {
            return $this->guzzle->get($url, [
                "headers" => [
                    "User-Agent" => "AugmentedSteam/1.0 (+bots@isthereanydeal.com)",
                ],
                "curl" => $curlOptions
            ]);
        } catch (GuzzleException $e) {
            \Sentry\captureException($e);
        }
        return null;
    }
}
