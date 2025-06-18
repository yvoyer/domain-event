<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Event\Blog;

use Star\Component\DomainEvent\DomainEvent;
use Star\Example\Blog\Domain\Model\BlogId;

final class BlogWasCreated implements DomainEvent
{
    public function __construct(
        private BlogId $blogId,
    ) {
    }

    public function blogName(): string
    {
        return $this->blogId->toString();
    }
}
