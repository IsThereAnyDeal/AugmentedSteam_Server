<?php
declare(strict_types=1);

use AugmentedSteam\Server\Routing\Router;

$container = require_once __DIR__."/bootstrap.php";

(new Router($container))
    ->route();
