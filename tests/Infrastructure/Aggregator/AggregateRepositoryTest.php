<?php

namespace App\Tests\Infrastructure\Aggregator;

use App\Domain\Account\Projection\Account;
use App\Infrastructure\Aggregator\AggregateRepository;
use App\Infrastructure\Aggregator\AggregateRoot;
use App\Infrastructure\Aggregator\Changed;
use App\Infrastructure\Aggregator\Event;
use App\Infrastructure\UUID\Generator;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;

/**
 * @group integration
 * @group infrastructure
 * @group aggregator
 */
final class AggregateRepositoryTest extends AppDoctrineKernelTestCase
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
     * @var AggregateRepository
     */
    protected $aggregateRepository;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $aggregateId = Generator::generate();
        $username = 'USER NAME';

        // implementation of the abstract class AggregateRoot
        $anonymousAggregateRoot = new class($aggregateId) extends AggregateRoot {
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

        $anonymousAggregateRoot->create($username);

        $this->aggregateType = get_class($anonymousAggregateRoot);
        $this->aggregateId = $aggregateId;
        $this->username = $username;

        $this->aggregateRepository = $this->entityManager
            ->getRepository(Changed::class);

        $this->aggregateRepository->store($anonymousAggregateRoot);
    }

    public function testLoad()
    {
        /** @var AggregateRoot $aggregateRoot */
        $aggregateRoot = $this->aggregateRepository->load($this->aggregateId, $this->aggregateType);

        $this->assertCount(1, $aggregateRoot->getChanges());
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->truncate(Account::class);

        parent::tearDown();
    }
}
