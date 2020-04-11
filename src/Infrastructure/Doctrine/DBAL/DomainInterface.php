<?php

namespace App\Infrastructure\Doctrine\DBAL;

interface DomainInterface
{
    public function marshalDBAL(): array;

    public static function unMarshalDBAL(array $value);
}
