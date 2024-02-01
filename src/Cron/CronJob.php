<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Cron;

use AugmentedSteam\Server\Environment\Lock;

class CronJob
{
    private ?Lock $lock = null;

    /** @var callable */
    private $callable;

    public function lock(string $name, int $durationMin): self {
        $this->lock = new Lock("temp/locks/{$name}.lock");
        $this->lock->tryLock($durationMin * 60);
        return $this;
    }

    public function callable(callable $callable): self {
        $this->callable = $callable;
        return $this;
    }

    public function execute(): void {

        try {
            call_user_func($this->callable);
        } catch(\Throwable $e) {
            if (!is_null($this->lock)) {
                $this->lock->unlock();
            }

            throw $e; // rethrow exception so we know about it
        }

        if (!is_null($this->lock)) {
            $this->lock->unlock();
        }
    }
}

