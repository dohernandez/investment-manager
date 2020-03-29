<?php

namespace App\Application\Transfer\Handler;

use App\Application\Transfer\Command\RemoveTransfer;
use App\Application\Transfer\Repository\TransferRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RemoveTransferHandler implements MessageHandlerInterface
{
    /**
     * @var TransferRepositoryInterface
     */
    private $transferRepository;

    public function __construct(TransferRepositoryInterface $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

    public function __invoke(RemoveTransfer $message)
    {
        $transfer = $this->transferRepository->find($message->getId());

        $transfer->remove();

        $this->transferRepository->delete($transfer);
    }
}
