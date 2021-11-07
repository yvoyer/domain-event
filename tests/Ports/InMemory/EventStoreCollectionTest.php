<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\InMemory;

use RuntimeException;
use Star\Component\DomainEvent\AggregateRoot;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventPublisher;
use PHPUnit\Framework\TestCase;

final class EventStoreCollectionTest extends TestCase
{
    public function test_should_throw_exception_when_aggregate_not_found(): void
    {
        $store = new StubCollection($this->createMock(EventPublisher::class));

        self::assertCount(0, $store);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('id not found.');
        $store->getAggregate('id');
    }

    public function test_it_should_add_item(): void
    {
        $store = new StubCollection($this->createMock(EventPublisher::class));

        self::assertCount(0, $store);

        $store->saveStub('id', StubAggregate::fromStream([new StubEvent()]));

        self::assertCount(1, $store);
    }

    public function test_it_should_trigger_events(): void
    {
        $publisher = $this->createMock(EventPublisher::class);
        $publisher
            ->expects(self::once())
            ->method('publishChanges')
            ->with(self::containsOnlyInstancesOf(StubEvent::class));

        $store = new StubCollection($publisher);
        $store->saveStub('id', StubAggregate::fromStream([new StubEvent()]));
    }

    public function test_it_should_load_aggregate(): void
    {
        $store = new StubCollection($this->createMock(EventPublisher::class));
        $aggregate = StubAggregate::fromStream([]);

        $store->saveStub('id', $aggregate);
        $aggregate = $store->getAggregate('id');
        self::assertSame(0, $aggregate->counter);

        $aggregate->increment();
        $store->saveStub('id', $aggregate);

        $aggregate = $store->getAggregate('id');
        self::assertSame(1, $aggregate->counter);

        $aggregate->increment();
        $store->saveStub('id', $aggregate);

        self::assertSame(2, $store->getAggregate('id')->counter);
    }
}

final class StubEvent implements DomainEvent
{
}

final class StubAggregate extends AggregateRoot
{
    /**
     * @var int
     */
    public $counter = 0;

    public function increment(): void
    {
        $this->mutate(new StubEvent());
    }

    protected function onStubEvent(StubEvent $event): void
    {
        $this->counter ++;
    }
}

final class StubCollection extends EventStoreCollection
{
    public function getAggregate(string $id): StubAggregate
    {
        return $this->loadAggregate($id);
    }

    public function saveStub(string $id, StubAggregate $aggregate): void
    {
        $this->saveAggregate($id, $aggregate);
    }

    protected function handleAggregateNotFound(string $id): void
    {
        throw new \RuntimeException($id . ' not found.');
    }

    protected function createAggregate(DomainEvent ...$events): AggregateRoot
    {
        return StubAggregate::fromStream($events);
    }
}
