<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing;

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Controllers\GameController;
use AugmentedSteam\Server\Controllers\MarketController;
use AugmentedSteam\Server\Controllers\ProfileController;
use AugmentedSteam\Server\Controllers\ProfileManagementController;
use AugmentedSteam\Server\Controllers\RatesController;
use AugmentedSteam\Server\Controllers\SimilarController;
use AugmentedSteam\Server\Controllers\StorePageController;
use AugmentedSteam\Server\OpenId\OpenId;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactoryInterface;
use AugmentedSteam\Server\Routing\Strategy\ApiStrategy;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\RouteGroup;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

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

        /** @deprecated */ $router->get("/v1/market/averagecardprices/", [MarketController::class, "getAverageCardPricesV1"]);
        $router->get("/v2/market/averagecardprices/", [MarketController::class, "getAverageCardPricesV2"]);
        $router->get("/v1/market/cardprices/", [MarketController::class, "getCardPricesV1"]);

        $router->get("/v1/profile/background/background/", [ProfileManagementController::class, "getBackgroundV1"]);
        $router->get("/v1/profile/background/games/", [ProfileManagementController::class, "getGamesV1"]);

        $router->group("/v1/profile/background/edit", function(RouteGroup $group) {
            $group->get("/delete/", [ProfileManagementController::class, "deleteBackgroundV1"]);
            $group->get("/save/", [ProfileManagementController::class, "saveBackgroundV1"]);
        })->setStrategy(new ApplicationStrategy());

        $router->group("/v1/profile/style/edit", function(RouteGroup $group) {
            $group->get("/delete/", [ProfileManagementController::class, "deleteStyleV1"]);
            $group->get("/save/", [ProfileManagementController::class, "saveStyleV1"]);
        })->setStrategy(new ApplicationStrategy());

        $router->get("/v1/profile/profile/", [ProfileController::class, "getProfileV1"]);

        $router->get("/v1/storepagedata/", [StorePageController::class, "getStorePageDataV1"]);

        $router->get("/v1/similar/", [SimilarController::class, "getSimilarV1"]);

        $request = ServerRequestFactory::fromGlobals();
        $response = $router->dispatch($request);

        (new SapiEmitter)->emit($response);
    }
}
