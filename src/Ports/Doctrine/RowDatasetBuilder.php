<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Doctrine;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use function sprintf;

final class RowDatasetBuilder
{
    /**
     * @var array<string, string>
     */
    private array $values;

    /**
     * @var array<string, mixed>
     */
    private array $parameters;

    /**
     * @var array<string, string>
     */
    private array $types;

    /**
     * @param array<string, string> $values
     * @param array<string, mixed> $parameters
     * @param array<string, string> $types
     */
    private function __construct(
        array $values,
        array $parameters,
        array $types
    ) {
        $this->values = $values;
        $this->parameters = $parameters;
        $this->types = $types;
    }

    public function setStringColumn(
        string $column,
        string $value,
        string $type = Types::STRING
    ): void {
        $this->values[$column] = sprintf(':%s', $column);
        $this->parameters[$column] = $value;
        $this->types[$column] = $type;
    }

    public function setIntegerColumn(
        string $column,
        int $value,
        string $type = Types::INTEGER
    ): void {
        $this->values[$column] = sprintf(':%s', $column);
        $this->parameters[$column] = $value;
        $this->types[$column] = $type;
    }

    public function setFloatColumn(
        string $column,
        float $value,
        string $type = Types::FLOAT
    ): void {
        $this->values[$column] = sprintf(':%s', $column);
        $this->parameters[$column] = $value;
        $this->types[$column] = $type;
    }

    public function setDateTimeColumn(
        string $column,
        DateTimeInterface $value,
        string $type = Types::DATETIME_IMMUTABLE
    ): void {
        $this->values[$column] = sprintf(':%s', $column);
        $this->parameters[$column] = $value;
        $this->types[$column] = $type;
    }

    public function setBooleanColumn(
        string $column,
        bool $value,
        string $type = Types::BOOLEAN
    ): void {
        $this->values[$column] = sprintf(':%s', $column);
        $this->parameters[$column] = $value;
        $this->types[$column] = $type;
    }

    /**
     * @return array<string, string>
     */
    final public function buildValues(): array
    {
        return $this->values;
    }

    /**
     * @return array<string, mixed>
     */
    final public function buildParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<string, string>
     */
    final public function buildTypes(): array
    {
        return $this->types;
    }

    /**
     * @param array<string, string> $values
     * @param array<string, mixed> $parameters
     * @param array<string, string> $types
     */
    public static function fromPayload(
        array $values,
        array $parameters,
        array $types
    ): self {
        return new self(
            $values,
            $parameters,
            $types
        );
    }
}
