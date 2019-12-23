<?php

namespace App\Presentation\Form\DataMapper;

use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;

final class MoneyMapper extends MoneyToLocalizedStringTransformer
{
    private $precision;

    private $currency;

    public function __construct(?int $scale = 2, ?bool $grouping = true, ?int $roundingMode = self::ROUND_HALF_UP, ?int $divisor = 1, ?string $currency = 'EUR', ?int $precision = 2)
    {
        parent::__construct($scale, $grouping, $roundingMode, $divisor);

        if (null === $precision) {
            $precision = 2;
        }

        if (null === $currency) {
            $currency = 'EUR';
        }

        $this->precision = $precision;
        $this->currency = $currency;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null) {
            return '';
        }

        if (!$value instanceof Money) {
            throw new \LogicException('The MoneyChoiceType can only be used with Money objects');
        }

        return parent::transform($value->getPreciseValue());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        $value = parent::reverseTransform($value);

        return new Money(
            Currency::fromCode($this->currency),
            $value,
            $this->precision
        );
    }
}
