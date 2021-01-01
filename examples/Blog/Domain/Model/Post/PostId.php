<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Model\Post;

use Star\Component\DomainEvent\Serialization\SerializableAttribute;

final class PostId implements SerializableAttribute
{
    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toSerializableString(): string
    {
        return $this->toString();
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public static function fromInt(int $id): self
    {
        return new self((string) $id);
    }

    public static function asUUID(): self
    {
        return new self(\uniqid('uuid'));
    }
}
