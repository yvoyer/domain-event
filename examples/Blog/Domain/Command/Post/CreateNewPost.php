<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Command\Post;

use Star\Component\DomainEvent\Messaging\Command;
use Star\Example\Blog\Domain\Model\BlogId;
use Star\Example\Blog\Domain\Model\Post\PostId;
use Star\Example\Blog\Domain\Model\Post\PostTitle;

final class CreateNewPost implements Command
{
    public function __construct(
        private PostId $postId,
        private PostTitle $title,
        private BlogId $blogId
    ) {
    }

    public function postId(): PostId
    {
        return $this->postId;
    }

    public function title(): PostTitle
    {
        return $this->title;
    }

    public function blogId(): BlogId
    {
        return $this->blogId;
    }
}
