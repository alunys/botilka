<?php

declare(strict_types=1);

namespace Botilka\Tests\Application\Command;

use Botilka\Application\Command\CommandResponse;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

final class CommandResponseTest extends TestCase
{
    /** @var CommandResponse */
    private $commandResponse;

    public function setUp()
    {
        $event = new StubEvent(123);
        $this->commandResponse = new CommandResponse('foo', 456, $event);
    }

    public function testGetPlayhead(): void
    {
        $this->assertSame(456, $this->commandResponse->getPlayhead());
    }

    public function testGetEvent(): void
    {
        $event = new StubEvent(123);
        $commandResponse = new CommandResponse('foo', 456, $event);
        $this->assertSame($event, $commandResponse->getEvent());
    }

    public function testGetId(): void
    {
        $this->assertSame('foo', $this->commandResponse->getId());
    }

    public function testWithValue(): void
    {
        $event = new StubEvent(123);
        $commandResponse = CommandResponse::withValue('bar', 654, $event);
        $this->assertInstanceOf(CommandResponse::class, $commandResponse);
        $this->assertSame('bar', $commandResponse->getId());
        $this->assertSame(654, $commandResponse->getPlayhead());
        $this->assertSame($event, $commandResponse->getEvent());
    }
}
