<?php

namespace App\Infrastructure\EventSource;

interface EventSourcedAggregateRoot
{
    /**
     * @param string $id
     * @param Changed[] $changes
     *
     * @return self
     */
    public static function reconstitute(string $id, array $changes);
}
