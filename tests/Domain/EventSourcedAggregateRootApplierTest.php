<?php

namespace Botilka\Tests\Domain;

use PHPUnit\Framework\TestCase;

final class EventSourcedAggregateRootApplierTest extends TestCase
{
    private $eventSourcedAggregateRoot;

    public function setUp()
    {
        $this->eventSourcedAggregateRoot = new StubEventSourcedAggregateRoot();
    }

    public function testGetPlayhead()
    {
        $this->assertSame(-1, $this->eventSourcedAggregateRoot->getPlayhead());
    }

    public function testApply()
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $result = $aggregateRoot->apply(new StubEvent(321));

        $this->assertInstanceOf(StubEventSourcedAggregateRoot::class, $result);
        $this->assertSame(0, $result->getPlayhead());
        $this->assertSame(321, $result->getFoo());
    }
}
