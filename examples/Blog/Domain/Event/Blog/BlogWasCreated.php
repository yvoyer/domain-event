<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Event\Blog;

use Star\Component\DomainEvent\DomainEvent;
use Star\Example\Blog\Domain\Model\BlogId;

final class BlogWasCreated implements DomainEvent
{
    /**
     * @var string
     */
    private $blogName;

    public function __construct(BlogId $blogId)
    {
        $this->blogName = $blogId->toString();
    }

    public function blogName(): string
    {
        return $this->blogName;
    }
}
