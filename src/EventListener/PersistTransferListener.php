<?php

namespace App\EventListener;

use App\Entity\Transfer;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class PersistTransferListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var Transfer $transfer */
        $transfer = $args->getObject();

        // only act on "Transfer" entity
        if (!$transfer instanceof Transfer) {
            return;
        }

        $beneficiary = $transfer->getBeneficiaryParty();
        $beneficiary->addDeposit($transfer->getAmount());

        $debtor = $transfer->getDebtorParty();
        $debtor->addWithdraw($transfer->getAmount());
    }
}
