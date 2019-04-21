<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

abstract class BaseFixtures extends Fixture
{
    /** @var Generator */
    protected $faker;

    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create();

        $this->loadData($manager);

        $manager->flush();
    }

    abstract protected function loadData(ObjectManager $manager);
}
