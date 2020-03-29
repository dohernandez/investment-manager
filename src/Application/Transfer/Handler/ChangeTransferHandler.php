<?php

namespace App\Application\Transfer\Handler;

use App\Application\Transfer\Command\ChangeTransfer;
use App\Application\Transfer\Repository\TransferRepositoryInterface;
use App\Domain\Transfer\Transfer;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ChangeTransferHandler implements MessageHandlerInterface
{
    /**
     * @var TransferRepositoryInterface
     */
    private $transferRepository;

    public function __construct(TransferRepositoryInterface $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

    public function __invoke(ChangeTransfer $message)
    {
        $transfer = $this->transferRepository->find($message->getId());

        $beneficiary = $message->getBeneficiary();
        $debtor = $message->getDebtor();
        $amount = $message->getAmount();
        $date = $message->getDate();

        $transfer->change($beneficiary, $debtor, $amount, $date);

        $this->transferRepository->save($transfer);

        return $transfer;
    }
}
