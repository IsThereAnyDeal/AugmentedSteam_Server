<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\StorePage;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\Cache\Cache;
use AugmentedSteam\Server\Model\Cache\ECacheKey;
use Psr\Log\LoggerInterface;

class WSGFManager
{
    private const CacheLimit = 86400;

    private Cache $cache;
    private SimpleLoader $loader;
    private LoggerInterface $logger;
    private EndpointsConfig $config;

    public function __construct(
        Cache $cache,
        SimpleLoader $loader,
        LoggerFactoryInterface $loggerFactory,
        EndpointsConfig $config
    ) {
        $this->cache = $cache;
        $this->loader = $loader;
        $this->logger = $loggerFactory->create("wsgf");
        $this->config = $config;
    }

    public function getData(int $appid): ?WSGFData {
        $data = $this->cache->getValue($appid, ECacheKey::WSGF, self::CacheLimit);

        if (is_null($data)) {
            $data = $this->refresh($appid);
        }

        if (empty($data)) {
            return null;
        }

        return new WSGFData($data);
    }

    private function refresh(int $appid): array {
        $url = $this->config->getWSGFEndpoint($appid);

        $data = [];

        $response = $this->loader->get($url);
        $xml = simplexml_load_string($response->getBody()->getContents());
        if ($xml !== false && !empty($xml->children())) {
            $json = json_encode($xml);

            $obj = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($obj)) {
                $node = isset($obj['node'][0]) // check if we have multiple nodes in indexed array
                    ? $obj['node'][count($obj['node']) - 1] // some entries have multiple nodes, not sure why. Use last?
                    : $obj['node'];
                $data = [
                    "Title" => $node['Title'],
                    "SteamID" => $node['SteamID'],
                    "Path" => $node['Path'],
                    "WideScreenGrade" => $node['WideScreenGrade'],
                    "MultiMonitorGrade" => $node['MultiMonitorGrade'],
                    "UltraWideScreenGrade" => $node['UltraWideScreenGrade'],
                    "Grade4k" => $node['Grade4k'],
                    "Nid" => $node['Nid']
                ];
            } else {
                $data = [];
            }
        }

        $this->cache->setValue($appid, ECacheKey::WSGF, json_encode($data));
        if (!empty($data)) {
            $this->logger->info((string)$appid);
        } else {
            $this->logger->error((string)$appid);
        }
        return $data;
    }
}
