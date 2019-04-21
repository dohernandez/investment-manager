<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Common\Persistence\ObjectManager;

class AccountFixtures extends BaseFixtures
{
    public function loadData(ObjectManager $manager)
    {
        for ($i = 0; $i < 4; $i++) {
            $account = new Account();

            $account->setName($this->faker->company);
            $account->setAccountNo($this->faker->iban($this->faker->countryCode));
            $account->setAlias($this->faker->name);
            $account->setType('iban');

            $manager->persist($account);
        }



    }
}
