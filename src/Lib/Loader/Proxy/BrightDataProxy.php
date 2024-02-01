<?php
namespace AugmentedSteam\Server\Lib\Loader\Proxy;

use AugmentedSteam\Server\Config\BrightDataConfig;

class BrightDataProxy implements ProxyInterface {

    private BrightDataConfig $config;

    public function __construct(BrightDataConfig $config) {
        $this->config = $config;
    }

    public function getCurlOptions(): array {
        $rand = mt_rand(10000, 99999);

        $setup = "lum-customer-{$this->config->getUser()}-zone-{$this->config->getZone()}-session-rand{$rand}";

        return [
            CURLOPT_PROXY => $this->config->getUrl(),
            CURLOPT_PROXYPORT => $this->config->getPort(),
            CURLOPT_PROXYUSERPWD => "$setup:{$this->config->getPassword()}"
        ];
    }
}
