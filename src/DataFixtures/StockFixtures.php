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
                ->setDescription($this->faker->paragraph(
                    $this->faker->randomDigitNotNull,
                    true
                ))
                ->setMarket($market)
                ->setType($type)
                ->setSector($sector)
                ->setIndustry($industry)
                ->setPeRatio($this->faker->randomFloat(2, 0, 100))
            ;

            $this->bindStockTradingPrice($stock);

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

    private function bindStockTradingPrice(Stock $stock) {
        $value = $this->faker->randomFloat(2, 0, 3000);

        $week52HighInPercentage = $this->faker->randomFloat(2, 0, 100);
        $week52LowInPercentage = $this->faker->randomFloat(2, 0, $week52HighInPercentage);

        $week52Low = $value - $value * $week52LowInPercentage / 100;
        $week52High = $value + $value * $week52HighInPercentage / 100;

        $dayHighInPercentage = $this->faker->randomFloat(2, 0, max(30, $week52HighInPercentage));
        $dayLowInPercentage = $this->faker->randomFloat(2, 0, $dayHighInPercentage);

        $dayLow = $value - $value * $dayLowInPercentage / 100;
        $dayHigh = $value + $value * $dayHighInPercentage / 100;

        $preClose = null;
        $open = null;
        // Indicate whether the stock trading will positive or negative
        if ($this->faker->boolean($chanceOfGettingTrue = 60)) {
            $openInPercentage = $this->faker->randomFloat(2, 0, $dayLowInPercentage);

            $open = $value - $value * $openInPercentage / 100;
        } else {
            $openInPercentage = $this->faker->randomFloat(2, 0, $dayHighInPercentage);

            $open = $value + $value * $openInPercentage / 100;
        }

        if ($this->faker->boolean($chanceOfGettingTrue = 60)) {
            $preCloseInPercentage = $this->faker->randomFloat(2, 0, $dayLowInPercentage);

            $preClose = $value - $value * $preCloseInPercentage / 100;
        } else {
            $preCloseInPercentage = $this->faker->randomFloat(2, 0, $dayHighInPercentage);

            $preClose = $value + $value * $preCloseInPercentage / 100;
        }

        $stock->setValue($value)
            ->setWeek52Low($week52Low)
            ->setWeek52High($week52High)
            ->setDayLow($dayLow)
            ->setDayHigh($dayHigh)
            ->setOpen($open)
            ->setPreClose($preClose)
        ;
    }
}
