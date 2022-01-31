<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Exceptions\MissingParameterException;
use AugmentedSteam\Server\Model\DataObjects\DDlcCategories;
use AugmentedSteam\Server\Model\Tables\TDlcCategories;
use AugmentedSteam\Server\Model\Tables\TGameDlc;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Http\Message\ServerRequestInterface;

class GameController extends Controller
{

    private function getAppidParam(ServerRequestInterface $request): int {
        $query = $request->getQueryParams();
        if (empty($query['appid'])) {
            throw new MissingParameterException("appid");
        }

        if (!is_numeric($query['appid'])) {
            throw new InvalidValueException("appid");
        }

        return (int)$query['appid'];
    }

    /** @deprecated */
    public function getDlcInfoV1(ServerRequestInterface $request): array {
        $appid = $this->getAppidParam($request);

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
                "count" => (int)$o->score // HACK
            ];
        }
        return $data;
    }

    public function getDlcInfoV2(ServerRequestInterface $request): array {
        $appid = $this->getAppidParam($request);

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
