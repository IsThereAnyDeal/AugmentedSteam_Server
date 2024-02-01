<?php
namespace AugmentedSteam\Server\Lib\OpenId;

use League\Uri\Uri;

class OpenId
{
    private readonly OpenIdProvider $provider;
    private string $steamId;

    public function __construct(string $host, string $returnPath) {
        $this->provider = new OpenIdProvider("https://".$host, $returnPath);
    }

    public function isAuthenticationStarted(): bool {
        return $this->provider->isAuthenticationInProgress();
    }

    public function getAuthUrl(): Uri {
        return Uri::new($this->provider->getAuthUrl());
    }

    public function getSteamId(): string {
        return $this->steamId;
    }

    public function authenticate(): bool {
        $result = $this->provider->validateLogin();
        if (is_null($result)) {
            return false;
        }

        $this->steamId = $result;
        return true;
    }
}
