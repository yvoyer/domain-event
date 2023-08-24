<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Model\Post;

use DateTimeInterface;
use Star\Component\DomainEvent\AggregateRoot;
use Star\Example\Blog\Domain\Event\Post\PostTitleWasChanged;
use Star\Example\Blog\Domain\Event\Post\PostWasDrafted;
use Star\Example\Blog\Domain\Event\Post\PostWasPublished;
use Star\Example\Blog\Domain\Model\BlogId;

final class PostAggregate extends AggregateRoot
{
    /**
     * @var PostId
     */
    private $id;

    /**
     * @var PostTitle
     */
    private $title;

    /**
     * @var BlogId
     */
    private $blog;

    public function getId(): PostId
    {
        return $this->id;
    }

    final public function getTitle(): PostTitle
    {
        return $this->title;
    }

    public function publish(\DateTimeInterface $publishedAt, string $publishedBy): void
    {
        $this->mutate(new PostWasPublished($this->id, $publishedAt, $publishedBy));
    }

    public function changeTitle(string $title, DateTimeInterface $date): void
    {
        $this->mutate(
            new PostTitleWasChanged(
                $this->id,
                $this->title,
                new PostTitle($title),
                $date
            )
        );
    }

    public static function draftPost(PostId $id, PostTitle $title, BlogId $blogId): self
    {
        return self::fromStream([new PostWasDrafted($id, $title, $blogId)]);
    }

    protected function onPostWasDrafted(PostWasDrafted $event): void
    {
        $this->id = $event->postId();
        $this->title = $event->title();
        $this->blog = $event->blogId();
    }

    protected function onPostWasPublished(PostWasPublished $event): void
    {
    }

    protected function onPostTitleWasChanged(PostTitleWasChanged $event): void
    {
        $this->title = $event->newTitle();
    }
}
