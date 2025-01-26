<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization\Transformation;

use Star\Component\DomainEvent\Serialization\SerializableAttribute;

/**
 * @see SerializableAttribute
 */
final class SerializableAttributeTransformer implements PropertyValueTransformer
{
    public function eventPropertyToPayloadValue(
        string $property,
        $value
    ) {
        if ($value instanceof SerializableAttribute) {
            $value = $value->toSerializableString();
        }

        return $value;
    }
}
