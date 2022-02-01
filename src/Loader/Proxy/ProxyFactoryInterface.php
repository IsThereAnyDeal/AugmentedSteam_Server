<?php
namespace AugmentedSteam\Server\Loader\Proxy;

interface ProxyFactoryInterface {
    public function createProxy(): ProxyInterface;
}
