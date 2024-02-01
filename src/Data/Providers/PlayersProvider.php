<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Providers;

use AugmentedSteam\Server\Data\Interfaces\AppData\PlayersProviderInterface;
use AugmentedSteam\Server\Data\Objects\Players;
use AugmentedSteam\Server\Endpoints\EndpointBuilder;
use AugmentedSteam\Server\Lib\Loader\SimpleLoader;

class PlayersProvider implements PlayersProviderInterface
{
    public function __construct(
        private readonly SimpleLoader $loader,
        private readonly EndpointBuilder $endpoints
    ) {}

    public function fetch(int $appid): Players {
        $endpoint = $this->endpoints->getPlayers($appid);
        $response = $this->loader->get($endpoint);

        $players = new Players();

        if (!is_null($response)) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

            if (is_array($json)) {
                /**
                 * @var array{
                 *     current: int,
                 *     day: int,
                 *     peak: int
                 * } $json
                 */

                $players->current = $json['current'];
                $players->peakToday = $json['day'];
                $players->peakAll = $json['peak'];
            }
        }

        return $players;
    }
}
