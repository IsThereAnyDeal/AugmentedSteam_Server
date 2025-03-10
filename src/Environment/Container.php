<?php
namespace AugmentedSteam\Server\Environment;

use AugmentedSteam\Server\Config\BrightDataConfig;
use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Controllers\AppController;
use AugmentedSteam\Server\Controllers\DLCController;
use AugmentedSteam\Server\Controllers\EarlyAccessController;
use AugmentedSteam\Server\Controllers\MarketController;
use AugmentedSteam\Server\Controllers\PricesController;
use AugmentedSteam\Server\Controllers\ProfileController;
use AugmentedSteam\Server\Controllers\ProfileManagementController;
use AugmentedSteam\Server\Controllers\RatesController;
use AugmentedSteam\Server\Controllers\SimilarController;
use AugmentedSteam\Server\Controllers\TwitchController;
use AugmentedSteam\Server\Data\Interfaces\AppData\HLTBProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\AppData\PlayersProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\AppData\ReviewsProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\AppData\SteamPeekProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\AppData\WSGFProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\EarlyAccessProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\ExfglsProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\PricesProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\RatesProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\SteamRepProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\TwitchProviderInterface;
use AugmentedSteam\Server\Data\Managers\Market\MarketIndex;
use AugmentedSteam\Server\Data\Managers\Market\MarketManager;
use AugmentedSteam\Server\Data\Managers\SteamRepManager;
use AugmentedSteam\Server\Data\Managers\UserManager;
use AugmentedSteam\Server\Data\Providers\EarlyAccessProvider;
use AugmentedSteam\Server\Data\Providers\ExfglsProvider;
use AugmentedSteam\Server\Data\Providers\HLTBProvider;
use AugmentedSteam\Server\Data\Providers\PlayersProvider;
use AugmentedSteam\Server\Data\Providers\PricesProvider;
use AugmentedSteam\Server\Data\Providers\RatesProvider;
use AugmentedSteam\Server\Data\Providers\ReviewsProvider;
use AugmentedSteam\Server\Data\Providers\SteamPeekProvider;
use AugmentedSteam\Server\Data\Providers\SteamRepProvider;
use AugmentedSteam\Server\Data\Providers\TwitchProvider;
use AugmentedSteam\Server\Data\Providers\WSGFProvider;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Endpoints\EndpointsConfig;
use AugmentedSteam\Server\Endpoints\KeysConfig;
use AugmentedSteam\Server\Lib\Cache\Cache;
use AugmentedSteam\Server\Lib\Cache\CacheInterface;
use AugmentedSteam\Server\Lib\Loader\Proxy\ProxyFactory;
use AugmentedSteam\Server\Lib\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Lib\Loader\SimpleLoader;
use AugmentedSteam\Server\Lib\Logging\LoggerFactory;
use AugmentedSteam\Server\Lib\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Lib\Logging\LoggingConfig;
use AugmentedSteam\Server\Lib\Money\CurrencyConverter;
use AugmentedSteam\Server\Lib\OpenId\Session;
use AugmentedSteam\Server\Lib\Redis\RedisClient;
use AugmentedSteam\Server\Lib\Redis\RedisConfig;
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

            // db
            DbDriver::class => fn(ContainerInterface $c) => (new DbFactory())
                ->getDatabase($c->get(DbConfig::class), "main"), // @phpstan-ignore-line

            RedisClient::class => create(RedisClient::class)
                ->constructor(get(RedisConfig::class)),

            // libraries
            GuzzleClient::class => create(GuzzleClient::class),

            SimpleLoader::class => create()
                ->constructor(get(GuzzleClient::class)),

            CacheInterface::class => create(Cache::class)
                ->constructor(get(DbDriver::class)),

            Session::class => fn(ContainerInterface $c) => new Session(
                $c->get(DbDriver::class), // @phpstan-ignore-line
                $c->get(CoreConfig::class)->getHost() // @phpstan-ignore-line
            ),

            EndpointBuilder::class => create(EndpointBuilder::class)
                ->constructor(
                    get(EndpointsConfig::class),
                    get(KeysConfig::class)
                ),

            CurrencyConverter::class => create(CurrencyConverter::class)
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

            // providers

            SteamRepProviderInterface::class => create(SteamRepProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),

            WSGFProviderInterface::class => fn(ContainerInterface $c) => new WSGFProvider(
                    $c->get(SimpleLoader::class), // @phpstan-ignore-line
                    $c->get(EndpointBuilder::class), // @phpstan-ignore-line
                    $c->get(LoggerFactoryInterface::class)->logger("wsgf") // @phpstan-ignore-line
                ),

            SteampeekProviderInterface::class => fn(ContainerInterface $c) => new SteamPeekProvider(
                $c->get(SimpleLoader::class), // @phpstan-ignore-line
                $c->get(EndpointBuilder::class), // @phpstan-ignore-line
                $c->get(LoggerFactoryInterface::class)->logger("steampeek") // @phpstan-ignore-line
            ),

            EarlyAccessProviderInterface::class => create(EarlyAccessProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
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

            ReviewsProviderInterface::class => create(ReviewsProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),

            RatesProviderInterface::class => create(RatesProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),

            HLTBProviderInterface::class => create(HLTBProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),

            ExfglsProviderInterface::class => create(ExfglsProvider::class)
                ->constructor(
                    get(SimpleLoader::class),
                    get(EndpointBuilder::class)
                ),

            // managers

            MarketManager::class => create()
                ->constructor(get(DbDriver::class)),
            MarketIndex::class => create()
                ->constructor(get(DbDriver::class)),
            UserManager::class => create()
                ->constructor(get(DbDriver::class)),
            SteamRepManager::class => create()
                ->constructor(
                    get(DbDriver::class),
                    get(SteamRepProviderInterface::class)
                ),

            // controllers

            RatesController::class => create()
                ->constructor(
                    get(CurrencyConverter::class)
                ),

            EarlyAccessController::class => create()
                ->constructor(
                    get(CacheInterface::class),
                    get(EarlyAccessProviderInterface::class)
                ),


            DLCController::class => create()
                ->constructor(
                    get(DbDriver::class)
                ),
            MarketController::class => create()
                ->constructor(
                    get(CurrencyConverter::class),
                    get(MarketIndex::class),
                    get(MarketManager::class),
                ),
            ProfileManagementController::class => create()
                ->constructor(
                    get(Session::class),
                    get(MarketManager::class),
                    get(UserManager::class),
                ),

            ProfileController::class => create()
                ->constructor(
                    get(UserManager::class),
                    get(SteamRepManager::class)
                ),

            AppController::class => create()
                ->constructor(
                    get(CacheInterface::class),
                    get(WSGFProviderInterface::class),
                    get(ExfglsProviderInterface::class),
                    get(HLTBProviderInterface::class),
                    get(ReviewsProviderInterface::class),
                    get(PlayersProviderInterface::class)
                ),

            SimilarController::class => create()
                ->constructor(
                    get(CacheInterface::class),
                    get(SteamPeekProviderInterface::class)
                ),

            PricesController::class => create()
                ->constructor(
                    get(PricesProviderInterface::class)
                ),

            TwitchController::class => create()
                ->constructor(
                    get(CacheInterface::class),
                    get(TwitchProviderInterface::class),
                )
        ];
    }
}
