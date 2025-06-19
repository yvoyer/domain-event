<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\DuplicatedListenerPriority;
use Star\Component\DomainEvent\EventListener;
use Star\Example\Blog\Domain\Event\Post\PostWasDrafted;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as ComponentDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractDispatcher;
use function interface_exists;
use function sprintf;

final class SymfonyPublisherTest extends TestCase implements EventListener
{
    private SymfonyPublisher $publisher;

    private bool $triggered = false;

    public function setUp(): void
    {
        $this->triggered = false;
        $this->publisher = new SymfonyPublisher(new EventDispatcher());
    }

    public function test_it_should_throw_exception_when_method_of_listener_do_not_exists(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            "The method 'onBadMethodCall' do not exists on listener '" . MissingMethodListener::class
        );
        $this->publisher->subscribe(new MissingMethodListener());
    }

    public function test_it_should_publish_event_to_listener(): void
    {
        $this->publisher->subscribe($this);

        $this->assertFalse($this->triggered);
        $this->publisher->publish(new SomethingOccurred('My action name'));
        $this->assertTrue($this->triggered);
    }

    public function onEventOccurred(SomethingOccurred $event): void
    {
        $this->assertSame('My action name', $event->action());
        $this->triggered = true;
    }

    public static function getListenedEvents(): array
    {
        return [
            SomethingOccurred::class => 'onEventOccurred',
        ];
    }

    public function test_it_should_publish_event_with_contract_dispatcher(): void
    {
        if (! interface_exists(ContractDispatcher::class)) {
            $this->markTestSkipped('Interface "' . ContractDispatcher::class . '" is not defined.');
        }
        $dispatcher = $this->createMock(ContractDispatcher::class);
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(EventAdapter::class));
        $publisher = new SymfonyPublisher($dispatcher);
        $publisher->publish(new class() implements DomainEvent {});
    }

    public function test_it_should_publish_event_with_component_dispatcher(): void
    {
        if (! interface_exists(ComponentDispatcher::class)) {
            $this->markTestSkipped('Interface "' . ComponentDispatcher::class . '" is not defined.');
        }
        $dispatcher = $this->createMock(ComponentDispatcher::class);
        $dispatcher
            ->expects($this->once())
            ->method('dispatch');
        $publisher = new SymfonyPublisher($dispatcher);
        $publisher->publish(new class() implements DomainEvent {});
    }

    public function test_it_should_publish_in_order_of_priority(): void
    {
        $dispatcher = new EventDispatcher();
        $publisher = new SymfonyPublisher($dispatcher);
        $publisher->subscribe(
            $listener = new class() implements EventListener {
                /**
                 * @var array<int, string>
                 */
                public array $methodCalls = [];

                public function onZero(): void
                {
                    $this->methodCalls[] = __FUNCTION__;
                }

                public function onPositive(): void
                {
                    $this->methodCalls[] = __FUNCTION__;
                }

                public function onNegative(): void
                {
                    $this->methodCalls[] = __FUNCTION__;
                }

                public static function getListenedEvents(): array
                {
                    return [
                        SomethingOccurred::class => [
                            0 => 'onZero', // Key is priority
                            45 => 'onPositive', // Key is priority
                            -4 => 'onNegative', // Key is priority
                        ]
                    ];
                }
            }
        );
        self::assertSame(
            [],
            $listener->methodCalls
        );

        $publisher->publish(new SomethingOccurred('action'));

        self::assertSame(
            [
                'onPositive',
                'onZero',
                'onNegative',
            ],
            $listener->methodCalls
        );
    }

    public function test_it_should_not_throw_exception_when_more_than_one_listener_listens_on_same_event(): void
    {
        $dispatcher = new EventDispatcher();
        $publisher = new SymfonyPublisher($dispatcher);
        $publisher->subscribe(new ListenerWithOldPriority());
        $publisher->subscribe(new ListenerWithOldPriority());
        $this->expectNotToPerformAssertions(); // For BC break using old pattern
    }

    public function test_it_should_throw_exception_when_more_than_one_priority_exists_on_event_with_new_priority_concept(): void
    {
        $dispatcher = new EventDispatcher();
        $publisher = new SymfonyPublisher($dispatcher);
        $publisher->subscribe(new ListenerWithNewPriority());

        $this->expectException(DuplicatedListenerPriority::class);
        $this->expectExceptionMessage(
            'Cannot subscribe a listener for event "event" at priority "100", another listener is' .
            ' already listening at that priority.'
        );
        $publisher->subscribe(new ListenerWithNewPriority());
    }

    public function test_it_should_allow_passing_more_than_one_event(): void
    {
        $dispatcher = $this->createMockDispatcher();
        $dispatcher
            ->expects(self::exactly(3))
            ->method('dispatch')
        ;
        $publisher = new SymfonyPublisher($dispatcher);
        $publisher->publish(
            $this->createMock(DomainEvent::class),
            $this->createMock(DomainEvent::class),
            $this->createMock(DomainEvent::class)
        );
    }

    /**
     * @return MockObject|ContractDispatcher|ComponentDispatcher
     */
    private function createMockDispatcher(): MockObject
    {
        if (interface_exists(ContractDispatcher::class)) {
            return $this->createMock(ContractDispatcher::class);
        } elseif (interface_exists(ComponentDispatcher::class)) {
            return $this->createMock(ComponentDispatcher::class);
        }

        $this->markTestSkipped(
            sprintf(
                'Interface "%s|%s" are not defined.',
                ComponentDispatcher::class,
                ContractDispatcher::class
            )
        );
    }

    public function test_it_should_register_listener_with_new_api(): void
    {
        $dispatcher = new EventDispatcher();
        $publisher = new SymfonyPublisher($dispatcher);
        $publisher->subscribe($listener = new ListenerWithMethod());
        self::assertCount(0, $listener->dispatchedEvents);

        $publisher->publish(
            new Version3EventOne(),
            new Version3EventTwo()
        );

        self::assertCount(1, $dispatcher->getListeners(Version3EventOne::class));
        self::assertCount(3, $dispatcher->getListeners(Version3EventTwo::class));
        self::assertCount(3, $listener->dispatchedEvents);
    }
}

final class MissingMethodListener implements EventListener
{
    public static function getListenedEvents(): array
    {
        return [
            PostWasDrafted::class => 'onBadMethodCall',
        ];
    }
}

final class SomethingOccurred implements DomainEvent
{
    public function __construct(
        private string $action,
    ) {
    }

    public function action(): string
    {
        return $this->action;
    }
}

final class ListenerWithOldPriority implements EventListener
{
    public function method(): void
    {
    }

    public static function getListenedEvents(): array
    {
        return [
            'old-event' => 'method',
        ];
    }
}

final class ListenerWithNewPriority implements EventListener
{
    public function method(): void
    {
    }

    public static function getListenedEvents(): array
    {
        return [
            'event' => [
                100 => 'method',
            ],
        ];
    }
}

final class Version3EventOne implements DomainEvent
{
}
final class Version3EventTwo implements DomainEvent
{
}

final class ListenerWithMethod implements EventListener
{
    /**
     * @var array<int, DomainEvent>
     */
    public array $dispatchedEvents = [];

    public function methodOne(Version3EventOne $event): void
    {
        $this->dispatchedEvents[] = $event;
    }

    public function methodTen(Version3EventTwo $event): void
    {
        $this->dispatchedEvents[] = $event;
    }

    public function methodZero(Version3EventTwo $event): void
    {
        $this->dispatchedEvents[] = $event;
    }

    public function methodMinusTen(Version3EventTwo $event): void
    {
    }

    public static function getListenedEvents(): array
    {
        return [
            Version3EventOne::class => 'methodOne',
            Version3EventTwo::class => [
                10 => 'methodTen',
                0 => 'methodZero',
                -10 => 'methodMinusTen',
            ],
        ];
    }
}
