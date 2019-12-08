<?php

namespace App\Infrastructure\Aggregator;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class AggregateRootListener
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    public function postPersistHandler(Changed $changed, LifecycleEventArgs $event)
    {
        $this->dispatcher->dispatch($changed->getPayload());
    }
}
