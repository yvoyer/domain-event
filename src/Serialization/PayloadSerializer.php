<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface PayloadSerializer
{
    public function createEventName(DomainEvent $event): string;

    public function createPayload(DomainEvent $event): Payload;

    /**
     * @param class-string<DomainEvent> $eventName
     */
    public function createEvent(
        string $eventName,
        Payload $payload,
    ): DomainEvent;
}
