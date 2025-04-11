<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use ArrayAccess;
use DateTimeInterface;
use RuntimeException;
use function array_filter;
use function array_key_exists;
use function in_array;
use function is_numeric;
use function is_string;
use function json_decode;
use function strpos;

/**
 * @deprecated ArrayAccess will be removed in 3.0
 * @implements ArrayAccess<string, SerializableAttribute|string|int|bool|float>
 */
final class Payload implements ArrayAccess
{
    /**
     * @var array<string, SerializableAttribute|string|int|bool|float> $data
     */
    private $data;

    /**
     * @param array<string, SerializableAttribute|string|int|bool|float> $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public function keyExists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function keyContainsString(string $string): bool
    {
        return count(
            array_filter(
                $this->data,
                function (string $key) use ($string): bool {
                    return strpos($key, $string) !== false;
                },
                ARRAY_FILTER_USE_KEY
            )
        ) > 0;
    }

    public function getString(string $key, PayloadFailureStrategy $strategy = null): string
    {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!is_string($value)) {
            return $strategy->handleInvalidStringValue($key, $value);
        }

        return $strategy->transformRawValueToString($value);
    }

    public function getInteger(string $key, PayloadFailureStrategy $strategy = null): int
    {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!is_numeric($value)) {
            return $strategy->handleInvalidIntegerValue($key, $value);
        }

        return $strategy->transformRawValueToInt($value);
    }

    public function getFloat(string $key, PayloadFailureStrategy $strategy = null): float
    {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!is_numeric($value)) {
            return $strategy->handleInvalidFloatValue($key, $value);
        }

        return $strategy->transformRawValueToFloat($value);
    }

    public function getBoolean(string $key, PayloadFailureStrategy $strategy = null): bool
    {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!in_array($value, ['1', '0', 1, 0, true, false], true)) {
            return $strategy->handleInvalidBooleanValue($key, $value);
        }

        return $strategy->transformRawValueToBoolean($value);
    }

    public function getDateTime(string $key, PayloadFailureStrategy $strategy = null): DateTimeInterface
    {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!is_string($value)) {
            return $strategy->handleInvalidDateTimeValue($key, $value);
        }

        return $strategy->transformRawValueToDateTime($value);
    }

    /**
     * @return array<string, SerializableAttribute|string|int|bool|float>
     * @internal Do not use, prone to removal
     */
    public function toArray(): array
    {
        return $this->data;
    }

    private function assertStrategy(PayloadFailureStrategy $strategy = null): PayloadFailureStrategy
    {
        if (!$strategy) {
            $strategy = new AlwaysThrowExceptionOnFailure();
        }

        return $strategy;
    }

    /**
     * @param string $key
     * @param PayloadFailureStrategy $strategy
     * @return SerializableAttribute|bool|float|int|string
     */
    private function getValue(string $key, PayloadFailureStrategy $strategy)
    {
        if (!array_key_exists($key, $this->data)) {
            return $strategy->handleKeyNotFound($key, $this->data);
        }

        return $this->data[$key];
    }

    /**
     * @param array<string, SerializableAttribute|string|int|bool|float> $payload
     * @return static
     */
    public static function fromArray(array $payload): self
    {
        return new self($payload);
    }

    public static function fromJson(string $json): self
    {
        /**
         * @var array<string, string|int|bool|float> $payload
         */
        $payload = json_decode($json, true);

        return self::fromArray($payload);
    }

    /**
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @param string $offset
     * @return SerializableAttribute|string|int|bool|float
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @param string $offset
     * @param SerializableAttribute|string|int|bool|float $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException(__METHOD__ . ' should never be invoked.');
    }

    /**
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException(__METHOD__ . ' should never be invoked.');
    }
}
