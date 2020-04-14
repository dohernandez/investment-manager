<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;

final class WalletInterestUpdated implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var DateTime
     */
    private $dateAt;

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
    private $interests;

    public function __construct(
        string $id,
        DateTime $dateAt,
        Money $funds,
        Money $benefits,
        float $percentageBenefits,
        BookEntry $interests
    ) {
        $this->id = $id;
        $this->dateAt = $dateAt;
        $this->funds = $funds;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
        $this->interests = $interests;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDateAt(): DateTime
    {
        return $this->dateAt;
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

    public function getInterests(): BookEntry
    {
        return $this->interests;
    }
}
