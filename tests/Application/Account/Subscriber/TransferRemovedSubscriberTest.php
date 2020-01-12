<?php

namespace App\Tests\Application\Account\Subscriber;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Application\Account\Subscriber\TransferRemovedSubscriber;
use App\Domain\Account\Account;
use App\Domain\Transfer\Event\TransferRemoved;
use App\Infrastructure\Money\Money;
use App\Tests\Application\Transfer\TransferProvider;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\UUID;

class TransferRemovedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [
                TransferRemoved::class => ['onTransferRemoved', 100],
            ],
            TransferRemovedSubscriber::getSubscribedEvents()
        );
    }

    public function testOnTransferRemoved()
    {
        $id = UUID\Generator::generate();
        $beneficiaryId = UUID\Generator::generate();
        $debtorId = UUID\Generator::generate();
        $amount = Money::fromEURValue(1000);

        $accountRepository = $this->prophesize(AccountRepositoryInterface::class);

        $beneficiary = $this->prophesize(Account::class);
        $beneficiary->withdraw($amount)
            ->shouldBeCalled();

        $accountRepository->find($beneficiaryId)
            ->shouldBeCalled()
            ->willReturn($beneficiary->reveal());
        $accountRepository->save($beneficiary)
            ->shouldBeCalled();

        $debtor = $this->prophesize(Account::class);
        $debtor->deposit($amount)
            ->shouldBeCalled();

        $accountRepository->find($debtorId)
            ->shouldBeCalled()
            ->willReturn($debtor->reveal());
        $accountRepository->save($debtor)
            ->shouldBeCalled();

        $subscriber = new TransferRemovedSubscriber($accountRepository->reveal());
        $subscriber->onTransferRemoved(
            new TransferRemoved(
                $id,
                TransferProvider::provideAccount($beneficiaryId, 'Beneficiary', 'ACCOUNTNO'),
                TransferProvider::provideAccount($debtorId, 'Debtor', 'ACCOUNTNO'),
                $amount
            )
        );
    }
}
