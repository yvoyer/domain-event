<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Logging;

use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;

final class LoggablePublisher implements EventPublisher
{
    public function subscribe(EventListener $listener): void
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function publish(DomainEvent $event): void
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function publishChanges(array $events): void
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }
}
