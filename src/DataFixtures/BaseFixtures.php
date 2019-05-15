<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;

abstract class BaseFixtures extends Fixture
{
    /** @var Generator */
    protected $faker;

    protected $referencesIndex;

    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create();

        $this->loadData($manager);
    }

    abstract protected function loadData(ObjectManager $manager);

    protected function createMany(ObjectManager $manager, string $className, int $count, callable $factory, int $offset = 0)
    {
        for ($i = $offset; $i < $offset + $count; $i++) {
            $entity = new $className();

            $factory($manager, $entity, $i);

            // store for usage later as App\Entity\ClassName_#COUNT#
            $this->addReference($className . '_' . $i, $entity);
        }
    }

    protected function getRandomReference(string $className, $exclude = [])
    {
        if (!isset($this->referencesIndex[$className])) {
            $this->referencesIndex = [];

            foreach ($this->referenceRepository->getReferences() as $key => $reference) {
                if (strpos($key, $className.'_') === 0) {
                    $this->referencesIndex[$className][] = $key;
                }
            }
        }

        if (empty($this->referencesIndex[$className])) {
            throw new \Exception(sprintf('Cannot find any references for class "%s"', $className));
        }

        $randomReferenceKey = $this->faker->randomElement($this->referencesIndex[$className]);
        $randomReference = $this->getReference($randomReferenceKey);

        // ensure that the reference is not part of the reference excludes
        while (in_array($randomReference, $exclude)) {
            $randomReferenceKey = $this->faker->randomElement($this->referencesIndex[$className]);
            $randomReference = $this->getReference($randomReferenceKey);
        }

        return $randomReference;
    }
}
