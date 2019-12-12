<?php

namespace App\Tests\Infrastructure\Storage;

use App\Domain\Account\Account;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\Money\Currency;
use App\Infrastructure\Money\Money;
use App\Infrastructure\Storage\AccountRepository;
use App\Infrastructure\Storage\ProjectionAccountRepository;
use App\Infrastructure\UUID;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;
use DateTime;

/**
 * @group integration
 * @group infrastructure
 * @group storage
 */
final class AccountRepositoryTest extends AppDoctrineKernelTestCase
{
    public function testSave()
    {
        $account = $this->provideAccount();

        /** @var AccountRepository $repo */
        $repo = $this->getRepository(AccountRepository::class);

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
        $this->assertEquals($account->getName(), $result->getName());
        $this->assertEquals($account->getType(), $result->getType());
        $this->assertEquals($account->getAccountNo(), $result->getAccountNo());
        $this->assertEquals($account->getBalance(), $result->getBalance());

    }

    private function provideAccount(): Account
    {
        $id = UUID\Generator::generate();
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $balance = Money::fromEURValue(1000);

        $account = Account::open($id, $type, $accountNo, Currency::eur());

        $account->deposit($balance);

        return $account;
    }

    public function testLoadAndSafe()
    {
        $account = $this->provideAccount();

        /** @var AccountRepository $repo */
        $repo = $this->getRepository(AccountRepository::class);

        $repo->save($account);

        $id = $account->getId();
        // Clearing the em in memory.
        $this->entityManager->clear();

        $account = $repo->find($id);
        $account->deposit(Money::fromEURValue(1500));
        $repo->save($account);

        // Clearing the em in memory.
        $this->entityManager->clear();
        /** @var Account $result */
        $result = $this->entityManager
            ->createQuery('SELECT account FROM ' . Account::class . ' account WHERE account.id = :id')
            ->setParameter('id', $account->getId())
            ->getSingleResult();

        $this->assertInstanceOf(Account::class, $result);
        $this->assertEquals($account->getBalance(), $result->getBalance());

    }

    public function testFind()
    {
        $account = $this->provideAccount();

        /** @var AccountRepository $repo */
        $repo = $this->getRepository(AccountRepository::class);

        $repo->save($account);

        // Clearing the em in memory.
        $this->entityManager->clear();

        $result = $repo->find($account->getId());

        $this->assertInstanceOf(Account::class, $result);
        $this->assertEquals($account->getId(), $result->getId());
        $this->assertEquals($account->getName(), $result->getName());
        $this->assertEquals($account->getType(), $result->getType());
        $this->assertEquals($account->getAccountNo(), $result->getAccountNo());
        $this->assertEquals($account->getBalance(), $result->getBalance());
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->truncate(Account::class);
        $this->truncate(Changed::class);

        parent::tearDown();
    }
}
