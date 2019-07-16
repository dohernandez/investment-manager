<?php

namespace App\VO;

class WalletDividendMetadata
{
    const FIELD_YEAR = 'year';
    const FIELD_PROJECTED = 'projected';
    const FIELD_PAID = 'paid';

    /** @var int */
    private $year;

    /** @var Money */
    private $projected;

    /** @var Money */
    private $paid;

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

    public function getProjected(): ?Money
    {
        return $this->projected;
    }

    public function setProjected(?Money $projected): self
    {
        $self = clone $this;

        $self->projected = $projected;

        return $self;
    }

    private function bind(self &$self)
    {
        $self->year = $this->year;
        $self->projected = $this->projected;
        $self->paid = $this->paid;
    }

    public function getPaid(): ?Money
    {
        return $this->paid;
    }

    public function setPaid(?Money $paid): self
    {
        $self = clone $this;

        $self->paid = $paid;

        return $self;
    }

    public function increasePaid(Money $money): self
    {
        $paid = $this->getPaid();

        if ($paid === null) {
            $paid = Money::fromCurrency($money->getCurrency());
        }

        return $this->setPaid($paid->increase($money));
    }

    public function toArray(): array
    {
        return [
            self::FIELD_YEAR      => $this->getYear(),
            self::FIELD_PROJECTED => $this->getProjected() ? $this->getProjected()->toArray() : null,
            self::FIELD_PAID => $this->getPaid() ? $this->getPaid()->toArray() : null,
        ];
    }

    static public function fromArray(array $dividendYear): self
    {
        $self = new static();

        $self->year = $dividendYear[self::FIELD_YEAR];
        $self->projected = $dividendYear[self::FIELD_PROJECTED] ? Money::fromArray($dividendYear[self::FIELD_PROJECTED]) : null;
        $self->paid = $dividendYear[self::FIELD_PAID] ? Money::fromArray($dividendYear[self::FIELD_PAID]) : null;

        return $self;
    }
}
