<?php

namespace App\Domain\Market;

use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourcedAggregateRoot;
use DateTime;

class StockInfo extends AggregateRoot implements EventSourcedAggregateRoot
{
    protected function apply(Changed $changed)
    {
        // TODO: Implement apply() method.
    }

    /**
     * @var DateTime
     */
    private $createdAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
