<?php

namespace App\Application\Account\Storage;

use App\Entity\Account;

interface Finder
{
    public function byId(string $uuid): ?Account;

    /**
     * @return Account[]
     */
    public function all(): array;

    /**
     * @param string $query
     * @param int $limit
     *
     * @return Account[]
     */
    public function allMatching(string $query, int $limit = 5): array;
}
