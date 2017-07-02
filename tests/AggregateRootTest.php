<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

final class AggregateRootTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException        \Star\Component\DomainEvent\AggregateRootException
     * @expectedExceptionMessage The mutation 'onMissingMethodEvent' do not exists on aggregate 'Star\Component\DomainEvent\StubAggregate'.
     */
    public function test_it_should_throw_exception_when_method_is_missing()
    {
        StubAggregate::fromStream([new MissingMethodEvent()]);
    }

    public function test_it_should_reset_uncommited_events_when_fetched()
    {
        $aggregate = StubAggregate::fromStream([new ValidEvent()]);
        $events = $aggregate->uncommitedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ValidEvent::class, $events[0]);
        $this->assertCount(0, $aggregate->uncommitedEvents());
    }

    public function test_it_should_apply_events_in_order_of_execution_when_event_triggers_another_event()
    {
        $aggregate = StubAggregate::fromStream([new MultipleEventWereTriggered()]);

        $events = $aggregate->uncommitedEvents();
        $this->assertCount(3, $events);
        $this->assertInstanceOf(MultipleEventWereTriggered::class, $events[0]);
        $this->assertInstanceOf(EventOneWasTriggered::class, $events[1]);
        $this->assertInstanceOf(EventTwoWasTriggered::class, $events[2]);
    }
}

final class StubAggregate extends AggregateRoot
{
    public function onValidEvent(ValidEvent $event)
    {
    }

    protected function onMultipleEventWereTriggered($event)
    {
        $this->mutate(new EventOneWasTriggered());
    }

    public function onEventOneWasTriggered($event)
    {
        $this->mutate(new EventTwoWasTriggered());
    }

    public function onEventTwoWasTriggered($event)
    {
    }
}

final class MissingMethodEvent implements DomainEvent
{
}

final class ValidEvent implements DomainEvent
{
}

final class MultipleEventWereTriggered implements DomainEvent
{
}

final class EventOneWasTriggered implements DomainEvent
{
}

final class EventTwoWasTriggered implements DomainEvent
{
}
