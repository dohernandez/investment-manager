<?php

namespace App\Infrastructure\Storage\Console;

use App\Domain\Market\Stock;
use App\Domain\Market\StockMarket;
use Doctrine\ORM\EntityManagerInterface;

use function array_map;
use function sprintf;

final class ConsoleStockMarketRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return array List of stock market. [id: ...]
     */
    public function findAll(): array
    {
        $all = $this->em
            ->createQuery(sprintf('SELECT PARTIAL sm.{id,symbol,currency,yahooSymbol} FROM %s sm', StockMarket::class))
            ->getResult();

        $this->em->clear();

        return array_map(
            function (StockMarket $stockMarket) {
                return [
                    'id' => $stockMarket->getId(),
                    'symbol' => $stockMarket->getSymbol(),
                    'currency' => $stockMarket->getCurrency(),
                    'yahoo_symbol' => $stockMarket->getYahooSymbol(),
                ];
            },
            $all
        );
    }

    /**
     * @param string $symbol
     *
     * @return array The stock market. [id: ...]
     */
    public function findBySymbol(string $symbol): array
    {
        if (
        !$stockMarket = $this->em
            ->createQuery(sprintf('SELECT PARTIAL sm.{id,symbol,currency,yahooSymbol} FROM %s sm WHERE s.symbol = :symbol', StockMarket::class))
            ->setParameter('symbol', $symbol)
            ->getOneOrNullResult()
        ) {
            return null;
        }

        $this->em->clear();

        /** @var StockMarket $stockMarket */
        return [
            'id' => $stockMarket->getId(),
            'symbol' => $stockMarket->getSymbol(),
            'currency' => $stockMarket->getCurrency(),
            'yahoo_symbol' => $stockMarket->getYahooSymbol(),
        ];
    }
}
