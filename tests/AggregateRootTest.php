<?php declare(strict_types=1);

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
        StubAggregate::fromStream(new MissingMethodEvent());
    }

    public function test_it_should_reset_uncommited_events_when_fetched(): void
    {
        $aggregate = StubAggregate::fromStream(new ValidEvent());
        $events = $aggregate->uncommitedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ValidEvent::class, $events[0]);
        self::assertCount(0, $aggregate->uncommitedEvents());
    }

    public function test_it_should_apply_events_in_order_of_execution_when_event_triggers_another_event(): void
    {
        $aggregate = StubAggregate::fromStream(new MultipleEventWereTriggered());

        $events = $aggregate->uncommitedEvents();
        self::assertCount(3, $events);
        self::assertInstanceOf(MultipleEventWereTriggered::class, $events[0]);
        self::assertInstanceOf(EventOneWasTriggered::class, $events[1]);
        self::assertInstanceOf(EventTwoWasTriggered::class, $events[2]);
    }

    public function test_it_should_allow_child_class_to_call_construct(): void
    {
        $root = RootWithConstruct::fromStream();
        self::assertCount(0, $root->uncommitedEvents());
        self::assertSame(12, $root->id);
    }

    public function test_it_should_allow_to_pass_multiple_events_on_mutate(): void
    {
        $root = StubAggregate::fromStream();
        $root->addEvents(
            $one = new NamedEvent('one'),
            $two = new NamedEvent('two'),
            $three = new NamedEvent('three')
        );

        self::assertCount(3, $events = $root->uncommitedEvents());
        self::assertSame($one, $events[0]);
        self::assertSame($two, $events[1]);
        self::assertSame($three, $events[2]);
    }

    public function test_it_should_allow_to_pass_stream_of_event_as_splat_operator(): void
    {
        $class = StubAggregate::fromStream(
            new NamedEvent(),
            new NamedEvent(),
            new NamedEvent()
        );

        self::assertCount(3, $class->uncommitedEvents());
    }
}

final class StubAggregate extends AggregateRoot
{
    public function addEvents(
        DomainEvent $event,
        DomainEvent ...$others
    ): void {
        $this->mutate($event, ...$others);
    }

    public function onValidEvent(ValidEvent $event): void
    {
    }

    protected function onMultipleEventWereTriggered(MultipleEventWereTriggered $event): void
    {
        $this->mutate(new EventOneWasTriggered());
    }

    protected function onNamedEvent(NamedEvent $event): void
    {
    }

    public function onEventOneWasTriggered(EventOneWasTriggered $event): void
    {
        $this->mutate(new EventTwoWasTriggered());
    }

    public function onEventTwoWasTriggered(EventTwoWasTriggered $event): void
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

final class NamedEvent implements DomainEvent
{
    public function __construct(
        public string $name = 'test',
    ) {
    }
}

final class RootWithConstruct extends AggregateRoot
{
    public ?int $id;

    protected function configure(): void
    {
        $this->id = 12;
    }
}
