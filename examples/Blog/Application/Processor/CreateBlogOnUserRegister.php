<?php declare(strict_types=1);

namespace Star\Example\Blog\Application\Processor;

use Star\Component\DomainEvent\EventListener;
use Star\Example\Blog\Application\Bridge\Event\UserWasRegistered;
use Star\Example\Blog\Domain\Command\Blog\CreateBlog;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Star\Example\Blog\Domain\Model\BlogId;

final class CreateBlogOnUserRegister implements EventListener
{
    /**
     * @var CommandBus
     */
    private $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    public function onUserWasRegistered(UserWasRegistered $event): void
    {
        $this->bus->dispatchCommand(new CreateBlog(new BlogId($event->blogName())));
    }

    public function listensTo(): array
    {
        return [
            UserWasRegistered::class => 'onUserWasRegistered',
        ];
    }
}
