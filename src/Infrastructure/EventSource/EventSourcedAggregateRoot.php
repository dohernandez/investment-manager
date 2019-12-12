<?php

namespace App\Infrastructure\EventSource;

interface EventSourcedAggregateRoot
{
    /**
     * @param Changed[] $changes
     *
     * @return self
     */
    public function replay(array $changes);
}
