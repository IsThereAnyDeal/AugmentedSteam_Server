<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Loader;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use SplQueue;
use Throwable;

abstract class Crawler
{
    private SplQueue $requestQueue;

    public function __construct(
        private readonly Loader $loader,
        protected readonly LoggerInterface $logger
    ) {
        $this->requestQueue = new SplQueue();
    }

    protected function enqueueRequest(Item $item): void {
        $request = $this->loader->createRequest(
            $item,
            fn(Item $item, ResponseInterface $response, string $effectiveUri) => $this->successHandler($item, $response, $effectiveUri),
            fn(Item $item, Throwable $e) => $this->errorHandler($item, $e)
        );
        $this->requestQueue->enqueue($request);
    }

    protected function mayProcess(Item $request, ResponseInterface $response, int $maxAttempts): bool {
        $status = $response->getStatusCode();

        if ($status !== 200) {
            if ($status === 429) {
                $this->logger->info("Throttling");
                sleep(60);
            }
            if ($request->getAttempt() <= $maxAttempts) {
                // replay request
                $request->incrementAttempt();
                $this->enqueueRequest($request);
                $this->logger->info("Retrying", ["url" => $request->getUrl()]);
            } else {
                $this->logger->error($request->getUrl());
            }
            return false;
        }

        return true;
    }

    protected abstract function successHandler(Item $request, ResponseInterface $response, string $effectiveUri): void;

    protected function errorHandler(Item $item, Throwable $e): void {
        $this->logger->error($e->getMessage().": ".$item->getUrl(), $item->getData());
    }

    protected function requestGenerator(): \Generator {
        while(true) {
            if ($this->requestQueue->isEmpty()) { break; }
            yield $this->requestQueue->dequeue();
        }
    }

    protected function runLoader(): void {
        while (true) {
            if ($this->requestQueue->isEmpty()) { break; }
            $this->loader->run($this->requestGenerator());
        }
    }
}
