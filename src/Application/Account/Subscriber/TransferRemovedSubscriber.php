<?php

namespace App\Application\Account\Subscriber;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Transfer\Event\TransferChanged;
use App\Domain\Transfer\Event\TransferRegistered;
use App\Domain\Transfer\Event\TransferRemoved;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransferRemovedSubscriber implements EventSubscriberInterface
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
            TransferRemoved::class => ['onTransferRemoved', 100],
        ];
    }

    public function onTransferRemoved(TransferRemoved $event)
    {
        $beneficiary = $this->accountRepository->find($event->getBeneficiary()->getId());
        $beneficiary->withdraw($event->getAmount());
        $this->accountRepository->save($beneficiary);

        $debtor = $this->accountRepository->find($event->getDebtor()->getId());
        $debtor->deposit($event->getAmount());
        $this->accountRepository->save($debtor);
    }
}
