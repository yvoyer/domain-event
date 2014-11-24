<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

use Star\Component\DomainEvent\Fixtures\MyService;
use Star\Component\DomainEvent\Fixtures\StuffToDoListener;

/**
 * Class EventStoreTest
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent
 */
final class EventStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventStore
     */
    private $store;

    public function setUp()
    {
        $this->store = new EventStore();
    }

    public function test_should_trigger_events()
    {
        $date = date('Y-m-d');
        $this->setExpectedException('\RuntimeException', "Event 'test' has been triggered at '{$date}' with id 'my-id'.");

        $this->store = new EventStore();
        $this->store->subscribe(new StuffToDoListener());

        $service = new MyService($this->store);
        $service->createAction();
    }

    /**
     * @expectedException        \BadMethodCallException
     * @expectedExceptionMessage Unmodifiable event dispatchers must not be modified.
     */
    public function test_should_be_immutable_on_lock()
    {
        $this->store = new EventStore();
        $this->store->lock();
        $this->store->subscribe(new StuffToDoListener());
    }
}
