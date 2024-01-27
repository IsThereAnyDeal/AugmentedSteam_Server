<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Reviews;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\Cache\Cache;
use AugmentedSteam\Server\Model\Cache\ECacheKey;
use Psr\Log\LoggerInterface;

class ReviewsManager
{
    private const CacheLimit = 86400;

    private Cache $cache;
    private SimpleLoader $loader;
    private LoggerInterface $logger;
    private EndpointsConfig $config;
    private KeysConfig $keysConfig;

    public function __construct(
        Cache $cache,
        SimpleLoader $loader,
        LoggerFactoryInterface $loggerFactory,
        EndpointsConfig $config,
        KeysConfig $keysConfig
    ) {
        $this->cache = $cache;
        $this->loader = $loader;
        $this->logger = $loggerFactory->create("reviews");
        $this->config = $config;
        $this->keysConfig = $keysConfig;
    }

    public function getData(int $appid): ?ReviewsData {
        $data = $this->cache->getValue($appid, ECacheKey::Reviews, self::CacheLimit);

        if (is_null($data)) {
            $data = $this->refresh($appid);
        }

        return empty($data)
            ? null
            : new ReviewsData($data);
    }

    private function refresh(int $appid): array {
        $host = $this->config->getIsThereAnyDealApiHost();
        $key = $this->keysConfig->getIsThereAnyDealApiKey();
        $url = "{$host}/v01/augmentedsteam/info/?key={$key}&appid={$appid}";

        $data = [];

        $response = $this->loader->get($url);
        if (!is_null($response)) {
            $json = json_decode($response->getBody()->getContents(), true);

            if (isset($json['data'])) {
                if (isset($json['data']['metacritic']['userscore'])) {
                    $data['metacritic']['userscore'] = $json['data']['metacritic']['userscore'];
                }

                if (isset($json['data']['opencritic'])) {
                    $opencritic = $json['data']['opencritic'];

                    $reviews = [];
                    foreach($opencritic['reviews'] as $r) {
                        $reviews[] = [
                            "publishedDate" => $r['publishedDate'],
                            "snippet" => $r['snippet'],
                            "displayScore" => $r['displayScore'],
                            "externalUrl" => $r['externalUrl'],
                            "author" => $r['author'],
                            "outletName" => $r['outletName'],
                        ];
                    }

                    $data['opencritic'] = [
                        "url" => $opencritic['url'],
                        "score" => $opencritic['score'],
                        "award" => $opencritic['award'],
                        "reviews" => $reviews,
                    ];
                }
            }

            // TODO should we cache even unsuccessful responses?
            $this->cache->setValue($appid, ECacheKey::Reviews, json_encode($data));
        }

        if (!empty($data)) {
            $this->logger->info((string)$appid);
        } else {
            $this->logger->error((string)$appid);
        }
        return $data;
    }
}
