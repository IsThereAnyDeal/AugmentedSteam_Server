<?php
declare(strict_types=1);

require_once __DIR__."/../vendor/autoload.php";

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Config\LoggingConfig;
use AugmentedSteam\Server\Routing\Router;
use IsThereAnyDeal\Config\Config;
use IsThereAnyDeal\Database\DbConfig;
use Psr\Container\ContainerInterface;

$config = new Config();
$config->loadJsonFile(__DIR__."/../.config.json");
$config->map([
    CoreConfig::class => "core",
    DbConfig::class => "db",
    LoggingConfig::class => "logging",
    KeysConfig::class => "keys",
    EndpointsConfig::class => "endpoints"
    // TODO twitch
    // TODO proxy
]);

$core = $config->getConfig(CoreConfig::class);

if ($core->isShowErrors()) {
    (new \Whoops\Run())
        ->pushHandler(new \Whoops\Handler\PrettyPageHandler)
        ->register();
}

$container = (new \DI\ContainerBuilder())
    ->addDefinitions([
        CoreConfig::class => fn(ContainerInterface $container) => $config->getConfig(CoreConfig::class),
        DbConfig::class => fn(ContainerInterface $container) => $config->getConfig(DbConfig::class),
        LoggingConfig::class => fn(ContainerInterface $container) => $config->getConfig(LoggingConfig::class),
        KeysConfig::class => fn(ContainerInterface $container) => $config->getConfig(KeysConfig::class),
        EndpointsConfig::class => fn(ContainerInterface $container) => $config->getConfig(EndpointsConfig::class),
    ])
    ->addDefinitions(__DIR__."/di.php")
    // ->enableCompilation() FIXME production
    // ->writeProxiesToFile() FIXME production
    ->useAutowiring(false)
    ->useAnnotations(false)
    ->build();

(new Router($container))
    ->route();
