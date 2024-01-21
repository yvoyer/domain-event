<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Event\Post;

use DateTimeInterface;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\Serialization\SerializableDateTime;
use Star\Example\Blog\Domain\Model\Post\PostId;

final class PostWasPublished implements DomainEvent
{
    /**
     * @var PostId
     */
    private $postId;

    /**
     * @var SerializableDateTime
     */
    private $publishedAt;

    /**
     * @var string
     */
    private $publishedBy;

    public function __construct(
        PostId $postId,
        DateTimeInterface $publishedAt,
        string $publishedBy
    ) {
        $this->postId = $postId;
        $this->publishedAt = new SerializableDateTime($publishedAt);
        $this->publishedBy = $publishedBy;
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
