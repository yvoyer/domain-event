<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Fixtures;

use Star\Component\DomainEvent\Event;
use Star\Component\DomainEvent\HelperListener;

/**
 * Class MoveAggregateToDoneListener
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent\Fixtures
 */
final class MoveAggregateToDoneListener extends HelperListener
{
    protected function configure()
    {
        $this->listenTo(MyCustomEvent::name(), 'onMyCustomEventAction', 10);
    }

    /**
     * @param Event $event
     */
    public function onMyCustomEventAction(Event $event)
    {
        $date = date('Y-m-d');
        throw new \RuntimeException("Event '{$event->name()}' was triggered with: MoveAggregateToDoneListener at '{$date}' with id 'my-id'.");
    }
}
