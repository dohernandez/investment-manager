<?php

namespace App\Domain\Report\Wallet;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;

final class Stock implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var Money
     */
    private $price;

    public function __construct(string $name, string $symbol, Money $price)
    {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }
}
