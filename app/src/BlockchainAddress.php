<?php

declare(strict_types=1);

namespace App;

class BlockchainAddress
{
    public function __construct(public string $address, public string $privateKey)
    {
    }

    public function getRawPrivateKey(): string
    {
        return str_replace('0x', '', $this->privateKey);
    }
}
