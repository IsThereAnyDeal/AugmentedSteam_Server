<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Twitch;

use AugmentedSteam\Server\Model\DataObjects\DTwitchToken;
use AugmentedSteam\Server\Model\Tables\TTwitchToken;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlDeleteQuery;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use IsThereAnyDeal\Twitch\Api\Token;
use IsThereAnyDeal\Twitch\Api\TokenStorageInterface;

class TokenStorage implements TokenStorageInterface
{
    private DbDriver $db;

    public function __construct(DbDriver $db) {
        $this->db = $db;
    }

    function get(): ?Token {
        $t = new TTwitchToken();

        $this->db->begin();

        (new SqlDeleteQuery($this->db,
            "DELETE FROM $t WHERE $t->expiry < :timestamp"
        ))->delete([
            ":timestamp" => time()
        ]);

        $token = (new SqlSelectQuery($this->db,
            "SELECT $t->token FROM $t"
        ))->fetchValue();

        $this->db->commit();

        if (is_null($token)) {
            return null;
        }

        return new Token($token);
    }

    function set(Token $token, int $expiry): void {

        $t = new TTwitchToken();
        (new SqlInsertQuery($this->db, $t))
            ->columns($t->token, $t->expiry)
            ->onDuplicateKeyUpdate($t->expiry)
            ->persist((new DTwitchToken())
                ->setToken($token->getValue())
                ->setExpiry($expiry)
            );
    }
}
