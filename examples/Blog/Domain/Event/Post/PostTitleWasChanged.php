<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Event\Post;

use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\Serialization\CreatedFromTypedPayload;
use Star\Component\DomainEvent\Serialization\Payload;
use Star\Example\Blog\Domain\Model\Post\PostId;
use Star\Example\Blog\Domain\Model\Post\PostTitle;

final class PostTitleWasChanged implements CreatedFromTypedPayload
{
    /**
     * @var PostId
     */
    private $postId;

    /**
     * @var PostTitle
     */
    private $oldTitle;

    /**
     * @var PostTitle
     */
    private $newTitle;

    public function __construct(
        PostId $postId,
        PostTitle $oldTitle,
        PostTitle $newTitle
    ) {
        $this->postId = $postId;
        $this->oldTitle = $oldTitle;
        $this->newTitle = $newTitle;
    }

    final public function postId(): PostId
    {
        return $this->postId;
    }

    final public function oldTitle(): PostTitle
    {
        return $this->oldTitle;
    }

    final public function newTitle(): PostTitle
    {
        return $this->newTitle;
    }

    public static function fromPayload(Payload $payload): DomainEvent
    {
        return new self(
            PostId::fromString($payload->getString('postId')),
            new PostTitle($payload->getString('oldTitle')),
            new PostTitle($payload->getString('newTitle'))
        );
    }
}
