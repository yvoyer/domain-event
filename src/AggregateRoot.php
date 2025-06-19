<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent;

use function array_merge;
use function array_pop;
use function explode;
use function get_class;
use function method_exists;

abstract class AggregateRoot
{
    /**
     * @var array<int, DomainEvent>
     */
    private array $mutations = [];

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
     * @return array<int, DomainEvent>
     */
    public function uncommitedEvents(): array
    {
        $mutations = $this->mutations;
        $this->mutations = [];

        return $mutations;
    }

    /**
     * @return static
     */
    public static function fromStream(
        DomainEvent ...$events,
    ): AggregateRoot {
        $aggregate = new static();
        if (count($events) > 0) {
            $aggregate->mutate(...$events);
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
            if (! method_exists($this, $method)) {
                throw AggregateRootException::missingMutationOnAggregate($this, $method);
            }

            $this->mutations[] = $event;
            $this->$method($event);
        }
    }

    /**
     * You can override this method to change the method format. Default: "onMyEvent" when $event class is "MyEvent".
     */
    protected function getEventMethod(
        DomainEvent $event,
    ): string {
        $class = get_class($event);
        $parts = explode('\\', $class);
        $name = array_pop($parts);

        return 'on' . $name;
    }
}
