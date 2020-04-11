<?php

namespace App\Domain\Report\Weekly;

use App\Infrastructure\Doctrine\DBAL\DomainInterface;
use App\Infrastructure\Money\Money;

final class Wallet implements DomainInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var float
     */
    private $weeklyReturn;

    /**
     * @var float
     */
    private $yearReturn;

    public function __construct(string $name, string $slug, Money $capital, float $weeklyReturn, float $yearReturn)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->capital = $capital;
        $this->weeklyReturn = $weeklyReturn;
        $this->yearReturn = $yearReturn;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function getWeeklyReturn(): float
    {
        return $this->weeklyReturn;
    }

    public function getYearReturn(): float
    {
        return $this->yearReturn;
    }

    public function marshalDBAL(): array
    {
        return [
            'name'         => $this->name,
            'slug'         => $this->slug,
            'capital'      => $this->capital,
            'weeklyReturn' => $this->weeklyReturn,
            'yearReturn'   => $this->yearReturn,
        ];
    }

    public static function unMarshalDBAL(array $value)
    {
        return new static(
            $value['name'],
            $value['slug'],
            $value['capital'],
            $value['weeklyReturn'],
            $value['yearReturn']
        );
    }
}
