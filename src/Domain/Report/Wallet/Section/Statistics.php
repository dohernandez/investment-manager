<?php

namespace App\Domain\Report\Wallet\Section;

use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;

final class Statistics implements DataInterface
{
    private const DBAL_KEY_INVESTED = 'invested';
    private const DBAL_KEY_CAPITAL = 'capital';
    private const DBAL_KEY_NET_CAPITAL = 'netCapital';
    private const DBAL_KEY_FUNDS = 'funds';
    private const DBAL_KEY_DIVIDENDS = 'dividends';
    private const DBAL_KEY_COMMISSIONS = 'commissions';
    private const DBAL_KEY_CONNECTION = 'connection';
    private const DBAL_KEY_INTEREST = 'interest';
    private const DBAL_KEY_BENEFITS = 'benefits';
    private const DBAL_KEY_PERCENTAGE_BENEFITS = 'percentageBenefits';

    /**
     * @var Money
     */
    private $invested;

    /**
     * @var Money
     */
    private $capital;

    /**
     * @var Money
     */
    private $netCapital;

    /**
     * @var Money
     */
    private $funds;

    /**
     * @var Money|null
     */
    private $dividends;

    /**
     * @var Money|null
     */
    private $commissions;

    /**
     * @var Money|null
     */
    private $connection;

    /**
     * @var Money|null
     */
    private $interest;

    /**
     * @var Money|null
     */
    private $benefits;

    /**
     * @var float|null
     */
    private $percentageBenefits;

    public function __construct(
        Money $invested,
        Money $capital,
        Money $netCapital,
        Money $funds,
        ?Money $dividends,
        ?Money $commissions,
        ?Money $connection,
        ?Money $interest,
        ?Money $benefits,
        ?float $percentageBenefits
    ) {
        $this->invested = $invested;
        $this->capital = $capital;
        $this->netCapital = $netCapital;
        $this->funds = $funds;
        $this->dividends = $dividends;
        $this->commissions = $commissions;
        $this->connection = $connection;
        $this->interest = $interest;
        $this->benefits = $benefits;
        $this->percentageBenefits = $percentageBenefits;
    }

    public function getInvested(): Money
    {
        return $this->invested;
    }

    public function getCapital(): Money
    {
        return $this->capital;
    }

    public function getNetCapital(): Money
    {
        return $this->netCapital;
    }

    public function getFunds(): Money
    {
        return $this->funds;
    }

    public function getDividends(): ?Money
    {
        return $this->dividends;
    }

    public function getCommissions(): ?Money
    {
        return $this->commissions;
    }

    public function getConnection(): ?Money
    {
        return $this->connection;
    }

    public function getInterest(): ?Money
    {
        return $this->interest;
    }

    public function getBenefits(): ?Money
    {
        return $this->benefits;
    }

    public function getPercentageBenefits(): ?float
    {
        return $this->percentageBenefits;
    }

    /**
     * @inheritDoc
     */
    public function marshalData()
    {
        return [
            self::DBAL_KEY_INVESTED            => $this->invested->marshalData(),
            self::DBAL_KEY_CAPITAL             => $this->capital->marshalData(),
            self::DBAL_KEY_NET_CAPITAL         => $this->netCapital->marshalData(),
            self::DBAL_KEY_FUNDS               => $this->funds->marshalData(),
            self::DBAL_KEY_DIVIDENDS           => $this->dividends->marshalData(),
            self::DBAL_KEY_COMMISSIONS         => $this->commissions->marshalData(),
            self::DBAL_KEY_CONNECTION          => $this->connection->marshalData(),
            self::DBAL_KEY_INTEREST            => $this->interest->marshalData(),
            self::DBAL_KEY_BENEFITS            => $this->benefits->marshalData(),
            self::DBAL_KEY_PERCENTAGE_BENEFITS => $this->percentageBenefits,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalData($data)
    {
        return new static(
            $data[self::DBAL_KEY_INVESTED],
            $data[self::DBAL_KEY_CAPITAL],
            $data[self::DBAL_KEY_NET_CAPITAL],
            $data[self::DBAL_KEY_FUNDS],
            $data[self::DBAL_KEY_DIVIDENDS],
            $data[self::DBAL_KEY_COMMISSIONS],
            $data[self::DBAL_KEY_CONNECTION],
            $data[self::DBAL_KEY_INTEREST],
            $data[self::DBAL_KEY_BENEFITS],
            $data[self::DBAL_KEY_PERCENTAGE_BENEFITS]
        );
    }
}
