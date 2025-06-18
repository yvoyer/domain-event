<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Command\Post;

use DateTimeInterface;
use Star\Component\DomainEvent\Messaging\Command;
use Star\Example\Blog\Domain\Model\Post\PostId;

final class PublishPost implements Command
{
    public function __construct(
        private PostId $postId,
        private DateTimeInterface $publishedAt,
        private $publishedBy,
    ) {
    }

    public function postId(): PostId
    {
        return $this->postId;
    }

    public function publishedAt(): DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function publishedBy(): string
    {
        return $this->publishedBy;
    }
}
