<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\PositionBook;
use App\Domain\Wallet\Stock;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use DateTime;

final class PositionOpened implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Wallet
     */
    private $wallet;

    /**
     * @var DateTime
     */
    private $openedAt;

    /**
     * @var Stock
     */
    private $stock;

    /**
     * @var PositionBook
     */
    private $book;

    public function __construct(string $id, Wallet $wallet, Stock $stock, DateTime $openedAt, PositionBook $book)
    {
        $this->id = $id;
        $this->wallet = $wallet;
        $this->openedAt = $openedAt;
        $this->stock = $stock;
        $this->book = $book;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getOpenedAt(): DateTime
    {
        return $this->openedAt;
    }

    public function getStock(): Stock
    {
        return $this->stock;
    }

    public function getBook(): PositionBook
    {
        return $this->book;
    }
}
