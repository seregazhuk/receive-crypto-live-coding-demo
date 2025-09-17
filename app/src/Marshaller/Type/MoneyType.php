<?php

declare(strict_types=1);

namespace App\Marshaller\Type;

use Money\Currency;
use Money\Money;
use Temporal\Internal\Marshaller\Type\Type;

/**
 * @extends Type<array>
 */
class MoneyType extends Type
{
    /**
     * @param mixed|Money $value
     */
    public function serialize($value): array
    {
        return [
            'amount' => $value->getAmount(),
            'currency' => $value->getCurrency()->getCode(),
        ];
    }

    /**
     * @param mixed|array $value
     * @param mixed       $current
     */
    public function parse($value, $current): Money
    {
        return new Money(
            $value['amount'],
            new Currency($value['currency'])
        );
    }
}
