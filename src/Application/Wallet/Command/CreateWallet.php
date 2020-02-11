<?php

namespace App\Application\Wallet\Command;

use App\Domain\Wallet\Account;
use App\Domain\Wallet\Broker;

class CreateWallet
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var Account
     */
    private $account;

    public function __construct(string $name, Broker $broker, Account $account)
    {
        $this->name = $name;
        $this->broker = $broker;
        $this->account = $account;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBroker(): Broker
    {
        return $this->broker;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }
}
