<?php
namespace AugmentedSteam\Server\Environment;

use AugmentedSteam\Server\Config\BrightDataConfig;
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
use AugmentedSteam\Server\Cron\CronJobFactory;
use AugmentedSteam\Server\Data\Interfaces\EarlyAccessProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\GameIdsProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\PlayersProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\PricesProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\RatesProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\SteamPeekProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\SteamRepProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\TwitchProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\WSGFProviderInterface;
use AugmentedSteam\Server\Data\Managers\EarlyAccessManager;
use AugmentedSteam\Server\Data\Managers\ExfglsManager;
use AugmentedSteam\Server\Data\Managers\GameIdsManager;
use AugmentedSteam\Server\Data\Managers\HLTBManager;
use AugmentedSteam\Server\Data\Managers\SteamPeekManager;
use AugmentedSteam\Server\Data\Managers\SteamRepManager;
use AugmentedSteam\Server\Data\Managers\TwitchManager;
use AugmentedSteam\Server\Data\Managers\WSGFManager;
use AugmentedSteam\Server\Data\Providers\EarlyAccessProvider;
use AugmentedSteam\Server\Data\Providers\GameIdsProvider;
use AugmentedSteam\Server\Data\Providers\PlayersProvider;
use AugmentedSteam\Server\Data\Providers\PricesProvider;
use AugmentedSteam\Server\Data\Providers\RatesProvider;
use AugmentedSteam\Server\Data\Providers\SteamPeekProvider;
use AugmentedSteam\Server\Data\Providers\SteamRepProvider;
use AugmentedSteam\Server\Data\Providers\TwitchProvider;
use AugmentedSteam\Server\Data\Providers\WSGFProvider;
use AugmentedSteam\Server\Data\Updaters\Exfgls\ExfglsConfig;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Endpoints\EndpointsConfig;
use AugmentedSteam\Server\Endpoints\KeysConfig;
use AugmentedSteam\Server\Lib\Redis\RedisCache;
use AugmentedSteam\Server\Lib\Redis\RedisClient;
use AugmentedSteam\Server\Lib\Redis\RedisConfig;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactory;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactory;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Logging\LoggingConfig;
use AugmentedSteam\Server\Model\Cache\Cache;
use AugmentedSteam\Server\Model\Market\MarketManager;
use AugmentedSteam\Server\Model\Reviews\ReviewsManager;
use AugmentedSteam\Server\Model\StorePage\SteamSpyManager;
use AugmentedSteam\Server\Model\User\UserManager;
use AugmentedSteam\Server\OpenId\Session;
use GuzzleHttp\Client as GuzzleClient;
use IsThereAnyDeal\Config\Config;
use IsThereAnyDeal\Database\DbConfig;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\DbFactory;
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
            ->useAttributes(false)
            ->build();
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
            RedisConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(RedisConfig::class),
            LoggingConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(LoggingConfig::class),
            KeysConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(KeysConfig::class),
            EndpointsConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(EndpointsConfig::class),
            BrightDataConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(BrightDataConfig::class),
            ExfglsConfig::class => fn(ContainerInterface $c) => $this->config->getConfig(ExfglsConfig::class),

            // db
            DbDriver::class => fn(ContainerInterface $c) => DbFactory::getDatabase($c->get(DbConfig::class)),
            RedisClient::class => create(RedisClient::class)
                ->constructor(RedisConfig::class),

            // libraries
            GuzzleClient::class => create(GuzzleClient::class),

            SimpleLoader::class => create()
                ->constructor(get(GuzzleClient::class)),

            Cache::class => create()
                ->constructor(get(DbDriver::class)),

            RedisCache::class => create()
                ->constructor(get(RedisClient::class)),

            Session::class => fn(ContainerInterface $c) => new Session(
                $c->get(DbDriver::class),
                $c->get(CoreConfig::class)->getHost()
            ),

            // factories

            ResponseFactoryInterface::class => create(ResponseFactory::class),

            LoggerFactoryInterface::class => create(LoggerFactory::class)
                ->constructor(
                    get(LoggingConfig::class),
                    LOGS_DIR
                ),

            ProxyFactoryInterface::class => create(ProxyFactory::class)
                ->constructor(get(BrightDataConfig::class)),

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

            // providers

            SteamRepProviderInterface::class => create(SteamRepProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),

            WSGFProviderInterface::class => fn(ContainerInterface $c) => new WSGFProvider(
                    $c->get(SimpleLoader::class),
                    $c->get(EndpointBuilder::class),
                    $c->get(LoggerFactoryInterface::class)->logger("wsgf")
                ),

            SteampeekProviderInterface::class => fn(ContainerInterface $c) => new SteamPeekProvider(
                $c->get(SimpleLoader::class),
                $c->get(EndpointBuilder::class),
                $c->get(LoggerFactoryInterface::class)->logger("steampeek")
            ),

            EarlyAccessProviderInterface::class => create(EarlyAccessProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),

            GameIdsProviderInterface::class => create(GameIdsProvider::class)
                ->constructor(
                    get(GuzzleClient::class),
                    get(EndpointBuilder::class)
                ),

            PricesProviderInterface::class => create(PricesProvider::class)
                ->constructor(
                    get(GuzzleClient::class),
                    get(EndpointBuilder::class)
                ),

            TwitchProviderInterface::class => create(TwitchProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),

            PlayersProviderInterface::class => create(PlayersProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),


            RatesProviderInterface::class => create(RatesProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),

            // managers

            MarketManager::class => create()
                ->constructor(get(DbDriver::class)),
            UserManager::class => create()
                ->constructor(get(DbDriver::class)),
            SteamRepManager::class => create()
                ->constructor(
                    get(DbDriver::class),
                    get(SteamRepProviderInterface::class)
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
                    get(WSGFProviderInterface::class)
                ),
            ExfglsManager::class => create()
                ->constructor(
                    get(DbDriver::class)
                ),
            HLTBManager::class => create()
                ->constructor(
                    get(DbDriver::class)
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
                    get(Cache::class),
                    get(SteamRepProviderInterface::class)
                ),
            GameIdsManager::class => create()
                ->constructor(
                    get(RedisClient::class),
                    get(GameIdsProviderInterface::class)
                ),
            EarlyAccessManager::class => create()
                ->constructor(
                    get(RedisClient::class),
                    get(EarlyAccessProviderInterface::class)
                ),
            TwitchManager::class => create()
                ->constructor(
                    get(RedisCache::class),
                    get(TwitchProviderInterface::class)
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
                    get(Session::class),
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
                    get(SteamSpyManager::class),
                    get(WSGFManager::class),
                    get(ExfglsManager::class),
                    get(HLTBManager::class),
                    get(ReviewsManager::class)
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
                    get(GameIdsProviderInterface::class),
                    get(PricesProviderInterface::class)
                ),

            EarlyAccessController::class => create()
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(DbDriver::class),
                    get(EarlyAccessManager::class)
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
