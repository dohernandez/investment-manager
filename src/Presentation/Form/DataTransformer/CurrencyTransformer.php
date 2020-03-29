<?php

namespace App\Presentation\Form\DataTransformer;

use App\Infrastructure\Money\Currency;
use Symfony\Component\Form\DataTransformerInterface;

final class CurrencyTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null) {
            return '';
        }

        if (!$value instanceof Currency) {
            throw new \LogicException('The CurrencyChoiceType can only be used with Currency objects');
        }

        return $value->getCurrencyCode();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        return Currency::fromCode($value);
    }
}
