<?php

namespace App\Infrastructure\Storage\Report\Hydrate;

interface HydrateInterface
{
    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function hydrate($data);
}
