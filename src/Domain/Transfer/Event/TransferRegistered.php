<?php

namespace App\Domain\Transfer\Event;

use App\Infrastructure\Money\Money;
use DateTime;

final class TransferRegistered
{
    public function __construct(
        string $id,
        string $beneficiaryParty,
        string $debtorParty,
        Money $amount,
        DateTime $date
    ) {
        $this->id = $id;
        $this->beneficiaryParty = $beneficiaryParty;
        $this->debtorParty = $debtorParty;
        $this->amount = $amount;
        $this->date = $date;
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
     * @var string
     */
    private $beneficiaryParty;

    /**
     * @return string
     */
    public function getBeneficiaryParty(): string
    {
        return $this->beneficiaryParty;
    }

    /**
     * @var string
     */
    private $debtorParty;

    /**
     * @return string
     */
    public function getDebtorParty(): string
    {
        return $this->debtorParty;
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

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }
}
