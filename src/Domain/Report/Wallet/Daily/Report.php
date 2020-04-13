<?php

namespace App\Domain\Report\Wallet\Daily;

class Report
{
    /**
     * @var int|null
     */
    private $id;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }
}
