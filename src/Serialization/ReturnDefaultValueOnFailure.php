<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Assert\Assertion;

final class ReturnDefaultValueOnFailure implements PayloadFailureStrategy
{
    /**
     * @var string|int|float|bool
     */
    private $value;

    /**
     * @param string|int|float|bool $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function handleKeyNotFound(string $key, array $payload)
    {
        return $this->value;
    }

    public function transformRawValueToString($value): string
    {
        Assertion::string($value);
        return $value;
    }

    public function transformRawValueToInt($value): int
    {
        Assertion::numeric($value);
        return (int) $value;
    }

    public function transformRawValueToFloat($value): float
    {
        Assertion::numeric($value);
        return (float) $value;
    }

    public function transformRawValueToBoolean($value): bool
    {
        Assertion::inArray($value, ['0', '1', 0, 1, true, false]);
        return (bool) $value;
    }

    public function handleInvalidStringValue(string $key, $value): string
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function handleInvalidIntegerValue(string $key, $value): int
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function handleInvalidFloatValue(string $key, $value): float
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    public function handleInvalidBooleanValue(string $key, $value): bool
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }
}
