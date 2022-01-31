<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing;

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Controllers\GameController;
use AugmentedSteam\Server\Controllers\RatesController;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactoryInterface;
use AugmentedSteam\Server\Routing\Strategy\ApiStrategy;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Container\ContainerInterface;

class Router
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function route() {
        $responseFactory = $this->container->get(ApiResponseFactoryInterface::class);

        $strategy = new ApiStrategy(
            $this->container->get(CoreConfig::class),
            $responseFactory
        );
        $strategy->setContainer($this->container);

        $router = new \League\Route\Router();
        $router->setStrategy($strategy);

        $router->get("/v1/rates/", [RatesController::class, "getRatesV1"]);

        /** @deprecated */ $router->get("/v1/dlcinfo/", [GameController::class, "getDlcInfoV1"]);
        $router->get("/v2/dlcinfo/", [GameController::class, "getDlcInfoV2"]);


        $request = ServerRequestFactory::fromGlobals();
        $response = $router->dispatch($request);

        (new SapiEmitter)->emit($response);
    }
}
