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

    public function getValue(?int $precision = null): ?float
    {
        if (!$precision) {
            return $this->value;
        }

        return round($this->value, $precision);
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

    public function exchangeToEUR(?float $exchangeRate): self
    {
        $self = new static();

        $self->setCurrency(Currency::eur());
        $self->setValue($this->getValue() / $exchangeRate);

        return $self;
    }

    public function increase(Money $value): self
    {
        if ($value->getCurrency()->getCurrencyCode() !== $this->getCurrency()->getCurrencyCode()) {
            throw new \LogicException('can not increase money value, currency are different');
        }

        $self = new static();

        $self->setCurrency($this->getCurrency());
        $self->setValue($this->getValue() + $this->getValue());

        return $self;
    }

    public function decrease(?Money $value): self
    {
        if (!$value) {
            return $this;
        }

        if ($value->getCurrency()->getCurrencyCode() !== $this->getCurrency()->getCurrencyCode()) {
            throw new \LogicException('can not increase money value, currency are different');
        }

        $self = new static();

        $self->setCurrency($this->getCurrency());
        $self->setValue($this->getValue() - $value->getValue());

        return $self;
    }
}
