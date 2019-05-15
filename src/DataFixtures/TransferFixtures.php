<?php

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Transfer;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TransferFixtures extends BaseFixtures implements DependentFixtureInterface
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

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            AccountFixtures::class,
        ];
    }
}
