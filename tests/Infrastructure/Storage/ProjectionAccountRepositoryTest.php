<?php

namespace App\Tests\Infrastructure\Storage;

use App\Application\Account\Repository\ProjectionAccountRepositoryInterface;
use App\Domain\Account\Account;
use App\Infrastructure\EventSource\Changed;
use App\Infrastructure\Storage\AccountRepository;
use App\Tests\Infrastructure\AppDoctrineKernelTestCase;

class ProjectionAccountRepositoryTest extends AppDoctrineKernelTestCase
{
    /**
     * @var AccountRepository
     */
    protected $repo;

    protected function setUp()
    {
        parent::setUp();

        $repo = $this->getRepository(AccountRepository::class);

        $account = AccountProvider::provide('Random Iban 1', 'iban', 'DE67500105176511458445');
        $repo->save($account);

        $account = AccountProvider::provide('Random Iban 2', 'iban', 'DE21500105172317164917');
        $repo->save($account);

        $account = AccountProvider::provide('My account', 'iban', 'ES5001822928617851115254');
        $repo->save($account);

        $account = AccountProvider::provide('My account closed', 'iban', 'ES5431901591182969523665');
        $account->close();
        $repo->save($account);
    }

    public function testFindAllOpen()
    {
        /** @var ProjectionAccountRepositoryInterface $projectionRepo */
        $projectionRepo = $this->getRepository(ProjectionAccountRepositoryInterface::class);

        $accounts = $projectionRepo->findAllOpen();

        $this->assertCount(3, $accounts);

        foreach ($accounts as $account) {
            $this->assertFalse($account->isClosed());
        }
    }

    public function testFindAllOpenMatching()
    {
        /** @var ProjectionAccountRepositoryInterface $projectionRepo */
        $projectionRepo = $this->getRepository(ProjectionAccountRepositoryInterface::class);

        $accounts = $projectionRepo->findAllOpenMatching('My account');

        $this->assertCount(1, $accounts);

        foreach ($accounts as $account) {
            $this->assertFalse($account->isClosed());
        }
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
