<?php

namespace App\Application\Transfer\Repository;

use App\Domain\Transfer\Transfer;

interface TransferRepositoryInterface
{
    public function find(string $id): Transfer;

    public function save(Transfer $transfer);

    public function delete(Transfer $transfer);
}
