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
use Symfony\Component\EventDispatcher\EventDispatcherInterface as ComponentDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractDispatcher;

final class SymfonyPublisher implements EventPublisher
{
    /**
     * @var ComponentDispatcher|ContractDispatcher
     */
    private $dispatcher;

    /**
     * @param ComponentDispatcher|ContractDispatcher $dispatcher
     */
    public function __construct(/* todo uncomment in major version EventDispatcherInterface */$dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param DomainEvent $event
     */
    public function publish(DomainEvent $event): void
    {
        if ($this->dispatcher instanceof ContractDispatcher) {
            // support for symfony >= 5 while keeping BC
            // todo remove conditional when upgrading dependency to current version
            $args = [
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
                \get_class($event),
            ];
        } else { // @phpstan-ignore-line
            $args = [
                \get_class($event),
                // @phpstan-ignore-next-line
                new class($event) extends \Symfony\Component\EventDispatcher\Event implements EventAdapter
                {
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

        $this->dispatcher->dispatch(...$args); // @phpstan-ignore-line
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

        $this->dispatcher->addListener($eventClassName, $transformer); // @phpstan-ignore-line
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
