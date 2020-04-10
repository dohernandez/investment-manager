<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;

use function sprintf;

final class Rate
{
    /**
     * @var Currency
     */
    private $fromCurrency;

    /**
     * @var Currency
     */
    private $toCurrency;

    /**
     * @var float
     */
    private $rate;

    public function __construct(Currency $fromCurrency, Currency $toCurrency, float $rate)
    {
        $this->fromCurrency = $fromCurrency;
        $this->toCurrency = $toCurrency;
        $this->rate = $rate;
    }

    public function getFromCurrency(): ?Currency
    {
        return $this->fromCurrency;
    }

    public function getToCurrency(): ?Currency
    {
        return $this->toCurrency;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function exchange(?Money $money): ?Money
    {
        if (!$money) {
            return null;
        }

        return $money->exchange(
            $this->getToCurrency(),
            $this->getRate(),
            $money->getPrecision()
        );
    }

    public function __toString(): string
    {
        return sprintf(
            '%s_%s: %s',
            $this->getFromCurrency(),
            $this->getToCurrency(),
            $this->getRate()
        );
    }
}
