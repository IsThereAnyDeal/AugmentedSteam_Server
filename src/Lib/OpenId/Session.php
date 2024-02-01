<?php
namespace AugmentedSteam\Server\Lib\OpenId;

use AugmentedSteam\Server\Database\DSession;
use AugmentedSteam\Server\Database\TSessions;
use IsThereAnyDeal\Database\DbDriver;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface;

class Session {
    private const string CookieName = "session";
    private const int CookieExpiry = 30*86400;
    private const string HashAlgorithm = "sha256";

    public function __construct(
        private readonly DbDriver $db,
        private readonly string $host
    ) {}

    private function hasSession(ServerRequestInterface $request, int $steamId): bool {
        $cookie = $request->getCookieParams();
        if (!isset($cookie[self::CookieName])) {
            return false;
        }

        $sessionCookie = $cookie[self::CookieName];
        $parts = explode(":", $sessionCookie);
        if (count($parts) != 2) {
            return false;
        }

        $s = new TSessions();
        /** @var ?DSession $session */
        $session = $this->db->select(<<<SQL
            SELECT $s->hash, $s->steam_id
            FROM $s
            WHERE $s->token = :token
              AND $s->expiry >= :timestamp
            SQL
        )->params([
            ":token" => $parts[0],
            ":timestamp" => time()
        ])->fetch(DSession::class)
          ->getOne();

        return !is_null($session)
            && $session->getSteamId() === $steamId
            && hash_equals($session->getHash(), hash(self::HashAlgorithm, $parts[1]));
    }

    private function saveSession(int $steamId): void {
        $token = bin2hex(openssl_random_pseudo_bytes(5));
        $validator = bin2hex(openssl_random_pseudo_bytes(20));
        $expiry = time() + self::CookieExpiry;

        setcookie(self::CookieName, "{$token}:{$validator}", $expiry, "/");

        $s = new TSessions();

        $this->db->delete(<<<SQL
            DELETE FROM $s
            WHERE $s->expiry < :timestamp
            SQL
        )->delete([
            ":timestamp" => time()
        ]);

        $this->db->insert($s)
            ->columns($s->token, $s->hash, $s->steam_id, $s->expiry)
            ->persist((new DSession())
                ->setToken($token)
                ->setHash(hash(self::HashAlgorithm, $validator))
                ->setSteamId($steamId)
                ->setExpiry($expiry)
            );
    }

    public function authorize(ServerRequestInterface $request, string $selfPath, string $errorUrl, ?int $steamId): int|RedirectResponse {
        if (is_null($steamId) || !$this->hasSession($request, $steamId)) {
            $openId = new OpenId($this->host, $selfPath);

            if (!$openId->isAuthenticationStarted()) {
                return new RedirectResponse($openId->getAuthUrl());
            }

            if (!$openId->authenticate()) {
                return new RedirectResponse($errorUrl);
            } else {
                $steamId = (int)($openId->getSteamId());
                $this->saveSession($steamId);
            }
        }

        return $steamId;
    }
}
