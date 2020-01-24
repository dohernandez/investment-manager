<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Money;

final class WalletMetadata
{
    /**
     * @var Money
     */
    private $invested;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var Money
     */
    private $funds;

    /**
     * @var BookMetadata
     */
    private $dividend;

    /**
     * @var BookMetadata
     */
    private $commissions;

    /**
     * @var BookMetadata
     */
    private $connection;

    /**
     * @var BookMetadata
     */
    private $interest;
}
