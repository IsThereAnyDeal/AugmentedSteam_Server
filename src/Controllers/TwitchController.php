<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Managers\TwitchManager;
use IsThereAnyDeal\Database\DbDriver;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class TwitchController extends Controller
{
    private TwitchManager $twitchManager;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        DbDriver $db,
        TwitchManager $twitchManager
    ) {
        parent::__construct($responseFactory, $db);
        $this->twitchManager = $twitchManager;
    }

    public function getStream_v2(ServerRequestInterface $request, array $params): ?array {
        return $this->twitchManager
            ->getStream($params['channel']);
    }

}
