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
     * @deprecated The signature of this method will change in 3.0. Consider changing your implementations.
     * @see https://github.com/yvoyer/domain-event/issues/52
     */
    public function publish(DomainEvent $event/*, DomainEvent ...$others*/): void;

    /**
     * @param DomainEvent[] $events
     * @deprecated This method will be removed in 3.0. Pass the events to publish.
     * @see self::publish()
     * @see https://github.com/yvoyer/domain-event/issues/52
     */
    public function publishChanges(array $events): void;
}
