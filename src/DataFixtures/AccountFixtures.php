<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Common\Persistence\ObjectManager;

class AccountFixtures extends BaseFixtures
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, Account::class, 4, function (ObjectManager $manager, Account $account) {

            $account->setName($this->faker->company)
                ->setAccountNo($this->faker->iban($this->faker->countryCode))
                ->setAlias($this->faker->name)
                ->setType('iban');

            $manager->persist($account);
        });

        $manager->flush();
    }
}
