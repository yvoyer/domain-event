<?php declare(strict_types=1);

namespace Star\Example\Blog\Infrastructure\Persistence\InMemory;

use Star\Example\Blog\Domain\Model\Post\PostAggregate;
use Star\Example\Blog\Domain\Model\Post\PostId;
use Star\Example\Blog\Domain\Model\Post\PostRepository;

final class PostCollection implements PostRepository
{
    /**
     * @var PostAggregate[]
     */
    private array $posts = [];

    public function savePost(PostAggregate $post): void
    {
        $this->posts[$post->getId()->toString()] = $post;
    }

    public function getPostWithId(PostId $id): PostAggregate
    {
        return $this->posts[$id->toString()];
    }
}
