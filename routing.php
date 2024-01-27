<?php
declare(strict_types=1);

use AugmentedSteam\Server\Environment\Container;
use AugmentedSteam\Server\Routing\Router;

$container = Container::getInstance();
(new Router())->route($container);
