<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\InMemory;

use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;

final class SpyPublisher implements EventPublisher
{
    /**
     * @var array<int, DomainEvent>
     */
    private array $events = [];

    public function subscribe(EventListener $listener): void
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function publish(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function publishChanges(array $events): void
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }

    /**
     * @return array<int, DomainEvent>
     */
    final public function getPublishedEvents(): array
    {
        return $this->events;
    }
}
