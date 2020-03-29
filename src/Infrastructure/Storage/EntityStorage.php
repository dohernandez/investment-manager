<?php

namespace App\Infrastructure\Storage;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class EntityStorage
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function getServiceEntityRepository(string $entityClass)
    {
        return new ServiceEntityRepository($this->registry, $entityClass);
    }
}
