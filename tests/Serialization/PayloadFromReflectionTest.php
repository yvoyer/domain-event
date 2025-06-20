<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\Serialization\Transformation\DateTimeTransformer;
use Star\Component\DomainEvent\Serialization\Transformation\PropertyValueTransformer;
use Star\Example\Blog\Domain\Model\Post\PostId;
use stdClass;
use function get_class;
use function sprintf;

final class PayloadFromReflectionTest extends TestCase
{
    public function test_should_serialize_event_without_attributes(): void
    {
        $event = new class implements DomainEvent {};
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload($event);

        self::assertTrue($payload->keyExists('event_class'));
        self::assertSame(get_class($event), $payload->getString('event_class'));
    }

    public function test_should_serialize_event_with_int_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(new MixedEvent(44));
        self::assertTrue($payload->keyExists('attribute'));
        self::assertSame(44, $payload->getInteger('attribute'));
    }

    public function test_should_serialize_event_with_string_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(new MixedEvent('something'));
        self::assertTrue($payload->keyExists('attribute'));
        self::assertSame('something', $payload->getString('attribute'));
    }

    public function test_should_serialize_event_with_float_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(
            new class implements DomainEvent {
                private float $as_int = 123.456; // @phpstan-ignore-line
                private string $as_string = '123.456'; // @phpstan-ignore-line
            }
        );
        self::assertTrue($payload->keyExists('as_int'));
        self::assertSame(123.456, $payload->getFloat('as_int'));
        self::assertTrue($payload->keyExists('as_string'));
        self::assertSame('123.456', $payload->getString('as_string'));
    }

    public function test_should_serialize_event_with_bool_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(
            new class implements DomainEvent {
                private bool $true = true; // @phpstan-ignore-line
                private bool $false = false; // @phpstan-ignore-line
            }
        );
        self::assertTrue($payload->keyExists('true'));
        self::assertTrue($payload->getBoolean('true'));
        self::assertTrue($payload->keyExists('false'));
        self::assertFalse($payload->getBoolean('false'));
    }

    public function test_should_not_allow_to_serialize_array(): void
    {
        $serializer = new PayloadFromReflection();

        $this->expectException(NotSupportedTypeInPayload::class);
        $this->expectExceptionMessage(
            'Payload do not support having a value of type "array" as attribute "attribute", ' .
            'only "int|string|float|bool|SerializableAttribute" are supported.' .
            sprintf(' You may register a "%s" to support your value.', PropertyValueTransformer::class)
        );
        $serializer->createPayload(new MixedEvent([]));
    }

    public function test_should_not_allow_to_serialize_object(): void
    {
        $serializer = new PayloadFromReflection();

        $this->expectException(NotSupportedTypeInPayload::class);
        $this->expectExceptionMessage(
            'Payload do not support having a value of type "object(stdClass)" as attribute "attribute"'
        );
        $serializer->createPayload(new MixedEvent((object) []));
    }

    public function test_it_should_allow_serialization_of_object_implementing_serializable_interface(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(
            new MixedEvent(PostId::fromString('id'))
        );

        self::assertTrue($payload->keyExists('attribute'));
        self::assertSame('id', $payload->getString('attribute'));
    }

    public function test_it_should_not_allow_event_class_that_are_not_created_from_payload(): void
    {
        $serializer = new PayloadFromReflection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Event with name "stdClass" must implement interface "%s" in order to be re-created.',
                CreatedFromPayload::class,
            )
        );
        $serializer->createEvent(stdClass::class, Payload::fromArray([])); // @phpstan-ignore-line
    }

    public function test_it_should_allow_creating_event_using_new_payload_object(): void
    {
        $serializer = new PayloadFromReflection();
        $class = new class() implements CreatedFromPayload {
            public static function fromPayload(Payload $payload): DomainEvent
            {
                return new self();
            }
        };

        self::assertInstanceOf(
            get_class($class),
            $serializer->createEvent(get_class($class), Payload::fromArray([]))
        );
    }

    public function test_it_should_allow_serialization_of_date_time_to_iso_8601(): void
    {
        $serializer = new PayloadFromReflection();
        $serializer->registerTransformer(new DateTimeTransformer());

        $payload = $serializer->createPayload(
            new MixedEvent(new DateTimeImmutable('2000-01-01 12:34:56.0987'))
        );

        self::assertSame('2000-01-01T12:34:56+0000', $payload->getString('attribute'));
    }

    public function test_it_should_allow_serialization_of_date_time_to_custom_format(): void
    {
        $serializer = new PayloadFromReflection();
        $serializer->registerTransformer(new DateTimeTransformer('Y-m-d H:i:s.u'));

        $payload = $serializer->createPayload(
            new MixedEvent(new DateTimeImmutable('2000-01-01 12:34:56.0987'))
        );

        self::assertSame('2000-01-01 12:34:56.098700', $payload->getString('attribute'));
    }
}

final class MixedEvent implements DomainEvent {
    public function __construct(
        public mixed $attribute,
    ) {
    }
}
