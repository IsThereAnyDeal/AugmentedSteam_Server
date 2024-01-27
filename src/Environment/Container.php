<?php
namespace AugmentedSteam\Server\Environment;

use AugmentedSteam\Server\Config\BrightDataConfig;
use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\ExfglsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Config\TwitchConfig;
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
use AugmentedSteam\Server\Cron\CronJobFactory;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactory;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactory;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Logging\LoggingConfig;
use AugmentedSteam\Server\Model\Cache\Cache;
use AugmentedSteam\Server\Model\EarlyAccess\EarlyAccessManager;
use AugmentedSteam\Server\Model\HowLongToBeat\HLTBManager;
use AugmentedSteam\Server\Model\Market\MarketManager;
use AugmentedSteam\Server\Model\Money\CurrencyConverter;
use AugmentedSteam\Server\Model\Prices\PricesManager;
use AugmentedSteam\Server\Model\Reviews\ReviewsManager;
use AugmentedSteam\Server\Model\SteamPeek\SteamPeekManager;
use AugmentedSteam\Server\Model\SteamRep\SteamRepManager;
use AugmentedSteam\Server\Model\StorePage\ExfglsManager;
use AugmentedSteam\Server\Model\StorePage\SteamChartsManager;
use AugmentedSteam\Server\Model\StorePage\SteamSpyManager;
use AugmentedSteam\Server\Model\StorePage\WSGFManager;
use AugmentedSteam\Server\Model\Survey\SurveyManager;
use AugmentedSteam\Server\Model\Twitch\TokenStorage;
use AugmentedSteam\Server\Model\Twitch\TwitchManager;
use AugmentedSteam\Server\Model\User\UserManager;
use GuzzleHttp\Client as GuzzleClient;
use IsThereAnyDeal\Config\Config;
use IsThereAnyDeal\Database\DbConfig;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\DbFactory;
use IsThereAnyDeal\Twitch\Api\TokenStorageInterface;
use Laminas\Diactoros\ResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use function DI\create;
use function DI\get;

class Container implements ContainerInterface
{
    private static Container $instance;

    public static function init(Config $config): void {
        self::$instance = new Container($config);
    }

    public static function getInstance(): Container {
        return self::$instance;
    }

    private readonly Config $config;
    private readonly \DI\Container $diContainer;

    public function __construct(Config $config) {
        $this->config = $config;
        $this->diContainer = (new \DI\ContainerBuilder())
            ->addDefinitions($this->definitions())
            // ->enableCompilation()
            // ->writeProxiesToFile()
            ->useAutowiring(false)
            ->useAttributes(false);
    }

    /**
     * @template T
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id): mixed {
        return $this->diContainer->get($id);
    }

    public function has(string $id): bool {
        return $this->diContainer->has($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function definitions(): array {
        return [
            // config
            CoreConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(CoreConfig::class),
            DbConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(DbConfig::class),
            LoggingConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(LoggingConfig::class),
            KeysConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(KeysConfig::class),
            EndpointsConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(EndpointsConfig::class),
            BrightDataConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(BrightDataConfig::class),
            ExfglsConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(ExfglsConfig::class),
            TwitchConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(TwitchConfig::class),

            // db
            DbDriver::class => fn(ContainerInterface $c) => DbFactory::getDatabase($c->get(DbConfig::class)),

            // libraries
            GuzzleClient::class => create(GuzzleClient::class),

            SimpleLoader::class => create()
                ->constructor(
                    get(GuzzleClient::class),
                    fn (ContainerInterface $c) => $c->get(LoggerFactoryInterface::class)->createLogger("guzzle")
            ),

            Cache::class => create()
                ->constructor(get(DbDriver::class)),

            // factories

            ResponseFactoryInterface::class => create(ResponseFactory::class),

            LoggerFactoryInterface::class => create(LoggerFactory::class)
                ->constructor(
                    get(LoggingConfig::class),
                    LOGS_DIR
                ),

            ProxyFactoryInterface::class => create(ProxyFactory::class)
                ->constructor(get(BrightDataConfig::class)),

            TokenStorageInterface::class => create(TokenStorage::class)
                ->constructor(get(DbDriver::class)),

            CronJobFactory::class => create()
                ->constructor(
                    get(LoggerFactoryInterface::class),
                    get(ProxyFactoryInterface::class),
                    get(DbDriver::class),
                    get(GuzzleClient::class),
                    get(EndpointsConfig::class),
                    get(KeysConfig::class),
                    get(ExfglsConfig::class)
                ),

            // managers

            MarketManager::class => create()
                ->constructor(get(DbDriver::class)),
            UserManager::class => create()
                ->constructor(get(DbDriver::class)),
            SteamRepManager::class => create()
                ->constructor(
                    get(DbDriver::class),
                    get(SimpleLoader::class),
                    get(EndpointsConfig::class),
                    get(LoggerFactoryInterface::class)
                ),
            SteamChartsManager::class => create()
                ->constructor(
                    get(DbDriver::class),
                    get(SimpleLoader::class),
                    get(LoggerFactoryInterface::class),
                    get(EndpointsConfig::class)
                ),
            SteamSpyManager::class => create()
                ->constructor(
                    get(DbDriver::class),
                    get(SimpleLoader::class),
                    get(LoggerFactoryInterface::class),
                    get(ProxyFactoryInterface::class),
                    get(EndpointsConfig::class)
                ),
            WSGFManager::class => create()
                ->constructor(
                    get(Cache::class),
                    get(SimpleLoader::class),
                    get(LoggerFactoryInterface::class),
                    get(EndpointsConfig::class)
                ),
            ExfglsManager::class => create()
                ->constructor(
                    get(DbDriver::class),
                    get(LoggerFactoryInterface::class),
                    get(ExfglsConfig::class)
                ),
            HLTBManager::class => create()
                ->constructor(
                    get(DbDriver::class),
                    get(SimpleLoader::class),
                    get(LoggerFactoryInterface::class),
                    get(ProxyFactoryInterface::class)
                ),
            ReviewsManager::class => create()
                ->constructor(
                    get(Cache::class),
                    get(SimpleLoader::class),
                    get(LoggerFactoryInterface::class),
                    get(EndpointsConfig::class),
                    get(KeysConfig::class)
                ),
            SteamPeekManager::class => create()
                ->constructor(
                    get(DbDriver::class),
                    get(SimpleLoader::class),
                    get(EndpointsConfig::class),
                    get(KeysConfig::class),
                    get(LoggerFactoryInterface::class)
                ),
            PricesManager::class => create()
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointsConfig::class),
                    get(KeysConfig::class)
                ),
            EarlyAccessManager::class => create()
                ->constructor(
                    get(DbDriver::class)
                ),
            SurveyManager::class => create()
                ->constructor(
                    get(DbDriver::class),
                    get(LoggerFactoryInterface::class)
                ),
            TwitchManager::class => create()
                ->constructor(
                    get(TwitchConfig::class),
                    get(TokenStorageInterface::class),
                    get(GuzzleClient::class)
                ),

            // controllers

            RatesController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class)
                ),
            GameController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class)
                ),
            MarketController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class)
                ),
            ProfileManagementController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class),
                    get(CoreConfig::class),
                    get(MarketManager::class),
                    get(UserManager::class),
                ),

            ProfileController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class),
                    get(UserManager::class),
                    get(SteamRepManager::class)
                ),

            StorePageController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class),
                    get(SteamChartsManager::class),
                    get(SteamSpyManager::class),
                    get(WSGFManager::class),
                    get(ExfglsManager::class),
                    get(HLTBManager::class),
                    get(ReviewsManager::class),
                    get(SurveyManager::class)
                ),

            SimilarController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class),
                    get(SteamPeekManager::class)
                ),

            PricesController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class),
                    get(PricesManager::class)
                ),

            EarlyAccessController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class),
                    get(EarlyAccessManager::class)
                ),

            SurveyController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class),
                    get(CoreConfig::class),
                    get(SurveyManager::class),
                ),

            TwitchController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class),
                    get(TwitchManager::class),
                )
        ];
    }
}
