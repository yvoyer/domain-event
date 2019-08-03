<?php declare(strict_types=1);
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
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
    protected function __construct()
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
     * @return static
     */
    public static function fromStream(array $events): self
    {
        /**
         * @var AggregateRoot $aggregate
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
