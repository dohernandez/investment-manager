<?php

namespace App\Application\Transfer\Command;

use App\Domain\Transfer\Account;
use App\Infrastructure\Money\Money;
use DateTime;

final class ChangeTransfer
{
    public function __construct(string $id, Account $beneficiary, Account $debtor, Money $amount, DateTime $date)
    {
        $this->id = $id;
        $this->beneficiary = $beneficiary;
        $this->debtor = $debtor;
        $this->amount = $amount;
        $this->date = $date;
    }

    /**
     * @var string
     */
    private $id;

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

    public function getAmount(): Money
    {
        return $this->amount;
    }

    /**
     * @var DateTime
     */
    private $date;

    public function getDate(): DateTime
    {
        return $this->date;
    }
}
