<?php

namespace App\Application\Account\Subscriber;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Transfer\Event\TransferRegistered;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransferRegisteredSubscriber implements EventSubscriberInterface
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        AccountRepositoryInterface $accountRepository,
        LoggerInterface $logger
    ) {
        $this->accountRepository = $accountRepository;
        $this->logger = $logger;
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

        $this->logger->debug(
            'Account credited',
            [
                'account_id' => $beneficiary->getId(),
                'account_no' => $beneficiary->getAccountNo(),
                'amount' => $event->getAmount(),
            ]
        );

        $debtor = $this->accountRepository->find($event->getDebtorParty()->getId());
        $debtor->withdraw($event->getAmount());
        $this->accountRepository->save($debtor);

        $this->logger->debug(
            'Account debited',
            [
                'account_id' => $debtor->getId(),
                'account_no' => $debtor->getAccountNo(),
                'amount' => $event->getAmount(),
            ]
        );
    }
}
