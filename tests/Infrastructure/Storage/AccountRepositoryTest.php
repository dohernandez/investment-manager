<?php

namespace App\Tests\Infrastructure\Storage;

use App\Domain\Account\Projection\Account;
use App\Infrastructure\Money\Money;
use App\Infrastructure\Storage\AccountRepository;
use App\Infrastructure\UUID;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;
use DateTime;
use DateTimeZone;

/**
 * @group integration
 * @group infrastructure
 * @group storage
 */
final class AccountRepositoryTest extends AppDoctrineKernelTestCase
{
    /**
     * @see \Doctrine\DBAL\Platforms\AbstractPlatform::getDateTimeFormatString
     */
    private const PLATFORM_DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public function testSave()
    {
        $account = $this->provideAccount();
        /** @var AccountRepository $repo */
        $repo = $this->entityManager
            ->getRepository(Account::class);

        $repo->save($account);

        // Clearing the em in memory.
        $this->entityManager->clear();
        /** @var Account $result */
        $result = $this->entityManager
            ->createQuery('SELECT account FROM ' . Account::class . ' account WHERE account.id = :id')
            ->setParameter('id', $account->getId())
            ->getSingleResult();

        $this->assertInstanceOf(Account::class, $result);
        $this->assertEquals($account->getId(), $result->getId());
        $this->assertEquals($account->getType(), $result->getType());
        $this->assertEquals($account->getAccountNo(), $result->getAccountNo());
        $this->assertEquals(
            $account->getCreatedAt()->format(self::PLATFORM_DATE_TIME_FORMAT),
            $result->getCreatedAt()->format(self::PLATFORM_DATE_TIME_FORMAT)
        );
        $this->assertEquals(
            $account->getUpdatedAt()->format(self::PLATFORM_DATE_TIME_FORMAT),
            $result->getUpdatedAt()->format(self::PLATFORM_DATE_TIME_FORMAT)
        );
        $this->assertEquals($account->getBalance(), $result->getBalance());

        return $account;
    }

    private function provideAccount(): Account
    {
        $id = UUID\Generator::generate();
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $createdAt = new DateTime();
        $balance = Money::fromEURValue(1000);

        return (new Account($id))
            ->setName($name)
            ->setType($type)
            ->setAccountNo($accountNo)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt)
            ->setBalance($balance)
        ;
    }

    public function testFind()
    {
        $account = $this->provideAccount();

        /** @var AccountRepository $repo */
        $repo = $this->entityManager
            ->getRepository(Account::class);

        $repo->save($account);

        // Clearing the em in memory.
        $this->entityManager->clear();
        /** @var Account $result */
        $result = $repo->find($account->getId());

        $this->assertInstanceOf(Account::class, $result);
        $this->assertEquals($account->getId(), $result->getId());
        $this->assertEquals($account->getName(), $result->getName());
        $this->assertEquals($account->getType(), $result->getType());
        $this->assertEquals($account->getAccountNo(), $result->getAccountNo());
        $this->assertEquals(
            $account->getCreatedAt()->format(self::PLATFORM_DATE_TIME_FORMAT),
            $result->getCreatedAt()->format(self::PLATFORM_DATE_TIME_FORMAT)
        );
        $this->assertEquals(
            $account->getUpdatedAt()->format(self::PLATFORM_DATE_TIME_FORMAT),
            $result->getUpdatedAt()->format(self::PLATFORM_DATE_TIME_FORMAT)
        );
        $this->assertEquals($account->getBalance(), $result->getBalance());
    }

    public function testRemove()
    {
        $account = $this->provideAccount();

        /** @var AccountRepository $repo */
        $repo = $this->entityManager
            ->getRepository(Account::class);

        $repo->save($account);

        $repo->delete($account->getId());


        // Clearing the em in memory.
        $this->entityManager->clear();
        $result = $this->entityManager->find(Account::class, $account->getId());

        $this->assertNull($result);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->truncate(Account::class);

        parent::tearDown();
    }
}
