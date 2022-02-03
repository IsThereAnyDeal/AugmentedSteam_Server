<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\User;

use AugmentedSteam\Server\Model\DataObjects\DBadges;
use AugmentedSteam\Server\Model\DataObjects\DUsersProfiles;
use AugmentedSteam\Server\Model\Tables\TBadges;
use AugmentedSteam\Server\Model\Tables\TUsersBadges;
use AugmentedSteam\Server\Model\Tables\TUsersProfiles;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlDeleteQuery;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use IsThereAnyDeal\Database\Sql\SqlUpdateObjectQuery;

class UserManager
{
    private DbDriver $db;
    private TUsersProfiles $p;

    public function __construct(DbDriver $db) {
        $this->db = $db;
        $this->p = new TUsersProfiles();
    }

    private function cleanupProfile(int $steamId): void {
        $p = $this->p;

        (new SqlDeleteQuery($this->db,
            "DELETE FROM $p
            WHERE $p->steam64=:steamId
              AND $p->bg_img IS NULL
              AND $p->style IS NULL"
        ))->delete([
            ":steamId" => $steamId
        ]);
    }

    public function deleteBackground(int $steamId): void {
        $p = $this->p;

        (new SqlUpdateObjectQuery($this->db, $p))
            ->columns($p->bg_img, $p->bg_appid)
            ->where($p->steam64)
            ->update(
                (new DUsersProfiles())
                    ->setSteam64($steamId)
                    ->setBgImg(null)
                    ->setBgAppid(null)
            );

        $this->cleanupProfile($steamId);
    }

    public function saveBackground(int $steamId, int $appid, string $img): void {
        $p = $this->p;

        (new SqlInsertQuery($this->db, $p))
            ->columns($p->steam64, $p->bg_img, $p->bg_appid)
            ->onDuplicateKeyUpdate($p->bg_img, $p->bg_appid)
            ->persist(
                (new DUsersProfiles())
                    ->setSteam64($steamId)
                    ->setBgAppid($appid)
                    ->setBgImg($img)
            );
    }

    public function deleteStyle(int $steamId): void {
        $p = $this->p;

        (new SqlUpdateObjectQuery($this->db, $p))
            ->columns($p->style)
            ->where($p->steam64)
            ->update(
                (new DUsersProfiles())
                    ->setSteam64($steamId)
                    ->setStyle(null)
            );

        $this->cleanupProfile($steamId);
    }

    public function saveStyle(int $steamId, string $style): void {
        $p = $this->p;

        (new SqlInsertQuery($this->db, $p))
            ->columns($p->steam64, $p->style)
            ->onDuplicateKeyUpdate($p->style)
            ->persist(
                (new DUsersProfiles())
                    ->setSteam64($steamId)
                    ->setStyle($style)
            );
    }

    /**
     * @param int $steamId
     * @return iterable<DBadges>
     */
    public function getBadges(int $steamId): iterable {
        $b = new TBadges();
        $u = new TUsersBadges();

        return (new SqlSelectQuery($this->db,
            "SELECT $b->title, $b->img
            FROM $u
            JOIN $b ON $u->badge_id=$b->id
            WHERE $u->steam64=:steamId"
        ))->params([
            ":steamId" => $steamId
        ])->fetch(DBadges::class);
    }

    public function getProfileInfo(int $steamId): ?DUsersProfiles {
        $p = $this->p;

        return (new SqlSelectQuery($this->db,
            "SELECT $p->bg_img, $p->bg_appid, $p->style
            FROM $p
            WHERE $p->steam64=:steamId"
        ))->params([
            ":steamId" => $steamId
        ])->fetch(DUsersProfiles::class)
          ->getOne();
    }
}
