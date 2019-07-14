<?php

namespace App\MessageHandler;

use App\Entity\Position;
use App\Message\UpdateWalletCapital;
use App\VO\Money;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateWalletCapitalHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->em = $em;
    }

    public function __invoke(UpdateWalletCapital $message)
    {
        $this->logger->debug('Handling message UpdateWalletCapital', [
            'message' => $message
        ]);

        $exchangeRates = $message->getExchangeRates();
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
                    $position->getAmount() * $stock->getValue()->exchange(
                        $wallet->getCurrency(),
                        $exchangeRates
                    )->getValue()
                );
            }

            $position->setCapital($pCapital);
            $this->em->persist($position);

            $capital = $capital->increase($pCapital);
        }

        $wallet->setCapital($capital);
        $this->em->persist($wallet);

        $this->em->flush();
    }
}
