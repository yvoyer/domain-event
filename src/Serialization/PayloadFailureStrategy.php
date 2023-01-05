<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

interface PayloadFailureStrategy
{
    /**
     * @param string $key
     * @param SerializableAttribute[]|string[]|int[]|bool[]|float[] $payload
     * @return SerializableAttribute|bool|float|int|string
     */
    public function handleKeyNotFound(string $key, array $payload);

    /**
     * @param string $key
     * @param mixed $value
     * @return string
     */
    public function handleInvalidStringValue(string $key, $value): string;

    /**
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function handleInvalidIntegerValue(string $key, $value): int;

    /**
     * @param string $key
     * @param mixed $value
     * @return float
     */
    public function handleInvalidFloatValue(string $key, $value): float;

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function handleInvalidBooleanValue(string $key, $value): bool;

    /**
     * @param mixed $value
     * @return string
     */
    public function transformRawValueToString($value): string;

    /**
     * @param mixed $value
     * @return int
     */
    public function transformRawValueToInt($value): int;

    /**
     * @param mixed $value
     * @return float
     */
    public function transformRawValueToFloat($value): float;

    /**
     * @param mixed $value
     * @return bool
     */
    public function transformRawValueToBoolean($value): bool;
}
