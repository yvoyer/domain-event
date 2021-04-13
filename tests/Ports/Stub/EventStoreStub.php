<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Stub;

use Star\Component\DomainEvent\EventPublisher;

final class EventStoreStub
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    public function __construct(EventPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function save(string $action): void
    {
        $this->publisher->publish(new ActionWasDone($action));
    }
}
