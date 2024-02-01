<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\TwitchProviderInterface;
use AugmentedSteam\Server\Data\Objects\TwitchStream;
use AugmentedSteam\Server\Lib\Cache\CacheInterface;
use AugmentedSteam\Server\Lib\Cache\ECacheKey;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;

class TwitchController extends Controller
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly TwitchProviderInterface $twitch
    ) {}

    /**
     * @param array{channel: string} $params
     * @return array<mixed>|JsonSerializable
     */
    public function stream_v2(ServerRequestInterface $request, array $params): array|JsonSerializable {
        $channel = $params['channel'];

        $key = ECacheKey::Twitch;
        $field = $channel;

        if ($this->cache->has($key, $field)) {
            $stream = $this->cache->get($key, $field);
            if (!is_null($stream) && !($stream instanceof TwitchStream)) {
                throw new \Exception();
            }
        } else {
            $stream = $this->twitch->fetch($channel);
            $this->cache->set($key, $field, $stream, 1800);
        }

        return $stream ?? [];
    }
}
