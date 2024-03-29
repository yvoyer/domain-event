<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface CreatedFromPayload extends DomainEvent
{
    /**
     * @param SerializableAttribute[]|string[]|int[]|bool[]|float[] $payload
     * @return CreatedFromPayload
     */
    public static function fromPayload(array $payload): CreatedFromPayload;
}
