<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Query\Post;

use Assert\Assertion;
use Star\Component\DomainEvent\Messaging\Query;
use Star\Example\Blog\Domain\Query\Post\DataTransfer\PostListItem;

final class SearchForPost implements Query
{
    /**
     * @var string[]
     */
    private array $strings;

    /**
     * @var array<int, PostListItem>
     */
    private array $result;

    public function __construct(string ...$strings)
    {
        $this->strings = $strings;
    }

    public function strings(): array
    {
        return $this->strings;
    }

    public function __invoke($result): void
    {
        Assertion::allIsInstanceOf($result, PostListItem::class);
        $this->result = $result;
    }

    /**
     * @return PostListItem[]
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
