<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Event\Post;

use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\Serialization\CreatedFromPayload;
use Star\Component\DomainEvent\Serialization\Payload;
use Star\Example\Blog\Domain\Model\BlogId;
use Star\Example\Blog\Domain\Model\Post\PostId;
use Star\Example\Blog\Domain\Model\Post\PostTitle;

final class PostWasDrafted implements CreatedFromPayload
{
    public function __construct(
        private PostId $id,
        private PostTitle $title,
        private BlogId $blogId,
    ) {
    }

    public function postId(): PostId
    {
        return $this->id;
    }

    public function title(): PostTitle
    {
        return $this->title;
    }

    public function blogId(): BlogId
    {
        return $this->blogId;
    }

    public static function fromPayload(Payload $payload): DomainEvent
    {
        return new self(
            PostId::fromString($payload->getString('id')),
            new PostTitle($payload->getString('title')),
            new BlogId($payload->getString('blogId')),
        );
    }
}
