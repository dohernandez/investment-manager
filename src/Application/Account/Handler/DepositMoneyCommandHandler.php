<?php

namespace App\Application\Account\Handler;

use App\Application\Account\Event\AccountUpdated;
use App\Application\Account\Command\DepositMoneyCommand;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\AccountAggregate;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class DepositMoneyCommandHandler implements MessageHandlerInterface
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository) {
        $this->accountRepository = $accountRepository;
    }

    public function __invoke(DepositMoneyCommand $message)
    {
        $account = $this->accountRepository->find($message->getId());

        $account->deposit($message->getMoney());

        $this->accountRepository->save($account);
    }
}
