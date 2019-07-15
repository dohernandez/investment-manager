<?php

namespace App\MessageHandler;

use App\Entity\Position;
use App\Entity\StockDividend;
use App\Entity\WalletDividendMetadata;
use App\Entity\WalletMetadata;
use App\Message\UpdateWalletCapital;
use App\Message\UpdateWalletDividendYear;
use App\VO\DividendYear;
use App\VO\Money;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateWalletDividendYearHandler implements MessageHandlerInterface
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

    public function __invoke(UpdateWalletDividendYear $message)
    {
        $this->logger->debug('Handling message UpdateWalletCapital', [
            'message' => $message
        ]);

        $now = new \DateTimeImmutable();
        $year = $now->format('Y');
        $exchangeRates = $message->getExchangeRates();
        $wallet = $message->getWallet();

        $metadata = $wallet->getMetadata() ?? new WalletMetadata();
        $dividends = $metadata->getDividends() ?? [];

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

        if (!isset($dividends[$year])) {
            $dividends[$year] = (new WalletDividendMetadata())
                                    ->setProjected($dividendProjectedYear)
            ;
        }

        $metadata->setDividends($dividends);
        $wallet->setMetadata($metadata);

        $this->em->persist($wallet);

        $this->em->flush();
    }
}
