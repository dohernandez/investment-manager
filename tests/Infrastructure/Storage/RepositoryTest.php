<?php

namespace App\Tests\Infrastructure\Storage;

use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\EventSourceRepositoryInterface;
use App\Infrastructure\EventSource\Metadata;
use App\Infrastructure\Storage\Repository;
use App\Infrastructure\UUID;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use function get_class;

class RepositoryTest extends TestCase
{
    /**
     * @dataProvider provider
     *
     * @param $change
     * @param $entity
     * @param $partialEntity
     */
    public function testOverloadPartialDependencies($change, $entity, $partialEntity)
    {
        $eventSource = $this->prophesize(EventSourceRepositoryInterface::class);

        $em = $this->prophesize(EntityManagerInterface::class);
        $em->find(get_class($partialEntity), $partialEntity)->shouldBeCalled()->willReturn($entity);

        $repo = new class($em->reveal(), $eventSource->reveal(), ['entity' => get_class($entity)]) extends Repository {
            public function __construct(
                EntityManagerInterface $em,
                EventSourceRepositoryInterface $eventSource,
                array $dependencies
            ) {
                parent::__construct($em, $eventSource);

                $this->dependencies = $dependencies;
            }

            public function overload(array $changes): self
            {
                return $this->overloadDependencies($changes);
            }
        };

        $changes = [$change];
        $repo->overload($changes);

        $this->assertEquals($entity, $changes[0]->getPayload()->getEntity());
        $this->assertNotEquals($partialEntity, $changes[0]->getPayload()->getEntity());
    }

    public function provider()
    {
        $entity = new class(UUID\Generator::generate(), 10) {
            /**
             * @var string
             */
            private $id;

            /**
             * @var int|null
             */
            private $value;

            public function __construct(string $id, ?int $value = null)
            {
                $this->id = $id;
                $this->value = $value;
            }

            public function getId(): string
            {
                return $this->id;
            }

            public function getValue(): ?int
            {
                return $this->value;
            }

            public function setValue(?int $value): self
            {
                $this->value = $value;

                return $this;
            }
        };

        $partialEntity = clone $entity;
        $partialEntity->setValue(null);

        $payload = new class($partialEntity) {
            private $entity;

            public function __construct($entity)
            {
                $this->entity = $entity;
            }

            public function getEntity()
            {
                return $this->entity;
            }
        };

        $change = new Changed(
            UUID\Generator::generate(),
            get_class($payload),
            $payload,
            new Metadata(),
            get_class($entity),
            UUID\Generator::generate(),
            1
        );

        return [
            yield [$change, $entity, $partialEntity]
        ];
    }

    /**
     * @dataProvider provider
     *
     * @param $change
     * @param $entity
     * @param $partialEntity
     */
    public function testUnburdenPartialDependencies($change, $entity, $partialEntity)
    {
        $eventSource = $this->prophesize(EventSourceRepositoryInterface::class);
        $em = $this->prophesize(EntityManagerInterface::class);

        $repo = new class($em->reveal(), $eventSource->reveal(), ['entity' => get_class($entity)]) extends Repository {
            public function __construct(
                EntityManagerInterface $em,
                EventSourceRepositoryInterface $eventSource,
                array $dependencies
            ) {
                parent::__construct($em, $eventSource);

                $this->dependencies = $dependencies;
            }

            public function unburden(ArrayCollection $changes): self
            {
                return $this->unburdenDependencies($changes);
            }
        };

        $changes = [$change];
        $repo->unburden(new ArrayCollection($changes));

        $this->assertEquals($partialEntity, $changes[0]->getPayload()->getEntity());
        $this->assertNotEquals($entity, $changes[0]->getPayload()->getEntity());
    }
}
