<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

interface PayloadFailureStrategy
{
    public function handleKeyNotFound(string $key, array $payload);
    public function handleInvalidStringValue(string $key, $value): string;
    public function handleInvalidIntegerValue(string $key, $value): int;
    public function handleInvalidFloatValue(string $key, $value): float;
    public function handleInvalidBooleanValue(string $key, $value): bool;
    public function transformRawValueToString($value): string;
    public function transformRawValueToInt($value): int;
    public function transformRawValueToFloat($value): float;
    public function transformRawValueToBoolean($value): bool;
}
