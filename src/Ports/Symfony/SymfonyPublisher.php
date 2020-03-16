<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony;

use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractInterface;

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
    public function publish(DomainEvent $event): void
    {
        if ($this->dispatcher instanceof ContractInterface) {
            // support for symfon >= 5 while keeping BC
            $args = [
                \get_class($event),
                new class($event) extends \Symfony\Contracts\EventDispatcher\Event implements EventAdapter {
                    /**
                     * @var DomainEvent
                     */
                    private $event;

                    public function __construct(DomainEvent $event)
                    {
                        $this->event = $event;
                    }

                    public function getWrappedEvent(): DomainEvent
                    {
                        return $this->event;
                    }
                },
            ];
        } else {
            $args = [
                \get_class($event),
                new class($event) extends \Symfony\Component\EventDispatcher\Event implements EventAdapter {
                    /**
                     * @var DomainEvent
                     */
                    private $event;

                    public function __construct(DomainEvent $event)
                    {
                        $this->event = $event;
                    }

                    public function getWrappedEvent(): DomainEvent
                    {
                        return $this->event;
                    }
                },
            ];
        }

        $this->dispatcher->dispatch(...$args);
    }

    /**
     * @param string $eventClassName The class full name of the event
     * @param EventListener $listener An object that listens to events
     * @param string $method
     * @throws \Star\Component\DomainEvent\BadMethodCallException
     */
    private function addListener(string $eventClassName, EventListener $listener, string $method): void
    {
        if (! \method_exists($listener, $method)) {
            throw BadMethodCallException::methodNotDefinedOnListener($method, $listener);
        }

        $transformer = function (EventAdapter $adapter) use ($listener, $method) {
            $listener->{$method}($adapter->getWrappedEvent());
        };

        $this->dispatcher->addListener($eventClassName, $transformer);
    }

    /**
     * @param EventListener $listener
     */
    public function subscribe(EventListener $listener): void
    {
        foreach ($listener->listensTo() as $eventClass => $method) {
            $this->addListener($eventClass, $listener, $method);
        }
    }

    /**
     * @param DomainEvent[] $events
     */
    public function publishChanges(array $events): void
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }
}
