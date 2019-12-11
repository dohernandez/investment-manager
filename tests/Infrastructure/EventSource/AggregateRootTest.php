<?php

namespace App\Tests\Infrastructure\EventSource;

use App\Infrastructure\EventSource\AggregateRoot;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\EventSource\Metadata;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\UUID;

/**
 * @group unit
 * @group infrastructure
 * @group aggregator
 */
final class AggregatorRootTest extends TestCase
{
    public function testRecordChange()
    {
        $aggregateId = UUID\Generator::generate();
        $name = 'CREATE EVENT';

        $event = new SimpleEventMock($name);

        $anonymousAggregateRoot = $this->createAggregateRoot($aggregateId, $event);

        $anonymousAggregateRoot->recordChange($event);

        $this->assertEquals($aggregateId, $anonymousAggregateRoot->getId());
        $this->assertCount(1, $anonymousAggregateRoot->getChanges());
        $this->assertEquals(1, $anonymousAggregateRoot->getVersion());
        $this->assertEquals($name, $anonymousAggregateRoot->getChanges()->first()->getPayload()->getName());
    }

    private function createAggregateRoot(string $aggregateId, SimpleEventMock $event): AggregateRoot
    {
        $assert = function (
            Changed $changed,
            $className
        ) use ($aggregateId, $event) {
            $this->assertEquals(get_class($event), $changed->getEventName());
            $this->assertEquals($event, $changed->getPayload());
            $this->assertEquals($className, $changed->getAggregateType());
        };

        // implementation of the abstract class AggregateRoot
        return new class($aggregateId, $assert) extends AggregateRoot {
            /**
             * @var callable
             */
            private $assert;

            public function __construct(string $id, callable $assert)
            {
                parent::__construct();

                $this->id = $id;
                $this->assert = $assert;
            }

            protected function apply(Changed $changed)
            {
                $assert = $this->assert;

                $assert($changed, get_class($this));
            }
        };
    }

    public function testReplay()
    {
        $aggregateId = UUID\Generator::generate();
        $name = 'CREATE EVENT';

        $event = new SimpleEventMock($name);

        $anonymousAggregateRoot = $this->createAggregateRoot($aggregateId, $event);

        $changed = new Changed(
            UUID\Generator::generate(),
            get_class($event),
            $event,
            new Metadata(),
            get_class($anonymousAggregateRoot),
            $aggregateId,
            1
        );

        $anonymousAggregateRoot->replay([$changed]);

        $this->assertEquals($aggregateId, $anonymousAggregateRoot->getId());
        $this->assertCount(1, $anonymousAggregateRoot->getChanges());
        $this->assertEquals(1, $anonymousAggregateRoot->getVersion());
        $this->assertEquals($name, $anonymousAggregateRoot->getChanges()->first()->getPayload()->getName());
    }
}
