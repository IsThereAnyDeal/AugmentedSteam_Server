<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing\Middleware;

use AugmentedSteam\Server\Routing\Response\ApiResponseFactoryInterface ;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorApiResponseMiddleware implements MiddlewareInterface
{
    private ApiResponseFactoryInterface  $responseFactory;
    private Throwable $exception;

    public function __construct(ApiResponseFactoryInterface  $responseFactory, Throwable $exception) {
        $this->responseFactory = $responseFactory;
        $this->exception = $exception;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $this->responseFactory->createErrorResponse($this->exception);
    }
}
