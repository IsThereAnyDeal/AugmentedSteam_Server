<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class AccessLogMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $this->logger->info("{$method} {$path}", $request->getQueryParams());
        return $handler->handle($request);
    }
}

