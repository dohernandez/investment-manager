<?php

namespace App\Application\Wallet\Handler;

use App\Application\Wallet\Command\CreateWallet;
use App\Application\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\Wallet;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateWalletHandler implements MessageHandlerInterface
{
    /**
     * @var WalletRepositoryInterface
     */
    private $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function __invoke(CreateWallet $message)
    {
        $wallet = Wallet::create(
          $message->getName(),
          $message->getBroker(),
          $message->getAccount()
        );

        $this->walletRepository->save($wallet);

        return $wallet;
    }
}
