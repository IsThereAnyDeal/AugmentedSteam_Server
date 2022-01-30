<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing;

use AugmentedSteam\Server\Controllers\RatesController;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactory;
use AugmentedSteam\Server\Routing\Strategy\ApiStrategy;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class Router
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function route() {
        $responseFactory = new ApiResponseFactory($this->container->get(ResponseFactoryInterface::class));

        $strategy = new ApiStrategy($responseFactory);
        $strategy->setContainer($this->container);

        $router = new \League\Route\Router();
        $router->setStrategy($strategy);

        $router->get("/v1/rates/", [RatesController::class, "getRatesV1"]);

        $request = ServerRequestFactory::fromGlobals();
        $response = $router->dispatch($request);

        (new SapiEmitter)->emit($response);
    }
}
