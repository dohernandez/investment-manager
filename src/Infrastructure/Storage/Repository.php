<?php

namespace App\Infrastructure\Storage;

use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\Storage\Market\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;

abstract class Repository
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EventSourceRepositoryInterface
     */
    protected $eventSource;

    public function __construct(EntityManagerInterface $em, EventSourceRepositoryInterface $eventSource)
    {
        $this->em = $em;
        $this->eventSource = $eventSource;
    }

    protected function mergeChanges(array $changes, array $properties): StockRepository
    {
        /** @var Changed $change */
        foreach ($changes as $change) {
            $payload = $change->getPayload();

            foreach ($properties as $property) {
                if (property_exists($payload, $property)) {
                    $reflectionClass = new ReflectionClass(get_class($payload));
                    $reflectionProperty = $reflectionClass->getProperty($property);
                    $reflectionProperty->setAccessible(true);

                    $value = $reflectionProperty->getValue($payload);
                    if ($value) {
                        $value = $this->em->merge($value);

                        $reflectionProperty->setValue($payload, $value);
                    }
                }
            }
        }

        return $this;
    }
}
