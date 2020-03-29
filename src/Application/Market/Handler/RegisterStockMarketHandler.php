<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\RegisterStockMarket;
use App\Application\Market\Repository\StockMarketRepositoryInterface;
use App\Domain\Market\StockMarket;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RegisterStockMarketHandler implements MessageHandlerInterface
{
    /**
     * @var StockMarketRepositoryInterface
     */
    private $stockMarketRepository;

    public function __construct(StockMarketRepositoryInterface $stockMarketRepository)
    {
        $this->stockMarketRepository = $stockMarketRepository;
    }

    public function __invoke(RegisterStockMarket $message)
    {
        $market = StockMarket::register(
            $message->getName(),
            $message->getCurrency(),
            $message->getCountry(),
            $message->getSymbol(),
            $message->getYahooSymbol()
        );

        $this->stockMarketRepository->save($market);

        return $market;
    }
}
