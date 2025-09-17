<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;

$workflowClient = WorkflowClient::create(
    serviceClient: ServiceClient::create('127.0.0.1:7233')
);

$amount = \Money\Money::ETH('1000000000000000'); // 0.001 ETH
