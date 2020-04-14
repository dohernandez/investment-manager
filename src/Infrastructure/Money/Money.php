<?php

namespace App\Infrastructure\Money;

use App\Infrastructure\Doctrine\DBAL\DataInterface;
use LogicException;

final class Money implements DataInterface
{
    public const DECIMAL_SYSTEM_UNITS = 'System of Units';
    public const DECIMAL_EUROPE = 'Europe';
    public const DECIMAL_UK = 'UK';

    private const DBAL_KEY_PRECISION = 'precision';
    private const DBAL_KEY_VALUE = 'value';
    private const DBAL_KEY_CURRENCY = 'currency';

    public function __construct(Currency $currency, int $value = 0, int $precision = 2)
    {
        $this->currency = $currency;
        $this->value = $value;
        $this->precision = $precision;
    }

    /**
     * @var Currency
     */
    private $currency;

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    /**
     * @var int
     */
    private $value;

    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @var int
     */
    private $precision;

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getPreciseValue(): float
    {
        return $this->value / $this->getDivisorBy();
    }

    public function getDivisorBy(): int
    {
        if ($this->getPrecision() === 4) {
            return 10000;
        }

        return 100;
    }

    public static function fromUSDValue(?float $value, int $precision = 2): self
    {
        return new static(
            Currency::usd(),
            $value,
            $precision
        );
    }

    public static function fromEURValue(?float $value, int $precision = 2): self
    {
        return new static(
            Currency::eur(),
            $value,
            $precision
        );
    }

    public static function fromCADValue(?float $value, int $precision = 2): self
    {
        return new static(
            Currency::cad(),
            $value,
            $precision
        );
    }

    public function exchange(Currency $currency, float $rate, int $precision = 2): self
    {
        $value = $this->getPreciseValue() / $rate * $this->getDivisorBy();

        return new static($currency, $value, $precision);
    }

    public function increase(?Money $value): self
    {
        if (!$value) {
            return $this;
        }

        if (!$this->getCurrency()->equals($value->getCurrency())) {
            throw new LogicException('can not increase money value, currency are different');
        }

        if ($this->getPrecision() == $value->getPrecision()) {
            $value = $this->getValue() + $value->getValue();
        } else {
            $value = ($this->getPreciseValue() + $value->getPreciseValue()) * $this->getDivisorBy();
        }

        return new static($this->getCurrency(), $value, $this->getPrecision());
    }

    public function decrease(?Money $value): self
    {
        if (!$value) {
            return $this;
        }

        if (!$this->getCurrency()->equals($value->getCurrency())) {
            throw new LogicException('can not decrease money value, currency are different');
        }

        if ($this->getPrecision() == $value->getPrecision()) {
            $value = $this->getValue() - $value->getValue();
        } else {
            $value = ($this->getPreciseValue() - $value->getPreciseValue()) * $this->getDivisorBy();
        }

        return new static($this->getCurrency(), $value, $this->getPrecision());
    }

    public function __toString(): string
    {
        switch ($this->getCurrency()->getCurrencyCode()) {
            case Currency::CURRENCY_CODE_CAD:
            {
                $toString = sprintf(
                    '%.2f %s',
                    $this->getPreciseValue(),
                    $this->getCurrency()->getSymbol()
                );

                break;
            }
            default:
            {
                $toString = sprintf(
                    '%s %.2f',
                    $this->getCurrency()->getSymbol(),
                    $this->getPreciseValue()
                );
            }
        }

        return $toString;
    }

    public function multiply(float $value): self
    {
        if (!$value) {
            return $this;
        }

        return new static(
            $this->getCurrency(),
            $this->getValue() * $value,
            $this->getPrecision()
        );
    }

    public function divide(float $value): self
    {
        if (!$value) {
            return $this;
        }

        return new static(
            $this->getCurrency(),
            $this->getValue() / $value,
            $this->getPrecision()
        );
    }

    public static function parser(string $price, int $divisor = 100, string $decSystem = self::DECIMAL_UK): float
    {
        $price = str_replace('$', '', $price);
        switch ($decSystem) {
            case self::DECIMAL_UK:
                $price = str_replace(',', '', $price);

                break;
            case self::DECIMAL_EUROPE:
                $price = str_replace('.', '', $price);
                $price = str_replace(',', '.', $price);

                break;
            case self::DECIMAL_SYSTEM_UNITS:
                $price = str_replace(',', '.', $price);
                break;
            default:
                throw new \BadMethodCallException('decimal system not supported');
        }

        return floatval($price) * $divisor;
    }

    public function equals(Money $money): bool
    {
        if (!$this->getCurrency()->equals($money->getCurrency())) {
            return false;
        }

        return $this->getPreciseValue() === $money->getPreciseValue();
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalData($array): self
    {
        return new Money(
            Currency::unMarshalData($array[self::DBAL_KEY_CURRENCY]),
            $array[self::DBAL_KEY_VALUE],
            $array[self::DBAL_KEY_PRECISION]
        );
    }

    public function marshalData()
    {
        return [
            self::DBAL_KEY_CURRENCY  => $this->getCurrency()->marshalData(),
            self::DBAL_KEY_VALUE     => $this->getValue(),
            self::DBAL_KEY_PRECISION => $this->getPrecision(),
        ];
    }
}
