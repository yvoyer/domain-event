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
}

final class StubAggregate extends AggregateRoot
{
    public function onValidEvent(ValidEvent $event)
    {
    }
}

final class MissingMethodEvent implements DomainEvent
{
}

final class ValidEvent implements DomainEvent
{
}
