<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\InMemory;

use Countable;
use Star\Component\DomainEvent\AggregateRoot;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventPublisher;
use function array_filter;

abstract class EventStoreCollection implements Countable
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    /**
     * @var DomainEvent[][]
     */
    private $events = [];

    public function __construct(EventPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    final protected function saveAggregate(string $id, AggregateRoot $aggregate): void
    {
        if (! $this->aggregateExists($id)) {
            $this->events[$id] = [];
        }

        $events = $aggregate->uncommitedEvents();
        $this->events[$id] = \array_merge($this->events[$id], $events);
        $this->publisher->publishChanges($events);
    }

    final protected function loadAggregate(string $id): AggregateRoot
    {
        if (! $this->aggregateExists($id)) {
            $this->handleAggregateNotFound($id);
        }

        $aggregate = $this->createAggregate(...$this->events[$id]);
        $aggregate->uncommitedEvents(); // remove events since we replayed them

        return $aggregate;
    }

    /**
     * @param callable $callable
     * @return AggregateRoot[] Indexed by id
     */
    protected function filter(callable $callable): array
    {
        $aggregates = [];
        foreach ($this->events as $aggregateId => $events) {
            $aggregates[$aggregateId] = $this->loadAggregate($aggregateId);
        }

        return array_filter($aggregates, $callable);
    }

    protected function exists(callable $callable): bool
    {
        foreach ($this->events as $aggregateId => $events) {
            if ($callable($this->loadAggregate($aggregateId))) {
                return true;
            }
        }

        return false;
    }

    final public function count(): int
    {
        return count(\array_keys($this->events));
    }

    private function aggregateExists(string $id): bool
    {
        return array_key_exists($id, $this->events);
    }

    abstract protected function handleAggregateNotFound(string $id): void;

    abstract protected function createAggregate(DomainEvent ...$events): AggregateRoot;
}
