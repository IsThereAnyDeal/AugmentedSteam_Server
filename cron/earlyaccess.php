<?php
$container = require_once __DIR__."/../src/bootstrap.php";

use AugmentedSteam\Server\Cron\CronJobFactory;

/** @var CronJobFactory $cronJobFactory */
$cronJobFactory = $container->get(CronJobFactory::class);

$cronJobFactory
    ->createEarlyAccessJob()
    ->execute();
