<?php

namespace App\Infrastructure\Reflection;

use App\Infrastructure\EventSource\AggregateRoot;
use Doctrine\Common\Persistence\Proxy;
use ReflectionClass;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

use function sprintf;

final class PropertySetter
{
    /**
     * @param mixed $objectOrValue
     * @param string $name
     * @param mixed $value
     */
    public static function setValueProperty($objectOrValue, string $name, $value)
    {
        $reflect = self::getObjectReflect($objectOrValue);
        if (!$reflect->hasProperty($name)) {
            throw new NoSuchPropertyException(
                sprintf('property does not exists in the object: [%s::%s]', $name, $reflect->getName())
            );
        }

        $reflectionProperty = $reflect->getProperty($name);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectOrValue, $value);
    }

    private static function getObjectReflect($object): ReflectionClass
    {
        $reflect = new ReflectionClass(get_class($object));
        // This gets the real object, the one that the Doctrine\Common\Persistence\Proxy extends
        if ($object instanceof Proxy) {
            $reflect = $reflect->getParentClass();
        }

        return $reflect;
    }
}
