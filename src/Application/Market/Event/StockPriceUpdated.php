<?php

namespace App\Application\Market\Event;

use App\Domain\Market\StockPrice;

final class StockPriceUpdated
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var StockPrice|null
     */
    private $old;

    /**
     * @var StockPrice
     */
    private $new;

    public function __construct(string $id, StockPrice $new, ?StockPrice $old = null)
    {
        $this->id = $id;
        $this->old = $old;
        $this->new = $new;
    }

    public function getOld(): ?StockPrice
    {
        return $this->old;
    }

    public function getNew(): StockPrice
    {
        return $this->new;
    }
}
