<?php

namespace App\MessageHandler;

use App\Entity\StockDividend;
use App\Message\StockDividendsUpdated;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class StockDividendsUpdatedHandler implements MessageSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    public static function getHandledMessages(): iterable
    {
        yield StockDividendsUpdated::class => 'stockDividendsUpdated';
    }

    /**
     * @param StockDividendsUpdated $message
     *
     * @throws \Exception
     */
    public function stockDividendsUpdated(StockDividendsUpdated $message)
    {
        $this->logger->debug('Handling message StockDividendSaved', [
            'message' => $message
        ]);

        $stock = $message->getStock();

        $nextDividend = $stock->nextDividend();

        if ($nextDividend === null) {
            $preDividend = $stock->preDividend();

            if ($preDividend !== null && $preDividend->getExDate() > new \DateTime('-3 months')) {

                $nextDividend = new StockDividend();
                $nextDividend->setStatus(StockDividend::STATUS_PROJECTED)
                    ->setExDate($preDividend->getExDate()->add(new \DateInterval('P3M')))
                    ->setValue($preDividend->getValue())
                ;

                $stock->addDividend($nextDividend);
            }
        }

        $dividendYield = null;
        if ($nextDividend !== null) {
            $dividendYield = $nextDividend->getValue() * 4 / $stock->getValue() * 100;
        }

        $stock->setDividendYield($dividendYield);

        $this->em->persist($stock);
        $this->em->flush();
    }
}
