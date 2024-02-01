<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\AppData\AppDataProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\AppData\PlayersProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\AppData\ReviewsProviderInterface;
use AugmentedSteam\Server\Data\Interfaces\AppData\WSGFProviderInterface;
use AugmentedSteam\Server\Data\Managers\ExfglsManager;
use AugmentedSteam\Server\Data\Managers\HLTBManager;
use AugmentedSteam\Server\Lib\Cache\CacheInterface;
use AugmentedSteam\Server\Lib\Cache\ECacheKey;
use Psr\Http\Message\ServerRequestInterface;

class AppController extends Controller
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly WSGFProviderInterface $wsgf,
        private readonly ExfglsManager $exfglsManager,
        private readonly HLTBManager $hltbManager,
        private readonly ReviewsProviderInterface $reviews,
        private readonly PlayersProviderInterface $players,
    ) {}

    public function getData(
        AppDataProviderInterface $provider,
        ECacheKey $cacheKey,
        int $appid,
        int $ttl
    ): mixed {
        $key = $cacheKey;
        $field = (string)$appid;

        if ($this->cache->has($key, $field)) {
            return $this->cache->get($key, $field);
        }

        $data = $provider->fetch($appid);
        $this->cache->set($key, $field, $data, $ttl);
        return $data;
    }

    /**
     * @param array{appid: numeric-string} $params
     * @return array<string, mixed>
     */
    public function appInfo_v2(ServerRequestInterface $request, array $params): array {
        $appid = intval($params['appid']);

        $exfgls = $this->exfglsManager->get($appid);

        return [
            "family_sharing" => !$exfgls->isExcluded(),
            "players" => $this->getData($this->players, ECacheKey::Players, $appid, 30*60),
            "wsgf" => $this->getData($this->wsgf, ECacheKey::WSGF, $appid, 3*86400),
            "hltb" => $this->hltbManager->get($appid),
            "reviews" => $this->getData($this->reviews, ECacheKey::Reviews, $appid, 86400)
        ];
    }
}
