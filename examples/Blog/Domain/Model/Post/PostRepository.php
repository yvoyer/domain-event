<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Model\Post;

interface PostRepository
{
    public function savePost(PostAggregate $post): void;

    public function getPostWithId(PostId $id): PostAggregate;
}
