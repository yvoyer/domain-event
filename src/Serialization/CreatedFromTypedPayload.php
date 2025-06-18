<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Star\Component\DomainEvent\DomainEvent;

/**
 * @deprecated Class will be removed in 3.0. Use CreatedFromPayload instead.
 * @see CreatedFromPayload
 */
interface CreatedFromTypedPayload extends DomainEvent
{
    /**
     * @param Payload $payload
     * @return DomainEvent
     * @deprecated Class will be removed in 3.0. Use CreatedFromPayload instead.
     */
    public static function fromPayload(Payload $payload): DomainEvent;
}
