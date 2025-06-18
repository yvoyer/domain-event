<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Command;

use Star\Component\DomainEvent\EventPublisher;
use Star\Example\Blog\Application\Bridge\Event\UserWasRegistered;

final class RegisterUserHandler
{
    public function __construct(
        private EventPublisher $publisher,
    ) {
    }

    public function __invoke(RegisterUser $command): void
    {
        $this->publisher->publish(new UserWasRegistered($command->blogName()));
    }
}
