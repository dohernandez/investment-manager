<?php

namespace App\Tests\Application\Market\Handler;

use App\Application\Market\Command\RegisterStockMarket;
use App\Application\Market\Handler\RegisterStockMarketHandler;
use App\Application\Market\Repository\StockMarketRepositoryInterface;
use App\Domain\Market\StockMarket;
use App\Infrastructure\Money\Currency;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

final class RegisterStockMarketHandlerTest extends TestCase
{

    public function testInvoke()
    {
        $name = 'Stock Market';
        $currency = Currency::usd();
        $country = 'US';
        $symbol = 'NasdaqGS';
        $yahooSymbol = 'YNasdaqGS';

        $stockMarketRepository = $this->prophesize(StockMarketRepositoryInterface::class);
        $stockMarketRepository->save(
            Argument::that(
                function (StockMarket $market) use ($name, $currency, $country, $symbol, $yahooSymbol) {
                    $this->assertEquals($name, $market->getName());
                    $this->assertEquals($currency, $market->getCurrency());
                    $this->assertEquals($country, $market->getCountry());
                    $this->assertEquals($symbol, $market->getSymbol());
                    $this->assertEquals($yahooSymbol, $market->getYahooSymbol());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new RegisterStockMarketHandler($stockMarketRepository->reveal());
        $handler(new RegisterStockMarket($name, $currency, $country, $symbol, $yahooSymbol));
    }
}
