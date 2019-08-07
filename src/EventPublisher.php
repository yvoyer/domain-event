<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent;

interface EventPublisher
{
    /**
     * @param EventListener $listener
     */
    public function subscribe(EventListener $listener): void;

    /**
     * @param DomainEvent $event
     */
    public function publish(DomainEvent $event): void;

    /**
     * @param DomainEvent[] $events
     */
    public function publishChanges(array $events): void;
}
