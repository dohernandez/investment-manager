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

        $position = $this->positionRepository->findOneByStockOpen($operation->getStock());
        if ($position === null) {
            $position = new Position();
            $position->setStock($stock)
                ->setStatus(Position::STATUS_OPEN)
                ;

            $wallet->addPosition($position);
        }

        $position->addOperation($operation);

        if ($type === Operation::TYPE_DIVIDEND) {
            // TODO dividend should be added to all trades opens with for the given stock

            return;
        }

        $trade = $operation->getTrade();
        if ($trade === null){
            if ($type === Operation::TYPE_BUY) {
                $trade = new Trade();
                $trade->setStock($stock)
                    ->setStatus(Trade::STATUS_OPEN)
                    ->setOpenedAt($operation->getDateAt())
                    ->setWallet($operation->getWallet())
                ;

                $position->addTrade($trade);

                $trade->addOperation($operation);
            } else {
                $trades = $position->getTrades(Trade::STATUS_OPEN);
                $closePosition = false;

                foreach ($trades as $trade) {
                    if ($operation->getAmount() - $trade->getAmount() <= 0) {
                        $trade->addOperation($operation);

                        if (!$trade->getAmount()) {
                            $trade->setStatus(Position::STATUS_CLOSE);
                            $trade->setClosedAt($operation->getDateAt());

                            $closePosition = true;
                        }

                        break;
                    }

                    $op = new Operation();
                    $op->setType($operation->getType())
                        ->setWallet($operation->getWallet())
                        ->setDateAt($operation->getDateAt())
                        ->setStock($operation->getStock())
                        ->setAmount($trade->getAmount())
                        ->setPrice($operation->getPrice())
                        ->setPriceChange($operation->getPriceChange())
                    ;

                    // Calc position amount, at this point, the amount of the operation was already
                    // subtract from the position, therefore to get the real actual position to calculate
                    // the percentage above, we need to sum back the amount subtracted by the operation.
                    $aPosition = ($position->getAmount() + $operation->getAmount());

                    // Calc how much in percentage represents the amount of stocks in the trade
                    // compare against the whole position
                    $pr = $trade->getAmount() * 100 / $aPosition;
                    $priceChangeCommission = round($operation->getPriceChangeCommission() * $pr / 100, 2);
                    $commission = round($operation->getCommission() * $pr / 100, 2);
                    $value = round($operation->getValue() * $pr / 100, 2);

                    // Allocate the change commission price, commission and value to the new operation based on the portion
                    // of the position the trade represent
                    $op->setPriceChangeCommission($priceChangeCommission);
                    $op->setCommission($commission);
                    $op->setValue($value);

                    $trade->addOperation($operation);
                    $trade->setStatus(Trade::STATUS_CLOSE);
                    $trade->setClosedAt($operation->getDateAt());

                    // subtract the change commission price, commission, value and the amount allocated into the trade
                    $operation->setPriceChangeCommission($operation->getPriceChangeCommission() - $op->getPriceChangeCommission());
                    $operation->setCommission($operation->getCommission() - $op->getCommission());
                    $operation->setAmount($operation->getAmount() - $op->getAmount());
                    $operation->setValue($operation->getValue() - $op->getValue());

                    // add new operation to the operations created by this class
                    $this->operationCreated->add($op);
                }

                if ($closePosition) {
                    $position->setStatus(Position::STATUS_CLOSE);
                }
            }
        } else {
            $trade->addOperation($operation);
        }
    }
}
