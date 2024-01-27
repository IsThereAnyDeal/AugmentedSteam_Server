<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Database\TDlcCategories;
use AugmentedSteam\Server\Database\TGameDlc;
use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\DataObjects\DDlcCategories;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Http\Message\ServerRequestInterface;

class GameController extends Controller
{
    /** @deprecated */
    public function getDlcInfoV1(ServerRequestInterface $request): array {
        $appid = (new Param($request, "appid"))
            ->int();

        $g = new TGameDlc();
        $d = new TDlcCategories();

        $select = (new SqlSelectQuery($this->db,
            "SELECT $d->name, $d->icon, $d->description, $g->score
            FROM $g
            JOIN $d ON $g->dlc_category=$d->id
            WHERE $g->appid=:appid
            ORDER BY $g->score DESC
            LIMIT 3"
        ))->params([
            ":appid" => $appid
        ])->fetch(DDlcCategories::class);

        $data = [];
        /** @var DDlcCategories $o */
        foreach ($select as $o) {
            $data[] = [
                "name" => $o->getName(),
                "icon" => $o->getIcon(),
                "desc" => $o->getDescription(),
                "count" => (int)$o->score  /** @phpstan-ignore-line */ // HACK
            ];
        }
        return $data;
    }

    public function getDlcInfoV2(ServerRequestInterface $request): array {
        $appid = (new Param($request, "appid"))
            ->int();

        $g = new TGameDlc();
        $d = new TDlcCategories();

        $select = (new SqlSelectQuery($this->db,
            "SELECT $d->id, $d->name, $d->description
            FROM $g
            JOIN $d ON $g->dlc_category=$d->id
            WHERE $g->appid=:appid
            ORDER BY $g->score DESC
            LIMIT 3"
        ))->params([
            ":appid" => $appid
        ])->fetch(DDlcCategories::class);

        $data = [];
        /** @var DDlcCategories $o */
        foreach ($select as $o) {
            $data[] = [
                "id" => (int)$o->getId(),
                "name" => $o->getName(),
                "description" => $o->getDescription(),
            ];
        }
        return $data;
    }
}
