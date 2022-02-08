<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\Twitch\TwitchManager;
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

    public function getStreamV1(ServerRequestInterface $request): array {
        $channel = (new Param($request, "channel"))->string();

        return $this->twitchManager
            ->getStream($channel);
    }

}
