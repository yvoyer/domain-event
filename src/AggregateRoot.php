<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 1.0
 */

namespace Star\Component\DomainEvent;

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
     * @param DomainEvent[] $events
     *
     * @return AggregateRoot|static
     */
    public static function fromStream(array $events): AggregateRoot
    {
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
     * @param DomainEvent $event
     * @throws AggregateRootException
     */
    protected function mutate(DomainEvent $event): void
    {
        $method = $this->getEventMethod($event);
        if (! \method_exists($this, $method)) {
            throw AggregateRootException::missingMutationOnAggregate($this, $method);
        }

        $this->mutations[] = $event;
        $this->$method($event);
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
