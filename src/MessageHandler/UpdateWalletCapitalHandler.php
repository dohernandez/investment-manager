<?php

namespace App\MessageHandler;

use App\Entity\Position;
use App\Message\UpdateWalletCapital;
use App\Repository\ExchangeRepository;
use App\VO\Money;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UpdateWalletCapitalHandler
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
     * @var ExchangeRepository
     */
    private $exchangeRepository;

    public function __construct(
        EntityManagerInterface $em,
        ExchangeRepository $exchangeRepository,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->em = $em;
        $this->exchangeRepository = $exchangeRepository;
    }

    public static function getHandledMessages(): iterable
    {
        yield UpdateWalletCapital::class => 'updateWalletCapital';
    }

    public function updateWalletCapital(UpdateWalletCapital $message)
    {
        $this->logger->debug('Handling message UpdateWalletCapital', [
            'message' => $message
        ]);

        $rateExchange = $this->exchangeRepository->findAll();
        $wallet = $message->getWallet();

        $capital = Money::fromCurrency($wallet->getCurrency());
        /** @var Position $position */
        foreach ($wallet->getPositions(Position::STATUS_OPEN) as $position) {
            $pCapital = null;

            $stock = $position->getStock();
            if ($stock === null || !$stock->getValue()) {
                $pCapital = $position->getInvested();
            } else {
                $pCapital = Money::from(
                    $wallet->getCurrency(),
                    $position->getAmount() * ($stock->getValue()->exchange($wallet->getCurrency(), $rateExchange))
                );
            }

            $position->setCapital($pCapital);
            $this->em->persist($position);

            $capital->increase($pCapital);
        }

        $wallet->setCapital($capital);
        $this->em->persist($wallet);

        $this->em->flush();
    }
}
