<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Loader\Proxy;

use AugmentedSteam\Server\Config\BrightDataConfig;

class ProxyFactory implements ProxyFactoryInterface
{
    private BrightDataConfig $config;

    public function __construct(BrightDataConfig $config) {
        $this->config = $config;
    }

    public function createProxy(): ProxyInterface {
        return new BrightDataProxy($this->config);
    }
}
