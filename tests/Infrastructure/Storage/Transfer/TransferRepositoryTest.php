<?php

namespace App\Tests\Infrastructure\Storage\Transfer;

use App\Domain\Transfer\Transfer;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\Money\Money;
use App\Infrastructure\Storage\Transfer\TransferRepository;
use App\Tests\Application\Transfer\TransferProvider;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;
use App\Infrastructure\UUID;
use DateTime;
use DateTimeZone;

class TransferRepositoryTest extends AppDoctrineKernelTestCase
{
    /**
     * @covers TransferRepository::save
     */
    public function testSave()
    {
        $transfer = TransferProvider::provide(
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Beneficiary', 'DE83726'),
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Debtor', 'DE83726'),
            Money::fromEURValue(1000),
            new DateTime('now', new DateTimeZone('UTC'))
        );

        /** @var TransferRepository $repo */
        $repo = $this->getRepository(TransferRepository::class);

        $repo->save($transfer);

        // Clearing the em in memory.
        $this->entityManager->clear();
        /** @var Transfer $result */
        $result = $this->entityManager
            ->createQuery('SELECT transfer FROM ' . Transfer::class . ' transfer WHERE transfer.id = :id')
            ->setParameter('id', $transfer->getId())
            ->getSingleResult();

        $this->assertInstanceOf(Transfer::class, $result);
        $this->assertEquals($transfer->getId(), $result->getId());
        $this->assertEquals($transfer->getBeneficiaryParty(), $result->getBeneficiaryParty());
        $this->assertEquals($transfer->getDebtorParty(), $result->getDebtorParty());
        $this->assertEquals($transfer->getAmount(), $result->getAmount());
        $this->assertEquals(
            date_format($transfer->getDate(),'d-m-Y H:i:s'),
            date_format($result->getDate(),'d-m-Y H:i:s')
        );
    }

    /**
     * @covers TransferRepository::find
     */
    public function testFind()
    {
        $transfer = TransferProvider::provide(
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Beneficiary', 'DE83726'),
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Debtor', 'DE83726'),
            Money::fromEURValue(1000),
            new DateTime('now', new DateTimeZone('UTC'))
        );

        /** @var TransferRepository $repo */
        $repo = $this->getRepository(TransferRepository::class);

        $repo->save($transfer);

        // Clearing the em in memory.
        $this->entityManager->clear();

        $result = $repo->find($transfer->getId());

        $this->assertInstanceOf(Transfer::class, $result);
        $this->assertEquals($transfer->getId(), $result->getId());
        $this->assertEquals($transfer->getBeneficiaryParty(), $result->getBeneficiaryParty());
        $this->assertEquals($transfer->getDebtorParty(), $result->getDebtorParty());
        $this->assertEquals($transfer->getAmount(), $result->getAmount());
        $this->assertEquals(
            date_format($transfer->getDate(),'d-m-Y H:i:s'),
            date_format($result->getDate(),'d-m-Y H:i:s')
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->truncate(Transfer::class);
        $this->truncate(Changed::class);

        parent::tearDown();
    }
}
