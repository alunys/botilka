<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\Event\Event;
use Botilka\Infrastructure\Doctrine\EventStoreManagerDoctrine;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Statement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class EventStoreManagerDoctrineTest extends TestCase
{
    /** @var DenormalizerInterface|MockObject */
    private $denormarlizer;
    /** @var Connection|MockObject */
    private $connection;
    /** @var EventStoreManagerDoctrine */
    private $manager;

    private $table = 'foo';

    protected function setUp()
    {
        $this->denormarlizer = $this->createMock(DenormalizerInterface::class);
        $this->connection = $this->createMock(Connection::class);

        $this->manager = new EventStoreManagerDoctrine($this->connection, $this->denormarlizer, $this->table);
    }

    private function getRows(): array
    {
        $rows = ['foo' => [], 'bar' => []];
        foreach ($rows as $rowId => $subRows) {
            for ($i = 0; $i < ('foo' === $rowId ? 10 : 5); ++$i) {
                $rows[$rowId][] = [
                    'id' => 'foo',
                    'playhead' => $i,
                    'type' => StubEvent::class,
                    'payload' => \json_encode(['foo' => $i]),
                    'metadata' => \json_encode(null),
                    'recorded_on' => (new \DateTimeImmutable('2018-11-14 19:42:'.($i * 2).'.1234'))->format('Y-m-d H:i:s.u'),
                    'domain' => 'Foo\\Domain',
                ];
            }
        }

        return $rows;
    }

    /** @dataProvider loadByAggregateRootIdProvider */
    public function testLoadByAggregateRootId(string $id, ?int $from = null, ?int $to = null, int $shouldBeCount, string $queryPart, array $parameters): void
    {
        $rows = $this->getRows();

        $expected = \array_slice($rows[$id], null !== $from ? $from : 0, null !== $to ? $to - $from : null);

        $stmt = $this->createMock(Statement::class);
        $stmt->expects($this->once())->method('execute')
            ->with($parameters);
        $stmt->expects($this->once())->method('fetchAll')
            ->willReturn($expected);

        $this->denormarlizer->expects($this->exactly(\count($expected)))
            ->method('denormalize')
            ->willReturn($this->createMock(Event::class));

        $query = \trim("SELECT * FROM {$this->table} WHERE id = :id ".$queryPart).' ORDER BY playhead';
        $this->connection->expects($this->once())->method('prepare')
            ->with($query)
            ->willReturn($stmt);

        $events = $this->manager->loadByAggregateRootId($id, $from, $to);

        $this->assertCount($shouldBeCount, $expected);
    }

    public function loadByAggregateRootIdProvider(): array
    {
        return [
            ['foo', null, null, 10, '', ['id' => 'foo']],
            ['foo', 4, null, 6, 'AND playhead >= :from', ['id' => 'foo', 'from' => 4]],
            ['foo', 4, 8, 4, 'AND playhead >= :from AND playhead <= :to', ['id' => 'foo', 'from' => 4, 'to' => 8]],
            ['bar', null, null, 5, '', ['id' => 'bar']],
            ['bar', 4, null, 1, 'AND playhead >= :from', ['id' => 'bar', 'from' => 4]],
            ['bar', 3, 8, 2, 'AND playhead >= :from AND playhead <= :to', ['id' => 'bar', 'from' => 3, 'to' => 8]],
        ];
    }

    public function testLoadByDomain(): void
    {
        $rows = [
            [
                'id' => 'foo',
                'playhead' => 42,
                'type' => StubEvent::class,
                'payload' => \json_encode(['foo' => 1337]),
                'metadata' => \json_encode(null),
                'recorded_on' => (new \DateTimeImmutable('2018-11-14 19:42:51.1234'))->format('Y-m-d H:i:s.u'),
                'domain' => 'Foo\\Domain',
            ],
        ];

        $stmt = $this->createMock(Statement::class);
        $stmt->expects($this->once())->method('execute')
            ->with(['domain' => 'Foo\\Domain']);
        $stmt->expects($this->once())->method('fetchAll')
            ->willReturn($rows);

        $this->denormarlizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($this->createMock(Event::class));

        $query = "SELECT * FROM {$this->table} WHERE domain = :domain ORDER BY playhead";
        $this->connection->expects($this->once())->method('prepare')
            ->with($query)
            ->willReturn($stmt);

        $events = $this->manager->loadByDomain('Foo\\Domain');

        $this->assertCount(1, $events);
    }

    /** @dataProvider getProvider */
    public function testGet(string $key, string $method): void
    {
        $rows = [
            [$key => 'foo'],
            [$key => 'bar'],
            [$key => 'baz'],
        ];

        $stmt = $this->createMock(Statement::class);
        $stmt->expects($this->once())->method('execute');
        $stmt->expects($this->once())->method('fetchAll')
            ->willReturn($rows);

        $this->connection->expects($this->once())->method('prepare')
            ->with("SELECT DISTINCT $key FROM {$this->table}")
            ->willReturn($stmt);

        $this->assertSame(['foo', 'bar', 'baz'], $this->manager->$method());
    }

    public function getProvider(): array
    {
        return [
            ['id', 'getAggregateRootIds'],
            ['domain', 'getDomains'],
        ];
    }
}
