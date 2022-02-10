<?php
declare(strict_types=1);

require_once __DIR__."/../vendor/autoload.php";

use AugmentedSteam\Server\Config\BrightDataConfig;
use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\ExfglsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Config\LoggingConfig;
use AugmentedSteam\Server\Config\SentryConfig;
use AugmentedSteam\Server\Config\TwitchConfig;
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
    ExfglsConfig::class => "exfgls",
    TwitchConfig::class => "twitch",
    SentryConfig::class => "sentry"
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

$sentry = $config->getConfig(SentryConfig::class);
if ($sentry->isEnabled()) {
    \Sentry\init([
        "dsn" => $sentry->getDsn(),
        "environment" => $core->getEnvironment(),
    ]);
}

$di = (new \DI\ContainerBuilder())
    ->addDefinitions([
        CoreConfig::class => fn(ContainerInterface $c) => $config->getConfig(CoreConfig::class),
        DbConfig::class => fn(ContainerInterface $c) => $config->getConfig(DbConfig::class),
        LoggingConfig::class => fn(ContainerInterface $c) => $config->getConfig(LoggingConfig::class),
        KeysConfig::class => fn(ContainerInterface $c) => $config->getConfig(KeysConfig::class),
        EndpointsConfig::class => fn(ContainerInterface $c) => $config->getConfig(EndpointsConfig::class),
        BrightDataConfig::class => fn(ContainerInterface $c) => $config->getConfig(BrightDataConfig::class),
        ExfglsConfig::class => fn(ContainerInterface $c) => $config->getConfig(ExfglsConfig::class),
        TwitchConfig::class => fn(ContainerInterface $c) => $config->getConfig(TwitchConfig::class),
    ])
    ->addDefinitions(__DIR__."/di.php")
    ->useAutowiring(false)
    ->useAnnotations(false);

    if ($core->isProduction()) {
        $di->enableCompilation(__DIR__."/../di-cache/");
    }

return $di->build();
