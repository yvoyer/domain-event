<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use DateTimeInterface;
use function array_key_exists;
use function in_array;
use function is_numeric;
use function is_string;
use function json_decode;

final class Payload
{
    /**
     * @var SerializableAttribute[]|string[]|int[]|bool[]|float[] $data
     */
    private $data;

    /**
     * @param SerializableAttribute[]|string[]|int[]|bool[]|float[] $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
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
     * @param SerializableAttribute[]|string[]|int[]|bool[]|float[] $payload
     * @return static
     */
    public static function fromArray(array $payload): self
    {
        return new self($payload);
    }

    public static function fromJson(string $json): self
    {
        /**
         * @var string[]|int[]|bool[]|float[] $payload
         */
        $payload = json_decode($json, true);

        return self::fromArray($payload);
    }
}
