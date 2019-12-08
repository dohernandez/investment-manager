<?php

namespace App\Tests\Infrastructure\Aggregator;

use App\Domain\Account\Event\AccountOpened;
use App\Infrastructure\Aggregator\AggregateRoot;
use App\Infrastructure\Aggregator\Changed;
use App\Infrastructure\Aggregator\Metadata;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\UUID;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AggregateRootListenerTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $this->eventDispatcher = $kernel->getContainer()
            ->get('event_dispatcher');

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testPostPersistHandler()
    {
        $id = UUID\Generator::generate();
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $currency = Currency::eur();

        $accountOpened = new AccountOpened($id, $name, $type, $accountNo, $currency);

        $change = new Changed(
            UUID\Generator::generate(),
            get_class($accountOpened),
            $accountOpened,
            new Metadata(),
            AggregateRoot::class,
            $id,
            1
        );

        $this->eventDispatcher->addListener(
            AccountOpened::class,
            function (AccountOpened $event) use ($id, $name, $type, $accountNo, $currency) {
                $this->assertEquals($id, $event->getId());
                $this->assertEquals($name, $event->getName());
                $this->assertEquals($type, $event->getType());
                $this->assertEquals($accountNo, $event->getAccountNo());
                $this->assertEquals($currency->getSymbol(), $event->getCurrency()->getSymbol());
            }
        );

        $this->entityManager->persist($change);
        $this->entityManager->flush();
    }
}
