<?php

namespace App\Entity;

use App\VO\Money;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 */
class Account implements Entity
{
    use TimestampableEntity;

    const TYPES = ['iban'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter the name")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter the Iban")
     * @Assert\Iban(message="Iban is not correct. Please enter a valid Iban")
     */
    private $accountNo;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter an alias")
     */
    private $alias;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Please choose a type")
     * @Assert\Choice(choices=Account::TYPES, message="Please choose a valid type")
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Broker", mappedBy="account")
     */
    private $broker;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $withdraw;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true)
     */
    private $deposit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAccountNo(): ?string
    {
        return $this->accountNo;
    }

    public function setAccountNo(string $accountNo): self
    {
        $this->accountNo = $accountNo;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return sprintf('%s - %s', $this->getName(), $this->getAccountNo());
    }

    public function getBroker(): ?Broker
    {
        return $this->broker;
    }

    public function setBroker(Broker $broker): self
    {
        $this->broker = $broker;

        // set the owning side of the relation if necessary
        if ($this !== $broker->getAccount()) {
            $broker->setAccount($this);
        }

        return $this;
    }

    public function getWithdraw(): ?float
    {
        return $this->withdraw;
    }

    public function setWithdraw(?float $withdraw): self
    {
        $this->withdraw = $withdraw;

        return $this;
    }

    public function addWithdraw(?Money $withdraw): self
    {
        $this->setWithdraw($this->getWithdraw() + $withdraw->getValue());

        $broker = $this->getBroker();
        if ($broker !== null) {
            $wallet = $broker->getWallet();

            if ($wallet !== null) {
                $wallet->subtractFunds($withdraw);
            }
        }

        return $this;
    }

    public function getDeposit(): ?float
    {
        return $this->deposit;
    }

    public function setDeposit(?float $deposit): self
    {
        $this->deposit = $deposit;

        return $this;
    }

    public function addDeposit(?Money $deposit): self
    {
        $this->setDeposit($this->getDeposit() + $deposit->getValue());

        $broker = $this->getBroker();
        if ($broker !== null) {
            $wallet = $broker->getWallet();

            if ($wallet !== null) {
                $wallet->addFunds($deposit);
            }
        }

        return $this;
    }

    public function getBalance(): ?float
    {
        $deposit = $this->getDeposit() ?? 0;
        $withdraw = $this->getWithdraw() ?? 0;

        return $deposit - $withdraw;
    }
}
