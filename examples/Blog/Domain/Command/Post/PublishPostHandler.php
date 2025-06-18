<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Command\Post;

use Star\Component\DomainEvent\EventPublisher;
use Star\Example\Blog\Domain\Model\Post\PostRepository;

final class PublishPostHandler
{
    public function __construct(
        private PostRepository $posts,
        private EventPublisher $publisher,
    ) {
    }

    public function __invoke(PublishPost $command): void
    {
        $post = $this->posts->getPostWithId($command->postId());
        $post->publish($command->publishedAt(), $command->publishedBy());

        $this->posts->savePost($post);
        $this->publisher->publishChanges($post->uncommitedEvents());
    }
}
