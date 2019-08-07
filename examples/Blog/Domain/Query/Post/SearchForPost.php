<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Query\Post;

use Star\Component\DomainEvent\Messaging\Results\CollectionQuery;
use Star\Example\Blog\Domain\Query\Post\DataTransfer\PostListItem;
use Webmozart\Assert\Assert;

final class SearchForPost extends CollectionQuery
{
    /**
     * @var string[]
     */
    private $strings;

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
        Assert::allIsInstanceOf($result, PostListItem::class);
    }

    /**
     * @return PostListItem[]
     */
    public function getResult(): array
    {
        return parent::getResult();
    }
}
