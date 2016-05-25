<?php
/**
 * This file is part of the php-ddd project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Symfony;

use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SymfonyPublisher implements EventPublisher
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param DomainEvent $event
     */
    public function publish(DomainEvent $event)
    {
        $this->dispatcher->dispatch(get_class($event), new EventAdapter($event));
    }

    /**
     * @param string $eventClassName The class full name of the event
     * @param EventListener $listener An object that listens to events
     * @param string $method
     * @throws \Star\Component\DomainEvent\BadMethodCallException
     */
    private function addListener($eventClassName, EventListener $listener, $method)
    {
        if (! method_exists($listener, $method)) {
            throw BadMethodCallException::methodNotDefinedOnListener($method, $listener);
        }

        $transformer = function (EventAdapter $adapter) use ($listener, $method) {
            call_user_func([$listener, $method], $adapter->getWrappedEvent());
        };

        $this->dispatcher->addListener($eventClassName, $transformer);
    }

    /**
     * @param EventListener $listener
     */
    public function subscribe(EventListener $listener)
    {
        foreach ($listener->listensTo() as $eventClass => $method) {
            $this->addListener($eventClass, $listener, $method);
        }
    }

    /**
     * @param DomainEvent[] $events
     */
    public function publishChanges(array $events)
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }
}
