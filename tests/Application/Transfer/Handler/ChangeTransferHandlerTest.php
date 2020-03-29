<?php

namespace App\Tests\Application\Transfer\Handler;

use App\Application\Transfer\Command\ChangeTransfer;
use App\Application\Transfer\Handler\ChangeTransferHandler;
use App\Application\Transfer\Repository\TransferRepositoryInterface;
use App\Domain\Transfer\Account;
use App\Domain\Transfer\Transfer;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use App\Tests\Application\Transfer\TransferProvider;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ChangeTransferHandlerTest extends TestCase
{
    public function testInvoke()
    {
        $transfer = TransferProvider::provide(
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Beneficiary', 'DE83726'),
            TransferProvider::provideAccount(UUID\Generator::generate(), 'Debtor', 'DE83726'),
            Money::fromEURValue(1000),
            new DateTime('now', new DateTimeZone('UTC'))
        );

        $repo = $this->prophesize(TransferRepositoryInterface::class);
        $repo->find($transfer->getId())->shouldBeCalled()->willReturn($transfer);

        $beneficiary = new Account(UUID\Generator::generate(), 'Random Iban 1', 'DE67500105176511458445');
        $debtor = new Account(UUID\Generator::generate(), 'Random Iban 2', 'NL50ABNA9904789940');
        $amount = Money::fromEURValue(1000);
        $date = new DateTime();

        $repo->save(
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

        $handler = new ChangeTransferHandler($repo->reveal());
        $handler(new ChangeTransfer($transfer->getId(), $beneficiary, $debtor, $amount, $date));
    }
}
