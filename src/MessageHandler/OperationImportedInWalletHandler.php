<?php

namespace App\MessageHandler;

use App\Message\OperationImportedInWallet;
use App\Message\UpdateWalletCapital;
use App\Repository\ExchangeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class OperationImportedInWalletHandler implements MessageHandlerInterface
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
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var ExchangeRepository
     */
    private $exchangeRepository;

    public function __construct(
        EntityManagerInterface $em,
        ExchangeRepository $exchangeRepository,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->em = $em;
        $this->bus = $bus;
        $this->exchangeRepository = $exchangeRepository;
    }

    public function __invoke(OperationImportedInWallet $message)
    {
        $this->logger->debug('Handling message OperationImportedInWallet', [
            'message' => $message
        ]);

        $wallet = $message->getWallet();

        $exchangeRates = $this->exchangeRepository->findAll();

        $this->bus->dispatch(new UpdateWalletCapital($wallet, $exchangeRates));
    }
}
