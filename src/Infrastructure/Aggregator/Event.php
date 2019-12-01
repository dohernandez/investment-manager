<?php

namespace App\Infrastructure\Aggregator;

interface Event
{
    public function getName(): string;

    /**
     * @return array Array with format ['class' => ..., 'context' => ...]
     */
    public function serialize(): array;

    /**
     * @param array $value
     *
     * @return mixed
     */
    static public function deserialize(array $value);
}
