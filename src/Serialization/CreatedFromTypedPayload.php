<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface CreatedFromTypedPayload extends DomainEvent
{
    /**
     * @param Payload $payload
     * @return DomainEvent
     */
    public static function fromPayload(Payload $payload): DomainEvent;
}
