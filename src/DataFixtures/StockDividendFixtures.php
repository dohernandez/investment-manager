<?php

namespace App\DataFixtures;

use App\Entity\Stock;
use App\Entity\StockDividend;
use App\Message\StockDividendSaved;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\MessageBusInterface;

class StockDividendFixtures extends BaseFixtures implements DependentFixtureInterface
{
    /** @var array */
    private $stocks;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->stocks = [];
        $this->bus = $bus;
    }

    public function loadData(ObjectManager $manager)
    {
        $this->createMany($manager, StockDividend::class, 6, function (ObjectManager $manager, StockDividend $stockDividend) {

            /** @var Stock $stock */
            $stock = $this->getRandomReference(Stock::class, $this->stocks);

            $fakeDate = $this->faker->dateTimeBetween('now', '+3 months');
            $exDate = $fakeDate->getTimestamp();

             date_add(
                $fakeDate,
                date_interval_create_from_date_string('1 day')
            );
            $recordDate = $fakeDate->getTimestamp();


            date_add(
                $fakeDate,
                date_interval_create_from_date_string('3 days')
            );
            $paymentDate = $fakeDate->getTimestamp();

            $stockDividend->setExDate(new \DateTime("@$exDate"))
                ->setRecordDate(new \DateTime("@$recordDate"))
                ->setPaymentDate(new \DateTime("@$paymentDate"))
                ->setValue($this->faker->randomFloat(4, 0.01, 1.40))
                ->setStatus(StockDividend::STATUS_ANNOUNCED)
                ->setStock($stock)
                ;

            $this->bus->dispatch(new StockDividendSaved($stockDividend));

            $this->stocks[] = $stock;

            $manager->persist($stockDividend);
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
        ];
    }
}
