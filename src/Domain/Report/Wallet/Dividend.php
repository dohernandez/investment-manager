<?php

namespace App\Domain\Report\Wallet;

use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use DateTimeInterface;

final class Dividend implements DataInterface
{
    use Data;

    /**
     * @var string|null
     */
    private $dividendYield;

    /**
     * @var DateTimeInterface|null
     */
    private $exDate;

    /**
     * @var string|null
     */
    private $realDividendYield;

    public function __construct(
        ?string $dividendYield,
        ?DateTimeInterface $exDate,
        ?string $realDividendYield
     ) {
        $this->dividendYield = $dividendYield;
        $this->exDate = $exDate;
        $this->realDividendYield = $realDividendYield;
    }

    public function getDividendYield(): ?string
    {
        return $this->dividendYield;
    }

    public function getExDate(): ?DateTimeInterface
    {
        return $this->exDate;
    }

    public function getRealDividendYield(): ?string
    {
        return $this->realDividendYield;
    }
}
