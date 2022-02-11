<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing\Middleware;

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Exceptions\MissingParameterException;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ThrowableMiddleware implements MiddlewareInterface
{
    private CoreConfig $config;
    private ApiResponseFactoryInterface $responseFactory;

    public function __construct(CoreConfig $config, ApiResponseFactoryInterface $responseFactory) {
        $this->config = $config;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if ($this->config->isShowErrors() && !$this->config->isProduction()) {
            return $handler->handle($request);
        } else {
            try {
                return $handler->handle($request);
            } catch (InvalidValueException | MissingParameterException $e) {
                return $this->responseFactory->createErrorResponse($e);
            } catch (Throwable $e) {
                \Sentry\captureException($e);
                return $this->responseFactory->createErrorResponse($e);
            }
        }
    }
}
