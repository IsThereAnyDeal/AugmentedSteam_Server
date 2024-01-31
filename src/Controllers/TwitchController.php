<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Managers\TwitchManager;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;

class TwitchController extends Controller
{
    public function __construct(
        private readonly TwitchManager $twitchManager
    ) {}

    /**
     * @param array{channel: string} $params
     */
    public function getStream_v2(ServerRequestInterface $request, array $params): array|JsonSerializable {
        $stream = $this->twitchManager
            ->getStream($params['channel']);

        return $stream ?? [];
    }
}
