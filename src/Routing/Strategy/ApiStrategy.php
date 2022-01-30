<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing\Strategy;

use AugmentedSteam\Server\Routing\Response\ApiResponseFactoryInterface;
use AugmentedSteam\Server\Routing\Middleware\ErrorApiResponseMiddleware;
use AugmentedSteam\Server\Routing\Middleware\ThrowableMiddleware;
use League\Route\ContainerAwareInterface;
use League\Route\ContainerAwareTrait;
use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Route;
use League\Route\Strategy\AbstractStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class ApiStrategy extends AbstractStrategy implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private ApiResponseFactoryInterface $responseFactory;

    public function __construct(ApiResponseFactoryInterface $responseFactory) {
        $this->responseFactory = $responseFactory;
    }

    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception): MiddlewareInterface {
        return new ErrorApiResponseMiddleware($this->responseFactory, $exception);
    }

    public function getNotFoundDecorator(NotFoundException $exception): MiddlewareInterface {
        return new ErrorApiResponseMiddleware($this->responseFactory, $exception);
    }

    public function getThrowableHandler(): MiddlewareInterface {
        return new ThrowableMiddleware($this->responseFactory);
    }

    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface {
        $controller = $route->getCallable($this->getContainer());
        $data = $controller($request, $route->getVars());
        $response = $this->responseFactory->createSuccessResponse($data);
        return $this->decorateResponse($response);
    }
}

