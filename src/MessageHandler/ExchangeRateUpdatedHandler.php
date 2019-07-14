<?php

namespace App\MessageHandler;

use App\Message\ExchangeRateUpdated;
use App\Message\UpdateWalletCapital;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ExchangeRateUpdatedHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(
        EntityManagerInterface $em,
        WalletRepository $walletRepository,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->em = $em;
        $this->walletRepository = $walletRepository;
        $this->bus = $bus;
    }

    public function __invoke(ExchangeRateUpdated $message)
    {
        $this->logger->debug('Handling message ExchangeRateUpdated', [
            'message' => $message
        ]);

        $exchangeRates = $message->getExchangeRates();

        $wallets = $this->walletRepository->findAll();
        foreach ($wallets as $wallet) {
            $this->bus->dispatch(new UpdateWalletCapital($wallet, $exchangeRates));
        }
    }
}
