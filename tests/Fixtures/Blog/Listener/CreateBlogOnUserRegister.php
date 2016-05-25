<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Fixtures\Blog\Listener;

use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Fixtures\Blog\Blog;
use Star\Component\DomainEvent\Fixtures\Blog\Event\UserWasRegistered;

/**
 * @author  Yannick Voyer (http://github.com/yvoyer)
 */
final class CreateBlogOnUserRegister implements EventListener
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    /**
     * @param EventPublisher $publisher
     */
    public function __construct(EventPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function onUserWasRegistered(UserWasRegistered $event)
    {
        $blog = Blog::createBlog($event->blogName());
        $this->publisher->publishChanges($blog->uncommitedEvents());
    }

    /**
     * Key value map, where key is the event full class name and the map is the method
     * to call when the event is triggered.
     *
     * ie.
     * array(
     *     "Full\Path\To\Event" => 'onEvent',
     * )
     *
     * @return array
     */
    public function listensTo()
    {
        return [
            UserWasRegistered::class => 'onUserWasRegistered',
        ];
    }
}
