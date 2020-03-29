<?php

namespace App\Application\Transfer\Repository;

use App\Domain\Transfer\Transfer;

interface ProjectionTransferRepositoryInterface
{
    /**
     * @return Transfer[] The objects.
     */
    public function findAll();
}
