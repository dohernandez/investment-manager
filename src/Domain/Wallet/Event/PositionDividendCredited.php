<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\BookEntry;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;

final class PositionDividendCredited implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

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
    private $dividendPaid;

    public function __construct(
        string $id,
        Money $benefits,
        float $percentageBenefits,
        BookEntry $dividendPaid
    ) {
        $this->id = $id;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
        $this->dividendPaid = $dividendPaid;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBenefits(): Money
    {
        return $this->benefits;
    }

    public function getPercentageBenefits(): float
    {
        return $this->percentageBenefits;
    }

    public function getDividendPaid(): BookEntry
    {
        return $this->dividendPaid;
    }
}
