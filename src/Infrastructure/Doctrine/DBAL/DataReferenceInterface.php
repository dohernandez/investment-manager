<?php

namespace App\Infrastructure\Doctrine\DBAL;

interface DataReferenceInterface
{
    public function getId();

    /**
     * @return mixed
     */
    public function marshalDataReference();

    /**
     * @param mixed $data
     */
    public static function unMarshalDataReference($data);
}
