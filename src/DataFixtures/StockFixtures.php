<?php

namespace App\DataFixtures;

use App\Entity\Stock;
use App\Entity\StockMarket;
use App\Entity\StockInfo;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class StockFixtures extends BaseFixtures implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, Stock::class, 50, function (ObjectManager $manager, Stock $stock) {

            $market = $this->getRandomReference(StockMarket::class);

            $type = $this->getStockInfoRandomReference(StockInfo::TYPE);
            $sector = $this->getStockInfoRandomReference(StockInfo::SECTOR);
            $industry = $this->getStockInfoRandomReference(StockInfo::INDUSTRY);

            $stock->setName($this->faker->company)
                // rand start 5 - text() can only generate text of at least 5 characters
                // rand end 10 - maximum string length to avoid data too long for column 'symbol'
                ->setSymbol($this->faker->text(rand(5, 10)))
                ->setValue($this->faker->randomFloat(2, 0, 10000))
                ->setDescription($this->faker->paragraph(
                    $this->faker->randomDigitNotNull,
                    true
                ))
                ->setMarket($market)
                ->setType($type)
                ->setSector($sector)
                ->setIndustry($industry)
            ;

            $manager->persist($stock);
        });

        $manager->flush();
    }

    private function getStockInfoRandomReference(string $type)
    {
        $className = StockInfo::class;

        if (!isset($this->referencesIndex[$className])) {
            $this->referencesIndex = [];

            foreach ($this->referenceRepository->getReferences() as $key => $reference) {
                if (strpos($key, $className.'_') === 0) {
                    /** @var StockInfo $reference */
                    if ($reference->getType() == $type) {
                        $this->referencesIndex[$className][] = $key;
                    }
                }
            }
        }

        if (empty($this->referencesIndex[$className])) {
            throw new \Exception(sprintf('Cannot find any references for class "%s"', $className));
        }

        $randomReferenceKey = $this->faker->randomElement($this->referencesIndex[$className]);
        $randomReference = $this->getReference($randomReferenceKey);

        return $randomReference;
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
            StockMarketFixtures::class,
            StockInfoFixtures::class,
        ];
    }
}
