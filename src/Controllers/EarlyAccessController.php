<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Managers\EarlyAccessManager;
use IsThereAnyDeal\Database\DbDriver;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class EarlyAccessController extends Controller {

    private EarlyAccessManager $earlyAccessManager;

    public function __construct(ResponseFactoryInterface $responseFactory, DbDriver $db, EarlyAccessManager $earlyAccessManager) {
        parent::__construct($responseFactory, $db);
        $this->earlyAccessManager = $earlyAccessManager;
    }

    public function getAppids_v1(ServerRequestInterface $request): array {
        $appids = $this->earlyAccessManager->getAppids();
        return array_combine($appids, $appids);
    }
}
