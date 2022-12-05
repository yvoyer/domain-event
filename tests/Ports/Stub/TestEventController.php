<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Stub;

use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventPublisher;

final class TestEventController
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    public function __construct(EventPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function triggerEvent(DomainEvent $event): void
    {
        $this->publisher->publish($event);
    }
}
