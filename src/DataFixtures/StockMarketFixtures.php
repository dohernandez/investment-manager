<?php

namespace App\DataFixtures;

use App\Entity\StockMarket;
use Doctrine\Common\Persistence\ObjectManager;

class StockMarketFixtures extends BaseFixtures
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, StockMarket::class, 6, function (ObjectManager $manager, StockMarket $stockMarket) {

            $stockMarket->setName($this->faker->company)
                ->setCountry($this->faker->countryCode)
                // rand start 5 - text() can only generate text of at least 5 characters
                // rand end 10 - maximum string length to avoid data too long for column 'symbol'
                ->setSymbol($this->faker->text(rand(5, 10)))
                ;

            $manager->persist($stockMarket);
        });

        $manager->flush();
    }
}
