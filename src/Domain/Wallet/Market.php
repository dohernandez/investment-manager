<?php

namespace App\Domain\Wallet;

final class Market
{
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

    public function __construct(string $id, string $name, string $symbol)
    {
        $this->id = $id;
        $this->name = $name;
        $this->symbol = $symbol;
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
}
