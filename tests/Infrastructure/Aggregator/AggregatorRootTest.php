<?php

namespace App\Tests\Infrastructure\Aggregator;

use App\Infrastructure\Aggregator\AggregatorRoot;
use App\Infrastructure\Aggregator\Changed;
use App\Infrastructure\Aggregator\Event;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AggregatorRootTest extends TestCase
{
    /**
     * @dataProvider recordChangeDataProvider
     */
    public function testShouldRecordChange(Event $event)
    {
        // create change check function
        $assetChanged = function (Changed $changed) use ($event) {
            $this->assertEquals($changed->getPayload(), $event);
            $this->assertEquals($changed->getEventName(), $event->getName());
            $this->assertEquals($changed->getAggregateType(), 'Account');
        };

        // mock EntityManager
        $em = $this->prophesize(EntityManagerInterface::class);
        $em->persist(Argument::that(function ($arg) use ($assetChanged) {
            $assetChanged($arg);

            return true;
        }))->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        // mock MessageBus
        $bus = $this->prophesize(MessageBusInterface::class);
        $bus->dispatch($event)->willReturn(new Envelope($event));

        // implementation of the abstract class AggregatorRoot
        $accountAggregateRoot = new class($em->reveal(), $bus->reveal(), $assetChanged) extends AggregatorRoot {
            /**
             * @var callable
             */
            private $assetChanged;

            public function __construct(EntityManagerInterface $em, MessageBusInterface $bus, callable $assetChanged)
            {
                parent::__construct($em, $bus);

                $this->assetChanged = $assetChanged;
            }

            public function getType()
            {
                return 'Account';
            }

            /**
             * @inheritDoc
             */
            public function Apply(Changed $event): void
            {
                call_user_func($this->assetChanged, $event);
            }
        };

        $accountAggregateRoot->recordChange($event);
    }

    public function recordChangeDataProvider()
    {
        return [
            'record event account created' => [
                $this->createEvent('Account Created')
            ],
        ];
    }

    private function createEvent($eventName): Event
    {
        return new class($eventName, random_int(0, 100)) implements Event {
            /**
             * @var string
             */
            private $name;

            /**
             * @var int
             */
            private $num;

            public function __construct($name, $num)
            {
                $this->name = $name;
                $this->num = $num;
            }

            public function getName(): string
            {
                return $this->name;
            }

            /**
             * @inheritDoc
             */
            public function serialize(): array
            {
                // TODO: Implement serialize() method.
            }

            /**
             * @inheritDoc
             */
            static public function deserialize(array $value)
            {
                // TODO: Implement deserialize() method.
            }

            /**
             * @return int
             */
            public function getNum(): int
            {
                return $this->num;
            }
        };
    }
}
