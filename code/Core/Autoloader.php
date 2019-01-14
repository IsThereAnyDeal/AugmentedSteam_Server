<?php
namespace Core;

class Autoloader {

    private static $instance;

    private function __construct() {}

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register() {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function loadClass($fqn) {
        $path = __DIR__."/../".str_replace("\\", "/", ltrim($fqn, "/")).".php";
        $this->requireFile($path);
    }

    private function requireFile($file) {
        if (!file_exists($file)) {
            return false;
        }
        require_once $file;
        return true;
    }
}
