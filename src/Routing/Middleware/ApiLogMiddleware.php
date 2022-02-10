<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing\Middleware;

use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ApiLogMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerFactoryInterface $loggerFactory) {
        $this->logger = $loggerFactory->createLogger("api");
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $this->logger->info("$method $path", $request->getQueryParams());
        return $handler->handle($request);
    }
}

