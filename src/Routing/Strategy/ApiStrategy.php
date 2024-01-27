<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing\Strategy;

use League\Route\ContainerAwareInterface;
use League\Route\Http;
use League\Route\Route;
use League\Route\Strategy\JsonStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ApiStrategy extends JsonStrategy implements ContainerAwareInterface
{
    public function __construct(ResponseFactoryInterface $responseFactory, int $jsonFlags=0) {
        parent::__construct($responseFactory, $jsonFlags);

        $this->addResponseDecorator(static function (ResponseInterface $response): ResponseInterface {
            if (false === $response->hasHeader("access-control-allow-origin")) {
                $response = $response->withHeader("access-control-allow-origin", "*");
            }
            return $response;
        });
    }

    public function getThrowableHandler(): MiddlewareInterface {
        return new class ($this->responseFactory->createResponse()) implements MiddlewareInterface
        {
            public function __construct(
                private ResponseInterface $response
            ) {}

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                try {
                    return $handler->handle($request);
                } catch (Throwable $exception) {
                    $response = $this->response;

                    if ($exception instanceof Http\Exception) {
                        return $exception->buildJsonResponse($response);
                    }

                    $response->getBody()->write(json_encode([
                        "status_code"   => 500,
                        "reason_phrase" => "Internal Server Error"
                    ], flags: JSON_THROW_ON_ERROR));

                    return $response
                        ->withAddedHeader("content-type", "application/json")
                        ->withStatus(500, "Internal Server Error");
                }
            }
        };
    }

    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface {
        $controller = $route->getCallable($this->getContainer());
        $response = $controller($request, $route->getVars());

        if ($this->isJsonSerializable($response)) {
            $body = json_encode([
                "result" => "success",
                "data" => $response
            ], $this->jsonFlags | JSON_THROW_ON_ERROR);
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write($body);
        }

        return $this->decorateResponse($response);
    }
}
