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

    public function publish(DomainEvent ...$events): void;
}
