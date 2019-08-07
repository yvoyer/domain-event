<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Event\Post;

use Star\Component\DomainEvent\DomainEvent;
use Star\Example\Blog\Domain\Model\Post\PostId;

final class PostWasPublished implements DomainEvent
{
    /**
     * @var PostId
     */
    private $postId;

    /**
     * @var \DateTimeInterface
     */
    private $publishedAt;

    /**
     * @var string
     */
    private $publishedBy;

    public function __construct(
        PostId $postId,
        \DateTimeInterface $publishedAt,
        string $publishedBy
    ) {
        $this->postId = $postId;
        $this->publishedAt = $publishedAt;
        $this->publishedBy = $publishedBy;
    }

    public function postId(): PostId
    {
        return $this->postId;
    }

    public function publishedAt(): \DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function publishedBy(): string
    {
        return $this->publishedBy;
    }
}
