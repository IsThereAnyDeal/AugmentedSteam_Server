<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Database\TDlcCategories;
use AugmentedSteam\Server\Database\TGameDlc;
use AugmentedSteam\Server\Model\DataObjects\DDlcCategories;
use IsThereAnyDeal\Database\DbDriver;
use Psr\Http\Message\ServerRequestInterface;

class DLCController extends Controller
{
    public function __construct(
        private readonly DbDriver $db
    ) {}

    /**
     * @param array{appid: numeric-string} $params
     * @return list<array{id: number, name: string, description: string}>
     */
    public function dlcInfo_v2(ServerRequestInterface $request, array $params): array {
        $appid = intval($params['appid']);
        if (empty($appid)) {
            return [];
        }

        $g = new TGameDlc();
        $d = new TDlcCategories();

        return $this->db->select(<<<SQL
            SELECT $d->id, $d->name, $d->description, $d->icon
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
                "icon" => $o->getIcon()
            ]);
    }
}
