<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\AppData\SteamPeekProviderInterface;
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

    public function similar_v2(ServerRequestInterface $request, array $params): \JsonSerializable {
        $appid = intval($params['appid']);
        $count = (new IntParam($request, "count", 5))->value();
        $shuffle = (new BoolParam($request, "shuffle", false))->value();

        $key = ECacheKey::SteamPeek;
        $field = (string)$appid;

        if ($this->cache->has($key, $field)) {
            $games = $this->cache->get($key, $field);
        } else {
            $games = $this->steamPeek->fetch($appid);
            $this->cache->set($key, $field, $games, 10*86400);
        }

        return $games
            ->shuffle($shuffle)
            ->limit($count);
    }
}
