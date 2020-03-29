<?php

namespace App\Tests\Application\Account\Subscriber;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Application\Account\Subscriber\TransferChangedSubscriber;
use App\Domain\Account\Account;
use App\Domain\Transfer\Event\TransferChanged;
use App\Infrastructure\Money\Money;
use App\Tests\Application\Transfer\TransferProvider;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\UUID;

class TransferChangedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [
                TransferChanged::class => ['onTransferChanged', 100],
            ],
            TransferChangedSubscriber::getSubscribedEvents()
        );
    }

    public function testOnTransferChangedWhenBeneficiaryIsDifferent()
    {
        $id = UUID\Generator::generate();
        $newBeneficiaryId = UUID\Generator::generate();
        $oldBeneficiaryId = UUID\Generator::generate();
        $debtorId = UUID\Generator::generate();

        $newAmount = Money::fromEURValue(1000);
        $oldAmount = Money::fromEURValue(1000);

        $date = new DateTime('now', new DateTimeZone('UTC'));

        $accountRepository = $this->prophesize(AccountRepositoryInterface::class);

        // new beneficiary
        $this->accountDepositAmount($accountRepository, $newBeneficiaryId, $newAmount);

        // old beneficiary
        $this->accountWithdrawAmount($accountRepository, $oldBeneficiaryId, $oldAmount);

        // debtor
        $accountRepository->find($debtorId)->shouldNotBeCalled();

        $subscriber = new TransferChangedSubscriber($accountRepository->reveal());
        $subscriber->onTransferChanged(
            new TransferChanged(
                $id,
                TransferProvider::provideAccount($newBeneficiaryId, 'New Beneficiary', 'NEWACCOUNTNO'),
                TransferProvider::provideAccount($oldBeneficiaryId, 'Old Beneficiary', 'OLDACCOUNTNO'),
                $debtor = TransferProvider::provideAccount($debtorId, 'Debtor', 'ACCOUNTNO'),
                $debtor,
                $newAmount,
                $oldAmount,
                $date,
                $date
            )
        );
    }

    /**
     * @param \Prophecy\Prophecy\ObjectProphecy $accountRepository
     * @param string $accountId
     * @param Money $amount
     */
    private function accountDepositAmount($accountRepository, $accountId, $amount)
    {
        $account = $this->prophesize(Account::class);
        $account->deposit($amount)
            ->shouldBeCalled();

        $accountRepository->find($accountId)
            ->shouldBeCalled()
            ->willReturn($account->reveal());
        $accountRepository->save($account)
            ->shouldBeCalled();
    }

    /**
     * @param \Prophecy\Prophecy\ObjectProphecy $accountRepository
     * @param string $accountId
     * @param Money $amount
     */
    private function accountWithdrawAmount($accountRepository, $accountId, $amount)
    {
        $account = $this->prophesize(Account::class);
        $account->withdraw($amount)
            ->shouldBeCalled();

        $accountRepository->find($accountId)
            ->shouldBeCalled()
            ->willReturn($account->reveal());
        $accountRepository->save($account)
            ->shouldBeCalled();
    }

    public function testOnTransferChangedWhenDebtorIsDifferent()
    {
        $id = UUID\Generator::generate();
        $beneficiaryId = UUID\Generator::generate();
        $newDebtorId = UUID\Generator::generate();
        $oldDebtorId = UUID\Generator::generate();

        $newAmount = Money::fromEURValue(1000);
        $oldAmount = Money::fromEURValue(1000);

        $date = new DateTime('now', new DateTimeZone('UTC'));

        $accountRepository = $this->prophesize(AccountRepositoryInterface::class);

        // beneficiary
        $accountRepository->find($beneficiaryId)->shouldNotBeCalled();

        // new debtor
        $this->accountWithdrawAmount($accountRepository, $newDebtorId, $newAmount);

        // old debtor
        $this->accountDepositAmount($accountRepository, $oldDebtorId, $oldAmount);

        $subscriber = new TransferChangedSubscriber($accountRepository->reveal());
        $subscriber->onTransferChanged(
            new TransferChanged(
                $id,
                $beneficiary = TransferProvider::provideAccount($beneficiaryId, 'Beneficiary', 'NEWACCOUNTNO'),
                $beneficiary,
                $debtor = TransferProvider::provideAccount($newDebtorId, 'New Debtor', 'NEWACCOUNTNO'),
                $debtor = TransferProvider::provideAccount($oldDebtorId, 'Old Debtor', 'OLDACCOUNTNO'),
                $newAmount,
                $oldAmount,
                $date,
                $date
            )
        );
    }
}
