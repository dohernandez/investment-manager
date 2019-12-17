<?php

namespace App\Application\Trasnfer\Repository;

use App\Domain\Transfer\Transfer;

interface ProjectionTransferRepositoryInterface
{
    /**
     * @return Transfer[] The objects.
     */
    public function findAll();
}
