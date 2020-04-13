<?php

namespace App\Domain\Report\Weekly;

use App\Infrastructure\Doctrine\DBAL\DomainInterface;
use App\Infrastructure\Money\Money;

final class Wallet implements DomainInterface
{
    const DBAL_KEY_NAME = 'name';
    const DBAL_KEY_SLUG = 'slug';
    const DBAL_KEY_CAPITAL = 'capital';
    const DBAL_KEY_WEEKLY_RETURN = 'weeklyReturn';
    const DBAL_KEY_YEAR_RETURN = 'yearReturn';

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

    /**
     * @inheritDoc
     */
    public function marshalDBAL()
    {
        return [
            self::DBAL_KEY_NAME          => $this->name,
            self::DBAL_KEY_SLUG          => $this->slug,
            self::DBAL_KEY_CAPITAL       => $this->capital,
            self::DBAL_KEY_WEEKLY_RETURN => $this->weeklyReturn,
            self::DBAL_KEY_YEAR_RETURN   => $this->yearReturn,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalDBAL($value)
    {
        return new static(
            $value[self::DBAL_KEY_NAME],
            $value[self::DBAL_KEY_SLUG],
            $value[self::DBAL_KEY_CAPITAL],
            $value[self::DBAL_KEY_WEEKLY_RETURN],
            $value[self::DBAL_KEY_YEAR_RETURN]
        );
    }
}
