<?php

namespace App\Application\Account\Handler;

use App\Application\Account\Command\WithdrawMoneyCommand;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\AccountAggregate;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class WithdrawMoneyCommandHandler implements MessageHandlerInterface
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function __invoke(WithdrawMoneyCommand $message)
    {
        $account = $this->accountRepository->find($message->getId());

        $account->withdraw($message->getMoney());

        $this->accountRepository->save($account);
    }
}
