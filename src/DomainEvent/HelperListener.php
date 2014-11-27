<?php
/**
 * This file is part of the StarDomainEvent project.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Star\Component\DomainEvent;

/**
 * Class HelperListener
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Star\Component\DomainEvent
 */
abstract class HelperListener implements EventListener
{
    /**
     * @var array
     */
    public  static $eventConfiguration = array();

    public function __construct()
    {
        $this->configure();
    }

    protected abstract function configure();

    /**
     * @param string $eventName
     * @param string $methodName
     * @param int    $priority
     */
    protected function listenTo($eventName, $methodName, $priority)
    {
        $listenerClass = get_class($this);

        self::$eventConfiguration[$listenerClass][$eventName][] = array($methodName, $priority);
    }

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
    final public static function getSubscribedEvents()
    {
        $className = get_called_class();
        if (false === isset(self::$eventConfiguration[$className])) {
            throw new \RuntimeException('The listener is not configured to listen to any event. Did you configured the listener to listen to any events?');
        }

        return self::$eventConfiguration[$className];
    }
}
