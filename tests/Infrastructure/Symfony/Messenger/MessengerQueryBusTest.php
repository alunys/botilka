<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\MessengerQueryBus;
use Botilka\Tests\Fixtures\Application\Query\SimpleQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerQueryBusTest extends TestCase
{
    public function testDispatch(): void
    {
        $query = new SimpleQuery('bar', 321);

        $messengerBus = $this->createMock(MessageBusInterface::class);
        $messengerBus->expects($this->once())
            ->method('dispatch')
            ->with($query)
            ->willReturn('bar');

        $bus = new MessengerQueryBus($messengerBus);
        $this->assertSame('bar', $bus->dispatch($query));
    }
}
