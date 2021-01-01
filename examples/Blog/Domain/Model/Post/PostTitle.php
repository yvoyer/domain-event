<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Model\Post;

use Star\Component\DomainEvent\Serialization\SerializableAttribute;

final class PostTitle implements SerializableAttribute
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
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
}
