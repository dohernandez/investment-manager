<?php

namespace App\DataFixtures;

use App\Entity\StockInfo;
use Doctrine\Common\Persistence\ObjectManager;

class StockInfoFixtures extends BaseFixtures
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, StockInfo::class, 3, function (ObjectManager $manager, StockInfo $stockInfo, int $index) {

            $stockInfo->setName($this->faker->company)
                ->setType(StockInfo::TYPE);

            $manager->persist($stockInfo);
        });

        $this->createMany($manager, StockInfo::class, 3, function (ObjectManager $manager, StockInfo $stockInfo, int $index) {

            $stockInfo->setName($this->faker->company)
                ->setType(StockInfo::SECTOR);

            $manager->persist($stockInfo);
        }, 3);

        $this->createMany($manager, StockInfo::class, 3, function (ObjectManager $manager, StockInfo $stockInfo, int $index) {

            $stockInfo->setName($this->faker->company)
                ->setType(StockInfo::INDUSTRY);

            $manager->persist($stockInfo);
        }, 6);

        $manager->flush();
    }
}
