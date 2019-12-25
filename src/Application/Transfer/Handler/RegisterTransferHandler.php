<?php

namespace App\Application\Transfer\Handler;

use App\Application\Transfer\Command\RegisterTransfer;
use App\Application\Transfer\Repository\TransferRepositoryInterface;
use App\Domain\Transfer\Transfer;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RegisterTransferHandler implements MessageHandlerInterface
{
    /**
     * @var TransferRepositoryInterface
     */
    private $transferRepository;

    public function __construct(TransferRepositoryInterface $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

    public function __invoke(RegisterTransfer $message)
    {
        $beneficiary = $message->getBeneficiary();
        $debtor = $message->getDebtor();
        $amount = $message->getAmount();
        $date = $message->getDate();

        $transfer = Transfer::transfer($beneficiary, $debtor, $amount, $date);

        $this->transferRepository->save($transfer);

        return $transfer;
    }
}
