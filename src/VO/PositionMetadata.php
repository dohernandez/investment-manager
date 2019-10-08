<?php

namespace App\VO;

class PositionMetadata
{
    const FIELD_DIVIDEND = 'dividend';
    const FIELD_DIVIDEND_YIELD = 'dividend_yield';

    /**
     * @var Money
     */
    private $dividend;

    /**
     * @var float
     */
    private $dividendYield;

    /**
     * @return Money
     */
    public function getDividend(): ?Money
    {
        return $this->dividend;
    }

    public function setDividendAndDividendYield(?Money $dividendProjected, float $dividendYieldProjected): self
    {
        $self = clone $this;

        $self->dividend = $dividendProjected;
        $self->dividendYield = $dividendYieldProjected;

        return $self;
    }

    public function getDividendYield(): float
    {
        return min($this->dividendYield, 100);
    }

    public function toArray(): array
    {
        return [
            self::FIELD_DIVIDEND       => $this->getDividend() ? $this->getDividend()->toArray() : null,
            self::FIELD_DIVIDEND_YIELD => $this->getDividendYield(),
        ];
    }

    public static function fromArray(?array $metadata): self
    {
        $self = new static();

        $self->dividend = isset($metadata[self::FIELD_DIVIDEND]) ? Money::fromArray($metadata[self::FIELD_DIVIDEND]) : null;
        $self->dividendYield = isset($metadata[self::FIELD_DIVIDEND_YIELD]) ? $metadata[self::FIELD_DIVIDEND_YIELD] : null;

        return $self;
    }
}
