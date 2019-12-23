<?php

namespace App\Tests\Application\Trasnfer\Handler;

use App\Application\Trasnfer\Command\RegisterTransfer;
use App\Application\Trasnfer\Handler\RegisterTransferHandler;
use App\Application\Trasnfer\Repository\AccountRepositoryInterface;
use App\Application\Trasnfer\Repository\TransferRepositoryInterface;
use App\Domain\Transfer\Account;
use App\Domain\Transfer\Transfer;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class RegisterTransferHandlerTest extends TestCase
{

    public function testInvoke()
    {
        $beneficiaryId = UUID\Generator::generate();
        $debtorId = UUID\Generator::generate();

        $beneficiary = new Account($beneficiaryId, 'Beneficiary', 'Account No');
        $debtor = new Account($debtorId, 'Debtor', 'Account No');
        $amount = Money::fromEURValue(1000);
        $date = new DateTime();

        $accountRepo = $this->prophesize(AccountRepositoryInterface::class);
        $accountRepo->find($beneficiaryId)->willReturn($beneficiary)->shouldBeCalled();
        $accountRepo->find($debtorId)->willReturn($debtor)->shouldBeCalled();

        $transferRepo = $this->prophesize(TransferRepositoryInterface::class);
        $transferRepo->save(
            Argument::that(
                function (Transfer $transfer) use ($beneficiary, $debtor, $amount, $date) {
                    $this->assertEquals($beneficiary, $transfer->getBeneficiaryParty());
                    $this->assertEquals($debtor, $transfer->getDebtorParty());
                    $this->assertEquals($amount, $transfer->getAmount());
                    $this->assertEquals($date, $transfer->getDate());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new RegisterTransferHandler($transferRepo->reveal(), $accountRepo->reveal());
        $handler(new RegisterTransfer($beneficiaryId, $debtorId, $amount, $date));
    }
}
