<?php
declare(strict_types=1);

const RELEASE_ROOT = __DIR__."/..";
const PROJECT_ROOT = RELEASE_ROOT."/../..";
const TEMP_DIR = RELEASE_ROOT."/temp";
const LOGS_DIR = RELEASE_ROOT."/logs";
const BIN_DIR = RELEASE_ROOT."/bin";

require_once RELEASE_ROOT."/vendor/autoload.php";

use AugmentedSteam\Server\Config\BrightDataConfig;
use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Data\Updaters\Exfgls\ExfglsConfig;
use AugmentedSteam\Server\Endpoints\EndpointsConfig;
use AugmentedSteam\Server\Endpoints\KeysConfig;
use AugmentedSteam\Server\Environment\Container;
use AugmentedSteam\Server\Lib\Redis\RedisConfig;
use AugmentedSteam\Server\Logging\LoggingConfig;
use IsThereAnyDeal\Config\Config;
use IsThereAnyDeal\Database\DbConfig;

$config = new Config();
$config->map([
    CoreConfig::class => "core",
    DbConfig::class => "db",
    RedisConfig::class => "redis",
    LoggingConfig::class => "logging",
    KeysConfig::class => "keys",
    EndpointsConfig::class => "endpoints",
    BrightDataConfig::class => "brightdata",
    ExfglsConfig::class => "exfgls"
]);
$config->loadJsonFile(RELEASE_ROOT."/.config.json");

Container::init($config);

/** @var AugmentedSteam\Server\Config\CoreConfig $coreConfig */
$coreConfig = $config->getConfig(CoreConfig::class);

if ($coreConfig->usePrettyErrors()) {
    $whoops = (new \Whoops\Run);
    $whoops->pushHandler(
        \Whoops\Util\Misc::isCommandLine()
            ? new \Whoops\Handler\PlainTextHandler()
            : new \Whoops\Handler\JsonResponseHandler()
    );
    $whoops->register();
}

if ($coreConfig->isSentryEnabled()) {
    Sentry\init([
        "dsn" => $coreConfig->getSentryDsn(),
        "environment" => $coreConfig->getSentryEnvironment(),
        "error_types" => E_ALL,
    ]);
}
