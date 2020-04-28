<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony;

use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\DomainEvent;
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
