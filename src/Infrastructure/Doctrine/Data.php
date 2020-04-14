<?php

namespace App\Infrastructure\Doctrine;

use App\Infrastructure\Doctrine\DBAL\DataInterface;
use ArrayAccess;
use DateTimeInterface;
use Doctrine\Common\Persistence\Proxy;
use ReflectionClass;

use function get_class;
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

//            \dump(get_class($this), $property, $value);
            $data[$property->getName()] = $this->marshalValue($value);
        }

        return $data;
    }

    protected function marshalValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            return [
                'class' => get_class($value),
                'date' => $value->format('c'),
            ];
        }

        if ($value instanceof DataInterface) {
            return [
                'class' => get_class($value),
                'data' => $value->marshalData(),
            ];
        }

        // TODO this has to be replace by the used of interface
        if ($value && method_exists($value, 'getId')) {
            return [
                'class' => get_class($value),
                'id' => serialize($value->getId()),
            ];
        }

        if ($value instanceof ArrayAccess) {
            $result = [];

            foreach ($value as $k => $item) {
//                \dump('calling marshalValue again');
                $result[$k] = static::marshalValue($item);
            }

            return [
                'class' => get_class($value),
                'items' => $result,
            ];
        }

        if (is_array($value)) {
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

            if (!isset($value[$property])) {
                $args[] = null;

                continue;
            }

//            \dump(static::class, $property);
            $args[] = static::unMarshalValue($value[$property]);
        }

        return new static(...$args);
    }

    protected static function unMarshalValue($value)
    {
        if ($value instanceof ArrayAccess) {
            if (isset($value['class']) && isset($value['items'])) {
                $result = [];

                foreach ($value['items'] as $k => $item) {
                    $result[$k] = static::unMarshalValue($item);
                }

                return new $value['class']($result);
            }
        }

        if (!is_array($value)) {
            return unserialize($value);
        }

        if (isset($value['class']) && isset($value['date'])) {
            return new $value['class']($value['date']);
        }

        if (isset($value['class']) && isset($value['data'])) {
            return call_user_func_array([$value['class'], 'unMarshalData'], [$value['data']]);
        }

        if (isset($value['class']) && isset($value['id'])) {
            return new $value['class'](unserialize($value['id']));
        }

        $result = [];

        foreach ($value as $k => $item) {
            $result[$k] = static::unMarshalValue($item);
        }

        return $result;
    }
}
