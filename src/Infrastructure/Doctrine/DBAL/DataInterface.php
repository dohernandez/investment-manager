<?php

namespace App\Infrastructure\Doctrine\DBAL;

interface DataInterface
{
    /**
     * @return mixed
     */
    public function marshalData();

    /**
     * @param mixed $data
     */
    public static function unMarshalData($data);
}
