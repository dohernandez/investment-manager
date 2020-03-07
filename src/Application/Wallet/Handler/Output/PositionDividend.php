<?php

namespace App\Application\Wallet\Handler\Output;

use App\Domain\Wallet\Stock;
use App\Infrastructure\Money\Money;
use DateTime;

final class PositionDividend
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Stock
     */
    private $stock;

    /**
     * @var Money
     */
    private $invested;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var DateTime|null
     */
    private $exDate;

    /**
     * @var string|null
     */
    private $displayDividendYield;

    /**
     * @var string|null
     */
    private $realDisplayDividendYield;

    public function __construct(
        string $id,
        Stock $stock,
        Money $invested,
        int $amount,
        ?DateTime $exDate = null,
        ?string $displayDividendYield = null,
        ?string $realDisplayDividendYield = null
    ) {
        $this->id = $id;
        $this->stock = $stock;
        $this->invested = $invested;
        $this->amount = $amount;
        $this->exDate = $exDate;
        $this->displayDividendYield = $displayDividendYield;
        $this->realDisplayDividendYield = $realDisplayDividendYield;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStock(): Stock
    {
        return $this->stock;
    }

    public function getInvested(): Money
    {
        return $this->invested;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getExDate(): ?DateTime
    {
        return $this->exDate;
    }

    public function getTitle(): string
    {
        return sprintf(
            '%s:%s - %d [%s]',
            $this->getStock()->getMarket()->getSymbol(),
            $this->getStock()->getSymbol(),
            $this->getAmount(),
            $this->realDisplayDividendYield
        );
    }

    public function getDisplayDividendYield(): ?string
    {
        return $this->displayDividendYield;
    }

    public function getRealDisplayDividendYield(): ?string
    {
        return $this->realDisplayDividendYield;
    }
}
