<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StockDividendRepository")
 * @UniqueEntity(
 *     fields={"stock", "exDate"},
 *     errorPath="exDate",
 *     message="The date is already taken."
 * )
 */
class StockDividend
{
    use TimestampableEntity;

    const STATUS = ['projected', 'announced', 'payed'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="Please enter the ex date.")
     */
    private $exDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $paymentDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $recordDate;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Choice(choices=StockDividend::STATUS, message="Choose a valid status.")
     */
    private $status;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=4)
     * @Assert\NotBlank(message="Please enter the value.")
     */
    private $value;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $changeFromPrev;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $changeFromPrevYear;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $prior12MonthsYield;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Stock", inversedBy="dividends")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stock;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExDate(): ?\DateTimeInterface
    {
        return $this->exDate;
    }

    public function setExDate(\DateTimeInterface $exDate): self
    {
        $this->exDate = $exDate;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getRecordDate(): ?\DateTimeInterface
    {
        return $this->recordDate;
    }

    public function setRecordDate(?\DateTimeInterface $recordDate): self
    {
        $this->recordDate = $recordDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getChangeFromPrev()
    {
        return $this->changeFromPrev;
    }

    public function setChangeFromPrev($changeFromPrev): self
    {
        $this->changeFromPrev = $changeFromPrev;

        return $this;
    }

    public function getChangeFromPrevYear()
    {
        return $this->changeFromPrevYear;
    }

    public function setChangeFromPrevYear($changeFromPrevYear): self
    {
        $this->changeFromPrevYear = $changeFromPrevYear;

        return $this;
    }

    public function getPrior12MonthsYield()
    {
        return $this->prior12MonthsYield;
    }

    public function setPrior12MonthsYield($prior12MonthsYield): self
    {
        $this->prior12MonthsYield = $prior12MonthsYield;

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;

        return $this;
    }
}
