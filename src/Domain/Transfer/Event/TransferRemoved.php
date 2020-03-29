<?php

namespace App\Domain\Transfer\Event;

use App\Domain\Transfer\Account;
use App\Infrastructure\Money\Money;

final class TransferRemoved
{
    public function __construct(
        string $id,
        Account $beneficiary,
        Account $debtor,
        Money $amount
    ) {
        $this->id = $id;
        $this->beneficiary = $beneficiary;
        $this->debtor = $debtor;
        $this->amount = $amount;
    }

    /**
     * @var string
     */
    private $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @var Account
     */
    private $beneficiary;

    public function getBeneficiary(): Account
    {
        return $this->beneficiary;
    }

    /**
     * @var Account
     */
    private $debtor;

    public function getDebtor(): Account
    {
        return $this->debtor;
    }

    /**
     * @var Money
     */
    private $amount;

    /**
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }
}
