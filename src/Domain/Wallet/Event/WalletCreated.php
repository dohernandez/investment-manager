<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Account;
use App\Domain\Wallet\Broker;
use App\Domain\Wallet\WalletBook;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;

final class WalletCreated implements DataInterface
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
     * @var Broker
     */
    private $broker;

    /**
     * @var WalletBook
     */
    private $book;

    /**
     * @var string|null
     */
    private $slug;

    /**
     * @var Account
     */
    private $account;

    public function __construct(
        string $id,
        string $name,
        Broker $broker,
        Account $account,
        WalletBook $book,
        ?string $slug = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->broker = $broker;
        $this->account = $account;
        $this->book = $book;
        $this->slug = $slug;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBroker(): Broker
    {
        return $this->broker;
    }

    public function getBook(): WalletBook
    {
        return $this->book;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }
}
