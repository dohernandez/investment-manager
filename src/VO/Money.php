<?php

namespace App\VO;

final class Money
{
    const FIELD_CURRENCY = 'currency';
    const FIELD_VALUE = 'value';

    private $currency;

    private $value = 0;

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getValue(?int $precision = null): float
    {
        if (!$precision) {
            return $this->value;
        }

        return round($this->value, $precision);
    }

    public function setValue(?float $value): self
    {
        $this->value = $value ?? 0;

        return $this;
    }

    public function toArray(): array
    {
        return [
            self::FIELD_CURRENCY => $this->getCurrency()->toArray(),
            self::FIELD_VALUE => $this->getValue(),
        ];
    }

    static public function from(Currency $currency, float $value): self
    {
        $self = new static();

        $self->setCurrency($currency);
        $self->setValue($value);

        return $self;
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

    static public function fromEURValue(?float $value): self
    {
        $self = new static();

        $self->setCurrency(Currency::eur());
        $self->setValue($value);

        return $self;
    }

    static public function fromCurrency(Currency $currency): self
    {
        $self = new static();

        $self->setCurrency($currency);

        return $self;
    }

    public function exchange(Currency $currency, array $rateExchange): self
    {
        $exchangeKey =  $currency->getPaarExchangeRate($this->getCurrency());

        $self = new static();

        $self->setCurrency($currency);

        if (isset($rateExchange[$exchangeKey])) {
            $self->setValue($this->getValue() / $rateExchange[$exchangeKey]);
        } else {
            $self->setValue($this->getValue());
        }

        return $self;
    }

    public function increase(?Money $value): self
    {
        if (!$value) {
            return $this;
        }

        if (!$this->getCurrency()->equals($value->getCurrency())) {
            throw new \LogicException('can not increase money value, currency are different');
        }

        $self = new static();

        $self->setCurrency($this->getCurrency());
        $self->setValue($this->getValue() + $value->getValue());

        return $self;
    }

    public function decrease(?Money $value): self
    {
        if (!$value) {
            return $this;
        }

        if (!$this->getCurrency()->equals($value->getCurrency())) {
            throw new \LogicException('can not increase money value, currency are different');
        }

        $self = new static();

        $self->setCurrency($this->getCurrency());
        $self->setValue($this->getValue() - $value->getValue());

        return $self;
    }

    public function __toString(): string
    {
        switch ($this->getCurrency()->getCurrencyCode()) {
            case Currency::CURRENCY_CODE_USD: {
                $toString = sprintf('%.2f %s', $this->getValue(2), $this->getCurrency()->getSymbol());

                break;
            }
            default: {
                $toString = sprintf('%s %.2f', $this->getCurrency()->getSymbol(), $this->getValue(2));
            }
        }

        return $toString;
    }

}
