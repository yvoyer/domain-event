<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony;

use Star\Component\DomainEvent\DomainEvent;

/**
 * @internal Adapter used internally by the SymfonyPublisher.
 */
interface EventAdapter
{
    public function getWrappedEvent(): DomainEvent;
}
