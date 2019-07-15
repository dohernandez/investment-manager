<?php

namespace App\VO;

final class DividendYear
{
    const FIELD_YEAR = 'year';
    const FIELD_PROJECTED = 'projected';
    const FIELD_RECORDED = 'recorded';
    const FIELD_PAID = 'paid';
    const FIELD_PENDING_TO_PAID = 'pending_to_paid';

    /** @var int */
    private $year;

    /** @var Money */
    private $projected;

    /** @var Money */
    private $recorded;

    /** @var Money */
    private $paid;

    /** @var \DateTimeImmutable[] */
    private $pendingToPaid = [];

    public function getYear(): int
    {
        return $this->year;
    }

    public function getProjected(): Money
    {
        return $this->projected;
    }

    public function getRecorded(): Money
    {
        return $this->recorded;
    }

    public function getPaid(): Money
    {
        return $this->paid;
    }

    /**
     * @return \DateTimeImmutable[]
     */
    public function getPendingToPaid(): array
    {
        return $this->pendingToPaid;
    }

    public static function fromYearCurrency(int $year, Currency $currency): self
    {
        $self = new static();

        $self->year = $year;
        $self->projected = Money::fromCurrency($currency);
        $self->recorded = Money::fromCurrency($currency);
        $self->paid = Money::fromCurrency($currency);

        return $self;
    }

    public function changeProjected(Money $projected): self
    {
        $self = new static();

        $self->year = $this->getYear();
        $self->projected = $projected;
        $self->recorded = $this->getRecorded();
        $self->paid = $this->getPaid();
        $self->pendingToPaid = $this->getPendingToPaid();

        return $self;
    }

    public function toArray(): array
    {
        return [
            self::FIELD_YEAR      => $this->getYear(),
            self::FIELD_PROJECTED => $this->getProjected()->toArray(),
            self::FIELD_RECORDED  => $this->getRecorded()->toArray(),
            self::FIELD_PAID  => $this->getPaid()->toArray(),
            self::FIELD_PENDING_TO_PAID  => $this->getPendingToPaid(),
        ];
    }

    static public function fromArray(array $dividendYear): self
    {
        $self = new static();

        $self->year = $dividendYear[self::FIELD_YEAR];
        $self->projected = Money::fromArray($dividendYear[self::FIELD_PROJECTED]);
        $self->recorded = Money::fromArray($dividendYear[self::FIELD_RECORDED]);
        $self->paid = Money::fromArray($dividendYear[self::FIELD_PAID]);
        $self->pendingToPaid = $dividendYear[self::FIELD_PENDING_TO_PAID];

        return $self;
    }
}
