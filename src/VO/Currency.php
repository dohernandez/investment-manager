<?php

namespace App\VO;

final class Currency
{
    const FIELD_SYMBOL = 'symbol';
    const FIELD_CURRENCY_CODE = 'currency_code';

    const CURRENCY_CODE_USD = 'USD';
    const CURRENCY_SYMBOL_USD = '$';

    const CURRENCY_CODE_EUR = 'EUR';
    const CURRENCY_SYMBOL_EUR = '€';

    private $symbol;

    private $currencyCode;

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(string $currencyCode): self
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    public function toArray(): array
    {
        return [
            self::FIELD_SYMBOL => $this->getSymbol(),
            self::FIELD_CURRENCY_CODE => $this->getCurrencyCode(),
        ];
    }

    static public function fromArray(array $currency): self
    {
        $self = new static();

        $self->setSymbol($currency[self::FIELD_SYMBOL]);
        $self->setCurrencyCode($currency[self::FIELD_CURRENCY_CODE]);

        return $self;
    }

    static public function usd(): self
    {
        $self = new static();

        $self->setSymbol(self::CURRENCY_SYMBOL_USD);
        $self->setCurrencyCode(self::CURRENCY_CODE_USD);

        return $self;
    }

    static public function eur(): self
    {
        $self = new static();

        $self->setSymbol(self::CURRENCY_SYMBOL_EUR);
        $self->setCurrencyCode(self::CURRENCY_CODE_EUR);

        return $self;
    }
}
