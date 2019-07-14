<?php

namespace App\VO;

final class DividendYear
{
    /** @var string */
    private $year;

    /** @var Money */
    private $dividend;

    /**
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @return Money
     */
    public function getDividend(): Money
    {
        return $this->dividend;
    }

    public static function fromYearCurrency(int $year, Currency $currency): self
    {
        $self = new static();

        $self->year = $year;
        $self->dividend = Money::fromCurrency($currency);

        return $self;
    }
}
