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
use AugmentedSteam\Server\Controllers\TwitchController;
use AugmentedSteam\Server\Environment\Container;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Routing\Middleware\AccessLogMiddleware;
use AugmentedSteam\Server\Routing\Strategy\ApiStrategy;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    private function defineRoutes(\League\Route\Router $router): void {

/*
        $router->get("/v1/dlcinfo/", [GameController::class, "getDlcInfoV1"]);
        $router->get("/v1/market/averagecardprices/", [MarketController::class, "getAverageCardPricesV1"]);
        $router->get("/v1/market/cardprices/", [MarketController::class, "getCardsV2"]);
        $router->get("/v1/profile/profile/", [ProfileController::class, "getProfileLegacyV1"]);
        $router->get("/v1/profile/background/background/", [ProfileManagementController::class, "getBackgroundsV2"]);
        $router->get("/v1/profile/background/edit/delete/", [ProfileManagementController::class, "deleteBackgroundV2"]);
        $router->get("/v1/profile/background/edit/save/", [ProfileManagementController::class, "saveBackgroundV2"]);
        $router->get("/v1/profile/style/edit/delete/", [ProfileManagementController::class, "deleteStyleV2"]);
        $router->get("/v1/profile/style/edit/save/", [ProfileManagementController::class, "saveStyleV2"]);
        $router->get("/v1/storepagedata/", [StorePageController::class, "getStorePageDataV1"]);
        $router->get("/v1/similar/", [SimilarController::class, "getSimilarV1"]);
        $router->get("/v1/prices/", [PricesController::class, "getPricesV1"]);
        $router->get("/v1/twitch/stream/", [TwitchController::class, "getStreamV1"]);
*/


        // old, redirects
        $redirects = [
            "/v1/rates/" => "/rates/v1",
            "/v2/dlcinfo/" => "/dlcinfo/v2",
            "/v2/market/cards/" => "/market/cards/v2",
            "/v2/market/cards/average-prices/" => "/market/cards/average-prices/v2",
            "/v2/profile/background/list/" => "/profile/background/list/v2",
            "/v1/profile/background/games/" => "/profile/background/games/v1",
            "/v2/profile/background/delete/" => "/profile/background/delete/v2",
            "/v2/profile/background/save/" => "/profile/background/save/v2",
            "/v2/profile/style/delete/" => "/profile/style/delete/v2",
            "/v2/profile/style/save/" => "/profile/style/save/v2",
        ];
        foreach($redirects as $oldUrl => $newUrl) {
            $router->get($oldUrl, fn() => new RedirectResponse($newUrl, 301));
        }
        $router->get("/v2/profile/{steamId:\d+}/",
            fn(ServerRequestInterface $request, array $params) => new RedirectResponse("/profile/{$params['steamId']}/v2", 301));
        $router->get("/v2/similar/{appid:\d+}/",
            fn(ServerRequestInterface $request, array $params) => new RedirectResponse("/similar/{$params['appid']}/v2", 301));

        // new urls
        $router->get("/rates/v1", [RatesController::class, "getRates_v1"]);

        $router->get("/dlcinfo/v2", [GameController::class, "getDlcInfo_v2"]);

        $router->group("/market", function(RouteGroup $g) {
            $g->get("/cards/v2", [MarketController::class, "getCards_v2"]);
            $g->get("/cards/average-prices/v2", [MarketController::class, "getAverageCardPrices_v2"]);
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

        $router->get("/app/{appid:\d+}/v2", [StorePageController::class, "getAppInfo_v2"]);

        $router->get("/similar/{appid:\d+}/v2", [SimilarController::class, "getSimilar_v2"]);
        $router->get("/prices/v2", [PricesController::class, "getPrices_v2"]);
        $router->get("/earlyaccess/v1", [EarlyAccessController::class, "getAppids_v1"]);

        $router->get("/v2/twitch/{channel}/stream/", [TwitchController::class, "getStream_v2"]);
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
