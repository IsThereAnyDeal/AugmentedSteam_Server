<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\DataObjects;

use IsThereAnyDeal\Database\Sql\AInsertableObject;
use IsThereAnyDeal\Database\Sql\ISelectable;

class DMarketIndex extends AInsertableObject implements ISelectable
{
    protected int $appid;
    protected int $last_update;
    protected int $last_request;
    protected int $request_counter;

    public function getAppid(): int {
        return $this->appid;
    }

    public function setAppid(int $appid): self {
        $this->appid = $appid;
        return $this;
    }

    public function getLastUpdate(): int {
        return $this->last_update;
    }

    public function setLastUpdate(int $last_update): self {
        $this->last_update = $last_update;
        return $this;
    }

    public function getLastRequest(): int {
        return $this->last_request;
    }

    public function setLastRequest(int $last_request): self {
        $this->last_request = $last_request;
        return $this;
    }

    public function getRequestCounter(): int {
        return $this->request_counter;
    }

    public function setRequestCounter(int $request_counter): self {
        $this->request_counter = $request_counter;
        return $this;
    }
}
