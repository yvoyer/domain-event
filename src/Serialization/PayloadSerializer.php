<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface PayloadSerializer
{
    public function createEventName(DomainEvent $event): string;

    /**
     * @return array<string, string|int|float|bool>
     */
    public function createPayload(DomainEvent $event): array;

    /**
     * @param string|class-string<DomainEvent> $eventName
     * @param array<string, string|int|bool|float> $payload
     */
    public function createEvent(
        string $eventName,
        array $payload
    ): DomainEvent;
}
