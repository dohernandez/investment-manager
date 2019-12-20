<?php

namespace App\Tests\Infrastructure;

use App\Domain\Account\Projection\Account;
use App\Infrastructure\EventSource\Changed;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AppDoctrineKernelTestCase extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Disabling lifecycle-callback
        $eventSourceListener = new class() {
            public function postPersistHandler(Changed $changed, LifecycleEventArgs $event)
            {
                return;
            }
        };

        $container = $kernel->getContainer();
        $container->set('app.doctrine.entity_listener', $eventSourceListener);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks

        parent::tearDown();
    }

    protected function truncate(string $className)
    {
        $classMetaData = $this->entityManager->getClassMetadata($className);
        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();

        $connection->beginTransaction();
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($classMetaData->getTableName());
            $connection->executeUpdate($q);
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollback();
        }
    }

    protected function getRepository(string $className)
    {
        return self::$container->get($className);
    }
}
