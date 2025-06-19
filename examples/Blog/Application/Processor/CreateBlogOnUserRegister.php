<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Processor;

use Star\Component\DomainEvent\EventListener;
use Star\Example\Blog\Application\Bridge\Event\UserWasRegistered;
use Star\Example\Blog\Domain\Command\Blog\CreateBlog;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Star\Example\Blog\Domain\Model\BlogId;

final class CreateBlogOnUserRegister implements EventListener
{
    public function __construct(
        private CommandBus $bus,
    ) {
    }

    public function onUserWasRegistered(UserWasRegistered $event): void
    {
        $this->bus->dispatchCommand(new CreateBlog(new BlogId($event->blogName())));
    }

    public static function getListenedEvents(): array
    {
        return [
            UserWasRegistered::class => 'onUserWasRegistered',
        ];
    }
}
