<?php

namespace App\EventListener;

use App\Entity\Stock;
use App\Entity\StockDividend;
use App\Message\StockDividendsUpdated;
use Doctrine\ORM\Event\PreFlushEventArgs;
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

    public function preFlush(PreFlushEventArgs $args)
    {

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $this->logger->info('preFlush');

        foreach ($uow->getIdentityMap() as $class => $entities) {
            $this->logger->debug('getIdentityMap', [
                'class' => $class,
            ]);

            // only act on "Stock" entity
            if ($class !== Stock::class) {
                return;
            }

            foreach ($entities as $entity) {
                $this->updateNextDividend($entity);
                $this->updateDividendYield($entity);

                $em->persist($entity);
            }
        }
    }

    public function updateNextDividend(Stock $stock)
    {
        $nextDividend = $stock->nextDividend();

        if ($nextDividend === null) {
            $preDividend = $stock->preDividend();

            if ($preDividend !== null && $preDividend->getExDate() > new \DateTime('-3 months')) {
                $exDate = clone $preDividend->getExDate();
                $exDate = $exDate->add(new \DateInterval('P3M'));

                $nextDividend = new StockDividend();
                $nextDividend->setStatus(StockDividend::STATUS_PROJECTED)
                    ->setExDate($exDate)
                    ->setValue($preDividend->getValue())
                ;

                $stock->addDividend($nextDividend);

                $this->logger->debug('added next dividend', [
                    'symbol' => $stock->getSymbol(),
                    'next_dividend' => $nextDividend,
                ]);
            }
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
