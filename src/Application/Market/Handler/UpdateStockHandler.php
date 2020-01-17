<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\UpdateStock;
use App\Application\Market\Repository\StockInfoRepositoryInterface;
use App\Application\Market\Repository\StockRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateStockHandler implements MessageHandlerInterface
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StockInfoRepositoryInterface
     */
    private $stockInfoRepository;

    public function __construct(
        StockRepositoryInterface $stockRepository,
        StockInfoRepositoryInterface $stockInfoRepository
    ) {
        $this->stockRepository = $stockRepository;
        $this->stockInfoRepository = $stockInfoRepository;
    }

    public function __invoke(UpdateStock $message)
    {
        $stock = $this->stockRepository->find($message->getId());

        $dirty = false;

        $name = $stock->getName();
        if ($name !== $message->getName()) {
            $name = $message->getName();

            $dirty = true;
        }

        $yahooSymbol = $stock->getMetadata()->getYahooSymbol();
        if ($yahooSymbol !== $message->getYahooSymbol()) {
            $yahooSymbol = $message->getYahooSymbol();

            $dirty = true;
        }

        $market = $stock->getMarket();
        if ($market->getId() !== $message->getMarket()->getId()) {
            $market = $message->getMarket();

            $dirty = true;
        }

        $description = $stock->getDescription();
        if ($description !== $message->getDescription()) {
            $description = $message->getDescription();

            $dirty = true;
        }

        $type = $stock->getType();
        if (
            !$type && $message->getType() || $type && !$message->getType() ||
            $type && $message->getType() && $type->getId() !== $message->getType()->getId()
        ) {
            $type = $message->getType();

            $dirty = true;
        }

        $sector = $stock->getSector();
        if (
            !$sector && $message->getSector() || $sector && !$message->getSector() ||
            $sector && $message->getSector() && $sector->getId() !== $message->getSector()->getId()
        ) {
            $sector = $message->getSector();

            $dirty = true;
        }

        $industry = $stock->getIndustry();
        if (
            !$industry && $message->getIndustry() || $industry && !$message->getIndustry() ||
            $industry && $message->getIndustry() && $industry->getId() !== $message->getIndustry()->getId()
        ) {
            $industry = $message->getIndustry();

            $dirty = true;
        }

        if ($type) {
            $this->stockInfoRepository->save($type);
        }

        if ($sector) {
            $this->stockInfoRepository->save($sector);
        }

        if ($industry) {
            $this->stockInfoRepository->save($industry);
        }

        if ($dirty) {
            $stock->update($name, $yahooSymbol, $market, $description, $type, $sector, $industry);
            $this->stockRepository->save($stock);
        }

        return $stock;
    }
}
