<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

use Star\Component\DomainEvent\Fixtures\MissConfiguredListener;
use Star\Component\DomainEvent\Fixtures\MoveAggregateToDoneListener;
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

    /**
     * @var MyService
     */
    private $service;

    public function setUp()
    {
        $this->store = new EventStore();
        $this->service = new MyService($this->store);
    }

    public function test_should_trigger_events()
    {
        $date = date('Y-m-d');
        $this->setExpectedException('\RuntimeException', "Event 'test' has been triggered at '{$date}' with id 'my-id'.");

        $this->store->subscribe(new StuffToDoListener());
        $this->service->createAction();
    }

    /**
     * @expectedException        \BadMethodCallException
     * @expectedExceptionMessage Unmodifiable event dispatchers must not be modified.
     */
    public function test_should_be_immutable_on_lock()
    {
        $this->store->lock();
        $this->store->subscribe(new StuffToDoListener());
    }

    public function test_should_allow_listener_to_extends_the_helper()
    {
        $date = date('Y-m-d');
        $this->setExpectedException('\RuntimeException', "Event 'test' was triggered with: MoveAggregateToDoneListener at '{$date}' with id 'my-id'.");

        $this->store->subscribe(new MoveAggregateToDoneListener());
        $this->service->createAction();
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage The listener is not configured to listen to any event. Did you configured the listener to listen to any events?
     */
    public function test_should_throw_exception_when_no_event_is_listened_to_by_listener()
    {
        $this->store->subscribe(new MissConfiguredListener());
    }
}
