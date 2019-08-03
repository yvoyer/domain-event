<?php declare(strict_types=1);
/**
 * This file is part of the php-ddd project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Symfony;

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
