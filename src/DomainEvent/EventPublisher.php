<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

/**
 * @author  Yannick Voyer (http://github.com/yvoyer)
 */
interface EventPublisher
{
    /**
     * @param EventListener $listener
     */
    public function subscribe(EventListener $listener);

    /**
     * @param DomainEvent $event
     */
    public function publish(DomainEvent $event);

    /**
     * @param DomainEvent[] $events
     */
    public function publishChanges(array $events);
}
