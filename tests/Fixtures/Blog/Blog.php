<?php
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

    public function name()
    {
        return $this->name;
    }

    /**
     * @param string $blogName
     *
     * @return Blog
     */
    public static function createBlog($blogName)
    {
        $blog = new Blog();
        $blog->mutate(new BlogWasCreated($blogName));

        return $blog;
    }

    /**
     * @param BlogWasCreated $event
     */
    public function onBlogWasCreated(BlogWasCreated $event)
    {
        $this->name = $event->blogName();
    }
}
