<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use RuntimeException;
use function sprintf;

final class UnexpectedTypeForPayloadKey extends RuntimeException
{
    public static function unexpectedValueForKey(string $key, $value, string $expectedType): self
    {
        return new self(
            sprintf(
                'Value "%s" for key "%s" is not of expected type "%s", got "%s".',
                $value,
                $key,
                $expectedType,
                gettype($value)
            )
        );
    }
}
