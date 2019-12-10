<?php

namespace App\Application\Account\Subscriber;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Event\AccountClosed;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AccountProjectionSubscriber implements EventSubscriberInterface
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AccountClosed::class => ['onAccountClosed', 100],
        ];
    }

    public function onAccountClosed(AccountClosed $event)
    {
        $this->accountRepository->delete($event->getId());
    }
}
