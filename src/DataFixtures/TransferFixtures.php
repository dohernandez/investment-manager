<?php

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Transfer;
use Doctrine\Common\Persistence\ObjectManager;

class TransferFixtures extends BaseFixtures
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, Transfer::class, 8, function (ObjectManager $manager, Transfer $transfer) {

            $beneficiaryParty = $this->getRandomReference(Account::class);
            $deborParty = $this->getRandomReference(Account::class, [$beneficiaryParty]);

            $transfer->setBeneficiaryParty($beneficiaryParty)
                ->setDebtorParty($deborParty)
                ->setAmount($this->faker->randomFloat(2, 10, 1000))
                ->setDate($this->faker->dateTimeBetween('-1 years', 'now'))
                ;

            $manager->persist($transfer);
        });

        $manager->flush();
    }
}
