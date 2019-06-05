<?php

namespace App\EventListener;

use App\Entity\Operation;
use App\Entity\Position;
use App\Entity\Trade;
use App\Repository\PositionRepository;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class PersistOperationListener
{
    /**
     * @var PositionRepository
     */
    private $positionRepository;

    public function __construct(PositionRepository $positionRepository)
    {
        $this->positionRepository = $positionRepository;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var Operation $operation */
        $operation = $args->getObject();

        // only act on "Operation" entity
        if (!$operation instanceof Operation) {
            return;
        }

        $type = $operation->getType();
        // only act on "Operation::TYPE_BUY", "Operation::TYPE_SELL" and "Operation::TYPE_DIVIDEND"
        if (!in_array($type, [
                Operation::TYPE_BUY,
                Operation::TYPE_SELL,
                Operation::TYPE_DIVIDEND,
            ])) {
            $operation->getWallet()->addExpenses($operation->getNetValue(), $type);

            return;
        }

        $stock = $operation->getStock();

        $position = $this->positionRepository->findOneByStockOpen($operation->getStock());
        if ($position === null) {
            $position = new Position();
            $position->setStock($stock)
                ->setStatus(Position::STATUS_OPEN)
                ;
        }

        $position->addOperation($operation);

        $trade = $operation->getTrade();
        if ($trade === null) {
            $trade = new Trade();
            $trade->setStock($stock)
                ->setStatus(Trade::STATUS_OPEN)
                ->setOpenedAt($operation->getDateAt())
                ->setWallet($operation->getWallet())
            ;

            $trade->addOperation($operation);
        }

        if ($type === Operation::TYPE_DIVIDEND) {
            // TODO dividend should be added to all trades opens with for the given stock
        }

        $position->addTrade($trade);
    }
}
