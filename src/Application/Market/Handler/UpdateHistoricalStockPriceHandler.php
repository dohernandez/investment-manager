<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Client\PricesClientInterface;
use App\Application\Market\Command\UpdateHistoricalStockPrice;
use App\Application\Market\Repository\StockRepositoryInterface;
use App\Domain\Market\MarketData;
use App\Infrastructure\Context\Context;
use App\Infrastructure\Context\Logger;
use App\Infrastructure\Exception\NotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateHistoricalStockPriceHandler implements MessageHandlerInterface
{
    /**
     * @var PricesClientInterface
     */
    private $pricesClient;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PricesClientInterface $pricesClient,
        StockRepositoryInterface $stockRepository,
        LoggerInterface $logger
    ) {
        $this->pricesClient = $pricesClient;
        $this->stockRepository = $stockRepository;
        $this->logger = $logger;
    }

    public function __invoke(UpdateHistoricalStockPrice $message)
    {
        $stock = $this->stockRepository->find($message->getId());
        if (!$stock) {
            throw new NotFoundException(
                'Stock not found',
                [
                    'id' => $message->getId()
                ]
            );
        }

        $context = Logger::toContext(Context::TODO(), $this->logger);

        $data = $this->pricesClient->getHistoricalData(
            $context,
            $stock->getCurrency(),
            $stock->getMetadata() ?
                $stock->getMetadata()->getYahooSymbol() ?? $stock->getSymbol() :
                $stock->getSymbol(),
            $stock->getHistoricalUpdatedAt()
        );

        $historicalData = new ArrayCollection();

        /** @var MarketData $datum */
        foreach ($data as $datum) {
            $datum->setStock($stock);
            $historicalData->add($datum);
        }

        $stock->setHistoricalData($historicalData);
        $this->stockRepository->save($stock);
    }
}
