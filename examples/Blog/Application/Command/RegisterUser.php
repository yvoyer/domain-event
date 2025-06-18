<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Command;

final class RegisterUser
{
    public function __construct(
        private string $blogName,
    ) {
    }

    public function blogName(): string
    {
        return $this->blogName;
    }
}
