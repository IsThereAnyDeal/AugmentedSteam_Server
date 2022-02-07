<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Prices;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Loader\SimpleLoader;

class PricesManager
{
    private SimpleLoader $loader;
    private EndpointsConfig $config;
    private KeysConfig $keysConfig;

    public function __construct(
        SimpleLoader $loader,
        EndpointsConfig $config,
        KeysConfig $keysConfig
    ) {
        $this->loader = $loader;
        $this->config = $config;
        $this->keysConfig = $keysConfig;
    }

    public function getData(
        array $ids,
        ?string $country=null,
        array $stores=[],
        bool $voucher=false
    ): ?array {

        $params = [
            "key" => $this->keysConfig->getIsThereAnyDealApiKey(),
            "shop" => "steam",
            "ids" => implode(",", $ids),
        ];
        if (!empty($country)) {
            $params['country'] = $country;
        }
        if (!empty($stores)) {
            $params['allowed'] = implode(",", $stores);
        }
        if ($voucher) {
            $params['optional'] = "voucher";
        }

        $host = $this->config->getIsThereAnyDealApiHost();
        $url = $host."/v01/game/overview/?".http_build_query($params);

        $response = $this->loader->get($url);
        if (!is_null($response)) {
            $json = json_decode($response->getBody()->getContents(), true);

            if (isset($json['data'])) {
                return [
                    "data" => $json['data'],
                    ".meta" => $json['.meta']
                ];
            }
        }

        return null;
    }
}
