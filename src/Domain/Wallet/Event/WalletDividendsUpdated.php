<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Money\Money;
use DateTime;

class WalletDividendsUpdated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var DateTime
     */
    private $getDateAt;

    /**
     * @var Money
     */
    private $funds;

    /**
     * @var Money
     */
    private $benefits;

    /**
     * @var float
     */
    private $percentageBenefits;

    /**
     * @var BookEntry
     */
    private $dividends;

    public function __construct(
        string $id,
        DateTime $getDateAt,
        Money $funds,
        Money $benefits,
        float $percentageBenefits,
        BookEntry $dividends
    ) {
        $this->id = $id;
        $this->getDateAt = $getDateAt;
        $this->funds = $funds;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
        $this->dividends = $dividends;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGetDateAt(): DateTime
    {
        return $this->getDateAt;
    }

    public function getFunds(): Money
    {
        return $this->funds;
    }

    public function getBenefits(): Money
    {
        return $this->benefits;
    }

    public function getPercentageBenefits(): float
    {
        return $this->percentageBenefits;
    }

    public function getDividends(): BookEntry
    {
        return $this->dividends;
    }
}
