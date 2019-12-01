<?php

namespace App\DBAL;

use App\Infrastructure\Aggregator\Event;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class ChangedPayloadType extends Type
{
    private const CHANGE_PAYLOAD_TYPE = Type::JSON;

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Event
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

        $payloadClass = $val['class'];

        return new $payloadClass(...$val['context']);
    }

    /**
     * @inheritdoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if (!$value instanceof Event) {
            return null;
        }

        $val = json_encode([
            'class' => get_class($value),
            'context' => $value->toArray()
        ]);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ConversionException::conversionFailedSerialization($value, 'json', json_last_error_msg());
        }

        return $val;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::CHANGE_PAYLOAD_TYPE;
    }
}
