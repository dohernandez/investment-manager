<?php

namespace App\Domain\Transfer\Event;

use App\Domain\Transfer\Account;
use App\Infrastructure\Money\Money;
use DateTime;

final class TransferChanged
{
    public function __construct(
        string $id,
        Account $newBeneficiary,
        Account $oldBeneficiary,
        Account $newDebtor,
        Account $oldDebtor,
        Money $newAmount,
        Money $oldAmount,
        DateTime $newDate,
        DateTime $oldDate
    ) {
        $this->id = $id;
        $this->newBeneficiary = $newBeneficiary;
        $this->oldBeneficiary = $oldBeneficiary;
        $this->newDebtor = $newDebtor;
        $this->oldDebtor = $oldDebtor;
        $this->newAmount = $newAmount;
        $this->oldAmount = $oldAmount;
        $this->newDate = $newDate;
        $this->oldDate = $oldDate;
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
    private $newBeneficiary;

    public function getNewBeneficiary(): Account
    {
        return $this->newBeneficiary;
    }

    /**
     * @var Account
     */
    private $oldBeneficiary;

    public function getOldBeneficiary(): Account
    {
        return $this->oldBeneficiary;
    }

    /**
     * @var Account
     */
    private $newDebtor;

    public function getNewDebtor(): Account
    {
        return $this->newDebtor;
    }

    /**
     * @var Account
     */
    private $oldDebtor;

    public function getOldDebtor(): Account
    {
        return $this->oldDebtor;
    }

    /**
     * @var Money
     */
    private $newAmount;

    /**
     * @return Money
     */
    public function getNewAmount(): Money
    {
        return $this->newAmount;
    }

    /**
     * @var Money
     */
    private $oldAmount;

    /**
     * @return Money
     */
    public function getOldAmount(): Money
    {
        return $this->oldAmount;
    }

    /**
     * @var DateTime
     */
    private $newDate;

    /**
     * @return DateTime
     */
    public function getNewDate(): DateTime
    {
        return $this->newDate;
    }

    /**
     * @var DateTime
     */
    private $oldDate;

    /**
     * @return DateTime
     */
    public function getOldDate(): DateTime
    {
        return $this->oldDate;
    }
}
