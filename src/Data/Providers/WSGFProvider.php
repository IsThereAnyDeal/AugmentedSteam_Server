<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\AppData\WSGFProviderInterface;
use AugmentedSteam\Server\Data\Objects\WSGF;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Lib\Loader\SimpleLoader;
use Psr\Log\LoggerInterface;

class WSGFProvider implements WSGFProviderInterface
{
    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints,
        private readonly LoggerInterface $logger
    ) {}

    public function fetch(int $appid): ?WSGF {
        $url = $this->endpoints->getWSGF($appid);
        $response = $this->loader->get($url);
        if (is_null($response)) {
            return null;
        }

        $data = null;

        $xml = simplexml_load_string($response->getBody()->getContents());
        if ($xml !== false && !empty($xml->children())) {
            $json = json_encode($xml, flags: JSON_THROW_ON_ERROR);

            $obj = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            if (json_last_error() === JSON_ERROR_NONE && !empty($obj) && is_array($obj)) {
                /**
                 * @var array{
                 *     Title: string,
                 *     SteamID: numeric-string,
                 *     Path: string,
                 *     WideScreenGrade: string,
                 *     MultiMonitorGrade: string,
                 *     UltraWideScreenGrade: string,
                 *     Grade4k: string,
                 *     Nid: numeric-string
                 * } $node
                 * @var array{
                 *     node: list<array<mixed>>
                 * } $obj
                 */
                $node = isset($obj['node'][0]) // check if we have multiple nodes in indexed array
                    ? $obj['node'][count($obj['node']) - 1] // some entries have multiple nodes, not sure why
                    : $obj['node'];

                $data = new WSGF();
                $data->title = $node['Title'];
                $data->steamId = intval($node['SteamID']);
                $data->path = $node['Path'];
                $data->wideScreenGrade = $node['WideScreenGrade'];
                $data->multiMonitorGrade = $node['MultiMonitorGrade'];
                $data->ultraWideScreenGrade = $node['UltraWideScreenGrade'];
                $data->grade4k = $node['Grade4k'];
                $data->nid = intval($node['Nid']);
            }
        }

        if (!empty($data)) {
            $this->logger->info((string)$appid);
        } else {
            $this->logger->error((string)$appid);
        }
        return $data;
    }
}
