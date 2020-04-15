<?php

namespace App\Domain\Report\Wallet\Section;

use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;

final class Positions implements DataInterface
{
    private const DBAL_KEY_DATE_ADDED = 'dateAdded';
    private const DBAL_KEY_NAME = 'name';
    private const DBAL_KEY_SYMBOL = 'symbol';
    private const DBAL_KEY_PRICE = 'price';
    private const DBAL_KEY_AMOUNT = 'amount';
    private const DBAL_KEY_CAPITAL = 'capital';
    private const DBAL_KEY_DIVIDENDS = 'dividends';
    private const DBAL_KEY_BENEFITS = 'benefits';
    private const DBAL_KEY_CHANGES = 'changes';

    /**
     * @var DateTime
     */
    private $dateAdded;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var Money
     */
    private $price;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var Money
     */
    private $dividends;

    /**
     * @var UpDown
     */
    private $benefits;

    /**
     * @var UpDown
     */
    private $changes;

    public function __construct(
        DateTime $dateAdded,
        string $name,
        string $symbol,
        Money $price,
        int $amount,
        Money $capital,
        Money $dividends,
        UpDown $benefits,
        UpDown $changes
    ) {
        $this->dateAdded = $dateAdded;
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
        $this->amount = $amount;
        $this->capital = $capital;
        $this->dividends = $dividends;
        $this->benefits = $benefits;
        $this->changes = $changes;
    }

    /**
     * @inheritDoc
     */
    public function marshalData()
    {
        return [
            self::DBAL_KEY_DATE_ADDED => $this->dateAdded,
            self::DBAL_KEY_NAME       => $this->name,
            self::DBAL_KEY_SYMBOL     => $this->symbol,
            self::DBAL_KEY_PRICE      => $this->price,
            self::DBAL_KEY_AMOUNT     => $this->amount,
            self::DBAL_KEY_CAPITAL    => $this->capital,
            self::DBAL_KEY_DIVIDENDS  => $this->dividends,
            self::DBAL_KEY_BENEFITS   => $this->benefits,
            self::DBAL_KEY_CHANGES    => $this->changes,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalData($data)
    {
        return new static(
            $data[self::DBAL_KEY_DATE_ADDED],
            $data[self::DBAL_KEY_NAME],
            $data[self::DBAL_KEY_SYMBOL],
            $data[self::DBAL_KEY_PRICE],
            $data[self::DBAL_KEY_AMOUNT],
            $data[self::DBAL_KEY_CAPITAL],
            $data[self::DBAL_KEY_DIVIDENDS],
            $data[self::DBAL_KEY_BENEFITS],
            $data[self::DBAL_KEY_CHANGES]
        );
    }
}
