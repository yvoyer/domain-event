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
            Payload::fromArray(['atHour' => 'Not matched'])->keyContains('At')
        );
        self::assertFalse(
            Payload::fromArray(['Hourat' => 'Not matched'])->keyContains('At')
        );
        self::assertTrue(
            Payload::fromArray(['AtKey' => 'matched'])->keyContains('At'));
        self::assertTrue(
            Payload::fromArray(['KeyAt' => 'matched'])->keyContains('At')
        );
    }

    public function test_it_should_return_string_of_first_key_that_contains_string_in_key(): void
    {
        $payload = Payload::fromArray(
            [
                'addedBy' => 'Joe',
                'updatedBy' => 'Jane',
                'deletedBy' => 'Will',
            ]
        );
        self::assertSame('Joe', $payload->getStringWhereKeyContains('By'));
        self::assertSame('Jane', $payload->getStringWhereKeyContains('up'));
        self::assertSame('Will', $payload->getStringWhereKeyContains('le'));

        $this->expectException(PayloadKeyNotFound::class);
        $this->expectExceptionMessage(
            'No key with needle "invalid" could be found. Available keys: "addedBy, updatedBy, deletedBy".'
        );
        $payload->getStringWhereKeyContains('invalid');
    }

    public function test_it_should_return_integer_of_first_key_that_contains_string_in_key(): void
    {
        $payload = Payload::fromArray(
            [
                'addedAt' => 2,
                'updatedAt' => '56',
                'deletedAt' => 8,
            ]
        );
        self::assertSame(2, $payload->getIntegerWhereKeyContains('At'));
        self::assertSame(56, $payload->getIntegerWhereKeyContains('up'));
        self::assertSame(8, $payload->getIntegerWhereKeyContains('le'));

        $this->expectException(PayloadKeyNotFound::class);
        $this->expectExceptionMessage(
            'No key with needle "invalid" could be found. Available keys: "addedAt, updatedAt, deletedAt".'
        );
        $payload->getStringWhereKeyContains('invalid');
    }

    public function test_it_should_return_float_of_first_key_that_contains_string_in_key(): void
    {
        $payload = Payload::fromArray(
            [
                'addedWith' => 2.5,
                'updatedWith' => '-2',
                'deletedWith' => 0.01,
            ]
        );
        self::assertSame(2.5, $payload->getFloatWhereKeyContains('With'));
        self::assertSame(-2.0, $payload->getFloatWhereKeyContains('up'));
        self::assertSame(0.01, $payload->getFloatWhereKeyContains('le'));

        $this->expectException(PayloadKeyNotFound::class);
        $this->expectExceptionMessage(
            'No key with needle "invalid" could be found. Available keys: "addedWith, updatedWith, deletedWith".'
        );
        $payload->getStringWhereKeyContains('invalid');
    }

    public function test_it_should_return_boolean_of_first_key_that_contains_string_in_key(): void
    {
        $payload = Payload::fromArray(
            [
                'isAdded' => '0',
                'hasSomething' => 1,
                'wasDeleted' => false,
            ]
        );
        self::assertFalse($payload->getBooleanWhereKeyContains('is'));
        self::assertTrue($payload->getBooleanWhereKeyContains('th'));
        self::assertFalse($payload->getBooleanWhereKeyContains('D'));

        $this->expectException(PayloadKeyNotFound::class);
        $this->expectExceptionMessage(
            'No key with needle "invalid" could be found. Available keys: "isAdded, hasSomething, wasDeleted".'
        );
        $payload->getStringWhereKeyContains('invalid');
    }

    public function test_it_should_return_date_of_first_key_that_contains_string_in_key(): void
    {
        $payload = Payload::fromArray(
            [
                'addedAt' => '2000-01-01',
                'updatedAt' => '2000-01-02',
                'deletedAt' => '2000-02-04 19:21:11.012345',
            ]
        );
        self::assertSame(
            '2000-01-01 00:00:00.000000',
            $payload->getDateTimeWhereKeyContains('At')->format('Y-m-d H:i:s.u')
        );
        self::assertSame(
            '2000-01-02 00:00:00.000000',
            $payload->getDateTimeWhereKeyContains('up')->format('Y-m-d H:i:s.u')
        );
        self::assertSame(
            '2000-02-04 19:21:11.012345',
            $payload->getDateTimeWhereKeyContains('le')->format('Y-m-d H:i:s.u')
        );

        $this->expectException(PayloadKeyNotFound::class);
        $this->expectExceptionMessage(
            'No key with needle "invalid" could be found. Available keys: "addedAt, updatedAt, deletedAt".'
        );
        $payload->getStringWhereKeyContains('invalid');
    }
}
