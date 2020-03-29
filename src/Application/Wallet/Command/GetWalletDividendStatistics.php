<?php

namespace App\Application\Wallet\Command;

final class GetWalletDividendStatistics
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $limit;

    public function __construct(string $id, int $limit = 3)
    {
        $this->id = $id;
        $this->limit = $limit;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
