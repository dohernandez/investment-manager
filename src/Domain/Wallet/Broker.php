<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Currency;

final class Broker implements DataInterface
{
    use Data;

    public function __construct(string $id, string $name, Currency $currency)
    {
        $this->id = $id;
        $this->name = $name;
        $this->currency = $currency;
    }

    /**
     * @var string
     */
    protected $id;

    public function getId(): ?string
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
     * @var Currency
     */
    private $currency;

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getTitle(): string
    {
        return \sprintf('%s (%s)', $this->name, $this->currency->getCurrencyCode());
    }
}
