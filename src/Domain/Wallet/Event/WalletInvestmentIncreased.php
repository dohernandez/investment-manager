<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\WalletBook;

class WalletInvestmentIncreased
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var WalletBook
     */
    private $metadata;

    public function __construct(string $id, WalletBook $metadata)
    {
        $this->id = $id;
        $this->metadata = $metadata;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMetadata(): WalletBook
    {
        return $this->metadata;
    }
}
