<?php

namespace App\Entity;

use App\VO\Money;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransferRepository")
 */
class Transfer implements Entity
{
    use TimestampableEntity;

    /**
     * @var integer
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="Please choose a beneficiary party")
     *
     */
    private $beneficiaryParty;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="Please choose a debtor party")
     */
    private $debtorParty;

    /**
     * @var Money
     *
     * @ORM\Column(type="money")
     * @Assert\NotBlank(message="Please enter the amount")
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Assert\NotNull(message="Please enter the date")
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBeneficiaryParty(): ?Account
    {
        return $this->beneficiaryParty;
    }

    public function setBeneficiaryParty(?Account $beneficiaryParty): self
    {
        $this->beneficiaryParty = $beneficiaryParty;

        return $this;
    }

    public function getDebtorParty(): ?Account
    {
        return $this->debtorParty;
    }

    public function setDebtorParty(?Account $debtorParty): self
    {
        $this->debtorParty = $debtorParty;

        return $this;
    }

    public function getAmount(): ?Money
    {
        return $this->amount;
    }

    public function setAmount(?Money $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string Entity string
     */
    public function __toString(): string
    {
        return sprintf('%s [%s]', $this->getDebtorParty(), $this->getAmount()->getValue());
    }
}
