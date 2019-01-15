<?php
require_once "Core/Autoloader.php";

Core\Autoloader::getInstance()->register();

require_once __DIR__ . "/../lib/vendor/autoload.php";

if (Config::ShowErrors) {
    error_reporting(E_ALL);
    ini_set("display_errors", "1");
} else {
    error_reporting(0);
    ini_set("display_errors", "0");
}
