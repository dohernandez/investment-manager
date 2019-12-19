<?php

namespace App\Application\Trasnfer\Repository;

use App\Domain\Transfer\Transfer;

interface TransferRepositoryInterface
{
    public function find(string $id): Transfer;

    public function save(Transfer $transfer);
}
