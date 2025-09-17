<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\BlockchainAddress;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;

$workflowClient = WorkflowClient::create(
    serviceClient: ServiceClient::create('127.0.0.1:7233')
);

$amount = \Money\Money::ETH('1000000000000000'); // 0.001 ETH
$invoiceAccount = \SWeb3\Accounts::create(); // address, public, private
$invoiceAddress = new BlockchainAddress(
    $invoiceAccount->address,
    $invoiceAccount->privateKey
);

echo "Invoice address: $invoiceAddress->address" . PHP_EOL;
echo "Invoice amount: 0.001 ETH" . PHP_EOL;

$workflow = $workflowClient->newWorkflowStub(\App\Workflow\AcceptCryptoWorkflow::class);
$request = new \App\AddressWithAmount($invoiceAddress, $amount);
$workflowClient->start($workflow, $request);
