<?php

namespace App\DBAL;

use App\VO\DividendYear;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class ArrayDividendYearType extends Type
{
    const ARRAY_DIVIDEND_YEAR_TYPE = Type::JSON;

    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }
    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $val = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        foreach ($val as $k => $dy) {
            $val[$k] = DividendYear::fromArray($dy);
        }

        return $val;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        $val = [];
        foreach ($value as $k => $item) {
            if ($item instanceof DividendYear) {
                $val[$k] = $item->toArray();
            }
        }

        $val = json_encode($val);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ConversionException::conversionFailedSerialization($value, 'json', json_last_error_msg());
        }

        return $val;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::ARRAY_DIVIDEND_YEAR_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
