<?php

declare(strict_types=1);

use App\Activity\AddressActivity;
use App\Activity\SweepingActivity;
use App\Workflow\AcceptCryptoWorkflow;
use Symfony\Component\Dotenv\Dotenv;
use Temporal\WorkerFactory;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$factory = WorkerFactory::create();
$worker = $factory->newWorker();

$node = new \SWeb3\SWeb3('https://ethereum-sepolia-rpc.publicnode.com');
$node->chainId = 0xaa36a7;
$sweepingAddress = new \App\BlockchainAddress(
    $_ENV['SWEEPING_ADDRESS'],
    $_ENV['SWEEPING_PRIVATE_KEY']
);

$worker->registerActivity(
    AddressActivity::class,
    fn() => new AddressActivity($node)
);
$worker->registerActivity(
    SweepingActivity::class,
    fn() => new SweepingActivity($node, $sweepingAddress)
);
$worker->registerWorkflowTypes(AcceptCryptoWorkflow::class);

$factory->run();
