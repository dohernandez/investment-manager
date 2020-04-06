<?php

namespace App\Domain\Wallet;

use App\Infrastructure\Money\Currency;
use Doctrine\Common\Collections\ArrayCollection;

class BookEntryMetadata
{
    /**
     * @var ArrayCollection|ExchangeTicket[]
     */
    private $exchangeTickets;

    public function __construct()
    {
        $this->exchangeTickets = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|ExchangeTicket[]
     */
    public function getExchangeTickets(): ArrayCollection
    {
        return $this->exchangeTickets;
    }

    /**
     * @param ArrayCollection|ExchangeTicket[] $exchangeTickets
     *
     * @return $this
     */
    public function setExchangeTickets(ArrayCollection $exchangeTickets): self
    {
        $this->exchangeTickets = $exchangeTickets;

        return $this;
    }

    public function setExchangeTicket(Currency $currency, ExchangeTicket $exchangeTicket): self
    {
        $this->exchangeTickets->set($currency->getCurrencyCode(), $exchangeTicket);

        return $this;
    }

    public function getExchangeTicket(Currency $currency): ?ExchangeTicket
    {
        return $this->exchangeTickets->get($currency->getCurrencyCode());
    }
}
