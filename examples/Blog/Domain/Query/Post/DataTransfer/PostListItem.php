<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Query\Post\DataTransfer;

final class PostListItem
{
    public ?string $id;
    public string $title;
    public ?string $blogName;
    public ?bool $published;
    public ?string $publishedAt;
    public ?string $publishedBy;
}
