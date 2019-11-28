<?php

namespace App\VO;

class PositionMetadata
{
    const FIELD_DIVIDEND = 'dividend';
    const FIELD_DIVIDEND_YIELD = 'dividend_yield';

    const FIELD_DIVIDEND_TO_PAY = 'dividend_to_pay';
    const FIELD_DIVIDEND_TO_PAY_YIELD = 'dividend_to_pay_yield';

    /**
     * @var Money
     */
    private $dividend;

    /**
     * @var float
     */
    private $dividendYield;

    /**
     * @var Money
     */
    private $dividendToPay;

    /**
     * @var float
     */
    private $dividendToPayYield;

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
        if ($this->dividendYield === null) {
            return 0;
        }

        return min($this->dividendYield, 100);
    }

    public function toArray(): array
    {
        return [
            self::FIELD_DIVIDEND       => $this->getDividend() ? $this->getDividend()->toArray() : null,
            self::FIELD_DIVIDEND_YIELD => $this->getDividendYield(),

            self::FIELD_DIVIDEND_TO_PAY       => $this->getDividendToPay() ? $this->getDividendToPay()->toArray() : null,
            self::FIELD_DIVIDEND_TO_PAY_YIELD => $this->getDividendToPay(),
        ];
    }

    public static function fromArray(?array $metadata): self
    {
        $self = new static();

        $self->dividend = isset($metadata[self::FIELD_DIVIDEND]) ? Money::fromArray($metadata[self::FIELD_DIVIDEND]) : null;
        $self->dividendYield = isset($metadata[self::FIELD_DIVIDEND_YIELD]) ? $metadata[self::FIELD_DIVIDEND_YIELD] : null;

        $self->dividendToPay = isset($metadata[self::FIELD_DIVIDEND_TO_PAY]) ? Money::fromArray($metadata[self::FIELD_DIVIDEND_TO_PAY]) : null;
        $self->dividendToPayYield = isset($metadata[self::FIELD_DIVIDEND_TO_PAY_YIELD]) ? $metadata[self::FIELD_DIVIDEND_TO_PAY_YIELD] : null;

        return $self;
    }

    /**
     * @return Money
     */
    public function getDividendToPay(): ?Money
    {
        return $this->dividendToPay;
    }

    public function setDividendToPayAndDividendToPayYield(?Money $dividendToPay, float $dividendToPayYield): self
    {
        $self = clone $this;

        $self->dividendToPay = $dividendToPay;
        $self->dividendToPayYield = $dividendToPayYield;

        return $self;
    }

    public function getDividendToPayYield(): float
    {
        if ($this->dividendToPayYield === null) {
            return 0;
        }

        return min($this->dividendToPayYield, 100);
    }
}
