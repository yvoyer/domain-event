<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony;

use Star\Component\DomainEvent\DomainEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * @internal Adapter used internally by the SymfonyPublisher.
 */
final class EventAdapter extends Event
{
    /**
     * @var DomainEvent
     */
    private $event;

    /**
     * @param DomainEvent $event
     */
    public function __construct(DomainEvent $event)
    {
        $this->event = $event;
    }

    /**
     * @return DomainEvent
     */
    public function getWrappedEvent(): DomainEvent
    {
        return $this->event;
    }
}
