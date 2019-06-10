<?php

namespace App\EventListener;

use App\Entity\Stock;
use App\Entity\StockDividend;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Psr\Log\LoggerInterface;

class FlushStockListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $stock) {
            // only act on "Stock" entity
            if (!$stock instanceof Stock) {
                return;
            }

            $this->logger->debug('updating dividend yield', [
                'schedule' => 'insertions',
                'symbol' => $stock->getSymbol(),
            ]);

            $this->updateDividendYield($stock);
        }

        foreach ($uow->getScheduledEntityUpdates() as $stock) {
            // only act on "Stock" entity
            if (!$stock instanceof Stock) {
                return;
            }

            $this->logger->debug('updating dividend yield', [
                'schedule' => 'updates',
                'symbol' => $stock->getSymbol(),
            ]);

            $this->updateDividendYield($stock);
        }
    }

    private function updateDividendYield(Stock $stock)
    {
        $dividendYield = null;

        $nextDividend = $stock->nextDividend();
        if ($nextDividend !== null) {
            $dividendYield = $nextDividend->getValue() * 4 / $stock->getValue() * 100;
        }

        $this->logger->debug('update dividend yield', [
            'symbol' => $stock->getSymbol(),
            'dividend_yield' => $dividendYield,
        ]);

        $stock->setDividendYield($dividendYield);
    }
}
