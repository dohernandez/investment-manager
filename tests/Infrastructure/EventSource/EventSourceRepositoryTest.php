<?php

namespace App\Tests\Infrastructure\EventSource;

use App\Domain\Account\Account;
use App\Infrastructure\EventSource\EventSourceRepository;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\UUID\Generator;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;

/**
 * @group integration
 * @group infrastructure
 * @group aggregator
 */
final class EventSourceRepositoryTest extends AppDoctrineKernelTestCase
{
    public function testLoad()
    {
        $aggregateId = Generator::generate();
        $username = 'USER NAME';

        // implementation of the abstract class AggregateRoot
        $anonymous = new class($aggregateId) extends AggregateRoot {
            /**
             * @@var string $name
             */
            private $name;

            /**
             * @return string
             */
            public function getName(): string
            {
                return $this->name;
            }

            public function create(string $name)
            {
                $event = new SimpleEventMock($name);

                $this->recordChange($event);
            }

            protected function apply(Changed $changed)
            {
                $this->name = $changed->getPayload()->getName();
            }
        };

        $anonymous->create($username);

        $aggregateType = get_class($anonymous);

        $eventSourceRepository = $this->getRepository(EventSourceRepositoryInterface::class);

        $eventSourceRepository->saveEvents($anonymous->getChanges(), true);

        /** @var AggregateRoot $aggregateRoot */
        $changes = $eventSourceRepository->findEvents($aggregateId, $aggregateType);

        $this->assertCount(1, $changes);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->truncate(Changed::class);

        parent::tearDown();
    }
}
