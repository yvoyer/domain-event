<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Fixtures;

use Star\Component\DomainEvent\EventPublisher;

/**
 * Class MyService
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent\Fixtures
 */
final class MyService
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    public function __construct(EventPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function createAction()
    {
        $aggregate = new MyAggregate($this->publisher);
        $id = $aggregate->create();

        $this->publisher->publish(new MyCustomEvent($id));
    }
}
