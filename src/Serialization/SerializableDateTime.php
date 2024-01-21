<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Serialization;

use DateTimeImmutable;
use DateTimeInterface;

final class SerializableDateTime implements SerializableAttribute
{
    /**
     * @var DateTimeInterface
     */
    private $dateTime;

    public function __construct(DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function toSerializableString(): string
    {
        return $this->dateTime->format('Y-m-d H:i:s.u');
    }

    final public function toDateTime(): DateTimeInterface
    {
        return $this->dateTime;
    }

    public static function fromNow(): self
    {
        return new self(new DateTimeImmutable());
    }

    public static function fromString(string $date): self
    {
        return new self(new DateTimeImmutable($date));
    }
}
