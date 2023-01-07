<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use RuntimeException;
use function sprintf;

final class UnexpectedTypeForPayloadKey extends RuntimeException
{
    /**
     * @param string $key
     * @param mixed $value
     * @param string $expectedType
     * @return static
     */
    public static function unexpectedValueForKey(string $key, $value, string $expectedType): self
    {
        return new self(
            sprintf(
                'Value "%s" for key "%s" is not of expected type "%s", got "%s".',
                self::stringify($value),
                $key,
                $expectedType,
                gettype($value)
            )
        );
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected static function stringify($value): string
    {
        $result = \gettype($value);

        if (\is_bool($value)) {
            $result = $value ? '<TRUE>' : '<FALSE>';
        } elseif (\is_scalar($value)) {
            $val = (string)$value;

            if (\mb_strlen($val) > 100) {
                $val = \mb_substr($val, 0, 97).'...';
            }

            $result = $val;
        } elseif (\is_array($value)) {
            $result = '<ARRAY>';
        } elseif (\is_object($value)) {
            $result = \get_class($value);
        } elseif (\is_resource($value)) {
            $result = \get_resource_type($value);
        } elseif (null === $value) {
            $result = '<NULL>';
        }

        return $result;
    }
}
