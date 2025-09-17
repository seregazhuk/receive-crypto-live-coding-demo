<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Temporal\WorkerFactory;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$factory = WorkerFactory::create();
$worker = $factory->newWorker();

$factory->run();
