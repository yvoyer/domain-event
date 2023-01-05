<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Assert\Assertion;
use function json_encode;
use function sprintf;

final class AlwaysThrowExceptionOnFailure implements PayloadFailureStrategy
{
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
        throw UnexpectedTypeForPayloadKey::unexpectedValueForKey($key, $value, 'string');
    }

    public function handleInvalidIntegerValue(string $key, $value): int
    {
        throw UnexpectedTypeForPayloadKey::unexpectedValueForKey($key, $value, 'integer');
    }

    public function handleInvalidFloatValue(string $key, $value): float
    {
        throw UnexpectedTypeForPayloadKey::unexpectedValueForKey($key, $value, 'float');
    }

    public function handleInvalidBooleanValue(string $key, $value): bool
    {
        throw UnexpectedTypeForPayloadKey::unexpectedValueForKey($key, $value, 'boolean');
    }
}
