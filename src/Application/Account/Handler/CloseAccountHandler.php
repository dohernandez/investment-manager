<?php

namespace App\Application\Account\Handler;

use App\Application\Account\Command\CloseAccount;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\AccountAggregate;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CloseAccountHandler implements MessageHandlerInterface
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function __invoke(CloseAccount $message)
    {
        $account = $this->accountRepository->find($message->getId());

        $account->close();

        $this->accountRepository->save($account);
    }
}
