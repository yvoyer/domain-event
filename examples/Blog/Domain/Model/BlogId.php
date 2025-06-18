<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Model;

use Star\Component\DomainEvent\Serialization\SerializableAttribute;
use function uniqid;

final class BlogId implements SerializableAttribute
{
    public function __construct(
        private string $name,
    ) {
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function toSerializableString(): string
    {
        return $this->toString();
    }

    public static function asUuid(): self
    {
        return new self(uniqid('blog-uuid-'));
    }
}
