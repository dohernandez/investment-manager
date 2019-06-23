<?php

namespace App\EventListener;

use App\Entity\Operation;
use App\Entity\Position;
use App\Entity\Trade;
use App\Repository\PositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class PersistOperationListener
{
    /**
     * @var PositionRepository
     */
    private $positionRepository;

    /**
     * @var ArrayCollection
     */
    private $operationCreated;

    public function __construct(PositionRepository $positionRepository)
    {
        $this->positionRepository = $positionRepository;
        $this->operationCreated = new ArrayCollection();
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var Operation $operation */
        $operation = $args->getObject();

        // only act on "Operation" entity
        if (!$operation instanceof Operation) {
            return;
        }

        // exclude "Operation" entity created in this class.
        if ($this->operationCreated->contains($operation)) {
            return;
        }

        $wallet = $operation->getWallet();

        $type = $operation->getType();
        // only act on "Operation::TYPE_BUY", "Operation::TYPE_SELL" and "Operation::TYPE_DIVIDEND"
        if (!in_array($type, [
                Operation::TYPE_BUY,
                Operation::TYPE_SELL,
                Operation::TYPE_DIVIDEND,
            ])) {

            return;
        }

        $stock = $operation->getStock();

        $position = $this->positionRepository->findOneByStockOpenOrDateAtOpen($operation->getStock());
        if ($position === null) {
            $position = new Position();
            $position->setStock($stock)
                ->setStatus(Position::STATUS_OPEN)
                ->setOpenedAt($operation->getDateAt())
                ;

            $wallet->addPosition($position);
        }

        $position->addOperation($operation);

        if ($type === Operation::TYPE_DIVIDEND) {
            $trades = $position->getTradesApplyDividend($stock, $operation->getDateAt());
            dump($trades);

            foreach ($trades as $trade) {
                // Calc how much in percentage represents the amount of stocks in the trade
                // compare against the whole position
                $pr = $trade->getAmount() * 100 / $position->getAmount();

                // this operation can drive into a diff of 0.01 cents
                $tradeDividend = round($operation->getValue() * $pr / 100, 2);
                $trade->increaseDividend($tradeDividend);
            }

            return;
        }

        if ($type === Operation::TYPE_BUY) {
            $trade = new Trade();
            $trade->setStock($stock)
                ->setStatus(Trade::STATUS_OPEN)
                ->setOpenedAt($operation->getDateAt())
                ->setWallet($operation->getWallet())
            ;

            $trade->addBuy($operation->getAmount(), $operation->getNetValue());

            $position->addTrade($trade);
        } else {
            $trades = $position->getOpenTrades();
            $netValue = $operation->getNetValue();
            // Calc position amount, at this point, the amount of the operation was already
            // subtract from the position, therefore to get the real actual position to calculate
            // the percentage above, we need to sum back the amount subtracted by the operation.
            $aPosition = ($position->getAmount() + $operation->getAmount());
            $aOperation = $operation->getAmount();

            foreach ($trades as $trade) {
                if ($aOperation - $trade->getAmount() <= 0) {
                    $trade->addSell($operation->getAmount(), $netValue);

                    if (!$trade->getAmount()) {
                        $trade->setStatus(Trade::STATUS_CLOSE)
                            ->setClosedAt($operation->getDateAt())
                        ;

                        $position->setStatus(Position::STATUS_CLOSE)
                            ->setClosedAt($operation->getDateAt())
                        ;
                    }

                    break;
                }

                // Calc how much in percentage represents the amount of stocks in the trade
                // compare against the whole position
                $pr = $trade->getAmount() * 100 / $aPosition;

                $prNetValue = round($operation->getNetValue() * $pr / 100, 2);

                $trade->addSell($trade->getAmount(), $prNetValue)
                    ->setStatus(Trade::STATUS_CLOSE)
                    ->setClosedAt($operation->getDateAt())
                ;

                $aOperation -= $trade->getAmount();
                $netValue -= $prNetValue;
            }
        }
    }
}
