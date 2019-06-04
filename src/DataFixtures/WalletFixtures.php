<?php

namespace App\DataFixtures;

use App\Entity\Broker;
use App\Entity\Wallet;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class WalletFixtures extends BaseFixtures implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, Wallet::class, 1, function (ObjectManager $manager, Wallet $wallet) {

            $broker = $this->getRandomReference(Broker::class);

            $wallet->setName($this->faker->name)
                ->setBroker($broker)
                ;

            $manager->persist($wallet);
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
            BrokerFixtures::class,
        ];
    }
}
