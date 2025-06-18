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

        $this->assertArrayHasKey('event_class', $payload);
        $this->assertSame(get_class($event), $payload['event_class']);
    }

    public function test_should_serialize_event_with_int_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(new MixedEvent(44));
        $this->assertArrayHasKey('attribute', $payload);
        $this->assertSame(44, $payload['attribute']);
    }

    public function test_should_serialize_event_with_string_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(new MixedEvent('something'));
        $this->assertArrayHasKey('attribute', $payload);
        $this->assertSame('something', $payload['attribute']);
    }

    public function test_should_serialize_event_with_float_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(
            new class implements DomainEvent {
                private float $as_int = 123.456;
                private string $as_string = '123.456';
            }
        );
        $this->assertArrayHasKey('as_int', $payload);
        $this->assertSame(123.456, $payload['as_int']);
        $this->assertArrayHasKey('as_string', $payload);
        $this->assertSame('123.456', $payload['as_string']);
    }

    public function test_should_serialize_event_with_bool_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(
            new class implements DomainEvent {
                private bool $true = true;
                private bool $false = false;
            }
        );
        $this->assertArrayHasKey('true', $payload);
        $this->assertTrue($payload['true']);
        $this->assertArrayHasKey('false', $payload);
        $this->assertFalse($payload['false']);
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

        self::assertArrayHasKey('attribute', $payload);
        self::assertSame('id', $payload['attribute']);
    }

    public function test_it_should_not_allow_event_class_that_are_not_created_from_payload(): void
    {
        $serializer = new PayloadFromReflection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Event with name "stdClass" must implement interface "%s" or "%s" in order to be re-created.',
                CreatedFromPayload::class,
                CreatedFromTypedPayload::class
            )
        );
        $serializer->createEvent(stdClass::class, []);
    }

    public function test_it_should_allow_creating_event_using_new_payload_object(): void
    {
        $serializer = new PayloadFromReflection();
        $class = new class() implements CreatedFromTypedPayload {
            public static function fromPayload(Payload $payload): DomainEvent
            {
                return new self();
            }
        };

        self::assertInstanceOf(
            get_class($class),
            $serializer->createEvent(get_class($class), [])
        );
    }

    public function test_it_should_allow_serialization_of_date_time_to_iso_8601(): void
    {
        $serializer = new PayloadFromReflection();
        $serializer->registerTransformer(new DateTimeTransformer());

        $payload = $serializer->createPayload(
            new MixedEvent(new DateTimeImmutable('2000-01-01 12:34:56.0987'))
        );

        self::assertSame('2000-01-01T12:34:56+0000', $payload['attribute']);
    }

    public function test_it_should_allow_serialization_of_date_time_to_custom_format(): void
    {
        $serializer = new PayloadFromReflection();
        $serializer->registerTransformer(new DateTimeTransformer('Y-m-d H:i:s.u'));

        $payload = $serializer->createPayload(
            new MixedEvent(new DateTimeImmutable('2000-01-01 12:34:56.0987'))
        );

        self::assertSame('2000-01-01 12:34:56.098700', $payload['attribute']);
    }

    public function test_it_should_allow_migration_to_new_api(): void
    {
        $serializer = new PayloadFromReflection();
        $oldEvent = $serializer->createEvent(V2EventArray::class, ['key' => 'value']);
        self::assertInstanceOf(V2EventArray::class, $oldEvent);
        self::assertSame('value', $oldEvent->key);

        $newEvent = $serializer->createEvent(V2EventPayload::class, ['key' => 'value']);
        self::assertInstanceOf(V2EventPayload::class, $newEvent);
        self::assertSame('value', $newEvent->key);
    }
}

final class MixedEvent implements DomainEvent {
    public function __construct(
        private mixed $attribute,
    ) {
    }
}

final class V2EventArray implements CreatedFromPayload
{
    public function __construct(
        public string $key,
    ) {
    }

    public static function fromPayload(array $payload): CreatedFromPayload
    {
        return new self($payload['key']);
    }
}

final class V2EventPayload implements CreatedFromTypedPayload
{
    public function __construct(
        public string $key,
    ) {
    }

    public static function fromPayload(Payload $payload): DomainEvent
    {
        return new self($payload->getString('key'));
    }
}
