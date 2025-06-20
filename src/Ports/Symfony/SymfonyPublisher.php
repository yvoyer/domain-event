<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony;

use Psr\EventDispatcher\StoppableEventInterface;
use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\DuplicatedListenerPriority;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function array_key_exists;
use function count;
use function get_class;
use function method_exists;
use function sprintf;

final class SymfonyPublisher implements EventPublisher
{
    /**
     * @var array<class-string<DomainEvent>, array<int, string>>
     */
    private array $priorityMap = [];

    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function publish(DomainEvent ...$events): void
    {
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
                $bcBreakPriority,
            );
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
        if (! method_exists($listenerClass, $method)) {
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
