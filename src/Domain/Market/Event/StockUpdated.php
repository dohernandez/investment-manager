<?php

namespace App\Domain\Market\Event;

use App\Domain\Market\StockDividend;
use App\Domain\Market\StockInfo;
use App\Domain\Market\StockMarket;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;

final class StockUpdated implements DataInterface
{
    use Data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $yahooSymbol;

    /**
     * @var StockMarket|null
     */
    private $market;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var StockInfo|null
     */
    private $type;

    /**
     * @var StockInfo|null
     */
    private $sector;

    /**
     * @var StockInfo|null
     */
    private $industry;

    /**
     * @var string|null
     */
    private $dividendFrequency;

    public function __construct(
        string $id,
        ?string $name = null,
        ?string $yahooSymbol = null,
        StockMarket $market = null,
        ?string $description = null,
        ?StockInfo $type = null,
        ?StockInfo $sector = null,
        ?StockInfo $industry = null,
        ?string $dividendFrequency = StockDividend::FREQUENCY_QUARTERLY
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->yahooSymbol = $yahooSymbol;
        $this->market = $market;
        $this->description = $description;
        $this->type = $type;
        $this->sector = $sector;
        $this->industry = $industry;
        $this->dividendFrequency = $dividendFrequency;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getYahooSymbol(): ?string
    {
        return $this->yahooSymbol;
    }

    public function getMarket(): ?StockMarket
    {
        return $this->market;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getType(): ?StockInfo
    {
        return $this->type;
    }

    public function getSector(): ?StockInfo
    {
        return $this->sector;
    }

    public function getIndustry(): ?StockInfo
    {
        return $this->industry;
    }

    public function getDividendFrequency(): ?string
    {
        return $this->dividendFrequency;
    }
}
