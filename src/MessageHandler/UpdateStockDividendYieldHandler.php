<?php

namespace App\MessageHandler;

use App\Entity\Stock;
use App\Entity\StockDividend;
use App\Message\StockDividendDeleted;
use App\Message\StockDividendSaved;
use App\Repository\StockDividendRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class UpdateStockDividendYieldHandler implements MessageSubscriberInterface
{
    /**
     * @var StockDividendRepository
     */
    private $stockDividendRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(StockDividendRepository $stockDividendRepository, LoggerInterface $logger)
    {
        $this->stockDividendRepository = $stockDividendRepository;
        $this->logger = $logger;
    }

    public static function getHandledMessages(): iterable
    {
        yield StockDividendSaved::class => 'stockDividendSaved';
        yield StockDividendDeleted::class => 'stockDividendDeleted';
    }

    /**
     * @param StockDividendSaved $message
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function stockDividendSaved(StockDividendSaved $message)
    {
        $this->logger->debug('Handling message StockDividendSaved', [
            'message' => $message
        ]);

        $stockDividend = $message->getStockDividend();

        // checking if the stock dividend is a payed dividend or not
        if ($stockDividend->getStatus() == StockDividend::STATUS_PAYED) {
            return;
        }

        $stock = $message->getStockDividend()->getStock();

        // checking if stock dividend is the next dividend to be payed
        $nextDividend = $this->stockDividendRepository->findNextByStock($stock);
        if ($nextDividend !== null) {
            // when there is a next dividend to be payed
            // checking if they are the same entity
            if ($nextDividend->getId() === $stockDividend->getId()) {
                // checking that the value has not changed
                if ($nextDividend->getValue() === $stockDividend->getValue()) {
                    $this->logger->debug('Skipping update dividend yield, equals value', [
                        'stored' => $nextDividend->getValue(),
                        'current' => $stockDividend->getValue(),
                    ]);
                    return;
                }
            } elseif ($nextDividend->getExDate() < $stockDividend->getExDate()) {
                $this->logger->debug('Skipping update dividend yield, after next ex date', [
                    'stored' => $nextDividend->getExDate(),
                    'current' => $stockDividend->getExDate(),
                ]);
                return;
            }
        }

        $this->updateStockDividendYield($stock, $stockDividend);
    }

    /**
     * @param Stock $stock
     * @param StockDividend $stockDividend
     */
    protected function updateStockDividendYield(Stock $stock, ?StockDividend $stockDividend)
    {
        $dividendYield = null;

        if ($stockDividend !== null) {
            $dividendYield = $stockDividend->getValue() * 4 / $stock->getValue() * 100;
        }

        $stock->setDividendYield($dividendYield);

        $this->logger->debug('Update dividend Yield', [
            'stock' => $stock,
            'dividend' => $stockDividend,
        ]);
    }

    /**
     * @param StockDividendDeleted $message
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function stockDividendDeleted(StockDividendDeleted $message)
    {
        $this->logger->debug('Handling message StockDividendDeleted', [
            'message' => $message
        ]);

        $stockDividend = $message->getStockDividend();
        $stock = $message->getStockDividend()->getStock();

        $nextDividend = $this->stockDividendRepository->findNextByStock($stock);
        // checking if they are not the same entity
        if ($nextDividend->getId() !== $stockDividend->getId()) {
            $this->logger->debug('Skipping update dividend yield, it wasn\'t use on the calculation', [
                'stored' => $nextDividend->getId(),
                'current' => $stockDividend->getId(),
            ]);
            return;
        }

        $exDate = $nextDividend->getExDate();
        date_add(
            $exDate,
            date_interval_create_from_date_string('1 day')
        );
        $nextDividend = $this->stockDividendRepository->findNextByStock($stock, $exDate);
        $this->updateStockDividendYield($stock, $nextDividend);
    }
}
