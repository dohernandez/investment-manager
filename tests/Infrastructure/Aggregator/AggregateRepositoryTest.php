<?php

namespace App\Tests\Infrastructure\Aggregator;

use App\Infrastructure\Aggregator\AggregateRepository;
use App\Infrastructure\Aggregator\AggregateRoot;
use App\Infrastructure\Aggregator\Changed;
use App\Infrastructure\Aggregator\Event;
use App\Infrastructure\UUID\Generator;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AggregateRepositoryTest extends KernelTestCase
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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

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

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

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
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
