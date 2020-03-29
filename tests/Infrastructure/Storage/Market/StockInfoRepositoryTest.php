<?php

namespace App\Tests\Infrastructure\Storage\Market;

use App\Domain\Market\StockInfo;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\Storage\Market\StockInfoRepository;
use App\Tests\Domain\Market\StockInfoProvider;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;

final class StockInfoRepositoryTest extends AppDoctrineKernelTestCase
{
    /**
     * @covers StockInfoRepository::save
     */
    public function testFind()
    {
        $stockInfo = StockInfoProvider::provide('Stock Info', StockInfo::TYPE);

        /** @var StockInfoRepository $repo */
        $repo = $this->getRepository(StockInfoRepository::class);

        $repo->save($stockInfo);

        // Clearing the em in memory.
        $this->entityManager->clear();
        /** @var StockInfo $result */
        $result = $this->entityManager
            ->createQuery('SELECT stock_info FROM ' . StockInfo::class . ' stock_info WHERE stock_info.id = :id')
            ->setParameter('id', $stockInfo->getId())
            ->getSingleResult();

        $this->assertInstanceOf(StockInfo::class, $result);
        $this->assertEquals($stockInfo->getId(), $result->getId());
        $this->assertEquals($stockInfo->getName(), $result->getName());
        $this->assertEquals($stockInfo->getType(), $result->getType());
    }

    public function testSave()
    {
        $stockInfo = StockInfoProvider::provide('Stock Info', StockInfo::TYPE);

        /** @var StockInfoRepository $repo */
        $repo = $this->getRepository(StockInfoRepository::class);

        $repo->save($stockInfo);

        // Clearing the em in memory.
        $this->entityManager->clear();

        $result = $repo->find($stockInfo->getId());

        $this->assertInstanceOf(StockInfo::class, $result);
        $this->assertEquals($stockInfo->getId(), $result->getId());
        $this->assertEquals($stockInfo->getName(), $result->getName());
        $this->assertEquals($stockInfo->getType(), $result->getType());
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->truncate(StockInfo::class);
        $this->truncate(Changed::class);

        parent::tearDown();
    }
}
