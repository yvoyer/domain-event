<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use PHPUnit\Framework\TestCase;

final class PayloadTest extends TestCase
{
    public function test_it_should_return_the_string_value(): void
    {
        self::assertSame('value', Payload::fromArray(['key' => 'value'])->getString('key'));
    }

    public function test_it_should_return_the_int_value(): void
    {
        self::assertSame(12, Payload::fromArray(['key' => 12])->getInteger('key'));
        self::assertSame(12, Payload::fromArray(['key' => '12'])->getInteger('key'));
    }

    public function test_it_should_return_the_float_value(): void
    {
        self::assertSame(12.34, Payload::fromArray(['key' => '12.34'])->getFloat('key'));
        self::assertSame(12.34, Payload::fromArray(['key' => 12.34])->getFloat('key'));
    }

    public function test_it_should_return_the_bool_value(): void
    {
        self::assertTrue(Payload::fromArray(['key' => true])->getBoolean('key'));
        self::assertFalse(Payload::fromArray(['key' => 0])->getBoolean('key'));
    }

    public function test_it_should_throw_exception_when_payload_do_not_contain_string(): void
    {
        $this->expectException(PayloadKeyNotFound::class);
        $this->expectExceptionMessage('Payload key "not-found" could not be found in payload: "[]".');
        Payload::fromArray([])->getString('not-found');
    }

    public function test_it_should_throw_exception_when_payload_do_not_contain_int(): void
    {
        $this->expectException(PayloadKeyNotFound::class);
        $this->expectExceptionMessage('Payload key "not-found" could not be found in payload: "[]".');
        Payload::fromArray([])->getInteger('not-found');
    }

    public function test_it_should_throw_exception_when_payload_do_not_contain_float(): void
    {
        $this->expectException(PayloadKeyNotFound::class);
        $this->expectExceptionMessage('Payload key "not-found" could not be found in payload: "[]".');
        Payload::fromArray([])->getFloat('not-found');
    }

    public function test_it_should_throw_exception_when_payload_do_not_contain_bool(): void
    {
        $this->expectException(PayloadKeyNotFound::class);
        $this->expectExceptionMessage('Payload key "not-found" could not be found in payload: "[]".');
        Payload::fromArray([])->getBoolean('not-found');
    }

    public function test_it_should_use_specified_strategy_when_payload_do_not_contain_string(): void
    {
        self::assertSame(
            'default',
            Payload::fromArray([])->getString('not-found', new ReturnDefaultValueOnFailure('default'))
        );
    }

    public function test_it_should_use_specified_strategy_when_payload_do_not_contain_int(): void
    {
        self::assertSame(
            87,
            Payload::fromArray([])->getInteger('not-found', new ReturnDefaultValueOnFailure('87'))
        );
    }

    public function test_it_should_use_specified_strategy_when_payload_do_not_contain_float(): void
    {
        self::assertSame(
            12.34,
            Payload::fromArray([])->getFloat('not-found', new ReturnDefaultValueOnFailure('12.34'))
        );
    }

    public function test_it_should_use_specified_strategy_when_payload_do_not_contain_bool(): void
    {
        self::assertFalse(
            Payload::fromArray([])->getBoolean('not-found', new ReturnDefaultValueOnFailure(0))
        );
    }

    public function test_it_should_use_specified_strategy_when_expected_string_value_is_not_valid(): void
    {
        $this->expectException(UnexpectedTypeForPayloadKey::class);
        $this->expectExceptionMessage('Value "<TRUE>" for key "key" is not of expected type "string", got "boolean".');
        Payload::fromArray(['key' => true])->getString('key');
    }

    public function test_it_should_use_specified_strategy_when_expected_int_value_is_not_valid(): void
    {
        $this->expectException(UnexpectedTypeForPayloadKey::class);
        $this->expectExceptionMessage('Value "string" for key "key" is not of expected type "integer", got "string".');
        Payload::fromArray(['key' => 'string'])->getInteger('key');
    }

    public function test_it_should_use_specified_strategy_when_expected_float_value_is_not_valid(): void
    {
        $this->expectException(UnexpectedTypeForPayloadKey::class);
        $this->expectExceptionMessage('Value "string" for key "key" is not of expected type "float", got "string".');
        Payload::fromArray(['key' => 'string'])->getFloat('key');
    }

    public function test_it_should_use_specified_strategy_when_expected_bool_value_is_not_valid(): void
    {
        $this->expectException(UnexpectedTypeForPayloadKey::class);
        $this->expectExceptionMessage('Value "string" for key "key" is not of expected type "boolean", got "string".');
        Payload::fromArray(['key' => 'string'])->getBoolean('key');
    }

    public function test_it_should_create_payload_from_json(): void
    {
        $payload = Payload::fromJson('{"string":"value","int":123,"float":12.34,"bool":true}');
        self::assertSame('value', $payload->getString('string'));
        self::assertSame(123, $payload->getInteger('int'));
        self::assertSame(12.34, $payload->getFloat('float'));
        self::assertTrue($payload->getBoolean('bool'));
    }

    public function test_it_should_allow_date_time(): void
    {
        $payload = Payload::fromArray(['date' => '2000-01-01 12:34:56']);
        self::assertSame(
            '2000-01-01 12:34:56',
            $payload->getDateTime('date')->format('Y-m-d H:i:s')
        );
    }

    public function test_it_should_check_whether_a_key_contains_string(): void
    {
        self::assertFalse(
            Payload::fromArray(['atHour' => 'Not matched'])->keyContainsString('At')
        );
        self::assertFalse(
            Payload::fromArray(['Hourat' => 'Not matched'])->keyContainsString('At')
        );
        self::assertTrue(
            Payload::fromArray(['AtKey' => 'matched'])->keyContainsString('At'));
        self::assertTrue(
            Payload::fromArray(['KeyAt' => 'matched'])->keyContainsString('At')
        );
    }
}
