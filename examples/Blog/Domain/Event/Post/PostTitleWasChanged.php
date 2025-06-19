<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Event\Post;

use DateTimeImmutable;
use DateTimeInterface;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\Serialization\CreatedFromPayload;
use Star\Component\DomainEvent\Serialization\SerializableDateTime;
use Star\Component\DomainEvent\Serialization\Payload;
use Star\Example\Blog\Domain\Model\Post\PostId;
use Star\Example\Blog\Domain\Model\Post\PostTitle;

final class PostTitleWasChanged implements CreatedFromPayload
{
    private SerializableDateTime $changedAt;

    public function __construct(
        private PostId $postId,
        private PostTitle $oldTitle,
        private PostTitle $newTitle,
        DateTimeInterface $changedAt,
    ) {
        $this->changedAt = new SerializableDateTime($changedAt);
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

    final public function changedAt(): DateTimeInterface
    {
        return $this->changedAt->toDateTime();
    }

    public static function fromPayload(Payload $payload): DomainEvent
    {
        return new self(
            PostId::fromString($payload->getString('postId')),
            new PostTitle($payload->getString('oldTitle')),
            new PostTitle($payload->getString('newTitle')),
            new DateTimeImmutable($payload->getString('changedAt'))
        );
    }
}
