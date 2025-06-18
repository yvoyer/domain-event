<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Event;

use DateTimeInterface;
use Star\Component\DomainEvent\Serialization\Payload;

final class AfterEventPersist implements EventStoreEvent
{
    public function __construct(
        private string $aggregateId,
        private string $eventName,
        private Payload $payload,
        private DateTimeInterface $pushedOn
    ) {
    }

    final public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    final public function getEventName(): string
    {
        return $this->eventName;
    }

    final public function getPayload(): Payload
    {
        return $this->payload;
    }

    final public function getPushedOn(): DateTimeInterface
    {
        return $this->pushedOn;
    }
}
