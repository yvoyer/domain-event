<?php
/**
 * This file is part of the php-ddd project.
 *
 * (c) Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Symfony;

use Star\Component\DomainEvent\EventListener;
use Star\Component\DomainEvent\Fixtures\Blog\Event\BlogWasCreated;
use Star\Component\DomainEvent\Fixtures\Blog\Event\UserWasRegistered;
use Star\Component\DomainEvent\Fixtures\Blog\Listener\CreateBlogOnUserRegister;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class SymfonyPublisherTest extends \PHPUnit_Framework_TestCase implements EventListener
{
    /**
     * @var SymfonyPublisher
     */
    private $publisher;

    /**
     * @var bool
     */
    private $triggered = false;

    public function setUp()
    {
        $this->triggered = false;
        $this->publisher = new SymfonyPublisher(new EventDispatcher());
    }

    /**
     * @expectedException        \Star\Component\DomainEvent\BadMethodCallException
     * @expectedExceptionMessage The method 'onBadMethodCall' do not exists on listener 'Star\Component\DomainEvent\Symfony\MissingMethodListener'.
     */
    public function test_it_should_throw_exception_when_method_of_listener_do_not_exists()
    {
        $this->publisher->subscribe(new MissingMethodListener());
    }

    public function test_it_should_publish_event_to_listener()
    {
        $this->publisher->subscribe(new CreateBlogOnUserRegister($this->publisher));
        $this->publisher->subscribe($this);

        $this->assertFalse($this->triggered);
        $this->publisher->publish(new UserWasRegistered());
        $this->assertTrue($this->triggered);
    }

    public function onBlogWasCreated(BlogWasCreated $event)
    {
        $this->assertSame('My blog name', $event->blogName());
        $this->triggered = true;
    }

    /**
     * Key value map, where key is the event full class name and the map is the method
     * to call when the event is triggered.
     *
     * ie.
     * array(
     *     "Full\Path\To\Event" => 'onEvent',
     * )
     *
     * @return array
     */
    public function listensTo()
    {
        return [
            BlogWasCreated::class => 'onBlogWasCreated',
        ];
    }
}

final class MissingMethodListener implements EventListener
{
    /**
     * Key value map, where key is the event full class name and the map is the method
     * to call when the event is triggered.
     *
     * ie.
     * array(
     *     "Full\Path\To\Event" => 'onEvent',
     * )
     *
     * @return array
     */
    public function listensTo()
    {
        return [
            BlogWasCreated::class => 'onBadMethodCall',
        ];
    }
}
