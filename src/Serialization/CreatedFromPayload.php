<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface CreatedFromPayload extends DomainEvent
{
    /**
     * Recreates the event from the Payload.
     * Payload should contain keys with same name as property based on PayloadSerializer strategy.
     *
     * @see PayloadSerializer
     * @since 3.0
     */
    public static function fromPayload(Payload $payload): DomainEvent;
}
