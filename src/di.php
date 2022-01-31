<?php

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Controllers\GameController;
use AugmentedSteam\Server\Controllers\RatesController;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactory;
use AugmentedSteam\Server\Routing\Response\ApiResponseFactoryInterface;
use IsThereAnyDeal\Database\DbConfig;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\DbFactory;
use Laminas\Diactoros\ResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

require_once __DIR__."/../vendor/autoload.php";

return [
    ResponseFactoryInterface::class => DI\create(ResponseFactory::class),

    DbDriver::class => function(ContainerInterface $c) {
        return DbFactory::getDatabase($c->get(DbConfig::class));
    },

    ApiResponseFactoryInterface::class => DI\create(ApiResponseFactory::class)
        ->constructor(
            DI\get(ResponseFactoryInterface::class)
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
        )
];
