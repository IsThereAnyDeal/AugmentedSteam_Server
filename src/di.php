<?php
declare(strict_types=1);

use AugmentedSteam\Server\Config\BrightDataConfig;
use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\ExfglsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Config\LoggingConfig;
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
use AugmentedSteam\Server\Cron\CronJobFactory;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactory;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Logging\MonologLoggerFactory;
use AugmentedSteam\Server\Model\Cache\Cache;
use AugmentedSteam\Server\Model\EarlyAccess\EarlyAccessManager;
use AugmentedSteam\Server\Model\HowLongToBeat\HLTBManager;
use AugmentedSteam\Server\Model\Prices\PricesManager;
use AugmentedSteam\Server\Model\Reviews\ReviewsManager;
use AugmentedSteam\Server\Model\Market\MarketManager;
use AugmentedSteam\Server\Model\SteamPeek\SteamPeekManager;
use AugmentedSteam\Server\Model\StorePage\ExfglsManager;
use AugmentedSteam\Server\Model\StorePage\SteamChartsManager;
use AugmentedSteam\Server\Model\StorePage\SteamSpyManager;
use AugmentedSteam\Server\Model\StorePage\WSGFManager;
use AugmentedSteam\Server\Model\Survey\SurveyManager;
use AugmentedSteam\Server\Model\User\UserManager;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactory;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactoryInterface;
use AugmentedSteam\Server\Model\SteamRep\SteamRepManager;
use GuzzleHttp\Client;
use IsThereAnyDeal\Database\DbConfig;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\DbFactory;
use Laminas\Diactoros\ResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

require_once __DIR__."/../vendor/autoload.php";

return [
    "guzzle" => DI\create(Client::class),

    SimpleLoader::class => DI\create()
        ->constructor(
            DI\get("guzzle"),
            function(ContainerInterface $c) {
                return $c->get(LoggerFactoryInterface::class)->createLogger("guzzle");
            }
        ),

    DbDriver::class => function(ContainerInterface $c) {
        return DbFactory::getDatabase($c->get(DbConfig::class));
    },

    Cache::class => DI\create()->constructor(DI\get(DbDriver::class)),

    // factories

    ResponseFactoryInterface::class => DI\create(ResponseFactory::class),

    ApiResponseFactoryInterface::class => DI\create(ApiResponseFactory::class)
        ->constructor(DI\get(ResponseFactoryInterface::class)),

    LoggerFactoryInterface::class => DI\create(MonologLoggerFactory::class)
        ->constructor(DI\get(LoggingConfig::class)),

    CronJobFactory::class => DI\create()
        ->constructor(
            DI\get(LoggerFactoryInterface::class),
            DI\get(ProxyFactoryInterface::class),
            DI\get(DbDriver::class),
            DI\get("guzzle")
        ),

    ProxyFactoryInterface::class => DI\create(ProxyFactory::class)
        ->constructor(DI\get(BrightDataConfig::class)),

    // managers

    MarketManager::class => DI\create()
        ->constructor(DI\get(DbDriver::class)),
    UserManager::class => DI\create()
        ->constructor(DI\get(DbDriver::class)),
    SteamRepManager::class => DI\create()
        ->constructor(
            DI\get(DbDriver::class),
            DI\get(SimpleLoader::class),
            DI\get(EndpointsConfig::class),
            DI\get(LoggerFactoryInterface::class)
        ),
    SteamChartsManager::class => DI\create()
        ->constructor(
            DI\get(DbDriver::class),
            DI\get(SimpleLoader::class),
            DI\get(LoggerFactoryInterface::class),
            DI\get(EndpointsConfig::class)
        ),
    SteamSpyManager::class => DI\create()
        ->constructor(
            DI\get(DbDriver::class),
            DI\get(SimpleLoader::class),
            DI\get(LoggerFactoryInterface::class),
            DI\get(ProxyFactoryInterface::class),
            DI\get(EndpointsConfig::class)
        ),
    WSGFManager::class => DI\create()
        ->constructor(
            DI\get(Cache::class),
            DI\get(SimpleLoader::class),
            DI\get(LoggerFactoryInterface::class),
            DI\get(EndpointsConfig::class)
        ),
    ExfglsManager::class => DI\create()
        ->constructor(
            DI\get(DbDriver::class),
            DI\get(LoggerFactoryInterface::class),
            DI\get(ExfglsConfig::class)
        ),
    HLTBManager::class => DI\create()
        ->constructor(
            DI\get(DbDriver::class),
            DI\get(SimpleLoader::class),
            DI\get(LoggerFactoryInterface::class),
            DI\get(ProxyFactoryInterface::class)
        ),
    ReviewsManager::class => DI\create()
        ->constructor(
            DI\get(Cache::class),
            DI\get(SimpleLoader::class),
            DI\get(LoggerFactoryInterface::class),
            DI\get(EndpointsConfig::class),
            DI\get(KeysConfig::class)
        ),
    SteamPeekManager::class => DI\create()
        ->constructor(
            DI\get(DbDriver::class),
            DI\get(SimpleLoader::class),
            DI\get(EndpointsConfig::class),
            DI\get(KeysConfig::class),
            DI\get(LoggerFactoryInterface::class)
        ),
    PricesManager::class => DI\create()
        ->constructor(
            DI\get(SimpleLoader::class),
            DI\get(EndpointsConfig::class),
            DI\get(KeysConfig::class)
        ),
    EarlyAccessManager::class => DI\create()
        ->constructor(
            DI\get(DbDriver::class)
        ),
    SurveyManager::class => DI\create()
        ->constructor(
            DI\get(DbDriver::class),
            DI\get(LoggerFactoryInterface::class)
        ),

    // controllers

    RatesController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class)
        ),
    GameController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class)
        ),
    MarketController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class)
        ),
    ProfileManagementController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class),
            DI\get(CoreConfig::class),
            DI\get(MarketManager::class),
            DI\get(UserManager::class),
        ),

    ProfileController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class),
            DI\get(UserManager::class),
            DI\get(SteamRepManager::class)
        ),

    StorePageController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class),
            DI\get(SteamChartsManager::class),
            DI\get(SteamSpyManager::class),
            DI\get(WSGFManager::class),
            DI\get(ExfglsManager::class),
            DI\get(HLTBManager::class),
            DI\get(ReviewsManager::class),
            DI\get(SurveyManager::class)
        ),

    SimilarController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class),
            DI\get(SteamPeekManager::class)
        ),

    PricesController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class),
            DI\get(PricesManager::class)
        ),

    EarlyAccessController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class),
            DI\get(EarlyAccessManager::class)
        ),

    SurveyController::class => DI\create()
        ->constructor(
            DI\get(ResponseFactoryInterface::class),
            DI\get(DbDriver::class),
            DI\get(CoreConfig::class),
            DI\get(SurveyManager::class),
        )
];
