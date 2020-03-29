<?php

namespace App\Application\Transfer\Command;

use App\Domain\Transfer\Account;
use App\Infrastructure\Money\Money;
use DateTime;

final class RegisterTransfer
{
    public function __construct(Account $beneficiary, Account $debtor, Money $amount, DateTime $date)
    {
        $this->beneficiary = $beneficiary;
        $this->debtor = $debtor;
        $this->amount = $amount;
        $this->date = $date;
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
