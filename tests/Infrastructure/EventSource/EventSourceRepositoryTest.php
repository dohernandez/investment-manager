<?php

namespace App\Tests\Infrastructure\EventSource;

use App\Domain\Account\Account;
use App\Infrastructure\EventSource\EventSourceRepository;
use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\UUID\Generator;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;

/**
 * @group integration
 * @group infrastructure
 * @group aggregator
 */
final class EventSourceRepositoryTest extends AppDoctrineKernelTestCase
{
    /**
     * @var string
     */
    protected $aggregateId;

    /**
     * @var string
     */
    protected $username;

    protected $aggregateType;

    /**
     * @var EventSourceRepository
     */
    protected $eventSourceRepository;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

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

        $this->aggregateType = get_class($anonymous);
        $this->aggregateId = $aggregateId;
        $this->username = $username;

        $this->eventSourceRepository = $this->getRepository(EventSourceRepository::class);

        $this->eventSourceRepository->saveEvents($anonymous->getChanges());
    }

    public function testLoad()
    {
        /** @var AggregateRoot $aggregateRoot */
        $changes = $this->eventSourceRepository->findEvents($this->aggregateId, $this->aggregateType);

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
