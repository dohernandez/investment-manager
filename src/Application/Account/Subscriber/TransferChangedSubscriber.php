<?php

namespace App\Application\Account\Subscriber;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Transfer\Event\TransferChanged;
use App\Domain\Transfer\Event\TransferRegistered;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransferChangedSubscriber implements EventSubscriberInterface
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
            TransferChanged::class => ['onTransferChanged', 100],
        ];
    }

    public function onTransferChanged(TransferChanged $event)
    {
        $diffBeneficiary = $event->getNewBeneficiary()->getId() != $event->getOldBeneficiary()->getId();
        $diffDebtor = $event->getNewDebtor()->getId() != $event->getOldDebtor()->getId();
        $difAmount = $event->getNewAmount()->getValue() != $event->getOldAmount()->getValue();

        if ($diffBeneficiary || $difAmount) {
            $newBeneficiary = $this->accountRepository->find($event->getNewBeneficiary()->getId());
            $newBeneficiary->deposit($event->getNewAmount());
            $this->accountRepository->save($newBeneficiary);

            $oldBeneficiary = $this->accountRepository->find($event->getOldBeneficiary()->getId());
            $oldBeneficiary->withdraw($event->getOldAmount());
            $this->accountRepository->save($oldBeneficiary);
        }

        if ($diffDebtor || $difAmount) {
            $newDebtor = $this->accountRepository->find($event->getNewDebtor()->getId());
            $newDebtor->withdraw($event->getNewAmount());
            $this->accountRepository->save($newDebtor);

            $oldDebtor = $this->accountRepository->find($event->getOldDebtor()->getId());
            $oldDebtor->deposit($event->getOldAmount());
            $this->accountRepository->save($oldDebtor);
        }
    }
}
