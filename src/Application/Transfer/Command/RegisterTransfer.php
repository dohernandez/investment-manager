<?php

namespace App\Application\Transfer\Command;

use App\Infrastructure\Money\Money;
use DateTime;

final class RegisterTransfer
{
    public function __construct(string $beneficiary, string $debtor, Money $amount, DateTime $date)
    {
        $this->beneficiary = $beneficiary;
        $this->debtor = $debtor;
        $this->amount = $amount;
        $this->date = $date;
    }

    /**
     * @var string
     */
    private $beneficiary;

    public function getBeneficiary(): string
    {
        return $this->beneficiary;
    }

    /**
     * @var string
     */
    private $debtor;

    public function getDebtor(): string
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
