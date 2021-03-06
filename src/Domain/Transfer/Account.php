<?php

namespace App\Domain\Transfer;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;

final class Account implements DataInterface
{
    use Data;

    public function __construct(string $id, string $name, string $accountNo)
    {
        $this->id = $id;
        $this->name = $name;
        $this->accountNo = $accountNo;
    }

    /** @var string */
    private $id;

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $accountNo;

    public function getAccountNo(): string
    {
        return $this->accountNo;
    }

    public function getTitle()
    {
        return sprintf('%s - %s', $this->getName(), $this->getAccountNo());
    }
}
