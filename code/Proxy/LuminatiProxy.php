<?php
namespace Proxy;

class LuminatiProxy {

    private $user;
    private $pass;
    private $zone;

    public function __construct($user, $pass, $zone) {
        $this->user = $user;
        $this->pass = $pass;
        $this->zone = $zone;
    }

    public function getCurlOptions() {
        $rand = mt_rand(10000, 99999);

        $setup = "lum-customer-{$this->user}-zone-{$this->zone}-session-rand{$rand}";

        return [
            CURLOPT_PROXY => "zproxy.lum-superproxy.io",
            CURLOPT_PROXYPORT => 22225,
            CURLOPT_PROXYUSERPWD => "$setup:{$this->pass}"
        ];
    }
}
