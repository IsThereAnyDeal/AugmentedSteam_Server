<?php
namespace AugmentedSteam\Server\OpenId;

use AugmentedSteam\Server\Model\DataObjects\DSession;
use AugmentedSteam\Server\Model\Tables\TSessions;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlDeleteQuery;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Http\Message\ServerRequestInterface;

class Session extends OpenId {
    private const CookieName = "session";
    private const CookieExpiry = 30*86400;
    private const HashAlgorithm = "sha256";

    private DbDriver $db;

    public function __construct(DbDriver $db, string $host, string $returnUrl) {
        parent::__construct($host, $returnUrl);
        $this->db = $db;
    }

    public function isAuthenticated(ServerRequestInterface $request, ?int $steamId): bool {
        if ($this->isAuthenticationStarted()) {
            return false;
        }

        if (is_null($steamId)) {
            return false;
        }

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
        $session = (new SqlSelectQuery($this->db,
            "SELECT $s->hash, $s->steam_id
            FROM $s
            WHERE $s->token = :token
              AND $s->expiry >= :timestamp"
        ))->params([
            ":token" => $parts[0],
            ":timestamp" => time()
        ])->fetch(DSession::class)
          ->getOne();

        if (!is_null($session)
            && $session->getSteamId() === $steamId
            && hash_equals($session->getHash(), hash(self::HashAlgorithm, $parts[1]))
        ) {
            $this->steamId = $steamId;
            return true;
        }
        return false;
    }

    public function authenticate(): bool {
        $result = parent::authenticate();

        if ($result) {
            $token = bin2hex(openssl_random_pseudo_bytes(5));
            $validator = bin2hex(openssl_random_pseudo_bytes(20));
            $expiry = time() + self::CookieExpiry;

            setcookie(self::CookieName, "{$token}:{$validator}", $expiry, "/");

            $s = new TSessions();

            (new SqlDeleteQuery($this->db,
                "DELETE FROM $s WHERE $s->expiry < :timestamp"
            ))->delete([
                ":timestamp" => time()
            ]);

            (new SqlInsertQuery($this->db, $s))
                ->columns($s->token, $s->hash, $s->steam_id, $s->expiry)
                ->persist((new DSession())
                    ->setToken($token)
                    ->setHash(hash(self::HashAlgorithm, $validator))
                    ->setSteamId($this->getSteamId())
                    ->setExpiry($expiry)
                );
        }

        return $result;
    }
}
