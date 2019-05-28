<?php

namespace App\EventListener;

use App\Entity\Wallet;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class PersistWalletListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var Wallet $wallet */
        $wallet = $args->getObject();

        // only act on "Wallet" entity
        if (!$wallet instanceof Wallet) {
            return;
        }

        $funds = $wallet->getBroker()->getAccount()->getBalance();

        $wallet
            ->setInvested($funds)
            ->setFunds($funds)
        ;
    }
}
