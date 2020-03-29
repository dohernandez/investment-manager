<?php

namespace App\Tests\Application\Transfer\Handler;

use App\Application\Transfer\Command\RemoveTransfer;
use App\Application\Transfer\Handler\RemoveTransferHandler;
use App\Application\Transfer\Repository\TransferRepositoryInterface;
use App\Domain\Transfer\Transfer;
use App\Infrastructure\Money\Money;
use App\Infrastructure\UUID;
use App\Tests\Application\Transfer\TransferProvider;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class RemoveTransferHandlerTest extends TestCase
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

        $repo->delete(
            Argument::that(
                function (Transfer $transfer) {
                    $this->assertTrue($transfer->isRemoved());

                    return true;
                }
            )
        )->shouldBeCalled();

        $handler = new RemoveTransferHandler($repo->reveal());
        $handler(new RemoveTransfer($transfer->getId()));
    }
}
