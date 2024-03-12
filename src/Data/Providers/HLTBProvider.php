<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\AppData\HLTBProviderInterface;
use AugmentedSteam\Server\Data\Objects\HLTB;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Lib\Loader\SimpleLoader;

class HLTBProvider implements HLTBProviderInterface
{
    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints
    ) {}

    public function fetch(int $appid): ?HLTB {
        $endpoint = $this->endpoints->getHLTB($appid);
        $response = $this->loader->get($endpoint);

        if (!is_null($response)) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

            if (is_array($json)) {
                /**
                 * @var array{
                 *     id: int,
                 *     main: ?int,
                 *     extra: ?int,
                 *     complete: ?int
                 * } $json
                 */

                $hltb = new HLTB();
                $hltb->story = $json['main'] === 0 ? null : (int)floor($json['main'] / 60);
                $hltb->extras = $json['extra'] === 0 ? null : (int)floor($json['extra'] / 60);
                $hltb->complete = $json['complete'] === 0 ? null : (int)floor($json['complete'] / 60);
                $hltb->url = "https://howlongtobeat.com/game/{$json['id']}";
                return $hltb;
            }
        }

        return null;
    }
}
