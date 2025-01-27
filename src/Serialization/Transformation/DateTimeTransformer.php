<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization\Transformation;

use DateTimeInterface;

final class DateTimeTransformer implements PropertyValueTransformer
{
    /**
     * @var string
     */
    private $format;

    public function __construct(
        string $format = 'Y-m-d\TH:i:sO'
    ) {
        $this->format = $format;
    }

    public function eventPropertyToPayloadValue(
        string $property,
        $value
    ) {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format($this->format);
        }

        return $value;
    }
}
