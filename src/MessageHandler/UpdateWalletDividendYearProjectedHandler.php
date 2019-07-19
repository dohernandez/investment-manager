<?php

namespace App\MessageHandler;

use App\Entity\Position;
use App\Entity\StockDividend;
use App\Message\UpdateWalletDividendYearProjected;
use App\VO\Money;
use App\VO\WalletDividendMonthMetadata;
use App\VO\WalletDividendYearMetadata;
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
        $dividendProjectedMonths = [];
        for ($i = 1; $i < $now->format('n'); $i++) {
            $dividendProjectedMonths[$i] = null;
        }

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
                    } elseif (
                        StockDividend::STATUS_ANNOUNCED == $dividend ->getStatus() &&
                        $dividend->getPaymentDate() < $now
                    ) {
                        $increase = true;
                    }

                    if ($increase) {
                        $dividendProjected = $dividend->getValue()
                            ->exchange($dividendProjectedYear->getCurrency(), $exchangeRates)
                            ->multiply($position->getAmount())
                        ;

                        $dividendProjectedYear = $dividendProjectedYear->increase($dividendProjected);

                        $month = $dividend->getExDate()->format('n');
                        if (!isset($dividendProjectedMonths[$month])) {
                            $dividendProjectedMonths[$month] = Money::fromCurrency($wallet->getCurrency());
                        }
                        $dividendProjectedMonths[$month] = $dividendProjectedMonths[$month]->increase($dividendProjected);
                    }
                }
            }
        }

        // Setting wallet metadata
        $metadata = $wallet->getMetadata();
        if ($metadata === null) {
            $metadata = new WalletMetadata();
        }

        // Setting dividend year metadata
        $dividendYearMetadata = $metadata->getDividendYear($year);
        if ($dividendYearMetadata === null) {
            $dividendYearMetadata = WalletDividendYearMetadata::fromYear($year);
        }

        /** @var WalletDividendYearMetadata $dividendYearMetadata */
        $dividendYearMetadata = $dividendYearMetadata->setProjected($dividendProjectedYear);

        // Setting dividend month metadata
        foreach ($dividendProjectedMonths as $month => $dividendProjectedMonth) {
            $dividendMonthMetadata = $dividendYearMetadata->getDividendMonth($month);
            if ($dividendMonthMetadata === null) {
                $dividendMonthMetadata = WalletDividendMonthMetadata::fromMonth($month);
            }
            $dividendMonthMetadata = $dividendMonthMetadata->setProjected($dividendProjectedMonth);

            $dividendYearMetadata = $dividendYearMetadata->setDividendMonth($month, $dividendMonthMetadata);
        }

        $wallet->setMetadata($metadata->setDividendYear($year, $dividendYearMetadata));

        $this->em->persist($wallet);

        $this->em->flush();
    }
}
