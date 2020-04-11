<?php

namespace App\Infrastructure\Doctrine\DBAL;

use App\Infrastructure\Date\Week;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class WeekType extends Type
{
    public const WEEK_TYPE = 'week';
    private const DEFAULT_LENGTH = 8;

    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Week
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        return new Week($value);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!$value instanceof Week) {
            return null;
        }

        return $value->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::WEEK_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return self::DEFAULT_LENGTH;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
