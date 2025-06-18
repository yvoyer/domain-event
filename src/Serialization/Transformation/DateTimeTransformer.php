<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization\Transformation;

use DateTimeInterface;

final class DateTimeTransformer implements PropertyValueTransformer
{
    public function __construct(
        private string $format = 'Y-m-d\TH:i:sO'
    ) {
    }

    public function eventPropertyToPayloadValue(
        string $property,
        mixed $value
    ): mixed {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format($this->format);
        }

        return $value;
    }
}
