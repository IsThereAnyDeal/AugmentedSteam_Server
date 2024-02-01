<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Environment;

class Lock
{
    private string $path;

    public function __construct(string $name) {
        $this->path = TEMP_DIR . "/locks/" . basename($name, ".lock") . ".lock";
    }

    public function isLocked(int $seconds): bool {
        clearstatcache();
        if (file_exists($this->path)) {
            $timestamp = (int)file_get_contents($this->path);
            return $timestamp + $seconds >= time();
        }
        return false;
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

    public function tryLock(int $seconds): void {
        if ($this->isLocked($seconds)) {
            if (php_sapi_name() == "cli") {
                echo "Locked by " . basename($this->path);
            }
            die();
        }
        $this->lock();
    }
}
