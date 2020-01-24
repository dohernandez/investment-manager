<?php

namespace App\Domain\Wallet\Event;

use App\Domain\Wallet\Account;
use App\Domain\Wallet\Broker;
use App\Domain\Wallet\WalletMetadata;

final class WalletCreated
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
     * @var Broker
     */
    private $broker;

    /**
     * @var WalletMetadata
     */
    private $metadata;

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
        WalletMetadata $metadata,
        ?string $slug = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->broker = $broker;
        $this->account = $account;
        $this->metadata = $metadata;
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

    public function getMetadata(): WalletMetadata
    {
        return $this->metadata;
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
