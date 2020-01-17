<?php

namespace App\Tests\Infrastructure\Storage\Market;

use App\Domain\Market\Stock;
use App\Domain\Market\StockMarket;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use App\Infrastructure\Storage\Market\StockMarketRepository;
use App\Infrastructure\Storage\Market\StockRepository;
use App\Tests\Domain\Market\StockMarketProvider;
use App\Tests\Domain\Market\StockProvider;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;

final class StockRepositoryTest extends AppDoctrineKernelTestCase
{
    /**
     * @covers StockRepository::save
     */
    public function testSave()
    {
        $stock = StockProvider::provide(
            'Stock',
            'STK',
            $this->createMarker()
        );

        /** @var StockRepository $repo */
        $repo = $this->getRepository(StockRepository::class);

        $repo->save($stock);

        // Clearing the em in memory.
        $this->entityManager->clear();
        /** @var Stock $result */
        $result = $this->entityManager
            ->createQuery('SELECT stock FROM ' . Stock::class . ' stock WHERE stock.id = :id')
            ->setParameter('id', $stock->getId())
            ->getSingleResult();

        $this->assertInstanceOf(Stock::class, $result);
        $this->assertEquals($stock->getId(), $result->getId());
        $this->assertEquals($stock->getName(), $result->getName());
        $this->assertEquals($stock->getSymbol(), $result->getSymbol());
        $this->assertEquals($stock->getMarket()->getId(), $result->getMarket()->getId());
    }

    private function createMarker(): StockMarket
    {
        $market = StockMarketProvider::provide('Stock Market', Currency::usd(), 'US', 'NasdaqGS', 'NasdaqGS');

        /** @var StockMarketRepository $repo */
        $repo = $this->getRepository(StockMarketRepository::class);

        $repo->save($market);

        return $market;
    }

    /**
     * @covers StockRepository::find
     */
    public function testFind()
    {
        $stock = StockProvider::provide(
            'Stock',
            'STK',
            $this->createMarker()
        );

        /** @var StockRepository $repo */
        $repo = $this->getRepository(StockRepository::class);

        $repo->save($stock);

        // Clearing the em in memory.
        $this->entityManager->clear();

        $result = $repo->find($stock->getId());

        $this->assertInstanceOf(Stock::class, $result);
        $this->assertEquals($stock->getId(), $result->getId());
        $this->assertEquals($stock->getName(), $result->getName());
        $this->assertEquals($stock->getSymbol(), $result->getSymbol());
        $this->assertEquals($stock->getMarket()->getId(), $result->getMarket()->getId());
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->truncate(StockMarket::class);
        $this->truncate(Stock::class);
        $this->truncate(Changed::class);

        parent::tearDown();
    }
}
