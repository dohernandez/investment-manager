<?php

namespace App\Application\Wallet\Handler\Output;

use App\Infrastructure\Money\Money;

final class WalletDividendStatistics
{
    /**
     * @var Money|null
     */
    private $totalYearProjected;

    /**
     * @var array|null
     */
    private $dividendYearMonthsProjected;

    /**
     * @var Money|null
     */
    private $monthYearPaid;

    /**
     * @var Money|null
     */
    private $yearPaid;

    /**
     * @var Money|null
     */
    private $totalLastYearPaid;

    /**
     * @var array|null
     */
    private $dividendLastYearMonthsPaid;

    /**
     * @var float
     */
    private $dividendYieldProjected;

    public function __construct(
        float $dividendYieldProjected = 0,
        ?Money $totalYearProjected = null,
        ?array $dividendYearMonthsProjected = [],
        ?Money $monthYearPaid = null,
        ?Money $yearPaid = null,
        ?Money $totalLastYearPaid = null,
        ?array $dividendLastYearMonthsPaid = []
    ) {
        $this->totalYearProjected = $totalYearProjected;
        $this->dividendYearMonthsProjected = $dividendYearMonthsProjected;
        $this->monthYearPaid = $monthYearPaid;
        $this->yearPaid = $yearPaid;
        $this->totalLastYearPaid = $totalLastYearPaid;
        $this->dividendLastYearMonthsPaid = $dividendLastYearMonthsPaid;
        $this->dividendYieldProjected = $dividendYieldProjected;
    }

    public function getDividendYieldProjected(): float
    {
        return $this->dividendYieldProjected;
    }

    public function getTotalYearProjected(): ?Money
    {
        return $this->totalYearProjected;
    }

    public function getDividendYearMonthsProjected(): ?array
    {
        return $this->dividendYearMonthsProjected;
    }

    public function getMonthYearPaid(): ?Money
    {
        return $this->monthYearPaid;
    }

    public function getYearPaid(): ?Money
    {
        return $this->yearPaid;
    }

    public function getTotalLastYearPaid(): ?Money
    {
        return $this->totalLastYearPaid;
    }

    public function getDividendLastYearMonthsPaid(): ?array
    {
        return $this->dividendLastYearMonthsPaid;
    }
}
