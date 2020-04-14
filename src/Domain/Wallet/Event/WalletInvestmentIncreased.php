<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\WalletBook;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use ReflectionClass;

final class WalletInvestmentIncreased extends WalletInvestmentIncreasedDecreased implements DataInterface
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
