<?php
namespace AugmentedSteam\Server\Lib\Loader\Proxy;

interface ProxyInterface {

    /**
     * @return array<int, mixed>
     */
    public function getCurlOptions(): array;
}
