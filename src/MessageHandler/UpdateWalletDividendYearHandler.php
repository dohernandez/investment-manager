<?php

namespace App\MessageHandler;

use App\Entity\Position;
use App\Entity\StockDividend;
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

        $dividendYear = $wallet->getDividendYear() ?? [];
        $dividendProjectedYear = Money::fromCurrency($wallet->getCurrency());
        $dividendPaidYear = Money::fromCurrency($wallet->getCurrency());

        /** @var Position $position */
        foreach ($wallet->getPositions(Position::STATUS_OPEN) as $position) {

            $stock = $position->getStock();
            if ($stock === null) {
                continue;
            }

            /** @var StockDividend $dividend */
            foreach ($stock->yearDividends() as $dividend) {
                switch ($dividend->getStatus()) {
                    case StockDividend::STATUS_ANNOUNCED:
                        if ($dividend->getExDate() > $now) {
                            $dividendProjectedYear = $dividendProjectedYear->increase(
                                $dividend->getValue()
                                    ->exchange($dividendProjectedYear->getCurrency(), $exchangeRates)
                                    ->multiply($position->getAmount())
                            );

                            break;
                        }

                        //TODO add to the pending
                        break;

                    case StockDividend::STATUS_PAYED:
                        // TODO need to sync the operation "dividend type" with the dividend.
//                        $dividendPaidYear = $dividendPaidYear->increase($dividend->get)

                        break;

                    default:
                        $dividendProjectedYear = $dividendProjectedYear->increase(
                            $dividend->getValue()
                                ->exchange($dividendProjectedYear->getCurrency(), $exchangeRates)
                                ->multiply($position->getAmount())
                        );
                }
            }
        }

        if (!isset($dividendYear[$year])) {
            $dividendYear[$year] = DividendYear::fromYearCurrency($year, $wallet->getCurrency());
        }

        $dividendYear[$year] = $dividendYear[$year]->changeProjected($dividendProjectedYear);

        $wallet->setDividendYear($dividendYear);
        $this->em->persist($wallet);

        $this->em->flush();
    }
}
