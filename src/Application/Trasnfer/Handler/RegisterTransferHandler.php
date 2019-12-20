<?php

namespace App\Application\Trasnfer\Handler;

use App\Application\Trasnfer\Command\RegisterTransfer;
use App\Application\Trasnfer\Repository\AccountRepositoryInterface;
use App\Application\Trasnfer\Repository\TransferRepositoryInterface;
use App\Domain\Transfer\Transfer;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RegisterTransferHandler implements MessageHandlerInterface
{
    /**
     * @var TransferRepositoryInterface
     */
    private $transferRepository;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(
        TransferRepositoryInterface $transferRepository,
        AccountRepositoryInterface $accountRepository
    ) {
        $this->transferRepository = $transferRepository;
        $this->accountRepository = $accountRepository;
    }

    public function __invoke(RegisterTransfer $message)
    {
        $beneficiary = $this->accountRepository->find($message->getBeneficiary());
        $debtor = $this->accountRepository->find($message->getDebtor());
        $amount = $message->getAmount();
        $date = $message->getDate();

        $transfer = Transfer::transfer($beneficiary, $debtor, $amount, $date);

        $this->transferRepository->save($transfer);

        return $transfer;
    }
}
