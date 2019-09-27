<?php

namespace App\Client;

interface DividendsClientInterface
{
    public function getDividends(string $stock): array;
}
