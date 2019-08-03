<?php declare(strict_types=1);
/**
 * This file is part of the php-ddd project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Ports\Symfony;

use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\BadMethodCallException;
use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\Fixtures\Blog\Event\BlogWasCreated;
use Star\Component\DomainEvent\Fixtures\Blog\Event\UserWasRegistered;
use Star\Component\DomainEvent\Fixtures\Blog\Listener\CreateBlogOnUserRegister;
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
        $this->publisher->subscribe(new CreateBlogOnUserRegister($this->publisher));
        $this->publisher->subscribe($this);

        $this->assertFalse($this->triggered);
        $this->publisher->publish(new UserWasRegistered());
        $this->assertTrue($this->triggered);
    }

    public function onBlogWasCreated(BlogWasCreated $event): void
    {
        $this->assertSame('My blog name', $event->blogName());
        $this->triggered = true;
    }

    public function listensTo(): array
    {
        return [
            BlogWasCreated::class => 'onBlogWasCreated',
        ];
    }
}

final class MissingMethodListener implements EventListener
{
    public function listensTo(): array
    {
        return [
            BlogWasCreated::class => 'onBadMethodCall',
        ];
    }
}
