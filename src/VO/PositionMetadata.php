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
        return $this->dividendYield;
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
        $self->dividendYield = isset($metadata[self::FIELD_DIVIDEND]) ? $metadata[self::FIELD_DIVIDEND] : null;

        return $self;
    }
}
