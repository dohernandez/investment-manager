<?php

namespace App\Application\Account\Handler;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Application\Account\Event\AccountCreated;
use App\Domain\Account\Projection\Account;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CreateAccountProjectionHandler implements MessageHandlerInterface
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function __invoke(AccountCreated $message)
    {
        $account = new Account($message->getId());
        $account->setName($message->getName());
        $account->setType($message->getType());
        $account->setAccountNo($message->getAccountNo());
        $account->setBalance($message->getBalance());
        $account->setCreatedAt($message->getCreatedAt());
        $account->setUpdatedAt($message->getCreatedAt());

        $this->accountRepository->save($account);
    }
}
