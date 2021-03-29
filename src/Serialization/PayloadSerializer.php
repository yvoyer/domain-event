<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface PayloadSerializer
{
    public function createEventName(DomainEvent $event): string;

    /**
     * @param DomainEvent $event
     * @return mixed[]
     */
    public function createPayload(DomainEvent $event): array;

    /**
     * @param string $eventName
     * @param mixed[] $payload
     * @return DomainEvent
     */
    public function createEvent(string $eventName, array $payload): DomainEvent;
}
