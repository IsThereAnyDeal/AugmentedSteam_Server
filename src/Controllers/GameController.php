<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Database\TDlcCategories;
use AugmentedSteam\Server\Database\TGameDlc;
use AugmentedSteam\Server\Http\IntParam;
use AugmentedSteam\Server\Model\DataObjects\DDlcCategories;
use Psr\Http\Message\ServerRequestInterface;

class GameController extends Controller
{
    /**
     * return list<array{id: number, name: string, description: string}>
     */
    public function getDlcInfo_v2(ServerRequestInterface $request): array {
        $appid = (new IntParam($request, "appid"))->value();

        $g = new TGameDlc();
        $d = new TDlcCategories();

        return $this->db->select(<<<SQL
            SELECT $d->id, $d->name, $d->description
            FROM $g
            JOIN $d ON $g->dlc_category=$d->id
            WHERE $g->appid=:appid
            ORDER BY $g->score DESC
            LIMIT 3
            SQL
        )->params([
            ":appid" => $appid
        ])->fetch(DDlcCategories::class)
          ->toArray(fn(DDlcCategories $o) => [
                "id" => $o->getId(),
                "name" => $o->getName(),
                "description" => $o->getDescription(),
            ]);
    }
}
