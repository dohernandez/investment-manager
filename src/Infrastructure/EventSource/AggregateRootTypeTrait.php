<?php

namespace App\Infrastructure\EventSource;

trait AggregateRootTypeTrait
{
    public function getAggregateType(): string
    {
        return self::class;
    }
}
