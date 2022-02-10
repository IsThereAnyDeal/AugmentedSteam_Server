<?php
namespace AugmentedSteam\Server\OpenId;

use League\Uri\Uri;
use LightOpenID;

class OpenId {

    private const ProviderUrl = "https://steamcommunity.com/openid/";

    private string $host;
    private LightOpenID $client;
    protected string $steamId;

    public function __construct(string $host, string $returnUrl) {
        $this->host = $host;

        // make sure we have proper host and scheme
        $returnUrl = Uri::createFromString($returnUrl)
            ->withHost($this->host)
            ->withScheme("https");

        try {
            $this->client = new LightOpenID($this->host);

            // return url must match host exactly, even with capitalization
            $this->client->returnUrl = str_ireplace($this->host, $this->host, $returnUrl);
        } catch (\ErrorException $e) {
        }
    }

    public function isAuthenticationStarted(): bool {
        return isset($this->client->mode) && $this->client->mode;
    }

    public function getAuthUrl(): Uri {
        /** @phpstan-ignore-next-line implements magic method */
        $this->client->identity = self::ProviderUrl;
        return Uri::createFromString($this->client->authUrl());
    }

    public function getSteamId(): string {
        return $this->steamId;
    }

    public function authenticate(): bool {

        if (!$this->client->validate()) {
            return false;
        }

        if (!preg_match("#^".preg_quote(self::ProviderUrl)."#", $this->client->data['openid_op_endpoint'])) {
            return false;
        }

        $matches = [];
        /** @phpstan-ignore-next-line implements magic method */
        $identity = $this->client->identity;
        if (preg_match("#^".preg_quote(self::ProviderUrl)."id/(\d+)$#", $identity, $matches)) {
            $this->steamId = $matches[1];
            return true;
        }

        return false;
    }
}
