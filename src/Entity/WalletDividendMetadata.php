<?php

namespace App\Entity;

use App\VO\Money;

class WalletDividendMetadata
{
    const FIELD_YEAR = 'year';
    const FIELD_PROJECTED = 'projected';
    const FIELD_RECORDED = 'recorded';
    const FIELD_PAID = 'paid';

    /** @var int */
    private $year;

    /** @var Money */
    private $projected;

    /** @var Money */
    private $recorded;

    /** @var Money */
    private $paid;

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getProjected(): ?Money
    {
        return $this->projected;
    }

    public function setProjected(?Money $projected): self
    {
        $this->projected = $projected;

        return $this;
    }

    public function getRecorded(): ?Money
    {
        return $this->recorded;
    }

    public function setRecorded(?Money $recorded): self
    {
        $this->recorded = $recorded;

        return $this;
    }

    public function getPaid(): ?Money
    {
        return $this->paid;
    }

    public function setPaid(?Money $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function toArray(): array
    {
        return [
            self::FIELD_YEAR      => $this->getYear(),
            self::FIELD_PROJECTED => $this->getProjected() ? $this->getProjected()->toArray() : [],
            self::FIELD_RECORDED  => $this->getRecorded() ? $this->getRecorded()->toArray() : [],
            self::FIELD_PAID => $this->getPaid() ? $this->getPaid()->toArray() : [],
        ];
    }

    static public function fromArray(array $dividendYear): self
    {
        $self = new static();

        $self->year = $dividendYear[self::FIELD_YEAR];
        $self->projected = empty($dividendYear[self::FIELD_PROJECTED]) ? Money::fromArray($dividendYear[self::FIELD_PROJECTED]) : null;
        $self->recorded = empty($dividendYear[self::FIELD_RECORDED]) ? Money::fromArray($dividendYear[self::FIELD_RECORDED]) : null;
        $self->paid = empty($dividendYear[self::FIELD_PAID]) ? Money::fromArray($dividendYear[self::FIELD_PAID]) : null;

        return $self;
    }
}
