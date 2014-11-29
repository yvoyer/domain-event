<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

/**
 * Class EventMock
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent
 */
trait EventMock
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDomainEvent()
    {
        return $this->getMock('Star\Component\DomainEvent\Event');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDomainEventPublisher()
    {
        return $this->getMock('Star\Component\DomainEvent\EventPublisher');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDomainEventListener()
    {
        return $this->getMock('Star\Component\DomainEvent\EventListener');
    }
}
