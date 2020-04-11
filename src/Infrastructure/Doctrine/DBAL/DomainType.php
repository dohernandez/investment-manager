<?php

namespace App\Infrastructure\Doctrine\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

use function call_user_func_array;
use function get_class;
use function is_resource;
use function json_decode;
use function json_encode;
use function json_last_error;
use function stream_get_contents;

class DomainType extends Type
{
    public const DOMAIN_TYPE = 'domain';

    private const KEY_CLASS = 'class';
    private const KEY_DATA = 'data';

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
    public function convertToPHPValue($value, AbstractPlatform $platform)
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

        return call_user_func_array([$val[self::KEY_CLASS], 'unMarshalDBAL'], [$val[self::KEY_DATA]]);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!$value instanceof DomainInterface) {
            return null;
        }

        $encoded = json_encode(
            [
                self::KEY_CLASS => get_class($value),
                self::KEY_DATA => $value->marshalDBAL(),
            ]
        );

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ConversionException::conversionFailedSerialization($value, 'json', json_last_error_msg());
        }

        return $encoded;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::DOMAIN_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
