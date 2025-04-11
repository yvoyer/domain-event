<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface PayloadSerializer
{
    public function createEventName(DomainEvent $event): string;

    /**
     * @return array<string, string|int|float|bool>|Payload
     * @deprecated Returning array will be removed in 3.0, We'll return Payload
     */
    public function createPayload(DomainEvent $event);

    /**
     * @param string|class-string<DomainEvent> $eventName
     * @param array<string, string|int|bool|float> $payload
     */
    public function createEvent(
        string $eventName,
        array $payload
    ): DomainEvent;
}
