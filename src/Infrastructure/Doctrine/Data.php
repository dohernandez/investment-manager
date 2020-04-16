<?php

namespace App\Infrastructure\Doctrine;

use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Doctrine\DBAL\DataReferenceInterface;
use ArrayAccess;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Proxy;
use ReflectionClass;

use function get_class;
use function get_parent_class;
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
        // This is for those entities that were already decorate by doctrine with the Proxy wrapper.
        if ($this instanceof Proxy) {
            $reflect = $reflect->getParentClass();
        }

        foreach ($reflect->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);

            $data[$property->getName()] = $this->marshalValue($value);
        }

        return $data;
    }

    protected function marshalValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            return [
                'class' => get_class($value),
                'date'  => $value->format('c'),
            ];
        }

        if ($value instanceof DataInterface) {
            return [
                'class' => get_class($value),
                'data' => $value->marshalData(),
            ];
        }

        if ($value instanceof Collection || $value instanceof ArrayAccess || is_array($value)) {
            $result = [];

            foreach ($value as $k => $item) {
                $result[$k] = static::marshalValue($item);
            }

            if ($value instanceof Collection) {
                return [
                    'class' => get_class($value),
                    'items' => $result,
                ];
            }

            return $result;
        }

        return serialize($value);
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalData($data)
    {
        $reflect = new ReflectionClass(static::class);
        $args = [];

        $constructor = $reflect->getConstructor();
        if (!$constructor) {
            return new static();
        }

        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            $property = $parameter->getName();

            if (!isset($data[$property])) {
                $args[] = null;

                continue;
            }

            $args[] = static::unMarshalValue($data[$property]);
        }

        $instance = new static(...$args);

        if ($reflect->implementsInterface(DataReferenceInterface::class)) {
            foreach ($reflect->getProperties() as $property) {
                $name = $property->getName();
                if (!isset($data[$name])) {
                    continue;
                }

                $property->setAccessible(true);
                $property->setValue($instance, static::unMarshalValue($data[$name]));
            }
        }

        return $instance;
    }

    protected static function unMarshalValue($value)
    {
        if (!is_array($value)) {
            return unserialize($value);
        }

        if (isset($value['class'])) {
            if (isset($value['date'])) {
                return new $value['class']($value['date']);
            }

            if (isset($value['data'])) {
                return call_user_func_array([$value['class'], 'unMarshalData'], [$value['data']]);
            }

            if (isset($value['reference'])) {
                return call_user_func_array([$value['class'], 'unMarshalDataReference'], [$value['reference']]);
            }

            if (isset($value['items'])) {
                $result = [];

                foreach ($value['items'] as $k => $item) {
                    $result[$k] = self::unMarshalValue($item);
                }

                return new ArrayCollection($result);
            }
        }

        $result = [];

        foreach ($value as $k => $item) {
            $result[$k] = static::unMarshalValue($item);
        }

        return $result;
    }
}
