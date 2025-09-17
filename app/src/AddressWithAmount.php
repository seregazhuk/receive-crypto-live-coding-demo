<?php

declare(strict_types=1);

namespace App;

use App\Marshaller\Type\MoneyType;
use Money\Money;
use Temporal\Internal\Marshaller\Meta\Marshal;

class AddressWithAmount
{
    public function __construct(
        public BlockchainAddress $address,
        #[Marshal(type: MoneyType::class)]
        public Money $amount,
    ) {

    }
}
