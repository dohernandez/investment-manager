<?php

namespace App\Domain\Report\Weekly;

use App\Infrastructure\Doctrine\DBAL\DomainInterface;
use App\Infrastructure\Money\Money;

final class Stock implements DomainInterface
{
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

    /**
     * @var float
     */
    private $weeklyReturn;

    /**
     * @var float
     */
    private $yearReturn;

    public function __construct(string $name, string $symbol, Money $price, float $weeklyReturn, float $yearReturn)
    {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
        $this->weeklyReturn = $weeklyReturn;
        $this->yearReturn = $yearReturn;
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

    public function getWeeklyReturn(): float
    {
        return $this->weeklyReturn;
    }

    public function getYearReturn(): float
    {
        return $this->yearReturn;
    }

    public function marshalDBAL(): array
    {
        return [
            'name'         => $this->name,
            'symbol'       => $this->symbol,
            'price'        => $this->price,
            'weeklyReturn' => $this->weeklyReturn,
            'yearReturn'   => $this->yearReturn,
        ];
    }

    public static function unMarshalDBAL(array $value)
    {
        return new static(
            $value['name'],
            $value['symbol'],
            $value['price'],
            $value['weeklyReturn'],
            $value['yearReturn']
        );
    }
}
