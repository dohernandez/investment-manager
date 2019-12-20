<?php

namespace App\Application\Account\Subscriber;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Transfer\Event\TransferRegistered;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransferRegisteredSubscriber implements EventSubscriberInterface
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
            TransferRegistered::class => ['onTransferRegistered', 100],
        ];
    }

    public function onTransferRegistered(TransferRegistered $event)
    {
        $beneficiary = $this->accountRepository->find($event->getBeneficiaryParty()->getId());
        $beneficiary->deposit($event->getAmount());
        $this->accountRepository->save($beneficiary);

        $debtor = $this->accountRepository->find($event->getDebtorParty()->getId());
        $debtor->withdraw($event->getAmount());
        $this->accountRepository->save($debtor);

    }
}
