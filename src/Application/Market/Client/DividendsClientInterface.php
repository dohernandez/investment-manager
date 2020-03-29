<?php

namespace App\Application\Market\Client;

interface DividendsClientInterface
{
    public function getDividends(string $stock): array;
}
