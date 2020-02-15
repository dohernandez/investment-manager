<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Money;

final class BookMetadata
{
    /**
     * @var Money|null
     */
    private $total;

    public function getTotal(): ?Money
    {
        return $this->total;
    }
}
