<?php

namespace App\Domain\Transfer\Event;

use App\Domain\Transfer\Account;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;

final class TransferRegistered implements DataInterface
{
    use Data;

    public function __construct(
        string $id,
        Account $beneficiaryParty,
        Account $debtorParty,
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
     * @var Account
     */
    private $beneficiaryParty;

    public function getBeneficiaryParty(): Account
    {
        return $this->beneficiaryParty;
    }

    /**
     * @var Account
     */
    private $debtorParty;

    public function getDebtorParty(): Account
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
