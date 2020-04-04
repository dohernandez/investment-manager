<?php

namespace App\Application\Wallet\Handler\Output;

use App\Infrastructure\Money\Money;

final class WalletDividendStatistics
{
    /**
     * @var float
     */
    private $dividendYieldProjected;

    /**
     * @var float
     */
    private $dividendYieldPaid;

    /**
     * @var Money|null
     */
    private $yearProjected;

    /**
     * @var array|null
     */
    private $dividendYearMonthsProjected;

    /**
     * @var Money|null
     */
    private $monthPaid;

    /**
     * @var Money|null
     */
    private $yearPaid;

    /**
     * @var array|null
     */
    private $dividendYearMonthsPaid;

    /**
     * @var Money|null
     */
    private $dividendLastYearPaid;

    /**
     * @var array|null
     */
    private $dividendLastYearMonthsPaid;

    public function __construct(
        float $dividendYieldProjected = 0,
        float $dividendYieldPaid = 0,
        ?Money $yearProjected = null,
        ?array $dividendYearMonthsProjected = [],
        ?Money $monthPaid = null,
        ?Money $yearPaid = null,
        ?array $dividendYearMonthsPaid = [],
        ?Money $dividendLastYearPaid = null,
        ?array $dividendLastYearMonthsPaid = []
    ) {
        $this->dividendYieldProjected = $dividendYieldProjected;
        $this->dividendYieldPaid = $dividendYieldPaid;
        $this->yearProjected = $yearProjected;
        $this->dividendYearMonthsProjected = $dividendYearMonthsProjected;
        $this->monthPaid = $monthPaid;
        $this->yearPaid = $yearPaid;
        $this->dividendYearMonthsPaid = $dividendYearMonthsPaid;
        $this->dividendLastYearPaid = $dividendLastYearPaid;
        $this->dividendLastYearMonthsPaid = $dividendLastYearMonthsPaid;
    }

    public function getDividendYieldProjected(): float
    {
        return $this->dividendYieldProjected;
    }

    public function getDividendYieldPaid(): float
    {
        return $this->dividendYieldPaid;
    }

    public function getYearProjected(): ?Money
    {
        return $this->yearProjected;
    }

    public function getDividendYearMonthsProjected(): ?array
    {
        return $this->dividendYearMonthsProjected;
    }

    public function getMonthPaid(): ?Money
    {
        return $this->monthPaid;
    }

    public function getYearPaid(): ?Money
    {
        return $this->yearPaid;
    }

    public function getDividendYearMonthsPaid(): ?array
    {
        return $this->dividendYearMonthsPaid;
    }

    public function getDividendLastYearPaid(): ?Money
    {
        return $this->dividendLastYearPaid;
    }

    public function getDividendLastYearMonthsPaid(): ?array
    {
        return $this->dividendLastYearMonthsPaid;
    }
}
