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
     */
    public function saveEvents(ArrayCollection $changes);
}
