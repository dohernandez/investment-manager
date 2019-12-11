<?php

namespace App\Infrastructure\EventSource;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class EventSourceListener
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
