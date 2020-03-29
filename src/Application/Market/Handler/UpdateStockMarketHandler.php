<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\UpdateStockMarket;
use App\Application\Market\Repository\StockMarketRepositoryInterface;
use App\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateStockMarketHandler implements MessageHandlerInterface
{
    /**
     * @var StockMarketRepositoryInterface
     */
    private $stockMarketRepository;

    public function __construct(StockMarketRepositoryInterface $stockMarketRepository)
    {
        $this->stockMarketRepository = $stockMarketRepository;
    }

    public function __invoke(UpdateStockMarket $message)
    {
        $market = $this->stockMarketRepository->find($message->getId());
        if ($market === null) {
            throw new NotFoundException(
                'Stock market not found',
                [
                    'id' => $message->getId()
                ]
            );
        }

        $dirty = false;

        $name = $market->getName();
        if ($name !== $message->getName()) {
            $name = $message->getName();

            $dirty = true;
        }

        $currency = $market->getCurrency();
        if ($currency !== $message->getCurrency()) {
            $currency = $message->getCurrency();

            $dirty = true;
        }

        $country = $market->getCountry();
        if ($country !== $message->getCountry()) {
            $country = $message->getCountry();

            $dirty = true;
        }

        $symbol = $market->getSymbol();
        if ($symbol !== $message->getSymbol()) {
            $symbol = $message->getSymbol();

            $dirty = true;
        }

        $symbolYahoo = $market->getYahooSymbol();
        if ($symbolYahoo !== $message->getYahooSymbol()) {
            $symbolYahoo = $message->getYahooSymbol();

            $dirty = true;
        }

        if ($dirty) {
            $market->update($name, $currency, $country, $symbol, $symbolYahoo);

            $this->stockMarketRepository->save($market);
        }

        return $market;
    }
}
