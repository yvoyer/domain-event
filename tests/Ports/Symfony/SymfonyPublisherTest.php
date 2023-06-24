<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony;

use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\DuplicatedListenerPriority;
use Star\Component\DomainEvent\EventListener;
use Star\Example\Blog\Domain\Event\Post\PostWasDrafted;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as ComponentDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractDispatcher;

final class SymfonyPublisherTest extends TestCase implements EventListener
{
    /**
     * @var SymfonyPublisher
     */
    private $publisher;

    /**
     * @var bool
     */
    private $triggered = false;

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

    public function listensTo(): array
    {
        return [
            SomethingOccurred::class => 'onEventOccurred',
        ];
    }

    public function test_it_should_publish_event_with_contract_dispatcher(): void
    {
        if (! \interface_exists(ContractDispatcher::class)) {
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
        if (! \interface_exists(ComponentDispatcher::class)) {
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
                public $methodCalls = [];

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

                public function listensTo(): array
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
}

final class MissingMethodListener implements EventListener
{
    public function listensTo(): array
    {
        return [
            PostWasDrafted::class => 'onBadMethodCall',
        ];
    }
}

final class SomethingOccurred implements DomainEvent
{
    /**
     * @var string
     */
    private $action;

    public function __construct(string $action)
    {
        $this->action = $action;
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

    public function listensTo(): array
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

    public function listensTo(): array
    {
        return [
            'event' => [
                100 => 'method',
            ],
        ];
    }
}
