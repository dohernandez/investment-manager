<?php

namespace App\VO;

final class Money
{
    const FIELD_CURRENCY = 'currency';
    const FIELD_VALUE = 'value';

    private $currency;

    private $value;

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(?float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function toArray(): array
    {
        return [
            self::FIELD_CURRENCY => $this->getCurrency()->toArray(),
            self::FIELD_VALUE => $this->getValue(),
        ];
    }

    static public function fromArray(array $money): self
    {
        $self = new static();

        $self->setCurrency(Currency::fromArray($money[self::FIELD_CURRENCY]));
        $self->setValue($money[self::FIELD_VALUE]);

        return $self;
    }

    static public function fromUSDValue(?float $value): self
    {
        $self = new static();

        $self->setCurrency(Currency::usd());
        $self->setValue($value);

        return $self;
    }
}
