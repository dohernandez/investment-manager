<?php

namespace App\Application\Account\Handler;

use App\Application\Account\Event\AccountUpdated;
use App\Application\Account\Repository\AccountRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateAccountProjectionHandler implements MessageHandlerInterface
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function __invoke(AccountUpdated $message)
    {
        $account = $this->accountRepository->find($message->getId());

        $account->setBalance($message->getBalance());
        $account->setUpdatedAt($message->getUpdatedAt());

        $this->accountRepository->save($account);
    }
}
