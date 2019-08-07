<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Command;

final class RegisterUser
{
    /**
     * @var string
     */
    private $blogName;

    public function __construct(string $blogName)
    {
        $this->blogName = $blogName;
    }

    public function blogName(): string
    {
        return $this->blogName;
    }
}
