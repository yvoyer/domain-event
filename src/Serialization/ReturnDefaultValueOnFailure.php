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

    /**
     * @param string $key
     * @param SerializableAttribute[]|string[]|int[]|bool[]|float[] $payload
     * @return SerializableAttribute|bool|float|int|string
     */
    public function handleKeyNotFound(string $key, array $payload)
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function transformRawValueToString($value): string
    {
        Assertion::string($value);
        return $value;
    }

    /**
     * @param mixed $value
     * @return int
     */
    public function transformRawValueToInt($value): int
    {
        Assertion::numeric($value);
        return (int) $value;
    }

    /**
     * @param mixed $value
     * @return float
     */
    public function transformRawValueToFloat($value): float
    {
        Assertion::numeric($value);
        return (float) $value;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function transformRawValueToBoolean($value): bool
    {
        Assertion::inArray($value, ['0', '1', 0, 1, true, false]);
        return (bool) $value;
    }

    /**
     * @param string $key
     * @param string $value
     * @return string
     */
    public function handleInvalidStringValue(string $key, $value): string
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    /**
     * @param string $key
     * @param int $value
     * @return int
     */
    public function handleInvalidIntegerValue(string $key, $value): int
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    /**
     * @param string $key
     * @param float $value
     * @return float
     */
    public function handleInvalidFloatValue(string $key, $value): float
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }

    /**
     * @param string $key
     * @param bool $value
     * @return bool
     */
    public function handleInvalidBooleanValue(string $key, $value): bool
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented yet.');
    }
}
