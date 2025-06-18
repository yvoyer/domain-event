<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Bridge\Event;

use Star\Component\DomainEvent\DomainEvent;

final class UserWasRegistered implements DomainEvent
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
