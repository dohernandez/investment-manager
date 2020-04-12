<?php

namespace App\Infrastructure\Doctrine\DBAL;

interface DomainInterface
{
    /**
     * @return mixed
     */
    public function marshalDBAL();

    /**
     * @param mixed $value
     */
    public static function unMarshalDBAL($value);
}
