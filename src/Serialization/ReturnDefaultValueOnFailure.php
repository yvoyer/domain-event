<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Assert\Assertion;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;

final class ReturnDefaultValueOnFailure implements PayloadFailureStrategy
{
    /**
     * @param SerializableAttribute|bool|float|int|string $value
     */
    public function __construct(private mixed $value)
    {
    }

    public function handleKeyNotFound(string $key, array $payload): mixed
    {
        return $this->value;
    }

    public function handleInvalidStringValue(string $key, mixed $value): string
    {
        throw new RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function handleInvalidIntegerValue(string $key, mixed $value): int
    {
        throw new RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function handleInvalidFloatValue(string $key, mixed $value): float
    {
        throw new RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function handleInvalidBooleanValue(string $key, mixed $value): bool
    {
        throw new RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function handleInvalidDateTimeValue(string $key, mixed $value): DateTimeInterface
    {
        throw new RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function transformRawValueToString(mixed $value): string
    {
        Assertion::string($value);

        return (string) $value;
    }

    /**
     * @param int|float|string $value
     */
    public function transformRawValueToInt(mixed $value): int
    {
        Assertion::numeric($value);

        return (int) $value;
    }

    /**
     * @param int|float|string $value
     */
    public function transformRawValueToFloat(mixed $value): float
    {
        Assertion::numeric($value);

        return (float) $value;
    }

    /**
     * @param int|float|string|bool $value
     */
    public function transformRawValueToBoolean(mixed $value): bool
    {
        Assertion::inArray($value, ['0', '1', 0, 1, true, false]);

        return (bool) $value;
    }

    /**
     * @param string $value
     */
    public function transformRawValueToDateTime(mixed $value): DateTimeInterface
    {
        Assertion::string($value);

        return new DateTimeImmutable($value);
    }
}
