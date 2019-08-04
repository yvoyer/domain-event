<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Query\Post\DataTransfer;

final class PostListItem
{
    public $id;
    public $title;
    public $blogName;
    public $published;
    public $publishedAt;
    public $publishedBy;
}
