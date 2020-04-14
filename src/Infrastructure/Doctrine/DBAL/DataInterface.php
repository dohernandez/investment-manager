<?php

namespace App\Infrastructure\Doctrine\DBAL;

interface DataInterface
{
    /**
     * @return mixed
     */
    public function marshalData();

    /**
     * @param mixed $value
     */
    public static function unMarshalData($value);
}
