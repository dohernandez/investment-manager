<?php

namespace App\Entity;

use App\VO\Currency;
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
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $withdraw;

    /**
     * @var Money
     *
     * @ORM\Column(type="money", nullable=true)
     */
    private $deposit;

    public function __construct()
    {
        $this->deposit = (new Money())
            ->setCurrency(Currency::eur());
        $this->withdraw = (new Money())
            ->setCurrency(Currency::eur());
    }

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

    public function getWithdraw(): Money
    {
        return $this->withdraw;
    }

    public function setWithdraw(?Money $withdraw): self
    {
        if (!$withdraw) {
            $withdraw = (new Money())
                ->setCurrency($this->withdraw->getCurrency());
        }

        $this->withdraw = $withdraw;

        return $this;
    }

    public function addWithdraw(?Money $withdraw): self
    {
        if (!$withdraw) {
            return $this;
        }

        $this->setWithdraw($this->getWithdraw()->increase($withdraw));

        $broker = $this->getBroker();
        if ($broker !== null) {
            $wallet = $broker->getWallet();

            if ($wallet !== null) {
                $wallet->subtractFunds($withdraw);
            }
        }

        return $this;
    }

    public function getDeposit(): Money
    {
        return $this->deposit;
    }

    public function setDeposit(?Money $deposit): self
    {
        if (!$deposit) {
            $deposit = (new Money())
                ->setCurrency($this->deposit->getCurrency());
        }

        $this->deposit = $deposit;

        return $this;
    }

    public function addDeposit(?Money $deposit): self
    {
        if (!$deposit) {
            return $this;
        }

        $this->setDeposit($this->getDeposit()->increase($deposit));

        $broker = $this->getBroker();
        if ($broker !== null) {
            $wallet = $broker->getWallet();

            if ($wallet !== null) {
                $wallet->addFunds($deposit);
            }
        }

        return $this;
    }

    public function getBalance(): ?Money
    {
        return $this->getDeposit()->decrease($this->getWithdraw());
    }
}
