<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\DomainEvent;
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
        $payload = $serializer->createPayload(
            new class implements DomainEvent {
                private $attribute = 44;
            }
        );
        $this->assertArrayHasKey('attribute', $payload);
        $this->assertSame(44, $payload['attribute']);
    }

    public function test_should_serialize_event_with_string_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(
            new class implements DomainEvent {
                private $attribute = 'something';
            }
        );
        $this->assertArrayHasKey('attribute', $payload);
        $this->assertSame('something', $payload['attribute']);
    }

    public function test_should_serialize_event_with_float_attribute(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(
            new class implements DomainEvent {
                private $as_int = 123.456;
                private $as_string = '123.456';
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
                private $true = true;
                private $false = false;
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
        $this->expectExceptionMessage('Payload do not support having a value of type "array" as attribute "array"');
        $serializer->createPayload(
            new class implements DomainEvent {
                private $array = [];
            }
        );
    }

    public function test_should_not_allow_to_serialize_object(): void
    {
        $serializer = new PayloadFromReflection();

        $this->expectException(NotSupportedTypeInPayload::class);
        $this->expectExceptionMessage(
            'Payload do not support having a value of type "object(stdClass)" as attribute "attribute"'
            . ', only "int, string, float, bool" are supported.'
        );
        $serializer->createPayload(
            new class implements DomainEvent {
                private $attribute;
                public function __construct()
                {
                    $this->attribute = (object) [];
                }
            }
        );
    }

    public function test_it_should_allow_serialization_of_object_implementing_serializable_interface(): void
    {
        $serializer = new PayloadFromReflection();
        $payload = $serializer->createPayload(
            new class implements DomainEvent {
                private $attribute;
                public function __construct()
                {
                    $this->attribute = PostId::fromString('id');
                }
            }
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
                'Event with name "stdClass" must implement interface "%s" in order to be re-created.',
                CreatedFromPayload::class
            )
        );
        $serializer->createEvent(stdClass::class, []);
    }
}
