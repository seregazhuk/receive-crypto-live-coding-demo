<?php

declare(strict_types=1);

namespace App;

use App\Marshaller\Type\MoneyType;
use Money\Money;
use Temporal\Internal\Marshaller\Meta\Marshal;

class RefillAmount
{
    public function __construct(
        #[Marshal(type: MoneyType::class)]
        public Money $amount,
    ) {

    }
}
