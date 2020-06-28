<?php
namespace Twitch\Api;

class Authorization {

    private const HOST = "https://id.twitch.tv/oauth2/token";

    private $guzzle;

    public function __construct(\GuzzleHttp\Client $guzzle) {
        $this->guzzle = $guzzle;
    }

    public function getToken(): ?Token {

        \dibi::begin();
        \dibi::query("DELETE FROM [twitch_token] WHERE [expiry] < %i", time());
        $token = \dibi::query("SELECT [token] FROM [twitch_token]")->fetchSingle();
        \dibi::commit();

        if (!empty($token)) {
            return new Token($token);
        }

        $response = $this->guzzle->post(self::HOST, [
            "query" => [
                "client_id" => \Config::TwitchClientId,
                "client_secret" => \Config::TwitchClientSecret,
                "grant_type" => "client_credentials"
            ]
        ]);

        $body = (string)$response->getBody();
        $json = json_decode($body, true);

        if ($json !== false && isset($json['access_token'])) {
            $token = $json['access_token'];
            $expiry = $json['expires_in'];

            \dibi::query(
                "INSERT INTO [twitch_token] ([token], [expiry])
                 VALUES (%s, %i)",
                $token, time() + $expiry);
            return new Token($token);
        }

        return null;
    }
}
