<?php

namespace App\Domain\Market;

use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use DateTime;

class StockMarketPrice
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Money|null
     */
    private $price;

    /**
     * @var Money|null
     */
    private $changePrice;

    /**
     * @var float|null
     */
    private $changePercentage;

    /**
     * @var Money|null
     */
    private $preClose;

    /**
     * @var Money|null
     */
    private $open;

    /**
     * @var Money|null
     */
    private $dayLow;

    /**
     * @var Money|null
     */
    private $dayHigh;

    /**
     * @var Money|null
     */
    private $week52Low;

    /**
     * @var Money|null
     */
    private $week52High;

    /**
     * @var DateTime|null
     */
    private $updatedAt;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function setPrice(?Money $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getChangePrice(): ?Money
    {
        return $this->changePrice;
    }

    public function setChangePrice(?Money $changePrice): self
    {
        $this->changePrice = $changePrice;

        $this->updateChangePercentage();

        return $this;
    }

    private function updateChangePercentage() {
        $changePercentage = null;
        if ($this->changePrice && $this->preClose && $this->preClose->getValue()) {
            $changePercentage = round(
                $this->changePrice->getValue() * 100 / $this->preClose->getValue(),
                3
            );
        }

        $this->changePercentage = $changePercentage;
    }

    public function getChangePercentage(): ?float
    {
        return $this->changePercentage;
    }

    public function getPeRatio(): float
    {
        return $this->peRatio;
    }

    public function setPeRatio(?float $peRatio): self
    {
        $this->peRatio = $peRatio;

        return $this;
    }

    public function getPreClose(): Money
    {
        return $this->preClose;
    }

    public function setPreClose(?Money $preClose): self
    {
        $this->preClose = $preClose;

        $this->updateChangePercentage();

        return $this;
    }

    public function getOpen(): Money
    {
        return $this->open;
    }

    public function setOpen(?Money $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function getDayLow(): Money
    {
        return $this->dayLow;
    }

    public function setDayLow(?Money $dayLow): self
    {
        $this->dayLow = $dayLow;

        return $this;
    }

    public function getDayHigh(): Money
    {
        return $this->dayHigh;
    }

    public function setDayHigh(?Money $dayHigh): self
    {
        $this->dayHigh = $dayHigh;

        return $this;
    }

    public function getWeek52Low(): Money
    {
        return $this->week52Low;
    }

    public function setWeek52Low(?Money $week52Low): self
    {
        $this->week52Low = $week52Low;

        return $this;
    }

    public function getWeek52High(): Money
    {
        return $this->week52High;
    }

    public function setWeek52High(?Money $week52High): self
    {
        $this->week52High = $week52High;

        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function equals(StockPrice $price): bool
    {
        return $this->price->equals($price->getPrice()) &&
            $this->changePrice->equals($price->getChangePrice()) &&
            $this->peRatio == $price->getPeRatio() &&
            $this->preClose->equals($price->getPreClose()) &&
            $this->open->equals($price->getOpen()) &&
            $this->dayLow->equals($price->getDayLow()) &&
            $this->dayHigh->equals($price->getDayHigh()) &&
            $this->week52Low->equals($price->getWeek52Low()) &&
            $this->week52High->equals($price->getWeek52High());
    }

    public function getCurrency(): ?Currency
    {
        return $this->price ? $this->price->getCurrency() : null;
    }
}
