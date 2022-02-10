<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing;

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Controllers\EarlyAccessController;
use AugmentedSteam\Server\Controllers\GameController;
use AugmentedSteam\Server\Controllers\MarketController;
use AugmentedSteam\Server\Controllers\PricesController;
use AugmentedSteam\Server\Controllers\ProfileController;
use AugmentedSteam\Server\Controllers\ProfileManagementController;
use AugmentedSteam\Server\Controllers\RatesController;
use AugmentedSteam\Server\Controllers\SimilarController;
use AugmentedSteam\Server\Controllers\StorePageController;
use AugmentedSteam\Server\Controllers\SurveyController;
use AugmentedSteam\Server\Controllers\TwitchController;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Logging\MonologLoggerFactory;
use AugmentedSteam\Server\Routing\Middleware\ApiLogMiddleware;
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
        $router->middleware(new ApiLogMiddleware($this->container->get(LoggerFactoryInterface::class)));
        $router->setStrategy($strategy);

        /** @deprecated */ $router->get("/v1/dlcinfo/", [GameController::class, "getDlcInfoV1"]);
        /** @deprecated */ $router->get("/v1/market/averagecardprices/", [MarketController::class, "getAverageCardPricesV1"]);
        /** @deprecated */ $router->get("/v1/market/cardprices/", [MarketController::class, "getCardsV2"]);
        /** @deprecated */ $router->get("/v1/profile/profile/", [ProfileController::class, "getProfileLegacyV1"]);
        /** @deprecated */ $router->get("/v1/profile/background/background/", [ProfileManagementController::class, "getBackgroundsV2"]);
        /** @deprecated */ $router->get("/v1/profile/background/edit/delete/", [ProfileManagementController::class, "deleteBackgroundV2"]);
        /** @deprecated */ $router->get("/v1/profile/background/edit/save/", [ProfileManagementController::class, "saveBackgroundV2"]);
        /** @deprecated */ $router->get("/v1/profile/style/edit/delete/", [ProfileManagementController::class, "deleteStyleV2"]);
        /** @deprecated */ $router->get("/v1/profile/style/edit/save/", [ProfileManagementController::class, "saveStyleV2"]);
        /** @deprecated */ $router->get("/v1/storepagedata/", [StorePageController::class, "getStorePageDataV1"]);
        /** @deprecated */ $router->get("/v1/similar/", [SimilarController::class, "getSimilarV1"]);
        /** @deprecated */ $router->get("/v1/prices/", [PricesController::class, "getPricesV1"]);
        /** @deprecated */ $router->get("/v1/twitch/stream/", [TwitchController::class, "getStreamV1"]);

        $router->get("/v1/rates/", [RatesController::class, "getRatesV1"]);
        $router->get("/v2/dlcinfo/", [GameController::class, "getDlcInfoV2"]);

        $router->get("/v2/market/cards/", [MarketController::class, "getCardsV2"]);
        $router->get("/v2/market/cards/average-prices/", [MarketController::class, "getAverageCardPricesV2"]);

        $router->get("/v2/profile/{steamId:\d+}/", [ProfileController::class, "getProfileV2"]);
        $router->get("/v2/profile/background/list/", [ProfileManagementController::class, "getBackgroundsV2"]);
        $router->get("/v1/profile/background/games/", [ProfileManagementController::class, "getGamesV1"]);
        $router->get("/v2/profile/background/delete/", [ProfileManagementController::class, "deleteBackgroundV2"]);
        $router->get("/v2/profile/background/save/", [ProfileManagementController::class, "saveBackgroundV2"]);
        $router->get("/v2/profile/style/delete/", [ProfileManagementController::class, "deleteStyleV2"]);
        $router->get("/v2/profile/style/save/", [ProfileManagementController::class, "saveStyleV2"]);

        $router->get("/v2/app/{appid:\d+}/", [StorePageController::class, "getGameInfoV2"]);
        $router->get("/v2/similar/{appid:\d+}/", [SimilarController::class, "getSimilarV2"]);
        $router->get("/v2/prices/", [PricesController::class, "getPricesV2"]);
        $router->get("/v1/earlyaccess/", [EarlyAccessController::class, "getAppidsV1"]);

        $router->get("/v1/survey/{appid:\d+}/submit/", [SurveyController::class, "getSubmitV1"]);

        $router->get("/v2/twitch/{channel}/stream/", [TwitchController::class, "getStreamV2"]);

        $request = ServerRequestFactory::fromGlobals();

        // backwards compatibility to allow versions prefix with 0
        $path = $request->getUri()->getPath();
        if (preg_match("#/v0+\d/#", $path)) {
            $uri = $request->getUri()
                ->withPath(preg_replace("#/v0+(\d)/#", "/v$1/", $path));
            $request = $request->withUri($uri);
        }

        $response = $router->dispatch($request);

        (new SapiEmitter)->emit($response);
    }
}
