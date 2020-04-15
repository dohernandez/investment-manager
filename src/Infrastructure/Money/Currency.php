<?php

namespace App\Infrastructure\Money;

use App\Infrastructure\Doctrine\DBAL\DataInterface;

final class Currency implements DataInterface
{
    public const CURRENCY_CODE_USD = 'USD';
    public const CURRENCY_SYMBOL_USD = '$';

    public const CURRENCY_CODE_EUR = 'EUR';
    public const CURRENCY_SYMBOL_EUR = '€';

    public const CURRENCY_CODE_CAD = 'CAD';
    public const CURRENCY_SYMBOL_CAD = 'C$';

    /**
     * @var string
     */
    private $symbol;

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    /**
     * @var string
     */
    private $currencyCode;

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public static function fromCode(string $code): self
    {
        $self = new static();

        switch ($code) {
            case self::CURRENCY_CODE_USD:
                $self->symbol = self::CURRENCY_SYMBOL_USD;
                $self->currencyCode = self::CURRENCY_CODE_USD;
                break;
            case self::CURRENCY_CODE_EUR:
                $self->symbol = self::CURRENCY_SYMBOL_EUR;
                $self->currencyCode = self::CURRENCY_CODE_EUR;
                break;
            case self::CURRENCY_CODE_CAD:
                $self->symbol = self::CURRENCY_SYMBOL_CAD;
                $self->currencyCode = self::CURRENCY_CODE_CAD;
                break;
            default:
                throw new \LogicException('currency not supported');
        }

        return $self;
    }

    public static function usd(): self
    {
        $self = new static();

        $self->symbol = self::CURRENCY_SYMBOL_USD;
        $self->currencyCode = self::CURRENCY_CODE_USD;

        return $self;
    }

    public static function eur(): self
    {
        $self = new static();

        $self->symbol = self::CURRENCY_SYMBOL_EUR;
        $self->currencyCode = self::CURRENCY_CODE_EUR;

        return $self;
    }

    public static function cad(): self
    {
        $self = new static();

        $self->symbol = self::CURRENCY_SYMBOL_CAD;
        $self->currencyCode = self::CURRENCY_CODE_CAD;

        return $self;
    }

    public function equals(Currency $currency): bool
    {
        return $this->getCurrencyCode() === $currency->getCurrencyCode();
    }

    /**
     * Returns the key to used to get the rate of exchange.
     *
     * @param Currency $currency Currency wanna be exchanged.
     *
     * @return string
     */
    public function getPaarExchangeRate(Currency $currency): string
    {
        return $this->getCurrencyCode() . '_' . $currency->getCurrencyCode();
    }

    public function __toString(): string
    {
        return $this->getCurrencyCode();
    }

    /**
     * @inheritDoc
     */
    public function marshalData()
    {
        return $this->currencyCode;
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalData($data)
    {
        return self::fromCode($data);
    }
}
