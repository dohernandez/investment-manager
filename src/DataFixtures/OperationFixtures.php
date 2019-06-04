<?php

namespace App\DataFixtures;

use App\Entity\Stock;
use App\Entity\Operation;
use App\Entity\Wallet;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class OperationFixtures extends BaseFixtures implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, Operation::class, 10, function (ObjectManager $manager, Operation $operation) {

            /** @var Stock $stock */
            $stock = $this->getRandomReference(Stock::class);

            $operation->setStock($stock)
                ->setAmount($this->faker->numberBetween(1, 50))
                ->setPrice($stock->getValue())
                ->setCommission($this->faker->numberBetween(1, 2))
                ->setPriceChange(0.16)
                ->setPriceChangeCommission($this->faker->numberBetween(1, 1.40))
                ->setDateAt($this->faker->dateTimeThisYear)
                ->setType(Operation::TYPE_BUY)
                ;

            $operation->setValue(
                // convert the price from $ into €
                $operation->getAmount() * $operation->getPrice() / $operation->getPriceChange()
            );

            /** @var Wallet $wallet */
            $wallet = $this->getRandomReference(Wallet::class);
            $wallet->addOperation($operation);

            $manager->persist($operation);
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
            StockFixtures::class,
            WalletFixtures::class,
        ];
    }
}
