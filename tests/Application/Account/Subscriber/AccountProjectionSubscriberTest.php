<?php

namespace App\Tests\Application\Account\Subscriber;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Application\Account\Subscriber\AccountProjectionSubscriber;
use App\Domain\Account\Event\AccountClosed;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\UUID;

final class AccountProjectionSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [
                AccountClosed::class => ['onAccountClosed', 100],
            ],
            AccountProjectionSubscriber::getSubscribedEvents()
        );
    }

    public function testOnAccountClosed()
    {
        $id = UUID\Generator::generate();
        $event = new AccountClosed($id);

        $repo = $this->prophesize(AccountRepositoryInterface::class);
        $repo->delete($id)->shouldBeCalled();

        $subscriber = new AccountProjectionSubscriber($repo->reveal());

        $subscriber->onAccountClosed($event);
    }
}
