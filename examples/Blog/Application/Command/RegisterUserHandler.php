<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Command;

use Star\Component\DomainEvent\EventPublisher;
use Star\Example\Blog\Application\Bridge\Event\UserWasRegistered;

final class RegisterUserHandler
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    public function __construct(EventPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function __invoke(RegisterUser $command): void
    {
        $this->publisher->publish(new UserWasRegistered($command->selectedBlogName()));
    }
}
