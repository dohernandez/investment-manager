<?php

namespace App\VO;

class WalletDividendYearMetadata extends WalletDividendMetadata
{
    const FIELD_YEAR = 'year';
    const FIELD_MONTHS = 'months';

    /** @var int */
    private $year;

    /** @var WalletDividendMonthMetadata[] */
    private $months;

    public function getYear(): ?int
    {
        return $this->year;
    }

    static public function fromYear(int $year): self
    {
        $self = new static();

        $self->year = $year;

        return $self;
    }

    /**
     * @return WalletDividendMonthMetadata[]|null
     */
    public function getMonths(): ?array
    {
        return $this->months;
    }

    public function getDividendMonth(int $month): ?WalletDividendMonthMetadata
    {
        return isset($this->months[$month]) ? $this->months[$month] : null;
    }

    public function setDividendMonth(int $month, ?WalletDividendMonthMetadata $dividendMetadata): self
    {
        $self = clone $this;

        $self->months[$month] = $dividendMetadata;

        return $self;
    }

    public function setProjected(?Money $projected): WalletDividendMetadata
    {
        $self = clone $this;

        $self->projected = $projected;

        return $self;
    }

    public function toArray(): array
    {
        $months = [];
        if (!empty($this->months)) {
            foreach ($this->months as $month) {
                $months[$month->getMonth()] = $month->toArray();
            }
        }

        return [
            self::FIELD_YEAR      => $this->getYear(),
            self::FIELD_PROJECTED => $this->getProjected() ? $this->getProjected()->toArray() : null,
            self::FIELD_PAID      => $this->getPaid() ? $this->getPaid()->toArray() : null,
            self::FIELD_MONTHS    => $months,
        ];
    }

    static public function fromArray(array $dividendYear): self
    {
        $self = new static();

        $self->year = $dividendYear[self::FIELD_YEAR];
        $self->projected = $dividendYear[self::FIELD_PROJECTED] ? Money::fromArray($dividendYear[self::FIELD_PROJECTED]) : null;
        $self->paid = $dividendYear[self::FIELD_PAID] ? Money::fromArray($dividendYear[self::FIELD_PAID]) : null;

        $months = null;
        if (!empty($dividendYear[self::FIELD_MONTHS])) {
            foreach ($dividendYear[self::FIELD_MONTHS] as $month => $dividendMonth) {
                $months[$month] = WalletDividendMonthMetadata::fromArray($dividendMonth);
            }
        }
        $self->months = $months;

        return $self;
    }
}
