<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Loader;

use AugmentedSteam\Server\Lib\Loader\Proxy\ProxyInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Loader
{
    private const int ConnectTimeout = 5;
    private const int Timeout = 15;

    private readonly LoggerInterface $logger;
    private readonly Client $guzzle;

    private int $concurrency = 5;
    private ?ProxyInterface $proxy = null;

    public function __construct(LoggerInterface $logger, Client $guzzle) {
        $this->logger = $logger;
        $this->guzzle = $guzzle;
    }

    public function setConcurrency(int $concurrency): self {
        $this->concurrency = $concurrency;
        return $this;
    }

    public function setProxy(?ProxyInterface $proxy): self {
        $this->proxy = $proxy;
        return $this;
    }

    public function createRequest(Item $item, callable $responseHandler, callable $errorHandler): callable {

        return function() use ($item, $responseHandler, $errorHandler) {

            $curlOptions = array_replace(
                is_null($this->proxy) ? [] : $this->proxy->getCurlOptions(),
                $item->getCurlOptions()
            );
            $curlOptions[CURLOPT_FOLLOWLOCATION] = false; // Force CURL follow location off, and let Guzzle handle it

            $settings = [
                "http_errors" => false, // do not throw on 400 and 500 level errors
                "connect_timeout" => self::ConnectTimeout,
                "timeout" => self::Timeout,
                "allow_redirects" => [
                    "track_redirects" => true
                ],
                "curl" => $curlOptions
            ];

            foreach($item->getHeaders() as $header => $value) {
                $settings['headers'][$header] = $value;
            }

            if (!empty($item->getFormData())) {
                $settings['form_params'] = $item->getFormData();
            }

            if (!empty($item->getBody())) {
                $settings['body'] = $item->getBody();
            }

            return $this->guzzle
                ->requestAsync($item->getMethod(), $item->getUrl(), $settings)
                ->then(
                    function(ResponseInterface $response) use($item, $responseHandler) {
                        try {
                            $redirects = $response->getHeader("X-Guzzle-Redirect-History");
                            $uri = (empty($redirects) ? $item->getUrl() : end($redirects));

                            $responseHandler($item, $response, $uri);
                        } catch (Throwable $e) {
                            $this->logger->error($e->getMessage());
                        }
                    },
                    function(GuzzleException $e) use($item, $errorHandler) {
                        try {
                            $errorHandler($item, $e);
                        } catch (Throwable $e) {
                            $this->logger->error($e->getMessage());
                        }
                    }
                );
        };
    }

    public function run($requests) {
        $pool = new Pool($this->guzzle, $requests, [
            "concurrency" => $this->concurrency,
        ]);
        $pool
            ->promise()
            ->wait();
    }
}
