<?php

namespace App\Tests\Infrastructure\Storage\Market;

use App\Domain\Market\StockMarket;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Storage\Market\StockMarketRepository;
use App\Tests\Domain\Market\StockMarketProvider;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;

final class StockMarketRepositoryTest extends AppDoctrineKernelTestCase
{
    /**
     * @covers StockMarketRepository::save
     */
    public function testFind()
    {
        $market = StockMarketProvider::provide('Stock Market', Currency::usd(), 'US', 'NasdaqGS');

        /** @var StockMarketRepository $repo */
        $repo = $this->getRepository(StockMarketRepository::class);

        $repo->save($market);

        // Clearing the em in memory.
        $this->entityManager->clear();
        /** @var StockMarket $result */
        $result = $this->entityManager
            ->createQuery('SELECT market FROM ' . StockMarket::class . ' market WHERE market.id = :id')
            ->setParameter('id', $market->getId())
            ->getSingleResult();

        $this->assertInstanceOf(StockMarket::class, $result);
        $this->assertEquals($market->getId(), $result->getId());
        $this->assertEquals($market->getCurrency(), $result->getCurrency());
        $this->assertEquals($market->getCountry(), $result->getCountry());
        $this->assertEquals($market->getSymbol(), $result->getSymbol());
        $this->assertEquals($market->getMetadata(), $result->getMetadata());
    }

    public function testSave()
    {
        $market = StockMarketProvider::provide('Stock Market', Currency::usd(), 'US', 'NasdaqGS');

        /** @var StockMarketRepository $repo */
        $repo = $this->getRepository(StockMarketRepository::class);

        $repo->save($market);

        // Clearing the em in memory.
        $this->entityManager->clear();

        $result = $repo->find($market->getId());

        $this->assertInstanceOf(StockMarket::class, $result);
        $this->assertEquals($market->getId(), $result->getId());
        $this->assertEquals($market->getCurrency(), $result->getCurrency());
        $this->assertEquals($market->getCountry(), $result->getCountry());
        $this->assertEquals($market->getSymbol(), $result->getSymbol());
        $this->assertEquals($market->getMetadata(), $result->getMetadata());
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->truncate(StockMarket::class);
        $this->truncate(Changed::class);

        parent::tearDown();
    }
}
