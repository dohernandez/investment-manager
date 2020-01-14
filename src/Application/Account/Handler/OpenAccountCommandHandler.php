<?php

namespace App\Application\Account\Handler;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account;
use App\Application\Account\Command\OpenAccountCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class OpenAccountCommandHandler implements MessageHandlerInterface
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository) {
        $this->accountRepository = $accountRepository;
    }

    public function __invoke(OpenAccountCommand $message)
    {
        $account = Account::open(
            $message->getName(),
            $message->getType(),
            $message->getAccountNo(),
            $message->getCurrency()
        );

        $this->accountRepository->save($account);

        return $account;
    }
}
