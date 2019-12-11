<?php

namespace App\Infrastructure\EventSource;

interface EventSourceRepositoryInterface
{
    public function load(string $id, string $typeName, int $fromNumber = 1, int $count = null);

    public function store(AggregateRoot $aggregateRoot);
}
