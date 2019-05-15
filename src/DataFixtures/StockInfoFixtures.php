<?php

namespace App\DataFixtures;

use App\Entity\StockInfo;
use Doctrine\Common\Persistence\ObjectManager;

class StockInfoFixtures extends BaseFixtures
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, StockInfo::class, 4, function (ObjectManager $manager, StockInfo $stockInfo) {

            $stockInfo->setName($this->faker->company)
                ->setType($this->faker->randomElement([StockInfo::TYPE, StockInfo::SECTOR, StockInfo::INDUSTRY]));

            $manager->persist($stockInfo);
        });

        $manager->flush();
    }
}
