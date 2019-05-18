<?php

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Broker;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BrokerFixtures extends BaseFixtures implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, Broker::class, 1, function (ObjectManager $manager, Broker $broker) {

            $account = $this->getRandomReference(Account::class);

            $broker->setName($this->faker->name)
                ->setSite($this->faker->url)
                ->setAccount($account)
            ;

            $manager->persist($broker);
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
