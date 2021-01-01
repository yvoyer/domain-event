<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Model;

use Star\Component\DomainEvent\Serialization\SerializableAttribute;

final class BlogId implements SerializableAttribute
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function toSerializableString(): string
    {
        return $this->toString();
    }
}
