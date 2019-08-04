<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Bridge\Event;

use Star\Component\DomainEvent\DomainEvent;

final class UserWasRegistered implements DomainEvent
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
