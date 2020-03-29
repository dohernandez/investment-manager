<?php

namespace App\Application\Market\Subscriber;

use App\Application\Market\Repository\HouseKeeperRepositoryInterface;
use App\Domain\Market\Event\StockDividendSynched;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HouseKeeperSubscriber implements EventSubscriberInterface
{
    /**
     * @var HouseKeeperRepositoryInterface
     */
    private $houseKeeperStockDividendRepository;

    public function __construct(HouseKeeperRepositoryInterface $houseKeeperStockDividendRepository)
    {
        $this->houseKeeperStockDividendRepository = $houseKeeperStockDividendRepository;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            StockDividendSynched::class => ['onHouseKeeperStockDividend', 100],
        ];
    }

    public function onHouseKeeperStockDividend(StockDividendSynched $event)
    {
        $this->houseKeeperStockDividendRepository->clean();
    }
}
