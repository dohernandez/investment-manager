<?php

namespace App\Domain\Wallet;

final class Stock
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var Market
     */
    private $market;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $id, string $name, string $symbol, Market $market)
    {
        $this->id = $id;
        $this->symbol = $symbol;
        $this->market = $market;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getMarket(): Market
    {
        return $this->market;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return sprintf(
            '%s (%s:%s)',
            $this->getName(),
            $this->getSymbol(),
            $this->getMarket()->getSymbol()
        );
    }
}
