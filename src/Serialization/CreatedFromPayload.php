<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

interface CreatedFromPayload extends DomainEvent
{
    /**
     * @param Payload|SerializableAttribute[]|string[]|int[]|bool[]|float[] $payload
     * @return CreatedFromPayload
     * @deprecated Class receive Payload class as argument in 3.0.
     */
    public static function fromPayload(array /* uncomment in 3.0: Payload */ $payload): CreatedFromPayload;
}
