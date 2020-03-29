<?php

namespace App\Tests\Application\Transfer\Handler;

use App\Application\Transfer\Command\RegisterTransfer;
use App\Application\Transfer\Handler\RegisterTransferHandler;
use App\Application\Transfer\Repository\AccountRepositoryInterface;
use App\Application\Transfer\Repository\TransferRepositoryInterface;
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
        $beneficiary = new Account(UUID\Generator::generate(), 'Beneficiary', 'Account No');
        $debtor = new Account(UUID\Generator::generate(), 'Debtor', 'Account No');
        $amount = Money::fromEURValue(1000);
        $date = new DateTime();

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

        $handler = new RegisterTransferHandler($transferRepo->reveal());
        $handler(new RegisterTransfer($beneficiary, $debtor, $amount, $date));
    }
}
