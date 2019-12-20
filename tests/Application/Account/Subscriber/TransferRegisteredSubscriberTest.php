<?php

namespace App\Tests\Application\Account\Subscriber;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Application\Account\Subscriber\TransferRegisteredSubscriber;
use App\Domain\Account\Account;
use App\Domain\Transfer\Event\TransferRegistered;
use App\Infrastructure\Money\Money;
use App\Tests\Application\Transfer\TransferProvider;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\UUID;

class TransferRegisteredSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [
                TransferRegistered::class => ['onTransferRegistered', 100],
            ],
            TransferRegisteredSubscriber::getSubscribedEvents()
        );
    }

    public function testOnTransferRegistered()
    {
        $id = UUID\Generator::generate();
        $beneficiaryId = UUID\Generator::generate();
        $debtorId = UUID\Generator::generate();
        $amount = Money::fromEURValue(1000);
        $date = new DateTime('now', new DateTimeZone('UTC'));

        $accountRepository = $this->prophesize(AccountRepositoryInterface::class);

        $beneficiary = $this->prophesize(Account::class);
        $beneficiary->deposit($amount)
            ->shouldBeCalled();

        $accountRepository->find($beneficiaryId)
            ->shouldBeCalled()
            ->willReturn($beneficiary->reveal());
        $accountRepository->save($beneficiary)
            ->shouldBeCalled();

        $debtor = $this->prophesize(Account::class);
        $debtor->withdraw($amount)
            ->shouldBeCalled();

        $accountRepository->find($debtorId)
            ->shouldBeCalled()
            ->willReturn($debtor->reveal());
        $accountRepository->save($debtor)
            ->shouldBeCalled();

        $subscriber = new TransferRegisteredSubscriber($accountRepository->reveal());
        $subscriber->onTransferRegistered(
            new TransferRegistered(
                $id,
                TransferProvider::provideAccount($beneficiaryId, 'Beneficiary', 'ACCOUNTNO'),
                TransferProvider::provideAccount($debtorId, 'Debtor', 'ACCOUNTNO'),
                $amount,
                $date
            )
        );
    }
}
