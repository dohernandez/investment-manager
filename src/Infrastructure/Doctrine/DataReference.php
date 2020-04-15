<?php

namespace App\Infrastructure\Doctrine;

use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Doctrine\DBAL\DataReferenceInterface;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use ArrayAccess;
use DateTimeInterface;
use Doctrine\Common\Persistence\Proxy;
use ReflectionClass;

use function get_class;
use function get_parent_class;
use function is_array;
use function serialize;
use function unserialize;

trait DataReference
{
    /**
     * @inheritDoc
     */
    public function marshalDataReference()
    {
        return [
            'id' => serialize($this->getId()),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalDataReference($data)
    {
        if (!$data) {
            return null;
        }

        return new static(unserialize($data['id']));
    }
}
