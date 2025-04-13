<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony;

use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\DuplicatedListenerPriority;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\EventPublisher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as ComponentDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractDispatcher;
use Symfony\Contracts\EventDispatcher\Event as ContractEvent;
use Symfony\Component\EventDispatcher\Event as LegacyEvent;
use function array_key_exists;
use function array_merge;
use function count;
use function get_class;
use function sprintf;
use function trigger_error;

final class SymfonyPublisher implements EventPublisher
{
    /**
     * @var ComponentDispatcher|ContractDispatcher
     */
    private $dispatcher;

    /**
     * @var array<string, array<int, class-string>
     */
    private $priorityMap = [];

    /**
     * @param ComponentDispatcher|ContractDispatcher $dispatcher
     * @deprecated Signature will be changed to use "Symfony\Component\EventDispatcher\EventDispatcherInterface"
     * in 3.0. Consider changing your implementations.
     *
     * @see https://github.com/yvoyer/domain-event/issues/47
     */
    public function __construct(/* todo Uncomment in 3.0 EventDispatcherInterface */$dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function publish(
        DomainEvent $event,
        DomainEvent ...$others
    ): void {
        $events = array_merge([$event], $others);
        foreach ($events as $event) {
            if ($this->dispatcher instanceof ContractDispatcher) {
                // support for symfony >= 5 while keeping BC
                // todo remove conditional when upgrading dependency to current version
                $this->dispatcher->dispatch(
                    new class($event) extends ContractEvent implements EventAdapter {
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
                    \get_class($event)
                );
            } else {
                trigger_error(
                    sprintf(
                        'Using an instance of "%s" will be removed in 3.0. Consider using an instance of "%s".' .
                        ' See: https://github.com/yvoyer/domain-event/issues/18',
                        'Symfony\Component\EventDispatcher\EventDispatcherInterface',
                        'Symfony\Contracts\EventDispatcher\EventDispatcherInterface'
                    ),
                    E_USER_DEPRECATED
                );
                $this->dispatcher->dispatch(
                    \get_class($event),
                    new class($event) extends LegacyEvent implements EventAdapter
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
                    }
                );
            }
        }
    }

    /**
     * @param string $eventClassName The class full name of the event
     * @param EventListener $listener An object that listens to events
     * @param string $method
     * @param int $priority
     * @throws BadMethodCallException
     */
    private function addListener(
        string $eventClassName,
        EventListener $listener,
        string $method,
        int $priority
    ): void {
        if (! \method_exists($listener, $method)) {
            throw BadMethodCallException::methodNotDefinedOnListener($method, $listener);
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

    public function subscribe(EventListener $listener): void
    {
        foreach ($listener->listensTo() as $eventClass => $method) {
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
     * @param DomainEvent[] $events
     */
    public function publishChanges(array $events): void
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }
}
