<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing;

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Controllers\EarlyAccessController;
use AugmentedSteam\Server\Controllers\DLCController;
use AugmentedSteam\Server\Controllers\MarketController;
use AugmentedSteam\Server\Controllers\PricesController;
use AugmentedSteam\Server\Controllers\ProfileController;
use AugmentedSteam\Server\Controllers\ProfileManagementController;
use AugmentedSteam\Server\Controllers\RatesController;
use AugmentedSteam\Server\Controllers\SimilarController;
use AugmentedSteam\Server\Controllers\AppController;
use AugmentedSteam\Server\Controllers\TwitchController;
use AugmentedSteam\Server\Environment\Container;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Routing\Middleware\AccessLogMiddleware;
use AugmentedSteam\Server\Routing\Strategy\ApiStrategy;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseFactoryInterface;

class Router
{
    private function defineRoutes(\League\Route\Router $router): void {

        $router->get("/rates/v1", [RatesController::class, "getRates_v1"]);
        $router->get("/early-access/v1", [EarlyAccessController::class, "getAppids_v1"]);
        $router->post("/prices/v2", [PricesController::class, "prices_v2"]);

        $router->get("/app/{appid:\d+}/v2", [AppController::class, "appInfo_v2"]);
        $router->get("/dlc/{appid:\d+}/v2", [DLCController::class, "dlcInfo_v2"]);
        $router->get("/similar/{appid:\d+}/v2", [SimilarController::class, "getSimilar_v2"]);

        $router->group("/market", function(RouteGroup $g) {
            $g->get("/cards/v2", [MarketController::class, "cards_v2"]);
            $g->get("/cards/average-prices/v2", [MarketController::class, "averageCardPrices_v2"]);
        });

        $router->group("/profile", function(RouteGroup $g) {
            $g->get("/{steamId:\d+}/v2", [ProfileController::class, "getProfile_v2"]);
            $g->get("/background/list/v2", [ProfileManagementController::class, "getBackgrounds_v2"]);
            $g->get("/background/games/v1", [ProfileManagementController::class, "getGames_v1"]);
            $g->get("/background/delete/v2", [ProfileManagementController::class, "deleteBackground_v2"]);
            $g->get("/background/save/v2", [ProfileManagementController::class, "saveBackground_v2"]);
            $g->get("/style/delete/v2", [ProfileManagementController::class, "deleteStyle_v2"]);
            $g->get("/style/save/v2", [ProfileManagementController::class, "saveStyle_v2"]);
        });

        $router->get("/twitch/{channel}/stream/v2", [TwitchController::class, "getStream_v2"]);
    }

    public function route(Container $container): void {

        $strategy = new ApiStrategy(
            $container->get(CoreConfig::class),
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
