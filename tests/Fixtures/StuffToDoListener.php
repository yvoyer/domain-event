<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent\Fixtures;

use Star\Component\DomainEvent\Event;
use Star\Component\DomainEvent\EventListener;

/**
 * Class StuffToDoListener
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent\Fixtures
 */
final class StuffToDoListener implements EventListener
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            MyCustomEvent::name() => array(
                array('onCreate', 1),
            ),
        );
    }

    /**
     * @param Event $event
     */
    public function onCreate(MyCustomEvent $event)
    {
        throw new \RuntimeException("Event '{$event->name()}' has been triggered at '{$event->createdAt()->format('Y-m-d')}' with id '{$event->aggregateId()}'.");
    }
}
