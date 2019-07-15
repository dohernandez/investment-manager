<?php

namespace App\VO;

use App\Entity\Exchange;
use Symfony\Component\Serializer\Annotation\SerializedName;

final class Money
{
    const FIELD_CURRENCY = 'currency';
    const FIELD_VALUE = 'value';
    const FIELD_PRECISION = 'precision';

    private $currency;

    private $value = 0;

    private $precision = 2;

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @SerializedName("preciseValue")
     */
    public function getPreciseValue(): float
    {
        return $this->value / $this->getDivisorBy();
    }

    public function getDivisorBy(): int
    {
        if ($this->getPrecision() === 4) {
            return 1000;
        }

        return 100;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(?int $value): self
    {
        $this->value = $value ?? 0;

        return $this;
    }

    public function toArray(): array
    {
        return [
            self::FIELD_CURRENCY => $this->getCurrency()->toArray(),
            self::FIELD_VALUE => $this->getValue(),
            self::FIELD_PRECISION => $this->getPrecision(),
        ];
    }

    static public function from(Currency $currency, int $value, int $precision = 2): self
    {
        $self = new static();

        $self->setCurrency($currency);
        $self->setValue($value);
        $self->setPrecision($precision);

        return $self;
    }

    static public function fromArray(array $money): self
    {
        $self = new static();

        $self->setCurrency(Currency::fromArray($money[self::FIELD_CURRENCY]));
        $self->setValue($money[self::FIELD_VALUE]);

        // supporting legacy data
        if (isset($money[self::FIELD_PRECISION])) {
            $self->setPrecision($money[self::FIELD_PRECISION]);
        }

        return $self;
    }

    static public function fromUSDValue(?float $value, int $precision = 2): self
    {
        $self = new static();

        $self->setCurrency(Currency::usd());
        $self->setValue($value);
        $self->setPrecision($precision);

        return $self;
    }

    static public function fromEURValue(?float $value, int $precision = 2): self
    {
        $self = new static();

        $self->setCurrency(Currency::eur());
        $self->setValue($value);
        $self->setPrecision($precision);

        return $self;
    }

    static public function fromCurrency(Currency $currency, int $precision = 2): self
    {
        $self = new static();

        $self->setCurrency($currency);
        $self->setPrecision($precision);

        return $self;
    }

    public function exchange(Currency $currency, array $exchangeRates, int $precision = 2): self
    {
        $exchangePaar =  $currency->getPaarExchangeRate($this->getCurrency());

        $self = new static();

        $self->setCurrency($currency);
        $self->setPrecision($precision);

        $exists = false;
        /** @var Exchange $rate */
        foreach ($exchangeRates as $rate) {
            if ($rate->getPaarCurrency() == $exchangePaar) {
                $self->setValue($this->getPreciseValue() / $rate->getRate() * $this->getDivisorBy());

                $exists = true;
                break;
            }
        }

        if (!$exists) {
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
            case Currency::CURRENCY_CODE_CAD: {
                $toString = sprintf(
                    '%.2f %s',
                    $this->getPreciseValue(),
                    $this->getCurrency()->getSymbol()
                );

                break;
            }
            default: {
                $toString = sprintf(
                    '%s %.2f',
                    $this->getCurrency()->getSymbol(),
                    $this->getPreciseValue()
                );
            }
        }

        return $toString;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function setPrecision(int $precision): void
    {
        $this->precision = $precision;
    }

    public function multiply(float $value): self
    {
        if (!$value) {
            return $this;
        }

        $self = new static();

        $self->setCurrency($this->getCurrency());
        $self->setValue($this->getValue() * $value);

        return $self;
    }
}
