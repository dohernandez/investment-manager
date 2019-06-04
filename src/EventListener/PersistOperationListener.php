<?php

namespace App\EventListener;

use App\Entity\Operation;
use App\Entity\Trade;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class PersistOperationListener
{
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

        $trade = $operation->getTrade();
        if ($trade === null) {
            $trade = new Trade();
            $trade->setStock($operation->getStock())
                ->setStatus(Trade::STATUS_OPEN)
                ->setOpenedAt($operation->getDateAt())
                ->setWallet($operation->getWallet())
            ;

            $operation->setTrade($trade);
        }

        switch ($type) {
            case Operation::TYPE_BUY:
                $trade->setBuyAmount($operation->getAmount())
                    ->setBuyPaid($operation->getNetValue())
                    ;
                break;
            case Operation::TYPE_SELL:
                $trade->setSellAmount($operation->getAmount())
                    ->setSellPaid($operation->getNetValue())
                    ;
                break;
            case Operation::TYPE_DIVIDEND:
                $trade->increaseDividend($operation->getNetValue())
                    ;
                break;
        }
    }
}
