<?php
declare(strict_types=1);

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\LoggingConfig;
use AugmentedSteam\Server\Controllers\GameController;
use AugmentedSteam\Server\Controllers\MarketController;
use AugmentedSteam\Server\Controllers\ProfileController;
use AugmentedSteam\Server\Controllers\ProfileManagementController;
use AugmentedSteam\Server\Controllers\RatesController;
use AugmentedSteam\Server\Cron\CronJobFactory;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Logging\MonologLoggerFactory;
use AugmentedSteam\Server\Model\Market\MarketManager;
use AugmentedSteam\Server\Model\User\UserManager;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactory;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactoryInterface;
use AugmentedSteam\Server\SteamRep\SteamRepManager;
use IsThereAnyDeal\Database\DbConfig;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\DbFactory;
use Laminas\Diactoros\ResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

require_once __DIR__."/../vendor/autoload.php";

return [
    "guzzle" => DI\create(\GuzzleHttp\Client::class),

    DbDriver::class => function(ContainerInterface $c) {
        return DbFactory::getDatabase($c->get(DbConfig::class));
    },

    // factories

    ResponseFactoryInterface::class => DI\create(ResponseFactory::class),

    ApiResponseFactoryInterface::class => DI\create(ApiResponseFactory::class)
        ->constructor(DI\get(ResponseFactoryInterface::class)),

    LoggerFactoryInterface::class => DI\create(MonologLoggerFactory::class)
        ->constructor(DI\get(LoggingConfig::class)),

    CronJobFactory::class => DI\create()
        ->constructor(
            DI\get(LoggerFactoryInterface::class),
            DI\get(DbDriver::class),
            DI\get("guzzle")
        ),

    // managers

    MarketManager::class => DI\create()->constructor(DI\get(DbDriver::class)),
    UserManager::class => DI\create()->constructor(DI\get(DbDriver::class)),
    SteamRepManager::class => DI\create()
        ->constructor(
            DI\get(DbDriver::class),
            DI\get("guzzle"),
            DI\get(EndpointsConfig::class),
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
        )
];
