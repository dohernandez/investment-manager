<?php

namespace App\Application\Market\Command;

class AddStock
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var string
     */
    private $marketId;

    /**
     * @var int|null
     */
    private $value;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $sector;

    /**
     * @var string|null
     */
    private $industry;

    public function __construct(
        string $name,
        string $symbol,
        string $marketId,
        ?int $value = null,
        ?string $description = null,
        ?string $type = null,
        ?string $sector = null,
        ?string $industry = null
    ) {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->marketId = $marketId;
        $this->value = $value;
        $this->description = $description;
        $this->type = $type;
        $this->sector = $sector;
        $this->industry = $industry;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getMarketId(): string
    {
        return $this->marketId;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function getIndustry(): ?string
    {
        return $this->industry;
    }
}
