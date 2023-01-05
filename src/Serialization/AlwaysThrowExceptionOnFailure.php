<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Assert\Assertion;
use function json_encode;
use function sprintf;

final class AlwaysThrowExceptionOnFailure implements PayloadFailureStrategy
{
    /**
     * @param string $key
     * @param SerializableAttribute[]|string[]|int[]|bool[]|float[] $payload
     * @return SerializableAttribute|bool|float|int|string
     */
    public function handleKeyNotFound(string $key, array $payload)
    {
        throw new PayloadKeyNotFound(
            sprintf(
                'Payload key "%s" could not be found in payload: "%s".',
                $key,
                json_encode($payload)
            )
        );
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
     * @param mixed $value
     * @return string
     */
    public function handleInvalidStringValue(string $key, $value): string
    {
        throw UnexpectedTypeForPayloadKey::unexpectedValueForKey($key, $value, 'string');
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function handleInvalidIntegerValue(string $key, $value): int
    {
        throw UnexpectedTypeForPayloadKey::unexpectedValueForKey($key, $value, 'integer');
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return float
     */
    public function handleInvalidFloatValue(string $key, $value): float
    {
        throw UnexpectedTypeForPayloadKey::unexpectedValueForKey($key, $value, 'float');
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function handleInvalidBooleanValue(string $key, $value): bool
    {
        throw UnexpectedTypeForPayloadKey::unexpectedValueForKey($key, $value, 'boolean');
    }
}
