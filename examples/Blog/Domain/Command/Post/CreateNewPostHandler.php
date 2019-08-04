<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Command\Post;

use Star\Component\DomainEvent\EventPublisher;
use Star\Example\Blog\Domain\Model\Post\PostAggregate;
use Star\Example\Blog\Domain\Model\Post\PostRepository;

final class CreateNewPostHandler
{
    /**
     * @var PostRepository
     */
    private $posts;

    /**
     * @var EventPublisher
     */
    private $publisher;

    public function __construct(PostRepository $posts, EventPublisher $publisher)
    {
        $this->posts = $posts;
        $this->publisher = $publisher;
    }

    public function __invoke(CreateNewPost $command): void
    {
        $post = PostAggregate::draftPost($command->postId(), $command->title(), $command->blogId());

        $this->posts->savePost($post);
        $this->publisher->publishChanges($post->uncommitedEvents());
    }
}
