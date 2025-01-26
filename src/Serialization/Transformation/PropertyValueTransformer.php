<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization\Transformation;

/**
 * Attribute transformers are invoked during serialization and deserialization of event.
 * It allows you to support new custom attributes to be serialized/deserialized in your custom events.
 */
interface PropertyValueTransformer
{
    /**
     * Invoked during serialization of the event. It must return a serializable value.
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    public function eventPropertyToPayloadValue(
        string $property,
        $value
    );
}
