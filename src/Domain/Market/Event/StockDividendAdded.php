<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\Stock;
use App\Domain\Market\StockDividend;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;

final class StockDividendAdded implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Stock
     */
    private $stock;

    /**
     * @var Money
     */
    private $value;

    /**
     * @var DateTime
     */
    private $exDate;

    /**
     * @var string
     */
    private $status;

    /**
     * @var DateTime|null
     */
    private $paymentDate;

    /**
     * @var DateTime|null
     */
    private $recordDate;

    public function __construct(
        string $id,
        Stock $stock,
        Money $value,
        DateTime $exDate,
        string $status = StockDividend::STATUS_PROJECTED,
        ?DateTime $paymentDate = null,
        ?DateTime $recordDate = null
    ) {
        $this->id = $id;
        $this->stock = $stock;
        $this->value = $value;
        $this->exDate = $exDate;
        $this->status = $status;
        $this->paymentDate = $paymentDate;
        $this->recordDate = $recordDate;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStock(): Stock
    {
        return $this->stock;
    }

    public function getValue(): Money
    {
        return $this->value;
    }

    public function getExDate(): DateTime
    {
        return $this->exDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaymentDate(): ?DateTime
    {
        return $this->paymentDate;
    }

    public function getRecordDate(): ?DateTime
    {
        return $this->recordDate;
    }

}
