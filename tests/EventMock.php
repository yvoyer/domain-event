<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

/**
 * @author  Yannick Voyer (http://github.com/yvoyer)
 */
trait EventMock
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDomainEvent()
    {
        return $this->getMock(DomainEvent::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDomainEventPublisher()
    {
        return $this->getMock(EventPublisher::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDomainEventListener()
    {
        return $this->getMock(EventListener::class);
    }
}
