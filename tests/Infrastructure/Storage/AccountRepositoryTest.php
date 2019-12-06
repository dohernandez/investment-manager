<?php

namespace App\Tests\Infrastructure\Storage;

use App\Domain\Account\Projection\Account;
use App\Infrastructure\Money\Money;
use App\Infrastructure\Storage\AccountRepository;
use App\Infrastructure\UUID;
use App\Tests\Infrastructure\AppKernelTestCase;
use DateTimeImmutable;

class AccountRepositoryTest extends AppKernelTestCase
{
    public function testSave()
    {
        $id = UUID\Generator::generate();
        $name = 'Random Iban 1';
        $type = 'iban';
        $accountNo = 'DE67500105176511458445';
        $createdAt = new DateTimeImmutable();
        $balance = Money::fromEURValue(1000);

        $account = (new Account($id))
            ->setName($name)
            ->setType($type)
            ->setAccountNo($accountNo)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt)
            ->setBalance($balance)
        ;

        /** @var AccountRepository $repo */
        $repo = $this->entityManager
            ->getRepository(Account::class);

        $repo->save($account);

        /** @var Account $result */
        $result = $this->entityManager->find(Account::class, $id);

        $this->assertEquals($name, $result->getName());
        $this->assertEquals($type, $result->getType());
        $this->assertEquals($accountNo, $result->getAccountNo());
        $this->assertEquals($createdAt, $result->getCreatedAt());
        $this->assertEquals($createdAt, $result->getUpdatedAt());
        $this->assertEquals($balance, $result->getBalance());
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
