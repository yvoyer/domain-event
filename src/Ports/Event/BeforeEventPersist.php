<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Event;

use DateTimeInterface;
use Star\Component\DomainEvent\Serialization\Payload;

final class BeforeEventPersist implements EventStoreEvent
{
    /**
     * @var string
     */
    private $aggregateId;

    /**
     * @var string
     */
    private $eventName;

    /**
     * @var Payload
     */
    private $payload;

    /**
     * @var DateTimeInterface
     */
    private $pushedOn;

    public function __construct(
        string $aggregateId,
        string $eventName,
        Payload $payload,
        DateTimeInterface $pushedOn
    ) {
        $this->aggregateId = $aggregateId;
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->pushedOn = $pushedOn;
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
