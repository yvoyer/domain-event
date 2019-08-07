<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony;

use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\DomainEvent;
use Star\Component\DomainEvent\EventListener;
use Star\Example\Blog\Domain\Event\Post\PostWasDrafted;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
