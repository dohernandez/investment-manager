<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Currency;

final class Market implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var Currency
     */
    private $currency;

    public function __construct(string $id, string $name, string $symbol, Currency $currency)
    {
        $this->id = $id;
        $this->name = $name;
        $this->symbol = $symbol;
        $this->currency = $currency;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getTitle(): string
    {
        return $this->getSymbol() . ' - ' . $this->getName();
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
