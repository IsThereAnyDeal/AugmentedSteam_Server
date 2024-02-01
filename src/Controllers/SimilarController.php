<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\AppData\SteamPeekProviderInterface;
use AugmentedSteam\Server\Data\Objects\SteamPeak\SteamPeekResults;
use AugmentedSteam\Server\Lib\Cache\CacheInterface;
use AugmentedSteam\Server\Lib\Cache\ECacheKey;
use AugmentedSteam\Server\Lib\Http\BoolParam;
use AugmentedSteam\Server\Lib\Http\IntParam;
use Psr\Http\Message\ServerRequestInterface;

class SimilarController extends Controller {

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly SteamPeekProviderInterface $steamPeek
    ) {}

    /**
     * @param array{
     *     appid: numeric-string
     * } $params
     * @return array<mixed>|\JsonSerializable
     */
    public function similar_v2(ServerRequestInterface $request, array $params): array|\JsonSerializable {
        $appid = intval($params['appid']);
        /** @var int $count */
        $count = (new IntParam($request, "count", 5))->value();
        /** @var bool $shuffle */
        $shuffle = (new BoolParam($request, "shuffle", false))->value();

        $key = ECacheKey::SteamPeek;
        $field = (string)$appid;

        if ($this->cache->has($key, $field)) {
            $games = $this->cache->get($key, $field);
            if (!is_null($games) && !($games instanceof SteamPeekResults)) {
                throw new \Exception();
            }
        } else {
            $games = $this->steamPeek->fetch($appid);
            $this->cache->set($key, $field, $games, 10*86400);
        }

        if (is_null($games)) {
            return [];
        }

        return $games
            ->shuffle($shuffle)
            ->limit($count);
    }
}
