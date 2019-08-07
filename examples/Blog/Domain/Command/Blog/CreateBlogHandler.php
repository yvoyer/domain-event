<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Command\Blog;

use Star\Component\DomainEvent\EventPublisher;
use Star\Example\Blog\Domain\Event\Blog\BlogWasCreated;

final class CreateBlogHandler
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    public function __construct(EventPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function __invoke(CreateBlog $command): void
    {
        $this->publisher->publish(new BlogWasCreated($command->blogId()));
    }
}
