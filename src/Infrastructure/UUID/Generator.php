<?php

namespace App\Infrastructure\UUID;

final class Generator
{
    public static function generate(): string
    {
        return uniqid();
    }
}
