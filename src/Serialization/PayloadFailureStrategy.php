<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use DateTimeInterface;

interface PayloadFailureStrategy
{
    /**
     * @param string $key
     * @param SerializableAttribute[]|string[]|int[]|bool[]|float[] $payload
     * @return SerializableAttribute|bool|float|int|string
     * @throws PayloadKeyNotFound
     */
    public function handleKeyNotFound(string $key, array $payload);

    /**
     * @param string $key
     * @param mixed $value
     * @return string
     * @throws UnexpectedTypeForPayloadKey
     */
    public function handleInvalidStringValue(string $key, $value): string;

    /**
     * @param string $key
     * @param mixed $value
     * @return int
     * @throws UnexpectedTypeForPayloadKey
     */
    public function handleInvalidIntegerValue(string $key, $value): int;

    /**
     * @param string $key
     * @param mixed $value
     * @return float
     * @throws UnexpectedTypeForPayloadKey
     */
    public function handleInvalidFloatValue(string $key, $value): float;

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws UnexpectedTypeForPayloadKey
     */
    public function handleInvalidBooleanValue(string $key, $value): bool;

    /**
     * @param string $key
     * @param mixed $value
     * @return DateTimeInterface
     * @throws UnexpectedTypeForPayloadKey
     */
    public function handleInvalidDateTimeValue(string $key, $value): DateTimeInterface;

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

    /**
     * @param mixed $value
     * @return DateTimeInterface
     */
    public function transformRawValueToDateTime($value): DateTimeInterface;
}
