<?php declare(strict_types=1);

namespace Star\Component\DomainEvent;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EventPublisherTest extends TestCase
{
    public function test_it_should_support_using_interface_with_one_event(): void
    {
        $withOne = new PublisherVersion1();
        $events = [
            $this->createMock(DomainEvent::class),
            $this->createMock(DomainEvent::class),
        ];

        $withOne->publish(...$events);
        self::assertCount(2, $withOne->getEvents());
    }

    public function test_it_should_support_using_interface_with_many_events(): void
    {
        $withMany = new PublisherVersion2();
        $withMany->publish(
            $this->createMock(DomainEvent::class),
            $this->createMock(DomainEvent::class),
            $this->createMock(DomainEvent::class)
        );
        self::assertCount(3, $withMany->getEvents());
    }
}

abstract class BaseTestPublisher implements EventPublisher
{
    /**
     * @var array<int, DomainEvent>
     */
    protected array $events = [];

    /**
     * @return array<int, DomainEvent>
     */
    final public function getEvents(): array
    {
        return $this->events;
    }

    public function subscribe(EventListener $listener): void
    {
        throw new RuntimeException(__METHOD__ . ' not implemented yet.');
    }
}

final class PublisherVersion1 extends BaseTestPublisher
{
    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->events[] = $event;
        }
    }
}

final class PublisherVersion2 extends BaseTestPublisher
{
    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->events[] = $event;
        }
    }
}
