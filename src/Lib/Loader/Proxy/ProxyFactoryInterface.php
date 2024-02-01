<?php
namespace AugmentedSteam\Server\Lib\Loader\Proxy;

interface ProxyFactoryInterface {
    public function createProxy(): ProxyInterface;
}
