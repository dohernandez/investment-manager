<?php

namespace App\Infrastructure\Doctrine\DBAL;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

use function call_user_func_array;
use function get_class;
use function get_parent_class;
use function is_resource;
use function json_decode;
use function json_encode;
use function json_last_error;
use function stream_get_contents;

class DataType extends Type
{
    public const DATA_TYPE = 'data';

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
        if ($value === null || $value === '' || $value === 'null') {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $val = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return call_user_func_array([$val[self::KEY_CLASS], 'unMarshalData'], [$val[self::KEY_DATA]]);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value === '' || $value === 'null') {
            return null;
        }

        if (!$value instanceof DataInterface) {
            return 'null';
        }

        $class = get_class($value);
        if ($value instanceof Proxy) {
            $class = get_parent_class($value);
        }

        $encoded = json_encode(
            [
                self::KEY_CLASS => $class,
                self::KEY_DATA => $value->marshalData(),
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
        return self::DATA_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
