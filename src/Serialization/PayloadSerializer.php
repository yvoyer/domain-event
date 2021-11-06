<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface PayloadSerializer
{
    public function createEventName(DomainEvent $event): string;

    /**
     * @param DomainEvent $event
     * @return string[]|int[]|float[]|bool[]
     */
    public function createPayload(DomainEvent $event): array;

    /**
     * @param string $eventName
     * @param SerializableAttribute[]|string[]|int[]|bool[]|float[] $payload
     * @return DomainEvent
     */
    public function createEvent(string $eventName, array $payload): DomainEvent;
}
