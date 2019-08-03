<?php declare(strict_types=1);
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

use PHPUnit\Framework\TestCase;

final class AggregateRootTest extends TestCase
{
    public function test_it_should_throw_exception_when_method_is_missing(): void
    {
        $this->expectException(AggregateRootException::class);
        $this->expectExceptionMessage(
            "The mutation 'onMissingMethodEvent' do not exists on aggregate 'Star\Component\DomainEvent\StubAggregate'."
        );
        StubAggregate::fromStream([new MissingMethodEvent()]);
    }

    public function test_it_should_reset_uncommited_events_when_fetched(): void
    {
        $aggregate = StubAggregate::fromStream([new ValidEvent()]);
        $events = $aggregate->uncommitedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ValidEvent::class, $events[0]);
        $this->assertCount(0, $aggregate->uncommitedEvents());
    }

    public function test_it_should_apply_events_in_order_of_execution_when_event_triggers_another_event(): void
    {
        $aggregate = StubAggregate::fromStream([new MultipleEventWereTriggered()]);

        $events = $aggregate->uncommitedEvents();
        $this->assertCount(3, $events);
        $this->assertInstanceOf(MultipleEventWereTriggered::class, $events[0]);
        $this->assertInstanceOf(EventOneWasTriggered::class, $events[1]);
        $this->assertInstanceOf(EventTwoWasTriggered::class, $events[2]);
    }

    public function test_it_should_allow_child_class_to_call_construct(): void
    {
        $root = RootWithConstruct::fromStream([]);
        $this->assertCount(0, $root->uncommitedEvents());
    }
}

final class StubAggregate extends AggregateRoot
{
    public function onValidEvent(ValidEvent $event): void
    {
    }

    protected function onMultipleEventWereTriggered($event): void
    {
        $this->mutate(new EventOneWasTriggered());
    }

    public function onEventOneWasTriggered($event): void
    {
        $this->mutate(new EventTwoWasTriggered());
    }

    public function onEventTwoWasTriggered($event): void
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

final class RootWithConstruct extends AggregateRoot
{
    protected function __construct()
    {
        parent::__construct();
    }
}
