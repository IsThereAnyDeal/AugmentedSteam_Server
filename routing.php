<?php
declare(strict_types=1);
require_once __DIR__."/src/bootstrap.php";

use AugmentedSteam\Server\Environment\Container;
use AugmentedSteam\Server\Routing\Router;

$container = Container::getInstance();
(new Router())->route($container);
