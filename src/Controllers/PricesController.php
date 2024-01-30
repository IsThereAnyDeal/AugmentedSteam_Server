<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\PricesProviderInterface;
use AugmentedSteam\Server\Data\Managers\GameIdsManager;
use AugmentedSteam\Server\Http\ListParam;
use AugmentedSteam\Server\Http\StringParam;
use League\Route\Http\Exception\BadRequestException;
use Psr\Http\Message\ServerRequestInterface;

class PricesController extends Controller {

    public function __construct(
        private readonly GameIdsManager $gameIdsManager,
        private readonly PricesProviderInterface $pricesProvider
    ) {}

    public function prices_v2(ServerRequestInterface $request): ?array {
        $data = $request->getBody()->getContents();
        if (!json_validate($data)) {
            throw new BadRequestException();
        }

        $params = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
        if (!isset($params['country']) || !is_string($params['country'])) {
            throw new BadRequestException();
        }

        $country = $params['country'];
        $shops = $this->validateIntList($params, "shops");
        $apps = $this->validateIntList($params, "apps");
        $subs = $this->validateIntList($params, "subs");
        $bundles = $this->validateIntList($params, "bundles");
        $voucher = filter_var($params['voucher'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $ids = array_merge(
            array_map(fn($id) => "app/$id", array_filter($apps)),
            array_map(fn($id) => "sub/$id", array_filter($subs)),
            array_map(fn($id) => "bundle/$id", array_filter($bundles)),
        );

        if (count($ids) == 0) {
            throw new BadRequestException();
        }

        $map = $this->gameIdsManager->getIdMap($ids);
        $gids = array_values($map);
        $gidMap = array_flip($map);

        $overview = $this->pricesProvider->fetch($gids, $shops, $country);

        $result = [];
        foreach($overview['prices'] as $game) {
            $steamId = $gidMap[$game['id']];
            $result['prices'][$steamId] = [
                "current" => $game['current'],
                "lowest" => $game['lowest'],
                "urls" => [
                    "info" => "https://isthereanydeal.com/game/id:{$game['id']}/info/",
                    "history" => "https://isthereanydeal.com/game/id:{$game['id']}/history/",
                ]
            ];
        }
        $result['bundles'] = $overview['bundles'];
        return $result;
    }
}
