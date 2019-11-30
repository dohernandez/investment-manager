<?php

namespace App\Infrastructure\Aggregator\Metadata;

interface Value
{
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
