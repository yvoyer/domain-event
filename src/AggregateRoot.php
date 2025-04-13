<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent;

use function array_merge;
use function count;
use function func_get_args;
use function is_array;
use function trigger_error;
use function var_dump;

abstract class AggregateRoot
{
    /**
     * @var DomainEvent[]
     */
    private $mutations = [];

    /**
     * Protected, define a static constructor (Factory method)
     */
    final protected function __construct()
    {
        $this->configure();
    }

    /**
     * This method allows child classes to define properties while keeping construct final
     */
    protected function configure(): void
    {
    }

    /**
     * @return DomainEvent[]
     */
    public function uncommitedEvents(): array
    {
        $mutations = $this->mutations;
        $this->mutations = [];

        return $mutations;
    }

    /**
     * @param DomainEvent[]|DomainEvent $events
     *
     * @return static
     */
    public static function fromStream($events): AggregateRoot
    {
        $args = func_get_args();
        if (count($args) === 1 && is_array($args[0])) {
            /**
             * @see https://github.com/yvoyer/domain-event/issues/55
             */
            trigger_error(
                'Passing an array of DomainEvent to AggregateRoot::fromStream() will be removed in 3.0.' .
                ' Pass them directly.',
                E_USER_DEPRECATED
            );

            // deprecated
        } else {
            $events = $args;
        }

        /**
         * @var static $aggregate
         */
        $aggregate = new static();
        foreach ($events as $event) {
            $aggregate->mutate($event);
        }

        return $aggregate;
    }

    /**
     * @throws AggregateRootException
     */
    protected function mutate(
        DomainEvent $event,
        DomainEvent ...$others
    ): void {
        $events = array_merge([$event], $others);
        foreach ($events as $event) {
            $method = $this->getEventMethod($event);
            if (! \method_exists($this, $method)) {
                throw AggregateRootException::missingMutationOnAggregate($this, $method);
            }

            $this->mutations[] = $event;
            $this->$method($event);
        }
    }

    /**
     * You can override this method to change the method format. Default: "onMyEvent" when $event class is "MyEvent".
     *
     * @param DomainEvent $event
     *
     * @return string
     */
    protected function getEventMethod(DomainEvent $event): string
    {
        $class = \get_class($event);
        $parts = \explode('\\', $class);
        $name = \array_pop($parts);
        $method = 'on' . $name;

        return $method;
    }
}
