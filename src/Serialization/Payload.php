<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use Assert\Assertion;
use DateTimeImmutable;
use DateTimeInterface;
use function array_filter;
use function array_key_exists;
use function in_array;
use function is_numeric;
use function is_string;
use function json_decode;
use function sprintf;
use function str_contains;

final class Payload
{
    /**
     * @var array<string, SerializableAttribute|string|int|bool|float> $data
     */
    private array $data;

    /**
     * @param array<string, SerializableAttribute|string|int|bool|float> $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public function keyExists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function keyContains(string $string): bool
    {
        return count(
            array_filter(
                $this->data,
                function (string $key) use ($string): bool {
                    return str_contains($key, $string);
                },
                ARRAY_FILTER_USE_KEY
            )
        ) > 0;
    }

    public function getString(string $key, ?PayloadFailureStrategy $strategy = null): string
    {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!is_string($value)) {
            return $strategy->handleInvalidStringValue($key, $value);
        }

        return $strategy->transformRawValueToString($value);
    }

    /**
     * Returns the first value where the $needle is found in key (case sensitive).
     *
     * @throws PayloadKeyNotFound When no key with $needle could be found
     */
    public function getStringWhereKeyContains(string $needle): string
    {
        /**
         * @var string $value
         */
        $value = $this->getValueWhereKeyContains($needle);
        Assertion::string($value);

        return $value;
    }

    public function getInteger(string $key, ?PayloadFailureStrategy $strategy = null): int
    {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!is_numeric($value)) {
            return $strategy->handleInvalidIntegerValue($key, $value);
        }

        return $strategy->transformRawValueToInt($value);
    }

    /**
     * Returns the first value where the $needle is found in key (case sensitive).
     *
     * @throws PayloadKeyNotFound When no key with $needle could be found
     */
    public function getIntegerWhereKeyContains(string $needle): int
    {
        /**
         * @var int|string $value
         */
        $value = $this->getValueWhereKeyContains($needle);
        Assertion::integerish($value);

        return (int) $value;
    }

    public function getFloat(string $key, ?PayloadFailureStrategy $strategy = null): float
    {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!is_numeric($value)) {
            return $strategy->handleInvalidFloatValue($key, $value);
        }

        return $strategy->transformRawValueToFloat($value);
    }

    /**
     * Returns the first value where the $needle is found in key (case sensitive).
     *
     * @throws PayloadKeyNotFound When no key with $needle could be found
     */
    public function getFloatWhereKeyContains(string $needle): float
    {
        /**
         * @var float|string $value
         */
        $value = $this->getValueWhereKeyContains($needle);
        Assertion::numeric($value);

        return (float) $value;
    }

    public function getBoolean(
        string $key,
        ?PayloadFailureStrategy $strategy = null,
    ): bool {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!in_array($value, ['1', '0', 1, 0, true, false], true)) {
            return $strategy->handleInvalidBooleanValue($key, $value);
        }

        return $strategy->transformRawValueToBoolean($value);
    }

    /**
     * Returns the first value where the $needle is found in key (case sensitive).
     *
     * @throws PayloadKeyNotFound When no key with $needle could be found
     */
    public function getBooleanWhereKeyContains(
        string $needle,
    ): bool {
        /**
         * @var bool|int|string $value
         */
        $value = $this->getValueWhereKeyContains($needle);
        Assertion::inArray($value, ['1', '0', 1, 0, true, false]);

        return (bool) $value;
    }

    public function getDateTime(
        string $key,
        ?PayloadFailureStrategy $strategy = null,
    ): DateTimeInterface {
        $strategy = $this->assertStrategy($strategy);
        $value = $this->getValue($key, $strategy);
        if (!is_string($value)) {
            return $strategy->handleInvalidDateTimeValue($key, $value);
        }

        return $strategy->transformRawValueToDateTime($value);
    }

    /**
     * Returns the first value where the $needle is found in key (case sensitive).
     *
     * @throws PayloadKeyNotFound When no key with $needle could be found
     */
    public function getDateTimeWhereKeyContains(
        string $needle,
    ): DateTimeInterface {
        return new DateTimeImmutable($this->getStringWhereKeyContains($needle));
    }

    /**
     * @return array<string, SerializableAttribute|string|int|bool|float>
     * @internal Do not use, prone to removal
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Returns the first value where the $needle is found in key (case sensitive).
     *
     * @throws PayloadKeyNotFound When no key with $needle could be found
     * @return SerializableAttribute|string|int|bool|float
     */
    private function getValueWhereKeyContains(string $needle): mixed
    {
        foreach ($this->data as $key => $value) {
            if (str_contains($key, $needle)) {
                return $value;
            }
        }

        throw new PayloadKeyNotFound(
            sprintf(
                'No key with needle "%s" could be found. Available keys: "%s".',
                $needle,
                implode(', ', array_keys($this->data))
            )
        );
    }

    private function assertStrategy(
        ?PayloadFailureStrategy $strategy = null,
    ): PayloadFailureStrategy {
        if (!$strategy) {
            $strategy = new AlwaysThrowExceptionOnFailure();
        }

        return $strategy;
    }

    /**
     * @param string $key
     * @param PayloadFailureStrategy $strategy
     * @return SerializableAttribute|bool|float|int|string
     */
    private function getValue(
        string $key,
        PayloadFailureStrategy $strategy,
    ): mixed {
        if (!array_key_exists($key, $this->data)) {
            return $strategy->handleKeyNotFound($key, $this->data);
        }

        return $this->data[$key];
    }

    /**
     * @param array<string, SerializableAttribute|string|int|bool|float> $payload
     * @return static
     */
    public static function fromArray(array $payload): self
    {
        return new self($payload);
    }

    public static function fromJson(string $json): self
    {
        /**
         * @var array<string, string|int|bool|float> $payload
         */
        $payload = json_decode($json, true);

        return self::fromArray($payload);
    }
}
