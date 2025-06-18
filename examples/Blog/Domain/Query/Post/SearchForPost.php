<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Query\Post;

use Assert\Assertion;
use Star\Component\DomainEvent\Messaging\Results\CollectionQuery;
use Star\Example\Blog\Domain\Query\Post\DataTransfer\PostListItem;

final class SearchForPost extends CollectionQuery
{
    /**
     * @var string[]
     */
    private array $strings;

    public function __construct(string ...$strings)
    {
        $this->strings = $strings;
    }

    public function strings(): array
    {
        return $this->strings;
    }

    protected function validateResult($result): void
    {
        Assertion::allIsInstanceOf($result, PostListItem::class);
    }

    /**
     * @return PostListItem[]
     */
    public function getResult(): array
    {
        return parent::getResult();
    }
}
