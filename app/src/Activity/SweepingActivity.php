<?php

declare(strict_types=1);

namespace App\Activity;

use App\AddressWithAmount;
use App\BlockchainAddress;
use App\RefillAmount;
use Money\Money;
use SWeb3\SWeb3;
use SWeb3\Utils;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
class SweepingActivity
{
    const int ETH_TRANSFER_GAS = 21000;

    public function __construct(
        private readonly SWeb3 $node,
        private readonly BlockchainAddress $sweepingAddress
    )
    {
    }

    #[ActivityMethod]
    public function sweep(AddressWithAmount $request): string
    {
        $this->node->setPersonalData(
            $request->address->address,
            $request->address->getRawPrivateKey()
        );

        $transaction = [
            'from' => $request->address->address,
            'to' => $this->sweepingAddress->address,
            'value' => $request->amount->getAmount(),
            'nonce' => $this->node->personal->getNonce(),
            'gasLimit' => self::ETH_TRANSFER_GAS,
        ];

        $result = $this->node->send($transaction);
        if (isset($result->error)) {
            throw new \RuntimeException($result->error->message);
        }

        return $result->result;
    }

    #[ActivityMethod]
    public function calcRefillAmount(): RefillAmount
    {
        $rawGasPrice = $this->node->call('eth_gasPrice')->result;
        $gasPrice = Money::ETH(Utils::hexToBn($rawGasPrice)->toString());
        $refillAmount = $gasPrice
            ->multiply(self::ETH_TRANSFER_GAS)
            ->multiply(2);

        return new RefillAmount($refillAmount);
    }

    #[ActivityMethod]
    public function refill(AddressWithAmount $request): string
    {
        $this->node->setPersonalData(
            $this->sweepingAddress->address,
            $this->sweepingAddress->getRawPrivateKey()
        );

        $transaction = [
            'from' => $this->sweepingAddress->address,
            'to' => $request->address->address,
            'value' => $request->amount->getAmount(),
            'nonce' => $this->node->personal->getNonce(),
            'gasLimit' => self::ETH_TRANSFER_GAS,
        ];

        $result = $this->node->send($transaction);
        if (isset($result->error)) {
            throw new \RuntimeException($result->error->message);
        }

        return $result->result;
    }
}
