<?php

namespace App\Infrastructure\EventSource;

use Doctrine\Common\Collections\ArrayCollection;

interface EventSourceRepositoryInterface
{
    /**
     * @param string $id
     * @param string $typeName
     * @param int $fromNumber
     * @param int|null $count
     *
     * @return Changed[]
     */
    public function findEvents(string $id, string $typeName, int $fromNumber = 1, int $count = null): array;

    /**
     * @param ArrayCollection $changes
     * @param bool $flush Whether to flush or not
     */
    public function saveEvents(ArrayCollection $changes, bool $flush = false);

    /**
     * Counts entities by a set of criteria.
     *
     * @param array $criteria
     *
     * @return int The cardinality of the objects that match the given criteria.
     */
    public function count(array $criteria);

    /**
     * Finds an entity by its primary key / identifier. In this case column `no` is the identifier
     *
     * @param mixed    $id          The identifier.
     * @param int|null $lockMode    One of the \Doctrine\DBAL\LockMode::* constants
     *                              or NULL if no specific lock mode should be used
     *                              during the search.
     * @param int|null $lockVersion The lock version.
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function find($id, $lockMode = null, $lockVersion = null);

    /**
     * Finds entities by a set of criteria.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
}
