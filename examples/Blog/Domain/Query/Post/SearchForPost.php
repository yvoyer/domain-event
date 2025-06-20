<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Query\Post;

use Assert\Assertion;
use Star\Component\DomainEvent\Messaging\Query;
use Star\Example\Blog\Domain\Query\Post\DataTransfer\PostListItem;

final class SearchForPost implements Query
{
    /**
     * @var array<int|string, string>
     */
    private array $strings;

    /**
     * @var array<string, PostListItem>
     */
    private array $result;

    public function __construct(string ...$strings)
    {
        $this->strings = $strings;
    }

    /**
     * @return array<int|string, string>
     */
    public function strings(): array
    {
        return $this->strings;
    }

    /**
     * @param array<string, PostListItem> $result
     */
    public function __invoke(mixed $result): void
    {
        Assertion::allIsInstanceOf($result, PostListItem::class);
        $this->result = $result;
    }

    /**
     * @return array<string, PostListItem>
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
