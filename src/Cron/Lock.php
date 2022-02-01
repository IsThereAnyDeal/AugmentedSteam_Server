<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Cron;

class Lock {

    private string $path = "./";

    public function __construct($path) {
        $this->path = __DIR__."/../../".ltrim($path, "/");
    }

    public function lock(): void {
        $dir = dirname($this->path);
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        file_put_contents($this->path, time());
    }

    public function unlock(): void {
        clearstatcache();
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    public function isLocked(int $duration): bool {
        clearstatcache();
        if (file_exists($this->path)) {
            $timestamp = (int)file_get_contents($this->path);
            return $timestamp+$duration >= time();
        }
        return false;
    }

    public function tryLock($duration) {
        if ($this->isLocked($duration)) {
            die("Locked by ".basename($this->path));
        }
        $this->lock();
    }
}
