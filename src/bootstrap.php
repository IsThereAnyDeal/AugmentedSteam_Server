<?php
declare(strict_types=1);

require_once __DIR__."/../vendor/autoload.php";

use AugmentedSteam\Server\Config\BrightDataConfig;
use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\ExfglsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Config\LoggingConfig;
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
    EndpointsConfig::class => "endpoints",
    BrightDataConfig::class => "brightdata",
    ExfglsConfig::class => "exfgls"
    // TODO twitch
]);

$core = $config->getConfig(CoreConfig::class);

$isCli = php_sapi_name() == "cli";
if ($core->isShowErrors() || $isCli) {
    $whoops = (new \Whoops\Run());
    if ($isCli) {
        $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
    } else {
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    }
    $whoops->register();
}

return (new \DI\ContainerBuilder())
    ->addDefinitions([
        CoreConfig::class => fn(ContainerInterface $container) => $config->getConfig(CoreConfig::class),
        DbConfig::class => fn(ContainerInterface $container) => $config->getConfig(DbConfig::class),
        LoggingConfig::class => fn(ContainerInterface $container) => $config->getConfig(LoggingConfig::class),
        KeysConfig::class => fn(ContainerInterface $container) => $config->getConfig(KeysConfig::class),
        EndpointsConfig::class => fn(ContainerInterface $container) => $config->getConfig(EndpointsConfig::class),
        BrightDataConfig::class => fn(ContainerInterface $container) => $config->getConfig(BrightDataConfig::class),
        ExfglsConfig::class => fn(ContainerInterface $container) => $config->getConfig(ExfglsConfig::class),
    ])
    ->addDefinitions(__DIR__."/di.php")
    // ->enableCompilation() FIXME production
    // ->writeProxiesToFile() FIXME production
    ->useAutowiring(false)
    ->useAnnotations(false)
    ->build();
