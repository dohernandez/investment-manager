<?php

namespace App\VO;

class WalletDividendMonthMetadata extends WalletDividendMetadata
{
    const FIELD_MONTH = 'month';

    /** @var int */
    private $month;

    public function getMonth(): ?int
    {
        return $this->month;
    }

    static public function fromMonth(int $month): self
    {
        $self = new static();

        $self->month = $month;

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
        return [
            self::FIELD_MONTH      => $this->getMonth(),
            self::FIELD_PROJECTED => $this->getProjected() ? $this->getProjected()->toArray() : null,
            self::FIELD_PAID => $this->getPaid() ? $this->getPaid()->toArray() : null,
        ];
    }

    static public function fromArray(array $dividendMonth): self
    {
        $self = new static();

        $self->month = $dividendMonth[self::FIELD_MONTH];
        $self->projected = $dividendMonth[self::FIELD_PROJECTED] ? Money::fromArray($dividendMonth[self::FIELD_PROJECTED]) : null;
        $self->paid = $dividendMonth[self::FIELD_PAID] ? Money::fromArray($dividendMonth[self::FIELD_PAID]) : null;

        return $self;
    }
}
