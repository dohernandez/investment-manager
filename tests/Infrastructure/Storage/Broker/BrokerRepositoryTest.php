<?php

namespace App\Tests\Infrastructure\Storage\Broker;

use App\Domain\Broker\Broker;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Storage\Broker\BrokerRepository;
use App\Tests\Domain\Broker\BrokerProvider;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;

final class BrokerRepositoryTest extends AppDoctrineKernelTestCase
{
    public function testSave()
    {
        $broker = BrokerProvider::provide('Broker', 'www.broker.com', Currency::eur());

        /** @var BrokerRepository $repo */
        $repo = $this->getRepository(BrokerRepository::class);

        $repo->save($broker);

        // Clearing the em in memory.
        $this->entityManager->clear();
        /** @var Broker $result */
        $result = $this->entityManager
            ->createQuery('SELECT broker FROM ' . Broker::class . ' broker WHERE broker.id = :id')
            ->setParameter('id', $broker->getId())
            ->getSingleResult();

        $this->assertInstanceOf(Broker::class, $result);
        $this->assertEquals($broker->getId(), $result->getId());
        $this->assertEquals($broker->getName(), $result->getName());
        $this->assertEquals($broker->getSite(), $result->getSite());

    }

    public function testFind()
    {
        $broker = BrokerProvider::provide('Broker', 'www.broker.com', Currency::eur());

        /** @var BrokerRepository $repo */
        $repo = $this->getRepository(BrokerRepository::class);

        $repo->save($broker);

        // Clearing the em in memory.
        $this->entityManager->clear();

        $result = $repo->find($broker->getId());

        $this->assertInstanceOf(Broker::class, $result);
        $this->assertEquals($broker->getId(), $result->getId());
        $this->assertEquals($broker->getName(), $result->getName());
        $this->assertEquals($broker->getSite(), $result->getSite());
    }
}
