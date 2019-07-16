<?php

namespace App\MessageHandler;

use App\Entity\Position;
use App\Entity\StockDividend;
use App\Message\UpdateWalletDividendYearProjected;
use App\VO\Money;
use App\VO\WalletDividendMetadata;
use App\VO\WalletMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateWalletDividendYearProjectedHandler implements MessageHandlerInterface
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

    public function __invoke(UpdateWalletDividendYearProjected $message)
    {
        $this->logger->debug('Handling message UpdateWalletCapital', [
            'message' => $message
        ]);

        $now = new \DateTimeImmutable();
        $year = $now->format('Y');
        $exchangeRates = $message->getExchangeRates();
        $wallet = $message->getWallet();

        $dividendProjectedYear = Money::fromCurrency($wallet->getCurrency());

        /** @var Position $position */
        foreach ($wallet->getPositions(Position::STATUS_OPEN) as $position) {

            $stock = $position->getStock();
            if ($stock === null) {
                continue;
            }

            /** @var StockDividend $dividend */
            foreach ($stock->yearDividends() as $dividend) {
                if (StockDividend::STATUS_ANNOUNCED || StockDividend::STATUS_PROJECTED) {
                    $increase = false;

                    if ($dividend->getExDate() > $now) {
                        $increase = true;
                    } elseif (StockDividend::STATUS_ANNOUNCED && $dividend->getPaymentDate() < $now) {
                        $increase = true;
                    }

                    if ($increase) {
                        $dividendProjectedYear = $dividendProjectedYear->increase(
                            $dividend->getValue()
                                ->exchange($dividendProjectedYear->getCurrency(), $exchangeRates)
                                ->multiply($position->getAmount())
                        );
                    }
                }
            }
        }

        $metadata = $wallet->getMetadata();
        if ($metadata === null) {
            $metadata = new WalletMetadata();
        }

        $dividendMetadata = $metadata->getDividendYear($year);
        if ($dividendMetadata === null) {
            $dividendMetadata = WalletDividendMetadata::fromYear($year);
        }
        $dividendMetadata = $dividendMetadata->setProjected($dividendProjectedYear);

        $wallet->setMetadata($metadata->setDividendYear($year, $dividendMetadata));

        $this->em->persist($wallet);

        $this->em->flush();
    }
}
