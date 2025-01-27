<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use InvalidArgumentException;
use ReflectionClass;
use Star\Component\DomainEvent\DomainEvent;
use function get_class;
use function is_bool;
use function is_scalar;
use function is_subclass_of;
use function sprintf;

final class PayloadFromReflection implements PayloadSerializer
{
    public function createPayload(DomainEvent $event): array
    {
        $reflection = new ReflectionClass($event);
        $properties = $reflection->getProperties();
        $payload = [
            'event_class' => get_class($event),
        ];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($event);
            $attribute = $property->getName();
            if ($value instanceof SerializableAttribute) {
                $value = $value->toSerializableString();
            }

            if (! is_bool($value) && ! is_scalar($value)) {
                throw new NotSupportedTypeInPayload($attribute, $value);
            }

            $payload[$attribute] = $value;
        }

        return $payload;
    }

    public function createEvent(
        string $eventName,
        array $payload
    ): DomainEvent {
        if (is_subclass_of($eventName, CreatedFromPayload::class)) {
            return $eventName::fromPayload($payload);
        }

        if (is_subclass_of($eventName, CreatedFromTypedPayload::class)) {
            return $eventName::fromPayload(Payload::fromArray($payload));
        }

        throw new InvalidArgumentException(
            sprintf(
                'Event with name "%s" must implement interface "%s" or "%s" in order to be re-created. ',
                $eventName,
                CreatedFromPayload::class,
                CreatedFromTypedPayload::class
            )
        );
    }

    public function createEventName(DomainEvent $event): string
    {
        return get_class($event);
    }
}
