<?php
use AugmentedSteam\Server\Cron\CronJobFactory;
use AugmentedSteam\Server\Environment\Container;

require_once __DIR__."/bootstrap.php";

/** @var string[] $argv */

$container = Container::getInstance();
(new CronJobFactory($container))
    ->getJob(...array_slice($argv, 1))
    ->execute();
