<?php

namespace App\Domain\Wallet\Event;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use ReflectionClass;

final class SellOperationRegistered extends BuySellOperationRegistered implements DataInterface
{
    use Data {
        marshalData as public parentMarshalData;
    }

    public function marshalData()
    {
        $data = [];

        $reflect = (new ReflectionClass(get_class($this)))->getParentClass();

        foreach ($reflect->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);

            $data[$property->getName()] = $this->marshalValue($value);
        }

        return $data;
    }
}
