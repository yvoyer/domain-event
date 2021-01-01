<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

/**
 * Class implementing this interface will be allowed to being serialized in the payload.
 */
interface SerializableAttribute
{
    /**
     * @return string
     */
    public function toSerializableString(): string;
}
