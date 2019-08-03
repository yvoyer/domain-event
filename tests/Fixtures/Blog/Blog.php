<?php declare(strict_types=1);
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Fixtures\Blog;

use Star\Component\DomainEvent\AggregateRoot;
use Star\Component\DomainEvent\Fixtures\Blog\Event\BlogWasCreated;

/**
 * @author  Yannick Voyer (http://github.com/yvoyer)
 */
final class Blog extends AggregateRoot
{
    /**
     * @var string
     */
    private $name;

    public function name(): string
    {
        return $this->name;
    }

    public static function createBlog(string $blogName): self
    {
        return self::fromStream([new BlogWasCreated($blogName)]);
    }

    public function onBlogWasCreated(BlogWasCreated $event): void
    {
        $this->name = $event->blogName();
    }
}
