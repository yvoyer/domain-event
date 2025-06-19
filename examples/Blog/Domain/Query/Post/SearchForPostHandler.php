<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Query\Post;

use Star\Component\DomainEvent\EventListener;
use Star\Example\Blog\Domain\Event\Post\PostWasDrafted;
use Star\Example\Blog\Domain\Event\Post\PostWasPublished;
use Star\Example\Blog\Domain\Query\Post\DataTransfer\PostListItem;
use function array_filter;
use function array_merge;

final class SearchForPostHandler implements EventListener
{
    /**
     * @var PostListItem[]
     */
    private array $posts = [];

    public function __invoke(SearchForPost $query): void
    {
        $result = [];
        foreach ($query->strings() as $pattern) {
            $items = array_filter(
                $this->posts,
                function (PostListItem $item) use ($pattern) {
                    return false !== \stripos($item->title, $pattern) && $item->published;
                }
            );

            $result = array_merge($result, $items);
        }

        $query($result);
    }

    public function onPostDrafted(PostWasDrafted $event): void
    {
        $item = new PostListItem();
        $item->id = $event->postId()->toString();
        $item->title = $event->title()->toString();
        $item->blogName = $event->blogId()->toString();
        $item->published = false;

        $this->posts[$event->postId()->toString()] = $item;
    }

    public function onPostPublished(PostWasPublished $event): void
    {
        $item = $this->posts[$event->postId()->toString()];
        $item->published = true;
        $item->publishedAt = $event->publishedAt()->format('Y-m-d');
        $item->publishedBy = $event->publishedBy();
    }

    public static function getListenedEvents(): array
    {
        return [
            PostWasDrafted::class => 'onPostDrafted',
            PostWasPublished::class => 'onPostPublished',
        ];
    }
}
