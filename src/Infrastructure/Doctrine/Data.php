<?php

namespace App\Infrastructure\Doctrine;

use ArrayAccess;
use DateTimeInterface;
use ReflectionClass;

use function is_array;
use function serialize;
use function unserialize;

trait Data
{
    /**
     * @inheritDoc
     */
    public function marshalData()
    {
        $data = [];

        $reflect = new ReflectionClass(get_class($this));

        foreach ($reflect->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);

            $data[$property->getName()] = $this->marshalValue($value);
        }

        return $data;
    }

    private function marshalValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            return [
                'class' => \get_class($value),
                'date' => $value->format('c'),
            ];
        }

        if ($value && method_exists($value, 'getId')) {
            $class = \get_class($value);

            return [
                'class' => $class,
                'id' => $value->getId(),
            ];
        }

        if (is_array($value) || $value instanceof ArrayAccess) {
            $result = [];

            foreach ($value as $k => $item) {
                $result[$k] = $this->marshalValue($item);
            }

            return $result;
        }

        return serialize($value);
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalData($value)
    {
        $reflect = new ReflectionClass(static::class);
        $args = [];

        $parameters = $reflect->getConstructor()->getParameters();
        foreach ($parameters as $parameter) {
            $property = $parameter->getName();

            $args[] = static::unMarshalValue($value[$property]);
        }

        return new static(...$args);
    }

    private static function unMarshalValue($value)
    {
        if (isset($value['class']) && isset($value['date'])) {
            return new $value['class']($value['date']);
        }

        if (isset($value['class']) && isset($value['id'])) {
            return new $value['class']($value['id']);
        }

        if (is_array($value) || $value instanceof ArrayAccess) {
            $result = [];

            foreach ($value as $k => $item) {
                $result[$k] = static::unMarshalValue($item);
            }

            return $result;
        }

        return unserialize($value);
    }
}
