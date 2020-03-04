<?php

namespace App\Presentation\Console;

use App\Infrastructure\Money\Currency as CurrencyEventSource;
use App\Infrastructure\Money\Money as MoneyEventSource;
use App\VO\Currency;
use App\VO\Money;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

abstract class Console extends Command
{
    /**
     * @var MessageBusInterface
     */
    protected $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();

        $this->bus = $bus;
    }

    protected function convertMoneyEventSource(Money $money): MoneyEventSource
    {
        return new MoneyEventSource(
            $this->convertCurrencyEventSource($money->getCurrency()),
            $money->getValue(),
            $money->getPrecision()
        );
    }

    protected function convertCurrencyEventSource(Currency $currency): CurrencyEventSource
    {
        return CurrencyEventSource::fromCode($currency->getCurrencyCode());
    }
    /**
     * @param mixed $command
     *
     * @return mixed
     */
    protected function handle($command)
    {
        $envelope = $this->bus->dispatch($command);

        // get the value that was returned by the last message handler
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp->getResult();
    }
}
