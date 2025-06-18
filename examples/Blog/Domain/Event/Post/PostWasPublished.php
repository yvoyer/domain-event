<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Event\Post;

use DateTimeInterface;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\Serialization\SerializableDateTime;
use Star\Example\Blog\Domain\Model\Post\PostId;

final class PostWasPublished implements DomainEvent
{
    private SerializableDateTime $publishedAt;

    public function __construct(
        private PostId $postId,
        DateTimeInterface $publishedAt,
        private string $publishedBy,
    ) {
        $this->publishedAt = new SerializableDateTime($publishedAt);
    }

    public function postId(): PostId
    {
        return $this->postId;
    }

    public function publishedAt(): DateTimeInterface
    {
        return $this->publishedAt->toDateTime();
    }

    public function publishedBy(): string
    {
        return $this->publishedBy;
    }
}
