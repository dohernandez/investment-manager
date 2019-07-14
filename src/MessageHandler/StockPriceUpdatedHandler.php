<?php

namespace App\MessageHandler;

use App\Message\ExchangeRateUpdated;
use App\Message\StockPriceUpdated;
use App\Message\UpdateWalletCapital;
use App\Repository\ExchangeRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class StockPriceUpdatedHandler implements MessageHandlerInterface
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

    /**
     * @var ExchangeRepository
     */
    private $exchangeRepository;

    public function __construct(
        EntityManagerInterface $em,
        WalletRepository $walletRepository,
        ExchangeRepository $exchangeRepository,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->em = $em;
        $this->walletRepository = $walletRepository;
        $this->bus = $bus;
        $this->exchangeRepository = $exchangeRepository;
    }

    public function __invoke(StockPriceUpdated $message)
    {
        $this->logger->debug('Handling message StockPriceUpdated', [
            'message' => $message
        ]);

        $exchangeRates = $this->exchangeRepository->findAll();

        $wallets = $this->walletRepository->findAll();
        foreach ($wallets as $wallet) {
            $this->bus->dispatch(new UpdateWalletCapital($wallet, $exchangeRates));
        }
    }
}
