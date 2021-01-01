<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface PayloadSerializer
{
    public function createEventName(DomainEvent $event): string;

    public function createPayload(DomainEvent $event): array;

    public function createEvent(string $eventName, array $payload): DomainEvent;
}
