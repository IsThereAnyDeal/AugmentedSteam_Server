<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing;

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
use AugmentedSteam\Server\Environment\Container;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Routing\Middleware\AccessLogMiddleware;
use AugmentedSteam\Server\Routing\Strategy\ApiStrategy;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Router
{
    private function defineRoutes(\League\Route\Router $router): void {
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
    }

    public function route(Container $container): void {

        $strategy = new ApiStrategy(
            $container->get(ResponseFactoryInterface::class)
        );
        $strategy->setContainer($container);

        $router = new \League\Route\Router();

        $logger = $container->get(LoggerFactoryInterface::class)->access();
        $router->middleware(new AccessLogMiddleware($logger));
        $router->setStrategy($strategy);

        $this->defineRoutes($router);

        $request = ServerRequestFactory::fromGlobals();
        $response = $router->dispatch($request);

        (new SapiEmitter)->emit($response);
    }
}
