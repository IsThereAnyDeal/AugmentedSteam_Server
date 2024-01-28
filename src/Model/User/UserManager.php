<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\User;

use AugmentedSteam\Server\Database\TBadges;
use AugmentedSteam\Server\Database\TUsersBadges;
use AugmentedSteam\Server\Database\TUsersProfiles;
use AugmentedSteam\Server\Model\DataObjects\DBadges;
use AugmentedSteam\Server\Model\DataObjects\DUsersProfiles;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\Read\SqlResult;

class UserManager
{
    public function __construct(
        private readonly DbDriver $db
    ) {}

    private function cleanupProfile(int $steamId): void {
        $p = new TUsersProfiles();

        $this->db->delete(<<<SQL
            DELETE FROM $p
            WHERE $p->steam64=:steamId
              AND $p->bg_img IS NULL
              AND $p->style IS NULL
            SQL
        )->delete([
            ":steamId" => $steamId
        ]);
    }

    public function deleteBackground(int $steamId): void {
        $p = new TUsersProfiles();

        $this->db->updateObj($p)
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
        $p = new TUsersProfiles();

        $this->db->insert($p)
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
        $p = new TUsersProfiles();

        $this->db->updateObj($p)
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
        $p = new TUsersProfiles();

        $this->db->insert($p)
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
     * @return SqlResult<DBadges>
     */
    public function getBadges(int $steamId): SqlResult {
        $b = new TBadges();
        $u = new TUsersBadges();

        return $this->db->select(<<<SQL
            SELECT $b->title, $b->img
            FROM $u
            JOIN $b ON $u->badge_id=$b->id
            WHERE $u->steam64=:steamId
            SQL
        )->params([
            ":steamId" => $steamId
        ])->fetch(DBadges::class);
    }

    public function getProfileInfo(int $steamId): ?DUsersProfiles {
        $p = new TUsersProfiles();

        return $this->db->select(<<<SQL
            SELECT $p->bg_img, $p->bg_appid, $p->style
            FROM $p
            WHERE $p->steam64=:steamId
            SQL
        )->params([
            ":steamId" => $steamId
        ])->fetch(DUsersProfiles::class)
          ->getOne();
    }
}
