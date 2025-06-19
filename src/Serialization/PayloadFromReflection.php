<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use InvalidArgumentException;
use ReflectionClass;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\Serialization\Transformation\PropertyValueTransformer;
use Star\Component\DomainEvent\Serialization\Transformation\SerializableAttributeTransformer;
use function get_class;
use function is_bool;
use function is_scalar;
use function is_subclass_of;
use function sprintf;

final class PayloadFromReflection implements PayloadSerializer
{
    /**
     * @var array<int, PropertyValueTransformer>
     */
    private array $transformers;

    public function __construct()
    {
        $this->transformers = [
            new SerializableAttributeTransformer(), // for BC
        ];
    }

    public function registerTransformer(
        PropertyValueTransformer $transformer
    ): void {
        $this->transformers[] = $transformer;
    }

    public function createPayload(
        DomainEvent $event,
    ): Payload {
        $reflection = new ReflectionClass($event);
        $properties = $reflection->getProperties();
        $payload = [
            'event_class' => get_class($event),
        ];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($event);
            $attribute = $property->getName();
            foreach ($this->transformers as $transformer) {
                $value = $transformer->eventPropertyToPayloadValue($attribute, $value);
            }

            if (! is_bool($value) && ! is_scalar($value)) {
                throw new NotSupportedTypeInPayload($attribute, $value);
            }

            $payload[$attribute] = $value;
        }

        return Payload::fromArray($payload);
    }

    public function createEvent(
        string $eventName,
        Payload $payload,
    ): DomainEvent {
        if (is_subclass_of($eventName, CreatedFromPayload::class)) {
            return $eventName::fromPayload($payload);
        }

        throw new InvalidArgumentException(
            sprintf(
                'Event with name "%s" must implement interface "%s" in order to be re-created.',
                $eventName,
                CreatedFromPayload::class,
            )
        );
    }

    public function createEventName(
        DomainEvent $event,
    ): string {
        return get_class($event);
    }
}
