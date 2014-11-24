<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\ImmutableEventDispatcher;

/**
 * Class EventStore
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent
 */
final class EventStore implements EventPublisher
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * @param EventListener $listener
     */
    public function subscribe(EventListener $listener)
    {
        $this->dispatcher->addSubscriber($listener);
    }

    /**
     * @param Event $event
     */
    public function publish(Event $event)
    {
        $this->dispatcher->dispatch($event->name(), $event);
    }

    /**
     * Locks the store from receiving subscribers
     */
    public function lock()
    {
        $this->dispatcher = new ImmutableEventDispatcher($this->dispatcher);
    }
}
