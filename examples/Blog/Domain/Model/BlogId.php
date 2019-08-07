<?php declare(strict_types=1);

namespace Star\Example\Blog\Domain\Model;

final class BlogId
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function toString(): string
    {
        return $this->name;
    }
}
