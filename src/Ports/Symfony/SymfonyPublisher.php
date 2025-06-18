<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\DuplicatedListenerPriority;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;
use function array_key_exists;
use function array_merge;
use function count;
use function get_class;
use function method_exists;
use function property_exists;
use function sprintf;
use function trigger_error;
use function uniqid;

final class SymfonyPublisher implements EventPublisher
{
    /**
     * @var array<class-string<DomainEvent>, array<int, class-string>
     */
    private array $priorityMap = [];

    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function publish(
        DomainEvent $event,
        DomainEvent ...$others,
    ): void {
        $events = array_merge([$event], $others);
        foreach ($events as $event) {
            $this->dispatcher->dispatch(
                new class($event) implements EventAdapter, StoppableEventInterface {
                    private bool $propagationStopped = false;

                    public function __construct(
                        private DomainEvent $event,
                    ) {
                    }

                    public function getWrappedEvent(): DomainEvent
                    {
                        return $this->event;
                    }

                    public function isPropagationStopped(): bool
                    {
                        return $this->propagationStopped;
                    }

                    /**
                     * Stops the propagation of the event to further event listeners.
                     *
                     * If multiple event listeners are connected to the same event, no
                     * further event listener will be triggered once any trigger calls
                     * stopPropagation().
                     */
                    public function stopPropagation(): void
                    {
                        $this->propagationStopped = true;
                    }
                },
                get_class($event)
            );
        }
    }

    public function subscribe(
        EventListener $listener,
    ): void {
        if (!method_exists($listener, 'getListenedEvents')) {
            @trigger_error(
                sprintf(
                    '%s::listensTo() method is deprecated and will be removed in 3.0. '
                    . 'Define the new static method "%s::getListenedEvents(): array" '
                    . 'and move the content of "listensTo()" into it. '
                    . 'See: https://github.com/yvoyer/domain-event/issues/62',
                    EventListener::class,
                    get_class($listener)
                ),
                E_USER_DEPRECATED
            );
            // todo remove eval in 3.0
            $name = uniqid('listener');
            $class = <<<CLASS
use Star\Component\DomainEvent\EventListener;

final class {$name} implements EventListener
{
    /**
     * @var EventListener
     */
    public static \$listener;

    public function __construct(EventListener \$listener)
    {
        self::\$listener = \$listener;
    }

    public function listensTo(): array
    {
        throw new \RuntimeException(__METHOD__ . " not implemented yet.");
    }

    public function __call(\$name, \$args): void
    {
        self::\$listener->{\$name}(...\$args);
    }

    public static function getListenedEvents(): array
    {
        return self::\$listener->listensTo();
    }
}

CLASS;

            eval($class);
            $listener = new $name($listener);
        }

        foreach ($listener::getListenedEvents() as $eventClass => $method) {
            if (is_array($method)) {
                foreach ($method as $priority => $_method) {
                    $this->addListener($eventClass, $listener, $_method, $priority);
                }
                continue;
            }

            if (!array_key_exists($eventClass, $this->priorityMap)) {
                $bcBreakPriority = 0;
            } else {
                $bcBreakPriority = count($this->priorityMap[$eventClass]);
            }
            $this->addListener(
                $eventClass,
                $listener,
                $method,
                $bcBreakPriority // for BC. fixme remove on next major
            );
        }
    }

    /**
     * @param array<int, DomainEvent> $events
     */
    public function publishChanges(
        array $events,
    ): void {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }

    /**
     * @param class-string<DomainEvent> $eventClassName The class full name of the event
     * @throws BadMethodCallException
     */
    private function addListener(
        string $eventClassName,
        EventListener $listener,
        string $method,
        int $priority,
    ): void {
        $listenerClass = get_class($listener);
        if (property_exists($listener, 'listener')) {
            // todo remove condition in 3.0 We fetch the listener from the bridge
            $listenerClass = get_class($listener::$listener);
        }

        if (! \method_exists($listenerClass, $method)) {
            throw BadMethodCallException::methodNotDefinedOnListener($method, $listenerClass);
        }

        $transformer = function (EventAdapter $adapter) use ($listener, $method) {
            $listener->{$method}($adapter->getWrappedEvent());
        };

        $namespacedMethod = get_class($listener) . '::' . $method . '()';
        if (isset($this->priorityMap[$eventClassName][$priority])) {
            throw new DuplicatedListenerPriority(
                sprintf(
                    'Cannot subscribe a listener for event "%s" at priority "%s", ' .
                    'another listener is already listening at that priority. Attempting to push "%s", '  .
                    'but listener "%s" is already registered.',
                    $eventClassName,
                    $priority,
                    $namespacedMethod,
                    $this->priorityMap[$eventClassName][$priority]
                )
            );
        }
        $this->priorityMap[$eventClassName][$priority] = $namespacedMethod;
        $this->dispatcher->addListener($eventClassName, $transformer, $priority);
    }
}
