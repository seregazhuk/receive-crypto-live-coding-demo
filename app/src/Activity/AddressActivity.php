<?php

declare(strict_types=1);

namespace App\Activity;

use App\AddressWithAmount;
use Money\Money;
use SWeb3\SWeb3;
use SWeb3\Utils;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
readonly class AddressActivity
{
    public function __construct(private SWeb3 $node) {

    }

    #[ActivityMethod]
    public function hasEnoughBalance(AddressWithAmount $request): bool
    {
        $rawBalance = $this->node->call(
            'eth_getBalance',
            [$request->address->address, 'latest']
        );
        $balance = Money::ETH(Utils::hexToBn($rawBalance->result)->toString());
        return $balance->greaterThanOrEqual($request->amount);
    }
}

